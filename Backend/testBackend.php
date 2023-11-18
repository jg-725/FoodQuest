<?php

/*	TESTING RECEIVING MESSAGES FROM FRONTEND	*/

require_once __DIR__ .'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
//use PhpAmqpLib\Message\AMQPMessage;

// RECEIVING MESSAGES FROM FRONTEND

$connection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
$channel = $connection->channel();

$channel->exchange('frontend_exchange', 'direct', false, false, false);

//	Using NON DURABLE QUEUES: Third parameter is false
$channel->queue_declare('backend_mailbox', false, false, false, false);

// Binding key
$binding_key = "backend";

// Binding three items together to receive msgs
$channel->queue_bind('backend_mailbox', 'frontend_exchange', $binding_key);

// Terminal message to signal we are waiting for messages from frontend
echo ' [*] Waiting for Frontend messages. To exit press CTRL+C', "\n\n";

// Callback that processes the login msg upon consuming from queue
$callback = function ($msg) {
	$data = json_decode($msg->getBody(), true);

	echo '[x] Received Login from Frontend\n\n', $data, "\n";

	$user = $data['username'];
	$pass = $data['password'];


	// TODO: IMPLEMENT REGEX FOR ALL USER INPUT

	if (is_string($user) == 1) {
		$data['validUser'] = true;
	}
	else {
		$data['validUser'] = false;
	}

	$data['validPassword'] = 'weak password';

	// RETURN ARRAY
	echo '[X] RETURNING ARRAY\n', $data, '\n';
	//$msg->ack();
};

$channel->basic_qos(null, 1, false);
$channel->basic_consume('backend_mailbox', '', false, true, false, false, $callback);

/*

try {
    $channel->consume();
} catch (\Throwable $exception) {
    echo $exception->getMessage();
}
*/

while(count($channel->callbacks)) {
	echo 'Waiting for incoming messages\n';
       	$channel->wait();
}

// Closing channel and connection
$channel->close();
$connection->close();


//	SENDING MESSAGES TO DATABASE

?>
