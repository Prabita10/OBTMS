<?php
session_start();
if(!isset($_SESSION['username']) || $_SESSION['role'] != 'admin'){
    header("Location: login.html");
    exit();
}

$servername="localhost";
$usernameDB="root";
$passwordDB="root";
$dbname="obtms";
$conn = new mysqli($servername,$usernameDB,$passwordDB,$dbname);
if($conn->connect_error){ die("Connection failed: ".$conn->connect_error); }

$msg = "";
if(isset($_POST['submit'])){
    $bus_name = $_POST['bus_name'];
    $type = $_POST['type'];
    $total_seats = $_POST['total_seats'];
    $fare = $_POST['fare'];

    $sql = "INSERT INTO buses (bus_name,type,total_seats,fare) VALUES ('$bus_name','$type','$total_seats','$fare')";
    if($conn->query($sql)===TRUE){
        $msg="Bus added successfully!";
    } else { $msg="Error: ".$conn->error; }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Bus</title>
</head>
<body>
<h2>Add New Bus</h2>
<?php if($msg!=""){ echo "<p>$msg</p>"; } ?>
<form method="POST">
    <label>Bus Name:</label><br>
    <input type="text" name="bus_name" required><br>
    <label>Type:</label><br>
    <input type="text" name="type" required><br>
    <label>Total Seats:</label><br>
    <input type="number" name="total_seats" required><br>
    <label>Fare:</label><br>
    <input type="number" step="0.01" name="fare" required><br><br>
    <button type="submit" name="submit">Add Bus</button>
</form>
<a href="admin_dashboard.php">Back to Dashboard</a>
</body>
</html>