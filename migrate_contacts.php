<?php
require_once __DIR__ . '/config/db.php';

try {
    // Ajouter la colonne type à la table contacts si elle n'existe pas
    $checkColumn = $pdo->query("SHOW COLUMNS FROM contacts LIKE 'type'");
    if ($checkColumn->rowCount() === 0) {
        $pdo->exec("ALTER TABLE contacts ADD COLUMN type VARCHAR(50) DEFAULT 'contact' AFTER message");
        echo "✅ Colonne 'type' ajoutée à la table 'contacts'<br>";
    } else {
        echo "✅ La colonne 'type' existe déjà dans 'contacts'<br>";
    }
    
    echo "<br><strong>Migration terminée!</strong>";
} catch (PDOException $e) {
    echo "❌ Erreur: " . $e->getMessage();
}
?>
