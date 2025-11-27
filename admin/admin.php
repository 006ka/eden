<?php
$session_started = session_status() === PHP_SESSION_ACTIVE;
if (!$session_started) session_start();

// Legacy fallback password used only to seed the first admin account during migration
$LEGACY_ADMIN_PASSWORD = 'edenadmin'; // change this locally if needed
$galleryError = '';
$retreatError = '';

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

// Ensure admins table exists and seed default admin when empty
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        email VARCHAR(255) DEFAULT NULL,
        display_name VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $countStmt = $pdo->query('SELECT COUNT(*) as c FROM admins');
    $countRow = $countStmt->fetch(PDO::FETCH_ASSOC);
    if ($countRow && (int)$countRow['c'] === 0) {
        // seed a default admin using the legacy password
        $hash = password_hash($LEGACY_ADMIN_PASSWORD, PASSWORD_DEFAULT);
        $pdo->prepare('INSERT INTO admins (username, password_hash, display_name, email) VALUES (?, ?, ?, ?)')
            ->execute(['admin', $hash, 'Administrateur', null]);
    }
} catch (PDOException $e) {
    // If DB not available for some reason, continue with legacy fallback later
}

// Handle login (username + password)
if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $authOk = false;
    if ($username !== '') {
        $stmt = $pdo->prepare('SELECT id, username, password_hash FROM admins WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && password_verify($password, $row['password_hash'])) {
            $_SESSION['is_admin'] = true;
            $_SESSION['admin_id'] = (int)$row['id'];
            $authOk = true;
        }
    }

    // Fallback: if no DB/auth matched, allow legacy password (for older installs)
    if (!$authOk && isset($LEGACY_ADMIN_PASSWORD) && $password === $LEGACY_ADMIN_PASSWORD) {
        $_SESSION['is_admin'] = true;
        // try to set admin_id to first admin if available
        try {
            $r = $pdo->query('SELECT id FROM admins ORDER BY id ASC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
            if ($r) $_SESSION['admin_id'] = (int)$r['id'];
        } catch (Exception $e) {}
        $authOk = true;
    }

    if (!$authOk) {
        $login_error = 'Nom d\'utilisateur ou mot de passe incorrect.';
    }
}

if (empty($_SESSION['is_admin'])) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Admin EDEN - Connexion</title>
        <link rel="stylesheet" href="../assets/css/main.css">
        <link rel="stylesheet" href="../assets/css/admin.css">
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
                <button type="submit" name="login" class="btn primary">Se connecter</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

require_once __DIR__ . '/../config/db.php';

// Helper: redimensionne une image en utilisant GD, préserve ratio
if (!function_exists('resize_image')) {
    function resize_image($src, $dest, $maxWidth, $maxHeight, $quality = 85) {
        if (!file_exists($src)) return false;
        $info = getimagesize($src);
        if ($info === false) return false;
        list($width, $height, $type) = $info;

        $ratio = $width / $height;
        if ($width <= $maxWidth && $height <= $maxHeight) {
            // pas besoin de redimensionner, copier si dest différent
            if ($src !== $dest) {
                return copy($src, $dest);
            }
            return true;
        }

        if ($maxWidth / $maxHeight > $ratio) {
            $newHeight = $maxHeight;
            $newWidth = intval($maxHeight * $ratio);
        } else {
            $newWidth = $maxWidth;
            $newHeight = intval($maxWidth / $ratio);
        }

        switch ($type) {
            case IMAGETYPE_JPEG:
                $srcImg = imagecreatefromjpeg($src);
                break;
            case IMAGETYPE_PNG:
                $srcImg = imagecreatefrompng($src);
                break;
            case IMAGETYPE_GIF:
                $srcImg = imagecreatefromgif($src);
                break;
            case IMAGETYPE_WEBP:
                if (function_exists('imagecreatefromwebp')) {
                    $srcImg = imagecreatefromwebp($src);
                } else {
                    return false;
                }
                break;
            default:
                return false;
        }

        $dstImg = imagecreatetruecolor($newWidth, $newHeight);
        // preserve transparency for PNG/GIF
        if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
            imagecolortransparent($dstImg, imagecolorallocatealpha($dstImg, 0, 0, 0, 127));
            imagealphablending($dstImg, false);
            imagesavealpha($dstImg, true);
        }

        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        $ok = false;
        switch ($type) {
            case IMAGETYPE_JPEG:
                $ok = imagejpeg($dstImg, $dest, $quality);
                break;
            case IMAGETYPE_PNG:
                // quality for png: 0 (no compression) - 9
                $pngLevel = 9 - floor($quality / 11); // map 0-100 to 9-0
                $ok = imagepng($dstImg, $dest, $pngLevel);
                break;
            case IMAGETYPE_GIF:
                $ok = imagegif($dstImg, $dest);
                break;
            case IMAGETYPE_WEBP:
                if (function_exists('imagewebp')) {
                    $ok = imagewebp($dstImg, $dest, $quality);
                }
                break;
        }

        imagedestroy($srcImg);
        imagedestroy($dstImg);
        return $ok;
    }
}


