<?php $page_title = 'Liste des Dons Recus'; ?>
<?php include __DIR__ . '/partials/header.php'; ?>

    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12">
          <div class="card mb-4">
            <div class="card-header pb-0 p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-1"><i class="fas fa-hand-holding-heart me-2 text-success"></i>Liste des Dons Reçus</h6>
                  <p class="text-sm text-secondary mb-0">Récapitulatif des articles donnés et leur distribution</p>
                </div>
                <span class="badge bg-gradient-dark" id="dons-count">0 dons</span>
              </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0" id="dons-table">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3" style="width: 60px;">#</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Article</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Catégorie</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Qté Donnée</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Qté Attribuée</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Qté Restante</th>
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
      </div>


<!-- Page-specific scripts -->
  <script>
    const BASE_URL = '<?= BASE_URL ?>';
  </script>
  <script src="<?= BASE_URL ?>/traitement-js/methodes/met_dons.js"></script>
  <script src="<?= BASE_URL ?>/traitement-js/dons.js"></script>


<?php include __DIR__ . '/partials/footer.php'; ?>