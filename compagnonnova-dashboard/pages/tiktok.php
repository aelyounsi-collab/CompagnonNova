<?php
require_once __DIR__ . '/../config.php';
$d = loadData('tiktok');
page_open('TikTok Analytics', 'tiktok');
?>

<div class="section">
  <div class="section-title">KPI du mois &mdash; <?= htmlspecialchars($d['meta']['period']) ?></div>
  <div class="kpi-grid">
    <div class="kpi-card">
      <div class="kpi-label">Followers</div>
      <div class="kpi-value"><?= num($d['followers']['total']) ?></div>
      <div class="kpi-footer"><?= badge_pct($d['followers']['change_pct']) ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Vues</div>
      <div class="kpi-value"><?= num($d['views']['total']) ?></div>
      <div class="kpi-footer"><?= badge_pct($d['views']['change_pct']) ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Complétion</div>
      <div class="kpi-value"><?= pct($d['completion_rate']['avg']) ?></div>
      <div class="kpi-footer"><?= badge_pct($d['completion_rate']['change_pct']) ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Favoris</div>
      <div class="kpi-value"><?= num($d['favorites']['total']) ?></div>
      <div class="kpi-footer"><?= badge_pct($d['favorites']['change_pct']) ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Partages</div>
      <div class="kpi-value"><?= num($d['shares']['total']) ?></div>
      <div class="kpi-footer"><?= badge_pct($d['shares']['change_pct']) ?></div>
    </div>
  </div>
</div>

<div class="section">
  <div class="chart-grid">
    <div class="chart-card">
      <h3>Croissance des vues (6 mois)</h3>
      <div style="height:220px;"><canvas id="chartViews"></canvas></div>
    </div>
    <div class="chart-card">
      <h3>Performance des hooks (complétion %)</h3>
      <div style="height:220px;"><canvas id="chartHooks"></canvas></div>
    </div>
  </div>
</div>

<div class="section">
  <div class="section-title">Top Vidéos TikTok</div>
  <div class="table-card">
    <table>
      <thead><tr><th>#</th><th>Titre</th><th>Vues</th><th>Complétion</th><th>Partages</th></tr></thead>
      <tbody>
        <?php foreach ($d['top_videos'] as $i => $v): ?>
        <tr>
          <td style="color:var(--gold);font-weight:700;"><?= $i+1 ?></td>
          <td><?= htmlspecialchars($v['title']) ?></td>
          <td><?= num($v['views']) ?></td>
          <td>
            <?= pct($v['completion']) ?>
            <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:<?= $v['completion'] ?>%"></div></div>
          </td>
          <td><?= num($v['shares']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

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
  lineChart('chartViews', mv.labels, [{ label: 'Vues', data: mv.data, borderColor: CN.tk, backgroundColor: 'rgba(105,201,208,0.1)', fill: true }]);
  const hp = <?= json_encode($d['hooks_performance']) ?>;
  hBarChart('chartHooks', hp.map(h => h.hook.substring(0,30)+'...'), hp.map(h => h.avg_completion), CN.tk);
});
</script>
