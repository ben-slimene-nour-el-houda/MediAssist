<?php
require_once '../session_config.php';
require_once '../../db_connect.php';

redirect_if_not_logged_in('../../login/login.php');

$user_id = $_SESSION['user_id'];

$message = '';
$message_type = '';

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Generate CSRF token for edit form
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Récupérer les informations de l'utilisateur
$user_info = [];
$stmt = $mysqli->prepare("SELECT id, name, email, password, age, gender, phone, profile_photo, created_at, username FROM users WHERE id = ?");
if (!$stmt) {
    error_log("Prepare failed: " . $mysqli->error);
    die("An error occurred. Please try again later.");
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user_info = $result->fetch_assoc();
} else {
    session_destroy();
    header("Location: ../../login/login.php?error=invalid_user");
    exit();
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../logo.png" type="image/png">
    <title>Edit Profile - MediAssist</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary: #26a69a;
            --primary-light: #64d8cb;
            --primary-dark: #00766c;
            --secondary: #80cbc4;
            --accent: #004d40;
            --light: #e0f2f1;
            --dark: #00352c;
            --gray: #f5f7fa;
            --text: #333;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--gray);
            color: var(--text);
        }

        /* Background with Enhanced Medical Icons */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300"><g fill="%2326a69a" opacity="0.2"><path d="M40,70 h20 v-20 h20 v20 h20 v20 h-20 v20 h-20 v-20 h-20 z"/><circle cx="150" cy="50" r="15"/><path d="M140,120 c0,-20 20,-20 20,0 c0,20 20,20 20,0"/><path d="M30,130 c5,0 10,5 10,10 c0,5 5,10 10,10 c5,0 10,-5 10,-10 c0,-5 5,-10 10,-10"/><rect x="120" y="150" width="30" height="20" rx="5"/><path d="M50,30 c0,8 6,15 15,15 c8,0 15,-6 15,-15 c0,-8 -6,-15 -15,-15 c-8,0 -15,6 -15,15 z"/><path d="M200,200 h10 v50 h20 v-50 h10 v-10 h-40 z"/><path d="M220,70 c-8.28,0 -15,6.72 -15,15 c0,8.28 6.72,15 15,15 c8.28,0 15,-6.72 15,-15 c0,-8.28 -6.72,-15 -15,-15 z m0,27 c-6.63,0 -12,-5.37 -12,-12 c0,-6.63 5.37,-12 12,-12 c6.63,0 12,5.37 12,12 c0,6.63 -5.37,12 -12,12 z"/><path d="M260,150 c0,5.52 -4.48,10 -10,10 s-10,-4.48 -10,-10 s4.48,-10 10,-10 s10,4.48 10,10 z m6,0 c0,8.84 -7.16,16 -16,16 s-16,-7.16 -16,-16 s7.16,-16 16,-16 s16,7.16 16,16 z"/><path d="M80,200 c0,0 0,-10 10,-10 c10,0 10,10 10,10 v30 h5 v-30 c0,0 0,-10 10,-10 c10,0 10,10 10,10 v30 h5 v-30 c0,0 0,-15 -15,-15 c-5,0 -10,2 -10,5 c0,-3 -5,-5 -10,-5 c-15,0 -15,15 -15,15 v30 h5 v-30 z"/><path d="M10,200 h30 v5 h-25 v10 h20 v5 h-20 v15 h-5 z"/><path d="M280,100 c-5.52,0 -10,4.48 -10,10 v25 c0,5.52 4.48,10 10,10 s10,-4.48 10,-10 v-25 c0,-5.52 -4.48,-10 -10,-10 z m5,35 c0,2.76 -2.24,5 -5,5 s-5,-2.24 -5,-5 v-25 c0,-2.76 2.24,-5 5,-5 s5,2.24 5,5 v25 z"/></g></svg>') repeat;
            background-size: 300px 300px;
            opacity: 0.08;
            z-index: -1;
            animation: moveIcons 60s linear infinite;
        }

        /* Animation for background icons */
        @keyframes moveIcons {
            0% { background-position: 0 0; }
            100% { background-position: 600px 600px; }
        }

        /* Header Styles */
        header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 1rem 2rem;
            color: var(--white);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .logo i {
            margin-right: 0.5rem;
            color: var(--light);
            font-size: 2rem;
            animation: pulse 2s infinite;
        }

        .back-button {
            color: var(--white);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            color: var(--light);
            transform: translateX(-5px);
        }

        /* Profile Section */
        .profile-section {
            max-width: 600px; /* Adjusted for more compact layout */
            margin: 3rem auto;
            padding: 2rem;
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .profile-photo {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .profile-avatar {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            border: 4px solid var(--primary-light);
            margin-bottom: 1rem;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .profile-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(38, 166, 154, 0.2);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .profile-header h1 {
            font-size: 2rem;
            color: var(--dark);
            position: relative;
            display: inline-block;
        }

        .profile-header h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: var(--primary-light);
            border-radius: 2px;
        }

        /* Alert message styling */
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
            text-align: center;
        }
        
        .alert-success {
            background-color: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
            border: 1px solid #4CAF50;
        }
        
        .alert-error {
            background-color: rgba(244, 67, 54, 0.1);
            color: #F44336;
            border: 1px solid #F44336;
        }

        /* Edit Form Styling */
        .edit-form {
            padding: 1rem;
            max-width: 100%;
        }

        .form-group { 
            margin-bottom: 20px; 
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .form-column { 
            flex: 1; 
        }
        
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
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .form-button {
            background: linear-gradient(90deg, #26a69a 0%, #4db6ac 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 100px;
        }
        
        .form-button:hover {
            background: linear-gradient(90deg, #00897b 0%, #26a69a 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(38, 166, 154, 0.3);
        }
        
        .form-button.secondary {
            background: linear-gradient(90deg, #b0bec5 0%, #90a4ae 100%);
        }

        .error-message {
            color: #f44336;
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }

        /* Animations */
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .profile-section {
                padding: 1.5rem;
            }

            .profile-avatar {
                width: 150px;
                height: 150px;
            }

            .profile-header h1 {
                font-size: 1.5rem;
            }

            .form-row { 
                flex-direction: column; 
                gap: 0; 
            }
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <header>
        <div class="header-content">
            <a href="profil.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Profile</span>
            </a>
            <div class="logo">
                <i class="fas fa-heartbeat"></i>
                <span>MediAssist</span>
            </div>
        </div>
    </header>
    
    <!-- Message Alert (if any) -->
    <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'error'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>
    
    <!-- Profile Section -->
    <section class="profile-section">
        <div class="profile-photo">
            <div class="profile-header">
                <h1>Edit Profile</h1>
            </div>
            <div class="profile-avatar">
                <?php 
                $default_photo = 'default-avatar.png';
                if (!empty($user_info['profile_photo'])) {
                    $photo_path = '../../signup/' . htmlspecialchars($user_info['profile_photo']);
                } else {
                    $photo_path = '../../signup/' . $default_photo;
                }
                ?>
                <img src="<?php echo $photo_path; ?>" alt="Profile Photo" onerror="this.src='../../signup/<?php echo $default_photo; ?>'">
            </div>
        </div>
        <div class="edit-form">
            <form id="editProfileForm" action="update_profile.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="form-row">
                    <div class="form-column">
                        <label for="name">Full Name</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_info['name']); ?>" required>
                        </div>
                        <div class="error-message" id="name-error"></div>
                    </div>
                    <div class="form-column">
                        <label for="email">Email Address</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_info['email']); ?>" required>
                        </div>
                        <div class="error-message" id="email-error"></div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-column">
                        <label for="username">Username</label>
                        <div class="input-group">
                            <i class="fas fa-at"></i>
                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_info['username']); ?>" required>
                        </div>
                        <div class="error-message" id="username-error"></div>
                    </div>
                    <div class="form-column">
                        <label for="password">New Password (leave blank to keep current)</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="●●●●●●●●">
                        </div>
                        <div class="error-message" id="password-error"></div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-column">
                        <label for="phone">Phone Number</label>
                        <div class="input-group">
                            <i class="fas fa-phone"></i>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user_info['phone']); ?>">
                        </div>
                        <div class="error-message" id="phone-error"></div>
                    </div>
                    <div class="form-column">
                        <label for="age">Age</label>
                        <div class="input-group">
                            <i class="fas fa-birthday-cake"></i>
                            <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($user_info['age']); ?>" min="1" max="120">
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
                                <option value="male" <?php echo $user_info['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo $user_info['gender'] === 'female' ? 'selected' : ''; ?>>Female</option>
                             
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
                    <a href="profil.php" class="form-button secondary">Cancel</a>
                    <button type="submit" class="form-button">Save Changes</button>
                </div>
            </form>
        </div>
    </section>

    <script>
        document.getElementById('editProfileForm').addEventListener('submit', function(event) {
            let isValid = true;
            document.querySelectorAll('.error-message').forEach(element => {
                element.style.display = 'none';
            });

            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();

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
            if (password && password.length < 8) {
                document.getElementById('password-error').textContent = 'Password must be at least 8 characters';
                document.getElementById('password-error').style.display = 'block';
                isValid = false;
            }

            if (!isValid) {
                event.preventDefault();
            } else {
                const button = document.querySelector('button[type="submit"]');
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                button.disabled = true;
            }
        });
    </script>
</body>
</html>