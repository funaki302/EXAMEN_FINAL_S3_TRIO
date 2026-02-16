<?php
namespace app\controllers;

use app\models\BesoinsVilles;
use app\models\Villes;
use app\models\Articles;
use Flight;

class BesoinsVillesController {
    private $besoinsModel;
    private $villesModel;
    private $articlesModel;
    
    public function __construct() {
        $this->besoinsModel = new BesoinsVilles();
        $this->villesModel = new Villes();
        $this->articlesModel = new Articles();
    }
    
    public function getAll() {
        return $this->besoinsModel->getAll();
    }
    
    public function getById($id_besoin) {
        return $this->besoinsModel->getById($id_besoin);
    }
    
    public function getByVille($id_ville) {
        return $this->villesModel->getById($id_ville);
    }
    
    public function getByArticle($id_article) {
        try {
            if (!$this->articlesModel->exists($id_article)) {
                Flight::json([
                    'success' => false,
                    'message' => 'Article non trouvé'
                ], 404);
                return;
            }
            
            $besoins = $this->besoinsModel->getByArticle($id_article);
            Flight::json([
                'success' => true,
                'data' => $besoins,
                'id_article' => $id_article,
                'count' => count($besoins)
            ]);
        } catch (Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des besoins par article: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function create() {
        try {
            $body = Flight::request()->getBody();
            $data = json_decode($body, true);

            if (!$data) {
                $data = Flight::request()->data->getData();
            }
            
            if (empty($data['id_ville']) || empty($data['id_article']) || empty($data['quantite_demandee'])) {
                Flight::json([
                    'success' => false,
                    'message' => 'L\'ID de la ville, l\'ID de l\'article et la quantité demandée sont obligatoires'
                ], 400);
                return;
            }
            
            if (!$this->villesModel->getById($data['id_ville'])) {
                Flight::json([
                    'success' => false,
                    'message' => 'Ville non trouvée'
                ], 404);
                return;
            }
            
            if (!$this->articlesModel->exists($data['id_article'])) {
                Flight::json([
                    'success' => false,
                    'message' => 'Article non trouvé'
                ], 404);
                return;
            }
            
            $quantite_demandee = floatval($data['quantite_demandee']);
            if ($quantite_demandee <= 0) {
                Flight::json([
                    'success' => false,
                    'message' => 'La quantité demandée doit être supérieure à 0'
                ], 400);
                return;
            }
            
            $id_besoin = $this->besoinsModel->create($data['id_ville'], $data['id_article'], $quantite_demandee);
            
            Flight::json([
                'success' => true,
                'message' => 'Besoin créé avec succès',
                'data' => [
                    'id_besoin' => $id_besoin,
                    'id_ville' => $data['id_ville'],
                    'id_article' => $data['id_article'],
                    'quantite_demandee' => $quantite_demandee
                ]
            ], 201);
        } catch (Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la création du besoin: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function update($id_besoin) {
        try {
            $data = Flight::request()->data;
            
            if (!$this->besoinsModel->exists($id_besoin)) {
                Flight::json([
                    'success' => false,
                    'message' => 'Besoin non trouvé'
                ], 404);
                return;
            }
            
            if (empty($data['id_ville']) || empty($data['id_article']) || empty($data['quantite_demandee'])) {
                Flight::json([
                    'success' => false,
                    'message' => 'L\'ID de la ville, l\'ID de l\'article et la quantité demandée sont obligatoires'
                ], 400);
                return;
            }
            
            if (!$this->villesModel->getById($data['id_ville'])) {
                Flight::json([
                    'success' => false,
                    'message' => 'Ville non trouvée'
                ], 404);
                return;
            }
            
            if (!$this->articlesModel->exists($data['id_article'])) {
                Flight::json([
                    'success' => false,
                    'message' => 'Article non trouvé'
                ], 404);
                return;
            }
            
            $quantite_demandee = floatval($data['quantite_demandee']);
            if ($quantite_demandee <= 0) {
                Flight::json([
                    'success' => false,
                    'message' => 'La quantité demandée doit être supérieure à 0'
                ], 400);
                return;
            }
            
            $this->besoinsModel->update($id_besoin, $data['id_ville'], $data['id_article'], $quantite_demandee);
            
            Flight::json([
                'success' => true,
                'message' => 'Besoin mis à jour avec succès',
                'data' => [
                    'id_besoin' => $id_besoin,
                    'id_ville' => $data['id_ville'],
                    'id_article' => $data['id_article'],
                    'quantite_demandee' => $quantite_demandee
                ]
            ]);
        } catch (Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du besoin: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function delete($id_besoin) {
        try {
            if (!$this->besoinsModel->exists($id_besoin)) {
                Flight::json([
                    'success' => false,
                    'message' => 'Besoin non trouvé'
                ], 404);
                return;
            }
            
            $this->besoinsModel->delete($id_besoin);
            
            Flight::json([
                'success' => true,
                'message' => 'Besoin supprimé avec succès'
            ]);
        } catch (Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du besoin: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function statsByVille() {
        try {
            $stats = $this->besoinsModel->getStatsByVille();
            Flight::json([
                'success' => true,
                'data' => $stats,
                'count' => count($stats)
            ]);
        } catch (Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques par ville: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function statsByArticle() {
        try {
            $stats = $this->besoinsModel->getStatsByArticle();
            Flight::json([
                'success' => true,
                'data' => $stats,
                'count' => count($stats)
            ]);
        } catch (Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques par article: ' . $e->getMessage()
            ], 500);
        }
    }
}
