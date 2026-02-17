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

    private function allocateProportionnelle($dateAttribution, $persist = true) {
        $dons = $this->getDonsAvecReste();
        $besoins = $this->getBesoinsAvecReste();

        $donsParArticle = [];
        foreach ($dons as $d) {
            $idArticle = (int) $d['id_article'];
            if (!isset($donsParArticle[$idArticle])) {
                $donsParArticle[$idArticle] = [];
            }
            $donsParArticle[$idArticle][] = $d;
        }

        $besoinsParArticle = [];
        foreach ($besoins as $b) {
            $idArticle = (int) $b['id_article'];
            if (!isset($besoinsParArticle[$idArticle])) {
                $besoinsParArticle[$idArticle] = [];
            }
            $besoinsParArticle[$idArticle][] = $b;
        }

        foreach ($besoinsParArticle as &$needs) {
            usort($needs, function($a, $b) {
                $da = strtotime((string) ($a['date_saisie'] ?? '1970-01-01 00:00:00'));
                $db = strtotime((string) ($b['date_saisie'] ?? '1970-01-01 00:00:00'));
                if ($da === $db) {
                    return ((int) ($a['id_besoin'] ?? 0)) <=> ((int) ($b['id_besoin'] ?? 0));
                }
                return $da <=> $db;
            });
        }
        unset($needs);

        $created = 0;
        $totalAttribue = 0.0;
        $attribParVilleArticle = [];

        foreach ($besoinsParArticle as $idArticle => $needs) {
            $idArticle = (int) $idArticle;
            if (!isset($donsParArticle[$idArticle]) || count($donsParArticle[$idArticle]) === 0) {
                continue;
            }

            $totalDonReste = 0.0;
            foreach ($donsParArticle[$idArticle] as $d) {
                $totalDonReste += (float) ($d['reste_don'] ?? 0);
            }

            if ($totalDonReste <= 0) {
                continue;
            }

            $donsQueue = $donsParArticle[$idArticle];
            $donIndex = 0;
            $resteDonCourant = (float) ($donsQueue[0]['reste_don'] ?? 0);

            foreach ($needs as $need) {
                $resteBesoin = (float) ($need['reste_besoin'] ?? 0);
                if ($resteBesoin <= 0) {
                    continue;
                }

                $quota = (int) floor($resteBesoin / $totalDonReste);
                if ($quota <= 0) {
                    continue;
                }

                $aDistribuer = min($resteBesoin, (float) $quota);
                while ($aDistribuer > 0 && $donIndex < count($donsQueue)) {
                    if ($resteDonCourant <= 0) {
                        $donIndex++;
                        if ($donIndex >= count($donsQueue)) {
                            break;
                        }
                        $resteDonCourant = (float) ($donsQueue[$donIndex]['reste_don'] ?? 0);
                        continue;
                    }

                    $attrib = min($aDistribuer, $resteDonCourant);
                    if ($attrib <= 0) {
                        break;
                    }

                    $idDon = (int) ($donsQueue[$donIndex]['id_don'] ?? 0);
                    $idVille = (int) ($need['id_ville'] ?? 0);

                    if ($persist === true) {
                        $this->distributionsModel->create($idDon, $idVille, $attrib, $dateAttribution);
                    }

                    $key = $idVille . ':' . $idArticle;
                    if (!isset($attribParVilleArticle[$key])) {
                        $attribParVilleArticle[$key] = 0.0;
                    }
                    $attribParVilleArticle[$key] += $attrib;

                    $resteDonCourant -= $attrib;
                    $aDistribuer -= $attrib;
                    $created++;
                    $totalAttribue += $attrib;
                }
            }
        }

        return [
            'dispatch' => [
                'distributions_creees' => $created,
                'quantite_attribuee_totale' => $totalAttribue,
                'persisted' => (bool) $persist,
                'date_execution' => $dateAttribution,
            ],
            'attrib_map' => $attribParVilleArticle,
        ];
    }

    public function runDispatchProportionnel($dateAttribution, $persist = true) {
        $res = $this->allocateProportionnelle($dateAttribution, $persist);
        return $res['dispatch'];
    }

    public function getSimulatedSummaryRowsProportionnel($dateAttribution) {
        $res = $this->allocateProportionnelle($dateAttribution, false);
        $attribParVilleArticle = $res['attrib_map'] ?? [];

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
            'dispatch' => $res['dispatch'] ?? [],
            'summary_rows' => $out,
            'count' => count($out),
        ];
    }

    public function runDispatch($dateAttribution, $persist = true, $extraDons = [], $smallestNeedsFirst = false) {
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

        if ($smallestNeedsFirst === true) {
            foreach ($besoinsParArticle as &$needs) {
                usort($needs, function($a, $b) {
                    $ra = (float) ($a['reste_besoin'] ?? 0);
                    $rb = (float) ($b['reste_besoin'] ?? 0);
                    if ($ra === $rb) {
                        $da = strtotime((string) ($a['date_saisie'] ?? '1970-01-01 00:00:00'));
                        $db = strtotime((string) ($b['date_saisie'] ?? '1970-01-01 00:00:00'));
                        if ($da === $db) {
                            return ((int) ($a['id_besoin'] ?? 0)) <=> ((int) ($b['id_besoin'] ?? 0));
                        }
                        return $da <=> $db;
                    }
                    return $ra <=> $rb;
                });
            }
            unset($needs);
        }

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

    public function getSimulatedSummaryRows($dateAttribution, $extraDons = [], $smallestNeedsFirst = false) {
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

        if ($smallestNeedsFirst === true) {
            foreach ($besoinsParArticle as &$needs) {
                usort($needs, function($a, $b) {
                    $ra = (float) ($a['reste_besoin'] ?? 0);
                    $rb = (float) ($b['reste_besoin'] ?? 0);
                    if ($ra === $rb) {
                        $da = strtotime((string) ($a['date_saisie'] ?? '1970-01-01 00:00:00'));
                        $db = strtotime((string) ($b['date_saisie'] ?? '1970-01-01 00:00:00'));
                        if ($da === $db) {
                            return ((int) ($a['id_besoin'] ?? 0)) <=> ((int) ($b['id_besoin'] ?? 0));
                        }
                        return $da <=> $db;
                    }
                    return $ra <=> $rb;
                });
            }
            unset($needs);
        }

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
        $sqlBesoins = "SELECT bv.id_ville, v.nom_ville, v.region, bv.id_article, a.nom_article, a.categorie,
                             SUM(bv.quantite_demandee) AS besoin_total,
                             MIN(bv.date_saisie) AS date_premier_besoin
                      FROM BNGRC_besoins_villes bv
                      JOIN BNGRC_villes v ON bv.id_ville = v.id_ville
                      JOIN BNGRC_articles a ON bv.id_article = a.id_article
                      GROUP BY bv.id_ville, v.nom_ville, v.region, bv.id_article, a.nom_article, a.categorie
                      ORDER BY date_premier_besoin ASC, v.nom_ville ASC, a.nom_article ASC";

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
                'date_premier_besoin' => $b['date_premier_besoin'] ?? null,
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
