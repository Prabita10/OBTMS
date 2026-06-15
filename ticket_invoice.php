<?php
session_start();

// Ensure invoice exists
if (!isset($_SESSION['invoice'])) {
    header("Location: userdashboard.php");
    exit();
}

$invoice = $_SESSION['invoice'];

// Safety check for schedule_id
if (!isset($invoice['schedule_id'])) {
    die("Error: Schedule ID missing in invoice session.");
}

$schedule_id = intval($invoice['schedule_id']);
$seats = explode(',', $invoice['seats']); // user's booked seats
$totalPrice = $invoice['seat_price'] * count($seats);

// Fetch other booked seats for this schedule (excluding current user)
$otherBookedSeats = [];
$servername = "localhost";
$usernameDB = "root";
$passwordDB = "root";
$dbname = "obtms";
$conn = new mysqli($servername, $usernameDB, $passwordDB, $dbname);
if($conn->connect_error){ die("Connection failed: ".$conn->connect_error); }

$username = $conn->real_escape_string($_SESSION['username']);
$res = $conn->query("SELECT seat_number FROM bookings WHERE schedule_id=$schedule_id AND username!='$username'");
while($row = $res->fetch_assoc()){
    $otherBookedSeats[] = intval($row['seat_number']);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>BusGo Ticket Invoice</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
<style>
body { font-family: 'Arial', sans-serif; background: #f0f4f7; padding: 30px; }
.invoice-container { max-width: 700px; margin: auto; background: #fff; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
.invoice-header { text-align: center; color: #1a2b4c; }
.invoice-header h1 { margin: 0; font-size: 28px; }
.invoice-info { margin: 20px 0; display: flex; justify-content: space-between; color: #333; }
.invoice-info div { line-height: 1.6; }
button { margin-top: 20px; padding: 10px 15px; background:#007bff; color:white; border:none; border-radius:5px; cursor:pointer; }
button:hover { background:#0056b3; }
</style>
</head>
<body>

<div class="invoice-container" id="invoice">
    <div class="invoice-header">
        <h1>BusGo Ticket</h1>
        <p>Booking Invoice</p>
    </div>

    <div class="invoice-info">
        <div>
            <strong>User:</strong> <?php echo htmlspecialchars($invoice['username']); ?><br>
            <strong>Booking Time:</strong> <?php echo $invoice['booking_time']; ?>
        </div>
        <div>
            <strong>Bus:</strong> <?php echo $invoice['bus_name']; ?><br>
            <strong>Route:</strong> <?php echo $invoice['route']; ?>
        </div>
    </div>

    <div class="invoice-info">
        <div>
            <strong>Departure:</strong> <?php echo $invoice['departure']; ?><br>
            <strong>Arrival:</strong> <?php echo $invoice['arrival']; ?>
        </div>
        <div>
            <strong>Seat Price:</strong> NPR <?php echo $invoice['seat_price']; ?><br>
            <strong>Seats:</strong> <?php echo $invoice['seats']; ?>
        </div>
    </div>

    <div style="margin-top:20px; font-size:12px; color:#555;">
        <strong>Seat Map:</strong> (Your seats: yellow, Booked: red, Available: green)
    </div>

    <button onclick="downloadPDF()">Download PDF</button>
</div>

<script>
const userSeats = <?php echo json_encode(array_map('intval',$seats)); ?>;
const otherBookedSeats = <?php echo json_encode($otherBookedSeats); ?>;
const totalSeats = 40; // adjust according to bus
const seatsPerRow = 4;

function downloadPDF() {
    const { jsPDF } = window.jspdf;
    var doc = new jsPDF();

    // Header
doc.setFontSize(18);
    doc.text("BusGo Ticket Invoice", 105, 20, { align: "center" });
    doc.setFontSize(12);
    doc.text("User: <?php echo htmlspecialchars($invoice['username']); ?>", 20, 35);
    doc.text("Booking Time: <?php echo $invoice['booking_time']; ?>", 20, 42);
    doc.text("Bus: <?php echo $invoice['bus_name']; ?>", 20, 50);
    doc.text("Route: <?php echo $invoice['route']; ?>", 20, 58);
    doc.text("Departure: <?php echo $invoice['departure']; ?>", 20, 66);
    doc.text("Arrival: <?php echo $invoice['arrival']; ?>", 20, 74);

    // Seat map
    let startX = 20, startY = 85, seatSize = 10, gap = 4;

    for(let i=1; i<=totalSeats; i++){
        let row = Math.floor((i-1)/seatsPerRow);
        let col = (i-1)%seatsPerRow;
        let x = startX + col*(seatSize+gap);
        let y = startY + row*(seatSize+gap);

        let fillColor = [0,255,0]; // available
        if(otherBookedSeats.includes(i)) fillColor = [255,0,0]; // booked by others
        if(userSeats.includes(i)) fillColor = [255,206,0]; // user's seats

        doc.setFillColor(...fillColor);
        doc.rect(x, y, seatSize, seatSize, 'F');
        doc.setTextColor(0);
        doc.setFontSize(7);
        doc.text(i.toString(), x+2, y+7);
    }

    // Table of seats and price
    var rows = userSeats.map(s => [s, <?php echo $invoice['seat_price']; ?>]);
    doc.autoTable({
        startY: startY + Math.ceil(totalSeats/seatsPerRow)*(seatSize + gap) + 15,
        head: [['Seat No','Price (NPR)']],
        body: rows,
        theme:'grid',
        headStyles: {fillColor:[26,43,76], textColor:255},
        styles: {cellPadding:3, fontSize:11}
    });

    var totalPrice = <?php echo $totalPrice; ?>;
    doc.text("Total Price: NPR " + totalPrice, 20, doc.lastAutoTable.finalY + 10);

    doc.save('busgo_ticket.pdf');
}
</script>

</body>
</html>