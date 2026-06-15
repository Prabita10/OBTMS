document.getElementById('registerForm').addEventListener('submit', function(e) {
    let fullname = document.getElementById('fullname').value.trim();
    let email = document.getElementById('email').value.trim();
    let phone = document.getElementById('phone').value.trim();
    let username = document.getElementById('username').value.trim();
    let password = document.getElementById('password').value;
    let confirm_password = document.getElementById('confirm_password').value;
    let errorMsg = document.getElementById('error-msg');

    // Full Name Validation (letters and spaces only)
    if(!/^[A-Za-z\s]+$/.test(fullname)){
        e.preventDefault();
        errorMsg.textContent = "Full Name can only contain letters and spaces.";
        return;
    }

    // Email Validation
    let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/;
    if(!emailPattern.test(email)){
        e.preventDefault();
        errorMsg.textContent = "Enter a valid email address.";
        return;
    }

    // Phone Validation (10 digits starting with 9)
    if(!/^9\d{9}$/.test(phone)){
        e.preventDefault();
        errorMsg.textContent = "Phone must be 10 digits starting with 9.";
        return;
    }

    // Username Validation (letters, numbers, 3-15 chars)
    if(!/^[A-Za-z0-9]{3,15}$/.test(username)){
        e.preventDefault();
        errorMsg.textContent = "Username must be 3-15 letters/numbers only.";
        return;
    }

    // Password Validation (min 5 chars, 1 capital, 1 digit, 1 special)
    let passPattern = /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{5,}$/;
    if(!passPattern.test(password)){
        e.preventDefault();
        errorMsg.textContent = "Password must be at least 5 chars with 1 capital, 1 digit, 1 special.";
        return;
    }

    // Confirm Password
    if(password !== confirm_password){
        e.preventDefault();
        errorMsg.textContent = "Passwords do not match.";
        return;
    }

    errorMsg.textContent = ""; // Clear error
});