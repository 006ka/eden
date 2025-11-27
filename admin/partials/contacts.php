<div class="admin-section" id="contacts">
    <h2>Messages de contact <span class="badge"><?php echo count($contacts); ?></span></h2>

    <?php
        $contactsPerPage = 15;
        $contactPage = isset($_GET['contact_page']) && is_numeric($_GET['contact_page']) ? max(1, (int)$_GET['contact_page']) : 1;
        $contactsTotal = count($contacts);
        $contactOffset = ($contactPage - 1) * $contactsPerPage;
        $contactsPageItems = array_slice($contacts, $contactOffset, $contactsPerPage);
        $contactHasPrev = $contactPage > 1;
        $contactHasNext = $contactOffset + $contactsPerPage < $contactsTotal;
    ?>

    <?php if (empty($contactsPageItems)): ?>
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
                <?php foreach ($contactsPageItems as $c): ?>
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

        <?php if ($contactsTotal > $contactsPerPage): ?>
            <div style="margin-top:10px; display:flex; justify-content:space-between; align-items:center; font-size:0.9rem;">
                <div>
                    Page <?php echo $contactPage; ?> / <?php echo max(1, (int)ceil($contactsTotal / $contactsPerPage)); ?>
                </div>
                <div style="display:flex; gap:8px;">
                    <?php if ($contactHasPrev): ?>
                        <a href="?section=contacts&amp;contact_page=<?php echo $contactPage - 1; ?>" class="btn btn-secondary" style="padding:4px 10px; font-size:0.85rem;">&laquo; Précédent</a>
                    <?php endif; ?>
                    <?php if ($contactHasNext): ?>
                        <a href="?section=contacts&amp;contact_page=<?php echo $contactPage + 1; ?>" class="btn btn-secondary" style="padding:4px 10px; font-size:0.85rem;">Suivant &raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
