<?php
require_once __DIR__ . '/config/db.php';
// On récupère les 6 dernières photos de la galerie pour l'accueil
$homePhotos = $pdo->query('SELECT * FROM gallery ORDER BY id DESC LIMIT 6')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EDEN - Accueil</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <div class="logo">EDEN</div>
        <nav>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="public/apropos.php">À propos</a></li>
                <li><a href="public/programmes.php">Programmes</a></li>
                <li><a href="public/retraite.php">Retraites</a></li>
                <li><a href="public/galerie.php">Galerie</a></li>
                <li><a href="public/contact.php">Contact</a></li>
            </ul>
        </nav>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h1>Bienvenue dans le Ministère EDEN</h1>
            <p>Un espace de restauration spirituelle, de rencontre avec Dieu et de transformation de vie.</p>
            <div class="hero-buttons">
                <a href="public/inscription.php" class="btn primary">S’inscrire à la retraite</a>
                <a href="public/programmes.php" class="btn secondary">Découvrir nos programmes</a>
            </div>
        </div>
    </section>

    <section class="apropos">
        <h2>Qui sommes-nous ?</h2>
        <p>
            EDEN est un groupe chrétien dédié à la croissance spirituelle, 
            l’édification des croyants et l’organisation de retraites marquantes.
        </p>
        <a href="public/apropos.php" class="btn secondary">En savoir plus</a>
    </section>

    <section class="retraite">
        <h2>Prochaine Retraite Spirituelle 2025</h2>
        <p><strong>Thème :</strong> Rencontre et Transformation</p>
        <ul>
            <li>📅 Dates : 20 - 27 Decembre 2025</li>
            <li>📍 Lieu : Centre Spirituel Mont-Eden</li>
            <li>⛪ Orateurs invités : Prophete isaac, Soeur Marie</li>
        </ul>
        <a href="public/inscription.php" class="btn primary">Je m’inscris maintenant</a>
    </section>

    <section class="galerie">
        <h2>Moments des retraites précédentes</h2>
        <div class="grid">
            <?php if (empty($homePhotos)): ?>
                <p>Aucune photo enregistrée pour le moment. Revenez bientôt&nbsp;!</p>
            <?php else: ?>
                <?php foreach ($homePhotos as $p): ?>
                    <?php
                    $link = !empty($p['retreat_id']) ? 'public/retraite.php?id=' . (int)$p['retreat_id'] : 'public/retraite.php';
                    $src = $p['image_url'];
                    // Si le chemin commence par '../' (pensé pour /public), on l'adapte pour la racine du site
                    if (strpos($src, '../') === 0) {
                        $src = substr($src, 3);
                    }
                    ?>
                    <a href="<?php echo htmlspecialchars($link); ?>">
                        <img src="<?php echo htmlspecialchars($src); ?>" alt="<?php echo htmlspecialchars($p['titre'] ?? ''); ?>">
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <a href="public/galerie.php" class="btn secondary">Voir plus de photos</a>
    </section>

    <footer>
        <p>© 2025 EDEN — Tous droits réservés.</p>
        <p>Verset du jour : <span id="verset"></span></p>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>
