<?php
require_once __DIR__ . '/../config/db.php';

// Récupérer les paramètres de filtrage/pagination
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 9;
$offset = ($page - 1) * $perPage;

// Initialisation
$enseignements = [];
$totalEnseignements = 0;
$totalPages = 0;
$error = null;

try {
    // Vérifier et créer la table si nécessaire
    $tableExists = $pdo->query("SHOW TABLES LIKE 'enseignements'")->rowCount() > 0;
    
    if (!$tableExists) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS enseignements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            auteur VARCHAR(255) NOT NULL,
            date_publication DATE NOT NULL,
            contenu LONGTEXT,
            fichier_url VARCHAR(500) DEFAULT NULL,
            image_url VARCHAR(500) DEFAULT NULL,
            description TEXT,
            retraite_id INT NULL,
            duree_minutes INT DEFAULT 0,
            est_public TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (retraite_id) REFERENCES retreats(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        
        $pdo->exec("CREATE INDEX idx_enseignements_public ON enseignements (est_public)");
        $pdo->exec("CREATE INDEX idx_enseignements_retraite ON enseignements (retraite_id)");
    }
    
    // Vérifier et ajouter la colonne retraite_id si elle n'existe pas
    $colExists = $pdo->query("SHOW COLUMNS FROM enseignements LIKE 'retraite_id'")->rowCount() > 0;
    if (!$colExists) {
        try {
            $pdo->exec("ALTER TABLE enseignements ADD COLUMN retraite_id INT NULL");
            $pdo->exec("ALTER TABLE enseignements ADD FOREIGN KEY (retraite_id) REFERENCES retreats(id) ON DELETE SET NULL");
            $pdo->exec("CREATE INDEX idx_enseignements_retraite ON enseignements (retraite_id)");
        } catch (Exception $e) {
            // Colonne déjà existante
        }
    }
    
    // Requête de comptage
    $countQuery = "SELECT COUNT(*) FROM enseignements e WHERE e.est_public = 1";
    $params = [];
    
    if (!empty($search)) {
        $countQuery .= " AND (e.titre LIKE ? OR e.auteur LIKE ? OR e.description LIKE ?)";
        $searchTerm = "%$search%";
        $params = [$searchTerm, $searchTerm, $searchTerm];
    }
    
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $totalEnseignements = (int)$stmt->fetchColumn();
    $totalPages = $perPage > 0 ? ceil($totalEnseignements / $perPage) : 0;
    
    // Requête pour récupérer les enseignements
    $query = "SELECT e.*, r.titre as retreat_titre FROM enseignements e 
              LEFT JOIN retreats r ON e.retraite_id = r.id 
              WHERE e.est_public = 1";
    
    $selectParams = [];
    if (!empty($search)) {
        $query .= " AND (e.titre LIKE ? OR e.auteur LIKE ? OR e.description LIKE ?)";
        $selectParams = [$searchTerm, $searchTerm, $searchTerm];
    }
    
    $query .= " ORDER BY e.date_publication DESC LIMIT " . $perPage . " OFFSET " . $offset;
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($selectParams);
    $enseignements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Une erreur est survenue lors du chargement des enseignements.";
    error_log('Erreur dans enseignements.php : ' . $e->getMessage());
}

$pageTitle = "Enseignements - EDEN";
$base = '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>

