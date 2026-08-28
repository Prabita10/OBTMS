<?php
session_start();
if(!isset($_SESSION['username']) || $_SESSION['role'] != 'user'){
    header("Location: login.html");
    exit();
}

$conn = new mysqli("localhost","root","","obtms");
if($conn->connect_error) die("Connection failed: ".$conn->connect_error);

$username = $_SESSION['username'];

// ============================================
// HANDLE AJAX REQUESTS
// ============================================

// Get unread count (for AJAX)
if(isset($_GET['action']) && $_GET['action'] == 'count'){
    $result = $conn->query("SELECT COUNT(*) as unread FROM notifications WHERE username = '$username' AND is_read = 0");
    $count = $result->fetch_assoc()['unread'];
    echo json_encode(['unread_count' => $count]);
    exit();
}

// Get notifications (for AJAX)
if(isset($_GET['action']) && $_GET['action'] == 'get'){
    $sql = "SELECT * FROM notifications WHERE username = '$username' AND is_read = 0 ORDER BY created_at DESC LIMIT 10";
    $result = $conn->query($sql);
    $notifications = [];
    while($row = $result->fetch_assoc()){
        $notifications[] = $row;
    }
    echo json_encode(['success' => true, 'notifications' => $notifications]);
    exit();
}

// Mark all as read (for AJAX)
if(isset($_POST['action']) && $_POST['action'] == 'mark_read'){
    $conn->query("UPDATE notifications SET is_read = 1 WHERE username = '$username'");
    echo json_encode(['success' => true]);
    exit();
}

// Mark single as read (for AJAX)
if(isset($_POST['action']) && $_POST['action'] == 'mark_single'){
    $id = (int)$_POST['id'];
    $conn->query("UPDATE notifications SET is_read = 1 WHERE id = $id AND username = '$username'");
    echo json_encode(['success' => true]);
    exit();
}

// Add notification (for AJAX - called from booking)
if(isset($_POST['action']) && $_POST['action'] == 'add'){
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $conn->query("INSERT INTO notifications (username, message) VALUES ('$username', '$message')");
    echo json_encode(['success' => true]);
    exit();
}

// ============================================
// PAGE LOAD - Mark all as read
// ============================================
$conn->query("UPDATE notifications SET is_read = 1 WHERE username = '$username'");

// Get all notifications
$sql = "SELECT * FROM notifications WHERE username = '$username' ORDER BY created_at DESC";
$notifications = $conn->query($sql);
$totalCount = $notifications->num_rows;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notifications - BusGo</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',Arial,sans-serif;background:#f0f4fb;min-height:100vh}

