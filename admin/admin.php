<?php
// Output buffering pour permettre les redirects même si du contenu a été envoyé
ob_start();

session_start();
require_once __DIR__ . '/../config/db.php';

// Vérification de l'authentification
if (!isset($_SESSION['admin_username'])) {
    header('Location: index.php');
    exit();
}

// Récupérer les informations de l'utilisateur connecté
$current_username = $_SESSION['admin_username'];
try {
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->execute([$current_username]);
    $admin_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin_info) {
        session_destroy();
        header('Location: index.php');
        exit();
    }

    // Vérifier et mettre à jour les champs manquants si nécessaire
    try {
        // Vérifier si la colonne last_login existe
        $checkColumn = $pdo->query("SHOW COLUMNS FROM admin_users LIKE 'last_login'");
        if ($checkColumn->rowCount() > 0) {
            $updateStmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE username = ?");
            $updateStmt->execute([$current_username]);
        }
    } catch (PDOException $e) {
        // Journaliser l'erreur mais ne pas arrêter l'exécution
        error_log('Erreur lors de la mise à jour de la dernière connexion : ' . $e->getMessage());
    }
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Statistiques globales pour le menu et certaines sections
try {
    $stats = [
        'total_retreats' => (int) $pdo->query('SELECT COUNT(*) FROM retreats')->fetchColumn(),
        'total_gallery' => (int) $pdo->query('SELECT COUNT(*) FROM gallery')->fetchColumn(),
        'total_contacts' => (int) $pdo->query("SELECT COUNT(*) FROM contacts WHERE type = 'contact' OR type IS NULL")->fetchColumn(),
        'total_partnerships' => (int) $pdo->query("SELECT COUNT(*) FROM contacts WHERE type = 'partnership'")->fetchColumn(),
        'total_inscriptions' => (int) $pdo->query('SELECT COUNT(*) FROM inscriptions')->fetchColumn(),
        'total_enseignements' => (int) $pdo->query("SELECT COUNT(*) FROM enseignements WHERE est_public = 1")->fetchColumn(),
        'total_feedbacks' => (int) $pdo->query('SELECT COUNT(*) FROM feedbacks')->fetchColumn(),
    ];
} catch (Exception $e) {
    // En cas d'erreur, on évite de casser tout l'admin
    $stats = [
        'total_retreats' => 0,
        'total_gallery' => 0,
        'total_contacts' => 0,
        'total_partnerships' => 0,
        'total_inscriptions' => 0,
        'total_enseignements' => 0,
        'total_feedbacks' => 0,
    ];
}

// Section courante
$section = isset($_GET['section']) ? preg_replace('/[^a-z_]/', '', $_GET['section']) : 'dashboard';

// Valeurs par défaut pour éviter les notices
$contacts = [];
$feedbacks = [];
$gallery = [];
$retreats = [];
$inscriptions = [];
$inscTempsFilter = '';
$uploadsStats = [
    'total' => 0,
    'referenced' => 0,
    'orphans' => [],
];
$tool_message = '';