// Gestion simple des retraites (ajout)
if (isset($_POST['add_retreat'])) {
    $titre = trim($_POST['ret_titre'] ?? '');
    $theme = trim($_POST['ret_theme'] ?? '');
    $date_debut = trim($_POST['ret_date_debut'] ?? '');
    $date_fin = trim($_POST['ret_date_fin'] ?? '');
    $lieu = trim($_POST['ret_lieu'] ?? '');
    $orateurs = trim($_POST['ret_orateurs'] ?? '');
    $description = trim($_POST['ret_description'] ?? '');
    $prix = trim($_POST['ret_prix'] ?? '');
    $programme_image_url = '';
    $fiche_url = '';

    // Traitement éventuel d'une image de programme uploadée
    if (!empty($_FILES['ret_programme_image_file']['name']) && $_FILES['ret_programme_image_file']['error'] === UPLOAD_ERR_OK) {
        $maxSize = 2 * 1024 * 1024; // 2 Mo
        if ($_FILES['ret_programme_image_file']['size'] > $maxSize) {
            $retreatError = 'Fichier trop volumineux (max 2 Mo).';
        } else {
            $uploadDir = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . 'uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $originalName = basename($_FILES['ret_programme_image_file']['name']);
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowed, true)) {
                $retreatError = 'Type de fichier non autorisé. Formats acceptés : jpg, jpeg, png, gif, webp.';
            } else {
                $imageInfo = @getimagesize($_FILES['ret_programme_image_file']['tmp_name']);
                if ($imageInfo === false) {
                    $retreatError = 'Le fichier sélectionné ne semble pas être une image valide.';
                } else {
                    $newName = 'programme_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
                    $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $newName;

                    if (move_uploaded_file($_FILES['ret_programme_image_file']['tmp_name'], $targetPath)) {
                        // Redimensionne l'image du programme (max 1200x1200)
                        resize_image($targetPath, $targetPath, 1200, 1200, 85);
                        // Chemin utilisé côté web depuis /public/retraite.php
                        $programme_image_url = '../uploads/' . $newName;
                    } else {
                        $retreatError = "Erreur lors de l'upload du fichier.";
                    }
                }
            }
        }
    }

    if ($titre === '') {
        $retreatError = 'Le titre de la retraite est obligatoire.';
    }

    // Upload éventuel d'une fiche pratique (PDF ou image)
    if ($retreatError === '' && !empty($_FILES['ret_fiche_file']['name']) && $_FILES['ret_fiche_file']['error'] === UPLOAD_ERR_OK) {
        $maxSize = 4 * 1024 * 1024; // 4 Mo
        if ($_FILES['ret_fiche_file']['size'] > $maxSize) {
            $retreatError = 'Fiche pratique trop volumineuse (max 4 Mo).';
        } else {
            $uploadDir = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . 'uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $originalName = basename($_FILES['ret_fiche_file']['name']);
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowedDocs = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowedDocs, true)) {
                $retreatError = 'Type de fichier pour la fiche non autorisé. Formats acceptés : pdf, jpg, jpeg, png, gif, webp.';
            } else {
                $newName = 'fiche_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
                $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $newName;

                if (move_uploaded_file($_FILES['ret_fiche_file']['tmp_name'], $targetPath)) {
                    $fiche_url = '../uploads/' . $newName;
                } else {
                    $retreatError = "Erreur lors de l'upload de la fiche pratique.";
                }
            }
        }
    }

    if ($retreatError === '') {
        $stmt = $pdo->prepare('INSERT INTO retreats (titre, theme, date_debut, date_fin, lieu, orateurs, prix, description, programme_image_url, fiche_url, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            $titre,
            $theme !== '' ? $theme : null,
            $date_debut !== '' ? $date_debut : null,
            $date_fin !== '' ? $date_fin : null,
            $lieu !== '' ? $lieu : null,
            $orateurs !== '' ? $orateurs : null,
            $prix !== '' ? $prix : null,
            $description !== '' ? $description : null,
            $programme_image_url !== '' ? $programme_image_url : null,
            $fiche_url !== '' ? $fiche_url : null,
        ]);
        header('Location: admin.php?section=retreats&retreat_success=1');
        exit;
    }
}

