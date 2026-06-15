<?php
session_start();
if(!isset($_SESSION['username']) || $_SESSION['role'] != 'user'){
    header("Location: login.html");
    exit();
}

$servername="localhost";
$usernameDB="root";
$passwordDB="root";
$dbname="obtms";
$conn = new mysqli($servername,$usernameDB,$passwordDB,$dbname);
if($conn->connect_error){ die("Connection failed: ".$conn->connect_error); }

$results = [];
if(isset($_GET['search'])){
    $from = $_GET['from'];
    $to = $_GET['to'];
    $date = $_GET['date']; // YYYY-MM-DD format

    // Search schedules joining buses and routes
    $sql = "SELECT s.schedule_id, b.bus_name, b.type, b.fare, s.departure_time, s.arrival_time, s.available_seats
            FROM schedules s
            JOIN buses b ON s.bus_id=b.bus_id
            JOIN routes r ON s.route_id=r.route_id
            WHERE r.source='$from' AND r.destination='$to'
              AND DATE(s.departure_time)='$date'
              AND s.available_seats>0
            ORDER BY s.departure_time ASC";

    $res = $conn->query($sql);
    if($res->num_rows > 0){
        $results = $res->fetch_all(MYSQLI_ASSOC);
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Search Buses</title>
</head>
<body>
<h2>Search Buses</h2>
<form method="GET">
    From: <input type="text" name="from" required>
    To: <input type="text" name="to" required>
    Date: <input type="date" name="date" required>
    <button type="submit" name="search">Search</button>
</form>

<?php if(count($results)>0): ?>
<table border="1" cellpadding="10">
<tr>
    <th>Bus Name</th>
    <th>Type</th>
    <th>Fare</th>
    <th>Departure</th>
    <th>Arrival</th>
    <th>Available Seats</th>
    <th>Action</th>
</tr>
<?php foreach($results as $row): ?>
<tr>
    <td><?php echo $row['bus_name']; ?></td>
    <td><?php echo $row['type']; ?></td>
    <td><?php echo $row['fare']; ?></td>
    <td><?php echo $row['departure_time']; ?></td>
    <td><?php echo $row['arrival_time']; ?></td>
    <td><?php echo $row['available_seats']; ?></td>
    <td><a href="book_seat.php?schedule_id=<?php echo $row['schedule_id']; ?>">Book</a></td>
</tr>
<?php endforeach; ?>
</table>
<?php elseif(isset($_GET['search'])): ?>
<p>No buses found for this route/date.</p>
<?php endif; ?>
</body>
</html>