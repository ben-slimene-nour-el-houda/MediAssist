<?php
session_start();
require_once '../session_config.php';
require_once '../../db_connect.php';

redirect_if_not_logged_in('../../login/login.php');

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        error_log("CSRF token validation failed.");
        $_SESSION['message'] = 'CSRF token validation failed.';
        $_SESSION['message_type'] = 'error';
        header('Location: profil.php');
        exit;
    }

    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    $age = filter_input(INPUT_POST, 'age', FILTER_SANITIZE_NUMBER_INT);
    $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);

    // Check if email already exists (excluding current user)
    $check_stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    if (!$check_stmt) {
        error_log("Prepare failed for email check: " . $mysqli->error);
        $_SESSION['message'] = 'Database error occurred.';
        $_SESSION['message_type'] = 'error';
        header('Location: profil.php');
        exit;
    }
    $check_stmt->bind_param("si", $email, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        error_log("Duplicate email attempted: $email");
        $_SESSION['message'] = 'This email is already registered. Please use a different email.';
        $_SESSION['message_type'] = 'error';
        header('Location: profil.php');
        $check_stmt->close();
        exit;
    }
    $check_stmt->close();

    // Handle profile photo upload
    $profile_photo = null;
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_file_size = 5 * 1024 * 1024; // 5MB
        $file_type = $_FILES['profile_photo']['type'];
        $file_size = $_FILES['profile_photo']['size'];

        // Validate file type and size
        if (!in_array($file_type, $allowed_types)) {
            error_log("Invalid file type: $file_type");
            $_SESSION['message'] = 'Only JPEG, PNG, and GIF files are allowed.';
            $_SESSION['message_type'] = 'error';
            header('Location: profil.php');
            exit;
        }
        if ($file_size > $max_file_size) {
            error_log("File size too large: $file_size bytes");
            $_SESSION['message'] = 'Profile photo must be less than 5MB.';
            $_SESSION['message_type'] = 'error';
            header('Location: profil.php');
            exit;
        }

        $upload_dir = '../../signup/Uploads/';
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                error_log("Failed to create directory: $upload_dir");
                $_SESSION['message'] = 'Failed to create upload directory.';
                $_SESSION['message_type'] = 'error';
                header('Location: profil.php');
                exit;
            }
        }
        $photo_name = uniqid() . '_' . basename($_FILES['profile_photo']['name']);
        $photo_path = $upload_dir . $photo_name;

        // Move the uploaded file
        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $photo_path)) {
            $profile_photo = 'Uploads/' . $photo_name;
            error_log("Profile photo uploaded successfully: $photo_path");
        } else {
            error_log("Failed to move uploaded file to: $photo_path");
            $_SESSION['message'] = 'Failed to upload profile photo.';
            $_SESSION['message_type'] = 'error';
            header('Location: profil.php');
            exit;
        }
    }

    // Prepare update query
    $query = "UPDATE users SET name = ?, email = ?, age = ?, gender = ?, phone = ?, username = ?";
    $types = "ssisss";
    $params = [$name, $email, $age, $gender, $phone, $username];

    if ($password) {
        $query .= ", password = ?";
        $types .= "s";
        $params[] = $password;
    }

    if ($profile_photo) {
        $query .= ", profile_photo = ?";
        $types .= "s";
        $params[] = $profile_photo;
    }

    $query .= " WHERE id = ?";
    $types .= "i";
    $params[] = $user_id;

    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $mysqli->error);
        $_SESSION['message'] = 'Database error occurred.';
        $_SESSION['message_type'] = 'error';
        header('Location: profil.php');
        exit;
    }

    $stmt->bind_param($types, ...$params);

    try {
        if ($stmt->execute()) {
            error_log("Profile updated successfully for user ID: $user_id");
            $_SESSION['message'] = 'Profile updated successfully!';
            $_SESSION['message_type'] = 'success';
            header('Location: profil.php');
        } else {
            error_log("SQL execution failed: " . $stmt->error);
            $_SESSION['message'] = 'Failed to update profile. Please try again.';
            $_SESSION['message_type'] = 'error';
            header('Location: profil.php');
        }
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        error_log("Database update failed: " . $e->getMessage());
        $_SESSION['message'] = 'Failed to update profile. Please try again or use a different email.';
        $_SESSION['message_type'] = 'error';
        header('Location: profil.php');
        $stmt->close();
    }
    $mysqli->close();
    exit;
}
?>