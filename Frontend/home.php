
//ini_set('display_errors', 'On');
//error_reporting(E_ALL);
//require('includes/core.inc.php');

//if(!isset($_SESSION["username"])){ // if NO log-in is detected
	//redirect_to('index.php');
	//exit();
	
//}


<!DOCTYPE html>
<html>
<head>
	<title>Chat Application</title>
	<link rel="stylesheet" type="text/css" href="public/css/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="public/css/main.css" />
</head>
<body>

  <div class="main">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <a class="navbar-brand" href="index.php">chatOn</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
        <div class="navbar-nav">
          <a class="nav-item nav-link" href="logout.php">Log Out </a>
        </div>
      </div>
    </nav>

    <div class="messages row">
      <div id="input" class="col-md-4">
        <p class="alert alert-info">Hello <?php echo $_SESSION["username"] ?> </p>
      	<span id="feedback">

      	</span>
      	<form action="#" method="post" id="form_input">
      	  <input type="text" name="sender" id="sender" value="<?php echo $_SESSION['username'] ?>" hidden>
          <div class="form-group">
            <label for="message">Type Message</label>
            <textarea class="form-control" id="msg" rows="3" name="message"></textarea>
          </div>
      	  <input type="submit" class="btn btn-success" name="send" value="Send Message" id="submit_btn">
      	</form>
      </div>
      <div class="col-md-8">
        <ul class="list-group" id="messages">
        </ul>
      </div>
  </div>

</div>




<script type="text/javascript" src="public/javascript/jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="public/javascript/script.js"></script>
<script type="text/javascript" src="public/javascript/send.js"></script>
</body>
</html>
