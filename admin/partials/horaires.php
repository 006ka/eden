<?php
// Gestion des horaires des retraites

// Fonction pour récupérer toutes les retraites
function getAllRetreats($pdo) {
    $stmt = $pdo->query('SELECT id, titre, date_debut, date_fin FROM retreats ORDER BY date_debut DESC');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour récupérer une retraite spécifique
function getRetreatById($pdo, $id) {
    if (!$id) return null;
    
    $stmt = $pdo->prepare('SELECT * FROM retreats WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fonction pour récupérer les horaires d'une retraite
function getRetreatSchedules($pdo, $retreatId) {
    if (!$retreatId) return [];
    
    $stmt = $pdo->prepare('SELECT * FROM retreat_schedules WHERE retreat_id = ? ORDER BY ordre ASC, heure_debut ASC');
    $stmt->execute([$retreatId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour valider et nettoyer les données d'horaire
function validateScheduleData($data) {
    return [
        'retreat_id' => isset($data['retreat_id']) && is_numeric($data['retreat_id']) ? (int)$data['retreat_id'] : 0,
        'jour' => trim($data['jour'] ?? ''),
        'heure_debut' => trim($data['heure_debut'] ?? ''),
        'heure_fin' => trim($data['heure_fin'] ?? ''),
        'activite' => trim($data['activite'] ?? ''),
        'responsable' => !empty(trim($data['responsable'] ?? '')) ? trim($data['responsable']) : null,
        'description' => !empty(trim($data['description'] ?? '')) ? trim($data['description']) : null,
        'ordre' => isset($data['ordre']) && is_numeric($data['ordre']) ? (int)$data['ordre'] : 0
    ];
}

// Récupère les retraites pour le filtre
$allRetreats = getAllRetreats($pdo);

// Retraite sélectionnée
$selectedRetreatId = isset($_GET['retreat_id']) && is_numeric($_GET['retreat_id']) ? (int)$_GET['retreat_id'] : null;
$selectedRetreat = $selectedRetreatId ? getRetreatById($pdo, $selectedRetreatId) : null;
$schedules = $selectedRetreat ? getRetreatSchedules($pdo, $selectedRetreatId) : [];

// Ajout d'un horaire
if (isset($_POST['add_schedule'])) {
    $data = validateScheduleData($_POST);
    
    if ($data['retreat_id'] > 0 && !empty($data['jour']) && !empty($data['heure_debut']) && !empty($data['heure_fin']) && !empty($data['activite'])) {
        $stmt = $pdo->prepare('INSERT INTO retreat_schedules (retreat_id, jour, heure_debut, heure_fin, activite, responsable, description, ordre, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            $data['retreat_id'], 
            $data['jour'], 
            $data['heure_debut'], 
            $data['heure_fin'], 
            $data['activite'], 
            $data['responsable'], 
            $data['description'], 
            $data['ordre']
        ]);
        
        // Redirection pour éviter le rechargement du formulaire
        header('Location: admin.php?section=horaires&retreat_id=' . $data['retreat_id']);
        exit;
    }
}

// Modification d'un horaire
if (isset($_POST['update_schedule'])) {
    $scheduleId = isset($_POST['schedule_id']) && is_numeric($_POST['schedule_id']) ? (int)$_POST['schedule_id'] : 0;
    $data = validateScheduleData($_POST);
    
    if ($scheduleId > 0 && $data['retreat_id'] > 0) {
        $stmt = $pdo->prepare('UPDATE retreat_schedules SET jour = ?, heure_debut = ?, heure_fin = ?, activite = ?, responsable = ?, description = ?, ordre = ? WHERE id = ?');
        $stmt->execute([
            $data['jour'], 
            $data['heure_debut'], 
            $data['heure_fin'], 
            $data['activite'], 
            $data['responsable'], 
            $data['description'], 
            $data['ordre'], 
            $scheduleId
        ]);
        
        header('Location: admin.php?section=horaires&retreat_id=' . $data['retreat_id']);
        exit;
    }
}

// Suppression d'un horaire
if (isset($_GET['delete_schedule'])) {
    $scheduleId = (int)$_GET['delete_schedule'];
    $stmt = $pdo->prepare('SELECT retreat_id FROM retreat_schedules WHERE id = ?');
    $stmt->execute([$scheduleId]);
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($schedule) {
        $pdo->prepare('DELETE FROM retreat_schedules WHERE id = ?')->execute([$scheduleId]);
        header('Location: admin.php?section=horaires&retreat_id=' . $schedule['retreat_id']);
        exit;
    }
}

// Horaire à modifier
$scheduleToEdit = null;
if (isset($_GET['edit_schedule'])) {
    $scheduleId = (int)$_GET['edit_schedule'];
    $stmt = $pdo->prepare('SELECT * FROM retreat_schedules WHERE id = ?');
    $stmt->execute([$scheduleId]);
    $scheduleToEdit = $stmt->fetch(PDO::FETCH_ASSOC);
}

$isEditSchedule = !empty($scheduleToEdit);
?>

<div class="admin-section" id="horaires">
    <h2 class="admin-section-header">
        <span>Horaires des Retraites</span>
    </h2>

    <!-- FILTRE PAR RETRAITE -->
    <div class="filter-container">
        <h3 class="filter-title">Sélectionner une retraite</h3>
        <form method="get" class="filter-form">
            <input type="hidden" name="section" value="horaires">
            <div class="form-group">
                <label class="form-label">Retraite</label>
                <select name="retreat_id" onchange="this.form.submit()" class="form-select">
                    <option value="">-- Sélectionner une retraite --</option>
                    <?php foreach ($allRetreats as $retreat): ?>
                        <option value="<?= (int)$retreat['id'] ?>" <?= ($selectedRetreatId === (int)$retreat['id'] ? 'selected' : '') ?>>
                            <?= htmlspecialchars($retreat['titre']) ?> 
                            <?php if ($retreat['date_debut']): ?>
                                (<?= date('d/m/Y', strtotime($retreat['date_debut'])) ?>)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

    <?php if ($selectedRetreat): ?>
        <div class="selected-retreat-info">
            <h3 class="retreat-title">
                📅 <?= htmlspecialchars($selectedRetreat['titre']) ?>
                <?php if ($selectedRetreat['date_debut'] && $selectedRetreat['date_fin']): ?>
                    <br><small class="retreat-dates">
                        Du <?= date('d/m/Y', strtotime($selectedRetreat['date_debut'])) ?> 
                        au <?= date('d/m/Y', strtotime($selectedRetreat['date_fin'])) ?>
                    </small>
                <?php endif; ?>
            </h3>
            <button id="btn-open-schedule-modal" class="btn btn-primary">➕ Ajouter un horaire</button>
        </div>

        <?php if (empty($schedules)): ?>
            <div class="empty-state">
                <p>Aucun horaire enregistré pour cette retraite.</p>
                <p class="empty-state-hint">Commencez par ajouter le premier horaire en cliquant sur le bouton ci-dessus.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="data-table">
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
                        <?php foreach ($schedules as $schedule): ?>
                            <tr>
                                <td class="day-cell"><?= htmlspecialchars($schedule['jour']) ?></td>
                                <td class="time-cell">
                                    <?= date('H:i', strtotime($schedule['heure_debut'])) ?> - <?= date('H:i', strtotime($schedule['heure_fin'])) ?>
                                </td>
                                <td><?= htmlspecialchars($schedule['activite']) ?></td>
                                <td><?= htmlspecialchars($schedule['responsable'] ?? '-') ?></td>
                                <td class="description-cell">
                                    <?php if ($schedule['description']): ?>
                                        <small><?= htmlspecialchars(substr($schedule['description'], 0, 50)) ?><?= strlen($schedule['description']) > 50 ? '...' : '' ?></small>
                                    <?php else: ?>
                                        <span class="no-data">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="order-cell"><?= (int)$schedule['ordre'] ?></td>
                                <td class="actions-cell">
                                    <a href="?section=horaires&amp;retreat_id=<?= $selectedRetreatId ?>&amp;edit_schedule=<?= (int)$schedule['id'] ?>" class="action-link">Modifier</a>
                                    <a href="?delete_schedule=<?= (int)$schedule['id'] ?>" onclick="return confirm('Supprimer cet horaire ?');" class="action-link action-delete">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-state">
            <p class="empty-state-message">Sélectionnez une retraite pour gérer ses horaires</p>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL AJOUT/MODIFICATION HORAIRE -->
<div id="schedule-modal" class="modal">
    <div class="modal-content">
        <button onclick="closeScheduleModal()" class="modal-close-btn">✕</button>
        
        <h2 id="schedule-modal-title" class="modal-title">
            <?= $isEditSchedule ? 'Modifier l\'horaire' : 'Ajouter un horaire' ?>
        </h2>
        
        <form method="post" class="modal-form">
            <?php if ($isEditSchedule): ?>
                <input type="hidden" name="update_schedule" value="1">
                <input type="hidden" name="schedule_id" value="<?= (int)$scheduleToEdit['id'] ?>">
                <input type="hidden" name="retreat_id" value="<?= (int)$scheduleToEdit['retreat_id'] ?>">
            <?php else: ?>
                <input type="hidden" name="add_schedule" value="1">
                <input type="hidden" name="retreat_id" value="<?= $selectedRetreatId ?>">
            <?php endif; ?>

            <div class="form-group">
                <label class="form-label">Jour *</label>
                <input type="text" name="jour" placeholder="Ex: Lundi 25 Janvier" 
                       value="<?= $isEditSchedule ? htmlspecialchars($scheduleToEdit['jour']) : '' ?>" required class="form-input">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Heure de début *</label>
                    <input type="time" name="heure_debut" 
                           value="<?= $isEditSchedule ? htmlspecialchars($scheduleToEdit['heure_debut']) : '' ?>" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Heure de fin *</label>
                    <input type="time" name="heure_fin" 
                           value="<?= $isEditSchedule ? htmlspecialchars($scheduleToEdit['heure_fin']) : '' ?>" required class="form-input">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Activité *</label>
                <input type="text" name="activite" placeholder="Ex: Prière du matin, Déjeuner, Enseignement..." 
                       value="<?= $isEditSchedule ? htmlspecialchars($scheduleToEdit['activite']) : '' ?>" required class="form-input">
            </div>

            <div class="form-group">
                <label class="form-label">Responsable (optionnel)</label>
                <input type="text" name="responsable" placeholder="Ex: Pasteur Jean, Mme Marie..." 
                       value="<?= $isEditSchedule && !empty($scheduleToEdit['responsable']) ? htmlspecialchars($scheduleToEdit['responsable']) : '' ?>" class="form-input">
            </div>

            <div class="form-group">
                <label class="form-label">Description (optionnel)</label>
                <textarea name="description" rows="3" placeholder="Détails supplémentaires..." class="form-textarea"><?= $isEditSchedule && !empty($scheduleToEdit['description']) ? htmlspecialchars($scheduleToEdit['description']) : '' ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Ordre d'affichage</label>
                <input type="number" name="ordre" placeholder="0" 
                       value="<?= $isEditSchedule ? (int)$scheduleToEdit['ordre'] : '0' ?>" class="form-input">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $isEditSchedule ? 'Mettre à jour' : 'Ajouter' ?></button>
                <button type="button" onclick="closeScheduleModal()" class="btn btn-secondary">Annuler</button>
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
        
        // Redirection pour fermer le mode édition si ouvert
        <?php if ($isEditSchedule): ?>
            window.location.href = '?section=horaires&retreat_id=<?= $selectedRetreatId ?>';
        <?php endif; ?>
    }
    
    // Événements
    document.addEventListener('DOMContentLoaded', function() {
        const openBtn = document.getElementById('btn-open-schedule-modal');
        const modal = document.getElementById('schedule-modal');
        
        if (openBtn) {
            openBtn.addEventListener('click', openScheduleModal);
        }
        
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) closeScheduleModal();
            });
        }
        
        // Ouvrir modal si en mode édition
        <?php if ($isEditSchedule): ?>
            openScheduleModal();
        <?php endif; ?>
    });
