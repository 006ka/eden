<?php
session_start();

$ADMIN_PASSWORD = 'edenadmin'; // à changer pour plus de sécurité
$galleryError = '';
$retreatError = '';

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

if (isset($_POST['password'])) {
    if ($_POST['password'] === $ADMIN_PASSWORD) {
        $_SESSION['is_admin'] = true;
    } else {
        $login_error = 'Mot de passe incorrect';
    }
}

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
    $programme_image_url = trim($_POST['ret_programme_image_url'] ?? '');

    if ($titre === '') {
        $retreatError = 'Le titre de la retraite est obligatoire.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO retreats (titre, theme, date_debut, date_fin, lieu, orateurs, description, programme_image_url, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            $titre,
            $theme !== '' ? $theme : null,
            $date_debut !== '' ? $date_debut : null,
            $date_fin !== '' ? $date_fin : null,
            $lieu !== '' ? $lieu : null,
            $orateurs !== '' ? $orateurs : null,
            $description !== '' ? $description : null,
            $programme_image_url !== '' ? $programme_image_url : null,
        ]);
        header('Location: admin.php#retreats');
        exit;
    }
}

if (isset($_GET['delete_retreat'])) {
    $id = (int) $_GET['delete_retreat'];
    $pdo->prepare('DELETE FROM retreats WHERE id = ?')->execute([$id]);
    header('Location: admin.php#retreats');
    exit;
}

if (isset($_POST['update_programme'])) {
    $id = isset($_POST['retreat_id']) ? (int) $_POST['retreat_id'] : 0;
    $programme_image_url = trim($_POST['programme_image_url'] ?? '');
    $stmt = $pdo->prepare('UPDATE retreats SET programme_image_url = ? WHERE id = ?');
    $stmt->execute([
        $programme_image_url !== '' ? $programme_image_url : null,
        $id
    ]);
    header('Location: admin.php#retreats');
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
        header('Location: admin.php#galerie');
        exit;
    }
}

if (isset($_GET['delete_gallery'])) {
    $id = (int) $_GET['delete_gallery'];
    $pdo->prepare('DELETE FROM gallery WHERE id = ?')->execute([$id]);
    header('Location: admin.php#galerie');
    exit;
}

$contacts = $pdo->query('SELECT * FROM contacts ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
$inscriptions = $pdo->query('SELECT * FROM inscriptions ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
$retreats = $pdo->query('SELECT * FROM retreats ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
 $gallery = $pdo->query('SELECT * FROM gallery ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin EDEN</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-header">
        <h1>Administration EDEN</h1>
        <div>
            <a href="../index.php" style="color:#fff; margin-right:15px;">Retour au site</a>
            <a href="?logout=1" style="color:#fff;">Se déconnecter</a>
        </div>
    </div>

    <div class="admin-layout">
        <aside class="admin-sidebar">
            <nav>
                <?php $section = isset($_GET['section']) ? preg_replace('/[^a-z_]/','', $_GET['section']) : 'retreats'; ?>
                <a href="?section=retreats" class="<?php echo $section === 'retreats' ? 'active' : ''; ?>">Retraites <span style="float:right;" class="badge"><?php echo count($retreats); ?></span></a>
                <a href="?section=galerie" class="<?php echo $section === 'galerie' ? 'active' : ''; ?>">Galerie <span style="float:right;" class="badge"><?php echo count($gallery); ?></span></a>
                <a href="?section=inscriptions" class="<?php echo $section === 'inscriptions' ? 'active' : ''; ?>">Inscriptions <span style="float:right;" class="badge"><?php echo count($inscriptions); ?></span></a>
                <a href="?section=contacts" class="<?php echo $section === 'contacts' ? 'active' : ''; ?>">Contacts <span style="float:right;" class="badge"><?php echo count($contacts); ?></span></a>
            </nav>
        </aside>

        <main class="admin-main">
            <?php
            $allowed = ['retreats','galerie','contacts','inscriptions'];
            if (!in_array($section, $allowed, true)) {
                $section = 'retreats';
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
