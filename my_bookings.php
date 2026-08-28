<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header("Location: login.html");
    exit();
}

$username = $_SESSION['username'];
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

$conn = new mysqli("localhost", "root", "", "obtms");
if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

// Fetch user bookings with bus number and fare
$sql = "SELECT b.booking_id, b.seat_number, b.status, b.booking_time,
               s.departure_time, s.arrival_time, s.fare,
               r.source, r.destination, 
               bus.bus_name, bus.bus_number,
               b.schedule_id
        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.schedule_id
        JOIN routes r ON s.route_id = r.route_id
        JOIN buses bus ON s.bus_id = bus.bus_id
        WHERE b.username='$username'
        ORDER BY s.departure_time ASC";

$res = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - BusGo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f4fb
        }

        header {
            background: #1a2b4c;
            color: #fff;
            padding: 15px 35px;
            display: flex;
            justify-content: space-between;
            align-items: center
        }

        header h1 {
            font-size: 22px
        }

        header h1 span {
            color: #66b0ff
        }

        header nav a {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            margin-left: 18px;
            font-size: 14px;
            padding: 6px 14px;
            border-radius: 5px;
            transition: 0.2s
        }

        header nav a:hover {
            background: rgba(255, 255, 255, 0.1)
        }

        header nav a.dashboard {
            background: rgba(255, 255, 255, 0.08)
        }

        header nav a.logout {
            background: #dc3545;
            color: #fff
        }

        header nav a.logout:hover {
            background: #c82333
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px
        }

        /* Header Bar */
        .top-bar {
            background: #fff;
            padding: 18px 25px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06)
        }

        .top-bar h2 {
            color: #1a2b4c;
            font-size: 20px
        }

        .top-bar h2 i {
            color: #66b0ff;
            margin-right: 10px
        }

        .top-bar .count {
            background: #e8edf5;
            padding: 4px 14px;
            border-radius: 15px;
            font-size: 13px;
            color: #1a2b4c
        }

        .msg {
            background: #d4edda;
            color: #155724;
            padding: 10px 18px;
            border-radius: 8px;
            margin-bottom: 18px;
            border-left: 4px solid #28a745
        }

        /* Table */
        .table-wrap {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06)
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th {
            background: #1a2b4c;
            color: #fff;
            padding: 12px 15px;
            text-align: left;
            font-size: 13px;
            font-weight: 600
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #edf2f7;
            font-size: 14px;
            color: #333
        }

        tr:last-child td {
            border-bottom: none
        }

        tr.cancelled td {
            background: #fdf2f2;
            color: #999
        }

        /* Bus Number Badge */
        .bus-number-badge {
            background: #1a2b4c;
            color: #fff;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
            margin-right: 6px
        }

        .bus-number-badge i {
            margin-right: 4px;
            font-size: 10px
        }

        /* Status Badge */
        .badge {
            padding: 3px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block
        }

        .badge.confirmed {
            background: #d4edda;
            color: #155724
        }

        .badge.cancelled {
            background: #f8d7da;
            color: #721c24
        }

        .badge.completed {
            background: #cce5ff;
            color: #004085
        }

        .badge.pending {
            background: #fff3cd;
            color: #856404
        }

        /* Buttons */
        .btn-cancel {
            padding: 5px 14px;
            background: #dc3545;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            transition: 0.2s
        }

        .btn-cancel:hover {
            background: #c82333
        }

        .btn-search {
            padding: 8px 20px;
            background: #1a2b4c;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            font-size: 13px;
            display: inline-block
        }

        .btn-search:hover {
            background: #2a4a7a
        }

        .empty {
            padding: 50px 20px;
            text-align: center
        }

        .empty i {
            font-size: 50px;
            color: #cbd5e1;
            margin-bottom: 15px
        }

        .empty h3 {
            color: #1a2b4c;
            font-size: 20px;
            margin-bottom: 8px
        }

        .empty p {
            color: #6b7a8f;
            margin-bottom: 15px
        }

        @media(max-width:768px) {
            header {
                padding: 12px 20px;
                flex-wrap: wrap
            }

            header h1 {
                font-size: 18px
            }

            header nav a {
                margin-left: 8px;
                padding: 5px 10px;
                font-size: 12px
            }

            .top-bar {
                flex-direction: column;
                gap: 8px;
                text-align: center
            }

            td,
            th {
                padding: 8px 10px;
                font-size: 12px
            }
        }
    </style>
