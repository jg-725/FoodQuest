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

/*
	FIX: I DONT THINK WE NEED testRabbitMQClient2.php file OR the return validateForm()

     	We just need the POST METHOD like the login page
*/

<form role="form" name="signup" action="testRabbitMQClient2.php" method="post" onSubmit="return validateForm();" >

	<input type="text" placeholder="Username" name="username_" id="username" class="username" required="required" aria-describedby="usernameHelp" />

        <input type="password" placeholder="Password" id="new_password" name="new_password_" class="password" required="required" />

        <input type="password" placeholder="Confirm Password" class="password" id="confirm_password" name="confirm_password_" autocomplete="new-password" required="required" />
        <input type="test" placeholder="First Name" class="Fname" id="Fname" name="first_name_" autocomplete="first-name" required="required">

    	<input type="test" placeholder="Last Name" class="Lname" id="Lname" name="last_name_" autocomplete="last-name" required="required" />

        <input type="email" placeholder="Email" class="Email" id="Email" name="email_" autocomplete="email" required="required" />

        <input type="test" placeholder="Address" class="Address" id="Address" name="address_" autocomplete="address" required="required" />

	<input type="test" placeholder="Phone Number" class="Pnumber" id="Pnumber" name="pnumber_" autocomplete="phone-number" required="required" onkeypress="return isNumberKey(event)" />
<br/>
<br/>


    <button type="submit" class="login" name="submit">Sign Up</button>
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

		$username = $_POST['username'];
        	$password = $_POST['password'];
        	$confirm = $_POST['confirm'];
        	$firstname = $_POST['firstname'];
        	$lastname = $_POST['lastname'];
		$email = $_POST['email'];

		/*      Sending Messages Section       */

		// Connecting to Main RabbitMQ Node IP
		$senderConnection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
		$senderChannel = $senderConnection->channel();	//Establishing Channel Connection for communication

		// Declaring exchange for frontend to send/publish messages
		$senderChannel->exchange_declare('backend_exchange', 'direct', false, false, false);

		// Binding key: Relationship between exchange and queue
		$binding_key_backend = "backend";

		// Creating an array to store user login POST request
		$send = array();
		if (empty($send)) {	// Check if array is empty
        		//$send['type'] = ;
        		$send['username'] = $username;
        		$send['password'] = $password;
			$send['confirm'] = $confirm;
                        $send['first'] = $firstname;
			$send['last'] = $lastname;
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
 		echo ' [x] Frontend Task: Send register form to Backend Exchange', "\n";
		print_r($send);
		echo "\n\n";

		// Terminating sending channel and connection
		$senderChannel->close();
		$senderConnection->close();


		/*	Receiving/Consuming Messages	*/

		// Connecting to Main RabbitMQ Node IP
        	$receiverConnection = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');
        	$receiverChannel = $receiverConnection->channel();  //Establishing Channel Connection for communication

		// Declaring queue that frontend will be listening for
		$receiverChannel ->queue_declare('database_queue', false, true, false, false);

		// Establishing callback variable for processing messages from database
		$receiverCallback = function ($msgContent) {

			// Decoding received msg from database into usuable code for processing
			$decoded_signup = json_decode($msgContent->getBody(), true);

			$validRegex = $decoded_signup['validRegex'];
			$userExists = $decoded_signup['userExists'];


			/* 2 IF statements: Checking if new user exists */

			// Commands to be executed if username/password does not match
			if ($validRegex == false) {
				echo "<script>alert('Sign Up Input DOES NOT MEET CRITERIA')</script>";
				echo "[x] Error on signup: TRY AGAIN";
				echo "<script>location.href='signup.php';</script>";
			}

			// Commands to be executed if REGEX is correct
			if ($validRegex == true) {

				if ($userExists == false) {
					//die(header("location:home.php"))
					echo "\n Congrats, Registeration Complete";
					// Should redirect to dashboard page
				}
				else {
					echo "<script>location.href='signup.php'</script>";
					//echo "<script></script>";
				}
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



<script type="text/javascript" src="public/javascript/jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="public/javascript/script.js"></script>
<script type="text/javascript" src="public/javascript/send.js"></script>
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
</body>
</html>
