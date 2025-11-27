# EDEN – Site de Retraites et Ministère

## 1. Présentation

EDEN est un site web PHP/MySQL qui présente le ministère EDEN, ses retraites spirituelles et ses activités, et qui permet :

- **Côté public**
  - De consulter les retraites à venir et passées.
  - De s’inscrire à une retraite via un formulaire dédié.
  - De voir les programmes/activités annuelles.
  - De parcourir une **galerie de photos** avec ouverture en grand (modale/lightbox).
  - De lire la page **À propos** (vision, mission, valeurs, équipe).
  - De contacter le ministère via un formulaire de contact.

- **Côté administration**
  - De gérer les **retraites** (CRUD, upload d’images, fiche pratique, pagination).
  - De gérer les **programmes** annuels.
  - De gérer la **galerie** (upload de photos, association à une retraite, miniatures).
  - De consulter et exporter les **inscriptions** (CSV + impression/"PDF" navigateur).
  - De consulter les **contacts** reçus.
  - De gérer les **témoignages** et les horaires.

Le projet est pensé pour tourner sur un environnement type **WAMP/XAMPP** (Windows, Apache, MySQL, PHP).

---

## 2. Prérequis

- **PHP** ≥ 7.4 (fonctionne aussi avec 8.x)
- **MySQL / MariaDB**
- **Serveur HTTP** (Apache via WAMP/XAMPP ou autre)
- Extension PHP `pdo_mysql` activée

Sous Windows, le projet est prévu pour être placé dans :

```text
c:\wamp64\www\eden
```

mais tout autre dossier de racine web convient si la configuration est adaptée.

---

## 3. Installation

1. **Cloner ou copier** le projet dans le dossier web de votre serveur :

   ```text
   c:\wamp64\www\eden
   ```

2. **Créer la base de données MySQL** (par exemple `eden_db`) via phpMyAdmin ou la ligne de commande.

3. **Importer le schéma** (fichier SQL) si vous en avez un export (`eden_db.sql` par exemple). À défaut, créez les tables principales :
   - `retreats`
   - `programmes`
   - `gallery`
   - `inscriptions`
   - `contacts`
   - `testimonials`
   - etc.

4. **Configurer la connexion à la base** dans :

   ```php
   /config/db.php
   ```

   Exemple typique (à adapter) :

   ```php
   $dsn = 'mysql:host=localhost;dbname=eden_db;charset=utf8mb4';
   $user = 'root';
   $password = '';
   ```

5. Vérifier les droits d’écriture sur le dossier `uploads` (créé par l’admin pour les images, fiches pratiques, miniatures, etc.).

---

## 4. Structure du projet

Répertoire principal :

```text
eden/
├─ admin/
│  ├─ admin.php           # Entrée principale de l’espace admin
│  ├─ index.php           # Redirection/accueil admin
│  └─ partials/           # Sections de l’admin (retreats, programmes, galerie, inscriptions, etc.)
├─ public/
│  ├─ retraite.php        # Page retraites (liste + détails)
│  ├─ programmes.php      # Page programmes / activités
│  ├─ inscription.php     # Formulaires d’inscription aux retraites
│  ├─ galerie.php         # Galerie publique avec lightbox/modale
│  ├─ contact.php         # Page de contact (+ formulaire)
│  ├─ apropos.php         # Page À propos (vision, mission, valeurs, équipe)
│  └─ ... autres pages publiques
├─ assets/
│  ├─ css/
│  │  └─ main.css         # Feuille de style principale (layout, boutons, grilles, etc.)
│  └─ js/
│     └─ lightbox.js      # Script de lightbox (ancienne version, peu utilisée)
├─ uploads/               # Fichiers uploadés (images, PDF, miniatures)
├─ includes/
│  ├─ header.php          # En-tête du site (menu, logo)
│  └─ footer.php          # Pied de page
├─ config/
│  └─ db.php              # Connexion PDO à la base MySQL
├─ index.php              # Page d’accueil publique
└─ README.md              # Ce fichier
```

---

## 5. Pages publiques principales

### 5.1. Accueil (`index.php`)

- **Hero** avec slider d’images provenant de la galerie (`homePhotos`).
- Bandeau **events-bar** avec informations rapides (prochaine retraite, rencontres hebdomadaires, etc.).
- Bloc de contact + **carte OpenStreetMap** centrée sur Ruashi Kundelungu.
- Bloc **Objectifs** (Adoration, Maturité, Impact, Service, Fraternité).
- Section **Enseignements & Méditations** avec 3 vignettes illustrées.
- Section **Galerie** (aperçu de quelques photos, ouverture modale, lien vers `/public/galerie.php`).

### 5.2. Retraites (`public/retraite.php`)

- Récupère toutes les retraites (`retreats`) et sépare en :
  - **Retraites à venir**
  - **Retraites passées**
- Détails d’une retraite mise en avant (en fonction d’un `id` ou de la prochaine à venir).
- Affiche : titre, thème, dates, lieu, orateurs, prix, programme image, etc.
- Lien vers **inscription** et vers **horaire**.

