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

      require_once '/home/FoodQuest/Frontend/vendor/autoload.php';

      use PhpAmqpLib\Connection\AMQPStreamConnection;
      use PhpAmqpLib\Message\AMQPMessage;

      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
  // Create a connection to RabbitMQ
 
 
 
 }
  ?>
</div>


</div>
    
</body>
</html>
