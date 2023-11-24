<?php

/*      TESTING RECEIVING MESSAGES FROM BACKEND       */

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

//	SECTION TO RECEIVE MESSAGES FOR PROCESSING

//	CONNECTING TO MAIN RABBITMQ
//$connection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
//$channel = $connection->channel();

$channel->exchange_declare('backend_exchange', 'direct', false, false, false);

//	Using NON DURABLE QUEUES: Third parameter is false
$channel->queue_declare('database_mailbox', false, false, false, false);

// Binding key
$binding_key = "database";

// Binding three items together to receive msgs
$channel->queue_bind('database_mailbox', 'backend_exchange', $binding_key);

// Terminal message to signal we are waiting for messages from BACKEND
echo '[*] Waiting for BACKEND messages. To exit press CTRL+C', "\n\n";

$callback = function ($msg) use ($channel) {
	$backendMsg =json_decode($msg->getBody(), true)
	echo '[+] RECEIVED REVISED LOGIN FROM BACKEND', "\n", $msg->getBody(), "\n\n";

	$existsMsg = array();

	$user = $backendMsg['username'];
	$pass = $backendMsg['password'];

	//	TODO: ADD MYSQL CODE TO PROCESS LOGIN DATA




	//	GETTING MYSQL MESSAGE READY FOR DELIVERY TO FRONTEND
	$encodedExistsMsg = json_encode($existsMsg);

	//	Process to send message back to FRONTEND
	$existsConnection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
	$existsChannel = $existsConnection->channel();

	// Separate Queue to send to frontend
	$existsChannel->queue_declare('returnQueue', false, false, false, false);

	//	Getting message ready for delivery
	$existsMessage = new AMQPMessage($encodedExistsMsg);

	// 	Publishing message to frontend via queue
        $existsChannel->basic_publish($existsMessage, '', 'returnQueue');

	//	COMMAND LINE MESSAGE
	echo '[@] MYSQL CHECK PROTOCOL ACTIVATED [@]', "\nRETURN MESSAGE TO FRONTEND\n";

	print_r($existsMsg);	//Displaying array in command line

	$existsChannel->close();
        $existsConnection->close();


};

$channel->basic_qos(null, 1, false);
$channel->basic_consume('databsae_mailbox', '', false, true, false, false, $callback);

while(count($channel->callbacks)) {
       	$channel->wait();
	echo 'NO MORE INCOMING MESSAGES FROM BACKEND', "\n\n";
	break;
}

// Closing channel and connection
$channel->close();
$connection->close();

?>
