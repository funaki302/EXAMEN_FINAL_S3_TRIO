// Variables globales pour stocker les données
let allDonsSaisi = [];
let allDonsRestants = [];
let selectedArticleId = null;

window.addEventListener('load', async function () {
    try {
        // Charger les deux listes
        const [listeDonsRestants, listeDonsSaisi] = await Promise.all([
            getDonsRestants(),
            getAllDons()
        ]);
        
        allDonsRestants = listeDonsRestants;
        allDonsSaisi = listeDonsSaisi;
        
        renderDonsStats(listeDonsRestants);
        loadListeDonsRestants(listeDonsRestants);
        loadListeDonsSaisi(listeDonsSaisi);
        
        // Bouton reset filter
        const btnReset = document.getElementById('btn-reset-filter');
        if (btnReset) {
            btnReset.addEventListener('click', function() {
                selectedArticleId = null;
                this.style.display = 'none';
                document.getElementById('dons-saisi-filter-info').textContent = 'Tous les dons';
                
                // Retirer la classe active de toutes les lignes
                document.querySelectorAll('#dons-table-body tr').forEach(row => {
                    row.classList.remove('bg-gradient-light');
                });
                
                loadListeDonsSaisi(allDonsSaisi);
            });
        }
    } catch (e) {
        console.error('Erreur:', e);
        this.alert('Erreur chargement de la page :'+ (e && e.message ? e.message : String(e)));
    }
});

function renderDonsStats(listeDons){
    const totalRecuEl = document.querySelector('#dons-total-recu');
    const totalAttribueEl = document.querySelector('#dons-total-attribue');
    const totalNonAttribueEl = document.querySelector('#dons-total-non-attribue');
    if (!totalRecuEl || !totalAttribueEl || !totalNonAttribueEl) {
        return;
    }

    let totalRecu = 0;
    let totalAttribue = 0;
    let totalNonAttribue = 0;

    (listeDons || []).forEach(don => {
        const donnee = Number(don.quantite_donnee_totale) || 0;
        const attribuee = Number(don.quantite_attribuee_totale) || 0;
        const restante = Number(don.quantite_restante) || 0;
        totalRecu += donnee;
        totalAttribue += attribuee;
        totalNonAttribue += restante;
    });

    const nf = new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 2 });
    totalRecuEl.textContent = nf.format(totalRecu);
    totalAttribueEl.textContent = nf.format(totalAttribue);
    totalNonAttribueEl.textContent = nf.format(totalNonAttribue);
}

