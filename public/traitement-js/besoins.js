window.addEventListener('load', async function () {
    try {
      console.log('Chargement des données...');
      const articles = await getAllArticles();
      console.log('Articles récupérés:', articles);
      
      const villes = await getAllVilles();
      console.log('Villes récupérées:', villes);
      
      const modes = await getAllModes();
      console.log('Modes récupérés:', modes);
      
      loadFormBesoin(villes, articles, modes);
      loadFormDon(articles, modes);
      loadFormArticle();
      loadFormVille();
      showForm('besoin');

      document.querySelector('#btn-form-besoin').addEventListener('click', function() {
          showForm('besoin');
      });
      document.querySelector('#btn-form-don').addEventListener('click', function() {
          showForm('don');
      });
      document.querySelector('#btn-form-article').addEventListener('click', function() {
          showForm('article');
      });
      document.querySelector('#btn-form-ville').addEventListener('click', function() {
          showForm('ville');
      });
    } catch (e) {
      console.error('Erreur:', e);
      this.alert('Erreur chargement de la page :'+ (e && e.message ? e.message : String(e)));
    }
});

function loadFormBesoin(villes, articles, modes){
    const div = document.querySelector('#form-besoin');
    
    if (!div) {
        console.error('Le div #form-besoin n\'a pas été trouvé!');
        return;
    }
    
    if (!villes || !villes.data || villes.data.length === 0) {
        console.error('Pas de données de villes disponibles');
        div.innerHTML = '<div class="alert alert-warning">Aucune ville disponible</div>';
        return;
    }
    
    if (!articles || !articles.data || articles.data.length === 0) {
        console.error('Pas de données d\'articles disponibles');
        div.innerHTML = '<div class="alert alert-warning">Aucun article disponible</div>';
        return;
    }
    
    // Créer le formulaire
    const form = document.createElement('form');
    form.className = 'card card-body p-4';
    form.innerHTML = `
        <div class="row">
            <div class="col-md-12">
                <h4 class="mb-4">Saisie des Besoins</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="ville" class="form-label">Ville</label>
                <select class="form-select" id="ville" required>
                    <option value="">Sélectionner une ville</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="article" class="form-label">Article</label>
                <select class="form-select" id="article" required>
                    <option value="">Sélectionner un article</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="mode" class="form-label">Mode</label>
                <select class="form-select" id="mode" required>
                    <option value="">Sélectionner un mode</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="quantite" class="form-label">Quantité demandée</label>
                <input type="number" class="form-control" id="quantite" min="0.01" step="0.01" required placeholder="Entrez la quantité">
            </div>
            <div class="col-md-4 mb-3">
                <label for="date_saisie" class="form-label">Date de saisie</label>
                <input type="date" class="form-control" id="date_saisie">
            </div>
            <div class="col-md-4 mb-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-plus me-2"></i>Valider
                </button>
            </div>
        </div>
        <div id="message" class="mt-3"></div>
    `;
    
    // Remplir les listes déroulantes
    const villeSelect = form.querySelector('#ville');
    villes.data.forEach(ville => {
        const option = document.createElement('option');
        option.value = ville.id_ville;
        option.textContent = `${ville.nom_ville} (${ville.region})`;
        villeSelect.appendChild(option);
    });
    
    const articleSelect = form.querySelector('#article');
    articles.data.forEach(article => {
        const option = document.createElement('option');
        option.value = article.id_article;
        option.textContent = `${article.nom_article} - ${article.categorie}`;
        articleSelect.appendChild(option);
    });
    
    // Remplir le select des modes
    const modeSelect = form.querySelector('#mode');
    if (modes && modes.data) {
        modes.data.forEach(mode => {
            const option = document.createElement('option');
            option.value = mode.id_mode;
            option.textContent = mode.nom_mode.charAt(0).toUpperCase() + mode.nom_mode.slice(1);
            if (mode.description) {
                option.title = mode.description;
            }
            // Par défaut, sélectionner "teste" (id_mode = 2)
            if (parseInt(mode.id_mode) === 2) {
                option.selected = true;
            }
            modeSelect.appendChild(option);
        });
    }
    
    // Gérer la soumission du formulaire
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const messageDiv = form.querySelector('#message');
        messageDiv.innerHTML = '';
        
        const villeId = form.querySelector('#ville').value;
        const articleId = form.querySelector('#article').value;
        const quantite = form.querySelector('#quantite').value;
        const modeId = form.querySelector('#mode').value;
        const dateSaisie = form.querySelector('#date_saisie').value;
        
        // Validation
        if (!villeId || !articleId || !quantite || !modeId) {
            messageDiv.innerHTML = '<div class="alert alert-warning">Veuillez remplir tous les champs</div>';
            return;
        }
        
        if (parseFloat(quantite) <= 0) {
            messageDiv.innerHTML = '<div class="alert alert-warning">La quantité doit être supérieure à 0</div>';
            return;
        }
        
        try {
            // Désactiver le bouton pendant la soumission
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>En cours...';
            
            const data = {
                id_ville: parseInt(villeId),
                id_article: parseInt(articleId),
                quantite_demandee: parseFloat(quantite),
                id_mode: parseInt(modeId),
                date_saisie: dateSaisie || null
            };
            
            const result = await createBesoin(data);
            
            if (result.success) {
                messageDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Besoin créé avec succès!</div>';
                // Réinitialiser le formulaire
                form.reset();
            } else {
                messageDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Erreur: ${result.message}</div>`;
            }
        } catch (error) {
            messageDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Erreur lors de la création du besoin</div>';
        } finally {
            // Réactiver le bouton
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-plus me-2"></i>Valider';
        }
    });
    
    // Vider le div et ajouter le formulaire
    div.innerHTML = '';
    div.appendChild(form);
}

function loadFormDon(articles, modes){
    const div = document.querySelector('#form-don');

    if (!div) {
        console.error('Le div #form-don n\'a pas été trouvé!');
        return;
    }

    if (!articles || !articles.data || articles.data.length === 0) {
        console.error('Pas de données d\'articles disponibles');
        div.innerHTML = '<div class="alert alert-warning">Aucun article disponible</div>';
        return;
    }

    // Créer le formulaire
    const form = document.createElement('form');
    form.className = 'card card-body p-4';
    form.innerHTML = `
        <div class="row">
            <div class="col-md-12">
                <h4 class="mb-4">Enregistrement d'un Don</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="don-article" class="form-label">Article</label>
                <select class="form-select" id="don-article" required>
                    <option value="">Sélectionner un article</option>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="don-quantite" class="form-label">Quantité donnée</label>
                <input type="number" class="form-control" id="don-quantite" min="0.01" step="0.01" required placeholder="Entrez la quantité">
            </div>
            <div class="col-md-3 mb-3">
                <label for="don-date" class="form-label">Date de réception</label>
                <input type="date" class="form-control" id="don-date" required>
            </div>
            <div class="col-md-3 mb-3">
                <label for="don-mode" class="form-label">Mode</label>
                <select class="form-select" id="don-mode" required>
                    <option value="">Sélectionner un mode</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3 ms-auto d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-hand-holding-heart me-2"></i>Enregistrer le don
                </button>
            </div>
        </div>
        <div id="don-message" class="mt-3"></div>
    `;

    // Remplir la liste déroulante des articles
    const articleSelect = form.querySelector('#don-article');
    articles.data.forEach(article => {
        const option = document.createElement('option');
        option.value = article.id_article;
        option.textContent = `${article.nom_article} - ${article.categorie}`;
        articleSelect.appendChild(option);
    });

    // Remplir le select des modes
    const modeSelect = form.querySelector('#don-mode');
    if (modes && modes.data) {
        modes.data.forEach(mode => {
            const option = document.createElement('option');
            option.value = mode.id_mode;
            option.textContent = mode.nom_mode.charAt(0).toUpperCase() + mode.nom_mode.slice(1);
            if (mode.description) {
                option.title = mode.description;
            }
            // Par défaut, sélectionner "teste" (id_mode = 2)
            if (parseInt(mode.id_mode) === 2) {
                option.selected = true;
            }
            modeSelect.appendChild(option);
        });
    }

    // Date par défaut = aujourd'hui
    const today = new Date().toISOString().split('T')[0];
    form.querySelector('#don-date').value = today;

    // Gérer la soumission du formulaire
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const messageDiv = form.querySelector('#don-message');
        messageDiv.innerHTML = '';

        const articleId = form.querySelector('#don-article').value;
        const quantite = form.querySelector('#don-quantite').value;
        const dateReception = form.querySelector('#don-date').value;
        const modeId = form.querySelector('#don-mode').value;

        // Validation
        if (!articleId || !quantite || !dateReception || !modeId) {
            messageDiv.innerHTML = '<div class="alert alert-warning">Veuillez remplir tous les champs</div>';
            return;
        }

        if (parseFloat(quantite) <= 0) {
            messageDiv.innerHTML = '<div class="alert alert-warning">La quantité doit être supérieure à 0</div>';
            return;
        }

        try {
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>En cours...';

            const data = {
                id_article: parseInt(articleId),
                quantite_donnee: parseFloat(quantite),
                date_reception: dateReception,
                id_mode: parseInt(modeId)
            };

            const result = await createDon(data);

            if (result.success) {
                messageDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Don enregistré avec succès!</div>';
                form.reset();
                form.querySelector('#don-date').value = today;
                // Remettre le mode par défaut sur "teste"
                form.querySelector('#don-mode').value = '2';
            } else {
                messageDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Erreur: ${result.message}</div>`;
            }
        } catch (error) {
            messageDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Erreur lors de l\'enregistrement du don</div>';
        } finally {
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-hand-holding-heart me-2"></i>Enregistrer le don';
        }
    });

    div.innerHTML = '';
    div.appendChild(form);
}

