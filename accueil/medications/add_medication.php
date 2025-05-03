<?php
require_once '../session_config.php';
require_once '../db_connect.php';

redirect_if_not_logged_in('../../login/login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $dosage = trim($_POST['dosage']);
    $frequency = trim($_POST['frequency']);
    $time_of_day = trim($_POST['time_of_day']);
    $start_date = trim($_POST['start_date']);
    $end_date = trim($_POST['end_date']);
    $instructions = trim($_POST['instructions']);
    $is_active = 1;

    // Validate input
    if (empty($name) || empty($dosage) || empty($frequency) || empty($time_of_day) || empty($start_date) || empty($end_date)) {
        header("Location: medication.php?error=" . urlencode("All required fields must be filled."));
        exit();
    }

    // Validate dates
    if (strtotime($start_date) > strtotime($end_date)) {
        header("Location: medication.php?error=" . urlencode("End date must be after start date."));
        exit();
    }

    // Insert into database
    $stmt = $mysqli->prepare("INSERT INTO medications (user_id, name, dosage, frequency, time_of_day, start_date, end_date, instructions, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        error_log("Prepare failed: " . $mysqli->error);
        header("Location: medication.php?error=" . urlencode("An error occurred. Please try again."));
        exit();
    }

    $stmt->bind_param("isssssssi", $user_id, $name, $dosage, $frequency, $time_of_day, $start_date, $end_date, $instructions, $is_active);
    if ($stmt->execute()) {
        header("Location: medication.php?success=" . urlencode("Medication added successfully!"));
    } else {
        error_log("Execute failed: " . $stmt->error);
        header("Location: medication.php?error=" . urlencode("Failed to add medication. Please try again."));
    }
    $stmt->close();
} else {
    header("Location: medication.php");
}
exit();