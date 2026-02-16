<?php
namespace app\controllers;

use app\models\DonsRecus;
use app\models\Articles;
use app\models\TransactionsArgent;
use Flight;

class DonsRecusController {
    private $donsModel;
    private $articlesModel;
    private $transactionsModel;
    
    public function __construct() {
        $this->donsModel = new DonsRecus();
        $this->articlesModel = new Articles();
        $this->transactionsModel = new TransactionsArgent();
    }
    
    public function getAll() {
        return $this->donsModel->getAll();
    }
    
    public function getById($id_don) {
        return $this->donsModel->getById($id_don);
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
            
            $dons = $this->donsModel->getByArticle($id_article);
            Flight::json([
                'success' => true,
                'data' => $dons,
                'id_article' => $id_article,
                'count' => count($dons)
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des dons par article: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getByDate($date) {
        try {
            $dons = $this->donsModel->getByDate($date);
            Flight::json([
                'success' => true,
                'data' => $dons,
                'date' => $date,
                'count' => count($dons)
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des dons par date: ' . $e->getMessage()
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
            
            if (empty($data['id_article']) || empty($data['quantite_donnee']) || empty($data['date_reception'])) {
                Flight::json([
                    'success' => false,
                    'message' => 'L\'ID de l\'article, la quantité donnée et la date de réception sont obligatoires'
                ], 400);
                return;
            }
            
            if (!$this->articlesModel->exists($data['id_article'])) {
                Flight::json([
                    'success' => false,
                    'message' => 'Article non trouvé'
                ], 404);
                return;
            }
            
            $quantite_donnee = floatval($data['quantite_donnee']);
            if ($quantite_donnee <= 0) {
                Flight::json([
                    'success' => false,
                    'message' => 'La quantité donnée doit être supérieure à 0'
                ], 400);
                return;
            }
            
            $date_reception = $data['date_reception'];
            if (!strtotime($date_reception)) {
                Flight::json([
                    'success' => false,
                    'message' => 'Format de date invalide'
                ], 400);
                return;
            }
            
            $id_don = $this->donsModel->create($data['id_article'], $quantite_donnee, $date_reception);

            $article = $this->articlesModel->getById($data['id_article']);
            if (($article['categorie'] ?? '') === 'Argent') {
                $this->transactionsModel->createEntreeDon($id_don, $quantite_donnee, $date_reception);
            }
            
            Flight::json([
                'success' => true,
                'message' => 'Don créé avec succès',
                'data' => [
                    'id_don' => $id_don,
                    'id_article' => $data['id_article'],
                    'quantite_donnee' => $quantite_donnee,
                    'date_reception' => $date_reception
                ]
            ], 201);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la création du don: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function update($id_don) {
        try {
            $data = Flight::request()->data;
            
            if (!$this->donsModel->exists($id_don)) {
                Flight::json([
                    'success' => false,
                    'message' => 'Don non trouvé'
                ], 404);
                return;
            }
            
            if (empty($data['id_article']) || empty($data['quantite_donnee']) || empty($data['date_reception'])) {
                Flight::json([
                    'success' => false,
                    'message' => 'L\'ID de l\'article, la quantité donnée et la date de réception sont obligatoires'
                ], 400);
                return;
            }
            
            if (!$this->articlesModel->exists($data['id_article'])) {
                Flight::json([
                    'success' => false,
                    'message' => 'Article non trouvé'
                ], 404);
                return;
            }
            
            $quantite_donnee = floatval($data['quantite_donnee']);
            if ($quantite_donnee <= 0) {
                Flight::json([
                    'success' => false,
                    'message' => 'La quantité donnée doit être supérieure à 0'
                ], 400);
                return;
            }
            
            $date_reception = $data['date_reception'];
            if (!strtotime($date_reception)) {
                Flight::json([
                    'success' => false,
                    'message' => 'Format de date invalide'
                ], 400);
                return;
            }
            
            $this->donsModel->update($id_don, $data['id_article'], $quantite_donnee, $date_reception);
            
            Flight::json([
                'success' => true,
                'message' => 'Don mis à jour avec succès',
                'data' => [
                    'id_don' => $id_don,
                    'id_article' => $data['id_article'],
                    'quantite_donnee' => $quantite_donnee,
                    'date_reception' => $date_reception
                ]
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du don: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function delete($id_don) {
        try {
            if (!$this->donsModel->exists($id_don)) {
                Flight::json([
                    'success' => false,
                    'message' => 'Don non trouvé'
                ], 404);
                return;
            }
            
            $this->donsModel->delete($id_don);
            
            Flight::json([
                'success' => true,
                'message' => 'Don supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du don: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function statsByArticle() {
        try {
            $stats = $this->donsModel->getStatsByArticle();
            Flight::json([
                'success' => true,
                'data' => $stats,
                'count' => count($stats)
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques par article: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function statsByCategorie() {
        try {
            $stats = $this->donsModel->getStatsByCategorie();
            Flight::json([
                'success' => true,
                'data' => $stats,
                'count' => count($stats)
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques par catégorie: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getByPeriod() {
        try {
            $data = Flight::request()->query;
            
            if (empty($data['date_debut']) || empty($data['date_fin'])) {
                Flight::json([
                    'success' => false,
                    'message' => 'Les dates de début et de fin sont obligatoires'
                ], 400);
                return;
            }
            
            $date_debut = $data['date_debut'];
            $date_fin = $data['date_fin'];
            
            if (!strtotime($date_debut) || !strtotime($date_fin)) {
                Flight::json([
                    'success' => false,
                    'message' => 'Format de date invalide'
                ], 400);
                return;
            }
            
            $dons = $this->donsModel->getByPeriod($date_debut, $date_fin);
            
            Flight::json([
                'success' => true,
                'data' => $dons,
                'date_debut' => $date_debut,
                'date_fin' => $date_fin,
                'count' => count($dons)
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des dons par période: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function valeurTotale() {
        try {
            $valeur = $this->donsModel->getValeurTotale();
            Flight::json([
                'success' => true,
                'data' => [
                    'valeur_totale' => $valeur
                ]
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors du calcul de la valeur totale: ' . $e->getMessage()
            ], 500);
        }
    }
}
