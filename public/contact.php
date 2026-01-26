<?php
// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialiser les variables
$successMessage = '';
$errorMessage = '';
$isFeedback = false;

// Journalisation des erreurs
function logError($message) {
    $logFile = __DIR__ . '/../logs/contact_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Créer le répertoire de logs s'il n'existe pas
if (!is_dir(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}

// Initialiser la connexion à la base de données
try {
    require_once __DIR__ . '/../config/db.php';
} catch (PDOException $e) {
    logError('Erreur de connexion à la base de données: ' . $e->getMessage());
    die('Erreur de connexion à la base de données. Veuillez réessayer plus tard.');
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    logError('Début du traitement du formulaire POST');
    logError('Données POST reçues : ' . print_r($_POST, true));
    // Vérifier si c'est un formulaire de feedback
    if (isset($_POST['feedback_form']) && $_POST['feedback_form'] === '1') {
        $isFeedback = true;
        $nom = trim($_POST['feedback_nom'] ?? '');
        $email = trim($_POST['feedback_email'] ?? '');
        $sujet = trim($_POST['feedback_sujet'] ?? '');
        $message = trim($_POST['feedback_message'] ?? '');

        if ($nom === '' || $email === '' || $sujet === '' || $message === '') {
            $errorMessage = 'Veuillez remplir tous les champs obligatoires.';
        } else {
            try {
                $stmt = $pdo->prepare('INSERT INTO feedbacks (nom, email, sujet, message, created_at) VALUES (?, ?, ?, ?, NOW())');
                $stmt->execute([$nom, $email, $sujet, $message]);
                $successMessage = 'Merci pour votre feedback ! Nous l\'avons bien reçu.';
            } catch (PDOException $e) {
                $errorMessage = "Une erreur est survenue lors de l'enregistrement. Veuillez réessayer.";
                logError('Erreur PDO: ' . $e->getMessage());
                logError('Trace: ' . $e->getTraceAsString());
            }
        }
    } else {
        // Formulaire de contact standard
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $sujet = trim($_POST['sujet'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($nom === '' || $email === '' || $telephone === '' || $message === '') {
            $errorMessage = 'Veuillez remplir tous les champs obligatoires.';
        } else {
            try {
                // Vérifier si la table a les colonnes attendues
                $stmt = $pdo->prepare('SHOW COLUMNS FROM contacts');
                $stmt->execute();
                $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                // Préparer les champs et les valeurs en fonction des colonnes disponibles
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
                
                // Vérifier si le téléphone est requis
                if (in_array('telephone', $columns)) {
                    $fields[] = 'telephone';
                    $values[] = $telephone;
                    $placeholders[] = '?';
                }
                
                // Vérifier si le sujet est requis
                if (in_array('sujet', $columns)) {
                    $fields[] = 'sujet';
                    $values[] = 'Contact depuis le formulaire'; // Valeur statique
                    $placeholders[] = '?';
                }
                
                $fields[] = 'message';
                $values[] = $message;
                $placeholders[] = '?';
                
                // Ajouter le type de contact
                if (in_array('type', $columns)) {
                    $fields[] = 'type';
                    $values[] = 'contact';
                    $placeholders[] = '?';
                }
                
                // Construire et exécuter la requête
                $sql = 'INSERT INTO contacts (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $placeholders) . ')';
                $stmt = $pdo->prepare($sql);
                $stmt->execute($values);
                $successMessage = 'Votre message a été envoyé avec succès ! Nous vous contacterons bientôt.';
                
                // Désactivé temporairement : Envoi d'email de notification
                // Pour activer l'envoi d'emails, configurez un serveur SMTP valide
                // et décommentez le code ci-dessous
                /*
                try {
                    $to = 'contact@votresite.com';
                    $subject = 'Nouveau message de contact';
                    $message = "Nouveau message de contact :\n\n";
                    $message .= "Nom : " . htmlspecialchars($nom) . "\n";
                    $message .= "Email : " . htmlspecialchars($email) . "\n";
                    $message .= "Téléphone : " . htmlspecialchars($telephone) . "\n";
                    $message .= "Sujet : " . htmlspecialchars($sujet) . "\n";
                    $message .= "Message :\n" . htmlspecialchars($message) . "\n";
                    
                    $headers = "From: $email\r\n";
                    $headers .= "Reply-To: $email\r\n";
                    $headers .= "X-Mailer: PHP/" . phpversion();
                    
                    // Configuration pour utiliser un serveur SMTP externe
                    ini_set('SMTP', 'votre_serveur_smtp');
                    ini_set('smtp_port', 587);
                    ini_set('sendmail_from', 'contact@votresite.com');
                    
                    mail($to, $subject, $message, $headers);
                } catch (Exception $e) {
                    logError('Erreur lors de l\'envoi de l\'email: ' . $e->getMessage());
                }
                */
            } catch (PDOException $e) {
                $errorMessage = "Une erreur est survenue lors de l'enregistrement. Veuillez réessayer.";
                logError('Erreur PDO: ' . $e->getMessage());
                logError('Trace: ' . $e->getTraceAsString());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - EDEN</title>
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
    <?php 
        $base = '';
        include __DIR__ . '/../includes/header.php'; 
    ?>

    <!-- CONTACT INFO SECTION -->
    <section id="info" class="contact-map-section">
        <div class="container">
            <h2 style="font-size: 2em; font-weight: 700; margin-bottom: 30px;">Informations de Contact</h2>
            <div class="contact-grid">
                <div class="contact-info">
                    <h3 style="font-weight: 700; margin-bottom: 20px;">Coordonnées</h3>
                    <div style="margin-bottom: 18px;">
                        <div style="font-weight: 700; color: var(--color-primary); margin-bottom: 5px;">📞 Téléphone</div>
                        <div style="font-size: 1em; color: #333;">+243 000 000 000</div>
                    </div>
                    <div style="margin-bottom: 18px;">
                        <div style="font-weight: 700; color: var(--color-primary); margin-bottom: 5px;">💬 WhatsApp</div>
                        <div style="font-size: 1em; color: #333;">+243 000 000 000</div>
                    </div>
                    <div style="margin-bottom: 18px;">
                        <div style="font-weight: 700; color: var(--color-primary); margin-bottom: 5px;">📧 Email</div>
                        <div style="font-size: 1em; color: #333;"><a href="mailto:contact@eden-ministere.com" style="color: var(--color-primary); text-decoration: none;">contact@eden-ministere.com</a></div>
                    </div>
                    <div class="separator">· · ·</div>
                    <div style="margin-top: 18px;">
                        <div style="font-weight: 700; color: var(--color-primary); margin-bottom: 5px;">📍 Adresse</div>
                        <div style="font-size: 1em; color: #333;">Kinshasa, RDC</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECONDARY BLOCKS - Why Contact Us -->
    <section class="secondary-blocks">
        <div class="container">
            <h2 style="font-size: 1.6em; font-weight: 700; margin-bottom: 20px; color: var(--dark-text);">Pourquoi nous contacter ?</h2>
            <div class="block-grid">
                <a href="faq.php" style="text-decoration: none; color: inherit;">
                    <div class="block" style="background-color: var(--color-primary); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)'">
                        <h3 style="color: white;">❓ Question</h3>
                        <p style="font-size: 0.95em; color: white;">Sur nos activités</p>
                    </div>
                </a>
                <a href="inscription.php" style="text-decoration: none; color: inherit;">
                    <div class="block" style="background-color: var(--color-primary-dark); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)'">
                        <h3 style="color: white;">📋 Inscription</h3>
                        <p style="font-size: 0.95em; color: white;">Pour participer</p>
                    </div>
                </a>
                <a href="partenaire.php" style="text-decoration: none; color: inherit;">
                    <div class="block" style="background-color: var(--color-primary-light); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)'">
                        <h3 style="color: white;">🤝 Partenariat</h3>
                        <p style="font-size: 0.95em; color: white;">Collaboration</p>
                    </div>
                </a>
                <div style="cursor: pointer;" onclick="openFeedbackModal()">
                    <div class="block" style="background-color: var(--color-secondary); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.12)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)'">
                        <h3 style="color: white;">💬 Feedback</h3>
                        <p style="font-size: 0.95em; color: white;">Vos suggestions</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTACT FORM SECTION -->
    <section id="contact-form" class="contact-map-section" style="background: var(--bg);">
        <div class="container">
            <h2 style="font-size: 2em; font-weight: 700; margin-bottom: 30px;">Envoyer un Message</h2>
            <div style="max-width: 700px; margin: 0 auto; text-align: center;">
                <p style="color: #666; font-size: 1.05em; margin-bottom: 30px;">Avez-vous une question ou un message pour notre équipe ? Cliquez sur le bouton ci-dessous pour nous contacter.</p>
                <button type="button" onclick="openContactModal()" style="background: #2c5aa0; color: white; padding: 12px 30px; border: none; border-radius: 6px; font-weight: 600; font-size: 1em; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#1e3f70'" onmouseout="this.style.background='#2c5aa0'">
                    ✉️ Envoyer un Message
                </button>
            </div>
        </div>
    </section>

    <!-- Modal Formulaire de Feedback -->
    <div id="feedbackModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; overflow-y:auto;">
        <div style="background:white; margin:20px auto; max-width:700px; border-radius:8px; box-shadow:0 4px 20px rgba(0,0,0,0.15); max-height:90vh; overflow-y:auto;">
            <div style="padding:20px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e0e0e0;">
                <h3 style="margin:0; color:#2c6e49;">💬 Feedback & Suggestions</h3>
                <button type="button" onclick="closeFeedbackModal()" style="background:none; border:none; font-size:24px; cursor:pointer; color:#666;">&times;</button>
            </div>
            <div style="padding:20px;">
                <form method="post" action="" style="display: grid; gap: 15px;">
                    <input type="hidden" name="feedback_form" value="1">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Nom Complet *</label>
                            <input type="text" name="feedback_nom" required placeholder="Votre nom" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:1em; box-sizing: border-box;">
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Email *</label>
                            <input type="email" name="feedback_email" required placeholder="votre@email.com" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:1em; box-sizing: border-box;">
                        </div>
                    </div>
                    
                    <div>
                        <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Sujet *</label>
                        <input type="text" name="feedback_sujet" required placeholder="Sujet de votre suggestion" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:1em; box-sizing: border-box;">
                    </div>
                    
                    <div>
                        <label style="display:block; margin-bottom:5px; font-weight:600; color:#333;">Suggestion ou Commentaire *</label>
                        <textarea name="feedback_message" rows="5" required placeholder="Partagez vos suggestions ou commentaires..." style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:1em; resize:vertical; box-sizing: border-box;"></textarea>
                    </div>
                    
                    <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
                        <button type="button" onclick="closeFeedbackModal()" style="background:#e0e0e0; color:#333; padding:10px 20px; border:none; border-radius:6px; cursor:pointer; font-weight:600;">Annuler</button>
                        <button type="submit" style="background:#2c6e49; color:white; padding:10px 20px; border:none; border-radius:6px; cursor:pointer; font-weight:600; transition:background 0.2s;" onmouseover="this.style.background='#1e4d2b'" onmouseout="this.style.background='#2c6e49'">Envoyer le Feedback</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Formulaire de Contact -->
    <div id="contactModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; overflow-y:auto;">
        <div style="background:white; margin:20px auto; max-width:700px; border-radius:8px; box-shadow:0 4px 20px rgba(0,0,0,0.15); max-height:90vh; overflow-y:auto;">
            <div style="padding:20px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e0e0e0;">
                <h3 style="margin:0; color:#2c5aa0;">Envoyer un Message</h3>
                <button type="button" onclick="closeContactModal()" style="background:none; border:none; font-size:24px; cursor:pointer; color:#666;">&times;</button>
            </div>
            <div style="padding:20px;">
                <?php if (!empty($successMessage)): ?>
                    <div style="background: #efe; border: 1px solid #cfc; padding: 12px; border-radius: 6px; margin-bottom: 20px; color: #3a3; border-left: 4px solid #3a3;">
                        ✅ <?= htmlspecialchars($successMessage) ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($errorMessage)): ?>
                    <div style="background: #fee; border: 1px solid #fcc; padding: 12px; border-radius: 6px; margin-bottom: 20px; color: #c33; border-left: 4px solid #c33;">
                        ⚠️ <?= htmlspecialchars($errorMessage) ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="" style="display: grid; gap: 15px;">
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
        
        function openFeedbackModal() {
            document.getElementById('feedbackModal').style.display = 'flex';
            document.getElementById('feedbackModal').style.alignItems = 'center';
            document.body.style.overflow = 'hidden';
        }
        
        function closeFeedbackModal() {
            document.getElementById('feedbackModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        window.addEventListener('click', function(event) {
            const contactModal = document.getElementById('contactModal');
            const feedbackModal = document.getElementById('feedbackModal');
            if (event.target === contactModal) {
                closeContactModal();
            }
            if (event.target === feedbackModal) {
                closeFeedbackModal();
            }
        });
    </script>

    <script src="../assets/js/contact.js"></script>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
