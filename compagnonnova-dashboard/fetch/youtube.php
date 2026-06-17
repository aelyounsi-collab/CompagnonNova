<?php
/**
 * Fetch YouTube Analytics -> met à jour data/youtube.json
 * Appelé depuis pages/sync.php
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config-api.php';
require_once __DIR__ . '/../auth/token-helper.php';

header('Content-Type: application/json');

$token = google_get_valid_token();
if (!$token) {
    echo json_encode(['ok' => false, 'error' => 'Token Google invalide ou expiré. Reconnectez votre compte Google.']);
    exit;
}

// ── Période : mois en cours ──
$start = date('Y-m-01');
$end   = date('Y-m-d');
$month = date('M Y', strtotime($start));

// ── 1. Infos chaîne (abonnés) ──
$channel = api_get('https://www.googleapis.com/youtube/v3/channels', [
    'part'        => 'statistics,snippet',
    'mine'        => 'true',
    'access_token'=> $token,
]);

if (empty($channel['items'][0])) {
    echo json_encode(['ok' => false, 'error' => 'Impossible de récupérer les infos de la chaîne YouTube.']);
    exit;
}

$stats = $channel['items'][0]['statistics'];
$subscribers_total = (int)($stats['subscriberCount'] ?? 0);

// ── 2. Analytics : vues, watch time, CTR, rétention, abonnés gagnés/perdus ──
$analytics = api_get('https://youtubeanalytics.googleapis.com/v2/reports', [
    'ids'         => 'channel==MINE',
    'startDate'   => $start,
    'endDate'     => $end,
    'metrics'     => 'views,estimatedMinutesWatched,subscribersGained,subscribersLost,impressions,impressionsClickThroughRate,averageViewPercentage',
    'access_token'=> $token,
]);

$row          = $analytics['rows'][0] ?? [0,0,0,0,0,0,0];
$views        = (int)$row[0];
$watch_min    = (int)$row[1];
$subs_gained  = (int)$row[2];
$subs_lost    = (int)$row[3];
$impressions  = (int)$row[4];
$ctr          = round((float)($row[5] ?? 0) * 100, 1);
$retention    = round((float)($row[6] ?? 0), 1);
$watch_hours  = round($watch_min / 60, 0);

// ── 3. Top 5 vidéos du mois ──
$search = api_get('https://www.googleapis.com/youtube/v3/search', [
    'part'        => 'snippet',
    'forMine'     => 'true',
    'type'        => 'video',
    'order'       => 'viewCount',
    'maxResults'  => '5',
    'publishedAfter' => $start . 'T00:00:00Z',
    'access_token'=> $token,
]);

$top_videos = [];
foreach ($search['items'] ?? [] as $item) {
    $vid = $item['id']['videoId'] ?? '';
    if (!$vid) continue;
    // Analytics par vidéo
    $va = api_get('https://youtubeanalytics.googleapis.com/v2/reports', [
        'ids'         => 'channel==MINE',
        'startDate'   => $start,
        'endDate'     => $end,
        'filters'     => 'video==' . $vid,
        'metrics'     => 'views,impressionsClickThroughRate,averageViewPercentage',
        'access_token'=> $token,
    ]);
    $vr = $va['rows'][0] ?? [0, 0, 0];
    $top_videos[] = [
        'title'     => $item['snippet']['title'] ?? '',
        'views'     => (int)$vr[0],
        'ctr'       => round((float)$vr[1] * 100, 1),
        'retention' => round((float)$vr[2], 1),
    ];
}

// ── 4. Sources de trafic ──
$traffic = api_get('https://youtubeanalytics.googleapis.com/v2/reports', [
    'ids'         => 'channel==MINE',
    'startDate'   => $start,
    'endDate'     => $end,
    'dimensions'  => 'insightTrafficSourceType',
    'metrics'     => 'views',
    'sort'        => '-views',
    'maxResults'  => '5',
    'access_token'=> $token,
]);

$src_labels = ['Recherche','Suggestions','Externe','Abonnés','Autres'];
$src_data   = [0, 0, 0, 0, 0];
if (!empty($traffic['rows'])) {
    $total_src = array_sum(array_column($traffic['rows'], 1));
    foreach ($traffic['rows'] as $i => $r) {
        if ($i < 5) $src_data[$i] = $total_src > 0 ? round($r[1] / $total_src * 100, 1) : 0;
    }
}

// ── 5. Croissance mensuelle (6 mois) ──
$monthly_labels = [];
$monthly_data   = [];
for ($i = 5; $i >= 0; $i--) {
    $ts    = strtotime("-$i months", strtotime($start));
    $ms    = date('Y-m-01', $ts);
    $me    = date('Y-m-t', $ts);
    $mv    = api_get('https://youtubeanalytics.googleapis.com/v2/reports', [
        'ids'         => 'channel==MINE',
        'startDate'   => $ms,
        'endDate'     => $me,
        'metrics'     => 'views',
        'access_token'=> $token,
    ]);
    $monthly_labels[] = date('M', $ts);
    $monthly_data[]   = (int)($mv['rows'][0][0] ?? 0);
}

// ── Lecture de l'ancien JSON pour calculer les variations ──
$old = loadData('youtube');
$old_subs  = $old['subscribers']['total'] ?? $subscribers_total;
$old_views = $old['views']['total']       ?? $views;
$old_wt    = $old['watch_time_hours']['total'] ?? $watch_hours;

$sub_change = $old_subs  > 0 ? round(($subscribers_total - $old_subs)  / $old_subs  * 100, 1) : 0;
$view_change= $old_views > 0 ? round(($views - $old_views) / $old_views * 100, 1) : 0;
$wt_change  = $old_wt    > 0 ? round(($watch_hours - $old_wt)  / $old_wt   * 100, 1) : 0;

// ── Recommandations dynamiques ──
$recos = [];
if ($ctr < 5)    $recos[] = ['priority'=>'haute',   'text'=>"CTR miniatures à {$ctr}% : en dessous de la moyenne (5%). Tester de nouvelles miniatures avec texte + émotion."];
if ($ctr >= 7)   $recos[] = ['priority'=>'basse',   'text'=>"CTR excellent à {$ctr}% : maintenir le format de miniatures actuel."];
if ($retention < 40) $recos[] = ['priority'=>'haute','text'=>"Rétention moyenne à {$retention}% : améliorer les hooks d'introduction (30 premières secondes)."];
if ($subs_gained > 50) $recos[] = ['priority'=>'basse','text'=>"+{$subs_gained} abonnés ce mois : identifier les vidéos qui ont converti et les reproduire."];
if (empty($recos)) $recos[] = ['priority'=>'basse','text'=>'Performances stables. Continuer le rythme de publication actuel.'];

// ── Construction du JSON final ──
$output = [
    'meta'            => ['period' => $month, 'updated' => date('Y-m-d')],
    'subscribers'     => ['total' => $subscribers_total, 'new' => $subs_gained, 'lost' => $subs_lost, 'net' => $subs_gained - $subs_lost, 'change_pct' => $sub_change],
    'views'           => ['total' => $views, 'change_pct' => $view_change],
    'watch_time_hours'=> ['total' => $watch_hours, 'change_pct' => $wt_change],
    'ctr'             => ['value' => $ctr, 'change_pct' => 0],
    'retention'       => ['avg'   => $retention, 'change_pct' => 0],
    'monthly_views'   => ['labels' => $monthly_labels, 'data' => $monthly_data],
    'top_videos'      => $top_videos,
    'traffic_sources' => ['labels' => $src_labels, 'data' => $src_data],
    'recommendations' => $recos,
];

file_put_contents(DATA_PATH . 'youtube.json', json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['ok' => true, 'message' => "YouTube synchronisé : {$views} vues, {$subscribers_total} abonnés.", 'updated' => date('Y-m-d H:i:s')]);
