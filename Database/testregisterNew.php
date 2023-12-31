<?php

/*      TESTING: RECEIVING VALID REGEX REGISTER INPUT FROM BACKEND       */

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

//	SECTION TO RECEIVE MESSAGES FOR PROCESSING

//	CONNECTING TO MAIN RABBITMQ
$connectionDB = new AMQPStreamConnection('192.168.194.2',
					5672,
					'foodquest',
					'rabbit123');
$channelDB = $connectionDB->channel();

$channelDB->exchange_declare('backend_exchange', 'direct', false, false, false);

//	Using NON DURABLE QUEUES: Third parameter is false
$channelDB->queue_declare('database_mailbox', false, false, false, false);

// Binding key
$bindingKeyDB = "database";

// Binding three items together to receive msgs
$channelDB->queue_bind('database_mailbox', 'backend_exchange', $bindingKeyDB);

// Terminal message to signal we are waiting for messages from BACKEND
echo '[*] Waiting for BACKEND messages. To exit press CTRL+C', "\n\n";


$callbackDB = function ($msg) use ($channelDB) {
	echo '[+] RECEIVED VALID REGEX REGISTER INPUT FROM BACKEND', "\n", $msg->getBody(), "\n\n";

	$validSignupRegex = json_decode($msg->getBody(), true);

	//	GETTING VARIABLES SENT FROM BACKEND
	$user  = $validSignupRegex['username'];
	$pass  = $validSignupRegex['password'];
	$first = $validSignupRegex['first'];
	$last  = $validSignupRegex['last'];
	$email = $validSignupRegex['email'];

	//      JSON to String sanitize
        $stringUser = filter_var($user, FILTER_SANITIZE_STRING);
        $stringPass = filter_var($pass, FILTER_SANITIZE_STRING);
        $stringFirst = filter_var($first, FILTER_SANITIZE_STRING);
        $stringLast = filter_var($last, FILTER_SANITIZE_STRING);
        $stringEmail = filter_var($email, FILTER_SANITIZE_STRING);

	/*	MYSQL CODE	*/


// Connect to the database

//$servername = "192.168.194.3";
$servername = "localhost";
$username_db = "John";
$password_db = "Pass123";
$dbname = "FoodQuest";

$conn = mysqli_connect($servername, $username_db, $password_db, $dbname);

// Check if the connection is successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


// Check if the user exists in the database
$sql_check = "SELECT * FROM Users WHERE BINARY username = '$user'";
$result = mysqli_query($conn, $sql_check);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$userFound = false;
$hash = null;
$id = null;

if (mysqli_num_rows($result) > 0) {
    // User exists, retrieve the password
    $row = mysqli_fetch_assoc($result);
    $hash = $row['password'];
    $userFound = true;
    $id = $row['id'];
}



/*
// Check if the user already exists in the database
$sql_check = "SELECT * FROM Users WHERE username = '$username' OR email = '$email'";
$result = mysqli_query($conn, $sql_check);

if (mysqli_num_rows($result) > 0) {
    // User already exists
    echo "User already exists in the database.\n";
    $newUser = FALSE;
} else {
    // User does not exist
    // Insert the user data into the database
    $sql = "INSERT INTO Users (username, password, firstname, lastname, email) VALUES ('$stringUser', '$stringPass', '$stringFirst', '$stringLast', '$stringEmail')";

    if (mysqli_query($conn, $sql)) {
        echo "New record created successfully\n";
        $newUser = TRUE;
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}
*/


	/*
	// Function to send a message
	function send_msg($sender, $message) {
    		global $conn;
    		if (!empty($sender) && !empty($message)) {
        		$sender = mysqli_real_escape_string($conn, $sender);
        		$message = mysqli_real_escape_string($conn, $message);
        		$query = "INSERT INTO chat VALUES(null, '$sender', '$message')";
        		$run = mysqli_query($conn, $query);
        		return $run ? true : false;
    		}
		else {
        		return false;
    		}

	}
	*/
	// Close the database connection
	mysqli_close($conn);

	// Assuming you have a valid $connection for the second part of your code
global $connection;

$query = "SELECT * FROM Users ORDER BY Msg_ID DESC";
$run = mysqli_query($connection, $query);

if (!$run) {
    die("Query failed: " . mysqli_error($connection));
}

$messages = array();
while ($message = mysqli_fetch_assoc($run)) {
    $messages[] = array('sender' => $message['Sender'], 'message' => $message['Message']);
}

return $messages;



	/*	GETTING FRONTEND MESSAGE READY - RABBITMQ	*/

	//	ARRAY TO STORE MESSAGE
	$returnMsg = array();

	if ($newUser == TRUE) {
		if (empty($returnMsg)) {        // Check if array is empty
                	$returnMsg['newUser'] = $newUser;

        	}

	}
	else {
		if (empty($returnMsg)) {        // Check if array is empty
                	$returnMsg['newUser'] = $newUser;
                	//$returnMsg['newUser'] = $userCondition;
                	//$send['password'] = $password;
        	}
	}


	//	GETTING MYSQL MESSAGE READY FOR DELIVERY TO FRONTEND
	$encodedMsg = json_encode($returnMsg);

	//	Process to send message back to FRONTEND
	$existsConnection = new AMQPStreamConnection('192.168.194.2',
						5672,
						'foodquest',
						'rabbit123'
	);
	$existsChannel = $existsConnection->channel();

	//	EXCHANGE THAT WILL ROUTE DATABASE MESSAGES
	$existsChannel->exchange_declare('database_exchange',
					false,
					false,
					false,
					false
	);

	//	Routing key address so RabbitMQ knows where to send the message
	$returnToFrontend = "frontend";

	//	Getting message ready for delivery
	$existsMessage = new AMQPMessage($encodedMsg,
			array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
	);

	// 	Publishing message to frontend via queue
        $existsChannel->basic_publish($existsMessage,
				'database_exchange',
				$returnToFrontend
	);

	//	COMMAND LINE MESSAGE
	echo '[@] MYSQL CHECK PROTOCOL ACTIVATED [@]', "\nRETURN MESSAGE TO FRONTEND\n";

	print_r($returnMsg);	//Displaying array in command line

	//	CLOSING CHANNEL AND CONNECTION TALKING TO FRONTEND
	$existsChannel->close();
        $existsConnection->close();
};


$channelDB->basic_qos(null, 1, false);
$channelDB->basic_consume('database_mailbox', '', false, true, false, false, $callbackDB);

while(count($channelDB->callbacks)) {
       	$channelDB->wait();
	echo 'NO MORE INCOMING MESSAGES FROM BACKEND', "\n\n";
	break;
}

//	Closing MAIN channel and connection
$channelDB->close();
$connectionDB->close();

?>
