<div class="admin-section" id="contacts">
    <h2>Messages de contact <span class="badge"><?php echo count($contacts); ?></span></h2>
    <?php if (empty($contacts)): ?>
        <p>Aucun message.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $c): ?>
                    <tr>
                        <td><?php echo (int)$c['id']; ?></td>
                        <td><?php echo htmlspecialchars($c['nom']); ?></td>
                        <td><?php echo htmlspecialchars($c['email']); ?></td>
                        <td><?php echo htmlspecialchars($c['telephone']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($c['message'])); ?></td>
                        <td><?php echo htmlspecialchars($c['created_at']); ?></td>
                        <td class="actions">
                            <a href="?delete_contact=<?php echo (int)$c['id']; ?>" onclick="return confirm('Supprimer ce message ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
