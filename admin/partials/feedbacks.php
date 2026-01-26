<div class="admin-section">
    <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
        <h2 style="margin: 0;">Gestion des Feedbacks & Suggestions</h2>
        <div style="display: flex; gap: 15px; align-items: center;">
            <span style="color: var(--color-text-light); font-size: 0.9rem;">
                Total: <?php echo count($feedbacks); ?> feedback(s)
            </span>
        </div>
    </div>

    <?php if (isset($_GET['feedback_deleted'])): ?>
        <div class="admin-alert admin-alert-success">
            <span>✅</span>
            Feedback supprimé avec succès !
        </div>
    <?php endif; ?>

    <?php if (!empty($feedbacks)): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Sujet</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feedbacks as $feedback): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600;">
                                    <?php echo htmlspecialchars($feedback['nom'] ?? ''); ?>
                                </div>
                            </td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($feedback['email']); ?>" 
                                   style="color: var(--admin-accent); text-decoration: none;">
                                    <?php echo htmlspecialchars($feedback['email']); ?>
                                </a>
                            </td>
                            <td>
                                <?php if (isset($feedback['sujet']) && !empty(trim($feedback['sujet']))): ?>
                                    <span style="font-weight: 600; color: var(--admin-accent);">
                                        <?php echo htmlspecialchars($feedback['sujet']); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--color-text-light); font-style: italic;">Aucun sujet</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $date = new DateTime($feedback['created_at']);
                                echo $date->format('d/m/Y à H:i');
                                ?>
                            </td>
                            <td class="actions">
                                <a href="#" onclick="showMessage('<?php echo addslashes($feedback['message']); ?>')">
                                    <span>📝</span>
                                    Afficher
                                </a>
                                <a href="mailto:<?php echo htmlspecialchars($feedback['email']); ?>">
                                    <span>📧</span>
                                    Répondre
                                </a>
                                <a href="?section=feedbacks&delete_feedback=<?php echo $feedback['id']; ?>" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce feedback ?')"
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
        <div class="admin-alert admin-alert-info">
            <span>ℹ️</span>
            Aucun feedback reçu pour le moment.
        </div>
    <?php endif; ?>
</div>

<script>
    function showMessage(message) {
        const modal = document.createElement('div');
        modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;';
        
        const content = document.createElement('div');
        content.style.cssText = 'background: white; padding: 30px; border-radius: 8px; max-width: 600px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); word-wrap: break-word; white-space: pre-wrap;';
        content.innerHTML = '<h3 style="margin-top: 0; margin-bottom: 15px;">Message</h3>' + 
                          '<p style="margin: 0 0 20px 0; color: #333; line-height: 1.6;">' + 
                          htmlEscape(message) + 
                          '</p>' +
                          '<button onclick="this.closest(\'div\').remove()" style="background: #2c5aa0; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Fermer</button>';
        
        modal.appendChild(content);
        document.body.appendChild(modal);
        
        modal.addEventListener('click', function(e) {
            if (e.target === modal) modal.remove();
        });
    }
    
    function htmlEscape(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
