<?php

/*	MILESTONE 4: RECEIVING HASHED PASSWORD INPUT FROM BACKEND WITH RABBITMQ CLUSTER */

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

//	-- SECTION TO RECEIVE MESSAGES FOR PROCESSING --

//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
$DBRegisterConnection = null;
$rabbitNodes = array('192.168.194.2', '192.168.194.1');
$port = 5672;
$user = 'foodquest';
$pass = 'rabbit123';

foreach ($rabbitNodes as $node) {
	try {
        	$DBRegisterConnection = new AMQPStreamConnection(
						$node,
						$port,
						$user,
						$pass
		);
		echo "DATABASE SIGNUP CONNECTION TO RABBITMQ CLUSTER WAS SUCCESSFUL @ $node\n\n";
		break;
	} catch (Exception $e) {
		echo "ERROR: RABBITMQ CONNECTION WAS UNSUCCESSFUL @ $node\n";
		continue;
	}
}

if (!$DBRegisterConnection) {
	die("RABBITMQ CONNECTION ERROR: DATABASE COULD NOT CONNECT TO ANY RABBITMQ NODE IN CLUSTER.");
}

//	TODO: ADD RabbitMQ CHANNEL connection settings

//      RABBITMQ MESSAGE BROKER SETTINGS
$consumeExchange 	= 'backend_exchange';	// Exchange Name
$exchangeType 		= 'direct';		// Exchange Type
$databaseQueue	 	= 'DB_signup_mailbox';	// Queue Name
$databaseBK   		= 'hash-database';	// BINDING KEY VALUE MATCHES REGISTER BACKEND ROUTING KEY


//	ACTIVING MAIN CHANNEL FOR BACKEND CONNECTION
$DBRegisterChannel = $DBRegisterConnection->channel();

//	DECLARING DURABLE EXCHANGE THAT WILL ROUTE MESSAGES FROM FRONTEND
$DBRegisterChannel->exchange_declare(
			$consumeExchange,
			$exchangeType,
			false,			// PASSIVE
			true,			// DURABLE
			false			// AUTO-DELETE
);

//	USING DURABLE QUEUE FOR WEBSITE: Third parameter is TRUE
$DBRegisterChannel->queue_declare(
		$databaseQueue,
		false,		// PASSIVE: check whether an exchange exists without modifying the server state
		true,		// DURABLE: the queue will survive a broker restart
		false,		// EXCLUSIVE: used by only one connection and the queue will be deleted when that connection closes
		false		// AUTO-DELETE: queue is deleted when last consumer unsubscribes
);

// Binding three items together to receive msgs
$DBRegisterChannel->queue_bind($databaseQueue, $consumeExchange, $databaseBK);

// Terminal message to signal we are waiting for messages from BACKEND
echo '[*] Waiting For BACKEND TO SEND SIGNUP DATA. To exit press CTRL+C', "\n\n";

