<?php $page_title = 'Achats'; ?>
<?php include __DIR__ . '/partials/header.php'; ?>

    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12">
          <div class="card mb-4">
            <div class="card-header pb-0">
              <div class="d-flex align-items-center justify-content-between">
                <h6 class="mb-0">Achats (conversion Argent -> Articles)</h6>
                <button id="btn-achats-refresh" class="btn btn-outline-primary btn-sm mb-0">Actualiser</button>
              </div>
              <p class="text-sm text-secondary mb-0 mt-2">
                L'argent est global. Les achats créent des dons qui seront dispatchés.
              </p>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-lg-4 mb-3">
                  <label class="form-label">Filtrer par ville</label>
                  <select id="select-ville" class="form-select">
                    <option value="">Toutes les villes</option>
                  </select>
                </div>
                <div class="col-lg-8 mb-3">
                  <label class="form-label">Solde Argent (global)</label>
                  <div id="achats-solde" class="p-3 border border-radius-md bg-gray-100 text-sm text-dark">Chargement...</div>
                </div>
              </div>

              <div id="achats-status" class="text-sm text-secondary mb-3"></div>

              <div class="table-responsive" id="achats-table"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="modal-achat" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Achat</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-2 text-sm text-secondary" id="modal-achat-info"></div>

              <div class="mb-3">
                <label class="form-label">Quantité à acheter</label>
                <input id="input-quantite" type="number" step="0.01" min="0" class="form-control" placeholder="Ex: 10" />
              </div>

              <div class="mb-3">
                <label class="form-label">Taux frais (%)</label>
                <input id="input-frais" type="number" step="0.01" min="0" class="form-control" placeholder="Ex: 5" />
              </div>

              <div class="border border-radius-md p-3 bg-gray-100">
                <div class="text-sm text-dark" id="modal-achat-result">Renseigne quantité + frais puis clique Simuler.</div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fermer</button>
              <button type="button" id="btn-achat-simuler" class="btn btn-primary btn-sm">Simuler</button>
              <button type="button" id="btn-achat-valider" class="btn btn-success btn-sm">Valider</button>
            </div>
          </div>
        </div>
      </div>

      <script nonce="<?= $nonce ?>">
        window.BASE_URL = '<?= BASE_URL ?>';
      </script>
      <script nonce="<?= $nonce ?>" src="<?= BASE_URL ?>/traitement-js/achats.js"></script>

    </div>

  </div>
  <!-- End main-content -->

<?php include __DIR__ . '/partials/footer.php'; ?>
