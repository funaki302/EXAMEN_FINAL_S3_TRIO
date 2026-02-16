<?php
namespace app\models;

use Flight;
use PDO;
use PDOException;

class DonsRecus {
    private $db;
    
    public function __construct() {
        $this->db = Flight::db();
    }
    
    /**
     * Récupérer tous les dons reçus
     */
    public function getAll() {
        $sql = "SELECT dr.*, a.nom_article, a.categorie, a.prix_unitaire 
                FROM BNGRC_dons_recus dr 
                JOIN BNGRC_articles a ON dr.id_article = a.id_article 
                ORDER BY dr.date_reception DESC";
        return $this->db->fetchAll($sql);
    }
    

    public function getDonsRestants() {
        $sql = "SELECT *
                FROM BNGRC_V_Dons_Restants_Par_Article 
                ";
        return $this->db->fetchAll($sql);
    }
    /**
     * Récupérer un don par son ID
     */
    public function getById($id_don) {
        $sql = "SELECT dr.*, a.nom_article, a.categorie, a.prix_unitaire 
                FROM BNGRC_dons_recus dr 
                JOIN BNGRC_articles a ON dr.id_article = a.id_article 
                WHERE dr.id_don = :id_don";
        return $this->db->fetchRow($sql, ['id_don' => $id_don]);
    }
    
    /**
     * Récupérer les dons par article
     */
    public function getByArticle($id_article) {
        $sql = "SELECT dr.*, a.nom_article, a.categorie, a.prix_unitaire 
                FROM BNGRC_dons_recus dr 
                JOIN BNGRC_articles a ON dr.id_article = a.id_article 
                WHERE dr.id_article = :id_article 
                ORDER BY dr.date_reception DESC";
        return $this->db->fetchAll($sql, ['id_article' => $id_article]);
    }
    
    /**
     * Récupérer les dons par date
     */
    public function getByDate($date) {
        $sql = "SELECT dr.*, a.nom_article, a.categorie, a.prix_unitaire 
                FROM BNGRC_dons_recus dr 
                JOIN BNGRC_articles a ON dr.id_article = a.id_article 
                WHERE DATE(dr.date_reception) = :date 
                ORDER BY dr.date_reception DESC";
        return $this->db->fetchAll($sql, ['date' => $date]);
    }
    
    /**
     * Ajouter un nouveau don
     */
    public function create($id_article, $quantite_donnee, $date_reception) {
        $sql = "INSERT INTO BNGRC_dons_recus (id_article, quantite_donnee, date_reception) VALUES (:id_article, :quantite_donnee, :date_reception)";
        $this->db->runQuery($sql, [
            'id_article' => $id_article,
            'quantite_donnee' => $quantite_donnee,
            'date_reception' => $date_reception
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Mettre à jour un don
     */
    public function update($id_don, $id_article, $quantite_donnee, $date_reception) {
        $sql = "UPDATE BNGRC_dons_recus SET id_article = :id_article, quantite_donnee = :quantite_donnee, date_reception = :date_reception WHERE id_don = :id_don";
        return $this->db->runQuery($sql, [
            'id_don' => $id_don,
            'id_article' => $id_article,
            'quantite_donnee' => $quantite_donnee,
            'date_reception' => $date_reception
        ]);
    }
    
    /**
     * Supprimer un don
     */
    public function delete($id_don) {
        $sql = "DELETE FROM BNGRC_dons_recus WHERE id_don = :id_don";
        return $this->db->runQuery($sql, ['id_don' => $id_don]);
    }
    
    /**
     * Compter le nombre de dons
     */
    public function count() {
        $sql = "SELECT COUNT(*) as total FROM BNGRC_dons_recus";
        $result = $this->db->fetchRow($sql);
        return $result['total'];
    }
    
    /**
     * Récupérer les statistiques des dons par article
     */
    public function getStatsByArticle() {
        $sql = "SELECT a.id_article, a.nom_article, a.categorie, COUNT(*) as nombre_dons, SUM(dr.quantite_donnee) as quantite_totale, SUM(dr.quantite_donnee * a.prix_unitaire) as valeur_totale
                FROM BNGRC_dons_recus dr 
                JOIN BNGRC_articles a ON dr.id_article = a.id_article 
                GROUP BY a.id_article, a.nom_article, a.categorie 
                ORDER BY quantite_totale DESC";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Récupérer les statistiques des dons par catégorie
     */
    public function getStatsByCategorie() {
        $sql = "SELECT a.categorie, COUNT(*) as nombre_dons, SUM(dr.quantite_donnee) as quantite_totale, SUM(dr.quantite_donnee * a.prix_unitaire) as valeur_totale
                FROM BNGRC_dons_recus dr 
                JOIN BNGRC_articles a ON dr.id_article = a.id_article 
                GROUP BY a.categorie 
                ORDER BY valeur_totale DESC";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Récupérer les dons par période
     */
    public function getByPeriod($date_debut, $date_fin) {
        $sql = "SELECT dr.*, a.nom_article, a.categorie, a.prix_unitaire 
                FROM BNGRC_dons_recus dr 
                JOIN BNGRC_articles a ON dr.id_article = a.id_article 
                WHERE dr.date_reception BETWEEN :date_debut AND :date_fin 
                ORDER BY dr.date_reception DESC";
        return $this->db->fetchAll($sql, [
            'date_debut' => $date_debut,
            'date_fin' => $date_fin
        ]);
    }
    
    /**
     * Calculer la valeur totale des dons
     */
    public function getValeurTotale() {
        $sql = "SELECT SUM(dr.quantite_donnee * a.prix_unitaire) as valeur_totale 
                FROM BNGRC_dons_recus dr 
                JOIN BNGRC_articles a ON dr.id_article = a.id_article";
        $result = $this->db->fetchRow($sql);
        return $result['valeur_totale'] ?? 0;
    }
    
    /**
     * Vérifier si un don existe
     */
    public function exists($id_don) {
        $sql = "SELECT COUNT(*) as count FROM BNGRC_dons_recus WHERE id_don = :id_don";
        $result = $this->db->fetchRow($sql, ['id_don' => $id_don]);
        return $result['count'] > 0;
    }
}
