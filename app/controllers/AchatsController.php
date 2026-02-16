<?php
namespace app\controllers;

use app\models\Achats;
use app\models\Articles;
use app\models\DonsRecus;
use app\models\Dispatch;
use app\models\Distributions;
use app\models\TransactionsArgent;
use Flight;

class AchatsController {
    private $achatsModel;
    private $articlesModel;
    private $donsModel;
    private $dispatchModel;
    private $distributionsModel;
    private $transactionsModel;

    public function __construct() {
        $this->achatsModel = new Achats();
        $this->articlesModel = new Articles();
        $this->donsModel = new DonsRecus();
        $this->dispatchModel = new Dispatch();
        $this->distributionsModel = new Distributions();
        $this->transactionsModel = new TransactionsArgent();
    }

    private function readJsonOrForm() {
        $body = Flight::request()->getBody();
        $data = json_decode($body, true);
        if (!$data) {
            $data = Flight::request()->data->getData();
        }
        return $data;
    }

    public function solde() {
        try {
            $solde = $this->transactionsModel->getSolde();
            Flight::json([
                'success' => true,
                'data' => [
                    'solde' => $solde,
                ],
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du solde: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function besoinsRestants() {
        try {
            $idVille = Flight::request()->query['id_ville'] ?? null;
            $idVille = $idVille !== null && $idVille !== '' ? (int) $idVille : null;

            $rows = $this->achatsModel->getBesoinsRestants($idVille);
            Flight::json([
                'success' => true,
                'data' => $rows,
                'count' => count($rows),
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors du chargement des besoins restants: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function simulate() {
        try {
            $data = $this->readJsonOrForm();

            if (empty($data['id_ville']) || empty($data['id_article']) || empty($data['quantite_achetee']) || !isset($data['taux_frais_pourcent'])) {
                Flight::json([
                    'success' => false,
                    'message' => 'id_ville, id_article, quantite_achetee et taux_frais_pourcent sont obligatoires'
                ], 400);
                return;
            }

            $idVille = (int) $data['id_ville'];
            $idArticle = (int) $data['id_article'];
            $quantiteAchetee = (float) $data['quantite_achetee'];
            $tauxFraisPourcent = (float) $data['taux_frais_pourcent'];

            if ($quantiteAchetee <= 0) {
                Flight::json(['success' => false, 'message' => 'La quantité achetée doit être > 0'], 400);
                return;
            }

            if (!$this->articlesModel->exists($idArticle)) {
                Flight::json(['success' => false, 'message' => 'Article non trouvé'], 404);
                return;
            }

            $article = $this->articlesModel->getById($idArticle);
            if (($article['categorie'] ?? '') === 'Argent') {
                Flight::json(['success' => false, 'message' => 'Impossible d\'acheter un article de catégorie Argent'], 400);
                return;
            }

            $resteBesoin = $this->achatsModel->getResteBesoinVilleArticle($idVille, $idArticle);
            if ($resteBesoin <= 0) {
                Flight::json(['success' => false, 'message' => 'Aucun besoin restant pour cet article dans cette ville'], 400);
                return;
            }

            if ($quantiteAchetee > $resteBesoin) {
                Flight::json(['success' => false, 'message' => 'La quantité achetée dépasse le besoin restant'], 400);
                return;
            }

            if ($this->achatsModel->hasDonsRestantsPourArticle($idArticle)) {
                Flight::json(['success' => false, 'message' => 'Il existe déjà des dons restants pour cet article. Veuillez dispatcher avant achat.'], 400);
                return;
            }

            $montants = $this->achatsModel->computeMontants($idArticle, $quantiteAchetee, $tauxFraisPourcent);
            $solde = $this->transactionsModel->getSolde();

            if ($montants['montant_ttc'] > $solde) {
                Flight::json(['success' => false, 'message' => 'Solde insuffisant pour effectuer cet achat'], 400);
                return;
            }

            $now = date('Y-m-d H:i:s');
            $dispatchSim = $this->achatsModel->simulateDispatchApresAchat($idVille, $idArticle, $quantiteAchetee, $now);

            Flight::json([
                'success' => true,
                'message' => 'Simulation achat + dispatch effectuée',
                'data' => [
                    'article' => [
                        'id_article' => (int) $idArticle,
                        'nom_article' => $article['nom_article'] ?? null,
                        'categorie' => $article['categorie'] ?? null,
                    ],
                    'besoin_restant' => $resteBesoin,
                    'achat' => [
                        'id_ville' => $idVille,
                        'id_article' => $idArticle,
                        'quantite_achetee' => $quantiteAchetee,
                        'taux_frais_pourcent' => $tauxFraisPourcent,
                        'montants' => $montants,
                    ],
                    'argent' => [
                        'solde_actuel' => $solde,
                        'solde_apres_achat' => $solde - $montants['montant_ttc'],
                    ],
                    'dispatch' => $dispatchSim,
                ]
            ]);
        } catch (\Exception $e) {
            Flight::json(['success' => false, 'message' => 'Erreur lors de la simulation achat: ' . $e->getMessage()], 500);
        }
    }

    public function validate() {
        try {
            $data = $this->readJsonOrForm();

            if (empty($data['id_ville']) || empty($data['id_article']) || empty($data['quantite_achetee']) || !isset($data['taux_frais_pourcent'])) {
                Flight::json([
                    'success' => false,
                    'message' => 'id_ville, id_article, quantite_achetee et taux_frais_pourcent sont obligatoires'
                ], 400);
                return;
            }

            $idVille = (int) $data['id_ville'];
            $idArticle = (int) $data['id_article'];
            $quantiteAchetee = (float) $data['quantite_achetee'];
            $tauxFraisPourcent = (float) $data['taux_frais_pourcent'];

            if ($quantiteAchetee <= 0) {
                Flight::json(['success' => false, 'message' => 'La quantité achetée doit être > 0'], 400);
                return;
            }

            if (!$this->articlesModel->exists($idArticle)) {
                Flight::json(['success' => false, 'message' => 'Article non trouvé'], 404);
                return;
            }

            $article = $this->articlesModel->getById($idArticle);
            if (($article['categorie'] ?? '') === 'Argent') {
                Flight::json(['success' => false, 'message' => 'Impossible d\'acheter un article de catégorie Argent'], 400);
                return;
            }

            $resteBesoin = $this->achatsModel->getResteBesoinVilleArticle($idVille, $idArticle);
            if ($resteBesoin <= 0) {
                Flight::json(['success' => false, 'message' => 'Aucun besoin restant pour cet article dans cette ville'], 400);
                return;
            }

            if ($quantiteAchetee > $resteBesoin) {
                Flight::json(['success' => false, 'message' => 'La quantité achetée dépasse le besoin restant'], 400);
                return;
            }

            if ($this->achatsModel->hasDonsRestantsPourArticle($idArticle)) {
                Flight::json(['success' => false, 'message' => 'Il existe déjà des dons restants pour cet article. Veuillez dispatcher avant achat.'], 400);
                return;
            }

            $montants = $this->achatsModel->computeMontants($idArticle, $quantiteAchetee, $tauxFraisPourcent);
            $solde = $this->transactionsModel->getSolde();

            if ($montants['montant_ttc'] > $solde) {
                Flight::json(['success' => false, 'message' => 'Solde insuffisant pour effectuer cet achat'], 400);
                return;
            }

            $now = date('Y-m-d H:i:s');

            $achatCreate = $this->achatsModel->create($idVille, $idArticle, $quantiteAchetee, $tauxFraisPourcent, $now);
            $idAchat = (int) $achatCreate['id_achat'];

            $this->transactionsModel->createSortieAchat($idAchat, $achatCreate['montants']['montant_ttc'], $now);

            $idDon = $this->donsModel->create($idArticle, $quantiteAchetee, $now);

            $this->distributionsModel->create((int) $idDon, (int) $idVille, (float) $quantiteAchetee, $now);

            $dispatchResult = [
                'distributions_creees' => 1,
                'quantite_attribuee_totale' => (float) $quantiteAchetee,
                'persisted' => true,
                'target' => [
                    'id_ville' => (int) $idVille,
                    'id_article' => (int) $idArticle,
                    'date_attribution' => $now,
                ],
            ];

            Flight::json([
                'success' => true,
                'message' => 'Achat validé + don créé + dispatch effectué',
                'data' => [
                    'achat' => [
                        'id_achat' => $idAchat,
                        'id_ville' => $idVille,
                        'id_article' => $idArticle,
                        'quantite_achetee' => $quantiteAchetee,
                        'taux_frais_pourcent' => $tauxFraisPourcent,
                        'montants' => $achatCreate['montants'],
                    ],
                    'don_cree' => [
                        'id_don' => (int) $idDon,
                        'id_article' => $idArticle,
                        'quantite_donnee' => $quantiteAchetee,
                        'date_reception' => $now,
                    ],
                    'dispatch' => $dispatchResult,
                ]
            ], 201);
        } catch (\Exception $e) {
            Flight::json(['success' => false, 'message' => 'Erreur lors de la validation achat: ' . $e->getMessage()], 500);
        }
    }
}
