<?php

/*	TESTING: RECEIVING LOGIN MESSAGES FROM FRONTEND	*/

//	NECESSARY AMQP LIBRARIES FOR PHP
require_once __DIR__ .'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
$connection = null;
$rabbitNodes = array('192.168.194.2', '192.168.194.1');

//      TODO: LOOP THROUGH ARRAY TO SEND DATA TO A WORKING NODE

foreach ($rabbitNodes as $node) {
	try {
		$connection = new AMQPStreamConnection(
						$node,
						5672,
						'foodquest',
						'rabbit123'
		);
		echo "LOGIN BACKEND CONNECTION TO RABBITMQ WAS SUCCESSFUL @ $node\n";
		break;
	} catch (Exception $e) {
		continue;
	}
}

//	CONNECTING TO MAIN RABBIT NODE
//$connection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');

if (!$connection) {
	die("CONNECTION ERROR: COULD NOT CONNECT TO RABBITMQ NODE.");
}


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
$callback = function ($loginData) use ($channel) {
	$userData = json_decode($loginData->getBody(), true);

	echo '[+] RECEIVED LOGIN FROM FRONTEND', "\n", $loginData->getBody(), "\n\n";

	$regexMsg = array();

	$user = $userData['username'];
	$pass = $userData['password'];

	//	JSON to String sanitize
	$stringUser = filter_var($user, FILTER_SANITIZE_STRING);
    	$stringPass = filter_var($pass, FILTER_SANITIZE_STRING);

	$arrayMsg = array();

	//	DELETED IF STATEMENT

		// Command line message
		echo "[+] LOGIN INPUT HAS BEEN SENT TO DATABASE\n";

		//	CREATING ARRAY OF LOGIN
		if (empty($arrayMsg)) {
			$arrayMsg['username'] = $stringUser;
			$arrayMsg['password'] = $stringPass;
		}

		//	ENCODING ARRAY INTO JSON FOR DELIVERY
		$encodedArray = json_encode($arrayMsg);


		$backendLoginConnection = null;
		$rabbitNodes = array('192.168.194.2', '192.168.194.1');

		foreach ($rabbitNodes as $node) {
			try {
				//	CONNECTION TO RABBIT NODE
				$backendLoginConnection = new AMQPStreamConnection(
								$node,
								5672,
								'foodquest',
								'rabbit123'
				);
				echo "BACKEND CONNECTION TO RABBITMQ WAS SUCCESSFUL @ $node\n";
				break;
			} catch (Exception $e) {
				continue;
			}
		}
		if (!$backendLoginConnection) {
        		die("BACKEND CONNECTION ERROR: COULD NOT CONNECT TO RABBITMQ NODE.");
		}

		//	OPENING CHANNEL TO COMMUNITCATE WITH DATABASE
		$backendLoginChannel = $backendLoginConnection->channel();

		//	EXCHANGE THAT WILL ROUTE MESSAGES TO DATABASE
		$backendLoginChannel->exchange_declare('backend_exchange', 'direct', false, false, false);

		//	ROUTING KEY TO DETERMINE DESTINATION
		$routing_key_database = 'database';

		//	Getting message ready for delivery
		$backendMessage = new AMQPMessage(
					$encodedArray,
					array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
		);

		// 	Publishing message to DATABASE VIA BACKEND EXCHANGE
        	$backendLoginChannel->basic_publish(
					$backendMessage,
					'backend_exchange',
					$routing_key_database
		);

		//	COMMAND RESPONSE TO SIGNAL MSG WAS PROCESSES AND SENT
		echo '[@] SENDING PROTOCOL ACTIVATED [@]', "\nMESSAGE TO DATABASE\n";
		print_r($arrayMsg);

		$backendLoginChannel->close();
        	$backendLoginConnection->close();
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
