<?php

/*      TESTING: RECEIVING LOGIN MESSAGES FROM BACKEND       */

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

//	SECTION TO RECEIVE MESSAGES FOR PROCESSING

//	CONNECTING TO MAIN RABBITMQ
$connection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
$channel = $connection->channel();

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
	echo '[+] RECEIVED VALID REGEX LOGIN FROM BACKEND', "\n", $msg->getBody(), "\n\n";

	$backendMsg =json_decode($msg->getBody(), true);

	$existsMsg = array();

	$user = $backendMsg['username'];
	$pass = $backendMsg['password'];

	//	TODO: ADD MYSQL CODE TO PROCESS LOGIN DATA

	//	VARIABLES TO CONNECTO TO MYSQL DATABASE
	$servername = "localhost";
	$username_db = "";
	$password_db = "";
	$dbname = "";

	$conn = mysqli_connect($servername, $username_db, $password_db, $dbname);

	// Check if the connection is successful
	if (!$conn) {
    		die("Connection failed: " . mysqli_connect_error());
	}

	// Check if the user exists in the database
	$sql_check = "SELECT * FROM users WHERE BINARY username = '$username'";
	$result = mysqli_query($conn, $sql_check);

	if (mysqli_num_rows($result) > 0) {
    		// User exists, retrieve the password
    		$row = mysqli_fetch_assoc($result);
    		$hash = $row['password'];
    		$userFound = true;
    		$userID = $row['id'];
	} else {
    		// User does not exist
    		$userFound = false;
    		$hash = null;
	}

	// Close the database connection
	mysqli_close($conn);

	$existsMsg = array();
	//	ENCODING RETURN MESSAGE
	if (empty($existsMsg)) {
		$existsMsg['userExists'] = $userFound;
		$existsMsg['userID'] = $userID;
	}

	//	GETTING MYSQL MESSAGE READY FOR DELIVERY TO FRONTEND
	$encodedExistsMsg = json_encode($existsMsg);

	//	Process to send message back to FRONTEND
	$existsConnection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
	$existsChannel = $existsConnection->channel();

	// Separate Queue to send to frontend
	$existsChannel->queue_declare('returnQueue', false, false, false, false);

	//	Getting message ready for delivery
	$existsMessage = new AMQPMessage(
			$encodedExistsMsg,
			array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
	);

	// 	Publishing message to frontend via queue
        $existsChannel->basic_publish($existsMessage, '', 'returnQueue');

	//	COMMAND LINE MESSAGE
	echo '[@] MYSQL CHECK PROTOCOL ACTIVATED [@]', "\nRETURN MESSAGE TO FRONTEND\n";

	print_r($existsMsg);	//Displaying array in command line

	$existsChannel->close();
        $existsConnection->close();


};

$channel->basic_qos(null, 1, false);
$channel->basic_consume('database_mailbox', '', false, true, false, false, $callback);

while (count($channel->callbacks)) {
       	$channel->wait();
	echo 'NO MORE INCOMING MESSAGES FROM BACKEND', "\n\n";
	break;
}

//	CLOSING MAIN CHANNEL AND CONNECTION
$channel->close();
$connection->close();

?>
