<?php
// Essayer différents chemins pour le fichier de configuration
define('BASE_PATH', dirname(__DIR__));

$possible_db_paths = [
    __DIR__ . '/../config/db.php',    // Pour accès direct
    __DIR__ . '/config/db.php',       // Si le fichier est dans le même dossier
    BASE_PATH . '/config/db.php',     // Chemin absolu
    'C:/wamp64/www/eden/config/db.php' // Chemin absolu Windows
];

$db_loaded = false;
foreach ($possible_db_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $db_loaded = true;
        break;
    }
}

if (!$db_loaded) {
    die("Erreur : Impossible de trouver le fichier de configuration de la base de données.");
}

try {
    // Fonction pour vérifier si une colonne existe
    function columnExists($pdo, $table, $column) {
        try {
            $result = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
            return $result->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Liste des colonnes à ajouter
    $columns = [
        [
            'name' => 'location',
            'definition' => 'VARCHAR(255) DEFAULT NULL AFTER author'
        ],
        [
            'name' => 'rating',
            'definition' => 'INT NOT NULL DEFAULT 5 AFTER content'
        ],
        [
            'name' => 'is_approved',
            'definition' => 'BOOLEAN DEFAULT FALSE'
        ],
        [
            'name' => 'updated_at',
            'definition' => 'DATETIME DEFAULT NULL'
        ]
    ];

    // Ajout des colonnes manquantes
    foreach ($columns as $column) {
        if (!columnExists($pdo, 'testimonials', $column['name'])) {
            try {
                $query = "ALTER TABLE testimonials ADD COLUMN `{$column['name']}` {$column['definition']}";
                $pdo->exec($query);
                echo "Colonne '{$column['name']}' ajoutée avec succès.<br>";
            } catch (PDOException $e) {
                echo "Erreur lors de l'ajout de la colonne '{$column['name']}': " . $e->getMessage() . "<br>";
            }
        } else {
            echo "La colonne '{$column['name']}' existe déjà.<br>";
        }
    }
    
    // Création des index
    try {
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_approved ON testimonials (is_approved)");
        echo "Index 'idx_approved' créé avec succès.<br>";
    } catch (PDOException $e) {
        echo "Erreur lors de la création de l'index 'idx_approved': " . $e->getMessage() . "<br>";
    }
    
    echo "La table testimonials a été mise à jour avec succès !";
} catch (PDOException $e) {
    echo "Erreur lors de la mise à jour de la table : " . $e->getMessage();
}