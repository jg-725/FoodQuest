<?php
session_start();
/*
// Checks if the user is logged in. If they are, redirect them to the home page as register.php should not be accessable to logged in users.
if (!isset($_SESSION["username"]) && !isset($_SESSION["userID"])) {
  die(header("Location: login.php")); // Redirect to login page if user is not logged in
}
*/

?>

<!DOCTYPE html>
<html>
<head>
 <link rel="stylesheet" type="text/css" href="public/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="public/css/main.css" />
    
    <script src="js/number.js"></script>
    <link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

	<title>FoodQuest - Successful Login</title>
	<meta charset="utf-8">
  	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="refresh" content="5;url=home.php">
	<script>
		var timeLeft = 3;
		function countdown() {
			if (timeLeft == 0) {
				return;
			}
			document.getElementById("timer").innerHTML = timeLeft + " seconds...";
			timeLeft--;
			setTimeout(countdown, 1000);
		}
	</script>
	<link rel="stylesheet" type="text/css" href="public/css/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="public/css/main.css" />
    
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" href="images/favicon.png" type="image/x-icon">
	
    <!-- <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700,900"> -->
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/style.css">
    <style>.ie-panel{display: none;background: #212121;padding: 10px 0;box-shadow: 3px 3px 5px 0 rgba(0,0,0,.3);clear: both;text-align:center;position: relative;z-index: 1;} html.ie-10 .ie-panel, html.lt-ie-10 .ie-panel {display: block;}
    </style>
    </div>
  </nav>
  <div class="container min-vh-100 py-2">
  	<center>
	  	<h3 style="padding-top:10px;">We have receieved your comment, Thank you!</h3>
		<p>You will be redirected to the login page in <span id="timer"></span></p>
		<iframe src="https://giphy.com/embed/lz67zZWfWPsWnuGH0s" width="480" height="360" frameBorder="0" class="giphy-embed" allowFullScreen></iframe><p><a href="https://giphy.com/gifs/comments-swear-trek-i-read-the-lz67zZWfWPsWnuGH0s"></a></p>


		
	</center>
  </div>
</body>
<script src="script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
</html>
