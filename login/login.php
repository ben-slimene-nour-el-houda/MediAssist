<?php
// Start the session
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../accueil/accueil.php?success=already_logged_in");
    exit();
}

// Include database connection
require_once '../db_connect.php';

// Initialize variables
$error_message = "";
$success_message = "";

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize form data
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    // Validate form data
    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password.";
    } else {
        // Check user credentials
        $stmt = $mysqli->prepare("SELECT id, name, password FROM users WHERE username = ?");
        if (!$stmt) {
            $error_message = "Database error. Please try again later.";
        } else {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();

                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Password is correct, set session variables
                    session_regenerate_id(true); // Prevent session fixation
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];

                    // Handle "Remember Me" functionality
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        $expires = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60));

                        // Store token in database
                        $stmt = $mysqli->prepare("INSERT INTO remember_tokens (user_id, token, expires) VALUES (?, ?, ?)");
                        $stmt->bind_param("iss", $user['id'], $token, $expires);
                        $stmt->execute();
                        $stmt->close();

                        // Set cookie
                        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
                    }

                    // Redirect to home page
                    header("Location: ../accueil/accueil.php?success=login_success");
                    exit();
                } else {
                    $error_message = "Invalid username or password.";
                }
            } else {
                $error_message = "Invalid username or password.";
            }
            $stmt->close();
        }
    }
}

// Check for error or success messages from redirect
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'session_expired':
            $error_message = "Your session has expired. Please log in again.";
            break;
        case 'auth_required':
            $error_message = "You must be logged in to access that page.";
            break;
        case 'invalid_user':
            $error_message = "User account not found. Please log in again.";
            break;
        default:
            $error_message = "An error occurred. Please try again.";
    }
}

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'logout':
            $success_message = "You have been successfully logged out.";
            break;
        case 'registered':
            $success_message = "Registration successful! You can now log in.";
            break;
        default:
            $success_message = "Operation completed successfully.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../logo.png" type="image/png">
    <title>MediAssist - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Your existing CSS remains unchanged */
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

        .login-card {
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

        .error-message {
            color: #f44336;
            font-size: 14px;
            text-align: left;
            margin-top: 6px;
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

        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            font-size: 14px;
            color: #455a64;
        }

        .remember-me input {
            width: auto;
            margin-right: 10px;
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
        <div class="login-card">
            <div class="medical-pulse"></div>

            <div class="logo">
                <div class="logo-img">
                    <i class="fas fa-heartbeat" style="font-size: 32px; color: #26a69a;"></i>
                    <i class="fas fa-plus"
                        style="font-size: 16px; color: #4dd0e1; position: relative; top: -5px; left: 2px;"></i>
                </div>
            </div>

            <h1>Welcome to MediAssist</h1>

            <?php if ($error_message): ?>
                <div class="error-message active"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <?php if ($success_message): ?>
                <div class="success-message active"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <form id="loginForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                    <span class="input-icon"><i class="fas fa-user"></i></span>
                    <div class="error-message" id="username-error"></div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <span class="input-icon"><i class="fas fa-lock"></i></span>
                    <span class="password-toggle" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </span>
                    <div class="error-message" id="password-error"></div>
                </div>

                

                <button type="submit" class="btn">
                    <i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i>Login
                </button>
            </form>

            <div class="links">
                <p>Don't have an account? <a href="../signup/signup.php"><i class="fas fa-user-plus"
                            style="font-size: 12px; margin-right: 4px;"></i>Register</a></p>
                <p><a href="../forgot_password/forgot_password.php"><i class="fas fa-question-circle"
                            style="font-size: 12px; margin-right: 4px;"></i>Forgot Password?</a></p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>