function loadListeDonsRestants(listeDons){
    const tbody = document.querySelector('#dons-table-body');
    tbody.innerHTML = '';

    if (listeDons.length === 0) {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td colspan="5" class="text-center text-secondary py-4">
            <i class="fas fa-inbox fa-2x mb-2 d-block opacity-5"></i>
            Aucun don enregistré pour le moment
        </td>`;
        tbody.appendChild(tr);
        return;
    }

    listeDons.forEach(don => {
        const tr = document.createElement('tr');
        tr.style.cursor = 'pointer';
        tr.dataset.idArticle = don.id_article;
        
        const donnee = parseInt(don.quantite_donnee_totale) || 0;
        const attribuee = parseInt(don.quantite_attribuee_totale) || 0;
        const restante = parseInt(don.quantite_restante) || 0;

        tr.innerHTML = `
            <td class="ps-3">
                <div class="d-flex flex-column">
                    <h6 class="mb-0 text-sm">${don.nom_article}</h6>
                </div>
            </td>
            <td>
                <span class="badge bg-gradient-secondary">${don.categorie}</span>
            </td>
            <td class="align-middle text-center">
                <span class="text-sm font-weight-bold">${donnee.toLocaleString('fr-FR')}</span>
            </td>
            <td class="align-middle text-center">
                <span class="text-sm font-weight-bold text-success">${attribuee.toLocaleString('fr-FR')}</span>
            </td>
            <td class="align-middle text-center">
                <span class="badge ${restante > 0 ? 'bg-gradient-warning' : 'bg-gradient-success'}">${restante.toLocaleString('fr-FR')}</span>
            </td>
        `;
        
        // Événement clic pour filtrer
        tr.addEventListener('click', function() {
            const articleId = this.dataset.idArticle;
            const articleNom = don.nom_article;
            
            selectedArticleId = articleId;
            
            // Mettre en évidence la ligne sélectionnée
            document.querySelectorAll('#dons-table-body tr').forEach(row => {
                row.classList.remove('bg-gradient-light');
            });
            this.classList.add('bg-gradient-light');
            
            // Afficher le bouton reset
            document.getElementById('btn-reset-filter').style.display = 'block';
            
            // Mettre à jour le texte du filtre
            document.getElementById('dons-saisi-filter-info').textContent = `Filtré par: ${articleNom}`;
            
            // Filtrer les dons saisis par article
            const filteredDons = allDonsSaisi.filter(d => d.id_article == articleId);
            loadListeDonsSaisi(filteredDons);
        });
        
        tbody.appendChild(tr);
    });
}

function loadListeDonsSaisi(listeDonSaisi) {
    const tbody = document.querySelector('#dons-saisi-table-body');
    tbody.innerHTML = '';
    
    // Mettre à jour le compteur
    const countBadge = document.getElementById('dons-saisi-count');
    if (countBadge) {
        countBadge.textContent = listeDonSaisi.length + (listeDonSaisi.length > 1 ? ' dons' : ' don');
    }

    if (listeDonSaisi.length === 0) {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td colspan="6" class="text-center text-secondary py-4">
            <i class="fas fa-inbox fa-2x mb-2 d-block opacity-5"></i>
            Aucun don trouvé
        </td>`;
        tbody.appendChild(tr);
        return;
    }

    listeDonSaisi.forEach((don, index) => {
        const tr = document.createElement('tr');
        const dateReception = don.date_reception ? new Date(don.date_reception).toLocaleDateString('fr-FR') : '-';

        tr.innerHTML = `
            <td class="ps-3">
                <p class="text-xs font-weight-bold mb-0">${index + 1}</p>
            </td>
            <td>
                <div class="d-flex flex-column">
                    <h6 class="mb-0 text-sm">${don.nom_article || 'Article #' + don.id_article}</h6>
                </div>
            </td>
            <td>
                <span class="badge bg-gradient-secondary">${don.categorie || '-'}</span>
            </td>
            <td class="align-middle text-center">
                <span class="badge bg-gradient-info">${parseFloat(don.quantite_donnee).toLocaleString('fr-FR')}</span>
            </td>
            <td class="align-middle text-center">
                <p class="text-xs text-secondary mb-0">${dateReception}</p>
            </td>
            <td class="text-center">
                <button class="btn btn-sm btn-outline-warning btn-edit-don mb-0" title="Modifier">
                    <i class="fas fa-edit"></i>
                </button>
            </td>
        `;
        
        const button = tr.querySelector('.btn-edit-don');
        button.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Supprimer tout formulaire existant
            const existingEditRow = document.querySelector('.edit-row');
            if (existingEditRow) {
                existingEditRow.remove();
            }

            // formulaire de modification
            const parentTR = this.closest('tr');
            const editRow = document.createElement('tr');
            editRow.classList.add('edit-row');

            editRow.innerHTML = `
                <td colspan="6" class="bg-gray-100">
                    <form class="d-flex align-items-center justify-content-between p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <label class="form-label text-xs mb-0">Quantité donnée</label>
                                <input type="number" name="quantite_donnee"
                                class="form-control form-control-sm" value="${don.quantite_donnee}" 
                                min="1" style="width: 150px;">
                            </div>
                            <div>
                                <label class="form-label text-xs mb-0">Date réception</label>
                                <input type="date" name="date_reception"
                                class="form-control form-control-sm" value="${don.date_reception ? don.date_reception.split(' ')[0] : ''}" 
                                style="width: 150px;">
                            </div>
                            <input type="hidden" name="id_don" value="${don.id_don}">
                            <input type="hidden" name="id_article" value="${don.id_article}">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-success mb-0">
                                <i class="fas fa-check me-1"></i>Valider
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary btn-cancel-edit mb-0">
                                <i class="fas fa-times me-1"></i>Annuler
                            </button>
                        </div>
                    </form>
                </td>
            `;

            parentTR.insertAdjacentElement('afterend', editRow);

            // Gestion de la validation du formulaire
            const form = editRow.querySelector('form');
            form.addEventListener('submit', async function (e) {
                e.preventDefault();

                try {
                    const formData = new FormData(this);
                    const data = {
                        id_don: formData.get('id_don'),
                        id_article: formData.get('id_article'),
                        quantite_donnee: formData.get('quantite_donnee'),
                        date_reception: formData.get('date_reception')
                    };
                    
                    const result = await updateDon(data);

                    if (result.success) {
                        location.reload(); 
                    } else {
                        alert('Erreur lors de la mise à jour: ' + result.message);
                    }
                } catch (error) {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue lors de la mise à jour du don.');
                }
            });

            // Gestion de l'annulation 
            const cancelButton = editRow.querySelector('.btn-cancel-edit');
            cancelButton.addEventListener('click', function () {
                editRow.remove();
            });
        });
        tbody.appendChild(tr);
    });
}