// Vérification de la suppression d'une retraite
if (isset($_GET['delete_retreat']) && is_numeric($_GET['delete_retreat'])) {
    try {
        $retreatId = (int)$_GET['delete_retreat'];
        
        // Récupérer le chemin de l'image avant de supprimer la retraite
        $stmt = $pdo->prepare('SELECT programme_image_url FROM retreats WHERE id = ?');
        $stmt->execute([$retreatId]);
        $retreat = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($retreat) {
            // D'abord, supprimer toutes les inscriptions liées à cette retraite
            $deleteInscriptions = $pdo->prepare('DELETE FROM inscriptions WHERE event_type = "retraite" AND event_id = ?');
            $deleteInscriptions->execute([$retreatId]);
            
            // Ensuite, supprimer la retraite de la base de données
            $stmt = $pdo->prepare('DELETE FROM retreats WHERE id = ?');
            $stmt->execute([$retreatId]);
            
            // Supprimer le fichier image associé s'il existe
            if (!empty($retreat['programme_image_url'])) {
                $imagePath = __DIR__ . '/..' . $retreat['programme_image_url'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            // Redirection avec message de succès
            header('Location: admin.php?section=retreats&retreat_deleted=1');
            exit;
        }
    } catch (Exception $e) {
        $retreatError = 'Erreur lors de la suppression de la retraite : ' . $e->getMessage();
    }
}

// Logique de traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Traitement de la mise à jour du profil
    if (isset($_POST['update_profile'])) {
        try {
            $full_name = trim($_POST['full_name'] ?? '');
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
            
            // Validation
            if (!$email) {
                throw new Exception('Veuillez fournir une adresse email valide');
            }
            
            // Gestion de l'upload de la photo de profil
            $profile_image = null;
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../uploads/profiles/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (!in_array($file_extension, $allowed_types)) {
                    throw new Exception('Format de fichier non supporté. Formats acceptés : ' . implode(', ', $allowed_types));
                }
                
                // Supprimer l'ancienne image si elle existe
                if (!empty($_POST['current_profile_image'])) {
                    $old_image = __DIR__ . '/..' . $_POST['current_profile_image'];
                    if (file_exists($old_image)) {
                        unlink($old_image);
                    }
                }
                
                $file_name = 'profile_' . $_SESSION['admin_username'] . '_' . time() . '.' . $file_extension;
                $target_file = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                    $profile_image = '/uploads/profiles/' . $file_name;
                }
            } else {
                // Conserver l'image existante si aucune nouvelle n'est téléchargée
                $profile_image = $_POST['current_profile_image'] ?? null;
            }
            
            // Mise à jour dans la base de données
            $sql = 'UPDATE admin_users SET email = :email';
            $params = [
                ':email' => $email,
                ':username' => $current_username
            ];
            
            // Ajouter le nom complet si la colonne existe
            try {
                $checkColumn = $pdo->query("SHOW COLUMNS FROM admin_users LIKE 'full_name'");
                if ($checkColumn->rowCount() > 0) {
                    $sql .= ', full_name = :full_name';
                    $params[':full_name'] = $full_name ?: null;
                    $_SESSION['admin_full_name'] = $full_name;
                }
            } catch (PDOException $e) {
                // Si la colonne n'existe pas, on continue sans
                error_log('Erreur lors de la vérification de la colonne full_name : ' . $e->getMessage());
            }
            
            // Ajouter l'image de profil si elle a été téléchargée
            if ($profile_image) {
                try {
                    $checkColumn = $pdo->query("SHOW COLUMNS FROM admin_users LIKE 'profile_image'");
                    if ($checkColumn->rowCount() > 0) {
                        $sql .= ', profile_image = :profile_image';
                        $params[':profile_image'] = $profile_image;
                    }
                } catch (PDOException $e) {
                    error_log('Erreur lors de la vérification de la colonne profile_image : ' . $e->getMessage());
                }
            }
            
            $sql .= ' WHERE username = :username';
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            $successMessage = 'Profil mis à jour avec succès !';
            
        } catch (Exception $e) {
            $errorMessage = 'Erreur lors de la mise à jour du profil : ' . $e->getMessage();
        }
    }
    
    // Traitement du changement de mot de passe
    if (isset($_POST['change_password'])) {
        try {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                throw new Exception("Tous les champs sont obligatoires");
            }
            
            if ($new_password !== $confirm_password) {
                throw new Exception('Les nouveaux mots de passe ne correspondent pas');
            }
            
            if (strlen($new_password) < 8) {
                throw new Exception('Le mot de passe doit contenir au moins 8 caractères');
            }
            
            // Vérifier le mot de passe actuel
            $stmt = $pdo->prepare('SELECT password FROM admin_users WHERE username = ?');
            $stmt->execute([$_SESSION['admin_username']]);
            $user = $stmt->fetch();
            
            // Vérifier le mot de passe (dans un cas réel, il faudrait vérifier le hash)
            if ($user['password'] !== $current_password) {
                throw new Exception('Le mot de passe actuel est incorrect');
            }
            
            // Dans un cas réel, il faudrait hasher le mot de passe
            // $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Mettre à jour le mot de passe
            $stmt = $pdo->prepare('UPDATE admin_users SET password = :password WHERE username = :username');
            $stmt->execute([
                ':password' => $new_password, // À remplacer par $hashed_password en production
                ':username' => $_SESSION['admin_username']
            ]);
            
            $successMessage = 'Mot de passe mis à jour avec succès !';
            
        } catch (Exception $e) {
            $errorMessage = 'Erreur lors du changement de mot de passe : ' . $e->getMessage();
        }
    }
    
    // Traitement de l'ajout ou de la mise à jour d'une photo de la galerie
    if (isset($_POST['add_gallery']) || isset($_POST['update_gallery'])) {
        try {
            // Récupération des données du formulaire
            $titre = trim($_POST['titre'] ?? '');
            $retreat_id = !empty($_POST['retreat_id']) ? (int)$_POST['retreat_id'] : null;
            $isUpdate = isset($_POST['update_gallery']);
            $gallery_id = $isUpdate ? (int)$_POST['gallery_id'] : null;
            
            // Vérifier si une URL d'image est fournie
            $image_url = trim($_POST['image_url'] ?? '');
            
            // Gestion de l'upload de l'image si un fichier est fourni
            if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../uploads/gallery/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
                $file_name = 'gallery_' . time() . '_' . uniqid() . '.' . $file_extension;
                $target_file = $upload_dir . $file_name;
                
                // Vérification du type de fichier
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($file_extension, $allowed_types)) {
                    throw new Exception('Type de fichier non autorisé. Formats acceptés : ' . implode(', ', $allowed_types));
                }
                
                // Vérification de la taille du fichier (max 5 Mo)
                $max_size = 5 * 1024 * 1024; // 5 Mo
                if ($_FILES['image_file']['size'] > $max_size) {
                    throw new Exception('Le fichier est trop volumineux. Taille maximale : 5 Mo');
                }
                
                // Déplacement du fichier uploadé
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target_file)) {
                    $image_url = 'uploads/gallery/' . $file_name;
                    
                    // Si c'est une mise à jour, on supprime l'ancienne image si elle existe
                    if ($isUpdate && !empty($_POST['old_image_url'])) {
                        $old_image_path = __DIR__ . '/../' . $_POST['old_image_url'];
                        if (file_exists($old_image_path)) {
                            unlink($old_image_path);
                        }
                    }
                } else {
                    throw new Exception('Erreur lors de l\'upload du fichier');
                }
            } elseif ($isUpdate && empty($image_url)) {
                // En mode édition, on conserve l'URL existante si aucune nouvelle image n'est fournie
                $stmt = $pdo->prepare('SELECT image_url FROM gallery WHERE id = ?');
                $stmt->execute([$gallery_id]);
                $image_url = $stmt->fetchColumn();
            }
            
            // Validation
            if (empty($image_url)) {
                throw new Exception('Veuillez fournir une image ou une URL valide');
            }
            
            if ($isUpdate) {
                // Mise à jour dans la base de données
                $sql = 'UPDATE gallery SET titre = :titre, image_url = :image_url, retreat_id = :retreat_id';
                
                // Vérifier si la colonne updated_at existe
                $stmtCheck = $pdo->query("SHOW COLUMNS FROM gallery LIKE 'updated_at'");
                if ($stmtCheck->rowCount() > 0) {
                    $sql .= ', updated_at = NOW()';
                }
                
                $sql .= ' WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':id' => $gallery_id,
                    ':titre' => $titre ?: 'Photo sans titre',
                    ':image_url' => $image_url,
                    ':retreat_id' => $retreat_id
                ]);
                $successMessage = 'Photo mise à jour avec succès !';
            } else {
                // Insertion dans la base de données
                $sql = 'INSERT INTO gallery (titre, image_url, retreat_id) VALUES (:titre, :image_url, :retreat_id)';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':titre' => $titre ?: 'Photo sans titre',
                    ':image_url' => $image_url,
                    ':retreat_id' => $retreat_id
                ]);
                $successMessage = 'Photo ajoutée avec succès !';
            }
            
            // Redirection avec message de succès
            header('Location: admin.php?section=galerie&gallery_success=1');
            exit;
            
        } catch (Exception $e) {
            $galleryError = 'Erreur lors de l\'ajout de la photo : ' . $e->getMessage();
        }
    }
    // Traitement de l'ajout/mise à jour d'une retraite
    if (isset($_POST['add_retreat']) || isset($_POST['update_retreat'])) {
        try {
            // Récupération et validation des données du formulaire
            $titre = trim($_POST['ret_titre'] ?? '');
            $theme = trim($_POST['ret_theme'] ?? '');
            $date_debut = !empty($_POST['ret_date_debut']) ? $_POST['ret_date_debut'] : null;
            $date_fin = !empty($_POST['ret_date_fin']) ? $_POST['ret_date_fin'] : null;
            $lieu = trim($_POST['ret_lieu'] ?? '');
            $description = trim($_POST['ret_description'] ?? '');
            $prix = isset($_POST['ret_prix']) ? (float)str_replace(',', '.', $_POST['ret_prix']) : 0;
            $statut = $_POST['ret_statut'] ?? 'brouillon';
            
            // Validation minimale
            if (empty($titre)) {
                throw new Exception('Le titre est obligatoire');
            }
            
            // Gestion de l'upload de l'image si fournie
            $image_path = null;
            if (isset($_FILES['ret_image_file']) && $_FILES['ret_image_file']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../uploads/retreats/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_extension = pathinfo($_FILES['ret_image_file']['name'], PATHINFO_EXTENSION);
                $file_name = 'retraite_' . time() . '.' . $file_extension;
                $target_file = $upload_dir . $file_name;
                
                // Vérification du type de fichier
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array(strtolower($file_extension), $allowed_types)) {
                    throw new Exception('Type de fichier non autorisé. Formats acceptés : ' . implode(', ', $allowed_types));
                }
                
                // Déplacement du fichier uploadé
                if (move_uploaded_file($_FILES['ret_image_file']['tmp_name'], $target_file)) {
                    // Utiliser un chemin relatif à la racine du site
                    $image_path = 'uploads/retreats/' . $file_name;
                } else {
                    throw new Exception('Erreur lors de l\'upload du fichier');
                }
            }
            
            // Préparation de la requête SQL
            if (isset($_POST['add_retreat'])) {
                // Insertion d'une nouvelle retraite
                $sql = 'INSERT INTO retreats (titre, theme, date_debut, date_fin, lieu, description, prix' . ($image_path ? ', programme_image_url' : '') . ')';
                $sql .= ' VALUES (:titre, :theme, :date_debut, :date_fin, :lieu, :description, :prix' . ($image_path ? ', :programme_image_url' : '') . ')';
                
                $stmt = $pdo->prepare($sql);
                $params = [
                    ':titre' => $titre,
                    ':theme' => $theme ?: null,
                    ':date_debut' => $date_debut,
                    ':date_fin' => $date_fin,
                    ':lieu' => $lieu ?: null,
                    ':description' => $description ?: null,
                    ':prix' => $prix
                ];
                
                if ($image_path) {
                    $params[':programme_image_url'] = $image_path;
                }
                
                $stmt->execute($params);
                $success_message = 'La retraite a été créée avec succès !';
            } else {
                // Mise à jour d'une retraite existante
                $retreat_id = (int)$_POST['retreat_id'];
                $sql = 'UPDATE retreats SET titre = :titre, theme = :theme, date_debut = :date_debut, date_fin = :date_fin, lieu = :lieu, description = :description, prix = :prix';
                
                if ($image_path) {
                    $sql .= ', programme_image_url = :programme_image_url';
                }
                
                $sql .= ' WHERE id = :id';
                
                $stmt = $pdo->prepare($sql);
                $params = [
                    ':id' => $retreat_id,
                    ':titre' => $titre,
                    ':theme' => $theme ?: null,
                    ':date_debut' => $date_debut,
                    ':date_fin' => $date_fin,
                    ':lieu' => $lieu ?: null,
                    ':description' => $description ?: null,
                    ':prix' => $prix
                ];
                
                if ($image_path) {
                    $params[':programme_image_url'] = $image_path;
                }
                
                $stmt->execute($params);
                $success_message = 'La retraite a été mise à jour avec succès !';
            }
            
            // Redirection avec un message de succès
            header('Location: admin.php?section=retreats&retreat_created=1');
            exit;
            
        } catch (Exception $e) {
            $retreatError = 'Erreur lors de ' . (isset($_POST['add_retreat']) ? 'l\'ajout' : 'la mise à jour') . ' de la retraite : ' . $e->getMessage();
        }
    }
}

