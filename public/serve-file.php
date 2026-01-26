<?php
// Proxy pour servir les fichiers PDF/audio avec le bon chemin

$filePath = isset($_GET['file']) ? $_GET['file'] : '';

// Sécurité : vérifier que le chemin ne remonte pas hors du dossier uploads
if (empty($filePath) || strpos($filePath, '..') !== false) {
    http_response_code(400);
    die('Invalid file path');
}

// Construire le chemin complet
$fullPath = __DIR__ . '/../' . $filePath;

// Vérifier que le fichier existe et est dans le bon répertoire
if (!file_exists($fullPath) || strpos(realpath($fullPath), realpath(__DIR__ . '/../uploads')) === false) {
    http_response_code(404);
    die('File not found');
}

// Déterminer le type MIME
$ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
$mimeTypes = [
    'pdf' => 'application/pdf',
    'mp3' => 'audio/mpeg',
    'wav' => 'audio/wav',
    'm4a' => 'audio/mp4',
    'ogg' => 'audio/ogg',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
];

$mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';

// Envoyer le fichier
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($fullPath));
header('Accept-Ranges: bytes');

// Pour les PDFs, permettre l'affichage inline
if ($ext === 'pdf') {
    header('Content-Disposition: inline; filename="' . basename($fullPath) . '"');
} else {
    header('Content-Disposition: attachment; filename="' . basename($fullPath) . '"');
}

readfile($fullPath);
?>
