<?php
require_once __DIR__ . '/../config/db.php';

$successMessage = '';
$errorMessage = '';

// Récupère toutes les activités pour la sélection
$today = new DateTime('today');

// Retraites à venir
$stmt = $pdo->prepare("SELECT id, titre, date_debut FROM retreats WHERE date_debut IS NOT NULL AND date_debut >= ? ORDER BY date_debut ASC");
$stmt->execute([$today->format('Y-m-d')]);
$upcomingRetreats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Programmes à venir
$stmt = $pdo->prepare("SELECT id, titre, date_debut FROM programmes WHERE date_debut IS NOT NULL AND date_debut >= ? ORDER BY date_debut ASC");
$stmt->execute([$today->format('Y-m-d')]);
$upcomingProgrammes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Informations d'évènement passées en GET (ex: type=retraite&id=3&label=Retraite+Décembre)
$eventType  = isset($_GET['type']) ? trim($_GET['type']) : '';
$eventId    = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
$eventLabel = isset($_GET['label']) ? trim($_GET['label']) : '';

// Si pas de type/id spécifié, cherche l'activité la plus proche
if ($eventType === '' || $eventId === null) {
    $nextRetreat = !empty($upcomingRetreats) ? $upcomingRetreats[0] : null;
    $nextProgramme = !empty($upcomingProgrammes) ? $upcomingProgrammes[0] : null;
    
    // Détermine quel événement est le plus proche
    if ($nextRetreat && $nextProgramme) {
        $eventType = (new DateTime($nextRetreat['date_debut'])) <= (new DateTime($nextProgramme['date_debut'])) ? 'retraite' : 'programme';
        $nextEvent = (new DateTime($nextRetreat['date_debut'])) <= (new DateTime($nextProgramme['date_debut'])) ? $nextRetreat : $nextProgramme;
    } elseif ($nextRetreat) {
        $eventType = 'retraite';
        $nextEvent = $nextRetreat;
    } elseif ($nextProgramme) {
        $eventType = 'programme';
        $nextEvent = $nextProgramme;
    } else {
        $nextEvent = null;
    }
    
    if ($nextEvent) {
        $eventId = (int)$nextEvent['id'];
        $eventLabel = htmlspecialchars($nextEvent['titre']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $eglise = trim($_POST['eglise'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    
    // Récupère aussi l'évènement depuis les champs cachés
    $eventType  = trim($_POST['event_type'] ?? $eventType);
    $eventId    = isset($_POST['event_id']) && is_numeric($_POST['event_id']) ? (int)$_POST['event_id'] : $eventId;
    $eventLabel = trim($_POST['event_label'] ?? $eventLabel);

    if ($nom === '' || $email === '' || $telephone === '' || $age === '') {
        $errorMessage = 'Veuillez remplir tous les champs obligatoires.';
    } else {
        // Construction des besoins selon le type
        $besoins = '';
        
        if ($eventType === 'retraite') {
            // Pour les retraites : temps de présence, sujet de prière, besoins
            $temps_sejour = isset($_POST['temps_sejour']) ? trim($_POST['temps_sejour']) : '';
            $sujet_priere = trim($_POST['sujet_priere'] ?? '');
            $besoins_libre = trim($_POST['besoins'] ?? '');
            
            $parts = [];
            if ($sujet_priere !== '') {
                $parts[] = 'Sujet de prière : ' . $sujet_priere;
            }
            if ($besoins_libre !== '') {
                $parts[] = 'Autres besoins : ' . $besoins_libre;
            }
            $besoins = implode("\n", $parts);
        } else {
            // Pour les programmes : juste besoins particuliers
            $temps_sejour = null;
            $besoins = trim($_POST['besoins'] ?? '');
        }

        try {
            $stmt = $pdo->prepare('INSERT INTO inscriptions (nom, email, telephone, age, eglise, adresse, temps_sejour, event_type, event_id, event_label, besoins, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
            $stmt->execute([
                $nom, 
                $email, 
                $telephone, 
                $age, 
                $eglise, 
                $adresse, 
                $eventType === 'retraite' ? $temps_sejour : null,
                $eventType !== '' ? $eventType : null, 
                $eventId, 
                $eventLabel !== '' ? $eventLabel : null, 
                $besoins
            ]);
            $successMessage = "Votre inscription a été enregistrée avec succès !";
        } catch (PDOException $e) {
            $errorMessage = "Une erreur est survenue lors de l\'enregistrement : " . $e->getMessage();
        }
    }
}

// Informations pratiques de l'événement (prix + fiche) pour l'écran de confirmation
$eventPrix = '';
$eventFicheUrl = '';
if ($successMessage !== '' && $eventType !== '' && $eventId !== null) {
    try {
        if ($eventType === 'retraite') {
            $stmt = $pdo->prepare('SELECT prix, fiche_url FROM retreats WHERE id = ?');
            $stmt->execute([$eventId]);
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $eventPrix = !empty($row['prix']) ? $row['prix'] : '';
                $eventFicheUrl = !empty($row['fiche_url']) ? $row['fiche_url'] : '';
            }
        } elseif ($eventType === 'programme') {
            $stmt = $pdo->prepare('SELECT prix FROM programmes WHERE id = ?');
            $stmt->execute([$eventId]);
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $eventPrix = !empty($row['prix']) ? $row['prix'] : '';
            }
        }
    } catch (PDOException $e) {
        // en cas d'erreur, on n'empêche pas l'affichage de la confirmation
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - EDEN</title>
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
    <?php 
        $base = '';
        include __DIR__ . '/../includes/header.php'; 
    ?>

    <?php if ($successMessage !== ''): ?>
        <!-- ÉCRAN DE CONFIRMATION PLEIN CADRE -->
        <section class="contact-map-section" style="min-height: 70vh; display:flex; align-items:center;">
            <div class="container" style="max-width: 800px;">
                <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 18px; box-shadow: 0 20px 50px rgba(0,0,0,0.16); padding: 28px 26px 24px; border: 1px solid rgba(21,41,63,0.08); text-align: center;">
                    <div style="width: 64px; height: 64px; margin: 0 auto 14px; border-radius: 50%; background: rgba(39, 174, 96, 0.1); display:flex; align-items:center; justify-content:center; font-size: 32px;">
                        ✅
                    </div>
                    <h1 style="margin: 0 0 10px; font-size: 1.8rem; color: var(--color-primary); font-weight: 700;">Inscription confirmée</h1>
                    <p style="margin: 0 0 14px; font-size: 1rem; color: var(--color-text-light);">
                        <?= htmlspecialchars($successMessage) ?><br>
                        Merci pour votre confiance. Nous sommes heureux de vous accueillir pour ce temps avec EDEN.
                    </p>
                    <?php if ($eventLabel !== '' || $eventType !== ''): ?>
                        <p style="margin: 0 0 8px; font-size: 0.98rem; color: var(--color-text);">
                            📌 Activité : <strong><?= htmlspecialchars($eventLabel !== '' ? $eventLabel : $eventType); ?></strong>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($eventPrix)): ?>
                        <p style="margin: 0 0 18px; font-size: 0.95rem; color: var(--color-text-light);">
                            💳 Participation : <strong><?= htmlspecialchars($eventPrix); ?></strong>
                        </p>
                    <?php endif; ?>
                    <div style="display:flex; flex-wrap:wrap; gap:12px; justify-content:center; margin-top: 10px;">
                        <a href="../index.php" class="btn btn-secondary" style="min-width: 180px; text-align:center;">Retour à l'accueil</a>
                        <a href="retraite.php" class="btn btn-primary" style="min-width: 200px; text-align:center;">Voir les retraites</a>
                        <?php if (!empty($eventFicheUrl)): ?>
                            <a href="<?= htmlspecialchars($eventFicheUrl); ?>" class="btn" style="min-width: 210px; text-align:center; border:1px solid var(--color-primary); color: var(--color-primary); background: transparent;" download>
                                Télécharger la fiche pratique
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    <?php else: ?>
        <!-- SECONDARY BLOCKS - Pourquoi s'inscrire -->
        <section class="secondary-blocks">
            <div class="container">
                <h2 style="font-size: 1.6em; font-weight: 700; margin-bottom: 20px; color: var(--dark-text);">Pourquoi s'inscrire ?</h2>
                <div class="block-grid">
                    <div class="block" style="background-color: #2c6e49;">
                        <h3>🎯 Planning</h3>
                        <p style="font-size: 0.95em;">Organisé à l'avance</p>
                    </div>
                    <div class="block" style="background-color: #3d7f5a;">
                        <h3>👥 Accueil</h3>
                        <p style="font-size: 0.95em;">Chaleureux et attentif</p>
                    </div>
                    <div class="block" style="background-color: #4d8f6a;">
                        <h3>📋 Suivi</h3>
                        <p style="font-size: 0.95em;">Personnalisé</p>
                    </div>
                    <div class="block" style="background-color: #5d9f7a;">
                        <h3>🤝 Communauté</h3>
                        <p style="font-size: 0.95em;">Solidaire</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- INSCRIPTION FORM -->
        <section id="form" class="contact-map-section" style="background: var(--bg);">
            <div class="container">
                <h2 style="font-size: 2em; font-weight: 700; margin-bottom: 30px;">Formulaire d'Inscription</h2>

                <?php if ($errorMessage !== ''): ?>
                    <div style="background: #fee; border: 1px solid #fcc; padding: 12px; border-radius: 6px; margin-bottom: 20px; color: #c33;">
                        <?= htmlspecialchars($errorMessage) ?>
                    </div>
                <?php endif; ?>

                <?php if ($eventLabel !== '' || $eventType !== ''): ?>
                <div style="background: rgba(44, 110, 73, 0.1); border: 2px solid #2c6e49; padding: 15px; border-radius: 6px; margin-bottom: 20px; color: #2c6e49; font-weight: 600;">
                    📌 Inscription pour : <strong><?= htmlspecialchars($eventLabel !== '' ? $eventLabel : $eventType); ?></strong>
                </div>
            <?php endif; ?>

            <!-- SÉLECTEUR D'ACTIVITÉ -->
            <div style="background: #f5f5f5; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="margin-top: 0; margin-bottom: 15px; font-size: 1.1em;">Choisir une activité</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">Type d'activité</label>
                        <select id="activity_type_select" onchange="updateActivityList()" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; cursor: pointer;">
                            <option value="">-- Sélectionner --</option>
                            <option value="retraite" <?php echo ($eventType === 'retraite' ? 'selected' : ''); ?>>Retraites</option>
                            <option value="programme" <?php echo ($eventType === 'programme' ? 'selected' : ''); ?>>Programmes</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">Activité</label>
                        <select id="activity_select" onchange="updateActivityData()" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; cursor: pointer;">
                            <option value="">-- Sélectionner --</option>
                            <?php if ($eventType === 'retraite' || $eventType === ''): ?>
                                <?php foreach ($upcomingRetreats as $r): ?>
                                    <option value="retraite_<?php echo (int)$r['id']; ?>" <?php echo ($eventType === 'retraite' && $eventId === (int)$r['id'] ? 'selected' : ''); ?> data-type="retraite" data-id="<?php echo (int)$r['id']; ?>" data-label="<?php echo htmlspecialchars($r['titre']); ?>">
                                        <?php echo htmlspecialchars($r['titre']); ?> - <?php echo date('d/m/Y', strtotime($r['date_debut'])); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if ($eventType === 'programme'): ?>
                                <?php foreach ($upcomingProgrammes as $p): ?>
                                    <option value="programme_<?php echo (int)$p['id']; ?>" <?php echo ($eventType === 'programme' && $eventId === (int)$p['id'] ? 'selected' : ''); ?> data-type="programme" data-id="<?php echo (int)$p['id']; ?>" data-label="<?php echo htmlspecialchars($p['titre']); ?>">
                                        <?php echo htmlspecialchars($p['titre']); ?> - <?php echo date('d/m/Y', strtotime($p['date_debut'])); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>

                <div style="max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: var(--radius); box-shadow: 0 2px 12px rgba(0,0,0,0.08);">
                <form method="post" action="">
                    <input type="hidden" name="event_type" value="<?= htmlspecialchars($eventType); ?>">
                    <input type="hidden" name="event_id" value="<?= $eventId !== null ? (int)$eventId : ''; ?>">
                    <input type="hidden" name="event_label" value="<?= htmlspecialchars($eventLabel); ?>">

                    <!-- SECTION 1: INFORMATIONS PERSONNELLES -->
                    <h3 style="font-weight: 700; margin-bottom: 15px; margin-top: 25px; font-size: 1.1em; color: #2c6e49; border-bottom: 2px solid #2c6e49; padding-bottom: 10px;">Informations Personnelles</h3>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">Nom complet *</label>
                        <input type="text" name="nom" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 1em;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">Email *</label>
                            <input type="email" name="email" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 1em;">
                            <p style="margin-top: 4px; font-size: 0.85em; color: var(--color-text-light);">Votre adresse sera utilisée uniquement pour vous contacter à propos de cette inscription.</p>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">Téléphone *</label>
                            <input type="text" name="telephone" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 1em;">
                            <p style="margin-top: 4px; font-size: 0.85em; color: var(--color-text-light);">Ce numéro pourra servir à vous joindre en cas de changement d'horaire ou d'information importante.</p>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">Âge *</label>
                            <input type="number" name="age" min="10" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 1em;">
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">Église / Communauté</label>
                            <input type="text" name="eglise" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 1em;" placeholder="Optionnel">
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">Adresse complète</label>
                        <input type="text" name="adresse" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 1em;" placeholder="Rue, ville, pays">
                    </div>

                    <!-- SECTION 2: INFORMATIONS SPIRITUELLES (pour retraites) -->
                    <div id="retreat_section" style="<?php echo ($eventType === 'retraite' ? '' : 'display:none;'); ?>">
                        <h3 style="font-weight: 700; margin-bottom: 15px; margin-top: 25px; font-size: 1.1em; color: #2c6e49; border-bottom: 2px solid #2c6e49; padding-bottom: 10px;">Informations Spirituelles</h3>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">Temps de présence *</label>
                            <div style="display: flex; gap: 20px;">
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="radio" name="temps_sejour" value="Temps plein" style="margin-right: 8px;">
                                    <span>Temps plein</span>
                                </label>
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="radio" name="temps_sejour" value="Temps partiel" style="margin-right: 8px;">
                                    <span>Temps partiel</span>
                                </label>
                            </div>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">Sujet de prière</label>
                            <textarea name="sujet_priere" rows="3" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 1em; font-family: inherit;" placeholder="Ce pour quoi vous aimeriez qu'on prie pour vous"></textarea>
                        </div>
                    </div>

                    <!-- SECTION 3: BESOINS PARTICULIERS -->
                    <h3 style="font-weight: 700; margin-bottom: 15px; margin-top: 25px; font-size: 1.1em; color: #2c6e49; border-bottom: 2px solid #2c6e49; padding-bottom: 10px;">
                        <?php echo ($eventType === 'retraite' ? 'Autres Besoins Particuliers' : 'Besoin Particulier'); ?>
                    </h3>

                    <div style="margin-bottom: 25px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">
                            <?php echo ($eventType === 'retraite' ? 'Besoin particulier (santé, allergies, régime...)' : 'Besoin particulier pour cette activité'); ?>
                        </label>
                        <textarea name="besoins" rows="4" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 1em; font-family: inherit;" placeholder="Partagez tout besoin particulier pour une meilleure organisation"></textarea>
                    </div>

                    <button type="submit" class="btn btn-red" style="padding: 14px 40px; font-weight: 700; font-size: 1em; cursor: pointer; border: none; width: 100%;">Valider l'Inscription</button>
                    <p style="margin-top: 8px; font-size: 0.85em; color: var(--color-text-light); text-align: center;">Vos informations restent confidentielles et sont utilisées uniquement dans le cadre de l'organisation de cette activité.</p>
                </form>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <script>
        // Données des activités
        const activities = {
            retraite: <?php echo json_encode($upcomingRetreats); ?>,
            programme: <?php echo json_encode($upcomingProgrammes); ?>
        };

        function updateActivityList() {
            const typeSelect = document.getElementById('activity_type_select');
            const activitySelect = document.getElementById('activity_select');
            const selectedType = typeSelect.value;

            // Effacer les options existantes
            activitySelect.innerHTML = '<option value="">-- Sélectionner --</option>';

            if (selectedType && activities[selectedType]) {
                activities[selectedType].forEach(activity => {
                    const option = document.createElement('option');
                    option.value = selectedType + '_' + activity.id;
                    option.dataset.type = selectedType;
                    option.dataset.id = activity.id;
                    option.dataset.label = activity.titre;
                    const date = new Date(activity.date_debut).toLocaleDateString('fr-FR');
                    option.textContent = activity.titre + ' - ' + date;
                    activitySelect.appendChild(option);
                });
            }

            updateActivityData();
        }

        function updateActivityData() {
            const typeSelect = document.getElementById('activity_type_select');
            const activitySelect = document.getElementById('activity_select');
            const selectedOption = activitySelect.options[activitySelect.selectedIndex];

            const eventType = selectedOption.dataset.type || typeSelect.value || '';
            const eventId = selectedOption.dataset.id || '';
            const eventLabel = selectedOption.dataset.label || '';

            // Mise à jour des champs cachés du formulaire
            document.querySelector('input[name="event_type"]').value = eventType;
            document.querySelector('input[name="event_id"]').value = eventId;
            document.querySelector('input[name="event_label"]').value = eventLabel;

            // Afficher/masquer les sections selon le type
            const retreatSection = document.getElementById('retreat_section');
            if (eventType === 'retraite') {
                retreatSection.style.display = 'block';
            } else {
                retreatSection.style.display = 'none';
            }

            // Mise à jour du résumé
            updateActivitySummary(eventType, eventLabel);
        }

        function updateActivitySummary(eventType, eventLabel) {
            // Ici on pourrait ajouter une mise à jour dynamique du résumé
            console.log('Activité sélectionnée: ' + eventType + ' - ' + eventLabel);
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('activity_type_select');
            if (typeSelect.value) {
                updateActivityList();
            }
        });
    </script>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
