<?php
// Admin partial to list, add and edit 'enseignements'

$action = $_GET['action'] ?? '';
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;

// Handle delete action
if (isset($_GET['delete']) && ctype_digit((string)$_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    try {
        $del = $pdo->prepare('DELETE FROM enseignements WHERE id = ?');
        $del->execute([$delId]);
        header('Location: ?section=enseignements&deleted=1');
        exit;
    } catch (Exception $e) {
        $deleteError = 'Erreur lors de la suppression : ' . $e->getMessage();
    }
}

// Handle form submission (add or edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_enseignement'])) {
    try {
    $titre = trim($_POST['titre'] ?? '');
    $auteur = trim($_POST['auteur'] ?? '');
    $date_publication = !empty($_POST['date_publication']) ? $_POST['date_publication'] : date('Y-m-d');
    $description = trim($_POST['description'] ?? '');
    // Le contenu HTML est supprimé (on n'enregistre plus de HTML complet)
    $contenu = '';
    $retraite_id = !empty($_POST['retraite_id']) && ctype_digit($_POST['retraite_id']) ? (int)$_POST['retraite_id'] : null;
    $duree = (int)($_POST['duree_minutes'] ?? 0);
    $est_public = isset($_POST['est_public']) ? 1 : 0;
    // fichier : valeur existante par défaut (si édition)
    $fichier_url = trim($_POST['existing_fichier_url'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');

        // Handle image upload if provided
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../uploads/enseignements/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (!in_array($ext, $allowed)) throw new Exception('Format d\'image non autorisé');
            $filename = 'enseignement_' . time() . '_' . uniqid() . '.' . $ext;
            $target = $upload_dir . $filename;
            if (!move_uploaded_file($_FILES['image_file']['tmp_name'], $target)) throw new Exception('Erreur lors de l\'upload de l\'image');
            $image_url = 'uploads/enseignements/' . $filename;
        }

        // Handle file upload (PDF / audio) if provided
        if (isset($_FILES['fichier_file']) && $_FILES['fichier_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../uploads/enseignements/files/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $ext = strtolower(pathinfo($_FILES['fichier_file']['name'], PATHINFO_EXTENSION));
            $allowedFiles = ['pdf','mp3','wav','m4a','ogg'];
            if (!in_array($ext, $allowedFiles)) throw new Exception('Format de fichier non autorisé. Formats acceptés : ' . implode(', ', $allowedFiles));
            $filename = 'enseignement_file_' . time() . '_' . uniqid() . '.' . $ext;
            $target = $upload_dir . $filename;
            if (!move_uploaded_file($_FILES['fichier_file']['tmp_name'], $target)) throw new Exception('Erreur lors de l\'upload du fichier');
            $fichier_url = 'uploads/enseignements/files/' . $filename;
        }

        if (isset($_POST['editing_id']) && ctype_digit($_POST['editing_id'])) {
            // Update
            $id = (int)$_POST['editing_id'];
            $sql = 'UPDATE enseignements SET titre = :titre, auteur = :auteur, date_publication = :date_publication, description = :description, contenu = :contenu, retraite_id = :retraite_id, duree_minutes = :duree_minutes, est_public = :est_public, fichier_url = :fichier_url';
            if ($image_url) $sql .= ', image_url = :image_url';
            $sql .= ' WHERE id = :id';
            $params = [':titre'=>$titre, ':auteur'=>$auteur, ':date_publication'=>$date_publication, ':description'=>$description, ':contenu'=>$contenu, ':retraite_id'=>$retraite_id, ':duree_minutes'=>$duree, ':est_public'=>$est_public, ':fichier_url'=>$fichier_url, ':id'=>$id];
            if ($image_url) $params[':image_url'] = $image_url;
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            header('Location: ?section=enseignements&updated=1'); exit;
        } else {
            // Insert
            $sql = 'INSERT INTO enseignements (titre, auteur, date_publication, description, contenu, retraite_id, duree_minutes, est_public, fichier_url, image_url) VALUES (:titre, :auteur, :date_publication, :description, :contenu, :retraite_id, :duree_minutes, :est_public, :fichier_url, :image_url)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':titre'=>$titre, ':auteur'=>$auteur, ':date_publication'=>$date_publication, ':description'=>$description, ':contenu'=>$contenu, ':retraite_id'=>$retraite_id, ':duree_minutes'=>$duree, ':est_public'=>$est_public, ':fichier_url'=>$fichier_url, ':image_url'=>$image_url]);
            header('Location: ?section=enseignements&created=1'); exit;
        }

    } catch (Exception $e) {
        $formError = 'Erreur lors de l\'enregistrement : ' . $e->getMessage();
    }
}

// If editing, fetch the record
$editing = null;
if ($editId) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM enseignements WHERE id = ?');
        $stmt->execute([$editId]);
        $editing = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Exception $e) {
        $editing = null;
    }
}

