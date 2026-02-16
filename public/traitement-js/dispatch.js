function escapeHtml(value) {
  if (value === null || value === undefined) return '';
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

async function validateDispatch() {
  const baseUrl = window.BASE_URL || '';
  const statusEl = document.getElementById('dispatch-status');
  const btn = document.getElementById('btn-dispatch-validate');

  if (!statusEl) return;

  if (btn) btn.disabled = true;
  statusEl.className = 'text-sm text-secondary';
  statusEl.textContent = 'Validation en cours...';

  try {
    const res = await fetch(baseUrl + '/dispatch/validate', {
      method: 'POST',
      headers: { Accept: 'application/json' },
    });
    const json = await res.json();

    if (!json || json.success !== true) {
      const msg = json && json.message ? json.message : 'Erreur lors de la validation.';
      statusEl.className = 'text-sm text-danger';
      statusEl.textContent = msg;
      return;
    }

    const info = json.data || {};
    statusEl.className = 'text-sm text-success';
    statusEl.textContent = `Dispatch validé: ${info.distributions_creees || 0} distributions créées, quantité attribuée: ${formatNumber(info.quantite_attribuee_totale || 0)}.`;

    await loadDispatchSummary();
  } catch (e) {
    statusEl.className = 'text-sm text-danger';
    statusEl.textContent = 'Erreur réseau lors de la validation.';
  } finally {
    if (btn) btn.disabled = false;
  }
}

function formatNumber(value) {
  const n = Number(value);
  if (!Number.isFinite(n)) return escapeHtml(value);
  return n.toLocaleString('fr-FR', { maximumFractionDigits: 2 });
}

function renderSummaryTable(targetEl, rows) {
  const htmlRows = (rows || [])
    .map((r) => {
      const ville = escapeHtml(r.nom_ville);
      const region = escapeHtml(r.region);
      const article = escapeHtml(r.nom_article);
      const categorie = escapeHtml(r.categorie);
      const besoin = formatNumber(r.besoin_total);
      const attrib = formatNumber(r.attribue_total);
      const reste = formatNumber(r.reste_a_combler);

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
          <td class="align-middle">
            <span class="text-sm font-weight-bold">${besoin}</span>
          </td>
          <td class="align-middle">
            <span class="text-sm font-weight-bold">${attrib}</span>
          </td>
          <td class="align-middle">
            <span class="text-sm font-weight-bold">${reste}</span>
          </td>
        </tr>
      `;
    })
    .join('');

  targetEl.innerHTML = `
    <table class="table align-items-center mb-0">
      <thead>
        <tr>
          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Ville</th>
          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Article</th>
          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Besoin total</th>
          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Don attribué</th>
          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Reste à combler</th>
        </tr>
      </thead>
      <tbody>
        ${htmlRows}
      </tbody>
    </table>
  `;
}

async function loadDispatchSummary() {
  const baseUrl = window.BASE_URL || '';
  const statusEl = document.getElementById('dispatch-status');
  const summaryEl = document.getElementById('dispatch-summary');

  if (!statusEl || !summaryEl) return;

  statusEl.className = 'text-sm text-secondary';
  statusEl.textContent = 'Chargement du résumé...';
  summaryEl.innerHTML = '';

  try {
    const res = await fetch(baseUrl + '/dispatch/summary', { headers: { Accept: 'application/json' } });
    const json = await res.json();

    if (!json || json.success !== true) {
      const msg = json && json.message ? json.message : 'Erreur lors du chargement.';
      statusEl.className = 'text-sm text-danger';
      statusEl.textContent = msg;
      return;
    }

    statusEl.className = 'text-sm text-secondary';
    statusEl.textContent = `Résumé chargé (${json.count || 0} lignes).`;

    renderSummaryTable(summaryEl, json.data);
  } catch (e) {
    statusEl.className = 'text-sm text-danger';
    statusEl.textContent = 'Erreur réseau lors du chargement.';
  }
}

async function runDispatch() {
  const baseUrl = window.BASE_URL || '';
  const statusEl = document.getElementById('dispatch-status');
  const btn = document.getElementById('btn-dispatch-run');

  if (!statusEl) return;

  if (btn) btn.disabled = true;
  statusEl.className = 'text-sm text-secondary';
  statusEl.textContent = 'Simulation en cours...';

  try {
    const res = await fetch(baseUrl + '/dispatch/run', {
      method: 'POST',
      headers: { Accept: 'application/json' },
    });
    const json = await res.json();

    if (!json || json.success !== true) {
      const msg = json && json.message ? json.message : 'Erreur lors de la simulation.';
      statusEl.className = 'text-sm text-danger';
      statusEl.textContent = msg;
      return;
    }

    const info = json.data || {};
    statusEl.className = 'text-sm text-success';
    statusEl.textContent = `Dispatch terminé: ${info.distributions_creees || 0} distributions créées, quantité attribuée: ${formatNumber(info.quantite_attribuee_totale || 0)}.`;

    await loadDispatchSummary();
  } catch (e) {
    statusEl.className = 'text-sm text-danger';
    statusEl.textContent = 'Erreur réseau lors de la simulation.';
  } finally {
    if (btn) btn.disabled = false;
  }
}

document.addEventListener('DOMContentLoaded', function () {
  loadDispatchSummary();

  const btn = document.getElementById('btn-dispatch-run');
  if (btn) {
    btn.addEventListener('click', function () {
      runDispatch();
    });
  }

  const btnValidate = document.getElementById('btn-dispatch-validate');
  if (btnValidate) {
    btnValidate.addEventListener('click', function () {
      validateDispatch();
    });
  }
});
