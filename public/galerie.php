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
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>

    <?php 
        $base = '';
        include __DIR__ . '/../includes/header.php'; 
    ?>

    <!-- SECONDARY BLOCKS - Gallery Info -->
    <section class="secondary-blocks">
        <div class="container">
            <h2 style="font-size: 1.6em; font-weight: 700; margin-bottom: 20px; color: var(--dark-text);">Galerie & Témoignages</h2>
            <div class="block-grid">
                <div class="block" style="background-color: var(--color-primary);">
                    <h3>📸 Moments</h3>
                    <p style="font-size: 0.95em;">Instants bénis capturés</p>
                </div>
                <div class="block" style="background-color: var(--color-primary-dark);">
                    <h3>🤝 Communauté</h3>
                    <p style="font-size: 0.95em;">Partage et fraternité</p>
                </div>
                <div class="block" style="background-color: var(--color-primary-light);">
                    <h3>🙏 Adoration</h3>
                    <p style="font-size: 0.95em;">Présence de Dieu</p>
                </div>
                <div class="block" style="background-color: var(--color-secondary);">
                    <h3>📝 Souvenirs</h3>
                    <p style="font-size: 0.95em;">À conserver</p>
                </div>
            </div>
        </div>
    </section>

    <!-- GALERIE -->
    <section id="gallery" class="contact-map-section">
        <div class="container">
            <h2 style="font-size: 2em; font-weight: 700; margin-bottom: 30px;">Photos des événements</h2>

            <div class="galerie-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                <?php if (empty($photos)): ?>
                    <p style="grid-column: 1 / -1; color: var(--muted); font-size: 1.1em;">Aucune photo enregistrée pour le moment.</p>
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
                        $full = $orig;
                        $link = !empty($p['retreat_id']) ? 'retraite.php?id=' . (int)$p['retreat_id'] : '';
                    ?>
                        <div class="home-gallery-card" data-full="<?php echo htmlspecialchars($full); ?>" data-title="<?php echo htmlspecialchars($p['titre'] ?? ''); ?>" data-link="<?php echo htmlspecialchars($link); ?>"
                             style="position: relative; background: white; border-radius: var(--radius); overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: transform 0.2s, box-shadow 0.2s; cursor: pointer;" 
                             onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.12)';" 
                             onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)';">
                            <div style="width: 100%; padding-bottom: 66.66%; position: relative; background: var(--light-bg); overflow: hidden;">
                                <img src="<?php echo htmlspecialchars($display); ?>" 
                                     alt="<?php echo htmlspecialchars($p['titre'] ?? 'Photo'); ?>" 
                                     loading="lazy" 
                                     decoding="async"
                                     style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <?php if (!empty($p['titre']) || !empty($link)): ?>
                                <div style="padding: 15px; background: white; display:flex; justify-content:space-between; align-items:center; gap:10px;">
                                    <?php if (!empty($p['titre'])): ?>
                                        <p style="font-weight: 600; color: #333; margin: 0; font-size: 0.95em;">
                                            <?php echo htmlspecialchars(substr($p['titre'], 0, 50)); ?>
                                        </p>
                                    <?php endif; ?>
                                    <?php if (!empty($link)): ?>
                                        <a href="<?php echo htmlspecialchars($link); ?>" style="font-size:0.8em; color: var(--color-primary); text-decoration:underline; white-space:nowrap;">Voir la retraite</a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- CTA SECTION -->
    <section class="contact-map-section" style="background: var(--bg);">
        <div class="container" style="text-align: center;">
            <h2 style="font-size: 1.8em; font-weight: 700; margin-bottom: 15px;">Participez à la Prochaine Retraite</h2>
            <p style="font-size: 1.1em; color: var(--muted); margin-bottom: 25px; max-width: 600px; margin-left: auto; margin-right: auto;">
                Vivez des moments forts de communion et de transformation. Inscrivez-vous à la prochaine retraite.
            </p>
            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <a href="retraite.php" class="btn btn-red" style="padding: 12px 30px; font-weight: 600;">Voir les retraites</a>
                <a href="inscription.php" class="btn btn-border" style="padding: 12px 30px; font-weight: 600; background: rgba(29, 58, 95, 0.1); color: var(--color-primary); border: 2px solid var(--color-primary);">S'inscrire</a>
            </div>
        </div>
    </section>

    <!-- MODAL GALERIE (même style que l'accueil) -->
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
                <a id="homeGalleryMore" href="galerie.php" class="btn btn-outline">Voir la galerie complète</a>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script>
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
