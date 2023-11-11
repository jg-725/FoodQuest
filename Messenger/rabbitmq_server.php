<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Creating the connection to rabbitmq
$connection = new AMQPStreamConnection('localhost', 5672, 'test', 'test');

$channel = $connection->channel();

$channel->exchange_declare('frontend_exchange', 'direct', false, false, false);

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

$callback = function($msg) {
    echo " [x] Received ", $msg->body, "\n";
    $job = json_decode($msg->body, $assocForm=true);
    $new_task = $job['type'];
    echo " [x] Done", "\n";
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

/*
class	RabbitMQ_Server
{
	private $connection;
    	private $channel;
    	private $callback_queue;
    	private $response;
    	private $corr_id;

}
*/
?>
