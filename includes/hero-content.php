<?php
// Reusable hero content overlay used on public pages.
// Pages can define variables before including header/hero-slideshow:
//  - $hero_label, $hero_title, $hero_subtitle, $hero_cta_primary (['href','label','class']), $hero_cta_secondary

$script = basename($_SERVER['SCRIPT_NAME'] ?? '');

// Defaults per page
$defaults = [
    'index.php' => [
        'label' => 'NOUVEAU ICI ?',
        'title' => 'VIVRE LA PRÉSENCE DE DIEU AVEC EDEN',
        'subtitle' => "Retraites spirituelles, temps de prière et enseignements pour une vie transformée en Christ.",
        'cta1' => ['href' => ($base ?? '') . 'index.php', 'label' => "S\'inscrire à la prochaine retraite", 'class' => 'btn btn-red'],
        'cta2' => ['href' => ($base ?? '') . 'public/programmes.php', 'label' => 'Découvrir nos programmes', 'class' => 'btn btn-border']
    ],
    'contact.php' => [
        'label' => 'Contactez-nous',
        'title' => 'Nous Contacter',
        'subtitle' => 'Nous sommes à votre disposition pour toute information',
        'cta1' => ['href' => ($base ?? '') . 'public/contact.php#contact-form', 'label' => 'Envoyer un message', 'class' => 'btn btn-red'],
        'cta2' => ['href' => ($base ?? '') . 'public/contact.php#info', 'label' => 'Nos coordonnées', 'class' => 'btn btn-border']
    ],
    'retraite.php' => [
        'label' => 'Retraites Spirituelles',
        // if page provides $retTitre/$retTheme they will be used instead
        'title' => isset($retTitre) ? $retTitre : 'Retraite Spirituelle',
        'subtitle' => isset($retTheme) ? 'Thème : ' . $retTheme : 'Retraites, temps de prière et enseignement',
        'cta1' => ['href' => ($base ?? '') . 'public/inscription.php', 'label' => "S\'inscrire", 'class' => 'btn btn-red'],
        'cta2' => ['href' => ($base ?? '') . 'public/retraite.php#details', 'label' => 'En savoir plus', 'class' => 'btn btn-border']
    ],
    'programmes.php' => [
        'label' => 'Programmes',
        'title' => 'Nos Programmes',
        'subtitle' => 'Formations, thèmes et séries pour grandir spirituellement.',
        'cta1' => ['href' => ($base ?? '') . 'public/programmes.php', 'label' => 'Voir les programmes', 'class' => 'btn btn-red'],
        'cta2' => ['href' => ($base ?? '') . 'public/inscription.php', 'label' => 'S\'inscrire à un programme', 'class' => 'btn btn-border']
    ],
    'galerie.php' => [
        'label' => 'Galerie',
        'title' => 'Moments des retraites',
        'subtitle' => 'Photos et souvenirs de nos retraites et activités.',
        'cta1' => ['href' => ($base ?? '') . 'public/galerie.php', 'label' => 'Voir la galerie', 'class' => 'btn btn-red'],
        'cta2' => ['href' => ($base ?? '') . 'public/galerie.php', 'label' => 'Contribuer une photo', 'class' => 'btn btn-border']
    ],
    'horaire.php' => [
        'label' => 'Horaires',
        'title' => 'Consultez l\'horaire',
        'subtitle' => 'Planning des activités et des retraites.',
        'cta1' => ['href' => ($base ?? '') . 'public/horaire.php', 'label' => 'Voir l\'horaire', 'class' => 'btn btn-red'],
        'cta2' => ['href' => ($base ?? '') . 'admin/admin.php?section=horaires', 'label' => 'Gérer (admin)', 'class' => 'btn btn-border']
    ]
];

$map = $defaults[$script] ?? $defaults['index.php'];

$hero_label = $hero_label ?? $map['label'];
$hero_title = $hero_title ?? $map['title'];
$hero_subtitle = $hero_subtitle ?? $map['subtitle'];
$hero_cta_primary = $hero_cta_primary ?? $map['cta1'];
$hero_cta_secondary = $hero_cta_secondary ?? $map['cta2'];
?>
<div class="container hero-content">
    <p class="new-here-link"><?php echo htmlspecialchars($hero_label); ?></p>
    <h1><?php echo htmlspecialchars($hero_title); ?></h1>
    <?php if (!empty($hero_subtitle)): ?>
        <p class="subtitle"><?php echo htmlspecialchars($hero_subtitle); ?></p>
    <?php endif; ?>
    <div class="hero-buttons">
        <?php if (!empty($hero_cta_primary) && is_array($hero_cta_primary)): ?>
            <a href="<?php echo htmlspecialchars($hero_cta_primary['href']); ?>" class="<?php echo htmlspecialchars($hero_cta_primary['class']); ?>"><?php echo htmlspecialchars($hero_cta_primary['label']); ?></a>
        <?php endif; ?>
        <?php if (!empty($hero_cta_secondary) && is_array($hero_cta_secondary)): ?>
            <a href="<?php echo htmlspecialchars($hero_cta_secondary['href']); ?>" class="<?php echo htmlspecialchars($hero_cta_secondary['class']); ?>"><?php echo htmlspecialchars($hero_cta_secondary['label']); ?></a>
        <?php endif; ?>
    </div>
</div>