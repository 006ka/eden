<?php
require_once 'config/db.php';

try {
    // Vérifier si la colonne updated_at existe
    $stmt = $pdo->query("SHOW COLUMNS FROM gallery LIKE 'updated_at'");
    $columnExists = $stmt->rowCount() > 0;
    
    if (!$columnExists) {
        // Ajouter la colonne updated_at
        $pdo->exec("ALTER TABLE gallery ADD COLUMN updated_at DATETIME DEFAULT NULL");
        echo "Colonne 'updated_at' ajoutée avec succès.\n";
    } else {
        echo "La colonne 'updated_at' existe déjà.\n";
    }
    
    // Vérifier si la colonne created_at existe
    $stmt = $pdo->query("SHOW COLUMNS FROM gallery LIKE 'created_at'");
    if ($stmt->rowCount() === 0) {
        // Ajouter la colonne created_at
        $pdo->exec("ALTER TABLE gallery ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
        echo "Colonne 'created_at' ajoutée avec succès.\n";
    } else {
        echo "La colonne 'created_at' existe déjà.\n";
    }
    
    echo "La structure de la table est à jour.\n";
    
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
