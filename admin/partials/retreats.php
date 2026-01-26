<?php
// Logique pour l'ajout/édition des retraites
$isEditing = isset($_GET['edit_retreat']) && $retreatToEdit;
$isAdding = isset($_GET['add']) && $_GET['add'] === 'new';
?>

<?php if ($isAdding || $isEditing): ?>
<!-- Formulaire d'ajout/édition -->
<div class="admin-section">
    <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 25px;">
        <h2 style="margin: 0;"><?php echo $isEditing ? 'Modifier la Retraite' : 'Nouvelle Retraite'; ?></h2>
        <a href="?section=retreats" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px;">
            <span>←</span>
            Retour
        </a>
    </div>

    <?php if (!empty($retreatError)): ?>
        <div class="admin-alert admin-alert-error">
            <span>⚠️</span>
            <?php echo htmlspecialchars($retreatError); ?>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="form-modern">
        <?php if ($isEditing): ?>
            <input type="hidden" name="retreat_id" value="<?php echo $retreatToEdit['id']; ?>">
        <?php endif; ?>
        
        <div class="form-row">
            <div class="form-group">
                <label for="ret_titre">Titre de la retraite *</label>
                <input type="text" id="ret_titre" name="ret_titre" 
                       value="<?php echo $isEditing ? htmlspecialchars($retreatToEdit['titre']) : ''; ?>" 
                       required placeholder="Ex: Retraite spirituelle de printemps">
            </div>
            
            <div class="form-group">
                <label for="ret_theme">Thème</label>
                <input type="text" id="ret_theme" name="ret_theme" 
                       value="<?php echo $isEditing ? htmlspecialchars($retreatToEdit['theme'] ?? '') : ''; ?>" 
                       placeholder="Ex: Renouveau et méditation">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="ret_date_debut">Date de début</label>
                <input type="date" id="ret_date_debut" name="ret_date_debut" 
                       value="<?php echo $isEditing ? htmlspecialchars($retreatToEdit['date_debut'] ?? '') : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="ret_date_fin">Date de fin</label>
                <input type="date" id="ret_date_fin" name="ret_date_fin" 
                       value="<?php echo $isEditing ? htmlspecialchars($retreatToEdit['date_fin'] ?? '') : ''; ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="ret_lieu">Lieu</label>
                <input type="text" id="ret_lieu" name="ret_lieu" 
                       value="<?php echo $isEditing ? htmlspecialchars($retreatToEdit['lieu'] ?? '') : ''; ?>" 
                       placeholder="Ex: Monastère de la Paix">
            </div>
            
            <div class="form-group">
                <label for="ret_prix">Prix</label>
                <input type="text" id="ret_prix" name="ret_prix" 
                       value="<?php echo $isEditing ? htmlspecialchars($retreatToEdit['prix'] ?? '') : ''; ?>" 
                       placeholder="Ex: 250€">
            </div>
        </div>

        <div class="form-group">
            <label for="ret_orateurs">Orateurs/Animateurs</label>
            <input type="text" id="ret_orateurs" name="ret_orateurs" 
                   value="<?php echo $isEditing ? htmlspecialchars($retreatToEdit['orateurs'] ?? '') : ''; ?>" 
                   placeholder="Ex: Père Martin, Sœur Marie">
        </div>

        <div class="form-group">
            <label for="ret_description">Description</label>
            <textarea id="ret_description" name="ret_description" rows="5" 
                      placeholder="Description détaillée de la retraite..."><?php echo $isEditing ? htmlspecialchars($retreatToEdit['description'] ?? '') : ''; ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="ret_image_file">Image de la retraite</label>
                <input type="file" id="ret_image_file" name="ret_image_file" accept="image/*">
                <small style="color: var(--color-text-light); margin-top: 5px; display: block;">
                    Formats: JPG, PNG, GIF, WEBP (max 2Mo)
                </small>
            </div>
            
            <div class="form-group">
                <label for="ret_fiche_file">Fiche pratique (PDF ou image)</label>
                <input type="file" id="ret_fiche_file" name="ret_fiche_file" accept=".pdf,image/*">
                <small style="color: var(--color-text-light); margin-top: 5px; display: block;">
                    PDF, JPG, PNG, GIF, WEBP (max 4Mo)
                </small>
            </div>
        </div>

        <div style="display: flex; gap: 15px; margin-top: 25px;">
            <button type="submit" name="<?php echo $isEditing ? 'update_retreat' : 'add_retreat'; ?>" 
                    class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px;">
                <span>💾</span>
                <?php echo $isEditing ? 'Mettre à jour' : 'Créer la retraite'; ?>
            </button>
            <a href="?section=retreats" class="btn btn-outline">Annuler</a>
        </div>
    </form>
</div>

