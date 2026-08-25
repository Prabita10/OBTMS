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

$msg = "";
$msgType = "";

// Fetch buses and routes for dropdown
$buses = $conn->query("SELECT bus_id, bus_number, bus_name FROM buses WHERE status = 'active'");
$routes = $conn->query("SELECT route_id, source, destination FROM routes");

if(isset($_POST['submit'])){
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
        $sql = "INSERT INTO schedules (bus_id, route_id, departure_time, arrival_time, fare) 
                VALUES ($bus_id, $route_id, '$departure_time', '$arrival_time', '$fare')";
        if($conn->query($sql)===TRUE){ 
            $msg = "Schedule added successfully!";
            $msgType = "success";
        } else { 
            $msg = "Error: ".$conn->error;
            $msgType = "error";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add New Schedule</title>
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

.container{max-width:600px;margin:40px auto;padding:0 20px}

.card{background:#fff;border-radius:14px;padding:35px;box-shadow:0 4px 20px rgba(0,0,0,0.06);border:1px solid #e8ecf3}
.card-header{text-align:center;margin-bottom:25px}
.card-header h2{color:#1a2b4c;font-size:24px}
.card-header h2 i{color:#66b0ff;margin-right:10px}
.card-header p{color:#6b7a8f;font-size:14px;margin-top:5px}

.msg{text-align:center;font-weight:500;margin-bottom:15px;padding:10px;border-radius:8px}
.msg.success{color:#155724;background:#d4edda;border:1px solid #c3e6cb}
.msg.error{color:#721c24;background:#f8d7da;border:1px solid #f5c6cb}

.form-group{margin-bottom:18px}
.form-group label{display:block;font-size:13px;font-weight:600;color:#1a2b4c;margin-bottom:5px}
.form-group label i{color:#66b0ff;margin-right:6px;width:18px}
.form-group label .required{color:#dc3545}

.input-wrapper{position:relative}
.input-wrapper .icon-left{position:absolute;left:15px;top:50%;transform:translateY(-50%);color:#6b7a8f;font-size:15px;pointer-events:none}

.form-group select,
.form-group input{
    width:100%;
    padding:12px 15px 12px 45px;
    border:2px solid #e2e8f0;
    border-radius:8px;
    font-size:14px;
    transition:0.3s;
    background:#fafcff;
    color:#1a2b4c;
    appearance:none;
    -webkit-appearance:none;
}

.form-group select{
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7a8f' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat:no-repeat;
    background-position:right 15px center;
    padding-right:40px;
}

.form-group select:focus,
.form-group input:focus{
    outline:none;
    border-color:#1a2b4c;
    box-shadow:0 0 0 4px rgba(26,43,76,0.08);
    background:#fff;
}
.form-group select:hover,
.form-group input:hover{border-color:#66b0ff}

.hint{font-size:11px;color:#6b7a8f;margin-top:4px}
.hint i{margin-right:4px}

.btn-submit{width:100%;padding:12px;background:#1a2b4c;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;transition:0.3s;display:flex;align-items:center;justify-content:center;gap:10px;margin-top:5px}
.btn-submit:hover{background:#2a4a7a;transform:scale(1.01);box-shadow:0 4px 12px rgba(26,43,76,0.3)}

.back-link{display:block;text-align:center;margin-top:18px;color:#6b7a8f;text-decoration:none;font-size:14px}
.back-link:hover{color:#1a2b4c}
.back-link i{margin-right:6px}

@media(max-width:600px){
    header{padding:12px 20px;flex-wrap:wrap}
    header h1{font-size:18px}
    header nav a{margin-left:8px;padding:5px 10px;font-size:12px}
    .card{padding:25px}
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
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-calendar-plus"></i> Add New Schedule</h2>
            <p>Create a new bus schedule</p>
        </div>

        <?php if($msg!=""): ?>
            <div class="msg <?php echo $msgType; ?>">
                <i class="fas <?php echo $msgType == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label><i class="fas fa-bus"></i> Bus <span class="required">*</span></label>
                <div class="input-wrapper">
                    <i class="fas fa-bus icon-left"></i>
                    <select name="bus_id" required>
                        <option value="">Select a bus...</option>
                        <?php while($b = $buses->fetch_assoc()): ?>
                            <option value="<?php echo $b['bus_id']; ?>">
                                <?php echo $b['bus_number'] . ' - ' . $b['bus_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-route"></i> Route <span class="required">*</span></label>
                <div class="input-wrapper">
                    <i class="fas fa-route icon-left"></i>
                    <select name="route_id" required>
                        <option value="">Select a route...</option>
                        <?php while($r = $routes->fetch_assoc()): ?>
                            <option value="<?php echo $r['route_id']; ?>">
                                <?php echo $r['source'] . ' → ' . $r['destination']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-clock"></i> Departure Time <span class="required">*</span></label>
                <div class="input-wrapper">
                    <i class="fas fa-clock icon-left"></i>
                    <input type="datetime-local" name="departure_time" required>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-flag-checkered"></i> Arrival Time <span class="required">*</span></label>
                <div class="input-wrapper">
                    <i class="fas fa-flag-checkered icon-left"></i>
                    <input type="datetime-local" name="arrival_time" required>
                </div>
                <div class="hint"><i class="fas fa-info-circle"></i> Must be after departure time</div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-money-bill-wave"></i> Fare (NPR) <span class="required">*</span></label>
                <div class="input-wrapper">
                    <i class="fas fa-money-bill-wave icon-left"></i>
                    <input type="number" step="0.01" name="fare" placeholder="Enter fare amount" min="0" required>
                </div>
            </div>

            <button type="submit" name="submit" class="btn-submit">
                <i class="fas fa-plus-circle"></i> Add Schedule
            </button>
        </form>

        <a href="admin_manage_schedules.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Schedules
        </a>
    </div>
</div>

</body>
</html>