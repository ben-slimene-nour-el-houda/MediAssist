<?php
// Affiche les erreurs PHP (utile en développement)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// En-tête JSON
header('Content-Type: application/json');

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mediassist";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

// Démarrer la session pour récupérer l'ID utilisateur
session_start();

// Vérifier si l'utilisateur est connecté (l'ID utilisateur existe dans la session)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit;
}

// Si la requête est bien en POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Vérifier les champs requis
    if (empty($_POST['title']) || empty($_POST['date']) || empty($_POST['time'])) {
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
        exit;
    }

    // Récupérer l'ID utilisateur depuis la session
    $user_id = $_SESSION['user_id'];

    // Récupération et nettoyage des données
    $title = $conn->real_escape_string($_POST['title']);
    $date = $_POST['date'];
    $time = $_POST['time'];
    $location = isset($_POST['location']) ? $conn->real_escape_string($_POST['location']) : '';
    $description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : '';
    $reminder_sent = isset($_POST['reminder']) ? 0 : null;

    $date_time = $date . ' ' . $time . ':00';

    // Requête SQL préparée
    $sql = "INSERT INTO appointments (user_id, title, date_time, location, description, reminder_sent)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "SQL prepare error: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("issssi", $user_id, $title, $date_time, $location, $description, $reminder_sent);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Appointment added", "id" => $conn->insert_id]);
    } else {
        echo json_encode(["status" => "error", "message" => "Execution failed: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
