<?php
ob_start();
header('Content-Type: application/json');

// Include database connection and session configuration
require_once '../../db_connect.php';
require_once '../session_config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get the contact ID to delete
$input = json_decode(file_get_contents('php://input'), true);
$contact_id = isset($input['id']) ? (int)$input['id'] : null;

if (!$contact_id) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Missing contact ID']);
    exit;
}

// Prepare the delete query
$stmt = $mysqli->prepare("DELETE FROM emergency_contacts WHERE id = ? AND user_id = ?");
if (!$stmt) {
    error_log("Prepare failed: " . $mysqli->error);
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Failed to prepare the statement']);
    exit;
}
$stmt->bind_param("ii", $contact_id, $user_id);

// Execute the query
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        ob_clean();
        echo json_encode(['success' => true, 'message' => 'Contact successfully deleted']);
    } else {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Contact not found or unauthorized']);
    }
} else {
    error_log("Execute failed: " . $stmt->error);
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Error during deletion']);
}

$stmt->close();
$mysqli->close();
ob_end_flush();
?>