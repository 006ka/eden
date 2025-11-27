<?php
// Section de gestion des programmes annuels
?>
<div class="admin-section" id="programmes">
    <h2 style="display:flex; justify-content:space-between; align-items:center;">
        <span>Programmes annuels <span class="badge"><?php echo count($programmes); ?></span></span>
        <button id="btn-open-programme-modal" class="btn">Ajouter un programme</button>
    </h2>

    <?php
        $programmeSuccessMsg = '';
        if (!empty($_GET['programme_success'])) {
            $programmeSuccessMsg = 'Programme enregistré avec succès.';
        } elseif (!empty($_GET['programme_deleted'])) {
            $programmeSuccessMsg = 'Programme supprimé avec succès.';
        }
    ?>

    <?php if ($programmeSuccessMsg !== ''): ?>
        <div style="margin:8px 0 12px; padding:10px 12px; border-radius:6px; background: rgba(39,174,96,0.1); border:1px solid rgba(39,174,96,0.4); color:#1e7e34; font-size:0.9rem;">
            <?php echo htmlspecialchars($programmeSuccessMsg); ?>
        </div>
    <?php endif; ?>

    <!-- Filtre par année -->
    <div style="margin:10px 0;">
        <form method="get" style="display:flex; gap:8px; align-items:center;">
            <input type="hidden" name="section" value="programmes">
            <label style="font-size:14px; margin-right:6px;">Filtrer par année :</label>
            <select name="programme_year" onchange="this.form.submit()" style="padding:6px;">
                <option value="">Toutes les années</option>
                <?php if (!empty($programmeYears)): ?>
                    <?php foreach ($programmeYears as $y): ?>
                        <option value="<?php echo (int)$y; ?>" <?php echo (isset($_GET['programme_year']) && (int)$_GET['programme_year'] === (int)$y) ? 'selected' : ''; ?>><?php echo (int)$y; ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <?php if (!empty($_GET['programme_year'])): ?>
                <a href="?section=programmes" class="btn" style="margin-left:6px;">Réinitialiser</a>
            <?php endif; ?>
        </form>
    </div>

    <?php $isEditProg = isset($programmeToEdit) && is_array($programmeToEdit); ?>

    <?php
        $programmesPerPage = 15;
        $programmePage = isset($_GET['programme_page']) && is_numeric($_GET['programme_page']) ? max(1, (int)$_GET['programme_page']) : 1;
        $programmesTotal = count($programmes);
        $programmeOffset = ($programmePage - 1) * $programmesPerPage;
        $programmesPageItems = array_slice($programmes, $programmeOffset, $programmesPerPage);
        $programmeHasPrev = $programmePage > 1;
        $programmeHasNext = $programmeOffset + $programmesPerPage < $programmesTotal;
    ?>

    <?php if (empty($programmesPageItems)): ?>
        <p>Aucun événement enregistré pour le moment.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Mois</th>
                    <th>Titre</th>
                    <th>Thème</th>
                    <th>Détails</th>
                    <th>Prix</th>
                    <th>Affiche</th>
                    <th>Dates</th>
                    <th>Ordre</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($programmesPageItems as $p): ?>
                    <tr>
                        <td><?php echo (int)$p['id']; ?></td>
                        <td><?php echo htmlspecialchars($p['mois']); ?></td>
                        <td><?php echo htmlspecialchars($p['titre']); ?></td>
                        <td><?php echo htmlspecialchars($p['theme']); ?></td>
                        <td><?php echo htmlspecialchars($p['details']); ?></td>
                        <td><?php echo !empty($p['prix']) ? htmlspecialchars($p['prix']) : '<span style="color:#888; font-size:12px;">—</span>'; ?></td>
                        <td>
                            <?php if (!empty($p['affiche_url'])): ?>
                                <img src="<?php echo htmlspecialchars($p['affiche_url']); ?>" alt="Affiche" style="max-width:60px; max-height:60px; border-radius:4px; border:1px solid #ddd;">
                            <?php else: ?>
                                <span style="color:#777; font-size:12px;">Pas d'affiche</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($p['date_debut'] ?? ''); ?>
                            <?php if (!empty($p['date_fin'])): ?>
                                - <?php echo htmlspecialchars($p['date_fin']); ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo (int)$p['ordre']; ?></td>
                        <td class="actions">
                            <a href="?section=inscriptions_gestion&amp;filter_event_type=programme&amp;filter_event_id=<?php echo (int)$p['id']; ?>" style="margin-right:8px;">Voir inscrits</a>
                            <a href="?section=programmes&amp;edit_programme=<?php echo (int)$p['id']; ?>" style="margin-right:8px;">Modifier</a>
                            <a href="?delete_programme=<?php echo (int)$p['id']; ?>" onclick="return confirm('Supprimer cet événement ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($programmesTotal > $programmesPerPage): ?>
            <div style="margin-top:10px; display:flex; justify-content:space-between; align-items:center; font-size:0.9rem;">
                <div>
                    Page <?php echo $programmePage; ?> / <?php echo max(1, (int)ceil($programmesTotal / $programmesPerPage)); ?>
                </div>
                <div style="display:flex; gap:8px;">
                    <?php if ($programmeHasPrev): ?>
                        <a href="?section=programmes&amp;programme_page=<?php echo $programmePage - 1; ?><?php echo !empty($_GET['programme_year']) ? '&amp;programme_year='.(int)$_GET['programme_year'] : ''; ?>" class="btn btn-secondary" style="padding:4px 10px; font-size:0.85rem;">&laquo; Précédent</a>
                    <?php endif; ?>
                    <?php if ($programmeHasNext): ?>
                        <a href="?section=programmes&amp;programme_page=<?php echo $programmePage + 1; ?><?php echo !empty($_GET['programme_year']) ? '&amp;programme_year='.(int)$_GET['programme_year'] : ''; ?>" class="btn btn-secondary" style="padding:4px 10px; font-size:0.85rem;">Suivant &raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- MODAL PROGRAMME -->
