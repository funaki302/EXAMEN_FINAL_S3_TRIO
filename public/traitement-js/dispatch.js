function escapeHtml(value) {
  if (value === null || value === undefined) return '';
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function formatDateTime(value) {
  if (!value) return '-';
  const d = new Date(value);
  if (Number.isNaN(d.getTime())) return escapeHtml(value);
  return d.toLocaleString('fr-FR', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  });
}

function clampPercent(value) {
  const n = Number(value);
  if (!Number.isFinite(n)) return 0;
  return Math.max(0, Math.min(100, n));
}

function renderBesoinsParArticle(rows) {
  const targetEl = document.getElementById('dispatch-besoins-par-article');
  if (!targetEl) return;

  const list = Array.isArray(rows) ? rows : [];
  if (list.length === 0) {
    targetEl.innerHTML = `
      <div class="col-12">
        <div class="text-sm text-secondary">Aucun besoin à afficher.</div>
      </div>
    `;
    return;
  }

  const groups = new Map();
  list.forEach((r) => {
    const idArticle = Number(r.id_article || 0) || 0;
    if (!groups.has(idArticle)) {
      groups.set(idArticle, {
        id_article: idArticle,
        nom_article: r.nom_article,
        categorie: r.categorie,
        rows: [],
      });
    }
    groups.get(idArticle).rows.push(r);
  });

  const articles = Array.from(groups.values());
  articles.sort((a, b) => {
    const ca = String(a.categorie || '');
    const cb = String(b.categorie || '');
    if (ca !== cb) return ca.localeCompare(cb, 'fr');
    return String(a.nom_article || '').localeCompare(String(b.nom_article || ''), 'fr');
  });

  const html = articles
    .map((g) => {
      const nomArticle = escapeHtml(g.nom_article);
      const categorie = escapeHtml(g.categorie);
      const gradient = getCategorieGradient(g.categorie);

      let totalBesoin = 0;
      let totalAttrib = 0;
      let totalReste = 0;
      g.rows.forEach((r) => {
        totalBesoin += Number(r.besoin_total || 0) || 0;
        totalAttrib += Number(r.attribue_total || 0) || 0;
        totalReste += Number(r.reste_a_combler || 0) || 0;
      });

      const pct = totalBesoin > 0 ? clampPercent((totalAttrib / totalBesoin) * 100) : 0;

      const innerRows = (g.rows || [])
        .slice()
        .sort((a, b) => {
          const da = new Date(a.date_premier_besoin || a.date_saisie || 0).getTime();
          const db = new Date(b.date_premier_besoin || b.date_saisie || 0).getTime();
          if (da === db) {
            return (Number(a.id_ville || 0) || 0) - (Number(b.id_ville || 0) || 0);
          }
          return da - db;
        })
        .map((r) => {
          const ville = escapeHtml(r.nom_ville);
          const region = escapeHtml(r.region);
          const dt = formatDateTime(r.date_premier_besoin);
          const besoin = Number(r.besoin_total || 0) || 0;
          const attrib = Number(r.attribue_total || 0) || 0;
          const reste = Number(r.reste_a_combler || 0) || 0;
          const p = besoin > 0 ? clampPercent((attrib / besoin) * 100) : 0;
          const ok = reste <= 0;

          return `
            <div class="d-flex align-items-center justify-content-between py-2" style="border-bottom: 1px solid rgba(0,0,0,0.05); gap: 12px;">
              <div style="min-width: 0;">
                <div class="d-flex align-items-center" style="gap: 10px;">
                  <span class="badge ${ok ? 'bg-gradient-success' : 'bg-gradient-dark'}" style="min-width: 24px;">${ok ? 'OK' : '...'}</span>
                  <div style="min-width:0;">
                    <h6 class="mb-0 text-sm" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${ville}</h6>
                    <p class="text-xs text-secondary mb-0" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${region} · ${dt}</p>
                  </div>
                </div>
              </div>

              <div class="text-end" style="flex: 0 0 auto; min-width: 190px;">
                <div class="d-flex justify-content-end" style="gap: 10px;">
                  <div>
                    <p class="text-xs text-secondary mb-0">Besoin</p>
                    <p class="text-sm font-weight-bold mb-0">${formatNumber(besoin)}</p>
                  </div>
                  <div>
                    <p class="text-xs text-secondary mb-0">Attribué</p>
                    <p class="text-sm font-weight-bold mb-0">${formatNumber(attrib)}</p>
                  </div>
                  <div>
                    <p class="text-xs text-secondary mb-0">Reste</p>
                    <p class="text-sm font-weight-bold mb-0">${formatNumber(reste)}</p>
                  </div>
                </div>
                <div class="progress progress-md mt-2" style="height: 6px;">
                  <div class="progress-bar ${ok ? 'bg-gradient-success' : gradient}" role="progressbar" aria-valuenow="${p.toFixed(2)}" aria-valuemin="0" aria-valuemax="100" style="width: ${p.toFixed(2)}%"></div>
                </div>
              </div>
            </div>
          `;
        })
        .join('');

      return `
        <div class="col-12 col-xl-6 mb-3">
          <div class="card h-100">
            <div class="card-header pb-0 p-3">
              <div class="d-flex align-items-start justify-content-between" style="gap: 12px;">
                <div style="min-width: 0;">
                  <h6 class="mb-0" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${nomArticle}</h6>
                  <p class="text-sm text-secondary mb-0">${categorie}</p>
                </div>
                <div class="text-end" style="flex: 0 0 auto;">
                  <div class="icon icon-shape ${gradient} shadow text-center border-radius-md">
                    <i class="ni ni-archive-2 text-lg opacity-10" aria-hidden="true"></i>
                  </div>
                </div>
              </div>

              <div class="mt-3">
                <div class="d-flex justify-content-between">
                  <span class="text-xs text-secondary">Satisfaction globale</span>
                  <span class="text-xs font-weight-bold">${pct.toFixed(2)}%</span>
                </div>
                <div class="progress progress-md" style="height: 6px;">
                  <div class="progress-bar ${gradient}" role="progressbar" aria-valuenow="${pct.toFixed(2)}" aria-valuemin="0" aria-valuemax="100" style="width: ${pct.toFixed(2)}%"></div>
                </div>
                <p class="text-xs text-secondary mb-0 mt-2">Total besoin: <span class="font-weight-bold">${formatNumber(totalBesoin)}</span> · Attribué: <span class="font-weight-bold">${formatNumber(totalAttrib)}</span> · Restant: <span class="font-weight-bold">${formatNumber(totalReste)}</span></p>
              </div>
            </div>

            <div class="card-body p-3" style="max-height: 360px; overflow: auto;">
              ${innerRows}
            </div>
          </div>
        </div>
      `;
    })
    .join('');

  targetEl.innerHTML = html;
}

