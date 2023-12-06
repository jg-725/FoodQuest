<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

try {
	// Creating the connection to RabbitMQ
	$connection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
    
	$channel = $connection->channel();

	// Additional code for your RabbitMQ operations can be added here.

	// Don't forget to close the channel and connection when you're done.
	$channel->close();
	$connection->close();
} catch (Exception $e) {
	// Handle connection errors
	echo "Error: " . $e->getMessage() . PHP_EOL;
}

?>
