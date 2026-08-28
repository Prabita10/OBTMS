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

$id = $_GET['id'];
$msg = "";
$msgType = "";

if(isset($_POST['update'])){
    $bus_number = mysqli_real_escape_string($conn, $_POST['bus_number']);
    $bus_name = mysqli_real_escape_string($conn, $_POST['bus_name']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $total_seats = (int)$_POST['total_seats'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Check if bus_number already exists (excluding current bus)
    $check = $conn->query("SELECT bus_id FROM buses WHERE bus_number = '$bus_number' AND bus_id != $id");
    if($check->num_rows > 0){
        $msg = "Bus number already exists!";
        $msgType = "error";
    } else {
        $sql = "UPDATE buses SET 
                bus_number='$bus_number',
                bus_name='$bus_name', 
                type='$type', 
                total_seats='$total_seats',
                status='$status' 
                WHERE bus_id=$id";
        if($conn->query($sql)===TRUE){ 
            $msg = "Bus updated successfully!";
            $msgType = "success";
        } else { 
            $msg = "Error: ".$conn->error;
            $msgType = "error";
        }
    }
}

// Fetch bus data
$sql = "SELECT * FROM buses WHERE bus_id=$id";
$result = $conn->query($sql);
$bus = $result->fetch_assoc();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Bus - BusGo Admin</title>
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
    max-width: 700px;
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

/* Bus ID Badge */
.bus-id-badge {
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

.bus-id-badge i {
    margin-right: 6px;
    color: #66b0ff;
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
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 12px 15px 12px 45px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 15px;
    transition: 0.3s;
    background: #fafcff;
    color: #1a2b4c;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #1a2b4c;
    box-shadow: 0 0 0 4px rgba(26, 43, 76, 0.08);
    background: #fff;
}

.form-group input:hover,
.form-group select:hover {
    border-color: #66b0ff;
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
    .bus-id-badge {
        margin-left: 0;
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

    .form-group input,
    .form-group select {
        padding: 10px 15px 10px 40px;
        font-size: 14px;
    }

    .form-group .icon-left {
        left: 12px;
        font-size: 13px;
    }
}

/* Status badge styles in select */
select option.status-active { color: #28a745; }
select option.status-maintenance { color: #ffc107; }
select option.status-inactive { color: #dc3545; }
</style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="header">
        <div class="icon">
            <i class="fas fa-bus"></i>
        </div>
        <h2>Edit <span>Bus</span></h2>
    </div>
    <div class="subtitle">Update bus details and manage your fleet</div>
    <div class="bus-id-badge">
        <i class="fas fa-tag"></i> Bus ID: #<?php echo $id; ?>
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
                <label>Bus Number <span class="required">*</span></label>
                <div class="input-wrapper">
                    <i class="fas fa-hashtag icon-left"></i>
                    <input type="text" name="bus_number" value="<?php echo htmlspecialchars($bus['bus_number']); ?>" placeholder="e.g., BUS-001" required>
                </div>
            </div>

            <div class="form-group">
                <label>Bus Name <span class="required">*</span></label>
                <div class="input-wrapper">
                    <i class="fas fa-bus icon-left"></i>
                    <input type="text" name="bus_name" value="<?php echo htmlspecialchars($bus['bus_name']); ?>" placeholder="Enter bus name" required>
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Type <span class="required">*</span></label>
                <div class="input-wrapper">
                    <i class="fas fa-chair icon-left"></i>
                    <select name="type" required>
                        <option value="AC" <?php echo ($bus['type'] == 'AC') ? 'selected' : ''; ?>>AC</option>
                        <option value="Non-AC" <?php echo ($bus['type'] == 'Non-AC') ? 'selected' : ''; ?>>Non-AC</option>
                        <option value="Deluxe" <?php echo ($bus['type'] == 'Deluxe') ? 'selected' : ''; ?>>Deluxe</option>
                        <option value="Sleeper" <?php echo ($bus['type'] == 'Sleeper') ? 'selected' : ''; ?>>Sleeper</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Total Seats <span class="required">*</span></label>
                <div class="input-wrapper">
                    <i class="fas fa-users icon-left"></i>
                    <input type="number" name="total_seats" value="<?php echo $bus['total_seats']; ?>" placeholder="Enter total seats" required min="1">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Status</label>
            <div class="input-wrapper">
                <i class="fas fa-circle icon-left"></i>
                <select name="status">
                    <option value="active" <?php echo ($bus['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="maintenance" <?php echo ($bus['status'] == 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                    <option value="inactive" <?php echo ($bus['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
        </div>

        <div class="actions">
            <button type="submit" name="update" class="btn btn-update">
                <i class="fas fa-save"></i> Update Bus
            </button>
            <a href="admin_manage_buses.php" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </form>
</div>

</body>
</html>