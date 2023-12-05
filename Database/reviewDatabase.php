<?php


/*      RECEIVING PROCESSED USER FEEDBACK FROM BACKEND       */


//      AMQP LIBRARIES
require_once __DIR__ . '/vendor/autoload.php';
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
        echo "DATABASE CONNECTION TO RABBITMQ WAS SUCCESSFUL @ $node\n";
        break;
    } catch (Exception $e) {
        continue;
    }
}


if (!$connection) {
    die("DATABASE CONNECTION ERROR: COULD NOT CONNECT TO ANY RABBITMQ NODE");
}

// 	EXCHANGE THAT WILL ROUTED MESSAGES COMING FROM BACKEND
$channel->exchange_declare('backend_exchange', 'direct', false, false, false);

// 	Using DURABLE QUEUES FOR DELIVERY: Third parameter is TRUE
$channel->queue_declare('database_review', false, true, false, false);

// 	Binding key
$databaseReviewKey = "databaseReview";

// 	Binding three items together to receive msgs
$channel->queue_bind('database_review', 'backend_exchange', $databaseReviewKey);

// 	Terminal message to signal we are waiting for messages from BACKEND
echo '[*] Waiting for BACKEND messages. To exit press CTRL+C', "\n\n";

// CALLBACK RESPONSIBLE FOR PROCESSING INCOMING MESSAGES
$callback = function ($backendMsg) use ($channel) {

	echo '[+] RECEIVED PROCESSED USER FEEDBACK FROM BACKEND', "\n", $msg->getBody(), "\n\n";

    	$unloadMsg = json_decode($backendMsg->getBody(), true);

    	//    GETTING VARIABLES SENT FROM BACKEND
    	$comment = $unloadMsg['comment'];
    	$rating = $unloadMsg['rating'];

    	//  	JSON variables to String sanitize
    	$stringComment = filter_var($comment, FILTER_SANITIZE_STRING);
    	$stringRating = filter_var($rating, FILTER_SANITIZE_STRING);



	/*	ENTER MYSQL CODE HERE	*/















	/*      END OF MYSQL CODE   */



};


//	KEEPING THE QUEUES AND EXCHANGES LOOKING FOR INCOMING MESSAGES
while (true) {
	try {
    		$channel->basic_qos(null, 1, false);
    		$channel->basic_consume('database_review', '', false, true, false, false, $callback);

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
