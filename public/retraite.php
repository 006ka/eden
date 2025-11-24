<?php
require_once __DIR__ . '/../config/db.php';

$retreat = null;

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $pdo->prepare('SELECT * FROM retreats WHERE id = ?');
    $stmt->execute([$id]);
    $retreat = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$retreat) {
    // Si aucun id ou retraite non trouvée, on prend la plus récente
    $retreat = $pdo->query('SELECT * FROM retreats ORDER BY created_at DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
}

// Valeurs par défaut si aucune retraite n est en base
$retTitre = $retreat ? $retreat['titre'] : 'Retraite Spirituelle';
$retTheme = $retreat && !empty($retreat['theme']) ? $retreat['theme'] : 'Rencontre et Transformation';
$retDates = '';
if ($retreat && !empty($retreat['date_debut'])) {
    $retDates = $retreat['date_debut'];
    if (!empty($retreat['date_fin'])) {
        $retDates .= ' - ' . $retreat['date_fin'];
    }
}
$retLieu = $retreat && !empty($retreat['lieu']) ? $retreat['lieu'] : 'Centre Spirituel Mont-Eden';
$retOrateurs = $retreat && !empty($retreat['orateurs']) ? $retreat['orateurs'] : 'Pasteur John, Soeur Marie';
 $programmeImage = $retreat && !empty($retreat['programme_image_url']) ? $retreat['programme_image_url'] : '';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programme de la retraite - EDEN</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <!-- HEADER -->
    <header>
        <div class="logo">EDEN</div>
        <nav>
            <ul>
                <li><a href="../index.php">Accueil</a></li>
                <li><a href="apropos.php">À propos</a></li>
                <li><a href="programmes.php">Programmes</a></li>
                <li><a class="active" href="retraite.php">Retraites</a></li>
                <li><a href="galerie.php">Galerie</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </nav>
    </header>

    <!-- BANNER -->
    <section class="banner">
        <h1><?php echo htmlspecialchars($retTitre); ?></h1>
        <p>Thème : <strong><?php echo htmlspecialchars($retTheme); ?></strong></p>
    </section>

    <!-- INFORMATIONS -->
    <section class="infos-retraite">
        <h2>Informations générales</h2>
        <ul>
            <?php if ($retDates !== ''): ?>
                <li>📅 Dates : <?php echo htmlspecialchars($retDates); ?></li>
            <?php endif; ?>
            <li>📍 Lieu : <?php echo htmlspecialchars($retLieu); ?></li>
            <li>⛪ Intervenants : <?php echo htmlspecialchars($retOrateurs); ?></li>
            <li>🎵 Activités : Louange, Enseignements, Ateliers, Prière, Délivrance</li>
        </ul>

        <a href="inscription.php" class="btn primary">S’inscrire maintenant</a>
    </section>

    <!-- PROGRAMME SOUS FORME D'IMAGE -->
    <section class="programme-details">
        <h2>Programme détaillé</h2>
        <?php if ($programmeImage !== ''): ?>
            <div style="text-align:center; margin-top:15px;">
                <img src="<?php echo htmlspecialchars($programmeImage); ?>" alt="Programme de la retraite" style="max-width:100%; height:auto; border-radius:8px;">
            </div>
        <?php else: ?>
            <p>Aucun programme détaillé n'a encore été publié pour cette retraite.</p>
        <?php endif; ?>
    </section>

    <!-- CONSEILS -->
    <section class="conseils">
        <h2>Conseils pratiques</h2>
        <ul>
            <li>🎒 Apporter une Bible, un carnet et un stylo.</li>
            <li>🛏️ Prévoir couverture ou draps selon préférence.</li>
            <li>💧 Bouteille d’eau personnelle.</li>
            <li>⚠️ Respect strict des horaires.</li>
            <li>🙏 Attitude de prière et de recueillement.</li>
        </ul>
    </section>

    <!-- FOOTER -->
    <footer>
        <p>© 2025 EDEN — Tous droits réservés.</p>
        <p>Verset du jour : <span id="verset"></span></p>
    </footer>

<script src="../assets/js/script.js"></script>
</body>
</html>
