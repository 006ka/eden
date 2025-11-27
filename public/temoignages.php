<?php
require_once __DIR__ . '/../config/db.php';

$testimonials = $pdo->query('SELECT * FROM testimonials WHERE visible = 1 ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Témoignages - EDEN</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        .testimonials-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap:18px; }
        .testimonial-card { background: white; padding:18px; border-radius:10px; box-shadow: 0 6px 18px rgba(0,0,0,0.06); }
        .testimonial-photo { width:72px; height:72px; border-radius:50%; overflow:hidden; flex:0 0 72px; }
        .testimonial-meta { display:flex; gap:12px; align-items:center; }
    </style>
</head>
<body>
    <?php $base = ''; include __DIR__ . '/../includes/header.php'; ?>

    <section class="hero-section" style="min-height:240px;">
        <div class="overlay"></div>
        <div class="container hero-content">
            <p class="new-here-link">Témoignages</p>
            <h1>Vécu & Transformation</h1>
            <p class="subtitle">Des histoires réelles de vies changées à travers les retraites EDEN.</p>
        </div>
    </section>

    <section class="contact-map-section">
        <div class="container">
            <?php if (empty($testimonials)): ?>
                <p>Aucun témoignage disponible pour le moment.</p>
            <?php else: ?>
                <div class="testimonials-grid">
                    <?php foreach ($testimonials as $t): ?>
                        <article class="testimonial-card">
                            <div class="testimonial-meta">
                                <?php if (!empty($t['image_url'])): ?>
                                    <?php
                                        $src = $t['image_url'];
                                        // Normalise les différents formats stockés en base
                                        // ../uploads/...
                                        if (strpos($src, '../') === 0) {
                                            $src = substr($src, 3);
                                        }
                                        // ./uploads/...
                                        if (strpos($src, './') === 0) {
                                            $src = substr($src, 2);
                                        }
                                        // /uploads/...  -> ../uploads/... depuis /public
                                        if (strpos($src, '/uploads/') === 0) {
                                            $src = '..' . $src;
                                        }
                                        // uploads/... -> ../uploads/... depuis /public
                                        if (strpos($src, 'uploads/') === 0) {
                                            $src = '../' . $src;
                                        }
                                    ?>
                                    <div class="testimonial-photo"><img src="<?php echo htmlspecialchars($src); ?>" alt="<?php echo htmlspecialchars($t['author']); ?>" style="width:100%; height:100%; object-fit:cover;"></div>
                                <?php endif; ?>
                                <div>
                                    <strong><?php echo htmlspecialchars($t['author']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($t['role'] ?? ''); ?></small>
                                </div>
                            </div>
                            <div style="margin-top:12px; font-size:0.98em; color:var(--color-text-light);">
                                <?php echo nl2br(htmlspecialchars($t['content'])); ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
