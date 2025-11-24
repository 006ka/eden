<?php
require_once __DIR__ . '/../config/db.php';
$photos = $pdo->query('SELECT * FROM gallery ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galerie - EDEN</title>
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
                <li><a href="retraite.php">Retraites</a></li>
                <li><a class="active" href="galerie.php">Galerie</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </nav>
    </header>

    <!-- BANNER -->
    <section class="banner">
        <h1>Galerie des Retraites</h1>
        <p>Quelques moments forts et bénis de nos retraites précédentes.</p>
    </section>

    <!-- GALERIE -->
    <section class="galerie-page">
        <h2>Photos des événements</h2>

        <div class="galerie-grid">
            <?php if (empty($photos)): ?>
                <p>Aucune photo enregistrée pour le moment.</p>
            <?php else: ?>
                <?php foreach ($photos as $p):
                    $orig = $p['image_url'];
                    $basename = basename($orig);
                    $thumbRel = '../uploads/thumb_' . $basename;
                    $uploadsDir = realpath(__DIR__ . '/../uploads');
                    if ($uploadsDir) {
                        $thumbFs = $uploadsDir . DIRECTORY_SEPARATOR . 'thumb_' . $basename;
                    } else {
                        $thumbFs = __DIR__ . '/../uploads/thumb_' . $basename;
                    }
                    $display = (file_exists($thumbFs)) ? $thumbRel : $orig;
                    $link = !empty($p['retreat_id']) ? 'retraite.php?id=' . (int)$p['retreat_id'] : 'retraite.php';
                ?>
                    <a href="<?php echo htmlspecialchars($link); ?>">
                        <img src="<?php echo htmlspecialchars($display); ?>" alt="<?php echo htmlspecialchars($p['titre'] ?? ''); ?>">
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- LIGHTBOX -->
    <div id="lightbox">
        <img id="lightbox-img" src="">
    </div>

    <!-- FOOTER -->
    <footer>
        <p>© 2025 EDEN — Tous droits réservés.</p>
        <p>Verset du jour : <span id="verset"></span></p>
    </footer>

<script src="../assets/js/script.js"></script>
<script src="../assets/js/lightbox.js"></script>
</body>
</html>
