<?php

require_once __DIR__ .'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


$connection = new AMQPStreamConnection('172.26.177.167', 5672, 'test', 'test');
$channel = $connection->channel();

$channel->exchange_declare('database_exchange', 'direct', false, false, false);

$binding_key = "frontend";

$username = "John";

$send = array();

if (empty($send)) {

        $send['type'] = "verified_user";
        $send['username'] = $username;
	$send['condition'] = "user exists";
}

$login_data = json_encode($send);

$msg = new AMQPMessage(
        $login_data,
        array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
);

$channel->basic_publish($msg, 'database_exchange', $binding_key);

echo ' [x] Database Task: User can log in', "\n";
print_r($send);
echo "\n\n";

$channel->close();
$connection->close();
?>
