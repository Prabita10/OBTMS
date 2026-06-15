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

$msg="";
if(isset($_POST['submit'])){
    $source = $_POST['source'];
    $destination = $_POST['destination'];
    $stops = $_POST['stops'];

    $sql="INSERT INTO routes (source,destination,stops) VALUES ('$source','$destination','$stops')";
    if($conn->query($sql)===TRUE){
        $msg="Route added successfully!";
    } else { $msg="Error: ".$conn->error; }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add New Route</title>
</head>
<body>
<h2>Add New Route</h2>
<?php if($msg!=""){ echo "<p>$msg</p>"; } ?>
<form method="POST">
    <label>Source:</label><br>
    <input type="text" name="source" required><br>
    <label>Destination:</label><br>
    <input type="text" name="destination" required><br>
    <label>Stops (comma separated):</label><br>
    <input type="text" name="stops"><br><br>
    <button type="submit" name="submit">Add Route</button>
</form>
<a href="admin_manage_routes.php">Back to Routes</a>
</body>
</html>