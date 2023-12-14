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

$exchangeName = 'frontend_exchange';	// Exhchange Name
$exchangeType = 'direct';
$queueName = 'backend_feedback';	// Queue Name
$backend_key = 'backendReview';		// Binding Key

//      ACTIVING MAIN API CHANNEL TO PROCESS FRONTEND REQUESTS
$channel = $connection->channel();

//      DECLARING EXCHANGE THAT WILL ROUTE MESSAGES FROM FRONTEND
$channel->exchange_declare(
			$exchangeName,
			$exchangeType,
			false,	// PASSIIVE
			true,	// DURABLE
			false	//AUTO-DELETE
);

//      DURABLE QUEUE ONLY FOR USER FEEDBACK/REVIEW : Third parameter is TRUE
$channel->queue_declare($queueName, false, true, false, false);

// Binding three items together to receive msgs
$channel->queue_bind($queueName, $exchangeName, $backend_key);

// Terminal message to signal we are waiting for messages from frontend
echo '[*] WAITING FOR FRONTEND TO SEND USER FEEDBACK DATA. To exit press CTRL+C', "\n\n";

//	CALLBACK RESPONSIBLE OF PROCESSESSING API REQUESTS
$callback = function ($userFeedback) use ($channel) {

	echo '[+] RECEIVED USER FEEDBACK FROM FRONTEND',"\n\n";
	$incomingData = json_decode($userFeedback->getBody(), true);

	print_r($incomingData);

	//	GETTING REVIEW DATA
	$userID = $incomingData['user_id'];
	$userComment = $incomingData['comment'];
	$userRating = $incomingData['rating'];

	$consumeFeedback = array();

	if (empty($consumeFeedback)) {
		$consumeFeedback['user_id'] = $userID;
		$consumeFeedback['comment'] = $userComment;
		$consumeFeedBack['rating'] = $userRating;
	}

	//	ENCODING ARRAY INTO JSON FOR DELIVERY
	$sendFeedback = json_encode($consumeFeedback);


	/*	PROCESS TO CONNECT TO RABBITMQ		*/

	//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
	$backendFeedbackConnection = null;
	$rabbitNodes = array('192.168.194.2', '192.168.194.1');

		foreach ($rabbitNodes as $node) {
			try {
            			$backendFeedbackConnection = new AMQPStreamConnection(
								$node,
								5672,
								'foodquest',
								'rabbit123'
				);
				echo "BACKEND FEEDBACK CONNECTION TO RABBITMQ CLUSTER WAS SUCCESSFUL @ $node\n";
				break;
			} catch (Exception $e) {
				continue;
			}

		}

		//	CONNECTION TO MAIN RABBIT NODE - PRE CLUSTER
		//$passwordConnection = new AMQPStreamConnection('192.168.194.2',5672,'foodquest','rabbit123');

		if (!$backendFeedbackConnection) {
        		die("CONNECTION ERROR: COULD NOT CONNECT TO ANY RABBITMQ NODE IN CLUSTER.");
		}

		//	OPENING CHANNEL TO COMMUNITCATE WITH DATABASE
		$backendFeedbackChannel = $backendFeedBackConnection->channel();

		//	EXCHANGE THAT WILL ROUTE MESSAGES TO DATABASE
		$backendFeedbackChannel->exchange_declare('backend_exchange', 'direct', false, false, false);

		//	ROUTING KEY TO DETERMINE DESTINATION
		$review_backend = 'database';

		//	Getting message ready for delivery
		$reviewMessage = new AMQPMessage(
					$sendFeedback,
					array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
		);

		// 	Publishing message to frontend via queue
        	$backendFeedbackChannel->basic_publish(
					$reviewMessage,
					'backend_exchange',
					$hash_key
		);

		//	COMMAND RESPONSE TO SIGNAL MSG WAS PROCESSES AND SENT
		echo '[@] BACKEND PROTOCOL ACTIVATED [@]', "\nMESSAGE TO DATABASE\n";
		print_r($consumeFeedback);

		$backendFeedbackChannel->close();
        	$backendFeedbackConnection->close();
};


while (true) {
	try {

		// 	MAIN CHANNEL QUALITY CONTROL
		$channel->basic_qos(null, 1, false);

		//	MAIN CHANNEL TO CONSUME MESSAGES FROM FRONTEND
		$channel->basic_consume($queueName, '', false, true, false, false, $callback);

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
