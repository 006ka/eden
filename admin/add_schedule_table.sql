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
