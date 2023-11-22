<?php
session_start();

// Checks if the user is logged in. If they are, redirect them to the home page as register.php should not be accessable to logged in users.
if (isset($_SESSION['username']) && isset($_SESSION["OurIPs"])) {
  header("Location: home.php");
  exit();
}
?>

<!DOCTYPE html>


<html>
<head>
	<title>FoodQuest</title>
	<link rel="stylesheet" type="text/css" href="public/css/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="public/css/main.css" />
	<link rel="stylesheet" type="text/css" href="js/css/style.css" />
	<script src="js/number.js"></script>
 	<link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</head>
<body>
<div class="main">

  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
      <div class="navbar-nav">
        <a class="nav-item nav-link active" href="index.php">Home <span class="sr-only">(current)</span></a>

        <a class="nav-item nav-link" href="login.php">Log In</a>
      </div>
    </div>
  </nav>

	<div class="sign container">
	<div class="login-block register-box">
	<div class="logo">
    	<img src="21.png"/>
</div>

<form role="form" name="signup" method="post">

	<input type="text" placeholder="Username" name="username_" id="username" class="username" required="required" aria-describedby="usernameHelp" />

        <input type="password" placeholder="Password" id="new_password" name="new_password_" class="password" required="required" />

        <input type="password" placeholder="Confirm Password" class="password" id="confirm_password" name="confirm_password_" autocomplete="new-password" required="required" />

        <input type="text" placeholder="First Name" class="Fname" id="Fname" name="first_name_" autocomplete="first-name" required="required">

    	<input type="text" placeholder="Last Name" class="Lname" id="Lname" name="last_name_" autocomplete="last-name" required="required" />

        <input type="email" placeholder="Email" class="Email" id="Email" name="email_" autocomplete="email" required="required" />

        <input type="text" placeholder="Address" class="Address" id="Address" name="address_" autocomplete="address" required="required" />

	<input type="text" placeholder="Phone Number" class="Pnumber" id="Pnumber" name="pnumber_" autocomplete="phone-number" required="required" onkeypress="return isNumberKey(event)" />
	<br/>
	<br/>
	<button type="submit" class="signup" name="submit">Sign Up</button>
</form>

</div>

</div>

/*	RabbitMQ Code	*/

