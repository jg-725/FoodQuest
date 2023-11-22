<?php

/*      TESTING: RECEIVING VALID REGEX REGISTER INPUT FROM BACKEND       */

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

//	SECTION TO RECEIVE MESSAGES FOR PROCESSING

//	CONNECTING TO MAIN RABBITMQ
$connectionDB = new AMQPStreamConnection('192.168.194.2',
					5672,
					'foodquest',
					'rabbit123');
$channelDB = $connectionDB->channel();

$channelDB->exchange_declare('backend_exchange', 'direct', false, false, false);

//	Using NON DURABLE QUEUES: Third parameter is false
$channelDB->queue_declare('database_mailbox', false, false, false, false);

// Binding key
$bindingKeyDB = "database";

// Binding three items together to receive msgs
$channelDB->queue_bind('database_mailbox', 'backend_exchange', $bindingKeyDB);

// Terminal message to signal we are waiting for messages from BACKEND
echo '[*] Waiting for BACKEND messages. To exit press CTRL+C', "\n\n";


$callbackDB = function ($msg) use ($channelDB) {
	echo '[+] RECEIVED VALID REGEX REGISTER INPUT FROM BACKEND', "\n", $msg->getBody(), "\n\n";

	$validSignupRegex = json_decode($msg->getBody(), true);

	//	GETTING VARIABLES SENT FROM BACKEND
	$validUser  = $validSignupRegex['username'];
	$validPass  = $validSignupRegex['password'];
	$validFirst = $validSignupRegex['first'];
	$validLast  = $validSignupRegex['last'];
	$validEmail = $validSignupRegex['email'];
	$validAddress = $validSignupRegex['address'];
	$validPhone = $validSignupRegex['phone'];

	//      JSON to String sanitize
        $stringUser = filter_var($validUser, FILTER_SANITIZE_STRING);
        $stringPass = filter_var($validPass, FILTER_SANITIZE_STRING);
        $stringFirst = filter_var($validFirst, FILTER_SANITIZE_STRING);
        $stringLast = filter_var($validLast, FILTER_SANITIZE_STRING);
        $stringEmail = filter_var($validEmail, FILTER_SANITIZE_STRING);
	$stringAddress = filter_var($validAddress, FILTER_SANITIZE_STRING);
	$stringPhone = filter_var($validPhone, FILTER_SANITIZE_STRING);

	/*	MYSQL CODE	*/


// Connect to the database

$servername = "192.168.194.3";
//$servername = "localhost";
$username_db = "test";
$password_db = "test";
$dbname = "FoodQuest";

$conn = mysqli_connect($servername, $username_db, $password_db, $dbname);

// Check if the connection is successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the user already exists in the database
$sql_check = "SELECT * FROM Users WHERE Username = '$stringUser' OR email = '$stringEmail'";
$result = mysqli_query($conn, $sql_check);

	if (mysqli_num_rows($result) > 0) {
    		// User already exists
    		echo "User already exists in the database.\n";
    		$newUser = FALSE;
	} else {
    		// User does not exist
    		// Insert the user data into the database
    		$sql = "INSERT INTO Users (username, password, fname, lname, email, address, phonenumber) VALUES ('$stringUser', '$stringPass', '$stringFirst', '$stringLast', '$stringEmail', '$stringAddress', '$stringPhone')";

    		if (mysqli_query($conn, $sql)) {
        		echo "New record created successfully\n";
        		$newUser = TRUE;
    		} else {
        		echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    		}
	}
	// Close the database connection
	mysqli_close($conn);


	/*	GETTING FRONTEND MESSAGE READY - RABBITMQ	*/

	//	ARRAY TO STORE MESSAGE
	$returnMsg = array();

	if ($newUser == TRUE) {
		if (empty($returnMsg)) {        // Check if array is empty
                	$returnMsg['newUser'] = $newUser;
        	}
	}
	else {
		if (empty($returnMsg)) {        // Check if array is empty
                	$returnMsg['newUser'] = $newUser;
                	//$returnMsg['newUser'] = $userCondition;
        	}
	}

	//	GETTING MYSQL MESSAGE READY FOR DELIVERY TO FRONTEND
	$encodedMsg = json_encode($returnMsg);

	//	Process to send message back to FRONTEND
	$existsConnection = new AMQPStreamConnection('192.168.194.2',
						5672,
						'foodquest',
						'rabbit123'
	);
	$existsChannel = $existsConnection->channel();

	//	EXCHANGE THAT WILL ROUTE DATABASE MESSAGES
	$existsChannel->exchange_declare('database_exchange',
					'direct',
					false,
					false,
					false
	);

	//	Routing key address so RabbitMQ knows where to send the message
	$returnToFrontend = "frontend";

	//	Getting message ready for delivery
	$existsMessage = new AMQPMessage($encodedMsg,
			array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
	);

	// 	Publishing message to frontend via queue
        $existsChannel->basic_publish($existsMessage,
				'database_exchange',
				$returnToFrontend
	);

	//	COMMAND LINE MESSAGE
	echo '[@] MYSQL CHECK PROTOCOL ACTIVATED [@]', "\nRETURN MESSAGE TO FRONTEND\n";

	print_r($returnMsg);	//Displaying array in command line

	//	CLOSING CHANNEL AND CONNECTION TALKING TO FRONTEND
	$existsChannel->close();
        $existsConnection->close();
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
	catch (ErrorException $e) {
        	// Handle Error
        	echo "ErrorException CAUGHT AT: " . $e->getMessage();
    	}
}

//	Closing MAIN channel and connection
$channelDB->close();
$connectionDB->close();

?>