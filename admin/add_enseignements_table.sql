-- Table des enseignements
CREATE TABLE IF NOT EXISTS enseignements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    auteur VARCHAR(255) NOT NULL,
    date_publication DATE NOT NULL,
    contenu LONGTEXT NOT NULL,
    fichier_url VARCHAR(500) DEFAULT NULL,
    image_url VARCHAR(500) DEFAULT NULL,
    description TEXT,
    categorie VARCHAR(100) DEFAULT 'Général',
    duree_minutes INT DEFAULT 0,
    est_public TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ajout d'un index pour optimiser les recherches
CREATE INDEX idx_enseignements_categorie ON enseignements (categorie);
CREATE INDEX idx_enseignements_public ON enseignements (est_public);

-- Insertion d'un exemple d'enseignement
INSERT INTO enseignements (titre, auteur, date_publication, contenu, description, categorie, duree_minutes) VALUES
('La grâce de Dieu', 'Pasteur Jean Dupont', '2024-01-01', 
'<p>La grâce de Dieu est un don immérité que nous recevons par la foi en Jésus-Christ. Elle nous est offerte non pas à cause de nos œuvres, mais selon la miséricorde de Dieu.</p>',
'Un enseignement profond sur la grâce de Dieu et son impact dans notre vie quotidienne.',
'Doctrine', 45);
