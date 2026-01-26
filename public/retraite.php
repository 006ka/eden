<?php
require_once __DIR__ . '/../config/db.php';

// Années disponibles pour filtre
$yearsStmt = $pdo->query("SELECT DISTINCT YEAR(COALESCE(date_debut,date_fin)) AS y FROM retreats WHERE date_debut IS NOT NULL OR date_fin IS NOT NULL ORDER BY y DESC");
$yearRows = $yearsStmt->fetchAll(PDO::FETCH_ASSOC);
$availableYears = [];
foreach ($yearRows as $ry) { if (!empty($ry['y'])) $availableYears[] = (int)$ry['y']; }

// Filtrage par année (GET 'year')
$filterYear = isset($_GET['year']) && is_numeric($_GET['year']) ? (int)$_GET['year'] : null;
if ($filterYear) {
    $stmt = $pdo->prepare('SELECT * FROM retreats WHERE (date_debut IS NOT NULL OR date_fin IS NOT NULL) AND (YEAR(date_debut) = ? OR YEAR(date_fin) = ?) ORDER BY date_debut IS NULL, date_debut ASC, id ASC');
    $stmt->execute([$filterYear, $filterYear]);
    $allRetreats = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Récupère toutes les retraites
    $allRetreats = $pdo->query('SELECT * FROM retreats ORDER BY date_debut IS NULL, date_debut ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
}

// Séparation à venir / passées
$today = new DateTime('today');
$upcoming = [];
$past = [];

foreach ($allRetreats as $r) {
    $start = !empty($r['date_debut']) ? new DateTime($r['date_debut']) : null;
    $end   = !empty($r['date_fin'])   ? new DateTime($r['date_fin'])   : $start;

    if ($start === null && $end === null) {
        $upcoming[] = $r;
        continue;
    }

    if ($end !== null && $end < $today) {
        $past[] = $r;
    } else {
        $upcoming[] = $r;
    }
}

// Tri : à venir (prochaine d abord)
usort($upcoming, function($a, $b) {
    $ad = $a['date_debut'] ?: $a['date_fin'] ?: '9999-12-31';
    $bd = $b['date_debut'] ?: $b['date_fin'] ?: '9999-12-31';
    return strcmp($ad, $bd);
});

// Tri : passées (plus récentes d abord)
usort($past, function($a, $b) {
    $ad = $a['date_fin'] ?: $a['date_debut'] ?: '0000-01-01';
    $bd = $b['date_fin'] ?: $b['date_debut'] ?: '0000-01-01';
    return strcmp($bd, $ad);
});

// Détermine la retraite mise en avant
$highlightRetreat = null;

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    foreach ($allRetreats as $r) {
        if ((int)$r['id'] === $id) {
            $highlightRetreat = $r;
            break;
        }
    }
}

if (!$highlightRetreat) {
    if (!empty($upcoming)) {
        $highlightRetreat = $upcoming[0];
    } elseif (!empty($past)) {
        $highlightRetreat = $past[0];
    }
}

