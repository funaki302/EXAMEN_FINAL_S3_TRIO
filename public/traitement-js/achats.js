function escapeHtml(value) {
  if (value === null || value === undefined) return '';
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function formatNumber(value) {
  const n = Number(value);
  if (!Number.isFinite(n)) return escapeHtml(value);
  return n.toLocaleString('fr-FR', { maximumFractionDigits: 2 });
}

function money(value) {
  return formatNumber(value) + ' Ar';
}

let currentRows = [];
let selectedRow = null;
let modalInstance = null;

async function loadVilles() {
  const baseUrl = window.BASE_URL || '';
  const select = document.getElementById('select-ville');
  if (!select) return;

  try {
    const res = await fetch(baseUrl + '/api/getAll/villes', { headers: { Accept: 'application/json' } });
    const json = await res.json();
    if (!json || json.success !== true) return;

    const villes = json.data || [];
    const options = ['<option value="">Toutes les villes</option>'];
    villes.forEach((v) => {
      options.push(`<option value="${escapeHtml(v.id_ville)}">${escapeHtml(v.nom_ville)} (${escapeHtml(v.region)})</option>`);
    });
    select.innerHTML = options.join('');
  } catch (e) {
    // ignore
  }
}

async function loadSolde() {
  const baseUrl = window.BASE_URL || '';
  const target = document.getElementById('achats-solde');
  if (!target) return;

  target.textContent = 'Chargement...';

  try {
    const res = await fetch(baseUrl + '/achats/solde', { headers: { Accept: 'application/json' } });
    const json = await res.json();

    if (!json || json.success !== true) {
      target.textContent = 'Erreur solde.';
      return;
    }

    const solde = (json.data && json.data.solde) || 0;
    target.textContent = money(solde);
  } catch (e) {
    target.textContent = 'Erreur réseau.';
  }
}

function renderTable(rows) {
  const tableEl = document.getElementById('achats-table');
  if (!tableEl) return;

  if (!rows || rows.length === 0) {
    tableEl.innerHTML = '<div class="p-3 text-sm text-secondary">Aucun besoin restant.</div>';
    return;
  }

  const body = rows
    .map((r, idx) => {
      const ville = escapeHtml(r.nom_ville);
      const region = escapeHtml(r.region);
      const article = escapeHtml(r.nom_article);
      const categorie = escapeHtml(r.categorie);
      const reste = formatNumber(r.reste_a_combler);
      const prix = money(r.prix_unitaire);
      const montant = money(r.montant_restant);

      return `
        <tr>
          <td>
            <div class="d-flex flex-column px-3 py-2">
              <h6 class="mb-0 text-sm">${ville}</h6>
              <p class="text-xs text-secondary mb-0">${region}</p>
            </div>
          </td>
          <td>
            <div class="d-flex flex-column">
              <h6 class="mb-0 text-sm">${article}</h6>
              <p class="text-xs text-secondary mb-0">${categorie}</p>
            </div>
          </td>
          <td class="align-middle"><span class="text-sm font-weight-bold">${reste}</span></td>
          <td class="align-middle"><span class="text-sm font-weight-bold">${prix}</span></td>
          <td class="align-middle"><span class="text-sm font-weight-bold">${montant}</span></td>
          <td class="align-middle text-end pe-3">
            <button class="btn btn-sm btn-primary mb-0" data-action="acheter" data-idx="${idx}">Acheter</button>
          </td>
        </tr>
      `;
    })
    .join('');

  tableEl.innerHTML = `
    <table class="table align-items-center mb-0">
      <thead>
        <tr>
          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Ville</th>
          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Article</th>
          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Reste</th>
          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Prix unitaire</th>
          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Montant restant</th>
          <th class="opacity-7"></th>
        </tr>
      </thead>
      <tbody>
        ${body}
      </tbody>
    </table>
  `;

  tableEl.querySelectorAll('button[data-action="acheter"]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const idx = Number(btn.getAttribute('data-idx'));
      selectedRow = currentRows[idx];
      openModal(selectedRow);
    });
  });
}

async function loadBesoinsRestants() {
  const baseUrl = window.BASE_URL || '';
  const statusEl = document.getElementById('achats-status');
  const select = document.getElementById('select-ville');

  if (statusEl) {
    statusEl.className = 'text-sm text-secondary mb-3';
    statusEl.textContent = 'Chargement des besoins restants...';
  }

  const idVille = select && select.value ? select.value : '';
  const qs = idVille ? `?id_ville=${encodeURIComponent(idVille)}` : '';

  try {
    const res = await fetch(baseUrl + '/achats/besoins-restants' + qs, { headers: { Accept: 'application/json' } });
    const json = await res.json();

    if (!json || json.success !== true) {
      if (statusEl) {
        statusEl.className = 'text-sm text-danger mb-3';
        statusEl.textContent = json && json.message ? json.message : 'Erreur de chargement.';
      }
      renderTable([]);
      return;
    }

    currentRows = json.data || [];
    if (statusEl) {
      statusEl.className = 'text-sm text-secondary mb-3';
      statusEl.textContent = `Besoins restants: ${json.count || 0} lignes.`;
    }
    renderTable(currentRows);
  } catch (e) {
    if (statusEl) {
      statusEl.className = 'text-sm text-danger mb-3';
      statusEl.textContent = 'Erreur réseau.';
    }
    renderTable([]);
  }
}

