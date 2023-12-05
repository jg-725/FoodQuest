<?php

/*      TESTING SPOONACULAR API FOR USER REQUESTS - RABBITMQ RECEIVER       */

//      AMQP LIBRARIES
require_once __DIR__ .'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

//	SPOONACULAR API ENDPOINT AND API KEY
$API_KEY = '886b4650b3eb4108a36e10a83da5be5d';
$endpointPrefix = "https://api.spoonacular.com/recipes/";

/*
//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
$connection = null;
$rabbitNodes = array('192.168.194.2', '192.168.194.1');

foreach ($rabbitNodes as $rabbitNode) {
    try {
        $connection = new AMQPStreamConnection($rabbitNode, 5672, 'foodquest', 'rabbit123');
        echo "BACKEND CONNECTION TO RABBITMQ WAS SUCCESSFUL @ $rabbitNode\n";
        break;
    } catch (Exception $e) {
        continue;
    }
}

if (!$connection) {
    die("CONNECTION ERROR: COULD NOT CONNECT TO RABBITMQ NODE");
}

//      ACTIVING MAIN API CHANNEL TO PROCESS FRONTEND REQUESTS
$channel = $connection->channel();
*/
//      DECLARING EXCHANGE THAT WILL ROUTE MESSAGES FROM FRONTEND
$channel->exchange_declare('test_api_exchange', 'direct', false, false, false);

//      NO DURABLE QUEUE FOR TESTING API REQUESTS: Third parameter is FALSE
$channel->queue_declare('test_api_queue', false, false, false, false);

// Binding Key
$backend_api = 'spoonacular';

// Binding three items together to receive msgs
$channel->queue_bind('test_api_queue', 'test_api_exchange', $backend_api);

// Terminal message to signal we are waiting for messages from frontend
echo '[*] WAITING FOR FRONTEND TO SEND API REQUESTS. To exit press CTRL+C', "\n\n";

//	CALLBACK RESPONSIBLE OF PROCESSESSING API REQUESTS
$callback = function ($msg) use ($channel) {

	echo '[+] RECEIVED USER REQUEST FROM FRONTEND',"\n\n";
	$apiData = json_decode($msg->getBody(), true);

	$request = $apiData['request'];

	//	TODO: CREATE IF OR SWITCH STATEMENT DEPENDING ON USER INPUT



};

















?>