</head>

<body>

    <header>
        <h1>Bus<span>Go</span></h1>
        <nav>
            <a href="user_dashboard.php" class="dashboard"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="search_buses.php"><i class="fas fa-search"></i> Search</a>
            <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </header>

    <div class="container">

        <div class="top-bar">
            <h2><i class="fas fa-ticket-alt"></i> My Bookings</h2>
            <span class="count"><i class="fas fa-bus"></i> <?php echo $res->num_rows; ?> bookings</span>
        </div>

        <?php if ($msg): ?>
            <div class="msg"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Bus & Route</th>
                        <th>Seat</th>
                        <th>Status</th>
                        <th>Departure</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($res->num_rows > 0): ?>
                        <?php while ($row = $res->fetch_assoc()):
                            $status = strtolower($row['status']);
                            ?>
                            <tr class="<?php echo ($status == 'cancelled') ? 'cancelled' : ''; ?>">
                                <td>
                                    <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:2px">
                                        <span class="bus-number-badge"><i class="fas fa-hashtag"></i>
                                            <?php echo htmlspecialchars($row['bus_number']); ?></span>
                                        <span
                                            style="font-weight:600;font-size:13px;"><?php echo htmlspecialchars($row['bus_name']); ?></span>
                                    </div>
                                    <div style="font-size:13px;color:#555;margin-top:2px">
                                        <strong><?php echo htmlspecialchars($row['source']); ?></strong>
                                        <i class="fas fa-arrow-right" style="color:#66b0ff;font-size:11px;margin:0 5px"></i>
                                        <strong><?php echo htmlspecialchars($row['destination']); ?></strong>
                                    </div>
                                    <div style="font-size:12px;color:#888;margin-top:2px">
                                        <i class="far fa-calendar-alt"></i>
                                        <?php echo date('M d, Y', strtotime($row['departure_time'])); ?>
                                        <?php if (isset($row['fare'])): ?>
                                            <span style="margin-left:12px;"><i class="fas fa-money-bill-wave"
                                                    style="color:#28a745;"></i> NPR
                                                <?php echo number_format($row['fare']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><i class="fas fa-chair" style="color:#28a745;margin-right:5px"></i>
                                    <?php echo htmlspecialchars($row['seat_number']); ?></td>
                                <td><span class="badge <?php echo $status; ?>"><?php echo ucfirst($status); ?></span></td>
                                <td style="font-size:13px;color:#555">
                                    <i class="far fa-clock"></i> <?php echo date('h:i A', strtotime($row['departure_time'])); ?>
                                    <div style="font-size:12px;color:#888;margin-top:2px">
                                        <i class="fas fa-flag-checkered"></i>
                                        <?php echo date('h:i A', strtotime($row['arrival_time'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($status == 'confirmed'): ?>
                                        <a href="cancel_booking.php?booking_id=<?php echo $row['booking_id']; ?>"
                                            onclick="return confirm('Cancel this booking?')" class="btn-cancel"><i
                                                class="fas fa-times"></i> Cancel</a>
                                    <?php else: ?>
                                        <span style="color:#999;font-size:12px">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty">
                                    <i class="fas fa-ticket-alt"></i>
                                    <h3>No Bookings</h3>
                                    <p>You haven't booked any tickets yet.</p>
                                    <a href="search_buses.php" class="btn-search"><i class="fas fa-search"></i> Search
                                        Buses</a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>

</html>