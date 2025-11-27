<div class="admin-section" id="inscriptions">
    <h2>Inscriptions à la retraite <span class="badge"><?php echo count($inscriptions); ?></span></h2>

    <div style="background: rgba(44, 110, 73, 0.1); border: 2px solid #2c6e49; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
        <p style="margin: 0; color: #2c6e49;">
            ✨ <strong>Nouvelle gestion améliorée !</strong><br>
            Vous pouvez maintenant gérer les inscriptions par activité et télécharger les listes en CSV depuis la section 
            <a href="?section=inscriptions_gestion" style="color: #2c6e49; font-weight: 600; text-decoration: underline;">"Inscriptions"</a> 
            dans le menu (voir le premier élément après les retraites).
        </p>
    </div>

    <div style="margin:10px 0;">
        <form method="get" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
            <input type="hidden" name="section" value="inscriptions">
            <label style="font-size:14px;">Filtrer par temps de présence :</label>
            <select name="insc_temps" onchange="this.form.submit()" style="padding:6px;">
                <option value="">Tous</option>
                <option value="plein" <?php echo (isset($_GET['insc_temps']) && $_GET['insc_temps']==='plein') ? 'selected' : ''; ?>>Temps plein</option>
                <option value="partiel" <?php echo (isset($_GET['insc_temps']) && $_GET['insc_temps']==='partiel') ? 'selected' : ''; ?>>Temps partiel</option>
            </select>
            <?php if (!empty($_GET['insc_temps'])): ?>
                <a href="?section=inscriptions" class="btn" style="margin-left:6px;">Réinitialiser</a>
            <?php endif; ?>
        </form>
    </div>

    <?php
        $inscPerPage = 15;
        $inscPage = isset($_GET['insc_page']) && is_numeric($_GET['insc_page']) ? max(1, (int)$_GET['insc_page']) : 1;
        $inscTotal = count($inscriptions);
        $inscOffset = ($inscPage - 1) * $inscPerPage;
        $inscPageItems = array_slice($inscriptions, $inscOffset, $inscPerPage);
        $inscHasPrev = $inscPage > 1;
        $inscHasNext = $inscOffset + $inscPerPage < $inscTotal;
    ?>

    <?php if (empty($inscPageItems)): ?>
        <p>Aucune inscription.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Âge</th>
                    <th>Église</th>
                    <th>Adresse</th>
                    <th>Temps présence</th>
                    <th>Type évènement</th>
                    <th>Évènement</th>
                    <th>Besoin particulier</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inscPageItems as $i): ?>
                    <tr>
                        <td><?php echo (int)$i['id']; ?></td>
                        <td><?php echo htmlspecialchars($i['nom']); ?></td>
                        <td><?php echo htmlspecialchars($i['email']); ?></td>
                        <td><?php echo htmlspecialchars($i['telephone']); ?></td>
                        <td><?php echo (int)$i['age']; ?></td>
                        <td><?php echo htmlspecialchars($i['eglise']); ?></td>
                        <td><?php echo htmlspecialchars($i['adresse'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($i['temps_sejour'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($i['event_type'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($i['event_label'] ?? ''); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($i['besoins'])); ?></td>
                        <td><?php echo htmlspecialchars($i['created_at']); ?></td>
                        <td class="actions">
                            <a href="?delete_inscription=<?php echo (int)$i['id']; ?>" onclick="return confirm('Supprimer cette inscription ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($inscTotal > $inscPerPage): ?>
            <div style="margin-top:10px; display:flex; justify-content:space-between; align-items:center; font-size:0.9rem;">
                <div>
                    Page <?php echo $inscPage; ?> / <?php echo max(1, (int)ceil($inscTotal / $inscPerPage)); ?>
                </div>
                <div style="display:flex; gap:8px;">
                    <?php if ($inscHasPrev): ?>
                        <a href="?section=inscriptions&amp;insc_page=<?php echo $inscPage - 1; ?><?php echo !empty($_GET['insc_temps']) ? '&amp;insc_temps='.htmlspecialchars($_GET['insc_temps']) : ''; ?>" class="btn btn-secondary" style="padding:4px 10px; font-size:0.85rem;">&laquo; Précédent</a>
                    <?php endif; ?>
                    <?php if ($inscHasNext): ?>
                        <a href="?section=inscriptions&amp;insc_page=<?php echo $inscPage + 1; ?><?php echo !empty($_GET['insc_temps']) ? '&amp;insc_temps='.htmlspecialchars($_GET['insc_temps']) : ''; ?>" class="btn btn-secondary" style="padding:4px 10px; font-size:0.85rem;">Suivant &raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
