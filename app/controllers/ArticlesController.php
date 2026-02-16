<?php
namespace app\controllers;

use app\models\Articles;
use Flight;

class ArticlesController {
    private $articlesModel;
    
    public function __construct() {
        $this->articlesModel = new Articles();
    }

    public function index() {
        try {
            $articles = $this->articlesModel->getAll();
            Flight::json([
                'success' => true,
                'data' => $articles,
                'count' => count($articles)
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des articles: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id_article) {
        try {
            $article = $this->articlesModel->getById($id_article);
            if (!$article) {
                Flight::json([
                    'success' => false,
                    'message' => 'Article non trouvé'
                ], 404);
                return;
            }

            Flight::json([
                'success' => true,
                'data' => $article
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'article: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getAll() {
        return $this->articlesModel->getAll();
    }
    
    public function getById($id_article) {
        return $this->articlesModel->getById($id_article);
    }
    
    public function getByCategorie($categorie) {
        return $this->articlesModel->getByCategorie($categorie);
    }
    
    public function create() {
        try {
            $data = Flight::request()->data;
            
            if (empty($data['nom_article']) || empty($data['categorie'])) {
                Flight::json([
                    'success' => false,
                    'message' => 'Le nom de l\'article et la catégorie sont obligatoires'
                ], 400);
                return;
            }
            
            $nom_article = trim($data['nom_article']);
            $categorie = $data['categorie'];
            $prix_unitaire = $data['prix_unitaire'] ?? 0;
            
            if (!in_array($categorie, ['Nature', 'Matériaux', 'Argent'])) {
                Flight::json([
                    'success' => false,
                    'message' => 'La catégorie doit être l\'une des valeurs suivantes: Nature, Matériaux, Argent'
                ], 400);
                return;
            }
            
            $id_article = $this->articlesModel->create($nom_article, $categorie, $prix_unitaire);
            
            Flight::json([
                'success' => true,
                'message' => 'Article créé avec succès',
                'data' => [
                    'id_article' => $id_article,
                    'nom_article' => $nom_article,
                    'categorie' => $categorie,
                    'prix_unitaire' => $prix_unitaire
                ]
            ], 201);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'article: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function update($id_article) {
        try {
            $data = Flight::request()->data;
            
            if (!$this->articlesModel->exists($id_article)) {
                Flight::json([
                    'success' => false,
                    'message' => 'Article non trouvé'
                ], 404);
                return;
            }
            
            if (empty($data['nom_article']) || empty($data['categorie'])) {
                Flight::json([
                    'success' => false,
                    'message' => 'Le nom de l\'article et la catégorie sont obligatoires'
                ], 400);
                return;
            }
            
            $nom_article = trim($data['nom_article']);
            $categorie = $data['categorie'];
            $prix_unitaire = $data['prix_unitaire'] ?? 0;
            
            if (!in_array($categorie, ['Nature', 'Matériaux', 'Argent'])) {
                Flight::json([
                    'success' => false,
                    'message' => 'La catégorie doit être l\'une des valeurs suivantes: Nature, Matériaux, Argent'
                ], 400);
                return;
            }
            
            $this->articlesModel->update($id_article, $nom_article, $categorie, $prix_unitaire);
            
            Flight::json([
                'success' => true,
                'message' => 'Article mis à jour avec succès',
                'data' => [
                    'id_article' => $id_article,
                    'nom_article' => $nom_article,
                    'categorie' => $categorie,
                    'prix_unitaire' => $prix_unitaire
                ]
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'article: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function delete($id_article) {
        try {
            if (!$this->articlesModel->exists($id_article)) {
                Flight::json([
                    'success' => false,
                    'message' => 'Article non trouvé'
                ], 404);
                return;
            }
            
            $this->articlesModel->delete($id_article);
            
            Flight::json([
                'success' => true,
                'message' => 'Article supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'article: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function categories() {
        try {
            $categories = $this->articlesModel->getAllCategories();
            Flight::json([
                'success' => true,
                'data' => $categories,
                'count' => count($categories)
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des catégories: ' . $e->getMessage()
            ], 500);
        }
    }
}
