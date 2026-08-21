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
if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

// Weather API function for current weather (fallback)
function getWeather($city, $apiKey)
{
    if (empty($city))
        return null;
    $city = urlencode($city);
    $url = "https://api.openweathermap.org/data/2.5/weather?q={$city}&appid={$apiKey}&units=metric";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['cod']) && $data['cod'] == 200) {
            return [
                'temp' => round($data['main']['temp']),
                'feels_like' => round($data['main']['feels_like']),
                'condition' => $data['weather'][0]['main'],
                'description' => $data['weather'][0]['description'],
                'icon' => $data['weather'][0]['icon'],
                'humidity' => $data['main']['humidity'],
                'wind_speed' => round($data['wind']['speed'] * 3.6),
                'city' => $data['name'],
                'date' => date('Y-m-d')
            ];
        }
    }
    return null;
}

// NEW: Get weather forecast for specific date
function getWeatherForecast($city, $date, $apiKey)
{
    if (empty($city))
        return null;
    $city = urlencode($city);

    // Get 5-day forecast
    $url = "https://api.openweathermap.org/data/2.5/forecast?q={$city}&appid={$apiKey}&units=metric&cnt=40";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['cod']) && $data['cod'] == 200) {
            // Find forecast for the specific date
            $selectedDate = date('Y-m-d', strtotime($date));

            foreach ($data['list'] as $forecast) {
                $forecastDate = date('Y-m-d', strtotime($forecast['dt_txt']));
                // Get the forecast closest to noon (12:00) for that date
                if ($forecastDate == $selectedDate && date('H', strtotime($forecast['dt_txt'])) >= 12) {
                    return [
                        'temp' => round($forecast['main']['temp']),
                        'feels_like' => round($forecast['main']['feels_like']),
                        'condition' => $forecast['weather'][0]['main'],
                        'description' => $forecast['weather'][0]['description'],
                        'icon' => $forecast['weather'][0]['icon'],
                        'humidity' => $forecast['main']['humidity'],
                        'wind_speed' => round($forecast['wind']['speed'] * 3.6),
                        'city' => $city,
                        'date' => $selectedDate
                    ];
                }
            }

            // If no forecast found for that date, try to get current weather
            return getWeather($city, $apiKey);
        }
    }
    return null;
}

function getWeatherIcon($condition)
{
    $icons = [
        'Clear' => 'sun',
        'Clouds' => 'cloud',
        'Rain' => 'cloud-rain',
        'Drizzle' => 'cloud-rain',
        'Thunderstorm' => 'bolt',
        'Snow' => 'snowflake',
        'Mist' => 'smog',
        'Smoke' => 'smog',
        'Haze' => 'smog',
        'Dust' => 'smog',
        'Fog' => 'smog'
    ];
    return $icons[$condition] ?? 'cloud-sun';
}

$results = [];
$from = $to = $date = "";
$fromWeather = null;
$toWeather = null;
$apiKey = "8b4de5cb6bb84984b5d3c0160dea5b38"; // Get from https://openweathermap.org/api

