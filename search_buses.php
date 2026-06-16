<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
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

$results = [];
$from = $to = $date = "";
if (isset($_GET['search'])) {
    $from = $_GET['from'];
    $to = $_GET['to'];
    $date = $_GET['date']; // YYYY-MM-DD format

    $sql = "SELECT s.schedule_id, b.bus_name, b.type, b.fare, s.departure_time, s.arrival_time, s.available_seats, b.total_seats,
                   r.source, r.destination
            FROM schedules s
            JOIN buses b ON s.bus_id=b.bus_id
            JOIN routes r ON s.route_id=r.route_id
            WHERE r.source='$from' AND r.destination='$to'
              AND DATE(s.departure_time)='$date'
              AND s.available_seats>0
            ORDER BY s.departure_time ASC";

    $res = $conn->query($sql);
    if ($res->num_rows > 0) {
        $results = $res->fetch_all(MYSQLI_ASSOC);
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Search Buses - OBTMS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f4f7;
            margin: 0;
            padding: 0;
        }

        header {
            background: #1a2b4c;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
        }

        header nav a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            font-weight: bold;
        }

        header nav a:hover {
            text-decoration: underline;
        }

        .container {
            display: flex;
            max-width: 1200px;
            margin: 30px auto;
            gap: 20px;
            padding: 0 20px;
        }

        .search-card {
            width: 300px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .search-card h2 {
            text-align: center;
            color: #1a2b4c;
            margin-bottom: 15px;
        }

        .search-card input,
        .search-card select,
        .search-card button {
            width: 100%;
            margin: 8px 0;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .search-card button {
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        .search-card button:hover {
            background: #0056b3;
        }

        .results {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .bus-card {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .bus-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .bus-info span {
            font-weight: 500;
        }

        .bus-info span.title {
            color: #007bff;
            font-weight: bold;
        }

        .bus-card button {
            padding: 8px 12px;
            border: none;
            background: #28a745;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }

        .bus-card button:hover {
            background: #218838;
        }

        .no-results {
            text-align: center;
            color: #555;
            font-style: italic;
            margin-top: 20px;
        }

        @media(max-width:768px) {
            .container {
                flex-direction: column;
            }

            .search-card {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <header>
        <h1>BusGo</h1>
        <nav>
            <a href="user_dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        <!-- Search Form Left Column -->
        <div class="search-card">
            <h2>Search Buses</h2>
            <form method="GET">
                <input type="text" name="from" placeholder="From" value="<?php echo htmlspecialchars($from); ?>"
                    required>
                <input type="text" name="to" placeholder="To" value="<?php echo htmlspecialchars($to); ?>" required>
                <input type="date" name="date" value="<?php echo htmlspecialchars($date); ?>" required>
                <select name="passengers">
                    <option>1 Passenger</option>
                    <option>2 Passengers</option>
                    <option>3 Passengers</option>
                    <option>4 Passengers</option>
                </select>
                <button type="submit" name="search">Search</button>
            </form>
        </div>
        <!-- Results Right Column -->
        <div class="results">
            <?php if (count($results) > 0): ?>
                <?php foreach ($results as $row): ?>
                    <div class="bus-card">
                        <div class="bus-info">
                            <span class="title"><?php echo htmlspecialchars($row['bus_name']); ?>
                                (<?php echo $row['type']; ?>)</span>
                            <span>Route: <?php echo $row['source'] . " → " . $row['destination']; ?></span>
                            <span>Departure: <?php echo $row['departure_time']; ?> | Arrival:
                                <?php echo $row['arrival_time']; ?></span>
                            <span>Fare: NPR <?php echo $row['fare']; ?> | Available Seats:
                                <?php echo $row['available_seats']; ?> / <?php echo $row['total_seats']; ?></span>
                        </div>
                        <form method="GET" action="book_seat.php">
                            <input type="hidden" name="schedule_id" value="<?php echo $row['schedule_id']; ?>">
                            <button type="submit">Book</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php elseif (isset($_GET['search'])): ?>
                <div class="no-results">No buses found for this route/date.</div>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>