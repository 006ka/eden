<?php
// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Effacer toutes les variables de session
$_SESSION = array();

// Si vous voulez détruire complètement la session, effacez également le cookie de session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Détruire la session
if (session_destroy()) {
    // Rediriger vers la page de connexion avec un paramètre de déconnexion réussie
    header('Location: index.php?logout=1');
} else {
    // En cas d'échec, rediriger quand même mais avec un message d'erreur
    header('Location: index.php?logout_error=1');
}
exit();
