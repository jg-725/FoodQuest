<?php

/*      Receiving Section       */

// Corresponding libraries
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

// Creating connection to MAIN RabbitMQ node
$connection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
$channel = $connection->channel();	// Opening a channel for communication

// Queue to receive messages: backend mailbox
$channel->queue_declare("backend_queue", false, false, true, false);

// Terminal message to signal we are waiting for messages from frontend
echo ' [*] Waiting for Frontend messages. To exit press CTRL+C', "\n\n";

// Callback function that is called when consuming incoming messages
$callback = function ($msg) use ($channel) {

	echo " [x] Message Received\n";	// Terminal message

	// Decoding JSON code into usuable format
	$login = json_decode($msg->body, $assocForm=true);

	// Grabbing the message variables for use
	$username = $login['username'];
	$password = $login['password'];

    	echo " [x] ", $username, " - ", $password,  "\n";


	// - CHECKPOINT FOR USERNAME & PASSWORD BEFORE SENDING THEM TO DATABASE -

	// USERNAME REGEX CHECK
	if () {
		$validUser = true;
		echo "[+] Username meets criteria";
	}
	else {
		$validUser = false;
		echo "[-] Username does not meet criteria";
	}

	// PASSWORD REGEX CHECK
	if () {
		$validPassword = true;
                echo "[+] Password meets criteria";
	}
	else {
		 $validPassword = false;
                echo "[+] Password does not meet criteria";
	}

	// Returning JSON message to frontend that login criteria was not met

	// Array to hold values

	// Line 79 in logStep2-ms4.php
	returnMessage = ;


};

$channel->basic_consume($backend_queue, '', false, true, false, false, $callback);


/*      Sending Section       */


// Corresponding libraries
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

// Creating the connection to rabbitmq
$connection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');

$channel = $connection->channel();

$channel->exchange_declare('backend_exchange', 'direct', false, false, false);

$channel->queue_declare("backend_queue", false, false, true, false);

$binding_key_backend = 'backend';

$channel->queue_bind('backend_queue', 'queue_exchange', $binding_key_backend);


?>
