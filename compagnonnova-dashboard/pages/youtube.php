<?php
require_once __DIR__ . '/../config.php';
$d = loadData('youtube');
page_open('YouTube Analytics', 'youtube');
?>

<!-- Stats chaîne -->
<div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:8px;">
  <span style="color:var(--text-muted);font-size:13px;">
    📺 <?= (int)($d['channel']['videos_count'] ?? 0) ?> vidéos publiées
  </span>
  <span style="color:var(--text-muted);font-size:13px;">
    👁 <?= num($d['channel']['views_lifetime'] ?? 0) ?> vues au total (lifetime)
  </span>
  <span style="color:var(--text-muted);font-size:13px;">
    📅 Période : <?= htmlspecialchars($d['meta']['period'] ?? '') ?>
  </span>
</div>

<!-- KPIs -->
<div class="section">
  <div class="section-title">KPI — 28 derniers jours</div>
  <div class="kpi-grid">
    <div class="kpi-card">
      <div class="kpi-label">Abonnés</div>
      <div class="kpi-value"><?= num($d['subscribers']['total'] ?? 0) ?></div>
      <div class="kpi-footer">+<?= $d['subscribers']['new'] ?? 0 ?> nouveaux</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Vues (28j)</div>
      <div class="kpi-value"><?= num($d['views']['total'] ?? 0) ?></div>
      <div class="kpi-footer" style="color:var(--text-muted);">Lifetime : <?= num($d['views']['lifetime'] ?? 0) ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Watch Time</div>
      <div class="kpi-value"><?= number_format($d['watch_time_hours']['total'] ?? 0, 1) ?>h</div>
      <div class="kpi-footer" style="color:var(--text-muted);">28 derniers jours</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">CTR Miniatures</div>
      <div class="kpi-value"><?= pct($d['ctr']['value'] ?? 0) ?></div>
      <div class="kpi-footer" style="color:var(--text-muted);">Taux de clic</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Rétention moyenne</div>
      <div class="kpi-value"><?= pct($d['retention']['avg'] ?? 0) ?></div>
      <div class="kpi-footer" style="color:var(--text-muted);">% vidéo regardé</div>
    </div>
  </div>
</div>

<!-- Charts -->
<div class="section">
  <div class="chart-grid">
    <div class="chart-card">
      <h3>Vues mensuelles (6 mois)</h3>
      <div style="height:220px;"><canvas id="chartViews"></canvas></div>
    </div>
    <div class="chart-card">
      <h3>Sources de trafic (28j)</h3>
      <div style="height:220px;"><canvas id="chartTraffic"></canvas></div>
    </div>
  </div>
</div>

<!-- Top Vidéos -->
<div class="section">
  <div class="section-title">Top Vidéos — Toutes périodes</div>
  <div class="table-card">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Titre</th>
          <th>Vues</th>
          <th>Likes</th>
          <th>CTR</th>
          <th>Rétention</th>
          <th>Watch Time</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($d['top_videos'] ?? [] as $i => $v): ?>
        <tr>
          <td style="color:var(--gold);font-weight:700;"><?= $i+1 ?></td>
          <td>
            <a href="<?= htmlspecialchars($v['url'] ?? '#') ?>" target="_blank"
               style="color:var(--white);">
              <?= htmlspecialchars($v['title']) ?>
            </a>
          </td>
          <td><?= num($v['views']) ?></td>
          <td><?= num($v['likes'] ?? 0) ?></td>
          <td><?= pct($v['ctr']) ?></td>
          <td>
            <?= pct($v['retention']) ?>
            <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:<?= min(100,$v['retention']) ?>%"></div></div>
          </td>
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
  <div class="chart-card">
    <ul class="reco-list">
      <?php foreach ($d['recommendations'] ?? [] as $r): ?>
      <li class="reco-item"><?= badge_priority($r['priority']) ?><span><?= htmlspecialchars($r['text']) ?></span></li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

<!-- Bouton sync rapide -->
<div style="text-align:right;margin-top:-20px;margin-bottom:30px;">
  <a href="sync.php" style="
    background:var(--gold-light);color:var(--gold);
    border:1px solid var(--gold);padding:8px 18px;
    border-radius:8px;font-size:13px;font-weight:600;">
    🔄 Actualiser les données YouTube
  </a>
</div>

<?php page_close(); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const mv = <?= json_encode($d['monthly_views'] ?? ['labels'=>[],'data'=>[]]) ?>;
  if (mv.data.some(v => v > 0)) {
    lineChart('chartViews', mv.labels, [{ label: 'Vues', data: mv.data, borderColor: CN.yt, backgroundColor: 'rgba(255,85,85,0.1)', fill: true }]);
  }
  const tr = <?= json_encode($d['traffic_sources'] ?? ['labels'=>[],'data'=>[]]) ?>;
  if (tr.data.length > 0) {
    doughnutChart('chartTraffic', tr.labels, tr.data, ['#C9A84C','#2D6A4F','#69C9D0','#E1306C','#6B7A99','#1877F2']);
  }
});
</script>
