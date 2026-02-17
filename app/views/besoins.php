<?php $page_title = 'Saisie des Besoins'; ?>
<?php include __DIR__ . '/partials/header.php'; ?>
    <div class="page-header min-height-200 border-radius-xl mt-4" style="background-image: url('../assets/img/curved-images/curved0.jpg'); background-position-y: 50%;">
      <span class="mask bg-gradient-primary opacity-6"></span>
    </div>
    <div class="container-fluid py-4 mt-n6">
      <div class="row">
        <div class="col-12">
          <br><br><br>
          <div class="card card-body p-3 mb-4">
            <div class="row">
              <div class="col-md-3 mb-2 mb-md-0">
                <button id="btn-form-besoin" class="btn btn-primary w-100 mb-0">
                  <i class="fas fa-clipboard-list me-2"></i>Formulaire Besoin
                </button>
              </div>
              <div class="col-md-3 mb-2 mb-md-0">
                <button id="btn-form-don" class="btn btn-outline-success w-100 mb-0">
                  <i class="fas fa-hand-holding-heart me-2"></i>Formulaire Don
                </button>
              </div>
              <div class="col-md-3 mb-2 mb-md-0">
                <button id="btn-form-article" class="btn btn-outline-info w-100 mb-0">
                  <i class="fas fa-box me-2"></i>Formulaire Article
                </button>
              </div>
              <div class="col-md-3">
                <button id="btn-form-ville" class="btn btn-outline-warning w-100 mb-0">
                  <i class="fas fa-city me-2"></i>Formulaire Ville
                </button>
              </div>
            </div>
          </div>
          <div id="form-besoin">
            <!-- Le formulaire sera ajouter ici par besoins.js -->
            <div class="card card-body p-4">
              <h4 class="mb-0">Chargement du formulaire...</h4>
            </div>
          </div>
          <div class="mt-4" id="form-don" style="display: none;">
            <!-- Le formulaire de don sera ajouter ici par besoins.js -->
            <div class="card card-body p-4">
              <h4 class="mb-0">Chargement du formulaire de don...</h4>
            </div>
          </div>
          <div class="mt-4" id="form-article" style="display: none;">
            <!-- Le formulaire d'article sera ajouter ici par besoins.js -->
            <div class="card card-body p-4">
              <h4 class="mb-0">Chargement du formulaire d'article...</h4>
            </div>
          </div>
          <div class="mt-4" id="form-ville" style="display: none;">
            <!-- Le formulaire de ville sera ajouter ici par besoins.js -->
            <div class="card card-body p-4">
              <h4 class="mb-0">Chargement du formulaire de ville...</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Page-specific scripts -->
  <script>
    const BASE_URL = '<?= BASE_URL ?>';
  </script>
    <script src="<?= BASE_URL ?>/traitement-js/methodes/met_villes.js"></script>
    <script src="<?= BASE_URL ?>/traitement-js/methodes/met_articles.js"></script>
    <script src="<?= BASE_URL ?>/traitement-js/methodes/met_modes.js"></script>
    <script src="<?= BASE_URL ?>/traitement-js/methodes/met_besoins.js"></script>
    <script src="<?= BASE_URL ?>/traitement-js/methodes/met_dons.js"></script>
    <script src="<?= BASE_URL ?>/traitement-js/besoins.js"></script>

  </div>
  <!-- End main-content -->

<?php include __DIR__ . '/partials/footer.php'; ?>