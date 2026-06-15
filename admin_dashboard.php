<?php
session_start();
if(!isset($_SESSION['username']) || $_SESSION['role'] != 'admin'){
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
header h1 { margin: 0; font-size: 24px; }
header nav a {
    color: white;
    text-decoration: none;
    margin-left: 20px;
    font-weight: bold;
}
header nav a:hover { text-decoration: underline; }
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
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.card h3 { margin-top: 0; color: #007bff; }
.card p { color: #555; }
button {
    padding: 10px 15px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
button:hover { background-color: #0056b3; }
.logout {
    margin-top: 20px;
    display: inline-block;
}
@media(max-width:768px) {
    .dashboard-grid { flex-direction: column; }
}
</style>
</head>
<body>

<header>
    <h1>BusGo Admin</h1>
    <nav>
        <a href="#">Dashboard</a>
        <a href="#">Manage Buses</a>
        <a href="#">Manage Routes</a>
        <a href="#">Manage Schedules</a>
        <a href="#">View Bookings</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <div class="welcome">Welcome Admin, John Doe!</div>

    <div class="dashboard-grid">
        <div class="card">
            <h3>Manage Buses</h3>
            <p>Add, update or remove buses from the system.</p>
            <button>Go</button>
        </div>
        <div class="card">
            <h3>Manage Routes</h3>
            <p>Create and edit bus routes.</p>
            <button>Go</button>
        </div>
        <div class="card">
            <h3>Manage Schedules</h3>
            <p>Set and update bus schedules.</p>
            <button>Go</button>
        </div>
        <div class="card">
            <h3>View Bookings</h3>
            <p>See all user bookings and ticket details.</p>
            <button>Go</button>
        </div>
        <div class="card">
            <h3>Account Settings</h3>
            <p>Update admin account information.</p>
            <button>Edit</button>
        </div>
    </div>

    <a class="logout" href="logout.php"><button>Logout</button></a>
</div>

</body>
</html>