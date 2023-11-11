<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);

require_once("../../includes/database/connect.db.php");
require_once("../../includes/functions/chat.func.php");
require_once("../../includes/functions/sessions.php");

$messages=get_msg();
foreach($messages as $message)
{
	if($_SESSION["username"] == $message["sender"]){
			echo '<li class="list-group-item active">';
	} else{
		echo '<li class="list-group-item">';			
	}
	echo '<strong>'.$message['sender'].'</strong><br />';
	echo $message['message'].'<br /><br />';
	echo '</li>';
}

?>