<?php

	// Initializing login session
	session_start();

	// Required PHP and AMQP Libraries to interact with RabbitMQ
	require_once __DIR__ .'/vendor/autoload.php';
	use PhpAmqpLib\Connection\AMQPStreamConnection;
	use PhpAmqpLib\Message\AMQPMessage;

	// POST method initialized to trigger REGISTER request flow - IF statement
	if ($_SERVER['REQUEST_METHOD' === 'POST']) {

		/*              SECTION TO SEND USER REGISTRATION TO BACKEND        */


		//	GETTING POST VARIABLES FROM SIGNUP FORM
		$username = $_POST['username'];
        	$password = $_POST['new_password'];
        	$confirm = $_POST['confirm_password'];
        	$firstname = $_POST['Fname'];
        	$lastname = $_POST['Lname'];
		$email = $_POST['Email'];
		$address = $_POST['Address'];
		$phone = $_POST['Pnumber'];


		// Connecting to Main RabbitMQ Node IP
		$connectionSend = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
		$channelSend = $connectionSend->channel();	//Establishing Channel Connection for communication

		// Declaring exchange for frontend to send/publish messages
		$channelSend->exchange_declare('backend_exchange', 'direct', false, false, false);

		// Routing key address so RabbitMQ knows where to send the message
		$routing_key = "backend";

		// Creating an array to store user login POST request
		$send = array();
		if (empty($send)) {	// Check if array is empty
        		//$send['type'] = ;
        		$send['username'] = $username;
        		$send['password'] = $password;
			$send['confirm'] = $confirm;
                        $send['first'] = $firstname;
			$send['last'] = $lastname;
			$send['email'] = $email;
			$send['address'] = $address;
			$send['phone'] = $phone;
		}

		// Turning array into JSON for compatability
		//$login_data = implode($send);
		$encodedSignup = json_encode($send);

		// Creating AMQPMessage protocol once login data is ready for delivery
		$msg = new AMQPMessage(
        		$encodedSignup,
        		array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
		);

		// Publishing data to RabbitMQ exchange for processing
		$channelSend->basic_publish($msg, 'frontend_exchange', $routing_key);

		echo ' [x] Frontend Task: SENT USER REGISTRATION TO BACKEND FOR PROCESSING', "\n";
 		//echo ' [x] Frontend Task: Send register form to Backend Exchange', "\n";
		print_r($send);
		echo "\n\n";

		// Terminating sending channel and connection
		$senderChannel->close();
		$senderConnection->close();


		/*		SECTION TO RECEIVE MESSAGES FROM BACKEND and DATABASE		*/


		//      --- THIS PART WILL LISTEN FOR MESSAGES FROM BACKEND ---

		// Connecting to RabbitMQ
		$connectionReceiveBackend = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');

		$channelReceiveBackend = $connectionReceiveBackend->channel();
		//	Making NON durable queue for testing
		$channelReceiveBackend->queue_declare('frontend_mailbox', false, false, false, false);

		// Establishing callback variable for processing messages from BACKEND
		$receiverCallback1 = function ($msgContent) {

			// Decoding received msg from database into usuable code for processing
			$decodedBackend = json_decode($msgContent->getBody(), true);

			$validUser = $decodedBackend['validUser'];

			$validPassword = $decodedBackend['validPassword'];

			/*	2 IF statements: Checking if SIGNUP PASSES REGEX	*/

			// Commands to be executed if username/password does not match
			if ($validUser == false || $validPassword == false) {
				echo "<script>alert('SIGN UP INPUT DOES NOT MEET CRITERIA')</script>";
                                //echo "[x] Error on signup: TRY AGAIN";
                                echo "<script>location.href='signup.php';</script>";
				//echo "<script>alert('Username or password does not exist in database');</script>";
				//echo "<script>location.href='login.php';</script>";
			}

			// Commands to be executed if data is valid
			if ($validUser == true && $validPassword == true) {
				die(header("location:home.php"));
				//echo "Congrats: Username and Password Are Valid\n";
			}
		};

		// Triggering the process to consume msgs from BACKEND IF USER FORMAT IS INVALID
		$channelReceiveBackend->basic_consume('frontend_mailbox', '', false, true, false, false, $receiveCallback1);

		// while loop to keep checking for incoming messages from BACKEND
		while ($channelReceiveBackend->is_open()) {
			$channelReceiveBackend->wait();
			break;
		}

		// Terminating channel and connection for receivin msgs
		$channelReceiveBackend->close();
		$connectionReceiveBackend->close();


		//      --- THIS PART WILL LISTEN FOR MESSAGES FROM DATABASE ---

		// Connecting to RabbitMQ
		$connectionReceiveDatabase = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');

		$channelReceiveDatabase = $connectionReceiveDatabase->channel();

		//      DECLARING NON durable queue for testing
		$channelReceiveDatabase->queue_declare('frontend_mailbox', false, false, false, false);

		// Establishing callback variable for processing messages from database
		$receiverCallback2 = function ($msgContent) {

        		// Decoding received msg from database into usuable code for processing
        		$decodedDatabase = json_decode($msgContent->getBody(), true);

        		$newUser = $decodedDatabase['newUser'];

        		/* 2 IF statements: Checking if user exists */

       			// Commands to be executed if username/password does not match
        		if ($newUser == FALSE) {
                		//echo "[x] DATABASE ERROR: USER ALREADY EXISTS\n";
				//echo "TRY AGAIN\n\n";
                		echo "<script>alert('USER ALREADY EXISTS: ENTER USERNAME AND PASSWORD');</script>";
                		echo "<script>location.href='login.php';</script>";
        		}

        		// Commands to be executed if user exists
        		if ($newUser == TRUE) {
                		die(header("location:home.php"));
				//echo "[+] WELCOME ";
        		}
		};

		// Triggering the process to consume msgs from DATABASE IF USER EXISTS
		$channelReceiveDatabase->basic_consume('frontend_mailbox', '', false, true, false, false, $receiverCallback2);

		// while loop to keep checking for incoming messages from database
		while ($channelReceiveDatabase->is_open()) {
        		$channelReceiveDatabase->wait();
        		break;
		}

		// Terminating channel and connection for receivin msgs
		$channelReceiveDatabase->close();
		$connectionReceiveDatabase->close();
	}
?>


<script type="text/javascript" src="public/javascript/jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="public/javascript/script.js"></script>
<script type="text/javascript" src="public/javascript/send.js"></script>

/*
<script>
  function validateForm(){
      var fields = ["username_","new_password_","confirm_password_"];
      var flag=0;
      if (document.forms["signup"][fields[0]].value === "") {
          flag=1;
      }
      if (document.forms["signup"][fields[1]].value === "") {
          flag=1;
      }
    if(document.forms["signup"][fields[1]].value !== document.forms["signup"][fields[2]].value){
          flag=1;
    }
      if(flag === 1){
        return false;
      }
      return true;
  }
</script>
*/
</body>
</html>
