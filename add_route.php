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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body {
    font-family: Arial, sans-serif;
    background: #ffffff; /* changed from gradient to white */
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
    max-width:700px;
    margin:50px auto;
    background:#fff;
    border-radius:15px;
    padding:30px;
    box-shadow:0 4px 15px rgba(0,0,0,0.2);
    display:flex;
    flex-wrap:wrap;
    gap:20px;
}
.left, .right {
    flex:1;
    min-width:250px;
}
h2 { color:#1a2b4c; text-align:center; margin-bottom:20px; }
label { display:block; margin-top:10px; font-weight:bold; }
input { width:100%; padding:10px; margin-top:5px; border-radius:5px; border:1px solid #ccc; }
button { width:100%; padding:10px; margin-top:15px; background:#007bff; color:white; border:none; border-radius:5px; cursor:pointer; }
button:hover { background:#0056b3; }
.msg { text-align:center; color:green; font-weight:bold; margin-bottom:15px; }
.right img { width:100%; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.2); }
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
    <div class="left">
        <h2>Add New Route</h2>
        <?php if($msg!=""){ echo "<div class='msg'>$msg</div>"; } ?>
        <form method="POST">
            <label>Source:</label>
            <input type="text" name="source" placeholder="Enter source city" required>

            <label>Destination:</label>
            <input type="text" name="destination" placeholder="Enter destination city" required>

            <label>Stops (comma separated):</label>
            <input type="text" name="stops" placeholder="Optional: Stop1,Stop2,...">

            <button type="submit" name="submit">Add Route</button>
        </form>
    </div>
    <div class="right">
        <img src="bus_route_image.jpg" alt="Bus Route">
        <p style="text-align:center; color:#1a2b4c; font-weight:bold; margin-top:10px;">Visualize your routes here!</p>
    </div>
</div>

</body>
</html>