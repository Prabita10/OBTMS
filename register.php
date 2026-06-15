<?php
// Database connection
$servername = "localhost";
$usernameDB = "root"; // your MySQL username
$passwordDB = "root";     // your MySQL password
$dbname = "obtms";    // your database name

$conn = new mysqli($servername, $usernameDB, $passwordDB, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect form data
$fullname = $_POST['fullname'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$username = $_POST['username'];
// $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
$password =$password = $_POST['password'];

// Insert into info table
$sql = "INSERT INTO info (fullname, email, phone, username, password)
        VALUES ('$fullname', '$email', '$phone', '$username', '$password')";

if ($conn->query($sql) === TRUE) {
    // Registration successful, redirect to homepage
    header("Location: homepage.html"); // Replace with your homepage file
    exit();
} else {
    // Registration failed, redirect back with error
    $error = "Error in registration: " . $conn->error;
    // Pass error as query string
    header("Location: register.html?error=" . urlencode($error));
    exit();
}

$conn->close();
?>