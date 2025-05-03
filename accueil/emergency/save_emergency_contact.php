<?php
ob_start();
header('Content-Type: application/json');

// Include database connection and session configuration
require_once '../../db_connect.php';
require_once '../session_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$user_id = $_SESSION['user_id'];
$contact_id = isset($_POST['contact_id']) && !empty($_POST['contact_id']) ? (int)$_POST['contact_id'] : null;
$contact_name = trim($_POST['contact_name'] ?? '');
$phone_number = trim($_POST['phone_number'] ?? '');
$relationship = trim($_POST['relationship'] ?? '');

// Validate input
if (empty($contact_name) || empty($phone_number) || empty($relationship)) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}



try {
    if ($contact_id) {
        // Update existing contact
        $stmt = $mysqli->prepare("UPDATE emergency_contacts SET contact_name = ?, phone_number = ?, relationship = ? WHERE id = ? AND user_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $mysqli->error);
        }
        $stmt->bind_param("sssii", $contact_name, $phone_number, $relationship, $contact_id, $user_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            ob_clean();
            echo json_encode(['success' => true, 'message' => 'Contact updated successfully']);
        } else {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'No contact found or no changes made']);
        }
        $stmt->close();
    } else {
        // Add new contact
        $stmt = $mysqli->prepare("INSERT INTO emergency_contacts (user_id, contact_name, phone_number, relationship) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $mysqli->error);
        }
        $stmt->bind_param("isss", $user_id, $contact_name, $phone_number, $relationship);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            ob_clean();
            echo json_encode(['success' => true, 'message' => 'Contact added successfully']);
        } else {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Failed to add contact']);
        }
        $stmt->close();
    }
} catch (Exception $e) {
    error_log("Error in save_emergency_contact.php: " . $e->getMessage());
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

$mysqli->close();
ob_end_flush();
?>