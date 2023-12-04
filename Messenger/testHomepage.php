<?php
/*	TEST CODE TO SEND A REVIEW TO BACKEND		*/

// Required PHP and AMQP Libraries to interact with RabbitMQ
require_once '/var/www/gci/FrontEnd/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
$feedbackConnection = null;
$rabbitNodes = array('192.168.194.2', '192.168.194.1');

foreach ($rabbitNodes as $rabbitNode) {
    try {
        $feedbackConnection = new AMQPStreamConnection($rabbitNode, 5672, 'foodquest', 'rabbit123');
	echo "BACKEND CONNECTION TO RABBITMQ WAS SUCCESSFUL @ $rabbitNode\n";
        break;
    } catch (Exception $e) {
        continue;
    }
}

if (!$feedbackConnection) {
    die("CONNECTION ERROR: COULD NOT CONNECT TO RABBITMQ NODE");
}

//      CHANNEL TO SEND REVIEW TO BACKEND
$feedbackChannel = $feedbackConnection->channel();


$comment = $_POST['comment'];
$rating = $_POST['rating'];

//      DECLARING EXCHANGE THAT WILL ROUTE MESSAGES FROM FRONTEND
$feedbackChannel->exchange_declare('review_exchange', 'direct', false, false, false);

// Routing key address so RabbitMQ knows where to send the message
$homeKey = "backend";

// Creating an array to store user login POST request
$userFeedback = array();

if (empty($userFeedback)) {    // Check if array is empty

        $userFeedback['comment'] = $comment;
       	$userFeedback['rating'] = $rating;
}

// Turning array into JSON for compatibility
$encodedFeedback = json_encode($userFeedback);

// Creating AMQPMessage protocol once REVIEW data is ready for delivery
$msg = new AMQPMessage(
	$encodedFeedback,
        array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
);

// Publishing message to backend exchange using binding key indicating the receiver
$feedbackChannel->basic_publish($msg, 'review_exchange', $homeKey);

//	MESSAGE THAT WORKFLOW WAS TRIGGERED
echo ' [x] FRONTEND TASK: SENT USER REVIEW TO BACKEND', "\n";
print_r($userFeedback);
echo "\n\n";

// Terminating sending channel and connection
$feedbackChannel->close();
$feedbackConnection->close();

?>
