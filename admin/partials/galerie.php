<div class="admin-section" id="galerie">
    <h2 style="display:flex; justify-content:space-between; align-items:center;">
        <span>Galerie photos <span class="badge"><?php echo count($gallery); ?></span></span>
        <button id="btn-toggle-gallery-form" class="btn">Ajouter une photo</button>
    </h2>

    <?php if ($galleryError !== ''): ?>
        <p style="color:red; margin-bottom:10px;"><?php echo htmlspecialchars($galleryError); ?></p>
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

    <div id="gallery-form" style="display:none; margin-top:18px;">
        <form method="post" enctype="multipart/form-data" style="margin-bottom:20px;" id="gallery-form-inner">
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
                <button type="button" id="btn-cancel-gallery" class="btn">Annuler</button>
            </div>
        </form>
    </div>

    <script>
        (function(){
            var toggle = document.getElementById('btn-toggle-gallery-form');
            var formWrap = document.getElementById('gallery-form');
            var cancel = document.getElementById('btn-cancel-gallery');
            if (toggle && formWrap) {
                toggle.addEventListener('click', function(){
                    formWrap.style.display = formWrap.style.display === 'none' || formWrap.style.display === '' ? 'block' : 'none';
                    toggle.textContent = formWrap.style.display === 'block' ? 'Masquer le formulaire' : 'Ajouter une photo';
                    if (formWrap.style.display === 'block') formWrap.scrollIntoView({behavior:'smooth', block:'center'});
                });
            }
            if (cancel && formWrap) {
                cancel.addEventListener('click', function(){ formWrap.style.display = 'none'; toggle.textContent = 'Ajouter une photo'; });
            }
        })();
    </script>
</div>
