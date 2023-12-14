<?php


/*	MILESTONE 4: RECEIVING REGISTER MESSAGE FROM FRONTEND	*/

//	NECESSARY AMQP LIBRARIES FOR PHP
require_once __DIR__ .'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
$mainBackendConnection = null;
$rabbitNodes = array('192.168.194.2', '192.168.194.1');
$port = 5672;
$user = 'foodquest';
$pass = 'rabbit123';

foreach ($rabbitNodes as $node) {
	try {
        	$mainBackendConnection = new AMQPStreamConnection(
						$node,
						$port,
						$user,
						$pass
		);
		echo "BACKEND SIGNUP CONNECTION TO RABBITMQ CLUSTER WAS SUCCESSFUL @ $node\n";
		break;
	} catch (Exception $e) {
		continue;
	}
}

//	CONNECTING TO MAIN RABBIT NODE
//$connection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');

if (!$mainBackendConnection) {
	die("CONNECTION ERROR: COULD NOT CONNECT TO ANY RABBITMQ NODE IN CLUSTER.");
}

//      RABBITMQ MESSAGE BROKER SETTINGS
$consumerExchange 	= 'frontend_exchange';	// Exchange Name
$exchangeType 		= 'direct';		// Exchange Type
$backendQueue	 	= 'hash_mailbox';	// Queue Name
$hashBK   		= 'signup-backend';	// BINDING KEY MATCHES SIGNUP ROUTING KEY


//	ACTIVING MAIN CHANNEL FOR BACKEND CONNECTION
$mainBackendChannel = $mainBackendConnection->channel();

//	DECLARING DURABLE EXCHANGE THAT WILL ROUTE MESSAGES FROM FRONTEND
$mainBackendChannel->exchange_declare(
			$consumerExchange,
			$exchangeType,
			false,			// PASSIVE
			true,			// DURABLE
			false			// AUTO-DELETE
);

//	USING DURABLE QUEUE FOR WEBSITE: Third parameter is TRUE
$mainBackendChannel->queue_declare(
		$backendQueue,
		false,		// PASSIVE: check whether an exchange exists without modifying the server state
		true,		// DURABLE: the queue will survive a broker restart
		false,		// EXCLUSIVE: used by only one connection and the queue will be deleted when that connection closes
		false		// AUTO-DELETE: queue is deleted when last consumer unsubscribes
);

// Binding three items together to receive msgs
$mainBackendChannel->queue_bind($backendQueue, $consumerExchange, $hashBK);


// Terminal message to signal we are waiting for messages from frontend
echo '[*] WAITING FOR FRONTEND TO SIGNUP DATA. To exit press CTRL+C', "\n\n";

