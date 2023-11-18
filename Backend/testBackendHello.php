<?php

require_once __DIR__ .'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


$connection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
$channel = $connection->channel();

$channel->queue_declare('hello', false, false, false, false);


// Terminal message to signal we are waiting for messages from frontend
echo ' [*] Waiting for Frontend messages. To exit press CTRL+C', "\n\n";


$callback1 = function ($msg) {

	echo " [x] RabbitMQ Received Message From Frontend\n";
	echo ' [x] ', 'Msg -> ', $msg->getBody(), "\n";
	//echo ' [x] ', 'Redirecting Message using Routing Key: ', $msg->getRoutingKey(), "\n\n";
	//$next_job = json_decode($msg->body, $assocForm=true);
	//$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_consume('hello', '', false, true, false, false, $callback1);


?>
