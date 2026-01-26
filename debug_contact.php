<?php
// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Afficher les données POST reçues
echo "<h2>Données POST reçues :</h2>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

// Tester la connexion à la base de données
echo "<h2>Test de connexion à la base de données :</h2>";
try {
    require_once __DIR__ . '/config/db.php';
    echo "<p style='color: green;'>✅ Connexion à la base de données réussie.</p>";
    
    // Vérifier si la table contacts existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'contacts'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ La table 'contacts' existe.</p>";
        
        // Afficher la structure de la table
        echo "<h3>Structure de la table 'contacts' :</h3>";
        $structure = $pdo->query("DESCRIBE contacts")->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($structure);
        echo "</pre>";
        
        // Tester l'insertion d'un enregistrement de test
        try {
            $testData = [
                'nom' => 'Test Nom',
                'email' => 'test@example.com',
                'telephone' => '0123456789',
                'sujet' => 'Test Sujet',
                'message' => 'Ceci est un message de test',
                'type' => 'test'
            ];
            
            $stmt = $pdo->prepare('INSERT INTO contacts (nom, email, telephone, sujet, message, type, created_at) 
                                 VALUES (:nom, :email, :telephone, :sujet, :message, :type, NOW())');
            
            if ($stmt->execute($testData)) {
                $testId = $pdo->lastInsertId();
                echo "<p style='color: green;'>✅ Insertion de test réussie (ID: $testId)</p>";
                
                // Supprimer l'enregistrement de test
                $pdo->exec("DELETE FROM contacts WHERE id = $testId");
                echo "<p>Enregistrement de test supprimé.</p>";
            } else {
                echo "<p style='color: red;'>❌ Échec de l'insertion de test.</p>";
                echo "<pre>Erreur: ";
                print_r($stmt->errorInfo());
                echo "</pre>";
            }
            
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Erreur lors de l'insertion de test: " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ La table 'contacts' n'existe pas dans la base de données.</p>";
        
        // Créer la table si elle n'existe pas
        echo "<h3>Création de la table 'contacts' :</h3>";
        try {
            $sql = "CREATE TABLE IF NOT EXISTS contacts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nom VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL,
                telephone VARCHAR(20) NOT NULL,
                sujet VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                type VARCHAR(50) DEFAULT 'contact',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            
            $pdo->exec($sql);
            echo "<p style='color: green;'>✅ Table 'contacts' créée avec succès.</p>";
            
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Erreur lors de la création de la table: " . $e->getMessage() . "</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erreur de connexion à la base de données: " . $e->getMessage() . "</p>";
    
    // Afficher les informations de connexion utilisées
    $config = [
        'host' => 'localhost',
        'dbname' => 'eden_db',
        'user' => 'root',
        'pass' => ''
    ];
    
    echo "<h3>Configuration de connexion :</h3>";
    echo "<pre>";
    print_r($config);
    echo "</pre>";
}

// Tester l'accès en écriture au répertoire
echo "<h2>Test des permissions :</h2>";
$logFile = __DIR__ . '/contact_debug.log';
if (file_put_contents($logFile, date('Y-m-d H:i:s') . " - Test d'écriture\n", FILE_APPEND) !== false) {
    echo "<p style='color: green;'>✅ Le serveur a les permissions d'écriture sur le répertoire.</p>";
    unlink($logFile); // Supprimer le fichier de test
} else {
    echo "<p style='color: red;'>❌ Le serveur n'a pas les permissions d'écriture sur le répertoire.</p>";
}

// Vérifier si les extensions PDO sont chargées
echo "<h2>Extensions PHP chargées :</h2>";
echo "<pre>";
print_r(get_loaded_extensions());
echo "</pre>";

// Vérifier la configuration PHP
echo "<h2>Configuration PHP :</h2>";
echo "<pre>";
print_r(ini_get_all(null, false));
echo "</pre>";
?>

<h2>Test du formulaire :</h2>
<form method="post" action="">
    <div>
        <label>Nom :</label>
        <input type="text" name="nom" required>
    </div>
    <div>
        <label>Email :</label>
        <input type="email" name="email" required>
    </div>
    <div>
        <label>Téléphone :</label>
        <input type="text" name="telephone" required>
    </div>
    <div>
        <label>Sujet :</label>
        <input type="text" name="sujet" required>
    </div>
    <div>
        <label>Message :</label>
        <textarea name="message" required></textarea>
    </div>
    <button type="submit">Tester l'envoi</button>
</form>