function getDispatchUiSelection() {
  const algoEl = document.getElementById('select-dispatch-algo');
  const actionEl = document.getElementById('select-dispatch-action');
  const idModeEl = document.getElementById('select-dispatch-id-mode');

  return {
    algo: algoEl ? String(algoEl.value || 'dispatch') : 'dispatch',
    action: actionEl ? String(actionEl.value || 'simulate') : 'simulate',
    idMode: idModeEl ? Number(idModeEl.value || 2) : 2,
  };
}

function syncDispatchModeUi() {
  const { action } = getDispatchUiSelection();
  const wrapper = document.getElementById('dispatch-mode-bd-wrapper');
  if (!wrapper) return;
  wrapper.style.display = action === 'validate' ? '' : 'none';
}

async function executeDispatchFromUi() {
  const baseUrl = window.BASE_URL || '';
  const statusEl = document.getElementById('dispatch-status');
  const btn = document.getElementById('btn-dispatch-execute');

  if (!statusEl) return;

  const { algo, action, idMode } = getDispatchUiSelection();

  let endpoint = '';
  let statusLabel = '';
  if (algo === 'dispatch') {
    endpoint = action === 'validate' ? '/dispatch/validate' : '/dispatch/run';
    statusLabel = action === 'validate' ? 'Validation' : 'Simulation';
  } else if (algo === 'smallest') {
    endpoint = action === 'validate' ? '/dispatch/validate-smallest' : '/dispatch/run-smallest';
    statusLabel = action === 'validate' ? "Validation (petits besoins d'abord)" : "Simulation (petits besoins d'abord)";
  } else if (algo === 'proportionnel') {
    endpoint = action === 'validate' ? '/dispatch/validate-proportionnel' : '/dispatch/run-proportionnel';
    statusLabel = action === 'validate' ? 'Validation (proportion)' : 'Simulation (proportion)';
  } else {
    endpoint = action === 'validate' ? '/dispatch/validate' : '/dispatch/run';
    statusLabel = action === 'validate' ? 'Validation' : 'Simulation';
  }

  if (btn) btn.disabled = true;
  statusEl.className = 'text-sm text-secondary';
  statusEl.textContent = `${statusLabel} en cours...`;

  try {
    const headers = { Accept: 'application/json' };
    let body;

    if (action === 'validate') {
      headers['Content-Type'] = 'application/x-www-form-urlencoded;charset=UTF-8';
      body = new URLSearchParams({ id_mode: String(idMode || 2) });
    }

    const res = await fetch(baseUrl + endpoint, {
      method: 'POST',
      headers,
      body,
    });

    const json = await res.json();

    if (!json || json.success !== true) {
      const msg = json && json.message ? json.message : `Erreur lors de ${action === 'validate' ? 'la validation' : 'la simulation'}.`;
      statusEl.className = 'text-sm text-danger';
      statusEl.textContent = msg;
      return;
    }

    const info = json.data || {};
    statusEl.className = 'text-sm text-success';
    statusEl.textContent = `Dispatch terminé: ${info.distributions_creees || 0} distributions créées, quantité attribuée: ${formatNumber(info.quantite_attribuee_totale || 0)}.`;

    if (action === 'simulate') {
      const summaryEl = document.getElementById('dispatch-summary');
      if (summaryEl && info.summary_rows && Array.isArray(info.summary_rows)) {
        renderSummaryTable(summaryEl, info.summary_rows);
        renderBesoinsStats(info.summary_rows);
        renderBesoinsParArticle(info.summary_rows);
      }
    } else {
      await loadDispatchSummary();
    }
  } catch (e) {
    statusEl.className = 'text-sm text-danger';
    statusEl.textContent = `Erreur réseau lors de ${action === 'validate' ? 'la validation' : 'la simulation'}.`;
  } finally {
    if (btn) btn.disabled = false;
  }
}

