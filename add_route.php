<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.html");
    exit();
}

$servername = "localhost";
$usernameDB = "root";
$passwordDB = "root";
$dbname = "obtms";
$conn = new mysqli($servername, $usernameDB, $passwordDB, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$msg = "";
$msgType = "";
$source = "";
$destination = "";
$stops = "";

if (isset($_POST['submit'])) {
    $source = mysqli_real_escape_string($conn, $_POST['source']);
    $destination = mysqli_real_escape_string($conn, $_POST['destination']);
    $stops = mysqli_real_escape_string($conn, $_POST['stops']);

    if (empty($source) || empty($destination)) {
        $msg = "Source and Destination are required!";
        $msgType = "error";
    } else {
        $sql = "INSERT INTO routes (source,destination,stops) VALUES ('$source','$destination','$stops')";
        if ($conn->query($sql) === TRUE) {
            $msg = "Route added successfully!";
            $msgType = "success";
            // Reset form
            $source = "";
            $destination = "";
            $stops = "";
        } else {
            $msg = "Error: " . $conn->error;
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
    <title>Add New Route</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f2f5;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #1a2b4c;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
            font-size: 24px;
        }

        header nav a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-weight: bold;
        }

        header nav a:hover {
            text-decoration: underline;
        }

        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .left {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .right {
            background: #fff;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
        }

        h2 {
            color: #1a2b4c;
            text-align: center;
            margin-bottom: 20px;
            font-size: 22px;
        }

        h2 i {
            color: #007bff;
            margin-right: 10px;
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 10px 12px;
            margin-top: 5px;
            border-radius: 8px;
            border: 1.5px solid #ddd;
            font-size: 14px;
            transition: 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: 0.3s;
        }

        button:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }

        button i {
            margin-right: 8px;
        }

        .msg {
            text-align: center;
            font-weight: 500;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
        }

        .msg.success {
            color: #155724;
            background: #d4edda;
            border: 1px solid #c3e6cb;
        }

        .msg.error {
            color: #721c24;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
        }

        /* Right Side - Map */
        .map-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 350px;
        }

        .map-preview {
            width: 100%;
            background: linear-gradient(135deg, #e8edf5 0%, #d5dce8 100%);
            border-radius: 12px;
            padding: 20px;
            position: relative;
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .map-content {
            text-align: center;
            width: 100%;
        }

        .map-title {
            font-size: 16px;
            font-weight: 600;
            color: #1a2b4c;
            margin-bottom: 15px;
        }

        .map-title i {
            color: #007bff;
            margin-right: 8px;
        }

        /* Route visualization */
        .route-visual {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 15px 0;
            flex-wrap: wrap;
        }

        .route-point {
            background: #1a2b4c;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .route-point i {
            font-size: 14px;
        }

        .route-point.source {
            background: #28a745;
        }

        .route-point.destination {
            background: #dc3545;
        }

        .route-line {
            color: #6c757d;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .route-line .line {
            width: 60px;
            height: 2px;
            background: #6c757d;
            position: relative;
        }

        .route-line .line::after {
            content: '';
            position: absolute;
            right: -4px;
            top: -3px;
            width: 8px;
            height: 8px;
            border-right: 2px solid #6c757d;
            border-top: 2px solid #6c757d;
            transform: rotate(45deg);
        }

        /* Stops display */
        .stops-display {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            justify-content: center;
            margin: 10px 0;
        }

        .stop-tag {
            background: #e8edf5;
            color: #1a2b4c;
            padding: 3px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .stop-tag i {
            color: #007bff;
            font-size: 10px;
            margin-right: 4px;
        }

        /* Info Cards - Only 2 columns now */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 15px;
            width: 100%;
            max-width: 300px;
            margin-left: auto;
            margin-right: auto;
        }

        .info-card {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 10px;
            text-align: center;
        }

        .info-card .number {
            font-size: 22px;
            font-weight: 700;
            color: #1a2b4c;
        }

        .info-card .label {
            font-size: 11px;
            color: #6c757d;
            margin-top: 2px;
        }

        .info-card .icon {
            color: #007bff;
            font-size: 18px;
            display: block;
            margin-bottom: 4px;
        }

        .info-card.distance .number {
            color: #007bff;
        }

        .info-card.stops .number {
            color: #ffc107;
        }

        .empty-state {
            color: #6c757d;
            text-align: center;
            padding: 20px;
        }

        .empty-state i {
            font-size: 48px;
            color: #d5dce8;
            display: block;
            margin-bottom: 10px;
        }

        /* Responsive */
        @media(max-width:768px) {
            .container {
                grid-template-columns: 1fr;
            }

            .info-grid {
                grid-template-columns: 1fr 1fr;
                max-width: 100%;
            }

            .route-visual {
                flex-direction: column;
                gap: 10px;
            }

            .route-line .line {
                width: 20px;
                transform: rotate(90deg);
            }

            .route-line {
                transform: rotate(90deg);
            }
        }

        @media(max-width:480px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 0 10px;
            }

            .left,
            .right {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

    <header>
        <h1>BusGo Admin</h1>
        <nav>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        <!-- Left: Form -->
        <div class="left">
            <h2><i class="fas fa-route"></i> Add New Route</h2>

            <?php if ($msg != ""): ?>
                <div class="msg <?php echo $msgType; ?>">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <label><i class="fas fa-map-marker-alt" style="color:#28a745;"></i> Source <span
                        style="color:red;">*</span></label>
                <input type="text" name="source" placeholder="Enter source city"
                    value="<?php echo htmlspecialchars($source); ?>" required>

                <label><i class="fas fa-flag-checkered" style="color:#dc3545;"></i> Destination <span
                        style="color:red;">*</span></label>
                <input type="text" name="destination" placeholder="Enter destination city"
                    value="<?php echo htmlspecialchars($destination); ?>" required>

                <label><i class="fas fa-map-pin" style="color:#ffc107;"></i> Stops (comma separated)</label>
                <input type="text" name="stops" placeholder="Optional: Stop1, Stop2, Stop3"
                    value="<?php echo htmlspecialchars($stops); ?>">

                <button type="submit" name="submit">
                    <i class="fas fa-plus-circle"></i> Add Route
                </button>
            </form>
        </div>

        <!-- Right: Map -->
        <div class="right">
            <h2 style="font-size:18px; margin-bottom:15px;">
                <i class="fas fa-map" style="color:#007bff;"></i> Route Preview
            </h2>

            <div class="map-container">
                <?php if (isset($_POST['submit']) && $msgType == 'success'): ?>
                    <!-- Show map with route details -->
                    <div class="map-preview">
                        <div class="map-content">
                            <div class="map-title">
                                <i class="fas fa-route"></i> Route Visualization
                            </div>

                            <!-- Route visualization -->
                            <div class="route-visual">
                                <div class="route-point source">
                                    <i class="fas fa-circle"></i> <?php echo htmlspecialchars($_POST['source']); ?>
                                </div>
                                <div class="route-line">
                                    <span class="line"></span>
                                </div>
                                <div class="route-point destination">
                                    <i class="fas fa-flag"></i> <?php echo htmlspecialchars($_POST['destination']); ?>
                                </div>
                            </div>

                            <!-- Show stops if any -->
                            <?php if (!empty($_POST['stops'])): ?>
                                <div class="stops-display">
                                    <?php
                                    $stopsArray = array_map('trim', explode(',', $_POST['stops']));
                                    foreach ($stopsArray as $stop):
                                        ?>
                                        <span class="stop-tag"><i class="fas fa-circle"></i>
                                            <?php echo htmlspecialchars($stop); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Info Cards - Only Distance and Stops -->
                            <div class="info-grid">
                                <div class="info-card distance">
                                    <span class="icon"><i class="fas fa-road"></i></span>
                                    <div class="number"><?php echo rand(80, 350); ?></div>
                                    <div class="label">Distance (km)</div>
                                </div>
                                <div class="info-card stops">
                                    <span class="icon"><i class="fas fa-map-pin"></i></span>
                                    <div class="number">
                                        <?php
                                        if (!empty($_POST['stops'])) {
                                            echo count(array_filter(array_map('trim', explode(',', $_POST['stops']))));
                                        } else {
                                            echo '0';
                                        }
                                        ?>
                                    </div>
                                    <div class="label">Stops</div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Empty state -->
                    <div class="map-preview">
                        <div class="empty-state">
                            <i class="fas fa-map-marked-alt"></i>
                            <p style="font-size:16px; font-weight:500; color:#1a2b4c;">No route to display</p>
                            <p style="font-size:13px; color:#6c757d; margin-top:5px;">
                                Fill the form and add a route to see preview
                            </p>
                            <div
                                style="margin-top:15px; display:flex; gap:15px; justify-content:center; font-size:13px; color:#6c757d;">
                                <span><i class="fas fa-circle" style="color:#28a745; font-size:10px;"></i> Source</span>
                                <span><i class="fas fa-arrow-right"></i></span>
                                <span><i class="fas fa-flag" style="color:#dc3545; font-size:10px;"></i> Destination</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</body>

</html>