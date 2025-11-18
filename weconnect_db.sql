-- Base de données WeConnect
CREATE DATABASE IF NOT EXISTS weconnect CHARACTER SET utf8 COLLATE utf8_general_ci;
USE weconnect;

-- Table utilisateurs
CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_utilisateur VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    biographie TEXT,
    avatar VARCHAR(255),
    date_creation DATETIME NOT NULL,
    INDEX idx_nom_utilisateur (nom_utilisateur)
) ENGINE=InnoDB;

-- Table publications
CREATE TABLE publications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    contenu TEXT NOT NULL,
    image VARCHAR(255),
    date_creation DATETIME NOT NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_date_creation (date_creation)
) ENGINE=InnoDB;

-- Table commentaires
CREATE TABLE commentaires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_publication INT NOT NULL,
    id_utilisateur INT NOT NULL,
    contenu TEXT NOT NULL,
    date_creation DATETIME NOT NULL,
    FOREIGN KEY (id_publication) REFERENCES publications(id) ON DELETE CASCADE,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_publication (id_publication)
) ENGINE=InnoDB;

-- Table likes
CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_publication INT NOT NULL,
    id_utilisateur INT NOT NULL,
    date_creation DATETIME NOT NULL,
    UNIQUE KEY unique_like (id_publication, id_utilisateur),
    FOREIGN KEY (id_publication) REFERENCES publications(id) ON DELETE CASCADE,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;
