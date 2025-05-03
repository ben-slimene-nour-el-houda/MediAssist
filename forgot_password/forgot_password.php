<?php
// Start the session
session_start();

// Include database connection
require_once '../db_connect.php';

// Include PHPMailer
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Initialize variables
$error_message = "";
$success_message = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        // Check if the email exists in the users table
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
        if (!$stmt) {
            $error_message = "Database error. Please try again later.";
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                // Email exists, generate a reset token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + (60 * 60)); // Token expires in 1 hour

                // Store the token in the password_resets table
                $insert_stmt = $mysqli->prepare("INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?)");
                if (!$insert_stmt) {
                    $error_message = "Database error. Please try again later.";
                } else {
                    $insert_stmt->bind_param("sss", $email, $token, $expires);
                    $insert_stmt->execute();
                    $insert_stmt->close();

                    // Prepare the reset email
                    $reset_link = "http://localhost:8080/medicament/forgot_password/reset_password.php?token=" . urlencode($token); // For local testing
                    $subject = "MediAssist - Password Reset Request";
                    $message = "
                        <html>
                        <body style='font-family: Arial, sans-serif; color: #333;'>
                            <h2 style='color: #26a69a;'>MediAssist Password Reset</h2>
                            <p>You requested a password reset for your MediAssist account. Click the button below to reset your password:</p>
                            <p style='margin: 20px 0;'>
                                <a href='$reset_link' style='background: #26a69a; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Reset Password</a>
                            </p>
                            <p>This link will expire in 1 hour. If you did not request this, please ignore this email.</p>
                            <p>Best regards,<br>MediAssist Team</p>
                        </body>
                        </html>";

                    // Send the reset email using PHPMailer
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = "smtp.gmail.com"; // Remplacez par votre hôte SMTP
                        $mail->SMTPAuth = true;
                        $mail->Username = "bsnour446@gmail.com"; // Remplacez par votre e-mail
                        $mail->Password = "rhfl miqm myiq tofy"; // Remplacez par votre mot de passe
                        $mail->SMTPSecure = "tls";
                        $mail->Port = 587;

                        $mail->isHTML(true);
                        $mail->From = "noreply@yourwebsite.com";
                        $mail->FromName = "AllPHPTricks";
                        $mail->Sender = "noreply@yourwebsite.com";
                        $mail->Subject = $subject;
                        $mail->Body = $message;
                        $mail->addAddress($email);


                        $mail->send();
                        $success_message = "A password reset link has been sent to your email address.";
                    } catch (Exception $e) {
                        $error_message = "Failed to send the reset email. Please try again later.";
                        error_log("PHPMailer Error: " . $mail->ErrorInfo); // Log to error log
                    }
                }
            } else {
                // Email doesn't exist, show a generic message to prevent email enumeration
                $success_message = "If an account exists with that email, a password reset link has been sent.";
            }
            $stmt->close();
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
    <title>MediAssist - Forgot Password</title>
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

        .forgot-password-card {
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
            right: 15px;
            top: 42px;
            color: #90a4ae;
        }

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
        <div class="forgot-password-card">
            <div class="medical-pulse"></div>

            <div class="logo">
                <div class="logo-img">
                    <i class="fas fa-heartbeat" style="font-size: 32px; color: #26a69a;"></i>
                    <i class="fas fa-plus"
                        style="font-size: 16px; color: #4dd0e1; position: relative; top: -5px; left: 2px;"></i>
                </div>
            </div>

            <h1>Forgot Password</h1>
            <p class="description">Enter your email address and we'll send you a link to reset your password.</p>

            <?php if ($error_message): ?>
                <div class="error-message active"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <?php if ($success_message): ?>
                <div class="success-message active"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <form id="forgotPasswordForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    <span class="input-icon"><i class="fas fa-envelope"></i></span>
                    <div class="error-message" id="email-error"></div>
                </div>

                <button type="submit" class="btn">
                    <i class="fas fa-paper-plane" style="margin-right: 8px;"></i>Send Reset Link
                </button>
            </form>

            <div class="links">
                <p>Back to <a href="../login/login.php"><i class="fas fa-sign-in-alt" style="font-size: 12px; margin-right: 4px;"></i>Login</a></p>
                <p>Don't have an account? <a href="../signup/signup.php"><i class="fas fa-user-plus" style="font-size: 12px; margin-right: 4px;"></i>Register</a></p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('forgotPasswordForm').addEventListener('submit', function(event) {
            const email = document.getElementById('email').value;
            const emailError = document.getElementById('email-error');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!emailRegex.test(email)) {
                event.preventDefault();
                emailError.textContent = 'Please enter a valid email address.';
                emailError.classList.add('active');
            } else {
                emailError.classList.remove('active');
            }
        });
    </script>
</body>
</html>