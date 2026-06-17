<?php
require_once __DIR__ . '/config.php';
$yt = loadData('youtube');
$tk = loadData('tiktok');
$ig = loadData('instagram');
$fb = loadData('facebook');
$gg = loadData('growth-global');

page_open('Vue Globale', 'home');
?>

<p class="page-header" style="margin-top:-12px;margin-bottom:28px;">
  <span style="color:var(--text-muted);font-size:14px;">Période : <?= htmlspecialchars($gg['meta']['period'] ?? '') ?> &mdash; Mis à jour le <?= htmlspecialchars($gg['meta']['updated'] ?? '') ?></span>
</p>

<!-- KPI globaux -->
<div class="section">
  <div class="section-title">KPI Globaux</div>
  <div class="kpi-grid">
    <div class="kpi-card">
      <div class="kpi-label">Vues Totales</div>
      <div class="kpi-value"><?= num($gg['total_views']['total']) ?></div>
      <div class="kpi-footer"><?= badge_pct($gg['total_views']['change_pct']) ?> vs mois préc.</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Nouveaux Abonnés</div>
      <div class="kpi-value"><?= num($gg['new_followers']['total']) ?></div>
      <div class="kpi-footer"><?= badge_pct($gg['new_followers']['change_pct']) ?> vs mois préc.</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Meilleure Plateforme</div>
      <div class="kpi-value"><?= htmlspecialchars($gg['best_platform']) ?></div>
      <div class="kpi-footer" style="color:var(--text-muted);">Croissance #1</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Meilleur Sujet</div>
      <div class="kpi-value" style="font-size:18px;"><?= htmlspecialchars($gg['best_subject']) ?></div>
      <div class="kpi-footer" style="color:var(--text-muted);">Toutes plateformes</div>
    </div>
  </div>
</div>

<!-- Platform KPIs -->
<div class="section">
  <div class="section-title">Plateformes</div>
  <div class="kpi-grid">
    <div class="kpi-card">
      <div class="kpi-label"><span class="platform-pill pill-yt">▶️ YouTube</span></div>
      <div class="kpi-value"><?= num($yt['subscribers']['total']) ?></div>
      <div class="kpi-footer"><?= badge_pct($yt['subscribers']['change_pct']) ?> abonnés</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label"><span class="platform-pill pill-tk">🎵 TikTok</span></div>
      <div class="kpi-value"><?= num($tk['followers']['total']) ?></div>
      <div class="kpi-footer"><?= badge_pct($tk['followers']['change_pct']) ?> followers</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label"><span class="platform-pill pill-ig">📸 Instagram</span></div>
      <div class="kpi-value"><?= num($ig['followers']['total']) ?></div>
      <div class="kpi-footer"><?= badge_pct($ig['followers']['change_pct']) ?> followers</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label"><span class="platform-pill pill-fb">👥 Facebook</span></div>
      <div class="kpi-value"><?= num($fb['fans']['total']) ?></div>
      <div class="kpi-footer"><?= badge_pct($fb['fans']['change_pct']) ?> fans</div>
    </div>
  </div>
</div>

<!-- Charts -->
<div class="section">
  <div class="section-title">Croissance mensuelle — Vues</div>
  <div class="chart-card">
    <h3>Vues par plateforme (6 derniers mois)</h3>
    <div style="height:260px;">
      <canvas id="chartGrowth"></canvas>
    </div>
  </div>
</div>

<div class="section">
  <div class="chart-grid">
    <div class="chart-card">
      <h3>Répartition des vues par plateforme</h3>
      <div style="height:220px;">
        <canvas id="chartShare"></canvas>
      </div>
    </div>
    <div class="chart-card">
      <h3>Top sujets — Vues cumulées</h3>
      <div style="height:220px;">
        <canvas id="chartSubjects"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Top Subjects table -->
<div class="section">
  <div class="section-title">Top Sujets Cross-Platform</div>
  <div class="table-card">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Sujet</th>
          <th>Vues totales</th>
          <th>Plateformes</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($gg['top_subjects'] as $i => $s): ?>
        <tr>
          <td style="color:var(--gold);font-weight:700;"><?= $i + 1 ?></td>
          <td><?= htmlspecialchars($s['subject']) ?></td>
          <td><?= num($s['total_views']) ?></td>
          <td><?= $s['platforms'] ?> / 4</td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Weekly Actions -->
<div class="section">
  <div class="section-title">Cycle Hebdomadaire</div>
  <div class="table-card">
    <table>
      <thead><tr><th>Jour</th><th>Tâche</th></tr></thead>
      <tbody>
        <?php foreach ($gg['weekly_actions'] as $a): ?>
        <tr>
          <td style="color:var(--gold);font-weight:600;white-space:nowrap;"><?= htmlspecialchars($a['day']) ?></td>
          <td><?= htmlspecialchars($a['task']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php page_close('./'); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const mg = <?= json_encode($gg['monthly_growth']) ?>;
  const labels = mg.labels;

  lineChart('chartGrowth', labels, [
    { label: 'YouTube',   data: mg.youtube,   borderColor: CN.yt, backgroundColor: 'rgba(255,85,85,0.08)', fill: true },
    { label: 'TikTok',   data: mg.tiktok,    borderColor: CN.tk, backgroundColor: 'rgba(105,201,208,0.08)', fill: true },
    { label: 'Instagram',data: mg.instagram, borderColor: CN.ig, backgroundColor: 'rgba(225,48,108,0.08)', fill: true },
    { label: 'Facebook', data: mg.facebook,  borderColor: CN.fb, backgroundColor: 'rgba(24,119,242,0.08)', fill: true },
  ]);

  const ps = <?= json_encode($gg['platform_share']) ?>;
  doughnutChart('chartShare', ps.labels, ps.data, ps.colors);

  const ts = <?= json_encode($gg['top_subjects']) ?>;
  hBarChart('chartSubjects',
    ts.map(s => s.subject),
    ts.map(s => s.total_views),
    CN.gold
  );
});
</script>
