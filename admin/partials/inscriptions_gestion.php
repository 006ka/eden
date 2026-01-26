<?php
/**
 * Gestion des inscriptions avec export PDF
 */

// Récupérer les retreats et programmes pour le filtre
$retreatsForFilter = $pdo->query('SELECT id, titre, date_debut FROM retreats ORDER BY COALESCE(date_debut, date_fin) DESC')->fetchAll(PDO::FETCH_ASSOC);

// Filtre: récupérer les inscrits pour une activité sélectionnée
$selectedEventType = isset($_GET['filter_event_type']) ? trim($_GET['filter_event_type']) : '';
$selectedEventId = isset($_GET['filter_event_id']) && is_numeric($_GET['filter_event_id']) ? (int)$_GET['filter_event_id'] : null;

$filteredInscriptions = [];
$selectedActivityLabel = '';

if ($selectedEventType !== '' && $selectedEventId !== null) {
    $stmt = $pdo->prepare('SELECT * FROM inscriptions WHERE event_type = ? AND event_id = ? ORDER BY created_at DESC');
    $stmt->execute([$selectedEventType, $selectedEventId]);
    $filteredInscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupère le label de l'activité
    if ($selectedEventType === 'retraite') {
        $stmt = $pdo->prepare('SELECT titre FROM retreats WHERE id = ?');
        $stmt->execute([$selectedEventId]);
        $activity = $stmt->fetch(PDO::FETCH_ASSOC);
        $selectedActivityLabel = $activity ? $activity['titre'] : '';
    }
}

// Gestion de l'export CSV (plus robuste que le PDF maison)
if (isset($_GET['export_csv']) && $selectedEventType !== '' && $selectedEventId !== null && !empty($filteredInscriptions)) {
    generateSimpleCSV($filteredInscriptions, $selectedActivityLabel, $selectedEventType, $selectedEventId);
    exit;
}

function generateSimpleCSV($inscriptions, $activityLabel, $eventType, $eventId) {
    $filename = 'inscriptions_' . sanitizeFilename($activityLabel) . '_' . date('Y-m-d') . '.csv';

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    // BOM UTF-8 pour une meilleure compatibilité Excel
    echo "\xEF\xBB\xBF";

    $out = fopen('php://output', 'w');

    // En-tête d'information
    fputcsv($out, ['EDEN - Liste des inscrits']);
    fputcsv($out, ['Activité', $activityLabel]);
    fputcsv($out, ['Type', $eventType]);
    fputcsv($out, ["Date d'export", date('d/m/Y H:i:s')]);
    fputcsv($out, ['Total inscrits', count($inscriptions)]);
    fputcsv($out, []);

    // En-têtes de colonnes
    fputcsv($out, ['N°','Nom','Email','Téléphone','Âge','Église','Adresse','Présence','Besoins']);

    $num = 1;
    foreach ($inscriptions as $insc) {
        fputcsv($out, [
            $num++,
            $insc['nom'],
            $insc['email'],
            $insc['telephone'],
            $insc['age'],
            $insc['eglise'] ?? '',
            $insc['adresse'] ?? '',
            $insc['temps_sejour'] ?? '',
            $insc['besoins'] ?? '',
        ]);
    }

    fclose($out);
}

function sanitizeFilename($str) {
    return preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(' ', '_', $str));
}
?>

