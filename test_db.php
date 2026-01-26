<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'eden_db';
$user = 'root';
$pass = '';

echo "<h2>Test de Connexion à la Base de Données</h2>";

// Test 1: Connexion
try {
    echo "<p><strong>Test 1: Connexion PDO...</strong></p>";
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Connexion réussie</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erreur de connexion: " . $e->getMessage() . "</p>";
    die();
}

// Test 2: Vérifier les tables
echo "<p><strong>Test 2: Tables existantes...</strong></p>";
try {
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (empty($tables)) {
        echo "<p style='color: orange;'>⚠️ Aucune table trouvée</p>";
    } else {
        echo "<p style='color: green;'>✅ Tables trouvées: " . implode(', ', $tables) . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur: " . $e->getMessage() . "</p>";
}

// Test 3: Vérifier la table enseignements
echo "<p><strong>Test 3: Table enseignements...</strong></p>";
try {
    $exists = $pdo->query("SHOW TABLES LIKE 'enseignements'")->rowCount() > 0;
    if ($exists) {
        echo "<p style='color: green;'>✅ Table enseignements existe</p>";
        
        // Afficher la structure
        $columns = $pdo->query("SHOW COLUMNS FROM enseignements")->fetchAll(PDO::FETCH_ASSOC);
        echo "<p>Colonnes:</p>";
        echo "<ul>";
        foreach ($columns as $col) {
            echo "<li>" . $col['Field'] . " (" . $col['Type'] . ")</li>";
        }
        echo "</ul>";
        
        // Compter les enregistrements
        $count = $pdo->query("SELECT COUNT(*) FROM enseignements")->fetchColumn();
        echo "<p>Total d'enseignements: <strong>" . $count . "</strong></p>";
        
        // Compter les publics
        $publicCount = $pdo->query("SELECT COUNT(*) FROM enseignements WHERE est_public = 1")->fetchColumn();
        echo "<p>Enseignements publics (est_public = 1): <strong>" . $publicCount . "</strong></p>";
        
        // Afficher les données
        if ($count > 0) {
            echo "<p><strong>Aperçu des données:</strong></p>";
            $data = $pdo->query("SELECT * FROM enseignements LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
            echo "<table border='1' cellpadding='10'>";
            echo "<tr>";
            foreach (array_keys($data[0]) as $key) {
                echo "<th>" . htmlspecialchars($key) . "</th>";
            }
            echo "</tr>";
            foreach ($data as $row) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars((string)$value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Table enseignements n'existe pas encore (sera créée au premier accès)</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur: " . $e->getMessage() . "</p>";
}

// Test 4: Vérifier la table retreats
echo "<p><strong>Test 4: Table retreats...</strong></p>";
try {
    $exists = $pdo->query("SHOW TABLES LIKE 'retreats'")->rowCount() > 0;
    if ($exists) {
        echo "<p style='color: green;'>✅ Table retreats existe</p>";
        $count = $pdo->query("SELECT COUNT(*) FROM retreats")->fetchColumn();
        echo "<p>Total de retraites: <strong>" . $count . "</strong></p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Table retreats n'existe pas</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='public/enseignements.php'>Retour à la page enseignements</a></p>";
?>
