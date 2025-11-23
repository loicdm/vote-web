<?php
// --- db_config.php ---
// Common database configuration for Docker setup
$host = 'db'; // service name from docker-compose
$dbname = 'scrutin_db';
$user = 'user';
$pass = 'userpass';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion à la base : " . $e->getMessage());
}
