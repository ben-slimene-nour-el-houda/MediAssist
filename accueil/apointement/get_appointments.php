<?php
require_once '../../db_connect.php';
session_start();

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(["error" => "Utilisateur non authentifié"]);
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT id, title, DATE(date_time) as date, TIME(date_time) as time, 
        location, description, reminder_sent 
        FROM appointments 
        WHERE user_id = ? 
        ORDER BY date_time DESC";

$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur lors de la préparation de la requête."]);
    exit();
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];

while ($row = $result->fetch_assoc()) {
    $row['reminder'] = ($row['reminder_sent'] == 0); // true si non encore envoyé
    unset($row['reminder_sent']);
    $appointments[] = $row;
}

echo json_encode($appointments);

$stmt->close();
$mysqli->close();
?>
