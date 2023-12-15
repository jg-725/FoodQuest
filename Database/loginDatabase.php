<?php

/*	MILESTONE 4: RECEIVING LOGIN MESSAGES FROM BACKEND WITH CLUSTER	*/


require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


// MAIN SECTION TO RECEIVE VALID REGEX MESSAGES FROM BACKEND

//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
$DBLoginConnection = null;
$rabbitNodes = array('192.168.194.2', '192.168.194.1');
$port = 5672;
$user = 'foodquest';
$pass = 'rabbit123';

foreach ($rabbitNodes as $node) {
        try {
                $DBLoginConnection = new AMQPStreamConnection(
                                                $node,
                                                $port,
                                                $user,
                                                $pass
                );
                echo "DATABASE LOGIN CONNECTION TO RABBITMQ CLUSTER WAS SUCCESSFUL @ $node\n\n";
                break;
        } catch (Exception $e) {
                echo "ERROR: RABBITMQ CONNECTION WAS UNSUCCESSFUL @ $node\n";
                continue;
        }
}

if (!$DBLoginConnection) {
        die("RABBITMQ CONNECTION ERROR: DATABASE COULD NOT CONNECT TO ANY RABBITMQ NODE IN CLUSTER.");
}

//      RABBITMQ MESSAGE BROKER SETTINGS TO RECEIVE
$consumeExchange        = 'backend_exchange';   // Exchange Name
$exchangeType           = 'direct';             // Exchange Type
$databaseQueue          = 'DB_login_mailbox';  // Queue Name
$databaseBK             = 'login-database';    // BINDING KEY VALUE MATCHES LOGIN BACKEND ROUTING KEY


//      ACTIVING MAIN CHANNEL FOR BACKEND CONNECTION
$DBLoginChannel = $DBLoginConnection->channel();

//      DECLARING DURABLE EXCHANGE THAT WILL ROUTE MESSAGES FROM FRONTEND
$DBLoginChannel->exchange_declare(
                        $consumeExchange,
                        $exchangeType,
                        false,                  // PASSIVE
                        true,                   // DURABLE
                        false                   // AUTO-DELETE
);


//      USING DURABLE QUEUE FOR WEBSITE: Third parameter is TRUE
$DBLoginChannel->queue_declare(
                $databaseQueue,
                false,          // PASSIVE: check whether an exchange exists without modifying the server state
                true,           // DURABLE: the queue will survive a broker restart
                false,          // EXCLUSIVE: used by only one connection and the queue will be deleted when that connection closes
                false           // AUTO-DELETE: queue is deleted when last consumer unsubscribes
);


// Binding three items together to receive msgs
$DBLoginChannel->queue_bind($databaseQueue, $consumeExchange, $databaseBK);

// Terminal message to signal we are waiting for messages from BACKEND
echo '[*] Waiting for BACKEND LOGIN messages. To exit press CTRL+C', "\n\n";

// CALLBACK RESPONSIBLE FOR PROCESSING INCOMING MESSAGES
$callback = function ($msg) use ($DBLoginChannel) {
    //echo '[+] RECEIVED LOGIN FROM BACKEND', "\n", $msg->getBody(), "\n\n";

    	$loginData = json_decode($msg->getBody(), true);

	print_r($loginData);
	echo '[+] RECEIVED LOGIN FROM BACKEND', "\n", $msg->getBody(), "\n\n";

    //    GETTING VARIABLES SENT FROM BACKEND
    $user = $loginData['username'];
    $pass = $loginData['password'];


    //  	JSON variables to String sanitize
    $stringUser = filter_var($user, FILTER_SANITIZE_STRING);
    $stringPass = filter_var($pass, FILTER_SANITIZE_STRING);

    // VARIABLES TO CONNECT TO MYSQL DATABASE SERVER
    $servername = "192.168.194.3";
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


    	/////////////////////	SENDING USER EXISTS MESSAGE TO FRONTEND	////////////////////////////


	//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
	$userExistsConnection = null;
        $rabbitNodes = array('192.168.194.2', '192.168.194.1');
        $port = 5672;
        $user = 'foodquest';
        $pass = 'rabbit123';


	foreach ($rabbitNodes as $node) {
        	try {
                	$userExistsConnection = new AMQPStreamConnection(
                                                $node,
                                                $port,
                                                $user,
                                                $pass
                	);
                	echo "LOGIN DATABASE CONNECTION TO RABBITMQ CLUSTER WAS SUCCESSFUL @ $node\n";
                	break;
        	} catch (Exception $e) {
			echo "CONNECTION TO RABBITMQ CLUSTER WAS UNSUCCESSFUL @ $node\n";
                	continue;
        	}
	}

	if (!$userExistsConnection) {
        	die("CONNECTION ERROR: DATABASE COULD NOT CONNECT TO ANY RABBITMQ NODE.");
	}

	//      RABBITMQ MESSAGE BROKER SETTINGS TO SEND MESSAGES
        $publishExchange        = 'database_exchange';  // Exchange Name
        $exchangeType           = 'direct';             // Exchange Type
        $userExistsRK           = 'userExists-frontend';   // ROUTING KEY TO DETERMINE DESTINATION

	$userExistsChannel = $userExistsConnection->channel();

        // EXCHANGE THAT WILL ROUTE MESSAGES TO FRONTEND
        $userExistsChannel->exchange_declare(
                        $publishExchange,
                        $exchangeType,
                        false,          // PASSIVE
                        true,           // DURABLE
                        false           // AUTO-DELETE
        );




    // Getting message ready for delivery
    $existsMessage = new AMQPMessage(
    				$encodedExistsMsg,
        			array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
    );


    	// Publishing message to exchange via routing key
    	$userExistsChannel->basic_publish(
				$existsMessage,
				$publishExchange,
				$userExistsRK
	);

    // COMMAND LINE MESSAGE
    echo '[@] MYSQL LOGIN CHECK PROTOCOL EXECUTED [@]\n', "\nRETURN MESSAGE TO FRONTEND\n";

    print_r($existsMsg); // Displaying array on the command line

    $userExistsChannel->close();
    $userExistsConnection->close();
};

//	KEEPING THE QUEUES AND EXCHANGES LOOKING FOR INCOMING MESSAGES
while (true) {
	try {
    		$DBLoginChannel->basic_qos(null, 1, false);
    		$DBLoginChannel->basic_consume($databaseQueue, '', false, true, false, false, $callback);

    		while (count($DBLoginChannel->callbacks)) {
        		$DBLoginChannel->wait();
        		echo 'NO MORE LOGIN MESSAGES FROM BACKEND', "\n\n";
        		break;
    		}
	} catch (ErrorException $e) {
    		// ERROR HANDLING
    		echo "CAUGHT DATABASE ErrorException: " . $e->getMessage();
	}
}
// CLOSING MAIN CHANNEL AND CONNECTION
$DBLoginChannel->close();
$DBLoginConnection->close();

?>



