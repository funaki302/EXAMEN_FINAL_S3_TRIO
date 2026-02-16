<?php
namespace app\models;

use Flight;
use PDO;
use PDOException;

class Villes {
    private $db;
    
    public function __construct() {
        $this->db = Flight::db();
    }
    
    /**
     * Récupérer toutes les villes
     */
    public function getAll() {
        $sql = "SELECT * FROM BNGRC_villes ORDER BY nom_ville ASC";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Récupérer une ville par son ID
     */
    public function getById($id_ville) {
        $sql = "SELECT * FROM BNGRC_villes WHERE id_ville = :id_ville";
        return $this->db->fetchRow($sql, ['id_ville' => $id_ville]);
    }
    
    /**
     * Récupérer les villes par région
     */
    public function getByRegion($region) {
        $sql = "SELECT * FROM BNGRC_villes WHERE region = :region ORDER BY nom_ville ASC";
        return $this->db->fetchAll($sql, ['region' => $region]);
    }
    
    /**
     * Ajouter une nouvelle ville
     */
    public function create($nom_ville, $region) {
        $sql = "INSERT INTO BNGRC_villes (nom_ville, region) VALUES (:nom_ville, :region)";
        $this->db->runQuery($sql, [
            'nom_ville' => $nom_ville,
            'region' => $region
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Mettre à jour une ville
     */
    public function update($id_ville, $nom_ville, $region) {
        $sql = "UPDATE BNGRC_villes SET nom_ville = :nom_ville, region = :region WHERE id_ville = :id_ville";
        return $this->db->runQuery($sql, [
            'id_ville' => $id_ville,
            'nom_ville' => $nom_ville,
            'region' => $region
        ]);
    }
    
    /**
     * Supprimer une ville
     */
    public function delete($id_ville) {
        $sql = "DELETE FROM BNGRC_villes WHERE id_ville = :id_ville";
        return $this->db->runQuery($sql, ['id_ville' => $id_ville]);
    }
    
    /**
     * Compter le nombre de villes
     */
    public function count() {
        $sql = "SELECT COUNT(*) as total FROM BNGRC_villes";
        $result = $this->db->fetchRow($sql);
        return $result['total'];
    }
    
    /**
     * Récupérer toutes les régions distinctes
     */
    public function getAllRegions() {
        $sql = "SELECT DISTINCT region FROM BNGRC_villes ORDER BY region ASC";
        return $this->db->fetchAll($sql);
    }

    public function getObjectifsDashboard() {
        $villesSql = "SELECT id_ville, nom_ville, region FROM BNGRC_villes ORDER BY nom_ville ASC";
        $villes = $this->db->fetchAll($villesSql);

        $besoinsSql = "SELECT id_ville, id_article, nom_article, categorie, quantite_demandee_totale
                      FROM BNGRC_V_Besoins_Par_Ville";
        $besoins = $this->db->fetchAll($besoinsSql);

        $attribSql = "SELECT id_ville, id_article, nom_article, categorie, quantite_attribuee_totale
                     FROM BNGRC_V_Distributions_Par_Ville";
        $attribues = $this->db->fetchAll($attribSql);

        $map = [];
        foreach ($villes as $v) {
            $map[$v['id_ville']] = [
                'id_ville' => $v['id_ville'],
                'nom_ville' => $v['nom_ville'],
                'region' => $v['region'],
                'besoins' => [],
                'attribues' => []
            ];
        }

        foreach ($besoins as $b) {
            $idVille = $b['id_ville'];
            if (!isset($map[$idVille])) {
                continue;
            }
            $map[$idVille]['besoins'][] = [
                'id_article' => $b['id_article'],
                'nom_article' => $b['nom_article'],
                'categorie' => $b['categorie'],
                'quantite' => $b['quantite_demandee_totale']
            ];
        }

        foreach ($attribues as $a) {
            $idVille = $a['id_ville'];
            if (!isset($map[$idVille])) {
                continue;
            }
            $map[$idVille]['attribues'][] = [
                'id_article' => $a['id_article'],
                'nom_article' => $a['nom_article'],
                'categorie' => $a['categorie'],
                'quantite' => $a['quantite_attribuee_totale']
            ];
        }

        return array_values($map);
    }
}