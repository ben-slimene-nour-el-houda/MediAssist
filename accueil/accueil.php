<?php
// Inclure la configuration de session et connexion DB
require_once 'session_config.php';
require_once '../db_connect.php';

// Rediriger vers la page de connexion si non connecté
redirect_if_not_logged_in('../login/login.php');

// Récupérer les informations de l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$user_info = [];

$stmt = $mysqli->prepare("SELECT name, email, profile_photo FROM users WHERE id = ?");
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
    header("Location: ../login/login.php?error=invalid_user");
    exit();
}
$stmt->close();

// Préparer les données pour l'affichage
$profile_name = $user_info['name'];
$names = explode(' ', $profile_name);
$profile_initials = substr($names[0], 0, 1);
if (count($names) > 1) {
    $profile_initials .= substr(end($names), 0, 1);
}



// Récupérer les statistiques
// 1. Médicaments actifs
$medication_count = 0;
$stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM medications WHERE user_id = ? AND is_active = 1");
if (!$stmt) {
    error_log("Prepare failed: " . $mysqli->error);
    die("An error occurred. Please try again later.");
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $medication_count = $row['count'];
}
$stmt->close();

// 2. Rendez-vous à venir
$appointment_count = 0;
$stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM appointments WHERE user_id = ? AND date_time >= NOW()");
if (!$stmt) {
    error_log("Prepare failed: " . $mysqli->error);
    die("An error occurred. Please try again later.");
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $appointment_count = $row['count'];
}
$stmt->close();

// 3. Prescriptions récentes
$prescription_count = 0;
$stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM prescriptions WHERE user_id = ? AND date_prescribed >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
if (!$stmt) {
    error_log("Prepare failed: " . $mysqli->error);
    die("An error occurred. Please try again later.");
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $prescription_count = $row['count'];
}
$stmt->close();

