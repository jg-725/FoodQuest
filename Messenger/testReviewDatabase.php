<?php

/* TESTING: RECEIVING REVIEW MESSAGES FROM BACKEND */

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;







// EXCHANGE THAT MESSAGES WILL COME FROM
$channel->exchange_declare('test_exchange', 'direct', false, false, false);

// Using DURABLE QUEUES FOR DELIVERY: Third parameter is TRUE
$channel->queue_declare('review_queue', false, false, false, false);

// Binding key
$ = "database";

// Binding three items together to receive msgs
$channel->queue_bind('review_queue', 'test_exchange', $);

// Terminal message to signal we are waiting for messages from BACKEND
echo '[*] Waiting for BACKEND REVIEW MESSAGES. To exit press CTRL+C', "\n\n";

// CALLBACK RESPONSIBLE FOR PROCESSING INCOMING MESSAGES
$callback = function ($package) use ($channel) {
	echo '[+] RECEIVED CONFIRMED USER FEEDBACK FROM BACKEND', "\n", $package->getBody(), "\n\n";

	$userFeedback = json_decode($package->getBody(), true);

	print_r($userFeedback);

	//    GETTING VARIABLES SENT FROM BACKEND
    	$comment = $userFeedback['comment'];
    	$rating = $userFeedback['rating'];

	//  	JSON variables to String sanitize
    	$stringComment = filter_var($comment, FILTER_SANITIZE_STRING);
    	$stringRating = filter_var($rating, FILTER_SANITIZE_STRING);


    	/*	INSERT MYSQL CODE HERE		*/



};


//	KEEPING THE QUEUES AND EXCHANGES LOOKING FOR INCOMING MESSAGES
while (true) {
	try {
    		$channel->basic_qos(null, 1, false);
    		$channel->basic_consume('review_queue', '', false, true, false, false, $callback);

    		while (count($channel->callbacks)) {
        		$channel->wait();
        		echo 'NO MORE USER FEEDBACK MESSAGES FROM BACKEND', "\n\n";
        		break;
    		}
	} catch (ErrorException $e) {
    		// ERROR HANDLING
    		echo "CAUGHT DATABASE ErrorException: " . $e->getMessage();
	}
}
// CLOSING MAIN CHANNEL AND CONNECTION
$channel->close();
$connection->close();


?>
