<?php
session_start();

// Définir login et mot de passe
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'motdepasse123'); // à changer

// Déconnexion si demandé
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Vérifier si l'utilisateur est déjà connecté
function check_login() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: login.php");
        exit;
    }
}