function showForm(type) {
    const formBesoin = document.querySelector('#form-besoin');
    const formDon = document.querySelector('#form-don');
    const formArticle = document.querySelector('#form-article');
    const formVille = document.querySelector('#form-ville');
    const btnBesoin = document.querySelector('#btn-form-besoin');
    const btnDon = document.querySelector('#btn-form-don');
    const btnArticle = document.querySelector('#btn-form-article');
    const btnVille = document.querySelector('#btn-form-ville');

    // Cacher tous les formulaires
    formBesoin.style.display = 'none';
    formDon.style.display = 'none';
    formArticle.style.display = 'none';
    formVille.style.display = 'none';

    // Réinitialiser tous les boutons en outline
    btnBesoin.className = 'btn btn-outline-primary w-100 mb-0';
    btnDon.className = 'btn btn-outline-success w-100 mb-0';
    btnArticle.className = 'btn btn-outline-info w-100 mb-0';
    btnVille.className = 'btn btn-outline-warning w-100 mb-0';

    // Afficher le formulaire sélectionné et activer son bouton
    if (type === 'besoin') {
        formBesoin.style.display = 'block';
        btnBesoin.className = 'btn btn-primary w-100 mb-0';
    } else if (type === 'don') {
        formDon.style.display = 'block';
        btnDon.className = 'btn btn-success w-100 mb-0';
    } else if (type === 'article') {
        formArticle.style.display = 'block';
        btnArticle.className = 'btn btn-info w-100 mb-0';
    } else if (type === 'ville') {
        formVille.style.display = 'block';
        btnVille.className = 'btn btn-warning w-100 mb-0';
    }
}