async function runDispatchProportionnel() {
  const baseUrl = window.BASE_URL || '';
  const statusEl = document.getElementById('dispatch-status');
  const btn = document.getElementById('btn-dispatch-run-proportionnel');

  if (!statusEl) return;

  if (btn) btn.disabled = true;
  statusEl.className = 'text-sm text-secondary';
  statusEl.textContent = 'Simulation (proportion) en cours...';

  try {
    const res = await fetch(baseUrl + '/dispatch/run-proportionnel', {
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
    statusEl.textContent = `Dispatch (proportion) terminé: ${info.distributions_creees || 0} distributions créées, quantité attribuée: ${formatNumber(info.quantite_attribuee_totale || 0)}.`;

    const summaryEl = document.getElementById('dispatch-summary');
    if (summaryEl && info.summary_rows && Array.isArray(info.summary_rows)) {
      renderSummaryTable(summaryEl, info.summary_rows);
      renderBesoinsStats(info.summary_rows);
      renderBesoinsParArticle(info.summary_rows);
    }
  } catch (e) {
    statusEl.className = 'text-sm text-danger';
    statusEl.textContent = 'Erreur réseau lors de la simulation.';
  } finally {
    if (btn) btn.disabled = false;
  }
}

async function validateDispatchProportionnel() {
  const baseUrl = window.BASE_URL || '';
  const statusEl = document.getElementById('dispatch-status');
  const btn = document.getElementById('btn-dispatch-validate-proportionnel');

  if (!statusEl) return;

  if (btn) btn.disabled = true;
  statusEl.className = 'text-sm text-secondary';
  statusEl.textContent = 'Validation (proportion) en cours...';

  try {
    const res = await fetch(baseUrl + '/dispatch/validate-proportionnel', {
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
    statusEl.textContent = `Dispatch (proportion) validé: ${info.distributions_creees || 0} distributions créées, quantité attribuée: ${formatNumber(info.quantite_attribuee_totale || 0)}.`;

    await loadDispatchSummary();
  } catch (e) {
    statusEl.className = 'text-sm text-danger';
    statusEl.textContent = 'Erreur réseau lors de la validation.';
  } finally {
    if (btn) btn.disabled = false;
  }
}

function renderBesoinsStats(rows) {
  const elTotal = document.getElementById('dispatch-besoins-total');
  const elSatisfaits = document.getElementById('dispatch-besoins-satisfaits');
  const elNonSatisfaits = document.getElementById('dispatch-besoins-non-satisfaits');
  const elResteTotal = document.getElementById('dispatch-besoins-reste-total');
  if (!elTotal || !elSatisfaits || !elNonSatisfaits || !elResteTotal) return;

  const list = Array.isArray(rows) ? rows : [];
  const total = list.length;
  let satisfaits = 0;
  let nonSatisfaits = 0;
  let resteTotal = 0;

  list.forEach((r) => {
    const reste = Number(r.reste_a_combler || 0) || 0;
    if (reste <= 0) {
      satisfaits += 1;
    } else {
      nonSatisfaits += 1;
    }
    resteTotal += reste;
  });

  elTotal.textContent = String(total);
  elSatisfaits.textContent = String(satisfaits);
  elNonSatisfaits.textContent = String(nonSatisfaits);
  elResteTotal.textContent = formatNumber(resteTotal);
}

async function runDispatchSmallestNeeds() {
  const baseUrl = window.BASE_URL || '';
  const statusEl = document.getElementById('dispatch-status');
  const btn = document.getElementById('btn-dispatch-run-smallest');

  if (!statusEl) return;

  if (btn) btn.disabled = true;
  statusEl.className = 'text-sm text-secondary';
  statusEl.textContent = 'Simulation (petits besoins d\'abord) en cours...';

  try {
    const res = await fetch(baseUrl + '/dispatch/run-smallest', {
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
    statusEl.textContent = `Dispatch (petits besoins d'abord) terminé: ${info.distributions_creees || 0} distributions créées, quantité attribuée: ${formatNumber(info.quantite_attribuee_totale || 0)}.`;

    const summaryEl = document.getElementById('dispatch-summary');
    if (summaryEl && info.summary_rows && Array.isArray(info.summary_rows)) {
      renderSummaryTable(summaryEl, info.summary_rows);
      renderBesoinsStats(info.summary_rows);
      renderBesoinsParArticle(info.summary_rows);
    }
  } catch (e) {
    statusEl.className = 'text-sm text-danger';
    statusEl.textContent = 'Erreur réseau lors de la simulation.';
  } finally {
    if (btn) btn.disabled = false;
  }
}

async function validateDispatchSmallestNeeds() {
  const baseUrl = window.BASE_URL || '';
  const statusEl = document.getElementById('dispatch-status');
  const btn = document.getElementById('btn-dispatch-validate-smallest');

  if (!statusEl) return;

  if (btn) btn.disabled = true;
  statusEl.className = 'text-sm text-secondary';
  statusEl.textContent = 'Validation (petits besoins d\'abord) en cours...';

  try {
    const res = await fetch(baseUrl + '/dispatch/validate-smallest', {
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
    statusEl.textContent = `Dispatch (petits besoins d'abord) validé: ${info.distributions_creees || 0} distributions créées, quantité attribuée: ${formatNumber(info.quantite_attribuee_totale || 0)}.`;

    await loadDispatchSummary();
  } catch (e) {
    statusEl.className = 'text-sm text-danger';
    statusEl.textContent = 'Erreur réseau lors de la validation.';
  } finally {
    if (btn) btn.disabled = false;
  }
}

function money(value) {
  return formatNumber(value) + ' Ar';
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

function formatCompact(value) {
  const n = Number(value);
  if (!Number.isFinite(n)) return escapeHtml(value);
  return n.toLocaleString('fr-FR', {
    notation: 'compact',
    compactDisplay: 'short',
    maximumFractionDigits: 2,
  });
}

function getCategorieGradient(categorie) {
  if (categorie === 'Matériaux') return 'bg-gradient-info';
  if (categorie === 'Nature') return 'bg-gradient-success';
  if (categorie === 'Argent') return 'bg-gradient-warning';
  return 'bg-gradient-dark';
}

function renderRestantsCards(targetEl, rows) {
  if (!targetEl) return;

  if (!rows || rows.length === 0) {
    targetEl.innerHTML = `
      <div class="card" style="min-width: 260px;">
        <div class="card-body p-3">
          <div class="text-sm text-secondary">Aucun article restant.</div>
        </div>
      </div>
    `;
    return;
  }

  const html = rows
    .map((r) => {
      const nom = escapeHtml(r.nom_article);
      const cat = escapeHtml(r.categorie);
      const qteRaw = Number(r.quantite_restante || 0);
      const qte = qteRaw > 999999 ? formatCompact(qteRaw) : formatNumber(qteRaw);
      const prix = Number(r.prix_unitaire || 0);
      const montantValue = (qteRaw * prix) || 0;
      const montant = montantValue > 999999 ? (formatCompact(montantValue) + ' Ar') : money(montantValue);
      const gradient = getCategorieGradient(r.categorie);

      const isArgent = r.categorie === 'Argent' || String(r.nom_article || '').toLowerCase() === 'argent';
      const mainValue = isArgent ? montant : qte;
      const subRight = isArgent ? qte : montant;

      return `
        <div style="flex: 0 0 auto; width: 260px;">
          <div class="card h-100">
            <div class="card-body p-3" style="height: 120px; overflow: hidden;">
              <div class="d-flex align-items-start justify-content-between" style="gap: 12px;">
                <div class="numbers" style="min-width: 0;">
                  <p class="text-sm mb-0 text-capitalize font-weight-bold" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${nom}</p>
                  <h5 class="font-weight-bolder mb-0" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${mainValue}</h5>
                  <p class="mb-0" style="min-width: 0;">
                    <span class="text-sm text-secondary">${cat}</span>
                    <span class="text-xs text-secondary" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"> · ${subRight}</span>
                  </p>
                </div>
                <div class="text-end" style="flex: 0 0 auto;">
                  <div class="icon icon-shape ${gradient} shadow text-center border-radius-md">
                    <i class="ni ni-box-2 text-lg opacity-10" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      `;
    })
    .join('');

  targetEl.innerHTML = html;
}

async function loadDonsRestantsCards() {
  const baseUrl = window.BASE_URL || '';
  const cardsEl = document.getElementById('dispatch-restants-cards');
  if (!cardsEl) return;

  cardsEl.innerHTML = `
    <div class="card" style="min-width: 260px;">
      <div class="card-body p-3">
        <div class="text-sm text-secondary">Chargement des articles restants...</div>
      </div>
    </div>
  `;

  try {
    const res = await fetch(baseUrl + '/dispatch/dons-restants', { headers: { Accept: 'application/json' } });
    const json = await res.json();
    if (!json || json.success !== true) {
      const msg = json && json.message ? json.message : 'Erreur lors du chargement.';
      cardsEl.innerHTML = `
        <div class="card" style="min-width: 260px;">
          <div class="card-body p-3">
            <div class="text-sm text-danger">${escapeHtml(msg)}</div>
          </div>
        </div>
      `;
      return;
    }

    renderRestantsCards(cardsEl, json.data || []);
  } catch (e) {
    cardsEl.innerHTML = `
      <div class="card" style="min-width: 260px;">
        <div class="card-body p-3">
          <div class="text-sm text-danger">Erreur réseau lors du chargement.</div>
        </div>
      </div>
    `;
  }
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
    renderBesoinsStats(json.data);
    renderBesoinsParArticle(json.data);
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

    const summaryEl = document.getElementById('dispatch-summary');
    if (summaryEl && info.summary_rows && Array.isArray(info.summary_rows)) {
      renderSummaryTable(summaryEl, info.summary_rows);
      renderBesoinsStats(info.summary_rows);
    }
  } catch (e) {
    statusEl.className = 'text-sm text-danger';
    statusEl.textContent = 'Erreur réseau lors de la simulation.';
  } finally {
    if (btn) btn.disabled = false;
  }
}

document.addEventListener('DOMContentLoaded', function () {
  loadDonsRestantsCards();
  loadDispatchSummary();

  syncDispatchModeUi();

  const selectAction = document.getElementById('select-dispatch-action');
  if (selectAction) {
    selectAction.addEventListener('change', function () {
      syncDispatchModeUi();
    });
  }

  const btnExecute = document.getElementById('btn-dispatch-execute');
  if (btnExecute) {
    btnExecute.addEventListener('click', function () {
      executeDispatchFromUi();
    });
  }

  const btnRefresh = document.getElementById('btn-dispatch-refresh');
  if (btnRefresh) {
    btnRefresh.addEventListener('click', async function () {
      await loadDonsRestantsCards();
      await loadDispatchSummary();
    });
  }

  const btn = document.getElementById('btn-dispatch-run');
  if (btn) {
    btn.addEventListener('click', function () {
      runDispatch();
    });
  }

  const btnSmallest = document.getElementById('btn-dispatch-run-smallest');
  if (btnSmallest) {
    btnSmallest.addEventListener('click', function () {
      runDispatchSmallestNeeds();
    });
  }

  const btnValidate = document.getElementById('btn-dispatch-validate');
  if (btnValidate) {
    btnValidate.addEventListener('click', function () {
      validateDispatch();
    });
  }

  const btnValidateSmallest = document.getElementById('btn-dispatch-validate-smallest');
  if (btnValidateSmallest) {
    btnValidateSmallest.addEventListener('click', function () {
      validateDispatchSmallestNeeds();
    });
  }

  const btnRunProp = document.getElementById('btn-dispatch-run-proportionnel');
  if (btnRunProp) {
    btnRunProp.addEventListener('click', function () {
      runDispatchProportionnel();
    });
  }

  const btnValidateProp = document.getElementById('btn-dispatch-validate-proportionnel');
  if (btnValidateProp) {
    btnValidateProp.addEventListener('click', function () {
      validateDispatchProportionnel();
    });
  }
});
