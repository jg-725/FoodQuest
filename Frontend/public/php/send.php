<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
require_once("../../includes/database/connect.db.php");
require_once("../../includes/functions/chat.func.php");

if(isset($_GET["sender"]) && !empty($_GET["sender"])){
  $sender = $_GET["sender"];
  if(isset($_GET["message"]) && !empty($_GET["message"])){
    $message = $_GET["message"];

    if(send_msg($sender, $message)){
      echo "Message successfully sent!";
    } else{
      echo "Failed to send message";
    }

  }
} else{
  echo "Details are incomplete";
}

?>
