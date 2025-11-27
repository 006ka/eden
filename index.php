<?php
require_once __DIR__ . '/config/db.php';
// On récupère les 6 dernières photos de la galerie pour l'accueil
$homePhotos = $pdo->query('SELECT * FROM gallery ORDER BY id DESC LIMIT 6')->fetchAll(PDO::FETCH_ASSOC);
// Prépare des images pour la section "Enseignements & Méditations" (3 vignettes)
$sermonImages = [];
if (!empty($homePhotos)) {
    for ($i = 0; $i < 3; $i++) {
        if (isset($homePhotos[$i])) {
            $src = $homePhotos[$i]['image_url'];
            if (strpos($src, '../') === 0) $src = substr($src, 3);
            $sermonImages[] = $src;
        }
    }
}
// si pas assez d'images en base, remplir avec des placeholders
while (count($sermonImages) < 3) {
    $sermonImages[] = 'assets/img/hero-placeholder.jpg';
}

// Image pour la colonne latérale des enseignements (4ème image si disponible)
$sermonSidebarImage = '';
if (!empty($homePhotos) && isset($homePhotos[3])) {
    $src = $homePhotos[3]['image_url'];
    if (strpos($src, '../') === 0) $src = substr($src, 3);
    $sermonSidebarImage = $src;
} else {
    $sermonSidebarImage = 'assets/img/hero-placeholder.jpg';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EDEN - Accueil</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        /* Inline slideshow styles for hero */
        .hero-section { position: relative; overflow: hidden; }
        .hero-slides { position: absolute; inset: 0; z-index: 0; }
        .hero-slide { position: absolute; inset: 0; background-size: cover; background-position: center; background-repeat: no-repeat; opacity: 0; transition: opacity 1s ease-in-out; }
        .hero-slide.active { opacity: 1; }
        .hero-section .overlay { position: absolute; inset: 0; z-index: 1; }
        .hero-content { position: relative; z-index: 2; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    <section class="hero-section hero-home">
        <div class="hero-slides">
            <?php
            if (!empty($homePhotos)) {
                foreach ($homePhotos as $i => $p) {
                    $src = $p['image_url'];
                    if (strpos($src, '../') === 0) $src = substr($src, 3);
                    $activeClass = $i === 0 ? ' active' : '';
                    echo '<div class="hero-slide' . $activeClass . '" style="background-image: url(' . htmlspecialchars($src) . ');"></div>' . "\n";
                }
            } else {
                // fallback image
                echo '<div class="hero-slide active" style="background-image: url(assets/img/hero-placeholder.jpg);"></div>' . "\n";
            }
            ?>
        </div>
        <div class="overlay"></div>
        <div class="container hero-content">
            <p class="new-here-link">NOUVEAU ICI ?</p>
            <h1>VIVRE LA PRÉSENCE DE DIEU AVEC EDEN</h1>
            <p class="subtitle">Retraites spirituelles, temps de prière et enseignements pour une vie transformée en Christ.</p>
            <div class="hero-buttons">
                <a href="public/inscription.php" class="btn btn-red">S'inscrire à la prochaine retraite</a>
                <a href="public/programmes.php" class="btn btn-border">Découvrir nos programmes</a>
            </div>
        </div>
    </section>

    <section class="events-bar">
        <div class="container event-grid">
            <div class="event-item">
                <p class="date">PROCHAINE RETRAITE</p>
                <p class="title">Rencontre et Transformation</p>
                <p class="time">Dates : à confirmer</p>
            </div>
            <div class="event-item">
                <p class="date">RENCONTRES HEBDOMADAIRES</p>
                <p class="title">Temps de prière & partage</p>
                <p class="time">Chaque jeude de 22H a 2H</p>
            </div>
            <div class="event-item">
                <p class="date">JEUNES & JEUNES ADULTES</p>
                <p class="title">Groupe EDEN Jeunes</p>
                <p class="time">Rencontres régulières</p>
            </div>
            <div class="event-item last-event">
                <p class="date">SOUTENIR LE MINISTÈRE</p>
                <p class="title">Prières & partenariat</p>
                <p class="time">Impliquons-nous ensemble</p>
            </div>
            <a href="public/programmes.php" class="more-events-btn">VOIR LES ACTIVITÉS →</a>
        </div>
    </section>

    <section class="contact-map-section">
        <div class="container contact-grid">
            <div class="contact-info">
                <p><strong>MINISTÈRE EDEN</strong></p>
                <p><strong>Lieu de retraite : communiqué lors de l'inscription</strong></p>
                <p class="separator">-------------------</p>
                <p>Retraites spirituelles, séminaires et rencontres de prière.</p>
                <p>
                    <a href="mailto:contact@eden-ministere.org">contact@eden-ministere.org</a>
                </p>
            </div>
            <div class="map-card" style="border-radius:8px; overflow:hidden; box-shadow:0 6px 18px rgba(0,0,0,0.08);">
                <div class="map-iframe-wrapper" style="width:100%; height:260px; background:#f0f0f0;">
                    <!-- OpenStreetMap embed centered on the exact point you provided (Ruashi Kundelungu) -->
                    <iframe
                        src="https://www.openstreetmap.org/export/embed.html?bbox=27.53008%2C-11.64649%2C27.54008%2C-11.63649&layer=mapnik&marker=-11.64149%2C27.53508"
                        width="100%" height="100%" style="border:0;"
                        allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                <div class="map-links" style="display:flex; gap:12px; padding:12px; align-items:center;">
                    <a href="https://www.openstreetmap.org/?mlat=-11.64149&mlon=27.53508#map=18/-11.64149/27.53508" target="_blank" rel="noopener" class="btn btn-border">Voir sur OpenStreetMap</a>
                    <a href="public/contact.php" class="btn">NOUS CONTACTER</a>
                    <a href="public/apropos.php" class="link">EN SAVOIR PLUS</a>
                </div>
            </div>
        </div>
    </section>

    <section class="secondary-blocks">
        <div class="container block-grid">
            <div class="block block-worship">
                <h2>Adoration</h2>
                <p>
                    Des personnes passionnées par Dieu, qui se laissent déborder par Lui
                    et qui grandissent avec transformation dans leur relation avec Lui.
                </p>
            </div>
            <div class="block block-calendar">
                <h2>Maturité</h2>
                <p>
                    Des personnes passionnées de Dieu, qui ont découvert leur but et
                    qui s’engagent au prix de leur vie à l’enfanter par la croissance.
                </p>
            </div>
            <div class="block block-ministries">
                <h2>Impact</h2>
                <p>
                    Des personnes capables d’affecter la vie des autres, pour participer
                    à leurs promotions au rang de l’Elite royal.
                </p>
            </div>
            <div class="block block-missions">
                <h2>Service</h2>
                <p>
                    Des personnes autonomes et non codépendantes, celles qui prospèrent
                    pour toujours avoir de quoi donner.
                </p>
            </div>
            <div class="block block-authentic">
                <h2>Fraternité</h2>
                <p>
                    Des personnes passionnées par les gens, pour les aimer et les honorer
                    sans limites, des personnes qui se purifient et se gardent pour les autres.
                </p>
            </div>
        </div>
    </section>

    <section class="new-sermons-section">
        <div class="container sermons-layout">
            <div class="sermon-sidebar" style="background-image: url('<?php echo htmlspecialchars($sermonSidebarImage); ?>'); background-size: cover; background-position: center; border-radius:8px; min-height: 220px;"></div>
            <div class="sermons-content">
                <h2>ENSEIGNEMENTS & MÉDITATIONS</h2>
                <div class="sermon-list">
                    <article class="sermon-post">
                        <div class="sermon-thumb" style="background-image: url('<?php echo htmlspecialchars($sermonImages[0]); ?>'); background-size: cover; background-position: center; min-height: 140px; border-radius: 8px;"></div>
                        <p class="date">JUIN 25, 2025</p>
                        <h3>Retrouver le chemin avec Dieu</h3>
                        <p class="author">Équipe EDEN →</p>
                        <p class="excerpt">Un enseignement pour revenir à la présence de Dieu et à Sa volonté.</p>
                    </article>

                    <article class="sermon-post">
                        <div class="sermon-thumb" style="background-image: url('<?php echo htmlspecialchars($sermonImages[1]); ?>'); background-size: cover; background-position: center; min-height: 140px; border-radius: 8px;"></div>
                        <p class="date">JUIN 18, 2025</p>
                        <h3>Adorer en esprit et en vérité</h3>
                        <p class="author">Ministère EDEN →</p>
                        <p class="excerpt">Une méditation sur une adoration sincère qui transforme le cœur.</p>
                    </article>

                    <article class="sermon-post">
                        <div class="sermon-thumb" style="background-image: url('<?php echo htmlspecialchars($sermonImages[2]); ?>'); background-size: cover; background-position: center; min-height: 140px; border-radius: 8px;"></div>
                        <p class="date">JUIN 11, 2025</p>
                        <h3>Marcher avec Dieu après la retraite</h3>
                        <p class="author">Invités EDEN →</p>
                        <p class="excerpt">Des pistes concrètes pour garder le feu reçu pendant la retraite.</p>
                    </article>
                </div>
                <div class="sermon-footer-links">
                    <a href="public/programmes.php" class="btn btn-small-red">TOUS LES ENSEIGNEMENTS</a>
                    <a href="public/programmes.php#themes" class="link">THÈMES</a>
                    <a href="public/programmes.php#series" class="link">SÉRIES</a>
                    <a href="public/programmes.php#livres" class="link">LIVRES</a>
                </div>
            </div>
        </div>
    </section>

    <section class="galerie">
        <div class="container">
            <h2>Moments des retraites précédentes</h2>
            <div class="grid home-gallery-grid">
            <?php if (empty($homePhotos)): ?>
                <p>Aucune photo enregistrée pour le moment. Revenez bientôt&nbsp;!</p>
            <?php else: ?>
                <?php foreach ($homePhotos as $p): ?>
                    <?php
                    $link = !empty($p['retreat_id']) ? 'public/retraite.php?id=' . (int)$p['retreat_id'] : 'public/retraite.php';
                    $src = $p['image_url'];
                    if (strpos($src, '../') === 0) {
                        $src = substr($src, 3);
                    }
                    ?>
                    <div class="home-gallery-card" data-full="<?php echo htmlspecialchars($src); ?>" data-title="<?php echo htmlspecialchars($p['titre'] ?? ''); ?>" data-link="<?php echo htmlspecialchars($link); ?>">
                        <div class="home-gallery-thumb">
                            <img src="<?php echo htmlspecialchars($src); ?>" alt="<?php echo htmlspecialchars($p['titre'] ?? ''); ?>" loading="lazy" decoding="async">
                        </div>
                        <?php if (!empty($p['titre'])): ?>
                            <div class="home-gallery-caption"><?php echo htmlspecialchars(substr($p['titre'], 0, 60)); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            </div>
            <a href="public/galerie.php" class="btn btn-secondary" style="display:inline-flex; margin-top: var(--space-md);">Voir plus de photos</a>
        </div>
    </section>

    <!-- MODAL GALERIE ACCUEIL -->
    <div id="homeGalleryModal" class="home-gallery-modal" style="display:none;">
        <div class="home-gallery-modal-backdrop" onclick="edenCloseHomePhotoModal(event)"></div>
        <div class="home-gallery-modal-content">
            <button type="button" class="home-gallery-modal-close" onclick="edenCloseHomePhotoModal(event)">✕</button>
            <div class="home-gallery-modal-image-wrapper">
                <img id="homeGalleryModalImg" src="" alt="Photo de retraite">
            </div>
            <p id="homeGalleryModalTitle" class="home-gallery-modal-title"></p>
            <div class="home-gallery-modal-actions">
                <a id="homeGalleryDownload" href="#" download class="btn btn-secondary">Télécharger la photo</a>
                <a id="homeGalleryRetreat" href="#" class="btn btn-primary" style="display:none;">Voir la retraite liée</a>
                <a id="homeGalleryMore" href="public/galerie.php" class="btn btn-outline">Voir la galerie complète</a>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script>
        // Hero slideshow
        (function(){
            var slides = document.querySelectorAll('.hero-slide');
            if (!slides || slides.length <= 1) return;
            var current = 0;
            var total = slides.length;
            // Preload
            for (var i=0;i<total;i++){ var img = new Image(); img.src = getComputedStyle(slides[i]).backgroundImage.replace(/url\((?:"|')?(.*?)(?:"|')?\)/,'$1'); }
            setInterval(function(){
                slides[current].classList.remove('active');
                current = (current + 1) % total;
                slides[current].classList.add('active');
            }, 5000);
            // expose for debugging
            window._edenHeroSlides = slides;
        })();

        (function() {
            const cards = document.querySelectorAll('.home-gallery-card');
            const modal = document.getElementById('homeGalleryModal');
            const modalImg = document.getElementById('homeGalleryModalImg');
            const modalTitle = document.getElementById('homeGalleryModalTitle');
            const downloadLink = document.getElementById('homeGalleryDownload');
            const retreatLink = document.getElementById('homeGalleryRetreat');

            cards.forEach(function(card) {
                card.addEventListener('click', function() {
                    const src = card.getAttribute('data-full');
                    const title = card.getAttribute('data-title') || '';
                    const retreatUrl = card.getAttribute('data-link') || '';
                    modalImg.src = src;
                    modalTitle.textContent = title;
                    downloadLink.href = src;
                    if (retreatUrl) {
                        retreatLink.href = retreatUrl;
                        retreatLink.style.display = 'inline-flex';
                    } else if (retreatLink) {
                        retreatLink.href = '#';
                        retreatLink.style.display = 'none';
                    }
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                });
            });

            window.edenCloseHomePhotoModal = function(event) {
                if (event && event.target && event.target.classList && event.target.classList.contains('home-gallery-modal-backdrop') || event && event.type === 'click') {
                    modal.style.display = 'none';
                    modalImg.src = '';
                    document.body.style.overflow = '';
                }
            };
        })();
    </script>
</body>
</html>
