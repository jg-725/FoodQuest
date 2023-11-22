<?php

require_once __DIR__ . '/vendor/autoload.php'; // Include RabbitMQ library
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Create a connection to RabbitMQ
//$connection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
$channel = $connection->channel();

// Declare the queue to send login requests
//$queueName = 'login_requests';

$channel->exchange_declare('frontend_exchange', 'direct', false, false, false);

$routing_key = 'backend';

//$channel->queue_declare($queueName, false, false, false, false,);

// Get username and password from the form
$username = 'John';
$password = 1234;

$loginArray = array();

if (empty($loginArray)) {
	$loginArray['username'] = $username;
	$loginArray['password'] = $password;
}

// ENCODING LOGIN INTO JSON
$encodedLogin = json_encode($loginArray);

//	TURNING MESSAGE RABBTMQ READY
$msg = new AMQPMessage($encodedLogin);

$channel->basic_publish($msg, 'frontend_exchange', $routing_key);

echo ' [*] SENT RANDOM LOGIN TO BACKEND FOR PROCESSING', "\n";
print_r($loginArray);
echo "\n\n";

$channel->close();
$connection->close();

/*		RECEIVING REGEX MESSAGE FROM BACKEND		*/

$connectionRegex = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
$channelRegex = $connectionRegex->channel();

$channelRegex->queue_declare('regexQueue', false, false, false, false);

$callbackRegex = function ($regexMsg) {
	$data = json_decode($regexMsg->getBody(),true);
	echo "[+] RECEIVED REGEX RESPONSE FROM BACKEND\n";
	print_r($data);
};

$channelRegex->basic_consume('regexQueue', '', false, true, false, false, $callbackRegex);

while ($channelRegex->is_open()) {
	$channelRegex->wait();
	break;
}

$channelRegex->close();
$connectionRegex->close();

?>
