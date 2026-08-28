<?php
session_start();
if(!isset($_SESSION['username']) || $_SESSION['role'] != 'user'){
    header("Location: login.html");
    exit();
}

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
$username = $_SESSION['username'];

if(!$booking_id){
    die("Invalid booking.");
}

$conn = new mysqli("localhost","root","","obtms");
if($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch the booking to get schedule_id and seat_number
$res = $conn->query("SELECT schedule_id, seat_number FROM bookings WHERE booking_id=$booking_id AND username='$username'");
if($res->num_rows == 0){
    die("Booking not found or you are not authorized.");
}
$row = $res->fetch_assoc();
$schedule_id = $row['schedule_id'];
$seat_number = $row['seat_number'];

// Also fetch bus and route details for notification
$details = $conn->query("SELECT b.bus_name, b.bus_number, r.source, r.destination 
                         FROM schedules s
                         JOIN buses b ON s.bus_id = b.bus_id
                         JOIN routes r ON s.route_id = r.route_id
                         WHERE s.schedule_id = $schedule_id");
$trip = $details->fetch_assoc();

if(isset($_POST['submit_feedback'])){
    $feedback = $conn->real_escape_string($_POST['feedback']);

    // Delete the booking row
    $conn->query("DELETE FROM bookings WHERE booking_id=$booking_id");

    // Insert into cancel_feedback table
    $conn->query("INSERT INTO cancel_feedback (booking_id, username, feedback, cancel_time) VALUES ($booking_id, '$username', '$feedback', NOW())");

    // ============================================
    // ADD NOTIFICATION FOR CANCELLATION
    // ============================================
    $notifMessage = "❌ Your booking has been cancelled! Bus: " . $trip['bus_name'] . " | Route: " . $trip['source'] . " → " . $trip['destination'] . " | Seat: " . $seat_number;
    $conn->query("INSERT INTO notifications (username, message) VALUES ('$username', '$notifMessage')");

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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',Arial,sans-serif;background:#f0f4fb;display:flex;justify-content:center;align-items:center;min-height:100vh;padding:20px}

.container{background:#fff;padding:35px;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.08);max-width:500px;width:100%;border:1px solid #e8ecf3}

.header{text-align:center;margin-bottom:25px}
.header .icon{width:60px;height:60px;background:#f8d7da;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:28px;color:#dc3545}
.header h2{color:#1a2b4c;font-size:22px}
.header p{color:#6b7a8f;font-size:14px;margin-top:4px}

.booking-info{background:#f8fafc;padding:12px 16px;border-radius:8px;margin-bottom:20px;border-left:3px solid #dc3545}
.booking-info .label{color:#6b7a8f;font-size:12px}
.booking-info .value{color:#1a2b4c;font-weight:600;font-size:14px}

label{display:block;font-weight:600;color:#1a2b4c;font-size:14px;margin-bottom:6px}
label i{color:#dc3545;margin-right:6px}
textarea{width:100%;padding:12px 15px;border-radius:8px;border:1.5px solid #e2e8f0;font-size:14px;transition:0.3s;background:#fafcff;resize:vertical;min-height:100px;font-family:inherit}
textarea:focus{outline:none;border-color:#dc3545;box-shadow:0 0 0 4px rgba(220,53,69,0.08)}

.btn-group{display:flex;gap:12px;margin-top:15px;flex-wrap:wrap}
.btn{flex:1;padding:12px 20px;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;transition:0.3s;display:inline-flex;align-items:center;justify-content:center;gap:8px;text-decoration:none;min-width:120px}
.btn-danger{background:#dc3545;color:#fff}
.btn-danger:hover{background:#c82333;transform:translateY(-2px);box-shadow:0 4px 12px rgba(220,53,69,0.3)}
.btn-secondary{background:#e8edf5;color:#1a2b4c}
.btn-secondary:hover{background:#d5dce8;transform:translateY(-2px)}

.info-text{background:#fff3cd;padding:10px 14px;border-radius:8px;margin-bottom:15px;font-size:13px;color:#856404;border-left:3px solid #ffc107;display:flex;align-items:center;gap:10px}
.info-text i{font-size:16px}

@media(max-width:480px){
    .container{padding:20px}
    .btn-group{flex-direction:column}
    .btn{min-width:auto}
}
</style>
</head>
<body>

<div class="container">
    <div class="header">
        <div class="icon"><i class="fas fa-times-circle"></i></div>
        <h2>Cancel Booking</h2>
        <p>Please let us know why you're cancelling</p>
    </div>

    <div class="booking-info">
        <div class="label"><i class="fas fa-ticket-alt"></i> Booking ID</div>
        <div class="value">#<?php echo $booking_id; ?></div>
        <?php if($trip): ?>
        <div style="margin-top:5px;font-size:12px;color:#6b7a8f;">
            <i class="fas fa-bus"></i> <?php echo htmlspecialchars($trip['bus_name']); ?> 
            (<?php echo htmlspecialchars($trip['bus_number']); ?>)
        </div>
        <div style="font-size:12px;color:#6b7a8f;">
            <i class="fas fa-route"></i> <?php echo htmlspecialchars($trip['source']); ?> → <?php echo htmlspecialchars($trip['destination']); ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="info-text">
        <i class="fas fa-info-circle"></i>
        <span>Cancelling this booking will free up your seat for others.</span>
    </div>

    <form method="POST">
        <label for="feedback"><i class="fas fa-comment"></i> Feedback (Optional)</label>
        <textarea name="feedback" id="feedback" rows="4" placeholder="Tell us why you're cancelling..."></textarea>

        <div class="btn-group">
            <a href="my_bookings.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <button type="submit" name="submit_feedback" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this booking? This action cannot be undone.');">
                <i class="fas fa-trash-alt"></i> Confirm Cancellation
            </button>
        </div>
    </form>
</div>

</body>
</html>