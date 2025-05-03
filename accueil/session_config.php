<?php
// session_config.php - Configuration de la session

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    // Configurer les options de session
    $session_options = [
        'cookie_lifetime' => 86400, // 24 heures
        'cookie_httponly' => true,  // Cookie accessible uniquement via HTTP
        'cookie_secure' => false,   // true en production avec HTTPS
        'use_strict_mode' => true,  // Sécurité améliorée
        'use_only_cookies' => true  // Utiliser uniquement des cookies
    ];
    
    session_start($session_options);
}

// Fonction pour vérifier si l'utilisateur est connecté
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Fonction pour rediriger si non connecté
function redirect_if_not_logged_in($redirect_url = '../login/login.php') {
    if (!is_logged_in()) {
        header("Location: $redirect_url");
        exit();
    }
}

// Fonction pour créer un token CSRF
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Fonction pour vérifier le token CSRF
function verify_csrf_token($token) {
    if (isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] === $token) {
        return true;
    }
    return false;
}