// Valeurs par défaut si aucune retraite n est en base
$retTitre = $highlightRetreat ? $highlightRetreat['titre'] : 'Retraite Spirituelle';
$retTheme = $highlightRetreat && !empty($highlightRetreat['theme']) ? $highlightRetreat['theme'] : 'Rencontre et Transformation';
$retDates = '';
if ($highlightRetreat && !empty($highlightRetreat['date_debut'])) {
    $retDates = $highlightRetreat['date_debut'];
    if (!empty($highlightRetreat['date_fin'])) {
        $retDates .= ' - ' . $highlightRetreat['date_fin'];
    }
}
$retLieu = $highlightRetreat && !empty($highlightRetreat['lieu']) ? $highlightRetreat['lieu'] : 'Centre Spirituel Mont-Eden';
$retOrateurs = $highlightRetreat && !empty($highlightRetreat['orateurs']) ? $highlightRetreat['orateurs'] : 'Pasteur John, Soeur Marie';
$retPrix = $highlightRetreat && !empty($highlightRetreat['prix']) ? $highlightRetreat['prix'] : '';
$retFicheUrl = $highlightRetreat && !empty($highlightRetreat['fiche_url']) ? $highlightRetreat['fiche_url'] : '';
$programmeImage = $highlightRetreat && !empty($highlightRetreat['programme_image_url']) ? $highlightRetreat['programme_image_url'] : '';
$highlightId = $highlightRetreat ? (int)$highlightRetreat['id'] : null;

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retraites - EDEN</title>
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
    <?php 
        $base = '';
        include __DIR__ . '/../includes/header.php'; 
    ?>

    <!-- EVENTS BAR - Autres Retraites -->
    <?php if (!empty($upcoming) || !empty($past)): ?>
    <section class="events-bar">
        <div class="container">
            <div class="event-grid">
                <?php 
                    $otherRetreats = array_slice($upcoming, 1, 3);
                    if (count($otherRetreats) < 3 && !empty($past)) {
                        $otherRetreats = array_merge($otherRetreats, array_slice($past, 0, 3 - count($otherRetreats)));
                    }
                    foreach ($otherRetreats as $idx => $r): 
                ?>
                    <div class="event-item<?php echo ($idx === count($otherRetreats) - 1) ? ' last-event' : ''; ?>">
                        <div class="date"><?php echo !empty($r['date_debut']) ? date('d M Y', strtotime($r['date_debut'])) : 'À venir'; ?></div>
                        <div class="title"><?php echo htmlspecialchars(substr($r['titre'], 0, 30)); ?></div>
                        <?php if (!empty($r['lieu'])): ?>
                            <div style="font-size: 0.8em; opacity: 0.9;">📍 <?php echo htmlspecialchars(substr($r['lieu'], 0, 25)); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <div style="padding: 0 20px;">
                    <a href="#retreats-list" class="more-events-btn">Voir plus ↓</a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- MAIN CONTENT - Détails de la Retraite -->
    <section id="details" class="contact-map-section">
        <div class="container">
            <h2 style="font-size: 2em; font-weight: 700; margin-bottom: 30px;">Informations de cette retraite</h2>
            <div class="contact-grid">
                <div class="contact-info">
                    <h3 style="font-weight: 700; margin-bottom: 15px;">Détails pratiques</h3>
                    <?php if ($retDates !== ''): ?>
                        <div style="margin-bottom: 12px;">
                            <strong>📅 Dates :</strong><br>
                            <?php echo htmlspecialchars($retDates); ?>
                        </div>
                    <?php endif; ?>
                    <div style="margin-bottom: 12px;">
                        <strong>📍 Lieu :</strong><br>
                        <?php echo htmlspecialchars($retLieu); ?>
                    </div>
                    <div style="margin-bottom: 12px;">
                        <strong>⛪ Intervenants :</strong><br>
                        <?php echo htmlspecialchars($retOrateurs); ?>
                    </div>
                    <?php if ($retPrix !== ''): ?>
                        <div style="margin-bottom: 12px;">
                            <strong>💳 Participation :</strong><br>
                            <?php echo htmlspecialchars($retPrix); ?>
                        </div>
                    <?php endif; ?>
                    <div class="separator">· · ·</div>
                    <div style="margin-top: 15px;">
                        <strong>🎯 Au programme :</strong><br>
                        Louange, Enseignements, Ateliers, Prière, Délivrance
                    </div>
                    <div style="margin-top: 15px; display:flex; flex-wrap:wrap; gap:10px;">
                        <?php if ($highlightId !== null): ?>
                            <a href="inscription.php?type=retraite&amp;id=<?php echo (int)$highlightId; ?>&amp;label=<?php echo urlencode($retTitre); ?>" class="btn btn-primary" style="text-decoration:none;">
                                🙋 Je m'inscris
                            </a>
                        <?php endif; ?>
                        <a href="horaire.php<?php echo ($highlightId !== null) ? '?retreat_id=' . (int)$highlightId : ''; ?>" class="btn btn-secondary" style="text-decoration: none;">
                            📋 Voir l'horaire
                        </a>
                        <?php if ($retFicheUrl !== ''): ?>
                            <a href="<?php echo htmlspecialchars($retFicheUrl); ?>" class="btn btn-outline" style="text-decoration: none;" download>
                                📄 Télécharger la fiche pratique
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <h3 style="font-weight: 700; margin-bottom: 15px;">Programme détaillé</h3>
                    <?php if ($programmeImage !== ''): 
                        $imagePath = $programmeImage;
                        // Si le chemin commence par 'uploads/', on ajoute un / au début
                        if (strpos($imagePath, 'uploads/') === 0) {
                            $imagePath = '/' . $imagePath;
                        }
                        // Si le chemin ne commence pas par /, on ajoute /uploads/ devant
                        elseif (strpos($imagePath, '/') !== 0) {
                            $imagePath = '/uploads/' . $imagePath;
                        }
                        // Vérification si le fichier existe
                        $fullPath = $_SERVER['DOCUMENT_ROOT'] . $imagePath;
                        if (!file_exists($fullPath)) {
                            // Essayer avec un chemin relatif si le chemin absolu ne fonctionne pas
                            $altPath = __DIR__ . '/..' . $imagePath;
                            if (file_exists($altPath)) {
                                $imagePath = '..' . $imagePath;
                            }
                        }
                    ?>
                        <div style="background: var(--light-bg); padding: 20px; border-radius: var(--radius); text-align: center;">
                            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Programme de la retraite" style="max-width: 100%; height: auto; border-radius: var(--radius);" loading="lazy" decoding="async">
                        </div>
                    <?php else: ?>
                        <div style="background: var(--light-bg); padding: 30px; border-radius: var(--radius); text-align: center; color: var(--muted);">
                            <p>Programme détaillé à venir</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- SECONDARY BLOCKS - Conseils & Infos -->
    <section class="secondary-blocks">
        <div class="container">
            <h2 style="font-size: 1.6em; font-weight: 700; margin-bottom: 20px; color: var(--dark-text);">Conseils pour votre retraite</h2>
            <div class="block-grid">
                <div class="block" style="background-color: var(--color-primary);">
                    <h3 style="color: white;">📚 À apporter</h3>
                    <p style="font-size: 0.95em; color: white;">Bible, carnet et stylo</p>
                </div>
                <div class="block" style="background-color: #3d7f5a;">
                    <h3 style="color: white;">🛏️ Logement</h3>
                    <p style="font-size: 0.95em; color: white;">Couverture / draps personnels</p>
                </div>
                <div class="block" style="background-color: #4d8f6a;">
                    <h3 style="color: white;">💧 Hydratation</h3>
                    <p style="font-size: 0.95em; color: white;">Bouteille d'eau réutilisable</p>
                </div>
                <div class="block" style="background-color: #5d9f7a;">
                    <h3 style="color: white;">⏰ Respect</h3>
                    <p style="font-size: 0.95em; color: white;">Horaires et recueillement</p>
                </div>
            </div>
        </div>
    </section>

    <!-- YEAR FILTER BAR -->
    <section style="padding: 30px 20px; background: var(--bg);">
        <div class="container">
            <h2 style="font-size: 1.4em; font-weight: 700; margin-bottom: 15px;">Parcourir les retraites</h2>
            <form method="get" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <label style="font-weight: 600; color: var(--muted);">Filtrer par année :</label>
                <select name="year" onchange="this.form.submit()" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95em; cursor: pointer;">
                    <option value="">Toutes les années</option>
                    <?php foreach ($availableYears as $y): ?>
                        <option value="<?php echo (int)$y; ?>" <?php echo ($filterYear && (int)$filterYear === (int)$y) ? 'selected' : ''; ?>><?php echo (int)$y; ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($filterYear): ?>
                    <a href="retraite.php" style="color: var(--color-primary); font-weight: 600; text-decoration: none; padding: 8px 12px; border-radius: 6px; background: rgba(29, 58, 95, 0.1);">✕ Réinitialiser</a>
                <?php endif; ?>
            </form>
        </div>
    </section>

    <!-- RETREATS LIST -->
    <section id="retreats-list" class="contact-map-section">
        <div class="container">
            <!-- Upcoming Retreats -->
            <h2 style="font-size: 1.8em; font-weight: 700; margin-bottom: 20px; margin-top: 20px;">Retraites à venir</h2>
            <div class="block-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <?php if (empty($upcoming)): ?>
                    <p style="grid-column: 1 / -1; color: var(--muted);">Aucune retraite à venir pour le moment.</p>
                <?php else: ?>
                    <?php foreach ($upcoming as $r): ?>
                        <?php $isHighlight = ($highlightId !== null && (int)$r['id'] === $highlightId); ?>
                        <a href="retraite.php?id=<?php echo (int)$r['id']; ?>" style="text-decoration: none; color: inherit; display: flex; flex-direction: column; padding: 20px; background: white; border-radius: var(--radius); box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: transform 0.2s, box-shadow 0.2s; border: 2px solid <?php echo $isHighlight ? 'var(--color-primary)' : 'transparent'; ?>;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.12)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)';">
                            <?php if (!empty($r['programme_image_url'])): 
                                $imagePath = $r['programme_image_url'];
                                // Si le chemin commence par 'uploads/', on ajoute un / au début
                                if (strpos($imagePath, 'uploads/') === 0) {
                                    $imagePath = '/' . $imagePath;
                                }
                                // Si le chemin ne commence pas par /, on ajoute /uploads/ devant
                                elseif (strpos($imagePath, '/') !== 0) {
                                    $imagePath = '/uploads/' . $imagePath;
                                }
                                // Vérification si le fichier existe
                                $fullPath = $_SERVER['DOCUMENT_ROOT'] . $imagePath;
                                if (!file_exists($fullPath)) {
                                    // Essayer avec un chemin relatif si le chemin absolu ne fonctionne pas
                                    $altPath = __DIR__ . '/..' . $imagePath;
                                    if (file_exists($altPath)) {
                                        $imagePath = '..' . $imagePath;
                                    }
                                }
                            ?>
                                <div style="width: 100%; height: 180px; background: var(--light-bg); border-radius: 6px; overflow: hidden; margin-bottom: 12px;">
                                    <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Programme" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy" decoding="async">
                                </div>
                            <?php endif; ?>
                            <h3 style="font-weight: 700; font-size: 1.1em; margin-bottom: 8px;"><?php echo htmlspecialchars($r['titre']); ?></h3>
                            <?php if (!empty($r['theme'])): ?>
                                <p style="font-size: 0.85em; color: var(--muted); margin-bottom: 8px;">🔥 <?php echo htmlspecialchars($r['theme']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($r['date_debut'])): ?>
                                <p style="font-size: 0.85em; color: var(--muted); margin-bottom: 6px;">📅 <?php echo date('d M Y', strtotime($r['date_debut'])); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($r['lieu'])): ?>
                                <p style="font-size: 0.85em; color: var(--muted); margin-bottom: 4px;">📍 <?php echo htmlspecialchars($r['lieu']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($r['prix'])): ?>
                                <p style="font-size: 0.85em; color: var(--muted); margin-bottom: 6px;">💳 <?php echo htmlspecialchars($r['prix']); ?></p>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Past Retreats -->
            <?php if (!empty($past)): ?>
                <h2 style="font-size: 1.8em; font-weight: 700; margin-bottom: 20px; margin-top: 40px; opacity: 0.7;">Retraites passées</h2>
                <div class="block-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; opacity: 0.8;">
                    <?php foreach ($past as $r): ?>
                        <a href="retraite.php?id=<?php echo (int)$r['id']; ?>" style="text-decoration: none; color: inherit; display: flex; flex-direction: column; padding: 20px; background: white; border-radius: var(--radius); box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.12)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)';">
                            <?php if (!empty($r['programme_image_url'])): 
                                $imagePath = $r['programme_image_url'];
                                // Si le chemin commence par 'uploads/', on ajoute un / au début
                                if (strpos($imagePath, 'uploads/') === 0) {
                                    $imagePath = '/' . $imagePath;
                                }
                                // Si le chemin ne commence pas par /, on ajoute /uploads/ devant
                                elseif (strpos($imagePath, '/') !== 0) {
                                    $imagePath = '/uploads/' . $imagePath;
                                }
                                // Vérification si le fichier existe
                                $fullPath = $_SERVER['DOCUMENT_ROOT'] . $imagePath;
                                if (!file_exists($fullPath)) {
                                    // Essayer avec un chemin relatif si le chemin absolu ne fonctionne pas
                                    $altPath = __DIR__ . '/..' . $imagePath;
                                    if (file_exists($altPath)) {
                                        $imagePath = '..' . $imagePath;
                                    }
                                }
                            ?>
                                <div style="width: 100%; height: 180px; background: var(--light-bg); border-radius: 6px; overflow: hidden; margin-bottom: 12px;">
                                    <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Programme" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy" decoding="async">
                                </div>
                            <?php endif; ?>
                            <h3 style="font-weight: 700; font-size: 1.1em; margin-bottom: 8px;"><?php echo htmlspecialchars($r['titre']); ?></h3>
                            <?php if (!empty($r['theme'])): ?>
                                <p style="font-size: 0.85em; color: var(--muted); margin-bottom: 8px;">🔥 <?php echo htmlspecialchars($r['theme']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($r['date_debut'])): ?>
                                <p style="font-size: 0.85em; color: var(--muted); margin-bottom: 6px;">📅 <?php echo date('d M Y', strtotime($r['date_debut'])); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($r['lieu'])): ?>
                                <p style="font-size: 0.85em; color: var(--muted); margin-bottom: 4px;">📍 <?php echo htmlspecialchars($r['lieu']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($r['prix'])): ?>
                                <p style="font-size: 0.85em; color: var(--muted);">💳 <?php echo htmlspecialchars($r['prix']); ?></p>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