// Récupérer les rendez-vous à venir
$upcoming_appointments = [];
$stmt = $mysqli->prepare("SELECT id, title, date_time, location, description 
                         FROM appointments 
                         WHERE user_id = ? AND date_time >= NOW() 
                         ORDER BY date_time ASC LIMIT 3");
if (!$stmt) {
    error_log("Prepare failed: " . $mysqli->error);
    die("An error occurred. Please try again later.");
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($appointment = $result->fetch_assoc()) {
    $upcoming_appointments[] = $appointment;
}
$stmt->close();

// Récupérer les médicaments actuels
$current_medications = [];
$stmt = $mysqli->prepare("SELECT id, name, dosage, frequency, instructions 
                         FROM medications 
                         WHERE user_id = ? AND is_active = 1
                         ORDER BY name ASC LIMIT 5");
if (!$stmt) {
    error_log("Prepare failed: " . $mysqli->error);
    die("An error occurred. Please try again later.");
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($medication = $result->fetch_assoc()) {
    $current_medications[] = $medication;
}
$stmt->close();

// Récupérer les prescriptions récentes
$recent_prescriptions = [];
$stmt = $mysqli->prepare("SELECT id, doctor_name, date_prescribed, notes
                         FROM prescriptions 
                         WHERE user_id = ? AND date_prescribed >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                         ORDER BY date_prescribed DESC LIMIT 3");
if (!$stmt) {
    error_log("Prepare failed: " . $mysqli->error);
    die("An error occurred. Please try again later.");
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($prescription = $result->fetch_assoc()) {
    $recent_prescriptions[] = $prescription;
}
$stmt->close();

// Liste de conseils santé (statique pour cet exemple)
$health_tips = [
    [
        'title' => 'Stay Hydrated',
        'description' => 'Drink at least 8 glasses of water daily to keep your body hydrated and support overall health.',
        'icon' => 'fas fa-tint'
    ],
    [
        'title' => 'Regular Exercise',
        'description' => 'Aim for at least 30 minutes of moderate exercise most days of the week to boost your mood and health.',
        'icon' => 'fas fa-running'
    ],
    [
        'title' => 'Healthy Diet',
        'description' => 'Incorporate more fruits, vegetables, and whole grains into your meals for better nutrition.',
        'icon' => 'fas fa-apple-alt'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediAssist - Your Health Companion</title>
    <link rel="icon" href="../logo.png" type="image/png">
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
            --success: #4CAF50;
            --warning: #FF9800;
            --danger: #F44336;
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

        /* Header Styles */
        header {
            position: relative;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 1rem 0 3rem;
            margin-bottom: 2rem;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
            color: white;
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
            animation: pulse 2s infinite; /* Added pulse animation */
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            transition: all 0.3s ease;
        }

        .nav-links a:hover {
            color: var(--light);
        }

        .notification-bell {
            position: relative;
            cursor: pointer;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--danger);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 12px;
        }

        .profile-container {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            cursor: pointer;
            position: relative;
        }

        .profile-pic {
            width: 40px;
            height: 40px;
            background-color: var(--light);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--primary);
            font-weight: bold;
        }

        .profile-info h4 {
            color: white;
            font-size: 0.9rem;
        }

        .wave {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 50px;
            background: var(--gray);
            clip-path: polygon(0 100%, 100% 100%, 100% 0, 0 100%, 0 0);
        }

        /* Hero Section */
        .hero {
            padding: 0 2rem 2rem;
            text-align: center;
        }

        .hero h1 {
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .hero p {
            color: #666;
            margin-bottom: 2rem;
        }

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            background-color: rgba(38, 166, 154, 0.1);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 1rem;
        }

        .stat-icon i {
            color: var(--primary);
            font-size: 1.5rem;
        }

        .stat-card h3 {
            font-size: 1.8rem;
            margin-bottom: 0.3rem;
            color: var(--dark);
        }

        .stat-card p {
            color: #666;
            font-size: 0.9rem;
        }

        /* Health Dashboard */
        .health-dashboard {
            padding: 0 2rem 2rem;
        }

        .health-panels {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 992px) {
            .health-panels {
                grid-template-columns: 1fr;
            }
        }

        .health-tips,
        .upcoming-events,
        .medication-list,
        .prescription-list {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .tips-header,
        .events-header,
        .medication-header,
        .prescription-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .view-all {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .view-all:hover {
            color: var(--primary-dark);
        }

        .tip-item {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            background-color: var(--gray);
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .tip-icon {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-color: rgba(38, 166, 154, 0.1);
            color: var(--primary);
        }

        .tip-icon i {
            font-size: 1.2rem;
        }

        .tip-details {
            flex-grow: 1;
        }

        .tip-title {
            font-weight: bold;
            margin-bottom: 0.3rem;
        }

        .tip-description {
            font-size: 0.85rem;
            color: #666;
        }

        .event-item {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            background-color: var(--gray);
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .event-date {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 60px;
            padding: 0.5rem;
            background-color: white;
            border-radius: 8px;
            text-align: center;
        }

        .event-month {
            font-size: 0.8rem;
            font-weight: bold;
            color: var(--primary);
            text-transform: uppercase;
        }

        .event-day {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--text);
        }

        .event-details {
            display: flex;
            flex-direction: column;
            justify-content: center;
            flex-grow: 1;
        }

        .event-title {
            font-weight: bold;
            margin-bottom: 0.3rem;
        }

        .event-time,
        .event-location {
            font-size: 0.85rem;
            color: #666;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .medication-item,
        .prescription-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background-color: var(--gray);
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .medication-icon,
        .prescription-icon {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-color: rgba(38, 166, 154, 0.1);
            color: var(--primary);
            margin-right: 1rem;
        }

        .medication-icon i,
        .prescription-icon i {
            font-size: 1.2rem;
        }

        .medication-details,
        .prescription-details {
            flex-grow: 1;
        }

        .medication-name,
        .prescription-name {
            font-weight: bold;
            margin-bottom: 0.3rem;
        }

        .medication-info,
        .prescription-info {
            font-size: 0.85rem;
            color: #666;
        }

        .medication-action,
        .prescription-action {
            margin-left: 1rem;
        }

        .action-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background-color: var(--primary-dark);
        }

        .emergency-card {
            background: linear-gradient(135deg, #ff5252, #f44336);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .emergency-title {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .emergency-title i {
            font-size: 1.5rem;
        }

        .emergency-btn {
            background-color: white;
            color: #f44336;
            border: none;
            border-radius: 5px;
            padding: 0.8rem 1rem;
            font-weight: bold;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .emergency-btn:hover {
            background-color: #f0f0f0;
        }

        /* Profile Dropdown */
        .profile-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 200px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 1rem;
            z-index: 1000;
            display: none;
        }

        .profile-dropdown.active {
            display: block;
        }

        .dropdown-item {
            padding: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            color: var(--text);
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 5px;
        }

        .dropdown-item:hover {
            background-color: var(--light);
        }

        .dropdown-item i {
            color: var(--primary);
            width: 20px;
            text-align: center;
        }

        .dropdown-divider {
            height: 1px;
            background-color: #e0e0e0;
            margin: 0.5rem 0;
        }

        /* Mobile Menu */
        .mobile-menu-toggle {
            display: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* Pulse Animation */
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .navbar {
                padding: 0 1rem;
            }

            .mobile-menu-toggle {
                display: block;
            }

            .nav-links {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 60px;
                left: 0;
                width: 100%;
                background: var(--primary-dark);
                padding: 1rem;
            }

            .nav-links.active {
                display: flex;
            }

            .hero,
            .health-dashboard {
                padding: 0 1rem 1.5rem;
            }
        }
    </style>
</head>

<body>
    <!-- Header Section -->
    <header>
        <div class="navbar">
            <div class="logo">
                <i class="fas fa-heartbeat"></i>
                <span>MediAssist</span>
            </div>

            <div class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </div>

            <div class="nav-links">
                <a href="accueil.php"><i class="fas fa-home"></i> Home</a>
                <a href="medications/medication.php"><i class="fas fa-pills"></i> Medications</a>
                <a href="apointement/appointment.php"><i class="fas fa-calendar-alt"></i> Schedule</a>
                <a href="prescription/prescription.php"><i class="fas fa-file-prescription"></i> Prescriptions</a>
            </div>

            <div class="notification-bell">
                <i class="fas fa-bell" style="color: white; font-size: 18px;"></i>
                <!-- Dans votre navigation dans header.php -->
                <div class="notification-badge">
                    <a href="notification/notifications.php">
                        <i class="fas fa-bell"></i>
                        <?php if (isset($unreadNotificationCount) && $unreadNotificationCount > 0) : ?>
                        <span id="notification-counter"><?php echo $unreadNotificationCount; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>

            <div class="profile-container">
                <div class="profile-pic">
                    <?php echo $profile_initials; ?>
                </div>
                <div class="profile-info">
                    <h4>
                        <?php echo htmlspecialchars($profile_name); ?>
                    </h4>
                </div>
                <div class="profile-dropdown" id="profileDropdown">
                    <a href="profil/profil.php" class="dropdown-item">
                        <i class="fas fa-user"></i>
                        <span>My Profile</span>
                    </a>
                    
                    <div class="dropdown-divider"></div>
                    <a href="../login/logout.php" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="wave"></div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <h1>Welcome back, <?php echo htmlspecialchars($profile_name); ?>!</h1>
        <p>Track your health, manage medications, and stay on top of your appointments all in one place.</p>

        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-pills"></i>
                </div>
                <h3><?php echo $medication_count; ?></h3>
                <p>Active Medications</p>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3><?php echo $appointment_count; ?></h3>
                <p>Upcoming Appointments</p>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-file-medical"></i>
                </div>
                <h3><?php echo $prescription_count; ?></h3>
                <p>Recent Prescriptions</p>
            </div>
        </div>
    </section>

    <!-- Health Dashboard -->
    <section class="health-dashboard">
        <div class="health-panels">
            <div class="main-panel">
                <!-- Health Tips Section -->
                <div class="health-tips">
                    <div class="tips-header">
                        <h3>Daily Health Tips</h3>
                        <a href="#" class="view-all">
                            <span>More Tips</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>

                    <?php foreach ($health_tips as $tip): ?>
                        <div class="tip-item">
                            <div class="tip-icon">
                                <i class="<?php echo $tip['icon']; ?>"></i>
                            </div>
                            <div class="tip-details">
                                <div class="tip-title">
                                    <?php echo htmlspecialchars($tip['title']); ?>
                                </div>
                                <div class="tip-description">
                                    <?php echo htmlspecialchars($tip['description']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Medication List -->
                <div class="medication-list">
                    <div class="medication-header">
                        <h3>Current Medications</h3>
                        <a href="medications/medication.php" class="view-all">
                            <span>Manage all</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>

                    <?php if (count($current_medications) > 0): ?>
                        <?php foreach ($current_medications as $medication): ?>
                            <div class="medication-item">
                                <div class="medication-icon">
                                    <i class="fas fa-capsules"></i>
                                </div>
                                <div class="medication-details">
                                    <div class="medication-name">
                                        <?php echo htmlspecialchars($medication['name']); ?>
                                    </div>
                                    <div class="medication-info">
                                        <?php echo htmlspecialchars($medication['dosage']); ?> -
                                        <?php echo htmlspecialchars($medication['frequency']); ?>
                                    </div>
                                </div>
                                <div class="medication-action">
                                    <button class="action-btn" onclick="markDoseTaken(<?php echo $medication['id']; ?>)">Take</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No active medications</p>
                    <?php endif; ?>
                </div>

                <!-- Prescription List -->
                <div class="prescription-list">
                    <div class="prescription-header">
                        <h3>Recent Prescriptions</h3>
                        <a href="prescription/prescription.php" class="view-all">
                            <span>View all</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>

                    <?php if (count($recent_prescriptions) > 0): ?>
                        <?php foreach ($recent_prescriptions as $prescription): ?>
                            <?php
                            $date = new DateTime($prescription['date_prescribed']);
                            $formatted_date = $date->format('M d, Y');
                            ?>
                            <div class="prescription-item">
                                <div class="prescription-icon">
                                    <i class="fas fa-file-medical"></i>
                                </div>
                                <div class="prescription-details">
                                    <div class="prescription-name">
                                        <?php echo htmlspecialchars($prescription['notes'] ?: 'Prescription'); ?>
                                    </div>
                                    <div class="prescription-info">
                                        Dr. <?php echo htmlspecialchars($prescription['doctor_name']); ?> -
                                        <?php echo $formatted_date; ?>
                                    </div>
                                </div>
                                <div class="prescription-action">
                                    <button class="action-btn">View</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No recent prescriptions</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="side-panel">
                <!-- Emergency Card -->
                <div class="emergency-card">
                    <div class="emergency-title">
                        <i class="fas fa-phone-alt"></i>
                        <h3>Emergency Contact</h3>
                    </div>
                    <p style="margin-bottom: 1rem;">In case of emergency, call immediately</p>
                    <button class="emergency-btn" onclick="window.location.href='emergency/emergency.php'">
                        <i class="fas fa-phone-alt"></i>
                        <span>Emergency Hotline</span>
                    </button>
                </div>

                <!-- Upcoming Events -->
                <div class="upcoming-events">
                    <div class="events-header">
                        <h3>Upcoming Appointments</h3>
                        <a href="apointement/appointment.php" class="view-all">
                            <span>View all</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>

                    <?php if (count($upcoming_appointments) > 0): ?>
                        <?php foreach ($upcoming_appointments as $appointment): ?>
                            <?php
                            $date = new DateTime($appointment['date_time']);
                            $month = $date->format('M');
                            $day = $date->format('d');
                            $time = $date->format('h:i A');
                            ?>
                            <div class="event-item">
                                <div class="event-date">
                                    <div class="event-month">
                                        <?php echo $month; ?>
                                    </div>
                                    <div class="event-day">
                                        <?php echo $day; ?>
                                    </div>
                                </div>
                                <div class="event-details">
                                    <div class="event-title">
                                        <?php echo htmlspecialchars($appointment['title']); ?>
                                    </div>
                                    <div class="event-time">
                                        <i class="far fa-clock"></i>
                                        <span><?php echo $time; ?></span>
                                    </div>
                                    <div class="event-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo htmlspecialchars($appointment['location']); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No upcoming appointments</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Profile dropdown toggle
    const profileContainer = document.querySelector('.profile-container');
    const profileDropdown = document.getElementById('profileDropdown');

    // Toggle dropdown when clicking the profile container
    profileContainer.addEventListener('click', function(event) {
        profileDropdown.classList.toggle('active');
    });

    // Prevent clicks on dropdown items from toggling the dropdown again
    const dropdownItems = document.querySelectorAll('.dropdown-item');
    dropdownItems.forEach(item => {
        item.addEventListener('click', function(event) {
            event.stopPropagation();
        });
    });

    // Close the dropdown when clicking outside
    document.addEventListener('click', function(event) {
        if (!profileContainer.contains(event.target)) {
            profileDropdown.classList.remove('active');
        }
    });

    // Mobile menu toggle
    document.querySelector('.mobile-menu-toggle').addEventListener('click', function() {
        document.querySelector('.nav-links').classList.toggle('active');
    });

    // Function to mark dose as taken
    function markDoseTaken(medicationId) {
        fetch('log_dose.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                medication_id: medicationId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Dose marked as taken!');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
    </script>
</body>

</html>