// Mise à jour complète d'une retraite existante
if (isset($_POST['update_retreat'])) {
    $id = isset($_POST['retreat_id']) ? (int) $_POST['retreat_id'] : 0;
    $titre = trim($_POST['ret_titre'] ?? '');
    $theme = trim($_POST['ret_theme'] ?? '');
    $date_debut = trim($_POST['ret_date_debut'] ?? '');
    $date_fin = trim($_POST['ret_date_fin'] ?? '');
    $lieu = trim($_POST['ret_lieu'] ?? '');
    $orateurs = trim($_POST['ret_orateurs'] ?? '');
    $description = trim($_POST['ret_description'] ?? '');
    $prix = trim($_POST['ret_prix'] ?? '');

    if ($id <= 0) {
        $retreatError = 'Retraite introuvable.';
    }

    // Récupère l'image actuelle et la fiche actuelle
    $programme_image_url = null;
    $fiche_url = null;
    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT programme_image_url, fiche_url FROM retreats WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $programme_image_url = $row['programme_image_url'];
            $fiche_url = $row['fiche_url'];
        }
    }

    // Traitement éventuel d'une nouvelle image de programme
    if (!empty($_FILES['ret_programme_image_file']['name']) && $_FILES['ret_programme_image_file']['error'] === UPLOAD_ERR_OK) {
        $maxSize = 2 * 1024 * 1024; // 2 Mo
        if ($_FILES['ret_programme_image_file']['size'] > $maxSize) {
            $retreatError = 'Fichier trop volumineux (max 2 Mo).';
        } else {
            $uploadDir = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . 'uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $originalName = basename($_FILES['ret_programme_image_file']['name']);
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowed, true)) {
                $retreatError = 'Type de fichier non autorisé. Formats acceptés : jpg, jpeg, png, gif, webp.';
            } else {
                $imageInfo = @getimagesize($_FILES['ret_programme_image_file']['tmp_name']);
                if ($imageInfo === false) {
                    $retreatError = 'Le fichier sélectionné ne semble pas être une image valide.';
                } else {
                    $newName = 'programme_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
                    $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $newName;

                    if (move_uploaded_file($_FILES['ret_programme_image_file']['tmp_name'], $targetPath)) {
                        // Redimensionne l'image du programme (max 1200x1200)
                        resize_image($targetPath, $targetPath, 1200, 1200, 85);
                        // Chemin utilisé côté web depuis /public/retraite.php
                        $programme_image_url = '../uploads/' . $newName;
                    } else {
                        $retreatError = "Erreur lors de l'upload du fichier.";
                    }
                }
            }
        }
    }

    // Traitement éventuel d'une nouvelle fiche pratique
    if ($retreatError === '' && !empty($_FILES['ret_fiche_file']['name']) && $_FILES['ret_fiche_file']['error'] === UPLOAD_ERR_OK) {
        $maxSize = 4 * 1024 * 1024; // 4 Mo
        if ($_FILES['ret_fiche_file']['size'] > $maxSize) {
            $retreatError = 'Fiche pratique trop volumineuse (max 4 Mo).';
        } else {
            $uploadDir = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . 'uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $originalName = basename($_FILES['ret_fiche_file']['name']);
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowedDocs = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowedDocs, true)) {
                $retreatError = 'Type de fichier pour la fiche non autorisé. Formats acceptés : pdf, jpg, jpeg, png, gif, webp.';
            } else {
                $newName = 'fiche_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
                $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $newName;

                if (move_uploaded_file($_FILES['ret_fiche_file']['tmp_name'], $targetPath)) {
                    $fiche_url = '../uploads/' . $newName;
                } else {
                    $retreatError = "Erreur lors de l'upload de la fiche pratique.";
                }
            }
        }
    }

    if ($titre === '') {
        $retreatError = 'Le titre de la retraite est obligatoire.';
    }

    if ($retreatError === '') {
        $stmt = $pdo->prepare('UPDATE retreats SET titre = ?, theme = ?, date_debut = ?, date_fin = ?, lieu = ?, orateurs = ?, prix = ?, description = ?, programme_image_url = ?, fiche_url = ? WHERE id = ?');
        $stmt->execute([
            $titre,
            $theme !== '' ? $theme : null,
            $date_debut !== '' ? $date_debut : null,
            $date_fin !== '' ? $date_fin : null,
            $lieu !== '' ? $lieu : null,
            $orateurs !== '' ? $orateurs : null,
            $prix !== '' ? $prix : null,
            $description !== '' ? $description : null,
            $programme_image_url !== '' ? $programme_image_url : null,
            $fiche_url !== '' ? $fiche_url : null,
            $id,
        ]);
        header('Location: admin.php?section=retreats&retreat_success=1');
        exit;
    }
}

