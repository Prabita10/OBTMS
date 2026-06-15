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

$id = $_GET['id'];
$msg="";
if(isset($_POST['update'])){
    $bus_name = $_POST['bus_name'];
    $type = $_POST['type'];
    $total_seats = $_POST['total_seats'];
    $fare = $_POST['fare'];

    $sql = "UPDATE buses SET bus_name='$bus_name', type='$type', total_seats='$total_seats', fare='$fare' WHERE bus_id=$id";
    if($conn->query($sql)===TRUE){ $msg="Bus updated successfully!"; }
    else{ $msg="Error: ".$conn->error; }
}

// Fetch bus data
$sql = "SELECT * FROM buses WHERE bus_id=$id";
$result = $conn->query($sql);
$bus = $result->fetch_assoc();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Bus</title>
</head>
<body>
<h2>Edit Bus</h2>
<?php if($msg!=""){ echo "<p>$msg</p>"; } ?>
<form method="POST">
    <label>Bus Name:</label><br>
    <input type="text" name="bus_name" value="<?php echo $bus['bus_name']; ?>" required><br>
    <label>Type:</label><br>
    <input type="text" name="type" value="<?php echo $bus['type']; ?>" required><br>
    <label>Total Seats:</label><br>
    <input type="number" name="total_seats" value="<?php echo $bus['total_seats']; ?>" required><br>
    <label>Fare:</label><br>
    <input type="number" step="0.01" name="fare" value="<?php echo $bus['fare']; ?>" required><br><br>
    <button type="submit" name="update">Update Bus</button>
</form>
<a href="admin_dashboard.php">Back to Dashboard</a>
</body>
</html>