### 5.3. Programmes (`public/programmes.php`)

- Liste les programmes annuels (table `programmes`).
- Présente les différentes activités / enseignements dans l’année.

### 5.4. Galerie (`public/galerie.php`)

- Grille de photos alimentée par la table `gallery`.
- Au clic sur une photo : ouverture d’une **modale** type "homeGalleryModal" avec :
  - Image en grand
  - Titre
  - Lien de téléchargement
  - Lien vers la retraite liée (si `retreat_id`) 
- Bouton pour accéder à la retraite ou à la galerie complète.

### 5.5. À propos (`public/apropos.php`)

- Section **Nos valeurs fondamentales** (Profondeur, Onction, Excellence, Conviction, Authenticité).
- Bloc **Notre mission / vision** avec texte détaillé.
- Bloc **Équipe EDEN** (Responsable, Intercession, Louange, Logistique, …).

### 5.6. Contact (`public/contact.php`)

- Coordonnées (téléphone, WhatsApp, mail, adresse).
- Carte **OpenStreetMap** (mêmes coordonnées que l’accueil).
- Rappel des raisons de contact (Question, Inscription, Partenariat, Feedback).
- Formulaire de contact enregistrant dans la table `contacts`.

### 5.7. Inscription (`public/inscription.php`)

- Formulaire d’inscription à une retraite.
- Enregistre les données dans `inscriptions` (nom, email, âge, présence, besoins particuliers, etc.).

---

## 6. Espace administration

Entrée : `admin/admin.php` (après authentification simple si prévue).

### 6.1. Retraites (`admin/partials/retreats.php`)

- Liste paginée des retraites (titre, thème, dates, lieu, prix, image, fiche).
- Ajout / édition / suppression.
- Upload d’image de programme (redimensionnée) et de fiche pratique (PDF ou image).

### 6.2. Programmes (`admin/partials/programmes.php`)

- Gestion des programmes annuels.

### 6.3. Galerie (`admin/partials/galerie.php`)

- Ajout de photos : par URL ou upload.
- Option d’associer une photo à une retraite (`retreat_id`).
- Génération de miniatures `thumb_*.jpg` pour l’affichage rapide.

### 6.4. Inscriptions (`admin/partials/inscriptions_gestion.php`)

- Filtre par type d’activité (retraite / programme) et par activité.
- Liste des inscrits avec détails (coordonnées, besoins, dates, etc.).
- **Export CSV** avec BOM UTF‑8 (compatible Excel).
- Bouton **Imprimer / PDF** qui utilise l’impression du navigateur pour générer un PDF propre.

### 6.5. Contacts (`admin/partials/contacts.php`)

- Liste paginée des messages envoyés via le formulaire de contact.

### 6.6. Témoignages, Horaires, Compte

- Gestion des témoignages affichés publiquement.
- Gestion des horaires/agenda si activé.
- Gestion de base du compte admin.

---

## 7. Pagination & performances

Plusieurs listes dans l’admin utilisent une **pagination par 15 éléments** :

- Retraites
- Programmes
- Inscriptions
- Contacts
- Témoignages

Les paramètres d’URL sont de type `?section=retreats&retreat_page=2`, etc.

---

## 8. Export & impression

- L’ancien export **PDF maison** pour les inscriptions a été remplacé par un export **CSV** fiable.
- Pour obtenir un PDF, l’admin peut utiliser le bouton **Imprimer / PDF** qui ouvre une fenêtre imprimable et s’appuie sur la fonction "Enregistrer en PDF" du navigateur.

---

## 9. Déploiement

1. Copier tous les fichiers du projet vers votre hébergement (FTP ou autre).
2. Créer la base de données MySQL et importer le dump.
3. Adapter `config/db.php` avec les identifiants de la base distante.
4. Vérifier le chemin des **uploads** (`../uploads/...`) et les droits d’écriture.
5. Configurer (si nécessaire) la racine du site pour pointer vers le dossier qui contient `index.php`.

---

## 10. Sécurité & bonnes pratiques

- Protéger l’accès à l’admin (`/admin/admin.php`) par une authentification (si ce n’est pas déjà fait).
- Vérifier régulièrement les droits du dossier `uploads/`.
- Garder PHP et MySQL à jour.
- Sauvegarder régulièrement la base de données (`mysqldump`) et le dossier `uploads`.

---

## 11. Contribuer / modifier

- Les styles sont principalement dans `assets/css/main.css`.
- Les sections sont souvent structurées en **partials** (dans `admin/partials` pour l’admin, et en fichiers dédiés dans `public/` pour le site public).
- Pour ajouter de nouvelles sections publiques, créer un nouveau fichier dans `public/` et inclure le header/footer via `includes/header.php` et `includes/footer.php`.

---

## 12. Support

Pour toute question ou bug :

- Utiliser la page **Contact** du site (`public/contact.php`).
- Ou contacter l’équipe en direct si vous avez les coordonnées internes.
