<?php
session_start();
if(!isset($_SESSION['username']) || $_SESSION['role'] != 'admin'){
    header("Location: login.html");
    exit();
}

$servername="localhost";
$usernameDB="root";
$passwordDB="root";
$dbname="obtms";
$conn=new mysqli($servername,$usernameDB,$passwordDB,$dbname);
if($conn->connect_error){ die("Connection failed: ".$conn->connect_error); }

$id=$_GET['id'];
$msg="";
$msgType="";

// Fetch buses and routes
$buses = $conn->query("SELECT bus_id, bus_number, bus_name FROM buses WHERE status = 'active'");
$routes = $conn->query("SELECT route_id, source, destination FROM routes");

// Fetch current schedule
$schedule = $conn->query("SELECT s.*, b.total_seats 
                          FROM schedules s 
                          JOIN buses b ON s.bus_id = b.bus_id 
                          WHERE s.schedule_id=$id")->fetch_assoc();

if(isset($_POST['update'])){
    $bus_id = (int)$_POST['bus_id'];
    $route_id = (int)$_POST['route_id'];
    $departure_time = mysqli_real_escape_string($conn, $_POST['departure_time']);
    $arrival_time = mysqli_real_escape_string($conn, $_POST['arrival_time']);
    $fare = (float)$_POST['fare'];

    // Validate
    if($fare < 0){
        $msg = "Fare cannot be negative!";
        $msgType = "error";
    } elseif(strtotime($departure_time) >= strtotime($arrival_time)){
        $msg = "Departure time must be before arrival time!";
        $msgType = "error";
    } else {
        $sql = "UPDATE schedules SET 
                bus_id=$bus_id, 
                route_id=$route_id, 
                departure_time='$departure_time', 
                arrival_time='$arrival_time',
                fare='$fare' 
                WHERE schedule_id=$id";
        if($conn->query($sql)===TRUE){ 
            $msg = "Schedule updated successfully!";
            $msgType = "success";
        } else { 
            $msg = "Error: ".$conn->error;
            $msgType = "error";
        }
    }
    // Refresh schedule
    $schedule = $conn->query("SELECT s.*, b.total_seats 
                              FROM schedules s 
                              JOIN buses b ON s.bus_id = b.bus_id 
                              WHERE s.schedule_id=$id")->fetch_assoc();
    // Refresh buses and routes
    $buses = $conn->query("SELECT bus_id, bus_number, bus_name FROM buses WHERE status = 'active'");
    $routes = $conn->query("SELECT route_id, source, destination FROM routes");
}

// Calculate booked seats and available seats
$booked_sql = "SELECT COUNT(*) as booked FROM bookings WHERE schedule_id = $id AND status != 'canceled'";
$booked_result = $conn->query($booked_sql);
$booked = $booked_result->fetch_assoc();
$booked_seats = $booked['booked'];
$available_seats = $schedule['total_seats'] - $booked_seats;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Schedule - BusGo Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Roboto', sans-serif;
}

body {
    background: #f0f4fb;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

/* Main Container */
.container {
    background: #fff;
    max-width: 780px;
    width: 100%;
    padding: 45px 50px;
    border-radius: 16px;
    box-shadow: 0 8px 40px rgba(0, 0, 0, 0.08);
    border: 1px solid #e8ecf3;
}

/* Header */
.header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 8px;
}

.header .icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #1a2b4c, #2a4a7a);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 22px;
}

.header h2 {
    font-size: 26px;
    font-weight: 700;
    color: #1a2b4c;
}

.header h2 span {
    color: #66b0ff;
}

.subtitle {
    color: #6b7a8f;
    font-size: 14px;
    margin-left: 65px;
    margin-bottom: 25px;
}

/* Schedule ID Badge */
.schedule-id-badge {
    display: inline-block;
    background: #e8edf5;
    color: #1a2b4c;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    margin-left: 65px;
    margin-bottom: 25px;
}

.schedule-id-badge i {
    margin-right: 6px;
    color: #66b0ff;
}

