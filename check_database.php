<?php
require_once __DIR__ . '/config/db.php';

echo "<h1>Vérification de la base de données</h1>";

try {
    // Vérifier la connexion à la base de données
    echo "<h2>1. Test de connexion</h2>";
    echo "<p style='color: green;'>✓ Connexion à la base de données réussie</p>";
    
    // Vérifier si la table enseignements existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'enseignements'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "<h2>2. Création de la table 'enseignements'</h2>";
        
        $sql = "CREATE TABLE IF NOT EXISTS enseignements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            auteur VARCHAR(255) NOT NULL,
            date_publication DATE NOT NULL,
            contenu LONGTEXT NOT NULL,
            fichier_url VARCHAR(500) DEFAULT NULL,
            image_url VARCHAR(500) DEFAULT NULL,
            description TEXT,
            categorie VARCHAR(100) DEFAULT 'Général',
            duree_minutes INT DEFAULT 0,
            est_public TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $pdo->exec($sql);
        
        // Ajouter des index
        $pdo->exec("CREATE INDEX idx_enseignements_categorie ON enseignements (categorie)");
        $pdo->exec("CREATE INDEX idx_enseignements_public ON enseignements (est_public)");
        
        echo "<p style='color: green;'>✓ Table 'enseignements' créée avec succès</p>";
        
        // Ajouter un exemple d'enseignement
        $sql = "INSERT INTO enseignements (titre, auteur, date_publication, contenu, description, categorie, duree_minutes) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        $titre = "La grâce de Dieu";
        $auteur = "Pasteur Jean Dupont";
        $date = date('Y-m-d');
        $contenu = "<p>La grâce de Dieu est un don immérité que nous recevons par la foi en Jésus-Christ. Elle nous est offerte non pas à cause de nos œuvres, mais selon la miséricorde de Dieu.</p>";
        $description = "Un enseignement profond sur la grâce de Dieu et son impact dans notre vie quotidienne.";
        $categorie = "Doctrine";
        $duree = 45;
        
        $stmt->execute([$titre, $auteur, $date, $contenu, $description, $categorie, $duree]);
        
        echo "<p style='color: green;'>✓ Exemple d'enseignement ajouté avec succès</p>";
    } else {
        echo "<h2>2. Vérification de la table 'enseignements'</h2>";
        echo "<p style='color: green;'>✓ La table 'enseignements' existe déjà</p>";
        
        // Afficher le nombre d'enseignements
        $count = $pdo->query("SELECT COUNT(*) FROM enseignements")->fetchColumn();
        echo "<p>Nombre d'enseignements dans la base de données : <strong>$count</strong></p>";
    }
    
    // Afficher les enseignements existants
    echo "<h2>3. Liste des enseignements</h2>";
    $enseignements = $pdo->query("SELECT * FROM enseignements")->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($enseignements) > 0) {
        echo "<table border='1' cellpadding='10' style='width: 100%; border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Titre</th><th>Auteur</th><th>Catégorie</th><th>Date</th></tr>";
        foreach ($enseignements as $enseignement) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($enseignement['id']) . "</td>";
            echo "<td>" . htmlspecialchars($enseignement['titre']) . "</td>";
            echo "<td>" . htmlspecialchars($enseignement['auteur']) . "</td>";
            echo "<td>" . htmlspecialchars($enseignement['categorie']) . "</td>";
            echo "<td>" . $enseignement['date_publication'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>Aucun enseignement trouvé dans la base de données.</p>";
    }
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Erreur</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    
    // Afficher les informations de connexion (à des fins de débogage uniquement)
    echo "<h3>Détails de connexion</h3>";
    echo "<pre>";
    print_r([
        'host' => $host ?? 'Non défini',
        'dbname' => $dbname ?? 'Non défini',
        'user' => $user ?? 'Non défini',
        'error' => $e->getMessage()
    ]);
    echo "</pre>";
}

echo "<p><a href='/public/enseignements.php'>Retour à la page des enseignements</a></p>";
?>
