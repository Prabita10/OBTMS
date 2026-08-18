<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header("Location: login.html");
    exit();
}

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
$username = $_SESSION['username'];

if (!$booking_id) {
    die("Invalid booking.");
}

$conn = new mysqli("localhost", "root", "root", "obtms");
if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

// Fetch the booking to get schedule_id and seat_number
$res = $conn->query("SELECT schedule_id, seat_number FROM bookings WHERE booking_id=$booking_id AND username='$username'");
if ($res->num_rows == 0) {
    die("Booking not found or you are not authorized.");
}
$row = $res->fetch_assoc();
$schedule_id = $row['schedule_id'];

if (isset($_POST['submit_feedback'])) {
    $feedback = $conn->real_escape_string($_POST['feedback']);

    // Delete the booking row
    $conn->query("DELETE FROM bookings WHERE booking_id=$booking_id");

    // Increment available seats
    $conn->query("UPDATE schedules SET available_seats = available_seats + 1 WHERE schedule_id=$schedule_id");

    // Insert into cancel_feedback table
    $conn->query("INSERT INTO cancel_feedback (booking_id, username, feedback, cancel_time) VALUES ($booking_id, '$username', '$feedback', NOW())");

    $conn->close();
    header("Location: my_bookings.php?msg=Booking Cancelled Successfully");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Cancel Booking Feedback</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f9fc;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            width: 400px;
        }

        h2 {
            color: #1a2b4c;
            text-align: center;
            margin-bottom: 20px;
        }

        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-bottom: 15px;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #dc3545;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background: #c82333;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Cancel Booking</h2>
        <form method="POST">
            <label for="feedback">Please provide your cancellation feedback:</label>
            <textarea name="feedback" id="feedback" rows="5" placeholder="Optional feedback..."></textarea>
            <button type="submit" name="submit_feedback">Submit Cancellation</button>
        </form>
    </div>

</body>

</html>