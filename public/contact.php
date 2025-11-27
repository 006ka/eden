<?php
require_once __DIR__ . '/../config/db.php';

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($nom === '' || $email === '' || $telephone === '' || $message === '') {
        $errorMessage = 'Veuillez remplir tous les champs obligatoires.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO contacts (nom, email, telephone, message, created_at) VALUES (?, ?, ?, ?, NOW())');
            $stmt->execute([$nom, $email, $telephone, $message]);
            $successMessage = 'Votre message a été envoyé avec succès !';
        } catch (PDOException $e) {
            $errorMessage = "Une erreur est survenue lors de l\'enregistrement.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - EDEN</title>
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
    <?php 
        $base = '';
        include __DIR__ . '/../includes/header.php'; 
    ?>

    <!-- CONTACT INFO SECTION -->
    <section id="info" class="contact-map-section">
        <div class="container">
            <h2 style="font-size: 2em; font-weight: 700; margin-bottom: 30px;">Informations de Contact</h2>
            <div class="contact-grid">
                <div class="contact-info">
                    <h3 style="font-weight: 700; margin-bottom: 20px;">Coordonnées</h3>
                    <div style="margin-bottom: 18px;">
                        <div style="font-weight: 700; color: var(--color-primary); margin-bottom: 5px;">📞 Téléphone</div>
                        <div style="font-size: 1em; color: #333;">+243 000 000 000</div>
                    </div>
                    <div style="margin-bottom: 18px;">
                        <div style="font-weight: 700; color: var(--color-primary); margin-bottom: 5px;">💬 WhatsApp</div>
                        <div style="font-size: 1em; color: #333;">+243 000 000 000</div>
                    </div>
                    <div style="margin-bottom: 18px;">
                        <div style="font-weight: 700; color: var(--color-primary); margin-bottom: 5px;">📧 Email</div>
                        <div style="font-size: 1em; color: #333;"><a href="mailto:contact@eden-ministere.com" style="color: var(--color-primary); text-decoration: none;">contact@eden-ministere.com</a></div>
                    </div>
                    <div class="separator">· · ·</div>
                    <div style="margin-top: 18px;">
                        <div style="font-weight: 700; color: var(--color-primary); margin-bottom: 5px;">📍 Adresse</div>
                        <div style="font-size: 1em; color: #333;">Kinshasa, RDC</div>
                    </div>
                </div>
                <div>
                    <h3 style="font-weight: 700; margin-bottom: 15px;">Localisation</h3>
                    <div style="border-radius:8px; overflow:hidden; box-shadow:0 6px 18px rgba(0,0,0,0.08);">
                        <div style="width:100%; height:260px; background:#f0f0f0;">
                            <!-- OpenStreetMap embed - mêmes coordonnées que la page d'accueil -->
                            <iframe
                                src="https://www.openstreetmap.org/export/embed.html?bbox=27.53008%2C-11.64649%2C27.54008%2C-11.63649&layer=mapnik&marker=-11.64149%2C27.53508"
                                width="100%" height="100%" style="border:0;"
                                allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECONDARY BLOCKS - Why Contact Us -->
    <section class="secondary-blocks">
        <div class="container">
            <h2 style="font-size: 1.6em; font-weight: 700; margin-bottom: 20px; color: var(--dark-text);">Pourquoi nous contacter ?</h2>
            <div class="block-grid">
                <div class="block" style="background-color: var(--color-primary);">
                    <h3>❓ Question</h3>
                    <p style="font-size: 0.95em;">Sur nos activités</p>
                </div>
                <div class="block" style="background-color: var(--color-primary-dark);">
                    <h3>📋 Inscription</h3>
                    <p style="font-size: 0.95em;">Pour participer</p>
                </div>
                <div class="block" style="background-color: var(--color-primary-light);">
                    <h3>🤝 Partenariat</h3>
                    <p style="font-size: 0.95em;">Collaboration</p>
                </div>
                <div class="block" style="background-color: var(--color-secondary);">
                    <h3>💬 Feedback</h3>
                    <p style="font-size: 0.95em;">Vos suggestions</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTACT FORM SECTION -->
    <section id="contact-form" class="contact-map-section" style="background: var(--bg);">
        <div class="container">
            <h2 style="font-size: 2em; font-weight: 700; margin-bottom: 30px;">Envoyer un Message</h2>
            
            <?php if ($errorMessage !== ''): ?>
                <div style="background: #fee; border: 1px solid #fcc; padding: 12px; border-radius: 6px; margin-bottom: 20px; color: #c33;">
                    <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>

            <?php if ($successMessage !== ''): ?>
                <div style="background: #efe; border: 1px solid #cfc; padding: 12px; border-radius: 6px; margin-bottom: 20px; color: #3a3;">
                    <?= htmlspecialchars($successMessage) ?>
                </div>
            <?php endif; ?>

            <div style="max-width: 700px; background: white; padding: 30px; border-radius: var(--radius); box-shadow: 0 2px 12px rgba(0,0,0,0.08);">
                <form method="post" action="">
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">Nom complet *</label>
                        <input type="text" name="nom" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 1em;">
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">Email *</label>
                        <input type="email" name="email" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 1em;">
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">Téléphone *</label>
                        <input type="text" name="telephone" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 1em;">
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">Message *</label>
                        <textarea name="message" rows="6" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 1em; font-family: inherit;"></textarea>
                    </div>

                    <button type="submit" class="btn btn-red" style="padding: 12px 30px; font-weight: 600; font-size: 1em; cursor: pointer; border: none;">Envoyer le message</button>
                </form>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
