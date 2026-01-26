<?php
require_once __DIR__ . '/config/db.php';
// Récupération des affiches des retraites pour le slider
$sliderPhotos = [];
try {
    // Récupère jusqu'à 6 affiches de retraites (à venir ou en cours en priorité)
    $sliderPhotos = $pdo->query("
        SELECT id, titre, programme_image_url AS image_url, date_debut, date_fin 
        FROM retreats 
        WHERE programme_image_url IS NOT NULL 
        AND programme_image_url != '' 
        ORDER BY 
            CASE 
                WHEN date_fin >= CURDATE() OR date_debut >= CURDATE() THEN 0 
                ELSE 1 
            END,
            COALESCE(date_debut, date_fin) ASC
        LIMIT 6
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $sliderPhotos = [];
}

// Récupération des images de la galerie pour la section "Moments des retraites précédentes"
$galleryPhotos = [];
try {
    // Récupère les images de la galerie des retraites précédentes
    $galleryPhotos = $pdo->query("
        SELECT g.*, r.id AS retreat_id, r.titre, r.date_debut, r.date_fin 
        FROM gallery g
        LEFT JOIN retreats r ON g.retreat_id = r.id
        WHERE (r.date_fin < CURDATE() OR r.date_debut < CURDATE())
        ORDER BY COALESCE(r.date_fin, r.date_debut) DESC, g.id DESC 
        LIMIT 6
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Si pas assez d'images, on complète avec des images plus anciennes
    if (count($galleryPhotos) < 6) {
        $limit = 6 - count($galleryPhotos);
        $older = $pdo->query("
            SELECT g.*, r.id AS retreat_id, r.titre, r.date_debut, r.date_fin 
            FROM gallery g
            LEFT JOIN retreats r ON g.retreat_id = r.id
            ORDER BY g.id DESC 
            LIMIT $limit
        ")->fetchAll(PDO::FETCH_ASSOC);
        $galleryPhotos = array_merge($galleryPhotos, $older);
    }
} catch (Exception $e) {
    $galleryPhotos = [];
}

// Pour la rétrocompatibilité, on garde $homePhotos pour les sections existantes
$homePhotos = $galleryPhotos;
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
// Récupère la prochaine retraite (date de début ou fin >= aujourd'hui)
$nextRetreat = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM retreats WHERE (date_debut IS NOT NULL AND date_debut >= CURDATE()) OR (date_fin IS NOT NULL AND date_fin >= CURDATE()) ORDER BY COALESCE(date_debut,date_fin) ASC LIMIT 1");
    $stmt->execute();
    $nextRetreat = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // ignore DB errors for homepage fallback
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
        .hero-section { position: relative; overflow: hidden; height: 520px; }
        .hero-slides { position: absolute; inset: 0; z-index: 0; }
        .hero-slide { position: absolute; inset: 0; background-size: cover; background-position: center center; background-repeat: no-repeat; opacity: 0; transition: opacity 1s ease-in-out; }
        .hero-slide.active { opacity: 1; }
        .hero-slide-link { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 2; }
        .hero-section .overlay { position: absolute; inset: 0; z-index: 1; }
        /* remove textual overlay but keep buttons visible */
        .hero-content { position: relative; z-index: 2; display: flex; align-items: flex-end; height: 100%; }
        .hero-content .hero-inner { width:100%; padding: 30px 0; text-align: left; }
        .hero-content h1, .hero-content .subtitle { display: none; }
        .hero-buttons { z-index:3; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    <section class="hero-section hero-home">
        <div class="hero-slides">
            <?php
            if (!empty($sliderPhotos)) {
                foreach ($sliderPhotos as $i => $p) {
                    $src = $p['image_url'];
                    if (strpos($src, '../') === 0) $src = substr($src, 3);
                    $activeClass = $i === 0 ? ' active' : '';
                    $retreatId = $p['id'] ?? null;
                    echo '<div class="hero-slide' . $activeClass . '" style="background-image: url(' . htmlspecialchars($src) . ');">';
                    if ($retreatId) {
                        echo '<a href="retraite.php?id=' . $retreatId . '" class="hero-slide-link"></a>';
                    }
                    echo '</div>' . "\n";
                }
            } else {
                // fallback image
                echo '<div class="hero-slide active" style="background-image: url(assets/img/hero-placeholder.jpg);"></div>' . "\n";
            }
            ?>
        </div>
        <div class="overlay"></div>
        <div class="container hero-content">
            <div class="hero-inner">
                <!-- Bouton d'inscription supprimé à la demande -->
            </div>
        </div>
    </section>

    <!-- Section Prochaine Retraite -->
    <?php
    // Récupération de la prochaine retraite
    $nextRetreat = null;
    try {
        $nextRetreat = $pdo->query("
            SELECT * FROM retreats 
            WHERE (date_debut >= CURDATE() OR date_fin >= CURDATE())
            ORDER BY COALESCE(date_debut, date_fin) ASC 
            LIMIT 1
        ")->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $nextRetreat = null;
    }
    
    if ($nextRetreat):
        $retDates = $nextRetreat['date_debut'];
        if (!empty($nextRetreat['date_fin']) && $nextRetreat['date_fin'] !== $nextRetreat['date_debut']) {
            $retDates .= ' - ' . $nextRetreat['date_fin'];
        }
        $retLieu = $nextRetreat['lieu'] ?? 'Lieu à préciser';
        $retOrateurs = $nextRetreat['orateurs'] ?? 'Intervenants à confirmer';
        $retPrix = $nextRetreat['prix'] ?? '';
        $retFicheUrl = $nextRetreat['fiche_url'] ?? '';
        $programmeImage = $nextRetreat['programme_image_url'] ?? '';
    ?>
    <section class="contact-map-section" style="padding: 60px 0; background-color: #f9f9f9;">
        <div class="container">
            <h2 style="font-size: 2em; font-weight: 700; margin-bottom: 30px; text-align: center;">Prochaine Retraite</h2>
            <div class="contact-grid">
                <div class="contact-info">
                    <h3 style="font-size: 1.5em; font-weight: 700; margin-bottom: 20px; color: var(--color-primary);">
                        <?php echo htmlspecialchars($nextRetreat['titre']); ?>
                    </h3>
                    <div style="margin-bottom: 15px; line-height: 1.6;">
                        <?php if (!empty($nextRetreat['description'])): ?>
                            <p style="margin-bottom: 20px;"><?php echo nl2br(htmlspecialchars($nextRetreat['description'])); ?></p>
                        <?php endif; ?>
                        
                        <div style="background: white; padding: 20px; border-radius: var(--radius); box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                            <h4 style="font-weight: 700; margin-bottom: 15px; color: var(--dark-text);">Détails pratiques</h4>
                            
                            <?php if (!empty($retDates)): ?>
                                <div style="margin-bottom: 12px;">
                                    <strong>📅 Dates :</strong><br>
                                    <?php echo htmlspecialchars($retDates); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div style="margin-bottom: 12px;">
                                <strong>📍 Lieu :</strong><br>
                                <?php echo nl2br(htmlspecialchars($retLieu)); ?>
                            </div>

                            <div style="margin-bottom: 12px;">
                                <strong>⛪ Intervenants :</strong><br>
                                <?php echo htmlspecialchars($retOrateurs); ?>
                            </div>

                            <?php if (!empty($retPrix)): ?>
                                <div style="margin-bottom: 12px;">
                                    <strong>💳 Participation :</strong><br>
                                    <?php echo htmlspecialchars($retPrix); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="separator" style="text-align: center; margin: 15px 0; color: #ccc;">· · ·</div>
                            
                            <div style="margin-top: 15px;">
                                <strong>🎯 Au programme :</strong><br>
                                Louange, Enseignements, Ateliers, Prière, Délivrance
                            </div>

                            <div style="margin-top: 20px; display: flex; flex-wrap: wrap; gap: 10px;">
                                <a href="public/retraite.php?id=<?php echo (int)$nextRetreat['id']; ?>" class="btn btn-primary" style="text-decoration: none;">
                                    ℹ️ Plus d'informations
                                </a>
                                <a href="public/inscription.php?type=retraite&amp;id=<?php echo (int)$nextRetreat['id']; ?>&amp;label=<?php echo urlencode($nextRetreat['titre']); ?>" class="btn btn-secondary" style="text-decoration: none;">
                                    🙋 Je m'inscris
                                </a>
                                <?php if (!empty($retFicheUrl)): ?>
                                    <a href="<?php echo htmlspecialchars($retFicheUrl); ?>" class="btn btn-outline" style="text-decoration: none;" download>
                                        📄 Fiche pratique
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($programmeImage)): 
                    // Normaliser le chemin pour affichage dans le navigateur
                    $img = $programmeImage;
                    // Retirer les préfixes relatifs
                    $img = preg_replace('#^(\./|/\./|\.\./)+#', '', $img);
                    // Si commence par '../' ou './' la regex ci-dessus retire ces parties

                    // Si le chemin contient 'uploads' on l'utilise tel quel (relatif)
                    if (stripos($img, 'uploads/') !== false) {
                        // garder tel quel
                        $imageSrc = $img;
                    } else {
                        // sinon, essayer d'utiliser le fichier tel quel ou comme basename sous uploads/
                        $candidate = ltrim($img, '/');
                        if ($candidate === '' ) {
                            $imageSrc = '';
                        } else {
                            // si le candidat semble être un chemin absolu (http...), on le garde
                            if (preg_match('#^https?://#i', $candidate)) {
                                $imageSrc = $candidate;
                            } else {
                                // vérifier si fichier existe dans /uploads/ avec basename
                                $base = basename($candidate);
                                $uploadsRel = 'uploads/' . $base;
                                $uploadsFull = __DIR__ . '/../' . $uploadsRel;
                                if (file_exists($uploadsFull)) {
                                    $imageSrc = $uploadsRel;
                                } else {
                                    // sinon, utiliser le candidat tel quel
                                    $imageSrc = $candidate;
                                }
                            }
                        }
                    }

                    if (!empty($imageSrc)) {
                        // ajouter timestamp pour forcer le rechargement si besoin
                        $sep = (strpos($imageSrc, '?') === false) ? '?' : '&';
                        $imageSrcWithTs = $imageSrc . $sep . 't=' . time();
                ?>
                    <div style="background: white; padding: 20px; border-radius: var(--radius); box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                        <h4 style="font-weight: 700; margin-bottom: 15px; color: var(--dark-text);">Programme détaillé</h4>
                        <div style="background: var(--light-bg); padding: 15px; border-radius: var(--radius); text-align: center;">
                            <img src="<?php echo htmlspecialchars($imageSrcWithTs); ?>" 
                                 alt="Programme de la retraite" 
                                 style="max-width: 100%; height: auto; border-radius: 4px;"
                                 onerror="console.error('Erreur de chargement de l\'image:', this.src); this.parentNode.style.display='none';">
                        </div>
                    </div>
                <?php }
                endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- events-bar moved below the gallery per user request -->

            </div>
            
        </div>
    </section>
    <!-- Section Activité principale (ajoutée) -->
    <section class="activities-section" style="padding: 60px 0; background-color: #fff;">
        <div class="container">
            <h2 style="font-size: 1.8em; font-weight:700; margin-bottom: 20px; color: var(--color-primary);">Activité principale</h2>
            <div class="grid activities-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:18px;">
                <article class="activity-card" style="background: #fff; border-radius:8px; padding:12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); text-align:left;">
                    <div class="activity-thumb" style="background-image: url('img/WhatsApp Image 2026-01-01 at 21.39.35.jpeg'); background-size:cover; background-position:center; height:140px; border-radius:6px;"></div>
                    <h3 style="margin-top:12px; margin-bottom:8px; font-size:1.05em; color:var(--dark-text);">Prière</h3>
                    <p style="margin:0; color:var(--muted);">Temps de prière personnelle et communautaire pour l'intercession, la guérison et l'élévation du cœur.</p>
                </article>

                <article class="activity-card" style="background: #fff; border-radius:8px; padding:12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); text-align:left;">
                    <div class="activity-thumb" style="background-image: url('img/WhatsApp Image 2026-01-01 at 21.44.50.jpeg'); background-size:cover; background-position:center; height:140px; border-radius:6px;"></div>
                    <h3 style="margin-top:12px; margin-bottom:8px; font-size:1.05em; color:var(--dark-text);">Enseignement</h3>
                    <p style="margin:0; color:var(--muted);">Enseignements bibliques et formations pratiques pour approfondir la foi et la vie spirituelle.</p>
                </article>

                <article class="activity-card" style="background: #fff; border-radius:8px; padding:12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); text-align:left;">
                    <div class="activity-thumb" style="background-image: url('img/WhatsApp Image 2026-01-03 at 17.00.29.jpeg'); background-size:cover; background-position:center; height:140px; border-radius:6px;"></div>
                    <h3 style="margin-top:12px; margin-bottom:8px; font-size:1.05em; color:var(--dark-text);">Nuit de contemplation</h3>
                    <p style="margin:0; color:var(--muted);">Veillée nocturne de méditation et d'adoration pour rencontrer Dieu dans le silence et la louange.</p>
                </article>

                <article class="activity-card" style="background: #fff; border-radius:8px; padding:12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); text-align:left;">
                    <div class="activity-thumb" style="background-image: url('img/WhatsApp Image 2026-01-01 at 21.37.17.jpeg'); background-size:cover; background-position:center; height:140px; border-radius:6px;"></div>
                    <h3 style="margin-top:12px; margin-bottom:8px; font-size:1.05em; color:var(--dark-text);">Carrefour d'exaucement</h3>
                    <p style="margin:0; color:var(--muted);">Espaces dédiés à la prière d'intercession et aux témoignages de délivrance et d'exaucement.</p>
                </article>

                <article class="activity-card" style="background: #fff; border-radius:8px; padding:12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); text-align:left;">
                    <div class="activity-thumb" style="background-image: url('img/WhatsApp Image 2026-01-01 at 21.39.53.jpeg'); background-size:cover; background-position:center; height:140px; border-radius:6px;"></div>
                    <h3 style="margin-top:12px; margin-bottom:8px; font-size:1.05em; color:var(--dark-text);">Atelier d'autonomie</h3>
                    <p style="margin:0; color:var(--muted);">Ateliers pratiques pour développer des compétences personnelles et spirituelles au quotidien.</p>
                </article>

                <article class="activity-card" style="background: #fff; border-radius:8px; padding:12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); text-align:left;">
                    <div class="activity-thumb" style="background-image: url('img/WhatsApp Image 2026-01-03 at 17.01.20.jpeg'); background-size:cover; background-position:center; height:140px; border-radius:6px;"></div>
                    <h3 style="margin-top:12px; margin-bottom:8px; font-size:1.05em; color:var(--dark-text);">Atelier des adolescents</h3>
                    <p style="margin:0; color:var(--muted);">Sessions adaptées aux adolescents: foi, identité, relations et autonomie dans un cadre bienveillant.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="new-sermons-section">
        <div class="container sermons-layout">
            <div class="sermon-sidebar" style="background-image: url('img/WhatsApp Image 2026-01-01 at 21.39.35.jpeg'); background-size: cover; background-position: center; border-radius:8px; min-height: 220px;"></div>
            <div class="sermons-content">
                <h2>BIBLIOTHEQUE DES ENSEIGNEMENTS</h2>
                <div class="sermon-list">
                    <article class="sermon-post">
                        <div class="sermon-thumb" style="background-image: url('img/WhatsApp Image 2026-01-03 at 17.01.20 (1).jpeg'); background-size: cover; background-position: center; min-height: 140px; border-radius: 8px;"></div>
                        <p class="date">JUIN 25, 2025</p>
                        <h3>Retrouver le chemin avec Dieu</h3>
                        <p class="author">Équipe EDEN →</p>
                        <p class="excerpt">Un enseignement pour revenir à la présence de Dieu et à Sa volonté.</p>
                    </article>

                    <article class="sermon-post">
                        <div class="sermon-thumb" style="background-image: url('img/WhatsApp Image 2026-01-01 at 21.37.13 (1).jpeg'); background-size: cover; background-position: center; min-height: 140px; border-radius: 8px;"></div>
                        <p class="date">JUIN 18, 2025</p>
                        <h3>Adorer en esprit et en vérité</h3>
                        <p class="author">Ministère EDEN →</p>
                        <p class="excerpt">Une méditation sur une adoration sincère qui transforme le cœur.</p>
                    </article>

                    <article class="sermon-post">
                        <div class="sermon-thumb" style="background-image: url('img/WhatsApp Image 2026-01-03 at 17.00.28.jpeg'); background-size: cover; background-position: center; min-height: 140px; border-radius: 8px;"></div>
                        <p class="date">JUIN 11, 2025</p>
                        <h3>Marcher avec Dieu après la retraite</h3>
                        <p class="author">Invités EDEN →</p>
                        <p class="excerpt">Des pistes concrètes pour garder le feu reçu pendant la retraite.</p>
                    </article>
                </div>
                <div class="sermon-footer-links">
                    <a href="public/enseignements.php" class="btn btn-small-red">TOUS LES ENSEIGNEMENTS</a>
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
            <?php if (empty($galleryPhotos)): ?>
                <p>Aucune photo enregistrée pour le moment. Revenez bientôt&nbsp;!</p>
            <?php else: ?>
                <?php foreach ($galleryPhotos as $p): ?>
                    <?php
                $retreatId = $p['retreat_id'] ?? null;
                $link = $retreatId ? 'public/retraite.php?id=' . (int)$retreatId : 'public/retraite.php';
                $src = $p['image_url'] ?? '';
                // Supprimer le préfixe '../' s'il existe
                if (strpos($src, '../') === 0) {
                    $src = substr($src, 3);
                }
                // Ajouter le préfixe 'uploads/' si nécessaire
                if (!empty($src) && strpos($src, 'uploads/') !== 0) {
                    $src = 'uploads/' . ltrim($src, '/');
                }
                $title = $p['titre'] ?? 'Photo de retraite';
                ?>
                    <div class="home-gallery-card" data-full="<?php echo htmlspecialchars($src); ?>" data-title="<?php echo htmlspecialchars($title); ?>" data-link="<?php echo htmlspecialchars($link); ?>">
                        <div class="home-gallery-thumb">
                            <img src="<?php echo htmlspecialchars($src); ?>" alt="<?php echo htmlspecialchars($title); ?>" loading="lazy" decoding="async">
                        </div>
                        <div class="home-gallery-caption"><?php echo htmlspecialchars(substr($title, 0, 60)); ?></div>
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
