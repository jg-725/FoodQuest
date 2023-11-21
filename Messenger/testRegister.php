<?php

//	TEST FILE FOR REGISTRATION PAGE

//	AMQP Libraries
require_once __DIR__ .'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/*              SECTION TO SEND LOGIN TO BACKEND        */

// Establishing a connection to MAIN RabbitMQ server
$connectionRegister = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
$channelRegister = $connectionRegister->channel();  //Channel connection to send message

// Declaring an EXCHANGE that ROUTES messages from FRONTEND TO BACKEND
$channelRegister->exchange_declare('backend_exchange', 'direct', false, false, false);

// Binding key to bind exchange with queue
$registerKey = 'backend';

// Login Data
$username = "John101";
$password = "Password123";
$first = 'John';
$last = 'Lennon';
$email = 'theBeatles@email.com';

// Creating array to store message type and login data
$send = array();

if (empty($send)) {

	$send['username'] 	= $username;
	$send['password'] 	= $password;
	$send['firstName'] 	= $first;
	$send['lastName']	= $last;
	$send['email']		= $email;
}

// Turining data into a string type
$login_data = json_encode($send);

// Creating AMQPMessage For Delivery
$msg = new AMQPMessage(
	$login_data,
	array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
	//array('delivery_mode' => 2)
);

// Publishing data to RabbitMQ exchange for processing
$channelRegister->basic_publish($msg, 'frontend_exchange', $routing_key);

echo ' [x] Frontend Task: SENT REGISTER DATA TO BACKEND FOR REGEX', "\n";
print_r($send);
echo "\n\n";

$channelRegister->close();
$connectionRegister->close();


//      --- THIS PART WILL LISTEN FOR MESSAGES FROM BACKEND ---

// Connecting to RabbitMQ
$connectionReceiveBackend = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');

$channelReceiveBackend = $connectionReceiveBackend->channel();

//	Making NON durable queue for testing
$channelReceiveBackend->queue_declare('frontend_mailbox', false, false, false, false);



?>
/*
$callback = function ($msg) {

	echo " [x] RabbitMQ Received Message From Frontend\n";
	echo ' [x] ', 'Msg -> ', $msg->getBody(), "\n";
	echo ' [x] ', 'Redirecting Message using Routing Key: ', $msg->getRoutingKey(), "\n\n";
	//$next_job = json_decode($msg->body, $assocForm=true);
	//$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};
*/

