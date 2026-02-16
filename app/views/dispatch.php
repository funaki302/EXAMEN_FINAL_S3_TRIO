<?php $page_title = 'Dispatch'; ?>
<?php include __DIR__ . '/partials/header.php'; ?>

    <div class="container-fluid py-4">
      <div class="row" id="dispatch-restants-cards"></div>
      <div class="row">
        <div class="col-12">
          <div class="card mb-4">
            <div class="card-header pb-0">
              <div class="d-flex align-items-center justify-content-between">
                <h6 class="mb-0">Simulation du Dispatch</h6>
                <div class="d-flex gap-2">
                  <button id="btn-dispatch-refresh" class="btn btn-secondary btn-sm mb-0">Actualiser</button>
                  <button id="btn-dispatch-run" class="btn btn-primary btn-sm mb-0">Simuler le Dispatch</button>
                  <button id="btn-dispatch-validate" class="btn btn-success btn-sm mb-0">Valider le Dispatch</button>
                </div>
              </div>
              <p class="text-sm text-secondary mb-0 mt-2">
                Répartition automatique des dons selon l'ordre de réception, et des besoins selon l'ordre de saisie.
              </p>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
              <div class="px-4 pt-3">
                <div id="dispatch-status" class="text-sm text-secondary">Chargement...</div>
              </div>
              <div class="table-responsive p-0" id="dispatch-summary"></div>
            </div>
          </div>
        </div>
      </div>

      <script nonce="<?= $nonce ?>">
        window.BASE_URL = '<?= BASE_URL ?>';
      </script>
      <script nonce="<?= $nonce ?>" src="<?= BASE_URL ?>/traitement-js/dispatch.js"></script>

    </div>

  </div>
  <!-- End main-content -->

<?php include __DIR__ . '/partials/footer.php'; ?>
