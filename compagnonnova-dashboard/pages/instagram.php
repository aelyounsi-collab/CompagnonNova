<?php
require_once __DIR__ . '/../config.php';
$d = loadData('instagram');
page_open('Instagram Analytics', 'instagram');
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
      <div class="kpi-label">Portée</div>
      <div class="kpi-value"><?= num($d['reach']['total']) ?></div>
      <div class="kpi-footer"><?= badge_pct($d['reach']['change_pct']) ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Sauvegardes</div>
      <div class="kpi-value"><?= num($d['saves']['total']) ?></div>
      <div class="kpi-footer"><?= badge_pct($d['saves']['change_pct']) ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Partages</div>
      <div class="kpi-value"><?= num($d['shares']['total']) ?></div>
      <div class="kpi-footer"><?= badge_pct($d['shares']['change_pct']) ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Engagement</div>
      <div class="kpi-value"><?= pct($d['engagement_rate']['value']) ?></div>
      <div class="kpi-footer"><?= badge_pct($d['engagement_rate']['change_pct']) ?></div>
    </div>
  </div>
</div>

<div class="section">
  <div class="chart-grid">
    <div class="chart-card">
      <h3>Portée mensuelle (6 mois)</h3>
      <div style="height:220px;"><canvas id="chartReach"></canvas></div>
    </div>
    <div class="chart-card">
      <h3>Mix de contenu</h3>
      <div style="height:220px;"><canvas id="chartMix"></canvas></div>
    </div>
  </div>
</div>

<div class="section">
  <div class="section-title">Top Reels</div>
  <div class="table-card">
    <table>
      <thead><tr><th>#</th><th>Titre</th><th>Portée</th><th>Sauvegardes</th><th>Partages</th></tr></thead>
      <tbody>
        <?php foreach ($d['top_reels'] as $i => $v): ?>
        <tr>
          <td style="color:var(--gold);font-weight:700;"><?= $i+1 ?></td>
          <td><?= htmlspecialchars($v['title']) ?></td>
          <td><?= num($v['reach']) ?></td>
          <td><?= num($v['saves']) ?></td>
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
  const mr = <?= json_encode($d['monthly_reach']) ?>;
  lineChart('chartReach', mr.labels, [{ label: 'Portée', data: mr.data, borderColor: CN.ig, backgroundColor: 'rgba(225,48,108,0.1)', fill: true }]);
  const cm = <?= json_encode($d['content_mix']) ?>;
  doughnutChart('chartMix', cm.labels, cm.data, ['#E1306C','#C9A84C','#2D6A4F','#6B7A99']);
});
</script>
