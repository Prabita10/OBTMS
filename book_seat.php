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

$username = $_SESSION['username'];
$schedule_id = isset($_GET['schedule_id']) ? intval($_GET['schedule_id']) : 0;

// Fetch schedule info
$sql = "SELECT s.schedule_id, s.available_seats, b.bus_name, b.type, b.fare, b.total_seats, 
               s.departure_time, s.arrival_time, r.source, r.destination
        FROM schedules s
        JOIN buses b ON s.bus_id=b.bus_id
        JOIN routes r ON s.route_id=r.route_id
        WHERE s.schedule_id=$schedule_id";

$schedule_res = $conn->query($sql);
if($schedule_res->num_rows == 0){
    die("Invalid schedule selected.");
}
$schedule = $schedule_res->fetch_assoc();

// Fetch already booked seats
$booked = [];
$booked_res = $conn->query("SELECT seat_number FROM bookings WHERE schedule_id=$schedule_id");
while($row = $booked_res->fetch_assoc()){
    $booked[] = $row['seat_number'];
}

// Handle booking submission
$msg = "";
if(isset($_POST['book'])){
    $seat_numbers = explode(',', $_POST['seat_number']); // selected seats from hidden input
    $seat_numbers = array_map('trim', $seat_numbers);
    $success_count = 0;
    $selectedSeats = [];

    foreach($seat_numbers as $seat_number){
        $seat_number = intval($seat_number);
        if(in_array($seat_number, $booked)){
            $msg .= "Seat $seat_number is already booked. ";
        } elseif($seat_number < 1 || $seat_number > $schedule['total_seats']){
            $msg .= "Seat $seat_number is invalid. ";
        } else {
            $conn->query("INSERT INTO bookings(username,schedule_id,seat_number,status) 
                          VALUES ('$username',$schedule_id,'$seat_number','confirmed')");
            $booked[] = $seat_number;
            $success_count++;
            $selectedSeats[] = $seat_number;
        }
    }

    if($success_count > 0){
        $conn->query("UPDATE schedules SET available_seats = available_seats - $success_count WHERE schedule_id=$schedule_id");
        
        // Store invoice session for ticket PDF
        $_SESSION['invoice'] = [
            'username' => $username,
            'booking_time' => date("Y-m-d H:i:s"),
            'bus_name' => $schedule['bus_name'],
            'route' => $schedule['source'] . " → " . $schedule['destination'],
            'departure' => $schedule['departure_time'],
            'arrival' => $schedule['arrival_time'],
            'seat_price' => $schedule['fare'],
            'seats' => implode(',', $selectedSeats),
            'schedule_id' => $schedule['schedule_id'],
            'total_seats' => $schedule['total_seats']
        ];

        header("Location: ticket_invoice.php"); // redirect to invoice
        exit();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book Seats - OBTMS</title>
<style>
body { font-family: Arial,sans-serif; background:#f7f9fc; padding:20px; }
h2 { color:#1a2b4c; text-align:center; }
.seat-map { display:flex; flex-wrap:wrap; max-width:500px; margin:20px auto; }
.seat {
    width:40px; height:40px; margin:5px; line-height:40px;
    text-align:center; border-radius:5px; cursor:pointer;
}
.seat.available { background: #28a745; color:white; }
.seat.booked { background: #dc3545; color:white; cursor:not-allowed; }
.seat.selected { background: #ffc107; color:#000; }
.seat:hover { opacity:0.8; }
button { margin-top:20px; padding:10px 15px; background:#007bff; color:white; border:none; border-radius:5px; cursor:pointer; }
button:hover { background:#0056b3; }
.msg { text-align:center; color:green; margin-top:10px; font-weight:bold; }
</style>
<script>
// Multiple seat selection
function toggleSeat(seat){
    if(seat.classList.contains('booked')) return;

    seat.classList.toggle('selected');
    let selected = [];
    document.querySelectorAll('.seat.selected').forEach(s => selected.push(s.dataset.seat));
    document.getElementById('seat_number').value = selected.join(',');
}
</script>
</head>
<body>

<h2>Book Seats for <?php echo $schedule['bus_name'] . " (" . $schedule['source'] . " → " . $schedule['destination'] . ")"; ?></h2>
<p>Departure: <?php echo $schedule['departure_time']; ?> | Arrival: <?php echo $schedule['arrival_time']; ?> | Available Seats: <?php echo $schedule['available_seats']; ?></p>

<?php if($msg != "") echo "<div class='msg'>$msg</div>"; ?>

<form method="POST">
<div class="seat-map">
<?php
for($i=1;$i<=$schedule['total_seats'];$i++){
    $class = in_array($i,$booked) ? 'booked' : 'available';
    echo "<div class='seat $class' data-seat='$i' onclick='toggleSeat(this)'>$i</div>";
}
?>
</div>
<input type="hidden" name="seat_number" id="seat_number" required>
<button type="submit" name="book">Book Selected Seats</button>
</form>

<a href="search_buses.php" style="display:block; text-align:center; margin-top:20px;">Back to Search</a>

</body>
</html>