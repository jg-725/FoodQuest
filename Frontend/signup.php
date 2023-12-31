<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
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
                    <img src="21.png" alt="Logo"/>
                </div>

                <form role="form" name="signup" method="POST" onsubmit="return validateForm();">
                    <input type="text" placeholder="Username" name="username_" id="username" class="username" required="required" aria-describedby="usernameHelp" />
                    <input type="password" placeholder="Password" id="new_password" name="new_password_" class="password" required pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#%^])[A-Za-z\d@#%^]{8,}" title="Password must be at least 8 characters long and include at least 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character (@#%^)."  />
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
        require_once __DIR__ . '/vendor/autoload.php';
        use PhpAmqpLib\Connection\AMQPStreamConnection;
        use PhpAmqpLib\Message\AMQPMessage;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

           // $connectionSignup = new AMQPStreamConnection('192.168.194.2', 5672, 'foodquest', 'rabbit123');

		/*  	--- THIS SECTION WILL SEND SIGNUP DATA TO BACKEND ---	*/

		//      RABBITMQ CONNECTION SETTINGS
                $connectionSignup = null;
                $rabbitNodes = array('192.168.194.2', '192.168.194.1');
                $port = 5672;
                $user = 'foodquest';
                $pass = 'rabbit123';

		//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
		foreach ($rabbitNodes as $node) {
			try {
            			$connectionSignup = new AMQPStreamConnection(
								$node,
								5672,
								'foodquest',
								'rabbit123'
				);
				echo "SIGNUP CONNECTION TO RABBITMQ CLUSTER WAS SUCCESSFUL @ $node\n";
				break;
			} catch (Exception $e) {
				continue;
			}

		}

            	if (!$connectionSignup) {
                	die("SIGNUP CONNECTION ERROR: FRONTEND COULD NOT CONNECT TO ANY RABBITMQ NODE.");
            	}

		//      RABBITMQ MESSAGE BROKER SETTINGS TO SEND
		$publishExchange = 'frontend_exchange';
		$exchangeType = 'direct';
		$signupRK   = 'signup-backend';

		$channelSignup = $connectionSignup->channel();

            	$channelSignup->exchange_declare(
						$publishExchange,
						$exchangeType,
						false,	// PASSIVE
						true,	// DURABLE
						false	// AUTO-DELETE
		);

		// GETTING POST VARIABLES FROM FORM
            	$username = $_POST['username_'];
            	$password = $_POST['new_password_'];
            	$confirm = $_POST['confirm_password_'];
            	$firstname = $_POST['first_name_'];
            	$lastname = $_POST['last_name_'];
            	$email = $_POST['email_'];
            	$address = $_POST['address_'];
            	$phone = $_POST['pnumber_'];

            $plainSignup = array(
                'username' => $username,
                'password' => $password,
                'confirm' => $confirm,
                'first' => $firstname,
                'last' => $lastname,
                'email' => $email,
                'address' => $address,
                'phone' => $phone
            );

            	$encodedSignup = json_encode($plainSignup);

            	$msg = new AMQPMessage(
            		$encodedSignup,
                	array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
            	);

            	$channelSignup->basic_publish($msg, $publishExchange, $signupRK);

            	echo ' [x] Frontend Task: SENT USER REGISTRATION TO BACKEND FOR PROCESSING', "\n";
            	print_r($send);
            	echo "\n\n";

            	$channelSignup->close();
            	$connectionSignup->close();


		//////////////////////////////////////////////////////////////////////////

            	/*	--- THIS SECTION WILL LISTEN FOR MESSAGES FROM DATABASE ---	*/


                //      RABBITMQ CONNECTION SETTINGS
                $connectionValidSignup = null;
                $rabbitNodes = array('192.168.194.2', '192.168.194.1');
                $port = 5672;
                $user = 'foodquest';
                $pass = 'rabbit123';

		//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
                foreach ($rabbitNodes as $node) {
			try {
            			$connectionValidSignup = new AMQPStreamConnection(
									$node,
									5672,
									'foodquest',
									'rabbit123'
				);
				echo "FRONTEND CONNECTION TO RABBITMQ WAS SUCCESSFUL @ $node\n";
				break;
			} catch (Exception $e) {
				continue;
			}
		}

		if (!$connectionValidSignup) {
                	die("CONNECTION ERROR: COULD NOT CONNECT TO RABBITMQ NODE");
            	}

		//      RABBITMQ MESSAGE BROKER SETTINGS
		$consumeExchange 	= 'database_exchange';	// Exchange Name
                $exchangeType		= 'direct';		// Exchange Type
		$frontendQueue	 	= 'FE_signup_mailbox';	// Queue Name
                $signupBK 		= 'newUser-Frontend';	// BINDING KEY MATCHES PUBLSIHER'S ROUTING KEY

		//	OPENING CHANNEL TO COMMUNICATE
            	$channelValidSignup = $connectionValidSignup->channel();

		//	DECLARING DURABLE EXCHANGE
            	$channelValidSignup->exchange_declare(
					$consumeExchange,
					$exchangeType,
					false,		// PASSIVE
					true,		// DURABLE
					false		// AUTO-DELETE
		);

		//      DECLARING DURABLE QUEUE
            	$channelValidSignup->queue_declare(
					$frontendQueue,
					false,		// PASSIVE
					true,		// DURABLE
					false,		// EXCLUSIVE
					false		// AUTO-DELETE
		);

		//      CREATING LINK BETWEEN EXCHANGE AND QUEUE TO GET MESSAGES
            	$channelValidSignup->queue_bind(
						$frontendQueue,
						$consumeExchange,
						$signupBK
		);

            	$frontendCallback = function ($signupCondition) use ($channelValidSignup) {

			$decodedDatabase = json_decode($signupCondition->getBody(), true);

                	$regexUser = $decodedDatabase['valid_signup'];
                	$newUser = $decodedDatabase['new_user'];

                	if ($regexUser == 'FALSE') {
                  		echo "\n[Incorrect format for Registration Info]\n";
                    		echo "<script>location.href='signup.php';</script>";
                	}

                	if ($regexUser == 'TRUE') {
                    		if ($newUser == 'FALSE') {
                        		echo "\n[Successfully Registered!]\n";
					echo "<script>alert('WELCOME TO FOODQUEST!');</script>";
                        		header("Location: successReg.php");
                        		// Ensure that no further output is sent
                        		//exit();
                    		} else {
                        		echo "\nUsername / Email is already taken!\n";
                        		echo "<script>alert('Username/Email is already taken!');</script>";
                        		echo "<script>location.href='signup.php';</script>";
                    		}
                	}
            };

            $channelValidSignup->basic_consume($frontendQueue, '', false, true, false, false, $frontendCallback);

            while ($channelValidSignup->is_open()) {
                $channelValidSignup->wait();
                break;
            }

            $channelValidSignup->close();
            $connectionValidSignup->close();
        }

	//////////////////////	END OF PHP SERVER POST SESSION	////////////////////////////////////////////////

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



