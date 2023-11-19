<?php

/*	TESTING RECEIVING MESSAGES FROM FRONTEND	*/

require_once __DIR__ .'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// RECEIVING MESSAGES FROM FRONTEND

$connection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
$channel = $connection->channel();

$channel->exchange_declare('frontend_exchange', 'direct', false, false, false);

//	Using NON DURABLE QUEUES: Third parameter is false
$channel->queue_declare('backend_mailbox', false, false, false, false);

// Binding key
$binding_key = "backend";

// Binding three items together to receive msgs
$channel->queue_bind('backend_mailbox', 'frontend_exchange', $binding_key);

// Terminal message to signal we are waiting for messages from frontend
echo '[*] Waiting for Frontend messages. To exit press CTRL+C', "\n\n";

// Callback that processes the login msg upon consuming from queue
$callback = function ($msg) use ($channel) {
	$data = json_decode($msg->getBody(), true);

	//echo '[x] Received Login from Frontend', "\n\n", $msg, "\n";
	echo '[+] RECEIVED LOGIN FROM FRONTEND', "\n", $msg->getBody(), "\n\n";

	$regexMsg = array();

	$user = $data['username'];
	$pass = $data['password'];

	// JSON to String sanitize
	$sanitizedUser = filter_var($user, FILTER_SANITIZE_STRING);
    	$sanitizedPass = filter_var($pass, FILTER_SANITIZE_STRING);

	// TODO: IMPLEMENT REGEX FOR ALL USER INPUT

	if (preg_match('/^[a-zA-Z0-9_]+$/', $sanitizedUser)) {
		$regexUser = "Correct Username";
	}
	else {
		//$validUser = false;
		$regexUser = "Invalid Username";
	}

	if (preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/', $sanitizedPass)) {
		$regexPass = "Password meets criteria";
	}
	else {
		$regexPass = "Error: Password DOES NOT meet criteria";
	}

	if (empty($regexMsg)) {
		$regexMsg['User Check'] = $regexUser;
		$regexMsg['Password Check'] = $regexPass;
	}

	$encodedRegex = json_encode($regexMsg);

	// Process to send message back
	$regexConnection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
	$regexChannel = $regexConnection->channel();

	// Separate Queue to send to frontend
	$regexChannel->queue_declare('regexQueue', false, false, false, false);

	//	Getting message ready for delivery
	$regexMessage = new AMQPMessage($encodedRegex);

	// 	Publishing message to frontend via queue
        $regexChannel->basic_publish($regexMessage, '', 'regexQueue');

	// RETURN ARRAY
	echo '[@] REGEX PROTOCOL ACTIVATED [@]', "\nRETURN MESSAGE TO FRONTEND\n";
	print_r($regexMsg);

	$regexChannel->close();
        $regexConnection->close();
	//$msg->ack();

};

$channel->basic_qos(null, 1, false);
$channel->basic_consume('backend_mailbox', '', false, true, false, false, $callback);

while(count($channel->callbacks)) {
       	$channel->wait();
	echo 'NO MORE INCOMING MESSAGES', "\n\n";
	break;
}

// Closing channel and connection
$channel->close();
$connection->close();


//	SENDING MESSAGES TO DATABASE

?>
