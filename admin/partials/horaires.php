<?php
// Gestion des horaires des retraites

// Récupère les retraites pour le filtre
$allRetreats = $pdo->query('SELECT id, titre, date_debut, date_fin FROM retreats ORDER BY date_debut DESC')->fetchAll(PDO::FETCH_ASSOC);

// Retraite sélectionnée
$selectedRetreatId = isset($_GET['retreat_id']) && is_numeric($_GET['retreat_id']) ? (int)$_GET['retreat_id'] : null;
$selectedRetreat = null;
$schedules = [];

if ($selectedRetreatId) {
    $stmt = $pdo->prepare('SELECT * FROM retreats WHERE id = ?');
    $stmt->execute([$selectedRetreatId]);
    $selectedRetreat = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($selectedRetreat) {
        $stmt = $pdo->prepare('SELECT * FROM retreat_schedules WHERE retreat_id = ? ORDER BY ordre ASC, heure_debut ASC');
        $stmt->execute([$selectedRetreatId]);
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Ajout d'un horaire
if (isset($_POST['add_schedule'])) {
    $retreat_id = isset($_POST['retreat_id']) && is_numeric($_POST['retreat_id']) ? (int)$_POST['retreat_id'] : 0;
    $jour = trim($_POST['jour'] ?? '');
    $heure_debut = trim($_POST['heure_debut'] ?? '');
    $heure_fin = trim($_POST['heure_fin'] ?? '');
    $activite = trim($_POST['activite'] ?? '');
    $responsable = trim($_POST['responsable'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $ordre = isset($_POST['ordre']) && is_numeric($_POST['ordre']) ? (int)$_POST['ordre'] : 0;

    if ($retreat_id > 0 && $jour !== '' && $heure_debut !== '' && $heure_fin !== '' && $activite !== '') {
        $stmt = $pdo->prepare('INSERT INTO retreat_schedules (retreat_id, jour, heure_debut, heure_fin, activite, responsable, description, ordre, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$retreat_id, $jour, $heure_debut, $heure_fin, $activite, $responsable !== '' ? $responsable : null, $description !== '' ? $description : null, $ordre]);
    }
    header('Location: admin.php?section=horaires&retreat_id=' . $retreat_id);
    exit;
}

// Modification d'un horaire
if (isset($_POST['update_schedule'])) {
    $id = isset($_POST['schedule_id']) && is_numeric($_POST['schedule_id']) ? (int)$_POST['schedule_id'] : 0;
    $retreat_id = isset($_POST['retreat_id']) && is_numeric($_POST['retreat_id']) ? (int)$_POST['retreat_id'] : 0;
    $jour = trim($_POST['jour'] ?? '');
    $heure_debut = trim($_POST['heure_debut'] ?? '');
    $heure_fin = trim($_POST['heure_fin'] ?? '');
    $activite = trim($_POST['activite'] ?? '');
    $responsable = trim($_POST['responsable'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $ordre = isset($_POST['ordre']) && is_numeric($_POST['ordre']) ? (int)$_POST['ordre'] : 0;

    if ($id > 0 && $retreat_id > 0) {
        $stmt = $pdo->prepare('UPDATE retreat_schedules SET jour = ?, heure_debut = ?, heure_fin = ?, activite = ?, responsable = ?, description = ?, ordre = ? WHERE id = ?');
        $stmt->execute([$jour, $heure_debut, $heure_fin, $activite, $responsable !== '' ? $responsable : null, $description !== '' ? $description : null, $ordre, $id]);
    }
    header('Location: admin.php?section=horaires&retreat_id=' . $retreat_id);
    exit;
}

// Suppression d'un horaire
if (isset($_GET['delete_schedule'])) {
    $id = (int)$_GET['delete_schedule'];
    $stmt = $pdo->prepare('SELECT retreat_id FROM retreat_schedules WHERE id = ?');
    $stmt->execute([$id]);
    $sch = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($sch) {
        $pdo->prepare('DELETE FROM retreat_schedules WHERE id = ?')->execute([$id]);
        header('Location: admin.php?section=horaires&retreat_id=' . $sch['retreat_id']);
        exit;
    }
}

// Horaire à modifier
$scheduleToEdit = null;
if (isset($_GET['edit_schedule'])) {
    $id = (int)$_GET['edit_schedule'];
    $stmt = $pdo->prepare('SELECT * FROM retreat_schedules WHERE id = ?');
    $stmt->execute([$id]);
    $scheduleToEdit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="admin-section" id="horaires">
    <h2 style="display:flex; justify-content:space-between; align-items:center;">
        <span>Horaires des Retraites</span>
    </h2>

    <!-- FILTRE PAR RETRAITE -->
    <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="margin-top: 0; margin-bottom: 15px;">Sélectionner une retraite</h3>
        <form method="get" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
            <input type="hidden" name="section" value="horaires">
            <div style="flex: 1; min-width: 300px;">
                <label style="display: block; font-weight: 600; margin-bottom: 6px;">Retraite</label>
                <select name="retreat_id" onchange="this.form.submit()" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                    <option value="">-- Sélectionner une retraite --</option>
                    <?php foreach ($allRetreats as $r): ?>
                        <option value="<?php echo (int)$r['id']; ?>" <?php echo ($selectedRetreatId === (int)$r['id'] ? 'selected' : ''); ?>>
                            <?php echo htmlspecialchars($r['titre']); ?> 
                            <?php if ($r['date_debut']): ?>(<?php echo date('d/m/Y', strtotime($r['date_debut'])); ?>)<?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

    <?php if ($selectedRetreat): ?>
        <div style="background: rgba(44, 110, 73, 0.1); border: 2px solid #2c6e49; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
            <h3 style="margin: 0 0 10px 0;">
                📅 <?php echo htmlspecialchars($selectedRetreat['titre']); ?>
                <?php if ($selectedRetreat['date_debut'] && $selectedRetreat['date_fin']): ?>
                    <br><small style="font-weight: normal; font-size: 0.9em;">
                        Du <?php echo date('d/m/Y', strtotime($selectedRetreat['date_debut'])); ?> 
                        au <?php echo date('d/m/Y', strtotime($selectedRetreat['date_fin'])); ?>
                    </small>
                <?php endif; ?>
            </h3>
            <button id="btn-open-schedule-modal" class="btn" style="background: #2c6e49; color: white; margin-top: 10px;">➕ Ajouter un horaire</button>
        </div>

        <?php if (empty($schedules)): ?>
            <div style="text-align: center; padding: 40px; color: #999;">
                <p>Aucun horaire enregistré pour cette retraite.</p>
                <p style="font-size: 0.9em;">Commencez par ajouter le premier horaire en cliquant sur le bouton ci-dessus.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Jour</th>
                            <th>Heure</th>
                            <th>Activité</th>
                            <th>Responsable</th>
                            <th>Description</th>
                            <th>Ordre</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $sch): ?>
                            <tr>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($sch['jour']); ?></td>
                                <td><?php echo date('H:i', strtotime($sch['heure_debut'])); ?> - <?php echo date('H:i', strtotime($sch['heure_fin'])); ?></td>
                                <td><?php echo htmlspecialchars($sch['activite']); ?></td>
                                <td><?php echo htmlspecialchars($sch['responsable'] ?? '-'); ?></td>
                                <td>
                                    <?php if ($sch['description']): ?>
                                        <small><?php echo htmlspecialchars(substr($sch['description'], 0, 50)); ?><?php echo strlen($sch['description']) > 50 ? '...' : ''; ?></small>
                                    <?php else: ?>
                                        <span style="color: #999;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;"><?php echo (int)$sch['ordre']; ?></td>
                                <td class="actions">
                                    <a href="?section=horaires&amp;retreat_id=<?php echo $selectedRetreatId; ?>&amp;edit_schedule=<?php echo (int)$sch['id']; ?>" style="margin-right: 8px;">Modifier</a>
                                    <a href="?delete_schedule=<?php echo (int)$sch['id']; ?>" onclick="return confirm('Supprimer cet horaire ?');">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 60px 20px; color: #999;">
            <p style="font-size: 1.1em; margin-bottom: 10px;">Sélectionnez une retraite pour gérer ses horaires</p>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL AJOUT/MODIFICATION HORAIRE -->
<div id="schedule-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:9999; align-items:center; justify-content:center; overflow-y:auto; padding:20px;">
    <div style="background:white; border-radius:12px; width:100%; max-width:600px; padding:30px; position:relative; box-shadow:0 20px 60px rgba(0,0,0,0.3); margin:auto;">
        <button onclick="closeScheduleModal()" style="position:absolute; top:15px; right:15px; background:transparent; border:none; font-size:28px; cursor:pointer; color:#999; width:36px; height:36px; display:flex; align-items:center; justify-content:center;">✕</button>
        
        <h2 id="schedule-modal-title" style="margin-bottom:20px; margin-top:0;">Ajouter un horaire</h2>
        
        <?php $isEditSchedule = isset($scheduleToEdit) && is_array($scheduleToEdit); ?>
        <form method="post">
            <?php if ($isEditSchedule): ?>
                <input type="hidden" name="update_schedule" value="1">
                <input type="hidden" name="schedule_id" value="<?php echo (int)$scheduleToEdit['id']; ?>">
                <input type="hidden" name="retreat_id" value="<?php echo (int)$scheduleToEdit['retreat_id']; ?>">
            <?php else: ?>
                <input type="hidden" name="add_schedule" value="1">
                <input type="hidden" name="retreat_id" value="<?php echo $selectedRetreatId; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>Jour *</label>
                <input type="text" name="jour" placeholder="Ex: Lundi 25 Janvier" value="<?php echo $isEditSchedule ? htmlspecialchars($scheduleToEdit['jour']) : ''; ?>" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Heure de début *</label>
                    <input type="time" name="heure_debut" value="<?php echo $isEditSchedule ? htmlspecialchars($scheduleToEdit['heure_debut']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Heure de fin *</label>
                    <input type="time" name="heure_fin" value="<?php echo $isEditSchedule ? htmlspecialchars($scheduleToEdit['heure_fin']) : ''; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Activité *</label>
                <input type="text" name="activite" placeholder="Ex: Prière du matin, Déjeuner, Enseignement..." value="<?php echo $isEditSchedule ? htmlspecialchars($scheduleToEdit['activite']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label>Responsable (optionnel)</label>
                <input type="text" name="responsable" placeholder="Ex: Pasteur Jean, Mme Marie..." value="<?php echo $isEditSchedule && !empty($scheduleToEdit['responsable']) ? htmlspecialchars($scheduleToEdit['responsable']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Description (optionnel)</label>
                <textarea name="description" rows="3" placeholder="Détails supplémentaires..."><?php echo $isEditSchedule && !empty($scheduleToEdit['description']) ? htmlspecialchars($scheduleToEdit['description']) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label>Ordre d'affichage</label>
                <input type="number" name="ordre" placeholder="0" value="<?php echo $isEditSchedule ? (int)$scheduleToEdit['ordre'] : '0'; ?>">
            </div>

            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn primary"><?php echo $isEditSchedule ? 'Mettre à jour' : 'Ajouter'; ?></button>
                <button type="button" onclick="closeScheduleModal()" class="btn">Annuler</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openScheduleModal() {
        document.getElementById('schedule-modal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    function closeScheduleModal() {
        document.getElementById('schedule-modal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    const btn = document.getElementById('btn-open-schedule-modal');
    if (btn) {
        btn.addEventListener('click', openScheduleModal);
    }
    
    document.getElementById('schedule-modal').addEventListener('click', function(e) {
        if (e.target === this) closeScheduleModal();
    });
    
    <?php if ($isEditSchedule): ?>
        openScheduleModal();
        document.getElementById('schedule-modal-title').textContent = 'Modifier l\'horaire';
    <?php endif; ?>
</script>
