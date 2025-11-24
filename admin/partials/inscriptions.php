<div class="admin-section" id="inscriptions">
    <h2>Inscriptions à la retraite <span class="badge"><?php echo count($inscriptions); ?></span></h2>
    <?php if (empty($inscriptions)): ?>
        <p>Aucune inscription.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Âge</th>
                    <th>Église</th>
                    <th>Besoin particulier</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inscriptions as $i): ?>
                    <tr>
                        <td><?php echo (int)$i['id']; ?></td>
                        <td><?php echo htmlspecialchars($i['nom']); ?></td>
                        <td><?php echo htmlspecialchars($i['email']); ?></td>
                        <td><?php echo htmlspecialchars($i['telephone']); ?></td>
                        <td><?php echo (int)$i['age']; ?></td>
                        <td><?php echo htmlspecialchars($i['eglise']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($i['besoins'])); ?></td>
                        <td><?php echo htmlspecialchars($i['created_at']); ?></td>
                        <td class="actions">
                            <a href="?delete_inscription=<?php echo (int)$i['id']; ?>" onclick="return confirm('Supprimer cette inscription ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