/* Schedule Preview */
.schedule-preview {
    background: linear-gradient(135deg, #f8faff, #e8f0fe);
    padding: 18px 22px;
    border-radius: 12px;
    margin-left: 65px;
    margin-bottom: 28px;
    border: 1px solid #dce4f0;
    display: grid;
    grid-template-columns: 1fr auto 1fr auto 1fr auto 1fr;
    align-items: center;
    gap: 5px;
}

.schedule-preview .preview-item {
    text-align: center;
}

.schedule-preview .preview-item .value {
    font-weight: 600;
    color: #1a2b4c;
    font-size: 14px;
}

.schedule-preview .preview-item .label {
    display: block;
    font-weight: 400;
    color: #6b7a8f;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 2px;
}

.schedule-preview .preview-divider {
    color: #dce4f0;
    font-size: 18px;
}

.schedule-preview .preview-bus {
    color: #1a2b4c;
}

/* Alert Messages */
.alert {
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 22px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideDown 0.4s ease;
}

.alert i {
    font-size: 18px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Form */
.form-group {
    margin-bottom: 22px;
}

.form-group label {
    display: block;
    font-weight: 500;
    color: #1a2b4c;
    margin-bottom: 6px;
    font-size: 14px;
}

.form-group label .required {
    color: #dc3545;
    margin-left: 2px;
}

.form-group label .hint {
    color: #6b7a8f;
    font-weight: 400;
    font-size: 12px;
    margin-left: 6px;
}

/* Input with icon wrapper */
.input-wrapper {
    position: relative;
}

.input-wrapper .icon-left {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6b7a8f;
    font-size: 15px;
    pointer-events: none;
    transition: 0.3s;
    z-index: 1;
}

.input-wrapper select,
.input-wrapper input {
    width: 100%;
    padding: 12px 15px 12px 45px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 15px;
    transition: 0.3s;
    background: #fafcff;
    color: #1a2b4c;
    appearance: none;
    -webkit-appearance: none;
    cursor: pointer;
}

.input-wrapper select {
    padding-right: 40px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7a8f' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 15px center;
}

.input-wrapper select:focus,
.input-wrapper input:focus {
    outline: none;
    border-color: #1a2b4c;
    box-shadow: 0 0 0 4px rgba(26, 43, 76, 0.08);
    background: #fff;
}

.input-wrapper select:hover,
.input-wrapper input:hover {
    border-color: #66b0ff;
}

.input-wrapper select:focus + .icon-left,
.input-wrapper input:focus + .icon-left {
    color: #1a2b4c;
}

/* Two columns layout */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

/* Buttons */
.actions {
    display: flex;
    gap: 12px;
    margin-top: 10px;
}

.btn {
    padding: 13px 35px;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
}

.btn-update {
    background: #1a2b4c;
    color: #fff;
    flex: 1;
    justify-content: center;
}

.btn-update:hover {
    background: #2a4a7a;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(26, 43, 76, 0.25);
}

.btn-update i {
    font-size: 16px;
}

.btn-back {
    background: #e8edf5;
    color: #1a2b4c;
    padding: 13px 25px;
}

.btn-back:hover {
    background: #d5dce8;
    transform: translateY(-2px);
}

.btn-back i {
    font-size: 14px;
}

/* Responsive */
@media(max-width: 768px) {
    .container {
        padding: 30px 25px;
    }

    .header h2 {
        font-size: 22px;
    }

    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .subtitle,
    .schedule-id-badge,
    .schedule-preview {
        margin-left: 0;
    }

    .schedule-preview {
        grid-template-columns: 1fr;
        gap: 6px;
        padding: 15px;
    }

    .schedule-preview .preview-divider {
        transform: rotate(90deg);
    }

    .header .icon {
        width: 45px;
        height: 45px;
        font-size: 18px;
    }

    .btn {
        padding: 12px 20px;
        font-size: 14px;
    }

    .actions {
        flex-direction: column;
    }

    .btn-back {
        justify-content: center;
    }
}

@media(max-width: 480px) {
    .container {
        padding: 20px 15px;
    }

    .header h2 {
        font-size: 19px;
    }

    .input-wrapper select,
    .input-wrapper input {
        padding: 10px 15px 10px 40px;
        font-size: 14px;
    }

    .input-wrapper .icon-left {
        left: 12px;
        font-size: 13px;
    }
}

/* Info Box */
.info-box {
    background: #e8f0fe;
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}
.info-box .info-item {
    font-size: 13px;
    color: #1a2b4c;
}
.info-box .info-item strong {
    color: #1a2b4c;
}
.info-box .info-item .seats-available {
    color: #28a745;
    font-weight: 700;
}
.info-box .info-item .seats-booked {
    color: #dc3545;
    font-weight: 700;
}
.info-box .info-item .seats-total {
    color: #007bff;
    font-weight: 700;
}
</style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="header">
        <div class="icon">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <h2>Edit <span>Schedule</span></h2>
    </div>
    <div class="subtitle">Update schedule details and manage bus timings</div>
    <div class="schedule-id-badge">
        <i class="fas fa-tag"></i> Schedule ID: #<?php echo $id; ?>
    </div>

    <!-- Schedule Preview -->
    <div class="schedule-preview">
        <div class="preview-item">
            <span class="value"><?php echo date('h:i A', strtotime($schedule['departure_time'])); ?></span>
            <span class="label"><i class="fas fa-clock"></i> Departure</span>
        </div>
        <div class="preview-divider">
            <i class="fas fa-arrow-right"></i>
        </div>
        <div class="preview-item">
            <span class="value"><?php echo date('h:i A', strtotime($schedule['arrival_time'])); ?></span>
            <span class="label"><i class="fas fa-flag-checkered"></i> Arrival</span>
        </div>
        <div class="preview-divider">
            <i class="fas fa-circle" style="font-size: 6px; color: #66b0ff;"></i>
        </div>
        <div class="preview-item">
            <span class="value preview-bus"><i class="fas fa-money-bill-wave"></i> NPR <?php echo number_format($schedule['fare'] ?? 0, 2); ?></span>
            <span class="label">Fare</span>
        </div>
        <div class="preview-divider">
            <i class="fas fa-circle" style="font-size: 6px; color: #66b0ff;"></i>
        </div>
        <div class="preview-item">
            <span class="value preview-bus"><i class="fas fa-chair"></i> <?php echo $available_seats; ?></span>
            <span class="label">Available Seats</span>
        </div>
    </div>

    <!-- Seat Info Box -->
    <div class="info-box">
        <div class="info-item">
            <i class="fas fa-chair"></i> Total: <strong class="seats-total"><?php echo $schedule['total_seats']; ?></strong>
        </div>
        <div class="info-item">
            <i class="fas fa-user-check"></i> Booked: <strong class="seats-booked"><?php echo $booked_seats; ?></strong>
        </div>
        <div class="info-item">
            <i class="fas fa-chair"></i> Available: <strong class="seats-available"><?php echo $available_seats; ?></strong>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if($msg != ""): ?>
        <div class="alert alert-<?php echo $msgType; ?>">
            <i class="fas <?php echo $msgType == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label>Bus <span class="required">*</span></label>
                <div class="input-wrapper">
                    <i class="fas fa-bus icon-left"></i>
                    <select name="bus_id" required>
                        <?php 
                        $buses->data_seek(0);
                        while($b=$buses->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $b['bus_id']; ?>" <?php if($b['bus_id']==$schedule['bus_id']) echo 'selected'; ?>>
                                <?php echo $b['bus_number'] . ' - ' . htmlspecialchars($b['bus_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Route <span class="required">*</span></label>
                <div class="input-wrapper">
                    <i class="fas fa-route icon-left"></i>
                    <select name="route_id" required>
                        <?php 
                        $routes->data_seek(0);
                        while($r=$routes->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $r['route_id']; ?>" <?php if($r['route_id']==$schedule['route_id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($r['source'].' → '.$r['destination']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Departure Time <span class="required">*</span></label>
                <div class="input-wrapper">
                    <i class="fas fa-clock icon-left"></i>
                    <input type="datetime-local" name="departure_time" value="<?php echo date('Y-m-d\TH:i', strtotime($schedule['departure_time'])); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Arrival Time <span class="required">*</span></label>
                <div class="input-wrapper">
                    <i class="fas fa-flag-checkered icon-left"></i>
                    <input type="datetime-local" name="arrival_time" value="<?php echo date('Y-m-d\TH:i', strtotime($schedule['arrival_time'])); ?>" required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Fare (NPR) <span class="required">*</span></label>
            <div class="input-wrapper">
                <i class="fas fa-money-bill-wave icon-left"></i>
                <input type="number" step="0.01" name="fare" value="<?php echo $schedule['fare'] ?? 0; ?>" required min="0" placeholder="Enter fare amount">
            </div>
        </div>

        <div class="actions">
            <button type="submit" name="update" class="btn btn-update">
                <i class="fas fa-save"></i> Update Schedule
            </button>
            <a href="admin_manage_schedules.php" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </form>
</div>

</body>
</html>