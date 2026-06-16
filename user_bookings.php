<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
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

$username = $_SESSION['username'];

// Fetch all bookings for this user
$sql = "SELECT b.booking_id, bu.bus_name, r.source, r.destination, s.departure_time, s.arrival_time, b.seat_number, b.status
        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.schedule_id
        JOIN buses bu ON s.bus_id = bu.bus_id
        JOIN routes r ON s.route_id = r.route_id
        WHERE b.username='$username'
        ORDER BY s.departure_time ASC";

$result = $conn->query($sql);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Bookings - OBTMS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f9fc;
            margin: 0;
            padding: 20px;
        }

        h2 {
            color: #1a2b4c;
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: left;
        }

        th {
            background: #1a2b4c;
            color: white;
        }

        .status-confirmed {
            color: green;
            font-weight: bold;
        }

        .status-canceled {
            color: red;
            font-weight: bold;
        }

        .status-pending {
            color: orange;
            font-weight: bold;
        }

        a.button {
            padding: 5px 10px;
            background: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }

        a.button:hover {
            background: #0056b3;
        }
    </style>
</head>

<body>

    <h2>My Upcoming Bookings</h2>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Booking ID</th>
                <th>Bus</th>
                <th>Route</th>
                <th>Departure</th>
                <th>Arrival</th>
                <th>Seat</th>
                <th>Status</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['booking_id']; ?></td>
                    <td><?php echo $row['bus_name']; ?></td>
                    <td><?php echo $row['source'] . ' → ' . $row['destination']; ?></td>
                    <td><?php echo $row['departure_time']; ?></td>
                    <td><?php echo $row['arrival_time']; ?></td>
                    <td><?php echo $row['seat_number']; ?></td>
                    <td class="status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p style="text-align:center;">You have no upcoming bookings.</p>
    <?php endif; ?>

    <a href="userdashboard.php" class="button" style="display:inline-block; margin-top:15px;">Back to Dashboard</a>

</body>

</html>