<?php

require_once __DIR__ .'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Connecting to RabbitMQ
$connection = new AMQPStreamConnection('172.26.177.167', 5672, 'test', 'test');
$channel = $connection->channel();

// Declaring the exchange to send the message
$channel->exchange_declare('frontend_exchange', 'direct', false, false, false);
// Routing key address so RabbitMQ knows where to send the message
$routing_key = "backend";

// Function for Receiving the message sent from database

// Login Data
$username = "John";
$password = "1234";

// Creating array to store message type and login data
$send = array();
if (empty($send)) {

	$send['type'] = "Login";
	$send['username'] = $username;
	$send['password'] = $password;
}

// Turining data into a string type
//$login_data = var_dump(implode(",", $send));
$login_data = json_encode($send);

// Creating AMQPMessage For Delivery
$msg = new AMQPMessage(
	$login_data,
	array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
	//array('delivery_mode' => 2)
);

// Publishing data to RabbitMQ exchange for processing
$channel->basic_publish($msg, 'frontend_exchange', $routing_key);

echo ' [x] Frontend Task: Sent Login to Messenger', "\n";
print_r($send);
echo "\n\n";

$channel->close();
$connection->close();
?>
