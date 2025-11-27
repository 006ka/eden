<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Redirige immédiatement vers la nouvelle interface admin
header('Location: admin.php');
exit;

// Gestion de la déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Vérification du formulaire de connexion
if (isset($_POST['username'], $_POST['password'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username === '' || $password === '') {
        $login_error = 'Veuillez renseigner le nom d\'utilisateur et le mot de passe.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Ici le mot de passe est stocké en clair dans la base (edenadmin)
            if ($user && $user['password'] === $password) {
                $_SESSION['is_admin'] = true;
                $_SESSION['admin_username'] = $user['username'];
            } else {
                $login_error = 'Identifiants incorrects';
            }
        } catch (PDOException $e) {
            $login_error = 'Erreur de connexion à la base de données : ' . $e->getMessage();
        }
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
                <label>Nom d'utilisateur</label>
                <input type="text" name="username" required>
                <label>Mot de passe</label>
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
