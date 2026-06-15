<?php
session_start();
if(!isset($_SESSION['username']) || $_SESSION['role'] != 'admin'){
    header("Location: login.html");
    exit();
}

$servername="localhost";
$usernameDB="root";
$passwordDB="root";
$dbname="obtms";
$conn=new mysqli($servername,$usernameDB,$passwordDB,$dbname);
if($conn->connect_error){ die("Connection failed: ".$conn->connect_error); }

$username=$_SESSION['username'];
$msg="";

// Fetch current admin info
$sql="SELECT fullname, username FROM admin WHERE username='$username'";
$result=$conn->query($sql);
$admin=$result->fetch_assoc();

// Update info if form submitted
if(isset($_POST['update'])){
    $fullname=$_POST['fullname'];
    $password=$_POST['password'];

    if(!empty($password)){
        $sql="UPDATE admin SET fullname='$fullname', password='$password' WHERE username='$username'";
    } else {
        $sql="UPDATE admin SET fullname='$fullname' WHERE username='$username'";
    }

    if($conn->query($sql)===TRUE){
        $msg="Profile updated successfully!";
        $admin['fullname']=$fullname;
    } else {
        $msg="Error updating profile: ".$conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Admin Account</title>
<style>
body { font-family: Arial,sans-serif; background:#f7f9fc; display:flex; justify-content:center; align-items:center; height:100vh; }
.container { background:#fff; padding:30px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.2); width:400px; }
h2 { color:#1a2b4c; text-align:center; margin-bottom:20px; }
input { width:100%; padding:10px; margin:10px 0; border-radius:5px; border:1px solid #ccc; }
button { width:100%; padding:10px; background:#007bff; color:white; border:none; border-radius:5px; cursor:pointer; }
button:hover { background:#0056b3; }
.msg { text-align:center; color:green; margin-bottom:10px; }
</style>
</head>
<body>

<div class="container">
<h2>Edit Admin Account</h2>
<?php if($msg!=""){ echo "<div class='msg'>$msg</div>"; } ?>
<form method="POST">
    <label>Full Name</label>
    <input type="text" name="fullname" value="<?php echo htmlspecialchars($admin['fullname']); ?>" required>
    <label>Username (cannot change)</label>
    <input type="text" value="<?php echo htmlspecialchars($admin['username']); ?>" disabled>
    <label>New Password (leave blank to keep current)</label>
    <input type="password" name="password" placeholder="New Password">
    <button type="submit" name="update">Update Profile</button>
</form>
<a href="admin_dashboard.php" style="display:block; text-align:center; margin-top:15px;">Back to Dashboard</a>
</div>

</body>
</html>