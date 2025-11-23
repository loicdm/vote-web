-- Table des scrutins / élections
CREATE TABLE scrutins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table des candidats globaux
CREATE TABLE candidats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL
);

-- Table des participations d'un candidat à un scrutin
CREATE TABLE candidatures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scrutin_id INT NOT NULL,
    candidat_id INT NOT NULL,
    FOREIGN KEY (scrutin_id) REFERENCES scrutins(id) ON DELETE CASCADE,
    FOREIGN KEY (candidat_id) REFERENCES candidats(id) ON DELETE CASCADE,
    UNIQUE(scrutin_id, candidat_id) -- un candidat ne peut pas être inscrit deux fois dans le même scrutin
);

-- Table des codes à usage unique pour voter
CREATE TABLE codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scrutin_id INT NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    utilise TINYINT(1) DEFAULT 0,
    FOREIGN KEY (scrutin_id) REFERENCES scrutins(id) ON DELETE CASCADE
);

-- Table des votes
CREATE TABLE votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scrutin_id INT NOT NULL,
    candidat_id INT NOT NULL,
    valeur TINYINT NOT NULL, -- -1, 0, 1
    date_vote DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (scrutin_id) REFERENCES scrutins(id) ON DELETE CASCADE,
    FOREIGN KEY (candidat_id) REFERENCES candidats(id) ON DELETE CASCADE
);

