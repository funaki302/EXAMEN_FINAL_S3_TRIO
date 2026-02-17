function escapeHtml(value) {
  if (value === null || value === undefined) return '';
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function formatMoneyAriary(value) {
  const n = Number(value);
  if (!Number.isFinite(n)) return '0 Ar';
  return n.toLocaleString('fr-FR', { maximumFractionDigits: 0 }) + ' Ar';
}

function clampPercent(value) {
  const n = Number(value);
  if (!Number.isFinite(n)) return 0;
  return Math.max(0, Math.min(100, n));
}

function setBar(barEl, pct) {
  if (!barEl) return;
  const p = clampPercent(pct);
  barEl.style.width = p.toFixed(2) + '%';
  barEl.setAttribute('aria-valuenow', String(p));
}

async function loadDonsPourcentagesDashboard() {
  const baseUrl = window.BASE_URL || '';

  const elPctDistribue = document.getElementById('dash-pct-distribue');
  const elPctAttente = document.getElementById('dash-pct-attente');
  const elPctRestant = document.getElementById('dash-pct-restant');

  const barDistribue = document.getElementById('dash-bar-distribue');
  const barAttente = document.getElementById('dash-bar-attente');
  const barRestant = document.getElementById('dash-bar-restant');

  const elTotal = document.getElementById('dash-dons-total');
  const elDistribues = document.getElementById('dash-dons-distribues');
  const elAttente = document.getElementById('dash-dons-attente');

  const btnRefresh = document.getElementById('btn-dash-refresh-dons');
  if (btnRefresh) btnRefresh.disabled = true;

  try {
    const res = await fetch(baseUrl + '/api/dashboard/dons-pourcentages', { headers: { Accept: 'application/json' } });
    const json = await res.json();

    if (!json || json.success !== true) {
      const fallback = '0%';
      if (elPctDistribue) elPctDistribue.textContent = fallback;
      if (elPctAttente) elPctAttente.textContent = fallback;
      if (elPctRestant) elPctRestant.textContent = fallback;
      setBar(barDistribue, 0);
      setBar(barAttente, 0);
      setBar(barRestant, 0);
      return;
    }

    const d = json.data || {};
    const pctDistribue = clampPercent(d.pct_distribue || 0);
    const pctAttente = clampPercent(d.pct_en_attente || 0);
    const pctRestant = clampPercent(d.pct_restant || 0);

    if (elPctDistribue) elPctDistribue.textContent = pctDistribue.toFixed(2) + '%';
    if (elPctAttente) elPctAttente.textContent = pctAttente.toFixed(2) + '%';
    if (elPctRestant) elPctRestant.textContent = pctRestant.toFixed(2) + '%';

    setBar(barDistribue, pctDistribue);
    setBar(barAttente, pctAttente);
    setBar(barRestant, pctRestant);

    if (elTotal) elTotal.textContent = formatMoneyAriary(d.valeur_totale || 0);
    if (elDistribues) elDistribues.textContent = formatMoneyAriary(d.valeur_distribuee || 0);
    if (elAttente) elAttente.textContent = formatMoneyAriary(d.valeur_en_attente || 0);
  } catch (e) {
    setBar(barDistribue, 0);
    setBar(barAttente, 0);
    setBar(barRestant, 0);
  } finally {
    if (btnRefresh) btnRefresh.disabled = false;
  }
}

function renderBadges(items, emptyText) {
  if (!items || items.length === 0) {
    return `<p class="text-xs text-secondary mb-0">${emptyText || 'Aucun'}</p>`;
  }
  return items
    .map((it) => {
      const nom = escapeHtml(it.nom_article);
      const qte = escapeHtml(it.quantite);
      return `<span class="badge bg-light border text-secondary me-1 mb-1" style="font-weight:500;">${nom}: ${qte}</span>`;
    })
    .join('');
}

function loadVilles(villes) {
  const container = document.getElementById('objectifs-dashboard');
  if (!container) return;
  container.innerHTML = '';

  if (!Array.isArray(villes) || villes.length === 0) {
    container.innerHTML = `
      <div class="text-center py-4 text-secondary">
        <i class="fas fa-city fa-2x mb-2 d-block opacity-5"></i>
        <span class="text-sm">Aucune ville enregistrée</span>
      </div>`;
    return;
  }

  const list = document.createElement('div');
  list.classList.add('list-group');

  villes.forEach((ville, index) => {
    const nomVille = escapeHtml(ville.nom_ville);
    const region   = escapeHtml(ville.region);
    const itemId   = 'ville-detail-' + index;

    const item = document.createElement('div');
    item.classList.add('list-group-item', 'border-0', 'px-0', 'py-2');

    item.innerHTML = `
      <div class="d-flex align-items-center justify-content-between px-2 py-1 ville-header" style="cursor:pointer; border-radius:0.5rem; transition: background .2s;">
        <div class="d-flex align-items-center">
          <div class="icon icon-shape icon-xs border-radius-md bg-gradient-info d-flex align-items-center justify-content-center me-2 shadow-sm" style="background:linear-gradient(90deg,#e6f0ff,#dbeeff);">
            <i class="fas fa-map-marker-alt text-info" style="font-size:0.65rem;"></i>
          </div>
          <div>
            <h6 class="mb-0 text-sm">${nomVille}</h6>
            <p class="text-xs text-secondary mb-0">${region}</p>
          </div>
        </div>
        <div class="d-flex align-items-center">
          <a href="#" class="text-primary text-xs me-2 ville-voirlink" style="opacity:0.95;">Voir Plus</a>
          <i class="fas fa-chevron-down text-xs text-secondary ville-chevron" style="transition: transform .3s;"></i>
        </div>
      </div>
      <div class="ville-details mt-2 px-2" id="${itemId}" style="display:none; overflow:hidden;">
      </div>
    `;

    // Click on header row to toggle
    const header = item.querySelector('.ville-header');
    header.addEventListener('mouseenter', function() { this.style.background = '#f8f9fa'; });
    header.addEventListener('mouseleave', function() { this.style.background = 'transparent'; });
    header.addEventListener('click', function (e) {
      e.preventDefault();
      voirPlus(ville, itemId, item);
    });

    // Also handle the small "Voir Plus" link
    const voirLink = item.querySelector('.ville-voirlink');
    if (voirLink) {
      voirLink.addEventListener('click', function (e) { e.preventDefault(); voirPlus(ville, itemId, item); });
    }

    list.appendChild(item);
  });

  container.appendChild(list);
}

function voirPlus(ville, detailId, itemEl) {
  const detailDiv = document.getElementById(detailId);
  if (!detailDiv) return;

  const chevron = itemEl.querySelector('.ville-chevron');

  // If already open → collapse
  if (detailDiv.style.display !== 'none') {
    detailDiv.style.display = 'none';
    if (chevron) chevron.style.transform = 'rotate(0deg)';
    return;
  }

  // Build detail content: besoins on top, dons attribués below
  const besoins = Array.isArray(ville.besoins) ? ville.besoins : [];
  const attribues = Array.isArray(ville.attribues) ? ville.attribues : [];

  // Create a map of attribues by article name to sum quantities
  const attribMap = {};
  attribues.forEach(a => {
    const key = (a.nom_article || '').toString();
    const q = parseInt(a.quantite) || 0;
    attribMap[key] = (attribMap[key] || 0) + q;
  });

  // Render besoins with green check when satisfied
  const besoinsHtml = besoins.length === 0 ? `<p class="text-xs text-secondary mb-0">Aucun besoin</p>` : besoins.map(b => {
    const nom = escapeHtml(b.nom_article || '');
    const qte = parseInt(b.quantite) || 0;
    const assigned = attribMap[nom] || 0;
    const fulfilled = assigned >= qte && qte > 0;
    if (fulfilled) {
      return `<span class="badge bg-gradient-success text-white me-1 mb-1" style="font-weight:600;"><i class="fa fa-check-circle me-1"></i>${nom}: ${qte}</span>`;
    }
    return `<span class="badge bg-light border text-secondary me-1 mb-1">${nom}: ${qte}</span>`;
  }).join('');

  // Render attribues as subtle spans below
  const attribuesHtml = attribues.length === 0 ? `<p class="text-xs text-secondary mb-0">Aucun don attribué</p>` : attribues.map(a => {
    const nom = escapeHtml(a.nom_article || '');
    const qte = escapeHtml(a.quantite) || 0;
    return `<span class="badge bg-light border text-secondary me-1 mb-1">${nom}: ${qte}</span>`;
  }).join('');

  detailDiv.innerHTML = `
    <div class="row gx-3">
      <div class="col-md-6">
        <div class="border rounded p-3 h-100" style="border-color:#e9ecef; background:#fbfcfd;">
          <div class="d-flex align-items-center mb-2">
            <i class="fas fa-clipboard-list text-info me-2 text-xs"></i>
            <span class="text-xs text-uppercase font-weight-bolder text-secondary">Besoins</span>
          </div>
          <div class="d-flex flex-wrap">${besoinsHtml}</div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="border rounded p-3 h-100" style="border-color:#e9ecef; background:#fbfcfd;">
          <div class="d-flex align-items-center mb-2">
            <i class="fas fa-hand-holding-heart text-success me-2 text-xs"></i>
            <span class="text-xs text-uppercase font-weight-bolder text-secondary">Dons attribués</span>
          </div>
          <div class="d-flex flex-wrap">${attribuesHtml}</div>
        </div>
      </div>
    </div>
  `;

  detailDiv.style.display = 'block';
  if (chevron) chevron.style.transform = 'rotate(180deg)';
}

async function loadObjectifsDashboard() {
  const baseUrl  = window.BASE_URL || '';
  const endpoint = baseUrl + '/villes/objectifs-dashboard';
  const container = document.getElementById('objectifs-dashboard');
  if (!container) return;

  container.innerHTML = '<div class="p-3 text-sm text-secondary">Chargement...</div>';

  try {
    const res  = await fetch(endpoint, { headers: { Accept: 'application/json' } });
    const json = await res.json();

    if (!json || json.success !== true) {
      const msg = json && json.message ? json.message : 'Erreur lors du chargement.';
      container.innerHTML = `<div class="p-3 text-sm text-danger">${escapeHtml(msg)}</div>`;
      return;
    }

    loadVilles(json.data);
  } catch (e) {
    container.innerHTML = '<div class="p-3 text-sm text-danger">Erreur réseau lors du chargement.</div>';
  }
}

document.addEventListener('DOMContentLoaded', function () {
  loadObjectifsDashboard();

  loadDonsPourcentagesDashboard();

  const btnRefresh = document.getElementById('btn-dash-refresh-dons');
  if (btnRefresh) {
    btnRefresh.addEventListener('click', function () {
      loadDonsPourcentagesDashboard();
    });
  }
});