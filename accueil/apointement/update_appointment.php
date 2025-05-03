<?php
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log'); // le fichier debug.log sera dans le même dossier que le script

ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mediassist";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['id'], $_POST['title'], $_POST['date'], $_POST['time'], $_POST['location'], $_POST['description'])) {
        
        $id = intval($_POST['id']);
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $date = $_POST['date'];
        $time = $_POST['time'];
        $date_time = $date . ' ' . $time . ':00';
        $location = mysqli_real_escape_string($conn, $_POST['location']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $reminder_sent = isset($_POST['reminder']) ? 0 : 1;

        $sql = "UPDATE appointments SET title=?, date_time=?, location=?, description=?, reminder_sent=? WHERE id=? AND user_id=?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            echo json_encode(["status" => "error", "message" => $conn->error]);
            exit();
        }

        $stmt->bind_param("ssssiii", $title, $date_time, $location, $description, $reminder_sent, $id, $user_id);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Champs manquants."]);
    }

    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Méthode invalide"]);
}
?>
