<?php
namespace app\controllers;

use app\models\Distributions;
use app\models\DonsRecus;
use app\models\Villes;
use Flight;

class DistributionsController {
    private $distributionsModel;
    private $donsModel;
    private $villesModel;

    public function __construct() {
        $this->distributionsModel = new Distributions();
        $this->donsModel = new DonsRecus();
        $this->villesModel = new Villes();
    }

    public function index() {
        try {
            $distributions = $this->distributionsModel->getAll();
            Flight::json([
                'success' => true,
                'data' => $distributions,
                'count' => count($distributions)
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des distributions: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id_distribution) {
        try {
            $distribution = $this->distributionsModel->getById($id_distribution);
            if (!$distribution) {
                Flight::json([
                    'success' => false,
                    'message' => 'Distribution non trouvée'
                ], 404);
                return;
            }

            Flight::json([
                'success' => true,
                'data' => $distribution
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la distribution: ' . $e->getMessage()
            ], 500);
        }
    }

    public function create() {
        try {
            $data = Flight::request()->data;

            if (empty($data['id_don']) || empty($data['id_ville']) || empty($data['quantite_attribuee'])) {
                Flight::json([
                    'success' => false,
                    'message' => 'id_don, id_ville et quantite_attribuee sont obligatoires'
                ], 400);
                return;
            }

            $id_don = intval($data['id_don']);
            $id_ville = intval($data['id_ville']);
            $quantite_attribuee = floatval($data['quantite_attribuee']);

            if (!$this->donsModel->exists($id_don)) {
                Flight::json([
                    'success' => false,
                    'message' => 'Don non trouvé'
                ], 404);
                return;
            }

            if (!$this->villesModel->getById($id_ville)) {
                Flight::json([
                    'success' => false,
                    'message' => 'Ville non trouvée'
                ], 404);
                return;
            }

            if ($quantite_attribuee <= 0) {
                Flight::json([
                    'success' => false,
                    'message' => 'La quantite_attribuee doit être supérieure à 0'
                ], 400);
                return;
            }

            $date_attribution = $data['date_attribution'] ?? null;
            if ($date_attribution !== null && !strtotime($date_attribution)) {
                Flight::json([
                    'success' => false,
                    'message' => 'Format de date_attribution invalide'
                ], 400);
                return;
            }

            $id_distribution = $this->distributionsModel->create($id_don, $id_ville, $quantite_attribuee, $date_attribution);

            Flight::json([
                'success' => true,
                'message' => 'Distribution créée avec succès',
                'data' => [
                    'id_distribution' => $id_distribution,
                    'id_don' => $id_don,
                    'id_ville' => $id_ville,
                    'quantite_attribuee' => $quantite_attribuee,
                    'date_attribution' => $date_attribution
                ]
            ], 201);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la création de la distribution: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update($id_distribution) {
        try {
            $data = Flight::request()->data;

            if (!$this->distributionsModel->exists($id_distribution)) {
                Flight::json([
                    'success' => false,
                    'message' => 'Distribution non trouvée'
                ], 404);
                return;
            }

            if (empty($data['id_don']) || empty($data['id_ville']) || empty($data['quantite_attribuee']) || empty($data['date_attribution'])) {
                Flight::json([
                    'success' => false,
                    'message' => 'id_don, id_ville, quantite_attribuee et date_attribution sont obligatoires'
                ], 400);
                return;
            }

            $id_don = intval($data['id_don']);
            $id_ville = intval($data['id_ville']);
            $quantite_attribuee = floatval($data['quantite_attribuee']);
            $date_attribution = $data['date_attribution'];

            if (!$this->donsModel->exists($id_don)) {
                Flight::json([
                    'success' => false,
                    'message' => 'Don non trouvé'
                ], 404);
                return;
            }

            if (!$this->villesModel->getById($id_ville)) {
                Flight::json([
                    'success' => false,
                    'message' => 'Ville non trouvée'
                ], 404);
                return;
            }

            if ($quantite_attribuee <= 0) {
                Flight::json([
                    'success' => false,
                    'message' => 'La quantite_attribuee doit être supérieure à 0'
                ], 400);
                return;
            }

            if (!strtotime($date_attribution)) {
                Flight::json([
                    'success' => false,
                    'message' => 'Format de date_attribution invalide'
                ], 400);
                return;
            }

            $this->distributionsModel->update($id_distribution, $id_don, $id_ville, $quantite_attribuee, $date_attribution);

            Flight::json([
                'success' => true,
                'message' => 'Distribution mise à jour avec succès',
                'data' => [
                    'id_distribution' => $id_distribution,
                    'id_don' => $id_don,
                    'id_ville' => $id_ville,
                    'quantite_attribuee' => $quantite_attribuee,
                    'date_attribution' => $date_attribution
                ]
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la distribution: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete($id_distribution) {
        try {
            if (!$this->distributionsModel->exists($id_distribution)) {
                Flight::json([
                    'success' => false,
                    'message' => 'Distribution non trouvée'
                ], 404);
                return;
            }

            $this->distributionsModel->delete($id_distribution);

            Flight::json([
                'success' => true,
                'message' => 'Distribution supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la distribution: ' . $e->getMessage()
            ], 500);
        }
    }

    public function count() {
        try {
            $total = $this->distributionsModel->count();
            Flight::json([
                'success' => true,
                'data' => [
                    'total_distributions' => $total
                ]
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors du comptage des distributions: ' . $e->getMessage()
            ], 500);
        }
    }
}
