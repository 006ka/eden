<?php
require_once __DIR__ . '/../config/db.php';

// Récupérer l'ID de l'enseignement
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: enseignements.php');
    exit;
}

// Récupérer l'enseignement
$enseignement = null;
$error = null;

try {
    $stmt = $pdo->prepare("SELECT e.*, r.titre as retreat_titre 
                          FROM enseignements e 
                          LEFT JOIN retreats r ON e.retraite_id = r.id 
                          WHERE e.id = ? AND e.est_public = 1");
    $stmt->execute([$id]);
    $enseignement = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$enseignement) {
        $error = "Cet enseignement n'existe pas ou n'est pas public.";
    }
} catch (PDOException $e) {
    $error = "Une erreur est survenue lors du chargement de l'enseignement.";
    error_log('Erreur enseignement.php : ' . $e->getMessage());
}

$pageTitle = $enseignement ? htmlspecialchars($enseignement['titre']) . " - EDEN" : "Enseignement - EDEN";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        .enseignement-detail { max-width: 900px; margin: 0 auto; }
        .enseignement-header { display: grid; grid-template-columns: 1fr 300px; gap: 30px; margin-bottom: 40px; }
        .enseignement-content { padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .pdf-viewer { width: 100%; height: 800px; border: 1px solid #ddd; border-radius: 8px; }
        @media (max-width: 768px) {
            .enseignement-header { grid-template-columns: 1fr; }
            .pdf-viewer { height: 600px; }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="container" style="padding: 40px 20px; max-width: 1200px; margin: 0 auto;">
    
    <?php if (!empty($error)): ?>
        <div style="background: #fee; color: #c33; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #c33; text-align: center;">
            <p><?php echo htmlspecialchars($error); ?></p>
            <a href="enseignements.php" style="color: #c33; text-decoration: underline;">← Retour aux enseignements</a>
        </div>
    <?php elseif ($enseignement): ?>
        
        <div style="margin-bottom: 30px;">
            <a href="enseignements.php" style="color: #2c5aa0; text-decoration: none; font-weight: 600;">← Retour aux enseignements</a>
        </div>
        
        <div class="enseignement-detail">
            <!-- En-tête -->
            <div class="enseignement-header">
                <div>
                    <!-- Badge retraite -->
                    <?php if (!empty($enseignement['retreat_titre'])): ?>
                        <span style="display: inline-block; background: #2c5aa0; color: white; padding: 6px 12px; border-radius: 20px; font-size: 0.9em; margin-bottom: 15px;">
                            🏫 <?php echo htmlspecialchars($enseignement['retreat_titre']); ?>
                        </span>
                    <?php elseif (!empty($enseignement['categorie'])): ?>
                        <span style="display: inline-block; background: #666; color: white; padding: 6px 12px; border-radius: 20px; font-size: 0.9em; margin-bottom: 15px;">
                            📂 <?php echo htmlspecialchars($enseignement['categorie']); ?>
                        </span>
                    <?php endif; ?>
                    
                    <h1 style="font-size: 2.2em; font-weight: 700; color: #2c5aa0; margin: 15px 0;">
                        <?php echo htmlspecialchars($enseignement['titre']); ?>
                    </h1>
                    
                    <!-- Métadonnées -->
                    <div style="margin: 20px 0; padding: 20px; background: #f5f5f5; border-radius: 8px; border-left: 4px solid #2c5aa0;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; font-size: 1em; color: #333;">
                            <div>
                                <span style="font-weight: 600; color: #2c5aa0;">👤 Auteur</span><br>
                                <?php echo htmlspecialchars($enseignement['auteur']); ?>
                            </div>
                            <div>
                                <span style="font-weight: 600; color: #2c5aa0;">📅 Date</span><br>
                                <?php echo date('d F Y', strtotime($enseignement['date_publication'])); ?>
                            </div>
                            <?php if ($enseignement['duree_minutes'] > 0): ?>
                                <div>
                                    <span style="font-weight: 600; color: #2c5aa0;">⏱️ Durée</span><br>
                                    <?php echo $enseignement['duree_minutes']; ?> minutes
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <?php if (!empty($enseignement['description'])): ?>
                        <div style="margin: 20px 0; padding: 20px; background: #f9f9f9; border-radius: 8px; border-left: 4px solid #ddd;">
                            <h3 style="color: #2c5aa0; margin-top: 0;">Description</h3>
                            <p style="color: #555; line-height: 1.7; margin: 0;">
                                <?php echo nl2br(htmlspecialchars($enseignement['description'])); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Image -->
                <div>
                    <div style="height: 350px; background: #f0f0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                        <?php if (!empty($enseignement['image_url'])): ?>
                            <?php
                                $fullImagePath = __DIR__ . '/../' . $enseignement['image_url'];
                                if (file_exists($fullImagePath)):
                            ?>
                                <img src="serve-file.php?file=<?php echo urlencode($enseignement['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($enseignement['titre']); ?>"
                                     style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <div style="text-align: center;">
                                    <div style="font-size: 4em; margin-bottom: 10px;">🖼️</div>
                                    <p style="color: #999;">Image non trouvée</p>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div style="text-align: center;">
                                <div style="font-size: 4em; margin-bottom: 10px;">📚</div>
                                <p style="color: #999;">Pas d'image</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Contenu PDF/Audio -->
            <?php if (!empty($enseignement['fichier_url'])): ?>
                <?php
                    $fullPath = __DIR__ . '/../' . $enseignement['fichier_url'];
                    if (file_exists($fullPath)):
                ?>
                <div class="enseignement-content" style="margin-top: 40px;">
                    <h2 style="color: #2c5aa0; margin-top: 0;">📄 Ressource</h2>
                    
                    <?php
                        $fileExt = strtolower(pathinfo($enseignement['fichier_url'], PATHINFO_EXTENSION));
                        $serveUrl = 'serve-file.php?file=' . urlencode($enseignement['fichier_url']);
                        
                        if ($fileExt === 'pdf'):
                    ?>
                        <p style="color: #666; margin-bottom: 15px;">Consultez le document PDF ci-dessous :</p>
                        <iframe src="<?php echo $serveUrl; ?>#toolbar=0&navpanes=0&scrollbar=1" class="pdf-viewer"></iframe>
                        <div style="margin-top: 15px;">
                            <a href="<?php echo $serveUrl; ?>" download style="display: inline-block; background: #2c5aa0; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; font-weight: 600; transition: background 0.2s;" onmouseover="this.style.background='#1e3f70'" onmouseout="this.style.background='#2c5aa0'">
                                ⬇️ Télécharger le PDF
                            </a>
                        </div>
                    <?php elseif (in_array($fileExt, ['mp3', 'wav', 'm4a', 'ogg'])): ?>
                        <p style="color: #666; margin-bottom: 15px;">Écoutez l'enregistrement audio :</p>
                        <audio controls style="width: 100%; margin: 20px 0; outline: none;">
                            <source src="<?php echo $serveUrl; ?>" type="audio/<?php echo ($fileExt === 'mp3' ? 'mpeg' : $fileExt); ?>">
                            Votre navigateur ne supporte pas la lecture audio.
                        </audio>
                        <div style="margin-top: 15px;">
                            <a href="<?php echo $serveUrl; ?>" download style="display: inline-block; background: #2c5aa0; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; font-weight: 600; transition: background 0.2s;" onmouseover="this.style.background='#1e3f70'" onmouseout="this.style.background='#2c5aa0'">
                                ⬇️ Télécharger l'audio
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                    <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 8px; margin-top: 20px; color: #856404;">
                        ⚠️ Le fichier PDF n'existe pas sur le serveur.
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- Autres enseignements -->
            <div style="margin-top: 60px; padding-top: 40px; border-top: 2px solid #e0e0e0;">
                <h3 style="color: #2c5aa0; font-size: 1.3em;">Autres enseignements</h3>
                <p style="color: #666; margin-bottom: 20px;">
                    <a href="enseignements.php" style="color: #2c5aa0; text-decoration: none; font-weight: 600;">
                        Voir tous les enseignements →
                    </a>
                </p>
            </div>
        </div>
        
    <?php else: ?>
        <div style="text-align: center; padding: 40px;">
            <p>Enseignement non trouvé.</p>
            <a href="enseignements.php">Retour aux enseignements</a>
        </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
