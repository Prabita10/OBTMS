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

// **Delete the booking row**
$conn->query("DELETE FROM bookings WHERE booking_id=$booking_id");

// **Increment available seats**
$conn->query("UPDATE schedules SET available_seats = available_seats + 1 WHERE schedule_id=$schedule_id");

$conn->close();

// Redirect back to My Bookings page with message
header("Location: my_bookings.php?msg=Booking Cancelled Successfully");
exit();
?>