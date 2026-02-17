<?php
namespace app\models;

use Flight;

class Distributions {
    private $db;

    public function __construct() {
        $this->db = Flight::db();
    }

    public function getAll() {
        $sql = "SELECT d.*, dr.id_article, dr.date_reception, a.nom_article, a.categorie, v.nom_ville, v.region
                FROM BNGRC_distributions d
                JOIN BNGRC_dons_recus dr ON d.id_don = dr.id_don
                JOIN BNGRC_articles a ON dr.id_article = a.id_article
                JOIN BNGRC_villes v ON d.id_ville = v.id_ville
                ORDER BY d.date_attribution DESC";
        return $this->db->fetchAll($sql);
    }

    public function getById($id_distribution) {
        $sql = "SELECT d.*, dr.id_article, dr.date_reception, a.nom_article, a.categorie, v.nom_ville, v.region
                FROM BNGRC_distributions d
                JOIN BNGRC_dons_recus dr ON d.id_don = dr.id_don
                JOIN BNGRC_articles a ON dr.id_article = a.id_article
                JOIN BNGRC_villes v ON d.id_ville = v.id_ville
                WHERE d.id_distribution = :id_distribution";
        return $this->db->fetchRow($sql, ['id_distribution' => $id_distribution]);
    }

    public function create($id_don, $id_ville, $quantite_attribuee, $date_attribution = null, $id_mode = 1) {
        if ($date_attribution) {
            $sql = "INSERT INTO BNGRC_distributions (id_don, id_ville, quantite_attribuee, date_attribution, id_mode)
                    VALUES (:id_don, :id_ville, :quantite_attribuee, :date_attribution, :id_mode)";
            $this->db->runQuery($sql, [
                'id_don' => $id_don,
                'id_ville' => $id_ville,
                'quantite_attribuee' => $quantite_attribuee,
                'date_attribution' => $date_attribution,
                'id_mode' => $id_mode
            ]);
        } else {
            $sql = "INSERT INTO BNGRC_distributions (id_don, id_ville, quantite_attribuee, id_mode)
                    VALUES (:id_don, :id_ville, :quantite_attribuee, :id_mode)";
            $this->db->runQuery($sql, [
                'id_don' => $id_don,
                'id_ville' => $id_ville,
                'quantite_attribuee' => $quantite_attribuee,
                'id_mode' => $id_mode
            ]);
        }

        return $this->db->lastInsertId();
    }

    public function update($id_distribution, $id_don, $id_ville, $quantite_attribuee, $date_attribution) {
        $sql = "UPDATE BNGRC_distributions
                SET id_don = :id_don, id_ville = :id_ville, quantite_attribuee = :quantite_attribuee, date_attribution = :date_attribution
                WHERE id_distribution = :id_distribution";
        return $this->db->runQuery($sql, [
            'id_distribution' => $id_distribution,
            'id_don' => $id_don,
            'id_ville' => $id_ville,
            'quantite_attribuee' => $quantite_attribuee,
            'date_attribution' => $date_attribution
        ]);
    }

    public function delete($id_distribution) {
        $sql = "DELETE FROM BNGRC_distributions WHERE id_distribution = :id_distribution";
        return $this->db->runQuery($sql, ['id_distribution' => $id_distribution]);
    }

    public function count() {
        $sql = "SELECT COUNT(*) as total FROM BNGRC_distributions";
        $result = $this->db->fetchRow($sql);
        return $result['total'];
    }

    public function exists($id_distribution) {
        $sql = "SELECT COUNT(*) as count FROM BNGRC_distributions WHERE id_distribution = :id_distribution";
        $result = $this->db->fetchRow($sql, ['id_distribution' => $id_distribution]);
        return $result['count'] > 0;
    }
}
