<?php
// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Configuration de la base de données</h1>";

// Paramètres de connexion
$host = 'localhost';
$dbname = 'eden_db';
$user = 'root';
$pass = '';

try {
    // Connexion sans sélectionner de base de données
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✅ Connexion au serveur MySQL réussie.</p>";
    
    // Vérifier si la base de données existe
    $stmt = $pdo->query("SHOW DATABASES LIKE '$dbname'");
    
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ La base de données '$dbname' existe.</p>";
        
        // Sélectionner la base de données
        $pdo->exec("USE $dbname");
        
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
                    
                    // Afficher les données insérées
                    $testRecord = $pdo->query("SELECT * FROM contacts WHERE id = $testId")->fetch(PDO::FETCH_ASSOC);
                    echo "<h3>Enregistrement inséré :</h3>";
                    echo "<pre>";
                    print_r($testRecord);
                    echo "</pre>";
                    
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
            echo "<p style='color: orange;'>⚠️ La table 'contacts' n'existe pas. Création en cours...</p>";
            
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
        
    } else {
        echo "<p style='color: red;'>❌ La base de données '$dbname' n'existe pas.</p>";
        
        // Créer la base de données
        try {
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "<p style='color: green;'>✅ Base de données '$dbname' créée avec succès.</p>";
            
            // Sélectionner la base de données
            $pdo->exec("USE $dbname");
            
            // Créer la table
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
            echo "<p style='color: red;'>❌ Erreur lors de la création de la base de données: " . $e->getMessage() . "</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erreur de connexion à la base de données: " . $e->getMessage() . "</p>";
    
    // Afficher les informations de connexion utilisées
    $config = [
        'host' => $host,
        'dbname' => $dbname,
        'user' => $user,
        'pass' => $pass
    ];
    
    echo "<h3>Configuration de connexion :</h3>";
    echo "<pre>";
    print_r($config);
    echo "</pre>
    <p>Vérifiez que :</p>
    <ul>
        <li>Le serveur MySQL est en cours d'exécution</li>
        <li>L'utilisateur a les permissions nécessaires</li>
        <li>Le mot de passe est correct</li>
        <li>La base de données existe</li>
    </ul>";
}

// Vérifier les permissions du répertoire
echo "<h2>Vérification des permissions :</h2>";
$logDir = __DIR__ . '/logs';
$logFile = $logDir . '/contact_errors.log';

if (!is_dir($logDir)) {
    if (mkdir($logDir, 0755, true)) {
        echo "<p style='color: green;'>✅ Répertoire de logs créé : $logDir</p>";
    } else {
        echo "<p style='color: red;'>❌ Impossible de créer le répertoire de logs : $logDir</p>";
    }
} else {
    echo "<p style='color: green;'>✅ Le répertoire de logs existe : $logDir</p>";
}

if (is_writable($logDir)) {
    echo "<p style='color: green;'>✅ Le répertoire de logs est accessible en écriture</p>";
    
    // Tester l'écriture dans le fichier de log
    $testMessage = date('Y-m-d H:i:s') . " - Test d'écriture dans le fichier de log\n";
    if (file_put_contents($logFile, $testMessage, FILE_APPEND) !== false) {
        echo "<p style='color: green;'>✅ Le fichier de log est accessible en écriture : $logFile</p>";
        
        // Afficher le contenu du fichier de log
        $logContent = file_get_contents($logFile);
        echo "<h3>Contenu du fichier de log :</h3>";
        echo "<pre>" . htmlspecialchars($logContent) . "</pre>";
    } else {
        echo "<p style='color: red;'>❌ Impossible d'écrire dans le fichier de log : $logFile</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Le répertoire de logs n'est pas accessible en écriture : $logDir</p>";
    echo "<p>Veuillez exécuter la commande suivante pour corriger les permissions :</p>";
    echo "<pre>chmod 755 " . escapeshellarg($logDir) . "</pre>";
}

// Vérifier les extensions PHP requises
echo "<h2>Vérification des extensions PHP :</h2>";
$requiredExtensions = ['pdo_mysql', 'mbstring'];
$allOk = true;

foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p style='color: green;'>✅ Extension $ext est chargée</p>";
    } else {
        echo "<p style='color: red;'>❌ Extension $ext n'est pas chargée</p>";
        $allOk = false;
    }
}

if ($allOk) {
    echo "<p style='color: green; font-weight: bold;'>✅ Toutes les extensions requises sont chargées</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Certaines extensions requises ne sont pas chargées</p>";
    echo "<p>Veuillez activer les extensions manquantes dans votre fichier php.ini et redémarrer votre serveur web.</p>";
}

// Lien pour tester le formulaire de contact
echo "<h2>Prochaines étapes :</h2>";
echo "<ol>";
echo "<li><a href='/public/contact.php' target='_blank'>Tester le formulaire de contact</a></li>";
echo "<li><a href='mailto:contact@example.com'>Contacter l'administrateur</a> en cas de problème persistant</li>";
echo "</ol>";
?>