function openModal(row) {
  const modalEl = document.getElementById('modal-achat');
  if (!modalEl) return;

  const infoEl = document.getElementById('modal-achat-info');
  const qEl = document.getElementById('input-quantite');
  const fEl = document.getElementById('input-frais');
  const resEl = document.getElementById('modal-achat-result');

  if (infoEl) {
    infoEl.innerHTML = `
      <div><strong>Ville:</strong> ${escapeHtml(row.nom_ville)} (${escapeHtml(row.region)})</div>
      <div><strong>Article:</strong> ${escapeHtml(row.nom_article)} (${escapeHtml(row.categorie)})</div>
      <div><strong>Reste à combler:</strong> ${formatNumber(row.reste_a_combler)}</div>
      <div><strong>Prix unitaire:</strong> ${money(row.prix_unitaire)}</div>
    `;
  }

  if (qEl) qEl.value = '';
  if (fEl) fEl.value = '';
  if (resEl) resEl.textContent = 'Renseigne quantité + frais puis clique Simuler.';

  if (!modalInstance) {
    modalInstance = new bootstrap.Modal(modalEl);
  }

  modalInstance.show();
}

async function simulateAchat() {
  const baseUrl = window.BASE_URL || '';
  const qEl = document.getElementById('input-quantite');
  const fEl = document.getElementById('input-frais');
  const resEl = document.getElementById('modal-achat-result');

  if (!selectedRow || !qEl || !fEl || !resEl) return;

  const quantite = Number(qEl.value);
  const frais = Number(fEl.value);

  resEl.textContent = 'Simulation...';

  try {
    const res = await fetch(baseUrl + '/achats/simulate', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({
        id_ville: selectedRow.id_ville,
        id_article: selectedRow.id_article,
        quantite_achetee: quantite,
        taux_frais_pourcent: frais,
      }),
    });

    const json = await res.json();
    if (!json || json.success !== true) {
      resEl.textContent = json && json.message ? json.message : 'Erreur simulation.';
      return;
    }

    const d = json.data || {};
    const montants = (d.achat && d.achat.montants) || {};

    resEl.innerHTML = `
      <div><strong>Montant HT:</strong> ${money(montants.montant_ht || 0)}</div>
      <div><strong>Frais:</strong> ${money(montants.montant_frais || 0)}</div>
      <div><strong>Montant TTC:</strong> ${money(montants.montant_ttc || 0)}</div>
      <hr class="horizontal dark my-2" />
      <div><strong>Solde actuel:</strong> ${money((d.argent && d.argent.solde_actuel) || 0)}</div>
      <div><strong>Solde après achat:</strong> ${money((d.argent && d.argent.solde_apres_achat) || 0)}</div>
      <hr class="horizontal dark my-2" />
      <div><strong>Dispatch (simulation):</strong> ${(d.dispatch && d.dispatch.distributions_creees) || 0} distributions, quantité attribuée ${formatNumber((d.dispatch && d.dispatch.quantite_attribuee_totale) || 0)}</div>
    `;
  } catch (e) {
    resEl.textContent = 'Erreur réseau.';
  }
}

async function validateAchat() {
  const baseUrl = window.BASE_URL || '';
  const qEl = document.getElementById('input-quantite');
  const fEl = document.getElementById('input-frais');
  const resEl = document.getElementById('modal-achat-result');

  if (!selectedRow || !qEl || !fEl || !resEl) return;

  const quantite = Number(qEl.value);
  const frais = Number(fEl.value);

  resEl.textContent = 'Validation...';

  try {
    const res = await fetch(baseUrl + '/achats/validate', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({
        id_ville: selectedRow.id_ville,
        id_article: selectedRow.id_article,
        quantite_achetee: quantite,
        taux_frais_pourcent: frais,
      }),
    });

    const json = await res.json();
    if (!json || json.success !== true) {
      resEl.textContent = json && json.message ? json.message : 'Erreur validation.';
      return;
    }

    const d = json.data || {};
    const montants = (d.achat && d.achat.montants) || {};

    resEl.innerHTML = `
      <div class="text-success"><strong>OK.</strong> Achat validé.</div>
      <div><strong>Montant TTC:</strong> ${money(montants.montant_ttc || 0)}</div>
      <div><strong>Dispatch (réel):</strong> ${(d.dispatch && d.dispatch.distributions_creees) || 0} distributions</div>
    `;

    await loadSolde();
    await loadBesoinsRestants();
  } catch (e) {
    resEl.textContent = 'Erreur réseau.';
  }
}

async function refreshAll() {
  await loadSolde();
  await loadBesoinsRestants();
}

document.addEventListener('DOMContentLoaded', async function () {
  await loadVilles();
  await refreshAll();

  const select = document.getElementById('select-ville');
  if (select) {
    select.addEventListener('change', async function () {
      await loadBesoinsRestants();
    });
  }

  const btnRefresh = document.getElementById('btn-achats-refresh');
  if (btnRefresh) {
    btnRefresh.addEventListener('click', async function () {
      await refreshAll();
    });
  }

  const btnSim = document.getElementById('btn-achat-simuler');
  if (btnSim) {
    btnSim.addEventListener('click', async function () {
      await simulateAchat();
    });
  }

  const btnVal = document.getElementById('btn-achat-valider');
  if (btnVal) {
    btnVal.addEventListener('click', async function () {
      await validateAchat();
    });
  }
});