header{background:#1a2b4c;color:#fff;padding:15px 35px;display:flex;justify-content:space-between;align-items:center}
header h1{font-size:22px}
header h1 span{color:#66b0ff}
header nav a{color:rgba(255,255,255,0.85);text-decoration:none;margin-left:18px;font-size:14px;padding:6px 14px;border-radius:5px;transition:0.2s}
header nav a:hover{background:rgba(255,255,255,0.1)}
header nav a.dashboard{background:rgba(255,255,255,0.08)}
header nav a.logout{background:#dc3545;color:#fff}
header nav a.logout:hover{background:#c82333}

.container{max-width:900px;margin:30px auto;padding:0 20px}

/* Page Header */
.page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;flex-wrap:wrap;gap:15px}
.page-header h2{color:#1a2b4c;font-size:24px}
.page-header h2 i{color:#66b0ff;margin-right:10px}
.page-header .count{background:#e8edf5;padding:4px 14px;border-radius:15px;font-size:13px;color:#1a2b4c}

/* Notification Item */
.notif-item{background:#fff;border-radius:12px;padding:18px 22px;margin-bottom:12px;border:1px solid #e8ecf3;box-shadow:0 2px 6px rgba(0,0,0,0.03);transition:0.3s;display:flex;align-items:flex-start;gap:15px}
.notif-item:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,0.07)}
.notif-item .icon{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.notif-item .icon.booking{background:#e3f2fd;color:#1976d2}
.notif-item .icon.system{background:#fff3e0;color:#e65100}
.notif-item .icon.success{background:#e8f5e9;color:#388e3c}
.notif-item .icon.warning{background:#fce4ec;color:#c62828}

.notif-item .content{flex:1}
.notif-item .content .message{font-size:15px;color:#1a2b4c;line-height:1.5}
.notif-item .content .message strong{color:#007bff}
.notif-item .content .time{font-size:12px;color:#6b7a8f;margin-top:5px}
.notif-item .content .time i{margin-right:4px}

.notif-item .action-link{display:inline-block;margin-top:8px;font-size:13px;color:#007bff;text-decoration:none;font-weight:600}
.notif-item .action-link:hover{text-decoration:underline}

/* Empty State */
.empty-state{text-align:center;padding:60px 20px;background:#fff;border-radius:12px;border:1px solid #e8ecf3}
.empty-state i{font-size:64px;color:#d5dce8;display:block;margin-bottom:15px}
.empty-state h3{color:#1a2b4c;font-size:20px;margin-bottom:8px}
.empty-state p{color:#6b7a8f;font-size:14px}

/* Back Button */
.back-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 22px;background:#1a2b4c;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;transition:0.3s;margin-bottom:20px}
.back-btn:hover{background:#2a4a7a;transform:translateX(-3px)}

/* Mark Read Button */
.mark-read-btn{padding:8px 18px;background:#007bff;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;font-size:13px;transition:0.3s}
.mark-read-btn:hover{background:#0056b3}

@media(max-width:768px){
    header{padding:12px 20px;flex-wrap:wrap}
    header h1{font-size:18px}
    header nav a{margin-left:8px;padding:5px 10px;font-size:12px}
    .page-header{flex-direction:column;align-items:flex-start}
    .notif-item{padding:15px;flex-direction:column;gap:10px}
    .notif-item .icon{width:32px;height:32px;font-size:14px}
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
    <!-- Back Button -->
    <a href="user_dashboard.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>

    <!-- Page Header -->
    <div class="page-header">
        <h2><i class="fas fa-bell"></i> Notifications</h2>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <span class="count"><i class="fas fa-bell"></i> <?php echo $totalCount; ?> notification(s)</span>
            <?php if($totalCount > 0): ?>
            <button class="mark-read-btn" onclick="markAllRead()">
                <i class="fas fa-check-double"></i> Mark All Read
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Notifications List -->
    <div id="notifList">
    <?php if($notifications->num_rows > 0): ?>
        <?php while($row = $notifications->fetch_assoc()): 
            $icon = 'bell';
            $iconClass = 'system';
            if(strpos($row['message'], 'booked') !== false || strpos($row['message'], 'booking') !== false){
                $icon = 'ticket-alt';
                $iconClass = 'booking';
            } elseif(strpos($row['message'], 'success') !== false || strpos($row['message'], 'confirmed') !== false){
                $icon = 'check-circle';
                $iconClass = 'success';
            } elseif(strpos($row['message'], 'cancel') !== false){
                $icon = 'times-circle';
                $iconClass = 'warning';
            }
        ?>
        <div class="notif-item" data-id="<?php echo $row['id']; ?>">
            <div class="icon <?php echo $iconClass; ?>">
                <i class="fas fa-<?php echo $icon; ?>"></i>
            </div>
            <div class="content">
                <div class="message"><?php echo $row['message']; ?></div>
                <div class="time"><i class="far fa-clock"></i> <?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?></div>
                <?php if(strpos($row['message'], 'booking') !== false): ?>
                    <a href="my_bookings.php" class="action-link">View Bookings →</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state" id="emptyState">
            <i class="fas fa-bell-slash"></i>
            <h3>No Notifications</h3>
            <p>You have no notifications yet. Book a ticket to get started!</p>
            <a href="search_buses.php" style="display:inline-block;margin-top:15px;padding:10px 25px;background:#007bff;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">
                <i class="fas fa-search"></i> Search Buses
            </a>
        </div>
    <?php endif; ?>
    </div>
</div>

<script>
// ============================================
// MARK ALL AS READ
// ============================================
function markAllRead() {
    if(!confirm('Mark all notifications as read?')) return;
    
    fetch('notifications.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=mark_read'
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            // Reload page to refresh notifications
            window.location.href = 'notifications.php';
        }
    });
}

// ============================================
// AUTO REFRESH NOTIFICATIONS (every 30 seconds)
// ============================================
function checkNewNotifications() {
    fetch('notifications.php?action=count')
        .then(response => response.json())
        .then(data => {
            // Update badge if you have one in header
            const badge = document.querySelector('.count');
            if(badge) {
                badge.textContent = '🔔 ' + data.unread_count + ' notification(s)';
            }
        });
}

// Run every 30 seconds
setInterval(checkNewNotifications, 30000);

// ============================================
// TOAST NOTIFICATION (if coming from booking)
// ============================================
<?php if(isset($_GET['booking']) && $_GET['booking'] == 'success'): ?>
window.onload = function() {
    showToast('🎫 Your ticket has been booked successfully!');
};
<?php endif; ?>

function showToast(message) {
    // Create toast element
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: #28a745;
        color: white;
        padding: 16px 25px;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(40,167,69,0.3);
        z-index: 9999;
        max-width: 400px;
        animation: slideUp 0.5s ease;
        border-left: 4px solid #1e7e34;
        font-family: 'Segoe UI', Arial, sans-serif;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 12px;
    `;
    toast.innerHTML = `
        <i class="fas fa-check-circle" style="font-size:22px;"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" style="background:none;border:none;color:white;font-size:18px;cursor:pointer;opacity:0.7;">✕</button>
    `;
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if(toast.parentElement) toast.remove();
    }, 5000);
}

// Add slideUp animation if not exists
if(!document.getElementById('toastStyle')) {
    const style = document.createElement('style');
    style.id = 'toastStyle';
    style.textContent = `
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    `;
    document.head.appendChild(style);
}
</script>

</body>
</html>