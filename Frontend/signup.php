<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>FoodQuest</title>
    <link rel="stylesheet" type="text/css" href="public/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="public/css/main.css" />
    <link rel="stylesheet" typse="text/css" href="js/css/style.css" />
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
                    <img src="21.png" alt="Logo"/>
                </div>

                <form role="form" name="signup" method="POST" onsubmit="return validateForm();">

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
                    <button type="submit" class="login" name="submit">Sign Up</button>
                </form>
            </div>
        </div>

        <?php
        // RabbitMQ Code
        // Required PHP and AMQP Libraries to interact with RabbitMQ
        require_once __DIR__ . '/vendor/autoload.php';
        use PhpAmqpLib\Connection\AMQPStreamConnection;
        use PhpAmqpLib\Message\AMQPMessage;

        // POST method initialized to trigger REGISTER request flow - IF statementsender
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

		//$rabbitmqNodes = array('192.168.194.2', '192.168.194.1');

            // Connecting to Main RabbitMQ Node IP
            $connectionSignup = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');

            if (!$connectionSignup) {
                die("CONNECTION ERROR: COULD NOT CONNECT TO RABBITMQ NODE.");
            }

            $username = $_POST['username_'];
            $password = $_POST['new_password_'];
            $confirm = $_POST['confirm_password_'];
            $firstname = $_POST['first_name_'];
            $lastname = $_POST['last_name_'];
            $email = $_POST['email_'];
            $address = $_POST['address_'];
            $phone = $_POST['pnumber_'];

            $channelSignup = $connectionSignup->channel();    // Establishing Channel Connection for communication

            // Declaring exchange for frontend to send/publish messages
            $channelSignup->exchange_declare('frontend_exchange', 'direct', false, false, false);

            // Routing key address so RabbitMQ knows where to send the message
            $routing_key = "backend";

            // Creating an array to store user login POST request
            $send = array();
            if (empty($send)) {    // Check if the array is empty
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

            // Turning the array into JSON for compatibility
            $encodedSignup = json_encode($send);

            // Creating AMQPMessage protocol once login data is ready for delivery
            $msg = new AMQPMessage(
                $encodedSignup,
                array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
            );

            // Publishing data to RabbitMQ exchange for processing
            $channelSignup->basic_publish($msg, 'frontend_exchange', $routing_key);

            echo ' [x] Frontend Task: SENT USER REGISTRATION TO BACKEND FOR PROCESSING', "\n";
            print_r($send);
            echo "\n\n";

            // Terminating the sending channel and connection
            $channelSignup->close();
            $connectionSignup->close();

            //  	--- THIS PART WILL LISTEN FOR MESSAGES FROM DATABASE ---

            // Connecting to RabbitMQ
            $connectionReceiveDatabase = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');

            if (!$connectionReceiveDatabase) {
                die("CONNECTION ERROR: COULD NOT CONNECT TO RABBITMQ NODE");
            }

            $channelReceiveDatabase = $connectionReceiveDatabase->channel();

            //  	DECLARING EXCHANGE THAT WILL BE ROUTING MESSAGES FROM BACKEND
            $channelReceiveDatabase->exchange_declare('database_exchange', 'direct', false, false, false);

            //  	BINDING KEY SHOULD MATCH ROUTING KEY SENT BY BACKEND
            $userExists = "frontend";

            //  	DECLARING durable queue for testing
            $channelReceiveDatabase->queue_declare('frontend_mailbox', false, true, false, false);

            //  	BINDING QUEUE WITH EXCHANGE USING THE BINDING KEY
            $channelReceiveDatabase->queue_bind('frontend_mailbox', 'database_exchange', $userExists);

            // Establishing a callback variable for processing messages from the database
            $frontendCallback = function ($signupContent) use ($channelReceiveDatabase) {

                // Decoding received msg from the database into usable code for processing
                $decodedDatabase = json_decode($signupContent->getBody(), true);

                $regexUser = $decodedDatabase['valid_signup'];
                $newUser = $decodedDatabase['new_user'];

                /* 3 IF statements: Checking if the user exists and valid input */
                // Commands to be executed if the user exists

                if ($newUser == TRUE && $regexUser == TRUE) {
                    echo "<script>alert('CONGRATS, NEW ACCOUNT CREATED');</script>";
                    header("Location:login.php"); // Redirect using PHP
                     echo "<script>location.href='login.php';</script>";
                    
                    
                    //exit; // Ensure the script stops execution after redirection
                }

                // Commands to be executed if the username/password does not match
                elseif ($newUser == FALSE && $regexUser == TRUE) {
                    echo "<script>alert('USERNAME IS NO LONGER AVAILABLE: ENTER A NEW USERNAME');</script>";
                    echo "<script>location.href='signup.php';</script>";
                }
		else {
			echo "<script>alert('INVALID INPUT: PLEASE TRY AGAIN');</script>";
			die(header("Location:signup.php"));
			//echo "<script>location.href='signup.php';</script>";

		}

            };

            // Triggering the process to consume msgs from DATABASE IF USER EXISTS
            $channelReceiveDatabase->basic_consume('frontend_mailbox', '', false, true, false, false, $frontendCallback);

            // While loop to keep checking for incoming messages from the database
            while ($channelReceiveDatabase->is_open()) {
                $channelReceiveDatabase->wait();
                break;
            }

            // Terminating the channel and connection for receiving msgs
            $channelReceiveDatabase->close();
            $connectionReceiveDatabase->close();
        }
        ?>
    </div>

    <script type="text/javascript" src="public/javascript/jquery-3.2.1.min.js"></script>
    <script type="text/javascript" src="public/javascript/script.js"></script>
    <script type="text/javascript" src="public/javascript/send.js"></script>

    <script>
        function validateForm() {
            var fields = ["username_", "new_password_", "confirm_password_"];
            var flag = 0;

            if (document.forms["signup"][fields[0]].value === "") {
                flag = 1;
                alert('Please enter a username.');
            }

            if (document.forms["signup"][fields[1]].value === "") {
                flag = 1;
                alert('Please enter a password.');
            }

            if (document.forms["signup"][fields[1]].value !== document.forms["signup"][fields[2]].value) {
                flag = 1;
                alert('Passwords do not match.');
            }

            if (flag === 1) {
                return false;
            }
            return true;
        }
    </script>
</body>
</html>



