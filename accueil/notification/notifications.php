<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediAssist - Notifications</title>
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
            animation: pulse 2s infinite;
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

        /* Notification Section */
        .notification-section {
            padding: 0 2rem 2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .notification-header h2 {
            color: var(--dark);
            font-size: 1.8rem;
        }

        .clear-all-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .clear-all-btn:hover {
            background-color: var(--primary-dark);
        }

        .notification-list {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .notification-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #e0e0e0;
            transition: background-color 0.3s ease;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item.unread {
            background-color: rgba(38, 166, 154, 0.05);
        }

        .notification-item:hover {
            background-color: var(--light);
        }

        .notification-icon {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 1rem;
            color: white;
            font-size: 1.2rem;
        }

        .notification-icon.appointment {
            background-color: var(--primary);
        }

        .notification-icon.medication {
            background-color: var(--warning);
        }

        .notification-icon.prescription {
            background-color: var(--success);
        }

        .notification-details {
            flex-grow: 1;
        }

        .notification-title {
            font-weight: bold;
            margin-bottom: 0.3rem;
            color: var(--text);
        }

        .notification-time {
            font-size: 0.85rem;
            color: #666;
        }

        .notification-action {
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

        .mark-read-btn {
            background-color: var(--gray);
            color: var(--text);
        }

        .mark-read-btn:hover {
            background-color: #e0e0e0;
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

            .notification-section {
                padding: 0 1rem 1.5rem;
            }

            .notification-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
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
                <a href="../accueil.php"><i class="fas fa-home"></i> Home</a>
                <a href="../medications/medication.php"><i class="fas fa-pills"></i> Medications</a>
                <a href="../apointement/appointment.php"><i class="fas fa-calendar-alt"></i> Schedule</a>
                <a href="../prescription/prescription.php"><i class="fas fa-file-prescription"></i> Prescriptions</a>
            </div>

            <div class="notification-bell">
                <i class="fas fa-bell" style="color: white; font-size: 18px;"></i>
                <div class="notification-badge">3</div>
            </div>

            <div class="profile-container">
                <div class="profile-pic">JD</div>
                <div class="profile-info">
                    <h4>John Doe</h4>
                </div>
                <div class="profile-dropdown" id="profileDropdown">
                    <a href="../profil/profil.php" class="dropdown-item">
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

    <!-- Notification Section -->
    <section class="notification-section">
        <div class="notification-header">
            <h2>Your Notifications</h2>
            <button class="clear-all-btn">
                <i class="fas fa-trash-alt"></i>
                <span>Clear All</span>
            </button>
        </div>

        <div class="notification-list">
            <div class="notification-item unread">
                <div class="notification-icon appointment">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="notification-details">
                    <div class="notification-title">Upcoming Appointment Reminder</div>
                    <div class="notification-time">Your dental check-up is scheduled for tomorrow at 2:00 PM</div>
                </div>
                <div class="notification-action">
                    <button class="action-btn mark-read-btn">Mark as Read</button>
                </div>
            </div>

            <div class="notification-item unread">
                <div class="notification-icon medication">
                    <i class="fas fa-pills"></i>
                </div>
                <div class="notification-details">
                    <div class="notification-title">Medication Reminder</div>
                    <div class="notification-time">Time to take your 10mg Aspirin dose - 30 minutes ago</div>
                </div>
                <div class="notification-action">
                    <button class="action-btn">Take Now</button>
                </div>
            </div>

            <div class="notification-item">
                <div class="notification-icon prescription">
                    <i class="fas fa-file-medical"></i>
                </div>
                <div class="notification-details">
                    <div class="notification-title">New Prescription Added</div>
                    <div class --gray);">
                        <div class="notification-time">Dr. Smith added a new prescription - 2 hours ago</div>
                    </div>
                    <div class="notification-action">
                        <button class="action-btn">View Details</button>
                    </div>
                </div>

                <div class="notification-item">
                    <div class="notification-icon appointment">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="notification-details">
                        <div class="notification-title">Appointment Confirmation</div>
                        <div class="notification-time">Your appointment with Dr. Johnson is confirmed for next week</div>
                    </div>
                    <div class="notification-action">
                        <button class="action-btn">Add to Calendar</button>
                    </div>
                </div>
            </div>
        </section>

        <script>
            // Profile dropdown toggle
            const profileContainer = document.querySelector('.profile-container');
            const profileDropdown = document.getElementById('profileDropdown');

            profileContainer.addEventListener('click', function(event) {
                profileDropdown.classList.toggle('active');
            });

            const dropdownItems = document.querySelectorAll('.dropdown-item');
            dropdownItems.forEach(item => {
                item.addEventListener('click', function(event) {
                    event.stopPropagation();
                });
            });

            document.addEventListener('click', function(event) {
                if (!profileContainer.contains(event.target)) {
                    profileDropdown.classList.remove('active');
                }
            });

            // Mobile menu toggle
            document.querySelector('.mobile-menu-toggle').addEventListener('click', function() {
                document.querySelector('.nav-links').classList.toggle('active');
            });

            // Clear all notifications
            document.querySelector('.clear-all-btn').addEventListener('click', function() {
                document.querySelector('.notification-list').innerHTML = '<p>No new notifications</p>';
            });

            // Mark as read functionality
            document.querySelectorAll('.mark-read-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const notificationItem = btn.closest('.notification-item');
                    notificationItem.classList.remove('unread');
                    btn.remove();
                });
            });
        </script>
    </body>
</html>