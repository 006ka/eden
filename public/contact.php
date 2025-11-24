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
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="logo">EDEN</div>
        <nav>
            <ul>
                <li><a href="../index.php">Accueil</a></li>
                <li><a href="apropos.php">À propos</a></li>
                <li><a href="programmes.php">Programmes</a></li>
                <li><a href="retraite.php">Retraites</a></li>
                <li><a href="galerie.php">Galerie</a></li>
                <li><a class="active" href="contact.php">Contact</a></li>
            </ul>
        </nav>
    </header>

    <section class="banner">
        <h1>Nous Contacter</h1>
        <p>Nous sommes à votre disposition pour toute information.</p>
    </section>

    <section class="contact-section">
        <div class="contact-container">
            <div class="contact-form">
                <h2>Envoyer un message</h2>

                <?php if ($errorMessage !== ''): ?>
                    <p class="success-message" style="color:red; display:block;"><?= htmlspecialchars($errorMessage) ?></p>
                <?php endif; ?>

                <?php if ($successMessage !== ''): ?>
                    <p class="success-message" style="display:block;"><?= htmlspecialchars($successMessage) ?></p>
                <?php endif; ?>

                <form method="post" action="">
                    <div class="form-group">
                        <label>Nom complet *</label>
                        <input type="text" name="nom" required>
                    </div>

                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label>Téléphone *</label>
                        <input type="text" name="telephone" required>
                    </div>

                    <div class="form-group">
                        <label>Message *</label>
                        <textarea name="message" rows="5" required></textarea>
                    </div>

                    <button type="submit" class="btn primary">Envoyer</button>
                </form>
            </div>

            <div class="contact-info">
                <h2>Informations</h2>
                <p>📞 Téléphone : <strong>+243 000 000 000</strong></p>
                <p>💬 WhatsApp : <strong>+243 000 000 000</strong></p>
                <p>📧 Email : <strong>contact@eden-ministere.com</strong></p>
                <p>📍 Adresse : <strong>Kinshasa, RDC</strong></p>

                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3975.7208994986716!2d15.308054314148471!3d-4.434726551535309!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1a6a33db38b6ac43%3A0x93d3df37b2a9eb26!2sKinshasa!5e0!3m2!1sfr!2scd!4v1700000000000"
                    width="100%"
                    height="250"
                    style="border:0; border-radius:10px; margin-top:15px;"
                    allowfullscreen=""
                    loading="lazy"></iframe>
            </div>
        </div>
    </section>

    <footer>
        <p>© 2025 EDEN — Tous droits réservés.</p>
        <p>Verset du jour : <span id="verset"></span></p>
    </footer>

    <script src="../assets/js/script.js"></script>
</body>
</html>
