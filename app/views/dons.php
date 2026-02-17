<?php $page_title = 'Liste des Dons Recus'; ?>
<?php include __DIR__ . '/partials/header.php'; ?>

    <div class="container-fluid py-4">
      <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-xl-0 mb-4">
          <div class="card h-100">
            <div class="card-body p-3">
              <div class="row align-items-center">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold text-secondary">Total dons reçus</p>
                    <h4 class="font-weight-bolder mb-0 mt-2" id="dons-total-recu">0</h4>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle" style="width: 48px; height: 48px;">
                    <i class="fas fa-gift text-lg opacity-10" style="line-height: 48px;" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-xl-0 mb-4">
          <div class="card h-100">
            <div class="card-body p-3">
              <div class="row align-items-center">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold text-secondary">Total dons attribués</p>
                    <h4 class="font-weight-bolder mb-0 mt-2" id="dons-total-attribue">0</h4>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle" style="width: 48px; height: 48px;">
                    <i class="fas fa-check-circle text-lg opacity-10" style="line-height: 48px;" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-xl-0 mb-4">
          <div class="card h-100">
            <div class="card-body p-3">
              <div class="row align-items-center">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold text-secondary">Dons non attribués</p>
                    <h4 class="font-weight-bolder mb-0 mt-2" id="dons-total-non-attribue">0</h4>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle" style="width: 48px; height: 48px;">
                    <i class="fas fa-hourglass-half text-lg opacity-10" style="line-height: 48px;" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Liste des dons restant par Article (GAUCHE) -->
        <div class="col-lg-5 col-12 mb-4">
          <div class="card h-100">
            <div class="card-header pb-0 p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-1"><i class="fas fa-boxes me-2 text-primary"></i>Dons Restants par Article</h6>
                  <p class="text-sm text-secondary mb-0">Cliquez sur un article pour filtrer</p>
                </div>
                <button class="btn btn-sm btn-outline-secondary mb-0" id="btn-reset-filter" style="display: none;">
                  <i class="fas fa-times me-1"></i>Refrech
                </button>
              </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
              <div class="table-responsive p-0" style="max-height: 450px; overflow-y: auto;">
                <table class="table align-items-center mb-0" id="dons-table">
                  <thead class="sticky-top bg-white">
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Article</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Catégorie</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Donnée</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Attribuée</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Restante</th>
                    </tr>
                  </thead>
                  <tbody id="dons-table-body">
                    <!-- Les données des dons reçus seront injectées ici par dons.js -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Liste des dons saisi (DROITE) -->
        <div class="col-lg-7 col-12 mb-4">
          <div class="card h-100">
            <div class="card-header pb-0 p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-1"><i class="fas fa-hand-holding-heart me-2 text-success"></i>Historique des Dons Saisis</h6>
                  <p class="text-sm text-secondary mb-0" id="dons-saisi-filter-info">Tous les dons</p>
                </div>
                <span class="badge bg-gradient-success" id="dons-saisi-count">0 dons</span>
              </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
              <div class="table-responsive p-0" style="max-height: 450px; overflow-y: auto;">
                <table class="table align-items-center mb-0" id="dons-saisi-table">
                  <thead class="sticky-top bg-white">
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3" style="width: 50px;">#</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Article</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Catégorie</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Qté Donnée</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date réception</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                    </tr>
                  </thead>
                  <tbody id="dons-saisi-table-body">
                    <!-- Les données de dons saisi seront ajouter ici par dons.js -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>


<!-- Page-specific scripts -->
  <script>
    const BASE_URL = '<?= BASE_URL ?>';
  </script>
  <script src="<?= BASE_URL ?>/traitement-js/methodes/met_dons.js"></script>
  <script src="<?= BASE_URL ?>/traitement-js/dons.js"></script>


<?php include __DIR__ . '/partials/footer.php'; ?>