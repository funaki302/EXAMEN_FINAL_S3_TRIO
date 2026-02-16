<?php
namespace app\models;

use Flight;

class Dispatch {
    private $db;
    private $distributionsModel;

    public function __construct() {
        $this->db = Flight::db();
        $this->distributionsModel = new Distributions();
    }

    public function runDispatch($dateAttribution, $persist = true, $extraDons = []) {
        $dons = $this->getDonsAvecReste();

        if (is_array($extraDons) && count($extraDons) > 0) {
            $dons = array_merge($dons, $extraDons);

            usort($dons, function($a, $b) {
                $da = strtotime((string) ($a['date_reception'] ?? '1970-01-01 00:00:00'));
                $db = strtotime((string) ($b['date_reception'] ?? '1970-01-01 00:00:00'));
                if ($da === $db) {
                    return ((int) ($a['id_don'] ?? 0)) <=> ((int) ($b['id_don'] ?? 0));
                }
                return $da <=> $db;
            });
        }
        $besoins = $this->getBesoinsAvecReste();

        $besoinsParArticle = [];
        foreach ($besoins as &$b) {
            $idArticle = (int) $b['id_article'];
            if (!isset($besoinsParArticle[$idArticle])) {
                $besoinsParArticle[$idArticle] = [];
            }
            $besoinsParArticle[$idArticle][] = &$b;
        }
        unset($b);

        $created = 0;
        $totalAttribue = 0.0;

        foreach ($dons as $don) {
            $idDon = (int) $don['id_don'];
            $idArticle = (int) $don['id_article'];
            $resteDon = (float) $don['reste_don'];

            if ($resteDon <= 0) {
                continue;
            }

            if (!isset($besoinsParArticle[$idArticle])) {
                continue;
            }

            foreach ($besoinsParArticle[$idArticle] as &$need) {
                if ($resteDon <= 0) {
                    break;
                }

                $resteBesoin = (float) $need['reste_besoin'];
                if ($resteBesoin <= 0) {
                    continue;
                }

                $attrib = min($resteDon, $resteBesoin);
                if ($attrib <= 0) {
                    continue;
                }

                if ($persist === true) {
                    $this->distributionsModel->create($idDon, (int) $need['id_ville'], $attrib, $dateAttribution);
                }

                $resteDon -= $attrib;
                $need['reste_besoin'] = $resteBesoin - $attrib;

                $created++;
                $totalAttribue += $attrib;
            }
            unset($need);
        }

        return [
            'distributions_creees' => $created,
            'quantite_attribuee_totale' => $totalAttribue,
            'persisted' => (bool) $persist,
        ];
    }

