<?php

// Initializing login session
session_start();

// Required PHP and AMQP Libraries to interact with RabbitMQ
require_once __DIR__ .'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Server request POST initialized to trigger login request flow - IF statement
if ($_SERVER['REQUEST_METHOD' === 'POST']) {

	$username = $_POST['username'];
	$password = $_POST['password'];

	/*      SENDING LOGIN TO BACKEND FOR PROCESSING       */

	//	Connecting to Main RabbitMQ Node IP
	$senderConnection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');

	$senderChannel = $senderConnection->channel();	//Establishing Channel Connection for communication

	// 	Declaring exchange for frontend to send/publish messages
	$senderChannel->exchange_declare('frontend_exchange', 'direct', false, false, false);

	//	ROUTING KEY: Relationship between exchange and queue
	$backendKey = "backend";

	// 	Creating an array to store user login POST request
	$send = array();
	if (empty($send)) {	// Check if array is empty
        	//$send['type'] = ;
        	$send['username'] = $username;
        	$send['password'] = $password;
	}

	// Turning array into JSON for compatability
	//$login_data = implode($send);
	$login_data = json_encode($send);

	// Creating AMQPMessage protocol once login data is ready for delivery
	$msg = new AMQPMessage(
        	$login_data,
        	array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
	);

	// Publishing message to backend exchange using binding key indicating the receiver
	$senderChannel->basic_publish($msg, 'frontend_exchange', $backendKey);

	// Message that shows login workflow was triggered
	echo ' [x] Frontend Task: Sent Login Data To Backend via frontend exchange', "\n";
	print_r($send);
	echo "\n\n";

	// Terminating sending channel and connection
	$senderChannel->close();
	$senderConnection->close();


	/*	RECEIVING REGEX ERROR MESSAGE FROM BACKEND	*/

	// Connecting to Main RabbitMQ Node IP
	$regexConnection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');

	$regexChannel = $regexConnection->channel();	//Establishing Channel Connection for communication

	//	EXCHANGE THAT WILL ROUTE MESSAGES COMING FROM BACKEND
	$regexChannel->exchange_declare('backend_exchange', 'direct', false, false, false);

	//	QUEUE THAT WILL BE LISTENING FOR THE EXCHANGE
	$regexChannel ->queue_declare('frontend_mailbox', false, false, false, false);

	// Binding Key
	$regexKey = 'frontend';

	// Binding corresponding queue and exchange
	$regexChannel->queue_bind('frontend_mailbox', 'backend_exchange', $regexKey);

	//	CALLBACK FOR processing messages from BACKEND
	$regexCallback = function ($regexMsg) {

		// Decoding received msg from database into usuable code for processing
		$decoded_login = json_decode($regexMsg->getBody(), true);

		//	GETTING DECODED VALUES
		$invalidUser = $decoded_login['invalidUsername'];
		$invalidPass = $decoded_login['invalidPassword'];

		// Commands to be executed if username/password does not match
		if ($invalidUser == FALSE || $invalidPass == FALSE) {
			echo "Username or password does not exist in database";
			echo "TRY AGAIN";
			echo "<script>location.href='login.php';</script>";
		}
	};

	// Triggering the process to consume msgs from database
	$regexChannel->basic_consume('frontend_mailbox', '', false, true, false, false, $regexCallback);

	// while loop to keep checking for incoming messages from database
	while ($regexChannel->is_open()) {
		$regexChannel->wait();
		break;
	}

	// Terminating channel and connection for receivin msgs
	$regexChannel->close();
	$regexConnection->close();


	/*      RECEIVING USER EXISTS MESSAGE FROM DATABASE      */

	// Connecting to Main RabbitMQ Node IP
        $receiverConnection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');

        $receiverChannel = $receiverConnection->channel();  //Establishing Channel Connection for communication

	// Exchange to listen for
	$receiverChannel->exchange_declare('database_exchange', 'direct', false, false, false);

	// Declaring queue that frontend will be listening for
	$receiverChannel ->queue_declare('frontend_mailbox', false, true, false, false);

	// Binding Key
	$logKey = 'frontend';

	// Binding corresponding queue and exchange
	$receiverChannel->queue_bind('frontend_mailbox', 'database_exchange', $logKey);

	// Establishing callback variable for processing messages from database
	$receiverCallback = function ($msgContent) {

		// Decoding received msg from database into usuable code for processing
		$decoded_login = json_decode($msgContent->getBody(), true);

		/* 2 IF statements: Checking if user exists */

		// Commands to be executed if username/password does not match
		if ($userExists == false) {
			//echo "Username or password does not exist in database";
			echo "<script>alert('Username or password does not exist in database');</script>";
			echo "<script>location.href='login.php';</script>";
		}

		// Commands to be executed if user exists
		if ($userExists == true) {
			die(header("location:home.php"));
		}
	};

	// Triggering the process to consume msgs from database
	$receiverChannel->basic_consume('frontend_mailbox', '', false, true, false, false, $receiverCallback);

	// while loop to keep checking for incoming messages from database
	while ($receiverChannel->is_open()) {
		$receiverChannel->wait();
		break;
	}

	// Terminating channel and connection for receivin msgs
	$receiverChannel->close();
	$receiverConnection->close();
}
?>
