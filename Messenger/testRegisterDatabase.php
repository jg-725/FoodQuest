<?php

/*      TESTING: RECEIVING VALID REGEX REGISTER INPUT FROM BACKEND       */

//require_once __DIR__ . '/vendor/autoload.php';
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
	echo '[+] RECEIVED VALID REGEX REGISTER INPUT FROM BACKEND', "\n", $msg->getBody(), "\n\n";

	$validRegRegex =json_decode($msg->getBody(), true)

	//	GETTING VARIABLES SENT FROM BACKEND
	$user  = $validRegRegex['username'];
	$pass  = $validRegRegex['password'];
	$first = $validRegRegex['firstName'];
	$last  = $validRegex['lastName'];
	$email = $validRegRegex['email'];

	//	TODO: ADD MYSQL CODE TO PROCESS LOGIN DATA

	// Connect to the database
	$servername = "localhost";
	$username_db = "test";
	$password_db = "test";
	$dbname = "test";

	$conn = mysqli_connect($servername, $username_db, $password_db, $dbname);

	// Check if the connection is successful
	if (!$conn) {
    		die("Connection failed: " . mysqli_connect_error());
	}
	echo "Connected Successfully To MYSQL";

	/*
	// Check if the user already exists in the database
    	$sql_check = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
    	$result = mysqli_query($conn, $sql_check);

    	if (mysqli_num_rows($result) > 0) {
        // User already exists
        	echo "User already exists in the database.\n";
		$userExists = true;
    	} else {
        // User does not exist
	// Insert the user data into the database
	$sql = "INSERT INTO users (username, password, email, firstname, lastname) VALUES ('$username', '$password', '$email', '$firstname', '$lastname')";
	*/



	// 	TODO: CREATE AN IF STATEMENT TO CHECK IS NEW USER IS SUCCESSFUL


	//	ARRAY TO STORE MESSAGE
	$returnMsg = array();
	if (empty($returnMsg)) {	// Check if array is empty
        	//$send['type'] = ;
        	$returnMsg['newUser'] = $userCondition;
        	//$send['password'] = $password;
	}

	//	GETTING MYSQL MESSAGE READY FOR DELIVERY TO FRONTEND
	$encodedMsg = json_encode($returnMsg);

	mysqli_close($conn);

	//	Process to send message back to FRONTEND
	$existsConnection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
	$existsChannel = $existsConnection->channel();

	//	EXCHANGE THAT WILL ROUTE DATABASE MESSAGES
	$existsChannel->exchange_declare('database_exchange', false, false, false, false);

	//	Routing key address so RabbitMQ knows where to send the message
	$returnToFrontend = "frontend";

	//	Getting message ready for delivery
	$existsMessage = new AMQPMessage($encodedMsg,
			array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
	);

	// 	Publishing message to frontend via queue
        $existsChannel->basic_publish($existsMessage, 'database_exchange', $returnToFrontend);

	//	COMMAND LINE MESSAGE
	echo '[@] MYSQL CHECK PROTOCOL ACTIVATED [@]', "\nRETURN MESSAGE TO FRONTEND\n";

	print_r($returnMsg);	//Displaying array in command line

	//	CLOSING CHANNEL AND CONNECTION TALKING TO FRONTEND
	$existsChannel->close();
        $existsConnection->close();
};

$channel->basic_qos(null, 1, false);
$channel->basic_consume('database_mailbox', '', false, true, false, false, $callback);

while(count($channel->callbacks)) {
       	$channel->wait();
	echo 'NO MORE INCOMING MESSAGES FROM BACKEND', "\n\n";
	break;
}

//	Closing MAIN channel and connection
$channel->close();
$connection->close();

?>
