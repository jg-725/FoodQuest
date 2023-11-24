<?php


/*	TESTING: RECEIVING REGISTER MESSAGE FROM FRONTEND	*/

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

	/*	TODO: IMPLEMENT REGEX FOR LOGIN INPUT BEFORE SENDING TO DATABASE	*/

	//      CREATING INVALID ARRAY TO CATCH INVALID REGEX INPUT
        //$invalidRegex = array();

	//	USERNAME REGEX
	if (preg_match('/^[a-zA-Z0-9_]+$/', $stringUser)) {
		$regexUser = TRUE;
	}
	else {
		$regexUser = "FALSE";
		//$invalidRegex['invalidUser'] = $stringUser;
		//$regexUser = "Invalid Username";
	}

	//	PASSWORD REGEX
	$strong_password = "/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/";

	if (preg_match($strong_password, $stringPass)) {
		//$regexPass = "Password meets criteria";
		$regexPass = TRUE;
	}
	else {
		$regexPass = "FALSE";
		//$invalidRegex['invalidPassword'] = $stringPass;
		//$regexPass = "Error: Password DOES NOT meet criteria";
	}

	//	CONFIRM PASSWORD VERIFICATION
	if ($stringConfirm == $stringPass) {
		$passwordConfirm = TRUE;
	}
	else {
		$passwordConfirm = "FALSE";
	}

	//      FIRST NAME REGEX
        if (preg_match('/^[a-zA-Z]+$/', $stringFirst)) {
                $regexFirst = TRUE;
        }
        else {
                $regexFirst = "FALSE";
		//$invalidRegex['invalidFirst'] = $stringFirst;
                //$regexUser = "Invalid Username";
        }

	//      LAST NAME REGEX
        if (preg_match('/^[a-zA-Z]+$/', $stringLast)) {
                $regexLast = TRUE;
        }
        else {
                $regexLast = "FALSE";
		//$invalidRegex['invalidLast'] = $stringLast;
        }

	//      EMAIL REGEX

	// testing = "/^[a-z0-9!#$%&'*+\\/=?^_`{|}~-]+(?:\\.[a-z0-9!#$%&'*+\\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/";
	$email_validation = '/^\\S+@\\S+\\.\\S+$/';

        if (preg_match($email_validation, $stringEmail)) {
                $regexEmail = TRUE;
        }
        else {
                $regexEmail = "FALSE";
		//$invalidRegex['invalidEmail'] = $stringEmail;
        }

	//	SIMPLE ADDRESS REGEX
	$valid_address_regex = "/^(\\d{1,}) [a-zA-Z0-9\\s]+(\\,)? [a-zA-Z]+(\\,)? [A-Z]{2} [0-9]{5,6}$/";
	if (preg_match($valid_address_regex, $stringAddress)) {
		$regexAddress = TRUE;
	}
	else {
		$regexAddress = "FALSE";
		//$invalidRegex['invalidAddress'] = $stringAddress;
	}

	//	PHONE REGEX
	$valid_phone_regex = "/^\\+?\\d{1,4}?[-.\\s]?\\(?\\d{1,3}?\\)?[-.\\s]?\\d{1,4}[-.\\s]?\\d{1,4}[-.\\s]?\\d{1,9}$/";
	if (preg_match($valid_phone_regex, $stringPhone)) {
		$regexPhone = TRUE;
	}
	else {
		$regexPhone = "FALSE";
		//$invalidRegex['invalidPhone'] = $stringPhone;
	}

	//	IF STATEMENT TO SEND VALID INPUT TO DATABASE

	if ($regexUser == TRUE && $regexPass == TRUE && $regexFirst == TRUE && $regexLast == TRUE && $regexEmail == TRUE && $regexAddress == TRUE && $regexPhone == TRUE && $passwordConfirm == TRUE) {
		// Command line message
		echo "[+] LOGIN INPUT MEETS SITE REQUIREMENTS";

		$regexRegister = array();
		//	CREATING ARRAY OF VALID REGEX LOGIN
		if (empty($regexRegister)) {
			$regexRegister['username'] = $stringUser;
			$regexRegister['password'] = $stringPass;
			$regexRegister['first'] = $stringFirst;
			$regexRegister['last'] = $stringLast;
			$regexRegister['email'] = $stringEmail;
			$regexRegister['address'] = $stringAddress;
			$regexRegister['phone'] = $stringPhone;
		}

		//	ENCODING ARRAY INTO JSON FOR DELIVERY
		$validRegexArray = json_encode($regexRegister);

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
		print_r($regexRegister);

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

		$invalidRegex = array();

		if (empty($invalidRegex)) {
			$invalidRegex['invalidSignup'] = 'FALSE';
		}

		$invalidEncodedRegex = json_encode($invalidRegex);

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
		print_r($invalidRegex);

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






