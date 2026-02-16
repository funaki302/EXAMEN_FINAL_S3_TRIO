window.addEventListener('load', async function () {
    try {
      const listeDons = await getDonsRestants();
      loadListeDons(listeDons);
    } catch (e) {
      console.error('Erreur:', e);
      this.alert('Erreur chargement de la page :'+ (e && e.message ? e.message : String(e)));
    }
});
function loadListeDons(listeDons){
    const tbody = document.querySelector('#dons-table-body');
    tbody.innerHTML = '';

    // Update counter badge
    const countBadge = document.querySelector('#dons-count');
    if (countBadge) {
        countBadge.textContent = listeDons.length + (listeDons.length > 1 ? ' dons' : ' don');
    }

    if (listeDons.length === 0) {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td colspan="6" class="text-center text-secondary py-4">
            <i class="fas fa-inbox fa-2x mb-2 d-block opacity-5"></i>
            Aucun don enregistré pour le moment
        </td>`;
        tbody.appendChild(tr);
        return;
    }

    listeDons.forEach(don => {
        const tr = document.createElement('tr');
        const restante = parseInt(don.quantite_restante) || 0;
        const donnee = parseInt(don.quantite_donnee_totale) || 0;
        const attribuee = parseInt(don.quantite_attribuee_totale) || 0;
        const restanteBadge = parseInt(don.quantite_restante) || 0;

        tr.innerHTML = `
            <td class="ps-3">
                <p class="text-xs font-weight-bold mb-0 text-secondary">${don.id_article}</p>
            </td>
            <td>
                <div class="d-flex px-2 py-1">
                    <div class="d-flex flex-column justify-content-center">
                        <h6 class="mb-0 text-sm">${don.nom_article}</h6>
                    </div>
                </div>
            </td>
            <td>
                ${don.categorie}
            </td>
            <td class="align-middle text-center">
                <span class="text-sm font-weight-bold mb-0">${donnee}</span>
            </td>
            <td class="align-middle text-center">
                <span class="text-sm font-weight-bold mb-0">${attribuee}</span>
            </td>
            <td class="align-middle text-center">
                <span class="text-sm font-weight-bold mb-0">${restanteBadge}</span>
            </td>
        `;
        tbody.appendChild(tr);
    });
}