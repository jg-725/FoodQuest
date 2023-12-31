<?php
session_start(); // Start the session

// Checks if the user is logged in. If they are, redirect them to the home page as register.php should not be accessable to logged in users.
if (!isset($_SESSION["username"]) && !isset($_SESSION["userID"])) {
  die(header("Location: login.php")); // Redirect to login page if user is not logged in
}

?>


<!DOCTYPE html>
<html class="wide wow-animation" lang="en">
  <head>
    <title>Home</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, height=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" href="images/favicon.png" type="image/x-icon">
	
    <!-- <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700,900"> -->
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/style.css">
     <link rel="stylesheet" type="text/css" href="css/style2.css">
    <style>.ie-panel{display: none;background: #212121;padding: 10px 0;box-shadow: 3px 3px 5px 0 rgba(0,0,0,.3);clear: both;text-align:center;position: relative;z-index: 1;} html.ie-10 .ie-panel, html.lt-ie-10 .ie-panel {display: block;}
    </style>
    <style>
        .uppercase-text {
            text-transform: uppercase;
        }
    </style>

    
  </head>
  <body>
			
  
  
    <div class="ie-panel"><a href="http://windows.microsoft.com/en-US/internet-explorer/"><img src="images/ie8-panel/warning_bar_0000_us.jpg" height="42" width="820" alt="You are using an outdated browser. For a faster, safer browsing experience, upgrade for free today."></a></div>
    <div class="preloader">
      <div class="loader">
        <div class="ball"></div>
        <div class="ball"></div>
        <div class="ball"></div>
      </div>
    </div>
    <div class="page">
	
	
      <section class="d-none d-xl-block"><img class="img-responsive" src="images/banner-top-2050x60.png" alt="" width="2050" height="30"/></a></section>
      <div class="position-relative">
        <header class="section page-header" id="programs">
          <!--RD Navbar-->
          <div class="rd-navbar-wrap">
            <nav class="rd-navbar rd-navbar-classic context-dark" data-layout="rd-navbar-fixed" data-sm-layout="rd-navbar-fixed" data-md-layout="rd-navbar-fixed" data-lg-layout="rd-navbar-fixed" data-xl-layout="rd-navbar-static" data-xxl-layout="rd-navbar-static" data-md-device-layout="rd-navbar-fixed" data-lg-device-layout="rd-navbar-fixed" data-xl-device-layout="rd-navbar-static" data-xxl-device-layout="rd-navbar-static" data-lg-stick-up-offset="10px" data-xl-stick-up-offset="10px" data-xxl-stick-up-offset="10px" data-lg-stick-up="true" data-xl-stick-up="true" data-xxl-stick-up="true">
              <div class="rd-navbar-main-outer">
                <div class="rd-navbar-main">
                  <!--RD Navbar Panel-->
                  <div class="rd-navbar-panel">
                    <!--RD Navbar Toggle-->
                    <button class="rd-navbar-toggle" data-rd-navbar-toggle=".rd-navbar-nav-wrap"><span></span></button>
                    <!--RD Navbar Brand-->
                    <div class="rd-navbar-brand">
                      <!--Brand--><a class="brand" href="index.php"><img class="brand-logo-dark" src="images/logo-default-363x100.png" alt="" width="181" height="50"/><img class="brand-logo-light" src="images/logo-inverse-363x100.png" alt="" width="181" height="50"/></a>
                    </div>
                  </div>
                  <div class="rd-navbar-nav-wrap">
                    <ul class="rd-navbar-nav">
 <li class="rd-nav-item"><a class="rd-nav-link">Welcome : &nbsp; <p class="uppercase-text"> <?php echo  $_SESSION["username"]; ?> </p> </a>
                      </li>                     
                     
  <li class="rd-nav-item"><a class="rd-nav-link" href="#about">About</a>
                      </li>
                      <li class="rd-nav-item"><a class="rd-nav-link" href="#clients">Clients</a> </il>                    
                      
                    
                     <li class="rd-nav-item"><a class="rd-nav-link" href="logout.php">Logout</a>
                      </li> 
                      </ul>
                  </div>
                </div>
              </div>
              <button class="rd-navbar-aside-open-toggle" data-custom-toggle="#rd-navbar-aside"></button>
              <div class="rd-navbar-aside" id="rd-navbar-aside">
              
            
              
                <h3>Write your review about our service</h3>
                

                <!--RD Mailform-->
                <form class="rd-form rd-mailform" data-form-output="form-output-global" data-form-type="contact" method="POST">
                 
                  <div class="row row-22">
                
                 <div class="rate">
                  <h3>Rating</h3>
    <input type="radio" id="star5" name="rate" value="5" />
    <label for="star5" title="text">5 stars</label>
    <input type="radio" id="star4" name="rate" value="4" />
    <label for="star4" title="text">4 stars</label>
    <input type="radio" id="star3" name="rate" value="3" />
    <label for="star3" title="text">3 stars</label>
    <input type="radio" id="star2" name="rate" value="2" />
    <label for="star2" title="text">2 stars</label>
    <input type="radio" id="star1" name="rate" value="1" />
    <label for="star1" title="text">1 star</label>
  </div>
 
                 
                    <div class="col-12">
                      <div class="form-wrap">
                       
                      
                      
                        <!-- Select 2-->
                        <!--
                        <select class="form-input select" data-placeholder="Choose a food package" data-constraints="@Required">
                          <option label="Choose a food package"></option>
                          <option value="1">Detox</option>
                          <option value="2">Balanced</option>
                          <option value="3">Vegan</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="form-wrap">
                        <input class="form-input" id="contact-email" type="email" name="email" data-constraints="@Email @Required">
                        <label class="form-label" for="contact-email">E-mail</label>
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="form-wrap">
                        <input class="form-input" id="contact-phone" type="text" name="phone" data-constraints="@Numeric">
                        <label class="form-label" for="contact-phone">Phone</label>
                        -->
                      </div>
                     
                    </div>
				
					<div class="col-12">
					<div class="new">
		<!--							<form>
				<div class="form-group">
									  <input type="checkbox" id="Cheddar Cheese">
									  <label for="Cheddar Cheese">Cheddar Cheese</label>
									</div>
									<div class="form-group">
									  <input type="checkbox" id="Broccoli">
									  <label for="Broccoli">Broccoli</label>
									</div>
									<div class="form-group">
									  <input type="checkbox" id="Avocado">
									  <label for="Avocado">Avocado</label>
									  
       								  
									</div>
								  </form>
								  -->
								</div>
								
					</div>
					
                    <div class="col-12">
                    
                   
                      <div class="form-wrap">
                      
                      
                        <label class="form-label" for="contact-message">Comment</label>
                        <textarea class="form-input" id="contact-message" name="message" data-constraints="@Required"></textarea>
                      </div>
                    </div>
					
                    <div class="col-12">
                      <button class="button button-primary" type="submit">Submit now</button>
                    </div>
                  </div>
                </form>
              </div>
            </nav>
          </div>
        </header>


	
	<?php
/*		RABBITMQ CODE TO SEND USER FEEDBACK TO BACKEND		*/

	// Required PHP and AMQP Libraries to interact with RabbitMQ
        require_once '/var/www/gci/FrontEnd/vendor/autoload.php';
	use PhpAmqpLib\Connection\AMQPStreamConnection;
        use PhpAmqpLib\Message\AMQPMessage;


	// Server request POST initialized to trigger login request flow - IF statement
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

		//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
		$feedbackConnection = null;
		$rabbitNodes = array('192.168.194.2', '192.168.194.1');
		$port = 5672;
		$user = 'foodquest';
		$pass = 'rabbit123';

		foreach ($rabbitNodes as $node) {
    			try {
        			$feedbackConnection = new AMQPStreamConnection(
							$node,
							$port,
							$user,
							$pass
				);
        			echo "FRONTEND HOMEPAGE CONNECTION TO RABBITMQ WAS SUCCESSFUL @ $node\n";
        			break;
    			} catch (Exception $e) {
        			continue;
    			}
		}

		if (!$feedbackConnection) {
    			die("FRONTEND HOMEPAGE CONNECTION ERROR: COULD NOT CONNECT TO ANY RABBITMQ NODE");
		}

		//      RABBITMQ MESSAGE BROKER SETTINGS
		$publishExchange = 'frontend_exchange';  // Exchange Name
                $exchangeType 	 = 'direct';		// Exchange Type
                $feedbackRK 	 = 'feedback-backend';	// ROUTING KEY: BACKEND ADDRESS

		//      ACTIVING MAIN CHANNEL TO SEND REQUESTS
		$feedbackChannel = $feedbackConnection->channel();

		//      DECLARING EXCHANGE THAT WILL ROUTE MESSAGES FROM FRONTEND
		$feedbackChannel->exchange_declare(
					$publishExchange,
					$exchangeType,
					false,	// PASSIVE
					true,	// DURABLE
					false	// AUTO-DELETE
		);

		$userID = $_SESSION['userID'];
        	$message = $_POST['message'];
        	$rating = $_POST['rate'];

		//	ARRAY TO STORE USER REVIEW POST request
        	$feedbackArray = array();
                	if (empty($feedbackArray)) {    // Check if array is empty

			$feedbackArray['user_id'] = $userID;
                    	$feedbackArray['message'] = $message;
                    	$feedbackArray['rating'] = $rating;
        	}

		//	Turning array into JSON for compatibility
       		$encodedFeedback = json_encode($feedbackArray);

		//	Creating AMQPMessage protocol once login data is ready for delivery
        	$feedbackMsg = new AMQPMessage(
        				$encodedFeedback,
                    			array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
        	);

		//	Publishing message to backend exchange using binding key indicating the receiver
        	$feedbackChannel->basic_publish(
					$feedbackMsg,
					$publishExchange,
					$feedbackRK
		);

		// Message that shows login workflow was triggered
                echo ' [x] FRONTEND TASK: SENT USER FEEDBACK TO BACKEND', "\n";
                print_r($feedbackArray);
                echo "\n\n";

		// Terminating sending channel and connection
                $feedbackChannel->close();
                $feedbackConnection->close();


		///////////////////////////////////////////////////////////////////////////////////

		/*  	--- THIS SECTION WILL CONSUME MESSAGES FROM DATABASE TO REDIRECT ---	*/

		//      RABBITMQ CONNECTION SETTINGS
                $successFeedbackConnection = null;
                $rabbitNodes = array('192.168.194.2', '192.168.194.1');
                $port = 5672;
                $user = 'foodquest';
                $pass = 'rabbit123';

		//      IMPLEMENTING RABBITMQ FAIL OVER CONNECTION
                foreach ($rabbitNodes as $node) {
                        try {
                                $successFeedbackConnection = new AMQPStreamConnection(
                                                                $node,
                                                                $port,
                                                                $user,
                                                                $pass
                                );
                                echo "HOME.PHP CONNECTION TO RABBITMQ WAS SUCCESSFUL @ $node\n";
				break;
			} catch (Exception $e) {
                                continue;
                        }
                }

		if (!$successFeedbackConnection) {
                        die("CONNECTION ERROR: FRONTEND COULD NOT CONNECT TO RABBITMQ NODE.");
                }

		//      RABBITMQ MESSAGE BROKER SETTINGS
                $consumeExchange = 'database_exchange';	// Exchange Name
                $exchangeType 	 = 'direct';		// Exchange Type
		$queueName	 = 'FE_feedback_mailbox';	// Queue Name
                $homeBK 	 = 'feedback-frontend';	// BINDING KEY MATCHES FEEDBACK DATABASE ROUTING KEY


		$successFeedbackChannel = $successFeedbackConnection->channel();


		$successFeedbackChannel->exchange_declare(
						$consumeExchange,
						$exchangeType,
						false,		// PASSIVE
						true,		// DURABLE
						false		// AUTO-DELETE
		);

		//  	DECLARING durable queue: third parameter TRUE
                $successFeedbackChannel->queue_declare(
						$queueName,
						false,
						true,
						false,
						false
		);

		//     Binding corresponding queue and exchange
                $successFeedbackChannel->queue_bind(
						$queueName,
						$consumeExchange,
						$homeBK
		);


		// Establishing callback variable for processing messages from database
		$feedbackCallback = function ($feedbackMsg) use ($successFeedbackChannel) {

			// Decoding received msg from database into usable code for processing
                	$decodedMsg = json_decode($feedbackMsg->getBody(), true);

			$feedbackCheck = $decodedMsg['message'];

			// REDIRECT TO DISPLAY SUCCESSFUL FEEDBACK PAGE
			if ($feedbackCheck = 'TRUE') {
			
	echo "<script>alert('YOUR FEEDBACK WAS SENT TO FOODQUEST!');</script>";
				header("Location: commentReg.php");
				
			}
			else {
			echo "<script>alert('ERROR IS PROCESSING YOUR FEEDBACK, PLEASE TRY AGAIN');</script>";
			//die(header("Location: commentReg.php"));
			
			}
		};

		// Triggering the process to consume msgs from DATABASE IF USER EXISTS
                $successFeedbackChannel->basic_consume(
						$queueName,
						'',
						false,
						true,
						false,
						false,
						$feedbackCallback
		);

		// while loop to keep checking for incoming messages from the database
                while ($successFeedbackChannel->is_open()) {
                    $successFeedbackChannel->wait();
                    break;
                }

		// Terminating MAIN Channel and Connection for receiving msgs
                $successFeedbackChannel->close();
                $successFeedbackConnection->close();

	}
	?>

        <!--Swiper-->
        <section class="section swiper-container swiper-slider bg-primary" data-autoplay="3500" data-loop="false" data-simulate-touch="false" data-effect="circle-bg" data-speed="750">
          <div class="swiper-bg-text">Food</div>
          <div class="swiper-wrapper">
            <div class="swiper-slide" data-circle-cx=".855" data-circle-cy=".5" data-circle-r=".39">
              <div class="swiper-slide-caption section-md">
                <div class="container">
                  <div class="row row-50 align-items-center swiper-custom-row">
                    <div class="col-lg-5">
                      <h3 class="subtitle" data-swiper-anime='{"animation":"swiperContentRide","duration":900,"delay":900}'>Welcome to Plate</h3>
                      <h1 data-swiper-anime='{"animation":"swiperContentRide","duration":1000,"delay":1000}' data-subtext="#1">delivery service</h1>
                      <p class="big" data-swiper-anime='{"animation":"swiperContentRide","duration":1100,"delay":1100}'>Plate offers quick delivery of organic &amp; healthy food all over the state.</p>
                      <p class="postitle" data-swiper-anime='{"animation":"swiperContentRide","duration":1200,"delay":1200}'>Why Choose Plate</p>
                    </div>
                    <div class="box-round-wrap"><img src="images/slider-01-671x671.png" alt="" width="671" height="335" data-swiper-anime='{"animation":"swiperContentFade","duration":1000,"delay":1000}'/>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="swiper-slide" data-circle-cx=".855" data-circle-cy=".5" data-circle-r=".39">
              <div class="swiper-slide-caption section-md">
                <div class="container">
                  <div class="row row-50 align-items-center swiper-custom-row">
                    <div class="col-lg-5">
                      <h3 class="subtitle" data-swiper-anime='{"animation":"swiperContentRide","duration":900,"delay":900}'>Delivery throughout the Country</h3>
                      <h1 data-swiper-anime='{"animation":"swiperContentRide","duration":1000,"delay":1000}' data-subtext="250">supported locations</h1>
                      <p class="big" data-swiper-anime='{"animation":"swiperContentRide","duration":1100,"delay":1100}'>Our food delivery service is available in more than 200 cities for your comfort.</p>
                      <p class="postitle" data-swiper-anime='{"animation":"swiperContentRide","duration":1200,"delay":1200}'>Nationwide Delivery</p>
                    </div>
                    <div class="box-round-wrap"><img src="images/slider-02-671x671.png" alt="" width="671" height="335" data-swiper-anime='{"animation":"swiperContentFade","duration":1000,"delay":1000}'/>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="swiper-slide" data-circle-cx=".855" data-circle-cy=".5" data-circle-r=".39">
              <div class="swiper-slide-caption section-md">
                <div class="container">
                  <div class="row row-50 align-items-center swiper-custom-row">
                    <div class="col-lg-5">
                      <h3 class="subtitle" data-swiper-anime='{"animation":"swiperContentRide","duration":900,"delay":900}'>Best dishes &amp; ingredients</h3>
                      <h1 data-swiper-anime='{"animation":"swiperContentRide","duration":1000,"delay":1000}' data-subtext="100%">Delicious Food</h1>
                      <p class="big" data-swiper-anime='{"animation":"swiperContentRide","duration":1100,"delay":1100}'>Plate regularly updates the menus to make sure our customers eat the best and the tastiest food.</p>
                      <p class="postitle" data-swiper-anime='{"animation":"swiperContentRide","duration":1200,"delay":1200}'>Organic Ingredients</p>
                    </div>
                    <div class="box-round-wrap"><img src="images/slider-03-671x671.png" alt="" width="671" height="335" data-swiper-anime='{"animation":"swiperContentFade","duration":1000,"delay":1000}'/>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="swiper-slide" data-circle-cx=".855" data-circle-cy=".5" data-circle-r=".39">
              <div class="swiper-slide-caption section-md">
                <div class="container">
                  <div class="row row-50 align-items-center swiper-custom-row">
                    <div class="col-lg-5">
                      <h3 class="subtitle" data-swiper-anime='{"animation":"swiperContentRide","duration":900,"delay":900}'>Our clients trust us</h3>
                      <h1 data-swiper-anime='{"animation":"swiperContentRide","duration":1000,"delay":1000}' data-subtext="3k">Positive Reviews</h1>
                      <p class="big" data-swiper-anime='{"animation":"swiperContentRide","duration":1100,"delay":1100}'>Our clients’ reviews are the best way to learn more about our food delivery service.</p>
                      <p class="postitle" data-swiper-anime='{"animation":"swiperContentRide","duration":1200,"delay":1200}'>Reviews &amp; Testimonials</p>
                    </div>
                    <div class="box-round-wrap"><img src="images/slider-04-671x671.png" alt="" width="671" height="335" data-swiper-anime='{"animation":"swiperContentFade","duration":1000,"delay":1000}'/>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!--Swiper Pagination-->
          <div class="swiper-pagination"></div>
        </section>
      </div>
      <!-- About-->
      <section class="section novi-bg novi-bg-img section-sm bg-gray-100 pb-xl-0" id="about">
        <div class="container">
          <div class="row row-50 flex-wrap-md-reverse flex-lg-wrap align-items-lg-center">
            <div class="col-xl-6 col-lg-6">
              <div class="box-custom-2"><img src="images/shutter-home-01-455x685.png" alt="" width="455" height="342"/>
                <div class="box-custom-2-smal">
                  <p class="box-custom-2-name">Jason Fox</p>
                  <p class="box-custom-2-position">Head Chef</p>
                </div>
              </div>
            </div>
            <div class="col-xl-5 col-lg-6">
              <div class="box-custom-1">
                <h3>About us</h3>
                <h2>healthy food <br> can be delicious</h2>
                <p>Plate was established in 2013, as a reliable food service provider where anyone could order healthy food they like.</p>
                <ul class="list-marked">
                  <li>Choose gluten-free meals from our menu</li>
                  <li>The freshest ingredients for every dish</li>
                  <li>Get varity of ingreadients from our menu</li>
                </ul><a class="button button-primary" href="#">read more</a>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- What we Offer-->
      <section class="section novi-bg novi-bg-img section-md-4 bg-primary">
        <div class="container">
          <div class="text-center">
            <h3>What we Offer</h3>
            <h2>Free ingreadient to download</h2>
          </div>
          <div class="row row-50 justify-content-center">
            <div class="col-xl-4 col-md-6">
              <!-- Product-->
              <div class="product novi-bg bg-default">
                <h3 class="product-title">detox</h3><img class="product-img" src="images/product-01-176x176.png" alt="" width="176" height="88"/>
                <div class="product-price">
                  <div class="product-price-header">
                    <div class="product-price-currency">$</div>
                    <div class="product-price-value">15</div>
                  </div>
                  <div class="product-price-footer">per day</div>
                </div>
                <p class="product-text">The best choice if you are looking for tasty &amp; light yet healthy food to start your day full of energy.</p><a class="button button-primary" onclick="redirectToURL()">View other recipes</a>
                <div><a class="link-border" onclick="redirectsToURL()">click to download recipe (pdf)</a></div>
              </div>
            </div>
            <div class="col-xl-4 col-md-6">
              <!-- Product-->
              <div class="product novi-bg bg-default">
                <h3 class="product-title">balanced</h3><img class="product-img" src="images/product-02-176x176.png" alt="" width="176" height="88"/>
                <div class="product-price">
                  <div class="product-price-header">
                    <div class="product-price-currency">$</div>
                    <div class="product-price-value">30</div>
                  </div>
                  <div class="product-price-footer">per day</div>
                </div>
                <p class="product-text">If you need daily balanced menu including breakfast &amp; dinner, then Balanced package is what you need!</p><a class="button button-primary" onclick="redirectToURL()">View other recipes</a>
                <div><a class="link-border" onclick="redirectssToURL()">click to download recipe (pdf)</a></div>
              </div>
            </div>
            <div class="col-xl-4 col-md-6">
              <!-- Product-->
              <div class="product novi-bg bg-default">
                <h3 class="product-title">Vegan</h3><img class="product-img" src="images/product-03-176x176.png" alt="" width="176" height="88"/>
                <div class="product-price">
                  <div class="product-price-header">
                    <div class="product-price-currency">$</div>
                    <div class="product-price-value">22</div>
                  </div>
                  <div class="product-price-footer">per day</div>
                </div>
                <p class="product-text">Special menu developed for our vegan clients who appreciate healthy and plant-based food.</p><a class="button button-primary" onclick="redirectToURL()">View other recipes</a>
                <div><a class="link-border" onclick="redirectsssToURL()">click to download recipe (pdf)</a></div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- How it Works-->
      <section class="section novi-bg novi-bg-img section-md-2 bg-default">
        <div class="container">
          <div class="text-center">
            <h3>how it works</h3>
            <h2>3 steps to healthy eating</h2>
          </div>
          <div class="row row-50 post-classic-counter justify-content-lg-between justify-content-center">
            <div class="col-lg-4 col-sm-6">
              <div class="post-classic novi-bg bg-secondary-1">
                <h3 class="post-classic-title">choose <br> a food package</h3>
                <p class="post-classic-text">First, select the package that you prefer the most.</p>
              </div>
            </div>
            <div class="col-lg-4 col-sm-6">
              <div class="post-classic novi-bg bg-secondary-2">
                <h3 class="post-classic-title">customize your menu</h3>
                <p class="post-classic-text">After that, feel free to change and customize your menu.</p>
              </div>
            </div>
            <div class="col-lg-4 col-sm-6">
              <div class="post-classic novi-bg bg-secondary-3">
                <h3 class="post-classic-title">Select a delivery <br> address & time</h3>
                <p class="post-classic-text">Finally, let us know where and when to deliver your food.</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Advantages-->
      <section class="section novi-bg novi-bg-img section-custom section-lg bg-primary">
        <div class="container">
          <div class="row row-fix">
            <div class="col-lg-7">
              <div class="row row-40">
                <div class="col-md-6">
                  <div class="box-icon">
                    <div class="box-icon-header">
                      <div class="icon novi-icon icon-lg linearicons-diamond2"></div>
                      <h3 class="box-icon-title">Quality</h3>
                    </div>
                    <p class="box-icon-text">We work with the best suppliers to make sure you get the top-quality dishes, beverages, and service.</p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="box-icon">
                    <div class="box-icon-header">
                      <div class="icon novi-icon icon-lg linearicons-leaf"></div>
                      <h3 class="box-icon-title">organic</h3>
                    </div>
                    <p class="box-icon-text">All ingredients we use are 100% organic and fresh. Such approach makes our food a lot healthier.</p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="box-icon">
                    <div class="box-icon-header">
                      <div class="icon novi-icon icon-lg linearicons-chef"></div>
                      <h3 class="box-icon-title">tasty</h3>
                    </div>
                    <p class="box-icon-text">Great and unforgettable taste of our dishes is what attracts more and more clients to Plate.</p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="box-icon">
                    <div class="box-icon-header">
                      <div class="icon novi-icon icon-lg linearicons-egg2"></div>
                      <h3 class="box-icon-title">Diverse</h3>
                    </div>
                    <p class="box-icon-text">Our team regularly updates the menus to provide you with better food diversity whenever you order.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="box-custom-3">
          <div class="box-custom-3-img-wrap"><img src="images/home-01-382x375.png" alt="" width="382" height="187"/>
          </div>
          <div class="box-custom-3-img-wrap"><img src="images/home-02-293x293.png" alt="" width="293" height="146"/>
          </div>
          <div class="box-custom-3-img-wrap"><img src="images/home-03-461x407.png" alt="" width="461" height="203"/>
          </div>
          <div class="box-custom-3-img-wrap"><img src="images/home-04-191x191.png" alt="" width="191" height="95"/>
          </div>
        </div>
      </section>

      <!-- Testimonials-->
      <section class="section novi-bg novi-bg-img section-md-3 bg-default" id="clients">
        <div class="container">
          <div class="row row-40 align-items-center">
            <div class="col-lg-6">
              <div class="owl-pagination-custom" id="owl-pagination-custom">
                <div class="data-dots-custom" data-owl-item="0"><img src="images/shutter-testimonials-01-179x179.png" alt="" width="179" height="89"/>
                </div>
                <div class="data-dots-custom" data-owl-item="1"><img src="images/shutter-testimonials-02-306x306.png" alt="" width="306" height="153"/>
                </div>
                <div class="data-dots-custom" data-owl-item="2"><img src="images/testimonials-03-179x179.png" alt="" width="179" height="89"/>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <h3>what Our clients say</h3>
              <h2>testimonials</h2>
              <!-- Owl Carousel-->
              <div class="quote-classic-wrap">
                <div class="quote-classic-img"><img src="images/quote-37x29.png" alt="" width="37" height="14"/>
                </div>
                <div class="owl-carousel owl-carousel-classic" data-items="1" data-dots="true" data-loop="false" data-autoplay="false" data-mouse-drag="false" data-dots-custom="#owl-pagination-custom">
                  <div class="quote-classic">
                    <p class="big">I have tried a lot of food delivery services but FoodQuest is something out of this world! Their food is really healthy and it tastes great, which is why I recommend this company to all my friends!</p>
                    <h3 class="quote-classic-name">Sophie Smith</h3>
                  </div>
                  <div class="quote-classic">
                    <p class="big">Both the food and your customer service are excellent in every way, and I just wanted to express how happy I am with your company. Wishing you all the best!</p>
                    <h3 class="quote-classic-name">Ann peters</h3>
                  </div>
                  <div class="quote-classic">
                    <p class="big">Thank you so much for your Balanced menu, it has been such a big help to me and I feel the food I am eating from you has really helped boost my immune system.</p>
                    <h3 class="quote-classic-name">Felix Opoku</h3>
                  </div>
                </div>
              </div><a class="button button-primary button-sm" href="#">Send Your Review</a>
            </div>
          </div>
        </div>
      </section>

      <section><img class="img-responsive" src="images/banner-bottom-2050x310.jpg" alt="" width="2050" height="155"/></a></section>
      <footer class="section footer-classic">
        <div class="container">
          <div class="row row-50 justify-content-between">
            <div class="col-xl-3 col-md-6">
              <!--Brand--><a class="brand" href="home.php"><img class="brand-logo-dark" src="images/logo-default-363x100.png" alt="" width="181" height="50"/><img class="brand-logo-light" src="images/logo-inverse-363x100.png" alt="" width="181" height="50"/></a>
              <p class="rights"><span>&copy;&nbsp;</span><span class="copyright-year"></span><span>&nbsp;</span><span>FoodQuest</span><span>.&nbsp;All Rights Reserved. Design by Group10</span></p>
            </div>
            <div class="col-xl-3 col-md-6">
              <p class="footer-classic-title">Contacts</p>
              <ul class="footer-classic-list">
                <li>
                  <ul>
                    <li>
                      <dl class="footer-classic-dl">
                        <dt>Ph.</dt>
                        <dd><a href="tel:#">862-437-2567</a></dd>
                      </dl>
                    </li>
                    <li>
                      <dl class="footer-classic-dl">
                        <dt>Mail.</dt>
                        <dd><a href="mailto:fk82@njit.edu">group10 Project</a></dd>
                      </dl>
                    </li>
                  </ul>
                </li>
                <li><a href="#">235 Washington Ave, Randolph, NJ, 07223</a></li>
                <li>
                  <ul class="group group-sm footer-classic-social-list">
                    <li><a class="link-social" href="#">
                        <div class="icon novi-icon mdi mdi-facebook"></div></a></li>
                    <li><a class="link-social" href="#">
                        <div class="icon novi-icon mdi mdi-instagram"></div></a></li>
                    <li><a class="link-social" href="#">
                        <div class="icon novi-icon mdi mdi-youtube-play"></div></a></li>
                  </ul>
                </li>
              </ul>
            </div>
            <div class="col-xl-2 col-md-6">
              <p class="footer-classic-title">Quick Links</p>
              <ul class="footer-classic-nav">
                <li><a href="#">How It Works</a></li>
                <li><a href="#">About</a></li>
                <li><a href="#">Why Choose Us</a></li>
              </ul>
            </div>
            <div class="col-xl-2 col-md-6">
              <p class="footer-classic-title">Food Packages</p>
              <ul class="footer-classic-nav">
                <li><a href="#">Detox</a></li>
                <li><a href="#">Balanced</a></li>
                <li><a href="#">Vegan</a></li>
              </ul>
            </div>
          </div>
        </div>
      </footer>
    </div>
    <div class="snackbars" id="form-output-global"></div>
    <script src="js/core.min.js"></script>
    <script src="js/script.js"></script>
    <!--coded by kraken-->
    
     <script>
        // JavaScript function to handle the button click event
        function redirectToURL() {
            // Specify the URL you want to redirect to
            var targetURL = "http://127.0.0.1:5000/"; // Replace with your desired URL

            // Use window.location.href to navigate to the specified URL
            window.location.href = targetURL;
        }
    </script>
<script>
        // JavaScript function to handle the button click event
        function redirectsToURL() {
            // Specify the URL you want to redirect to
            var targetURL = "http://127.0.0.1:5000/recipe/1095732?search_query=detox"; // Replace with your desired URL

            // Use window.location.href to navigate to the specified URL
            window.location.href = targetURL;
        }
    </script>
    <script>
        // JavaScript function to handle the button click event
        function redirectssToURL() {
            // Specify the URL you want to redirect to
            var targetURL = "http://127.0.0.1:5000/recipe/645514?search_query=salad"; // Replace with your desired URL

            // Use window.location.href to navigate to the specified URL
            window.location.href = targetURL;
        }
    </script>
    <script>
        // JavaScript function to handle the button click event
        function redirectsssToURL() {
            // Specify the URL you want to redirect to
            var targetURL = "http://127.0.0.1:5000/recipe/1095892?search_query=vegan"; // Replace with your desired URL

            // Use window.location.href to navigate to the specified URL
            window.location.href = targetURL;
        }
    </script>
  </body>
</html>
