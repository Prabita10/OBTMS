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
    $seat_numbers = explode(',', $_POST['seat_number']);
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

        header("Location: ticket_invoice.php");
        exit();
    }
}
$conn->close();

// Create realistic bus layout (2 seats left, aisle, 2 seats right)
$total_seats = $schedule['total_seats'];
$seats_per_row = 4; // 2 left + 2 right
$rows = ceil($total_seats / $seats_per_row);
$seat_map = [];
$seat_num = 1;
for($r = 0; $r < $rows; $r++){
    for($c = 0; $c < $seats_per_row; $c++){
        if($seat_num <= $total_seats){
            $seat_map[$r][$c] = $seat_num;
            $seat_num++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Book Seats - BusGo</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',Arial,sans-serif;background:#eef2f7;padding:20px}
.container{max-width:800px;margin:auto;background:#fff;border-radius:16px;padding:30px;box-shadow:0 4px 20px rgba(0,0,0,0.08)}
h2{color:#1a2b4c;text-align:center;font-size:24px;margin-bottom:5px}
h2 i{color:#66b0ff;margin-right:10px}
.bus-info{text-align:center;color:#6b7a8f;margin-bottom:20px;font-size:14px}
.bus-info i{margin:0 5px;color:#66b0ff}

/* Real Bus Layout */
.bus-wrapper{background:#f0f4fb;border-radius:14px;padding:25px;border:3px solid #1a2b4c;position:relative;margin:20px 0}
.bus-wrapper::before{content:'🚌';position:absolute;top:-16px;left:50%;transform:translateX(-50%);background:#1a2b4c;color:#fff;padding:4px 25px;border-radius:20px;font-size:14px;font-weight:600;letter-spacing:1px}

.bus-body{display:flex;flex-direction:column;gap:8px}

/* Each row */
.bus-row{display:flex;gap:10px;justify-content:center;align-items:center}

/* Left seats (2) */
.seat-group-left{display:flex;gap:10px;flex:1;justify-content:flex-end}

/* Aisle */
.aisle{width:30px;min-height:40px;background:rgba(26,43,76,0.05);border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:11px;color:#999;font-weight:600;flex-shrink:0}

/* Right seats (2) */
.seat-group-right{display:flex;gap:10px;flex:1;justify-content:flex-start}

/* Individual Seat */
.seat{width:55px;height:55px;border-radius:10px 10px 6px 6px;display:flex;flex-direction:column;align-items:center;justify-content:center;cursor:pointer;transition:0.3s;font-size:12px;font-weight:700;position:relative;border:2px solid transparent}
.seat:not(.booked):hover{transform:scale(1.1);box-shadow:0 4px 15px rgba(0,0,0,0.2)}
.seat .seat-icon{font-size:20px;line-height:1}
.seat .seat-num{font-size:11px;margin-top:2px}
.seat.available{background:#28a745;color:#fff;border-color:#1e7e34}
.seat.available:hover{background:#34ce57}
.seat.booked{background:#dc3545;color:#fff;border-color:#bd2130;cursor:not-allowed;opacity:0.7}
.seat.selected{background:#ffc107;color:#1a2b4c;border-color:#e0a800;box-shadow:0 0 0 4px rgba(255,193,7,0.4);transform:scale(1.05)}
.seat.selected .seat-icon{color:#1a2b4c}

/* Empty seat placeholder */
.seat.empty{visibility:hidden}

/* Legend */
.legend{display:flex;justify-content:center;gap:30px;margin:18px 0;flex-wrap:wrap}
.legend-item{display:flex;align-items:center;gap:8px;font-size:13px;color:#555}
.legend-item .box{width:22px;height:22px;border-radius:6px}
.legend-item .box.available{background:#28a745}
.legend-item .box.booked{background:#dc3545}
.legend-item .box.selected{background:#ffc107}

/* Booking Info */
.booking-info{background:#f8fafc;padding:15px 20px;border-radius:10px;margin:15px 0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;border:1px solid #e2e8f0}
.booking-info .info-item{display:flex;align-items:center;gap:8px;font-size:14px;color:#1a2b4c}
.booking-info .info-item i{color:#66b0ff;width:20px}
.booking-info .info-item strong{color:#28a745;font-size:16px}

/* Buttons */
.btn-group{display:flex;gap:15px;justify-content:center;margin-top:20px;flex-wrap:wrap}
.btn{padding:12px 30px;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;transition:0.3s;display:inline-flex;align-items:center;gap:8px;text-decoration:none}
.btn-primary{background:#1a2b4c;color:#fff}
.btn-primary:hover{background:#2a4a7a;transform:scale(1.02)}
.btn-success{background:#28a745;color:#fff}
.btn-success:hover{background:#218838;transform:scale(1.02)}
.btn-success:disabled{opacity:0.5;cursor:not-allowed;transform:none}
.btn-secondary{background:#e8edf5;color:#1a2b4c}
.btn-secondary:hover{background:#d5dce8}
.btn-danger{background:#dc3545;color:#fff}
.btn-danger:hover{background:#c82333}

.msg{background:#d4edda;color:#155724;padding:12px 18px;border-radius:8px;margin:10px 0;border-left:4px solid #28a745;font-weight:500}

/* Responsive */
@media(max-width:600px){
    .container{padding:15px}
    .bus-wrapper{padding:15px}
    .seat{width:42px;height:42px;font-size:10px}
    .seat .seat-icon{font-size:16px}
    .aisle{width:20px;min-height:30px;font-size:9px}
    .seat-group-left,.seat-group-right{gap:6px}
    .bus-row{gap:6px}
}
</style>
</head>
<body>

<div class="container">
    <h2><i class="fas fa-chair"></i> Select Your Seats</h2>
    <p class="bus-info">
        <i class="fas fa-bus"></i> <?php echo htmlspecialchars($schedule['bus_name']); ?> 
        <i class="fas fa-arrow-right"></i> <?php echo htmlspecialchars($schedule['source']); ?> → <?php echo htmlspecialchars($schedule['destination']); ?>
        <i class="fas fa-clock"></i> <?php echo date('h:i A', strtotime($schedule['departure_time'])); ?>
    </p>

    <?php if($msg != "") echo "<div class='msg'><i class='fas fa-check-circle'></i> $msg</div>"; ?>

    <!-- Real Bus Visualization -->
    <div class="bus-wrapper">
        <div class="bus-body">
            <?php foreach($seat_map as $row_index => $row_seats): ?>
                <div class="bus-row">
                    <!-- Left side seats (2) -->
                    <div class="seat-group-left">
                        <?php 
                        $left_seats = array_slice($row_seats, 0, 2);
                        foreach($left_seats as $seat_num):
                            if($seat_num <= $total_seats):
                                $is_booked = in_array($seat_num, $booked);
                                $class = $is_booked ? 'booked' : 'available';
                        ?>
                            <div class="seat <?php echo $class; ?>" data-seat="<?php echo $seat_num; ?>" onclick="toggleSeat(this)">
                                <span class="seat-icon"><?php echo $is_booked ? '❌' : '💺'; ?></span>
                                <span class="seat-num"><?php echo $seat_num; ?></span>
                            </div>
                        <?php endif; endforeach; ?>
                    </div>

                    <!-- Aisle -->
                    <div class="aisle">⬇</div>

                    <!-- Right side seats (2) -->
                    <div class="seat-group-right">
                        <?php 
                        $right_seats = array_slice($row_seats, 2, 2);
                        foreach($right_seats as $seat_num):
                            if($seat_num <= $total_seats):
                                $is_booked = in_array($seat_num, $booked);
                                $class = $is_booked ? 'booked' : 'available';
                        ?>
                            <div class="seat <?php echo $class; ?>" data-seat="<?php echo $seat_num; ?>" onclick="toggleSeat(this)">
                                <span class="seat-icon"><?php echo $is_booked ? '❌' : '💺'; ?></span>
                                <span class="seat-num"><?php echo $seat_num; ?></span>
                            </div>
                        <?php endif; endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Legend -->
    <div class="legend">
        <div class="legend-item"><span class="box available"></span> Available</div>
        <div class="legend-item"><span class="box selected"></span> Selected</div>
        <div class="legend-item"><span class="box booked"></span> Booked</div>
    </div>

    <!-- Booking Info -->
    <div class="booking-info">
        <div class="info-item"><i class="fas fa-chair"></i> <span id="selectedCount">0</span> seat(s) selected</div>
        <div class="info-item"><i class="fas fa-money-bill-wave"></i> Fare: <strong>NPR <?php echo number_format($schedule['fare']); ?></strong> per seat</div>
        <div class="info-item"><i class="fas fa-users"></i> Available: <strong><?php echo $schedule['available_seats']; ?></strong> seats</div>
    </div>

    <!-- Booking Form -->
    <form method="POST" id="bookingForm">
        <input type="hidden" name="seat_number" id="seat_number" required>
        
        <div class="btn-group">
            <button type="button" class="btn btn-secondary" onclick="clearSelection()">
                <i class="fas fa-undo"></i> Clear
            </button>
            <button type="submit" name="book" class="btn btn-success" id="bookBtn" disabled>
                <i class="fas fa-ticket-alt"></i> Book Selected Seats
            </button>
            <a href="search_buses.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </form>
</div>

<script>
let selectedSeats = [];

function toggleSeat(element) {
    if(element.classList.contains('booked')) return;
    
    element.classList.toggle('selected');
    const seatNum = parseInt(element.dataset.seat);
    
    if(element.classList.contains('selected')) {
        if(!selectedSeats.includes(seatNum)) {
            selectedSeats.push(seatNum);
        }
    } else {
        selectedSeats = selectedSeats.filter(s => s !== seatNum);
    }
    
    updateSelection();
}

function updateSelection() {
    document.getElementById('selectedCount').textContent = selectedSeats.length;
    document.getElementById('seat_number').value = selectedSeats.join(',');
    
    const bookBtn = document.getElementById('bookBtn');
    if(selectedSeats.length > 0) {
        bookBtn.disabled = false;
        bookBtn.innerHTML = '<i class="fas fa-ticket-alt"></i> Book ' + selectedSeats.length + ' Seat(s)';
    } else {
        bookBtn.disabled = true;
        bookBtn.innerHTML = '<i class="fas fa-ticket-alt"></i> Book Selected Seats';
    }
}

function clearSelection() {
    document.querySelectorAll('.seat.selected').forEach(el => {
        el.classList.remove('selected');
    });
    selectedSeats = [];
    updateSelection();
}

// Prevent form submission if no seats selected
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    if(selectedSeats.length === 0) {
        e.preventDefault();
        alert('Please select at least one seat.');
    }
});
</script>

</body>
</html>