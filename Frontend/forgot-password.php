<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<link rel="stylesheet" href="css/forgot-password.css">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

<div id="highlighted" class="hl-basic hidden-xs">
   <div class="container-fluid">
      <div class="row">
         <div class="col-sm-9 col-sm-offset-3 col-md-9 col-md-offset-3 col-lg-10 col-lg-offset-2">
            <h1>
               Forgot Password
            </h1>
         </div>
      </div>
   </div>
</div>

<div id="content" class="interior-page">
<div class="container-fluid">
<div class="row">
<!--Sidebar-->
<div class="col-sm-3 col-md-3 col-lg-2 sidebar equal-height interior-page-nav hidden-xs">
   <div class="dynamicDiv panel-group" id="dd.0.1.0">
      <div id="subMenu" class="panel panel-default">
         <ul class="subMenuHighlight panel-heading">
            <li class="subMenuHighlight panel-title" id="subMenuHighlight">
               <a id="li_291" class="subMenuHighlight" href="signup.php"><span>Register</span></a>
            </li>
         </ul>
         <ul class="panel-heading">
            <li class="panel-title">
               <a class="subMenu1" href=""><span class="subMenuHighlight">Forgot Password</span></a>
            </li>
         </ul>
         <ul class="panel-heading">
            <li class="panel-title">
               <a class="subMenu1" href="login.php"><span>Login</span></a>
            </li>
         </ul>
      </div>
     
   </div>
</div>

<!--Content-->
<div class="col-sm-9 col-md-9 col-lg-10 content equal-height">
  <div class="content-area-right">
   <div class="content-crumb-div">
      <a href="index.php">Home</a> / <a href="">Your Account</a> / Forgot Password
   </div>
      <div class="row">
         <div class="col-md-5 forgot-form">
            <p>
               Please enter your email address below and we will send you information to change your password.
            </p>
            <label class="label-default" for="un">Email Address</label> <input id="email_addy" name="email_addy" class="form-control" type="text"><br>
            <a id="mybad" class="btn btn-primary" role="button">RESET</a>
         </div>
         <div class="col-md-5 forgot-return" style="display:none;">
            <h3>
               Reset Password Sent
            </h3>
            <p>
               An email has been sent to your address with a reset password you can use to access your account.
            </p>
         </div>
      </div>
   </div>
</div>

<?php
// Add your database connection code here if needed
// Required PHP and AMQP Libraries to interact with RabbitMQ
            require_once '/var/www/gci/FrontEnd/vendor/autoload.php';
            use PhpAmqpLib\Connection\AMQPStreamConnection;
            use PhpAmqpLib\Message\AMQPMessage;



if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);

    // Validate the email address (you might want to add more robust validation)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email address";
        exit;
    }

    // Generate a unique token (you might want to use a library for this)
    $token = bin2hex(random_bytes(32));

    // Store the token in your database along with the user's email
    // Add your database update code here if needed

    // Send a password reset email to the user
    $resetLink = "https://yourwebsite.com/reset_password.php?email=$email&token=$token";
    $subject = "Password Reset";
    $message = "Click the following link to reset your password:\n\n$resetLink";
    $headers = "From: your-email@example.com"; // Change this to your email

    // Uncomment the following line when you're ready to send emails
    // mail($email, $subject, $message, $headers);

    echo "Password reset instructions have been sent to your email.";
}
?>






