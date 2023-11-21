<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Creating the connection to rabbitmq
$connection = new AMQPStreamConnection('', 5672, 'foodquest', 'rabbit123');

$channel = $connection->channel();




?>
