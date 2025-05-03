<?php
$host = 'localhost';
$dbname = 'mediassist';
$username = 'root';
$password = '';

$mysqli = new mysqli($host, $username, $password, $dbname);

// Vérifier la connexion
if ($mysqli->connect_error) {
    die('Erreur de connexion à la base de données : ' . $mysqli->connect_error);
}

// Définir le charset

// Fonction pour nettoyer les entrées utilisateur
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}