<?php
require_once '../session_config.php';
require_once '../db_connect.php';

redirect_if_not_logged_in('../../login/login.php');

if (isset($_GET['med_id'])) {
    $med_id = intval($_GET['med_id']);
    $user_id = $_SESSION['user_id'];

    // Verify the medication belongs to the user
    $stmt = $mysqli->prepare("SELECT id FROM medications WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $med_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Delete the medication
        $delete_stmt = $mysqli->prepare("DELETE FROM medications WHERE id = ?");
        $delete_stmt->bind_param("i", $med_id);
        if ($delete_stmt->execute()) {
            header("Location: medication.php?success=" . urlencode("Medication deleted successfully!"));
        } else {
            header("Location: medication.php?error=" . urlencode("Failed to delete medication."));
        }
        $delete_stmt->close();
    } else {
        header("Location: medication.php?error=" . urlencode("Invalid medication."));
    }
    $stmt->close();
} else {
    header("Location: medication.php");
}
exit();