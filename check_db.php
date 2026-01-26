<?php
// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Vérification de la base de données</h1>";

// Informations de connexion
$host = 'localhost';
$dbname = 'eden_db';
$user = 'root';
$pass = '';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✅ Connexion à la base de données réussie.</p>";
    
    // Vérifier si la table contacts existe
    $tables = $pdo->query("SHOW TABLES LIKE 'contacts'")->fetchAll();
    
    if (count($tables) > 0) {
        echo "<p style='color: green;'>✅ La table 'contacts' existe.</p>";
        
        // Afficher la structure de la table
        $structure = $pdo->query("DESCRIBE contacts")->fetchAll(PDO::FETCH_ASSOC);
        echo "<h3>Structure de la table 'contacts' :</h3>";
        echo "<pre>";
        print_r($structure);
        echo "</pre>";
        
        // Tester l'insertion d'un enregistrement
        try {
            $stmt = $pdo->prepare("INSERT INTO contacts (nom, email, telephone, sujet, message, type) VALUES (?, ?, ?, ?, ?, ?)");
            $result = $stmt->execute([
                'Test Nom',
                'test@example.com',
                '0123456789',
                'Test Sujet',
                'Ceci est un message de test',
                'test'
            ]);
            
            if ($result) {
                $id = $pdo->lastInsertId();
                echo "<p style='color: green;'>✅ Insertion de test réussie (ID: $id)</p>";
                
                // Supprimer l'enregistrement de test
                $pdo->exec("DELETE FROM contacts WHERE id = $id");
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
        echo "<p style='color: red;'>❌ La table 'contacts' n'existe pas.</p>";
        
        // Créer la table
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
}

// Vérifier les variables POST
echo "<h3>Variables POST :</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

// Vérifier les erreurs PHP
$errors = error_get_last();
if ($errors !== null) {
    echo "<h3>Dernière erreur PHP :</h3>";
    echo "<pre>";
    print_r($errors);
    echo "</pre>";
}
?>
