





// Connect to the database
	$servername = "localhost";
	$username_db = "test";
	$password_db = "test";
	$dbname = "test";

	$conn = mysqli_connect($servername, $username_db, $password_db, $dbname);

	// Check if the connection is successful
	if (!$conn) {
    	die("Connection failed: " . mysqli_connect_error());
	}
	
	// Check if the user already exists in the database
    	$sql_check = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
    	$result = mysqli_query($conn, $sql_check);

    	if (mysqli_num_rows($result) > 0) {
        // User already exists
        	echo "User already exists in the database.\n";
		$userExists = true;
    	} else {
        // User does not exist
	// Insert the user data into the database
	$sql = "INSERT INTO users (username, password, email, firstname, lastname) VALUES ('$username', '$password', '$email', '$firstname', '$lastname')";
	
	if (mysqli_query($conn, $sql)) {
    	
	echo "New record created successfully";
	$userExists = false;
	} else {
    	echo "Error: " . $sql . "<br>" . mysqli_error($conn);
	
	}
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
	// Close the database connection
	mysqli_close($conn);
