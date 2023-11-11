<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
//require_once("../includes/database/connect.db.php");

function confirm_query($result){
	if(!$result){
		die("Database Query failed");
	}
}

function redirect_to($new_location){
	header("Location: ".$new_location);
	exit;
}

function get_msg()
{
	global $connection;

	$query="SELECT Sender,Message FROM chat ORDER BY Msg_ID DESC";
	$run=mysqli_query($connection, $query);
	confirm_query($run);
	$messages=array();
	while($message=mysqli_fetch_assoc($run))
	{
		$messages[]= array('sender' =>$message['Sender'] , 'message' =>$message['Message'] );
	}
	return $messages;

}
function send_msg($sender,$message)
{
	global $connection;
	if(!empty($sender) && !empty($message))
	{
		$sender=mysqli_real_escape_string($connection, $sender);
		$message=mysqli_real_escape_string($connection, $message);
		$query="INSERT INTO chat VALUES(null,'{$sender}','{$message}')";
		if($run=mysqli_query($connection, $query))
		{
			return true;
		}
		else{
			return false;
		}
	}
	else{
		return false;
	}
}
?>
