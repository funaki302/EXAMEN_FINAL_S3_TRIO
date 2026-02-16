window.addEventListener('load', async function () {
    try {
      console.log('Chargement des données...');
      const articles = await getAllArticles();
      console.log('Articles récupérés:', articles);
      
      const villes = await getAllVilles();
      console.log('Villes récupérées:', villes);
      
      loadFormBesoin(villes, articles);
      loadFormDon(articles);
    } catch (e) {
      console.error('Erreur:', e);
      this.alert('Erreur chargement de la page :'+ (e && e.message ? e.message : String(e)));
    }
});

function loadFormBesoin(villes, articles){
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
            <div class="col-md-6 mb-3">
                <label for="ville" class="form-label">Ville</label>
                <select class="form-select" id="ville" required>
                    <option value="">Sélectionner une ville</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="article" class="form-label">Article</label>
                <select class="form-select" id="article" required>
                    <option value="">Sélectionner un article</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="quantite" class="form-label">Quantité demandée</label>
                <input type="number" class="form-control" id="quantite" min="0.01" step="0.01" required placeholder="Entrez la quantité">
            </div>
            <div class="col-md-6 mb-3 d-flex align-items-end">
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
    
    // Gérer la soumission du formulaire
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const messageDiv = form.querySelector('#message');
        messageDiv.innerHTML = '';
        
        const villeId = form.querySelector('#ville').value;
        const articleId = form.querySelector('#article').value;
        const quantite = form.querySelector('#quantite').value;
        
        // Validation
        if (!villeId || !articleId || !quantite) {
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
                quantite_demandee: parseFloat(quantite)
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

function loadFormDon(articles){
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
            <div class="col-md-4 mb-3">
                <label for="don-article" class="form-label">Article</label>
                <select class="form-select" id="don-article" required>
                    <option value="">Sélectionner un article</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="don-quantite" class="form-label">Quantité donnée</label>
                <input type="number" class="form-control" id="don-quantite" min="0.01" step="0.01" required placeholder="Entrez la quantité">
            </div>
            <div class="col-md-4 mb-3">
                <label for="don-date" class="form-label">Date de réception</label>
                <input type="date" class="form-control" id="don-date" required>
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

        // Validation
        if (!articleId || !quantite || !dateReception) {
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
                date_reception: dateReception
            };

            const result = await createDon(data);

            if (result.success) {
                messageDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Don enregistré avec succès!</div>';
                form.reset();
                form.querySelector('#don-date').value = today;
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