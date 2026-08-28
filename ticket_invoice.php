<?php
session_start();

// Ensure invoice exists
if (!isset($_SESSION['invoice'])) {
    header("Location: user_dashboard.php");
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
$passwordDB = "";
$dbname = "obtms";
$conn = new mysqli($servername, $usernameDB, $passwordDB, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $conn->real_escape_string($_SESSION['username']);
$res = $conn->query("SELECT seat_number FROM bookings WHERE schedule_id=$schedule_id AND username!='$username'");
while ($row = $res->fetch_assoc()) {
    $otherBookedSeats[] = intval($row['seat_number']);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Invoice - BusGo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #eef2f7;
            padding: 30px
        }

        .invoice-container {
            max-width: 700px;
            margin: auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden
        }

        /* Header */
        .invoice-header {
            background: linear-gradient(135deg, #1a2b4c, #2a4a7a);
            color: #fff;
            padding: 25px;
            text-align: center
        }

        .invoice-header h1 {
            font-size: 28px;
            margin-bottom: 3px
        }

        .invoice-header h1 i {
            color: #66b0ff;
            margin-right: 10px
        }

        .invoice-header p {
            opacity: 0.8;
            font-size: 13px
        }

        /* Body */
        .invoice-body {
            padding: 25px
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            padding: 6px 0;
            border-bottom: 1px solid #f0f2f5
        }

        .info-row .label {
            color: #6b7a8f;
            font-size: 13px
        }

        .info-row .value {
            color: #1a2b4c;
            font-weight: 600;
            font-size: 14px
        }

        /* Seat Map */
        .seat-map-section {
            background: #f8fafc;
            border-radius: 10px;
            padding: 15px;
            margin: 15px 0;
            border: 1px solid #e2e8f0
        }

        .seat-map-section h4 {
            color: #1a2b4c;
            font-size: 14px;
            margin-bottom: 10px
        }

        .seat-map-section h4 i {
            color: #66b0ff;
            margin-right: 6px
        }

        .seat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 5px;
            max-width: 280px;
            margin: auto
        }

        .seat-box {
            width: 42px;
            height: 42px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            margin: auto
        }

        .seat-box.user {
            background: #ffc107;
            color: #1a2b4c;
            border: 2px solid #e0a800
        }

        .seat-box.other {
            background: #dc3545;
            color: #fff;
            border: 2px solid #bd2130;
            opacity: 0.7
        }

        .seat-box.available {
            background: #28a745;
            color: #fff;
            border: 2px solid #1e7e34
        }

        .legend {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 10px 0;
            flex-wrap: wrap
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            color: #555
        }

        .legend-item .dot {
            width: 12px;
            height: 12px;
            border-radius: 3px
        }

        .legend-item .dot.user {
            background: #ffc107
        }

        .legend-item .dot.other {
            background: #dc3545
        }

        .legend-item .dot.available {
            background: #28a745
        }

        .summary-box {
            background: linear-gradient(135deg, #f8fafc, #e8edf5);
            padding: 15px 20px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin: 15px 0
        }

        .summary-box .total {
            font-size: 22px;
            font-weight: 700;
            color: #28a745
        }

        .summary-box .total i {
            color: #28a745;
            margin-right: 8px
        }

        .btn-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 15px
        }

        .btn {
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none
        }

        .btn-primary {
            background: #1a2b4c;
            color: #fff
        }

        .btn-primary:hover {
            background: #2a4a7a
        }

        .btn-success {
            background: #28a745;
            color: #fff
        }

        .btn-success:hover {
            background: #218838
        }

        .btn-secondary {
            background: #e8edf5;
            color: #1a2b4c
        }

        .btn-secondary:hover {
            background: #d5dce8
        }

        @media(max-width:600px) {
            .invoice-body {
                padding: 15px
            }

            .seat-grid {
                gap: 4px;
                max-width: 220px
            }

            .seat-box {
                width: 34px;
                height: 34px;
                font-size: 10px
            }

            .summary-box {
                flex-direction: column;
                text-align: center;
                gap: 8px
            }

            .btn-group {
                flex-direction: column
            }

            .btn {
                justify-content: center
            }
        }
    </style>
</head>

<body>

    <div class="invoice-container">
        <div class="invoice-header">
            <h1><i class="fas fa-ticket-alt"></i> BusGo Ticket</h1>
            <p>Booking Invoice</p>
        </div>

        <div class="invoice-body">
            <!-- User & Booking Info -->
            <div class="info-row">
                <span class="label"><i class="fas fa-user"></i> User</span>
                <span class="value"><?php echo htmlspecialchars($invoice['username']); ?></span>
            </div>
            <div class="info-row">
                <span class="label"><i class="fas fa-clock"></i> Booking Time</span>
                <span class="value"><?php echo $invoice['booking_time']; ?></span>
            </div>
            <div class="info-row">
                <span class="label"><i class="fas fa-bus"></i> Bus</span>
                <span class="value"><?php echo htmlspecialchars($invoice['bus_name']); ?></span>
            </div>
            <div class="info-row">
                <span class="label"><i class="fas fa-route"></i> Route</span>
                <span class="value"><?php echo htmlspecialchars($invoice['route']); ?></span>
            </div>
            <div class="info-row">
                <span class="label"><i class="fas fa-play"></i> Departure</span>
                <span class="value"><?php echo $invoice['departure']; ?></span>
            </div>
            <div class="info-row">
                <span class="label"><i class="fas fa-flag-checkered"></i> Arrival</span>
                <span class="value"><?php echo $invoice['arrival']; ?></span>
            </div>
            <div class="info-row">
                <span class="label"><i class="fas fa-chair"></i> Seats</span>
                <span class="value"><?php echo $invoice['seats']; ?></span>
            </div>
            <div class="info-row" style="border-bottom:none">
                <span class="label"><i class="fas fa-money-bill-wave"></i> Seat Price</span>
                <span class="value">NPR <?php echo $invoice['seat_price']; ?> per seat</span>
            </div>

            <!-- Seat Map -->
            <div class="seat-map-section">
                <h4><i class="fas fa-chair"></i> Seat Map</h4>
                <div class="seat-grid">
                    <?php
                    $totalSeats = intval($invoice['total_seats']);
                    for ($i = 1; $i <= $totalSeats; $i++):
                        $class = 'available';
                        if (in_array($i, array_map('intval', $seats)))
                            $class = 'user';
                        elseif (in_array($i, $otherBookedSeats))
                            $class = 'other';
                        ?>
                        <div class="seat-box <?php echo $class; ?>"><?php echo $i; ?></div>
                    <?php endfor; ?>
                </div>
                <div class="legend">
                    <span class="legend-item"><span class="dot user"></span> Your seats</span>
                    <span class="legend-item"><span class="dot other"></span> Booked</span>
                    <span class="legend-item"><span class="dot available"></span> Available</span>
                </div>
            </div>

            <!-- Summary -->
            <div class="summary-box">
                <span style="font-size:14px;color:#1a2b4c;font-weight:600">
                    <i class="fas fa-chair"></i> <?php echo count($seats); ?> Seat(s)
                </span>
                <span class="total"><i class="fas fa-money-bill-wave"></i> NPR
                    <?php echo number_format($totalPrice); ?></span>
            </div>

            <!-- Buttons -->
            <div class="btn-group">
                <button onclick="downloadPDF()" class="btn btn-success">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </button>
                <a href="user_dashboard.php" class="btn btn-primary">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
                <a href="search_buses.php" class="btn btn-secondary">
                    <i class="fas fa-search"></i> Search
                </a>
            </div>
        </div>
    </div>

    <script>
        const userSeats = <?php echo json_encode(array_map('intval', $seats)); ?>;
        const otherBookedSeats = <?php echo json_encode($otherBookedSeats); ?>;
        const totalSeats = <?php echo intval($invoice['total_seats']); ?>;
        const seatsPerRow = 4;

        function downloadPDF() {
            const { jsPDF } = window.jspdf;
            var doc = new jsPDF('p', 'mm', 'a4');

            // ========== MODERN HEADER WITH GRADIENT EFFECT ==========
            // Dark gradient background
            doc.setFillColor(26, 43, 76);
            doc.rect(0, 0, 210, 40, 'F');

            // Accent bar
            doc.setFillColor(255, 193, 7);
            doc.rect(0, 38, 210, 2, 'F');

            // Bus icon (simple representation)
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(28);
            doc.setFont('helvetica', 'bold');
            doc.text("BusGo", 105, 20, { align: "center" });
            doc.setFontSize(10);
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(200, 200, 200);
            doc.text("Booking Confirmation #<?php echo date('Ymd') . rand(100, 999); ?>", 105, 30, { align: "center" });

            // ========== TICKET INFO - MODERN CARDS ==========
            let y = 52;

            // Section title with icon
            doc.setTextColor(26, 43, 76);
            doc.setFontSize(11);
            doc.setFont('helvetica', 'bold');
            doc.text("TICKET DETAILS", 20, y);
            y += 5;

            // Subtle divider
            doc.setDrawColor(255, 193, 7);
            doc.setLineWidth(0.5);
            doc.line(20, y, 190, y);
            y += 7;

            // Two-column layout for compactness
            const info = [
                ["User", "<?php echo htmlspecialchars($invoice['username']); ?>"],
                ["Booking", "<?php echo $invoice['booking_time']; ?>"],
                ["Bus", "<?php echo htmlspecialchars($invoice['bus_name']); ?>"],
                ["Route", "<?php echo htmlspecialchars($invoice['route']); ?>"],
                ["Departure", "<?php echo $invoice['departure']; ?>"],
                ["Arrival", "<?php echo $invoice['arrival']; ?>"],
                ["Seats", "<?php echo $invoice['seats']; ?>"],
                ["Price", "NPR <?php echo $invoice['seat_price']; ?>/seat"]
            ];

            let col1 = 20, col2 = 110;
            let rowHeight = 7;
            let currentY = y;

            doc.setFont('helvetica', 'normal');
            info.forEach(([label, value], index) => {
                let xPos = index < 4 ? col1 : col2;
                let yPos = currentY + (index % 4) * rowHeight;

                // Light background for alternating rows
                if ((index % 4) % 2 === 0) {
                    doc.setFillColor(248, 249, 250);
                    doc.rect(xPos - 2, yPos - 4, 85, 7, 'F');
                }

                doc.setFont('helvetica', 'bold');
                doc.setFontSize(8);
                doc.setTextColor(100, 100, 100);
                doc.text(label, xPos, yPos);

                doc.setFont('helvetica', 'normal');
                doc.setFontSize(9);
                doc.setTextColor(0, 0, 0);
                doc.text(value, xPos + 30, yPos);
            });

            y = currentY + (4 * rowHeight) + 10;

            // ========== SEAT MAP - COMPACT DESIGN ==========
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(11);
            doc.setTextColor(26, 43, 76);
            doc.text("SEAT LAYOUT", 20, y);
            y += 5;
            doc.setDrawColor(255, 193, 7);
            doc.line(20, y, 190, y);
            y += 7;

            // Compact seat map
            let startX = 20, startY = y, seatSize = 8, gap = 2.5;
            let seatsPerRow = 10;

            // Bus silhouette
            doc.setDrawColor(200, 200, 200);
            doc.setLineWidth(0.3);
            doc.roundedRect(18, startY - 3, 172, Math.ceil(totalSeats / seatsPerRow) * (seatSize + gap) + 6, 3, 3, 'S');

            for (let i = 1; i <= totalSeats; i++) {
                let row = Math.floor((i - 1) / seatsPerRow);
                let col = (i - 1) % seatsPerRow;
                let x = startX + col * (seatSize + gap);
                let yPos = startY + row * (seatSize + gap);

                let fillColor = [52, 152, 219]; // Available - Blue
                if (otherBookedSeats.includes(i)) fillColor = [231, 76, 60]; // Booked - Red
                if (userSeats.includes(i)) fillColor = [241, 196, 15]; // Your seats - Gold

                // Rounded seats
                doc.setFillColor(...fillColor);
                doc.roundedRect(x, yPos, seatSize, seatSize, 1.5, 1.5, 'F');

                // Seat number (white for better contrast)
                doc.setTextColor(255, 255, 255);
                doc.setFontSize(6);
                doc.setFont('helvetica', 'bold');
                doc.text(i.toString(), x + 2.5, yPos + 5.5);
            }

            // ========== COMPACT LEGEND ==========
            let legendY = startY + Math.ceil(totalSeats / seatsPerRow) * (seatSize + gap) + 10;

            // Legend items in one line
            const legendItems = [
                { color: [241, 196, 15], label: "Your Seats" },
                { color: [231, 76, 60], label: "Booked" },
                { color: [52, 152, 219], label: "Available" }
            ];

            let legendX = 20;
            doc.setFontSize(7);
            legendItems.forEach((item) => {
                doc.setFillColor(...item.color);
                doc.roundedRect(legendX, legendY, 5, 5, 1, 1, 'F');
                doc.setTextColor(0);
                doc.setFont('helvetica', 'normal');
                doc.text(item.label, legendX + 8, legendY + 4);
                legendX += 45;
            });

            // ========== TOTAL - EYE-CATCHING ==========
            let totalY = legendY + 12;

            // Highlight box for total
            doc.setFillColor(255, 193, 7);
            doc.roundedRect(20, totalY - 2, 60, 10, 2, 2, 'F');
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(12);
            doc.setTextColor(26, 43, 76);
            doc.text("TOTAL: NPR <?php echo number_format($totalPrice); ?>", 24, totalY + 6);

            // ========== FOOTER - CLEAN & MINIMAL ==========
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(7);
            doc.setTextColor(150, 150, 150);
            doc.text("✓ Thank you for choosing BusGo • Safe travels!", 105, 285, { align: "center" });

            // Decorative line
            doc.setDrawColor(255, 193, 7);
            doc.setLineWidth(0.3);
            doc.line(20, 275, 190, 275);

            // QR code placeholder (optional)
            doc.setFillColor(240, 240, 240);
            doc.roundedRect(170, 270, 18, 18, 2, 2, 'F');
            doc.setTextColor(100, 100, 100);
            doc.setFontSize(5);
            doc.text("SCAN", 179, 281, { align: "center" });

            // ========== SAVE ==========
            doc.save('BusGo_Ticket_<?php echo date('Ymd'); ?>.pdf');

            // Smooth redirect
            setTimeout(function () {
                window.location.href = "user_dashboard.php";
            }, 800);
        };
    </script>

</body>

</html>