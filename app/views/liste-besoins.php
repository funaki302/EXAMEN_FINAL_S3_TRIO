<?php $page_title = 'Liste des Besoins saisi'; ?>
<?php include __DIR__ . '/partials/header.php'; ?>

    <div class="container-fluid py-4">
      <!-- Cards statistiques -->
      <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card h-100">
            <div class="card-body p-3">
              <div class="row align-items-center">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold text-secondary">Total Besoins</p>
                    <h4 class="font-weight-bolder mb-0 mt-2" id="stat-total-besoins">0</h4>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle" style="width: 48px; height: 48px;">
                    <i class="fas fa-clipboard-list text-lg opacity-10" style="line-height: 48px;" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card h-100">
            <div class="card-body p-3">
              <div class="row align-items-center">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold text-secondary">Quantité Totale</p>
                    <h4 class="font-weight-bolder mb-0 mt-2" id="stat-quantite-totale">0</h4>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle" style="width: 48px; height: 48px;">
                    <i class="fas fa-boxes text-lg opacity-10" style="line-height: 48px;" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card h-100">
            <div class="card-body p-3">
              <div class="row align-items-center">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold text-secondary">Villes concernées</p>
                    <h4 class="font-weight-bolder mb-0 mt-2" id="stat-villes">0</h4>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle" style="width: 48px; height: 48px;">
                    <i class="fas fa-city text-lg opacity-10" style="line-height: 48px;" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card h-100">
            <div class="card-body p-3">
              <div class="row align-items-center">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold text-secondary">Valeur Totale</p>
                    <h4 class="font-weight-bolder mb-0 mt-2" id="stat-valeur-totale">0 Ar</h4>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle" style="width: 48px; height: 48px;">
                    <i class="fas fa-coins text-lg opacity-10" style="line-height: 48px;" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <div class="card mb-4">
            <div class="card-header pb-0 p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-1"><i class="fas fa-list me-2 text-primary"></i>Liste des Besoins saisis</h6>
                  <p class="text-sm text-secondary mb-0">Historique des besoins enregistrés</p>
                </div>
                <span class="badge bg-gradient-primary" id="besoins-count">0 besoins</span>
              </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0" id="besoins-table">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3" style="width: 60px;">#</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Ville / Région</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Article</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Qté Demandée</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Prix unitaire</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Valeur</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                    </tr>
                  </thead>
                  <tbody id="besoins-table-body">
                    <!-- Les données des besoins reçus seront ajouter ici par liste-besoins.js -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>


<!-- Page-specific scripts -->
  <script>
    const BASE_URL = '<?= BASE_URL ?>';
  </script>
  <script src="<?= BASE_URL ?>/traitement-js/methodes/met_besoins.js"></script>
  <script src="<?= BASE_URL ?>/traitement-js/liste-besoins.js"></script>


<?php include __DIR__ . '/partials/footer.php'; ?>