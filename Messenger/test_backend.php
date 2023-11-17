<?php

/*      Receiving Section       */

// Corresponding libraries
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

// Creating the connection to rabbitmq
$connection = new AMQPStreamConnection('localhost', 5672, 'test', 'test');

$channel = $connection->channel();

$channel->exchange_declare('backend_exchange', 'direct', false, false, false);

$channel->queue_declare("backend_queue", false, false, true, false);

$binding_key_backend = 'backend';

$channel->queue_bind('backend_queue', 'queue_exchange', $binding_key_backend);

// Message to signal we are waiting for messages from frontend
echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

// Callback function that is called when consuming incoming messages
$callback = function($msg) {
    echo " [x] Received ", $msg->body, "\n";
    $job = json_decode($msg->body, $assocForm=true);
    $new_task = $job['type'];
    echo " [x] Done", "\n";
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_consume($backend_queue, '', false, true, false, false, $callback);


/*      Sending Section       */




?>
