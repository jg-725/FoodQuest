<?php
require_once __DIR__ . '/vendor/autoload.php'; // Include RabbitMQ library
use PhpAmqpLib\Connection\AMPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
// Create a connection to RabbitMQ
$connection = new AMQPStreamConnection('172.26.144.241', 5672, 'test', 'test');
$channel = $connection->channel();

// Define RabbitMQ server connection parameters
//$host = '172.26.144.241'; // RabbitMQ server host
//$port = 5672;        // RabbitMQ server port
//$user = 'test'; // RabbitMQ username
//$pass = 'test'; // RabbitMQ password
//$vhost = 'testHost';        // Virtual host (default is '/')

// Connect to RabbitMQ
$connection = new AMQPStreamConnection($host, $port, $user, $pass );
$channel = $connection->channel();

// Declare the queue to send login requests
$queueName = 'login_requests';
$channel->queue_declare($queueName, false, false, false, false,);

// Get username and password from the form
$username = $_POST["username"];
$password = $_POST["password"];

// Send login request to the queue
$message = json_encode(["username" => $username, "password" => $password]);
$msg = new AMQPMessage($message);
$channel->basic_publish($msg, '', $queueName);

<br />define( 'WP_DEBUG', true );<br />
define( 'WP_DEBUG_LOG', true );<br />
define( 'WP_DEBUG_DISPLAY', false );<br />Logs are usually found in the /wp-content directory.<br />

$channel->close();
$connection->close();

header("Location: response.php");
