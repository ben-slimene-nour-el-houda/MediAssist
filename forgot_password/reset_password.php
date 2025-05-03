<?php
// Start the session
session_start();

// Include database connection
require_once '../db_connect.php';

// Initialize variables
$error_message = "";
$success_message = "";
$token = "";
$email = "";
$valid_token = false;

// Check if token is provided in URL
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    
    // Verify token exists and is not expired
    $stmt = $mysqli->prepare("SELECT email, expires FROM password_resets WHERE token = ?");
    if (!$stmt) {
        $error_message = "Database error. Please try again later.";
    } else {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $reset = $result->fetch_assoc();
            $email = $reset['email'];
            $expiry = strtotime($reset['expires']);
            
            // Check if token has expired
            if (time() > $expiry) {
                $error_message = "This password reset link has expired. Please request a new one.";
            } else {
                $valid_token = true;
            }
        } else {
            $error_message = "Invalid password reset link. Please request a new one.";
        }
        $stmt->close();
    }
} else {
    $error_message = "No reset token provided. Please use the link from your email.";
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && $valid_token) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate passwords
    if (empty($new_password) || empty($confirm_password)) {
        $error_message = "Please enter both password fields.";
    } elseif (strlen($new_password) < 8) {
        $error_message = "Password must be at least 8 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update the user's password
        $update_stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE email = ?");
        if (!$update_stmt) {
            $error_message = "Database error. Please try again later.";
        } else {
            $update_stmt->bind_param("ss", $hashed_password, $email);
            $update_stmt->execute();
            
            if ($update_stmt->affected_rows > 0) {
                // Delete the used token
                $delete_stmt = $mysqli->prepare("DELETE FROM password_resets WHERE token = ?");
                $delete_stmt->bind_param("s", $token);
                $delete_stmt->execute();
                $delete_stmt->close();
                
                $success_message = "Your password has been successfully reset. You can now <a href='../login/login.php'>login</a> with your new password.";
                $valid_token = false; // Hide the form
            } else {
                $error_message = "Failed to update password. Please try again.";
            }
            $update_stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../logo.png" type="image/png">
    <title>MediAssist - Reset Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .medical-icon {
            position: absolute;
            opacity: 0.1;
            z-index: -1;
        }

        .icon-1 { top: 10%; left: 5%; font-size: 80px; color: #0288d1; }
        .icon-2 { bottom: 15%; right: 10%; font-size: 70px; color: #26a69a; }
        .icon-3 { top: 20%; right: 15%; font-size: 60px; color: #5c6bc0; }
        .icon-4 { bottom: 10%; left: 15%; font-size: 65px; color: #26c6da; }

        .container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .reset-password-card {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            padding: 35px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .logo {
            margin-bottom: 25px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logo-img {
            height: 65px;
            width: 65px;
            border-radius: 12px;
            padding: 8px;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        h1 {
            color: #2C3E50;
            font-size: 26px;
            margin-bottom: 26px;
            position: relative;
            display: inline-block;
        }

        h1:after {
            content: '';
            position: absolute;
            width: 60%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #4dd0e1, transparent);
            bottom: -8px;
            left: 20%;
        }

        p.description {
            margin-bottom: 20px;
            color: #607d8b;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 22px;
            text-align: left;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 15px;
            color: #455a64;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
            background-color: #f9f9f9;
        }

        input:focus {
            border-color: #4dd0e1;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(77, 208, 225, 0.2);
            outline: none;
        }

        .input-icon {
            position: absolute;
            right: 40px;
            top: 42px;
            color: #90a4ae;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 42px;
            color: #90a4ae;
            cursor: pointer;
        }

        .password-toggle:hover {
            color: #26a69a;
        }

        .password-requirements {
            font-size: 12px;
            color: #607d8b;
            margin-top: 5px;
        }

        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 3px;
            transition: all 0.3s;
            background-color: #e0e0e0;
        }

        .strength-weak { background-color: #f44336; width: 25%; }
        .strength-fair { background-color: #ff9800; width: 50%; }
        .strength-good { background-color: #4caf50; width: 75%; }
        .strength-strong { background-color: #2e7d32; width: 100%; }

        .error-message {
            color: #f44336;
            font-size: 14px;
            text-align: left;
            margin-top: 6px;
            padding: 10px;
            background-color: #ffebee;
            border-radius: 8px;
            display: none;
        }

        .error-message.active {
            display: block;
        }

        .success-message {
            color: #2e7d32;
            font-weight: 500;
            margin-top: 15px;
            display: none;
            padding: 10px;
            background-color: #e8f5e9;
            border-radius: 8px;
        }

        .success-message.active {
            display: block;
        }

        .btn {
            background: linear-gradient(90deg, #26a69a 0%, #4db6ac 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 14px 24px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .btn:hover {
            background: linear-gradient(90deg, #00897b 0%, #26a69a 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(38, 166, 154, 0.3);
        }

        .btn:active {
            transform: translateY(0);
        }

        .links {
            margin-top: 25px;
            font-size: 14px;
            color: #607d8b;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .links a {
            color: #26a69a;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .links a:hover {
            color: #00897b;
            text-decoration: underline;
        }

        .medical-pulse {
            width: 30px;
            height: 30px;
            background-color: rgba(77, 208, 225, 0.2);
            border-radius: 50%;
            position: absolute;
            top: 15px;
            right: 15px;
        }

        .medical-pulse:before {
            content: "";
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background-color: rgba(77, 208, 225, 0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(0.8); opacity: 0.8; }
            70% { transform: scale(1.5); opacity: 0; }
            100% { transform: scale(0.8); opacity: 0; }
        }
    </style>
</head>
<body>
    <!-- Medical icons in background -->
    <div class="medical-icon icon-1"><i class="fas fa-heartbeat"></i></div>
    <div class="medical-icon icon-2"><i class="fas fa-prescription-bottle-alt"></i></div>
    <div class="medical-icon icon-3"><i class="fas fa-stethoscope"></i></div>
    <div class="medical-icon icon-4"><i class="fas fa-hospital"></i></div>

    <div class="container">
        <div class="reset-password-card">
            <div class="medical-pulse"></div>

            <div class="logo">
                <div class="logo-img">
                    <i class="fas fa-heartbeat" style="font-size: 32px; color: #26a69a;"></i>
                    <i class="fas fa-plus"
                        style="font-size: 16px; color: #4dd0e1; position: relative; top: -5px; left: 2px;"></i>
                </div>
            </div>

            <h1>Reset Your Password</h1>

            <?php if ($error_message): ?>
                <div class="error-message active"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <?php if ($success_message): ?>
                <div class="success-message active"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if ($valid_token): ?>
                <p class="description">Please enter a new password for your account.</p>

                <form id="resetPasswordForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?token=' . urlencode($token)); ?>" method="POST">
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <span class="password-toggle" onclick="togglePassword('new_password', 'toggleIcon1')">
                            <i class="fas fa-eye" id="toggleIcon1"></i>
                        </span>
                        <div class="password-requirements">Password must be at least 8 characters long</div>
                        <div class="password-strength" id="passwordStrength"></div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <span class="password-toggle" onclick="togglePassword('confirm_password', 'toggleIcon2')">
                            <i class="fas fa-eye" id="toggleIcon2"></i>
                        </span>
                    </div>
                    
                    <button type="submit" class="btn">Reset Password</button>
                </form>
            <?php endif; ?>

            <div class="links">
                <p>Back to <a href="../login/login.php"><i class="fas fa-sign-in-alt" style="font-size: 12px; margin-right: 4px;"></i>Login</a></p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            let strength = 0;

            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;

            strengthBar.className = 'password-strength';
            if (strength === 1) strengthBar.classList.add('strength-weak');
            else if (strength === 2) strengthBar.classList.add('strength-fair');
            else if (strength === 3) strengthBar.classList.add('strength-good');
            else if (strength === 4) strengthBar.classList.add('strength-strong');
        });

        document.getElementById('resetPasswordForm').addEventListener('submit', function(event) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            let errorMsg = document.querySelector('.error-message');

            if (newPassword.length < 8) {
                event.preventDefault();
                if (!errorMsg) {
                    errorMsg = document.createElement('div');
                    errorMsg.className = 'error-message active';
                    document.querySelector('.reset-password-card').insertBefore(errorMsg, document.querySelector('form'));
                } else {
                    errorMsg.classList.add('active');
                }
                errorMsg.textContent = 'Password must be at least 8 characters long.';
            } else if (newPassword !== confirmPassword) {
                event.preventDefault();
                if (!errorMsg) {
                    errorMsg = document.createElement('div');
                    errorMsg.className = 'error-message active';
                    document.querySelector('.reset-password-card').insertBefore(errorMsg, document.querySelector('form'));
                } else {
                    errorMsg.classList.add('active');
                }
                errorMsg.textContent = 'Passwords do not match.';
            }
        });
    </script>
</body>
</html>