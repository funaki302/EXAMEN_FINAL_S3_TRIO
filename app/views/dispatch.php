<?php $page_title = 'Dispatch'; ?>
<?php include __DIR__ . '/partials/header.php'; ?>

    <div class="container-fluid py-4">
      <div id="dispatch-restants-cards" class="d-flex flex-row flex-nowrap overflow-auto pb-2 mb-3" style="gap: 16px;"></div>

      <div class="row mb-4" id="dispatch-besoins-stats">
        <div class="col-xl-3 col-sm-6 mb-3">
          <div class="card h-100">
            <div class="card-body p-3">
              <p class="text-sm text-uppercase font-weight-bold mb-1">Total besoins</p>
              <h4 class="font-weight-bolder mb-0" id="dispatch-besoins-total">0</h4>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-3">
          <div class="card h-100">
            <div class="card-body p-3">
              <p class="text-sm text-uppercase font-weight-bold mb-1">Besoins satisfaits</p>
              <h4 class="font-weight-bolder mb-0" id="dispatch-besoins-satisfaits">0</h4>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-3">
          <div class="card h-100">
            <div class="card-body p-3">
              <p class="text-sm text-uppercase font-weight-bold mb-1">Besoins non satisfaits</p>
              <h4 class="font-weight-bolder mb-0" id="dispatch-besoins-non-satisfaits">0</h4>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-3">
          <div class="card h-100">
            <div class="card-body p-3">
              <p class="text-sm text-uppercase font-weight-bold mb-1">Reste total</p>
              <h4 class="font-weight-bolder mb-0" id="dispatch-besoins-reste-total">0</h4>
            </div>
          </div>
        </div>
      </div>

      <div class="row mb-4">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0 p-3">
              <div class="d-flex align-items-center justify-content-between">
                <h6 class="mb-0">Besoins par article</h6>
              </div>
              <p class="text-sm text-secondary mb-0 mt-2">Vue synthétique et détaillée des besoins par article, par ville.</p>
            </div>
            <div class="card-body p-3">
              <div id="dispatch-besoins-par-article" class="row"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <div class="card mb-4">
            <div class="card-header pb-0">
              <div class="d-flex align-items-center justify-content-between">
                <h6 class="mb-0">Simulation du Dispatch</h6>
                <div class="d-flex gap-2 align-items-center">
                  <button id="btn-dispatch-refresh" class="btn btn-secondary btn-sm mb-0">Actualiser</button>

                  <select id="select-dispatch-algo" class="form-select form-select-sm" style="min-width: 220px;">
                    <option value="dispatch" selected>Dispatch</option>
                    <option value="smallest">Petite demande</option>
                    <option value="proportionnel">Proportionnelle</option>
                  </select>

                  <select id="select-dispatch-action" class="form-select form-select-sm" style="min-width: 140px;">
                    <option value="simulate" selected>Simuler</option>
                    <option value="validate">Valider</option>
                  </select>

                  <div id="dispatch-mode-bd-wrapper" style="display: none;">
                    <select id="select-dispatch-id-mode" class="form-select form-select-sm" style="min-width: 160px;">
                      <option value="2" selected>Test</option>
                      <option value="1">Origine</option>
                    </select>
                  </div>

                  <button id="btn-dispatch-execute" class="btn btn-primary btn-sm mb-0">Exécuter</button>
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

      <script>
        window.BASE_URL = '<?= BASE_URL ?>';
      </script>
      <script src="<?= BASE_URL ?>/traitement-js/dispatch.js"></script>

    </div>

  </div>
  <!-- End main-content -->

<?php include __DIR__ . '/partials/footer.php'; ?>
