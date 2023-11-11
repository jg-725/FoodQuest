<?
if(isset($_SESSION["username"])) { //is the user already logged in?

redirect_to('login.php')

}

header("location:login.php")

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
  <form role="form" name="signup" action="testRabbitMQClient2.php" method="post" onSubmit="return validateForm();" >
    
    
    <input type="text" placeholder="Username" name="username_" id="username" class="username" required="required" aria-describedby="usernameHelp" />
    
         <input type="password" placeholder="Password" id="new_password" name="new_password_" class="password" required="required" />
         
         
         <input type="password" placeholder="Confirm Password" class="password" id="confirm_password" name="confirm_password_" autocomplete="new-password" required="required" />
         <input type="test" placeholder="First Name" class="Fname" id="Fname" name="first_name_" autocomplete="first-name" required="required">
           
    <input type="test" placeholder="Last Name" class="Lname" id="Lname" name="last_name_" autocomplete="last-name" required="required" />
                      
         <input type="email" placeholder="Email" class="Email" id="Email" name="email_" autocomplete="email" required="required" />
                         
          <input type="test" placeholder="Address" class="Address" id="Address" name="address_" autocomplete="address" required="required" />

<input type="test" placeholder="Phone Number" class="Pnumber" id="Pnumber" name="pnumber_" autocomplete="phone-number" required="required" onkeypress="return isNumberKey(event)" />
<br />
<br/ >


    <button type="submit" class="login" name="submit">Sign Up</button>
  </form>
</div>


</div>

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
