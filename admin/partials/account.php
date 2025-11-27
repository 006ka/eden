<?php
// Account management for admin
$message = '';
$error = '';
$admin = null;
if (!empty($_SESSION['admin_id'])) {
    $stmt = $pdo->prepare('SELECT * FROM admins WHERE id = ? LIMIT 1');
    $stmt->execute([(int)$_SESSION['admin_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
}
if (!$admin) {
    // fallback: pick first admin
    $admin = $pdo->query('SELECT * FROM admins ORDER BY id ASC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    if ($admin && empty($_SESSION['admin_id'])) {
        $_SESSION['admin_id'] = (int)$admin['id'];
    }
}

if (!$admin) {
    echo '<div class="admin-section"><p>Aucun compte administrateur trouvé.</p></div>';
    return;
}

if (isset($_POST['update_account'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $display_name = trim($_POST['display_name'] ?? '');

    if ($username === '') {
        $error = 'Le nom d\'utilisateur ne peut pas être vide.';
    } else {
        try {
            $stmt = $pdo->prepare('UPDATE admins SET username = ?, email = ?, display_name = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$username, $email !== '' ? $email : null, $display_name !== '' ? $display_name : null, (int)$admin['id']]);
            $message = 'Compte mis à jour.';
            // refresh admin data
            $stmt = $pdo->prepare('SELECT * FROM admins WHERE id = ? LIMIT 1');
            $stmt->execute([(int)$admin['id']]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $error = 'Erreur lors de la mise à jour : ' . $e->getMessage();
        }
    }
}

if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($new === '' || strlen($new) < 6) {
        $error = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
    } elseif ($new !== $confirm) {
        $error = 'Le nouveau mot de passe et la confirmation ne correspondent pas.';
    } else {
        // verify current
        if (!password_verify($current, $admin['password_hash'])) {
            $error = 'Mot de passe actuel incorrect.';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE admins SET password_hash = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$hash, (int)$admin['id']]);
            $message = 'Mot de passe mis à jour avec succès.';
        }
    }
}
?>
<div class="admin-section">
    <h2>Mon Compte</h2>
    <?php if ($error !== ''): ?>
        <div class="admin-alert admin-alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($message !== ''): ?>
        <div class="admin-alert admin-alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="post" style="max-width:700px;">
        <h3>Informations</h3>
        <div style="margin-bottom:12px;">
            <label>Nom d'utilisateur</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required style="width:100%; padding:8px;">
        </div>
        <div style="margin-bottom:12px;">
            <label>Nom affiché</label>
            <input type="text" name="display_name" value="<?php echo htmlspecialchars($admin['display_name'] ?? ''); ?>" style="width:100%; padding:8px;">
        </div>
        <div style="margin-bottom:12px;">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>" style="width:100%; padding:8px;">
        </div>
        <div>
            <button type="submit" name="update_account" class="btn btn-primary">Mettre à jour le compte</button>
        </div>
    </form>

    <hr style="margin:24px 0;">

    <form method="post" style="max-width:700px;">
        <h3>Changer le mot de passe</h3>
        <div style="margin-bottom:12px;">
            <label>Mot de passe actuel</label>
            <input type="password" name="current_password" required style="width:100%; padding:8px;">
        </div>
        <div style="margin-bottom:12px;">
            <label>Nouveau mot de passe</label>
            <input type="password" name="new_password" required style="width:100%; padding:8px;">
        </div>
        <div style="margin-bottom:12px;">
            <label>Confirmer le nouveau mot de passe</label>
            <input type="password" name="confirm_password" required style="width:100%; padding:8px;">
        </div>
        <div>
            <button type="submit" name="change_password" class="btn btn-danger">Changer le mot de passe</button>
        </div>
    </form>
</div>