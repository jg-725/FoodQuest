<?php

/*	TESTING: RECEIVING LOGIN MESSAGES FROM FRONTEND	*/

//	NECESSARY AMQP LIBRARIES FOR PHP
require_once __DIR__ .'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

//	CONNECTING TO MAIN RABBIT NODE
$connection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');

//	ACTIVING MAIN CHANNEL FOR FRONTEND CONNECTION
$channel = $connection->channel();

//	DECLARING EXCHANGE THAT WILL ROUTE MESSAGES FROM FRONTEND
$channel->exchange_declare('frontend_exchange', 'direct', false, false, false);

//	USING DURABLE QUEUE FOR WEBSITE: Third parameter is TRUE
$channel->queue_declare('backend_mailbox', false, true, false, false);

// Binding key
$binding_key = "backend";

// Binding three items together to receive msgs
$channel->queue_bind('backend_mailbox', 'frontend_exchange', $binding_key);

// Terminal message to signal we are waiting for messages from frontend
echo '[*] WAITING FOR FRONTEND TO SEND MESSAGES. To exit press CTRL+C', "\n\n";



//	CALLBACK RESPONSIBLE OF PROCESSESSING VALID AND INVALID USER REQUESTS
$callback = function ($userData) use ($channel) {
	$userData = json_decode($msg->getBody(), true);

	echo '[+] RECEIVED LOGIN FROM FRONTEND', "\n", $msg->getBody(), "\n\n";

	$regexMsg = array();

	$user = $data['username'];
	$pass = $data['password'];

	//	JSON to String sanitize
	$stringUser = filter_var($user, FILTER_SANITIZE_STRING);
    	$stringPass = filter_var($pass, FILTER_SANITIZE_STRING);

	//	TODO: IMPLEMENT REGEX FOR LOGIN INPUT BEFORE SENDING TO DATABASE

	//	USERNAME REGEX
	if (preg_match('/^[a-zA-Z0-9_]+$/', $stringUser)) {
		$regexUser = TRUE;
	}
	else {
		$regexUser = FALSE;
		//$regexUser = "Invalid Username";
	}

	//	PASSWORD REGEX
	if (preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $stringPass)) {
		//$regexPass = "Password meets criteria";
		$regexPass = TRUE;
	}
	else {
		$regexPass = FALSE;
		//$regexPass = "Error: Password DOES NOT meet criteria";
	}

	$regexMsg = array();

	//	IF STATEMENT TO SEND VALID INPUT TO DATABASE

	if ($regexUser == TRUE && $regexPass == TRUE) {
		// Command line message
		echo "[+] LOGIN INPUT MEETS SITE REQUIREMENTS";

		//	CREATING ARRAY OF VALID REGEX LOGIN
		if (empty($regexMsg)) {
			$regexMsg['username'] = $stringUser;
			$regexMsg['password'] = $stringPass;
		}

		//	ENCODING ARRAY INTO JSON FOR DELIVERY
		$validRegexArray = json_encode($regexMsg);

		//	CONNECTION TO MAIN RABBIT NODE
		$validRegexConnection = new AMQPStreamConnection('192.168.194.2',
					5672,
					'foodquest',
					'rabbit123'
		);

		//	OPENING CHANNEL TO COMMUNITCATE WITH DATABASE
		$validRegexChannel = $validRegexConnection->channel();

		//	EXCHANGE THAT WILL ROUTE MESSAGES TO DATABASE
		$validRegexChannel->exchange_declare('backend_exchange', 'direct', false, false, false);

		//	ROUTING KEY TO DETERMINE DESTINATION
		$valid_key = 'database';

		//	Separate Queue to send to DATABASE
		//$validRegexChannel->queue_declare('validRegex', false, false, false, false);

		//	Getting message ready for delivery
		$validRegexMessage = new AMQPMessage(
					$validRegexArray,
					array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
		);

		// 	Publishing message to frontend via queue
        	$validRegexChannel->basic_publish(
					$validRegexMessage,
					'backend_exchange',
					$valid_key
		);

		//	COMMAND RESPONSE TO SIGNAL MSG WAS PROCESSES AND SENT
		echo '[@] REGEX PROTOCOL ACTIVATED [@]', "\nMESSAGE TO DATABASE\n";
		print_r($regexMsg);

		$validRegexChannel->close();
        	$validRegexConnection->close();

	}
	//	ELSE STATEMENT TO CATCH INVALID INPUT AND SEND IT BACK TO FRONTEND
	else {

		//      Process to send message back
                $invalidRegexConnection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
                $invalidRegexChannel = $invalidRegexConnection->channel();


		// Declaring the exchange to send the message
		$invalidRegexChannel->exchange_declare('backend_exchange', 'direct', false, false, false);

		// Routing key address so RabbitMQ knows where to send the message
		$error_key = "frontend";

		if (empty($regexMsg)) {
			$regexMsg['invalidUsername'] = $regexUser;
			$regexMsg['invalidPassword'] = $regexPass;
		}

		$invalidEncodedRegex = json_encode($regexMsg);

		//	Separate Queue to send to frontend
		//$invalidRegexChannel->queue_declare('regexQueue', false, false, false, false);

		//	Getting message ready for delivery
		$invalidRegexMessage = new AMQPMessage(
					$invalidEncodedRegex,
					array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
		);

		// 	Publishing message to frontend via queue
        	$invalidRegexChannel->basic_publish(
					$invalidRegexMessage,
					'backend_exchange',
					$error_key
		);

		// RETURN ARRAY
		echo '[@] REGEX PROTOCOL ACTIVATED [@]', "\nRETURN MESSAGE TO FRONTEND\n";
		print_r($regexMsg);

		$invalidRegexChannel->close();
        	$invalidRegexConnection->close();
	}
};

while (true) {
	try {

		// 	MAIN CHANNEL QUALITY CONTROL
		$channel->basic_qos(null, 1, false);

		//	MAIN CHANNEL TO CONSUME MESSAGES FROM FRONTEND
		$channel->basic_consume('backend_mailbox', '', false, true, false, false, $callback);

		//	TODO: CREATE FUNCTION OR LOOP TO ACTIVE LISTEN FOR MESSAGES FROM FRONTEND

		while(count($channel->callbacks)) {
       			$channel->wait();
			echo 'NO MORE INCOMING MESSAGES', "\n\n";
			break;
		}
	} catch (ErrorException $e) {
     	 		// Handle Error
      			echo "ERROR IN SENDING LOGIN MESSAGE-> " . $e->getMessage();
  	}
}


//	CLOSING MAIN CHANNEL AND CONNECTION
$channel->close();
$connection->close();

?>
