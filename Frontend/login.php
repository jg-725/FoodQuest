<?php
session_start();

// Checks if the user is logged in. If they are, redirect them to the home page as login.php should not be accessable to logged in users.
if (isset($_SESSION['username']) && isset($_SESSION["OurIPs"])) {
  header("Location: home.php");
  exit();
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Login Page</title>
   <link rel="stylesheet" type="text/css" href="public/css/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="public/css/main.css" />

     <link rel="stylesheet" type="text/css" href="js/css/style.css" />
 <link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>

 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</head>
<body>

<div class="main">
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="index.php"></a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
      <div class="navbar-nav">
        <a class="nav-item nav-link active" href="index.php">Home <span class="sr-only">(current)</span></a>
        <a class="nav-item nav-link" href="signup.php">Sign Up</a>

      </div>
    </div>
  </nav>


<div class="login-block login-box">
<div class="logo">

    	<img src="22.png"/>

    </div>    
    <form method="POST">

     <input type="text" placeholder="Username" id="username" name="username" class="username" required />

	    <input type="password" placeholder="Password" id="password" name="password" class="password" required />
        <button type="submit" value="Log In" name="submit" class="login">Log In</button>
 
 <strong/><a href="signup.php"> <p style="text-align:center; font-size:14px; width:24%;position:relative;top:35px; left:6px"/>Sign up?</a>
 
  <a href="forgot-password.php"><p style="text-align:center;font-size:14px; width: 150%;top:3px; right:10px"/> Forgot password?</a>
 
  </form>
<?php
	session_start(); // Start the session

	// Required PHP and AMQP Libraries to interact with RabbitMQ
	require_once '/home/FoodQuest/Frontend/vendor/autoload.php';
	use PhpAmqpLib\Connection\AMQPStreamConnection;
      	use PhpAmqpLib\Message\AMQPMessage;

	// Server request POST initialized to trigger login request flow - IF statement
      	if ($_SERVER['REQUEST_METHOD'] === 'POST') {

		$username = $_POST['username'];
        	$password = $_POST['password'];

  		/*      Sending/Publishing Section       */

		// Connecting to Main RabbitMQ Node IP
		$senderConnection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
		$senderChannel = $senderConnection->channel();	//Establishing Channel Connection for communication

		// Declaring exchange for frontend to send/publish messages
		$senderChannel->exchange_declare('backend_exchange', 'direct', false, false, false)

 		// Binding key: Relationship between exchange and queue
		$binding_key_backend = "backend";

		// Creating an array to store user login POST request
		$send = array();
		if (empty($send)) {	// Check if array is empty
        		//$send['type'] = ;
        		$send['username'] = $username;
        		$send['password'] = $password;
		}

		// Turning array into JSON for compatability
		//$login_data = implode($send);
		$login_data = json_encode($send);

		// Creating AMQPMessage protocol once login data is ready for delivery
	       $msg = new AMQPMessage(
        		$login_data,
        		array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
		);

		// Publishing message to backend exchange using binding key indicating the receiver
		$senderChannel->basic_publish($msg, 'backend_exchange', $binding_key_backend);

		// Message that shows login workflow was triggered
		echo ' [x] Frontend Task: Sent Login Data To Backend Exchange', "\n";
		print_r($send);
		echo "\n\n";

		// Terminating sending channel and connection
		$senderChannel->close();
		$senderConnection->close();

		/*

			TODO FOR JEFF: ADD THE RECEIVING CODE FOR BACKEND LOGIN ERRORS
			SAME PROCESS AS DATBASE
			exchange -> queue -> binding key -> queue bind -> callback -> basic consume

		*/

		//	Receiving/Consuming From Database

		// Connecting to Main RabbitMQ Node IP
        	$receiverConnection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
        	$receiverChannel = $receiverConnection->channel();  //Establishing Channel Connection for communication

		// Exchange to listen for
		$receiverChannel->exchange_declare('database_exchange', 'direct', false, false);

		// Declaring queue that frontend will be listening for
		$receiverChannel ->queue_declare('database_queue', false, false, true, false);

		// Binding Key
		$binding_key_database = 'frontend'

		// Binding corresponding queue and exchange
		$receiverChannel->queue_bind('database_queue', 'database_exchange', $binding_key_database);

		// Establishing callback variable for processing messages from database
		$receiverCallback = function ($msgContent) {

			// Decoding received msg from database into usuable code for processing
			$decoded_login = json_decode($msgContent->getBody(), true);

			/* 2 IF statements: Checking if user exists */

			// Commands to be executed if username/password does not match
			if ($userExists == false) {
				//echo "Username or password does not exist in database";
				echo "<script>alert('Username or password does not exist in database');</script>";
				echo "<script>location.href='login.php';</script>";
			}

			// Commands to be executed if user exists
			if ($userExists == true) {
				die(header("location:home.php"));
			}
		}

		// Triggering the process to consume msgs from database
		$receiverChannel->basic_consume('database_queue', '', false, true, false, false, $receiverCallback);

		// while loop to keep checking for incoming messages from database
		while ($receiverChannel->is_open()) {
			$receiverChannel->wait();
			break;
		}

		// Terminating channel and connection for receivin msgs
		$receiverChannel->close();
		$receiverConnection->close();
	}
?>
</div>


</div>
</body>
</html>
