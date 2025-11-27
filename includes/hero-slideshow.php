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
        $homePhotos = $pdo->query('SELECT * FROM gallery ORDER BY id DESC LIMIT 6')->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $homePhotos = [];
}

?>
<style>
    /* Slideshow styles (scoped to global hero) */
    .global-hero-slideshow { position: relative; height: 500px; overflow: hidden; }
    .global-hero-slideshow .hero-slides { position: absolute; inset: 0; z-index: 0; }
    .global-hero-slideshow .hero-slide { position: absolute; inset: 0; background-size: cover; background-position: center; background-repeat: no-repeat; opacity: 0; transition: opacity 1s ease-in-out; }
    .global-hero-slideshow .hero-slide.active { opacity: 1; }
    /* Blue overlay, aligned with main hero-section filter */
    .global-hero-slideshow .overlay { position: absolute; inset: 0; z-index: 1; background: rgba(21, 41, 63, 0.55); }
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
                $src = $p['image_url'];
                if (strpos($src, '../') === 0) $src = substr($src, 3);
                // Ensure correct relative prefix when header set a $base
                $src_url = $src;
                if (!preg_match('#^(https?:)?//#', $src_url) && strpos($src_url, '/') !== 0) {
                    $src_url = (isset($base) ? $base : '') . $src_url;
                }
                $activeClass = $i === 0 ? ' active' : '';
                echo '<div class="hero-slide' . $activeClass . '" style="background-image: url(' . htmlspecialchars($src_url) . ');"></div>' . "\n";
            }
        } else {
            $placeholder = (isset($base) ? $base : '') . 'assets/img/hero-placeholder.jpg';
            echo '<div class="hero-slide active" style="background-image: url(' . htmlspecialchars($placeholder) . ');"></div>' . "\n";
        }
        ?>
    </div>
    <div class="overlay"></div>
    <?php // render hero content overlay (per-page defaults inside include)
    include __DIR__ . '/hero-content.php'; ?>
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