<div class="admin-section" id="inscriptions-gestion">
    <h2 style="display:flex; justify-content:space-between; align-items:center;">
        <span>Gestion des Inscriptions</span>
    </h2>

    <!-- FILTRE PAR ACTIVITÉ -->
    <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="margin-top: 0; margin-bottom: 15px; color: var(--color-primary);"> Sélectionner une activité pour voir les inscrits</h3>
        <h3 style="margin-top: 0; margin-bottom: 15px; color: var(--color-primary);">📊 Sélectionner une activité pour voir les inscrits</h3>
        <form method="get" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
            <input type="hidden" name="section" value="inscriptions_gestion">
            
            <input type="hidden" name="filter_event_type" value="retraite">

            <div style="flex: 1; min-width: 200px;">
                <label style="display: block; font-weight: 600; margin-bottom: 6px;">Activité</label>
                <select name="filter_event_id" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                    <option value="">-- Sélectionner --</option>
                    <?php if ($selectedEventType === 'retraite'): ?>
                        <?php foreach ($retreatsForFilter as $r): ?>
                            <option value="<?php echo (int)$r['id']; ?>" <?php echo ($selectedEventId === (int)$r['id'] ? 'selected' : ''); ?>>
                                <?php echo htmlspecialchars($r['titre']); ?> 
                                <?php if ($r['date_debut']): ?>(<?php echo date('d/m/Y', strtotime($r['date_debut'])); ?>)<?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <button type="submit" class="btn primary">Afficher les inscrits</button>
        </form>
    </div>

    <?php if ($selectedEventType !== '' && $selectedEventId !== null): ?>
        <div style="background: rgba(29, 58, 95, 0.1); border: 2px solid var(--color-primary); padding: 15px; border-radius: 6px; margin-bottom: 20px;">
            <h3 style="margin: 0 0 10px 0; color: var(--color-primary);">
                📋 <?php echo htmlspecialchars($selectedActivityLabel); ?>
                <span class="badge" style="background: var(--color-primary); color: white; font-size: 0.9em; padding: 4px 12px; border-radius: 12px; margin-left: 10px;">
                    <?php echo count($filteredInscriptions); ?> inscrit(s)
                </span>
            </h3>
            
            <?php if (!empty($filteredInscriptions)): ?>
                <div style="margin-top: 15px; display:flex; gap:10px; flex-wrap:wrap;">
                    <a href="?section=inscriptions_gestion&amp;filter_event_type=<?php echo htmlspecialchars($selectedEventType); ?>&amp;filter_event_id=<?php echo (int)$selectedEventId; ?>&amp;export_csv=1" class="btn" style="background: var(--color-accent); color: white;">
                        📥 Télécharger en CSV
                    </a>
                    <button type="button" onclick="printInscriptions()" class="btn btn-secondary" style="border:1px solid var(--color-primary); color: var(--color-primary); background:#fff;">
                        🖨 Imprimer / PDF
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <?php if (empty($filteredInscriptions)): ?>
            <p style="color: #999; text-align: center; padding: 40px;">Aucune inscription pour cette activité.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Âge</th>
                            <th>Église</th>
                            <th>Adresse</th>
                            <th>Présence</th>
                            <th>Besoin particulier</th>
                            <th>Date inscription</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $num = 1; foreach ($filteredInscriptions as $insc): ?>
                            <tr>
                                <td><?php echo $num++; ?></td>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($insc['nom']); ?></td>
                                <td><a href="mailto:<?php echo htmlspecialchars($insc['email']); ?>" style="color: var(--color-primary); text-decoration: none;"><?php echo htmlspecialchars($insc['email']); ?></a></td>
                                <td><?php echo htmlspecialchars($insc['telephone']); ?></td>
                                <td style="text-align: center;"><?php echo (int)$insc['age']; ?></td>
                                <td><?php echo htmlspecialchars($insc['eglise'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($insc['adresse'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($insc['temps_sejour'] ?? '-'); ?></td>
                                <td>
                                    <?php if (!empty($insc['besoins'])): ?>
                                        <button type="button" onclick="showBesoin(<?php echo json_encode($insc['besoins']); ?>)" style="background: var(--color-primary); color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 0.8em;">Voir</button>
                                    <?php else: ?>
                                        <span style="color: #999;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($insc['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 60px 20px; color: #999;">
            <p style="font-size: 1.1em; margin-bottom: 10px;">Sélectionnez une activité pour voir les inscrits</p>
            <p>Choisissez le type d'activité et l'activité spécifique dans les filtres ci-dessus.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Modal pour afficher les besoins particuliers -->
<div id="modal-besoins" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; padding:16px;">
    <div style="background:white; border-radius:10px; max-width:600px; width:100%; padding:20px 22px; box-shadow:0 18px 40px rgba(0,0,0,0.25); position:relative;">
        <button type="button" onclick="closeBesoinModal()" style="position:absolute; top:10px; right:10px; background:transparent; border:none; font-size:20px; cursor:pointer; color:#999; width:28px; height:28px; display:flex; align-items:center; justify-content:center;">✕</button>
        <h3 style="margin:0 0 10px; font-size:1.1rem; color:var(--color-primary);">Besoins particuliers</h3>
        <p id="modal-besoins-texte" style="margin:0; white-space:pre-wrap; font-size:0.95rem; color:var(--color-text-light);"></p>
    </div>
</div>

<script>
    function updateEventSelect() {
        var form = document.querySelector('form');
        form.submit();
    }
    
    function showBesoin(texte) {
        var modal = document.getElementById('modal-besoins');
        var contenu = document.getElementById('modal-besoins-texte');
        contenu.textContent = texte || '';
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function printInscriptions() {
        var table = document.querySelector('#inscriptions-gestion table');
        if (!table) return;
        var win = window.open('', '_blank');
        if (!win) return;
        var title = document.querySelector('#inscriptions-gestion h3');
        var html = '<html><head><meta charset="UTF-8"><title>Inscriptions</title>' +
                   '<style>body{font-family:sans-serif;font-size:12px;padding:20px;} table{width:100%;border-collapse:collapse;} th,td{border:1px solid #ccc;padding:4px 6px;font-size:11px;} th{background:#f2f2f2;}</style>' +
                   '</head><body>';
        if (title) {
            html += '<h2>' + title.textContent + '</h2>';
        }
        html += table.outerHTML + '</body></html>';
        win.document.open();
        win.document.write(html);
        win.document.close();
        win.focus();
        win.print();
    }

    function closeBesoinModal() {
        var modal = document.getElementById('modal-besoins');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Fermer la modale en cliquant sur le fond
    (function(){
        var modal = document.getElementById('modal-besoins');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeBesoinModal();
                }
            });
        }
    })();
</script>
