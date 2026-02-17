<?php
namespace app\models;

use Flight;
use PDO;
use PDOException;

class Modes {
    private $db;
    
    public function __construct() {
        $this->db = Flight::db();
    }
    
    /**
     * Récupérer tous les modes
     */
    public function getAll() {
        $sql = "SELECT * FROM BNGRC_modes ORDER BY id_mode ASC";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Récupérer un mode par son ID
     */
    public function getById($id_mode) {
        $sql = "SELECT * FROM BNGRC_modes WHERE id_mode = :id_mode";
        return $this->db->fetchRow($sql, ['id_mode' => $id_mode]);
    }
    
    /**
     * Récupérer un mode par son nom
     */
    public function getByNom($nom_mode) {
        $sql = "SELECT * FROM BNGRC_modes WHERE nom_mode = :nom_mode";
        return $this->db->fetchRow($sql, ['nom_mode' => $nom_mode]);
    }
    
    /**
     * Vérifier si un mode existe
     */
    public function exists($id_mode) {
        $sql = "SELECT COUNT(*) as total FROM BNGRC_modes WHERE id_mode = :id_mode";
        $result = $this->db->fetchRow($sql, ['id_mode' => $id_mode]);
        return $result['total'] > 0;
    }
    
    /**
     * Récupérer l'ID du mode 'teste'
     */
    public function getIdModeTeste() {
        $mode = $this->getByNom('teste');
        return $mode ? $mode['id_mode'] : null;
    }
    
    /**
     * Récupérer l'ID du mode 'origine'
     */
    public function getIdModeOrigine() {
        $mode = $this->getByNom('origine');
        return $mode ? $mode['id_mode'] : null;
    }
    
    /**
     * Récupérer les statistiques par mode
     */
    public function getStatsByMode() {
        $sql = "SELECT * FROM BNGRC_V_Stats_Par_Mode";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Réinitialiser les données de test en appelant la procédure stockée
     */
    public function reinitialiserDonneesTest() {
        try {
            $sql = "CALL sp_reinitialiser_donnees_test()";
            $this->db->runQuery($sql);
            return [
                'success' => true,
                'message' => 'Réinitialisation des données de test effectuée avec succès'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la réinitialisation: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Réinitialiser les données de test manuellement (si procédure non disponible)
     */
    public function reinitialiserDonneesTestManuel() {
        try {
            $idModeTeste = $this->getIdModeTeste();
            
            if (!$idModeTeste) {
                return [
                    'success' => false,
                    'message' => 'Mode "teste" non trouvé'
                ];
            }
            
            // Désactiver temporairement les vérifications de clés étrangères
            $this->db->runQuery("SET FOREIGN_KEY_CHECKS = 0");
            
            try {
                // Supprimer dans l'ordre pour respecter les FK (même avec FK désactivées, c'est plus propre)
                // 1. Distributions de test
                $this->db->runQuery("DELETE FROM BNGRC_distributions WHERE id_mode = :id_mode", ['id_mode' => $idModeTeste]);
                
                // 2. Transactions argent de test
                $this->db->runQuery("DELETE FROM BNGRC_transactions_argent WHERE id_mode = :id_mode", ['id_mode' => $idModeTeste]);
                
                // 3. Achats de test
                $this->db->runQuery("DELETE FROM BNGRC_achats WHERE id_mode = :id_mode", ['id_mode' => $idModeTeste]);
                
                // 4. Dons reçus de test
                $this->db->runQuery("DELETE FROM BNGRC_dons_recus WHERE id_mode = :id_mode", ['id_mode' => $idModeTeste]);
                
                // 5. Besoins villes de test
                $this->db->runQuery("DELETE FROM BNGRC_besoins_villes WHERE id_mode = :id_mode", ['id_mode' => $idModeTeste]);
                
            } finally {
                // Réactiver les vérifications de clés étrangères
                $this->db->runQuery("SET FOREIGN_KEY_CHECKS = 1");
            }
            
            return [
                'success' => true,
                'message' => 'Réinitialisation des données de test effectuée avec succès'
            ];
        } catch (\Exception $e) {
            // S'assurer de réactiver les FK en cas d'erreur
            try {
                $this->db->runQuery("SET FOREIGN_KEY_CHECKS = 1");
            } catch (\Exception $ignored) {}
            
            return [
                'success' => false,
                'message' => 'Erreur lors de la réinitialisation: ' . $e->getMessage()
            ];
        }
    }
}
