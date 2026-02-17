<?php
namespace app\models;

use Flight;
use PDO;
use PDOException;

class BesoinsVilles {
    private $db;
    
    public function __construct() {
        $this->db = Flight::db();
    }
    
    /**
     * Récupérer tous les besoins des villes
     */
    public function getAll() {
        $sql = "SELECT bv.*, v.nom_ville, v.region, a.nom_article, a.categorie, a.prix_unitaire
                FROM BNGRC_besoins_villes bv 
                JOIN BNGRC_villes v ON bv.id_ville = v.id_ville 
                JOIN BNGRC_articles a ON bv.id_article = a.id_article 
                ORDER BY bv.date_saisie DESC";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Récupérer un besoin par son ID
     */
    public function getById($id_besoin) {
        $sql = "SELECT bv.*, v.nom_ville, v.region, a.nom_article, a.categorie 
                FROM BNGRC_besoins_villes bv 
                JOIN BNGRC_villes v ON bv.id_ville = v.id_ville 
                JOIN BNGRC_articles a ON bv.id_article = a.id_article 
                WHERE bv.id_besoin = :id_besoin";
        return $this->db->fetchRow($sql, ['id_besoin' => $id_besoin]);
    }
    
    /**
     * Récupérer les besoins par ville
     */
    public function getByVille($id_ville) {
        $sql = "SELECT bv.*, a.nom_article, a.categorie 
                FROM BNGRC_besoins_villes bv 
                JOIN BNGRC_articles a ON bv.id_article = a.id_article 
                WHERE bv.id_ville = :id_ville 
                ORDER BY bv.date_saisie DESC";
        return $this->db->fetchAll($sql, ['id_ville' => $id_ville]);
    }
    
    /**
     * Récupérer les besoins par article
     */
    public function getByArticle($id_article) {
        $sql = "SELECT bv.*, v.nom_ville, v.region 
                FROM BNGRC_besoins_villes bv 
                JOIN BNGRC_villes v ON bv.id_ville = v.id_ville 
                WHERE bv.id_article = :id_article 
                ORDER BY bv.date_saisie DESC";
        return $this->db->fetchAll($sql, ['id_article' => $id_article]);
    }
    
    /**
     * Ajouter un nouveau besoin
     * @param int $id_mode - 1 = origine, 2 = teste (par défaut: 1)
     * @param string|null $date_saisie - Date de saisie (optionnel, utilise NOW() par défaut)
     */
    public function create($id_ville, $id_article, $quantite_demandee, $id_mode = 1, $date_saisie = null) {
        $params = [
            'id_ville' => $id_ville,
            'id_article' => $id_article,
            'quantite_demandee' => $quantite_demandee,
            'id_mode' => $id_mode
        ];
        
        if ($date_saisie !== null && $date_saisie !== '') {
            $sql = "INSERT INTO BNGRC_besoins_villes (id_ville, id_article, quantite_demandee, id_mode, date_saisie) VALUES (:id_ville, :id_article, :quantite_demandee, :id_mode, :date_saisie)";
            $params['date_saisie'] = $date_saisie;
        } else {
            $sql = "INSERT INTO BNGRC_besoins_villes (id_ville, id_article, quantite_demandee, id_mode) VALUES (:id_ville, :id_article, :quantite_demandee, :id_mode)";
        }
        
        $this->db->runQuery($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Mettre à jour un besoin
     * @param int $id_mode - 1 = origine, 2 = teste
     */
    public function update($id_besoin, $id_ville, $id_article, $quantite_demandee, $date_saisie = null, $id_mode = null) {
        $params = [
            'id_besoin' => $id_besoin,
            'id_ville' => $id_ville,
            'id_article' => $id_article,
            'quantite_demandee' => $quantite_demandee
        ];
        
        $sql = "UPDATE BNGRC_besoins_villes SET id_ville = :id_ville, id_article = :id_article, quantite_demandee = :quantite_demandee";
        
        if ($date_saisie !== null) {
            $sql .= ", date_saisie = :date_saisie";
            $params['date_saisie'] = $date_saisie;
        }
        
        if ($id_mode !== null) {
            $sql .= ", id_mode = :id_mode";
            $params['id_mode'] = $id_mode;
        }
        
        $sql .= " WHERE id_besoin = :id_besoin";
        
        return $this->db->runQuery($sql, $params);
    }
    
    /**
     * Supprimer un besoin
     */
    public function delete($id_besoin) {
        $sql = "DELETE FROM BNGRC_besoins_villes WHERE id_besoin = :id_besoin";
        return $this->db->runQuery($sql, ['id_besoin' => $id_besoin]);
    }
    
    /**
     * Compter le nombre de besoins
     */
    public function count() {
        $sql = "SELECT COUNT(*) as total FROM BNGRC_besoins_villes";
        $result = $this->db->fetchRow($sql);
        return $result['total'];
    }
    
    /**
     * Récupérer les statistiques des besoins par ville
     */
    public function getStatsByVille() {
        $sql = "SELECT v.id_ville, v.nom_ville, v.region, COUNT(*) as nombre_besoins, SUM(bv.quantite_demandee) as quantite_totale
                FROM BNGRC_besoins_villes bv 
                JOIN BNGRC_villes v ON bv.id_ville = v.id_ville 
                GROUP BY v.id_ville, v.nom_ville, v.region 
                ORDER BY quantite_totale DESC";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Récupérer les statistiques des besoins par article
     */
    public function getStatsByArticle() {
        $sql = "SELECT a.id_article, a.nom_article, a.categorie, COUNT(*) as nombre_demandes, SUM(bv.quantite_demandee) as quantite_totale
                FROM BNGRC_besoins_villes bv 
                JOIN BNGRC_articles a ON bv.id_article = a.id_article 
                GROUP BY a.id_article, a.nom_article, a.categorie 
                ORDER BY quantite_totale DESC";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Vérifier si un besoin existe
     */
    public function exists($id_besoin) {
        $sql = "SELECT COUNT(*) as count FROM BNGRC_besoins_villes WHERE id_besoin = :id_besoin";
        $result = $this->db->fetchRow($sql, ['id_besoin' => $id_besoin]);
        return $result['count'] > 0;
    }
}
