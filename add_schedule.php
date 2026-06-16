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

// Fetch buses and routes for dropdown
$buses=$conn->query("SELECT bus_id, bus_name FROM buses");
$routes=$conn->query("SELECT route_id, source, destination FROM routes");

if(isset($_POST['submit'])){
    $bus_id=$_POST['bus_id'];
    $route_id=$_POST['route_id'];
    $departure_time=$_POST['departure_time'];
    $arrival_time=$_POST['arrival_time'];
    $available_seats=$_POST['available_seats'];

    $sql="INSERT INTO schedules (bus_id, route_id, departure_time, arrival_time, available_seats)
          VALUES ($bus_id, $route_id, '$departure_time', '$arrival_time', $available_seats)";
    if($conn->query($sql)===TRUE){ $msg="Schedule added successfully!"; }
    else{ $msg="Error: ".$conn->error; }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add New Schedule</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body {
    font-family: Arial, sans-serif;
    background: #ffffff; /* white background */
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
}
h2 { color:#1a2b4c; text-align:center; margin-bottom:20px; }
label { display:block; margin-top:10px; font-weight:bold; }
input, select { width:100%; padding:10px; margin-top:5px; border-radius:5px; border:1px solid #ccc; }
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
    <h2>Add New Schedule</h2>
    <?php if($msg!=""){ echo "<div class='msg'>$msg</div>"; } ?>
    <form method="POST">
        <label>Bus:</label>
        <select name="bus_id" required>
            <?php while($b=$buses->fetch_assoc()): ?>
            <option value="<?php echo $b['bus_id']; ?>"><?php echo $b['bus_name']; ?></option>
            <?php endwhile; ?>
        </select>

        <label>Route:</label>
        <select name="route_id" required>
            <?php while($r=$routes->fetch_assoc()): ?>
            <option value="<?php echo $r['route_id']; ?>"><?php echo $r['source'].' → '.$r['destination']; ?></option>
            <?php endwhile; ?>
        </select>

        <label>Departure Time:</label>
        <input type="datetime-local" name="departure_time" required>

        <label>Arrival Time:</label>
        <input type="datetime-local" name="arrival_time" required>

        <label>Available Seats:</label>
        <input type="number" name="available_seats" required>

        <button type="submit" name="submit">Add Schedule</button>
    </form>
</div>

</body>
</html>