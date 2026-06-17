<?php
session_start();
require_once __DIR__ . '/../config.php';

$api_configured = file_exists(__DIR__ . '/../config-api.php');
if ($api_configured) {
    require_once __DIR__ . '/../config-api.php';
    require_once __DIR__ . '/../auth/token-helper.php';
    $google_ok = google_is_connected();
    $meta_ok   = meta_is_connected();
} else {
    $google_ok = false;
    $meta_ok   = false;
}

$msg      = htmlspecialchars($_GET['msg']  ?? '');
$msg_type = $_GET['type'] ?? 'error';

$yt = loadData('youtube');
$tk = loadData('tiktok');
$ig = loadData('instagram');
$fb = loadData('facebook');

page_open('Synchronisation', 'sync');
?>

<p style="color:var(--text-muted);font-size:14px;margin-top:-12px;margin-bottom:28px;">
  Récupèrez les vraies données de vos plateformes en un clic.
</p>

<?php if ($msg): ?>
<div style="
  background: <?= $msg_type === 'success' ? 'var(--green-light)' : 'var(--red-light)' ?>;
  border: 1px solid <?= $msg_type === 'success' ? '#2D6A4F' : '#C0392B' ?>;
  color: <?= $msg_type === 'success' ? '#4ADE80' : '#F87171' ?>;
  border-radius: var(--radius); padding:14px 18px; margin-bottom:24px; font-size:14px;">
  <?= $msg_type === 'success' ? '✅' : '⚠️' ?> <?= $msg ?>
</div>
<?php endif; ?>

<?php if (!$api_configured): ?>
<div style="background:rgba(230,126,34,0.15);border:1px solid #E67E22;border-radius:var(--radius);padding:20px;margin-bottom:28px;">
  <strong style="color:#FB923C;">⚠️ config-api.php non configuré</strong>
  <p style="color:var(--text-muted);margin-top:8px;font-size:14px;">
    Copiez <code>config-api.example.php</code> en <code>config-api.php</code> et renseignez vos credentials API.
  </p>
  <pre style="background:var(--navy);padding:12px;border-radius:6px;font-size:12px;margin-top:10px;color:#4ADE80;">cp config-api.example.php config-api.php</pre>
</div>
<?php endif; ?>

<!-- ── YOUTUBE ── -->
<div class="section">
  <div class="section-title">YouTube Analytics</div>
  <div class="chart-card" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
    <div>
      <div style="font-size:15px;font-weight:600;margin-bottom:4px;">
        ▶️ YouTube
        <?php if ($google_ok): ?>
          <span class="badge badge-green" style="margin-left:8px;">✓ Connecté</span>
        <?php else: ?>
          <span class="badge badge-down" style="margin-left:8px;">✕ Non connecté</span>
        <?php endif; ?>
      </div>
      <div style="color:var(--text-muted);font-size:13px;">
        Dernière sync : <?= htmlspecialchars($yt['meta']['updated'] ?? 'jamais') ?>
        &mdash; <?= num($yt['subscribers']['total'] ?? 0) ?> abonnés, <?= num($yt['views']['total'] ?? 0) ?> vues
      </div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <?php if (!$google_ok): ?>
      <a href="../auth/google.php" style="
        background:var(--gold);color:var(--navy);padding:10px 20px;
        border-radius:8px;font-weight:700;font-size:14px;text-decoration:none;
        display:inline-block;">&#128279; Connecter Google</a>
      <?php endif; ?>
      <?php if ($google_ok): ?>
      <button onclick="syncPlatform('youtube', this)" class="btn-sync">
        🔄 Synchroniser YouTube
      </button>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ── TIKTOK ── -->
