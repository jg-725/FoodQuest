<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

try {
    // Establishing connection to RabbitMQ
    $connection = new AMQPStreamConnection('172.26.177.167', 5672, 'test', 'test');
    $channel = $connection->channel();

    // Declaring the exchange
    $channel->exchange_declare('database_exchange', 'direct', false, false, false);

    $binding_key = "frontend";

    $username = "John";

    // Creating the data to be sent
    $send = [
        'type' => 'verified_user',
        'username' => $username,
        'condition' => 'user exists'
    ];

    // Encoding data to JSON
    $login_data = json_encode($send);

    // Creating a new AMQP message
    $msg = new AMQPMessage(
        $login_data,
        array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
    );

    // Publishing the message to the exchange
    $channel->basic_publish($msg, 'database_exchange', $binding_key);

    // Outputting a success message and the data sent
    echo ' [x] Database Task: User can log in', "\n";
    print_r($send);
    echo "\n\n";
} catch (Exception $e) {
    // Handle exceptions
    echo "Error: " . $e->getMessage(), "\n";
} finally {
    // Closing the channel and connection
    if ($channel) {
        $channel->close();
    }
    if ($connection) {
        $connection->close();
    }
}

?>