if (isset($_GET['delete_retreat'])) {
    $id = (int) $_GET['delete_retreat'];
    $pdo->prepare('DELETE FROM retreats WHERE id = ?')->execute([$id]);
    header('Location: admin.php?section=retreats&retreat_deleted=1');
    exit;
}

// Gestion suppressions contacts / inscriptions
if (isset($_GET['delete_contact'])) {
    $id = (int) $_GET['delete_contact'];
    $pdo->prepare('DELETE FROM contacts WHERE id = ?')->execute([$id]);
    header('Location: admin.php#contacts');
    exit;
}

if (isset($_GET['delete_inscription'])) {
    $id = (int) $_GET['delete_inscription'];
    $pdo->prepare('DELETE FROM inscriptions WHERE id = ?')->execute([$id]);
    header('Location: admin.php#inscriptions');
    exit;
}

// Gestion galerie : ajout (URL ou upload) et suppression
if (isset($_POST['add_gallery'])) {
    $image_url = trim($_POST['image_url'] ?? '');
    $titre = trim($_POST['titre'] ?? '');
    $retreat_id = isset($_POST['retreat_id']) && $_POST['retreat_id'] !== '' ? (int) $_POST['retreat_id'] : null;

    // Si un fichier est uploadé, on le traite en priorité
    if (!empty($_FILES['image_file']['name']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $maxSize = 2 * 1024 * 1024; // 2 Mo
        if ($_FILES['image_file']['size'] > $maxSize) {
            $galleryError = 'Fichier trop volumineux (max 2 Mo).';
        } else {
            $uploadDir = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . 'uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $originalName = basename($_FILES['image_file']['name']);
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowed, true)) {
                $galleryError = 'Type de fichier non autorisé. Formats acceptés : jpg, jpeg, png, gif, webp.';
            } else {
                // Vérification que le fichier est bien une image
                $imageInfo = @getimagesize($_FILES['image_file']['tmp_name']);
                if ($imageInfo === false) {
                    $galleryError = 'Le fichier sélectionné ne semble pas être une image valide.';
                } else {
                    $newName = 'img_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
                    $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $newName;

                    if (move_uploaded_file($_FILES['image_file']['tmp_name'], $targetPath)) {
                        // Redimensionne l'image principale (max 1200x1200) et crée une vignette
                        $resizedOk = resize_image($targetPath, $targetPath, 1200, 1200, 85);
                        $thumbPath = $uploadDir . DIRECTORY_SEPARATOR . 'thumb_' . $newName;
                        $thumbOk = resize_image($targetPath, $thumbPath, 400, 300, 80);

                        // Chemin utilisé côté web depuis /public/galerie.php
                        $image_url = '../uploads/' . $newName;
                        // note: vignette si disponible sera nommée ../uploads/thumb_$newName
                    } else {
                        $galleryError = "Erreur lors de l'upload du fichier.";
                    }
                }
            }
        }
    }

    if ($image_url === '' && $galleryError === '') {
        $galleryError = "Veuillez fournir soit une URL d'image, soit un fichier à uploader.";
    }

    if ($galleryError === '' && $image_url !== '') {
        $stmt = $pdo->prepare('INSERT INTO gallery (image_url, titre, retreat_url, retreat_id, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->execute([
            $image_url,
            $titre !== '' ? $titre : null,
            null,
            $retreat_id
        ]);
        header('Location: admin.php?section=galerie&gallery_success=1');
        exit;
    }
}

