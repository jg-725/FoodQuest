<?php

/*	MILESTONE 4: RECEIVING LOGIN MESSAGES FROM BACKEND WITH CLUSTER	*/


require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


// MAIN SECTION TO RECEIVE VALID REGEX MESSAGES FROM BACKEND

//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
$mainConnection = null;
$rabbitNodes = array('192.168.194.2', '192.168.194.1');

foreach ($rabbitNodes as $node) {
	try {
        	$mainConnection = new AMQPStreamConnection(
						$node,
						5672,
						'foodquest',
						'rabbit123'
		);
		echo "LOGIN DATABASE CONNECTION TO RABBITMQ CLUSTER WAS SUCCESSFUL @ $node\n";
		break;
	} catch (Exception $e) {
		echo "CONNECTION ERROR: RABBITMQ CLUSTER LINK WAS UNSUCCESSFUL @ $node\n";
		continue;
	}
}

if (!$mainConnection) {
	die("CONNECTION ERROR: DATABASE COULD NOT CONNECT TO ANY RABBITMQ NODE.");
}


// CONNECTING TO MAIN RABBITMQ
//$connection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');

$mainChannel = $mainConnection->channel();

// EXCHANGE THAT MESSAGES WILL COME FROM
$mainChannel->exchange_declare('backend_exchange', 'direct', false, false, false);

// Using DURABLE QUEUES FOR DELIVERY: Third parameter is TRUE
$mainChannel->queue_declare('database_mailbox', false, true, false, false);

// Binding key
$loginDB = "database";

// Binding three items together to receive msgs
$mainChannel->queue_bind('database_mailbox', 'backend_exchange', $loginDB);

// Terminal message to signal we are waiting for messages from BACKEND
echo '[*] Waiting for BACKEND messages. To exit press CTRL+C', "\n\n";

// CALLBACK RESPONSIBLE FOR PROCESSING INCOMING MESSAGES
$callback = function ($msg) use ($mainChannel) {
    echo '[+] RECEIVED LOGIN FROM BACKEND', "\n", $msg->getBody(), "\n\n";

    $loginData = json_decode($msg->getBody(), true);

	print_r($loginData);

    //    GETTING VARIABLES SENT FROM BACKEND
    $user = $loginData['username'];
    $pass = $loginData['password'];


    //  	JSON variables to String sanitize
    $stringUser = filter_var($user, FILTER_SANITIZE_STRING);
    $stringPass = filter_var($pass, FILTER_SANITIZE_STRING);

    // VARIABLES TO CONNECT TO MYSQL DATABASE SERVER
    $servername = "192.168.194.5";
    $username_db = "test";
    $password_db = "test";
    $dbname = "FoodQuest";

    $conn = mysqli_connect($servername, $username_db, $password_db, $dbname);

    // Check if the connection is successful
    if (!$conn) {
        die("[X] CONNECTION TO MYSQL SERVER FAILED [X]\n" . mysqli_connect_error());
    }

    // Check if the user exists in the database
    $sql_check = "SELECT * FROM Users WHERE BINARY username = '$stringUser'";
    $result = mysqli_query($conn, $sql_check);

    if (mysqli_num_rows($result) > 0) {

        //	CHECKING IF USER EXISTS
        $row = mysqli_fetch_assoc($result);

	echo " [+] ENTERED USER MATCHES WITH DATABASE DATA.\n";

        $userExists = 'TRUE';
        $id = $row['id'];
        $username = $row['username'];
    } else {
        //	COMMAND LINE MESSAGE THAT ENTERED USERNAME DOESN'T MATCH A CURRENT USER
	echo " [-] ENTERED LOGIN DOES NOT MATCH WITH ANY USER IN DATABASE.\n";
        $userExists = 'FALSE';
    }

    // Close the database connection
    mysqli_close($conn);


    	/* GETTING RETURN ARRAY TO SEND TO FRONTEND - RABBITMQ */

    	$existsMsg = [
        	'user_exists' => $userExists,
        	'username' => $username,
		'user_id' => $id,
    	];

    	// GETTING MYSQL MESSAGE READY FOR DELIVERY TO FRONTEND
    	$encodedExistsMsg = json_encode($existsMsg);


    	/* SENDING USER EXISTS MESSAGE TO FRONTEND */

	//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
	$existsConnection = null;
	$rabbitNodes = array('192.168.194.2', '192.168.194.1');


	foreach ($rabbitNodes as $node) {
        	try {
                	$existsConnection = new AMQPStreamConnection(
                                                $node,
                                                5672,
                                                'foodquest',
                                                'rabbit123'
                	);
                	echo "LOGIN DATABASE CONNECTION TO RABBITMQ CLUSTER WAS SUCCESSFUL @ $node\n";
                	break;
        	} catch (Exception $e) {
			echo "CONNECTION TO RABBITMQ CLUSTER WAS UNSUCCESSFUL @ $node\n";
                	continue;
        	}
	}

	if (!$existsConnection) {
        	die("CONNECTION ERROR: DATABASE COULD NOT CONNECT TO ANY RABBITMQ NODE.");
	}

    	//$existsConnection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');

    	$existsChannel = $existsConnection->channel();


    // EXCHANGE THAT WILL ROUTE MESSAGES TO FRONTEND
    $existsChannel->exchange_declare('database_exchange', 'direct', false, false, false);

    // ROUTING KEY TO DETERMINE DESTINATION
    $exists_key = 'frontend';

    // Getting message ready for delivery
    $existsMessage = new AMQPMessage(
    				$encodedExistsMsg,
        			array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
    );

    // Publishing message to exchange via routing key
    $existsChannel->basic_publish($existsMessage, 'database_exchange', $exists_key);

    // COMMAND LINE MESSAGE
    echo '[@] MYSQL LOGIN CHECK PROTOCOL EXECUTED [@]\n', "\nRETURN MESSAGE TO FRONTEND\n";

    print_r($existsMsg); // Displaying array on the command line

    $existsChannel->close();
    $existsConnection->close();
};

//	KEEPING THE QUEUES AND EXCHANGES LOOKING FOR INCOMING MESSAGES
while (true) {
	try {
    		$mainChannel->basic_qos(null, 1, false);
    		$mainChannel->basic_consume('database_mailbox', '', false, true, false, false, $callback);

    		while (count($mainChannel->callbacks)) {
        		$mainChannel->wait();
        		echo 'NO MORE LOGIN MESSAGES FROM BACKEND', "\n\n";
        		break;
    		}
	} catch (ErrorException $e) {
    		// ERROR HANDLING
    		echo "CAUGHT DATABASE ErrorException: " . $e->getMessage();
	}
}
// CLOSING MAIN CHANNEL AND CONNECTION
$mainChannel->close();
$mainConnection->close();

?>



