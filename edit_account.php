<?php
// Start session only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is logged in
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header("Location: login.html");
    exit();
}

// Database connection
$servername = "localhost";
$usernameDB = "root";
$passwordDB = "root";
$dbname = "obtms";

$conn = new mysqli($servername, $usernameDB, $passwordDB, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_SESSION['username'];

// Fetch current user info
$sql = "SELECT fullname,email,phone FROM info WHERE username='$username'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Update info if form submitted
$updated = false;
if (isset($_POST['update'])) {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $update_sql = "UPDATE info SET fullname='$fullname', email='$email', phone='$phone' WHERE username='$username'";
    if ($conn->query($update_sql) === TRUE) {
        $updated = true;
        $user['fullname'] = $fullname;
        $user['email'] = $email;
        $user['phone'] = $phone;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Account - OBTMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f9fc;
            margin: 0;
            padding: 0;
        }

        /* Header same as dashboard */
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

        /* Container below header */
        .container {
            max-width: 500px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        /* Form styling */
        h2 {
            color: #1a2b4c;
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 15px;
        }

        button:hover {
            background: #0056b3;
        }

        /* Popup message */
        .popup {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: #fff;
            padding: 15px 20px;
            border-radius: 5px;
            display: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
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
        <h2>Edit Account</h2>

        <form id="editForm" method="POST" onsubmit="return validateForm();">
            <label for="fullname">Full Name</label>
            <input type="text" name="fullname" id="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>"
                required>

            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                required>

            <label for="phone">Phone Number</label>
            <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required
                placeholder="10 digits starting with 9">

            <button type="submit" name="update">Update</button>
        </form>
    </div>

    <div id="popup" class="popup">Profile Updated Successfully!</div>

    <script>
        // JS validation
        function validateForm() {
            let fullname = document.getElementById('fullname').value.trim();
            let email = document.getElementById('email').value.trim();
            let phone = document.getElementById('phone').value.trim();

            if (!/^[A-Za-z\s]+$/.test(fullname)) {
                alert("Full Name can only contain letters and spaces.");
                return false;
            }
            if (!/^[a-zA-Z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/.test(email)) {
                alert("Enter a valid email.");
                return false;
            }
            if (!/^9\d{9}$/.test(phone)) {
                alert("Phone must be 10 digits starting with 9.");
                return false;
            }
            return true;
        }

        // Show popup if updated
        <?php if ($updated): ?>
            document.getElementById('popup').style.display = 'block';
            setTimeout(() => { document.getElementById('popup').style.display = 'none'; }, 3000);
        <?php endif; ?>
    </script>

</body>

</html>