$callbackDB = function ($hashMsg) use ($DBRegisterChannel) {
	//echo '[+] RECEIVED HASHED PASSWORD FROM BACKEND', "\n\n";

    	$backendMsg = json_decode($hashMsg->getBody(), true);

	print_r($backendMsg);

	echo '[+] RECEIVED HASHED PASSWORD FROM BACKEND', "\n\n";

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
		$checkValues['user'] = 'VALID USERNAME';
	}
	else {
		$checkValues['user'] = 'WRONG USERNAME';
	}

    	// PASSWORD REGEX
    	$strong_password = "/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/";
    	$regexPass = preg_match($strong_password, $stringPass);
        if (preg_match($strong_password, $stringPass)) {
		$checkValues['pass'] = 'VALID PASSWORD';
        }
        else {
		$checkValues['pass'] = 'WRONG PASSWORD';
        }

    	// FIRST NAME REGEX
    	$regexFirst = preg_match('/^[a-zA-Z]+$/', $stringFirst);

        if (preg_match('/^[a-zA-Z]+$/', $stringFirst)) {
		$checkValues['first'] = 'VALID FIRST NAME';
        }
        else {
		$checkValues['first'] = 'WRONG FIRST NAME';
        }

    	// LAST NAME REGEX
    	$regexLast = preg_match('/^[a-zA-Z]+$/', $stringLast);

        if (preg_match('/^[a-zA-Z]+$/', $stringLast)) {
		$checkValues['last'] = 'VALID LAST NAME';
        }
        else {
		$checkValues['last'] = 'WRONG LAST NAME';
        }

    	//  	EMAIL REGEX
    	$strong_email = "/^[a-z0-9!#$%&'*+\\/=?^_`{|}~-]+(?:\\.[a-z0-9!#$%&'*+\\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/";
    	$regexEmail = preg_match($strong_email, $stringEmail);

        if (preg_match($strong_email, $stringEmail)) {
		$checkValues['email'] = 'VALID EMAIL';
        }
        else {
		$checkValues['email'] = 'WRONG EMAIL';
        }


    	// SIMPLE ADDRESS REGEX
    	$valid_address_regex = "/^(\\d{1,}) [a-zA-Z0-9\\s]+(\\,)? [a-zA-Z]+(\\,)? [A-Z]{2} [0-9]{5,6}$/";
    	$regexAddress = preg_match($valid_address_regex, $stringAddress);

        if (preg_match($valid_address_regex, $stringAddress)) {

		$checkValues['address'] = 'VALID ADDRESS';
        }
        else {
		$checkValues['address'] = 'WRONG ADDRESS';
        }

    	// PHONE REGEX
    	$valid_phone_regex = "/^\\+?\\d{1,4}?[-.\\s]?\\(?\\d{1,3}?\\)?[-.\\s]?\\d{1,4}[-.\\s]?\\d{1,4}[-.\\s]?\\d{1,9}$/";
    	$regexPhone = preg_match($valid_phone_regex, $stringPhone);

        if (preg_match($valid_phone_regex, $stringPhone)) {

		$checkValues['phone'] = 'VALID PHONE NUMBER';
        }
        else {
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
	$newUserConnection = null;
		$rabbitNodes = array('192.168.194.2', '192.168.194.1');
		$port = 5672;
                $user = 'foodquest';
                $pass = 'rabbit123';

	foreach ($rabbitNodes as $node) {
		try {
            		$newUserConnection = new AMQPStreamConnection(
								$node,
								$port,
								$user,
								$pass
			);
			echo "DATABASE REGISTRATION CONNECTION TO RABBITMQ CLUSTER WAS SUCCESSFUL @ $node\n";
			break;
		} catch (Exception $e) {
			echo "ERROR: RABBITMQ CONNECTION WAS UNSUCCESSFUL @ $node\n";
			continue;
		}
	}

        // ESTABLISHING CONNECTION
        if (!$newUserConnection) {
                die("CONNECTION ERROR: COULD NOT CONNECT TO RABBITMQ NODE.");
        }

	//      RABBITMQ MESSAGE BROKER SETTINGS TO SEND MESSAGES
	$publishExchange = 'database_exchange'; // Exchange Name
	$exchangeType 	 = 'direct'; 		// Exchange Type
	$newUserRK 	 = 'newUser-Frontend';  // ROUTING KEY TO DETERMINE DESTINATION

        $newUserChannel = $newUserConnection->channel();

        // EXCHANGE THAT WILL ROUTE MESSAGES TO FRONTEND
        $newUserChannel->exchange_declare(
			$publishExchange,
			$exchangeType,
			false,		// PASSIVE
			true,		// DURABLE
			false		// AUTO-DELETE
	);

        // ARRAY TO STORE MESSAGE
        $returnMsg = [
            'valid_signup' => $validSignup,
            'new_user' => $newUser,
        ];

        // GETTING MYSQL MESSAGE READY FOR DELIVERY TO FRONTEND
        $encodedMsg = json_encode($returnMsg);

        // Getting message ready for delivery
        $regexMessage = new AMQPMessage(
				$encodedMsg,
				array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));

        // Publishing message to DATABASE EXCHANGE FOR ROUTING
        $newUserChannel->basic_publish(
				$existsMessage,
				$publishExchange,
				$newUserRK
	);

        // Command line message
        echo '[@] REGEX AND MYSQL PROTOCOLS WERE EXECUTED [@]', "\n--RETURN MESSAGE SENT TO FRONTEND--\n";

        print_r($returnMsg); // Displaying array in command line

        // CLOSING CHANNEL AND CONNECTION TALKING TO FRONTEND
        $newUserChannel->close();
        $newUserConnection->close();
    }

    // SENDING REGEX ERROR MESSAGE TO FRONTEND
    else  {

	$regexConnection = null;
	$rabbitmqNodes = array('192.168.194.2', '192.168.194.1');
	$port = 5672;
        $user = 'foodquest';
        $pass = 'rabbit123';

	//	TODO: LOOP THROUGH ARRAY TO SEND DATA TO WORKING NODE

	foreach ($rabbitNodes as $node) {
                try {
                        $regexConnection = new AMQPStreamConnection(
                                                                $node,
                                                                $port,
                                                                $user,
                                                                $pass
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

	//      RABBITMQ MESSAGE BROKER SETTINGS TO SEND MESSAGES
	$publishExchange 	= 'frontend_exchange';	// Exchange Name
        $exchangeType		= 'direct';		// Exchange Type
        $regexRK 		= 'regex-database';	// ROUTING KEY TO DETERMINE DESTINATION

	//	OPENING CHANNEL TO COMMUNITCATE WITH FRONTEND
        $regexChannel = $regexConnection->channel();

        // EXCHANGE THAT WILL ROUTE MESSAGES TO FRONTEND
        $regexChannel->exchange_declare(
			$publishExchange,
			$exchangeType,
			false,		// PASSIVE
			true,		// DURABLE
			false		// AUTO-DELETE
		);

        $returnMsg = [
            'valid_signup' => 'FALSE',
            'new_user' => 'FALSE',
        ];

        $invalidEncodedRegex = json_encode($returnMsg);

        // Getting message ready for delivery
        $regexMessage = new AMQPMessage(
				$invalidEncodedRegex,
				array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));

        // Publishing regex message to exchange via routing key
        $regexChannel->basic_publish(
				$regexMessage,
				$publishExchange,
				$regexRK);

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
		$DBRegisterChannel->basic_qos(null, 1, false);
		$DBRegisterChannel->basic_consume($databaseQueue, '', false, true, false, false, $callbackDB);

		while(count($DBRegisterChannel->callbacks)) {
       			$DBRegisterChannel->wait();
			echo 'NO MORE INCOMING REGISTER REQUESTS FROM BACKEND', "\n\n";
			break;
		}
	}
	catch (ErrorException $e) {
        	// Handle Error
        	echo "ErrorException CAUGHT AT: " . $e->getMessage();
    	}
}

//	Closing MAIN channel and connection
$DBRegisterChannel->close();
$DBRegisterConnection->close();

?>



