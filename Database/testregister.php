





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

// Function to hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Assume that you have obtained $username, $password, $email, $firstname, $lastname from the form submission

// Check if the user already exists in the database
$sql_check = "SELECT * FROM users WHERE username = ? OR email = ?";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, "ss", $username, $email);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($result_check) > 0) {
    // User already exists
    echo "User already exists in the database.\n";
    $userExists = true;
} else {
    // User does not exist
    // Hash the password before storing
    $hashedPassword = hashPassword($password);

    // Insert the user data into the database using a prepared statement
    $sql_insert = "INSERT INTO users (username, password, email, firstname, lastname) VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = mysqli_prepare($conn, $sql_insert);
    mysqli_stmt_bind_param($stmt_insert, "sssss", $username, $hashedPassword, $email, $firstname, $lastname);

    if (mysqli_stmt_execute($stmt_insert)) {
        echo "New record created successfully";
        $userExists = false;

        // Insert additional user information into another table
        $sql_insert_additional = "INSERT INTO user_additional_info (username, additional_info_column) VALUES (?, ?)";
        $stmt_insert_additional = mysqli_prepare($conn, $sql_insert_additional);
        $additionalInfo = "Some additional information"; // Replace with the actual data
        mysqli_stmt_bind_param($stmt_insert_additional, "ss", $username, $additionalInfo);
        
        if (mysqli_stmt_execute($stmt_insert_additional)) {
            echo "Additional user information inserted successfully";
        } else {
            echo "Error: " . $sql_insert_additional . "<br>" . mysqli_stmt_error($stmt_insert_additional);
        }
    } else {
        echo "Error: " . $sql_insert . "<br>" . mysqli_stmt_error($stmt_insert);
    }
}

// Close the prepared statements
mysqli_stmt_close($stmt_check);
mysqli_stmt_close($stmt_insert);
mysqli_stmt_close($stmt_insert_additional);

// Close the database connection
mysqli_close($conn);