    public function getSimulatedSummaryRows($dateAttribution, $extraDons = []) {
        $dons = $this->getDonsAvecReste();

        if (is_array($extraDons) && count($extraDons) > 0) {
            $dons = array_merge($dons, $extraDons);

            usort($dons, function($a, $b) {
                $da = strtotime((string) ($a['date_reception'] ?? '1970-01-01 00:00:00'));
                $db = strtotime((string) ($b['date_reception'] ?? '1970-01-01 00:00:00'));
                if ($da === $db) {
                    return ((int) ($a['id_don'] ?? 0)) <=> ((int) ($b['id_don'] ?? 0));
                }
                return $da <=> $db;
            });
        }

        $besoins = $this->getBesoinsAvecReste();

        $besoinsParArticle = [];
        foreach ($besoins as &$b) {
            $idArticle = (int) $b['id_article'];
            if (!isset($besoinsParArticle[$idArticle])) {
                $besoinsParArticle[$idArticle] = [];
            }
            $besoinsParArticle[$idArticle][] = &$b;
        }
        unset($b);

        $created = 0;
        $totalAttribue = 0.0;
        $attribParVilleArticle = [];

        foreach ($dons as $don) {
            $idArticle = (int) $don['id_article'];
            $resteDon = (float) $don['reste_don'];

            if ($resteDon <= 0) {
                continue;
            }

            if (!isset($besoinsParArticle[$idArticle])) {
                continue;
            }

            foreach ($besoinsParArticle[$idArticle] as &$need) {
                if ($resteDon <= 0) {
                    break;
                }

                $resteBesoin = (float) $need['reste_besoin'];
                if ($resteBesoin <= 0) {
                    continue;
                }

                $attrib = min($resteDon, $resteBesoin);
                if ($attrib <= 0) {
                    continue;
                }

                $idVille = (int) $need['id_ville'];
                $key = $idVille . ':' . $idArticle;
                if (!isset($attribParVilleArticle[$key])) {
                    $attribParVilleArticle[$key] = 0.0;
                }
                $attribParVilleArticle[$key] += $attrib;

                $resteDon -= $attrib;
                $need['reste_besoin'] = $resteBesoin - $attrib;

                $created++;
                $totalAttribue += $attrib;
            }
            unset($need);
        }

        $base = $this->getSummaryRows();
        $out = [];

        foreach ($base as $r) {
            $key = ((int) $r['id_ville']) . ':' . ((int) $r['id_article']);
            $extra = (float) ($attribParVilleArticle[$key] ?? 0.0);
            $attribTotal = (float) ($r['attribue_total'] ?? 0) + $extra;
            $reste = max(0.0, (float) ($r['reste_a_combler'] ?? 0) - $extra);

            $r['attribue_total'] = $attribTotal;
            $r['reste_a_combler'] = $reste;
            $out[] = $r;
        }

        return [
            'dispatch' => [
                'distributions_creees' => $created,
                'quantite_attribuee_totale' => $totalAttribue,
                'persisted' => false,
                'date_execution' => $dateAttribution,
            ],
            'summary_rows' => $out,
            'count' => count($out),
        ];
    }

    public function getSummaryRows() {
        $sqlBesoins = "SELECT bv.id_ville, v.nom_ville, v.region, bv.id_article, a.nom_article, a.categorie, SUM(bv.quantite_demandee) AS besoin_total
                      FROM BNGRC_besoins_villes bv
                      JOIN BNGRC_villes v ON bv.id_ville = v.id_ville
                      JOIN BNGRC_articles a ON bv.id_article = a.id_article
                      GROUP BY bv.id_ville, v.nom_ville, v.region, bv.id_article, a.nom_article, a.categorie
                      ORDER BY v.nom_ville ASC, a.nom_article ASC";

        $sqlAttrib = "SELECT d.id_ville, dr.id_article, SUM(d.quantite_attribuee) AS attribue_total
                      FROM BNGRC_distributions d
                      JOIN BNGRC_dons_recus dr ON d.id_don = dr.id_don
                      GROUP BY d.id_ville, dr.id_article";

        $besoins = $this->db->fetchAll($sqlBesoins);
        $attribs = $this->db->fetchAll($sqlAttrib);

        $mapAttrib = [];
        foreach ($attribs as $a) {
            $mapAttrib[$a['id_ville'] . ':' . $a['id_article']] = (float) $a['attribue_total'];
        }

        $out = [];
        foreach ($besoins as $b) {
            $key = $b['id_ville'] . ':' . $b['id_article'];
            $attribue = $mapAttrib[$key] ?? 0.0;
            $besoin = (float) $b['besoin_total'];
            $reste = max(0.0, $besoin - $attribue);

            $out[] = [
                'id_ville' => (int) $b['id_ville'],
                'nom_ville' => $b['nom_ville'],
                'region' => $b['region'],
                'id_article' => (int) $b['id_article'],
                'nom_article' => $b['nom_article'],
                'categorie' => $b['categorie'],
                'besoin_total' => $besoin,
                'attribue_total' => $attribue,
                'reste_a_combler' => $reste,
            ];
        }

        return $out;
    }

