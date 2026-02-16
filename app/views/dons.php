<?php $page_title = 'Liste des Dons Recus'; ?>
<?php include __DIR__ . '/partials/header.php'; ?>

    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12">
          <div class="card mb-4">
            <div class="card-header pb-0">
              <h6>Liste des Dons Recus</h6>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0" id="dons-table">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">ID</th>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Article</th>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Categorie</th>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Prix unitaire</th>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Quantité</th>
                      <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Date de Donnation</th>
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


<!-- Page-specific scripts -->
  <script>
    const BASE_URL = '<?= BASE_URL ?>';
  </script>
  <script nonce="<?= $nonce ?>" src="<?= BASE_URL ?>/assets/js/coralie/met_dons.js"></script>
  <script nonce="<?= $nonce ?>" src="<?= BASE_URL ?>/traitement-js/dons.js"></script>


<?php include __DIR__ . '/partials/footer.php'; ?>