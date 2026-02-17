<?php
namespace app\controllers;

use app\models\Modes;
use Flight;

class ModesController {
    private $modesModel;
    
    public function __construct() {
        $this->modesModel = new Modes();
    }
    
    /**
     * Récupérer tous les modes
     */
    public function getAll() {
        try {
            $modes = $this->modesModel->getAll();
            Flight::json([
                'success' => true,
                'data' => $modes,
                'count' => count($modes)
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des modes: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Récupérer un mode par son ID
     */
    public function getById($id_mode) {
        try {
            $mode = $this->modesModel->getById($id_mode);
            
            if (!$mode) {
                Flight::json([
                    'success' => false,
                    'message' => 'Mode non trouvé'
                ], 404);
                return;
            }
            
            Flight::json([
                'success' => true,
                'data' => $mode
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du mode: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Récupérer les statistiques par mode
     */
    public function getStats() {
        try {
            $stats = $this->modesModel->getStatsByMode();
            Flight::json([
                'success' => true,
                'data' => $stats,
                'count' => count($stats)
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Réinitialiser les données de test
     */
    public function reinitialiser() {
        try {
            // Essayer d'abord avec la procédure stockée
            $result = $this->modesModel->reinitialiserDonneesTestManuel();
            
            if ($result['success']) {
                // Récupérer les stats après réinitialisation
                $stats = $this->modesModel->getStatsByMode();
                
                Flight::json([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => [
                        'stats_apres_reinitialisation' => $stats
                    ]
                ]);
            } else {
                Flight::json([
                    'success' => false,
                    'message' => $result['message']
                ], 500);
            }
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la réinitialisation: ' . $e->getMessage()
            ], 500);
        }
    }
}
