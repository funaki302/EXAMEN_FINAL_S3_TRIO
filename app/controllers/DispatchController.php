<?php
namespace app\controllers;

use app\models\Dispatch;
use Flight;

class DispatchController {
    private $dispatchModel;

    public function __construct() {
        $this->dispatchModel = new Dispatch();
    }

    private function readIdMode() {
        $data = Flight::request()->data;
        $idMode = isset($data['id_mode']) ? (int) $data['id_mode'] : 1;
        if ($idMode < 1 || $idMode > 2) {
            $idMode = 1;
        }
        return $idMode;
    }

    public function run() {
        try {
            $now = date('Y-m-d H:i:s');

            $sim = $this->dispatchModel->getSimulatedSummaryRows($now);
            $result = $sim['dispatch'] ?? [];

            Flight::json([
                'success' => true,
                'message' => 'Simulation du dispatch effectuée avec succès',
                'data' => [
                    'distributions_creees' => $result['distributions_creees'] ?? 0,
                    'quantite_attribuee_totale' => $result['quantite_attribuee_totale'] ?? 0,
                    'date_execution' => $now,
                    'summary_rows' => $sim['summary_rows'] ?? [],
                    'summary_count' => $sim['count'] ?? 0,
                ],
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors du dispatch: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function runProportionnel() {
        try {
            $now = date('Y-m-d H:i:s');

            $sim = $this->dispatchModel->getSimulatedSummaryRowsProportionnel($now);
            $result = $sim['dispatch'] ?? [];

            Flight::json([
                'success' => true,
                'message' => 'Simulation du dispatch (proportionnel) effectuée avec succès',
                'data' => [
                    'distributions_creees' => $result['distributions_creees'] ?? 0,
                    'quantite_attribuee_totale' => $result['quantite_attribuee_totale'] ?? 0,
                    'date_execution' => $now,
                    'summary_rows' => $sim['summary_rows'] ?? [],
                    'summary_count' => $sim['count'] ?? 0,
                ],
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors du dispatch (proportionnel): ' . $e->getMessage(),
            ], 500);
        }
    }

    public function runSmallestNeeds() {
        try {
            $now = date('Y-m-d H:i:s');

            $sim = $this->dispatchModel->getSimulatedSummaryRows($now, [], true);
            $result = $sim['dispatch'] ?? [];

            Flight::json([
                'success' => true,
                'message' => 'Simulation du dispatch (petits besoins d\'abord) effectuée avec succès',
                'data' => [
                    'distributions_creees' => $result['distributions_creees'] ?? 0,
                    'quantite_attribuee_totale' => $result['quantite_attribuee_totale'] ?? 0,
                    'date_execution' => $now,
                    'summary_rows' => $sim['summary_rows'] ?? [],
                    'summary_count' => $sim['count'] ?? 0,
                ],
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors du dispatch (petits besoins d\'abord): ' . $e->getMessage(),
            ], 500);
        }
    }

    public function validateSmallestNeeds() {
        try {
            $now = date('Y-m-d H:i:s');

            $idMode = $this->readIdMode();

            $result = $this->dispatchModel->runDispatch($now, true, [], true, $idMode);

            Flight::json([
                'success' => true,
                'message' => 'Dispatch (petits besoins d\'abord) validé avec succès',
                'data' => [
                    'distributions_creees' => $result['distributions_creees'] ?? 0,
                    'quantite_attribuee_totale' => $result['quantite_attribuee_totale'] ?? 0,
                    'date_execution' => $now,
                ],
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la validation dispatch (petits besoins d\'abord): ' . $e->getMessage(),
            ], 500);
        }
    }

    public function validateProportionnel() {
        try {
            $now = date('Y-m-d H:i:s');

            $idMode = $this->readIdMode();

            $result = $this->dispatchModel->runDispatchProportionnel($now, true, $idMode);

            Flight::json([
                'success' => true,
                'message' => 'Dispatch (proportionnel) validé avec succès',
                'data' => [
                    'distributions_creees' => $result['distributions_creees'] ?? 0,
                    'quantite_attribuee_totale' => $result['quantite_attribuee_totale'] ?? 0,
                    'date_execution' => $now,
                ],
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la validation dispatch (proportionnel): ' . $e->getMessage(),
            ], 500);
        }
    }

    public function validate() {
        try {
            $now = date('Y-m-d H:i:s');

            $idMode = $this->readIdMode();

            $result = $this->dispatchModel->runDispatch($now, true, [], false, $idMode);

            Flight::json([
                'success' => true,
                'message' => 'Dispatch validé avec succès',
                'data' => [
                    'distributions_creees' => $result['distributions_creees'] ?? 0,
                    'quantite_attribuee_totale' => $result['quantite_attribuee_totale'] ?? 0,
                    'date_execution' => $now,
                ],
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors de la validation dispatch: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function summary() {
        try {
            $out = $this->dispatchModel->getSummaryRows();

            Flight::json([
                'success' => true,
                'data' => $out,
                'count' => count($out),
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors du résumé dispatch: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function donsRestants() {
        try {
            $rows = $this->dispatchModel->getDonsRestantsParArticle();

            Flight::json([
                'success' => true,
                'data' => $rows,
                'count' => count($rows),
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors du chargement des dons restants: ' . $e->getMessage(),
            ], 500);
        }
    }
}
