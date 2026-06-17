<?php
require_once __DIR__ . '/../config.php';
$d = loadData('youtube');
page_open('YouTube Analytics', 'youtube');
?>

<!-- Infos chaîne -->
<div style="display:flex;gap:16px;flex-wrap:wrap;align-items:center;margin-bottom:20px;">
  <span style="color:var(--text-muted);font-size:13px;">📺 <?= (int)($d['channel']['videos_count'] ?? 0) ?> vidéos</span>
  <span style="color:var(--text-muted);font-size:13px;">👁 <?= num($d['channel']['views_lifetime'] ?? 0) ?> vues lifetime</span>
  <span style="color:var(--text-muted);font-size:13px;">🔄 Mis à jour : <?= htmlspecialchars($d['meta']['updated'] ?? 'jamais') ?></span>
</div>

<!-- Sélecteur de période -->
<div class="section">
  <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:16px;">
    <span style="color:var(--text-muted);font-size:13px;font-weight:600;margin-right:4px;">Période :</span>
    <button class="btn-period active" data-days="1"   data-label="Hier">24h</button>
    <button class="btn-period" data-days="7"  data-label="7 derniers jours">7 jours</button>
    <button class="btn-period" data-days="28" data-label="28 derniers jours">1 mois</button>
    <button class="btn-period" data-days="90" data-label="90 derniers jours">3 mois</button>
    <button class="btn-period" data-days="365" data-label="12 derniers mois">1 an</button>
    <span style="color:var(--navy-border);margin:0 4px;">|</span>
    <input type="date" id="dateStart" style="
      background:var(--navy-card);border:1px solid var(--navy-border);
      color:var(--white);padding:6px 10px;border-radius:6px;font-size:13px;">
    <span style="color:var(--text-muted);">au</span>
    <input type="date" id="dateEnd" style="
      background:var(--navy-card);border:1px solid var(--navy-border);
      color:var(--white);padding:6px 10px;border-radius:6px;font-size:13px;">
    <button onclick="syncCustom()" style="
      background:var(--gold);color:var(--navy);
      border:none;padding:7px 16px;border-radius:6px;
      font-size:13px;font-weight:700;cursor:pointer;">Appliquer</button>
    <span id="period-label" style="color:var(--gold);font-size:13px;font-weight:600;margin-left:8px;">
      <?= htmlspecialchars($d['meta']['period'] ?? '') ?>
    </span>
  </div>

  <!-- KPIs (mis à jour dynamiquement) -->
  <div class="kpi-grid" id="kpi-grid">
    <div class="kpi-card">
      <div class="kpi-label">Abonnés</div>
      <div class="kpi-value" id="k-subs"><?= num($d['subscribers']['total'] ?? 0) ?></div>
      <div class="kpi-footer">+<span id="k-subs-new"><?= $d['subscribers']['new'] ?? 0 ?></span> nouveaux</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Vues</div>
      <div class="kpi-value" id="k-views"><?= num($d['views']['total'] ?? 0) ?></div>
      <div class="kpi-footer" style="color:var(--text-muted);">Lifetime : <?= num($d['views']['lifetime'] ?? 0) ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Watch Time</div>
      <div class="kpi-value" id="k-watch"><?= number_format($d['watch_time_hours']['total'] ?? 0, 1) ?>h</div>
      <div class="kpi-footer" style="color:var(--text-muted);">Heures regardées</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">CTR Miniatures</div>
      <div class="kpi-value" id="k-ctr"><?= pct($d['ctr']['value'] ?? 0) ?></div>
      <div class="kpi-footer" style="color:var(--text-muted);">Taux de clic</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Rétention</div>
      <div class="kpi-value" id="k-ret"><?= pct($d['retention']['avg'] ?? 0) ?></div>
      <div class="kpi-footer" style="color:var(--text-muted);">% vidéo regardé</div>
    </div>
  </div>
</div>

<!-- Graphiques -->
<div class="section">
  <div class="chart-grid">
    <div class="chart-card">
      <h3 id="chart-title">Vues sur la période</h3>
      <div style="height:220px;"><canvas id="chartViews"></canvas></div>
    </div>
    <div class="chart-card">
      <h3>Sources de trafic</h3>
      <div style="height:220px;"><canvas id="chartTraffic"></canvas></div>
    </div>
  </div>
</div>

