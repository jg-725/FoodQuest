<?php


/*      FINAL DELIVERABLE: RECEIVING PROCESSED USER FEEDBACK FROM BACKEND       */


//      AMQP LIBRARIES
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
$DBFeedbackConnection = null;
$rabbitNodes = array('192.168.194.2', '192.168.194.1');
$port = 5672;
$user = 'foodquest';
$pass = 'rabbit123';

foreach ($rabbitNodes as $node) {
	try {
        	$DBFeedbackConnection = new AMQPStreamConnection(
							$node,
							$port,
							$user,
							$pass
		);
		echo "DATABASE FEEDBACK CONNECTION TO RABBITMQ CLUSTER WAS SUCCESSFUL @ $node\n";
		break;
	} catch (Exception $e) {
		continue;
	}
}

if (!$DBFeedbackConnection) {
	die("CONNECTION ERROR: DATABASE COULD NOT CONNECT TO ANY RABBITMQ NODE.");
}

//      RABBITMQ MESSAGE BROKER SETTINGS
$consumeExchange        = 'backend_exchange';   // Exchange Name
$exchangeType           = 'direct';             // Exchange Type
$databaseQueue          = 'DB_feedback_mailbox';  // Queue Name
$feedbackBK             = 'feedback-database';    // BINDING KEY VALUE MATCHES REGISTER BACKEND ROUTING KEY


//      ACTIVING MAIN CHANNEL FOR BACKEND CONNECTION
$DBFeedbackChannel = $DBFeedbackConnection->channel();

// 	DECLARING DURABLE EXCHANGE THAT WILL ROUTE MESSAGES FROM BACKEND
$DBFeedbackChannel->exchange_declare(
			$consumeExchange,
			$exchangeType,
			false,
			true,		// DURABLE
			false		// AUTO-DELETE
);

//      USING DURABLE QUEUE FOR WEBSITE: Third parameter is TRUE
$DBFeedbackChannel->queue_declare(
                $databaseQueue,
                false,          // PASSIVE: check whether an exchange exists without modifying the server state
                true,           // DURABLE: the queue will survive a broker restart
                false,          // EXCLUSIVE: used by only one connection and the queue will be deleted when that connection closes
                false           // AUTO-DELETE: queue is deleted when last consumer unsubscribes
);

// Binding three items together to receive msgs
$DBFeedbackChannel->queue_bind($databaseQueue, $consumeExchange, $feedbackBK);


// 	Terminal message to signal we are waiting for messages from BACKEND
echo '[*] Waiting for BACKEND messages. To exit press CTRL+C', "\n\n";

// CALLBACK RESPONSIBLE FOR PROCESSING INCOMING MESSAGES
$callbackDBFeedback = function ($backendMsg) use ($DBFeedbackChannel) {

    	$unloadMsg = json_decode($backendMsg->getBody(), true);

	print_r($unloadMsg);

	echo '[+] RECEIVED PROCESSED USER FEEDBACK FROM BACKEND', "\n", $backendMsg->getBody(), "\n\n";

    	//    GETTING VARIABLES SENT FROM BACKEND
	$userID = $unloadMsg['userID'];
    	$comment = $unloadMsg['message'];
    	$rating = $unloadMsg['rating'];

    	//  	JSON variables to String sanitize
	$stringID = filter_var($userID, FILTER_SANITIZE_STRING);
    	$stringComment = filter_var($comment, FILTER_SANITIZE_STRING);
    	$stringRating = filter_var($rating, FILTER_SANITIZE_STRING);


	/*	ENTER MYSQL CODE HERE	*/
		
	$servername = "192.168.194.3";
 		   $username_db = "test";
   		 $password_db = "test";
  		  $dbname = "FoodQuest";


	//	TODO: ADD MYSQL ACCOUNT CONNECTION
	$conn = mysqli_connect($servername, $username_db, $password_db, $dbname);



	// Insert the user data into the database
	$sql = "INSERT INTO Feedback (Comment, Rating) VALUES ('$comment', '$rating') WHERE id = $userID";

	if (mysqli_query($conn, $sql)) {
    		echo "USER FEEDBACK WAS STORED IN FOODQUEST DATABASE";
    		$result = 'SUCCESS';
	} else {
    		echo "Error: " . $sql . "<br>" . mysqli_error($conn);
	}

	// Close the database connection
	mysqli_close($conn);


	/*      END OF MYSQL CODE   */



	/////////////////	PROCESS TO SEND SUCCESSFUL FEEDBACK MESSAGE TO FRONTEND - RABBITMQ	///////////////////


	//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
	$messageConnection = null;
	$rabbitNodes = array('192.168.194.2', '192.168.194.1');
	$port = 5672;
        $user = 'foodquest';
        $pass = 'rabbit123';

	foreach ($rabbitNodes as $node) {
		try {
            		$messageConnection = new AMQPStreamConnection(
								$node,
								$port,
								$user,
								$pass
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

	 //      RABBITMQ MESSAGE BROKER SETTINGS TO SEND MESSAGES
        $publishExchange        = 'database_exchange';  // Exchange Name
        $exchangeType           = 'direct';             // Exchange Type
        $successRK              = 'feedback-frontend';   // ROUTING KEY TO DETERMINE DESTINATION

	$messageChannel = $messageConnection->channel();

        // EXCHANGE THAT WILL ROUTE DATABASE MESSAGES
        $messageChannel->exchange_declare(
                        $publishExchange,
                        $exchangeType,
                        false,          // PASSIVE
                        true,           // DURABLE
                        false           // AUTO-DELETE
        );

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
        $messageChannel->basic_publish(
				$rabbitMessage,
				$publishExchange,
				$successRK
	);

	// Command line message
        echo '[@] MYSQL PROTOCOLS WERE EXECUTED [@]', "\n--RETURN MESSAGE SENT TO FRONTEND--\n";

	print_r($reviewOutcome); // Displaying array in command line

	// CLOSING CHANNEL AND CONNECTION TALKING TO FRONTEND
        $messageChannel->close();
        $messageConnection->close();

};


while (true) {
	try {
		$DBFeedbackChannel->basic_qos(null, 1, false);
		$DBFeedbackChannel->basic_consume($databaseQueue, '', false, true, false, false, $callbackDBFeedback);

		while (count($DBFeedbackChannel->callbacks)) {
       			$DBFeedbackChannel->wait();
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
$DBFeedbackChannel->close();
$DBFeedbackConnection->close();


?>
