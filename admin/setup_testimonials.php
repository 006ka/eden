<?php
// Run this file once from the admin area to ensure the testimonials table exists.
require_once __DIR__ . '/../config/db.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS testimonials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        author VARCHAR(255) NOT NULL,
        role VARCHAR(255) DEFAULT NULL,
        content TEXT NOT NULL,
        image_url VARCHAR(512) DEFAULT NULL,
        visible TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    echo "Table 'testimonials' OK or already exists.";
} catch (PDOException $e) {
    echo 'Erreur lors de la création de la table testimonials: ' . $e->getMessage();
}

// Optionally seed a sample testimonial if table empty
try {
    $count = (int) $pdo->query('SELECT COUNT(*) as c FROM testimonials')->fetch(PDO::FETCH_ASSOC)['c'];
    if ($count === 0) {
        $stmt = $pdo->prepare('INSERT INTO testimonials (author, role, content, image_url, visible, created_at) VALUES (?, ?, ?, ?, 1, NOW())');
        $stmt->execute(['Marie', 'Retraitante', 'Dieu a transformé ma vie durant la retraite à EDEN. Un temps profond.', null]);
        echo "\nSeed testimonial inserted.";
    }
} catch (Exception $e) {
    // ignore
}

?>