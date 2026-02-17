<?php
namespace app\models;

use Flight;

class Achats {
    private $db;
    private $articlesModel;
    private $dispatchModel;

    public function __construct() {
        $this->db = Flight::db();
        $this->articlesModel = new Articles();
        $this->dispatchModel = new Dispatch();
    }

    public function computeMontants($idArticle, $quantiteAchetee, $tauxFraisPourcent) {
        $article = $this->articlesModel->getById($idArticle);
        if (!$article) {
            throw new \Exception('Article non trouvé');
        }

        $prixUnitaire = (float) $article['prix_unitaire'];
        $montantHt = $prixUnitaire * (float) $quantiteAchetee;
        $montantFrais = $montantHt * ((float) $tauxFraisPourcent / 100.0);
        $montantTtc = $montantHt + $montantFrais;

        return [
            'prix_unitaire' => $prixUnitaire,
            'montant_ht' => $montantHt,
            'montant_frais' => $montantFrais,
            'montant_ttc' => $montantTtc,
        ];
    }

    public function getResteBesoinVilleArticle($idVille, $idArticle) {
        $sqlBesoin = "SELECT IFNULL(SUM(quantite_demandee), 0) AS besoin_total
                      FROM BNGRC_besoins_villes
                      WHERE id_ville = :id_ville AND id_article = :id_article";

        $sqlAttrib = "SELECT IFNULL(SUM(d.quantite_attribuee), 0) AS attribue_total
                      FROM BNGRC_distributions d
                      JOIN BNGRC_dons_recus dr ON dr.id_don = d.id_don
                      WHERE d.id_ville = :id_ville AND dr.id_article = :id_article";

        $besoinRow = $this->db->fetchRow($sqlBesoin, ['id_ville' => $idVille, 'id_article' => $idArticle]);
        $attribRow = $this->db->fetchRow($sqlAttrib, ['id_ville' => $idVille, 'id_article' => $idArticle]);

        $besoinTotal = (float) ($besoinRow['besoin_total'] ?? 0);
        $attribTotal = (float) ($attribRow['attribue_total'] ?? 0);

        return max(0.0, $besoinTotal - $attribTotal);
    }

    public function hasDonsRestantsPourArticle($idArticle) {
        $sql = "SELECT dr.id_don,
                       (dr.quantite_donnee - IFNULL(SUM(d.quantite_attribuee), 0)) AS reste_don
                FROM BNGRC_dons_recus dr
                LEFT JOIN BNGRC_distributions d ON d.id_don = dr.id_don
                WHERE dr.id_article = :id_article
                GROUP BY dr.id_don, dr.quantite_donnee
                HAVING reste_don > 0
                LIMIT 1";

        $row = $this->db->fetchRow($sql, ['id_article' => $idArticle]);
        if ($row instanceof \flight\util\Collection) {
            return $row->count() > 0;
        }

        return is_array($row) && count($row) > 0;
    }

    public function getBesoinsRestants($idVille = null) {
        $params = [];
        $whereVille = '';
        if ($idVille !== null) {
            $whereVille = ' AND bv.id_ville = :id_ville ';
            $params['id_ville'] = (int) $idVille;
        }

        $sql = "SELECT
                    bv.id_ville,
                    v.nom_ville,
                    v.region,
                    bv.id_article,
                    a.nom_article,
                    a.categorie,
                    a.prix_unitaire,
                    SUM(bv.quantite_demandee) AS besoin_total,
                    (
                        SELECT IFNULL(SUM(d.quantite_attribuee), 0)
                        FROM BNGRC_distributions d
                        JOIN BNGRC_dons_recus dr ON dr.id_don = d.id_don
                        WHERE d.id_ville = bv.id_ville
                          AND dr.id_article = bv.id_article
                    ) AS attribue_total
                FROM BNGRC_besoins_villes bv
                JOIN BNGRC_villes v ON v.id_ville = bv.id_ville
                JOIN BNGRC_articles a ON a.id_article = bv.id_article
                WHERE a.categorie IN ('Nature', 'Matériaux')
                $whereVille
                GROUP BY bv.id_ville, v.nom_ville, v.region, bv.id_article, a.nom_article, a.categorie, a.prix_unitaire
                HAVING (SUM(bv.quantite_demandee) - attribue_total) > 0
                ORDER BY v.nom_ville ASC, a.nom_article ASC";

        $rows = $this->db->fetchAll($sql, $params);
        $out = [];
        foreach ($rows as $r) {
            $besoinTotal = (float) ($r['besoin_total'] ?? 0);
            $attribTotal = (float) ($r['attribue_total'] ?? 0);
            $reste = max(0.0, $besoinTotal - $attribTotal);
            $prix = (float) ($r['prix_unitaire'] ?? 0);

            $out[] = [
                'id_ville' => (int) $r['id_ville'],
                'nom_ville' => $r['nom_ville'],
                'region' => $r['region'],
                'id_article' => (int) $r['id_article'],
                'nom_article' => $r['nom_article'],
                'categorie' => $r['categorie'],
                'prix_unitaire' => $prix,
                'besoin_total' => $besoinTotal,
                'attribue_total' => $attribTotal,
                'reste_a_combler' => $reste,
                'montant_restant' => $reste * $prix,
            ];
        }

        return $out;
    }

    public function create($idVille, $idArticle, $quantiteAchetee, $tauxFraisPourcent, $dateAchat, $idMode = 1) {
        $m = $this->computeMontants($idArticle, $quantiteAchetee, $tauxFraisPourcent);

        $sql = "INSERT INTO BNGRC_achats
                    (id_ville, id_article, quantite_achetee, prix_unitaire, taux_frais_pourcent, montant_ht, montant_frais, montant_ttc, date_achat, id_mode)
                VALUES
                    (:id_ville, :id_article, :quantite_achetee, :prix_unitaire, :taux_frais_pourcent, :montant_ht, :montant_frais, :montant_ttc, :date_achat, :id_mode)";

        $this->db->runQuery($sql, [
            'id_ville' => $idVille,
            'id_article' => $idArticle,
            'quantite_achetee' => $quantiteAchetee,
            'prix_unitaire' => $m['prix_unitaire'],
            'taux_frais_pourcent' => $tauxFraisPourcent,
            'montant_ht' => $m['montant_ht'],
            'montant_frais' => $m['montant_frais'],
            'montant_ttc' => $m['montant_ttc'],
            'date_achat' => $dateAchat,
            'id_mode' => $idMode,
        ]);

        $idAchat = $this->db->lastInsertId();

        return [
            'id_achat' => (int) $idAchat,
            'montants' => $m,
        ];
    }

    public function simulateDispatchApresAchat($idVille, $idArticle, $quantiteAchetee, $dateAttribution) {
        return [
            'distributions_creees' => 1,
            'quantite_attribuee_totale' => (float) $quantiteAchetee,
            'persisted' => false,
            'target' => [
                'id_ville' => (int) $idVille,
                'id_article' => (int) $idArticle,
                'date_attribution' => $dateAttribution,
            ],
        ];
    }
}
