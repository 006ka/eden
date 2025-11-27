<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>À propos - EDEN</title>
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
    <?php 
        $base = '';
        include __DIR__ . '/../includes/header.php'; 
    ?>

    <!-- SECONDARY BLOCKS - Valeurs Fondamentales -->
    <section class="secondary-blocks">
        <div class="container">
            <h2 style="font-size: 1.6em; font-weight: 700; margin-bottom: 20px; color: var(--dark-text);">Nos Valeurs Fondamentales</h2>
            <div class="block-grid">
                <div class="block" style="background-color: var(--color-primary);">
                    <h3>Profondeur</h3>
                    <p style="font-size: 0.95em;">
                        Nous croyons vivement et fermement qu’un parcours terrestre réussi est le
                        résultat d’une vie fondée sur la relation intime et personnelle avec Dieu en
                        Jésus-Christ par le Saint-Esprit, en dehors tout n’est qu’illusion.
                    </p>
                </div>
                <div class="block" style="background-color: var(--color-primary-dark);">
                    <h3>Onction</h3>
                    <p style="font-size: 0.95em;">
                        Nous croyons en l’appel spécifique de Dieu pour chaque être humain sur la terre,
                        ce qui lui confère la faveur de collaborer avec le Saint-Esprit et d’avoir accès à sa
                        puissance, les deux mis ensemble qui font l’onction du Saint-Esprit.
                    </p>
                </div>
                <div class="block" style="background-color: var(--color-primary-light);">
                    <h3>Excellence</h3>
                    <p style="font-size: 0.95em;">
                        Nous croyons que notre Dieu n’est pas médiocre, il est excellent et désire vivement
                        que ses enfants soient excellents et qu’ils l’apportent partout dans leur entourage.
                    </p>
                </div>
                <div class="block" style="background-color: var(--color-secondary);">
                    <h3>Conviction</h3>
                    <p style="font-size: 0.95em;">
                        Nous croyons que le but de tout être humain sur terre est de recevoir la foi,
                        vivre par elle et la garder pour le retour de Christ ; raison pour laquelle enseigner
                        la Parole est notre activité principale.
                    </p>
                </div>
                <div class="block" style="background-color: #4d8f6a;">
                    <h3>Authenticité</h3>
                    <p style="font-size: 0.95em;">
                        Nous croyons que chaque être humain est pleinement aimé par Dieu et que
                        nous sommes uniques chacun en son sens, raison pour laquelle nous célébrons
                        les différences et haïssons les discriminations, les comparaisons et les
                        compétitions déloyales.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- MISSION SECTION -->
    <section id="mission" class="contact-map-section">
        <div class="container">
            <h2 style="font-size: 2em; font-weight: 700; margin-bottom: 30px;">Notre Mission</h2>
            <div class="contact-grid">
                <div class="contact-info">
                    <h3 style="font-weight: 700; margin-bottom: 15px;">Qui sommes-nous ?</h3>
                    <p style="line-height: 1.8; margin-bottom: 15px;">
                        Nous sommes <strong> EdEn </strong> : Des personnes qui n'ont aucun atout, sauf la preuve que l'amour de Dieu est réel. 
