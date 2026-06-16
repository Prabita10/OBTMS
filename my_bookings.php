<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header("Location: login.html");
    exit();
}

$username = $_SESSION['username'];
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

$conn = new mysqli("localhost", "root", "root", "obtms");
if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

// Fetch user bookings
$sql = "SELECT b.booking_id, b.seat_number, b.status, b.booking_time,
               s.departure_time, s.arrival_time, r.source, r.destination, b.schedule_id
        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.schedule_id
        JOIN routes r ON s.route_id = r.route_id
        WHERE b.username='$username'
        ORDER BY s.departure_time ASC";

$res = $conn->query($sql);
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
            padding: 0;
        }

        /* Header */
        header {
            background-color: #1a2b4c;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
        }

        header nav a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            font-weight: bold;
        }

        header nav a:hover {
            text-decoration: underline;
        }

        /* Page Content */
        .page-container {
            padding: 20px;
            max-width: 1000px;
            margin: auto;
        }

        h2 {
            color: #1a2b4c;
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ccc;
            text-align: center;
        }

        th {
            background: #1a2b4c;
            color: #fff;
        }

        tr.cancelled td {
            background: #f8d7da;
            color: #721c24;
        }

        a.button {
            padding: 6px 12px;
            background: #dc3545;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
        }

        a.button:hover {
            background: #c82333;
        }

        .msg {
            text-align: center;
            color: green;
            font-weight: bold;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

    <header>
        <h1>BusGo</h1>
        <nav>
            <a href="user_dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="page-container">
        <h2>My Bookings</h2>
        <?php if ($msg)
            echo "<div class='msg'>$msg</div>"; ?>

        <table>
            <tr>
                <th>Bus</th>
                <th>Route</th>
                <th>Seat</th>
                <th>Status</th>
                <th>Departure</th>
                <th>Arrival</th>
                <th>Action</th>
            </tr>

            <?php if ($res->num_rows > 0): ?>
                <?php while ($row = $res->fetch_assoc()): ?>
                    <tr class="<?php echo ($row['status'] == 'cancelled') ? 'cancelled' : ''; ?>">
                        <td><?php echo htmlspecialchars($row['schedule_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['source'] . " → " . $row['destination']); ?></td>
                        <td><?php echo htmlspecialchars($row['seat_number']); ?></td>
                        <td><?php echo ucfirst($row['status']); ?></td>
                        <td><?php echo $row['departure_time']; ?></td>
                        <td><?php echo $row['arrival_time']; ?></td>
                        <td>
                            <?php if ($row['status'] == 'confirmed'): ?>
                                <a href="cancel_booking.php?booking_id=<?php echo $row['booking_id']; ?>"
                                    onclick="return confirm('Are you sure you want to cancel this booking?');"
                                    class="button">Cancel</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No bookings found.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

</body>

</html>