<!-- Top Vidéos -->
<div class="section">
  <div class="section-title" id="top-videos-title">Top Vidéos — <?= htmlspecialchars($d['meta']['period'] ?? 'Lifetime') ?></div>
  <div class="table-card" id="top-videos-table">
    <table>
      <thead>
        <tr><th>#</th><th>Titre</th><th>Vues</th><th>Likes</th><th>CTR</th><th>Rétention</th><th>Watch Time</th></tr>
      </thead>
      <tbody id="top-videos-body">
        <?php foreach ($d['top_videos'] ?? [] as $i => $v): ?>
        <tr>
          <td style="color:var(--gold);font-weight:700;"><?= $i+1 ?></td>
          <td><a href="<?= htmlspecialchars($v['url'] ?? '#') ?>" target="_blank" style="color:var(--white);"><?= htmlspecialchars($v['title']) ?></a></td>
          <td><?= num($v['views']) ?></td>
          <td><?= num($v['likes'] ?? 0) ?></td>
          <td><?= pct($v['ctr']) ?></td>
          <td><?= pct($v['retention']) ?><div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:<?= min(100,$v['retention']) ?>%"></div></div></td>
          <td><?= $v['watch_hours'] ?? 0 ?>h</td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($d['top_videos'])): ?>
        <tr><td colspan="7" style="color:var(--text-muted);text-align:center;padding:20px;">Synchronisez YouTube pour voir vos vidéos.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Recommandations -->
<div class="section">
  <div class="section-title">Recommandations IA</div>
  <div class="chart-card" id="recos">
    <ul class="reco-list">
      <?php foreach ($d['recommendations'] ?? [] as $r): ?>
      <li class="reco-item">
        <?php
        $cls   = ['haute'=>'badge-red','moyenne'=>'badge-orange','basse'=>'badge-green'][$r['priority']] ?? 'badge-green';
        $label = ['haute'=>'🔴 Haute','moyenne'=>'🟡 Moyenne','basse'=>'🟢 Basse'][$r['priority']] ?? $r['priority'];
        echo "<span class='badge $cls'>$label</span>";
        ?>
        <span><?= htmlspecialchars($r['text']) ?></span>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

<?php page_close(); ?>

<style>
.btn-period {
  background: var(--navy-card);
  border: 1px solid var(--navy-border);
  color: var(--text-muted);
  padding: 6px 14px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-period:hover { border-color: var(--gold); color: var(--gold); }
.btn-period.active { background: var(--gold-light); border-color: var(--gold); color: var(--gold); }
#loading-overlay {
  position:fixed;top:0;left:0;right:0;bottom:0;
  background:rgba(11,15,26,0.7);z-index:999;
  display:none;align-items:center;justify-content:center;
  font-size:18px;font-weight:700;color:var(--gold);
}
</style>

<div id="loading-overlay">⏳ Chargement des données YouTube...</div>

<script>
let chartViews = null;
let chartTraffic = null;

const today = new Date().toISOString().split('T')[0];
document.getElementById('dateEnd').value   = today;
document.getElementById('dateStart').value = new Date(Date.now() - 28*86400000).toISOString().split('T')[0];

// Init charts avec données existantes
document.addEventListener('DOMContentLoaded', function () {
  const cv = <?= json_encode($d['chart'] ?? $d['monthly_views'] ?? ['labels'=>[],'data'=>[]]) ?>;
  const tr = <?= json_encode($d['traffic_sources'] ?? ['labels'=>[],'data'=>[]]) ?>;
  initCharts(cv.labels, cv.data, tr.labels, tr.data);
});

function initCharts(vLabels, vData, tLabels, tData) {
  if (chartViews)   { chartViews.destroy();   chartViews = null; }
  if (chartTraffic) { chartTraffic.destroy(); chartTraffic = null; }
  if (vData && vData.some(v => v > 0)) {
    chartViews = lineChart('chartViews', vLabels, [{
      label: 'Vues', data: vData,
      borderColor: CN.yt, backgroundColor: 'rgba(255,85,85,0.1)', fill: true
    }]);
  }
  if (tData && tData.length > 0) {
    chartTraffic = doughnutChart('chartTraffic', tLabels, tData,
      ['#C9A84C','#2D6A4F','#69C9D0','#E1306C','#6B7A99','#1877F2']);
  }
}

