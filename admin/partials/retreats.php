<div class="admin-section" id="retreats">
    <h2 style="display:flex; justify-content:space-between; align-items:center;">
        <span>Retraites <span class="badge"><?php echo count($retreats); ?></span></span>
        <button id="btn-open-retreat-modal" class="btn">Ajouter une retraite</button>
    </h2>

    <?php
        $retreatSuccessMsg = '';
        if (!empty($_GET['retreat_success'])) {
            $retreatSuccessMsg = 'Retraite enregistrée avec succès.';
        } elseif (!empty($_GET['retreat_deleted'])) {
            $retreatSuccessMsg = 'Retraite supprimée avec succès.';
        }
    ?>

    <?php if ($retreatSuccessMsg !== ''): ?>
        <div style="margin:8px 0 12px; padding:10px 12px; border-radius:6px; background: rgba(39,174,96,0.1); border:1px solid rgba(39,174,96,0.4); color:#1e7e34; font-size:0.9rem;">
            <?php echo htmlspecialchars($retreatSuccessMsg); ?>
        </div>
    <?php endif; ?>

    <!-- Filtre par année -->
    <div style="margin:10px 0;">
        <form method="get" style="display:flex; gap:8px; align-items:center;">
            <input type="hidden" name="section" value="retreats">
            <label style="font-size:14px; margin-right:6px;">Filtrer par année :</label>
            <select name="retreat_year" onchange="this.form.submit()" style="padding:6px;">
                <option value="">Toutes les années</option>
                <?php if (!empty($retreatYears)): ?>
                    <?php foreach ($retreatYears as $y): ?>
                        <option value="<?php echo (int)$y; ?>" <?php echo (isset($_GET['retreat_year']) && (int)$_GET['retreat_year'] === (int)$y) ? 'selected' : ''; ?>><?php echo (int)$y; ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <?php if (!empty($_GET['retreat_year'])): ?>
                <a href="?section=retreats" class="btn" style="margin-left:6px;">Réinitialiser</a>
            <?php endif; ?>
        </form>
    </div>

    <?php
        $retreatsPerPage = 15;
        $retreatPage = isset($_GET['retreat_page']) && is_numeric($_GET['retreat_page']) ? max(1, (int)$_GET['retreat_page']) : 1;
        $retreatTotal = count($retreats);
        $retreatOffset = ($retreatPage - 1) * $retreatsPerPage;
        $retreatsPageItems = array_slice($retreats, $retreatOffset, $retreatsPerPage);
        $retreatHasPrev = $retreatPage > 1;
        $retreatHasNext = $retreatOffset + $retreatsPerPage < $retreatTotal;
    ?>

    <?php if (empty($retreatsPageItems)): ?>
        <p>Aucune retraite enregistrée.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Thème</th>
                    <th>Dates</th>
                    <th>Lieu</th>
                    <th>Prix</th>
                    <th>Image</th>
                    <th>Fiche</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($retreatsPageItems as $r): ?>
                    <tr>
                        <td><?php echo (int)$r['id']; ?></td>
                        <td><?php echo htmlspecialchars($r['titre']); ?></td>
                        <td><?php echo htmlspecialchars($r['theme']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($r['date_debut'] ?? ''); ?>
                            <?php if (!empty($r['date_fin'])): ?>
                                - <?php echo htmlspecialchars($r['date_fin']); ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($r['lieu']); ?></td>
                        <td><?php echo !empty($r['prix']) ? htmlspecialchars($r['prix']) : '<span style="color:#888; font-size:12px;">—</span>'; ?></td>
                        <td>
                            <?php if (!empty($r['programme_image_url'])): ?>
                                <div style="margin-bottom:6px;">
                                    <img src="<?php echo htmlspecialchars($r['programme_image_url']); ?>" alt="Programme" style="max-width:80px; max-height:80px; border-radius:4px; border:1px solid #ddd;">
                                </div>
                            <?php else: ?>
                                <div style="margin-bottom:6px; color:#777; font-size:12px;">Pas d'image</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($r['fiche_url'])): ?>
                                <a href="<?php echo htmlspecialchars($r['fiche_url']); ?>" target="_blank" style="font-size:12px; text-decoration:underline;">Télécharger</a>
                            <?php else: ?>
                                <span style="color:#888; font-size:12px;">Aucune</span>
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <a href="?section=inscriptions_gestion&amp;filter_event_type=retraite&amp;filter_event_id=<?php echo (int)$r['id']; ?>" style="margin-right:8px;">Voir inscrits</a>
                            <a href="?section=retreats&amp;edit_retreat=<?php echo (int)$r['id']; ?>" style="margin-right:8px;">Modifier</a>
                            <a href="?delete_retreat=<?php echo (int)$r['id']; ?>" onclick="return confirm('Supprimer cette retraite ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($retreatTotal > $retreatsPerPage): ?>
            <div style="margin-top:10px; display:flex; justify-content:space-between; align-items:center; font-size:0.9rem;">
                <div>
                    Page <?php echo $retreatPage; ?> / <?php echo max(1, (int)ceil($retreatTotal / $retreatsPerPage)); ?>
                </div>
                <div style="display:flex; gap:8px;">
                    <?php if ($retreatHasPrev): ?>
                        <a href="?section=retreats&amp;retreat_page=<?php echo $retreatPage - 1; ?><?php echo !empty($_GET['retreat_year']) ? '&amp;retreat_year='.(int)$_GET['retreat_year'] : ''; ?>" class="btn btn-secondary" style="padding:4px 10px; font-size:0.85rem;">&laquo; Précédent</a>
                    <?php endif; ?>
                    <?php if ($retreatHasNext): ?>
                        <a href="?section=retreats&amp;retreat_page=<?php echo $retreatPage + 1; ?><?php echo !empty($_GET['retreat_year']) ? '&amp;retreat_year='.(int)$_GET['retreat_year'] : ''; ?>" class="btn btn-secondary" style="padding:4px 10px; font-size:0.85rem;">Suivant &raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- MODAL RETRAITE -->
<div id="retreat-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:9999; align-items:center; justify-content:center; overflow-y:auto; padding:20px;">
    <div style="background:white; border-radius:12px; width:100%; max-width:600px; padding:30px; position:relative; box-shadow:0 20px 60px rgba(0,0,0,0.3); margin:auto;">
        <button onclick="closeRetreatModal()" style="position:absolute; top:15px; right:15px; background:transparent; border:none; font-size:28px; cursor:pointer; color:#999; width:36px; height:36px; display:flex; align-items:center; justify-content:center;">✕</button>
        
        <h2 id="retreat-modal-title" style="margin-bottom:6px; margin-top:0;">Ajouter une retraite</h2>
        <p style="margin:0 0 18px; font-size:13px; color:#666;">Renseignez les informations principales, les détails pratiques puis les supports (image, fiche).</p>
        
        <?php $isEdit = isset($retreatToEdit) && is_array($retreatToEdit); ?>
        <form method="post" enctype="multipart/form-data">
            <?php if ($isEdit): ?>
                <input type="hidden" name="update_retreat" value="1">
                <input type="hidden" name="retreat_id" value="<?php echo (int)$retreatToEdit['id']; ?>">
            <?php else: ?>
                <input type="hidden" name="add_retreat" value="1">
            <?php endif; ?>

            <h3 style="margin:10px 0 8px; font-size:14px; text-transform:uppercase; letter-spacing:0.06em; color:#555;">Informations de base</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Titre *</label>
                    <input type="text" name="ret_titre" value="<?php echo $isEdit ? htmlspecialchars($retreatToEdit['titre']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Thème</label>
                    <input type="text" name="ret_theme" value="<?php echo $isEdit && !empty($retreatToEdit['theme']) ? htmlspecialchars($retreatToEdit['theme']) : ''; ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Date de début</label>
                    <input type="date" name="ret_date_debut" value="<?php echo $isEdit && !empty($retreatToEdit['date_debut']) ? htmlspecialchars($retreatToEdit['date_debut']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>Date de fin</label>
                    <input type="date" name="ret_date_fin" value="<?php echo $isEdit && !empty($retreatToEdit['date_fin']) ? htmlspecialchars($retreatToEdit['date_fin']) : ''; ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Lieu</label>
                    <input type="text" name="ret_lieu" value="<?php echo $isEdit && !empty($retreatToEdit['lieu']) ? htmlspecialchars($retreatToEdit['lieu']) : ''; ?>">
                </div>
            </div>

            <h3 style="margin:16px 0 8px; font-size:14px; text-transform:uppercase; letter-spacing:0.06em; color:#555;">Détails pratiques</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Orateurs</label>
                    <input type="text" name="ret_orateurs" value="<?php echo $isEdit && !empty($retreatToEdit['orateurs']) ? htmlspecialchars($retreatToEdit['orateurs']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>Prix de participation (optionnel)</label>
                    <input type="text" name="ret_prix" placeholder="Ex: 20 000 FC / 30€" value="<?php echo $isEdit && !empty($retreatToEdit['prix']) ? htmlspecialchars($retreatToEdit['prix']) : ''; ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="ret_description" rows="3"><?php echo $isEdit && !empty($retreatToEdit['description']) ? htmlspecialchars($retreatToEdit['description']) : ''; ?></textarea>
            </div>

            <h3 style="margin:16px 0 8px; font-size:14px; text-transform:uppercase; letter-spacing:0.06em; color:#555;">Supports / médias</h3>
            <div class="form-group">
                <label>Image du programme (optionnel)</label>
                <input type="file" name="ret_programme_image_file" accept="image/*">
                <?php if ($isEdit && !empty($retreatToEdit['programme_image_url'])): ?>
                    <div style="margin-top:4px; font-size:12px; color:#555;">
                        Image actuelle : <img src="<?php echo htmlspecialchars($retreatToEdit['programme_image_url']); ?>" alt="Programme" style="max-width:80px; max-height:80px; border-radius:4px; border:1px solid #ddd; vertical-align:middle;">
                    </div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label>Fiche pratique (PDF ou image, optionnel)</label>
                <input type="file" name="ret_fiche_file" accept=".pdf,image/*">
                <?php if ($isEdit && !empty($retreatToEdit['fiche_url'])): ?>
                    <div style="margin-top:4px; font-size:12px; color:#555;">
                        Fiche actuelle : <a href="<?php echo htmlspecialchars($retreatToEdit['fiche_url']); ?>" target="_blank" style="text-decoration:underline;">Voir / Télécharger</a>
                    </div>
                <?php endif; ?>
            </div>
            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn primary"><?php echo $isEdit ? 'Mettre à jour la retraite' : 'Ajouter la retraite'; ?></button>
                <button type="button" onclick="closeRetreatModal()" class="btn">Annuler</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRetreatModal() {
        document.getElementById('retreat-modal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    function closeRetreatModal() {
        document.getElementById('retreat-modal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    document.getElementById('btn-open-retreat-modal').addEventListener('click', openRetreatModal);
    document.getElementById('retreat-modal').addEventListener('click', function(e) {
        if (e.target === this) closeRetreatModal();
    });
    <?php if ($isEdit): ?>
        openRetreatModal();
        document.getElementById('retreat-modal-title').textContent = 'Modifier la retraite';
    <?php endif; ?>
</script>
