<?php
session_start();

$servername="localhost";
$usernameDB="root";
$passwordDB="root";
$dbname="obtms";
$conn = new mysqli($servername,$usernameDB,$passwordDB,$dbname);
if($conn->connect_error){
    die("Connection failed: ".$conn->connect_error); }

$username = $_POST['username'];
$password = $_POST['password'];
$role = $_POST['role'];

if($role === 'user'){
    $table = 'info';
    $dashboard = 'user_dashboard.php';
} 
else {
    $table = 'admin';
    $dashboard = 'admin_dashboard.php';
}

$sql = "SELECT * FROM $table WHERE username='$username'";
$result = $conn->query($sql);

if($result->num_rows === 1){
    $row = $result->fetch_assoc();
    if($password === $row['password']){
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        header("Location: $dashboard");
        exit();
    } else {
        $error = "Invalid username or password.";
        header("Location: login.html?error=".urlencode($error));
        exit();
    }
} else {
    $error = "Invalid username or password.";
    header("Location: login.html?error=".urlencode($error));
    exit();
}

$conn->close();
?>