<div class="section">
  <div class="section-title">TikTok — Import CSV</div>
  <div class="chart-card">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex-wrap:wrap;">
      <div>
        <div style="font-size:15px;font-weight:600;margin-bottom:6px;">
          🎵 TikTok
          <span class="badge badge-orange" style="margin-left:8px;">📥 Import CSV</span>
        </div>
        <div style="color:var(--text-muted);font-size:13px;line-height:1.6;">
          L'API TikTok est restreinte (vérification business requise).<br>
          Méthode : <strong style="color:var(--white);">TikTok Studio &rarr; Analytiques &rarr; Télécharger le rapport</strong><br>
          Déposez le fichier CSV dans <code>data/tiktok-export.csv</code> puis cliquez Importer.
        </div>
        <div style="margin-top:10px;color:var(--text-muted);font-size:13px;">
          Dernière sync : <?= htmlspecialchars($tk['meta']['updated'] ?? 'jamais') ?>
          &mdash; <?= num($tk['followers']['total'] ?? 0) ?> followers, <?= num($tk['views']['total'] ?? 0) ?> vues
        </div>
      </div>
      <div style="display:flex;gap:10px;align-items:flex-start;">
        <label style="
          background:var(--gold);color:var(--navy);padding:10px 20px;
          border-radius:8px;font-weight:700;font-size:14px;cursor:pointer;white-space:nowrap;">
          📂 Choisir CSV
          <input type="file" accept=".csv" style="display:none;" onchange="uploadTikTokCSV(this)">
        </label>
        <button onclick="syncPlatform('tiktok-csv', this)" class="btn-sync">
          🔄 Importer CSV
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ── INSTAGRAM + FACEBOOK ── -->
<div class="section">
  <div class="section-title">Instagram + Facebook (Meta Graph API)</div>
  <div class="chart-card" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
    <div>
      <div style="font-size:15px;font-weight:600;margin-bottom:4px;">
        📸 Instagram &amp; 👥 Facebook
        <?php if ($meta_ok): ?>
          <span class="badge badge-green" style="margin-left:8px;">✓ Token configuré</span>
        <?php else: ?>
          <span class="badge badge-down" style="margin-left:8px;">✕ Token manquant</span>
        <?php endif; ?>
      </div>
      <div style="color:var(--text-muted);font-size:13px;">
        Instagram : <?= num($ig['followers']['total'] ?? 0) ?> followers, portée <?= num($ig['reach']['total'] ?? 0) ?><br>
        Facebook : <?= num($fb['fans']['total'] ?? 0) ?> fans, portée <?= num($fb['organic_reach']['total'] ?? 0) ?><br>
        Dernière sync : <?= htmlspecialchars($ig['meta']['updated'] ?? 'jamais') ?>
      </div>
      <?php if (!$meta_ok && $api_configured): ?>
      <div style="margin-top:10px;font-size:13px;color:#FB923C;">
        → Renseignez <code>META_ACCESS_TOKEN</code>, <code>INSTAGRAM_ACCOUNT_ID</code> et <code>FACEBOOK_PAGE_ID</code> dans <code>config-api.php</code>
      </div>
      <?php endif; ?>
    </div>
    <?php if ($meta_ok): ?>
    <button onclick="syncPlatform('instagram', this)" class="btn-sync">
      🔄 Synchroniser Meta
    </button>
    <?php endif; ?>
  </div>
</div>

<!-- ── TOUT SYNCHRONISER ── -->
<?php if ($google_ok || $meta_ok): ?>
<div class="section">
  <div class="chart-card" style="text-align:center;padding:28px;">
    <div style="font-size:16px;font-weight:700;margin-bottom:8px;">Tout synchroniser + consolider</div>
    <div style="color:var(--text-muted);font-size:14px;margin-bottom:20px;">Lance toutes les syncs disponibles puis recalcule la vue Growth Global.</div>
    <button onclick="syncAll()" style="
      background:var(--gold);color:var(--navy);
      border:none;padding:14px 36px;border-radius:10px;
      font-size:16px;font-weight:700;cursor:pointer;
      transition:opacity 0.2s;">
      🚀 Synchroniser tout
    </button>
  </div>
</div>
<?php endif; ?>

<!-- LOG -->
<div class="section">
  <div class="section-title">Journal de synchronisation</div>
  <div id="sync-log" class="chart-card" style="min-height:80px;font-family:monospace;font-size:13px;color:var(--text-muted);">
    En attente d'une synchronisation...
  </div>
</div>

<?php page_close('../'); ?>

<style>
.btn-sync {
  background: var(--navy-border);
  color: var(--white);
  border: 1px solid var(--navy-border);
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: background 0.2s;
}
.btn-sync:hover { background: var(--gold-light); }
.btn-sync:disabled { opacity: 0.5; cursor: wait; }
</style>

<script>
const log = document.getElementById('sync-log');

function addLog(msg, ok = true) {
  const line = document.createElement('div');
  line.style.color = ok ? '#4ADE80' : '#F87171';
  line.style.marginBottom = '4px';
  line.textContent = '[' + new Date().toLocaleTimeString() + '] ' + msg;
  if (log.textContent.includes('En attente')) log.textContent = '';
  log.appendChild(line);
  log.scrollTop = log.scrollHeight;
}

async function syncPlatform(platform, btn) {
  if (btn) { btn.disabled = true; btn.textContent = '⏳ En cours...'; }
  addLog('Synchronisation ' + platform + '...');
  try {
    const r = await fetch('../fetch/' + platform + '.php');
    const d = await r.json();
    addLog(d.message || d.error, d.ok);
    if (d.ok) setTimeout(() => location.reload(), 1500);
  } catch(e) {
    addLog('Erreur réseau : ' + e.message, false);
  }
  if (btn) { btn.disabled = false; btn.textContent = '🔄 Synchroniser'; }
}

async function syncAll() {
  const platforms = [];
  <?php if ($google_ok): ?>platforms.push('youtube');<?php endif; ?>
  <?php if ($meta_ok): ?>platforms.push('instagram');<?php endif; ?>

  for (const p of platforms) {
    await syncPlatform(p, null);
  }
  // Consolider growth-global
  addLog('Consolidation Growth Global...');
  const r = await fetch('../fetch/growth-global.php');
  const d = await r.json();
  addLog(d.message || d.error, d.ok);
  if (d.ok) setTimeout(() => location.reload(), 1500);
}

function uploadTikTokCSV(input) {
  const file = input.files[0];
  if (!file) return;
  addLog('Fichier sélectionné : ' + file.name + '. Cliquez Importer CSV pour continuer.');
  // Copie via FormData -> endpoint dédié
  const fd = new FormData();
  fd.append('csv', file);
  fetch('../fetch/tiktok-upload.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => addLog(d.message || d.error, d.ok))
    .catch(e => addLog('Erreur : ' + e.message, false));
}
</script>
