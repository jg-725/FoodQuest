<?php

/* TESTING: RECEIVING REGISTER INPUT FROM BACKEND */

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// SECTION TO RECEIVE MESSAGES FOR PROCESSING

// CONNECTING TO MAIN RABBITMQ
$connectionDB = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
$channelDB = $connectionDB->channel();

$channelDB->exchange_declare('backend_exchange', 'direct', false, false, false);

// Using DURABLE QUEUES: Third parameter is true
$channelDB->queue_declare('database_mailbox', false, true, false, false);

// Binding key
$bindingKeyDB = "database";

// Binding three items together to receive msgs
$channelDB->queue_bind('database_mailbox', 'backend_exchange', $bindingKeyDB);

// Terminal message to signal we are waiting for messages from BACKEND
echo '[*] Waiting for BACKEND messages. To exit press CTRL+C', "\n\n";

$callbackDB = function ($msg) use ($channelDB) {
    echo '[+] RECEIVED HASHED PASSWORD FROM BACKEND', "\n", $msg->getBody(), "\n\n";

    $backendMsg = json_decode($msg->getBody(), true);

    // GETTING VARIABLES SENT FROM BACKEND
    $validUser = filter_var($backendMsg['username'], FILTER_SANITIZE_STRING);
    $validPass = filter_var($backendMsg['password'], FILTER_SANITIZE_STRING);
    $validFirst = filter_var($backendMsg['first'], FILTER_SANITIZE_STRING);
    $validLast = filter_var($backendMsg['last'], FILTER_SANITIZE_STRING);
    $validEmail = filter_var($backendMsg['email'], FILTER_SANITIZE_STRING);
    $validAddress = filter_var($backendMsg['address'], FILTER_SANITIZE_STRING);
    $validPhone = filter_var($backendMsg['phone'], FILTER_SANITIZE_STRING);


    // CHECKING IF NEW USER DATA EXISTS
    
        /* MYSQL CODE */
        // Connect to the database
        $servername = "192.168.194.3";
        $username_db = "test";
        $password_db = "test";
        $dbname = "FoodQuest";

        $conn = mysqli_connect($servername, $username_db, $password_db, $dbname);

        // Check if the connection is successful
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        // Check if the user already exists in the database
        $sql_check = "SELECT * FROM Users WHERE username = '$validUser' OR email = '$validEmail'";
        $result = mysqli_query($conn, $sql_check);

        if (mysqli_num_rows($result) > 0) {
            // User already exists
            echo "ENTERED USER ALREADY EXISTS IN FOODQUEST DATABASE.\n";
            $newUser = 'FALSE';
        } else {
            // User does not exist
            // Insert the user data into the database
            $sql = "INSERT INTO Users (username, password, fname, lname, email, address, phonumber) VALUES ('$validUser', '$validPass', '$validFirst', '$validLast', '$validEmail', '$validAddress', '$validPhone')";

            if (mysqli_query($conn, $sql)) {
                echo "NEW USER WAS SUCCESSFULLY REGISTERED INTO FOODQUEST DATABASE\n";
                $newUser = 'TRUE';
            } else {
                echo "FOODQUEST DATABASE ERROR: " . $sql . "<br>" . mysqli_error($conn);
            }
        }

        // Close the database connection
        mysqli_close($conn);

        /* PROCESS TO SEND USER EXISTS MESSAGE TO FRONTEND - RABBITMQ */
        // ESTABLISHING CONNECTION
        $existsConnection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
        $existsChannel = $existsConnection->channel();

        // EXCHANGE THAT WILL ROUTE DATABASE MESSAGES
        $existsChannel->exchange_declare('database_exchange', 'direct', false, false, false);

        // Routing key address so RabbitMQ knows where to send the message
        $returnToFrontend = "frontend";

        // ARRAY TO STORE MESSAGE
        $returnMsg = [
            'valid_signup' => 'TRUE',
            'new_user' => $newUser,
        ];

        // GETTING MYSQL MESSAGE READY FOR DELIVERY TO FRONTEND
        $encodedMsg = json_encode($returnMsg);

        // Getting message ready for delivery
        $existsMessage = new AMQPMessage($encodedMsg, array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));

        // Publishing message to FRONTEND via queue
        $existsChannel->basic_publish($existsMessage, 'database_exchange', $returnToFrontend);

        // Command line message
        echo '[@] REGEX CHECK PROTOCOLS ACTIVATED [@]', "\nRETURN MESSAGE TO FRONTEND\n";

        print_r($returnMsg); // Displaying array in command line

        // CLOSING CHANNEL AND CONNECTION TALKING TO FRONTEND
        $existsChannel->close();
        $existsConnection->close();
    
};

while (true) {
	try {
		$channelDB->basic_qos(null, 1, false);
		$channelDB->basic_consume('database_mailbox', '', false, true, false, false, $callbackDB);

		while(count($channelDB->callbacks)) {
       			$channelDB->wait();
			echo 'NO MORE INCOMING MESSAGES FROM BACKEND', "\n\n";
			break;
		}
	}
	catch (ErrorException $e) {
        	// Handle Error
        	echo "ErrorException CAUGHT AT: " . $e->getMessage();
    	}
}

//	Closing MAIN channel and connection
$channelDB->close();
$connectionDB->close();

?>
