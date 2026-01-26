<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Si l'utilisateur est déjà connecté, le rediriger vers le tableau de bord
if (isset($_SESSION['admin_username'])) {
    header('Location: admin.php');
    exit();
}

// Gestion de la déconnexion
if (isset($_GET['logout'])) {
    // Détruire toutes les données de session
    $_SESSION = array();
    
    // Détruire le cookie de session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Détruire la session
    session_destroy();
    
    // Rediriger vers la page de connexion
    header('Location: index.php');
    exit();
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

            // Vérification du mot de passe (en clair pour le moment, à remplacer par password_verify en production)
            if ($user && $user['password'] === $password) {
                // Initialisation de la session
                $_SESSION['is_admin'] = true;
                $_SESSION['admin_username'] = $user['username'];
                
                // Mettre à jour la dernière connexion si la colonne existe
                try {
                    // Vérifier si la colonne last_login existe
                    $checkColumn = $pdo->query("SHOW COLUMNS FROM admin_users LIKE 'last_login'");
                    if ($checkColumn->rowCount() > 0) {
                        $updateStmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE username = ?");
                        $updateStmt->execute([$user['username']]);
                    }
                } catch (PDOException $e) {
                    // En cas d'erreur, on continue quand même
                    error_log('Erreur lors de la mise à jour de la dernière connexion : ' . $e->getMessage());
                }
                
                // Rediriger vers le tableau de bord
                header('Location: admin.php');
                exit();
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
