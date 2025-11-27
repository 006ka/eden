<div class="admin-section" id="dashboard">
    <h2>Tableau de bord</h2>
    <p>Vue d'ensemble du site</p>

    <div style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:18px;">
        <div style="background:#fff;padding:12px;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,0.05);min-width:160px;">
            <strong>Retraites</strong>
            <div style="font-size:24px; margin-top:6px;"><?php echo count($retreats); ?></div>
        </div>
        <div style="background:#fff;padding:12px;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,0.05);min-width:160px;">
            <strong>Programmes</strong>
            <div style="font-size:24px; margin-top:6px;"><?php echo isset($programmes) ? count($programmes) : 0; ?></div>
        </div>
        <div style="background:#fff;padding:12px;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,0.05);min-width:160px;">
            <strong>Photos</strong>
            <div style="font-size:24px; margin-top:6px;"><?php echo count($gallery); ?></div>
        </div>
        <div style="background:#fff;padding:12px;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,0.05);min-width:160px;">
            <strong>Messages</strong>
            <div style="font-size:24px; margin-top:6px;"><?php echo count($contacts); ?></div>
        </div>
        <div style="background:#fff;padding:12px;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,0.05);min-width:160px;">
            <strong>Inscriptions</strong>
            <div style="font-size:24px; margin-top:6px;"><?php echo count($inscriptions); ?></div>
        </div>
        <?php if (!empty($uploadsStats)): ?>
            <div style="background:#fff;padding:12px;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,0.05);min-width:190px;">
                <strong>Fichiers uploads</strong>
                <div style="font-size:18px; margin-top:4px;">
                    Total : <strong><?php echo (int)$uploadsStats['total']; ?></strong>
                </div>
                <div style="font-size:12px; color:#555; margin-top:2px;">
                    Référencés : <?php echo (int)$uploadsStats['referenced']; ?><br>
                    Orphelins : <?php echo count($uploadsStats['orphans']); ?>
                </div>
                <div style="margin-top:6px;">
                    <a href="?section=tools" style="font-size:12px; color:var(--color-primary); text-decoration:underline;">Détail &gt;</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="admin-section" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap:16px;">
        <div>
            <h3>Dernières retraites</h3>
            <?php if (empty($retreats)): ?>
                <p>Aucune retraite enregistrée.</p>
            <?php else: ?>
                <ul>
                    <?php $i=0; foreach ($retreats as $r): if ($i++>=5) break; ?>
                        <li><?php echo htmlspecialchars($r['titre']); ?><?php if (!empty($r['date_debut'])): ?> - <?php echo htmlspecialchars($r['date_debut']); ?><?php endif; ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div>
            <h3>Derniers programmes</h3>
            <?php if (empty($programmes)): ?>
                <p>Aucun programme enregistré.</p>
            <?php else: ?>
                <ul>
                    <?php $j=0; foreach ($programmes as $p): if ($j++>=5) break; ?>
                        <li><?php echo htmlspecialchars($p['titre']); ?><?php if (!empty($p['date_debut'])): ?> - <?php echo htmlspecialchars($p['date_debut']); ?><?php endif; ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

</div>
