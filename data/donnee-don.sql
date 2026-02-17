-- ==========================================================
-- INSERTIONS DANS BNGRC_dons_recus (ID_MODE = 1)
-- ==========================================================

INSERT INTO BNGRC_dons_recus (id_article, quantite_donnee, date_reception, id_mode)
VALUES 
-- Argent
((SELECT id_article FROM BNGRC_articles WHERE nom_article='Argent'), 5000000, '2026-02-16', 1),
((SELECT id_article FROM BNGRC_articles WHERE nom_article='Argent'), 3000000, '2026-02-16', 1),
((SELECT id_article FROM BNGRC_articles WHERE nom_article='Argent'), 4000000, '2026-02-17', 1),
((SELECT id_article FROM BNGRC_articles WHERE nom_article='Argent'), 1500000, '2026-02-17', 1),
((SELECT id_article FROM BNGRC_articles WHERE nom_article='Argent'), 6000000, '2026-02-17', 1),
((SELECT id_article FROM BNGRC_articles WHERE nom_article='Argent'), 20000000, '2026-02-19', 1),

-- Nature
((SELECT id_article FROM BNGRC_articles WHERE nom_article='Riz (kg)'), 400, '2026-02-16', 1),
((SELECT id_article FROM BNGRC_articles WHERE nom_article='Eau (L)'), 600, '2026-02-16', 1),
((SELECT id_article FROM BNGRC_articles WHERE nom_article='Haricots'), 100, '2026-02-17', 1),
((SELECT id_article FROM BNGRC_articles WHERE nom_article='Riz (kg)'), 2000, '2026-02-18', 1),
((SELECT id_article FROM BNGRC_articles WHERE nom_article='Eau (L)'), 5000, '2026-02-18', 1),
((SELECT id_article FROM BNGRC_articles WHERE nom_article='Haricots'), 88, '2026-02-17', 1),

-- Materiel
((SELECT id_article FROM BNGRC_articles WHERE nom_article='Tôle'), 50, '2026-02-17', 1),
((SELECT id_article FROM BNGRC_articles WHERE nom_article='Bâche'), 70, '2026-02-17', 1),
((SELECT id_article FROM BNGRC_articles WHERE nom_article='Tôle'), 300, '2026-02-18', 1),
((SELECT id_article FROM BNGRC_articles WHERE nom_article='Bâche'), 500, '2026-02-19', 1);