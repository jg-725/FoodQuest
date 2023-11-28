<?php


/*	TESTING: RECEIVING REGISTER MESSAGE FROM FRONTEND	*/

//	NECESSARY AMQP LIBRARIES FOR PHP
require_once __DIR__ .'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

//$rabbitmqNodes = array('192.168.194.2', '192.168.194.1');


//	CONNECTING TO MAIN RABBIT NODE
$connection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');

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
$callback = function ($userData) use ($channel) {

	echo '[+] RECEIVED REGISTRATION INPUT FROM FRONTEND',"\n\n";
	$registerData = json_decode($userData->getBody(), true);

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
        	$hashedPassword = password_hash($stringPass, PASSWORD_DEFAULT);
        }
        else {
                $passwordConfirm = FALSE;
        }

		// Command line message
		echo "[+] SIGNUP PASSWORD HAS BEEN HASHED";

		$consumeRegister = array();

		//	CREATING ARRAY OF VALID REGEX LOGIN
		if (empty($consumeRegister)) {
			$consumeRegister['username'] = $stringUser;
			$consumeRegister['password'] = $stringPass;
			$consumeRegister['first'] = $stringFirst;
			$consumeRegister['last'] = $stringLast;
			$consumeRegister['email'] = $stringEmail;
			$consumeRegister['address'] = $stringAddress;
			$consumeRegister['phone'] = $stringPhone;
			$consumeRegister['password_confirm'] = $passwordConfirm;
		}

		//	ENCODING ARRAY INTO JSON FOR DELIVERY
		$sendRegister = json_encode($consumeRegister);


		//	CONNECTION TO MAIN RABBIT NODE
		$passwordConnection = new AMQPStreamConnection('192.168.194.2',
					5672,
					'foodquest',
					'rabbit123'
		);

		if (!$passwordConnection) {
        		die("CONNECTION ERROR: COULD NOT CONNECT TO RABBITMQ NODE.");
		}


		//	OPENING CHANNEL TO COMMUNITCATE WITH DATABASE
		$passwordChannel = $passwordConnection->channel();

		//	EXCHANGE THAT WILL ROUTE MESSAGES TO DATABASE
		$passwordChannel->exchange_declare('backend_exchange', 'direct', false, false, false);

		//	ROUTING KEY TO DETERMINE DESTINATION
		$hash_key = 'database';

		//	Getting message ready for delivery
		$passwordMessage = new AMQPMessage(
					$sendRegister,
					array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
		);

		// 	Publishing message to frontend via queue
        	$passwordChannel->basic_publish(
					$passwordMessage,
					'backend_exchange',
					$hash_key
		);

		//	COMMAND RESPONSE TO SIGNAL MSG WAS PROCESSES AND SENT
		echo '[@] HASHING PROTOCOL ACTIVATED [@]', "\nMESSAGE TO DATABASE\n";
		print_r($consumeRegister);

		$passwordChannel->close();
        	$passwordConnection->close();

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

	}
	catch (ErrorException $e) {
        	// Handle Error
        	echo "CAUGHT ERROR: MESSAGE DID NOT GO THROUGH->" . $e->getMessage();
    	}
}
//	CLOSING MAIN CHANNEL AND CONNECTION
$channel->close();
$connection->close();

?>






