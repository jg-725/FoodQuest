<?php

// Test file for the registation page
// PHP code for sending user data to backend and receiving confirmation from database

// Calling all the neccesary AMQP Libraries

require_once __DIR__ .'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

// Establishing a connection to MAIN RabbitMQ server
$connection = new AMQPStreamConnection('192.168.194.2', 5672, 'galijeff', 'Rabbit123');
$channel = $connection->channel();  //Channel connection to send message


// Declaring an EXCHANGE that ROUTES messages from FRONTEND TO BACKEND
$channel->exchange_declare('backend_exchange', 'direct', false, false, false);

// Binding key to bind exchange with queue
$binding_key_backend = 'backend';

$callback = function ($msg) {

	echo " [x] RabbitMQ Received Message From Frontend\n";
	echo ' [x] ', 'Msg -> ', $msg->getBody(), "\n";
	echo ' [x] ', 'Redirecting Message using Routing Key: ', $msg->getRoutingKey(), "\n\n";
	//$next_job = json_decode($msg->body, $assocForm=true);
	//$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

?>
