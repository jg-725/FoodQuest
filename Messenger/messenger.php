<?php

require_once __DIR__ .'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('192.168.194.2', 5672, 'galijeff', 'Rabbit123');
$channel = $connection->channel();

/*
class RabbitMQServer {
  public static function welcome() {
    echo "Hello World!";
  }
  public function __construct() {
    self::welcome();
  }
}
*/

// Declaring an EXCHANGE to receive messages from FRONTEND
$channel->exchange_declare('frontend_exchange', 'direct', false, false, false);

// Declaring an EXCHANGE to receive messages from BACKEND
$channel->exchange_declare('backend_exchange', 'direct', false, false, false);

// Declaring an EXCHANGE to receive messages from DATABASE
$channel->exchange_declare('database_exchange', 'direct', false, false, false);


// Declaring a DURABLE QUEUE to send messages to BACKEND
$channel->queue_declare('backend_queue', false, true, false, false);

// Declaring a DURABLE QUEUE to send messages to DATABASE
$channel->queue_declare('database_queue', false, true, false, false);

// Declaring a DURABLE QUEUE to send messages to FRONTEND
$channel->queue_declare('frontend_queue', false, true, false, false);

// --- Creating the binding keys to the link servers together  ---//

// Binding key to connect to backend
$binding_key_backend = 'backend';

// Binding key to connect to database
$binding_key_database = 'database';

// Binding key to connect to frontend
$binding_key_frontend = 'frontend';


// Binding the exchange and queue together using the binding key
$channel->queue_bind('backend_queue', 'frontend_exchange', $binding_key_backend);

$channel->queue_bind('database_queue', 'backend_exchange', $binding_key_database);

$channel->queue_bind('frontend_queue', 'database_exchange', $binding_key_frontend);


echo " [*] Messenger Server INITIATED\n";
echo " [*] Waiting for Senders to send a message to RabbitMQ. To exit press CTRL+C\n\n";

$callback1 = function ($msg) {

	echo " [x] RabbitMQ Received Message From Frontend\n";
	echo ' [x] ', 'Msg -> ', $msg->getBody(), "\n";
	echo ' [x] ', 'Redirecting Message using Routing Key: ', $msg->getRoutingKey(), "\n\n";
	//$next_job = json_decode($msg->body, $assocForm=true);
	//$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_qos(null, 1, false);  //changed third var: false

$channel->basic_consume('backend_queue', '', false, true, false, false, $callback1);

$channel->basic_consume('database_queue', '', false, true, false, false, $callback1);

//$channel->basic_consume('frontend_queue', '', false, true, false, false, $callback3);


// Sending the messages to the corresponding server
//$channel->basic_publish($msg, '');


try {
	$channel->consume();
} catch (\Throwable $exception) {
	echo $exception->getMessage();
}

$channel->close();
$connection->close();
?>