//	CALLBACK RESPONSIBLE OF PROCESSESSING VALID AND INVALID USER REQUESTS
$hashCallback = function ($signupRequest) use ($mainBackendChannel) {

	echo '[+] RECEIVED USER SIGNUP REQUEST FROM FRONTEND',"\n\n";
	$registerData = json_decode($signupRequest->getBody(), true);

	//	GETTING SENDER VARIABLES

	$user = $registerData['username'];
	$pass = $registerData['password'];
	$confirm = $registerData['confirm'];
	$first = $registerData['first'];
	$last = $registerData['last'];
	$email = $registerData['email'];
	$address = $registerData['address'];
	$phoneNum = $registerData['phone'];

	//	JSON to String sanitize

	$stringUser = filter_var($user, FILTER_SANITIZE_STRING);
    	$stringPass = filter_var($pass, FILTER_SANITIZE_STRING);
	$stringConfirm = filter_var($confirm, FILTER_SANITIZE_STRING);
	$stringFirst = filter_var($first, FILTER_SANITIZE_STRING);
        $stringLast = filter_var($last, FILTER_SANITIZE_STRING);
	$stringEmail = filter_var($email, FILTER_SANITIZE_STRING);
	$stringAddress = filter_var($address, FILTER_SANITIZE_STRING);
	$stringPhone = filter_var($phoneNum, FILTER_SANITIZE_STRING);

	/*	TODO: IMPLEMENT PASSWORD HASH BEFORE SENDING TO DATABASE	*/

	//	CHECKING IF PASSWORDS ARE EQUAL
        if ($stringConfirm == $stringPass) {
                $passwordConfirm = TRUE;
		//      Hash the password using the default algorithm (currently bcrypt)
        	$hashPassword = password_hash($stringPass, PASSWORD_DEFAULT);
        }
        else {
                $passwordConfirm = FALSE;
        }

		// Command line message
		echo "[+] PASSWORD HAS BEEN HASHED FOR SECURITY\n";

		$consumeRegister = array();

		//	CREATING ARRAY THAT SENDS HASHED PASSWORD
		if (empty($consumeRegister)) {

			$consumeRegister['username'] = $stringUser;
			$consumeRegister['password'] = $stringPass;
			$consumeRegister['hash'] = $hashPassword;
			$consumeRegister['first'] = $stringFirst;
			$consumeRegister['last'] = $stringLast;
			$consumeRegister['email'] = $stringEmail;
			$consumeRegister['address'] = $stringAddress;
			$consumeRegister['phone'] = $stringPhone;
			$consumeRegister['password_confirm'] = $passwordConfirm;
		}

		//	ENCODING ARRAY INTO JSON FOR DELIVERY
		$hashArray = json_encode($consumeRegister);


		////////	PROCESS TO CONNECT TO RABBITMQ		*/


		//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION

		$hashConnection = null;
		$rabbitNodes = array('192.168.194.2', '192.168.194.1');
		$port = 5672;
                $user = 'foodquest';
                $pass = 'rabbit123';

		foreach ($rabbitNodes as $node) {
			try {
            			$hashConnection = new AMQPStreamConnection(
								$node,
								$port,
								$user,
								$pass
				);
				echo "BACKEND PROTOCOL CONNECTION TO RABBITMQ CLUSTER WAS SUCCESSFUL @ $node\n";
				break;
			} catch (Exception $e) {
				continue;
			}

		}

		//	CONNECTION TO MAIN RABBIT NODE - PRE CLUSTER
		//$passwordConnection = new AMQPStreamConnection('192.168.194.2',5672,'foodquest','rabbit123');

		if (!$hashConnection) {
        		die("CONNECTION ERROR: COULD NOT CONNECT TO ANY RABBITMQ NODE IN CLUSTER.");
		}

		//      RABBITMQ MESSAGE BROKER SETTINGS TO SEND MESSAGES
		$publishExchange 	= 'backend_exchange';	// Exchange Name
                $exchangeType		= 'direct';		// Exchange Type
                $hashRK 		= 'hash-database';	// ROUTING KEY TO DETERMINE DESTINATION

		//	OPENING CHANNEL TO COMMUNITCATE WITH DATABASE
		$hashChannel = $hashConnection->channel();

		//	EXCHANGE THAT WILL ROUTE MESSAGES TO DATABASE
		$hashChannel->exchange_declare(
			$publishExchange,
			$exchangeType,
			false,		// PASSIVE
			true,		// DURABLE
			false		// AUTO-DELETE
		);

		//	Getting message ready for delivery
		$hashedMessage = new AMQPMessage(
					$hashArray,
					array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
		);

		// 	Publishing message to frontend via queue
        	$hashChannel->basic_publish(
					$hashedMessage,
					$publishExchange,
					$hashRK
		);

		//	COMMAND RESPONSE TO SIGNAL MSG WAS PROCESSES AND SENT
		echo '[@] HASHING PROTOCOL ACTIVATED [@]', "\nMESSAGE TO DATABASE\n";
		print_r($consumeRegister);

		$hashChannel->close();
        	$hashConnection->close();

};

while (true) {

	try {
		// 	MAIN CHANNEL QUALITY CONTROL
		$mainBackendChannel->basic_qos(null, 1, false);

		//	MAIN CHANNEL TO CONSUME MESSAGES FROM FRONTEND
		$mainBackendChannel->basic_consume($backendQueue, '', false, true, false, false, $callback);

		//	TODO: CREATE FUNCTION OR LOOP TO ACTIVE LISTEN FOR MESSAGES FROM FRONTEND

		while (count($mainBackendChannel->callbacks)) {
       			$mainBackendChannel->wait();
			echo 'NO MORE INCOMING MESSAGES', "\n\n";
			break;
		}

	}
	catch (ErrorException $e) {
        	// Handle Error
        	echo "CAUGHT ERROR: MESSAGE DID NOT GO THROUGH->" . $e->getMessage();
    	}
}
//	CLOSING MAIN CHANNEL AND CONNECTION
$mainBackendChannel->close();
$mainBackendConnection->close();

?>






