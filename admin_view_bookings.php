<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost", "root", "root", "obtms");
if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

// ============ STATISTICS ============
$totalBookings = $conn->query("SELECT COUNT(*) as total FROM bookings")->fetch_assoc()['total'];
$totalActive = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'confirmed'")->fetch_assoc()['total'];

// Get cancelled count from cancel_feedback table
$totalCancelled = $conn->query("SELECT COUNT(*) as total FROM cancel_feedback")->fetch_assoc()['total'];

$today = date('Y-m-d');
$todayBookings = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE DATE(booking_time) = '$today'")->fetch_assoc()['total'];
$todayCancelled = $conn->query("SELECT COUNT(*) as total FROM cancel_feedback WHERE DATE(cancel_time) = '$today'")->fetch_assoc()['total'];

$thisMonth = date('Y-m');
$monthBookings = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE DATE_FORMAT(booking_time, '%Y-%m') = '$thisMonth'")->fetch_assoc()['total'];

// ============ FETCH CANCELLED BOOKINGS FROM cancel_feedback TABLE ============
$cancelledSql = "SELECT cf.id, cf.booking_id, cf.username, cf.feedback, cf.cancel_time
                 FROM cancel_feedback cf
                 ORDER BY cf.cancel_time DESC";
$cancelledBookings = $conn->query($cancelledSql);

// ============ FETCH ALL BOOKINGS (from bookings table) ============
$sql = "SELECT b.booking_id, b.username, b.seat_number, b.status, b.booking_time,
               s.departure_time, s.arrival_time, s.fare,
               r.source, r.destination,
               bus.bus_name, bus.bus_number
        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.schedule_id
        JOIN routes r ON s.route_id = r.route_id
        JOIN buses bus ON s.bus_id = bus.bus_id
        ORDER BY b.booking_time DESC";
$bookings = $conn->query($sql);

// ============ FETCH ACTIVE BOOKINGS ============
$activeSql = "SELECT b.booking_id, b.username, b.seat_number, b.booking_time,
                     s.departure_time, s.arrival_time, s.fare,
                     r.source, r.destination,
                     bus.bus_name, bus.bus_number
              FROM bookings b
              JOIN schedules s ON b.schedule_id = s.schedule_id
              JOIN routes r ON s.route_id = r.route_id
              JOIN buses bus ON s.bus_id = bus.bus_id
              WHERE b.status = 'confirmed'
              ORDER BY b.booking_time DESC";
$activeBookings = $conn->query($activeSql);

// ============ FETCH DELETED BOOKINGS WITH CANCELLATION FEEDBACK ============
// These are bookings that were cancelled and then deleted from bookings table
$deletedBookingsSql = "SELECT cf.id as cancel_id, cf.booking_id, cf.username, cf.feedback, cf.cancel_time,
                              'Deleted' as bus_name,
                              'Deleted' as bus_number,
                              'Deleted' as source,
                              'Deleted' as destination,
                              'Deleted' as seat_number,
                              'N/A' as fare
                       FROM cancel_feedback cf
                       LEFT JOIN bookings b ON cf.booking_id = b.booking_id
                       WHERE b.booking_id IS NULL
                       ORDER BY cf.cancel_time DESC";
