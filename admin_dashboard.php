<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - OBTMS</title>
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
            color: white;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 10px;
            text-align: center;
        }

        .card a.button:hover {
            background-color: #0056b3;
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
.welcome-section {
    background: white;
    border-radius: 12px;
    padding: 20px 10px;
    margin: 0 0 25px;  / Center with auto margins /
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    display: flex;
    justify-content:left;
    flex-wrap: wrap;
    gap: 15px;
    background-color: #007bff;
    padding-top:2px;
    width: 600px;

}

.welcome-text h2 {
    font-size: 20px;
    color: white;
}


.welcome-text p {
    color: white;
    font-size: 13px;
    margin-top: 3px;
}

.welcome-text p i {
    margin-right: 5px;
}

.welcome-actions {
    display: flex;
    gap: 10px;
}


.welcome-actions a:hover {
    background: #dee2e6;
    transform: translateY(-2px);
}

.welcome-actions .primary-btn {
    background: #007bff;
    color: white;
}

.welcome-actions .primary-btn:hover {
    background: #0056b3;
}

/ ===== RESPONSIVE ===== /
@media (max-width: 768px) {
    .welcome-section {
        flex-direction: column;
        text-align: center;
        padding: 18px 20px;
        max-width: 100%;      / Full width on mobile */
    }

    .welcome-actions {
        flex-wrap: wrap;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .welcome-text h2 {
        font-size: 18px;
    }
}
    </style>
</head>

<body>

    <header>
        <h1>BusGo</h1>
        <nav>

            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
<!-- Welcome Section - Smaller Width -->
<div class="welcome-section">
    <div class="welcome-text">
        <h2><i class="fas fa-user-shield"></i> Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
        <p><i class="fas fa-calendar-alt"></i> <?php echo date('l, F j, Y'); ?> &nbsp;|&nbsp; <i class="fas fa-clock"></i> <?php echo date('h:i A'); ?></p>
    </div>

</div>

        <div class="dashboard-grid">
            <div class="card">
                <h3>Manage Buses</h3>
                <p>Add, update or remove buses from the system.</p>
                <a href="admin_manage_buses.php" class="button">Go</a>
            </div>
            <div class="card">
                <h3>Manage Routes</h3>
                <p>Create and edit bus routes.</p>
                <a href="admin_manage_routes.php" class="button">Go</a>
            </div>
            <div class="card">
                <h3>Manage Schedules</h3>
                <p>Set and update bus schedules.</p>
                <a href="admin_manage_schedules.php" class="button">Go</a>
            </div>
            <div class="card">
                <h3>View Bookings</h3>
                <p>See all user bookings and ticket details.</p>
                <a href="admin_view_bookings.php" class="button">Go</a>
            </div>
            <div class="card">
                <h3>Account Settings</h3>
                <p>Update admin account information.</p>
                <a href="admin_edit_account.php" class="button">Edit</a>
            </div>
        </div>

        <a class="logout" href="logout.php"></a>
    </div>

</body>

</html>