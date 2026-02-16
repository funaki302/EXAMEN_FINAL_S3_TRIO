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
        $this->db->execute($sql, [
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
        return $this->db->execute($sql, [
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
        return $this->db->execute($sql, ['id_ville' => $id_ville]);
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
}