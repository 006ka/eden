<?php
require_once __DIR__ . '/../config/db.php';

// années disponibles
$py = $pdo->query("SELECT DISTINCT YEAR(COALESCE(date_debut,date_fin)) AS y FROM programmes WHERE date_debut IS NOT NULL OR date_fin IS NOT NULL ORDER BY y DESC")->fetchAll(PDO::FETCH_ASSOC);
$availableYears = [];
foreach ($py as $r) { if (!empty($r['y'])) $availableYears[] = (int)$r['y']; }

$filterYear = isset($_GET['year']) && is_numeric($_GET['year']) ? (int)$_GET['year'] : null;
if ($filterYear) {
    $stmt = $pdo->prepare('SELECT * FROM programmes WHERE (date_debut IS NOT NULL OR date_fin IS NOT NULL) AND (YEAR(date_debut) = ? OR YEAR(date_fin) = ?) ORDER BY ordre ASC, id ASC');
    $stmt->execute([$filterYear, $filterYear]);
    $programmes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $programmes = $pdo->query('SELECT * FROM programmes ORDER BY ordre ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
}

// On sépare les programmes en "à venir" et "passés" en fonction des dates
$today = new DateTime('today');
$upcoming = [];
$past = [];

foreach ($programmes as $p) {
    $start = !empty($p['date_debut']) ? new DateTime($p['date_debut']) : null;
    $end   = !empty($p['date_fin'])   ? new DateTime($p['date_fin'])   : $start;

    // Si aucune date n'est renseignée, on le considère comme à venir
    if ($start === null && $end === null) {
        $upcoming[] = $p;
        continue;
    }

    // Programme passé si date de fin (ou de début) strictement avant aujourd'hui
    if ($end !== null && $end < $today) {
        $past[] = $p;
    } else {
        $upcoming[] = $p;
    }
}

// Tri des listes
usort($upcoming, function($a, $b) {
    $ad = $a['date_debut'] ?: $a['date_fin'] ?: '9999-12-31';
    $bd = $b['date_debut'] ?: $b['date_fin'] ?: '9999-12-31';
    return strcmp($ad, $bd);
});

usort($past, function($a, $b) {
    $ad = $a['date_fin'] ?: $a['date_debut'] ?: '0000-01-01';
    $bd = $b['date_fin'] ?: $b['date_debut'] ?: '0000-01-01';
    return strcmp($bd, $ad); // plus récents d'abord
});

// Le prochain programme est le premier à venir (s il existe)
$highlightId = isset($upcoming[0]) ? $upcoming[0]['id'] : null;
$highlightProgram = isset($upcoming[0]) ? $upcoming[0] : (isset($past[0]) ? $past[0] : null);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier des programmes - EDEN</title>
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
    <?php 
        $base = '';
        include __DIR__ . '/../includes/header.php'; 
    ?>

    <!-- EVENTS BAR - Featured Programme -->
    <?php if ($highlightProgram): ?>
    <section class="events-bar">
        <div class="container">
            <div class="event-grid">
                <?php 
                    $otherProgrammes = array_slice($upcoming, 1, 3);
                    if (count($otherProgrammes) < 3 && !empty($past)) {
                        $otherProgrammes = array_merge($otherProgrammes, array_slice($past, 0, 3 - count($otherProgrammes)));
                    }
                    foreach ($otherProgrammes as $idx => $p): 
                ?>
                    <div class="event-item<?php echo ($idx === count($otherProgrammes) - 1) ? ' last-event' : ''; ?>">
                        <div class="date"><?php echo !empty($p['date_debut']) ? date('d M Y', strtotime($p['date_debut'])) : 'À venir'; ?></div>
                        <div class="title"><?php echo htmlspecialchars(substr($p['titre'], 0, 30)); ?></div>
                        <?php if (!empty($p['description'])): ?>
                            <div style="font-size: 0.8em; opacity: 0.9;"><?php echo htmlspecialchars(substr($p['description'], 0, 30)); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <div style="padding: 0 20px;">
                    <a href="#programmes-list" class="more-events-btn">Tous les événements ↓</a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- YEAR FILTER SECTION -->
    <section style="padding: 30px 20px; background: var(--light-bg);">
        <div class="container">
            <h2 style="font-size: 1.4em; font-weight: 700; margin-bottom: 15px;">Filtrer par année</h2>
            <form method="get" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <select name="year" onchange="this.form.submit()" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95em; cursor: pointer;">
                    <option value="">Toutes les années</option>
                    <?php foreach ($availableYears as $y): ?>
                        <option value="<?php echo (int)$y; ?>" <?php echo ($filterYear && (int)$filterYear === (int)$y) ? 'selected' : ''; ?>><?php echo (int)$y; ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($filterYear): ?>
                    <a href="programmes.php" style="color: var(--color-primary); font-weight: 600; text-decoration: none; padding: 8px 12px; border-radius: 6px; background: rgba(29, 58, 95, 0.1);">✕ Réinitialiser</a>
                <?php endif; ?>
            </form>
        </div>
    </section>

    <!-- SECONDARY BLOCKS - Programme Info -->
    <section class="secondary-blocks">
        <div class="container">
            <h2 style="font-size: 1.6em; font-weight: 700; margin-bottom: 20px; color: var(--dark-text);">Notre approche</h2>
            <div class="block-grid">
                <div class="block" style="background-color: var(--color-primary);">
                    <h3>🙏 Spirituel</h3>
                    <p style="font-size: 0.95em;">Croissance spirituelle</p>
                </div>
                <div class="block" style="background-color: var(--color-primary-dark);">
                    <h3>👥 Communautaire</h3>
                    <p style="font-size: 0.95em;">Liens fraternels</p>
                </div>
                <div class="block" style="background-color: var(--color-primary-light);">
                    <h3>📚 Éducatif</h3>
                    <p style="font-size: 0.95em;">Enseignements riches</p>
                </div>
                <div class="block" style="background-color: var(--color-secondary);">
                    <h3>🌟 Transformant</h3>
                    <p style="font-size: 0.95em;">Changement de vie</p>
                </div>
            </div>
        </div>
    </section>

    <!-- PROGRAMMES LIST -->
    <section id="programmes-list" class="contact-map-section">
        <div class="container">
            <!-- Upcoming Programmes -->
            <h2 style="font-size: 1.8em; font-weight: 700; margin-bottom: 20px; margin-top: 20px;">Programmes à venir</h2>
            <div class="block-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                <?php if (empty($upcoming)): ?>
                    <p style="grid-column: 1 / -1; color: var(--muted);">Aucun programme à venir pour le moment.</p>
                <?php else: ?>
                    <?php foreach ($upcoming as $p): ?>
                        <?php $isHighlight = ($highlightId !== null && $p['id'] == $highlightId); ?>
                        <div style="cursor: pointer; text-decoration: none; color: inherit; display: flex; flex-direction: column; padding: 20px; background: white; border-radius: var(--radius); box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: transform 0.2s, box-shadow 0.2s; border: 2px solid <?php echo $isHighlight ? '#2c6e49' : 'transparent'; ?>;" onclick="openProgrammeModal(<?php echo htmlspecialchars(json_encode($p)); ?>)" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.12)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)';">
                            <?php if (!empty($p['affiche_url'])): ?>
                                <div style="width: 100%; height: 150px; margin-bottom: 12px; overflow: hidden; border-radius: 6px; background: #f3f3f3; display:flex; align-items:center; justify-content:center;">
                                    <img src="<?php echo htmlspecialchars($p['affiche_url']); ?>" alt="Affiche du programme" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy" decoding="async">
                                </div>
                            <?php endif; ?>
                            <h3 style="font-weight: 700; font-size: 1.15em; margin-bottom: 12px; color: var(--color-primary);"><?php echo htmlspecialchars($p['titre']); ?></h3>
                            <?php if (!empty($p['date_debut']) || !empty($p['date_fin'])): ?>
                                <p style="font-size: 0.9em; color: var(--muted); margin-bottom: 8px; font-weight: 600;">
                                    📅 
                                    <?php 
                                        if (!empty($p['date_debut']) && !empty($p['date_fin'])) {
                                            echo date('d M Y', strtotime($p['date_debut'])) . ' - ' . date('d M Y', strtotime($p['date_fin']));
                                        } elseif (!empty($p['date_debut'])) {
                                            echo date('d M Y', strtotime($p['date_debut']));
                                        } else {
                                            echo 'Date à confirmer';
                                        }
                                    ?>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($p['description'])): ?>
                                <p style="font-size: 0.9em; color: #555; margin-bottom: 12px; line-height: 1.5;">
                                    <?php echo htmlspecialchars(substr($p['description'], 0, 120)) . '...'; ?>
                                </p>
                            <?php endif; ?>
                            <div style="margin-top: auto; display: flex; gap: 8px; align-items:center; justify-content: space-between; flex-wrap:wrap;">
                                <span style="display: inline-block; padding: 4px 12px; background: rgba(29, 58, 95, 0.15); color: var(--color-primary); border-radius: 4px; font-size: 0.8em; font-weight: 600;">Événement</span>
                                <a href="inscription.php?type=programme&amp;id=<?php echo (int)$p['id']; ?>&amp;label=<?php echo urlencode($p['titre']); ?>" class="btn btn-secondary" style="padding: 6px 14px; font-size: 0.85em; text-decoration:none;">
                                    Je m'inscris
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Past Programmes -->
            <?php if (!empty($past)): ?>
                <h2 style="font-size: 1.8em; font-weight: 700; margin-bottom: 20px; margin-top: 40px; opacity: 0.7;">Programmes passés</h2>
                <div class="block-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; opacity: 0.8;">
                    <?php foreach ($past as $p): ?>
                        <div style="cursor: pointer; text-decoration: none; color: inherit; display: flex; flex-direction: column; padding: 20px; background: white; border-radius: var(--radius); box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: transform 0.2s, box-shadow 0.2s;" onclick="openProgrammeModal(<?php echo htmlspecialchars(json_encode($p)); ?>)" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.12)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)';">
                            <?php if (!empty($p['affiche_url'])): ?>
                                <div style="width: 100%; height: 150px; margin-bottom: 12px; overflow: hidden; border-radius: 6px; background: #f3f3f3; display:flex; align-items:center; justify-content:center;">
                                    <img src="<?php echo htmlspecialchars($p['affiche_url']); ?>" alt="Affiche du programme" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                            <?php endif; ?>
                            <h3 style="font-weight: 700; font-size: 1.15em; margin-bottom: 12px; color: #666;"><?php echo htmlspecialchars($p['titre']); ?></h3>
                            <?php if (!empty($p['date_debut']) || !empty($p['date_fin'])): ?>
                                <p style="font-size: 0.9em; color: var(--muted); margin-bottom: 8px; font-weight: 600;">
                                    📅 
                                    <?php 
                                        if (!empty($p['date_debut']) && !empty($p['date_fin'])) {
                                            echo date('d M Y', strtotime($p['date_debut'])) . ' - ' . date('d M Y', strtotime($p['date_fin']));
                                        } elseif (!empty($p['date_debut'])) {
                                            echo date('d M Y', strtotime($p['date_debut']));
                                        } else {
                                            echo 'Date archivée';
                                        }
                                    ?>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($p['description'])): ?>
                                <p style="font-size: 0.9em; color: #777; margin-bottom: 12px; line-height: 1.5;">
                                    <?php echo htmlspecialchars(substr($p['description'], 0, 120)) . '...'; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- MODAL DÉTAILLÉE -->
    <div id="programmeModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center; overflow-y: auto; padding: 16px;">
        <div style="background: white; border-radius: 10px; max-width: 650px; width: 100%; padding: 24px; position: relative; box-shadow: 0 16px 40px rgba(0,0,0,0.25); margin: auto;">
            <button onclick="closeProgrammeModal()" style="position: absolute; top: 10px; right: 10px; background: transparent; border: none; font-size: 24px; cursor: pointer; color: #999; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">✕</button>
            
            <div id="modalAffiche" style="width: 100%; max-height: 360px; margin-bottom: 20px; overflow: hidden; border-radius: 8px; background: #f3f3f3; display: flex; align-items: center; justify-content: center;"></div>
            
            <h2 id="modalTitre" style="font-size: 1.8em; font-weight: 700; margin-bottom: 12px; color: var(--color-primary);"></h2>
            
            <div style="margin-bottom: 15px; padding: 12px; background: rgba(29, 58, 95, 0.05); border-left: 4px solid var(--color-primary); border-radius: 4px;">
                <p id="modalDates" style="margin: 0; font-weight: 600; color: var(--color-primary);">📅 Dates</p>
            </div>
            
            <div style="margin-bottom: 20px;">
                <h3 style="font-weight: 700; margin-bottom: 8px; color: #333;">Description</h3>
                <p id="modalDescription" style="line-height: 1.8; color: #555; margin: 0;"></p>
            </div>
            
            <div style="display: flex; gap: 12px;">
                <a id="modalInscribeBtn" href="inscription.php" class="btn btn-primary" style="flex: 1; text-align: center;">S'inscrire</a>
                <button onclick="closeProgrammeModal()" style="background: #f3f3f3; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: 600; color: #333;">Fermer</button>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
    
    <script>
        function openProgrammeModal(programme) {
            const modal = document.getElementById('programmeModal');
            document.getElementById('modalTitre').textContent = programme.titre || 'Programme';
            
            // Affiche image
            const affiches = document.getElementById('modalAffiche');
            if (programme.affiche_url) {
                affiches.innerHTML = '<img src="' + programme.affiche_url.replace(/"/g, '&quot;') + '" style="max-width: 100%; max-height: 100%; object-fit: contain; display:block;">';
            } else {
                affiches.innerHTML = '<div style="color: #999; font-size: 3em;">📅</div>';
            }
            
            // Dates
            let datesText = '📅 ';
            if (programme.date_debut && programme.date_fin) {
                datesText += new Date(programme.date_debut).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' }) + ' - ' + new Date(programme.date_fin).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' });
            } else if (programme.date_debut) {
                datesText += new Date(programme.date_debut).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' });
            } else {
                datesText += 'Date à confirmer';
            }
            document.getElementById('modalDates').textContent = datesText;
            
            // Description complète
            document.getElementById('modalDescription').textContent = programme.description || 'Pas de description disponible';
            
            // Vérifie si le programme est passé
            let isPast = false;
            if (programme.date_fin) {
                const endDate = new Date(programme.date_fin);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                isPast = endDate < today;
            }
            
            // Masque ou affiche le bouton S'inscrire
            const inscribeBtn = document.getElementById('modalInscribeBtn');
            if (isPast) {
                inscribeBtn.style.display = 'none';
            } else {
                inscribeBtn.style.display = '';
                // Met à jour le lien d'inscription avec les paramètres
                inscribeBtn.href = 'inscription.php?type=programme&id=' + programme.id + '&label=' + encodeURIComponent(programme.titre);
            }
            
            // Affiche la modal
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeProgrammeModal() {
            const modal = document.getElementById('programmeModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Ferme la modal en cliquant en dehors
        document.getElementById('programmeModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeProgrammeModal();
            }
        });
    </script>
</body>
</html>
