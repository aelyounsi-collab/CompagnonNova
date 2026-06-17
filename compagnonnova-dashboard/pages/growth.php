<?php
require_once __DIR__ . '/../config.php';
$d = loadData('growth-global');
page_open('Growth Lab', 'growth');
?>

<!-- Summary KPIs -->
<div class="section">
  <div class="section-title">Vue consolidée &mdash; <?= htmlspecialchars($d['meta']['period']) ?></div>
  <div class="kpi-grid">
    <div class="kpi-card">
      <div class="kpi-label">Vues Totales</div>
      <div class="kpi-value"><?= num($d['total_views']['total']) ?></div>
      <div class="kpi-footer"><?= badge_pct($d['total_views']['change_pct']) ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Nouveaux Followers</div>
      <div class="kpi-value"><?= num($d['new_followers']['total']) ?></div>
      <div class="kpi-footer"><?= badge_pct($d['new_followers']['change_pct']) ?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Meilleure Plateforme</div>
      <div class="kpi-value"><?= htmlspecialchars($d['best_platform']) ?></div>
      <div class="kpi-footer" style="color:var(--text-muted);">Croissance max</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Meilleur Sujet</div>
      <div class="kpi-value" style="font-size:17px;"><?= htmlspecialchars($d['best_subject']) ?></div>
      <div class="kpi-footer" style="color:var(--text-muted);">Cross-platform #1</div>
    </div>
  </div>
</div>

<!-- Charts -->
<div class="section">
  <div class="chart-grid">
    <div class="chart-card">
      <h3>Croissance comparée (vues)</h3>
      <div style="height:240px;"><canvas id="chartGrowth"></canvas></div>
    </div>
    <div class="chart-card">
      <h3>Répartition des vues</h3>
      <div style="height:240px;"><canvas id="chartShare"></canvas></div>
    </div>
  </div>
</div>

<!-- Experiments -->
<div class="section">
  <div class="section-title">Expérimentations en cours &amp; terminées</div>
  <div class="exp-grid">
    <?php foreach ($d['experiments'] as $e):
      $statusClass = $e['status'] === 'terminé' ? 'badge-termine' : 'badge-encours';
      $statusLabel = $e['status'] === 'terminé' ? '✅ Terminé' : '⏳ En cours';
    ?>
    <div class="exp-card">
      <div class="exp-id"><?= htmlspecialchars($e['id']) ?></div>
      <div class="exp-hypothesis"><?= htmlspecialchars($e['hypothesis']) ?></div>
      <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
      <?php if ($e['result']): ?>
      <div class="exp-result">📈 <?= htmlspecialchars($e['result']) ?></div>
      <?php else: ?>
      <div class="exp-result" style="color:var(--text-muted);">Résultat en attente...</div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Top Subjects -->
<div class="section">
  <div class="section-title">Top Sujets Cross-Platform</div>
  <div class="table-card">
    <table>
      <thead><tr><th>#</th><th>Sujet</th><th>Vues totales</th><th>Plateformes</th></tr></thead>
      <tbody>
        <?php foreach ($d['top_subjects'] as $i => $s): ?>
        <tr>
          <td style="color:var(--gold);font-weight:700;"><?= $i+1 ?></td>
          <td><?= htmlspecialchars($s['subject']) ?></td>
          <td><?= num($s['total_views']) ?></td>
          <td><?= $s['platforms'] ?> / 4</td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php page_close('../'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const mg = <?= json_encode($d['monthly_growth']) ?>;
  lineChart('chartGrowth', mg.labels, [
    { label: 'YouTube',   data: mg.youtube,   borderColor: CN.yt },
    { label: 'TikTok',   data: mg.tiktok,    borderColor: CN.tk },
    { label: 'Instagram',data: mg.instagram, borderColor: CN.ig },
    { label: 'Facebook', data: mg.facebook,  borderColor: CN.fb },
  ]);
  const ps = <?= json_encode($d['platform_share']) ?>;
  doughnutChart('chartShare', ps.labels, ps.data, ps.colors);
});
</script>
