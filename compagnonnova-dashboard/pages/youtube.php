<?php
require_once __DIR__ . '/../config.php';
$d = loadData('youtube');
page_open('YouTube Analytics', 'youtube');
?>

<!-- KPIs -->
<div class="section">
  <div class="section-title">KPI du mois &mdash; <?= htmlspecialchars($d['meta']['period']) ?></div>
  <div class="kpi-grid">
    <div class="kpi-card">
      <div class="kpi-label">Abonnés</div>
      <div class="kpi-value"><?= num($d['subscribers']['total']) ?></div>
      <div class="kpi-footer"><?= badge_pct($d['subscribers']['change_pct']) ?> +<?= $d['subscribers']['net'] ?> nets</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Vues</div>
      <div class="kpi-value"><?= num($d['views']['total']) ?></div>
      <div class="kpi-footer"><?= badge_pct($d['views']['change_pct']) ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Watch Time</div>
      <div class="kpi-value"><?= number_format($d['watch_time_hours']['total']) ?>h</div>
      <div class="kpi-footer"><?= badge_pct($d['watch_time_hours']['change_pct']) ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">CTR Miniatures</div>
      <div class="kpi-value"><?= pct($d['ctr']['value']) ?></div>
      <div class="kpi-footer"><?= badge_pct($d['ctr']['change_pct']) ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Rétention moyenne</div>
      <div class="kpi-value"><?= pct($d['retention']['avg']) ?></div>
      <div class="kpi-footer"><?= badge_pct($d['retention']['change_pct']) ?></div>
    </div>
  </div>
</div>

<!-- Charts -->
<div class="section">
  <div class="chart-grid">
    <div class="chart-card">
      <h3>Croissance des vues (6 mois)</h3>
      <div style="height:220px;"><canvas id="chartViews"></canvas></div>
    </div>
    <div class="chart-card">
      <h3>Sources de trafic</h3>
      <div style="height:220px;"><canvas id="chartTraffic"></canvas></div>
    </div>
  </div>
</div>

<!-- Top Videos -->
<div class="section">
  <div class="section-title">Top Vidéos du mois</div>
  <div class="table-card">
    <table>
      <thead>
        <tr><th>#</th><th>Titre</th><th>Vues</th><th>CTR</th><th>Rétention</th></tr>
      </thead>
      <tbody>
        <?php foreach ($d['top_videos'] as $i => $v): ?>
        <tr>
          <td style="color:var(--gold);font-weight:700;"><?= $i+1 ?></td>
          <td><?= htmlspecialchars($v['title']) ?></td>
          <td><?= num($v['views']) ?></td>
          <td><?= pct($v['ctr']) ?></td>
          <td>
            <?= pct($v['retention']) ?>
            <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:<?= $v['retention'] ?>%"></div></div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Recommandations -->
<div class="section">
  <div class="section-title">Recommandations IA</div>
  <div class="chart-card">
    <ul class="reco-list">
      <?php foreach ($d['recommendations'] as $r): ?>
      <li class="reco-item"><?= badge_priority($r['priority']) ?><span><?= htmlspecialchars($r['text']) ?></span></li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

<?php page_close('../'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const mv = <?= json_encode($d['monthly_views']) ?>;
  lineChart('chartViews', mv.labels, [{ label: 'Vues', data: mv.data, borderColor: CN.yt, backgroundColor: 'rgba(255,85,85,0.1)', fill: true }]);
  const tr = <?= json_encode($d['traffic_sources']) ?>;
  doughnutChart('chartTraffic', tr.labels, tr.data, ['#C9A84C','#2D6A4F','#69C9D0','#E1306C','#6B7A99']);
});
</script>
