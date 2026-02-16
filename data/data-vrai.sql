-- Création de la base de données
CREATE DATABASE IF NOT EXISTS BNGRC;
USE BNGRC;

-- 1. Table des Villes (Les sinistrés sont répartis par ville [cite: 9])
CREATE TABLE BNGRC_villes (
    id_ville INT AUTO_INCREMENT PRIMARY KEY,
    nom_ville VARCHAR(100) NOT NULL,
    region VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- 2. Table des Articles (riz, huile, tôle, clou, argent [cite: 10, 11, 12])
-- Chaque besoin possède un prix unitaire qui ne change jamais [cite: 19]
CREATE TABLE BNGRC_articles (
    id_article INT AUTO_INCREMENT PRIMARY KEY,
    nom_article VARCHAR(100) NOT NULL,
    categorie ENUM('Nature', 'Matériaux', 'Argent') NOT NULL,
    prix_unitaire DECIMAL(15, 2) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- 3. Table des Besoins (Saisie par ville sans identifier le sinistré [cite: 14])
CREATE TABLE BNGRC_besoins_villes (
    id_besoin INT AUTO_INCREMENT PRIMARY KEY,
    id_ville INT NOT NULL,
    id_article INT NOT NULL,
    quantite_demandee DECIMAL(15, 2) NOT NULL,
    date_saisie DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_ville) REFERENCES BNGRC_villes(id_ville),
    FOREIGN KEY (id_article) REFERENCES BNGRC_articles(id_article)
) ENGINE=InnoDB;

-- 4. Table des Dons (Saisie des dons reçus )
CREATE TABLE BNGRC_dons_recus (
    id_don INT AUTO_INCREMENT PRIMARY KEY,
    id_article INT NOT NULL,
    quantite_donnee DECIMAL(15, 2) NOT NULL,
    date_reception DATETIME NOT NULL, -- Pour l'ordre de dispatch 
    FOREIGN KEY (id_article) REFERENCES BNGRC_articles(id_article)
) ENGINE=InnoDB;

-- 5. Table de Dispatch (Pour le tableau de bord et le suivi )
CREATE TABLE BNGRC_distributions (
    id_distribution INT AUTO_INCREMENT PRIMARY KEY,
    id_don INT NOT NULL,
    id_ville INT NOT NULL,
    quantite_attribuee DECIMAL(15, 2) NOT NULL,
    date_attribution DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_don) REFERENCES BNGRC_dons_recus(id_don),
    FOREIGN KEY (id_ville) REFERENCES BNGRC_villes(id_ville)
) ENGINE=InnoDB;

-- ==========================================
-- INSERTION DES DONNÉES DE TEST
-- ==========================================

-- Villes
INSERT INTO BNGRC_villes (nom_ville, region) VALUES 
('Antananarivo', 'Analamanga'),
('Tamatave', 'Atsinanana'),
('Mananjary', 'Vatovavy');

-- Articles avec prix unitaires fixes [cite: 19]
INSERT INTO BNGRC_articles (nom_article, categorie, prix_unitaire) VALUES 
('Riz', 'Nature', 3200.00),
('Huile', 'Nature', 9000.00),
('Tôle', 'Matériaux', 45000.00),
('Clou', 'Matériaux', 12000.00),
('Argent', 'Argent', 1.00);

-- Saisie des besoins par ville [cite: 14]
-- Tamatave a besoin de 500kg de riz et 100 tôles
INSERT INTO BNGRC_besoins_villes (id_ville, id_article, quantite_demandee) VALUES 
(2, 1, 500.00), 
(2, 3, 100.00);

-- Saisie d'un don de Riz reçu le 16 fév à 14h 
INSERT INTO BNGRC_dons_recus (id_article, quantite_donnee, date_reception) VALUES 
(1, 1000.00, '2026-02-16 14:00:00');