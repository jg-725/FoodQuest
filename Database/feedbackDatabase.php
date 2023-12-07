<?php


//	NECESSARY AMQP LIBRARIES FOR PHP
require_once __DIR__ .'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
$feedbackConnection = null;
$rabbitmqNodes = array('192.168.194.2', '192.168.194.1');


foreach ($rabbitNodes as $node) {
	try {
        	$feedbackConnection = new AMQPStreamConnection(
							$node,
							5672,
							'foodquest',
							'rabbit123'
		);
		echo "DATABASE CONNECTION TO RABBITMQ CLUSTER WAS SUCCESSFUL @ $node\n";
		break;
	} catch (Exception $e) {
		continue;
	}
}

if (!$feedbackConnection) {
	die("CONNECTION ERROR: DATABASE COULD NOT CONNECT TO ANY RABBITMQ NODE.");
}


?>
