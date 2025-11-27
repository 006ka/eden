<?php
// Admin testimonials management (list, add, edit, delete)
if (!isset($pdo)) require_once __DIR__ . '/../../config/db.php';

$error = '';
$message = '';

// Optional success flag from redirect (delete action)
if (!empty($_GET['testimonial_deleted'])) {
    $message = 'Témoignage supprimé.';
}

// Handle add
if (isset($_POST['add_testimonial'])) {
    $author = trim($_POST['author'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $visible = isset($_POST['visible']) ? 1 : 0;
    $image_url = null;

    if ($author === '' || $content === '') {
        $error = 'L\'auteur et le contenu sont requis.';
    } else {
        // handle upload
        if (!empty($_FILES['image_file']['name']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            // store in the same /uploads directory as other admin uploads (root/uploads)
            $uploadDir = realpath(__DIR__ . '/..' . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'uploads';

            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $orig = basename($_FILES['image_file']['name']);
            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (in_array($ext, $allowed, true)) {
                $newName = 'testi_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
                $target = $uploadDir . DIRECTORY_SEPARATOR . $newName;
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target)) {
                    // try resize (resize_image exists in admin.php)
                    if (function_exists('resize_image')) resize_image($target, $target, 800, 800, 85);
                    // stocke un chemin relatif cohérent avec le reste de l'admin
                    $image_url = '../uploads/' . $newName;
                }
            }
        }

        $stmt = $pdo->prepare('INSERT INTO testimonials (author, role, content, image_url, visible, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$author, $role !== '' ? $role : null, $content, $image_url, $visible]);
        $message = 'Témoignage ajouté.';
    }
}

// Handle update
if (isset($_POST['update_testimonial'])) {
    $id = (int)($_POST['id'] ?? 0);
    $author = trim($_POST['author'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $visible = isset($_POST['visible']) ? 1 : 0;

    if ($id <= 0 || $author === '' || $content === '') {
        $error = 'Données invalides.';
    } else {
        // get current image
        $img = $pdo->prepare('SELECT image_url FROM testimonials WHERE id = ?');
        $img->execute([$id]);
        $cur = $img->fetch(PDO::FETCH_ASSOC);
        $image_url = $cur['image_url'] ?? null;

        // handle new upload
        if (!empty($_FILES['image_file']['name']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            // store in the same /uploads directory as other admin uploads (root/uploads)
            $uploadDir = realpath(__DIR__ . '/..' . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'uploads';

            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $orig = basename($_FILES['image_file']['name']);
            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (in_array($ext, $allowed, true)) {
                $newName = 'testi_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
                $target = $uploadDir . DIRECTORY_SEPARATOR . $newName;
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target)) {
                    if (function_exists('resize_image')) resize_image($target, $target, 800, 800, 85);
                    $image_url = '../uploads/' . $newName;
                }
            }
        }

        $stmt = $pdo->prepare('UPDATE testimonials SET author = ?, role = ?, content = ?, image_url = ?, visible = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$author, $role !== '' ? $role : null, $content, $image_url, $visible, $id]);
        $message = 'Témoignage mis à jour.';
    }
}

// Handle delete
if (isset($_GET['delete_testimonial'])) {
    $id = (int) $_GET['delete_testimonial'];
    if ($id > 0) {
        $pdo->prepare('DELETE FROM testimonials WHERE id = ?')->execute([$id]);
        header('Location: admin.php?section=testimonials&testimonial_deleted=1');
        exit;
    }
}

$testimonials = $pdo->query('SELECT * FROM testimonials ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="admin-section">
    <h2>Gérer les Témoignages</h2>
    <?php if ($error !== ''): ?>
        <div class="admin-alert admin-alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($message !== ''): ?>
        <div class="admin-alert admin-alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div style="display:flex; gap:24px; align-items:flex-start;">
        <div style="flex:1; max-width:480px;">
            <h3>Ajouter un témoignage</h3>
            <form method="post" enctype="multipart/form-data">
                <div style="margin-bottom:8px;"><label>Auteur</label><input type="text" name="author" required style="width:100%; padding:8px;"></div>
                <div style="margin-bottom:8px;"><label>Rôle / Ville (optionnel)</label><input type="text" name="role" style="width:100%; padding:8px;"></div>
                <div style="margin-bottom:8px;"><label>Contenu</label><textarea name="content" rows="5" required style="width:100%; padding:8px;"></textarea></div>
                <div style="margin-bottom:8px;"><label>Photo (optionnel)</label><input type="file" name="image_file" accept="image/*"></div>
                <div style="margin-bottom:8px;"><label><input type="checkbox" name="visible" checked> Visible publiquement</label></div>
                <div><button type="submit" name="add_testimonial" class="btn btn-primary">Ajouter</button></div>
            </form>
        </div>

        <div style="flex:2;">
            <h3>Liste des témoignages</h3>
            <?php
                $testiPerPage = 15;
                $testiPage = isset($_GET['testi_page']) && is_numeric($_GET['testi_page']) ? max(1, (int)$_GET['testi_page']) : 1;
                $testiTotal = count($testimonials);
                $testiOffset = ($testiPage - 1) * $testiPerPage;
                $testiPageItems = array_slice($testimonials, $testiOffset, $testiPerPage);
                $testiHasPrev = $testiPage > 1;
                $testiHasNext = $testiOffset + $testiPerPage < $testiTotal;
            ?>

            <?php if (empty($testiPageItems)): ?>
                <p>Aucun témoignage pour le moment.</p>
            <?php else: ?>
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="text-align:left; border-bottom:1px solid #eee;"><th>Auteur</th><th>Contenu</th><th>Visible</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($testiPageItems as $t): ?>
                        <tr style="border-bottom:1px solid #f4f4f4;">
                            <td style="padding:8px; vertical-align:top;"><?php echo htmlspecialchars($t['author']); ?><br><small><?php echo htmlspecialchars($t['role'] ?? ''); ?></small></td>
                            <td style="padding:8px; vertical-align:top; max-width:480px;"><?php echo nl2br(htmlspecialchars(substr($t['content'],0,280))); ?><?php echo (strlen($t['content'])>280)?'...':''; ?></td>
                            <td style="padding:8px; vertical-align:top;"><?php echo $t['visible'] ? 'Oui' : 'Non'; ?></td>
                            <td style="padding:8px; vertical-align:top;">
                                <a href="#edit_<?php echo (int)$t['id']; ?>" onclick="document.getElementById('edit_<?php echo (int)$t['id']; ?>').style.display='block'; return false;" class="btn btn-sm">Éditer</a>
                                <a href="?section=testimonials&delete_testimonial=<?php echo (int)$t['id']; ?>" onclick="return confirm('Supprimer ce témoignage ?')" class="btn btn-sm btn-danger">Supprimer</a>
                                <?php if (!empty($t['image_url'])): ?><div style="margin-top:6px;"><img src="<?php echo htmlspecialchars($t['image_url']); ?>" alt="" style="max-width:120px; border-radius:6px;"></div><?php endif; ?>
                                <div id="edit_<?php echo (int)$t['id']; ?>" style="display:none; margin-top:12px; background:#fff; padding:12px; border-radius:6px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                                    <form method="post" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?php echo (int)$t['id']; ?>">
                                        <div style="margin-bottom:8px;"><label>Auteur</label><input type="text" name="author" required value="<?php echo htmlspecialchars($t['author']); ?>" style="width:100%; padding:8px;"></div>
                                        <div style="margin-bottom:8px;"><label>Rôle</label><input type="text" name="role" value="<?php echo htmlspecialchars($t['role'] ?? ''); ?>" style="width:100%; padding:8px;"></div>
                                        <div style="margin-bottom:8px;"><label>Contenu</label><textarea name="content" rows="4" required style="width:100%; padding:8px;"><?php echo htmlspecialchars($t['content']); ?></textarea></div>
                                        <div style="margin-bottom:8px;"><label>Nouvelle photo (laisser vide pour conserver)</label><input type="file" name="image_file" accept="image/*"></div>
                                        <div style="margin-bottom:8px;"><label><input type="checkbox" name="visible" <?php echo $t['visible'] ? 'checked' : ''; ?>> Visible publiquement</label></div>
                                        <div><button type="submit" name="update_testimonial" class="btn btn-primary">Enregistrer</button></div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($testiTotal > $testiPerPage): ?>
                    <div style="margin-top:10px; display:flex; justify-content:space-between; align-items:center; font-size:0.9rem;">
                        <div>
                            Page <?php echo $testiPage; ?> / <?php echo max(1, (int)ceil($testiTotal / $testiPerPage)); ?>
                        </div>
                        <div style="display:flex; gap:8px;">
                            <?php if ($testiHasPrev): ?>
                                <a href="?section=testimonials&amp;testi_page=<?php echo $testiPage - 1; ?>" class="btn btn-secondary" style="padding:4px 10px; font-size:0.85rem;">&laquo; Précédent</a>
                            <?php endif; ?>
                            <?php if ($testiHasNext): ?>
                                <a href="?section=testimonials&amp;testi_page=<?php echo $testiPage + 1; ?>" class="btn btn-secondary" style="padding:4px 10px; font-size:0.85rem;">Suivant &raquo;</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

    </div>
</div>