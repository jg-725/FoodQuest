<?php


/*      RECEIVING PROCESSED USER FEEDBACK FROM BACKEND       */


//      AMQP LIBRARIES
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
$feedbackDatabaseConnection = null;
$rabbitmqNodes = array('192.168.194.2', '192.168.194.1');


foreach ($rabbitNodes as $node) {
	try {
        	$feedbackDatabaseConnection = new AMQPStreamConnection(
							$node,
							5672,
							'foodquest',
							'rabbit123'
		);
		echo "DATABASE CONNECTION TO RABBITMQ CLUSTER WAS SUCCESSFUL @ $node\n";
		break;
	} catch (Exception $e) {
		continue;
	}
}

if (!$feedbackDatabaseConnection) {
	die("CONNECTION ERROR: DATABASE COULD NOT CONNECT TO ANY RABBITMQ NODE.");
}

/ 	EXCHANGE THAT WILL ROUTED MESSAGES COMING FROM BACKEND
$channelDB->exchange_declare('backend_exchange', 'direct', false, false, false);

// 	Using DURABLE QUEUES FOR DELIVERY: Third parameter is TRUE
$channelDB->queue_declare('database_review', false, true, false, false);

// 	Binding key
$databaseReviewKey = "databaseReview";

// 	Binding three items together to receive msgs
$channelDB->queue_bind('database_review', 'backend_exchange', $databaseReviewKey);

// 	Terminal message to signal we are waiting for messages from BACKEND
echo '[*] Waiting for BACKEND messages. To exit press CTRL+C', "\n\n";

// CALLBACK RESPONSIBLE FOR PROCESSING INCOMING MESSAGES
$callback = function ($backendMsg) use ($channel) {

	echo '[+] RECEIVED PROCESSED USER FEEDBACK FROM BACKEND', "\n", $msg->getBody(), "\n\n";

    	$unloadMsg = json_decode($backendMsg->getBody(), true);

    	//    GETTING VARIABLES SENT FROM BACKEND
	$userID = $unloadMsg['userID'];
    	$comment = $unloadMsg['comment'];
    	$rating = $unloadMsg['rating'];

    	//  	JSON variables to String sanitize
	$stringID = filter_var($userID, FILTER_SANITIZE_STRING);
    	$stringComment = filter_var($comment, FILTER_SANITIZE_STRING);
    	$stringRating = filter_var($rating, FILTER_SANITIZE_STRING);


	/*	ENTER MYSQL CODE HERE	*/


	//	TODO: ADD MYSQL ACCOUNT CONNECTION


	// Insert the user data into the database
	$sql = "INSERT INTO Results (Comment, Rating) VALUES ('$comment', '$rating')";

	if (mysqli_query($conn, $sql)) {
    		echo "USER FEEDBACK WAS STORED IN FOODQUEST DATABASE";
    		$result = 'SUCCESS';
	} else {
    		echo "Error: " . $sql . "<br>" . mysqli_error($conn);
	}

	// Close the database connection
	mysqli_close($conn);


	/*      END OF MYSQL CODE   */



	/* PROCESS TO SEND SUCCESSFUL FEEDBACK MESSAGE TO FRONTEND - RABBITMQ */

	//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
	$messageConnection = null;
	$rabbitNodes = array('192.168.194.2', '192.168.194.1');

	foreach ($rabbitNodes as $node) {
		try {
            		$messageConnection = new AMQPStreamConnection(
								$node,
								5672,
								'foodquest',
								'rabbit123'
			);
			echo "FEEDBACK DATABASE CONNECTION TO RABBITMQ WAS SUCCESSFUL @ $node\n";
			break;
		} catch (Exception $e) {
			echo "ERROR: RABBITMQ CONNECTION WAS UNSUCCESSFUL @ $node\n";
			continue;
		}
	}

	if (!$messageConnection) {
                die("CONNECTION ERROR: COULD NOT CONNECT TO RABBITMQ NODE.");
        }

	$messageChannel = $messageConnection->channel();

        // EXCHANGE THAT WILL ROUTE DATABASE MESSAGES
        $messageChannel->exchange_declare('database_exchange', 'direct', false, false, false);

        // Routing key address so RabbitMQ knows where to send the message
        $reviewDatabase = "frontend";

	$reviewOutcome = [
        		'message' => $result,
    	];

	// GETTING MYSQL MESSAGE READY FOR DELIVERY TO FRONTEND
        $encodedMessage = json_encode($reviewOutcome);

	// Getting message ready for delivery
        $rabbitMessage = new AMQPMessage(
				$encodedMessage,
				array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));

	// Publishing message to FRONTEND via queue
        $messageChannel->basic_publish($rabbitMessage, 'database_exchange', $reviewDatabase);

	// Command line message
        echo '[@] MYSQL PROTOCOLS WERE EXECUTED [@]', "\n--RETURN MESSAGE SENT TO FRONTEND--\n";

	print_r($reviewOutcome); // Displaying array in command line

	// CLOSING CHANNEL AND CONNECTION TALKING TO FRONTEND
        $messageChannel->close();
        $messageConnection->close();

};


while (true) {
	try {
		$channelDB->basic_qos(null, 1, false);
		$channelDB->basic_consume('database_review', '', false, true, false, false, $callbackDB);

		while(count($channelDB->callbacks)) {
       			$channelDB->wait();
			echo 'NO MORE INCOMING MESSAGES FROM BACKEND', "\n\n";
			break;
		}
	}
	catch (ErrorException $e) {
        	// Handle Error
        	echo "ErrorException CAUGHT AT: " . $e->getMessage();
    	}
}

//	Closing MAIN channel and connection
$channelDB->close();
$$feedbackDatabaseConnection->close();


?>
