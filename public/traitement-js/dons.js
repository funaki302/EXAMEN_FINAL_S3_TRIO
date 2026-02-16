window.addEventListener('load', async function () {
    try {
      const listeDons = await getAllDons();
      loadListeDons(listeDons);
    } catch (e) {
      console.error('Erreur:', e);
      this.alert('Erreur chargement de la page :'+ (e && e.message ? e.message : String(e)));
    }
});
function loadListeDons(listeDons){
    const tbody = document.querySelector('#dons-table-body');
    tbody.innerHTML = '';
    listeDons.forEach(don => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${don.id_don}</td>
            <td>${don.nom_article}</td>
            <td>${don.categorie}</td>
            <td>${don.prix_unitaire} Ar</td>
            <td>${don.quantite_donnee} Ar</td>
            <td>${new Date(don.date_reception).toLocaleDateString()}</td>
        `;
        tbody.appendChild(tr);
    });
}