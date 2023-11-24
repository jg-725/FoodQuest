<?php
require_once __DIR__ .'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/*              SECTION TO SEND LOGIN TO BACKEND        */

// Connecting to RabbitMQ
$connectionSend = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
$channelSend = $connectionSend->channel();

// Declaring the exchange to send the message
$channelSend->exchange_declare('test_exchange', 'direct', false, false, false);

// Routing key address so RabbitMQ knows where to send the message
$routing_key = "backend";

// Login Data
$username = "test";
$password = "test";
$first = 'Michael';
$last = 'Jordan';
$email = 'chicagoRedBulls@email.com';

// Creating array to store message type and login data
$send = array();
if (empty($send)) {

	$send['username'] 	= $username;
	$send['password'] 	= $password;
	$send['firstName'] 	= $first;
	$send['lastName']	= $last;
	$send['email']		= $email;
}

// Turining data into a string type
//$login_data = var_dump(implode(",", $send));
$login_data = json_encode($send);

// Creating AMQPMessage For Delivery
$msg = new AMQPMessage(
	$login_data,
	array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
	//array('delivery_mode' => 2)
);

// Publishing data to RabbitMQ exchange for processing
$channelSend->basic_publish($msg, 'test_exchange', $routing_key);

echo ' [x] Frontend Task: SENT USER REGISTRATION TO BACKEND FOR PROCESSING', "\n";
print_r($send);
echo "\n\n";

$channelSend->close();
$connectionSend->close();


/*		SECTION TO RECEIVE MESSAGES FROM BACKEND and DATABASE		*/


//      --- THIS PART WILL LISTEN FOR MESSAGES FROM BACKEND ---

// Connecting to RabbitMQ
$connectionReceiveBackend = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');

$channelReceiveBackend = $connectionReceiveBackend->channel();
//	Making NON durable queue for testing
$channelReceiveBackend->queue_declare('test_queue', false, false, false, false);

// Establishing callback variable for processing messages from database
$receiveCallback1 = function ($msgContent) {

	// Decoding received msg from database into usuable code for processing
	$decodedBackend = json_decode($msgContent->getBody(), true);

	$validUser = $decodedBackend['validUser'];

	$validPassword = $decodedBackend['validPassword'];

	/* 2 IF statements: Checking if login data is valid */

	// Commands to be executed if username/password does not match
	if ($validUser == false || $validPassword == false) {
		echo "Username or password does not meet criteria\n";
		//echo "<script>alert('Username or password does not exist in database');</script>";
		//echo "<script>location.href='login.php';</script>";
	}

	// Commands to be executed if data is valid
	if ($validUser == true && $validPassword == true) {
		//die(header("location:home.php"));
		echo "Congrats: Username and Password Are Valid\n";
	}
};

// Triggering the process to consume msgs from BACKEND IF USER FORMAT IS INVALID
$channelReceiveBackend->basic_consume('test_queue', '', false, true, false, false, $receiveCallback1);

// while loop to keep checking for incoming messages from database
while ($channelReceiveBackend->is_open()) {
	$channelReceiveBackend->wait();
	break;
}

// Terminating channel and connection for receivin msgs
$channelReceiveBackend->close();
$connectionReceiveBackend->close();



//      --- THIS PART WILL LISTEN FOR MESSAGES FROM DATABASE ---

// Connecting to RabbitMQ
$connectionReceiveDatabase = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');

$channelReceiveDatabase = $connectionReceiveDatabase->channel();

//      DECLARING NON durable queue for testing
$channelReceiveDatabase->queue_declare('test_queue', false, false, false, false);

// Establishing callback variable for processing messages from database
$receiverCallback2 = function ($msgContent) {

        // Decoding received msg from database into usuable code for processing
        $decodedDatabase = json_decode($msgContent->getBody(), true);

        $userExists = $decodedDatabase['userExists'];

        /* 2 IF statements: Checking if user exists */

        // Commands to be executed if username/password does not match
        if ($userExists == false) {
                echo "[x] DATABASE ERROR: USER ALREADY EXISTS\n";
		echo "TRY AGAIN\n\n";
                //echo "<script>alert('Username or password does not exist in database');</script>";
                //echo "<script>location.href='login.php';</script>";
        }

        // Commands to be executed if user exists
        if ($userExists == true) {
                //die(header("location:home.php"));
		echo "[+] WELCOME ";
        }
};

// Triggering the process to consume msgs from DATABASE IF USER EXISTS
$channelReceiveDatabase->basic_consume('test_queue', '', false, true, false, false, $receiverCallback2);

// while loop to keep checking for incoming messages from database
while ($channelReceiveDatabase->is_open()) {
        $channelReceiveDatabase->wait();
        break;
}

// Terminating channel and connection for receivin msgs
$channelReceiveDatabase->close();
$connectionReceiveDatabase->close();

?>
