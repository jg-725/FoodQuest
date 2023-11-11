#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$username = $_POST['username'];
$password = $_POST['password'];
$Fname = $_POST['Fname'];
$Lname = $_POST['Lname'];
$Email = $_POST['Email'];
$Address = $_POST['Address'];
$Pnumber = $_POST['Pnumber'];

//if (  ($username == $result['username'])
      // && ($password == $result['password']) && ($Fname == $result['Fname']) && ($Lname == $result['Lname']) && ($Email == $result['Email'])  && ($Address == $result['Address']) ($Email == $result['Email'])  && ($Pnumber == $result['Pnumber'])){
      
header('Location: login.php');
//exit(0);
//} 
//else{

   //echo " test"

//}
//}
$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
if (isset($argv[1]))
{
  $msg = $argv[1];
}
else
{
  $msg = "test message";
}

$request = array();
$request['type'] = "Register";
$request['username'] = $username;
$request['password'] = $password;
$request['Fname'] = $Fname;
$request['Lname'] = $Lname;
$request['Email'] = $Email;
$request['Address'] = $Address;
$request['Pnumber'] = $Pnumber;
$request['message'] = $msg;
$response = $client->send_request($request);
//$response = $client->publish($request);

echo "client received response: ".PHP_EOL;
print_r($response);
echo "\n\n";

echo $argv[0]." END".PHP_EOL;

