<?php
namespace app\models;

use Flight;
use PDO;
use PDOException;

class Articles {
    private $db;
    
    public function __construct() {
        $this->db = Flight::db();
    }
    
    /**
     * Récupérer tous les articles
     */
    public function getAll() {
        $sql = "SELECT * FROM BNGRC_articles ORDER BY nom_article ASC";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Récupérer un article par son ID
     */
    public function getById($id_article) {
        $sql = "SELECT * FROM BNGRC_articles WHERE id_article = :id_article";
        return $this->db->fetchRow($sql, ['id_article' => $id_article]);
    }
    
    /**
     * Récupérer les articles par catégorie
     */
    public function getByCategorie($categorie) {
        $sql = "SELECT * FROM BNGRC_articles WHERE categorie = :categorie ORDER BY nom_article ASC";
        return $this->db->fetchAll($sql, ['categorie' => $categorie]);
    }
    
    /**
     * Ajouter un nouvel article
     */
    public function create($nom_article, $categorie, $prix_unitaire = 0) {
        $sql = "INSERT INTO BNGRC_articles (nom_article, categorie, prix_unitaire) VALUES (:nom_article, :categorie, :prix_unitaire)";
        $this->db->execute($sql, [
            'nom_article' => $nom_article,
            'categorie' => $categorie,
            'prix_unitaire' => $prix_unitaire
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Mettre à jour un article
     */
    public function update($id_article, $nom_article, $categorie, $prix_unitaire) {
        $sql = "UPDATE BNGRC_articles SET nom_article = :nom_article, categorie = :categorie, prix_unitaire = :prix_unitaire WHERE id_article = :id_article";
        return $this->db->execute($sql, [
            'id_article' => $id_article,
            'nom_article' => $nom_article,
            'categorie' => $categorie,
            'prix_unitaire' => $prix_unitaire
        ]);
    }
    
    /**
     * Supprimer un article
     */
    public function delete($id_article) {
        $sql = "DELETE FROM BNGRC_articles WHERE id_article = :id_article";
        return $this->db->execute($sql, ['id_article' => $id_article]);
    }
    
    /**
     * Compter le nombre d'articles
     */
    public function count() {
        $sql = "SELECT COUNT(*) as total FROM BNGRC_articles";
        $result = $this->db->fetchRow($sql);
        return $result['total'];
    }
    
    /**
     * Récupérer toutes les catégories distinctes
     */
    public function getAllCategories() {
        $sql = "SELECT DISTINCT categorie FROM BNGRC_articles ORDER BY categorie ASC";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Vérifier si un article existe
     */
    public function exists($id_article) {
        $sql = "SELECT COUNT(*) as count FROM BNGRC_articles WHERE id_article = :id_article";
        $result = $this->db->fetchRow($sql, ['id_article' => $id_article]);
        return $result['count'] > 0;
    }
}
