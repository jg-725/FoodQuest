<?php

/* TESTING: RECEIVING VALID REGEX REGISTER INPUT FROM BACKEND */

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

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

    print_r($backendMsg);

    $validUser   = $backendMsg['username'];
    $validPass   = $backendMsg['password'];
    $validFirst  = $backendMsg['first'];
    $validLast   = $backendMsg['last'];
    $validEmail  = $backendMsg['email'];
    $validAddress = $backendMsg['address'];
    $validPhone  = $backendMsg['phone'];

    $stringUser = filter_var($validUser, FILTER_SANITIZE_STRING);
    $stringPass = filter_var($validPass, FILTER_SANITIZE_STRING);
    $stringFirst = filter_var($validFirst, FILTER_SANITIZE_STRING);
    $stringLast = filter_var($validLast, FILTER_SANITIZE_STRING);
    $stringEmail = filter_var($validEmail, FILTER_SANITIZE_STRING);
    $stringAddress = filter_var($validAddress, FILTER_SANITIZE_STRING);
    $stringPhone = filter_var($validPhone, FILTER_SANITIZE_STRING);

    $regexUser = preg_match('/^[a-zA-Z0-9_]+$/', $stringUser);
    $strong_password = "/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/";
    $regexPass = preg_match($strong_password, $stringPass);
    $regexFirst = preg_match('/^[a-zA-Z]+$/', $stringFirst);
    $regexLast = preg_match('/^[a-zA-Z]+$/', $stringLast);
    $strong_email = "/^[a-z0-9!#$%&'*+\\/=?^_`{|}~-]+(?:\\.[a-z0-9!#$%&'*+\\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/";
    $regexEmail = preg_match($strong_email, $stringEmail);
    $valid_address_regex = "/^(\\d{1,}) [a-zA-Z0-9\\s]+(\\,)? [a-zA-Z]+(\\,)? [A-Z]{2} [0-9]{5,6}$/";
    $regexAddress = preg_match($valid_address_regex, $stringAddress);
    $valid_phone_regex = "/^\\+?\\d{1,4}?[-.\\s]?\\(?\\d{1,3}?\\)?[-.\\s]?\\d{1,4}[-.\\s]?\\d{1,4}[-.\\s]?\\d{1,9}$/";
    $regexPhone = preg_match($valid_phone_regex, $stringPhone);

    if ($regexUser == TRUE && $regexPass == TRUE && $regexFirst == TRUE && $regexLast == TRUE && $regexEmail == TRUE && $regexAddress == TRUE && $regexPhone == TRUE) {

        // MYSQL CODE
        $servername = "192.168.194.3";
        $username_db = "test";
        $password_db = "test";
        $dbname = "FoodQuest";

        $conn = mysqli_connect($servername, $username_db, $password_db, $dbname);

        if (!$conn) {
            die("[X] CONNECTION TO MYSQL SERVER FAILED [X]" . mysqli_connect_error());
        }

        $sql_check = "SELECT * FROM Users WHERE username = '$stringUser' OR email = '$stringEmail'";
        $result = mysqli_query($conn, $sql_check);

        if (mysqli_num_rows($result) > 0) {
            echo " [-] ENTERED USER ALREADY EXISTS IN FOODQUEST DATABASE.\n";
            $newUser = 'FALSE';
            $validSignup = 'TRUE';
        } else {
            $sql = "INSERT INTO Users (username, password, fname, lname, email, address, phonumber) VALUES ('$validUser', '$validPass', '$validFirst', '$validLast', '$validEmail', '$validAddress', '$validPhone')";

            if (mysqli_query($conn, $sql)) {
                echo "[+] NEW USER WAS SUCCESSFULLY REGISTERED INTO FOODQUEST DATABASE [+]\n";
                $newUser = 'TRUE';
                $validSignup = 'TRUE';
            } else {
                echo "FOODQUEST DATABASE ERROR: " . $sql . "<br>" . mysqli_error($conn);
            }
        }

        mysqli_close($conn);

        // PROCESS TO SEND USER EXISTS MESSAGE TO FRONTEND - RABBITMQ

        $existsConnection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');

        if (!$existsConnection) {
            die("CONNECTION ERROR: COULD NOT CONNECT TO RABBITMQ NODE.");
        }

        $existsChannel = $existsConnection->channel();

        $existsChannel->exchange_declare('database_exchange', 'direct', false, false, false);

        $returnToFrontend = "frontend";

        $returnMsg = [
            'valid_signup' => $validSignup,
            'new_user' => $newUser,
        ];

        $encodedMsg = json_encode($returnMsg);

        $existsMessage = new AMQPMessage($encodedMsg, array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));

        $existsChannel->basic_publish($existsMessage, 'database_exchange', $returnToFrontend);

        echo '[@] REGEX AND MYSQL PROTOCOLS WERE EXECUTED [@]',
            "\n--RETURN MESSAGE SENT TO FRONTEND--\n";

        print_r($returnMsg); // Displaying array in command line

        // Closing channel and connection talking to FRONTEND
        $existsChannel->close();
        $existsConnection->close();
    }

    // SENDING REGEX ERROR MESSAGE TO FRONTEND
    else {
        $regexConnection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');

        if (!$regexConnection) {
            die("CONNECTION ERROR: COULD NOT CONNECT TO RABBITMQ NODE.");
        }

        $regexChannel = $regexConnection->channel();

        $regexChannel->exchange_declare('database_exchange', 'direct', false, false, false);

        $regexFrontend = "frontend";

        $returnMsg = [
            'valid_signup' => 'FALSE',
            'new_user' => 'FALSE',
        ];

        $invalidEncodedRegex = json_encode($returnMsg);

        $regexMessage = new AMQPMessage($invalidEncodedRegex, array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));

        $regexChannel->basic_publish($regexMessage, 'database_exchange', $regexFrontend);

        echo '[@] REGEX CHECK PROTOCOL ACTIVATED [@]', "\n\n[x] SIGNUP INPUT DOES NOT MEET SITE REQUIREMENTS\n";

        print_r($returnMsg); // Displaying array in the command line

        // Closing channel and connection talking to FRONTEND
        $regexChannel->close();
        $regexConnection->close();
    }
};

while (true) {
    try {
        $channelDB->basic_qos(null, 1, false);
        $channelDB->basic_consume('database_mailbox', '', false, true, false, false, $callbackDB);

        while (count($channelDB->callbacks)) {
            $channelDB->wait();
            echo 'NO MORE INCOMING MESSAGES FROM BACKEND', "\n\n";
            break;
        }
    } catch (ErrorException $e) {
        // Handle Error
        echo "ErrorException CAUGHT AT: " . $e->getMessage();
    }
}

// Closing MAIN channel and connection
$channelDB->close();
$connectionDB->close();

?>
