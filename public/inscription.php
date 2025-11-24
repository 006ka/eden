<?php
require_once __DIR__ . '/../config/db.php';

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $eglise = trim($_POST['eglise'] ?? '');
    $besoins = trim($_POST['besoins'] ?? '');

    if ($nom === '' || $email === '' || $telephone === '' || $age === '') {
        $errorMessage = 'Veuillez remplir tous les champs obligatoires.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO inscriptions (nom, email, telephone, age, eglise, besoins, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
            $stmt->execute([$nom, $email, $telephone, $age, $eglise, $besoins]);
            $successMessage = "Votre inscription a été enregistrée avec succès !";
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
    <title>Inscription Retraite - EDEN</title>
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
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </nav>
    </header>

    <section class="banner">
        <h1>Inscription à la Retraite</h1>
        <p>Remplissez le formulaire ci-dessous pour participer.</p>
    </section>

    <section class="form-section">
        <h2>Formulaire d'inscription</h2>

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
                <label>Âge *</label>
                <input type="number" name="age" min="10" required>
            </div>

            <div class="form-group">
                <label>Église / Communauté (optionnel)</label>
                <input type="text" name="eglise">
            </div>

            <div class="form-group">
                <label>Besoin particulier ? (santé, allergies, régime...)</label>
                <textarea name="besoins" rows="4"></textarea>
            </div>

            <button type="submit" class="btn primary">Valider l'inscription</button>
        </form>
    </section>

    <footer>
        <p>© 2025 EDEN — Tous droits réservés.</p>
        <p>Verset du jour : <span id="verset"></span></p>
    </footer>

    <script src="../assets/js/script.js"></script>
</body>
</html>