</script>

<style>
/* Styles pour une meilleure organisation */
.filter-container { background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
.filter-title { margin-top: 0; margin-bottom: 15px; }
.filter-form { display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; }
.selected-retreat-info { background: rgba(44, 110, 73, 0.1); border: 2px solid #2c6e49; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
.retreat-title { margin: 0 0 10px 0; }
.retreat-dates { font-weight: normal; font-size: 0.9em; }
.empty-state { text-align: center; padding: 40px; color: #999; }
.empty-state-hint { font-size: 0.9em; }
.table-container { overflow-x: auto; }
.day-cell { font-weight: 600; }
.no-data { color: #999; }
.order-cell { text-align: center; }
.actions-cell { white-space: nowrap; }
.action-link { margin-right: 8px; }
.action-delete { color: #dc3545; }

/* Styles du modal */
.modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center; overflow-y: auto; padding: 20px; }
.modal-content { background: white; border-radius: 12px; width: 100%; max-width: 600px; padding: 30px; position: relative; box-shadow: 0 20px 60px rgba(0,0,0,0.3); margin: auto; }
.modal-close-btn { position: absolute; top: 15px; right: 15px; background: transparent; border: none; font-size: 28px; cursor: pointer; color: #999; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; }
.modal-title { margin-bottom: 20px; margin-top: 0; }

/* Styles des formulaires */
.form-group { margin-bottom: 15px; }
.form-label { display: block; font-weight: 600; margin-bottom: 6px; }
.form-input, .form-select, .form-textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
.form-actions { display: flex; gap: 8px; }

/* Boutons */
.btn { padding: 10px 16px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; text-align: center; }
.btn-primary { background: #2c6e49; color: white; }
.btn-secondary { background: #6c757d; color: white; }
</style>