$deletedBookings = $conn->query($deletedBookingsSql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking & Cancellation Reports - BusGo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f4fb;
            min-height: 100vh
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
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px
        }

        .page-title {
            color: #1a2b4c;
            font-size: 24px;
            margin-bottom: 20px
        }

        .page-title i {
            color: #66b0ff;
            margin-right: 10px
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 25px
        }

        .stat-card {
            background: #fff;
            padding: 18px 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            border: 1px solid #e8ecf3;
            text-align: center
        }

        .stat-card .number {
            font-size: 28px;
            font-weight: 700;
            color: #1a2b4c
        }

        .stat-card .label {
            color: #6b7a8f;
            font-size: 12px;
            margin-top: 4px
        }

        .stat-card .icon {
            font-size: 22px;
            margin-bottom: 6px
        }

        .stat-card.total .icon {
            color: #007bff
        }

        .stat-card.active .icon {
            color: #28a745
        }

        .stat-card.cancelled .icon {
            color: #dc3545
        }

        .stat-card.today .icon {
            color: #17a2b8
        }

        .stat-card.today-cancel .icon {
            color: #dc3545
        }

        .stat-card.month .icon {
            color: #ffc107
        }

        /* Tabs */
        .tabs {
            display: flex;
            gap: 5px;
            margin-bottom: 20px;
            flex-wrap: wrap
        }

        .tab-btn {
            padding: 10px 25px;
            border: none;
            border-radius: 8px 8px 0 0;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: 0.3s;
            background: #e8edf5;
            color: #1a2b4c
        }

        .tab-btn:hover {
            background: #d5dce8
        }

        .tab-btn.active {
            background: #1a2b4c;
            color: #fff
        }

        .tab-btn i {
            margin-right: 8px
        }

        .tab-btn .count {
            background: rgba(255, 255, 255, 0.2);
            padding: 0 8px;
            border-radius: 10px;
            font-size: 11px;
            margin-left: 5px
        }

        .tab-btn.active .count {
            background: rgba(255, 255, 255, 0.2)
        }

        .tab-content {
            display: none
        }

        .tab-content.active {
            display: block
        }

        /* Toolbar */
        .toolbar {
            background: #fff;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            border: 1px solid #e8ecf3
        }

        .toolbar .left {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center
        }

        .toolbar .right {
            display: flex;
            gap: 10px;
            flex-wrap: wrap
        }

        .toolbar input {
            padding: 9px 14px;
            border-radius: 8px;
            border: 1.5px solid #e2e8f0;
            font-size: 13px;
            background: #fafcff;
            min-width: 200px
        }

        .toolbar input:focus {
            outline: none;
            border-color: #1a2b4c
        }

        .toolbar .btn {
            padding: 9px 18px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px
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

        .btn-outline {
            background: #e8edf5;
            color: #1a2b4c
        }

        .btn-outline:hover {
            background: #d5dce8
        }

        /* Table */
        .table-wrap {
            background: #fff;
            border-radius: 12px;
            overflow: auto;
            border: 1px solid #e8ecf3;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06)
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px
        }

        th {
            background: #1a2b4c;
            color: #fff;
            padding: 12px 15px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 10
        }

        td {
            padding: 11px 15px;
            border-bottom: 1px solid #edf2f7;
            font-size: 13px;
            color: #333;
            vertical-align: middle
        }

        tr:last-child td {
            border-bottom: none
        }

        tr.cancelled td {
            background: #fdf2f2;
            color: #999
        }

        tr.deleted td {
            background: #fdf2f2;
            color: #999;
            opacity: 0.7
        }

        tr:hover td {
            background: #f8faff
        }

        .bus-badge {
            background: #1a2b4c;
            color: #fff;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
            white-space: nowrap
        }

        .bus-badge i {
            margin-right: 4px;
            font-size: 10px
        }

        .badge {
            padding: 3px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block
        }

        .badge.confirmed {
            background: #d4edda;
            color: #155724
        }

        .badge.canceled {
            background: #f8d7da;
            color: #721c24
        }

        .badge.pending {
            background: #fff3cd;
            color: #856404
        }

        .feedback-text {
            max-width: 200px;
            word-wrap: break-word;
            font-style: italic;
            color: #555;
            font-size: 12px
        }

        .no-data {
            text-align: center;
            padding: 50px 20px;
            color: #6b7a8f
        }

        .no-data i {
            font-size: 48px;
            color: #d5dce8;
            display: block;
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

            .stats-grid {
                grid-template-columns: repeat(3, 1fr)
            }

            .toolbar {
                flex-direction: column;
                align-items: stretch
            }

            .toolbar .left,
            .toolbar .right {
                flex-wrap: wrap
            }

            .toolbar input {
                flex: 1;
                min-width: 120px
            }

            table {
                font-size: 12px;
                min-width: 700px
            }

            td,
            th {
                padding: 8px 10px
            }

            .tab-btn {
                padding: 8px 15px;
                font-size: 12px
            }
        }

        @media(max-width:480px) {
            .stats-grid {
                grid-template-columns: 1fr 1fr
            }

            .container {
                padding: 0 10px
            }
        }
    </style>
</head>

<body>

    <header>
        <h1>Bus<span>Go</span></h1>
        <nav>
            <a href="admin_dashboard.php" class="dashboard"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </header>

    <div class="container">
        <h2 class="page-title"><i class="fas fa-chart-bar"></i> Booking & Cancellation Reports</h2>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="icon"><i class="fas fa-ticket-alt"></i></div>
                <div class="number"><?php echo $totalBookings; ?></div>
                <div class="label">Total Bookings</div>
            </div>
            <div class="stat-card active">
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <div class="number"><?php echo $totalActive; ?></div>
                <div class="label">Active Bookings</div>
            </div>
            <div class="stat-card cancelled">
                <div class="icon"><i class="fas fa-times-circle"></i></div>
                <div class="number"><?php echo $totalCancelled; ?></div>
                <div class="label">Cancelled</div>
            </div>
            <div class="stat-card today">
                <div class="icon"><i class="fas fa-calendar-day"></i></div>
                <div class="number"><?php echo $todayBookings; ?></div>
                <div class="label">Today's Bookings</div>
            </div>
            <div class="stat-card today-cancel">
                <div class="icon"><i class="fas fa-calendar-times"></i></div>
                <div class="number"><?php echo $todayCancelled; ?></div>
                <div class="label">Today's Cancellations</div>
            </div>
            <div class="stat-card month">
                <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="number"><?php echo $monthBookings; ?></div>
                <div class="label">This Month</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-btn active" data-tab="all" onclick="switchTab('all')">
                <i class="fas fa-list"></i> All Bookings <span class="count"><?php echo $totalBookings; ?></span>
            </button>
            <button class="tab-btn" data-tab="active" onclick="switchTab('active')">
                <i class="fas fa-check-circle"></i> Active <span class="count"><?php echo $totalActive; ?></span>
            </button>
            <button class="tab-btn" data-tab="cancelled" onclick="switchTab('cancelled')">
                <i class="fas fa-times-circle"></i> Cancelled <span class="count"><?php echo $totalCancelled; ?></span>
            </button>
        </div>

        <!-- Tab: All Bookings -->
        <div id="tab-all" class="tab-content active">
            <div class="toolbar">
                <div class="left">
                    <input type="text" id="searchAll" placeholder="🔍 Search user, bus, route..."
                        onkeyup="filterTable('all')">
                    <button class="btn btn-primary" onclick="searchTable('all')"><i class="fas fa-search"></i>
                        Search</button>
                    <button class="btn btn-outline" onclick="resetFilters('all')"><i class="fas fa-undo"></i>
                        Reset</button>
                </div>
                <div class="right">
                    <button class="btn btn-success" onclick="exportCSV('all')"><i class="fas fa-file-csv"></i> Export
                        CSV</button>
                </div>
            </div>

            <div class="table-wrap">
                <table id="table-all">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Bus</th>
                            <th>Route</th>
                            <th>Seat</th>
                            <th>Status</th>
                            <th>Departure</th>
                            <th>Fare</th>
                            <th>Booked At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($bookings->num_rows > 0): ?>
                            <?php while ($row = $bookings->fetch_assoc()):
                                $status = strtolower($row['status']);
                                $isCancelled = ($status == 'canceled');
                                ?>
                                <tr class="<?php echo $isCancelled ? 'cancelled' : ''; ?>">
                                    <td>#<?php echo $row['booking_id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                                    <td>
                                        <span class="bus-badge"><i class="fas fa-hashtag"></i>
                                            <?php echo htmlspecialchars($row['bus_number']); ?></span>
                                        <div style="font-size:11px;color:#6b7a8f;margin-top:2px;">
                                            <?php echo htmlspecialchars($row['bus_name']); ?></div>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($row['source']); ?>
                                        <i class="fas fa-arrow-right" style="color:#66b0ff;font-size:10px;margin:0 3px;"></i>
                                        <?php echo htmlspecialchars($row['destination']); ?>
                                    </td>
                                    <td><i class="fas fa-chair" style="color:#28a745;"></i>
                                        <?php echo htmlspecialchars($row['seat_number']); ?></td>
                                    <td><span class="badge <?php echo $status; ?>"><?php echo ucfirst($status); ?></span></td>
                                    <td style="font-size:12px;color:#555;">
                                        <?php echo date('M d, Y', strtotime($row['departure_time'])); ?>
                                        <div style="font-size:10px;color:#888;">
                                            <?php echo date('h:i A', strtotime($row['departure_time'])); ?></div>
                                    </td>
                                    <td><strong style="color:#28a745;">NPR <?php echo number_format($row['fare']); ?></strong>
                                    </td>
                                    <td style="font-size:12px;color:#555;">
                                        <?php echo date('M d, Y', strtotime($row['booking_time'])); ?>
                                        <div style="font-size:10px;color:#888;">
                                            <?php echo date('h:i A', strtotime($row['booking_time'])); ?></div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9">
                                    <div class="no-data">
                                        <i class="fas fa-ticket-alt"></i>
                                        <p>No bookings found</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Active Bookings -->
        <div id="tab-active" class="tab-content">
            <div class="toolbar">
                <div class="left">
                    <input type="text" id="searchActive" placeholder="🔍 Search user, bus, route..."
                        onkeyup="filterTable('active')">
                    <button class="btn btn-primary" onclick="searchTable('active')"><i class="fas fa-search"></i>
                        Search</button>
                    <button class="btn btn-outline" onclick="resetFilters('active')"><i class="fas fa-undo"></i>
                        Reset</button>
                </div>
                <div class="right">
                    <button class="btn btn-success" onclick="exportCSV('active')"><i class="fas fa-file-csv"></i> Export
                        CSV</button>
                </div>
            </div>

            <div class="table-wrap">
                <table id="table-active">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Bus</th>
                            <th>Route</th>
                            <th>Seat</th>
                            <th>Departure</th>
                            <th>Fare</th>
                            <th>Booked At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($activeBookings->num_rows > 0): ?>
                            <?php while ($row = $activeBookings->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $row['booking_id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                                    <td>
                                        <span class="bus-badge"><i class="fas fa-hashtag"></i>
                                            <?php echo htmlspecialchars($row['bus_number']); ?></span>
                                        <div style="font-size:11px;color:#6b7a8f;margin-top:2px;">
                                            <?php echo htmlspecialchars($row['bus_name']); ?></div>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($row['source']); ?>
                                        <i class="fas fa-arrow-right" style="color:#66b0ff;font-size:10px;margin:0 3px;"></i>
                                        <?php echo htmlspecialchars($row['destination']); ?>
                                    </td>
                                    <td><i class="fas fa-chair" style="color:#28a745;"></i>
                                        <?php echo htmlspecialchars($row['seat_number']); ?></td>
                                    <td style="font-size:12px;color:#555;">
                                        <?php echo date('M d, Y', strtotime($row['departure_time'])); ?>
                                        <div style="font-size:10px;color:#888;">
                                            <?php echo date('h:i A', strtotime($row['departure_time'])); ?></div>
                                    </td>
                                    <td><strong style="color:#28a745;">NPR <?php echo number_format($row['fare']); ?></strong>
                                    </td>
                                    <td style="font-size:12px;color:#555;">
                                        <?php echo date('M d, Y', strtotime($row['booking_time'])); ?>
                                        <div style="font-size:10px;color:#888;">
                                            <?php echo date('h:i A', strtotime($row['booking_time'])); ?></div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8">
                                    <div class="no-data">
                                        <i class="fas fa-check-circle" style="color:#28a745;"></i>
                                        <p>No active bookings found</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Cancelled Only - Directly from cancel_feedback -->
        <div id="tab-cancelled" class="tab-content">
            <div class="toolbar">
                <div class="left">
                    <input type="text" id="searchCancel" placeholder="🔍 Search user..."
                        onkeyup="filterTable('cancel')">
                    <button class="btn btn-primary" onclick="searchTable('cancel')"><i class="fas fa-search"></i>
                        Search</button>
                    <button class="btn btn-outline" onclick="resetFilters('cancel')"><i class="fas fa-undo"></i>
                        Reset</button>
                </div>
                <div class="right">
                    <button class="btn btn-success" onclick="exportCSV('cancel')"><i class="fas fa-file-csv"></i> Export
                        CSV</button>
                </div>
            </div>

            <div class="table-wrap">
                <table id="table-cancel">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Booking ID</th>
                            <th>User</th>
                            <th>Feedback</th>
                            <th>Cancelled At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($cancelledBookings->num_rows > 0): ?>
                            <?php while ($row = $cancelledBookings->fetch_assoc()): ?>
                                <tr class="cancelled">
                                    <td>#<?php echo $row['id']; ?></td>
                                    <td>#<?php echo $row['booking_id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                                    <td class="feedback-text">
                                        <?php if (!empty($row['feedback'])): ?>
                                            <?php echo htmlspecialchars($row['feedback']); ?>
                                        <?php else: ?>
                                            <span style="color:#999;">No feedback provided</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size:12px;color:#555;">
                                        <?php echo date('M d, Y', strtotime($row['cancel_time'])); ?>
                                        <div style="font-size:10px;color:#888;">
                                            <?php echo date('h:i A', strtotime($row['cancel_time'])); ?></div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">
                                    <div class="no-data">
                                        <i class="fas fa-check-circle" style="color:#28a745;"></i>
                                        <p>No cancelled bookings found</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            document.getElementById('tab-' + tabName).classList.add('active');
            document.querySelector('.tab-btn[data-tab="' + tabName + '"]').classList.add('active');
        }

        function searchTable(type) {
            filterTable(type);
        }

        function filterTable(type) {
            const searchMap = {
                'all': 'searchAll',
                'active': 'searchActive',
                'cancel': 'searchCancel'
            };
            const tableMap = {
                'all': 'table-all',
                'active': 'table-active',
                'cancel': 'table-cancel'
            };

            const searchId = searchMap[type];
            const search = document.getElementById(searchId).value.toLowerCase();
            const tableId = tableMap[type];
            const rows = document.querySelectorAll('#' + tableId + ' tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = search && !text.includes(search) ? 'none' : '';
            });
        }

        function resetFilters(type) {
            const searchMap = {
                'all': 'searchAll',
                'active': 'searchActive',
                'cancel': 'searchCancel'
            };
            document.getElementById(searchMap[type]).value = '';
            filterTable(type);
        }

        function exportCSV(type) {
            const tableMap = {
                'all': 'table-all',
                'active': 'table-active',
                'cancel': 'table-cancel'
            };
            const tableId = tableMap[type];
            const rows = document.querySelectorAll('#' + tableId + ' tbody tr');
            let csv = '';

            const headers = document.querySelectorAll('#' + tableId + ' thead th');
            const headerRow = [];
            headers.forEach(th => headerRow.push(th.textContent.trim()));
            csv += headerRow.join(',') + '\n';

            rows.forEach(row => {
                if (row.style.display !== 'none') {
                    const cells = row.querySelectorAll('td');
                    const rowData = [];
                    cells.forEach(cell => {
                        let text = cell.textContent.trim().replace(/,/g, ';');
                        text = text.replace(/\s+/g, ' ').trim();
                        rowData.push(text);
                    });
                    csv += rowData.join(',') + '\n';
                }
            });

            const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            const typeNames = {
                'all': 'all_bookings',
                'active': 'active_bookings',
                'cancel': 'cancelled_bookings'
            };
            a.download = typeNames[type] + '_' + '<?php echo date('Y-m-d'); ?>' + '.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }
    </script>

</body>

</html>