if (isset($_GET['delete_gallery'])) {
    $id = (int) $_GET['delete_gallery'];
    $pdo->prepare('DELETE FROM gallery WHERE id = ?')->execute([$id]);
    header('Location: admin.php?section=galerie&gallery_deleted=1');
    exit;
}

// Gestion des programmes annuels (ajout)
if (isset($_POST['add_programme'])) {
    $mois = trim($_POST['prog_mois'] ?? '');
    $titre = trim($_POST['prog_titre'] ?? '');
    $theme = trim($_POST['prog_theme'] ?? '');
    $details = trim($_POST['prog_details'] ?? '');
    $prix = trim($_POST['prog_prix'] ?? '');
    $date_debut = trim($_POST['prog_date_debut'] ?? '');
    $date_fin = trim($_POST['prog_date_fin'] ?? '');
    $ordre = isset($_POST['prog_ordre']) && $_POST['prog_ordre'] !== '' ? (int) $_POST['prog_ordre'] : 0;

    // Upload éventuel d'une affiche d'image
    $affiche_url = '';
    if (!empty($_FILES['prog_affiche_file']['name']) && $_FILES['prog_affiche_file']['error'] === UPLOAD_ERR_OK) {
        $maxSize = 2 * 1024 * 1024; // 2 Mo
        if ($_FILES['prog_affiche_file']['size'] > $maxSize) {
            $galleryError = 'Fichier trop volumineux (max 2 Mo).';
        } else {
            $uploadDir = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . 'uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $originalName = basename($_FILES['prog_affiche_file']['name']);
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowed, true)) {
                $galleryError = 'Type de fichier non autorisé. Formats acceptés : jpg, jpeg, png, gif, webp.';
            } else {
                $imageInfo = @getimagesize($_FILES['prog_affiche_file']['tmp_name']);
                if ($imageInfo === false) {
                    $galleryError = 'Le fichier sélectionné ne semble pas être une image valide.';
                } else {
                    $newName = 'programme_affiche_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
                    $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $newName;

                    if (move_uploaded_file($_FILES['prog_affiche_file']['tmp_name'], $targetPath)) {
                        // Redimensionne l'affiche (max 1200x1200)
                        resize_image($targetPath, $targetPath, 1200, 1200, 85);
                        // Chemin utilisé côté web
                        $affiche_url = '../uploads/' . $newName;
                    } else {
                        $galleryError = "Erreur lors de l'upload du fichier.";
                    }
                }
            }
        }
    }

    if ($mois !== '' && $titre !== '') {
        $stmt = $pdo->prepare('INSERT INTO programmes (mois, titre, theme, details, prix, affiche_url, date_debut, date_fin, ordre, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            $mois,
            $titre,
            $theme !== '' ? $theme : null,
            $details !== '' ? $details : null,
            $prix !== '' ? $prix : null,
            $affiche_url !== '' ? $affiche_url : null,
            $date_debut !== '' ? $date_debut : null,
            $date_fin !== '' ? $date_fin : null,
            $ordre,
        ]);
    }
    header('Location: admin.php?section=programmes&programme_success=1');
    exit;
}

if (isset($_GET['delete_programme'])) {
    $id = (int) $_GET['delete_programme'];
    $pdo->prepare('DELETE FROM programmes WHERE id = ?')->execute([$id]);
    header('Location: admin.php?section=programmes&programme_deleted=1');
    exit;
}

