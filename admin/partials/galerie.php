<div class="admin-section" id="galerie">
    <h2 style="display:flex; justify-content:space-between; align-items:center;">
        <span>Galerie photos <span class="badge"><?php echo count($gallery); ?></span></span>
        <button id="btn-open-gallery-modal" class="btn">Ajouter une photo</button>
    </h2>

    <?php
        $gallerySuccessMsg = '';
        if (!empty($_GET['gallery_success'])) {
            $gallerySuccessMsg = 'Photo ajoutée à la galerie.';
        } elseif (!empty($_GET['gallery_deleted'])) {
            $gallerySuccessMsg = 'Photo supprimée de la galerie.';
        }
    ?>

    <?php if ($gallerySuccessMsg !== ''): ?>
        <div style="margin:8px 0 12px; padding:10px 12px; border-radius:6px; background: rgba(39,174,96,0.1); border:1px solid rgba(39,174,96,0.4); color:#1e7e34; font-size:0.9rem;">
            <?php echo htmlspecialchars($gallerySuccessMsg); ?>
        </div>
    <?php endif; ?>

    <?php if ($galleryError !== ''): ?>
        <div style="margin:4px 0 10px; padding:8px 10px; border-radius:6px; background: #ffecec; border:1px solid #f5c2c7; color:#842029; font-size:0.9rem;">
            <?php echo htmlspecialchars($galleryError); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($gallery)): ?>
        <p>Aucune photo dans la galerie.</p>
    <?php else: ?>
        <div class="gallery-grid">
            <?php foreach ($gallery as $g):
                // prefer thumbnail if exists
                $orig = $g['image_url'];
                $basename = basename($orig);
                $thumbRel = '../uploads/thumb_' . $basename;
                $uploadsDir = realpath(__DIR__ . '/../../uploads');
                if ($uploadsDir) {
                    $thumbFs = $uploadsDir . DIRECTORY_SEPARATOR . 'thumb_' . $basename;
                } else {
                    $thumbFs = __DIR__ . '/../../uploads/thumb_' . $basename;
                }
                $displayUrl = (file_exists($thumbFs)) ? $thumbRel : $orig;
            ?>
                <div class="gallery-item">
                    <?php if (!empty($g['retreat_url'])): ?>
                        <a href="<?php echo htmlspecialchars($g['retreat_url']); ?>">
                            <img src="<?php echo htmlspecialchars($displayUrl); ?>" alt="">
                        </a>
                    <?php else: ?>
                        <img src="<?php echo htmlspecialchars($displayUrl); ?>" alt="">
                    <?php endif; ?>
                    <?php if (!empty($g['titre'])): ?>
                        <small><?php echo htmlspecialchars($g['titre']); ?></small>
                    <?php endif; ?>
                    <small><?php echo htmlspecialchars($g['created_at']); ?></small>
                    <div class="actions">
                        <a href="?delete_gallery=<?php echo (int)$g['id']; ?>" onclick="return confirm('Supprimer cette photo ?');">Supprimer</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL GALERIE -->
<div id="gallery-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:9999; align-items:center; justify-content:center; overflow-y:auto; padding:20px;">
    <div style="background:white; border-radius:12px; width:100%; max-width:600px; padding:30px; position:relative; box-shadow:0 20px 60px rgba(0,0,0,0.3); margin:auto;">
        <button onclick="closeGalleryModal()" style="position:absolute; top:15px; right:15px; background:transparent; border:none; font-size:28px; cursor:pointer; color:#999; width:36px; height:36px; display:flex; align-items:center; justify-content:center;">✕</button>
        
        <h2 style="margin-bottom:20px; margin-top:0;">Ajouter une photo</h2>
        
        <form method="post" enctype="multipart/form-data" id="gallery-form-inner">
            <input type="hidden" name="add_gallery" value="1">
            <div class="form-group">
                <label>URL de l'image (optionnel)</label>
                <input type="text" name="image_url" id="image_url">
            </div>
            <div class="form-group">
                <label>Ou choisir un fichier image à uploader</label>
                <input type="file" name="image_file" id="image_file" accept="image/*">
            </div>
            <div class="form-group">
                <label>Titre (optionnel)</label>
                <input type="text" name="titre">
            </div>
            <div class="form-group">
                <label>Retraite associée (optionnel)</label>
                <select name="retreat_id">
                    <option value="">Aucune</option>
                    <?php foreach ($retreats as $r): ?>
                        <option value="<?php echo (int)$r['id']; ?>"><?php echo htmlspecialchars($r['titre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <p style="font-size:13px; margin-bottom:10px;">Vous pouvez soit fournir une URL d'image, soit uploader un fichier. Si un fichier est sélectionné, il sera utilisé en priorité.</p>
            <div class="form-group" style="margin-bottom:10px;">
                <label>Aperçu :</label>
                <div style="margin-top:5px;">
                    <img id="preview-img" src="" alt="Prévisualisation" style="max-width:200px; max-height:150px; display:none; border-radius:6px; border:1px solid #ccc;">
                </div>
            </div>
            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn primary">Ajouter la photo</button>
                <button type="button" onclick="closeGalleryModal()" class="btn">Annuler</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openGalleryModal() {
        document.getElementById('gallery-modal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    function closeGalleryModal() {
        document.getElementById('gallery-modal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    document.getElementById('btn-open-gallery-modal').addEventListener('click', openGalleryModal);
    document.getElementById('gallery-modal').addEventListener('click', function(e) {
        if (e.target === this) closeGalleryModal();
    });
</script>
