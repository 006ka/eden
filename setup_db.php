<?php
require_once __DIR__ . '/config/db.php';

try {
    // Ajouter la colonne sujet à la table contacts si elle n'existe pas
    $checkColumn = $pdo->query("SHOW COLUMNS FROM contacts LIKE 'sujet'");
    if ($checkColumn->rowCount() === 0) {
        $pdo->exec("ALTER TABLE contacts ADD COLUMN sujet VARCHAR(255) DEFAULT NULL AFTER telephone");
        echo "✅ Colonne 'sujet' ajoutée à la table 'contacts'<br>";
    } else {
        echo "✅ La colonne 'sujet' existe déjà dans 'contacts'<br>";
    }
    
    // Créer la table feedbacks si elle n'existe pas
    $sql = "CREATE TABLE IF NOT EXISTS feedbacks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        sujet VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        created_at DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sql);
    echo "✅ Table 'feedbacks' créée avec succès!<br>";
    
    echo "<br><strong>Configuration terminée!</strong>";
} catch (PDOException $e) {
    echo "❌ Erreur: " . $e->getMessage();
}
?>
