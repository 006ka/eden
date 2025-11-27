<div class="admin-section" id="tools">
    <h2>Outils du site</h2>

    <?php if (!empty($tool_message)): ?>
        <div style="background:#f7f7f7;padding:10px;border-radius:6px;margin-bottom:12px;">
            <?php echo htmlspecialchars($tool_message); ?>
        </div>
    <?php endif; ?>

    <form method="post" onsubmit="return confirm('Confirmez-vous la génération des miniatures ?');">
        <input type="hidden" name="run_thumbs" value="1">
        <label style="display:flex; gap:8px; align-items:center; margin-bottom:8px;">
            <input type="checkbox" name="resize_originals" value="1"> Redimensionner aussi les originaux (1200x1200)
        </label>
        <div style="display:flex; gap:8px;">
            <button type="submit" class="btn primary">Générer les miniatures</button>
        </div>
    </form>

    <div style="margin-top:18px;">
        <p>Script CLI disponible : <code>scripts/generate_thumbnails.php</code></p>
        <p>Exécution CLI (PowerShell) : <code>php .\scripts\generate_thumbnails.php</code></p>
    </div>

    <?php if (!empty($uploadsStats) && is_array($uploadsStats)): ?>
        <hr style="margin:20px 0; border:none; border-top:1px solid #eee;">
        <h3 style="margin-top:0;">Fichiers uploads</h3>
        <p style="font-size:0.95rem; color:#555; margin-bottom:8px;">
            Total fichiers : <strong><?php echo (int)$uploadsStats['total']; ?></strong> ·
            Référencés en base : <strong><?php echo (int)$uploadsStats['referenced']; ?></strong> ·
            Possiblement orphelins : <strong><?php echo count($uploadsStats['orphans']); ?></strong>
        </p>
        <?php if (!empty($uploadsStats['orphans'])): ?>
            <p style="font-size:0.9rem; color:#777; margin-bottom:4px;">Fichiers non référencés (à vérifier manuellement avant suppression) :</p>
            <ul style="font-size:0.85rem; max-height:220px; overflow:auto; padding-left:18px; margin:0;">
                <?php foreach ($uploadsStats['orphans'] as $f): ?>
                    <li><?php echo htmlspecialchars($f); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p style="font-size:0.9rem; color:#777;">Aucun fichier orphelin détecté dans <code>/uploads</code>.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>
