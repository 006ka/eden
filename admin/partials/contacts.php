<div class="admin-section">
    <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
        <h2 style="margin: 0;">Gestion des Contacts</h2>
        <div style="display: flex; gap: 15px; align-items: center;">
            <span style="color: var(--color-text-light); font-size: 0.9rem;">
                Total: <?php echo count($contacts); ?> contact(s)
            </span>
        </div>
    </div>

    <?php if (isset($_GET['delete_contact'])): ?>
        <div class="admin-alert admin-alert-success">
            <span>✅</span>
            Contact supprimé avec succès !
        </div>
    <?php endif; ?>

    <?php if (!empty($contacts)): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Sujet</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contacts as $contact): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600;">
                                    <?php 
                                    // Afficher le nom de famille
                                    echo htmlspecialchars($contact['nom'] ?? ''); 
                                    
                                    // Afficher le prénom s'il existe dans le tableau
                                    if (isset($contact['prenom']) && !empty($contact['prenom'])): 
                                    ?>
                                        <div style="color: var(--color-text-light); font-size: 0.9rem;">
                                            <?php echo htmlspecialchars($contact['prenom']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>" 
                                   style="color: var(--admin-accent); text-decoration: none;">
                                    <?php echo htmlspecialchars($contact['email']); ?>
                                </a>
                            </td>
                            <td>
                                <?php if ($contact['telephone']): ?>
                                    <a href="tel:<?php echo htmlspecialchars($contact['telephone']); ?>" 
                                       style="color: var(--color-text); text-decoration: none;">
                                        <?php echo htmlspecialchars($contact['telephone']); ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--color-text-light);">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($contact['sujet']) && !empty(trim($contact['sujet']))): ?>
                                    <span style="font-weight: 600; color: var(--admin-accent);">
                                        <?php echo htmlspecialchars($contact['sujet']); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--color-text-light); font-style: italic;">Aucun sujet</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $date = new DateTime($contact['created_at']);
                                echo $date->format('d/m/Y à H:i');
                                ?>
                            </td>
                            <td class="actions">
                                <a href="#" onclick="showMessage('<?php echo htmlspecialchars($contact['message'], ENT_QUOTES, 'UTF-8'); ?>'); return false;">
                                    <span>📝</span>
                                    Message
                                </a>
                                <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>">
                                    <span>📧</span>
                                    Répondre
                                </a>\n                                <a href="?section=contacts&delete_contact=<?php echo $contact['id']; ?>" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce contact ?')"
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
            <i>📞</i>
            <h3>Aucun contact</h3>
            <p>Les messages de vos visiteurs apparaîtront ici.</p>
        </div>
    <?php endif; ?>
</div>

<script>
function showMessage(message) {
    const modal = document.createElement('div');
    modal.id = 'messageModal';
    modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;';
    
    const content = document.createElement('div');
    content.style.cssText = 'background: white; padding: 30px; border-radius: 8px; max-width: 700px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);';
    
    const messageDiv = document.createElement('div');
    messageDiv.style.cssText = 'margin: 0 0 20px 0; color: #333; line-height: 1.6; font-family: monospace; background: #f5f5f5; padding: 15px; border-radius: 6px; max-height: 400px; overflow-y: auto; white-space: pre-wrap; word-wrap: break-word;';
    messageDiv.textContent = message;
    
    const title = document.createElement('h3');
    title.style.cssText = 'margin-top: 0; margin-bottom: 15px;';
    title.textContent = 'Message du Contact';
    
    const closeBtn = document.createElement('button');
    closeBtn.onclick = function() { modal.remove(); };
    closeBtn.style.cssText = 'background: #2c5aa0; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;';
    closeBtn.textContent = 'Fermer';
    
    content.appendChild(title);
    content.appendChild(messageDiv);
    content.appendChild(closeBtn);
    modal.appendChild(content);
    document.body.appendChild(modal);
    
    modal.addEventListener('click', function(e) {
        if (e.target === modal) modal.remove();
    });
}
</script>