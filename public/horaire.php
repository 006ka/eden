<?php
require_once __DIR__ . '/../config/db.php';

// Récupère tous les horaires disponibles
$retreatsWithSchedules = [];
$stmt = $pdo->query('
    SELECT DISTINCT r.id, r.titre, r.date_debut, r.date_fin
    FROM retreats r
    INNER JOIN retreat_schedules rs ON r.id = rs.retreat_id
    ORDER BY r.date_debut DESC
');
$retreatsWithSchedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Détermine la retraite à afficher
$selectedRetreatId = null;

if (isset($_GET['retreat_id']) && is_numeric($_GET['retreat_id'])) {
    $selectedRetreatId = (int)$_GET['retreat_id'];
} elseif (!empty($retreatsWithSchedules)) {
    // Par défaut, la première retraite (la plus récente)
    $selectedRetreatId = (int)$retreatsWithSchedules[0]['id'];
}

$retreat = null;
$schedules = [];

if ($selectedRetreatId) {
    $stmt = $pdo->prepare('SELECT * FROM retreats WHERE id = ?');
    $stmt->execute([$selectedRetreatId]);
    $retreat = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($retreat) {
        $stmt = $pdo->prepare('SELECT * FROM retreat_schedules WHERE retreat_id = ? ORDER BY ordre ASC, heure_debut ASC');
        $stmt->execute([$selectedRetreatId]);
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horaire des retraites - EDEN</title>
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
    <?php $base = ''; include __DIR__ . '/../includes/header.php'; ?>

    <!-- MAIN CONTENT -->
    <section class="section">
        <div class="container">
            <!-- SÉLECTEUR DE RETRAITE -->
            <?php if (count($retreatsWithSchedules) > 0): ?>
                <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 10px; font-size: 0.95em;">Sélectionner une retraite :</label>
                    <form method="get" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                        <select name="retreat_id" onchange="this.form.submit()" style="padding: 10px 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 1em; flex: 1; min-width: 250px;">
                            <?php foreach ($retreatsWithSchedules as $r): ?>
                                <option value="<?php echo (int)$r['id']; ?>" <?php echo ($selectedRetreatId === (int)$r['id'] ? 'selected' : ''); ?>>
                                    <?php echo htmlspecialchars($r['titre']); ?> 
                                    <?php if ($r['date_debut']): ?>
                                        (<?php echo date('d/m/Y', strtotime($r['date_debut'])); ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($retreat && !empty($schedules)): ?>
                <!-- INFOS RETRAITE -->
                <div style="background: linear-gradient(135deg, #f0f7f3 0%, #e8f4f0 100%); border-left: 5px solid #2c6e49; padding: 20px; border-radius: 6px; margin-bottom: 30px;">
                    <h2 style="margin: 0 0 10px 0; color: #1e4620;">📅 <?php echo htmlspecialchars($retreat['titre']); ?></h2>
                    <div style="display: flex; gap: 30px; flex-wrap: wrap; margin-top: 12px;">
                <form method="get" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                    <select name="retreat_id" onchange="this.form.submit()" style="padding: 10px 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 1em; flex: 1; min-width: 250px;">
                        <?php foreach ($retreatsWithSchedules as $r): ?>
                            <option value="<?php echo (int)$r['id']; ?>" <?php echo ($selectedRetreatId === (int)$r['id'] ? 'selected' : ''); ?>>
                                <?php echo htmlspecialchars($r['titre']); ?> 
                                <?php if ($r['date_debut']): ?>
                                    (<?php echo date('d/m/Y', strtotime($r['date_debut'])); ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($retreat && !empty($schedules)): ?>
            <!-- INFOS RETRAITE -->
            <div style="background: linear-gradient(135deg, #f0f7f3 0%, #e8f4f0 100%); border-left: 5px solid #2c6e49; padding: 20px; border-radius: 6px; margin-bottom: 30px;">
                <h2 style="margin: 0 0 10px 0; color: #1e4620;">📅 <?php echo htmlspecialchars($retreat['titre']); ?></h2>
                <div style="display: flex; gap: 30px; flex-wrap: wrap; margin-top: 12px;">
                    <div>
                        <small style="color: #666; font-weight: 600; display: block; margin-bottom: 4px;">DATES</small>
                        <strong style="color: #2c6e49; font-size: 1.05em;">
                            <?php echo date('d/m/Y', strtotime($retreat['date_debut'])); ?> 
                            <span style="color: #999;">→</span>
                            <?php echo date('d/m/Y', strtotime($retreat['date_fin'])); ?>
                        </strong>
                    </div>
                    <?php if (!empty($retreat['lieu'])): ?>
                        <div>
                            <small style="color: #666; font-weight: 600; display: block; margin-bottom: 4px;">LIEU</small>
                            <strong style="color: #2c6e49; font-size: 1.05em;">📍 <?php echo htmlspecialchars($retreat['lieu']); ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TABLEAU HORAIRE -->
            <div style="overflow-x: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <table style="width: 100%; border-collapse: collapse; background: white;">
                    <thead>
                        <tr style="background: #2c6e49; color: white;">
                            <th style="padding: 15px; text-align: left; font-weight: 600;">Jour</th>
                            <th style="padding: 15px; text-align: left; font-weight: 600;">Horaire</th>
                            <th style="padding: 15px; text-align: left; font-weight: 600;">Activité</th>
                            <th style="padding: 15px; text-align: left; font-weight: 600;">Responsable</th>
                            <th style="padding: 15px; text-align: left; font-weight: 600;">Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $index => $sch): ?>
                            <tr style="border-bottom: 1px solid #f0f0f0; <?php echo $index % 2 === 0 ? 'background: #fafafa;' : ''; ?>">
                                <td style="padding: 15px; font-weight: 600; color: #2c6e49; white-space: nowrap;"><?php echo htmlspecialchars($sch['jour']); ?></td>
                                <td style="padding: 15px; white-space: nowrap;">
                                    <span style="background: #e8f4f0; padding: 6px 12px; border-radius: 20px; font-size: 0.9em; color: #1e4620; font-weight: 500;">
                                        <?php echo date('H:i', strtotime($sch['heure_debut'])); ?> – <?php echo date('H:i', strtotime($sch['heure_fin'])); ?>
                                    </span>
                                </td>
                                <td style="padding: 15px; font-weight: 500; color: #333;"><?php echo htmlspecialchars($sch['activite']); ?></td>
                                <td style="padding: 15px; color: #666;">
                                    <?php echo !empty($sch['responsable']) ? htmlspecialchars($sch['responsable']) : '<span style="color: #ccc;">—</span>'; ?>
                                </td>
                                <td style="padding: 15px; color: #666; font-size: 0.95em;">
                                    <?php if (!empty($sch['description'])): ?>
                                        <span title="<?php echo htmlspecialchars($sch['description']); ?>">
                                            <?php echo htmlspecialchars(substr($sch['description'], 0, 60)); ?><?php echo strlen($sch['description']) > 60 ? '...' : ''; ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #ccc;">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- NOTES -->
            <div style="background: #fffef0; border: 1px solid #ffe6b6; padding: 15px; border-radius: 6px; margin-top: 30px; display: flex; gap: 15px; align-items: flex-start;">
                <span style="font-size: 1.5em; flex-shrink: 0;">📌</span>
                <div>
                    <p style="margin: 0 0 5px 0; color: #333;">
                        <strong>Important :</strong> Cet horaire est sujet à modification. 
                        Veuillez consulter régulièrement cette page pour les dernières mises à jour.
                    </p>
                    <p style="margin: 0; color: #666; font-size: 0.95em;">
                        Pour toute question, n'hésitez pas à nous contacter.
                    </p>
                </div>
            </div>

        <?php elseif ($retreat): ?>
            <!-- AUCUN HORAIRE -->
            <div style="text-align: center; padding: 60px 20px; background: #f9f9f9; border-radius: 8px;">
                <p style="color: #999; font-size: 1.1em; margin: 0;">
                    Aucun horaire n'a encore été publié pour cette retraite.
                </p>
            </div>
        <?php else: ?>
            <!-- AUCUNE RETRAITE -->
            <div style="text-align: center; padding: 60px 20px; background: #f9f9f9; border-radius: 8px;">
                <p style="color: #999; font-size: 1.1em; margin: 0;">
                    Aucun horaire disponible pour le moment.
                </p>
            </div>
        <?php endif; ?>

        <!-- LIEN VERS RETRAITES -->
        <div style="text-align: center; margin-top: 40px;">
            <a href="/eden/public/retraite.php" class="btn" style="background: #2c6e49; color: white;">
                ← Retour aux Retraites
            </a>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