// Gestion de l'édition d'une photo de la galerie
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    try {
        $editId = (int)$_GET['edit'];
        $stmt = $pdo->prepare('SELECT * FROM gallery WHERE id = ?');
        $stmt->execute([$editId]);
        $photoToEdit = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($photoToEdit) {
            // Si on a une photo à éditer, on force le mode ajout avec les données existantes
            $_GET['add'] = 'new';
            $isAdding = true;
        }
    } catch (Exception $e) {
        $galleryError = 'Erreur lors de la récupération de la photo : ' . $e->getMessage();
    }
}

// Préparation des données selon la section (logique minimale pour éviter les erreurs)
try {
    // Chargement des retraites pour la section des retraites
    if ($section === 'retreats') {
        // Récupérer l'année de filtrage si spécifiée
        $retreatsFilterYear = isset($_GET['retreat_year']) ? (int)$_GET['retreat_year'] : null;
        
        // Charger les détails de la retraite à éditer si l'ID est fourni
        $retreatToEdit = null;
        if (isset($_GET['edit_retreat']) && is_numeric($_GET['edit_retreat'])) {
            $stmt = $pdo->prepare('SELECT * FROM retreats WHERE id = ?');
            $stmt->execute([(int)$_GET['edit_retreat']]);
            $retreatToEdit = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$retreatToEdit) {
                // Rediriger si la retraite n'existe pas
                header('Location: admin.php?section=retreats');
                exit;
            }
        }
        
        // Requête de base pour récupérer les retraites
        $sql = 'SELECT * FROM retreats';
        $params = [];
        
        // Ajout du filtre par année si spécifié
        if ($retreatsFilterYear) {
            $sql .= ' WHERE YEAR(date_debut) = :year OR YEAR(date_fin) = :year';
            $params[':year'] = $retreatsFilterYear;
        }
        
        // Trier par date de début (les plus récentes en premier)
        $sql .= ' ORDER BY date_debut DESC';
        
        // Préparation et exécution de la requête
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $retreats = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        // Récupérer la liste des années disponibles pour le filtre
        $stmt = $pdo->query('SELECT DISTINCT YEAR(date_debut) as year FROM retreats WHERE date_debut IS NOT NULL UNION SELECT DISTINCT YEAR(date_fin) as year FROM retreats WHERE date_fin IS NOT NULL ORDER BY year DESC');
        $retreatYears = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    } elseif ($section === 'contacts') {
        $stmt = $pdo->query("SELECT * FROM contacts WHERE type = 'contact' OR type IS NULL ORDER BY created_at DESC");
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } elseif ($section === 'partnerships') {
        $stmt = $pdo->query("SELECT * FROM contacts WHERE type = 'partnership' ORDER BY created_at DESC");
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } elseif ($section === 'feedbacks') {
        $stmt = $pdo->query('SELECT * FROM feedbacks ORDER BY created_at DESC');
        $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } elseif ($section === 'galerie') {
        // Suppression simple d’une image de galerie
        if (isset($_GET['delete_gallery']) && ctype_digit((string) $_GET['delete_gallery'])) {
            $id = (int) $_GET['delete_gallery'];
            $del = $pdo->prepare('DELETE FROM gallery WHERE id = ?');
            $del->execute([$id]);
            header('Location: admin.php?section=galerie&gallery_deleted=1');
            exit;
        }

        $stmt = $pdo->query('SELECT * FROM gallery ORDER BY created_at DESC');
        $gallery = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Liste des retraites pour l’association éventuelle
        $stmt = $pdo->query('SELECT id, titre FROM retreats ORDER BY date_debut DESC');
        $retreats = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } elseif ($section === 'inscriptions' || $section === 'inscriptions_gestion') {
        $inscTempsFilter = isset($_GET['insc_temps']) ? $_GET['insc_temps'] : '';

        $sql = 'SELECT * FROM inscriptions';
        if ($section === 'inscriptions_gestion' && in_array($inscTempsFilter, ['plein', 'partiel'], true)) {
            // Correspondance simple avec les libellés stockés
            $value = $inscTempsFilter === 'plein' ? 'Temps plein' : 'Temps partiel';
            $sql .= " WHERE temps_sejour = " . $pdo->quote($value);
        }
        $sql .= ' ORDER BY created_at DESC';

        $stmt = $pdo->query($sql);
        $inscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
} catch (Exception $e) {
    // On ne bloque pas l’affichage de l’admin si une requête échoue
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin EDEN - Tableau de Bord</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include 'partials/menu.php'; ?>
    
    <div class="admin-main-content">
        <div class="admin-header">
            <div class="admin-header-left">
                <img src="../Logo Initiales Nominatif Moderne Minimal Blanc Orange Noir.png" alt="EDEN" style="height:35px; width:auto; display:block;">
                <h1>Tableau de Bord</h1>
            </div>
            <div class="admin-header-right">
                <div class="admin-user">
                    <span>Bienvenue, <?php echo htmlspecialchars($admin_info['full_name'] ?? 'Admin'); ?></span>
                    <div class="admin-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="admin-info">
                        <div class="admin-name"><?php echo htmlspecialchars($admin_info['full_name'] ?? $admin_info['username']); ?></div>
                        <div class="admin-role">Administrateur</div>
                    </div>
                </div>
            </div>
            <div class="admin-header-actions">
                <a href="../index.php" target="_blank" class="btn-icon" title="Voir le site">
                    <span>👁️</span>
                </a>
                <a href="?section=account" class="btn-icon" title="Mon compte">
                    <span>⚙️</span>
                </a>
                <a href="logout.php" class="btn-icon" title="Déconnexion">
                    <span>🚪</span>
                </a>
            </div>
        </div>
    </div>

    <div class="admin-layout">
        <aside class="admin-sidebar">
            <nav>
                <?php $section = isset($_GET['section']) ? preg_replace('/[^a-z_]/','', $_GET['section']) : 'dashboard'; ?>
                <a href="?section=dashboard" class="<?php echo $section === 'dashboard' ? 'active' : ''; ?>">
                    <span>📊</span>
                    Tableau de bord
                </a>
                <a href="?section=retreats" class="<?php echo $section === 'retreats' ? 'active' : ''; ?>">
                    <span>📅</span>
                    Retraites 
                    <span class="badge"><?php echo $stats['total_retreats']; ?></span>
                </a>
                <a href="?section=galerie" class="<?php echo $section === 'galerie' ? 'active' : ''; ?>">
                    <span>🖼️</span>
                    Galerie 
                    <span class="badge"><?php echo $stats['total_gallery']; ?></span>
                </a>
                <a href="?section=inscriptions_gestion" class="<?php echo $section === 'inscriptions_gestion' ? 'active' : ''; ?>">
                    <span>✍️</span>
                    Inscriptions 
                    <span class="badge"><?php echo $stats['total_inscriptions']; ?></span>
                </a>
                <a href="?section=contacts" class="<?php echo $section === 'contacts' ? 'active' : ''; ?>">
                    <span>📞</span>
                    Contacts 
                    <span class="badge"><?php echo $stats['total_contacts']; ?></span>
                </a>
                <a href="?section=partnerships" class="<?php echo $section === 'partnerships' ? 'active' : ''; ?>">
                    <span>🤝</span>
                    Partenaires
                    <span class="badge"><?php echo $stats['total_partnerships']; ?></span>
                </a>
                <a href="?section=feedbacks" class="<?php echo $section === 'feedbacks' ? 'active' : ''; ?>">
                    <span>💬</span>
                    Feedbacks
                    <span class="badge"><?php echo $stats['total_feedbacks']; ?></span>
                </a>
                <a href="?section=horaires" class="<?php echo $section === 'horaires' ? 'active' : ''; ?>">
                    <span>⏰</span>
                    Horaires
                </a>
                <a href="?section=testimonials" class="<?php echo $section === 'testimonials' ? 'active' : ''; ?>">
                    <span>💬</span>
                    Témoignages
                </a>
                <a href="?section=enseignements" class="<?php echo $section === 'enseignements' ? 'active' : ''; ?>">
                    <span>📚</span>
                    Enseignements
                    <span class="badge"><?php echo $stats['total_enseignements']; ?></span>
                </a>
                <a href="?section=account" class="<?php echo $section === 'account' ? 'active' : ''; ?>">
                    <span>👤</span>
                    Compte
                </a>
            </nav>
        </aside>

        <main class="admin-main">
            <?php
            // Gestion de la suppression des feedbacks
            if (isset($_GET['delete_feedback']) && is_numeric($_GET['delete_feedback'])) {
                $id = (int)$_GET['delete_feedback'];
                $stmt = $pdo->prepare('DELETE FROM feedbacks WHERE id = ?');
                $stmt->execute([$id]);
                header('Location: admin.php?section=feedbacks&feedback_deleted=1');
                exit();
            }
            
            $allowed = ['dashboard','tools','programmes','retreats','galerie','contacts','partnerships','feedbacks','inscriptions','inscriptions_gestion','horaires','account','testimonials','enseignements'];
            if (!in_array($section, $allowed, true)) {
                $section = 'dashboard';
            }
            $partial = __DIR__ . '/partials/' . $section . '.php';
            if (file_exists($partial)) {
                include $partial;
            } else {
                echo '<div class="admin-section"><p>Section introuvable.</p></div>';
            }
            ?>
        </main>
    </div>

    <script>
        // Animation pour les cartes de métriques
        document.addEventListener('DOMContentLoaded', function() {
            const metricCards = document.querySelectorAll('.metric-card');
            metricCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('animate-in');
            });
        });
    </script>
</body>
</html>

<?php ob_end_flush(); ?>