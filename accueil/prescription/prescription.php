<?php
require_once '../session_config.php';
require_once '../../db_connect.php';

// Sanitize input
function sanitize_input($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Format date
function formatDate($date, $format = 'M d, Y') {
    if (empty($date)) return 'N/A';
    try {
        $dateObj = new DateTime($date);
        return $dateObj->format($format);
    } catch (Exception $e) {
        error_log("Invalid date format: $date");
        return 'Invalid Date';
    }
}

// Execute prepared statement
function execute_query($mysqli, $query, $params = [], $types = '') {
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $mysqli->error);
        throw new Exception("Database error occurred.");
    }
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        throw new Exception("Query execution failed.");
    }
    return $stmt;
}

// CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Redirect if not logged in
redirect_if_not_logged_in('../../login/login.php');

$user_id = $_SESSION['user_id'];
$success_message = "";
$error_message = "";

// Fetch prescriptions
$prescriptions = [];
try {
    $stmt = execute_query($mysqli, "
        SELECT id, medication_id, doctor_name, medication_name, date_prescribed, expires_on, notes, prescription_pdf, dosage, quantity, instructions
        FROM prescriptions
        WHERE user_id = ?
        ORDER BY date_prescribed DESC", [$user_id], 'i');
    $result = $stmt->get_result();
    while ($prescription = $result->fetch_assoc()) {
        $prescriptions[] = array_map('sanitize_input', $prescription);
    }
    $stmt->close();
} catch (Exception $e) {
    $error_message = "Error fetching prescriptions: " . $e->getMessage();
}

// Fetch medications
$medications = [];
try {
    $stmt = execute_query($mysqli, "SELECT id, name, dosage FROM medications WHERE user_id = ? ORDER BY name ASC", [$user_id], 'i');
    $result = $stmt->get_result();
    while ($medication = $result->fetch_assoc()) {
        $medications[] = array_map('sanitize_input', $medication);
    }
    $stmt->close();
} catch (Exception $e) {
    $error_message = "Error fetching medications: " . $e->getMessage();
}

/// Handle prescription addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_prescription'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_message = "Invalid CSRF token.";
    } else {
        $upload_mode = $_POST['upload_mode'] ?? 'manual';
        $medication_id = filter_var($_POST['medication_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
        $doctor_name = sanitize_input($_POST['doctor_name'] ?? '');
        $medication_name = sanitize_input($_POST['medication_name'] ?? '');
        $date_prescribed = $_POST['date_prescribed'] ?? null;
        $expires_on = $_POST['expires_on'] ?? null;
        $notes = sanitize_input($_POST['notes'] ?? '');
        $dosage = sanitize_input($_POST['dosage'] ?? '');
        $quantity = sanitize_input($_POST['quantity'] ?? '');
        $instructions = sanitize_input($_POST['instructions'] ?? '');

        // Validate inputs
        $today = date('Y-m-d');
        if ($upload_mode === 'manual') {
            if (empty($medication_name) || empty($doctor_name) || empty($date_prescribed) || empty($dosage) || empty($quantity) || empty($instructions) || empty($notes)) {
                $error_message = "All fields are required for Manual Entry.";
            } elseif ($date_prescribed > $today) {
                $error_message = "Date prescribed cannot be in the future.";
            } elseif ($expires_on && $expires_on < $date_prescribed) {
                $error_message = "Expiration date cannot be before the prescribed date.";
            }
        } else {
            if (empty($doctor_name) || empty($date_prescribed) || empty($expires_on)) {
                $error_message = "Doctor name, date prescribed, and expiration date are required for PDF Upload.";
            } elseif (!isset($_FILES['prescription_pdf']) || $_FILES['prescription_pdf']['error'] != 0) {
                $error_message = "PDF file is required for PDF Upload mode.";
            } elseif ($date_prescribed > $today) {
                $error_message = "Date prescribed cannot be in the future.";
            } elseif ($expires_on && $expires_on < $date_prescribed) {
                $error_message = "Expiration date cannot be before the prescribed date.";
            }
        }

        // Handle PDF upload
        $pdf_path = null;
        if ($upload_mode === 'pdf' && isset($_FILES['prescription_pdf']) && $_FILES['prescription_pdf']['error'] == 0 && empty($error_message)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $file_type = finfo_file($finfo, $_FILES['prescription_pdf']['tmp_name']);
            finfo_close($finfo);
            $max_size = 5 * 1024 * 1024;

            if ($file_type !== 'application/pdf') {
                $error_message = "Only PDF files are allowed.";
            } elseif ($_FILES['prescription_pdf']['size'] > $max_size) {
                $error_message = "File is too large. Maximum size: 5 MB.";
            } else {
                $upload_dir = "../Uploads/prescriptions/";
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $file_name = $user_id . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.pdf';
                $target_file = $upload_dir . $file_name;
                if (move_uploaded_file($_FILES['prescription_pdf']['tmp_name'], $target_file)) {
                    $pdf_path = $file_name;
                } else {
                    $error_message = "Error uploading file.";
                }
            }
        }

        // Insert into database
        if (empty($error_message)) {
            try {
                $stmt = execute_query($mysqli, "
                    INSERT INTO prescriptions (user_id, medication_id, doctor_name, medication_name, date_prescribed, expires_on, notes, prescription_pdf, dosage, quantity, instructions)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$user_id, $medication_id, $doctor_name, $medication_name, $date_prescribed, $expires_on, $notes, $pdf_path, $dosage, $quantity, $instructions],
                    'iisssssssss');
                $stmt->close();
                $base_url = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                header("Location: $base_url/prescription.php?tab=prescriptions");
                exit;
            } catch (Exception $e) {
                $error_message = "Error adding prescription: " . $e->getMessage();
            }
        }
    }
}

