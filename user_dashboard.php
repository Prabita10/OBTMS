<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header("Location: login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - OBTMS</title>
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
            color: #fff;
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
        <div class="welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</div>

        <div class="dashboard-grid">
            <div class="card">
                <h3>View Bookings</h3>
                <p>View all your upcoming bus trips.</p>
                <a href="my_bookings.php" class="button">View Bookings</a>
            </div>
            <div class="card">
                <h3>Search & Book</h3>
                <p>Find buses and book tickets for your next journey.</p>
                <a href="search_buses.php" class="button">Search Buses</a>
            </div>
            <div class="card">
                <h3>Account Settings</h3>
                <p>Update your personal information and preferences.</p>
                <a href="edit_account.php" class="button">Edit Account</a>
            </div>
            <div class="card">
                <h3>Notifications</h3>
                <p>Check alerts for upcoming trips and booking updates.</p>
                <a href="#" class="button">View Alerts</a>
            </div>
        </div>

        <a class="logout" href="logout.php"></a>
    </div>

</body>

</html>