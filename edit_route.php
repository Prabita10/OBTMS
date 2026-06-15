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
$conn=new mysqli($servername,$usernameDB,$passwordDB,$dbname);
if($conn->connect_error){ die("Connection failed: ".$conn->connect_error); }

$id = $_GET['id'];
$msg="";
if(isset($_POST['update'])){
    $source = $_POST['source'];
    $destination = $_POST['destination'];
    $stops = $_POST['stops'];

    $sql="UPDATE routes SET source='$source', destination='$destination', stops='$stops' WHERE route_id=$id";
    if($conn->query($sql)===TRUE){
        $msg="Route updated successfully!";
    } else { $msg="Error: ".$conn->error; }
}

// Fetch route data
$sql="SELECT * FROM routes WHERE route_id=$id";
$result=$conn->query($sql);
$route=$result->fetch_assoc();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Route</title>
</head>
<body>
<h2>Edit Route</h2>
<?php if($msg!=""){ echo "<p>$msg</p>"; } ?>
<form method="POST">
    <label>Source:</label><br>
    <input type="text" name="source" value="<?php echo $route['source']; ?>" required><br>
    <label>Destination:</label><br>
    <input type="text" name="destination" value="<?php echo $route['destination']; ?>" required><br>
    <label>Stops (comma separated):</label><br>
    <input type="text" name="stops" value="<?php echo $route['stops']; ?>"><br><br>
    <button type="submit" name="update">Update Route</button>
</form>
<a href="admin_manage_routes.php">Back to Routes</a>
</body>
</html>