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

// Fetch schedules with bus and route info
$sql = "SELECT s.schedule_id, b.bus_name, r.source, r.destination, s.departure_time, s.arrival_time, s.available_seats
        FROM schedules s
        JOIN buses b ON s.bus_id=b.bus_id
        JOIN routes r ON s.route_id=r.route_id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Schedules - OBTMS</title>
<style>
body { font-family: Arial, sans-serif; background:#f7f9fc; margin:0; padding:20px; }
h2 { color:#1a2b4c; text-align:center; margin-bottom:20px; }
table { border-collapse: collapse; width: 100%; background:#fff; border-radius:10px; overflow:hidden; }
th, td { border:1px solid #ccc; padding:12px; text-align:left; }
th { background:#1a2b4c; color:white; }
a.button { padding:8px 12px; background:#007bff; color:white; text-decoration:none; border-radius:5px; display:inline-block; }
a.button:hover { background:#0056b3; }
.add-btn { margin-bottom:15px; display:inline-block; }
</style>
</head>
<body>

<h2>Manage Schedules</h2>

<a href="add_schedule.php" class="button add-btn">Add New Schedule</a>

<table>
<tr>
    <th>ID</th>
    <th>Bus</th>
    <th>Route</th>
    <th>Departure</th>
    <th>Arrival</th>
    <th>Available Seats</th>
    <th>Actions</th>
</tr>

<?php if($result->num_rows>0): ?>
    <?php while($row=$result->fetch_assoc()): ?>
    <tr>
        <td><?php echo $row['schedule_id']; ?></td>
        <td><?php echo $row['bus_name']; ?></td>
        <td><?php echo $row['source'].' → '.$row['destination']; ?></td>
        <td><?php echo $row['departure_time']; ?></td>
        <td><?php echo $row['arrival_time']; ?></td>
        <td><?php echo $row['available_seats']; ?></td>
        <td>
            <a href="edit_schedule.php?id=<?php echo $row['schedule_id']; ?>" class="button">Edit</a>
            <a href="delete_schedule.php?id=<?php echo $row['schedule_id']; ?>" class="button" onclick="return confirm('Are you sure?');">Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr><td colspan="7" style="text-align:center;">No schedules found.</td></tr>
<?php endif; ?>
</table>

</body>
</html>