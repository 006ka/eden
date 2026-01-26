<?php
// Script de mise à jour de la table admin_users
require_once __DIR__ . '/../config/db.php';

try {
    // Désactiver temporairement les erreurs SQL pour la vérification
    $pdo->setAttribute(2, 0); // PDO::ATTR_ERRMODE = 2, PDO::ERRMODE_SILENT = 0
    
    // Vérifier si la table admin_users existe
    $tableExists = $pdo->query("SHOW TABLES LIKE 'admin_users'")->rowCount() > 0;
    
    if (!$tableExists) {
        die("La table admin_users n'existe pas dans la base de données.");
    }
    
    // Activer à nouveau les erreurs SQL (PDO::ERRMODE_EXCEPTION = 2)
    $pdo->setAttribute(2, 2);
    
    // Démarrer la transaction
    $pdo->beginTransaction();
    
    // Liste des colonnes à ajouter avec leurs définitions
    $columns = [
        'email' => "VARCHAR(255) NULL AFTER username",
        'full_name' => "VARCHAR(255) NULL AFTER email",
        'profile_image' => "VARCHAR(255) NULL AFTER full_name",
        'last_login' => "DATETIME NULL AFTER profile_image",
        'updated_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at"
    ];
    
    // Pour chaque colonne, vérifier si elle existe avant de l'ajouter
    foreach ($columns as $column => $definition) {
        $check = $pdo->query("SHOW COLUMNS FROM admin_users LIKE '$column'");
        if ($check->rowCount() === 0) {
            $pdo->exec("ALTER TABLE admin_users ADD COLUMN $column $definition");
            echo "Colonne '$column' ajoutée avec succès.\n";
        } else {
            echo "La colonne '$column' existe déjà.\n";
        }
    }
    
    // Valider les modifications
    $pdo->commit();
    echo "Mise à jour de la table admin_users terminée avec succès.\n";
    
} catch (PDOException $e) {
    // En cas d'erreur, annuler les modifications
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Erreur lors de la mise à jour de la table admin_users : " . $e->getMessage());
}

// Redirection vers l'administration après 5 secondes
echo "<p>Redirection vers l'administration dans 5 secondes...</p>";
echo "<p><a href='admin.php'>Cliquez ici si vous n'êtes pas redirigé automatiquement</a></p>";
echo "<script>setTimeout(function() { window.location.href = 'admin.php'; }, 5000);</script>";
