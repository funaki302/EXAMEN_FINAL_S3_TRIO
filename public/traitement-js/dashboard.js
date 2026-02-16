function escapeHtml(value) {
  if (value === null || value === undefined) return '';
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function renderBadges(items) {
  if (!items || items.length === 0) {
    return '<span class="text-xs text-secondary">Aucune</span>';
  }

  return items
    .map((it) => {
      const nom = escapeHtml(it.nom_article);
      const qte = escapeHtml(it.quantite);
      return `<span class="badge bg-gradient-info me-1 mb-1">${nom}: ${qte}</span>`;
    })
    .join('');
}

function renderObjectifsTable(targetEl, villes, options) {
  const maxHeight = options && options.maxHeight ? options.maxHeight : 420;

  const rows = (villes || [])
    .map((v) => {
      const nomVille = escapeHtml(v.nom_ville);
      const region = escapeHtml(v.region);
      const besoinsHtml = renderBadges(v.besoins);
      const attribuesHtml = renderBadges(v.attribues);

      return `
        <tr>
          <td>
            <div class="d-flex px-2 py-1">
              <div class="d-flex flex-column justify-content-center">
                <h6 class="mb-0 text-sm">${nomVille}</h6>
                <p class="text-xs text-secondary mb-0">${region}</p>
              </div>
            </div>
          </td>
          <td>
            <div class="d-flex flex-wrap">${besoinsHtml}</div>
          </td>
          <td>
            <div class="d-flex flex-wrap">${attribuesHtml}</div>
          </td>
        </tr>
      `;
    })
    .join('');

  targetEl.innerHTML = `
    <div class="table-responsive" style="max-height: ${maxHeight}px; overflow-y: auto;">
      <table class="table align-items-center mb-0">
        <thead>
          <tr>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Ville</th>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Besoins</th>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Dons attribués</th>
          </tr>
        </thead>
        <tbody>
          ${rows}
        </tbody>
      </table>
    </div>
  `;
}

async function loadObjectifsDashboard(options) {
  const baseUrl = window.BASE_URL || '';
  const targetId = options && options.targetId ? options.targetId : 'objectifs-dashboard';
  const endpoint = options && options.endpoint ? options.endpoint : baseUrl + '/villes/objectifs-dashboard';
  const maxHeight = options && options.maxHeight ? options.maxHeight : 420;

  const targetEl = document.getElementById(targetId);
  if (!targetEl) return;

  targetEl.innerHTML = '<div class="p-3 text-sm text-secondary">Chargement...</div>';

  try {
    const res = await fetch(endpoint, { headers: { Accept: 'application/json' } });
    const json = await res.json();

    if (!json || json.success !== true) {
      const msg = json && json.message ? json.message : 'Erreur lors du chargement.';
      targetEl.innerHTML = `<div class="p-3 text-sm text-danger">${escapeHtml(msg)}</div>`;
      return;
    }

    renderObjectifsTable(targetEl, json.data, { maxHeight });
  } catch (e) {
    targetEl.innerHTML = '<div class="p-3 text-sm text-danger">Erreur réseau lors du chargement.</div>';
  }
}

document.addEventListener('DOMContentLoaded', function () {
  loadObjectifsDashboard({ targetId: 'objectifs-dashboard', maxHeight: 420 });
});
