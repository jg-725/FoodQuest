<?php

/*	MILESTONE 4: RECEIVING LOGIN MESSAGES FROM FRONTEND	*/

//	NECESSARY AMQP LIBRARIES FOR PHP
require_once __DIR__ .'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
$loginBackendConnection = null;
$rabbitNodes = array('192.168.194.2', '192.168.194.1');
$port = 5672;
$user = 'foodquest';
$pass = 'rabbit123';

//      TODO: LOOP THROUGH ARRAY TO SEND DATA TO A WORKING NODE

foreach ($rabbitNodes as $node) {
	try {
		$loginBackendConnection = new AMQPStreamConnection(
						$node,
						$port,
						$user,
						$pass
		);
		echo "BACKEND LOGIN CONNECTION TO RABBITMQ CLUSTER WAS SUCCESSFUL @ $node\n\n";
		break;
	} catch (Exception $e) {
		continue;
	}
}

//	CONNECTING TO MAIN RABBIT NODE
//$connection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');

if (!$loginBackendConnection) {
	die("CONNECTION ERROR: COULD NOT CONNECT TO ANY RABBITMQ NODE IN CLUSTER.");
}


//      RABBITMQ MESSAGE BROKER SETTINGS
$consumerExchange 	= 'frontend_exchange';	// Exchange Name
$exchangeType 		= 'direct';		// Exchange Type
$backendQueue	 	= 'BE_login_mailbox';	// Queue Name
$backendBK   		= 'login-backend';	// BINDING KEY MATCHES SIGNUP ROUTING KEY


//	ACTIVING MAIN CHANNEL FOR BACKEND CONNECTION
$loginBackendChannel = $loginBackendConnection->channel();

//	DECLARING DURABLE EXCHANGE THAT WILL ROUTE MESSAGES FROM FRONTEND
$loginBackendChannel->exchange_declare(
			$consumerExchange,
			$exchangeType,
			false,			// PASSIVE
			true,			// DURABLE
			false			// AUTO-DELETE
);


//	USING DURABLE QUEUE FOR WEBSITE: Third parameter is TRUE
$loginBackendChannel->queue_declare(
		$backendQueue,
		false,		// PASSIVE: check whether an exchange exists without modifying the server state
		true,		// DURABLE: the queue will survive a broker restart
		false,		// EXCLUSIVE: used by only one connection and the queue will be deleted when that connection closes
		false		// AUTO-DELETE: queue is deleted when last consumer unsubscribes
);


// 	Binding three items together to receive msgs
$loginBackendChannel->queue_bind($backendQueue, $consumerExchange, $backendBK);

// Terminal message to signal we are waiting for messages from frontend
echo '[*] WAITING FOR FRONTEND TO SEND LOGIN DATA. To exit press CTRL+C', "\n\n";


//	CALLBACK RESPONSIBLE OF PROCESSESSING VALID AND INVALID USER REQUESTS
$callback = function ($loginData) use ($loginBackendChannel) {

	$userData = json_decode($loginData->getBody(), true);
	echo '[+] RECEIVED LOGIN FROM FRONTEND', "\n", $loginData->getBody(), "\n\n";

	//	UNLOADING LOGIN VARIABLES
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


		//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION

		$sendLoginConnection = null;
		$rabbitNodes = array('192.168.194.2', '192.168.194.1');
		$port = 5672;
                $user = 'foodquest';
                $pass = 'rabbit123';

		foreach ($rabbitNodes as $node) {
			try {
            			$sendLoginConnection = new AMQPStreamConnection(
								$node,
								$port,
								$user,
								$pass
				);
				echo "BACKEND CONNECTION TO RABBITMQ CLUSTER WAS SUCCESSFUL @ $node\n\n";
				break;
			} catch (Exception $e) {
				continue;
			}

		}

		if (!$sendLoginConnection) {
        		die("BACKEND CONNECTION ERROR: COULD NOT CONNECT TO RABBITMQ NODE.");
		}

		//      RABBITMQ MESSAGE BROKER SETTINGS TO SEND MESSAGES
		$publishExchange 	= 'backend_exchange';	// Exchange Name
                $exchangeType		= 'direct';		// Exchange Type
                $loginRK 		= 'login-database';	// ROUTING KEY TO DETERMINE DESTINATION


		//	OPENING CHANNEL TO COMMUNITCATE WITH DATABASE
		$sendLoginChannel = $sendLoginConnection->channel();

		//	EXCHANGE THAT WILL ROUTE MESSAGES TO DATABASE
		$sendLoginChannel->exchange_declare(
			$publishExchange,
			$exchangeType,
			false,		// PASSIVE
			true,		// DURABLE
			false		// AUTO-DELETE
		);


		//	Getting message ready for delivery
		$loginMessage = new AMQPMessage(
					$encodedArray,
					array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
		);

		// 	Publishing message to DATABASE VIA BACKEND EXCHANGE
        	$sendLoginChannel->basic_publish(
					$loginMessage,
					$publishExchange,
					$loginRK
		);

		//	COMMAND RESPONSE TO SIGNAL MSG WAS PROCESSES AND SENT
		echo '[@] SENDING PROTOCOL ACTIVATED [@]', "\nMESSAGE TO DATABASE\n";
		print_r($arrayMsg);

		$sendLoginChannel->close();
        	$sendLoginConnection->close();
};

while (true) {
	try {

		// 	MAIN CHANNEL QUALITY CONTROL
		$loginBackendChannel->basic_qos(null, 1, false);

		//	MAIN CHANNEL TO CONSUME MESSAGES FROM FRONTEND
		$loginBackendChannel->basic_consume($backendQueue, '', false, true, false, false, $callback);

		//	TODO: CREATE FUNCTION OR LOOP TO ACTIVE LISTEN FOR MESSAGES FROM FRONTEND

		while(count($loginBackendChannel->callbacks)) {
       			$loginBackendChannel->wait();
			echo 'NO MORE INCOMING MESSAGES', "\n\n";
			break;
		}
	} catch (ErrorException $e) {
     	 		// Handle Error
      			echo "ERROR IN SENDING LOGIN MESSAGE-> " . $e->getMessage();
  	}
}


//	CLOSING MAIN CHANNEL AND CONNECTION
$loginBackendChannel->close();
$loginBackendConnection->close();

?>
