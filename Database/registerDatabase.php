<?php

/*	MILESTONE 4: RECEIVING HASHED PASSWORD INPUT FROM BACKEND WITH RABBITMQ CLUSTER */

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

//	-- SECTION TO RECEIVE MESSAGES FOR PROCESSING --

//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
$connectionDB = null;
$rabbitNodes = array('192.168.194.2', '192.168.194.1');

foreach ($rabbitNodes as $node) {
	try {
        	$connectionDB = new AMQPStreamConnection(
							$node,
							5672,
							'foodquest',
							'rabbit123'
		);
		echo "SIGNUP CONNECTION TO RABBITMQ WAS SUCCESSFUL @ $node\n";
		break;
	} catch (Exception $e) {
		echo "ERROR: RABBITMQ CONNECTION WAS UNSUCCESSFUL @ $node\n";
		continue;
	}
}

if (!$connectionDB) {
	die("RABBITMQ CONNECTION ERROR: DATABASE COULD NOT CONNECT TO ANY RABBITMQ NODE IN CLUSTER.");
}

// CONNECTING TO MAIN RABBITMQ
//$connectionDB = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');

$channelDB = $connectionDB->channel();

$channelDB->exchange_declare('backend_exchange', 'direct', false, false, false);

// Using DURABLE QUEUES: Third parameter is true
$channelDB->queue_declare('database_mailbox', false, true, false, false);

// Binding key
$bindingKeyDB = "database";

// Binding three items together to receive msgs
$channelDB->queue_bind('database_mailbox', 'backend_exchange', $bindingKeyDB);

// Terminal message to signal we are waiting for messages from BACKEND
echo '[*] Waiting for BACKEND messages. To exit press CTRL+C', "\n\n";

