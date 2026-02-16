<?php
namespace app\controllers;

use app\models\Dispatch;
use Flight;

class DispatchController {
    private $dispatchModel;

    public function __construct() {
        $this->dispatchModel = new Dispatch();
    }

    public function run() {
        try {
            $now = date('Y-m-d H:i:s');

            $result = $this->dispatchModel->runDispatch($now, false);

            Flight::json([
                'success' => true,
                'message' => 'Simulation du dispatch effectuée avec succès',
                'data' => [
                    'distributions_creees' => $result['distributions_creees'] ?? 0,
                    'quantite_attribuee_totale' => $result['quantite_attribuee_totale'] ?? 0,
                    'date_execution' => $now,
                ],
            ]);
        } catch (\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => 'Erreur lors du dispatch: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function validate() {
        try {
            $now = date('Y-m-d H:i:s');

            $result = $this->dispatchModel->runDispatch($now, true);

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
}