function loadFormArticle() {
    const div = document.querySelector('#form-article');

    if (!div) {
        console.error('Le div #form-article n\'a pas été trouvé!');
        return;
    }

    // Créer le formulaire
    const form = document.createElement('form');
    form.className = 'card card-body p-4';
    form.innerHTML = `
        <div class="row">
            <div class="col-md-12">
                <h4 class="mb-4">Ajout d'un Article</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="article-nom" class="form-label">Nom de l'article</label>
                <input type="text" class="form-control" id="article-nom" required placeholder="Ex: Riz, Huile, Tôle...">
            </div>
            <div class="col-md-4 mb-3">
                <label for="article-categorie" class="form-label">Catégorie</label>
                <select class="form-select" id="article-categorie" required>
                    <option value="">Sélectionner une catégorie</option>
                    <option value="Nature">Nature</option>
                    <option value="Matériaux">Matériaux</option>
                    <option value="Argent">Argent</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="article-prix" class="form-label">Prix unitaire (Ar)</label>
                <input type="number" class="form-control" id="article-prix" min="0" step="0.01" required placeholder="Ex: 3200">
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3 ms-auto d-flex align-items-end">
                <button type="submit" class="btn btn-info w-100">
                    <i class="fas fa-plus me-2"></i>Ajouter l'article
                </button>
            </div>
        </div>
        <div id="article-message" class="mt-3"></div>
    `;

    // Gérer la soumission du formulaire
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const messageDiv = form.querySelector('#article-message');
        messageDiv.innerHTML = '';

        const nomArticle = form.querySelector('#article-nom').value.trim();
        const categorie = form.querySelector('#article-categorie').value;
        const prixUnitaire = form.querySelector('#article-prix').value;

        // Validation
        if (!nomArticle || !categorie || !prixUnitaire) {
            messageDiv.innerHTML = '<div class="alert alert-warning">Veuillez remplir tous les champs</div>';
            return;
        }

        if (parseFloat(prixUnitaire) < 0) {
            messageDiv.innerHTML = '<div class="alert alert-warning">Le prix doit être positif ou nul</div>';
            return;
        }

        try {
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>En cours...';

            const data = {
                nom_article: nomArticle,
                categorie: categorie,
                prix_unitaire: parseFloat(prixUnitaire)
            };

            const result = await createArticle(data);

            if (result.success) {
                messageDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Article ajouté avec succès!</div>';
                form.reset();
            } else {
                messageDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Erreur: ${result.message}</div>`;
            }
        } catch (error) {
            messageDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Erreur lors de l\'ajout de l\'article</div>';
        } finally {
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-plus me-2"></i>Ajouter l\'article';
        }
    });

    div.innerHTML = '';
    div.appendChild(form);
}

function loadFormVille() {
    const div = document.querySelector('#form-ville');

    if (!div) {
        console.error('Le div #form-ville n\'a pas été trouvé!');
        return;
    }

    // Créer le formulaire
    const form = document.createElement('form');
    form.className = 'card card-body p-4';
    form.innerHTML = `
        <div class="row">
            <div class="col-md-12">
                <h4 class="mb-4">Ajout d'une Ville</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="ville-nom" class="form-label">Nom de la ville</label>
                <input type="text" class="form-control" id="ville-nom" required placeholder="Ex: Antananarivo, Tamatave...">
            </div>
            <div class="col-md-6 mb-3">
                <label for="ville-region" class="form-label">Région</label>
                <input type="text" class="form-control" id="ville-region" required placeholder="Ex: Analamanga, Atsinanana...">
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3 ms-auto d-flex align-items-end">
                <button type="submit" class="btn btn-warning w-100">
                    <i class="fas fa-plus me-2"></i>Ajouter la ville
                </button>
            </div>
        </div>
        <div id="ville-message" class="mt-3"></div>
    `;

    // Gérer la soumission du formulaire
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const messageDiv = form.querySelector('#ville-message');
        messageDiv.innerHTML = '';

        const nomVille = form.querySelector('#ville-nom').value.trim();
        const region = form.querySelector('#ville-region').value.trim();

        // Validation
        if (!nomVille || !region) {
            messageDiv.innerHTML = '<div class="alert alert-warning">Veuillez remplir tous les champs</div>';
            return;
        }

        try {
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>En cours...';

            const data = {
                nom_ville: nomVille,
                region: region
            };

            const result = await createVille(data);

            if (result.success) {
                messageDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Ville ajoutée avec succès!</div>';
                form.reset();
            } else {
                messageDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Erreur: ${result.message}</div>`;
            }
        } catch (error) {
            messageDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Erreur lors de l\'ajout de la ville</div>';
        } finally {
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-plus me-2"></i>Ajouter la ville';
        }
    });

    div.innerHTML = '';
    div.appendChild(form);
}