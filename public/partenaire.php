<?php
require_once __DIR__ . '/../config/db.php';

$pageTitle = "Partenariat - EDEN";
$successMessage = '';
$errorMessage = '';

// Fonction de journalisation des erreurs
function logError($message) {
    $logFile = __DIR__ . '/../logs/partnership_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Créer le répertoire de logs s'il n'existe pas
if (!is_dir(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}

// Traitement du formulaire de partenariat
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_partnership'])) {
    try {
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $organisation = trim($_POST['organisation'] ?? '');
        $typePartenariat = trim($_POST['typePartenariat'] ?? '');
        $message = trim($_POST['message'] ?? '');
        
        if (empty($nom) || empty($email) || empty($organisation) || empty($typePartenariat) || empty($message)) {
            throw new Exception('Veuillez remplir tous les champs obligatoires.');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email invalide.');
        }
        
        // Vérifier si la table a les colonnes attendues
        $stmt = $pdo->prepare('SHOW COLUMNS FROM contacts');
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Préparer les champs et les valeurs
        $fields = [];
        $placeholders = [];
        $values = [];
        
        // Champs obligatoires
        $fields[] = 'nom';
        $values[] = $nom;
        $placeholders[] = '?';
        
        $fields[] = 'email';
        $values[] = $email;
        $placeholders[] = '?';
        
        // Champs conditionnels
        if (in_array('telephone', $columns)) {
            $fields[] = 'telephone';
            $values[] = $telephone;
            $placeholders[] = '?';
        }
        
        if (in_array('sujet', $columns)) {
            $fields[] = 'sujet';
            $values[] = 'Demande de partenariat: ' . $typePartenariat;
            $placeholders[] = '?';
        }
        
        // Construire le message complet
        $fullMessage = "Type de Partenariat: $typePartenariat\n";
        $fullMessage .= "Organisation: $organisation\n\n";
        $fullMessage .= $message;
        
        $fields[] = 'message';
        $values[] = $fullMessage;
        $placeholders[] = '?';
        
        // Ajouter le type de contact
        if (in_array('type', $columns)) {
            $fields[] = 'type';
            $values[] = 'partnership';
            $placeholders[] = '?';
        }
        
        // Construire et exécuter la requête
        $sql = 'INSERT INTO contacts (' . implode(', ', $fields) . ', created_at) VALUES (' . implode(', ', $placeholders) . ', NOW())';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        
        $successMessage = '✅ Merci ! Votre demande de partenariat a été reçue. Notre équipe vous contactera bientôt.';
        
        // Réinitialiser les champs du formulaire
        $_POST = [];
        
    } catch (Exception $e) {
        $errorMessage = '❌ Erreur : ' . htmlspecialchars($e->getMessage());
        logError('Erreur formulaire partenariat: ' . $e->getMessage());
        logError('Données POST: ' . print_r($_POST, true));
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        .partnership-container { max-width: 900px; margin: 0 auto; }
        .partnership-section { margin-bottom: 40px; }
        .partnership-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .partnership-card h3 { color: #2c5aa0; margin-top: 0; font-size: 1.3em; }
        .benefit-list { list-style: none; padding: 0; }
        .benefit-list li { padding: 10px 0; padding-left: 30px; position: relative; }
        .benefit-list li:before { content: "✓"; position: absolute; left: 0; color: #2c6e49; font-weight: bold; }
        .cta-button { 
            display: inline-block;
            background: #2c5aa0; 
            color: white; 
            padding: 12px 30px; 
            border-radius: 6px; 
            text-decoration: none; 
            font-weight: 600; 
            transition: background 0.2s;
            margin-top: 15px;
        }
        .cta-button:hover { background: #1e3f70; }
    </style>
</head>
<body>

<?php include __DIR__ . '/../includes/header.php'; ?>

<section class="hero-section" style="min-height:240px;">
    <div class="overlay"></div>
    <div class="container hero-content">
        <p class="new-here-link">Partenaires</p>
        <h1>Devenez Partenaire d'EDEN</h1>
        <p class="subtitle">Collaborons ensemble pour propager l'amour et la transformation spirituelle.</p>
    </div>
</section>

<section class="contact-map-section">
    <div class="container">
        <div class="partnership-container">
            <!-- Introduction -->
            <div class="partnership-section">
                <div class="partnership-card">
                    <h3>Pourquoi Devenir Partenaire ?</h3>
                    <p>EDEN cherche à collaborer avec des organisations, églises, entreprises et individus partageant notre vision d'un monde transformé par la foi et la spiritualité. En tant que partenaire, vous contribuez à une mission plus grande tout en bénéficiant d'avantages exclusifs.</p>
                </div>
            </div>

            <!-- Types de Partenariat -->
            <div class="partnership-section">
                <h2 style="color: #2c5aa0; margin-bottom: 20px;">Types de Partenariat</h2>
                
                <div class="partnership-card">
                    <h3>🤝 Partenariat Stratégique</h3>
                    <p>Pour les organisations désireuses de collaborer sur des projets à long terme, des événements conjoints ou des initiatives communautaires.</p>
                    <ul class="benefit-list">
                        <li>Co-organisation d'événements et retraites</li>
                        <li>Partage de ressources et expertise</li>
                        <li>Visibilité mutuelle et promotion</li>
                        <li>Développement de programmes conjoints</li>
                    </ul>
                </div>

                <div class="partnership-card">
                    <h3>💼 Partenariat Corporatif</h3>
                    <p>Pour les entreprises qui souhaitent soutenir nos initiatives et bénéficier de visibilité auprès de nos communautés.</p>
                    <ul class="benefit-list">
                        <li>Sponsoring d'événements</li>
                        <li>Branding et reconnaissance</li>
                        <li>Accès à nos réseaux et audiences</li>
                        <li>Opportunités de marketing social</li>
                    </ul>
                </div>

                <div class="partnership-card">
                    <h3>🙏 Partenariat Spirituel</h3>
                    <p>Pour les églises et organisations religieuses souhaitant promouvoir nos retraites auprès de leurs membres.</p>
                    <ul class="benefit-list">
                        <li>Tarifs préférentiels pour vos membres</li>
                        <li>Matériel promotionnel personnalisé</li>
                        <li>Co-organisation de sessions spécialisées</li>
                        <li>Support continu et accompagnement</li>
                    </ul>
                </div>

                <div class="partnership-card">
                    <h3>📱 Partenariat Digital</h3>
                    <p>Pour les plateformes, blogs, chaînes YouTube et influenceurs partageant notre mission.</p>
                    <ul class="benefit-list">
                        <li>Contenu exclusif et ressources</li>
                        <li>Programme d'affiliation</li>
                        <li>Cross-promotion et collaboration</li>
                        <li>Accès anticipé aux nouveautés</li>
                    </ul>
                </div>
            </div>

            <!-- Avantages Généraux -->
            <div class="partnership-section">
                <h2 style="color: #2c5aa0; margin-bottom: 20px;">Avantages pour Tous les Partenaires</h2>
                <div class="partnership-card">
                    <ul class="benefit-list" style="padding-left: 0;">
                        <li style="padding-left: 30px;">Reconnaissance publique sur notre site et matériel promotionnel</li>
                        <li style="padding-left: 30px;">Accès à une communauté active et engagée</li>
                        <li style="padding-left: 30px;">Rapports réguliers sur l'impact et les résultats</li>
                        <li style="padding-left: 30px;">Support dédié et communications personnalisées</li>
                        <li style="padding-left: 30px;">Opportunités d'apprentissage et de développement mutuel</li>
                        <li style="padding-left: 30px;">Flexibilité et adaptation des termes selon vos besoins</li>
                    </ul>
                </div>
            </div>

            <!-- Processus -->
            <div class="partnership-section">
                <h2 style="color: #2c5aa0; margin-bottom: 20px;">Comment Devenir Partenaire ?</h2>
                <div class="partnership-card">
                    <p><strong>Étape 1 :</strong> Contactez-nous via le formulaire de contact ou par email</p>
                    <p><strong>Étape 2 :</strong> Présentez votre organisation et vos objectifs</p>
                    <p><strong>Étape 3 :</strong> Discutez des options de partenariat avec notre équipe</p>
                    <p><strong>Étape 4 :</strong> Finalisez les détails et commencez la collaboration</p>
                </div>
            </div>

            <!-- Call to Action -->
            <div class="partnership-section">
                <div class="partnership-card" style="background: linear-gradient(135deg, #2c5aa0 0%, #1e3f70 100%); color: white; text-align: center;">
                    <h3 style="color: white; margin-top: 0;">Prêt à Collaborer ?</h3>
                    <p>Contactez notre équipe dès aujourd'hui pour explorer les opportunités de partenariat qui conviendraient à votre organisation.</p>
                    <button type="button" onclick="openPartnershipModal()" class="cta-button" style="background: white; color: #2c5aa0; cursor: pointer; border: none;" onmouseover="this.style.background='#f5f5f5'" onmouseout="this.style.background='white'">
                        Nous Contacter
                    </button>
                </div>
            </div>

            <!-- FAQ Partenariat -->
            <div class="partnership-section">
                <h2 style="color: #2c5aa0; margin-bottom: 20px;">Questions Fréquentes sur les Partenariats</h2>
                
                <div class="partnership-card">
                    <h4 style="color: #2c5aa0;">Y a-t-il des frais de partenariat ?</h4>
                    <p>Les frais varient selon le type et l'envergure du partenariat. Certains partenariats sont basés sur l'échange mutuel sans frais, tandis que d'autres peuvent inclure des contributions ou des investissements. Nous discuterons des détails lors de nos conversations initiales.</p>
                </div>

                <div class="partnership-card">
                    <h4 style="color: #2c5aa0;">Combien de temps dure un partenariat ?</h4>
                    <p>La durée dépend du type de partenariat. Certains peuvent être ponctuels (pour un événement spécifique), tandis que d'autres sont à long terme. Nous négocions les termes ensemble.</p>
                </div>

                <div class="partnership-card">
                    <h4 style="color: #2c5aa0;">Quel type d'engagement est requis ?</h4>
                    <p>L'engagement dépend du partenariat choisi. Certains nécessitent une participation active, d'autres sont plus minimalistes. Nous personnalisons l'arrangement selon vos capacités.</p>
                </div>

                <div class="partnership-card">
                    <h4 style="color: #2c5aa0;">Comment mesure-t-on le succès d'un partenariat ?</h4>
                    <p>Nous établissons des KPIs (indicateurs clés de performance) clairs dès le départ et effectuons des révisions régulières pour garantir que le partenariat atteint les objectifs de tous les côtés.</p>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="partnership-section" style="text-align: center; margin-top: 40px;">
                <p style="color: #666; font-size: 1.1em;">Vous avez des questions ? N'hésitez pas à nous contacter !</p>
                <button type="button" onclick="openContactModal()" class="cta-button" style="cursor: pointer; border: none;">Envoyer un Message</button>
            </div>
        </div>
    </div>
</section>

<!-- Modal Formulaire de Partenariat -->
<div id="partnershipModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; overflow-y:auto;">
    <div style="background:white; margin:20px auto; max-width:700px; border-radius:8px; box-shadow:0 4px 20px rgba(0,0,0,0.15); max-height:90vh; overflow-y:auto;">
        <div style="padding:20px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e0e0e0;">
            <h3 style="margin:0; color:#2c5aa0;">Demande de Partenariat</h3>
            <button type="button" onclick="closePartnershipModal()" style="background:none; border:none; font-size:24px; cursor:pointer; color:#666;">&times;</button>
        </div>
        <div style="padding:20px;">
            <?php if (!empty($successMessage)): ?>
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 15px; border-left: 4px solid #155724;">
                    <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($errorMessage)): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 15px; border-left: 4px solid #721c24;">
                    <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" style="display: grid; gap: 15px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Nom Complet *</label>
                        <input type="text" name="nom" required placeholder="Votre nom" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:1em; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Email *</label>
                        <input type="email" name="email" required placeholder="votre@email.com" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:1em; box-sizing: border-box;">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Téléphone</label>
                        <input type="tel" name="telephone" placeholder="+243..." style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:1em; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Organisation *</label>
                        <input type="text" name="organisation" required placeholder="Nom de l'organisation" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:1em; box-sizing: border-box;">
                    </div>
                </div>
                
                <div>
                    <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Type de Partenariat *</label>
                    <select name="typePartenariat" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:1em; box-sizing: border-box;">
                        <option value="">-- Sélectionnez une option --</option>
                        <option value="Partenariat Stratégique">Partenariat Stratégique</option>
                        <option value="Partenariat Corporatif">Partenariat Corporatif</option>
                        <option value="Partenariat Spirituel">Partenariat Spirituel</option>
                        <option value="Partenariat Digital">Partenariat Digital</option>
                        <option value="Autre">Autre</option>
                    </select>
                </div>
                
                <div>
                    <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Message *</label>
                    <textarea name="message" rows="5" required placeholder="Décrivez votre demande de partenariat..." style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:1em; resize:vertical; box-sizing: border-box;"></textarea>
                </div>
                
                <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                    <button type="button" onclick="closePartnershipModal()" style="background:#e0e0e0; color:#333; padding:10px 20px; border:none; border-radius:6px; cursor:pointer; font-weight:600;">Annuler</button>
                    <button type="submit" name="submit_partnership" style="background:#2c5aa0; color:white; padding:10px 20px; border:none; border-radius:6px; cursor:pointer; font-weight:600; transition:background 0.2s;" onmouseover="this.style.background='#1e3f70'" onmouseout="this.style.background='#2c5aa0'">Envoyer ma Demande</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openPartnershipModal() {
        document.getElementById('partnershipModal').style.display = 'flex';
        document.getElementById('partnershipModal').style.alignItems = 'center';
        document.body.style.overflow = 'hidden';
    }
    
    function closePartnershipModal() {
        document.getElementById('partnershipModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('partnershipModal');
        if (event.target === modal) {
            closePartnershipModal();
        }
    });
</script>

<!-- Modal Formulaire de Contact -->
<div id="contactModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; overflow-y:auto;">
    <div style="background:white; margin:20px auto; max-width:700px; border-radius:8px; box-shadow:0 4px 20px rgba(0,0,0,0.15); max-height:90vh; overflow-y:auto;">
        <div style="padding:20px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e0e0e0;">
            <h3 style="margin:0; color:#2c5aa0;">Envoyer un Message</h3>
            <button type="button" onclick="closeContactModal()" style="background:none; border:none; font-size:24px; cursor:pointer; color:#666;">&times;</button>
        </div>
        <div style="padding:20px;">
            <form method="post" action="contact.php" style="display: grid; gap: 15px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Nom Complet *</label>
                        <input type="text" name="nom" required placeholder="Votre nom" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:1em; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Email *</label>
                        <input type="email" name="email" required placeholder="votre@email.com" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:1em; box-sizing: border-box;">
                    </div>
                </div>
                
                <div>
                    <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Téléphone *</label>
                    <input type="tel" name="telephone" required placeholder="+243..." style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:1em; box-sizing: border-box;">
                </div>
                
                <div>
                    <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Sujet</label>
                    <input type="text" name="sujet" placeholder="Sujet de votre message" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:1em; box-sizing: border-box;">
                </div>
                
                <div>
                    <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Message *</label>
                    <textarea name="message" rows="5" required placeholder="Votre message..." style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:1em; resize:vertical; box-sizing: border-box;"></textarea>
                </div>
                
                <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                    <button type="button" onclick="closeContactModal()" style="background:#e0e0e0; color:#333; padding:10px 20px; border:none; border-radius:6px; cursor:pointer; font-weight:600;">Annuler</button>
                    <button type="submit" style="background:#2c5aa0; color:white; padding:10px 20px; border:none; border-radius:6px; cursor:pointer; font-weight:600; transition:background 0.2s;" onmouseover="this.style.background='#1e3f70'" onmouseout="this.style.background='#2c5aa0'">Envoyer le Message</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openContactModal() {
        document.getElementById('contactModal').style.display = 'flex';
        document.getElementById('contactModal').style.alignItems = 'center';
        document.body.style.overflow = 'hidden';
    }
    
    function closeContactModal() {
        document.getElementById('contactModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('contactModal');
        if (event.target === modal) {
            closeContactModal();
        }
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
