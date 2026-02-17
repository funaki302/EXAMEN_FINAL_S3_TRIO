<?php
namespace app\models;

use Flight;

class TransactionsArgent {
    private $db;

    public function __construct() {
        $this->db = Flight::db();
    }

    public function createEntreeDon($idDon, $montant, $dateTransaction, $idMode = 1) {
        $sql = "INSERT INTO BNGRC_transactions_argent (type_transaction, montant, id_don, id_achat, date_transaction, id_mode)
                VALUES ('ENTREE_DON', :montant, :id_don, NULL, :date_transaction, :id_mode)";

        $this->db->runQuery($sql, [
            'montant' => $montant,
            'id_don' => $idDon,
            'date_transaction' => $dateTransaction,
            'id_mode' => $idMode,
        ]);

        return $this->db->lastInsertId();
    }

    public function createSortieAchat($idAchat, $montant, $dateTransaction, $idMode = 1) {
        $sql = "INSERT INTO BNGRC_transactions_argent (type_transaction, montant, id_don, id_achat, date_transaction, id_mode)
                VALUES ('SORTIE_ACHAT', :montant, NULL, :id_achat, :date_transaction, :id_mode)";

        $this->db->runQuery($sql, [
            'montant' => $montant,
            'id_achat' => $idAchat,
            'date_transaction' => $dateTransaction,
            'id_mode' => $idMode,
        ]);

        return $this->db->lastInsertId();
    }

    public function getTotalEntrees() {
        $sql = "SELECT IFNULL(SUM(t.montant), 0) AS total_entrees
                FROM BNGRC_transactions_argent t
                WHERE t.type_transaction = 'ENTREE_DON'";

        $row = $this->db->fetchRow($sql);
        return (float) ($row['total_entrees'] ?? 0);
    }

    public function getTotalSorties() {
        $sql = "SELECT IFNULL(SUM(t.montant), 0) AS total_sorties
                FROM BNGRC_transactions_argent t
                WHERE t.type_transaction = 'SORTIE_ACHAT'";

        $row = $this->db->fetchRow($sql);
        return (float) ($row['total_sorties'] ?? 0);
    }

    public function getSolde() {
        return $this->getTotalEntrees() - $this->getTotalSorties();
    }
}
