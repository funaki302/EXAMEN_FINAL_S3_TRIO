window.addEventListener('load', async function () {
    try {
      const besoins = await getAllBesoins();
      loadListeBesoins(besoins);
      updateStats(besoins);
    } catch (e) {
      console.error('Erreur:', e);
      this.alert('Erreur chargement de la page :'+ (e && e.message ? e.message : String(e)));
    }
});

function updateStats(besoins) {
    // Total besoins
    document.getElementById('stat-total-besoins').textContent = besoins.length;
    document.getElementById('besoins-count').textContent = besoins.length + ' besoins';

    // Quantité totale
    const quantiteTotale = besoins.reduce((sum, b) => sum + parseFloat(b.quantite_demandee || 0), 0);
    document.getElementById('stat-quantite-totale').textContent = quantiteTotale.toLocaleString('fr-FR');

    // Villes uniques
    const villesUniques = [...new Set(besoins.map(b => b.id_ville))];
    document.getElementById('stat-villes').textContent = villesUniques.length;

    // Valeur totale
    const valeurTotale = besoins.reduce((sum, b) => {
        const prix = parseFloat(b.prix_unitaire || 0);
        const qte = parseFloat(b.quantite_demandee || 0);
        return sum + (prix * qte);
    }, 0);
    document.getElementById('stat-valeur-totale').textContent = valeurTotale.toLocaleString('fr-FR') + ' Ar';
}

function loadListeBesoins(besoins) {
    const tableBody = document.querySelector('#besoins-table-body');

    if (!tableBody) {
        console.error('Le tableau des besoins n\'a pas été trouvé!');
        return;
    }

    tableBody.innerHTML = '';

    besoins.forEach((besoin, index) => {
        const row = document.createElement('tr');
        const valeur = parseFloat(besoin.prix_unitaire || 0) * parseFloat(besoin.quantite_demandee || 0);
        const dateFormatted = besoin.date_saisie ? new Date(besoin.date_saisie).toLocaleDateString('fr-FR') : '-';

        row.innerHTML = `
            <td class="ps-3">
                <p class="text-xs font-weight-bold mb-0">${index + 1}</p>
            </td>
            <td>
                <div class="d-flex flex-column">
                    <h6 class="mb-0 text-sm">${besoin.nom_ville}</h6>
                    <p class="text-xs text-secondary mb-0">${besoin.region || ''}</p>
                </div>
            </td>
            <td>
                <div class="d-flex flex-column">
                    <h6 class="mb-0 text-sm">${besoin.nom_article}</h6>
                    <p class="text-xs text-secondary mb-0">${besoin.categorie || ''}</p>
                </div>
            </td>
            <td class="text-center">
                <span class="badge bg-gradient-info">${parseFloat(besoin.quantite_demandee).toLocaleString('fr-FR')}</span>
            </td>
            <td class="text-center">
                <p class="text-xs font-weight-bold mb-0">${parseFloat(besoin.prix_unitaire).toLocaleString('fr-FR')} Ar</p>
            </td>
            <td class="text-center">
                <p class="text-xs font-weight-bold mb-0 text-success">${valeur.toLocaleString('fr-FR')} Ar</p>
            </td>
            <td class="text-center">
                <p class="text-xs text-secondary mb-0">${dateFormatted}</p>
            </td>
            <td class="text-center">
                <button class="btn btn-sm btn-outline-warning btn-edit-besoin mb-0" >
                    Modifier
                </button>
            </td>
        `;

        const button = row.querySelector('.btn-edit-besoin');
        button.addEventListener('click', function (e) {
            e.preventDefault();
            
            // Supprimer tout formulaire existant
            const existingEditRow = document.querySelector('.edit-row');
            if (existingEditRow) {
                existingEditRow.remove();
            }

            // Préparer la date au format YYYY-MM-DD pour l'input date
            const dateSaisieValue = besoin.date_saisie ? besoin.date_saisie.split(' ')[0] : '';
            const currentMode = besoin.id_mode || 1;

            // formulaire de modification
            const parentTR = this.closest('tr');
            const editRow = document.createElement('tr');
            editRow.classList.add('edit-row');

            editRow.innerHTML = `
                <td colspan="8" class="bg-gray-100">
                    <form class="d-flex align-items-center justify-content-between p-3">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div>
                                <label class="form-label text-xs mb-0">Quantité demandée</label>
                                <input type="number" name="quantite_demandee"
                                class="form-control form-control-sm" value="${besoin.quantite_demandee}" 
                                min="1" style="width: 120px;">
                            </div>
                            <div>
                                <label class="form-label text-xs mb-0">Date de saisie</label>
                                <input type="date" name="date_saisie"
                                class="form-control form-control-sm" value="${dateSaisieValue}" 
                                style="width: 150px;">
                            </div>
                            <div>
                                <label class="form-label text-xs mb-0">Mode</label>
                                <select name="id_mode" class="form-select form-select-sm" style="width: 130px;">
                                    <option value="1" ${currentMode == 1 ? 'selected' : ''}>Origine</option>
                                    <option value="2" ${currentMode == 2 ? 'selected' : ''}>Teste</option>
                                </select>
                            </div>
                            <input type="hidden" name="id_besoin" value="${besoin.id_besoin}">
                            <input type="hidden" name="id_article" value="${besoin.id_article}">
                            <input type="hidden" name="id_ville" value="${besoin.id_ville}">
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
                        id_besoin: formData.get('id_besoin'),
                        id_article: formData.get('id_article'),
                        id_ville: formData.get('id_ville'),
                        quantite_demandee: formData.get('quantite_demandee'),
                        date_saisie: formData.get('date_saisie'),
                        id_mode: formData.get('id_mode')
                    };
                    const result = await updateBesoin(data);

                    if (result.success) {
                        location.reload(); 
                    } else {
                        alert('Erreur lors de la mise à jour: ' + result.message);
                    }
                } catch (error) {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue lors de la mise à jour du besoin.');
                }
            });

            // Gestion de l'annulation 
            const cancelButton = editRow.querySelector('.btn-cancel-edit');
            cancelButton.addEventListener('click', function () {
                editRow.remove();
            });
        });
        tableBody.appendChild(row);
    });
}