// Handle prescription deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $prescription_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($prescription_id) {
        try {
            $stmt = execute_query($mysqli, "SELECT prescription_pdf FROM prescriptions WHERE id = ? AND user_id = ?", [$prescription_id, $user_id], 'ii');
            $result = $stmt->get_result();
            $pdf_file = $result->fetch_assoc()['prescription_pdf'] ?? null;
            $stmt->close();

            $stmt = execute_query($mysqli, "DELETE FROM prescriptions WHERE id = ? AND user_id = ?", [$prescription_id, $user_id], 'ii');
            $stmt->close();

            if ($pdf_file && file_exists("../Uploads/prescriptions/" . $pdf_file)) {
                unlink("../Uploads/prescriptions/" . $pdf_file);
            }
            $success_message = "Prescription deleted successfully!";
            $base_url = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            header("Location: $base_url/prescription.php?tab=prescriptions");
            exit;
        } catch (Exception $e) {
            $error_message = "Error deleting prescription: " . $e->getMessage();
        }
    }
}

// Handle calendar export
if (isset($_GET['action']) && $_GET['action'] == 'export_calendar' && isset($_GET['id'])) {
    $prescription_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($prescription_id) {
        try {
            $stmt = execute_query($mysqli, "SELECT medication_name, expires_on FROM prescriptions WHERE id = ? AND user_id = ?", [$prescription_id, $user_id], 'ii');
            $result = $stmt->get_result();
            $prescription = $result->fetch_assoc();
            $stmt->close();

            if ($prescription && !empty($prescription['expires_on'])) {
                $expire_date = new DateTime($prescription['expires_on']);
                $ics_content = "BEGIN:VCALENDAR\nVERSION:2.0\nBEGIN:VEVENT\n";
                $ics_content .= "DTSTART:" . $expire_date->format('Ymd') . "T090000\n";
                $ics_content .= "DTEND:" . $expire_date->format('Ymd') . "T100000\n";
                $ics_content .= "SUMMARY:Prescription Expiry: " . $prescription['medication_name'] . "\n";
                $ics_content .= "DESCRIPTION:Your prescription for " . $prescription['medication_name'] . " expires today.\n";
                $ics_content .= "END:VEVENT\nEND:VCALENDAR";

                header('Content-Type: text/calendar');
                header('Content-Disposition: attachment;filename=prescription_expiry.ics');
                echo $ics_content;
                exit;
            } else {
                $error_message = "No expiration date found for this prescription.";
            }
        } catch (Exception $e) {
            $error_message = "Error exporting to calendar: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../logo.png" type="image/png">
    <title>MediAssist - Prescriptions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
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
            --background: var(--gray);
            --card-bg: var(--white);
            --text-color: var(--text);
            --manual-color: #4CAF50;
            --pdf-color: #2196F3;
        }

        [data-theme="dark"] {
            --background: #121212;
            --card-bg: #1e1e1e;
            --text-color: #e0e0e0;
            --gray: #2a2a2a;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--background);
            color: var(--text-color);
        }

        header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 1rem 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .back-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .back-link:hover {
            color: var(--light);
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            color: var(--dark);
        }

        .page-title h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .page-title p {
            color: #666;
            font-size: 1rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            position: relative;
            display: flex;
            align-items: center;
        }

        .alert-icon {
            margin-right: 1rem;
            font-size: 1.2rem;
        }

        .alert-content {
            flex-grow: 1;
        }

        .alert-success {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .alert-error {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        .close-alert {
            cursor: pointer;
            font-size: 1rem;
            opacity: 0.7;
            transition: opacity 0.3s;
        }

        .close-alert:hover {
            opacity: 1;
        }

        .tabs {
            display: flex;
            margin-bottom: 1.5rem;
            background-color: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .tab {
            padding: 1rem 1.5rem;
            cursor: pointer;
            color: #666;
            font-weight: 500;
            transition: all 0.3s ease;
            flex: 1;
            text-align: center;
            border-bottom: 3px solid transparent;
        }

        .tab.active {
            color: var(--primary);
            border-bottom: 3px solid var(--primary);
            background-color: rgba(38, 166, 154, 0.05);
        }

        .tab:hover:not(.active) {
            background-color: rgba(0, 0, 0, 0.02);
            color: var(--primary-dark);
        }

        .tab-content {
            background: var(--card-bg);
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            animation: fadeIn 0.3s ease-in-out;
        }
        /* Pulse Animation */
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .tab-content:not(.active) {
            display: none;
        }

        .controls-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .search-box {
            display: flex;
            align-items: center;
            background-color: var(--gray);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            min-width: 250px;
            flex-grow: 1;
            max-width: 400px;
            border: 1px solid transparent;
            transition: all 0.3s ease;
        }

        .search-box:focus-within {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 2px rgba(38, 166, 154, 0.1);
        }

        .search-box input {
            background: transparent;
            border: none;
            outline: none;
            flex-grow: 1;
            padding: 0.5rem;
            color: var(--text-color);
            font-size: 0.95rem;
        }

        .search-box i {
            color: #666;
            margin-right: 0.5rem;
        }

        .controls-right {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 0.6rem 1rem;
            border-radius: 6px;
            border: 1px solid #ddd;
            background: var(--card-bg);
            color: var(--text-color);
            font-size: 0.95rem;
            cursor: pointer;
            outline: none;
            transition: border 0.3s ease;
        }

        .filter-select:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 2px rgba(38, 166, 154, 0.1);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background-color: rgba(38, 166, 154, 0.05);
        }

        .prescription-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .prescription-card {
            background-color: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .prescription-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            padding: 1.2rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.3rem;
            color: var(--dark);
        }

        .card-subtitle {
            color: #666;
            font-size: 0.9rem;
        }

        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-active {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success);
        }

        .status-expired {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger);
        }

        .status-warning {
            background-color: rgba(255, 152, 0, 0.1);
            color: var(--warning);
        }

        .card-body {
            padding: 1.2rem;
        }

        .card-info {
            margin-bottom: 1rem;
        }

        .info-row {
            display: flex;
            margin-bottom: 0.8rem;
        }

        .info-label {
            width: 120px;
            color: #666;
            font-size: 0.9rem;
        }

        .info-value {
            font-weight: 500;
            color: var(--text-color);
            font-size: 0.9rem;
        }

        .card-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.8rem;
            padding: 1rem 1.2rem;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .btn-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: all 0.3s ease;
        }

        .btn-view {
            background-color: rgba(38, 166, 154, 0.1);
            color: var(--primary);
        }

        .btn-view:hover {
            background-color: var(--primary);
            color: white;
        }

        .btn-pdf {
            background-color: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
        }

        .btn-pdf:hover {
            background-color: #0d6efd;
            color: white;
        }

        .btn-calendar {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        .btn-calendar:hover {
            background-color: #ffc107;
            color: white;
        }

        .btn-delete {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger);
        }

        .btn-delete:hover {
            background-color: var(--danger);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }

        .empty-icon {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1.5rem;
        }

        .empty-title {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 0.8rem;
        }

        .empty-description {
            color: #999;
            margin-bottom: 1.5rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .mode-selection {
            margin-bottom: 1.5rem;
        }

        .mode-select {
            width: 250px;
            padding: 0.7rem;
            border-radius: 6px;
            border: 1px solid #ddd;
            background: var(--card-bg);
            color: var(--text-color);
            font-size: 0.95rem;
            cursor: pointer;
            outline: none;
            transition: all 0.3s ease;
            position: relative;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23333' width='18px' height='18px'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.7rem center;
            background-size: 1.2rem;
        }

        .mode-select:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 2px rgba(38, 166, 154, 0.1);
        }

        .mode-select option[value="manual"] {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--manual-color);
        }

        .mode-select option[value="pdf"] {
            background-color: rgba(33, 150, 243, 0.1);
            color: var(--pdf-color);
        }

        .form-container {
            transition: border 0.3s ease;
            border-radius: 8px;
            padding: 1rem;
        }

        .form-container.manual {
            border: 2px solid var(--manual-color);
        }

        .form-container.pdf {
            border: 2px solid var(--pdf-color);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #666;
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 0.7rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            outline: none;
            transition: all 0.3s ease;
            background: var(--card-bg);
            color: var(--text-color);
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(38, 166, 154, 0.1);
        }

        .file-upload {
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            cursor: pointer;
        }

        .file-upload:hover {
            border-color: var(--primary-light);
            background-color: rgba(38, 166, 154, 0.02);
        }

        .file-upload-icon {
            font-size: 2.5rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        .file-upload-text {
            margin-bottom: 1rem;
            color: #666;
        }

        .file-upload-btn {
            display: inline-block;
            background-color: var(--primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload-btn:hover {
            background-color: var(--primary-dark);
        }

        .file-upload input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .preview-container {
            margin-top: 1.5rem;
            display: none;
        }

        .preview-container.active {
            display: block;
        }

        .pdf-preview {
            width: 100%;
            height: 300px;
            border: 1px solid #ddd;
            border-radius: 6px;
            overflow: hidden;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            overflow-y: auto;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease-in-out;
        }

        .modal-content {
            background-color: var(--card-bg);
            border-radius: 8px;
            width: 80%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            position: relative;
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
            transition: color 0.3s ease;
        }

        .modal-close:hover {
            color: var(--danger);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-pdf {
            width: 100%;
            height: 600px;
            border: none;
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .manual-fields {
            display: block;
        }

        .pdf-fields {
            display: none;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 95%;
            }

            .prescription-grid {
                grid-template-columns: 1fr;
            }

            .controls-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                max-width: 100%;
            }

            .mode-select {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="navbar">
            <a href="../accueil.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <div class="logo">
                <i class="fas fa-heartbeat"></i> MediAssist
            </div>
        </div>
    </header>

    <div class="container">
        <div class="page-header">
            <div class="page-title">
                <h1>Prescriptions</h1>
                <p>Manage and track your prescription medications</p>
            </div>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success" id="success-alert">
                <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
                <div class="alert-content"><?php echo $success_message; ?></div>
                <span class="close-alert" onclick="this.parentElement.style.display='none';">×</span>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error" id="error-alert">
                <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
                <div class="alert-content"><?php echo $error_message; ?></div>
                <span class="close-alert" onclick="this.parentElement.style.display='none';">×</span>
            </div>
        <?php endif; ?>

        <div class="tabs">
            <div class="tab active" data-tab="prescriptions">
                <i class="fas fa-clipboard-list"></i> My Prescriptions
            </div>
            <div class="tab" data-tab="add-prescription">
                <i class="fas fa-plus-circle"></i> Add Prescription
            </div>
        </div>

        <div class="tab-content active" id="prescriptions-tab">
            <div class="controls-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="search-prescription" placeholder="Search prescriptions...">
                </div>
                <div class="controls-right">
                    <select class="filter-select" id="filter-status">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="expiring">Expiring Soon</option>
                        <option value="expired">Expired</option>
                    </select>
                    <button class="btn btn-primary" id="add-prescription-btn">
                        <i class="fas fa-plus"></i> Add Prescription
                    </button>
                </div>
            </div>

            <?php if (empty($prescriptions)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-clipboard"></i>
                    </div>
                    <h3 class="empty-title">No Prescriptions Yet</h3>
                    <p class="empty-description">You haven't added any prescriptions yet. Start tracking your prescriptions by adding your first one.</p>
                    <button class="btn btn-primary" id="empty-add-btn">
                        <i class="fas fa-plus"></i> Add Your First Prescription
                    </button>
                </div>
            <?php else: ?>
                <div class="prescription-grid">
                    <?php foreach ($prescriptions as $prescription):
                        $status = 'active';
                        $status_label = 'Active';
                        if (!empty($prescription['expires_on'])) {
                            $today = new DateTime();
                            $expires = new DateTime($prescription['expires_on']);
                            $diff = $today->diff($expires);
                            if ($expires < $today) {
                                $status = 'expired';
                                $status_label = 'Expired';
                            } elseif ($diff->days <= 14) {
                                $status = 'warning';
                                $status_label = 'Expiring Soon';
                            }
                        }
                    ?>
                        <div class="prescription-card" data-id="<?php echo $prescription['id']; ?>">
                            <div class="card-header">
                                <div>
                                    <h3 class="card-title"><?php echo $prescription['medication_name']; ?></h3>
                                    <p class="card-subtitle">By Dr. <?php echo $prescription['doctor_name']; ?></p>
                                </div>
                                <span class="status-badge status-<?php echo $status; ?>"><?php echo $status_label; ?></span>
                            </div>
                            <div class="card-body">
                                <div class="card-info">
                                    <div class="info-row">
                                        <div class="info-label">Dosage:</div>
                                        <div class="info-value"><?php echo $prescription['dosage']; ?></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Prescribed:</div>
                                        <div class="info-value"><?php echo formatDate($prescription['date_prescribed']); ?></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Expires:</div>
                                        <div class="info-value"><?php echo formatDate($prescription['expires_on']); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-actions">
                                <button class="btn-icon btn-view view-details" title="View Details" data-id="<?php echo $prescription['id']; ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if (!empty($prescription['prescription_pdf'])): ?>
                                    <button class="btn-icon btn-pdf view-pdf" title="View PDF" data-pdf="../Uploads/prescriptions/<?php echo $prescription['prescription_pdf']; ?>">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                <?php endif; ?>
                                <?php if (!empty($prescription['expires_on'])): ?>
                                    <a href="?action=export_calendar&id=<?php echo $prescription['id']; ?>" class="btn-icon btn-calendar" title="Add to Calendar">
                                        <i class="fas fa-calendar-plus"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="?action=delete&id=<?php echo $prescription['id']; ?>" class="btn-icon btn-delete" title="Delete" onclick="return confirm('Are you sure you want to delete this prescription?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="tab-content" id="add-prescription-tab">
            <form method="POST" action="" enctype="multipart/form-data" id="prescription-form" class="form-container">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="mode-selection">
                    <label class="form-label">Entry Mode</label>
                    <select name="upload_mode" class="mode-select" onchange="togglePdfRequirement()">
                        <option value="manual" selected>Manual Entry</option>
                        <option value="pdf">PDF Upload</option>
                    </select>
                </div>
                <div class="manual-fields">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Medication<span class="required-text">*</span></label>
                            <select name="medication_id" class="form-control">
                                <option value="">Select a medication or enter new</option>
                                <?php foreach ($medications as $medication): ?>
                                    <option value="<?php echo $medication['id']; ?>">
                                        <?php echo $medication['name'] . ' - ' . $medication['dosage']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Medication Name<span class="required-text">*</span></label>
                            <input type="text" name="medication_name" class="form-control" placeholder="Enter medication name" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Doctor Name<span class="required-text">*</span></label>
                            <input type="text" name="doctor_name" class="form-control manual-doctor" placeholder="Enter doctor name" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date Prescribed<span class="required-text">*</span></label>
                            <input type="date" name="date_prescribed" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Expiration Date<span class="required-text">*</span></label>
                            <input type="date" name="expires_on" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Dosage<span class="required-text">*</span></label>
                            <input type="text" name="dosage" class="form-control" placeholder="e.g., 10mg, 1 tablet" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Quantity<span class="required-text">*</span></label>
                            <input type="text" name="quantity" class="form-control" placeholder="e.g., 30 tablets" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Instructions<span class="required-text">*</span></label>
                        <textarea name="instructions" class="form-control" rows="3" placeholder="e.g., Take one tablet daily with food" required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notes<span class="required-text">*</span></label>
                        <textarea name="notes" class="form-control manual-notes" rows="3" placeholder="Any additional notes" required></textarea>
                    </div>
                </div>
                <div class="pdf-fields">
    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Doctor Name<span class="required-text">*</span></label>
            <input type="text" name="doctor_name" class="form-control pdf-doctor" placeholder="Enter doctor name" required>
        </div>
        <div class="form-group">
            <label class="form-label">Date Prescribed<span class="required-text">*</span></label>
            <input type="date" name="date_prescribed" class="form-control pdf-date-prescribed" required>
        </div>
        <div class="form-group">
            <label class="form-label">Expiration Date<span class="required-text">*</span></label>
            <input type="date" name="expires_on" class="form-control pdf-expires-on" required>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control pdf-notes" rows="3" placeholder="Any additional notes"></textarea>
    </div>
    <div class="form-group">
        <label class="form-label">Upload PDF<span class="required-text">*</span></label>
        <div class="file-upload">
            <div class="file-upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
            <div class="file-upload-text">
                <p>Drag & Drop your prescription PDF or</p>
                <span class="file-upload-btn">Browse Files</span>
            </div>
            <input type="file" name="prescription_pdf" id="prescription_pdf" accept=".pdf" required>
        </div>
        <div class="form-text">Max file size: 5 MB. PDF format only.</div>
        <div class="preview-container" id="pdf-preview-container">
            <div class="pdf-preview" id="pdf-preview"></div>
        </div>
    </div>
</div>
                <div class="btn-group">
                    <button type="submit" name="add_prescription" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Prescription
                    </button>
                    <button type="reset" class="btn btn-outline">
                        <i class="fas fa-undo"></i> Reset Form
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="prescription-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Prescription Details</h3>
                <button class="modal-close">×</button>
            </div>
            <div class="modal-body" id="prescription-details"></div>
        </div>
    </div>

    <div class="modal" id="pdf-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Prescription PDF</h3>
                <button class="modal-close">×</button>
            </div>
            <div class="modal-body">
                <iframe class="modal-pdf" id="pdf-iframe" src=""></iframe>
            </div>
        </div>
    </div>

    <script>
        // Toggle PDF and field requirements
        function togglePdfRequirement() {
            const manualFields = document.querySelector('.manual-fields');
            const pdfFields = document.querySelector('.pdf-fields');
            const pdfInput = document.getElementById('prescription_pdf');
            const formContainer = document.querySelector('.form-container');
            const isPdfMode = document.querySelector('.mode-select').value === 'pdf';

            manualFields.style.display = isPdfMode ? 'none' : 'block';
            pdfFields.style.display = isPdfMode ? 'block' : 'none';

            // Reset required attributes
            document.querySelectorAll('.manual-fields .form-control').forEach(input => {
                input.required = !isPdfMode;
            });
            document.querySelector('.pdf-doctor').required = isPdfMode;
            document.querySelector('.pdf-date-prescribed').required = isPdfMode;
            document.querySelector('.pdf-expires-on').required = isPdfMode;
            document.querySelector('.pdf-notes').required = false;
            pdfInput.required = isPdfMode;

            // Update required indicators
            document.querySelectorAll('.manual-fields .required-text').forEach(el => {
                el.textContent = isPdfMode ? '' : '*';
            });
            document.querySelectorAll('.pdf-fields .required-text').forEach(el => {
                el.textContent = isPdfMode ? '*' : '';
            });

            // Update form container border
            formContainer.classList.remove('manual', 'pdf');
            formContainer.classList.add(isPdfMode ? 'pdf' : 'manual');

            // Clear PDF preview if switching to manual
            if (!isPdfMode) {
                document.getElementById('pdf-preview-container').classList.remove('active');
                document.getElementById('pdf-preview').innerHTML = '';
                pdfInput.value = '';
            }
        }

        // Tab switching
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                this.classList.add('active');
                document.getElementById(this.dataset.tab + '-tab').classList.add('active');
            });
        });

        // Add prescription button
        document.getElementById('add-prescription-btn').addEventListener('click', function() {
            document.querySelector('.tab[data-tab="add-prescription"]').click();
        });

        // Empty state button
        if (document.getElementById('empty-add-btn')) {
            document.getElementById('empty-add-btn').addEventListener('click', function() {
                document.querySelector('.tab[data-tab="add-prescription"]').click();
            });
        }

        // PDF preview
        document.getElementById('prescription_pdf').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type === 'application/pdf') {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const container = document.getElementById('pdf-preview-container');
                    const preview = document.getElementById('pdf-preview');
                    container.classList.add('active');
                    pdfjsLib.getDocument(e.target.result).promise.then(function(pdf) {
                        pdf.getPage(1).then(function(page) {
                            const viewport = page.getViewport({ scale: 0.5 });
                            const canvas = document.createElement('canvas');
                            const context = canvas.getContext('2d');
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;
                            preview.innerHTML = '';
                            preview.appendChild(canvas);
                            page.render({
                                canvasContext: context,
                                viewport: viewport
                            });
                        });
                    });
                };
                reader.readAsDataURL(file);
            }
        });

        // Modal close
        document.querySelectorAll('.modal-close').forEach(close => {
            close.addEventListener('click', function() {
                this.closest('.modal').classList.remove('active');
            });
        });

        // View PDF
        document.querySelectorAll('.view-pdf').forEach(btn => {
            btn.addEventListener('click', function() {
                const pdfUrl = this.dataset.pdf;
                document.getElementById('pdf-iframe').src = pdfUrl;
                document.getElementById('pdf-modal').classList.add('active');
            });
        });

        // View prescription details
        document.querySelectorAll('.view-details').forEach(btn => {
            btn.addEventListener('click', function() {
                const card = this.closest('.prescription-card');
                const title = card.querySelector('.card-title').innerText;
                const doctor = card.querySelector('.card-subtitle').innerText;
                const info = card.querySelector('.card-info').innerHTML;
                document.getElementById('prescription-details').innerHTML = `
                    <h3>${title}</h3>
                    <p>${doctor}</p>
                    <div class="card-info">${info}</div>
                `;
                document.getElementById('prescription-modal').classList.add('active');
            });
        });

        // Close modals on outside click
        window.addEventListener('click', function(e) {
            document.querySelectorAll('.modal').forEach(modal => {
                if (e.target === modal) {
                    modal.classList.remove('active');
                }
            });
        });

        // Search
        document.getElementById('search-prescription').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.prescription-card').forEach(card => {
                const title = card.querySelector('.card-title').innerText.toLowerCase();
                const doctor = card.querySelector('.card-subtitle').innerText.toLowerCase();
                const info = card.querySelector('.card-info').innerText.toLowerCase();
                card.style.display = (title.includes(searchTerm) || doctor.includes(searchTerm) || info.includes(searchTerm)) ? 'block' : 'none';
            });
        });

        // Filter
        document.getElementById('filter-status').addEventListener('change', function() {
            const filter = this.value;
            document.querySelectorAll('.prescription-card').forEach(card => {
                const status = card.querySelector('.status-badge').classList[1].replace('status-', '');
                card.style.display = (filter === 'all' || status === filter || (filter === 'expiring' && status === 'warning')) ? 'block' : 'none';
            });
        });

        // Auto-hide alerts
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);

        // Initialize form requirements
        togglePdfRequirement();
    </script>
</body>
</html>