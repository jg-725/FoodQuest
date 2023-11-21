<?php


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

// Assume that you have obtained $username, $password, $email, $firstname, $lastname from the form submission

// Check if the user already exists in the database
$sql_check = "SELECT * FROM User WHERE username = '$username' OR email = '$email'";
$result = mysqli_query($conn, $sql_check);

if (mysqli_num_rows($result) > 0) {
    // User already exists
    echo "User already exists in the database.\n";
    $userExists = true;
} else {
    // User does not exist
    // Insert the user data into the database
    $sql = "INSERT INTO User (username, password, email, firstname, lastname) VALUES ('$username', '$password', '$email', '$firstname', '$lastname')";

    if (mysqli_query($conn, $sql)) {
        echo "New record created successfully";
        $userExists = false;

        // Insert additional user information into the User table
        $sql_insert_additional = "INSERT INTO User (username, additional_info_column) VALUES ('$username', 'Some additional information')";
        if (mysqli_query($conn, $sql_insert_additional)) {
            echo "Additional user information inserted successfully";
        } else {
            echo "Error: " . $sql_insert_additional . "<br>" . mysqli_error($conn);
        }
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}

// Close the database connection
mysqli_close($conn);
