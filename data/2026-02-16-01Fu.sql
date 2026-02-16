USE BNGRC;

CREATE OR REPLACE VIEW BNGRC_V_Besoins_Par_Ville AS
SELECT
    v.id_ville,
    v.nom_ville,
    v.region,
    a.id_article,
    a.nom_article,
    a.categorie,
    SUM(bv.quantite_demandee) AS quantite_demandee_totale,
    MAX(bv.date_saisie) AS derniere_saisie
FROM BNGRC_villes v
JOIN BNGRC_besoins_villes bv ON bv.id_ville = v.id_ville
JOIN BNGRC_articles a ON a.id_article = bv.id_article
GROUP BY v.id_ville, v.nom_ville, v.region, a.id_article, a.nom_article, a.categorie;

CREATE OR REPLACE VIEW BNGRC_V_Distributions_Par_Ville AS
SELECT
    v.id_ville,
    v.nom_ville,
    v.region,
    a.id_article,
    a.nom_article,
    a.categorie,
    SUM(d.quantite_attribuee) AS quantite_attribuee_totale,
    MAX(d.date_attribution) AS derniere_attribution
FROM BNGRC_villes v
JOIN BNGRC_distributions d ON d.id_ville = v.id_ville
JOIN BNGRC_dons_recus dr ON dr.id_don = d.id_don
JOIN BNGRC_articles a ON a.id_article = dr.id_article
GROUP BY v.id_ville, v.nom_ville, v.region, a.id_article, a.nom_article, a.categorie;