<div id="programme-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:9999; align-items:center; justify-content:center; overflow-y:auto; padding:20px;">
    <div style="background:white; border-radius:12px; width:100%; max-width:600px; padding:30px; position:relative; box-shadow:0 20px 60px rgba(0,0,0,0.3); margin:auto;">
        <button onclick="closeProgrammeModal()" style="position:absolute; top:15px; right:15px; background:transparent; border:none; font-size:28px; cursor:pointer; color:#999; width:36px; height:36px; display:flex; align-items:center; justify-content:center;">✕</button>
        
        <h2 id="programme-modal-title" style="margin-bottom:6px; margin-top:0;">Ajouter un programme</h2>
        <p style="margin:0 0 18px; font-size:13px; color:#666;">Complétez les informations de base, les détails pratiques puis l'affiche éventuelle.</p>
        
        <form method="post" enctype="multipart/form-data">
            <?php if ($isEditProg): ?>
                <input type="hidden" name="update_programme" value="1">
                <input type="hidden" name="programme_id" value="<?php echo (int)$programmeToEdit['id']; ?>">
            <?php else: ?>
                <input type="hidden" name="add_programme" value="1">
            <?php endif; ?>

            <h3 style="margin:10px 0 8px; font-size:14px; text-transform:uppercase; letter-spacing:0.06em; color:#555;">Informations de base</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Mois *</label>
                    <input type="text" name="prog_mois" placeholder="Ex: Janvier" value="<?php echo $isEditProg ? htmlspecialchars($programmeToEdit['mois']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Titre de l'événement *</label>
                    <input type="text" name="prog_titre" placeholder="Ex: Jeûne &amp; Prière" value="<?php echo $isEditProg ? htmlspecialchars($programmeToEdit['titre']) : ''; ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label>Thème (optionnel)</label>
                <input type="text" name="prog_theme" placeholder="Ex: Consécration &amp; Restauration" value="<?php echo $isEditProg && !empty($programmeToEdit['theme']) ? htmlspecialchars($programmeToEdit['theme']) : ''; ?>">
            </div>

            <h3 style="margin:16px 0 8px; font-size:14px; text-transform:uppercase; letter-spacing:0.06em; color:#555;">Détails pratiques</h3>
            <div class="form-group">
                <label>Détails (ligne courte)</label>
                <input type="text" name="prog_details" placeholder="Ex: 2 - 10 Janvier, Thème : Consécration &amp; Restauration" value="<?php echo $isEditProg && !empty($programmeToEdit['details']) ? htmlspecialchars($programmeToEdit['details']) : ''; ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Prix de participation (optionnel)</label>
                    <input type="text" name="prog_prix" placeholder="Ex: 10 000 FC / 15€" value="<?php echo $isEditProg && !empty($programmeToEdit['prix']) ? htmlspecialchars($programmeToEdit['prix']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>Ordre d'affichage (optionnel)</label>
                    <input type="number" name="prog_ordre" placeholder="0" value="<?php echo $isEditProg ? (int)$programmeToEdit['ordre'] : ''; ?>">
                </div>
            </div>
            <div style="display:flex; gap:10px;">
                <div class="form-group" style="flex:1;">
                    <label>Date de début</label>
                    <input type="date" name="prog_date_debut" value="<?php echo $isEditProg && !empty($programmeToEdit['date_debut']) ? htmlspecialchars($programmeToEdit['date_debut']) : ''; ?>">
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Date de fin</label>
                    <input type="date" name="prog_date_fin" value="<?php echo $isEditProg && !empty($programmeToEdit['date_fin']) ? htmlspecialchars($programmeToEdit['date_fin']) : ''; ?>">
                </div>
            </div>

            <h3 style="margin:16px 0 8px; font-size:14px; text-transform:uppercase; letter-spacing:0.06em; color:#555;">Support visuel</h3>
            <div class="form-group">
                <label>Affiche (image optionnelle)</label>
                <input type="file" name="prog_affiche_file" accept="image/*">
                <?php if ($isEditProg && !empty($programmeToEdit['affiche_url'])): ?>
                    <div style="margin-top:4px; font-size:12px; color:#555;">
                        Affiche actuelle : <img src="<?php echo htmlspecialchars($programmeToEdit['affiche_url']); ?>" alt="Affiche" style="max-width:80px; max-height:80px; border-radius:4px; border:1px solid #ddd; vertical-align:middle;">
                    </div>
                <?php endif; ?>
            </div>
            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn primary"><?php echo $isEditProg ? 'Mettre à jour le programme' : 'Ajouter l\'événement'; ?></button>
                <button type="button" onclick="closeProgrammeModal()" class="btn">Annuler</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openProgrammeModal() {
        document.getElementById('programme-modal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    function closeProgrammeModal() {
        document.getElementById('programme-modal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    document.getElementById('btn-open-programme-modal').addEventListener('click', openProgrammeModal);
    document.getElementById('programme-modal').addEventListener('click', function(e) {
        if (e.target === this) closeProgrammeModal();
    });
    <?php if ($isEditProg): ?>
        openProgrammeModal();
        document.getElementById('programme-modal-title').textContent = 'Modifier le programme';
    <?php endif; ?>
</script>
