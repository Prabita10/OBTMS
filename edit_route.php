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
$conn=new mysqli($servername,$usernameDB,$passwordDB,$dbname);
if($conn->connect_error){ die("Connection failed: ".$conn->connect_error); }

$id = $_GET['id'];
$msg = "";
$msgType = "";

if(isset($_POST['update'])){
    $source = mysqli_real_escape_string($conn, $_POST['source']);
    $destination = mysqli_real_escape_string($conn, $_POST['destination']);
    $stops = mysqli_real_escape_string($conn, $_POST['stops']);

    $sql="UPDATE routes SET source='$source', destination='$destination', stops='$stops' WHERE route_id=$id";
    if($conn->query($sql)===TRUE){
        $msg = "Route updated successfully!";
        $msgType = "success";
    } else { 
        $msg = "Error: ".$conn->error;
        $msgType = "error";
    }
}

// Fetch route data
$sql="SELECT * FROM routes WHERE route_id=$id";
$result=$conn->query($sql);
$route=$result->fetch_assoc();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Route - BusGo Admin</title>
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
    max-width: 750px;
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

/* Route ID Badge */
.route-id-badge {
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

.route-id-badge i {
    margin-right: 6px;
    color: #66b0ff;
}

/* Route Preview */
.route-preview {
    background: linear-gradient(135deg, #f8faff, #e8f0fe);
    padding: 15px 20px;
    border-radius: 10px;
    margin-left: 65px;
    margin-bottom: 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border: 1px solid #dce4f0;
}

.route-preview .route-point {
    text-align: center;
    font-weight: 600;
    color: #1a2b4c;
    font-size: 15px;
}

.route-preview .route-point small {
    display: block;
    font-weight: 400;
    color: #6b7a8f;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.route-preview .route-arrow {
    color: #66b0ff;
    font-size: 20px;
    flex: 1;
    text-align: center;
    position: relative;
}

.route-preview .route-arrow::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 15%;
    right: 15%;
    height: 2px;
    background: #dce4f0;
    z-index: 0;
}

.route-preview .route-arrow i {
    background: #f8faff;
    padding: 0 8px;
    position: relative;
    z-index: 1;
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
}

.input-wrapper input,
.input-wrapper textarea {
    width: 100%;
    padding: 12px 15px 12px 45px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 15px;
    transition: 0.3s;
    background: #fafcff;
    color: #1a2b4c;
}

.input-wrapper textarea {
    padding: 12px 15px 12px 45px;
    resize: vertical;
    min-height: 70px;
    font-family: 'Roboto', sans-serif;
}

.input-wrapper input:focus,
.input-wrapper textarea:focus {
    outline: none;
    border-color: #1a2b4c;
    box-shadow: 0 0 0 4px rgba(26, 43, 76, 0.08);
    background: #fff;
}

.input-wrapper input:hover,
.input-wrapper textarea:hover {
    border-color: #66b0ff;
}

.input-wrapper input:focus + .icon-left,
.input-wrapper textarea:focus + .icon-left {
    color: #1a2b4c;
}

/* Two columns layout */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

/* Stops input with special styling */
.stops-wrapper .icon-left {
    top: 18px;
    transform: none;
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

/* Stops tag display */
.stops-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 8px;
}

.stops-tags .tag {
    background: #e8edf5;
    color: #1a2b4c;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 500;
}

.stops-tags .tag i {
    margin-right: 4px;
    color: #66b0ff;
    font-size: 10px;
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
    .route-id-badge,
    .route-preview {
        margin-left: 0;
    }

    .route-preview {
        flex-direction: column;
        gap: 8px;
        padding: 15px;
    }

    .route-preview .route-arrow {
        transform: rotate(90deg);
    }

    .route-preview .route-arrow::after {
        display: none;
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

    .input-wrapper input,
    .input-wrapper textarea {
        padding: 10px 15px 10px 40px;
        font-size: 14px;
    }

    .input-wrapper .icon-left {
        left: 12px;
        font-size: 13px;
    }

    .route-preview .route-point {
        font-size: 13px;
    }
}
</style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="header">
        <div class="icon">
            <i class="fas fa-route"></i>
        </div>
        <h2>Edit <span>Route</span></h2>
    </div>
    <div class="subtitle">Update route details and manage your destinations</div>
    <div class="route-id-badge">
        <i class="fas fa-tag"></i> Route ID: #<?php echo $id; ?>
    </div>

    <!-- Route Preview -->
    <div class="route-preview">
        <div class="route-point">
            <?php echo htmlspecialchars($route['source']); ?>
            <small>Source</small>
        </div>
        <div class="route-arrow">
            <i class="fas fa-arrow-right"></i>
        </div>
        <div class="route-point">
            <?php echo htmlspecialchars($route['destination']); ?>
            <small>Destination</small>
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
                <label>Source <span class="required">*</span></label>
                <div class="input-wrapper">
                    <i class="fas fa-map-marker-alt icon-left"></i>
                    <input type="text" name="source" value="<?php echo htmlspecialchars($route['source']); ?>" placeholder="Enter source city" required>
                </div>
            </div>

            <div class="form-group">
                <label>Destination <span class="required">*</span></label>
                <div class="input-wrapper">
                    <i class="fas fa-flag-checkered icon-left"></i>
                    <input type="text" name="destination" value="<?php echo htmlspecialchars($route['destination']); ?>" placeholder="Enter destination city" required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Stops <span class="hint">(comma separated)</span></label>
            <div class="input-wrapper stops-wrapper">
                <i class="fas fa-map-pin icon-left"></i>
                <textarea name="stops" placeholder="e.g. Chitwan, Butwal, Palpa"><?php echo htmlspecialchars($route['stops']); ?></textarea>
            </div>
            <?php if(!empty($route['stops'])): ?>
                <div class="stops-tags">
                    <?php 
                    $stopsArray = array_map('trim', explode(',', $route['stops']));
                    foreach($stopsArray as $stop): 
                    ?>
                        <span class="tag"><i class="fas fa-circle"></i> <?php echo htmlspecialchars($stop); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="actions">
            <button type="submit" name="update" class="btn btn-update">
                <i class="fas fa-save"></i> Update Route
            </button>
            <a href="admin_manage_routes.php" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </form>
</div>

</body>
</html>