<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.html");
    exit();
}

$servername = "localhost";
$usernameDB = "root";
$passwordDB = "root";
$dbname = "obtms";
$conn = new mysqli($servername, $usernameDB, $passwordDB, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'];
$msg = "";

// Fetch buses and routes
$buses = $conn->query("SELECT bus_id, bus_name FROM buses");
$routes = $conn->query("SELECT route_id, source, destination FROM routes");

// Fetch current schedule
$schedule = $conn->query("SELECT * FROM schedules WHERE schedule_id=$id")->fetch_assoc();

if (isset($_POST['update'])) {
    $bus_id = $_POST['bus_id'];
    $route_id = $_POST['route_id'];
    $departure_time = $_POST['departure_time'];
    $arrival_time = $_POST['arrival_time'];
    $available_seats = $_POST['available_seats'];

    $sql = "UPDATE schedules SET bus_id=$bus_id, route_id=$route_id, departure_time='$departure_time', arrival_time='$arrival_time', available_seats=$available_seats WHERE schedule_id=$id";
    if ($conn->query($sql) === TRUE) {
        $msg = "Schedule updated successfully!";
    } else {
        $msg = "Error: " . $conn->error;
    }
    // Refresh schedule
    $schedule = $conn->query("SELECT * FROM schedules WHERE schedule_id=$id")->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Schedule</title>
</head>

<body>
    <h2>Edit Schedule</h2>
    <?php if ($msg != "") {
        echo "<p>$msg</p>";
    } ?>
    <form method="POST">
        <label>Bus:</label><br>
        <select name="bus_id" required>
            <?php while ($b = $buses->fetch_assoc()): ?>
                <option value="<?php echo $b['bus_id']; ?>" <?php if ($b['bus_id'] == $schedule['bus_id'])
                       echo 'selected'; ?>>
                    <?php echo $b['bus_name']; ?></option>
            <?php endwhile; ?>
        </select><br>

        <label>Route:</label><br>
        <select name="route_id" required>
            <?php while ($r = $routes->fetch_assoc()): ?>
                <option value="<?php echo $r['route_id']; ?>" <?php if ($r['route_id'] == $schedule['route_id'])
                       echo 'selected'; ?>><?php echo $r['source'] . ' → ' . $r['destination']; ?></option>
            <?php endwhile; ?>
        </select><br>

        <label>Departure Time:</label><br>
        <input type="datetime-local" name="departure_time"
            value="<?php echo date('Y-m-d\TH:i', strtotime($schedule['departure_time'])); ?>" required><br>

        <label>Arrival Time:</label><br>
        <input type="datetime-local" name="arrival_time"
            value="<?php echo date('Y-m-d\TH:i', strtotime($schedule['arrival_time'])); ?>" required><br>

        <label>Available Seats:</label><br>
        <input type="number" name="available_seats" value="<?php echo $schedule['available_seats']; ?>"
            required><br><br>

        <button type="submit" name="update">Update Schedule</button>
    </form>
    <a href="admin_manage_schedules.php">Back to Schedules</a>
</body>

</html>