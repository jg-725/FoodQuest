<?php

// Include RabbitMQ library
require_once __DIR__ .'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/*              SECTION TO SEND LOGIN TO BACKEND        */

// Connecting to RabbitMQ
$connectionSend = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
$channelSend = $connectionSend->channel();

// Declaring the exchange to send the message
$channelSend->exchange_declare('frontend_exchange', 'direct', false, false, false);

// Routing key address so RabbitMQ knows where to send the message
$routing_key = "backend";

// Login Data
$username = "John";
$password = "Password123";

// Creating array to store message type and login data
$loginArray = array();
if (empty($loginArray)) {

	$loginArray['username'] = $username;
	$loginArray['password'] = $password;
}

// Turining data into a string type
//$login_data = var_dump(implode(",", $send));
$encodedLogin = json_encode($loginArray);

// Creating AMQPMessage For Delivery
$msg = new AMQPMessage(
	$encodedLogin,
	array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
	//array('delivery_mode' => 2)
);

// Publishing data to RabbitMQ exchange for processing
$channelSend->basic_publish($msg, 'frontend_exchange', $routing_key);

echo ' [x] FRONTEND TASK: SENT TEST LOGIN TO BACKEND FOR PROCESSING', "\n";
print_r($loginArray);
echo "\n\n";

$channelSend->close();
$connectionSend->close();


/*		2 SECTIONS TO RECEIVE MESSAGES FROM BACKEND and DATABASE		*/


//      --- SECTION 1: WILL LISTEN FOR MESSAGES FROM BACKEND ---

// Connecting to RabbitMQ
$regexConnection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
$regexChannel = $regexConnection->channel();

$regexChannel->exchange_declare('backend_exchange', 'direct', false, false, false);

//	Making NON durable queue for testing
$regexChannel->queue_declare('frontend_mailbox', false, false, false, false);

// Binding Key
$regexKey = 'frontend';

// Binding corresponding queue and exchange
$regexChannel->queue_bind('frontend_mailbox', 'backend_exchange', $regexKey);

// Establishing callback variable for processing messages from database
$regexCallback = function ($msgContent) {

	echo "[+] RECEIVED REGEX RESPONSE FROM BACKEND\n";

	// Decoding return msg from backend into usuable code for processing
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

$regexChannel->basic_qos(null, 1, false);

// Triggering the process to consume msgs from BACKEND IF USER FORMAT IS INVALID
$regexChannel->basic_consume('frontend_mailbox', '', false, true, false, false, $regexCallback);

// while loop to keep checking for incoming messages from database
while ($regexChannel->is_open()) {
	$regexChannel->wait();
	break;
}

// Terminating channel and connection for receivin msgs
$regexChannel->close();
$regexConnection->close();



//      --- THIS PART WILL LISTEN FOR MESSAGES FROM DATABASE ---

//	Connecting to RabbitMQ
$connectionReceiveDatabase = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');

$channelReceiveDatabase = $connectionReceiveDatabase->channel();

$channelReceiveDatabase->exchange_declare('database_exchange', 'direct', false, false, false);

//      DECLARING NON durable queue for testing third parameter
$channelReceiveDatabase->queue_declare('frontend_mailbox', false, false, false, false);

$loginKey = 'frontend';

// 	Binding corresponding queue and exchange
$channelReceiveDatabase->queue_bind('frontend_mailbox', 'database_exchange', $loginKey);

// 	Establishing callback variable for processing messages from database
$receiverCallback = function ($msgContent) {

        // Decoding received msg from database into usuable code for processing
        $decodedDatabase = json_decode($msgContent->getBody(), true);

        $userExists = $decodedDatabase['userExists'];

        /* 2 IF statements: Checking if user exists */

        // Commands to be executed if username/password does not match
        if ($userExists == false) {
                echo "Entered Username already exists\n";
		echo "TRY AGAIN\n\n";
                //echo "<script>alert('Username or password does not exist in database');</script>";
                //echo "<script>location.href='login.php';</script>";
        }

        // Commands to be executed if user exists
        if ($userExists == true) {
                //die(header("location:home.php"));
		echo "[x] Welcome user";
        }
};

// Triggering the process to consume msgs from DATABASE IF USER EXISTS
$channelReceiveDatabase->basic_consume('frontend_mailbox', '', false, true, false, false, $receiverCallback);

// while loop to keep checking for incoming messages from database
while ($channelReceiveDatabase->is_open()) {
        $channelReceiveDatabase->wait();
        break;
}

// Terminating channel and connection for receivin msgs
$channelReceiveDatabase->close();
$connectionReceiveDatabase->close();

?>
