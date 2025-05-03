<?php
session_start();
// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'mediassist';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        error_log("CSRF token validation failed.");
        $_SESSION['error_message'] = 'CSRF token validation failed.';
        header('Location: signup.php');
        exit;
    }

    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $age = filter_input(INPUT_POST, 'age', FILTER_SANITIZE_NUMBER_INT);
    $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $created_at = date('Y-m-d H:i:s');

    // Check if email already exists
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if (!$check_stmt) {
        error_log("Prepare failed for email check: " . $conn->error);
        $_SESSION['error_message'] = 'Database error occurred.';
        header('Location: signup.php');
        exit;
    }
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        error_log("Duplicate email attempted: $email");
        $_SESSION['error_message'] = 'This email is already registered. Please use a different email or log in.';
        header('Location: signup.php');
        $check_stmt->close();
        exit;
    }
    $check_stmt->close();

    // Handle profile photo upload
    $profile_photo = NULL;
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_file_size = 5 * 1024 * 1024; // 5MB
        $file_type = $_FILES['profile_photo']['type'];
        $file_size = $_FILES['profile_photo']['size'];

        // Validate file type and size
        if (!in_array($file_type, $allowed_types)) {
            error_log("Invalid file type: $file_type");
            $_SESSION['error_message'] = 'Only JPEG, PNG, and GIF files are allowed.';
            header('Location: signup.php');
            exit;
        }
        if ($file_size > $max_file_size) {
            error_log("File size too large: $file_size bytes");
            $_SESSION['error_message'] = 'Profile photo must be less than 5MB.';
            header('Location: signup.php');
            exit;
        }

        $upload_dir = 'Uploads/';
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                error_log("Failed to create directory: $upload_dir");
                $_SESSION['error_message'] = 'Failed to create upload directory.';
                header('Location: signup.php');
                exit;
            }
        }
        $photo_name = uniqid() . '_' . basename($_FILES['profile_photo']['name']);
        $photo_path = $upload_dir . $photo_name;

        // Move the uploaded file
        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $photo_path)) {
            // Store just the path in the database, not NULL
            $profile_photo = $photo_path;
            error_log("Profile photo uploaded successfully: $photo_path");
        } else {
            error_log("Failed to move uploaded file to: $photo_path");
            $_SESSION['error_message'] = 'Failed to upload profile photo.';
            header('Location: signup.php');
            exit;
        }
    }

    // Debug the value of $profile_photo
    error_log("Profile photo path to be saved in DB: " . ($profile_photo ?? 'NULL'));

    // Insert into database - Fixed the binding parameter type for profile_photo
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, age, gender, phone, profile_photo, created_at, username) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        $_SESSION['error_message'] = 'Database error occurred.';
        header('Location: signup.php');
        exit;
    }
    
    // Changed binding parameter from "isssississ" to "sssisssss"
    $stmt->bind_param("sssisssss", $name, $email, $password, $age, $gender, $phone, $profile_photo, $created_at, $username);

    try {
        if ($stmt->execute()) {
            error_log("User registered successfully. Profile photo: " . ($profile_photo ?? 'NULL'));
            $_SESSION['success_message'] = 'Registration successful! Please log in.';
            header('Location: ../login/login.php');
            $stmt->close();
            $conn->close();
            exit;
        } else {
            // Add this to debug SQL execution errors
            error_log("SQL execution failed: " . $stmt->error);
            $_SESSION['error_message'] = 'Registration failed. Please try again.';
            header('Location: signup.php');
            exit;
        }
    } catch (mysqli_sql_exception $e) {
        error_log("Database insert failed: " . $e->getMessage());
        $_SESSION['error_message'] = 'Registration failed. Please try again or use a different email.';
        header('Location: signup.php');
        $stmt->close();
        $conn->close();
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../logo.png" type="image/png">
    <title>MediAssist - Sign Up</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            overflow-x: hidden;
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
            max-width: 780px;
            padding: 20px;
            position: relative;
            z-index: 1;
        }
        
        .signup-card {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            padding: 35px;
            position: relative;
            overflow: hidden;
        }
        
        .card-accent {
            position: absolute;
            height: 8px;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(90deg, #4dd0e1 0%, #26a69a 50%, #5c6bc0 100%);
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
        
        .logo {
            margin-bottom: 25px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .logo-img {
            height: 65px;
            border-radius: 12px;
            padding: 8px;
            background-color: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        h1 {
            color: #2C3E50;
            font-size: 26px;
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
            text-align: center;
            width: 100%;
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
        
        h2 {
            color: #455a64;
            font-size: 18px;
            margin-bottom: 26px;
            margin-top: 10px;
            text-align: center;
        }
        
        .form-group { margin-bottom: 20px; }
        
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .form-column { flex: 1; }
        
        label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #455a64;
            font-size: 15px;
        }
        
        .input-group {
            position: relative;
            display: flex;
            align-items: center;
            background-color: #f9f9f9;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 10px 16px;
            transition: all 0.3s;
        }
        
        .input-group:focus-within {
            border-color: #4dd0e1;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(77, 208, 225, 0.2);
        }
        
        .input-group i {
            margin-right: 10px;
            color: #78909c;
        }
        
        .input-group input, .input-group select {
            border: none;
            outline: none;
            width: 100%;
            font-size: 15px;
            background: transparent;
        }
        
        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        button {
            background: linear-gradient(90deg, #26a69a 0%, #4db6ac 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 14px 24px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 120px;
        }
        
        button:hover {
            background: linear-gradient(90deg, #00897b 0%, #26a69a 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(38, 166, 154, 0.3);
        }
        
        button.secondary {
            background: linear-gradient(90deg, #b0bec5 0%, #90a4ae 100%);
        }
        
        .error-message {
            color: #f44336;
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }
        
        .error-banner {
            background-color: #f44336;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            display: <?php echo isset($_SESSION['error_message']) ? 'block' : 'none'; ?>;
        }
        
        @media (max-width: 768px) {
            .form-row { flex-direction: column; gap: 0; }
            .container { padding: 10px; }
            .signup-card { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="medical-icon icon-1"><i class="fas fa-heartbeat"></i></div>
    <div class="medical-icon icon-2"><i class="fas fa-prescription-bottle-alt"></i></div>
    <div class="medical-icon icon-3"><i class="fas fa-stethoscope"></i></div>
    <div class="medical-icon icon-4"><i class="fas fa-hospital"></i></div>
    
    <div class="container">
        <div class="signup-card">
            <div class="card-accent"></div>
            <div class="medical-pulse"></div>
            
            <div class="logo">
                <div class="logo-img">
                    <i class="fas fa-heartbeat" style="font-size: 32px; color: #26a69a;"></i>
                    <i class="fas fa-plus" style="font-size: 16px; color: #4dd0e1; position: relative; top: -5px; left: 2px;"></i>
                </div>
            </div>
            
            <h1>MediAssist</h1>
            <h2>Create Your Account</h2>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="error-banner"><?php echo htmlspecialchars($_SESSION['error_message']); ?></div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            
            <form id="signupForm" action="signup.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                
                <div class="form-row">
                    <div class="form-column">
                        <label for="name">Full Name</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" id="name" name="name" placeholder="John Doe" required>
                        </div>
                        <div class="error-message" id="name-error"></div>
                    </div>
                    <div class="form-column">
                        <label for="email">Email Address</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="example@email.com" required>
                        </div>
                        <div class="error-message" id="email-error"></div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-column">
                        <label for="username">Username</label>
                        <div class="input-group">
                            <i class="fas fa-at"></i>
                            <input type="text" id="username" name="username" placeholder="johndoe123" required>
                        </div>
                        <div class="error-message" id="username-error"></div>
                    </div>
                    <div class="form-column">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="●●●●●●●●" required>
                        </div>
                        <div class="error-message" id="password-error"></div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-column">
                        <label for="phone">Phone Number</label>
                        <div class="input-group">
                            <i class="fas fa-phone"></i>
                            <input type="tel" id="phone" name="phone" placeholder="+1 (555) 123-4567">
                        </div>
                        <div class="error-message" id="phone-error"></div>
                    </div>
                    <div class="form-column">
                        <label for="age">Age</label>
                        <div class="input-group">
                            <i class="fas fa-birthday-cake"></i>
                            <input type="number" id="age" name="age" placeholder="25" min="1" max="120">
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-column">
                        <label for="gender">Gender</label>
                        <div class="input-group">
                            <i class="fas fa-venus-mars"></i>
                            <select id="gender" name="gender">
                                <option value="">Select gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-column">
                        <label for="profile_photo">Profile Photo</label>
                        <div class="input-group">
                            <i class="fas fa-camera"></i>
                            <input type="file" id="profile_photo" name="profile_photo" accept="image/*">
                        </div>
                    </div>
                </div>
                
                <div class="button-group">
                    <button type="button" onclick="window.location.href='../login/login.php'" class="secondary">
                        <i class="fas fa-chevron-left" style="margin-right: 8px;"></i> Back to Login
                    </button>
                    <button type="submit">
                        Sign Up <i class="fas fa-check" style="margin-left: 8px;"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('signupForm').addEventListener('submit', function(event) {
            let isValid = true;
            document.querySelectorAll('.error-message').forEach(element => {
                element.style.display = 'none';
            });

            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            const phone = document.getElementById('phone').value.trim();

            if (name.length < 2) {
                document.getElementById('name-error').textContent = 'Name must be at least 2 characters';
                document.getElementById('name-error').style.display = 'block';
                isValid = false;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                document.getElementById('email-error').textContent = 'Invalid email address';
                document.getElementById('email-error').style.display = 'block';
                isValid = false;
            }
            if (username.length < 3) {
                document.getElementById('username-error').textContent = 'Username must be at least 3 characters';
                document.getElementById('username-error').style.display = 'block';
                isValid = false;
            }
            if (password.length < 8) {
                document.getElementById('password-error').textContent = 'Password must be at least 8 characters';
                document.getElementById('password-error').style.display = 'block';
                isValid = false;
            }

            if (!isValid) {
                event.preventDefault();
            } else {
                const button = document.querySelector('button[type="submit"]');
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                button.disabled = true;
            }
        });
    </script>
</body>
</html>