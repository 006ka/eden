<?php
// partials/testimonials.php
require_once __DIR__ . '/../../config/db.php';

$isAdding = isset($_GET['add']) && $_GET['add'] === 'new';

// Gestion de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_testimonial'])) {
        // Gestion de l'ajout d'un témoignage (depuis l'admin)
        $author = trim($_POST['author']);
        $content = trim($_POST['content']);
        $location = trim($_POST['location'] ?? '');
        $rating = (int)($_POST['rating'] ?? 5);
        
        // Gestion de l'upload de l'image
        $imageUrl = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../uploads/testimonials/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($fileExt, $allowedExts)) {
                $fileName = uniqid('testimonial_') . '.' . $fileExt;
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $imageUrl = '/uploads/testimonials/' . $fileName;
                }
            }
        }
        
        // Insertion en base de données
        $stmt = $pdo->prepare('INSERT INTO testimonials (author, content, location, rating, image_url, is_approved, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())');
        $stmt->execute([$author, $content, $location, $rating, $imageUrl]);
        
        header('Location: ?section=testimonials&success=added');
        exit;
    } 
    // Gestion de l'approbation/rejet des témoignages
    elseif (isset($_POST['approve_testimonial'])) {
        $id = (int)$_POST['testimonial_id'];
        $stmt = $pdo->prepare('UPDATE testimonials SET is_approved = 1, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$id]);
        
        header('Location: ?section=testimonials&success=approved');
        exit;
    }
    elseif (isset($_POST['delete_testimonial'])) {
        $id = (int)$_POST['testimonial_id'];
        
        // Récupérer l'URL de l'image pour la supprimer
        $stmt = $pdo->prepare('SELECT image_url FROM testimonials WHERE id = ?');
        $stmt->execute([$id]);
        $testimonial = $stmt->fetch();
        
        if ($testimonial && !empty($testimonial['image_url'])) {
            $imagePath = __DIR__ . '/../..' . $testimonial['image_url'];
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }
        
        $stmt = $pdo->prepare('DELETE FROM testimonials WHERE id = ?');
        $stmt->execute([$id]);
        
        header('Location: ?section=testimonials&success=deleted');
        exit;
    }
}

// Récupération des témoignages
$pendingTestimonials = $pdo->query('SELECT * FROM testimonials WHERE is_approved = 0 ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
$approvedTestimonials = $pdo->query('SELECT * FROM testimonials WHERE is_approved = 1 ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);

// Gestion des messages de succès
$successMessage = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'added':
            $successMessage = 'Le témoignage a été ajouté avec succès.';
            break;
        case 'approved':
            $successMessage = 'Le témoignage a été approuvé avec succès.';
            break;
        case 'deleted':
            $successMessage = 'Le témoignage a été supprimé avec succès.';
            break;
    }
}
?>

<?php if ($isAdding): ?>
<!-- Formulaire d'ajout de témoignage -->
<div class="admin-section">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h2 style="margin: 0;">Ajouter un Témoignage</h2>
        <a href="?section=testimonials" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px;">
            <span>←</span>
            Retour
        </a>
    </div>

    <?php if (!empty($testimonialError)): ?>
        <div class="admin-alert admin-alert-error">
            <span>⚠️</span>
            <?php echo htmlspecialchars($testimonialError); ?>
        </div>
    <?php endif; ?>

    <div class="admin-section">
        <form method="post" enctype="multipart/form-data" class="form-modern">
            <div class="form-row">
                <div class="form-group">
                    <label for="testimonial_author">Auteur *</label>
                    <input type="text" id="testimonial_author" name="author" required 
                           placeholder="Ex: Marie D.">
                </div>
                
                <div class="form-group">
                    <label for="testimonial_location">Localisation</label>
                    <input type="text" id="testimonial_location" name="location" 
                           placeholder="Ex: Paris, France">
                </div>
            </div>
            
            <div class="form-group">
                <label for="testimonial_content">Témoignage *</label>
                <textarea id="testimonial_content" name="content" rows="4" required 
                          placeholder="Partagez votre expérience..."></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="testimonial_rating">Note</label>
                    <select id="testimonial_rating" name="rating" 
                            style="padding: 12px; border: 2px solid var(--color-border); border-radius: 8px; width: 100%;">
                        <option value="5">⭐⭐⭐⭐⭐ (5/5)</option>
                        <option value="4">⭐⭐⭐⭐ (4/5)</option>
                        <option value="3">⭐⭐⭐ (3/5)</option>
                        <option value="2">⭐⭐ (2/5)</option>
                        <option value="1">⭐ (1/5)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="testimonial_image">Photo de l'auteur</label>
                    <input type="file" id="testimonial_image" name="image" accept="image/*">
                    <small style="color: var(--color-text-light); display: block; margin-top: 5px;">
                        Formats: JPG, PNG, WEBP (max 1Mo)
                    </small>
                </div>
            </div>
            
            <div style="margin-top: 25px; display: flex; gap: 15px;">
                <button type="submit" name="add_testimonial" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px;">
                    <span>💾</span>
                    Ajouter le témoignage
                </button>
                <a href="?section=testimonials" class="btn btn-outline">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php else: ?>
