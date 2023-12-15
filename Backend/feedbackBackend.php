<?php

/*      NEW FEATURE: RECEIVING USER FEEDBACK FROM FRONTEND       */


//      AMQP LIBRARIES
require_once __DIR__ .'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
$feedbackBackendConnection = null;
$rabbitNodes = array('192.168.194.2', '192.168.194.1');
$port = 5672;
$user = 'foodquest';
$pass = 'rabbit123';


foreach ($rabbitNodes as $node) {
	try {
        	$feedbackBackendConnection = new AMQPStreamConnection(
						$node,
						$port,
						$user,
						$pass
		);
		echo "BACKEND FEEDBACK CONNECTION TO RABBITMQ CLUSTER WAS SUCCESSFUL @ $node\n";
		break;
	} catch (Exception $e) {
		continue;
	}
}

if (!$feedbackBackendConnection) {
	die("CONNECTION ERROR: COULD NOT CONNECT TO ANY RABBITMQ NODE IN CLUSTER.");
}


//      RABBITMQ MESSAGE BROKER SETTINGS TO CONSUME MESSAGES
$consumerExchange 	= 'frontend_exchange';		// Exchange Name
$exchangeType 		= 'direct';			// Exchange Type
$backendQueue	 	= 'BE_feedback_mailbox';	// Queue Name
$feedbackBK   		= 'feedback-backend';		// BINDING KEY MATCHES SIGNUP ROUTING KEY


//      ACTIVING MAIN API CHANNEL TO PROCESS FRONTEND REQUESTS
$feedbackBackendChannel = $feedbackBackendConnection->channel();

//      DECLARING DURABLE EXCHANGE THAT WILL ROUTE MESSAGES FROM FRONTEND
$feedbackBackendChannel->exchange_declare(
			$consumerExchange,
			$exchangeType,
			false,			// PASSIVE
			true,			// DURABLE
			false			// AUTO-DELETE
);

//      DURABLE QUEUE ONLY FOR USER FEEDBACK/REVIEW : Third parameter is TRUE
$feedbackBackendChannel->queue_declare(
		$backendQueue,
		false,		// PASSIVE: check whether an exchange exists without modifying the server state
		true,		// DURABLE: the queue will survive a broker restart
		false,		// EXCLUSIVE: used by only one connection and the queue will be deleted when that connection closes
		false		// AUTO-DELETE: queue is deleted when last consumer unsubscribes
);

// Binding three items together to receive msgs
$feedbackBackendChannel->queue_bind($backendQueue, $consumerExchange, $feedbackBK);

// Terminal message to signal we are waiting for messages from frontend
echo '[*] WAITING FOR FRONTEND TO SEND USER FEEDBACK DATA. To exit press CTRL+C', "\n\n";

//	CALLBACK RESPONSIBLE OF PROCESSESSING API REQUESTS
$callback = function ($userFeedback) use ($feedbackBackendChannel) {

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


	////////	PROCESS TO CONNECT TO RABBITMQ		*/


	//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
	$sendFeedbackConnection = null;
	$rabbitNodes = array('192.168.194.2', '192.168.194.1');
	$port = 5672;
        $user = 'foodquest';
        $pass = 'rabbit123';

	foreach ($rabbitNodes as $node) {
			try {
            			$sendFeedbackConnection = new AMQPStreamConnection(
									$node,
									$port,
									$user,
									$pass
				);
				echo "BACKEND FEEDBACK CONNECTION TO RABBITMQ CLUSTER WAS SUCCESSFUL @ $node\n\n";
				break;
			} catch (Exception $e) {
				continue;
			}

	}

		//	CONNECTION TO MAIN RABBIT NODE - PRE CLUSTER
		//$passwordConnection = new AMQPStreamConnection('192.168.194.2',5672,'foodquest','rabbit123');

		if (!$sendFeedbackConnection) {
        		die("CONNECTION ERROR: COULD NOT CONNECT TO ANY RABBITMQ NODE IN CLUSTER.");
		}

		//      RABBITMQ MESSAGE BROKER SETTINGS TO SEND MESSAGES
		$publishExchange 	= 'backend_exchange';	// Exchange Name
                $exchangeType		= 'direct';		// Exchange Type
                $feedbackRK 		= 'feedback-database';	// ROUTING KEY TO DETERMINE DESTINATION


		//	OPENING CHANNEL TO COMMUNITCATE WITH DATABASE
		$sendFeedbackChannel = $sendFeedBackConnection->channel();


		//	EXCHANGE THAT WILL ROUTE MESSAGES TO DATABASE
		$sendFeedbackChannel->exchange_declare(
			$publishExchange,
			$exchangeType,
			false,		// PASSIVE
			true,		// DURABLE
			false		// AUTO-DELETE
		);

		//	Getting message ready for delivery
		$reviewMessage = new AMQPMessage(
					$sendFeedback,
					array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
		);

		// 	Publishing message to frontend via queue
        	$sendFeedbackChannel->basic_publish(
					$reviewMessage,
					$publishExchange,
					$feedbackRK
		);

		//	COMMAND RESPONSE TO SIGNAL MSG WAS PROCESSES AND SENT
		echo '[@] BACKEND PROTOCOL ACTIVATED [@]', "\nMESSAGE TO DATABASE\n";
		print_r($consumeFeedback);

		$sendFeedbackChannel->close();
        	$sendFeedbackConnection->close();
};


while (true) {
	try {

		// 	MAIN CHANNEL QUALITY CONTROL
		$feedbackBackendChannel->basic_qos(null, 1, false);

		//	MAIN CHANNEL TO CONSUME MESSAGES FROM FRONTEND
		$feedbackBackendChannel->basic_consume($backendQueue, '', false, true, false, false, $callback);

		//	TODO: CREATE FUNCTION OR LOOP TO ACTIVE LISTEN FOR MESSAGES FROM FRONTEND

		while (count($feedbackBackendChannel->callbacks)) {
       			$feedbackBackendChannel->wait();
			echo 'NO MORE INCOMING MESSAGES', "\n\n";
			break;
		}
	} catch (ErrorException $e) {
     	 		// Handle Error
      			echo "ERROR IN SENDING LOGIN MESSAGE-> " . $e->getMessage();
  	}
}


//	CLOSING MAIN CHANNEL AND CONNECTION
$feedbackBackendChannel->close();
$feedbackBackendConnection->close();


?>
