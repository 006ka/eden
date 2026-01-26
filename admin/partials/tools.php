<div class="admin-section">
    <h2>Outils d'Administration</h2>
    
    <div class="dashboard-grid">
        <!-- Gestion des fichiers -->
        <div class="admin-section">
            <h3>Gestion des Fichiers</h3>
            
            <?php if ($uploadsStats): ?>
            <div style="background: var(--color-bg); padding: 20px; border-radius: 12px; margin-bottom: 20px;">
                <h4 style="margin: 0 0 15px 0; color: var(--color-text);">Statistiques des Uploads</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                    <div style="text-align: center; padding: 15px; background: var(--admin-surface); border-radius: 8px;">
                        <div style="font-size: 2rem; font-weight: 800; color: var(--admin-accent);">
                            <?php echo $uploadsStats['total']; ?>
                        </div>
                        <div style="font-size: 0.9rem; color: var(--color-text-light);">Fichiers totaux</div>
                    </div>
                    <div style="text-align: center; padding: 15px; background: var(--admin-surface); border-radius: 8px;">
                        <div style="font-size: 2rem; font-weight: 800; color: var(--color-success);">
                            <?php echo $uploadsStats['referenced']; ?>
                        </div>
                        <div style="font-size: 0.9rem; color: var(--color-text-light);">Fichiers utilisés</div>
                    </div>
                    <div style="text-align: center; padding: 15px; background: var(--admin-surface); border-radius: 8px;">
                        <div style="font-size: 2rem; font-weight: 800; color: var(--color-warning);">
                            <?php echo count($uploadsStats['orphans']); ?>
                        </div>
                        <div style="font-size: 0.9rem; color: var(--color-text-light);">Fichiers orphelins</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Régénération des miniatures -->
            <form method="post">
                <div style="background: var(--color-bg); padding: 20px; border-radius: 12px; margin-bottom: 20px;">
                    <h4 style="margin: 0 0 15px 0; color: var(--color-text);">Régénération des Miniatures</h4>
                    <p style="color: var(--color-text-light); margin-bottom: 15px;">
                        Crée des miniatures pour toutes les images qui n'en ont pas encore.
                    </p>
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" name="resize_originals" value="1">
                            <span>Redimensionner aussi les images originales (max 1200px)</span>
                        </label>
                    </div>

                    <button type="submit" name="run_thumbs" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px;">
                        <span>🔄</span>
                        Lancer la régénération
                    </button>
                </div>
            </form>

            <?php if (!empty($tool_message)): ?>
                <div class="admin-alert <?php echo strpos($tool_message, 'erreur') !== false ? 'admin-alert-error' : 'admin-alert-success'; ?>">
                    <span><?php echo strpos($tool_message, 'erreur') !== false ? '⚠️' : '✅'; ?></span>
                    <?php echo htmlspecialchars($tool_message); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Maintenance -->
        <div class="admin-section">
            <h3>Maintenance</h3>
            
            <div style="background: var(--color-bg); padding: 20px; border-radius: 12px; margin-bottom: 20px;">
                <h4 style="margin: 0 0 15px 0; color: var(--color-text);">Base de Données</h4>
                <p style="color: var(--color-text-light); margin-bottom: 15px;">
                    Optimisez et nettoyez votre base de données.
                </p>
                
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <button type="button" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 8px;">
                        <span>🧹</span>
                        Nettoyer le cache
                    </button>
                    <button type="button" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 8px;">
                        <span>📊</span>
                        Optimiser les tables
                    </button>
                </div>
            </div>

            <div style="background: var(--color-bg); padding: 20px; border-radius: 12px;">
                <h4 style="margin: 0 0 15px 0; color: var(--color-text);">Sauvegarde</h4>
                <p style="color: var(--color-text-light); margin-bottom: 15px;">
                    Sauvegardez vos données importantes.
                </p>
                
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <button type="button" class="btn btn-success" style="display: inline-flex; align-items: center; gap: 8px;">
                        <span>💾</span>
                        Sauvegarder la BDD
                    </button>
                    <button type="button" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 8px;">
                        <span>🖼️</span>
                        Sauvegarder les images
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations système -->
    <div class="admin-section">
        <h3>Informations Système</h3>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Version PHP</h3>
                <div class="stat-number"><?php echo PHP_VERSION; ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Version MySQL</h3>
                <div class="stat-number">
                    <?php 
                    try {
                        $version = $pdo->query('SELECT VERSION()')->fetchColumn();
                        echo explode('-', $version)[0];
                    } catch (Exception $e) {
                        echo 'N/A';
                    }
                    ?>
                </div>
            </div>
            
            <div class="stat-card">
                <h3>Espace disque</h3>
                <div class="stat-number">
                    <?php
                    $free = disk_free_space(__DIR__);
                    $total = disk_total_space(__DIR__);
                    $used = $total - $free;
                    echo round($used / 1024 / 1024 / 1024, 1) . 'GB';
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>