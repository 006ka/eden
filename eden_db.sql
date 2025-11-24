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

-- Table des inscriptions à la retraite
CREATE TABLE IF NOT EXISTS inscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telephone VARCHAR(50) NOT NULL,
    age INT NOT NULL,
    eglise VARCHAR(255) DEFAULT NULL,
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