Notre seule compétence est l'adoration!! Nous Avons été choisi et consacrer par onction selon la souveraineté de Dieu, pour vous Dire avec conviction
 que Dieu vous aimes énormément ; Pour vous montrer comment recevoir cet amour au
 travers toutes sortes d'atmosphère de croissance, et pour vous accompagner dans l'utilisation efficace,
 constructive et Adorative de cet amour
                    </p>
                    <p style="line-height: 1.8;">
                        Fondé avec la vision de ramener les cœurs vers Dieu, EDEN organise régulièrement des retraites spirituelles, 
                        des temps de jeûne, des enseignements, et des moments d'adoration dans la présence de Dieu.
                    </p>
                </div>
                <div>
                    <h3 style="font-weight: 700; margin-bottom: 15px;">Notre Vision</h3>
                    <div style="background: var(--light-bg); padding: 30px; border-radius: var(--radius); border-left: 4px solid var(--color-primary);">
                        <p style="line-height: 1.8; color: #333; font-style: italic;">
                            " Je vois  un centre d’Incubation  en leadership, ministère, création d’entreprise, développement de
 carrière des personnes y viennent étudient la parole, reçoivent des compétences utile, sont armé de
 courage pour passer à un autre niveau d’excellence et de perfectionnement
 je vois une école de musique, un académie sportif, une école de médias, une école de gouvernance,
 une école de MBA ... (Des écoles qui honore les gens depuis le bâtiment jusqu’au programme
 Je vois un très grand auditorium, des gens y viennent pour être maintenu dans la croissance par la
 parole de Dieu , je vois des salles pour des enfants, une salle pour des adolescents et les jeunes
 adultes, avec des endroit pour le networking, je vois une grande bibliothèque, je vois des terrains au
 meme endroit , je vois d’un coté un bâtiment pour la cirep et de l’autre un bâtiment pour l’EMBC
 c’est un camps très beau  et reproduisible les gens y trouve un refuge apaisant et glorifie Dieu qui 
prends soin des ses enfants dans le domaine de la croissance
 je vois une université de Medcine, Droit, ingenieurie...
"
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- TEAM SECTION -->
    <section id="team" class="contact-map-section" style="background: var(--bg);">
        <div class="container">
            <h2 style="font-size: 2em; font-weight: 700; margin-bottom: 30px;">L'Équipe EDEN</h2>
            <div class="block-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div style="padding: 30px; background: white; border-radius: var(--radius); box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-align: center;">
                    <div style="width: 100px; height: 100px; background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light)); border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; font-size: 40px;">🙏</div>
                    <h3 style="font-weight: 700; margin-bottom: 8px;">Responsable</h3>
                    <p style="color: var(--muted); font-size: 0.95em; line-height: 1.6;">
                        Direction spirituelle et coordination générale du ministère EDEN.
                    </p>
                </div>

                <div style="padding: 30px; background: white; border-radius: var(--radius); box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-align: center;">
                    <div style="width: 100px; height: 100px; background: linear-gradient(135deg, var(--color-primary-light), var(--color-secondary)); border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; font-size: 40px;">🤲</div>
                    <h3 style="font-weight: 700; margin-bottom: 8px;">Équipe d'Intercession</h3>
                    <p style="color: var(--muted); font-size: 0.95em; line-height: 1.6;">
                        Intercesseurs engagés dans la prière quotidienne et l'accompagnement spirituel.
                    </p>
                </div>

                <div style="padding: 30px; background: white; border-radius: var(--radius); box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-align: center;">
                    <div style="width: 100px; height: 100px; background: linear-gradient(135deg, var(--color-primary-light), var(--color-secondary)); border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; font-size: 40px;">🎵</div>
                    <h3 style="font-weight: 700; margin-bottom: 8px;">Équipe de Louange</h3>
                    <p style="color: var(--muted); font-size: 0.95em; line-height: 1.6;">
                        Groupe de chant qui conduit les adorateurs dans la présence de Dieu.
                    </p>
                </div>

                <div style="padding: 30px; background: white; border-radius: var(--radius); box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-align: center;">
                    <div style="width: 100px; height: 100px; background: linear-gradient(135deg, var(--color-secondary), var(--color-primary-light)); border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; font-size: 40px;">🙌</div>
                    <h3 style="font-weight: 700; margin-bottom: 8px;">Serviteurs & Logistique</h3>
                    <p style="color: var(--muted); font-size: 0.95em; line-height: 1.6;">
                        Organisation, accueil et soutien pendant les événements.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA SECTION -->
    <section class="contact-map-section">
        <div class="container" style="text-align: center;">
            <h2 style="font-size: 1.8em; font-weight: 700; margin-bottom: 15px;">Rejoignez EDEN</h2>
            <p style="font-size: 1.1em; color: var(--muted); margin-bottom: 25px; max-width: 600px; margin-left: auto; margin-right: auto;">
                Vous cherchez une communauté de prière et de croissance spirituelle ? 
                Rejoignez-nous lors de nos prochaines retraites ou activités.
            </p>
            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <a href="../public/retraite.php" class="btn btn-red" style="padding: 12px 30px; font-weight: 600;">Voir les retraites</a>
                <a href="../public/contact.php" class="btn btn-border" style="padding: 12px 30px; font-weight: 600; background: rgba(29, 58, 95, 0.1); color: var(--color-primary); border: 2px solid var(--color-primary);">Nous contacter</a>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
