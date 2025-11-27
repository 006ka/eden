<?php
/**
 * Script de migration - Crée la table retreat_schedules si elle n'existe pas
 * Exécutez ce script une seule fois depuis votre navigateur
 */

require_once __DIR__ . '/../config/db.php';

// Vérifie la session admin
session_start();
if (empty($_SESSION['is_admin'])) {
    header('Location: admin.php');
    exit;
}

$message = '';
$error = '';

try {
    // Vérifie si la table existe déjà
    $stmt = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?");
    $stmt->execute([$_ENV['DB_NAME'] ?? 'eden_db', 'retreat_schedules']);
    $tableExists = $stmt->fetchColumn();

    if ($tableExists) {
        $message = '✅ La table retreat_schedules existe déjà.';
    } else {
        // Crée la table
        $sql = "CREATE TABLE IF NOT EXISTS retreat_schedules (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

        $pdo->exec($sql);
        $message = '✅ La table retreat_schedules a été créée avec succès !';
    }
} catch (Exception $e) {
    $error = '❌ Erreur lors de la création de la table : ' . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Installation - Horaires des Retraites</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        .setup-container {
            max-width: 600px;
            margin: 60px auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        .setup-container h1 {
            color: #2c6e49;
            margin-bottom: 20px;
        }
        .message {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 30px;
        }
        .btn {
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
        }
        .btn.primary {
            background: #2c6e49;
            color: white;
        }
        .btn.secondary {
            background: #ddd;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h1>⚙️ Installation - Horaires des Retraites</h1>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <p>Le système de gestion des horaires est maintenant prêt à être utilisé.</p>
        <p style="color: #666; font-size: 0.95em; margin-top: 20px;">Vous pouvez :</p>
        <ul style="text-align: left; display: inline-block; color: #666;">
            <li>Ajouter des horaires via l'espace admin (section Horaires)</li>
            <li>Consulter les horaires sur la page publique</li>
            <li>Filtrer par retraite sélectionnée</li>
        </ul>

        <div class="action-buttons">
            <a href="admin.php?section=horaires" class="btn primary">📋 Gérer les horaires</a>
            <a href="../public/horaire.php" class="btn secondary">👁️ Voir la page publique</a>
        </div>
    </div>
</body>
</html>
