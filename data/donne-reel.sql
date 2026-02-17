-- ==========================================================
-- 1. INSERTION DES MODES
-- ==========================================================

-- ==========================================================
-- 2. INSERTION DES VILLES
-- ==========================================================
INSERT INTO BNGRC_villes (nom_ville, region) VALUES 
('Toamasina', 'Atsinanana'),
('Mananjary', 'Vatovavy'),
('Farafangana', 'Atsimo-Atsinanana'),
('Nosy Be', 'DIANA'),
('Morondava', 'Menabe');

-- ==========================================================
-- 3. INSERTION DES ARTICLES
-- ==========================================================
INSERT INTO BNGRC_articles (nom_article, categorie, prix_unitaire) VALUES 
('Riz (kg)', 'Nature', 3000),
('Eau (L)', 'Nature', 1000),
('Tôle', 'Matériaux', 25000),
('Bâche', 'Matériaux', 15000),
('Argent', 'Argent', 1),
('Huile (L)', 'Nature', 6000),
('Clous (kg)', 'Matériaux', 8000),
('Bois', 'Matériaux', 10000),
('Haricots', 'Nature', 4000),
('groupe', 'Matériaux', 6750000);

-- ==========================================================
-- 4. INSERTION DES BESOINS (AVEC ID_MODE = 1)
-- ==========================================================
INSERT INTO BNGRC_besoins_villes (id_ville, id_article, id_mode, quantite_demandee, date_saisie)
VALUES 
-- Toamasina
((SELECT id_ville FROM BNGRC_villes WHERE nom_ville='Toamasina'), (SELECT id_article FROM BNGRC_articles WHERE nom_article='Riz (kg)'), 1, 800, '2026-02-16'),
((SELECT id_ville FROM BNGRC_villes WHERE nom_ville='Toamasina'), (SELECT id_article FROM BNGRC_articles WHERE nom_article='Eau (L)'), 1, 1500, '2026-02-15'),
((SELECT id_ville FROM BNGRC_villes WHERE nom_ville='Toamasina'), (SELECT id_article FROM BNGRC_articles WHERE nom_article='Tôle'), 1, 120, '2026-02-16'),
((SELECT id_ville FROM BNGRC_villes WHERE nom_ville='Toamasina'), (SELECT id_article FROM BNGRC_articles WHERE nom_article='Bâche'), 1, 200, '2026-02-15'),
((SELECT id_ville FROM BNGRC_villes WHERE nom_ville='Toamasina'), (SELECT id_article FROM BNGRC_articles WHERE nom_article='Argent'), 1, 12000000, '2026-02-16'),
((SELECT id_ville FROM BNGRC_villes WHERE nom_ville='Toamasina'), (SELECT id_article FROM BNGRC_articles WHERE nom_article='groupe'), 1, 3, '2026-02-16'),

-- Mananjary
((SELECT id_ville FROM BNGRC_villes WHERE nom_ville='Mananjary'), (SELECT id_article FROM BNGRC_articles WHERE nom_article='Riz (kg)'), 1, 500, '2026-02-15'),
((SELECT id_ville FROM BNGRC_villes WHERE nom_ville='Mananjary'), (SELECT id_article FROM BNGRC_articles WHERE nom_article='Huile (L)'), 1, 120, '2026-02-16'),
((SELECT id_ville FROM BNGRC_villes WHERE nom_ville='Mananjary'), (SELECT id_article FROM BNGRC_articles WHERE nom_article='Tôle'), 1, 80, '2026-02-15'),
((SELECT id_ville FROM BNGRC_villes WHERE nom_ville='Mananjary'), (SELECT id_article FROM BNGRC_articles WHERE nom_article='Clous (kg)'), 1, 60, '2026-02-16'),
((SELECT id_ville FROM BNGRC_villes WHERE nom_ville='Mananjary'), (SELECT id_article FROM BNGRC_articles WHERE nom_article='Argent'), 1, 6000000, '2026-02-15'),

-- Farafangana
((SELECT id_ville FROM BNGRC_villes WHERE nom_ville='Farafangana'), (SELECT id_article FROM BNGRC_articles WHERE nom_article='Riz (kg)'), 1, 600, '2026-02-16'),
((SELECT id_ville FROM BNGRC_villes WHERE nom_ville='Farafangana'), (SELECT id_article FROM BNGRC_articles WHERE nom_article='Eau (L)'), 1, 1000, '2026-02-15'),
((SELECT id_ville FROM BNGRC_villes WHERE nom_ville='Farafangana'), (SELECT id_article FROM BNGRC_articles WHERE nom_article='Bâche'), 1, 150, '2026-02-16'),
((SELECT id_ville FROM BNGRC_villes WHERE nom_ville='Farafangana'), (SELECT id_article FROM BNGRC_articles WHERE nom_article='Bois'), 1, 100, '2026-02-15'),
((SELECT id_ville FROM BNGRC_villes WHERE nom_ville='Farafangana'), (SELECT id_article FROM BNGRC_articles WHERE nom_article='Argent'), 1, 8000000, '2026-02-16'),

-- Nosy Be
((SELECT id_ville FROM BNGRC_villes WHERE nom_ville='Nosy Be'), (SELECT id_article FROM BNGRC_articles WHERE nom_article='Riz (kg)'), 1, 300, '2026-02-15'),
((SELECT id_ville FROM BNGRC_villes WHERE nom_ville='Nosy Be'), (SELECT id_article FROM BNGRC_articles WHERE nom_article='Haricots'), 1, 200, '2026-02-16'),
((SELECT id_ville FROM BNGRC_villes WHERE nom_ville='Nosy Be'), (SELECT id_article FROM BNGRC_articles WHERE nom_article='Tôle'), 1, 40, '2026-02-15'),
((SELECT id_ville FROM BNGRC_villes WHERE nom_ville='Nosy Be'), (SELECT id_article FROM BNGRC_articles WHERE nom_article='Clous (kg)'), 1, 30, '2026-02-16'),
((SELECT id_ville FROM BNGRC_villes WHERE nom_ville='Nosy Be'), (SELECT id_article FROM BNGRC_articles WHERE nom_article='Argent'), 1, 4000000, '2026-02-15'),

-- Morondava
((SELECT id_ville FROM BNGRC_villes WHERE nom_ville='Morondava'), (SELECT id_article FROM BNGRC_articles WHERE nom_article='Riz (kg)'), 1, 700, '2026-02-15'),
((SELECT id_ville FROM BNGRC_villes WHERE nom_ville='Morondava'), (SELECT id_article FROM BNGRC_articles WHERE nom_article='Eau (L)'), 1, 1200, '2026-02-16'),
((SELECT id_ville FROM BNGRC_villes WHERE nom_ville='Morondava'), (SELECT id_article FROM BNGRC_articles WHERE nom_article='Bâche'), 1, 180, '2026-02-15'),
((SELECT id_ville FROM BNGRC_villes WHERE nom_ville='Morondava'), (SELECT id_article FROM BNGRC_articles WHERE nom_article='Bois'), 1, 150, '2026-02-16'),
((SELECT id_ville FROM BNGRC_villes WHERE nom_ville='Morondava'), (SELECT id_article FROM BNGRC_articles WHERE nom_article='Argent'), 1, 10000000, '2026-02-16');