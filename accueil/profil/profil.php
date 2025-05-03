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
    <title>My Profile - MediAssist</title>
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
            max-width: 900px;
            margin: 3rem auto;
            padding: 2rem;
            display: flex;
            gap: 2rem;
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .profile-photo {
            flex: 4; /* 40% width */
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

        .profile-details {
            flex: 6; /* 60% width */
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* Fixed two-column layout */
            gap: 1rem;
        }

        .detail-card {
            background: var(--gray);
            border-radius: 8px;
            padding: 1.2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            width: 100%; /* Ensure cards take full column width */
            box-sizing: border-box;
            border-left: 3px solid transparent;
        }

        .detail-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.08);
            border-left: 3px solid var(--primary);
        }

        .detail-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(38, 166, 154, 0.1), transparent);
            transition: transform 0.5s ease;
            transform: translateX(-100%);
        }

        .detail-card:hover::before {
            transform: translateX(100%);
        }

        .detail-icon {
            font-size: 1.2rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .detail-label {
            font-size: 0.85rem;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 0.3rem;
            letter-spacing: 0.5px;
        }

        .detail-value {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--text);
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

        /* Edit Button */
        .edit-button {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 20px;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 1rem;
            gap: 0.5rem;
        }

        .edit-button:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(38, 166, 154, 0.3);
        }

        /* Animations */
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .profile-section {
                flex-direction: column;
                padding: 1.5rem;
            }

            .profile-photo {
                flex: none; /* Reset flex for stacking */
            }

            .profile-details {
                flex: none; /* Reset flex for stacking */
                grid-template-columns: 1fr; /* Single column on mobile */
            }

            .profile-avatar {
                width: 150px;
                height: 150px;
            }

            .profile-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <header>
        <div class="header-content">
            <a href="../accueil.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Dashboard</span>
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
                <h1>My Profile</h1>
            </div>
            <div class="profile-avatar">
                <?php 
                // Chemin correct pour l'image de profil
                $default_photo = 'default-avatar.png'; // Une image par défaut dans votre dossier
                
                if (!empty($user_info['profile_photo'])) {
                    $photo_path = '../../signup/' . htmlspecialchars($user_info['profile_photo']);
                } else {
                    $photo_path = '../../signup/' . $default_photo;
                }
                ?>
                <img src="<?php echo $photo_path; ?>" alt="Profile Photo" onerror="this.src='../../signup/<?php echo $default_photo; ?>'">
            </div>
            <a href="edit_profile.php" class="edit-button">
                <i class="fas fa-edit"></i> Edit Profile
            </a>
        </div>
        <div class="profile-details">
            <div class="detail-card">
                <div class="detail-icon"><i class="fas fa-user"></i></div>
                <div class="detail-label">Name</div>
                <div class="detail-value"><?php echo htmlspecialchars($user_info['name']); ?></div>
            </div>
            <div class="detail-card">
                <div class="detail-icon"><i class="fas fa-envelope"></i></div>
                <div class="detail-label">Email</div>
                <div class="detail-value"><?php echo htmlspecialchars($user_info['email']); ?></div>
            </div>
            <div class="detail-card">
                <div class="detail-icon"><i class="fas fa-lock"></i></div>
                <div class="detail-label">Password</div>
                <div class="detail-value">********</div>
            </div>
            <div class="detail-card">
                <div class="detail-icon"><i class="fas fa-birthday-cake"></i></div>
                <div class="detail-label">Age</div>
                <div class="detail-value"><?php echo !empty($user_info['age']) ? htmlspecialchars($user_info['age']) : 'Not specified'; ?></div>
            </div>
            <div class="detail-card">
                <div class="detail-icon"><i class="fas fa-venus-mars"></i></div>
                <div class="detail-label">Gender</div>
                <div class="detail-value"><?php echo !empty($user_info['gender']) ? htmlspecialchars($user_info['gender']) : 'Not specified'; ?></div>
            </div>
            <div class="detail-card">
                <div class="detail-icon"><i class="fas fa-phone"></i></div>
                <div class="detail-label">Phone</div>
                <div class="detail-value"><?php echo !empty($user_info['phone']) ? htmlspecialchars($user_info['phone']) : 'Not specified'; ?></div>
            </div>
            <div class="detail-card">
                <div class="detail-icon"><i class="fas fa-user-tag"></i></div>
                <div class="detail-label">Username</div>
                <div class="detail-value"><?php echo !empty($user_info['username']) ? htmlspecialchars($user_info['username']) : 'Not specified'; ?></div>
            </div>
            <div class="detail-card">
                <div class="detail-icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="detail-label">Created At</div>
                <div class="detail-value"><?php echo date('M d, Y', strtotime($user_info['created_at'])); ?></div>
            </div>
        </div>
    </section>
</body>
</html>