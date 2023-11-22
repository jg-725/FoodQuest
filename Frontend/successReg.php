<?php
session_start();

// Checks if the user is logged in. If they are, redirect them to the home page as successReg.php should not be accessable to logged in users.
if (isset($_SESSION['username']) && isset($_SESSION["user_id"])) {
  header("Location: home.php");
  exit();
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>PokéHub - Successful Login</title>
	<meta charset="utf-8">
  	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="refresh" content="5;url=login.php">
	<script>
		var timeLeft = 5;
		function countdown() {
			if (timeLeft == 0) {
				return;
			}
			document.getElementById("timer").innerHTML = timeLeft + " seconds...";
			timeLeft--;
			setTimeout(countdown, 1000);
		}
	</script>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
	<link href="style.css" rel="stylesheet" type="text/css" />
</head>
<body onload="countdown()">
<nav class="navbar navbar-expand-md bg-dark-subtle justify-content-start">
    <div class="container-fluid">
      <a class="navbar-brand" href="index.php">
        <img src="PokeHub_FinalLogo2.png" alt="Logo" width="75" height="26.25" class="d-inline-block align-text-top">
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
        <div class="navbar-nav ms-auto">
          <a class="nav-link" href="register.php">Register</a>
          <a class="nav-link" href="login.php">Login</a>
        </div>
        <div style="padding:5px"></div>
        <button class="btn btn-outline-dark" id="btnSwitch">
          <i class="bi bi-sun-fill"></i>
        </button>
      </div>
    </div>
  </nav>
  <div class="container min-vh-100 py-2">
  	<center>
	  	<h1 style="padding-top:10px;">Successful Register</h1>
		<p>You will be redirected to the login page in <span id="timer"></span></p>
		<img class="mx-auto d-block" src="https://media.tenor.com/tZVpbfTIjNMAAAAC/pikachu.gif" width="75%">
		<p>GIF CREDIT: <a href="https://tenor.com/view/pikachu-gif-21053665" target="_blank">Tenor & The Pokémon Company</a></p>
	</center>
  </div>
</body>
<script src="script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
</html>
