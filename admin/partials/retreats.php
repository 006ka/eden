<div class="admin-section" id="retreats">
    <h2 style="display:flex; justify-content:space-between; align-items:center;">
        <span>Retraites <span class="badge"><?php echo count($retreats); ?></span></span>
        <button id="btn-toggle-retreat-form" class="btn">Ajouter une retraite</button>
    </h2>

    <?php if ($retreatError !== ''): ?>
        <p style="color:red; margin-bottom:10px;"><?php echo htmlspecialchars($retreatError); ?></p>
    <?php endif; ?>

    <?php if (empty($retreats)): ?>
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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($retreats as $r): ?>
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
                        <td class="actions">
                            <form method="post" style="display:inline-block; margin-right:10px;">
                                <input type="hidden" name="update_programme" value="1">
                                <input type="hidden" name="retreat_id" value="<?php echo (int)$r['id']; ?>">
                                <input type="text" name="programme_image_url" value="<?php echo htmlspecialchars($r['programme_image_url'] ?? ''); ?>" placeholder="URL image programme" style="width:180px; padding:4px; font-size:12px;">
                                <button type="submit" class="btn" style="padding:4px 8px; font-size:12px;">Enregistrer</button>
                            </form>
                            <a href="?delete_retreat=<?php echo (int)$r['id']; ?>" onclick="return confirm('Supprimer cette retraite ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div id="retreat-form" style="display:none; margin-top:18px;">
        <form method="post" style="margin-bottom:20px;">
            <input type="hidden" name="add_retreat" value="1">
            <div class="form-group">
                <label>Titre *</label>
                <input type="text" name="ret_titre" required>
            </div>
            <div class="form-group">
                <label>Thème</label>
                <input type="text" name="ret_theme">
            </div>
            <div class="form-group">
                <label>Date de début</label>
                <input type="date" name="ret_date_debut">
            </div>
            <div class="form-group">
                <label>Date de fin</label>
                <input type="date" name="ret_date_fin">
            </div>
            <div class="form-group">
                <label>Lieu</label>
                <input type="text" name="ret_lieu">
            </div>
            <div class="form-group">
                <label>Orateurs</label>
                <input type="text" name="ret_orateurs">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="ret_description" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>URL de l'image du programme (optionnel)</label>
                <input type="text" name="ret_programme_image_url" placeholder="Ex: ../uploads/programme1.png">
            </div>
            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn primary">Ajouter la retraite</button>
                <button type="button" id="btn-cancel-retreat" class="btn">Annuler</button>
            </div>
        </form>
    </div>

    <script>
        (function(){
            var btn = document.getElementById('btn-toggle-retreat-form');
            var formWrap = document.getElementById('retreat-form');
            var cancel = document.getElementById('btn-cancel-retreat');
            if (btn && formWrap) {
                btn.addEventListener('click', function(){
                    formWrap.style.display = formWrap.style.display === 'none' || formWrap.style.display === '' ? 'block' : 'none';
                    btn.textContent = formWrap.style.display === 'block' ? 'Masquer le formulaire' : 'Ajouter une retraite';
                    if (formWrap.style.display === 'block') {
                        formWrap.scrollIntoView({behavior:'smooth', block:'center'});
                    }
                });
            }
            if (cancel && formWrap) {
                cancel.addEventListener('click', function(){ formWrap.style.display = 'none'; btn.textContent = 'Ajouter une retraite'; });
            }
        })();
    </script>
</div>
