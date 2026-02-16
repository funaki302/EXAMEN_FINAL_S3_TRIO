
CREATE TABLE IF NOT EXISTS BNGRC_achats (
    id_achat INT AUTO_INCREMENT PRIMARY KEY,
    id_ville INT NOT NULL,
    id_article INT NOT NULL,
    quantite_achetee DECIMAL(15, 2) NOT NULL,
    prix_unitaire DECIMAL(15, 2) NOT NULL,
    taux_frais_pourcent DECIMAL(7, 4) NOT NULL,
    montant_ht DECIMAL(15, 2) NOT NULL,
    montant_frais DECIMAL(15, 2) NOT NULL,
    montant_ttc DECIMAL(15, 2) NOT NULL,
    date_achat DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_ville) REFERENCES BNGRC_villes(id_ville),
    FOREIGN KEY (id_article) REFERENCES BNGRC_articles(id_article)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS BNGRC_transactions_argent (
    id_transaction INT AUTO_INCREMENT PRIMARY KEY,
    type_transaction ENUM('ENTREE_DON', 'SORTIE_ACHAT') NOT NULL,
    montant DECIMAL(15, 2) NOT NULL,
    id_don INT NULL,
    id_achat INT NULL,
    date_transaction DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_don) REFERENCES BNGRC_dons_recus(id_don),
    FOREIGN KEY (id_achat) REFERENCES BNGRC_achats(id_achat)
) ENGINE=InnoDB;

CREATE OR REPLACE VIEW BNGRC_V_Total_Argent_Dons AS
SELECT
    IFNULL(SUM(dr.quantite_donnee), 0) AS total_entrees
FROM BNGRC_dons_recus dr
JOIN BNGRC_articles a ON a.id_article = dr.id_article
WHERE a.categorie = 'Argent';

CREATE OR REPLACE VIEW BNGRC_V_Total_Argent_Sorti_Achats AS
SELECT
    IFNULL(SUM(ac.montant_ttc), 0) AS total_sorties
FROM BNGRC_achats ac;

CREATE OR REPLACE VIEW BNGRC_V_Solde_Argent AS
SELECT
    d.total_entrees,
    s.total_sorties,
    (d.total_entrees - s.total_sorties) AS solde
FROM BNGRC_V_Total_Argent_Dons d
CROSS JOIN BNGRC_V_Total_Argent_Sorti_Achats s;

CREATE OR REPLACE VIEW BNGRC_V_Recap_Besoins_Montant AS
SELECT
    bv.id_ville,
    v.nom_ville,
    v.region,
    SUM(bv.quantite_demandee * a.prix_unitaire) AS montant_besoin_total
FROM BNGRC_besoins_villes bv
JOIN BNGRC_villes v ON v.id_ville = bv.id_ville
JOIN BNGRC_articles a ON a.id_article = bv.id_article
WHERE a.categorie IN ('Nature', 'Matériaux')
GROUP BY bv.id_ville, v.nom_ville, v.region;

CREATE OR REPLACE VIEW BNGRC_V_Recap_Distributions_Montant AS
SELECT
    d.id_ville,
    SUM(d.quantite_attribuee * a.prix_unitaire) AS montant_satisfait
FROM BNGRC_distributions d
JOIN BNGRC_dons_recus dr ON dr.id_don = d.id_don
JOIN BNGRC_articles a ON a.id_article = dr.id_article
WHERE a.categorie IN ('Nature', 'Matériaux')
GROUP BY d.id_ville;

CREATE OR REPLACE VIEW BNGRC_V_Recap_Global_Montant AS
SELECT
    IFNULL(SUM(bv.quantite_demandee * a.prix_unitaire), 0) AS montant_besoin_total,
    (
        SELECT IFNULL(SUM(d.quantite_attribuee * a2.prix_unitaire), 0)
        FROM BNGRC_distributions d
        JOIN BNGRC_dons_recus dr2 ON dr2.id_don = d.id_don
        JOIN BNGRC_articles a2 ON a2.id_article = dr2.id_article
        WHERE a2.categorie IN ('Nature', 'Matériaux')
    ) AS montant_satisfait,
    (
        IFNULL(SUM(bv.quantite_demandee * a.prix_unitaire), 0)
        -
        (
            SELECT IFNULL(SUM(d.quantite_attribuee * a2.prix_unitaire), 0)
            FROM BNGRC_distributions d
            JOIN BNGRC_dons_recus dr2 ON dr2.id_don = d.id_don
            JOIN BNGRC_articles a2 ON a2.id_article = dr2.id_article
            WHERE a2.categorie IN ('Nature', 'Matériaux')
        )
    ) AS montant_restant
FROM BNGRC_besoins_villes bv
JOIN BNGRC_articles a ON a.id_article = bv.id_article
WHERE a.categorie IN ('Nature', 'Matériaux');

CREATE OR REPLACE VIEW BNGRC_V_Dons_Restants_Par_Article AS
SELECT
    a.id_article,
    a.nom_article,
    a.categorie,
    IFNULL(SUM(dr.quantite_donnee), 0) AS quantite_donnee_totale,
    IFNULL(SUM(IFNULL(dsum.quantite_attribuee_totale, 0)), 0) AS quantite_attribuee_totale,
    IFNULL(SUM(dr.quantite_donnee - IFNULL(dsum.quantite_attribuee_totale, 0)), 0) AS quantite_restante
FROM BNGRC_articles a
LEFT JOIN BNGRC_dons_recus dr ON dr.id_article = a.id_article
LEFT JOIN (
    SELECT id_don, SUM(quantite_attribuee) AS quantite_attribuee_totale
    FROM BNGRC_distributions
    GROUP BY id_don
) dsum ON dsum.id_don = dr.id_don
GROUP BY a.id_article, a.nom_article, a.categorie;

CREATE OR REPLACE VIEW BNGRC_V_Transactions_Argent_Ledger AS
SELECT
    t.id_transaction,
    t.type_transaction,
    t.montant,
    t.id_don,
    t.id_achat,
    t.date_transaction
FROM BNGRC_transactions_argent t
ORDER BY t.date_transaction DESC, t.id_transaction DESC;
