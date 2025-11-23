<?php
require 'db_config.php';

    // Créer la base si elle n'existe pas
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE $dbname");

    // Créer les tables
    $sql = <<<SQL

CREATE TABLE IF NOT EXISTS scrutins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS candidats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS candidatures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scrutin_id INT NOT NULL,
    candidat_id INT NOT NULL,
    UNIQUE(scrutin_id, candidat_id),
    FOREIGN KEY (scrutin_id) REFERENCES scrutins(id) ON DELETE CASCADE,
    FOREIGN KEY (candidat_id) REFERENCES candidats(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    utilise TINYINT(1) DEFAULT 0,
);

CREATE TABLE IF NOT EXISTS votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scrutin_id INT NOT NULL,
    candidat_id INT NOT NULL,
    valeur TINYINT NOT NULL,
    date_vote DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (scrutin_id) REFERENCES scrutins(id) ON DELETE CASCADE,
    FOREIGN KEY (candidat_id) REFERENCES candidats(id) ON DELETE CASCADE
);

SQL;

    $pdo->exec($sql);
    echo "Installation terminée : tables créées avec succès.";

?>