    public function getDonsRestantsParArticle() {
        try {
            $sql = "SELECT id_article, nom_article, categorie, prix_unitaire, quantite_donnee_totale, quantite_attribuee_totale, quantite_restante
                    FROM BNGRC_V_Dons_Restants_Par_Article
                    ORDER BY categorie ASC, nom_article ASC";
            $rows = $this->db->fetchAll($sql);
        } catch (\Exception $e) {
            $sql = "SELECT a.id_article, a.nom_article, a.categorie, a.prix_unitaire,
                           IFNULL(SUM(dr.quantite_donnee), 0) AS quantite_donnee_totale,
                           IFNULL(SUM(d.quantite_attribuee), 0) AS quantite_attribuee_totale,
                           (IFNULL(SUM(dr.quantite_donnee), 0) - IFNULL(SUM(d.quantite_attribuee), 0)) AS quantite_restante
                    FROM BNGRC_articles a
                    LEFT JOIN BNGRC_dons_recus dr ON dr.id_article = a.id_article
                    LEFT JOIN BNGRC_distributions d ON d.id_don = dr.id_don
                    GROUP BY a.id_article, a.nom_article, a.categorie, a.prix_unitaire
                    ORDER BY a.categorie ASC, a.nom_article ASC";
            $rows = $this->db->fetchAll($sql);
        }

        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'id_article' => (int) ($r['id_article'] ?? 0),
                'nom_article' => $r['nom_article'] ?? null,
                'categorie' => $r['categorie'] ?? null,
                'prix_unitaire' => (float) ($r['prix_unitaire'] ?? 0),
                'quantite_restante' => (float) ($r['quantite_restante'] ?? 0),
            ];
        }

        return $out;
    }

    public function getDonsAvecReste() {
        $sql = "SELECT dr.id_don, dr.id_article, dr.quantite_donnee, dr.date_reception,
                       (dr.quantite_donnee - IFNULL(SUM(d.quantite_attribuee), 0)) AS reste_don
                FROM BNGRC_dons_recus dr
                LEFT JOIN BNGRC_distributions d ON d.id_don = dr.id_don
                GROUP BY dr.id_don, dr.id_article, dr.quantite_donnee, dr.date_reception
                HAVING reste_don > 0
                ORDER BY dr.date_reception ASC, dr.id_don ASC";

        return $this->db->fetchAll($sql);
    }

    public function getBesoinsAvecReste() {
        $sqlBesoins = "SELECT id_besoin, id_ville, id_article, quantite_demandee, date_saisie
                       FROM BNGRC_besoins_villes
                       ORDER BY date_saisie ASC, id_besoin ASC";

        $sqlAttrib = "SELECT d.id_ville, dr.id_article, SUM(d.quantite_attribuee) AS attribue_total
                      FROM BNGRC_distributions d
                      JOIN BNGRC_dons_recus dr ON d.id_don = dr.id_don
                      GROUP BY d.id_ville, dr.id_article";

        $besoins = $this->db->fetchAll($sqlBesoins);
        $attribs = $this->db->fetchAll($sqlAttrib);

        $resteAttribParVilleArticle = [];
        foreach ($attribs as $a) {
            $resteAttribParVilleArticle[$a['id_ville'] . ':' . $a['id_article']] = (float) $a['attribue_total'];
        }

        $out = [];
        foreach ($besoins as $b) {
            $key = $b['id_ville'] . ':' . $b['id_article'];
            $already = $resteAttribParVilleArticle[$key] ?? 0.0;
            $demand = (float) $b['quantite_demandee'];

            if ($already >= $demand) {
                $resteAttribParVilleArticle[$key] = $already - $demand;
                $reste = 0.0;
            } else {
                $reste = $demand - $already;
                $resteAttribParVilleArticle[$key] = 0.0;
            }

            $b['reste_besoin'] = $reste;
            $out[] = $b;
        }

        return $out;
    }
}
