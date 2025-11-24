<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programme de l'année - EDEN</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <!-- HEADER -->
    <header>
        <div class="logo">EDEN</div>
        <nav>
            <ul>
                <li><a href="../index.php">Accueil</a></li>
                <li><a href="apropos.php">À propos</a></li>
                <li><a class="active" href="programmes.php">Programmes</a></li>
                <li><a href="retraite.php">Retraites</a></li>
                <li><a href="galerie.php">Galerie</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </nav>
    </header>

    <!-- BANNER -->
    <section class="banner">
        <h1>Programme Annuel des Activités</h1>
        <p>Découvrez les grands rendez-vous spirituels de l'année.</p>
    </section>

    <!-- SECTION PROGRAMMES -->
    <section class="programme-annee">
        <h2>Calendrier 2025</h2>
        <p>Voici les événements majeurs organisés par le Ministère EDEN cette année.</p>

        <div class="programme-grid">

            <!-- JANVIER -->
            <div class="programme-card">
                <h3>Janvier — Jeûne & Prière</h3>
                <p>📅 2 - 10 Janvier<br>⛪ Thème : Consécration & Restauration</p>
            </div>

            <!-- FEVRIER -->
            <div class="programme-card">
                <h3>Février — Séminaire des Jeunes</h3>
                <p>📅 17 - 18 Février<br>🎤 Intervenants : Pasteur Chris & Soeur Debora</p>
            </div>

            <!-- AVRIL -->
            <div class="programme-card">
                <h3>Avril — Grande Retraite Spirituelle</h3>
                <p>📅 15 - 17 Avril<br>🔥 Thème : Rencontre & Transformation</p>
            </div>

            <!-- JUIN -->
            <div class="programme-card">
                <h3>Juin — Conférence des Familles</h3>
                <p>📅 5 - 6 Juin<br>❤ Ateliers pour couples et parents</p>
            </div>

            <!-- AOUT -->
            <div class="programme-card">
                <h3>Août — Camp de Jeunesse</h3>
                <p>📅 20 - 25 Août<br>🏕️ Camp spirituel et activités en plein air</p>
            </div>

            <!-- OCTOBRE -->
            <div class="programme-card">
                <h3>Octobre — Nuit de Louange</h3>
                <p>📅 12 Octobre<br>🎵 Louange, prière, témoignages</p>
            </div>

            <!-- DECEMBRE -->
            <div class="programme-card">
                <h3>Décembre — Veillée de Fin d’Année</h3>
                <p>📅 31 Décembre<br>✨ Célébration & actions de grâce</p>
            </div>

        </div>

        <a href="#" class="btn primary" style="margin-top:30px;">
            Télécharger le programme en PDF
        </a>
    </section>

    <!-- FOOTER -->
    <footer>
        <p>© 2025 EDEN — Tous droits réservés.</p>
        <p>Verset du jour : <span id="verset"></span></p>
    </footer>

<script src="../assets/js/script.js"></script>
</body>
</html>
