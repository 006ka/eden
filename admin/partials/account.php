<?php
// Récupérer les informations de l'admin connecté
$username = $_SESSION['admin_username'] ?? null;
$admin_info = null;

if ($username) {
    $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE username = ?');
    $stmt->execute([$username]);
    $admin_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Si c'est la première connexion, initialiser les champs vides
    if ($admin_info && !isset($admin_info['full_name'])) {
        $admin_info['full_name'] = '';
        $admin_info['email'] = '';
    }
}

// Afficher les messages de succès ou d'erreur
if (isset($successMessage)) {
    echo '<div class="alert alert-success">' . htmlspecialchars($successMessage) . '</div>';
}
if (isset($errorMessage)) {
    echo '<div class="alert alert-error">' . htmlspecialchars($errorMessage) . '</div>';
}
?>

<div class="admin-section">
    <h2>Mon Compte</h2>
    
    <div class="dashboard-grid">
        <!-- Informations du compte -->
        <div class="admin-section">
            <h3>Informations du Compte</h3>
            
            <?php if ($admin_info): ?>
            <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 30px; padding: 20px; background: var(--color-bg); border-radius: 12px;">
                <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--admin-accent); display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem; font-weight: bold;">
                    <?php echo strtoupper(substr($admin_info['username'], 0, 1)); ?>
                </div>
                <div>
                    <div style="font-size: 1.3rem; font-weight: 700; margin-bottom: 5px;">
                        <?php echo htmlspecialchars($admin_info['full_name'] ?? $admin_info['username']); ?>
                    </div>
                    <div style="color: var(--color-text-light);">
                        <?php echo htmlspecialchars($admin_info['username']); ?>
                    </div>
                    <div style="color: var(--color-text-light); font-size: 0.9rem; margin-top: 5px;">
                        Membre depuis <?php echo (new DateTime($admin_info['created_at']))->format('d/m/Y'); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" class="form-modern">
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label for="full_name">Nom complet</label>
                        <input type="text" id="full_name" name="full_name" 
                               value="<?php echo htmlspecialchars($admin_info['full_name'] ?? ''); ?>" 
                               placeholder="Votre nom complet">
                    </div>
                    
                    <div class="form-group" style="flex: 1;">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required
                               value="<?php echo htmlspecialchars($admin_info['email'] ?? ''); ?>" 
                               placeholder="votre@email.com">
                    </div>
                </div>

                <div class="form-row" style="margin-top: 20px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="profile_image">Photo de profil</label>
                        <input type="file" id="profile_image" name="profile_image" accept="image/*">
                        <p class="help-text">Formats acceptés : JPG, PNG, GIF (max 2 Mo)</p>
                        
                        <?php if (!empty($admin_info['profile_image'])): ?>
                            <div style="margin-top: 15px;">
                                <img src="<?php echo htmlspecialchars($admin_info['profile_image']); ?>" 
                                     alt="Photo de profil" 
                                     style="max-width: 150px; max-height: 150px; border-radius: 8px; margin-top: 10px;">
                                <input type="hidden" name="current_profile_image" value="<?php echo htmlspecialchars($admin_info['profile_image'] ?? ''); ?>">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="margin-top: 25px;">
                    <button type="submit" name="update_profile" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px;">
                        <span>💾</span>
                        Mettre à jour le profil
                    </button>
                </div>
            </form>
        </div>

        <!-- Sécurité -->
        <div class="admin-section">
            <h3>Sécurité</h3>
            
            <form method="post" class="form-modern">
                <div class="form-group">
                    <label for="current_password">Mot de passe actuel</label>
                    <input type="password" id="current_password" name="current_password" required
                           placeholder="Votre mot de passe actuel">
                    <p class="help-text">Pour des raisons de sécurité, vous devez confirmer votre mot de passe actuel</p>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="new_password">Nouveau mot de passe</label>
                        <input type="password" id="new_password" name="new_password" required minlength="8"
                               placeholder="Au moins 8 caractères">
                        <div class="password-strength">
                            <div class="strength-bar"></div>
                            <div class="strength-text">Force du mot de passe</div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" required
                               placeholder="Retapez votre nouveau mot de passe">
                    </div>
                </div>

                <div style="margin-top: 25px;">
                    <button type="submit" name="change_password" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px;">
                        <span>🔒</span>
                        Changer le mot de passe
                    </button>
                </div>
            </form>
            
            <style>
                .password-strength {
                    margin-top: 10px;
                }
                .strength-bar {
                    height: 5px;
                    background: #e0e0e0;
                    border-radius: 3px;
                    margin-bottom: 5px;
                    overflow: hidden;
                }
                .strength-bar:after {
                    content: '';
                    display: block;
                    height: 100%;
                    width: 0;
                    background: #ff4444;
                    transition: width 0.3s, background 0.3s;
                }
                .strength-text {
                    font-size: 0.8em;
                    color: #666;
                }
                input[type="password"]:focus + .password-strength .strength-bar:after {
                    background: #ffbb33;
                }
                input[type="password"].medium + .password-strength .strength-bar:after {
                    width: 60%;
                    background: #ffbb33;
                }
                input[type="password"].strong + .password-strength .strength-bar:after {
                    width: 100%;
                    background: #00C851;
                }
            </style>
            
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const passwordInput = document.getElementById('new_password');
                const passwordStrength = document.querySelector('.password-strength');
                
                if (passwordInput) {
                    passwordInput.addEventListener('input', function() {
                        const password = this.value;
                        const strength = checkPasswordStrength(password);
                        
                        // Mettre à jour la barre de force
                        const strengthBar = this.nextElementSibling.querySelector('.strength-bar');
                        const strengthText = this.nextElementSibling.querySelector('.strength-text');
                        
                        // Réinitialiser les classes
                        this.classList.remove('weak', 'medium', 'strong');
                        
                        if (password.length === 0) {
                            strengthBar.style.width = '0%';
                            strengthBar.style.background = '#e0e0e0';
                            strengthText.textContent = '';
                        } else {
                            strengthBar.style.width = strength.percentage + '%';
                            strengthBar.style.background = strength.color;
                            strengthText.textContent = strength.text;
                            this.classList.add(strength.class);
                        }
                    });
                }
                
                function checkPasswordStrength(password) {
                    let strength = 0;
                    
                    // Longueur minimale
                    if (password.length >= 8) strength += 1;
                    
                    // Contient des lettres majuscules
                    if (password.match(/[A-Z]+/)) strength += 1;
                    
                    // Contient des chiffres
                    if (password.match(/[0-9]+/)) strength += 1;
                    
                    // Contient des caractères spéciaux
                    if (password.match(/[!@#$%^&*(),.?":{}|<>]+/)) strength += 1;
                    
                    // Déterminer la force
                    if (password.length === 0) {
                        return { percentage: 0, color: '#e0e0e0', text: '', class: '' };
                    } else if (password.length < 4) {
                        return { percentage: 25, color: '#ff4444', text: 'Très faible', class: 'weak' };
                    } else if (strength < 2) {
                        return { percentage: 40, color: '#ffbb33', text: 'Faible', class: 'weak' };
                    } else if (strength < 4) {
                        return { percentage: 70, color: '#ffbb33', text: 'Moyen', class: 'medium' };
                    } else {
                        return { percentage: 100, color: '#00C851', text: 'Fort', class: 'strong' };
                    }
                }
                
                // Validation du formulaire de changement de mot de passe
                const passwordForm = document.querySelector('form[action*="change_password"]');
                if (passwordForm) {
                    passwordForm.addEventListener('submit', function(e) {
                        const newPassword = document.getElementById('new_password').value;
                        const confirmPassword = document.getElementById('confirm_password').value;
                        
                        if (newPassword !== confirmPassword) {
                            e.preventDefault();
                            alert('Les mots de passe ne correspondent pas.');
                            return false;
                        }
                        
                        if (newPassword.length < 8) {
                            e.preventDefault();
                            alert('Le mot de passe doit contenir au moins 8 caractères.');
                            return false;
                        }
                        
                        return true;
                    });
                }
            });
            </script>
        </div>
    </div>

    <!-- Statistiques personnelles -->
    <div class="admin-section">
        <h3>Mes Statistiques</h3>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Retraites créées</h3>
                <div class="stat-number"><?php echo $stats['total_retreats']; ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Photos ajoutées</h3>
                <div class="stat-number"><?php echo $stats['total_gallery']; ?></div>
            </div>
        </div>
    </div>
</div>