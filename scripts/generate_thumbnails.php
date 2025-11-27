<?php
/**
 * Script CLI / web pour générer des miniatures (thumb_) dans le dossier uploads/
 * Usage (CLI):
 *   php scripts/generate_thumbnails.php            -> crée les vignettes pour les images manquantes
 *   php scripts/generate_thumbnails.php --resize-original
 *   php scripts/generate_thumbnails.php --help
 *
 * Par défaut le script crée des miniatures 400x300 et ne touche pas aux originaux.
 * Avec --resize-original il redimensionne les originaux à max 1200x1200 (écrase après sauvegarde .bak).
 */

$uploadsDir = realpath(__DIR__ . '/../uploads');
if ($uploadsDir === false) {
    echo "Dossier uploads introuvable: " . __DIR__ . "/../uploads\n";
    exit(1);
}

$options = array_slice($argv ?? [], 1);
$resizeOriginal = in_array('--resize-original', $options, true);
$help = in_array('--help', $options, true) || in_array('-h', $options, true);

function print_help() {
    echo "generate_thumbnails.php - génère des vignettes dans uploads/\n\n";
    echo "Options:\n";
    echo "  --resize-original   Redimensionne aussi les originaux (max 1200x1200).\n";
    echo "  --help, -h          Affiche cette aide.\n";
}

if ($help && php_sapi_name() === 'cli') {
    print_help();
    exit(0);
}

// helper resize (similar to admin helper) - returns true on success
function resize_image_local($src, $dest, $maxWidth, $maxHeight, $quality = 85) {
    if (!file_exists($src)) return false;
    $info = @getimagesize($src);
    if ($info === false) return false;
    list($width, $height, $type) = $info;

    $ratio = $width / $height;
    if ($width <= $maxWidth && $height <= $maxHeight) {
        if ($src !== $dest) return copy($src, $dest);
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
            $pngLevel = 9 - floor($quality / 11);
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

$allowed = ['jpg','jpeg','png','gif','webp'];
$files = scandir($uploadsDir);
$count = 0;
$created = 0;

foreach ($files as $f) {
    if ($f === '.' || $f === '..') continue;
    if (strpos($f, 'thumb_') === 0) continue; // skip existing thumbs
    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) continue;

    $full = $uploadsDir . DIRECTORY_SEPARATOR . $f;
    if (!is_file($full)) continue;
    $count++;

    $thumbFs = $uploadsDir . DIRECTORY_SEPARATOR . 'thumb_' . $f;
    if (!file_exists($thumbFs)) {
        $ok = resize_image_local($full, $thumbFs, 400, 300, 80);
        if ($ok) {
            $created++;
            echo "Vignette créée: thumb_{$f}\n";
        } else {
            echo "Erreur vignette pour: {$f}\n";
        }
    } else {
        echo "Vignette existe: thumb_{$f} (skip)\n";
    }

    if ($resizeOriginal) {
        // create backup then resize original
        $bak = $full . '.bak';
        if (!file_exists($bak)) copy($full, $bak);
        $ok2 = resize_image_local($full, $full, 1200, 1200, 85);
        if ($ok2) {
            echo "Original redimensionné: {$f}\n";
        } else {
            echo "Échec redimension original: {$f}\n";
        }
    }
}

echo "Traitement terminé. Fichiers analysés: {$count}, vignettes créées: {$created}.\n";

if (php_sapi_name() !== 'cli') {
    echo "<p><a href=\"../admin/admin.php?section=galerie\">Retour admin</a></p>";
}

?>
