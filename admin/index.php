<?php
session_start();

// Configuration simple du mot de passe admin
$ADMIN_PASSWORD = 'edenadmin'; // À changer pour plus de sécurité

// Gestion de la déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Vérification du formulaire de connexion
if (isset($_POST['password'])) {
    if ($_POST['password'] === $ADMIN_PASSWORD) {
        $_SESSION['is_admin'] = true;
    } else {
        $login_error = 'Mot de passe incorrect';
    }
}

// Si non connecté, afficher le formulaire de login et arrêter là
if (empty($_SESSION['is_admin'])) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Admin EDEN - Connexion</title>
        <link rel="stylesheet" href="../assets/css/style.css">
        <style>
            .admin-login { max-width: 400px; margin: 80px auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
            .admin-login h1 { margin-bottom: 20px; text-align: center; }
            .admin-login input[type=password] { width: 100%; padding: 10px; margin-bottom: 15px; }
            .admin-login button { width: 100%; padding: 10px; }
            .error { color: red; margin-bottom: 10px; text-align: center; }
        </style>
    </head>
    <body>
        <div class="admin-login">
            <h1>Espace Admin</h1>
            <?php if (!empty($login_error)): ?>
                <p class="error"><?php echo htmlspecialchars($login_error); ?></p>
            <?php endif; ?>
            <form method="post">
                <label>Mot de passe admin</label>
                <input type="password" name="password" required>
                <button type="submit" class="btn primary">Se connecter</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// À partir d'ici, l'utilisateur est authentifié
// On redirige vers admin.php qui contient désormais toute la gestion (retraites, galerie, etc.)
header('Location: admin.php');
exit;
