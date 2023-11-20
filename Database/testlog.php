





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

// Check if the user exists in the database
$sql_check = "SELECT * FROM users WHERE BINARY username = '$username'";
$result = mysqli_query($conn, $sql_check);

if (mysqli_num_rows($result) > 0) {
    // User exists, retrieve the password
    $row = mysqli_fetch_assoc($result);
    $hash = $row['password'];
    $userFound = true;
    $id = $row['id'];
} else {
    // User does not exist
    $userFound = false;
    $hash = null;
}

// Close the database connection
mysqli_close($conn);

// Send a message to the backend with the authentication information
$dbmessageBody = json_encode([
    'userFound' => $userFound,
    'hash' => $hash,
    'password' => $password,
    'username' => $username,
    'id' => $id
]);

// Connect to the database for login messages
$loginMessagesConn = mysqli_connect($servername, $username_db, $password_db, $dbname);

if (!$loginMessagesConn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Retrieve login messages from FoodQuest table
$loginMessagesQuery = "SELECT Sender, Message FROM FoodQuest ORDER BY Msg_ID DESC";
$loginMessagesResult = mysqli_query($loginMessagesConn, $loginMessagesQuery);

$loginMessages = [];
while ($loginMessage = mysqli_fetch_assoc($loginMessagesResult)) {
    $loginMessages[] = ['sender' => $loginMessage['Sender'], 'message' => $loginMessage['Message']];
}

// Close the connection for login messages
mysqli_close($loginMessagesConn);

// Return login messages
return $loginMessages;