if (isset($_GET['search'])) {
    $from = $_GET['from'];
    $to = $_GET['to'];
    $date = $_GET['date'];

    // SQL query
    $sql = "SELECT s.schedule_id, b.bus_name, b.type, b.fare, s.departure_time, s.arrival_time, s.available_seats, b.total_seats,
                   r.source, r.destination
            FROM schedules s
            JOIN buses b ON s.bus_id=b.bus_id
            JOIN routes r ON s.route_id=r.route_id
            WHERE r.source='$from' AND r.destination='$to'
              AND DATE(s.arrival_time)='$date'
              AND s.available_seats>0
            ORDER BY s.departure_time ASC";

    $res = $conn->query($sql);
    if ($res->num_rows > 0) {
        $results = $res->fetch_all(MYSQLI_ASSOC);
    }

    // Get weather forecast for the selected date
    $fromWeather = getWeatherForecast($from, $date, $apiKey);
    $toWeather = getWeatherForecast($to, $date, $apiKey);
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Buses - BusGo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f4fb
        }

        header {
            background: #1a2b4c;
            color: #fff;
            padding: 15px 35px;
            display: flex;
            justify-content: space-between;
            align-items: center
        }

        header h1 {
            font-size: 22px
        }

        header h1 span {
            color: #66b0ff
        }

        header nav a {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            margin-left: 18px;
            font-size: 14px;
            padding: 6px 14px;
            border-radius: 5px;
            transition: 0.2s
        }

        header nav a:hover {
            background: rgba(255, 255, 255, 0.1)
        }

        header nav a.dashboard {
            background: rgba(255, 255, 255, 0.08)
        }

        header nav a.logout {
            background: #dc3545;
            color: #fff
        }

        header nav a.logout:hover {
            background: #c82333
        }

        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
            display: flex;
            gap: 30px
        }

        /* Search Card */
        .search-card {
            width: 320px;
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
            height: fit-content;
            border: 1px solid #e8ecf3;
            flex-shrink: 0
        }

        .search-card h2 {
            color: #1a2b4c;
            font-size: 20px;
            margin-bottom: 5px
        }

        .search-card h2 i {
            color: #66b0ff;
            margin-right: 8px
        }

        .search-card .sub {
            color: #6b7a8f;
            font-size: 13px;
            margin-bottom: 18px
        }

        .search-card input,
        .search-card select {
            width: 100%;
            padding: 11px 14px;
            margin: 6px 0;
            border-radius: 8px;
            border: 1.5px solid #e2e8f0;
            font-size: 14px;
            transition: 0.3s;
            background: #fafcff
        }

        .search-card input:focus,
        .search-card select:focus {
            outline: none;
            border-color: #1a2b4c;
            box-shadow: 0 0 0 4px rgba(26, 43, 76, 0.08)
        }

        .search-card button {
            width: 100%;
            padding: 12px;
            background: #1a2b4c;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 8px
        }

        .search-card button:hover {
            background: #2a4a7a;
            transform: scale(1.01)
        }

        .search-card button i {
            margin-right: 8px
        }

        /* Results */
        .results {
            flex: 1
        }

        .results-header {
            background: #fff;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #e8ecf3
        }

        .results-header h3 {
            color: #1a2b4c;
            font-size: 17px
        }

        .results-header h3 i {
            color: #66b0ff;
            margin-right: 8px
        }

        .results-header .count {
            background: #e8edf5;
            padding: 4px 14px;
            border-radius: 15px;
            font-size: 13px;
            color: #1a2b4c
        }

        .bus-card {
            background: #fff;
            padding: 18px 22px;
            border-radius: 10px;
            border: 1px solid #e8ecf3;
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: 0.3s;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03)
        }

        .bus-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.07)
        }

        .bus-info {
            flex: 1
        }

        .bus-name {
            font-size: 18px;
            font-weight: 700;
            color: #1a2b4c
        }

        .bus-name i {
            color: #66b0ff;
            margin-right: 8px
        }

        .bus-type {
            background: #e8edf5;
            padding: 2px 12px;
            border-radius: 12px;
            font-size: 12px;
            color: #1a2b4c;
            margin-left: 10px
        }

        .bus-details {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            margin-top: 8px;
            font-size: 14px;
            color: #555
        }

        .bus-details span {
            display: flex;
            align-items: center;
            gap: 5px
        }

        .bus-details i {
            color: #66b0ff;
            width: 16px
        }

        .fare-seats {
            display: flex;
            gap: 20px;
            margin-top: 6px;
            font-size: 15px
        }

        .fare {
            color: #28a745;
            font-weight: 700;
            font-size: 17px
        }

        .seats {
            color: #555
        }

        .seats i {
            color: #28a745
        }

        .book-btn {
            padding: 10px 28px;
            background: #28a745;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 8px
        }

        .book-btn:hover {
            background: #218838;
            transform: scale(1.03)
        }

        .book-btn i {
            font-size: 13px
        }

        .weather-tag {
            font-size: 12px;
            color: #6b7a8f;
            margin-top: 5px;
            display: flex;
            gap: 15px
        }

        .weather-tag i {
            color: #66b0ff
        }

        .no-results {
            background: #fff;
            padding: 50px 20px;
            border-radius: 10px;
            text-align: center;
            border: 1px solid #e8ecf3
        }

        .no-results i {
            font-size: 50px;
            color: #cbd5e1;
            margin-bottom: 15px
        }

        .no-results h3 {
            color: #1a2b4c;
            font-size: 20px
        }

        .no-results p {
            color: #6b7a8f;
            margin-top: 5px
        }

        /* Weather Sidebar */
        .weather-sidebar {
            width: 320px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            flex-shrink: 0
        }

        .weather-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e8ecf3;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05)
        }

        .weather-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px
        }

        .weather-header h3 {
            color: #1a2b4c;
            font-size: 15px;
            margin: 0
        }

        .weather-header h3 i {
            color: #66b0ff;
            margin-right: 8px
        }

        .weather-city {
            font-weight: 600;
            color: #1a2b4c;
            font-size: 14px
        }

        .weather-main {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 8px 0
        }

        .weather-temp {
            font-size: 32px;
            font-weight: 700;
            color: #1a2b4c
        }

        .weather-temp span {
            font-size: 18px;
            color: #6b7a8f
        }

        .weather-icon {
            width: 60px;
            height: 60px
        }

        .weather-condition {
            font-size: 14px;
            color: #555;
            text-transform: capitalize;
            margin: 2px 0
        }

        .weather-condition i {
            color: #66b0ff;
            width: 20px
        }

        .weather-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #f0f4fb
        }

        .weather-detail-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #555
        }

        .weather-detail-item i {
            color: #66b0ff;
            width: 16px
        }

        .weather-travel-tip {
            background: #f8faff;
            padding: 12px 15px;
            border-radius: 10px;
            margin-top: 12px;
            border-left: 3px solid #66b0ff;
            font-size: 13px;
            color: #555
        }

        .weather-travel-tip i {
            color: #66b0ff;
            margin-right: 8px
        }

        .weather-loading {
            text-align: center;
            padding: 20px;
            color: #6b7a8f
        }

        .weather-loading i {
            font-size: 30px;
            margin-bottom: 10px;
            color: #66b0ff
        }

        .weather-route-info {
            background: linear-gradient(135deg, #f8faff, #e8f0fe);
            padding: 12px 15px;
            border-radius: 10px;
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center
        }

        .weather-route-info .route-arrow {
            color: #66b0ff;
            font-size: 20px
        }

        .weather-route-info .route-city {
            text-align: center
        }

        .weather-route-info .route-city small {
            display: block;
            font-size: 11px;
            color: #6b7a8f
        }

        @media(max-width:1024px) {
            .weather-sidebar {
                width: 280px
            }
        }

        @media(max-width:768px) {
            header {
                padding: 12px 20px;
                flex-wrap: wrap
            }

            header h1 {
                font-size: 18px
            }

            header nav a {
                margin-left: 8px;
                padding: 5px 10px;
                font-size: 12px
            }

            .container {
                flex-direction: column;
                gap: 20px
            }

            .search-card {
                width: 100%
            }

            .bus-card {
                flex-direction: column;
                gap: 12px;
                align-items: stretch
            }

            .book-btn {
                justify-content: center
            }

            .weather-sidebar {
                width: 100%;
                flex-direction: row;
                flex-wrap: wrap
            }

            .weather-card {
                flex: 1;
                min-width: 250px
            }
        }
    </style>
</head>

<body>
    <header>
        <h1>Bus<span>Go</span></h1>
        <nav>
            <a href="user_dashboard.php" class="dashboard"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </header>

    <div class="container">
        <!-- Search Form -->
        <div class="search-card">
            <h2><i class="fas fa-search"></i> Search Buses</h2>
            <p class="sub">Find the best buses for your journey</p>
            <form method="GET">
                <input type="text" name="from" placeholder="📍 From" value="<?php echo htmlspecialchars($from); ?>"
                    required>
                <input type="text" name="to" placeholder="📍 To" value="<?php echo htmlspecialchars($to); ?>" required>
                <input type="date" name="date" value="<?php echo htmlspecialchars($date); ?>" required>
                <select name="passengers">
                    <option>1 Passenger</option>
                    <option>2 Passengers</option>
                    <option>3 Passengers</option>
                    <option>4 Passengers</option>
                </select>
                <button type="submit" name="search"><i class="fas fa-search"></i> Search Buses</button>
            </form>
        </div>

        <!-- Results -->
        <div class="results">
            <?php if (count($results) > 0): ?>
                <div class="results-header">
                    <h3><i class="fas fa-bus"></i> Available Buses</h3>
                    <span class="count"><i class="fas fa-chair"></i> <?php echo count($results); ?> buses found</span>
                </div>

                <?php foreach ($results as $row): ?>
                    <div class="bus-card">
                        <div class="bus-info">
                            <div>
                                <span class="bus-name"><i class="fas fa-bus"></i>
                                    <?php echo htmlspecialchars($row['bus_name']); ?></span>
                                <span class="bus-type"><?php echo htmlspecialchars($row['type']); ?></span>
                            </div>
                            <div class="bus-details">
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['source']); ?> →
                                    <?php echo htmlspecialchars($row['destination']); ?></span>
                                <span><i class="fas fa-clock"></i>
                                    <?php echo date('h:i A', strtotime($row['departure_time'])); ?></span>
                                <span><i class="fas fa-flag-checkered"></i>
                                    <?php echo date('h:i A', strtotime($row['arrival_time'])); ?></span>
                            </div>
                            <div class="fare-seats">
                                <span class="fare"><i class="fas fa-money-bill-wave"></i> NPR
                                    <?php echo number_format($row['fare']); ?></span>
                                <span class="seats"><i class="fas fa-chair"></i> <?php echo $row['available_seats']; ?> /
                                    <?php echo $row['total_seats']; ?> seats</span>
                            </div>
                            <?php if ($fromWeather && $toWeather): ?>
                                <div class="weather-tag">
                                    <span><i class="fas fa-cloud-sun"></i> Departure: <?php echo $fromWeather['temp']; ?>°C</span>
                                    <span><i class="fas fa-flag-checkered"></i> Arrival: <?php echo $toWeather['temp']; ?>°C</span>
                                    <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($date)); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <form method="GET" action="book_seat.php">
                            <input type="hidden" name="schedule_id" value="<?php echo $row['schedule_id']; ?>">
                            <button type="submit" class="book-btn"><i class="fas fa-ticket-alt"></i> Book</button>
                        </form>
                    </div>
                <?php endforeach; ?>

            <?php elseif (isset($_GET['search'])): ?>
                <div class="no-results">
                    <i class="fas fa-bus"></i>
                    <h3>No Buses Found</h3>
                    <p>No buses available for <?php echo htmlspecialchars($from); ?> → <?php echo htmlspecialchars($to); ?>
                        on <?php echo date('M d, Y', strtotime($date)); ?></p>
                    <p style="color:#999;font-size:13px;margin-top:8px"><i class="fas fa-info-circle"></i> Try different
                        dates or routes</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Weather Sidebar -->
        <div class="weather-sidebar">
            <!-- Origin Weather -->
            <div class="weather-card">
                <div class="weather-header">
                    <h3><i class="fas fa-map-pin"></i> Departure Weather</h3>
                    <span class="weather-city">📍 <?php echo htmlspecialchars($from ?: 'City'); ?></span>
                </div>

                <?php if ($fromWeather && isset($_GET['search'])): ?>
                    <div class="weather-main">
                        <div>
                            <div class="weather-temp"><?php echo $fromWeather['temp']; ?>°<span>C</span></div>
                            <div class="weather-condition">
                                <i class="fas fa-<?php echo getWeatherIcon($fromWeather['condition']); ?>"></i>
                                <?php echo $fromWeather['description']; ?>
                            </div>
                        </div>
                        <img class="weather-icon"
                            src="https://openweathermap.org/img/wn/<?php echo $fromWeather['icon']; ?>@2x.png"
                            alt="<?php echo $fromWeather['condition']; ?>">
                    </div>

                    <div class="weather-details">
                        <div class="weather-detail-item">
                            <i class="fas fa-thermometer-half"></i>
                            Feels like <?php echo $fromWeather['feels_like']; ?>°C
                        </div>
                        <div class="weather-detail-item">
                            <i class="fas fa-tint"></i>
                            Humidity <?php echo $fromWeather['humidity']; ?>%
                        </div>
                        <div class="weather-detail-item">
                            <i class="fas fa-wind"></i>
                            Wind <?php echo $fromWeather['wind_speed']; ?> km/h
                        </div>
                        <div class="weather-detail-item">
                            <i class="fas fa-calendar"></i>
                            <?php echo date('M d, Y', strtotime($date)); ?>
                        </div>
                    </div>

                    <?php if ($fromWeather['condition'] == 'Rain' || $fromWeather['condition'] == 'Drizzle'): ?>
                        <div class="weather-travel-tip" style="border-left-color:#4a90d9;">
                            <i class="fas fa-umbrella" style="color:#4a90d9;"></i>
                            <strong>Travel Tip:</strong> Carry an umbrella - rain expected!
                        </div>
                    <?php elseif ($fromWeather['temp'] > 30): ?>
                        <div class="weather-travel-tip" style="border-left-color:#ff6b6b;">
                            <i class="fas fa-water" style="color:#ff6b6b;"></i>
                            <strong>Travel Tip:</strong> Hot weather! Stay hydrated.
                        </div>
                    <?php elseif ($fromWeather['temp'] < 10): ?>
                        <div class="weather-travel-tip" style="border-left-color:#4a90d9;">
                            <i class="fas fa-mitten" style="color:#4a90d9;"></i>
                            <strong>Travel Tip:</strong> Cold weather! Carry warm clothes.
                        </div>
                    <?php else: ?>
                        <div class="weather-travel-tip" style="border-left-color:#28a745;">
                            <i class="fas fa-check-circle" style="color:#28a745;"></i>
                            <strong>Perfect!</strong> Great weather for travel.
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="weather-loading">
                        <i class="fas fa-cloud-sun"></i>
                        <p>Search a route to see weather</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Destination Weather -->
            <div class="weather-card">
                <div class="weather-header">
                    <h3><i class="fas fa-flag-checkered"></i> Arrival Weather</h3>
                    <span class="weather-city">📍 <?php echo htmlspecialchars($to ?: 'City'); ?></span>
                </div>

                <?php if ($toWeather && isset($_GET['search'])): ?>
                    <div class="weather-main">
                        <div>
                            <div class="weather-temp"><?php echo $toWeather['temp']; ?>°<span>C</span></div>
                            <div class="weather-condition">
                                <i class="fas fa-<?php echo getWeatherIcon($toWeather['condition']); ?>"></i>
                                <?php echo $toWeather['description']; ?>
                            </div>
                        </div>
                        <img class="weather-icon"
                            src="https://openweathermap.org/img/wn/<?php echo $toWeather['icon']; ?>@2x.png"
                            alt="<?php echo $toWeather['condition']; ?>">
                    </div>

                    <div class="weather-details">
                        <div class="weather-detail-item">
                            <i class="fas fa-thermometer-half"></i>
                            Feels like <?php echo $toWeather['feels_like']; ?>°C
                        </div>
                        <div class="weather-detail-item">
                            <i class="fas fa-tint"></i>
                            Humidity <?php echo $toWeather['humidity']; ?>%
                        </div>
                        <div class="weather-detail-item">
                            <i class="fas fa-wind"></i>
                            Wind <?php echo $toWeather['wind_speed']; ?> km/h
                        </div>
                        <div class="weather-detail-item">
                            <i class="fas fa-calendar"></i>
                            <?php echo date('M d, Y', strtotime($date)); ?>
                        </div>
                    </div>

                    <!-- Route Comparison -->
                    <?php if ($fromWeather): ?>
                        <div class="weather-route-info">
                            <div class="route-city">
                                <strong><?php echo htmlspecialchars($from); ?></strong>
                                <small><?php echo $fromWeather['temp']; ?>°C</small>
                            </div>
                            <div class="route-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                            <div class="route-city">
                                <strong><?php echo htmlspecialchars($to); ?></strong>
                                <small><?php echo $toWeather['temp']; ?>°C</small>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($fromWeather && $toWeather): ?>
                        <?php $tempDiff = abs($toWeather['temp'] - $fromWeather['temp']); ?>
                        <?php if ($tempDiff > 10): ?>
                            <div class="weather-travel-tip" style="border-left-color:#ff6b6b;">
                                <i class="fas fa-exclamation-triangle" style="color:#ff6b6b;"></i>
                                <strong>Temperature Alert:</strong> <?php echo $tempDiff; ?>°C difference! Pack accordingly.
                            </div>
                        <?php elseif ($toWeather['condition'] == 'Rain'): ?>
                            <div class="weather-travel-tip" style="border-left-color:#4a90d9;">
                                <i class="fas fa-umbrella" style="color:#4a90d9;"></i>
                                <strong>Tip:</strong> Rain expected at destination. Bring an umbrella!
                            </div>
                        <?php else: ?>
                            <div class="weather-travel-tip" style="border-left-color:#28a745;">
                                <i class="fas fa-smile" style="color:#28a745;"></i>
                                <strong>Enjoy!</strong> Good weather at your destination.
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="weather-loading">
                        <i class="fas fa-map-marked-alt"></i>
                        <p>Select destination to check weather</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>