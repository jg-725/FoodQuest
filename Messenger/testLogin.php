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

	/*      Sending/Publishing Section       */

	// Connecting to Main RabbitMQ Node IP
	$senderConnection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
	$senderChannel = $senderConnection->channel();	//Establishing Channel Connection for communication

	// Declaring exchange for frontend to send/publish messages
	$senderChannel->exchange_declare('backend_exchange', 'direct', false, false, false);

	// Binding key: Relationship between exchange and queue
	$binding_key_backend = "backend";

	// Creating an array to store user login POST request
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
	$senderChannel->basic_publish($msg, 'backend_exchange', $binding_key_backend);

	// Message that shows login workflow was triggered
	echo ' [x] Frontend Task: Sent Login Data To Backend Exchange', "\n";
	print_r($send);
	echo "\n\n";

	// Terminating sending channel and connection
	$senderChannel->close();
	$senderConnection->close();


	/*	Receiving/Consuming From Database	*/

	// Connecting to Main RabbitMQ Node IP
        $receiverConnection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
        $receiverChannel = $receiverConnection->channel();  //Establishing Channel Connection for communication

	// Declaring queue that frontend will be listening for
	$receiverChannel ->queue_declare('database_queue', false, true, false, false);

	// Establishing callback variable for processing messages from database
	$receiverCallback = function ($msgContent) {

		// Decoding received msg from database into usuable code for processing
		$decoded_login = json_decode($msgContent->getBody(), true);

		/* 2 IF statements: Checking if user exists */

		// Commands to be executed if username/password does not match
		if ($userExists == false) {
			echo "Username or password does not exist in database";
			echo "TRY AGAIN";
			echo "<script>location.href='login.php';</script>";
		}

		// Commands to be executed if user exists
		if ($userExists == true) {
			die(header("location:home.php"));
		}
	}
	// Triggering the process to consume msgs from database
	$receiverChannel->basic_consume('database_queue', '', false, true, false, false, $receiverCallback);

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
/*
$connection = null;
if (!$connection) {
	die("ERROR OCCURED: Could not connect to RabbitMQ server.");
}
*/
