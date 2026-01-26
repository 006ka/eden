<?php
// Reusable hero slideshow include. Shows latest 6 gallery images as background slides.
// Only include this file from public pages (header will guard admin pages).

// Ensure we have a PDO instance
if (!isset($pdo)) {
    if (file_exists(__DIR__ . '/../config/db.php')) {
        require_once __DIR__ . '/../config/db.php';
    }
}

$homePhotos = [];
try {
    if (isset($pdo)) {
        // Prefer retreat posters (programme_image_url) similarly to homepage
        $homePhotos = $pdo->query("SELECT id, titre, programme_image_url AS image_url, date_debut, date_fin FROM retreats WHERE programme_image_url IS NOT NULL AND programme_image_url != '' ORDER BY CASE WHEN date_fin >= CURDATE() OR date_debut >= CURDATE() THEN 0 ELSE 1 END, COALESCE(date_debut,date_fin) ASC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
        // if none, fall back to gallery images
        if (empty($homePhotos)) {
            $homePhotos = $pdo->query('SELECT g.*, r.id AS retreat_id FROM gallery g LEFT JOIN retreats r ON g.retreat_id = r.id ORDER BY g.id DESC LIMIT 6')->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (Exception $e) {
    $homePhotos = [];
}

?>
<style>
    /* Slideshow styles (scoped to global hero) */
    .global-hero-slideshow { position: relative; height: 520px; overflow: hidden; }
    .global-hero-slideshow .hero-slides { position: absolute; inset: 0; z-index: 0; }
    .global-hero-slideshow .hero-slide { position: absolute; inset: 0; background-size: cover; background-position: center center; background-repeat: no-repeat; opacity: 0; transition: opacity 1s ease-in-out; }
    .global-hero-slideshow .hero-slide.active { opacity: 1; }
    .global-hero-slideshow .hero-slide-link { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 2; display: block; cursor: pointer; }
    /* Blue overlay, aligned with main hero-section filter */
    .global-hero-slideshow .overlay { position: absolute; inset: 0; z-index: 1; pointer-events: none; }
    /* Keep header above slides */
    .site-header { position: relative; z-index: 100; }
    /* Ensure per-page hero background images are hidden so slideshow shows through */
    .hero-section { background: transparent !important; }
</style>

<div class="global-hero-slideshow" aria-hidden="true">
    <div class="hero-slides">
        <?php
        if (!empty($homePhotos)) {
                foreach ($homePhotos as $i => $p) {
                    $src = $p['image_url'] ?? $p['image_url'];
                    if (strpos($src, '../') === 0) $src = substr($src, 3);
                    // Ensure correct relative prefix when header set a $base
                    $src_url = $src;
                    if (!preg_match('#^(https?:)?//#', $src_url) && strpos($src_url, '/') !== 0) {
                        $src_url = (isset($base) ? $base : '') . $src_url;
                    }
                    $activeClass = $i === 0 ? ' active' : '';
                    // Determine retreat id: from retreats query 'id' or from gallery 'retreat_id'
                    $retreatId = $p['id'] ?? ($p['retreat_id'] ?? null);
                    echo '<div class="hero-slide' . $activeClass . '" style="background-image: url(' . htmlspecialchars($src_url) . ');">';
                    if ($retreatId) {
                        // link to public retreat page (use base if necessary)
                        $link = (isset($base) ? $base : '') . 'public/retraite.php?id=' . (int)$retreatId;
                        echo '<a class="hero-slide-link" href="' . htmlspecialchars($link) . '"></a>';
                    }
                    echo '</div>' . "\n";
                }
            } else {
                $placeholder = (isset($base) ? $base : '') . 'assets/img/hero-placeholder.jpg';
                echo '<div class="hero-slide active" style="background-image: url(' . htmlspecialchars($placeholder) . ');"></div>' . "\n";
            }
        ?>
    </div>
    <div class="overlay"></div>
    <?php // hero content overlay removed — slides should display image-only. ?>
</div>

<script>
    (function(){
        var slides = document.querySelectorAll('.global-hero-slideshow .hero-slide');
        if (!slides || slides.length <= 1) return;
        var current = 0;
        var total = slides.length;
        for (var i=0;i<total;i++){ var img = new Image(); img.src = getComputedStyle(slides[i]).backgroundImage.replace(/url\((?:"|')?(.*?)(?:"|')?\)/,'$1'); }
        setInterval(function(){
            slides[current].classList.remove('active');
            current = (current + 1) % total;
            slides[current].classList.add('active');
        }, 5000);
        window._edenGlobalHeroSlides = slides;
    })();
</script>
