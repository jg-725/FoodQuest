<?php
session_start(); // Start the session

// Checks if the user is logged in. If they are, redirect them to the home page as register.php should not be accessable to logged in users.
if (isset($_SESSION['username']) && isset($_SESSION["userID"])) {
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
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

    
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
                <img src="22.png" alt="Logo" />
            </div>
            <form method="POST">
                <input type="text" placeholder="Username" id="username" name="username" class="username" required />
                <input type="password" placeholder="Password" id="password" name="password" class="password" required />
                <button type="submit" value="Log In" name="submit" class="login">Log In</button>

                <strong/><a href="signup.php"> <p style="text-align:center; font-size:14px; width:24%;position:relative;top:35px; left:6px"/>Sign up?</a>

                <a href="forgot-password.php"><p style="text-align:center;font-size:14px; width: 150%;top:3px; right:10px"/> Forgot password?</a>
            </form>

            <?php
            // Required PHP and AMQP Libraries to interact with RabbitMQ
            require_once '/var/www/gci/FrontEnd/vendor/autoload.php';
            use PhpAmqpLib\Connection\AMQPStreamConnection;
            use PhpAmqpLib\Message\AMQPMessage;

            // Server request POST initialized to trigger login request flow - IF statement
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                /*  	Sending/Publishing Section   	*/


		//      RABBITMQ CONNECTION SETTINGS
		$loginConnection = null;
		$rabbitNodes = array('192.168.194.2', '192.168.194.1');
		$port = 5672;
		$user = 'foodquest';
		$pass = 'rabbit123';

		//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
		foreach ($rabbitNodes as $node) {
                	try {
                		$loginConnection = new AMQPStreamConnection(
                    						$node,
                    						$port,
                    						$user,
                    						$pass
                		);
				echo "LOGIN.PHP CONNECTION TO RABBITMQ WAS SUCCESSFUL @ $node\n";
				break;
			} catch (Exception $e) {
				continue;
			}
		}

		if (!$loginConnection) {
                	die("LOGIN CONNECTION ERROR: FRONTEND COULD NOT CONNECT TO RABBITMQ NODE.");
            	}

		//      RABBITMQ MESSAGE BROKER SETTINGS
		$publishExchange = 'frontend_exchange';  // Exchange Name
                $exchangeType 	 = 'direct';		// Exchange Type
                $loginRK 	 = 'login-backend';	// ROUTING KEY


                $loginchannel = $loginConnection->channel();    // Establishing Channel Connection for communication

                // Declaring exchange for frontend to send/publish messages
                $loginChannel->exchange_declare(
						$publishExchange,
						$exchangeType,
						false,	// PASSIVE
						true,	// DURABLE
						false	// AUTO-DELETE
		);

		$username = $_POST['username'];
                $password = $_POST['password'];

                // Creating an array to store user login POST request
                $loginArray = array();
                if (empty($loginArray)) {    // Check if array is empty

                	$loginArray['username'] = $username;
                    	$loginArray['password'] = $password;
                }

                // Turning array into JSON for compatibility
                $encodedLogin = json_encode($loginArray);

                // Creating AMQPMessage protocol once login data is ready for delivery
                $msg = new AMQPMessage(
                		$encodedLogin,
                    		array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
                );

                // Publishing message to backend exchange using binding key indicating the receiver
                $loginChannel->basic_publish($msg, $publishExchange, $loginRK);

                // Message that shows login workflow was triggered
                echo ' [x] FRONTEND TASK: SENT LOGIN TO BACKEND', "\n";
                print_r($loginArray);
                echo "\n\n";

                // Terminating sending channel and connection
                $loginChannel->close();
                $loginConnection->close();


		//////////////////////////////////////////////////////////////////////////

                /*  	--- THIS PART WILL LISTEN FOR MESSAGES FROM DATABASE ---	*/

		 //      RABBITMQ CONNECTION SETTINGS
                $connectionReceiveDatabase = null;
                $rabbitNodes = array('192.168.194.2', '192.168.194.1');
                $port = 5672;
                $user = 'foodquest';
                $pass = 'rabbit123';

		//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
                foreach ($rabbitNodes as $node) {
                        try {
                                $connectionReceiveDatabase = new AMQPStreamConnection(
                                                                $node,
                                                                $port,
                                                                $user,
                                                                $pass
                                );
                                echo "LOGIN.PHP CONNECTION TO RABBITMQ WAS SUCCESSFUL @ $node\n";
				break;
			} catch (Exception $e) {
                                continue;
                        }
                }

                //    Connecting to MAIN RabbitMQ
                //$connectionReceiveDatabase = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');

		if (!$connectionReceiveDatabase) {
                        die("CONNECTION ERROR: FRONTEND COULD NOT CONNECT TO RABBITMQ NODE.");
                }

		//      RABBITMQ MESSAGE BROKER SETTINGS TO RECEIVE
                $consumeExchange = 'database_exchange';		// Exchange Name
                $exchangeType 	 = 'direct';			// Exchange Type
		$queueName	 = 'FE_login_mailbox';		// Queue Name
                $loginBK 	 = 'userExists-frontend';	// BINDING KEY MATCHES LOGIN DATABASE ROUTING KEY


                $channelReceiveDatabase = $connectionReceiveDatabase->channel();

                $channelReceiveDatabase->exchange_declare(
						$consumeExchange,
						$exchangeType,
						false,		// PASSIVE
						true,		// DURABLE
						false		// AUTO-DELETE
		);

                //  	DECLARING durable queue: third parameter TRUE
                $channelReceiveDatabase->queue_declare(
						$queueName,
						false,
						true,
						false,
						false
		);


                //     Binding corresponding queue and exchange
                $channelReceiveDatabase->queue_bind(
						$queueName,
						$consumeExchange,
						$loginBK);

                // Establishing callback variable for processing messages from database
                $receiverCallback = function ($msgContent) use ($channelReceiveDatabase) {

                    // Decoding received msg from database into usable code for processing
                    $decodedDBLogin = json_decode($msgContent->getBody(), true);

                    $userExists = $decodedDBLogin['userExists'];
                    $userID = $decodedDBLogin['userID'];
                    $dbUser = $decodedDBLogin['username'];
                   

                    /* 2 IF statements: Checking if user exists */

                    // Commands to be executed if username/password does not match
                    if ($userExists == 'FALSE') {
                        echo "Username or password does not exist in the database";
          		echo "<script>alert('USER DOES NOT EXIST IN DATABASE');</script>";
          		
                    	echo "<script>location.href='login.php';</script>";
                    }

                    // Commands to be executed if the user exists
                    else  {
                        
                         $_SESSION['userID'] = $userID;
                         $_SESSION['username'] = $dbUser;
                         die(header("Location: home.php"));
                         
                          //echo $dbUser;
                    }
                };

                // Triggering the process to consume msgs from DATABASE IF USER EXISTS
                $channelReceiveDatabase->basic_consume($queueName, '', false, true, false, false, $receiverCallback);

                // while loop to keep checking for incoming messages from the database
                while ($channelReceiveDatabase->is_open()) {
                    $channelReceiveDatabase->wait();
                    break;
                }

                // Terminating channel and connection for receiving msgs
                $channelReceiveDatabase->close();
                $connectionReceiveDatabase->close();
            }
            //    END OF PHP SERVER POST SESSION
            ?>
        </div>
    </div>
    
    
</body>

</html>