// Fetch retreats for dropdown
$retreats = [];
try {
    $stmt = $pdo->query("SELECT id, titre FROM retreats ORDER BY date_debut DESC");
    $retreats = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
    $retreats = [];
}

// Fetch list for table view
try {
    $stmt = $pdo->query("SELECT id, titre, auteur, date_publication, est_public FROM enseignements ORDER BY date_publication DESC");
    $enseignements = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
    $enseignements = [];
}

?>
<div class="admin-section">
    <h2>Enseignements</h2>
    <p class="mb-3">Gérez les enseignements publiés et en brouillon.</p>

    <div style="margin-bottom:16px;">
        <a href="../public/enseignements.php" class="btn btn-primary" target="_blank">Voir la liste publique</a>
        <button type="button" onclick="openEnseignementModal()" class="btn btn-secondary">Ajouter un enseignement</button>
    </div>

    <?php if (!empty($formError)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($formError); ?></div>
    <?php endif; ?>

    <!-- Modal pour le formulaire -->
    <div id="enseignementModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; overflow-y:auto;">
        <div style="background:white; margin:20px auto; max-width:900px; border-radius:8px; box-shadow:0 4px 20px rgba(0,0,0,0.15); max-height:90vh; overflow-y:auto;">
            <div style="padding:20px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--color-border);">
                <h3 id="modalTitle">Ajouter un enseignement</h3>
                <button type="button" onclick="closeEnseignementModal()" style="background:none; border:none; font-size:24px; cursor:pointer;">&times;</button>
            </div>
            <div style="padding:20px;">
                <form method="post" enctype="multipart/form-data" id="enseignementForm">
                <?php if ($editing): ?>
                    <input type="hidden" name="editing_id" value="<?php echo (int)$editing['id']; ?>">
                <?php endif; ?>
                <div style="display:grid; grid-template-columns: 1fr 300px; gap:18px; align-items:start;">
                    <div>
                        <label>Titre</label>
                        <input type="text" name="titre" class="form-control" value="<?php echo htmlspecialchars($editing['titre'] ?? ''); ?>" required>

                        <label style="margin-top:8px;">Auteur</label>
                        <input type="text" name="auteur" class="form-control" value="<?php echo htmlspecialchars($editing['auteur'] ?? ''); ?>">

                        <label style="margin-top:8px;">Date de publication</label>
                        <input type="date" name="date_publication" class="form-control" value="<?php echo htmlspecialchars($editing['date_publication'] ?? date('Y-m-d')); ?>">

                        <label style="margin-top:8px;">Retraite associée</label>
                        <select name="retraite_id" class="form-control">
                            <option value="">-- Aucune retraite --</option>
                            <?php foreach ($retreats as $retreat): ?>
                                <option value="<?php echo (int)$retreat['id']; ?>" <?php echo (($editing['retraite_id'] ?? null) == $retreat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($retreat['titre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <label style="margin-top:8px;">Description</label>
                        <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($editing['description'] ?? ''); ?></textarea>

                        <!-- Champ 'contenu' supprimé : nous n'acceptons plus de HTML complet -->
                    </div>

                    <div>
                        <label>Image (URL ou upload)</label>
                        <input type="text" name="image_url" class="form-control" value="<?php echo htmlspecialchars($editing['image_url'] ?? ''); ?>">
                        <input type="file" name="image_file" class="form-control" style="margin-top:8px;">

                        <label style="margin-top:8px;">Fichier (PDF / audio) — upload</label>
                        <?php if (!empty($editing['fichier_url'])): ?>
                            <div style="margin-bottom:8px;">Fichier existant: <a href="<?php echo htmlspecialchars($editing['fichier_url']); ?>" target="_blank"><?php echo htmlspecialchars(basename($editing['fichier_url'])); ?></a></div>
                        <?php endif; ?>
                        <input type="file" name="fichier_file" accept=".pdf,audio/*,audio/mpeg" class="form-control">
                        <input type="hidden" name="existing_fichier_url" value="<?php echo htmlspecialchars($editing['fichier_url'] ?? ''); ?>">

                        <label style="margin-top:8px;">Durée (minutes)</label>
                        <input type="number" name="duree_minutes" class="form-control" value="<?php echo htmlspecialchars($editing['duree_minutes'] ?? 0); ?>">

                        <label style="margin-top:8px;"><input type="checkbox" name="est_public" value="1" <?php echo (!isset($editing) || ($editing['est_public'] ?? 0)) ? 'checked' : ''; ?>> Publier</label>

                        <div style="margin-top:12px;">
                            <button type="submit" name="save_enseignement" class="btn btn-primary"><?php echo $editing ? 'Mettre à jour' : 'Enregistrer'; ?></button>
                            <button type="button" onclick="closeEnseignementModal()" class="btn btn-outline">Annuler</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if (empty($enseignements)): ?>
        <div class="card p-3">Aucun enseignement trouvé.</div>
    <?php else: ?>
        <table class="admin-table" style="width:100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid var(--color-border);">Titre</th>
                    <th style="padding:8px; border-bottom:1px solid var(--color-border);">Auteur</th>
                    <th style="padding:8px; border-bottom:1px solid var(--color-border);">Date</th>
                    <th style="padding:8px; border-bottom:1px solid var(--color-border);">Statut</th>
                    <th style="padding:8px; border-bottom:1px solid var(--color-border);">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($enseignements as $e): ?>
                    <tr>
                        <td style="padding:8px; border-bottom:1px solid var(--color-border);"><?php echo htmlspecialchars($e['titre']); ?></td>
                        <td style="padding:8px; border-bottom:1px solid var(--color-border);"><?php echo htmlspecialchars($e['auteur']); ?></td>
                        <td style="padding:8px; border-bottom:1px solid var(--color-border);"><?php echo htmlspecialchars($e['date_publication']); ?></td>
                        <td style="padding:8px; border-bottom:1px solid var(--color-border);"><?php echo $e['est_public'] ? 'Public' : 'Brouillon'; ?></td>
                        <td style="padding:8px; border-bottom:1px solid var(--color-border);">
                            <a href="?section=enseignements&edit=<?php echo (int)$e['id']; ?>" class="btn btn-sm">Éditer</a>
                            <a href="?section=enseignements&delete=<?php echo (int)$e['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet enseignement ?');">Supprimer</a>
                            <a href="../public/enseignement.php?id=<?php echo (int)$e['id']; ?>" target="_blank" class="btn btn-sm btn-outline">Voir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
function openEnseignementModal() {
    const modal = document.getElementById('enseignementModal');
    const form = document.getElementById('enseignementForm');
    const title = document.getElementById('modalTitle');
    
    // Réinitialiser le formulaire pour l'ajout
    if (form) form.reset();
    
    title.textContent = 'Ajouter un enseignement';
    
    // Prévalider les dates et durée
    const dateInput = form.querySelector('input[name="date_publication"]');
    if (dateInput) dateInput.value = new Date().toISOString().split('T')[0];
    
    const dureeInput = form.querySelector('input[name="duree_minutes"]');
    if (dureeInput) dureeInput.value = '0';
    
    const checkbox = form.querySelector('input[name="est_public"]');
    if (checkbox) checkbox.checked = true;
    
    // Retirer l'ID d'édition si présent
    const editingId = form.querySelector('input[name="editing_id"]');
    if (editingId) editingId.remove();
    
    modal.style.display = 'block';
}

function closeEnseignementModal() {
    const modal = document.getElementById('enseignementModal');
    modal.style.display = 'none';
}

// Fermer le modal en cliquant sur le fond
document.addEventListener('click', function(event) {
    const modal = document.getElementById('enseignementModal');
    if (event.target === modal) {
        closeEnseignementModal();
    }
});
</script>