<?php else: ?>
<!-- Liste des retraites -->
<div class="admin-section">
    <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
        <h2 style="margin: 0;">Gestion des Retraites</h2>
        <div style="display: flex; gap: 15px; align-items: center;">
            <a href="?section=retreats&add=new" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px;">
                <span>➕</span>
                Nouvelle Retraite
            </a>
        </div>
    </div>

    <!-- Filtres -->
    <div style="background: var(--color-bg); padding: 20px; border-radius: 12px; margin-bottom: 25px;">
        <form method="get" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            <input type="hidden" name="section" value="retreats">
            <label style="font-weight: 600; color: var(--color-text);">Filtrer par année:</label>
            <select name="retreat_year" onchange="this.form.submit()" 
                    style="padding: 10px 15px; border: 2px solid var(--color-border); border-radius: 8px; background: var(--admin-surface);">
                <option value="">Toutes les années</option>
                <?php foreach ($retreatYears as $year): ?>
                    <option value="<?php echo $year; ?>" <?php echo $retreatsFilterYear === $year ? 'selected' : ''; ?>>
                        <?php echo $year; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if (isset($_GET['retreat_success'])): ?>
        <div class="admin-alert admin-alert-success">
            <span>✅</span>
            Retraite <?php echo isset($_GET['edit_retreat']) ? 'modifiée' : 'créée'; ?> avec succès !
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['retreat_deleted'])): ?>
        <div class="admin-alert admin-alert-success">
            <span>✅</span>
            Retraite supprimée avec succès !
        </div>
    <?php endif; ?>

    <?php if (!empty($retreats)): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Titre</th>
                        <th>Dates</th>
                        <th>Lieu</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($retreats as $retreat): ?>
                        <tr>
                            <td style="width: 100px; padding: 8px;">
                                <?php if (!empty($retreat['programme_image_url'])): 
                                    $imagePath = $retreat['programme_image_url'];
                                    // Si le chemin commence par /uploads, on ajoute .. pour remonter d'un niveau
                                    if (strpos($imagePath, '/uploads/') === 0) {
                                        $imagePath = '..' . $imagePath;
                                    }
                                    // Si le chemin ne commence pas par /, on ajoute /admin/../
                                    elseif (strpos($imagePath, '/') !== 0) {
                                        $imagePath = '../' . $imagePath;
                                    }
                                ?>
                                    <div style="width: 80px; height: 60px; background: #f5f5f5; border-radius: 4px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Image de la retraite" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                    </div>
                                <?php else: ?>
                                    <div style="width: 80px; height: 60px; background: #f5f5f5; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #999;">
                                        <span>Pas d'image</span>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($retreat['titre']); ?></div>
                                <?php if ($retreat['theme']): ?>
                                    <div style="color: var(--color-text-light); font-size: 0.9rem; margin-top: 4px;">
                                        <?php echo htmlspecialchars($retreat['theme']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $start = $retreat['date_debut'] ? (new DateTime($retreat['date_debut']))->format('d/m/Y') : 'Non définie';
                                $end = $retreat['date_fin'] ? (new DateTime($retreat['date_fin']))->format('d/m/Y') : '';
                                echo $start . ($end ? ' - ' . $end : '');
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($retreat['lieu'] ?? 'Non spécifié'); ?></td>
                            <td>
                                <?php
                                $now = new DateTime();
                                $startDate = $retreat['date_debut'] ? new DateTime($retreat['date_debut']) : null;
                                $endDate = $retreat['date_fin'] ? new DateTime($retreat['date_fin']) : null;
                                
                                if (!$startDate) {
                                    echo '<span class="badge" style="background: var(--color-warning);">🔄 Planifiée</span>';
                                } elseif ($startDate > $now) {
                                    echo '<span class="badge" style="background: var(--color-info);">⏳ À venir</span>';
                                } elseif ($endDate && $endDate < $now) {
                                    echo '<span class="badge" style="background: var(--color-text-light);">✅ Terminée</span>';
                                } else {
                                    echo '<span class="badge" style="background: var(--color-success);">🔥 En cours</span>';
                                }
                                ?>
                            </td>
                            <td class="actions">
                                <a href="?section=retreats&edit_retreat=<?php echo $retreat['id']; ?>">
                                    <span>✏️</span>
                                    Éditer
                                </a>
                                <a href="?section=retreats&delete_retreat=<?php echo $retreat['id']; ?>" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette retraite ?')"
                                   style="color: var(--color-accent);">
                                    <span>🗑️</span>
                                    Supprimer
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i>📅</i>
            <h3>Aucune retraite trouvée</h3>
            <p>Commencez par créer votre première retraite pour organiser vos événements spirituels.</p>
            <a href="?section=retreats&add=new" class="btn btn-primary" style="margin-top: 20px;">
                Créer une retraite
            </a>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>