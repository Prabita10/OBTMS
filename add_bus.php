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
<title>Add New Bus</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body {
    font-family: Arial, sans-serif;
    background:#ffffff;
    margin:0;
    padding:0;
}
header {
    background-color:#1a2b4c;
    color:white;
    padding:15px 30px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}
header h1 { margin:0; font-size:24px; }
header nav a { color:white; text-decoration:none; margin-left:20px; font-weight:bold; }
header nav a:hover { text-decoration:underline; }

.container {
    max-width:600px;
    margin:50px auto;
    background:#fff;
    border-radius:15px;
    padding:30px;
    box-shadow:0 4px 15px rgba(0,0,0,0.2);
}
h2 { color:#1a2b4c; text-align:center; margin-bottom:20px; }
label { display:block; margin-top:10px; font-weight:bold; }
input { width:100%; padding:10px; margin-top:5px; border-radius:5px; border:1px solid #ccc; }
button { width:100%; padding:10px; margin-top:15px; background:#007bff; color:white; border:none; border-radius:5px; cursor:pointer; }
button:hover { background:#0056b3; }
.msg { text-align:center; color:green; font-weight:bold; margin-bottom:15px; }
</style>
</head>
<body>

<header>
    <h1>BusGo Admin</h1>
    <nav>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h2>Add New Bus</h2>
    <?php if($msg!=""){ echo "<div class='msg'>$msg</div>"; } ?>
    <form method="POST">
        <label>Bus Name:</label>
        <input type="text" name="bus_name" placeholder="Enter bus name" required>

        <label>Type:</label>
        <input type="text" name="type" placeholder="Bus type (AC, Non-AC)" required>

        <label>Total Seats:</label>
        <input type="number" name="total_seats" placeholder="Total number of seats" required>

        <label>Fare:</label>
        <input type="number" step="0.01" name="fare" placeholder="Fare per ticket" required>

        <button type="submit" name="submit">Add Bus</button>
    </form>
</div>

</body>
</html>