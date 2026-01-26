<?php
require_once __DIR__ . '/../config/db.php';

// Gestion de la soumission du formulaire
$success = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_testimonial'])) {
    try {
        // Validation des données
        $author = trim($_POST['author'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $rating = (int)($_POST['rating'] ?? 5);
        
        if (empty($author) || empty($content)) {
            throw new Exception('Veuillez remplir tous les champs obligatoires.');
        }
        
        // Gestion de l'upload de l'image
        $imageUrl = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/testimonials/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (!in_array($fileExt, $allowedExts)) {
                throw new Exception('Format de fichier non supporté. Formats acceptés : ' . implode(', ', $allowedExts));
            }
            
            $fileName = uniqid('testimonial_') . '.' . $fileExt;
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $imageUrl = '/uploads/testimonials/' . $fileName;
            } else {
                throw new Exception('Erreur lors de l\'upload du fichier.');
            }
        }
        
        // Insertion en base de données
        $stmt = $pdo->prepare('INSERT INTO testimonials (author, content, location, rating, image_url, is_approved, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())');
        $stmt->execute([$author, $content, $location, $rating, $imageUrl]);
        
        $success = true;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Récupération des témoignages approuvés
$testimonials = $pdo->query('SELECT * FROM testimonials WHERE is_approved = 1 ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Témoignages - EDEN</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        .testimonials-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap:18px; }
        .testimonial-card { background: white; padding:18px; border-radius:10px; box-shadow: 0 6px 18px rgba(0,0,0,0.06); }
        .testimonial-photo { width:72px; height:72px; border-radius:50%; overflow:hidden; flex:0 0 72px; }
        .testimonial-meta { display:flex; gap:12px; align-items:center; }
    </style>
</head>
<body>
    <?php $base = ''; include __DIR__ . '/../includes/header.php'; ?>

    <section class="hero-section" style="min-height:240px;">
        <div class="overlay"></div>
        <div class="container hero-content">
            <p class="new-here-link">Témoignages</p>
            <h1>Vécu & Transformation</h1>
            <p class="subtitle">Des histoires réelles de vies changées à travers les retraites EDEN.</p>
        </div>
    </section>

    <section class="contact-map-section">
        <div class="container">
            <div class="testimonial-form-container" style="background: #f8f9fa; padding: 2rem; border-radius: 10px; margin-bottom: 3rem; text-align: center;">
                <h2 style="margin-top: 0;">Partagez votre expérience</h2>
                <p style="color: #666; margin-bottom: 20px;">Votre témoignage nous aide à grandir et inspire d'autres personnes.</p>
                <button type="button" onclick="openTestimonialModal()" style="background: #2c5aa0; color: white; padding: 12px 30px; border: none; border-radius: 6px; font-size: 1em; font-weight: 600; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#1e3f70'" onmouseout="this.style.background='#2c5aa0'">
                    ✍️ Publier mon témoignage
                </button>
            </div>
                
                <style>
                    .testimonial-form {
                        display: grid;
                        gap: 1.5rem;
                        max-width: 800px;
                        margin: 0 auto;
                    }
                    
                    .form-row {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 1.5rem;
                    }
                    
                    @media (max-width: 768px) {
                        .form-row {
                            grid-template-columns: 1fr;
                        }
                    }
                    
                    .form-group {
                        margin-bottom: 0;
                    }
                    
                    .form-group label {
                        display: block;
                        margin-bottom: 0.5rem;
                        font-weight: 500;
                        color: #2d3748;
                    }
                    
                    .form-group input[type="text"],
                    .form-group textarea,
                    .form-group select {
                        width: 100%;
                        padding: 0.75rem 1rem;
                        border: 1px solid #e2e8f0;
                        border-radius: 8px;
                        font-size: 1rem;
                        transition: all 0.2s ease;
                        background-color: #fff;
                    }
                    
                    .form-group textarea {
                        min-height: 120px;
                        resize: vertical;
                    }
                    
                    .form-group input[type="text"]:focus,
                    .form-group textarea:focus,
                    .form-group select:focus {
                        outline: none;
                        border-color: #4a6baf;
                        box-shadow: 0 0 0 3px rgba(74, 107, 175, 0.1);
                    }
                    
                    .rating-select {
                        appearance: none;
                        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%234b5563' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
                        background-repeat: no-repeat;
                        background-position: right 0.75rem center;
                        background-size: 12px 12px;
                        padding-right: 2.5rem;
                    }
                    
                    .file-input {
                        width: 100%;
                        padding: 0.5rem 0;
                        border: none;
                        background: none;
                    }
                    
                    .file-hint {
                        display: block;
                        margin-top: 0.5rem;
                        color: #718096;
                        font-size: 0.85rem;
                    }
                    
                    .submit-btn {
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        background: #4a6baf;
                        color: white;
                        border: none;
                        padding: 0.875rem 2rem;
                        border-radius: 8px;
                        font-weight: 500;
                        font-size: 1rem;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        margin-top: 0.5rem;
                    }
                    
                    .submit-btn:hover {
                        background: #3a5a9f;
                        transform: translateY(-2px);
                        box-shadow: 0 4px 12px rgba(74, 107, 175, 0.25);
                    }
                    
                    .submit-icon {
                        margin-left: 0.5rem;
                        transition: transform 0.2s ease;
                    }
                    
                    .submit-btn:hover .submit-icon {
                        transform: translateX(4px);
                    }
                </style>
            </div>

            <h2 style="margin-bottom: 1.5rem;">Témoignages</h2>
            <?php if (empty($testimonials)): ?>
                <p>Aucun témoignage disponible pour le moment. Soyez le premier à partager votre expérience !</p>
            <?php else: ?>
                <div class="testimonials-grid">
                    <?php foreach ($testimonials as $t): ?>
                        <article class="testimonial-card">
                            <div class="testimonial-meta">
                                <?php if (!empty($t['image_url'])): ?>
                                    <?php
                                        $src = $t['image_url'];
                                        // Supprime les parties de chemin inutiles
                                        $src = str_replace(['../', './'], '', $src);
                                        // Si le chemin ne commence pas par /, on l'ajoute
                                        if (strpos($src, '/') !== 0) {
                                            $src = '/' . $src;
                                        }
                                        // Vérifie si le fichier existe
                                        $filePath = $_SERVER['DOCUMENT_ROOT'] . $src;
                                        if (!file_exists($filePath)) {
                                            // Si le fichier n'existe pas, on essaie avec un chemin relatif
                                            $altPath = __DIR__ . '/..' . $src;
                                            if (file_exists($altPath)) {
                                                $src = '..' . $src;
                                            } else {
                                                // Si le fichier n'existe toujours pas, on utilise une image par défaut
                                                $src = '/assets/images/default-avatar.jpg';
                                            }
                                        }
                                    ?>
                                    <div class="testimonial-photo">
                                        <img src="<?php echo htmlspecialchars($src); ?>" 
                                             alt="Photo de <?php echo htmlspecialchars($t['author']); ?>" 
                                             style="width:100%; height:100%; object-fit:cover; border-radius: 50%;">
                                    </div>
                                <?php else: ?>
                                    <div class="testimonial-photo" style="background-color: #e2e8f0; display: flex; align-items: center; justify-content: center; color: #64748b; font-weight: bold; font-size: 1.5rem;">
                                        <?php echo strtoupper(substr($t['author'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <strong><?php echo htmlspecialchars($t['author']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($t['role'] ?? ''); ?></small>
                                </div>
                            </div>
                            <div style="margin-top:12px; font-size:0.98em; color:var(--color-text-light);">
                                <?php echo nl2br(htmlspecialchars($t['content'])); ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modal pour le formulaire de témoignage -->
    <div id="testimonialModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; overflow-y:auto;">
        <div style="background:white; margin:20px auto; max-width:700px; border-radius:8px; box-shadow:0 4px 20px rgba(0,0,0,0.15); max-height:90vh; overflow-y:auto;">
            <div style="padding:20px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e0e0e0;">
                <h3 style="margin:0; color:#2c5aa0;">Publier mon témoignage</h3>
                <button type="button" onclick="closeTestimonialModal()" style="background:none; border:none; font-size:24px; cursor:pointer; color:#666;">&times;</button>
            </div>
            <div style="padding:20px;">
                <?php if ($success): ?>
                    <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 15px; border-left: 4px solid #155724;">
                        ✅ Merci pour votre témoignage ! Il est en attente de modération avant d'être publié.
                    </div>
                <?php elseif ($error): ?>
                    <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 15px; border-left: 4px solid #721c24;">
                        ⚠️ <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" enctype="multipart/form-data" style="display: grid; gap: 15px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Votre nom *</label>
                            <input type="text" name="author" required placeholder="Votre nom complet" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:1em;">
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Localisation</label>
                            <input type="text" name="location" placeholder="Ex: Paris, France" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:1em;">
                        </div>
                    </div>
                    
                    <div>
                        <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Votre témoignage *</label>
                        <textarea name="content" rows="6" required placeholder="Partagez votre expérience avec nous..." style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:1em; resize:vertical;"></textarea>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Note</label>
                            <select name="rating" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:1em;">
                                <option value="5">⭐⭐⭐⭐⭐ (5/5)</option>
                                <option value="4">⭐⭐⭐⭐ (4/5)</option>
                                <option value="3">⭐⭐⭐ (3/5)</option>
                                <option value="2">⭐⭐ (2/5)</option>
                                <option value="1">⭐ (1/5)</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Votre photo (optionnel)</label>
                            <input type="file" name="image" accept="image/*" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:6px;">
                        </div>
                    </div>
                    
                    <small style="color:#666; display:block;">JPG, PNG, WEBP acceptés</small>
                    
                    <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                        <button type="button" onclick="closeTestimonialModal()" style="background:#e0e0e0; color:#333; padding:10px 20px; border:none; border-radius:6px; cursor:pointer; font-weight:600;">Annuler</button>
                        <button type="submit" name="submit_testimonial" style="background:#2c5aa0; color:white; padding:10px 20px; border:none; border-radius:6px; cursor:pointer; font-weight:600; transition:background 0.2s;" onmouseover="this.style.background='#1e3f70'" onmouseout="this.style.background='#2c5aa0'">Envoyer mon témoignage</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openTestimonialModal() {
            document.getElementById('testimonialModal').style.display = 'flex';
            document.getElementById('testimonialModal').style.alignItems = 'center';
            document.body.style.overflow = 'hidden';
        }
        
        function closeTestimonialModal() {
            document.getElementById('testimonialModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('testimonialModal');
            if (event.target === modal) {
                closeTestimonialModal();
            }
        });
    </script>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
