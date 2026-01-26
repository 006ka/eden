<?php
$isAdding = isset($_GET['add']) && $_GET['add'] === 'new';

function getAdminImagePath($path) {
    if (empty($path)) return '';
    
    // Supprimer les doublons de "uploads/"
    $path = preg_replace('/(^|\/)uploads\/+/', '/uploads/', $path);
    
    // Si le chemin commence par /uploads, on ajoute .. pour remonter d'un niveau
    if (strpos($path, '/uploads/') === 0) {
        return '..' . $path;
    }
    // Si le chemin ne commence pas par /, on ajoute ../
    elseif (strpos($path, '/') !== 0) {
        return '../' . $path;
    }
    return $path;
}
?>

<?php if ($isAdding): ?>
<!-- Formulaire d'ajout photo -->
<div class="admin-section">
    <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 25px;">
        <h2 style="margin: 0;"><?php echo isset($photoToEdit) ? 'Modifier la photo' : 'Ajouter une Photo'; ?></h2>
        <a href="?section=galerie" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px;">
            <span>←</span>
            Retour
        </a>
    </div>

    <?php if (!empty($galleryError)): ?>
        <div class="admin-alert admin-alert-error">
            <span>⚠️</span>
            <?php echo htmlspecialchars($galleryError); ?>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <?php if (isset($photoToEdit)): ?>
            <input type="hidden" name="gallery_id" value="<?php echo $photoToEdit['id']; ?>">
        <?php endif; ?>
        <div class="form-row">
            <div class="form-group">
                <label for="image_file">Uploader une image *</label>
                <input type="file" id="image_file" name="image_file" accept="image/*" required>
                <small style="color: var(--color-text-light); display: block; margin-top: 5px;">
                    Formats: JPG, PNG, GIF, WEBP (max 2Mo)
                </small>
            </div>
            
            <div class="form-group">
                <label>Ou utiliser une URL</label>
                <input type="url" id="image_url" name="image_url" 
                       placeholder="https://example.com/image.jpg">
                <small style="color: var(--color-text-light); display: block; margin-top: 5px;">
                    Laissez vide si vous uploadez un fichier
                </small>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="titre">Titre de la photo</label>
                <input type="text" id="titre" name="titre" 
                               value="<?php echo isset($photoToEdit) ? htmlspecialchars($photoToEdit['titre']) : ''; ?>"
                               placeholder="Ex: Moment de méditation">
            </div>
            
            <div class="form-group">
                <label for="retreat_id">Associer à une retraite</label>
                <select id="retreat_id" name="retreat_id" style="padding: 12px; border: 2px solid var(--color-border); border-radius: 8px; width: 100%;">
                    <option value="">Aucune retraite spécifique</option>
                            <?php if (isset($photoToEdit)): ?>
                                <option value="<?php echo $photoToEdit['retreat_id']; ?>" selected>
                                    <?php echo htmlspecialchars($photoToEdit['retraite_titre'] ?? 'Sélectionnez une retraite'); ?>
                                </option>
                            <?php endif; ?>
                    <?php foreach ($retreats as $retreat): ?>
                        <option value="<?php echo $retreat['id']; ?>">
                            <?php echo htmlspecialchars($retreat['titre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Aperçu de l'image -->
        <div class="form-group">
            <label>Aperçu</label>
            <div id="preview-container" style="border: 2px dashed var(--color-border); border-radius: 12px; padding: 20px; text-align: center; background: var(--color-bg); min-height: 200px; display: flex; align-items: center; justify-content: center;">
                <img id="preview-img" src="" alt="Aperçu" style="max-width: 100%; max-height: 300px; display: none; border-radius: 8px;">
                <div id="preview-placeholder" style="color: var(--color-text-light);">
                    <div style="font-size: 3rem; margin-bottom: 10px;">🖼️</div>
                    <p>Aperçu de l'image apparaîtra ici</p>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 15px; margin-top: 25px;">
            <button type="submit" name="<?php echo isset($photoToEdit) ? 'update_gallery' : 'add_gallery'; ?>" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px;">
                <span>💾</span>
                <?php echo isset($photoToEdit) ? 'Mettre à jour' : 'Ajouter la photo'; ?>
            </button>
            <a href="?section=galerie" class="btn btn-outline">Annuler</a>
        </div>
    </form>
</div>

<?php else: ?>
<!-- Galerie des photos -->
<div class="admin-section">
    <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
        <h2 style="margin: 0;">Galerie Photos</h2>
        <div style="display: flex; gap: 15px; align-items: center;">
            <a href="?section=galerie&add=new" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px;">
                <span>➕</span>
                Ajouter une Photo
            </a>
        </div>
    </div>

    <?php if (isset($_GET['gallery_success'])): ?>
        <div class="admin-alert admin-alert-success">
            <span>✅</span>
            Photo ajoutée avec succès !
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['gallery_deleted'])): ?>
        <div class="admin-alert admin-alert-success">
            <span>✅</span>
            Photo supprimée avec succès !
        </div>
    <?php endif; ?>

    <?php if (!empty($gallery)): ?>
        <div class="gallery-grid">
            <?php foreach ($gallery as $item): ?>
                <div class="gallery-item">
                    <?php $imagePath = getAdminImagePath($item['image_url']); ?>
                    <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                         alt="<?php echo htmlspecialchars($item['titre'] ?? 'Photo galerie'); ?>"
                         style="width: 100%; height: 200px; object-fit: cover;">
                    <div style="padding: 15px;">
                        <div style="font-weight: 600; margin-bottom: 8px; font-size: 0.95rem;">
                            <?php echo htmlspecialchars($item['titre'] ?? 'Sans titre'); ?>
                        </div>
                        <div style="color: var(--color-text-light); font-size: 0.85rem; margin-bottom: 12px;">
                            <?php 
                            $date = new DateTime($item['created_at']);
                            echo $date->format('d/m/Y');
                            ?>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <a href="?section=galerie&edit=<?php echo $item['id']; ?>" 
                               style="flex: 1; text-align: center; padding: 8px; background: var(--color-primary); color: white; border-radius: 6px; text-decoration: none; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                <span>✏️</span>
                                Modifier
                            </a>
                            <a href="?section=galerie&delete_gallery=<?php echo $item['id']; ?>" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette photo ?')"
                               style="flex: 1; text-align: center; padding: 8px; background: var(--color-accent); color: white; border-radius: 6px; text-decoration: none; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                <span>🗑️</span>
                                Suppr.
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i>🖼️</i>
            <h3>Aucune photo dans la galerie</h3>
            <p>Ajoutez des photos pour enrichir votre galerie et partager vos moments.</p>
            <a href="?section=galerie&add=new" class="btn btn-primary" style="margin-top: 20px;">
                Ajouter une photo
            </a>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('image_file');
    const urlInput = document.getElementById('image_url');
    const previewImg = document.getElementById('preview-img');
    const previewPlaceholder = document.getElementById('preview-placeholder');

    function showPreviewFromFile(file) {
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewImg.style.display = 'block';
            previewPlaceholder.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }

    function showPreviewFromUrl(url) {
        if (!url) {
            previewImg.style.display = 'none';
            previewImg.src = '';
            previewPlaceholder.style.display = 'block';
            return;
        }
        previewImg.src = url;
        previewImg.style.display = 'block';
        previewPlaceholder.style.display = 'none';
    }

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files && this.files[0];
            if (file) {
                showPreviewFromFile(file);
            }
        });
    }

    if (urlInput) {
        urlInput.addEventListener('input', function() {
            showPreviewFromUrl(this.value.trim());
        });
    }
});
</script>