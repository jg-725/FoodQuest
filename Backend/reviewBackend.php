<?php

/*      NEW FEATURE: RECEIVING USER FEEDBACK FROM FRONTEND       */


//      AMQP LIBRARIES
require_once __DIR__ .'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
$connection = null;
$rabbitNodes = array('192.168.194.2', '192.168.194.1');

foreach ($rabbitNodes as $node) {
    try {
        $connection = new AMQPStreamConnection($node,
						5672,
						'foodquest',
						'rabbit123'
	);
        echo "BACKEND CONNECTION TO RABBITMQ CLUSTER WAS SUCCESSFUL @ $node\n";
        break;
    } catch (Exception $e) {
        continue;
    }
}

if (!$connection) {
    die("BACKEND CONNECTION ERROR: COULD NOT CONNECT TO ANY RABBITMQ NODE");
}

//      ACTIVING MAIN API CHANNEL TO PROCESS FRONTEND REQUESTS
$channel = $connection->channel();

//      DECLARING EXCHANGE THAT WILL ROUTE MESSAGES FROM FRONTEND
$channel->exchange_declare('frontend_exchange', 'direct', false, false, false);

//      DURABLE QUEUE ONLY FOR USER FEEDBACK/REVIEW : Third parameter is TRUE
$channel->queue_declare('backend_review', false, true, false, false);

// Binding Key
$backend_key = 'backendReview';

// Binding three items together to receive msgs
$channel->queue_bind('backend_review', 'frontend_exchange', $backend_key);

// Terminal message to signal we are waiting for messages from frontend
echo '[*] WAITING FOR FRONTEND TO SEND USER FEEDBACK DATA. To exit press CTRL+C', "\n\n";

//	CALLBACK RESPONSIBLE OF PROCESSESSING API REQUESTS
$callback = function ($userContent) use ($channel) {

	echo '[+] RECEIVED USER FEEDBACK FROM FRONTEND',"\n\n";
	$incomingData = json_decode($userContent->getBody(), true);

	//	GETTING REVIEW DATA


};



while (true) {
	try {

		// 	MAIN CHANNEL QUALITY CONTROL
		$channel->basic_qos(null, 1, false);

		//	MAIN CHANNEL TO CONSUME MESSAGES FROM FRONTEND
		$channel->basic_consume('backend_review', '', false, true, false, false, $callback);

		//	TODO: CREATE FUNCTION OR LOOP TO ACTIVE LISTEN FOR MESSAGES FROM FRONTEND

		while(count($channel->callbacks)) {
       			$channel->wait();
			echo 'NO MORE INCOMING MESSAGES', "\n\n";
			break;
		}
	} catch (ErrorException $e) {
     	 		// Handle Error
      			echo "ERROR IN SENDING LOGIN MESSAGE-> " . $e->getMessage();
  	}
}


//	CLOSING MAIN CHANNEL AND CONNECTION
$channel->close();
$connection->close();



?>
