<?php
$base = (strpos($_SERVER['SCRIPT_NAME'], '/public/') !== false) ? '../' : '';
$current = $_SERVER['REQUEST_URI'] ?? '';
// simple helper pour ajouter classe active
function is_active($href) {
    $request = $_SERVER['REQUEST_URI'] ?? '';
    // compare uniquement le chemin (sans query)
    $reqPath = parse_url($request, PHP_URL_PATH);
    $hrefPath = parse_url($href, PHP_URL_PATH);
    return rtrim($reqPath, '/') === rtrim($hrefPath, '/');
}
?>
<header class="site-header">
    <div class="container header-inner">
        <a class="logo" href="<?php echo $base; ?>index.php">EDEN</a>
        <button id="nav-toggle" class="nav-toggle" aria-label="Ouvrir le menu" aria-expanded="false">☰</button>
        <nav class="site-nav" id="site-nav" role="navigation" aria-label="Menu principal">
            <ul>
                <li><a href="<?php echo $base; ?>index.php" class="<?php echo is_active($base . 'index.php') ? 'active' : ''; ?>">Accueil</a></li>
                <li><a href="<?php echo $base; ?>public/apropos.php" class="<?php echo is_active($base . 'public/apropos.php') ? 'active' : ''; ?>">À propos</a></li>
                <li><a href="<?php echo $base; ?>public/retraite.php" class="<?php echo is_active($base . 'public/retraite.php') ? 'active' : ''; ?>">Programmes</a></li>
                <li><a href="<?php echo $base; ?>public/horaire.php" class="<?php echo is_active($base . 'public/horaire.php') ? 'active' : ''; ?>">Horaires</a></li>
                <li><a href="<?php echo $base; ?>public/enseignements.php" class="<?php echo is_active($base . 'public/enseignements.php') ? 'active' : ''; ?>">Enseignements</a></li>
                <li><a href="<?php echo $base; ?>public/galerie.php" class="<?php echo is_active($base . 'public/galerie.php') ? 'active' : ''; ?>">Galerie</a></li>
                <li><a href="<?php echo $base; ?>public/temoignages.php" class="<?php echo is_active($base . 'public/temoignages.php') ? 'active' : ''; ?>">Témoignages</a></li>
                <li><a href="<?php echo $base; ?>public/contact.php" class="<?php echo is_active($base . 'public/contact.php') ? 'active' : ''; ?>">Contact</a></li>
            </ul>
        </nav>
    </div>
</header>

<?php
// Include global hero slideshow on public pages (skip admin area and homepage)
$scriptName = basename($_SERVER['SCRIPT_NAME'] ?? '');
if (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin') === false && $scriptName !== 'index.php') {
    include __DIR__ . '/hero-slideshow.php';
}
?>

