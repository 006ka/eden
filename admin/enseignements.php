<?php
// Activer l'affichage des erreurs PHP
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../config/db.php';

// Vérification de l'authentification
if (!isset($_SESSION['admin_username'])) {
    header('Location: index.php');
    exit();
}

// Récupérer la liste des enseignements
try {
    $search = $_GET['search'] ?? '';
    $categorie = $_GET['categorie'] ?? '';
    
    $query = "SELECT * FROM enseignements WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $query .= " AND (titre LIKE ? OR auteur LIKE ? OR description LIKE ?)";
        $searchTerm = "%$search%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
    }
    
    if (!empty($categorie)) {
        $query .= " AND categorie = ?";
        $params[] = $categorie;
    }
    
    $query .= " ORDER BY date_publication DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $enseignements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer les catégories uniques pour le filtre
    $categories = $pdo->query("SELECT DISTINCT categorie FROM enseignements WHERE categorie IS NOT NULL AND categorie != '' ORDER BY categorie")->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des enseignements : " . $e->getMessage();
}

// Traitement des actions (ajout, modification, suppression)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('Méthode POST détectée');
    error_log('Données POST: ' . print_r($_POST, true));
    error_log('Fichiers: ' . print_r($_FILES, true));
    if (isset($_POST['ajouter_enseignement'])) {
        try {
            $titre = trim($_POST['titre']);
            $auteur = trim($_POST['auteur']);
            $date_publication = $_POST['date_publication'];
            $contenu = trim($_POST['contenu']);
            $description = trim($_POST['description']);
            $categorie = trim($_POST['categorie']);
            $duree_minutes = (int)$_POST['duree_minutes'];
            $est_public = isset($_POST['est_public']) ? 1 : 0;
            
            // Gestion du téléchargement du fichier
            $fichier_url = null;
            if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../uploads/enseignements/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $extension = pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $extension;
                $destination = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['fichier']['tmp_name'], $destination)) {
                    $fichier_url = '/uploads/enseignements/' . $filename;
                }
            }
            
            // Gestion du téléchargement de l'image
            $image_url = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../uploads/enseignements/images/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('img_') . '.' . $extension;
                $destination = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    $image_url = '/uploads/enseignements/images/' . $filename;
                }
            }
            
            $stmt = $pdo->prepare("INSERT INTO enseignements (titre, auteur, date_publication, contenu, description, categorie, duree_minutes, fichier_url, image_url, est_public) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$titre, $auteur, $date_publication, $contenu, $description, $categorie, $duree_minutes, $fichier_url, $image_url, $est_public]);
            
            $success = "L'enseignement a été ajouté avec succès.";
            header('Location: enseignements.php?success=' . urlencode($success));
            exit();
            
        } catch (PDOException $e) {
            $error = "Erreur lors de l'ajout de l'enseignement : " . $e->getMessage();
        }
    }
    
    // Autres actions (modification, suppression) à implémenter...
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des enseignements - Administration EDEN</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .enseignements-container {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        .enseignements-list {
            flex: 1;
        }
        .enseignements-form {
            width: 400px;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .action-buttons a {
            margin-right: 5px;
            color: #333;
            text-decoration: none;
        }
        .action-buttons a:hover {
            color: #007bff;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group textarea {
            min-height: 100px;
        }
        .btn {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .filters {
            background: #f9f9f9;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .filters .form-group {
            display: inline-block;
            margin-right: 15px;
            margin-bottom: 0;
        }
        .filters label {
            display: inline-block;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/partials/menu.php'; ?>
    
    <div class="admin-container">
        <!-- La sidebar est maintenant incluse dans le menu -->
        
        <main class="main-content">
            <h1>Gestion des enseignements</h1>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php endif; ?>
            
            <div class="enseignements-container">
                <div class="enseignements-list">
                    <div class="filters">
                        <form method="get" action="" class="filter-form">
                            <div class="form-group">
                                <input type="text" name="search" placeholder="Rechercher..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <select name="categorie">
                                    <option value="">Toutes les catégories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo (isset($categorie) && $categorie === $cat) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn">Filtrer</button>
                            <a href="enseignements.php" class="btn">Réinitialiser</a>
                        </form>
                    </div>
                    
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Titre</th>
                                    <th>Auteur</th>
                                    <th>Date</th>
                                    <th>Catégorie</th>
                                    <th>Durée</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($enseignements as $enseignement): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($enseignement['titre']); ?></td>
                                        <td><?php echo htmlspecialchars($enseignement['auteur']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($enseignement['date_publication'])); ?></td>
                                        <td><?php echo htmlspecialchars($enseignement['categorie']); ?></td>
                                        <td><?php echo $enseignement['duree_minutes'] > 0 ? $enseignement['duree_minutes'] . ' min' : '-'; ?></td>
                                        <td class="action-buttons">
                                            <a href="#" title="Voir"><i class="fas fa-eye"></i></a>
                                            <a href="#" title="Modifier"><i class="fas fa-edit"></i></a>
                                            <a href="#" title="Supprimer" class="text-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet enseignement ?')"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($enseignements)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Aucun enseignement trouvé</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="enseignements-form">
                    <h2>Ajouter un enseignement</h2>
                    <div id="form-debug" style="color: red; margin-bottom: 10px;"></div>
                    <form id="ajout-enseignement-form" method="post" enctype="multipart/form-data" onsubmit="console.log('Form submitted'); document.getElementById('form-debug').textContent = 'Soumission du formulaire...';">
                        <div class="form-group">
                            <label for="titre">Titre *</label>
                            <input type="text" id="titre" name="titre" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="auteur">Auteur *</label>
                            <input type="text" id="auteur" name="auteur" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="date_publication">Date de publication *</label>
                            <input type="date" id="date_publication" name="date_publication" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="categorie">Catégorie</label>
                            <input type="text" id="categorie" name="categorie" list="categories" placeholder="Saisir ou sélectionner une catégorie">
                            <datalist id="categories">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        
                        <div class="form-group">
                            <label for="duree_minutes">Durée (en minutes)</label>
                            <input type="number" id="duree_minutes" name="duree_minutes" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description courte</label>
                            <textarea id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="contenu">Contenu détaillé *</label>
                            <textarea id="contenu" name="contenu" rows="5" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="fichier">Fichier (PDF, MP3, etc.)</label>
                            <input type="file" id="fichier" name="fichier">
                        </div>
                        
                        <div class="form-group">
                            <label for="image">Image de couverture</label>
                            <input type="file" id="image" name="image" accept="image/*">
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="est_public" value="1" checked> Rendre cet enseignement public
                            </label>
                        </div>
                        
                        <button type="submit" name="ajouter_enseignement" class="btn">Enregistrer l'enseignement</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Vérifier que le DOM est complètement chargé
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM complètement chargé');
            
            const form = document.getElementById('ajout-enseignement-form');
            if (!form) {
                console.error('Le formulaire avec l\'ID ajout-enseignement-form n\'a pas été trouvé');
                return;
            }
            
            console.log('Formulaire trouvé :', form);
            
            // Activer la validation HTML5 native
            form.setAttribute('novalidate', '');
            
            // Gestionnaire de soumission du formulaire
            form.addEventListener('submit', function(e) {
                console.log('Soumission du formulaire détectée');
                
                // Réinitialiser les messages d'erreur
                const errorMessages = form.querySelectorAll('.error-message');
                errorMessages.forEach(el => el.remove());
                
                // Vérifier la validation du formulaire
                if (!form.checkValidity()) {
                    console.log('Validation du formulaire échouée');
                    e.preventDefault();
                    
                    // Afficher les erreurs de validation
                    const invalidFields = form.querySelectorAll(':invalid');
                    invalidFields.forEach(field => {
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'error-message';
                        errorMessage.style.color = 'red';
                        errorMessage.textContent = field.validationMessage || 'Ce champ est requis';
                        field.parentNode.insertBefore(errorMessage, field.nextSibling);
                    });
                    
                    return false;
                }
                
                console.log('Le formulaire est valide, envoi en cours...');
                
                // Afficher un indicateur de chargement
                const submitButton = form.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = 'Envoi en cours...';
                }
                
                return true;
            });
            
            // Script pour la prévisualisation de l'image
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let preview = document.getElementById('image-preview');
                    if (!preview) {
                        preview = document.createElement('div');
                        preview.id = 'image-preview';
                        preview.style.marginTop = '10px';
                        e.target.parentNode.appendChild(preview);
                    }
                    preview.innerHTML = `<img src="${e.target.result}" style="max-width: 100%; max-height: 150px; margin-top: 10px;">`;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
