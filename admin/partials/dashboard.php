<?php
// Récupération des statistiques avancées
$stats = [
    'total_retreats' => $pdo->query('SELECT COUNT(*) FROM retreats')->fetchColumn(),
    'total_gallery' => $pdo->query('SELECT COUNT(*) FROM gallery')->fetchColumn(),
    'total_contacts' => $pdo->query('SELECT COUNT(*) FROM contacts')->fetchColumn(),
    'total_inscriptions' => $pdo->query('SELECT COUNT(*) FROM inscriptions')->fetchColumn(),
    'recent_contacts' => $pdo->query('SELECT COUNT(*) FROM contacts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)')->fetchColumn(),
    'recent_inscriptions' => $pdo->query('SELECT COUNT(*) FROM inscriptions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)')->fetchColumn(),
    'active_retreats' => $pdo->query('SELECT COUNT(*) FROM retreats WHERE date_debut >= CURDATE() OR date_fin >= CURDATE()')->fetchColumn(),
];

// Activité récente étendue
$recent_activity = $pdo->query('
    (SELECT "contact" as type, nom, email, created_at, "Nouveau contact" as action FROM contacts ORDER BY created_at DESC LIMIT 4)
    UNION ALL
    (SELECT "inscription" as type, nom, email, created_at, "Nouvelle inscription" as action FROM inscriptions ORDER BY created_at DESC LIMIT 4)
    UNION ALL
    (SELECT "retraite" as type, titre as nom, "" as email, created_at, "Retraite créée" as action FROM retreats ORDER BY created_at DESC LIMIT 2)
    UNION ALL
    (SELECT "galerie" as type, titre as nom, "" as email, created_at, "Photo ajoutée" as action FROM gallery ORDER BY created_at DESC LIMIT 2)
    ORDER BY created_at DESC LIMIT 8
')->fetchAll(PDO::FETCH_ASSOC);

// Statistiques mensuelles
$monthly_stats = $pdo->query('
    SELECT 
        MONTH(created_at) as month,
        COUNT(*) as count,
        "contacts" as type 
    FROM contacts 
    WHERE YEAR(created_at) = YEAR(CURDATE())
    GROUP BY MONTH(created_at)
    ORDER BY month DESC
    LIMIT 6
')->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="admin-section">
    <div class="dashboard-header" style="display: flex; justify-content: between; align-items: center; margin-bottom: 30px;">
        <div>
            <h2 style="margin: 0 0 8px 0;">Tableau de Bord</h2>
            <p style="margin: 0; color: var(--color-text-light);">Bienvenue dans votre espace d'administration EDEN</p>
        </div>
        <div style="display: flex; align-items: center; gap: 15px;">
            <span style="color: var(--color-text-light);"><?php echo date('d/m/Y'); ?></span>
            <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--admin-accent); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                A
            </div>
        </div>
    </div>
    
    <!-- Métriques principales -->
    <div class="dashboard-metrics">
        <div class="metric-card">
            <div class="metric-value"><?php echo $stats['total_retreats']; ?></div>
            <div class="metric-label">Retraites Actives</div>
            <div class="metric-icon">📅</div>
        </div>
        
        <div class="metric-card success">
            <div class="metric-value"><?php echo $stats['total_inscriptions']; ?></div>
            <div class="metric-label">Inscriptions</div>
            <div class="metric-icon">✍️</div>
        </div>
        
        <div class="metric-card warning">
            <div class="metric-value"><?php echo $stats['recent_contacts']; ?></div>
            <div class="metric-label">Nouveaux Contacts</div>
            <div class="metric-icon">📞</div>
        </div>
        
        <div class="metric-card info">
            <div class="metric-value"><?php echo $stats['total_gallery']; ?></div>
            <div class="metric-label">Photos Galerie</div>
            <div class="metric-icon">🖼️</div>
        </div>
    </div>
    
    <div class="dashboard-grid">
        <!-- Colonne principale -->
        <div>
            <!-- Actions rapides -->
            <div class="admin-section">
                <h3>Actions Rapides</h3>
                <div class="quick-actions">
                    <a href="?section=retreats&add=new" class="action-btn">
                        <i>➕</i>
                        <span>Nouvelle Retraite</span>
                    </a>
                    
                    <a href="?section=galerie&add=new" class="action-btn">
                        <i>🖼️</i>
                        <span>Ajouter Photo</span>
                    </a>
                    
                    <a href="?section=inscriptions_gestion" class="action-btn">
                        <i>👥</i>
                        <span>Voir Inscriptions</span>
                    </a>
                </div>
            </div>
            
            <!-- Statistiques détaillées -->
            <div class="admin-section">
                <h3>Statistiques Détaillées</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Retraites à venir</h3>
                        <div class="stat-number"><?php echo $stats['active_retreats']; ?></div>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Nouvelles inscriptions (7j)</h3>
                        <div class="stat-number"><?php echo $stats['recent_inscriptions']; ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar droite -->
        <div>
            <!-- Activité récente -->
            <div class="admin-section">
                <div class="activity-header">
                    <h3 style="margin: 0;">Activité Récente</h3>
                    <a href="#" style="color: var(--admin-accent); text-decoration: none; font-size: 0.9rem;">Voir tout</a>
                </div>
                <div class="recent-activity">
                    <div class="activity-list">
                        <?php if (!empty($recent_activity)): ?>
                            <?php foreach ($recent_activity as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <?php 
                                        $icon = match($activity['type']) {
                                            'contact' => '📧',
                                            'inscription' => '✍️',
                                            'retraite' => '📅',
                                            'galerie' => '🖼️',
                                            default => '📌'
                                        };
                                        echo $icon;
                                        ?>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">
                                            <?php echo htmlspecialchars($activity['nom']); ?>
                                        </div>
                                        <div class="activity-time">
                                            <?php 
                                            $date = new DateTime($activity['created_at']);
                                            echo $activity['action'] . ' • ' . $date->format('d/m à H:i');
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state" style="padding: 40px 20px;">
                                <i>📊</i>
                                <h3 style="font-size: 1.1rem;">Aucune activité récente</h3>
                                <p style="font-size: 0.9rem;">Les nouvelles activités apparaîtront ici.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Outils admin -->
<div class="admin-section">
    <h3>Outils d'Administration</h3>
    <div class="quick-actions">
        <a href="?section=tools" class="action-btn">
            <i>🔧</i>
            <span>Outils Système</span>
        </a>
        
        <a href="../index.php" target="_blank" class="action-btn">
            <i>👁️</i>
            <span>Voir le Site</span>
        </a>
        
        <a href="?section=account" class="action-btn">
            <i>👤</i>
            <span>Mon Compte</span>
        </a>
        
        <a href="?logout=1" class="action-btn">
            <i>🚪</i>
            <span>Déconnexion</span>
        </a>
    </div>
</div>