<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="container" style="padding: 40px 20px; max-width: 1200px; margin: 0 auto;">
    <div style="text-align: center; margin-bottom: 40px;">
        <h1 style="font-size: 2.5em; font-weight: 700; color: #2c5aa0; margin-bottom: 10px;">Enseignements</h1>
        <p style="font-size: 1.1em; color: #666;">Découvrez nos enseignements bibliques et ressources spirituelles</p>
    </div>
    
    <!-- Barre de recherche -->
    <div style="background: #f5f5f5; padding: 20px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <form method="get" action="" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <input type="text" 
                   name="search" 
                   placeholder="Rechercher un enseignement..." 
                   value="<?php echo htmlspecialchars($search); ?>"
                   style="flex: 1; min-width: 200px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 1em;">
            <button type="submit" style="background: #2c5aa0; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; transition: background 0.2s;" onmouseover="this.style.background='#1e3f70'" onmouseout="this.style.background='#2c5aa0'">
                Rechercher
            </button>
        </form>
    </div>
    
    <!-- Message d'erreur -->
    <?php if (!empty($error)): ?>
        <div style="background: #fee; color: #c33; padding: 15px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #c33;">
            ⚠️ <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <!-- Grille des enseignements -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <?php if (!empty($enseignements)): ?>
            <?php foreach ($enseignements as $e): ?>
                <article style="background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; border: 1px solid #e0e0e0;" 
                         onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.12)'" 
                         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)'">
                    
                    <!-- Image -->
                    <div style="height: 200px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                        <?php if (!empty($e['image_url'])): ?>
                            <?php
                                $fullImagePath = __DIR__ . '/../' . $e['image_url'];
                                if (file_exists($fullImagePath)):
                            ?>
                                <img src="serve-file.php?file=<?php echo urlencode($e['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($e['titre']); ?>"
                                     style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <div style="font-size: 3em; color: #ccc;">📚</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div style="font-size: 3em; color: #ccc;">📚</div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Contenu -->
                    <div style="padding: 15px;">
                        <!-- Badge retraite/catégorie -->
                        <?php if (!empty($e['retreat_titre'])): ?>
                            <span style="display: inline-block; background: #2c5aa0; color: white; padding: 4px 10px; border-radius: 12px; font-size: 0.8em; margin-bottom: 8px;">
                                <?php echo htmlspecialchars($e['retreat_titre']); ?>
                            </span>
                        <?php elseif (!empty($e['categorie'])): ?>
                            <span style="display: inline-block; background: #666; color: white; padding: 4px 10px; border-radius: 12px; font-size: 0.8em; margin-bottom: 8px;">
                                <?php echo htmlspecialchars($e['categorie']); ?>
                            </span>
                        <?php endif; ?>
                        
                        <!-- Titre -->
                        <h3 style="font-size: 1.1em; font-weight: 600; color: #2c5aa0; margin: 8px 0;">
                            <?php echo htmlspecialchars($e['titre']); ?>
                        </h3>
                        
                        <!-- Métadonnées -->
                        <div style="font-size: 0.9em; color: #666; margin-bottom: 10px;">
                            <div>👤 <?php echo htmlspecialchars($e['auteur']); ?></div>
                            <?php if ($e['duree_minutes'] > 0): ?>
                                <div>⏱️ <?php echo $e['duree_minutes']; ?> min</div>
                            <?php endif; ?>
                            <div>📅 <?php echo date('d/m/Y', strtotime($e['date_publication'])); ?></div>
                        </div>
                        
                        <!-- Description -->
                        <p style="color: #555; font-size: 0.95em; margin: 10px 0; line-height: 1.5;">
                            <?php echo htmlspecialchars(mb_substr($e['description'] ?? '', 0, 100) . (strlen($e['description'] ?? '') > 100 ? '...' : '')); ?>
                        </p>
                        
                        <!-- Bouton -->
                        <a href="enseignement.php?id=<?php echo $e['id']; ?>" 
                           style="display: inline-block; background: #2c5aa0; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-size: 0.9em; margin-top: 10px; transition: background 0.2s;"
                           onmouseover="this.style.background='#1e3f70'"
                           onmouseout="this.style.background='#2c5aa0'">
                            Lire la suite →
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px 20px; background: #f5f5f5; border-radius: 8px;">
                <div style="font-size: 3em; margin-bottom: 10px;">📭</div>
                <h3 style="color: #666;">Aucun enseignement trouvé</h3>
                <p style="color: #999;">Revenez bientôt pour découvrir nos nouveaux enseignements.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav style="display: flex; justify-content: center; gap: 5px; margin-top: 30px; flex-wrap: wrap;">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                   style="padding: 8px 12px; background: #2c5aa0; color: white; border-radius: 4px; text-decoration: none; transition: background 0.2s;"
                   onmouseover="this.style.background='#1e3f70'"
                   onmouseout="this.style.background='#2c5aa0'">← Précédent</a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                   style="padding: 8px 12px; background: <?php echo ($i == $page) ? '#2c5aa0' : '#f5f5f5'; ?>; color: <?php echo ($i == $page) ? 'white' : '#2c5aa0'; ?>; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; transition: background 0.2s;"
                   onmouseover="this.style.background='<?php echo ($i == $page) ? '#1e3f70' : '#e5e5e5'; ?>'"
                   onmouseout="this.style.background='<?php echo ($i == $page) ? '#2c5aa0' : '#f5f5f5'; ?>'">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                   style="padding: 8px 12px; background: #2c5aa0; color: white; border-radius: 4px; text-decoration: none; transition: background 0.2s;"
                   onmouseover="this.style.background='#1e3f70'"
                   onmouseout="this.style.background='#2c5aa0'">Suivant →</a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
