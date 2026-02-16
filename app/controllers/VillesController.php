<?php
namespace app\controllers;
use app\models\Villes;
use Flight;

class VillesController {
    private $villesModel;
    
    public function __construct() {
        $this->villesModel = new Villes();
    }

    public function index() {
        try {
            $villes = $this->villesModel->getAll();
            Flight::json([
                'success' => true,
                'data' => $villes,
                'count' => count($villes)
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des villes: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id_ville) {
        try {
            $ville = $this->villesModel->getById($id_ville);
            if (!$ville) {
                Flight::json([
                    'success' => false,
                    'message' => 'Ville non trouvée'
                ], 404);
                return;
            }

            Flight::json([
                'success' => true,
                'data' => $ville
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la ville: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getAll() {
        return $this->villesModel->getAll();
    }
    
    public function getById($id_ville) {
        return $this->villesModel->getById($id_ville);
    }
    
    public function getByRegion($region) {
        try {
            $villes = $this->villesModel->getByRegion($region);
            Flight::json([
                'success' => true,
                'data' => $villes,
                'region' => $region,
                'count' => count($villes)
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des villes de la région: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function create() {
        try {
            $data = Flight::request()->data;
            
            // Validation
            if (empty($data['nom_ville']) || empty($data['region'])) {
                Flight::json([
                    'success' => false,
                    'message' => 'Le nom de la ville et la région sont obligatoires'
                ], 400);
                return;
            }
            
            $nom_ville = trim($data['nom_ville']);
            $region = trim($data['region']);
            
            $id_ville = $this->villesModel->create($nom_ville, $region);
            
            Flight::json([
                'success' => true,
                'message' => 'Ville créée avec succès',
                'data' => [
                    'id_ville' => $id_ville,
                    'nom_ville' => $nom_ville,
                    'region' => $region
                ]
            ], 201);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la création de la ville: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function update($id_ville) {
        try {
            $data = Flight::request()->data;
            
            // Vérifier si la ville existe
            $villeExistante = $this->villesModel->getById($id_ville);
            if (!$villeExistante) {
                Flight::json([
                    'success' => false,
                    'message' => 'Ville non trouvée'
                ], 404);
                return;
            }
            
            // Validation
            if (empty($data['nom_ville']) || empty($data['region'])) {
                Flight::json([
                    'success' => false,
                    'message' => 'Le nom de la ville et la région sont obligatoires'
                ], 400);
                return;
            }
            
            $nom_ville = trim($data['nom_ville']);
            $region = trim($data['region']);
            
            $this->villesModel->update($id_ville, $nom_ville, $region);
            
            Flight::json([
                'success' => true,
                'message' => 'Ville mise à jour avec succès',
                'data' => [
                    'id_ville' => $id_ville,
                    'nom_ville' => $nom_ville,
                    'region' => $region
                ]
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la ville: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function delete($id_ville) {
        try {
            // Vérifier si la ville existe
            $villeExistante = $this->villesModel->getById($id_ville);
            if (!$villeExistante) {
                Flight::json([
                    'success' => false,
                    'message' => 'Ville non trouvée'
                ], 404);
                return;
            }
            
            $this->villesModel->delete($id_ville);
            
            Flight::json([
                'success' => true,
                'message' => 'Ville supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la ville: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function stats() {
        try {
            $totalVilles = $this->villesModel->count();
            $regions = $this->villesModel->getAllRegions();
            
            $statsParRegion = [];
            foreach ($regions as $region) {
                $villesRegion = $this->villesModel->getByRegion($region['region']);
                $statsParRegion[] = [
                    'region' => $region['region'],
                    'nombre_villes' => count($villesRegion)
                ];
            }
            
            Flight::json([
                'success' => true,
                'data' => [
                    'total_villes' => $totalVilles,
                    'total_regions' => count($regions),
                    'regions' => $statsParRegion
                ]
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function regions() {
        try {
            $regions = $this->villesModel->getAllRegions();
            Flight::json([
                'success' => true,
                'data' => $regions,
                'count' => count($regions)
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des régions: ' . $e->getMessage()
            ], 500);
        }
    }

    public function objectifsDashboard() {
        try {
            $data = $this->villesModel->getObjectifsDashboard();
            Flight::json([
                'success' => true,
                'data' => $data,
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des objectifs du dashboard: ' . $e->getMessage()
            ], 500);
        }
    }
}