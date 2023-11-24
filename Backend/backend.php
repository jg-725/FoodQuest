<?php

require_once __DIR__ .'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

public function listen()	{
	//$connection = new AMQPStreamConnection('172.26.177.167', 5672, 'test', 'test');
	//$channel = $connection->channel();

	// Creating exchange to send messages to Messenger
	$channel->queue_declare('backend_queue', false, true, false, false);

	$channel-basic_qos(
		null,   //prefetch size - prefetch window size in octets, null meaning "no specific limit"
            	1,      //prefetch count - prefetch window in terms of whole messages
            	null    //global - global=null to mean that the QoS settings should apply per-consumer, global=true to mean that the QoS settings should apply per-channel
        );
	$channel->basic_consume(
        	'backend_queue',        //queue
        	'',                     //consumer tag - Identifier for the consumer, valid within the current channel. just string
        	false,                  //no local - TRUE: the server will not send messages to the connection that published them
        	true,                   //no ack - false - acks turned on, true - off.  send a proper acknowledgment from the worker, once we're done with a task
        	false,                  //exclusive - queues may only be accessed by the current connection
        	false,                  //no wait - TRUE: the server will not respond to the method. The client should not wait for a reply method
        	array($this, 'receive') //callback
        );

	while(count($channel->callbacks)) {
        	echo 'Waiting for incoming messages';
        	$channel->wait();
        }
	$channel->close();
        $connection->close();
}


/*
// Connecting to RabbitMQ with correct IP Address
$connection = new AMQPStreamConnection('172.26.177.167', 5672, 'test', 'test');
$channel = $connection->channel();

// Creating exchange to send messages to Messenger
$channel->exchange_declare('backend_exchange', 'direct', false, false, false);
// The key/address of where message is going
$binding_key = "database";
*/


// Function that consumes incoming messages
function process(AMQPMessage $msg) {
	// Terminal message to signal messaged received
	echo "Backend Server received message from Frontend via Messenger\n";

	//Decoding JSON Formata into array form
	$format_data = var_dump(json_decode($msg, $assocForm=true));
	if(!isset($format_data['type'])) {
		return "ERROR: UNSUPPORTED MESSAGE TYPE";
	}
	// Creating SWITCH statement to trigger corresponding type request
	switch (format_data['type']) {
		case "login":
			return doLogin($format_data);
		case "register":
			return doRegister($format_data);
	}
}

// Function to process login request from switch statement
function doLogin($request) {

	$newData = array();
	$newData = $request;

  	//$newRequest['type'] = 'LoginDB';
  	$newData['password'] = password_hash($newData['password'], PASSWORD_DEFAULT);
  	$newData['message'] = "Backend processed the request";
	$newData['auth'] = "authenticated";

  	echo $newData['message'];
	$encode_data = json_encode($newData);
	echo "\n\n";

  	return $encode_data;
}

/*
//Array Variables
$username = "John";
$password = "1234";
$auth = "authenticated";
$new_password = password_hash($password, PASSWORD_DEFAULT);

$send = array();
if (empty($send)) {

        $send['type'] = "loginDB";
        $send['username'] = $username;
	$send['password'] = $new_password;
	$send['auth'] = $auth;
}
//$login_data = json_encode($send);
*/

// Terminal message to say messaged received
$callback = function ($msg) {
	$process_data = process($msg->getBody());
  	echo ' [x] Received This From Messenger\n', $msg->body, "\n\n";
	$envelope = new AMQPMessage(    // Getting new data ready for delivery
        $process_data,
        array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
	);
	// Publishing message to Messenger via backend exchange
	$channel->basic_publish($envelope, 'backend_exchange', $binding_key);
	echo ' [x] Backend Task: Sent Hashed Password to Messenger', "\n";
	print_r($process_data);
	echo "\n\n";
};

/*
$channel->basic_qos(null, 1, false);

// Listens for incoming messages from backend queue
//$channel->basic_consume('backend_queue', '', false, true, false, false, $callback);
$channel->basic_consume(
	'backend_queue',	//queue
	'',			//consumer tag
	false,			//no local
	true,			//no ack
	false,			//exclusive
	false,			//no wait
	array($this, 'receive')	//callback
	);
*/



/*

$process_data = receive(AMQPMessage $msg);	// Calling Receive() function to process data received
$envelope = new AMQPMessage(    // Getting new data ready for delivery
        $process_data,
        array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
);

// Publishing message to Messenger via backend exchange
$channel->basic_publish($envelope, 'backend_exchange', $binding_key);

echo ' [x] Backend Task: Sent Hashed Password to Messenger', "\n";
print_r($process_data);
echo "\n\n";
*/

/*
$channel->close();
$connection->close();
*/

?>
