<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header("Location: login.html");
    exit();
}

// Database connection for notifications
$conn = new mysqli("localhost","root","","obtms");
if($conn->connect_error) die("Connection failed: ".$conn->connect_error);

$username = $_SESSION['username'];

// Get unread notification count
$unreadResult = $conn->query("SELECT COUNT(*) as unread FROM notifications WHERE username = '$username' AND is_read = 0");
$unreadCount = $unreadResult->fetch_assoc()['unread'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - OBTMS</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f9fc;
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
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .welcome {
            font-size: 22px;
            margin-bottom: 20px;
        }

        .dashboard-grid {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            flex: 1;
            min-width: 250px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .card h3 {
            margin-top: 0;
            color: #007bff;
        }

        .card p {
            color: #555;
        }

        .card a.button {
            display: inline-block;
            padding: 10px 15px;
            background: #007bff;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 10px;
            text-align: center;
        }

        .card a.button:hover {
            background-color: #0056b3;
        }

        /* ============================================
           NOTIFICATION INDICATOR - RED DOT
           ============================================ */
        .notif-badge {
            position: absolute;
            top: 15px;
            right: 20px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 4px 8px;
            font-size: 12px;
            font-weight: 700;
            min-width: 22px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.4);
            animation: pulse 2s infinite;
            display: <?php echo $unreadCount > 0 ? 'block' : 'none'; ?>;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .notif-dot {
            position: absolute;
            top: 18px;
            right: 20px;
            width: 12px;
            height: 12px;
            background: #dc3545;
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.6);
            animation: pulse 2s infinite;
            display: <?php echo $unreadCount > 0 ? 'block' : 'none'; ?>;
        }

        /* ============================================
           NOTIFICATION BELL WITH BADGE (Optional - if you want)
           ============================================ */
        .bell-container {
            display: inline-block;
            position: relative;
            margin-left: 15px;
        }
        .bell-container .bell-icon {
            font-size: 20px;
            color: rgba(255,255,255,0.85);
            cursor: pointer;
        }
        .bell-container .bell-icon:hover {
            color: #fff;
        }
        .bell-container .bell-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 7px;
            font-size: 11px;
            font-weight: 700;
            min-width: 18px;
            text-align: center;
            display: <?php echo $unreadCount > 0 ? 'block' : 'none'; ?>;
        }

        .logout {
            margin-top: 20px;
            display: inline-block;
        }

        @media(max-width:768px) {
            .dashboard-grid {
                flex-direction: column;
            }
        }

        .welcome-box {
            background: #0056b3;
            color: #fff;
            padding: 28px 35px;
            padding-top: 1px;
            border-radius: 14px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            box-shadow: 0 4px 15px rgba(26, 43, 76, 0.25);
            width: 300px;
            height: 110px;
        }

        .welcome-box h2 {
            font-size: 24px;
            font-weight: 600;
        }

        .welcome-box h2 span {
            color: white;
        }

        .welcome-box p {
            opacity: 0.85;
            margin-top: 5px;
            font-size: 14px;
        }

        .welcome-box .user-badge {
            background: rgba(255, 255, 255, 0.15);
            padding: 6px 20px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        @media (max-width: 768px) {
            .welcome-box {
                flex-direction: column;
                text-align: center;
                gap: 10px;
                padding: 22px 20px;
            }

            .welcome-box h2 {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>

    <header>
        <h1>BusGo</h1>
        <nav>
            <!-- Optional: Bell icon with badge in header -->
            <span class="bell-container">
                <a href="notifications.php" style="color:white; text-decoration:none;">
                    <i class="fas fa-bell bell-icon"></i>
                    <span class="bell-badge"><?php echo $unreadCount > 0 ? $unreadCount : ''; ?></span>
                </a>
            </span>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        <!-- Welcome Box -->
        <div class="welcome-box">
            <div>
                <h2>Welcome back, <span><?php echo htmlspecialchars($_SESSION['username']); ?></span> 👋</h2>
                <p><i class="fas fa-calendar-alt"></i> <?php echo date('l, F j, Y'); ?> &nbsp;·&nbsp; <i
                        class="fas fa-clock"></i> <?php echo date('h:i A'); ?></p>
            </div>

            <div class="user-badge"><i class="fas fa-user-circle"></i> <?php echo ucfirst($_SESSION['role']); ?></div>
        </div>

        <div class="dashboard-grid">

            <div class="card">
                <h3>Search & Book</h3>
                <p>Find buses and book tickets for your next journey.</p>
                <a href="search_buses.php" class="button">Search Buses</a>
            </div>

            <div class="card">
                <h3>View Bookings</h3>
                <p>View all your upcoming bus trips.</p>
                <a href="my_bookings.php" class="button">View Bookings</a>
            </div>

            <div class="card">
                <h3>Account Settings</h3>
                <p>Update your personal information and preferences.</p>
                <a href="edit_account.php" class="button">Edit Account</a>
            </div>

            <!-- ============================================
            NOTIFICATION CARD WITH RED DOT
            ============================================ -->
            <div class="card">
                <!-- RED DOT INDICATOR -->
                <span class="notif-dot" id="notifDot"></span>

                <h3><i class="fas fa-bell"></i> Notifications</h3>
                <p>Check alerts for upcoming trips and booking updates.</p>
                <a href="notifications.php" class="button" id="notifBtn">
                    <i class="fas fa-arrow-right"></i> View Alerts
                </a>
            </div>

        </div>

        <a class="logout" href="logout.php"></a>
    </div>

    <script>
        // ============================================
        // CHECK FOR NEW NOTIFICATIONS EVERY 30 SECONDS
        // ============================================
        function checkNotifications() {
            fetch('notifications/get_notifications.php')
                .then(response => response.json())
                .then(data => {
                    const dot = document.getElementById('notifDot');
                    const badge = document.querySelector('.bell-badge');
                    
                    if (data.success && data.unread_count > 0) {
                        // Show red dot
                        dot.style.display = 'block';
                        if (badge) {
                            badge.textContent = data.unread_count;
                            badge.style.display = 'block';
                        }
                    } else {
                        // Hide red dot
                        dot.style.display = 'none';
                        if (badge) {
                            badge.style.display = 'none';
                        }
                    }
                })
                .catch(() => {
                    // If error, keep dot hidden
                });
        }

        // Check every 30 seconds
        setInterval(checkNotifications, 30000);

        // Check on page load
        document.addEventListener('DOMContentLoaded', checkNotifications);
    </script>

</body>

</html>