$callbackDB = function ($msg) use ($channelDB) {
	echo '[+] RECEIVED HASHED PASSWORD FROM BACKEND', "\n\n";

    	$backendMsg = json_decode($msg->getBody(), true);

	print_r($backendMsg);

	/*
    // GETTING VARIABLES SENT FROM BACKEND
    $validUser = filter_var($backendMsg['username'], FILTER_SANITIZE_STRING);
    $validPass = filter_var($backendMsg['password'], FILTER_SANITIZE_STRING);
    $validFirst = filter_var($backendMsg['first'], FILTER_SANITIZE_STRING);
    $validLast = filter_var($backendMsg['last'], FILTER_SANITIZE_STRING);
    $validEmail = filter_var($backendMsg['email'], FILTER_SANITIZE_STRING);
    $validAddress = filter_var($backendMsg['address'], FILTER_SANITIZE_STRING);
    $validPhone = filter_var($backendMsg['phone'], FILTER_SANITIZE_STRING);
	*/

    //    GETTING VARIABLES SENT FROM BACKEND
    $validUser   = $backendMsg['username'];
    $validPass   = $backendMsg['password'];
    $validFirst  = $backendMsg['first'];
    $validLast   = $backendMsg['last'];
    $validEmail  = $backendMsg['email'];
    $validAddress = $backendMsg['address'];
    $validPhone  = $backendMsg['phone'];

    //  	JSON to String sanitize
    $stringUser = filter_var($validUser, FILTER_SANITIZE_STRING);
    $stringPass = filter_var($validPass, FILTER_SANITIZE_STRING);
    $stringFirst = filter_var($validFirst, FILTER_SANITIZE_STRING);
    $stringLast = filter_var($validLast, FILTER_SANITIZE_STRING);
    $stringEmail = filter_var($validEmail, FILTER_SANITIZE_STRING);
    $stringAddress = filter_var($validAddress, FILTER_SANITIZE_STRING);
    $stringPhone = filter_var($validPhone, FILTER_SANITIZE_STRING);


    	/*	SIGNUP REGEX CHECK	*/

	$checkValues = array();

    	// USERNAME REGEX
    	$regexUser = preg_match('/^[a-zA-Z0-9_]+$/', $stringUser);
	//	USERNAME REGEX
	if (preg_match('/^[a-zA-Z0-9_]+$/', $stringUser)) {
		echo "VALID USERNAME";
		$checkValues['user'] = 'VALID USERNAME';
	}
	else {
		echo "WRONG USERNAME";
		$checkValues['user'] = 'WRONG USERNAME';
	}

    	// PASSWORD REGEX
    	$strong_password = "/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/";
    	$regexPass = preg_match($strong_password, $stringPass);
        if (preg_match($strong_password, $stringPass)) {
                echo "VALID PASSWORD";
		$checkValues['pass'] = 'VALID PASSWORD';
        }
        else {
                echo "WRONG PASSWORD";
		$checkValues['pass'] = 'WRONG PASSWORD';
        }

    	// FIRST NAME REGEX
    	$regexFirst = preg_match('/^[a-zA-Z]+$/', $stringFirst);

        if (preg_match('/^[a-zA-Z]+$/', $stringFirst)) {
                echo "VALID FIRST NAME";
		$checkValues['first'] = 'VALID FIRST NAME';
        }
        else {
                echo "WRONG FIRST NAME";
		$checkValues['first'] = 'WRONG FIRST NAME';
        }

    	// LAST NAME REGEX
    	$regexLast = preg_match('/^[a-zA-Z]+$/', $stringLast);

        if (preg_match('/^[a-zA-Z]+$/', $stringLast)) {
                echo "VALID LAST NAME";
		$checkValues['last'] = 'VALID LAST NAME';
        }
        else {
                echo "WRONG LAST NAME";
		$checkValues['last'] = 'WRONG LAST NAME';
        }

    	//  	EMAIL REGEX
    	$strong_email = "/^[a-z0-9!#$%&'*+\\/=?^_`{|}~-]+(?:\\.[a-z0-9!#$%&'*+\\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/";
    	$regexEmail = preg_match($strong_email, $stringEmail);

        if (preg_match($strong_email, $stringEmail)) {
                echo "VALID EMAIL";
		$checkValues['email'] = 'VALID EMAIL';
        }
        else {
                echo "WRONG EMAIL";
		$checkValues['email'] = 'WRONG EMAIL';
        }


    	// SIMPLE ADDRESS REGEX
    	$valid_address_regex = "/^(\\d{1,}) [a-zA-Z0-9\\s]+(\\,)? [a-zA-Z]+(\\,)? [A-Z]{2} [0-9]{5,6}$/";
    	$regexAddress = preg_match($valid_address_regex, $stringAddress);

        if (preg_match($valid_address_regex, $stringAddress)) {
                echo "VALID ADDRESS";
		$checkValues['address'] = 'VALID ADDRESS';
        }
        else {
                echo "WRONG ADDRESS";
		$checkValues['address'] = 'WRONG ADDRESS';
        }

    	// PHONE REGEX
    	$valid_phone_regex = "/^\\+?\\d{1,4}?[-.\\s]?\\(?\\d{1,3}?\\)?[-.\\s]?\\d{1,4}[-.\\s]?\\d{1,4}[-.\\s]?\\d{1,9}$/";
    	$regexPhone = preg_match($valid_phone_regex, $stringPhone);

        if (preg_match($valid_phone_regex, $stringPhone)) {
                echo "VALID PHONE NUMBER";
		$checkValues['phone'] = 'VALID PHONE NUMBER';
        }
        else {
                echo "WRONG PHONE NUMBER";
		$checkValues['phone'] = 'WRONG PHONE NUMBER';
        }

	//	PRINTING CONDITION OF VALUES
	print_r($checkValues);


    	// CHECKING IF NEW USER DATA EXISTS
    	if ($regexUser == TRUE && $regexPass == TRUE && $regexFirst == TRUE && $regexLast == TRUE && $regexEmail == TRUE && $regexAddress == TRUE && $regexPhone == TRUE) {

        /* MYSQL CODE */

        // Connect to the database
        $servername = "192.168.194.3";
        $username_db = "test";
        $password_db = "test";
        $dbname = "FoodQuest";

        $conn = mysqli_connect($servername, $username_db, $password_db, $dbname);

        // Check if the connection is successful
        if (!$conn) {
            die("[X] CONNECTION TO MYSQL SERVER FAILED [X]" . mysqli_connect_error());
        }

        // Check if the user already exists in the database
        $sql_check = "SELECT * FROM Users WHERE username = '$stringUser' OR email = '$stringEmail'";
        $result = mysqli_query($conn, $sql_check);

        if (mysqli_num_rows($result) > 0) {

            	//	RESULT > 0: USER EXISTS
            	echo " [-] ENTERED USER ALREADY EXISTS IN FOODQUEST DATABASE.\n";
            	$newUser = 'TRUE';
		$validSignup = 'TRUE';
        } else {
            	//	USER DOES NOT EXIST AND MEETS REGEX REQUIREMENTS

            	//	INSERTING NEW USER INTO FOODQUEST DATABASE
            	$sql = "INSERT INTO Users (username, password, fname, lname, email, address, phonenumber) VALUES ('$validUser', '$validPass', '$validFirst', '$validLast', '$validEmail', '$validAddress', '$validPhone')";

   	//$sql = "INSERT INTO Users (username, password, fname, lname, email, address, phonenumber) VALUES ('$stringUser', '$stringPass', '$stringFirst', '$stringLast', '$stringEmail', '$stringAddress', '$stringPhone')";
            	if (mysqli_query($conn, $sql)) {
                	echo "[+] NEW USER WAS SUCCESSFULLY REGISTERED INTO FOODQUEST DATABASE [+]\n";
                	$newUser = 'FALSE';
			$validSignup = 'TRUE';
            	} else {
                	echo "FOODQUEST DATABASE ERROR: " . $sql . "<br>" . mysqli_error($conn);
            	}
        }

        // Close the database connection
        mysqli_close($conn);


        /* PROCESS TO SEND USER EXISTS MESSAGE TO FRONTEND - RABBITMQ */

	//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
	$existsConnection = null;
	$rabbitNodes = array('192.168.194.2', '192.168.194.1');

	foreach ($rabbitNodes as $node) {
		try {
            		$existsConnection = new AMQPStreamConnection(
								$node,
								5672,
								'foodquest',
								'rabbit123'
			);
			echo "SIGNUP CONNECTION TO RABBITMQ WAS SUCCESSFUL @ $node\n";
			break;
		} catch (Exception $e) {
			echo "ERROR: RABBITMQ CONNECTION WAS UNSUCCESSFUL @ $node\n";
			continue;
		}
	}

        // ESTABLISHING CONNECTION
        //$existsConnection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');

        if (!$existsConnection) {
                die("CONNECTION ERROR: COULD NOT CONNECT TO RABBITMQ NODE.");
        }

        $existsChannel = $existsConnection->channel();

        // EXCHANGE THAT WILL ROUTE DATABASE MESSAGES
        $existsChannel->exchange_declare('database_exchange', 'direct', false, false, false);

        // Routing key address so RabbitMQ knows where to send the message
        $returnToFrontend = "frontend";

        // ARRAY TO STORE MESSAGE
        $returnMsg = [
            'valid_signup' => $validSignup,
            'new_user' => $newUser,
        ];

        // GETTING MYSQL MESSAGE READY FOR DELIVERY TO FRONTEND
        $encodedMsg = json_encode($returnMsg);

        // Getting message ready for delivery
        $existsMessage = new AMQPMessage($encodedMsg, array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));

        // Publishing message to FRONTEND via queue
        $existsChannel->basic_publish($existsMessage, 'database_exchange', $returnToFrontend);

        // Command line message
        echo '[@] REGEX AND MYSQL PROTOCOLS WERE EXECUTED [@]', "\n--RETURN MESSAGE SENT TO FRONTEND--\n";

        print_r($returnMsg); // Displaying array in command line

        // CLOSING CHANNEL AND CONNECTION TALKING TO FRONTEND
        $existsChannel->close();
        $existsConnection->close();
    }

    // SENDING REGEX ERROR MESSAGE TO FRONTEND
    else  {

	$regexConnection = null;
	$rabbitmqNodes = array('192.168.194.2', '192.168.194.1');

	//	TODO: LOOP THROUGH ARRAY TO SEND DATA TO WORKING NODE

	foreach ($rabbitNodes as $node) {
                try {
                        $regexConnection = new AMQPStreamConnection(
                                                                $node,
                                                                5672,
                                                                'foodquest',
                                                                'rabbit123'
                        );
                        echo "SIGNUP CONNECTION TO RABBITMQ WAS SUCCESSFUL @ $node\n";
                        break;
                } catch (Exception $e) {
                        echo "ERROR: RABBITMQ CONNECTION WAS UNSUCCESSFUL @ $node\n";
                        continue;
                }
        }

        // Process to send message back to FRONTEND
        $regexConnection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');

        if (!$regexConnection) {
        	die("CONNECTION ERROR: COULD NOT CONNECT TO RABBITMQ NODE.");
        }

        $regexChannel = $regexConnection->channel();

        // EXCHANGE THAT WILL ROUTE DATABASE MESSAGES
        $regexChannel->exchange_declare('database_exchange', 'direct', false, false, false);

        // Routing key address so RabbitMQ knows where to send the message
        $regexFrontend = "frontend";

        $returnMsg = [
            'valid_signup' => 'FALSE',
            'new_user' => 'FALSE',
        ];

        $invalidEncodedRegex = json_encode($returnMsg);

        // Getting message ready for delivery
        $regexMessage = new AMQPMessage($invalidEncodedRegex, array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));

        // Publishing regex message to exchange via routing key
        $regexChannel->basic_publish($regexMessage, 'database_exchange', $regexFrontend);

        // Command line message
        echo '[@] REGEX CHECK PROTOCOL ACTIVATED [@]', "\n\n[x] SIGNUP INPUT DOES NOT MEET SITE REQUIREMENTS\n";

        print_r($returnMsg); // Displaying array in the command line

        // Closing channel and connection talking to FRONTEND
        $regexChannel->close();
        $regexConnection->close();
    }
};

while (true) {
	try {
		$channelDB->basic_qos(null, 1, false);
		$channelDB->basic_consume('database_mailbox', '', false, true, false, false, $callbackDB);

		while(count($channelDB->callbacks)) {
       			$channelDB->wait();
			echo 'NO MORE INCOMING MESSAGES FROM BACKEND', "\n\n";
			break;
		}
	}
	catch (ErrorException $e) {
        	// Handle Error
        	echo "ErrorException CAUGHT AT: " . $e->getMessage();
    	}
}

//	Closing MAIN channel and connection
$channelDB->close();
$connectionDB->close();

?>



