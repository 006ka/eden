<?php
require_once __DIR__ . '/../../config/db.php';

try {
    // Vérifier si les colonnes existent déjà
    $stmt = $pdo->query("SHOW COLUMNS FROM admin_users LIKE 'email'");
    $emailColumnExists = $stmt->rowCount() > 0;

    if (!$emailColumnExists) {
        // Ajouter les nouvelles colonnes
        $pdo->exec("ALTER TABLE admin_users 
            ADD COLUMN email VARCHAR(255) AFTER username,
            ADD COLUMN full_name VARCHAR(255) AFTER email,
            ADD COLUMN profile_image VARCHAR(255) NULL AFTER full_name,
            ADD COLUMN last_login DATETIME NULL,
            ADD COLUMN updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
            MODIFY COLUMN password VARCHAR(255) NOT NULL COMMENT 'Mot de passe hashé',
            MODIFY COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");

        // Mettre à jour l'email admin par défaut
        $pdo->exec("UPDATE admin_users SET email = 'admin@example.com', full_name = 'Administrateur' WHERE username = 'admin'");
        
        echo "Mise à jour de la table admin_users effectuée avec succès.\n";
    } else {
        echo "La table admin_users est déjà à jour.\n";
    }
} catch (Exception $e) {
    die("Erreur lors de la mise à jour de la table : " . $e->getMessage());
}

echo "Migration terminée.\n";
