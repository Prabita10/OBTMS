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

// Fetch bookings with user, bus, route
$sql="SELECT b.booking_id, b.username, bu.bus_name, r.source, r.destination, b.seat_number, b.status, b.booking_time
      FROM bookings b
      JOIN schedules s ON b.schedule_id=s.schedule_id
      JOIN buses bu ON s.bus_id=bu.bus_id
      JOIN routes r ON s.route_id=r.route_id
      ORDER BY b.booking_time DESC";
$result=$conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Bookings - OBTMS</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body { font-family: Arial,sans-serif; background:#f7f9fc; margin:0; padding:20px; }
h2 { color:#1a2b4c; text-align:center; margin-bottom:20px; }
table { border-collapse: collapse; width: 100%; background:#fff; border-radius:10px; overflow:hidden; }
th, td { border:1px solid #ccc; padding:12px; text-align:left; }
th { background:#1a2b4c; color:white; }
.status-confirmed { color: green; font-weight:bold; }
.status-canceled { color: red; font-weight:bold; }
.status-pending { color: orange; font-weight:bold; }
</style>
</head>
<body>

<h2>All Bookings</h2>

<table>
<tr>
    <th>Booking ID</th>
    <th>User</th>
    <th>Bus</th>
    <th>Route</th>
    <th>Seat</th>
    <th>Status</th>
    <th>Booking Time</th>
</tr>

<?php if($result->num_rows>0): ?>
    <?php while($row=$result->fetch_assoc()): ?>
    <tr>
        <td><?php echo $row['booking_id']; ?></td>
        <td><?php echo $row['username']; ?></td>
        <td><?php echo $row['bus_name']; ?></td>
        <td><?php echo $row['source'].' → '.$row['destination']; ?></td>
        <td><?php echo $row['seat_number']; ?></td>
        <td class="status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></td>
        <td><?php echo $row['booking_time']; ?></td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr><td colspan="7" style="text-align:center;">No bookings found.</td></tr>
<?php endif; ?>
</table>

</body>
</html>