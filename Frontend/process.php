<?php
require_once __DIR__ . '/vendor/autoload.php'; // Include the AMQP library
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// RabbitMQ server connection parameters
$host = '127.0.0.1'; // RabbitMQ server host
$port = 5672;                 // RabbitMQ server port
$user = $_POST['test'];    // RabbitMQ username from the form
$pass = $_POST['test'];    // RabbitMQ password from the form
$vhost = 'testHost';  // RabbitMQ virtual host
$exchangeName = 'testExchange'; // RabbitMQ exchange name

try {
    // Connect to RabbitMQ
    $connection = new AMQPStreamConnection($host, $port, $user, $pass, $vhost);
    $channel = $connection->channel();

    // Declare the exchange
    $channel->exchange_declare($exchangeName, 'direct');

    // Send a message (for demonstration purposes)
    $messageBody = "Authentication successful for user: $user";
    $message = new AMQPMessage($messageBody);
    $channel->basic_publish($message, $exchangeName);

    // Close the connection
    $channel->close();
    $connection->close();

    echo "Authentication successful. Message sent to RabbitMQ.";
} catch (\Exception $e) {
    echo "Authentication failed: " . $e->getMessage();
}
<br />define( 'WP_DEBUG', true );<br />
define( 'WP_DEBUG_LOG', true );<br />
define( 'WP_DEBUG_DISPLAY', false );<br />Logs are usually found in the /wp-content directory.<br />
?>
