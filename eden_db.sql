CREATE DATABASE IF NOT EXISTS eden_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE eden_db;

-- Table des messages de contact
CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telephone VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des programmes annuels (événements de l'année)
CREATE TABLE IF NOT EXISTS programmes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mois VARCHAR(50) NOT NULL,
    titre VARCHAR(255) NOT NULL,
    theme VARCHAR(255) DEFAULT NULL,
    details VARCHAR(255) DEFAULT NULL,
    affiche_url VARCHAR(500) DEFAULT NULL,
    date_debut DATE DEFAULT NULL,
    date_fin DATE DEFAULT NULL,
    ordre INT DEFAULT 0,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des inscriptions à la retraite
CREATE TABLE IF NOT EXISTS inscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telephone VARCHAR(50) NOT NULL,
    age INT NOT NULL,
    eglise VARCHAR(255) DEFAULT NULL,
    adresse VARCHAR(255) DEFAULT NULL,
    temps_sejour VARCHAR(50) DEFAULT NULL,
    event_type VARCHAR(50) DEFAULT NULL,
    event_id INT DEFAULT NULL,
    event_label VARCHAR(255) DEFAULT NULL,
    besoins TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des photos de la galerie
CREATE TABLE IF NOT EXISTS gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_url VARCHAR(500) NOT NULL,
    titre VARCHAR(255) DEFAULT NULL,
    retreat_url VARCHAR(255) DEFAULT NULL,
    retreat_id INT DEFAULT NULL,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des retraites
CREATE TABLE IF NOT EXISTS retreats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    theme VARCHAR(255) DEFAULT NULL,
    date_debut DATE DEFAULT NULL,
    date_fin DATE DEFAULT NULL,
    lieu VARCHAR(255) DEFAULT NULL,
    orateurs VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    programme_image_url VARCHAR(500) DEFAULT NULL,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des horaires des retraites
CREATE TABLE IF NOT EXISTS retreat_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    retreat_id INT NOT NULL,
    jour VARCHAR(50) NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    activite VARCHAR(255) NOT NULL,
    responsable VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    ordre INT DEFAULT 0,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (retreat_id) REFERENCES retreats(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des témoignages (utilisée par public/temoignages.php)
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    author VARCHAR(255) NOT NULL,
    role VARCHAR(255) DEFAULT NULL,
    content TEXT NOT NULL,
    image_url VARCHAR(500) DEFAULT NULL,
    visible TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Données d'exemple pour les témoignages
INSERT INTO testimonials (author, role, content, image_url, visible, created_at) VALUES
('Sarah K.', 'Retraitante 2024', 'Durant la retraite EDEN, j''ai retrouvé une paix intérieure que je croyais perdue. Dieu a restauré ma joie et ma foi.', NULL, 1, NOW()),
('David M.', 'Serviteur', 'Servir lors des retraites EDEN m''a enseigné l''humilité et la puissance du service discret. J''ai vu Dieu toucher de nombreuses vies.', NULL, 1, NOW()),
('Joëlle B.', 'Participant', 'Cette retraite a été un tournant dans ma marche avec Dieu. J''ai reçu des réponses claires dans la prière et une nouvelle direction.', NULL, 1, NOW());

-- Table des utilisateurs admin
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Compte admin par défaut (mot de passe: edenadmin)
INSERT INTO admin_users (username, password, created_at)
VALUES ('admin', 'edenadmin', NOW())
ON DUPLICATE KEY UPDATE username = VALUES(username);