<!-- Liste des témoignages -->
<div class="admin-section">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
        <h2 style="margin: 0;">Gestion des Témoignages</h2>
        <div style="display: flex; gap: 15px; align-items: center;">
            <a href="?section=testimonials&add=new" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px;">
                <span>➕</span>
                Nouveau Témoignage
            </a>
        </div>
    </div>

    <?php if (isset($_GET['testimonial_success'])): ?>
        <div class="admin-alert admin-alert-success">
            <span>✅</span>
            Témoignage ajouté avec succès !
        </div>
    <?php endif; ?>

    <?php if ($successMessage): ?>
        <div class="admin-alert admin-alert-success" style="margin-bottom: 20px;">
            <span>✅</span>
            <?php echo htmlspecialchars($successMessage); ?>
        </div>
    <?php endif; ?>

    <div class="dashboard-grid">
        <div class="admin-section">
            <h3>Témoignages en attente (<?php echo count($pendingTestimonials); ?>)</h3>
            
            <?php if (empty($pendingTestimonials)): ?>
                <div class="empty-state" style="padding: 40px 20px;">
                    <i>💬</i>
                    <h3 style="font-size: 1.1rem;">Aucun témoignage en attente</h3>
                    <p style="font-size: 0.9rem;">Les nouveaux témoignages apparaîtront ici pour modération.</p>
                </div>
            <?php else: ?>
                <div class="testimonials-list" style="margin-top: 20px;">
                    <?php foreach ($pendingTestimonials as $testimonial): ?>
                        <div class="testimonial-item" style="background: #fff; border-radius: 8px; padding: 15px; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                            <div style="display: flex; gap: 15px; margin-bottom: 10px;">
                                <?php if (!empty($testimonial['image_url'])): 
                                    $imagePath = $testimonial['image_url'];
                                    // Si le chemin commence par /uploads, on ajoute .. pour remonter d'un niveau
                                    if (strpos($imagePath, '/uploads/') === 0) {
                                        $imagePath = '..' . $imagePath;
                                    }
                                    // Si le chemin ne commence pas par /, on ajoute /admin/../
                                    elseif (strpos($imagePath, '/') !== 0) {
                                        $imagePath = '../' . $imagePath;
                                    }
                                ?>
                                    <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                                         alt="<?php echo htmlspecialchars($testimonial['author']); ?>" 
                                         style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                                <?php endif; ?>
                                <div>
                                    <h4 style="margin: 0 0 5px 0;"><?php echo htmlspecialchars($testimonial['author']); ?></h4>
                                    <?php if (!empty($testimonial['location'])): ?>
                                        <p style="margin: 0; color: #666; font-size: 0.9em;"><?php echo htmlspecialchars($testimonial['location']); ?></p>
                                    <?php endif; ?>
                                    <?php if (isset($testimonial['rating'])): ?>
                                        <div style="color: #ffc107; font-size: 1.1em; margin-top: 3px;">
                                            <?php 
                                            $rating = (int)$testimonial['rating'];
                                            echo str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <p style="margin: 10px 0 15px; line-height: 1.5;"><?php echo nl2br(htmlspecialchars($testimonial['content'])); ?></p>
                            <div style="display: flex; gap: 10px; margin-top: 15px;">
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="testimonial_id" value="<?php echo $testimonial['id']; ?>">
                                    <button type="submit" name="approve_testimonial" class="btn btn-success" style="padding: 5px 10px; font-size: 0.9em;">
                                        <span>✓</span> Approuver
                                    </button>
                                </form>
                                <form method="post" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce témoignage ? Cette action est irréversible.');">
                                    <input type="hidden" name="testimonial_id" value="<?php echo $testimonial['id']; ?>">
                                    <button type="submit" name="delete_testimonial" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.9em;">
                                        <span>✕</span> Supprimer
                                    </button>
                                </form>
                            </div>
                            <div style="font-size: 0.8em; color: #888; margin-top: 10px;">
                                Posté le <?php echo date('d/m/Y à H:i', strtotime($testimonial['created_at'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="admin-section">
            <h3>Témoignages approuvés (<?php echo count($approvedTestimonials); ?>)</h3>
            
            <?php if (empty($approvedTestimonials)): ?>
                <div class="empty-state" style="padding: 40px 20px;">
                    <i>⭐</i>
                    <h3 style="font-size: 1.1rem;">Aucun témoignage approuvé</h3>
                    <p style="font-size: 0.9rem;">Approuvez des témoignages pour les afficher sur le site.</p>
                </div>
            <?php else: ?>
                <div class="testimonials-list" style="margin-top: 20px;">
                    <?php foreach ($approvedTestimonials as $testimonial): ?>
                        <div class="testimonial-item" style="background: #fff; border-radius: 8px; padding: 15px; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                            <div style="display: flex; gap: 15px; margin-bottom: 10px;">
                                <?php if (!empty($testimonial['image_url'])): 
                                    $imagePath = $testimonial['image_url'];
                                    // Si le chemin commence par /uploads, on ajoute .. pour remonter d'un niveau
                                    if (strpos($imagePath, '/uploads/') === 0) {
                                        $imagePath = '..' . $imagePath;
                                    }
                                    // Si le chemin ne commence pas par /, on ajoute /admin/../
                                    elseif (strpos($imagePath, '/') !== 0) {
                                        $imagePath = '../' . $imagePath;
                                    }
                                ?>
                                    <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                                         alt="<?php echo htmlspecialchars($testimonial['author']); ?>" 
                                         style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                                <?php endif; ?>
                                <div>
                                    <h4 style="margin: 0 0 5px 0;"><?php echo htmlspecialchars($testimonial['author']); ?></h4>
                                    <?php if (!empty($testimonial['location'])): ?>
                                        <p style="margin: 0; color: #666; font-size: 0.9em;"><?php echo htmlspecialchars($testimonial['location']); ?></p>
                                    <?php endif; ?>
                                    <?php if (isset($testimonial['rating'])): ?>
                                        <div style="color: #ffc107; font-size: 1.1em; margin-top: 3px;">
                                            <?php 
                                            $rating = (int)$testimonial['rating'];
                                            echo str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <p style="margin: 10px 0 15px; line-height: 1.5;"><?php echo nl2br(htmlspecialchars($testimonial['content'])); ?></p>
                            <div style="display: flex; gap: 10px; margin-top: 15px;">
                                <form method="post" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce témoignage ? Cette action est irréversible.');">
                                    <input type="hidden" name="testimonial_id" value="<?php echo $testimonial['id']; ?>">
                                    <button type="submit" name="delete_testimonial" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.9em;">
                                        <span>✕</span> Supprimer
                                    </button>
                                </form>
                            </div>
                            <div style="font-size: 0.8em; color: #888; margin-top: 10px;">
                                Posté le <?php echo date('d/m/Y à H:i', strtotime($testimonial['created_at'])); ?>
                                <?php if ($testimonial['updated_at'] && $testimonial['updated_at'] !== $testimonial['created_at']): ?>
                                    <br>Approuvé le <?php echo date('d/m/Y à H:i', strtotime($testimonial['updated_at'])); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>