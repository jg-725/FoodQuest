<?php

/*      TESTING: RECEIVING LOGIN MESSAGES FROM BACKEND       */

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

//	MAIN SECTION TO RECEIVE VALID REGEX MESSAGES FROM BACKEND

//	CONNECTING TO MAIN RABBITMQ
$connection = new AMQPStreamConnection('192.168.194.2',
					5672,
					'foodquest',
					'rabbit123');
$channel = $connection->channel();

//	EXCHANGE THAT MESSAGES WILL COME FROM
$channel->exchange_declare('backend_exchange', 'direct', false, false, false);

//	Using DURABLE QUEUES FOR DELIVERY: Third parameter is TRUE
$channel->queue_declare('database_mailbox', false, true, false, false);

// Binding key
$loginDB = "database";

// Binding three items together to receive msgs
$channel->queue_bind('database_mailbox', 'backend_exchange', $loginDB);

// Terminal message to signal we are waiting for messages from BACKEND
echo '[*] Waiting for BACKEND messages. To exit press CTRL+C', "\n\n";

//	CALLBACK RESPONSIBLE OF PROCESSING INCOMING MESSAGES
$callback = function ($regexMsg) use ($channel) {
	echo '[+] RECEIVED VALID REGEX LOGIN FROM BACKEND', "\n", $msg->getBody(), "\n\n";

	$validLoginRegex =json_decode($msg->getBody(), true);

	//	GETTING VARIABLES SENT FROM BACKEND
	$user = $validLoginRegex['username'];
	$pass = $validLoginRegex['password'];

	//      JSON variables to String sanitize
        $stringUser = filter_var($user, FILTER_SANITIZE_STRING);
        $stringPass = filter_var($pass, FILTER_SANITIZE_STRING);

	//	VARIABLES TO CONNECTO TO MYSQL DATABASE SERVER
	$servername = "192.168.194.3";
	//$servername = "localhost";
	$username_db = "test";
	$password_db = "test";
	$dbname = "FoodQuest";

	$conn = mysqli_connect($servername, $username_db, $password_db, $dbname);

	// Check if the connection is successful
	if (!$conn) {
    		die("[X] CONNECTION TO MYSQL SERVER FAILED [X] " . mysqli_connect_error());
	}

	// Check if the user exists in the database
    	$sql_check = "SELECT * FROM Users WHERE BINARY username = '$stringUser'";
    	$result = mysqli_query($conn, $sql_check);

	if (mysqli_num_rows($result) > 0) {
    		// User exists
    		$row = mysqli_fetch_assoc($result);
    			$userExists = TRUE;
    			$id = $row['id'];
			$username = $row['username'];
	} else {
    		// User does not exist
    		$userExists = FALSE;
    		//$hash = null;
	}

	// Close the database connection
	mysqli_close($conn);

	/*
	$query=" SELECT Users FROM FoodQuest ORDER BY Msg_ID DESC";
	$run=mysqli_query($connection, $query);
	confirm_query($run);
	$messages=array();
	while($message=mysqli_fetch_assoc($run))
	{
		$messages[]= array('sender' =>$message['Sender'] , 'message' =>$message['Message'] );
	}
	return $messages;
	*/


	/*	GETTING RETURN ARRAY TO SEND TO FRONTEND - RABBITMQ	*/

	$existsMsg = array();
        //      ENCODING RETURN MESSAGE
        if (empty($existsMsg)) {
                $existsMsg['userExists'] = $userExists;
                $existsMsg['userID'] = $id;
		$existsMsg['username'] = $username;
        }

	//	GETTING MYSQL MESSAGE READY FOR DELIVERY TO FRONTEND
	$encodedExistsMsg = json_encode($existsMsg);

	/*	SENDING USER EXISTS MESSAGE TO FRONTEND	*/

	$existsConnection = new AMQPStreamConnection('192.168.194.2',
				5672,
				'foodquest',
				'rabbit123'
	);

	$existsChannel = $existsConnection->channel();

	//	TODO: ADD EXCHANGE, QUEUE, BINDING KEY, AND QUEUE BIND

	//	EXCHANGE THAT WILL ROUTE MESSAGES TO FRONTEND
	$existsChannel->exchange_declare('database_exchange',
					'direct',
					false,
					false,
					false);

	//	ROUTING KEY TO DETERMINE DESTINATION
	$exists_key = 'frontend';

	//	NOT NECESSARY ANYMORE
	//$existsChannel->queue_declare('returnQueue', false, false, false, false);

	//	Getting message ready for delivery
	$existsMessage = new AMQPMessage(
			$encodedExistsMsg,
			array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
	);

	// 	Publishing message to exchange via routing key
        $existsChannel->basic_publish($existsMessage,
					'database_exchange',
					$exists_key
	);

	//	COMMAND LINE MESSAGE
	echo '[@] MYSQL CHECK PROTOCOL ACTIVATED [@]', "\nRETURN MESSAGE TO FRONTEND\n";

	print_r($existsMsg);	//Displaying array in command line

	$existsChannel->close();
        $existsConnection->close();
};

	try {
		$channel->basic_qos(null, 1, false);
		$channel->basic_consume('database_mailbox', '', false, true, false, false, $callback);

		while (count($channel->callbacks)) {
       			$channel->wait();
			echo 'NO MORE INCOMING LOGIN MESSAGES FROM BACKEND', "\n\n";
			break;
		}
	}
	catch (ErrorException $e) {
		//	ERROR HANDLING
      		echo "CAUGHT DATABASE ErrorException: " . $e->getMessage();
  	}

//	CLOSING MAIN CHANNEL AND CONNECTION
$channel->close();
$connection->close();

?>
