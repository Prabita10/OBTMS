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

// Fetch all buses
$sql = "SELECT * FROM buses";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Buses - OBTMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f9fc;
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
            padding: 20px;
            max-width: 1200px;
            margin: auto;
        }

        h2 {
            color: #1a2b4c;
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: left;
        }

        th {
            background: #1a2b4c;
            color: white;
        }

        a.button {
            padding: 8px 12px;
            background: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }

        a.button:hover {
            background: #0056b3;
        }

        .add-btn {
            margin-bottom: 15px;
            display: inline-block;
        }
    </style>
</head>

<body>

    <header>
        <h1>BusGo</h1>
        <nav>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        <h2>Manage Buses</h2>

        <a href="add_bus.php" class="button add-btn">Add New Bus</a>

        <table>
            <tr>
                <th>ID</th>
                <th>Bus Name</th>
                <th>Type</th>
                <th>Total Seats</th>
                <th>Fare</th>
                <th>Actions</th>
            </tr>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['bus_id']; ?></td>
                        <td><?php echo $row['bus_name']; ?></td>
                        <td><?php echo $row['type']; ?></td>
                        <td><?php echo $row['total_seats']; ?></td>
                        <td><?php echo $row['fare']; ?></td>
                        <td>
                            <a href="edit_bus.php?id=<?php echo $row['bus_id']; ?>" class="button">Edit</a>
                            <a href="delete_bus.php?id=<?php echo $row['bus_id']; ?>" class="button"
                                onclick="return confirm('Are you sure you want to delete this bus?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center;">No buses found.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

</body>

</html>