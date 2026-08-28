<?php
session_start();
if(!isset($_SESSION['username']) || $_SESSION['role'] != 'admin'){
    header("Location: login.html");
    exit();
}

$servername="localhost";
$usernameDB="root";
$passwordDB="";
$dbname="obtms";
$conn=new mysqli($servername,$usernameDB,$passwordDB,$dbname);
if($conn->connect_error){ die("Connection failed: ".$conn->connect_error); }

$username=$_SESSION['username'];
$msg="";
$msgType="";

// Fetch current admin info
$sql="SELECT fullname, username FROM admin WHERE username='$username'";
$result=$conn->query($sql);
$admin=$result->fetch_assoc();

// Update info if form submitted
if(isset($_POST['update'])){
    $fullname = mysqli_real_escape_string($conn, trim($_POST['fullname']));
    $password = $_POST['password'];

    // Validate Full Name: Only letters and spaces
    if(!preg_match('/^[A-Za-z\s]+$/', $fullname)){
        $msg = "Full Name can only contain letters and spaces!";
        $msgType = "error";
    }
    // Validate password if provided
    elseif(!empty($password)){
        // Password must be at least 5 characters, contain 1 capital letter, 1 digit, 1 special character
        if(strlen($password) < 5){
            $msg = "Password must be at least 5 characters long!";
            $msgType = "error";
        } elseif(!preg_match('/[A-Z]/', $password)){
            $msg = "Password must contain at least 1 capital letter!";
            $msgType = "error";
        } elseif(!preg_match('/[0-9]/', $password)){
            $msg = "Password must contain at least 1 digit!";
            $msgType = "error";
        } elseif(!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)){
            $msg = "Password must contain at least 1 special character (!@#$%^&*(),.?\":{}|<>)!";
            $msgType = "error";
        } else {
            // Valid password - hash it
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE admin SET fullname='$fullname', password='$hashed_password' WHERE username='$username'";
            if($conn->query($sql)===TRUE){
                $msg = "Profile updated successfully!";
                $msgType = "success";
                $admin['fullname'] = $fullname;
            } else {
                $msg = "Error updating profile: ".$conn->error;
                $msgType = "error";
            }
        }
    } else {
        // No password change - update without password
        $sql = "UPDATE admin SET fullname='$fullname' WHERE username='$username'";
        if($conn->query($sql)===TRUE){
            $msg = "Profile updated successfully!";
            $msgType = "success";
            $admin['fullname'] = $fullname;
        } else {
            $msg = "Error updating profile: ".$conn->error;
            $msgType = "error";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Admin Account - OBTMS</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',Arial,sans-serif;background:#f0f4fb;min-height:100vh}

/* Header */
header{background:#1a2b4c;color:#fff;padding:15px 35px;display:flex;justify-content:space-between;align-items:center}
header h1{font-size:22px}
header h1 span{color:#66b0ff}
header nav a{color:rgba(255,255,255,0.85);text-decoration:none;margin-left:18px;font-size:14px;padding:6px 14px;border-radius:5px;transition:0.2s}
header nav a:hover{background:rgba(255,255,255,0.1)}
header nav a.dashboard{background:rgba(255,255,255,0.08)}
header nav a.logout{background:#dc3545;color:#fff}
header nav a.logout:hover{background:#c82333}

/* Container */
.container{max-width:550px;margin:40px auto;padding:0 20px}

/* Card */
.card{background:#fff;border-radius:14px;padding:35px;box-shadow:0 4px 20px rgba(0,0,0,0.06);border:1px solid #e8ecf3}
.card-header{text-align:center;margin-bottom:25px}
.card-header h2{color:#1a2b4c;font-size:24px}
.card-header h2 i{color:#66b0ff;margin-right:10px}
.card-header p{color:#6b7a8f;font-size:14px;margin-top:5px}

/* Alert Messages */
.alert{padding:12px 16px;border-radius:8px;margin-bottom:18px;font-weight:500;display:flex;align-items:center;gap:10px}
.alert i{font-size:18px}
.alert-success{background:#d4edda;color:#155724;border:1px solid #c3e6cb}
.alert-error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb}

/* Form */
.form-group{margin-bottom:18px}
.form-group label{display:block;font-size:13px;font-weight:600;color:#1a2b4c;margin-bottom:5px}
.form-group label i{color:#66b0ff;margin-right:6px;width:18px}
.form-group input{width:100%;padding:11px 15px;border-radius:8px;border:1.5px solid #e2e8f0;font-size:14px;transition:0.3s;background:#fafcff}
.form-group input:focus{outline:none;border-color:#1a2b4c;box-shadow:0 0 0 4px rgba(26,43,76,0.08)}
.form-group input:disabled{background:#f0f2f5;color:#999}
.form-group input.error{border-color:#dc3545;box-shadow:0 0 0 4px rgba(220,53,69,0.1)}
.form-group input.success{border-color:#28a745;box-shadow:0 0 0 4px rgba(40,167,69,0.1)}
.hint{font-size:11px;color:#6b7a8f;margin-top:4px}
.hint i{margin-right:4px}
.hint.valid{color:#28a745}
.hint.invalid{color:#dc3545}

/* Password toggle */
.password-wrapper{position:relative}
.password-wrapper input{padding-right:45px}
.toggle-password{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#6b7a8f;cursor:pointer;font-size:16px;padding:5px}
.toggle-password:hover{color:#1a2b4c}

/* Password strength indicators */
.password-requirements {
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    margin-top:6px;
}
.req-item {
    font-size:11px;
    color:#6c757d;
    display:flex;
    align-items:center;
    gap:4px;
    padding:2px 8px;
    border-radius:10px;
    background:#f8f9fa;
}
.req-item i { font-size:10px; }
.req-item.met { color:#28a745; background:#d4edda; }
.req-item.unmet { color:#dc3545; background:#f8d7da; }

/* Button */
.btn-submit{width:100%;padding:12px;background:#1a2b4c;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;transition:0.3s;display:flex;align-items:center;justify-content:center;gap:10px;margin-top:5px}
.btn-submit:hover{background:#2a4a7a;transform:scale(1.01)}

/* Back link */
.back-link{display:block;text-align:center;margin-top:18px;color:#6b7a8f;text-decoration:none;font-size:14px}
.back-link:hover{color:#1a2b4c}
.back-link i{margin-right:6px}

@media(max-width:600px){
    header{padding:12px 20px;flex-wrap:wrap}
    header h1{font-size:18px}
    header nav a{margin-left:8px;padding:5px 10px;font-size:12px}
    .card{padding:25px}
    .password-requirements{flex-direction:column;gap:4px}
}
</style>
</head>
<body>

<header>
    <h1>Bus<span>Go</span></h1>
    <nav>
        <a href="admin_dashboard.php" class="dashboard"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
</header>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-user-cog"></i> Edit Admin Account</h2>
            <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($admin['username']); ?></p>
        </div>

        <?php if($msg != ""): ?>
            <div class="alert alert-<?php echo $msgType; ?>">
                <i class="fas <?php echo $msgType == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <form id="editForm" method="POST">
            <div class="form-group">
                <label for="fullname"><i class="fas fa-user"></i> Full Name</label>
                <input type="text" name="fullname" id="fullname" value="<?php echo htmlspecialchars($admin['fullname']); ?>" required>
                <div class="hint" id="nameHint">
                    <i class="fas fa-info-circle"></i> Only letters and spaces allowed
                </div>
            </div>

            <div class="form-group" style="margin-bottom:5px">
                <label><i class="fas fa-lock"></i> Username</label>
                <input type="text" value="<?php echo htmlspecialchars($admin['username']); ?>" disabled>
                <div class="hint"><i class="fas fa-info-circle"></i> Username cannot be changed</div>
            </div>

            <!-- Password Field -->
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> New Password <span style="color:#6b7a8f;font-weight:400;">(optional)</span></label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" placeholder="Leave blank to keep current password" maxlength="50">
                    <button type="button" class="toggle-password" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
                <div class="hint" id="passwordHint">
                    <i class="fas fa-info-circle"></i> At least 5 characters, 1 capital letter, 1 digit, 1 special character
                </div>
                <div class="password-requirements" id="passwordRequirements">
                    <span class="req-item unmet" id="reqLength"><i class="fas fa-times-circle"></i> Min 5 chars</span>
                    <span class="req-item unmet" id="reqCapital"><i class="fas fa-times-circle"></i> 1 Capital letter</span>
                    <span class="req-item unmet" id="reqDigit"><i class="fas fa-times-circle"></i> 1 Digit</span>
                    <span class="req-item unmet" id="reqSpecial"><i class="fas fa-times-circle"></i> 1 Special char</span>
                </div>
            </div>

            <button type="submit" name="update" class="btn-submit">
                <i class="fas fa-save"></i> Update Profile
            </button>
        </form>

        <a href="admin_dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<script>
// Toggle password visibility
function togglePassword() {
    const password = document.getElementById('password');
    const icon = document.getElementById('toggleIcon');
    if (password.type === 'password') {
        password.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        password.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Check password requirements
function checkPassword(password) {
    const requirements = {
        length: password.length >= 5,
        capital: /[A-Z]/.test(password),
        digit: /[0-9]/.test(password),
        special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
    };
    return requirements;
}

// Update password requirements UI
function updateRequirements(password) {
    const reqs = checkPassword(password);
    const elements = {
        length: document.getElementById('reqLength'),
        capital: document.getElementById('reqCapital'),
        digit: document.getElementById('reqDigit'),
        special: document.getElementById('reqSpecial')
    };
    
    const metCount = Object.values(reqs).filter(v => v).length;
    const hint = document.getElementById('passwordHint');
    
    // Update each requirement
    for (const [key, element] of Object.entries(elements)) {
        if (reqs[key]) {
            element.className = 'req-item met';
            element.innerHTML = '<i class="fas fa-check-circle"></i> ' + element.textContent.replace(/[✓✗]\s*/, '');
        } else {
            element.className = 'req-item unmet';
            element.innerHTML = '<i class="fas fa-times-circle"></i> ' + element.textContent.replace(/[✓✗]\s*/, '');
        }
    }
    
    // Update overall hint
    if (password.length === 0) {
        hint.innerHTML = '<i class="fas fa-info-circle"></i> At least 5 characters, 1 capital letter, 1 digit, 1 special character';
        hint.className = 'hint';
    } else if (metCount === 4) {
        hint.innerHTML = '<i class="fas fa-check-circle"></i> Strong password!';
        hint.className = 'hint valid';
    } else {
        hint.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + (4 - metCount) + ' requirement(s) remaining';
        hint.className = 'hint invalid';
    }
}

// Real-time validation for full name
document.getElementById('fullname').addEventListener('input', function() {
    const value = this.value;
    const hint = document.getElementById('nameHint');
    const isValid = /^[A-Za-z\s]+$/.test(value);
    
    if (value.length === 0) {
        hint.innerHTML = '<i class="fas fa-info-circle"></i> Only letters and spaces allowed';
        hint.className = 'hint';
        this.className = '';
    } else if (isValid) {
        hint.innerHTML = '<i class="fas fa-check-circle"></i> Valid name!';
        hint.className = 'hint valid';
        this.className = 'success';
    } else {
        hint.innerHTML = '<i class="fas fa-exclamation-circle"></i> Only letters and spaces allowed';
        hint.className = 'hint invalid';
        this.className = 'error';
    }
});

// Real-time validation for password
document.getElementById('password').addEventListener('input', function() {
    const value = this.value;
    const reqs = checkPassword(value);
    const metCount = Object.values(reqs).filter(v => v).length;
    
    updateRequirements(value);
    
    if (value.length === 0) {
        this.className = '';
    } else if (metCount === 4) {
        this.className = 'success';
    } else {
        this.className = 'error';
    }
});

// Form validation
document.getElementById('editForm').addEventListener('submit', function(e) {
    let fullname = document.getElementById('fullname').value.trim();
    let password = document.getElementById('password').value.trim();

    // Validate Full Name
    if(!/^[A-Za-z\s]+$/.test(fullname)){
        e.preventDefault();
        alert("❌ Full Name can only contain letters and spaces.");
        return false;
    }
    
    // Validate password if provided
    if(password.length > 0){
        const reqs = checkPassword(password);
        if(!reqs.length){
            e.preventDefault();
            alert("❌ Password must be at least 5 characters long.");
            return false;
        }
        if(!reqs.capital){
            e.preventDefault();
            alert("❌ Password must contain at least 1 capital letter.");
            return false;
        }
        if(!reqs.digit){
            e.preventDefault();
            alert("❌ Password must contain at least 1 digit.");
            return false;
        }
        if(!reqs.special){
            e.preventDefault();
            alert("❌ Password must contain at least 1 special character (!@#$%^&*(),.?\":{}|<>).");
            return false;
        }
    }
    return true;
});
</script>

</body>
</html>