<?php
// Fichier de connexion à la base de données
// À adapter selon votre configuration WAMP

$host = 'localhost';
$dbname = 'eden_db'; // nom de la base à créer
$user = 'root';      // utilisateur par défaut WAMP
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}