function numFmt(n) {
  if (n >= 1000000) return (n/1000000).toFixed(1) + 'M';
  if (n >= 1000)    return (n/1000).toFixed(1) + 'k';
  return n.toLocaleString();
}
function pctFmt(n) { return n.toFixed(1).replace('.', ',') + '%'; }

async function syncPeriod(start, end, label) {
  document.getElementById('loading-overlay').style.display = 'flex';
  document.getElementById('period-label').textContent = '⏳ ' + label;

  const url = `/compagnonnova-dashboard/fetch/youtube.php?start=${start}&end=${end}&label=${encodeURIComponent(label)}`;
  try {
    const r = await fetch(url);
    const d = await r.json();
    if (!d.ok) { alert('Erreur : ' + d.error); return; }

    // Mise à jour KPIs
    const s = d.data;
    document.getElementById('k-subs').textContent     = numFmt(s.subscribers.total);
    document.getElementById('k-subs-new').textContent = s.subscribers.new;
    document.getElementById('k-views').textContent    = numFmt(s.views.total);
    document.getElementById('k-watch').textContent    = s.watch_time_hours.total + 'h';
    document.getElementById('k-ctr').textContent      = pctFmt(s.ctr.value);
    document.getElementById('k-ret').textContent      = pctFmt(s.retention.avg);
    document.getElementById('period-label').textContent = label;
    document.getElementById('chart-title').textContent  = 'Vues — ' + label;

    // Mise à jour graphiques
    initCharts(s.chart.labels, s.chart.data, s.traffic_sources.labels, s.traffic_sources.data);

    // Mise à jour titre + top vidéos
    const titleEl = document.getElementById('top-videos-title');
    if (titleEl) titleEl.textContent = `Top Vidéos — ${s.meta.period}`;
    const tbody = document.getElementById('top-videos-body');
    tbody.innerHTML = '';
    if (s.top_videos.length === 0) {
      tbody.innerHTML = '<tr><td colspan="7" style="color:var(--text-muted);text-align:center;padding:20px;">Aucune vidéo sur cette période.</td></tr>';
    } else {
      s.top_videos.forEach((v, i) => {
        const ret_w = Math.min(100, v.retention);
        tbody.innerHTML += `
          <tr>
            <td style="color:var(--gold);font-weight:700;">${i+1}</td>
            <td><a href="${v.url}" target="_blank" style="color:var(--white);">${v.title}</a></td>
            <td>${numFmt(v.views)}</td>
            <td>${numFmt(v.likes)}</td>
            <td>${pctFmt(v.ctr)}</td>
            <td>${pctFmt(v.retention)}<div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:${ret_w}%"></div></div></td>
            <td>${v.watch_hours}h</td>
          </tr>`;
      });
    }

    // Mise à jour recommandations
    const badgeCls = {haute:'badge-red',moyenne:'badge-orange',basse:'badge-green'};
    const badgeLbl = {haute:'🔴 Haute',moyenne:'🟡 Moyenne',basse:'🟢 Basse'};
    const recoEl = document.querySelector('#recos .reco-list');
    recoEl.innerHTML = '';
    s.recommendations.forEach(r => {
      recoEl.innerHTML += `<li class="reco-item"><span class="badge ${badgeCls[r.priority]}">${badgeLbl[r.priority]}</span><span>${r.text}</span></li>`;
    });

  } catch(e) {
    alert('Erreur réseau : ' + e.message);
  } finally {
    document.getElementById('loading-overlay').style.display = 'none';
  }
}

// Boutons prédéfinis
document.querySelectorAll('.btn-period').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.btn-period').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    const days  = parseInt(this.dataset.days);
    const label = this.dataset.label;
    const end   = today;
    const start = new Date(Date.now() - days * 86400000).toISOString().split('T')[0];
    document.getElementById('dateStart').value = start;
    document.getElementById('dateEnd').value   = end;
    syncPeriod(start, end, label);
  });
});

// Date personnalisée
function syncCustom() {
  const start = document.getElementById('dateStart').value;
  const end   = document.getElementById('dateEnd').value;
  if (!start || !end) { alert('Veuillez sélectionner les deux dates.'); return; }
  if (start > end)    { alert('La date de début doit être avant la date de fin.'); return; }
  document.querySelectorAll('.btn-period').forEach(b => b.classList.remove('active'));
  const label = `Du ${start} au ${end}`;
  syncPeriod(start, end, label);
}
</script>