if (isset($_POST['update_programme'])) {
    $id = isset($_POST['programme_id']) ? (int) $_POST['programme_id'] : 0;
    $mois = trim($_POST['prog_mois'] ?? '');
    $titre = trim($_POST['prog_titre'] ?? '');
    $theme = trim($_POST['prog_theme'] ?? '');
    $details = trim($_POST['prog_details'] ?? '');
    $date_debut = trim($_POST['prog_date_debut'] ?? '');
    $date_fin = trim($_POST['prog_date_fin'] ?? '');
    $ordre = isset($_POST['prog_ordre']) && $_POST['prog_ordre'] !== '' ? (int) $_POST['prog_ordre'] : 0;

    if ($id > 0 && $mois !== '' && $titre !== '') {
        // Récupère l'affiche actuelle
        $affiche_url = null;
        $stmt = $pdo->prepare('SELECT affiche_url FROM programmes WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $affiche_url = $row['affiche_url'];
        }

        // Nouvelle affiche éventuelle
        if (!empty($_FILES['prog_affiche_file']['name']) && $_FILES['prog_affiche_file']['error'] === UPLOAD_ERR_OK) {
            $maxSize = 2 * 1024 * 1024; // 2 Mo
            if ($_FILES['prog_affiche_file']['size'] <= $maxSize) {
                $uploadDir = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . 'uploads';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $originalName = basename($_FILES['prog_affiche_file']['name']);
                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (in_array($ext, $allowed, true)) {
                    $imageInfo = @getimagesize($_FILES['prog_affiche_file']['tmp_name']);
                    if ($imageInfo !== false) {
                        $newName = 'programme_affiche_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
                        $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $newName;

                        if (move_uploaded_file($_FILES['prog_affiche_file']['tmp_name'], $targetPath)) {
                            resize_image($targetPath, $targetPath, 1200, 1200, 85);
                            $affiche_url = '../uploads/' . $newName;
                        }
                    }
                }
            }
        }

        $stmt = $pdo->prepare('UPDATE programmes SET mois = ?, titre = ?, theme = ?, details = ?, prix = ?, affiche_url = ?, date_debut = ?, date_fin = ?, ordre = ? WHERE id = ?');
        $stmt->execute([
            $mois,
            $titre,
            $theme !== '' ? $theme : null,
            $details !== '' ? $details : null,
            $prix !== '' ? $prix : null,
            $affiche_url !== '' ? $affiche_url : null,
            $date_debut !== '' ? $date_debut : null,
            $date_fin !== '' ? $date_fin : null,
            $ordre,
            $id,
        ]);
    }
    header('Location: admin.php?section=programmes');
    exit;
}

// Outil admin: régénérer miniatures depuis l'interface (POST)
$tool_message = '';
if (isset($_POST['run_thumbs'])) {
    $resizeOriginals = !empty($_POST['resize_originals']);
    $uploadDir = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($uploadDir)) {
        $tool_message = 'Dossier uploads introuvable.';
    } else {
        $allowedExt = ['jpg','jpeg','png','gif','webp'];
        $files = scandir($uploadDir);
        $created = 0; $total = 0; $errors = 0;
        foreach ($files as $f) {
            if ($f === '.' || $f === '..') continue;
            if (strpos($f, 'thumb_') === 0) continue;
            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt, true)) continue;
            $total++;
            $full = $uploadDir . DIRECTORY_SEPARATOR . $f;
            if (!is_file($full)) continue;
            $thumbFs = $uploadDir . DIRECTORY_SEPARATOR . 'thumb_' . $f;
            if (!file_exists($thumbFs)) {
                $ok = resize_image($full, $thumbFs, 400, 300, 80);
                if ($ok) { $created++; } else { $errors++; }
            }
            if ($resizeOriginals) {
                $bak = $full . '.bak';
                if (!file_exists($bak)) copy($full, $bak);
                $ok2 = resize_image($full, $full, 1200, 1200, 85);
                if (!$ok2) $errors++;
            }
        }
        $tool_message = "Traitement : fichiers analysés={$total}, vignettes créées={$created}, erreurs={$errors}.";
    }
}

