<?php
require_once __DIR__ . '/../config.php';
$d = loadData('facebook');
page_open('Facebook Analytics', 'facebook');
?>

<div class="section">
  <div class="section-title">KPI du mois &mdash; <?= htmlspecialchars($d['meta']['period']) ?></div>
  <div class="kpi-grid">
    <div class="kpi-card">
      <div class="kpi-label">Fans</div>
      <div class="kpi-value"><?= num($d['fans']['total']) ?></div>
      <div class="kpi-footer"><?= badge_pct($d['fans']['change_pct']) ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Portée Organique</div>
      <div class="kpi-value"><?= num($d['organic_reach']['total']) ?></div>
      <div class="kpi-footer"><?= badge_pct($d['organic_reach']['change_pct']) ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Portée Virale</div>
      <div class="kpi-value"><?= num($d['viral_reach']['total']) ?></div>
      <div class="kpi-footer"><?= badge_pct($d['viral_reach']['change_pct']) ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Partages</div>
      <div class="kpi-value"><?= num($d['shares']['total']) ?></div>
      <div class="kpi-footer"><?= badge_pct($d['shares']['change_pct']) ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Commentaires</div>
      <div class="kpi-value"><?= num($d['comments']['total']) ?></div>
      <div class="kpi-footer"><?= badge_pct($d['comments']['change_pct']) ?></div>
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
      <h3>Répartition de la portée</h3>
      <div style="height:220px;"><canvas id="chartSplit"></canvas></div>
    </div>
  </div>
</div>

<div class="section">
  <div class="section-title">Top Posts</div>
  <div class="table-card">
    <table>
      <thead><tr><th>#</th><th>Titre</th><th>Portée</th><th>Partages</th><th>Commentaires</th></tr></thead>
      <tbody>
        <?php foreach ($d['top_posts'] as $i => $v): ?>
        <tr>
          <td style="color:var(--gold);font-weight:700;"><?= $i+1 ?></td>
          <td><?= htmlspecialchars($v['title']) ?></td>
          <td><?= num($v['reach']) ?></td>
          <td><?= num($v['shares']) ?></td>
          <td><?= num($v['comments']) ?></td>
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
  lineChart('chartReach', mr.labels, [{ label: 'Portée', data: mr.data, borderColor: CN.fb, backgroundColor: 'rgba(24,119,242,0.1)', fill: true }]);
  const rs = <?= json_encode($d['reach_split']) ?>;
  doughnutChart('chartSplit', rs.labels, rs.data, ['#1877F2','#69C9D0','#C9A84C']);
});
</script>
