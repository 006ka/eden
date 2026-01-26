<?php
$pageTitle = "FAQ - Questions Fréquemment Posées - EDEN";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        .faq-container { max-width: 900px; margin: 0 auto; }
        .faq-item { background: white; margin-bottom: 20px; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .faq-question { 
            padding: 20px; 
            cursor: pointer; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            background: #f8f9fa;
            font-weight: 600;
            color: #2c5aa0;
            transition: background 0.2s;
        }
        .faq-question:hover { background: #f0f2f5; }
        .faq-question.active { background: #2c5aa0; color: white; }
        .faq-answer { 
            padding: 20px; 
            background: white;
            color: #555;
            line-height: 1.7;
            display: none;
            border-top: 1px solid #e0e0e0;
        }
        .faq-answer.active { display: block; }
        .faq-icon { font-size: 1.5em; transition: transform 0.2s; }
        .faq-icon.active { transform: rotate(180deg); }
    </style>
</head>
<body>

<?php include __DIR__ . '/../includes/header.php'; ?>

<section class="hero-section" style="min-height:240px;">
    <div class="overlay"></div>
    <div class="container hero-content">
        <p class="new-here-link">Questions Fréquemment Posées</p>
        <h1>FAQ - EDEN</h1>
        <p class="subtitle">Trouvez les réponses à vos questions sur nos activités et retraites.</p>
    </div>
</section>

<section class="contact-map-section">
    <div class="container">
        <div class="faq-container">
            <!-- Question 1 -->
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Qui sommes-nous et qu'est-ce qu'EDEN ?</span>
                    <span class="faq-icon">▼</span>
                </div>
                <div class="faq-answer">
                    <p>EDEN est une organisation spirituelle dédiée à l'enseignement biblique et au développement personnel à travers des retraites de qualité. Nous nous engageons à offrir des expériences transformatrices basées sur la foi, l'introspection et la communauté.</p>
                </div>
            </div>

            <!-- Question 2 -->
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Comment puis-je m'inscrire à une retraite ?</span>
                    <span class="faq-icon">▼</span>
                </div>
                <div class="faq-answer">
                    <p>Vous pouvez vous inscrire directement depuis notre site dans la section "Programmes" en sélectionnant la retraite de votre choix. Un formulaire d'inscription vous permettra de fournir vos informations personnelles et de confirmer votre participation.</p>
                </div>
            </div>

            <!-- Question 3 -->
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Quels sont les tarifs des retraites ?</span>
                    <span class="faq-icon">▼</span>
                </div>
                <div class="faq-answer">
                    <p>Les tarifs varient selon le type et la durée de chaque retraite. Vous trouverez le prix exact de chaque retraite dans la section "Programmes". Des tarifs réduits peuvent être disponibles pour les groupes ou les participants en difficulté financière. N'hésitez pas à nous contacter pour discuter des options.</p>
                </div>
            </div>

            <!-- Question 4 -->
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Que dois-je apporter pour une retraite ?</span>
                    <span class="faq-icon">▼</span>
                </div>
                <div class="faq-answer">
                    <p>Nous recommandons d'apporter :</p>
                    <ul style="margin: 10px 0; padding-left: 20px;">
                        <li>Votre Bible et un carnet de notes</li>
                        <li>Vêtements confortables et appropriés au climat</li>
                        <li>Articles de toilette personnels</li>
                        <li>Médicaments prescrits</li>
                        <li>Couverture ou draps personnels (si demandé)</li>
                    </ul>
                </div>
            </div>

            <!-- Question 5 -->
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Y a-t-il des retraites pour les enfants ou les jeunes ?</span>
                    <span class="faq-icon">▼</span>
                </div>
                <div class="faq-answer">
                    <p>Oui, nous proposons des programmes adaptés à différents âges. Pour connaître les retraites spécifiques pour enfants, adolescents ou jeunes adultes, veuillez consulter notre section "Programmes" ou nous contacter directement.</p>
                </div>
            </div>

            <!-- Question 6 -->
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Puis-je annuler mon inscription ?</span>
                    <span class="faq-icon">▼</span>
                </div>
                <div class="faq-answer">
                    <p>Les annulations doivent être effectuées avant la date limite indiquée dans les conditions de votre inscription. Selon la politique de remboursement, vous pouvez recevoir un remboursement total ou partiel. Veuillez consulter nos conditions générales ou nous contacter pour plus de détails.</p>
                </div>
            </div>

            <!-- Question 7 -->
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Offrez-vous un hébergement ?</span>
                    <span class="faq-icon">▼</span>
                </div>
                <div class="faq-answer">
                    <p>La plupart de nos retraites incluent un hébergement. Les détails spécifiques (type d'hébergement, commodités, etc.) sont disponibles pour chaque retraite dans notre section "Programmes". Si vous avez des besoins spéciaux d'hébergement, veuillez nous contacter.</p>
                </div>
            </div>

            <!-- Question 8 -->
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Les repas sont-ils fournis ?</span>
                    <span class="faq-icon">▼</span>
                </div>
                <div class="faq-answer">
                    <p>Oui, les repas sont généralement fournis lors de nos retraites. Nous veillons à proposer une alimentation équilibrée et variée. Si vous avez des restrictions alimentaires ou des allergies, veuillez les signaler lors de votre inscription.</p>
                </div>
            </div>

            <!-- Question 9 -->
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Comment puis-je télécharger les enseignements ?</span>
                    <span class="faq-icon">▼</span>
                </div>
                <div class="faq-answer">
                    <p>Tous nos enseignements sont disponibles dans la section "Enseignements" de notre site. Vous pouvez consulter chaque enseignement, lire les PDFs directement ou les télécharger. Les ressources audio sont également disponibles pour certains enseignements.</p>
                </div>
            </div>

            <!-- Question 10 -->
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Comment puis-je partager mon témoignage ?</span>
                    <span class="faq-icon">▼</span>
                </div>
                <div class="faq-answer">
                    <p>Vous pouvez partager votre témoignage dans la section "Témoignages" en cliquant sur le bouton "Publier mon témoignage". Votre témoignage sera examiné par notre équipe et publié après modération. Nous apprécions vos histoires d'inspiration !</p>
                </div>
            </div>

            <!-- Question 11 -->
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Puis-je vous contacter pour des questions spécifiques ?</span>
                    <span class="faq-icon">▼</span>
                </div>
                <div class="faq-answer">
                    <p>Absolument ! Vous pouvez nous contacter via notre formulaire de contact, par email ou par téléphone. Notre équipe répondra à vos questions dans les meilleurs délais. Consultez notre page "Contact" pour tous les moyens de nous joindre.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    function toggleFaq(element) {
        const question = element;
        const answer = question.nextElementSibling;
        const icon = question.querySelector('.faq-icon');
        
        // Fermer tous les autres éléments
        document.querySelectorAll('.faq-question').forEach(q => {
            if (q !== question) {
                q.classList.remove('active');
                q.nextElementSibling.classList.remove('active');
                q.querySelector('.faq-icon').classList.remove('active');
            }
        });
        
        // Basculer l'élément actuel
        question.classList.toggle('active');
        answer.classList.toggle('active');
        icon.classList.toggle('active');
    }
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
