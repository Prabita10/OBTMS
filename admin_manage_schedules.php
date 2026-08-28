<?php
session_start();
if(!isset($_SESSION['username']) || $_SESSION['role'] != 'admin'){
    header("Location: login.html");
    exit();
}

$servername="localhost";
$usernameDB="root";
$passwordDB="";
$dbname="obtms";

$conn = new mysqli($servername,$usernameDB,$passwordDB,$dbname);
if($conn->connect_error){ die("Connection failed: ".$conn->connect_error); }

// Fetch schedules with bus, route info, and calculate available seats
$sql = "SELECT s.schedule_id, 
               b.bus_name, 
               b.bus_number,
               b.total_seats,
               r.source, 
               r.destination, 
               s.departure_time, 
               s.arrival_time, 
               s.fare,
               (SELECT COUNT(*) FROM bookings WHERE schedule_id = s.schedule_id AND status != 'canceled') as booked_seats
        FROM schedules s
        JOIN buses b ON s.bus_id = b.bus_id
        JOIN routes r ON s.route_id = r.route_id
        ORDER BY s.departure_time DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Schedules - OBTMS</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',Arial,sans-serif;background:#f0f4fb;min-height:100vh}

header{background:#1a2b4c;color:#fff;padding:15px 35px;display:flex;justify-content:space-between;align-items:center}
header h1{font-size:22px}
header h1 span{color:#66b0ff}
header nav a{color:rgba(255,255,255,0.85);text-decoration:none;margin-left:18px;font-size:14px;padding:6px 14px;border-radius:5px;transition:0.2s}
header nav a:hover{background:rgba(255,255,255,0.1)}
header nav a.dashboard{background:rgba(255,255,255,0.08)}
header nav a.logout{background:#dc3545;color:#fff}
header nav a.logout:hover{background:#c82333}

.container{max-width:1200px;margin:30px auto;padding:0 20px}

.header-actions{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px}
.header-actions h2{color:#1a2b4c;font-size:24px}
.header-actions h2 i{color:#66b0ff;margin-right:10px}
.btn-add{background:#28a745;color:#fff;padding:10px 22px;border-radius:8px;text-decoration:none;font-weight:600;transition:0.3s;display:inline-flex;align-items:center;gap:8px}
.btn-add:hover{background:#218838;transform:translateY(-2px);box-shadow:0 4px 12px rgba(40,167,69,0.3)}

.table-wrapper{background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.06);border:1px solid #e8ecf3;overflow-x:auto}
table{width:100%;border-collapse:collapse}
th{background:#1a2b4c;color:#fff;padding:14px 18px;text-align:left;font-size:13px;font-weight:600;white-space:nowrap}
td{padding:12px 18px;border-bottom:1px solid #eef2f7;font-size:14px;color:#333}
tr:hover{background:#f8faff}

.status-badge{padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600}
.status-badge.available{background:#d4edda;color:#155724}
.status-badge.limited{background:#fff3cd;color:#856404}
.status-badge.full{background:#f8d7da;color:#721c24}

.btn-action{padding:6px 14px;border-radius:6px;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:5px;transition:0.3s}
.btn-edit{background:#007bff;color:#fff}
.btn-edit:hover{background:#0056b3}
.btn-delete{background:#dc3545;color:#fff}
.btn-delete:hover{background:#c82333}

.no-data{text-align:center;padding:40px;color:#6b7a8f}
.no-data i{font-size:48px;color:#d5dce8;display:block;margin-bottom:15px}

@media(max-width:768px){
    header{padding:12px 20px;flex-wrap:wrap}
    header h1{font-size:18px}
    header nav a{margin-left:8px;padding:5px 10px;font-size:12px}
    .header-actions{flex-direction:column;align-items:flex-start}
    .header-actions h2{font-size:20px}
    th,td{padding:8px 12px;font-size:13px}
    .btn-action{padding:4px 10px;font-size:12px}
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
    <div class="header-actions">
        <h2><i class="fas fa-calendar-alt"></i> Manage Schedules</h2>
        <a href="add_schedule.php" class="btn-add">
            <i class="fas fa-plus-circle"></i> Add New Schedule
        </a>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Bus</th>
                    <th>Route</th>
                    <th>Departure</th>
                    <th>Arrival</th>
                    <th>Fare (NPR)</th>
                    <th>Seats</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): 
                        $available_seats = $row['total_seats'] - $row['booked_seats'];
                        
                        // ============================================
                        // UPDATED STATUS LOGIC - Percentage Based
                        // ============================================
                        $percentage = ($available_seats / $row['total_seats']) * 100;
                        
                        if ($percentage > 30) {
                            $status_class = 'available';
                            $status_text = 'Available';
                        } elseif ($percentage > 0) {
                            $status_class = 'limited';
                            $status_text = 'Limited';
                        } else {
                            $status_class = 'full';
                            $status_text = 'Full';
                        }
                    ?>
                    <tr>
                        <td>#<?php echo $row['schedule_id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['bus_number']); ?></strong>
                            <br><small style="color:#6b7a8f;"><?php echo htmlspecialchars($row['bus_name']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($row['source'] . ' → ' . $row['destination']); ?></td>
                        <td><?php echo date('M d, h:i A', strtotime($row['departure_time'])); ?></td>
                        <td><?php echo date('M d, h:i A', strtotime($row['arrival_time'])); ?></td>
                        <td><strong>NPR <?php echo number_format($row['fare'], 2); ?></strong></td>
                        <td><?php echo $available_seats; ?> / <?php echo $row['total_seats']; ?></td>
                        <td>
                            <span class="status-badge <?php echo $status_class; ?>">
                                <?php echo $status_text; ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit_schedule.php?id=<?php echo $row['schedule_id']; ?>" class="btn-action btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="delete_schedule.php?id=<?php echo $row['schedule_id']; ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this schedule?');">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">
                            <div class="no-data">
                                <i class="fas fa-calendar-alt"></i>
                                <p>No schedules found. <a href="add_schedule.php" style="color:#007bff;">Add your first schedule</a></p>
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