// Contacts & inscriptions
$contacts = $pdo->query('SELECT * FROM contacts ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);

// Filtre inscriptions par temps de présence (GET insc_temps)
$inscTempsFilter = isset($_GET['insc_temps']) ? trim($_GET['insc_temps']) : '';
if ($inscTempsFilter === 'plein' || $inscTempsFilter === 'partiel') {
    $val = ($inscTempsFilter === 'plein') ? 'Temps plein' : 'Temps partiel';
    $stmt = $pdo->prepare('SELECT * FROM inscriptions WHERE temps_sejour = ? ORDER BY created_at DESC');
    $stmt->execute([$val]);
    $inscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $inscriptions = $pdo->query('SELECT * FROM inscriptions ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
}

// Galerie
$gallery = $pdo->query('SELECT * FROM gallery ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);

// Aperçu des fichiers uploads pour l'outil d'administration (section tools)
$uploadsStats = null;
$uploadDirFs = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . 'uploads';
if ($uploadDirFs && is_dir($uploadDirFs)) {
    $allFiles = scandir($uploadDirFs);
    $files = [];
    foreach ($allFiles as $f) {
        if ($f === '.' || $f === '..') continue;
        $full = $uploadDirFs . DIRECTORY_SEPARATOR . $f;
        if (is_file($full)) $files[] = $f;
    }

    // Récupère les chemins en base
    $refNames = [];

    $gpaths = $pdo->query('SELECT image_url FROM gallery')->fetchAll(PDO::FETCH_COLUMN);
    foreach ($gpaths as $p) { if ($p) $refNames[] = basename($p); }

    $rpaths = $pdo->query('SELECT programme_image_url, fiche_url FROM retreats')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rpaths as $row) {
        foreach (['programme_image_url','fiche_url'] as $k) {
            if (!empty($row[$k])) $refNames[] = basename($row[$k]);
        }
    }

    $ppaths = $pdo->query('SELECT affiche_url FROM programmes')->fetchAll(PDO::FETCH_COLUMN);
    foreach ($ppaths as $p) { if ($p) $refNames[] = basename($p); }

    $tpaths = $pdo->query('SELECT image_url FROM testimonials')->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tpaths as $p) { if ($p) $refNames[] = basename($p); }

    $refSet = [];
    foreach ($refNames as $name) {
        if ($name !== '') $refSet[$name] = true;
    }

    $orphans = [];
    $referencedCount = 0;
    foreach ($files as $f) {
        if (isset($refSet[$f])) {
            $referencedCount++;
        } else {
            $orphans[] = $f;
        }
    }

    $uploadsStats = [
        'total' => count($files),
        'referenced' => $referencedCount,
        'orphans' => $orphans,
    ];
}

// --- Retraites: récupère liste d'années disponibles et résultats filtrés (optionnel)
$retreatYears = [];
$yrs = $pdo->query("SELECT DISTINCT YEAR(COALESCE(date_debut,date_fin)) AS y FROM retreats WHERE date_debut IS NOT NULL OR date_fin IS NOT NULL ORDER BY y DESC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($yrs as $row) {
    if (!empty($row['y'])) $retreatYears[] = (int)$row['y'];
}

$retreatsFilterYear = isset($_GET['retreat_year']) && is_numeric($_GET['retreat_year']) ? (int)$_GET['retreat_year'] : null;
if ($retreatsFilterYear) {
    $stmt = $pdo->prepare('SELECT * FROM retreats WHERE (YEAR(date_debut) = ? OR YEAR(date_fin) = ?) ORDER BY date_debut IS NULL, date_debut ASC, id ASC');
    $stmt->execute([$retreatsFilterYear, $retreatsFilterYear]);
    $retreats = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $retreats = $pdo->query('SELECT * FROM retreats ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
}

// --- Programmes: années disponibles et filtrage
$programmeYears = [];
$pyrs = $pdo->query("SELECT DISTINCT YEAR(COALESCE(date_debut,date_fin)) AS y FROM programmes WHERE date_debut IS NOT NULL OR date_fin IS NOT NULL ORDER BY y DESC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($pyrs as $row) {
    if (!empty($row['y'])) $programmeYears[] = (int)$row['y'];
}

$programmeFilterYear = isset($_GET['programme_year']) && is_numeric($_GET['programme_year']) ? (int)$_GET['programme_year'] : null;
if ($programmeFilterYear) {
    $stmt = $pdo->prepare('SELECT * FROM programmes WHERE (YEAR(date_debut) = ? OR YEAR(date_fin) = ?) ORDER BY ordre ASC, id ASC');
    $stmt->execute([$programmeFilterYear, $programmeFilterYear]);
    $programmes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $programmes = $pdo->query('SELECT * FROM programmes ORDER BY ordre ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
}

// Retraite sélectionnée pour édition (formulaire pré-rempli)
$retreatToEdit = null;
if (isset($_GET['section']) && $_GET['section'] === 'retreats' && isset($_GET['edit_retreat'])) {
    $editId = (int) $_GET['edit_retreat'];
    if ($editId > 0) {
        $stmt = $pdo->prepare('SELECT * FROM retreats WHERE id = ?');
        $stmt->execute([$editId]);
        $retreatToEdit = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

// Programme annuel sélectionné pour édition
$programmeToEdit = null;
if (isset($_GET['section']) && $_GET['section'] === 'programmes' && isset($_GET['edit_programme'])) {
    $editProgId = (int) $_GET['edit_programme'];
    if ($editProgId > 0) {
        $stmt = $pdo->prepare('SELECT * FROM programmes WHERE id = ?');
        $stmt->execute([$editProgId]);
        $programmeToEdit = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin EDEN</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-header">
        <div style="display:flex; align-items:center; gap:12px;">
            <img src="../Logo Initiales Nominatif Moderne Minimal Blanc Orange Noir.png" alt="EDEN" style="height:40px; width:auto; display:block;">
            <h1 style="margin:0;"><span style="font-weight:700; letter-spacing:0.08em; text-transform:uppercase;">EDEN</span> <span style="font-weight:400; opacity:0.9;">Administration</span></h1>
        </div>
        <div>
            <a href="../index.php" style="color:#fff; margin-right:15px; font-weight:600;">Retour au site</a>
            <a href="?logout=1" style="color:#fff; font-weight:600;">Se déconnecter</a>
        </div>
    </div>

    <div class="admin-layout">
        <aside class="admin-sidebar">
            <nav>
                <?php $section = isset($_GET['section']) ? preg_replace('/[^a-z_]/','', $_GET['section']) : 'dashboard'; ?>
                <a href="?section=dashboard" class="<?php echo $section === 'dashboard' ? 'active' : ''; ?>">Tableau de bord</a>
                <a href="?section=retreats" class="<?php echo $section === 'retreats' ? 'active' : ''; ?>">Retraites <span style="float:right;" class="badge"><?php echo count($retreats); ?></span></a>
                <a href="?section=programmes" class="<?php echo $section === 'programmes' ? 'active' : ''; ?>">Programmes <span style="float:right;" class="badge"><?php echo count($programmes); ?></span></a>
                <a href="?section=galerie" class="<?php echo $section === 'galerie' ? 'active' : ''; ?>">Galerie <span style="float:right;" class="badge"><?php echo count($gallery); ?></span></a>
                <a href="?section=inscriptions_gestion" class="<?php echo $section === 'inscriptions_gestion' ? 'active' : ''; ?>">Inscriptions <span style="float:right;" class="badge"><?php echo count($inscriptions); ?></span></a>
                <a href="?section=contacts" class="<?php echo $section === 'contacts' ? 'active' : ''; ?>">Contacts <span style="float:right;" class="badge"><?php echo count($contacts); ?></span></a>
                <a href="?section=horaires" class="<?php echo $section === 'horaires' ? 'active' : ''; ?>">Horaires</a>
                <a href="?section=account" class="<?php echo $section === 'account' ? 'active' : ''; ?>">Compte</a>
                <a href="?section=testimonials" class="<?php echo $section === 'testimonials' ? 'active' : ''; ?>">Témoignages</a>
            </nav>
        </aside>

        <main class="admin-main">
            <?php
            // ajout des sections 'dashboard', 'tools' et 'programmes' pour gestion globale
            $allowed = ['dashboard','tools','programmes','retreats','galerie','contacts','inscriptions','inscriptions_gestion','horaires','account','testimonials'];
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
        (function() {
            const fileInput = document.getElementById('image_file');
            const urlInput = document.getElementById('image_url');
            const preview = document.getElementById('preview-img');

            function showPreviewFromFile(file) {
                if (!file || !preview) return;
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }

            function showPreviewFromUrl(url) {
                if (!preview) return;
                if (!url) {
                    preview.style.display = 'none';
                    preview.src = '';
                    return;
                }
                preview.src = url;
                preview.style.display = 'block';
            }

            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    const file = this.files && this.files[0];
                    if (file) {
                        showPreviewFromFile(file);
                    }
                });
            }

            if (urlInput) {
                urlInput.addEventListener('input', function() {
                    if (this.value.trim() !== '') {
                        showPreviewFromUrl(this.value.trim());
                    }
                });
            }
        })();
    </script>
</body>
</html>
