<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config-api.php';
require_once __DIR__ . '/../auth/token-helper.php';

header('Content-Type: application/json');

$token = google_get_valid_token();
if (!$token) {
    echo json_encode(['ok' => false, 'error' => 'Token Google invalide. Reconnectez votre compte Google.']);
    exit;
}

// ── Période dynamique ──
$end   = isset($_GET['end'])   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['end'])   ? $_GET['end']   : date('Y-m-d');
$start = isset($_GET['start']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['start']) ? $_GET['start'] : date('Y-m-01');

// Sécurité : start ne peut pas être avant 2020 ni après end
if ($start > $end) $start = date('Y-m-01');
if ($start < '2020-01-01') $start = '2020-01-01';

// Label de la période
$period_label = isset($_GET['label']) ? htmlspecialchars($_GET['label']) : "Du $start au $end";

// ── 1. Stats chaîne (lifetime) ──
$channel = api_get('https://www.googleapis.com/youtube/v3/channels', [
    'part' => 'statistics,snippet', 'mine' => 'true', 'access_token' => $token,
]);
if (empty($channel['items'][0])) {
    echo json_encode(['ok' => false, 'error' => 'Impossible de récupérer la chaîne YouTube.']);
    exit;
}
$stats             = $channel['items'][0]['statistics'];
$channel_name      = $channel['items'][0]['snippet']['title'] ?? 'CompagnonNova';
$subscribers_total = (int)($stats['subscriberCount'] ?? 0);
$views_lifetime    = (int)($stats['viewCount']       ?? 0);
$videos_count      = (int)($stats['videoCount']      ?? 0);

// ── 2. Analytics sur la période choisie ──
$ana = api_get('https://youtubeanalytics.googleapis.com/v2/reports', [
    'ids'          => 'channel==MINE',
    'startDate'    => $start,
    'endDate'      => $end,
    'metrics'      => 'views,estimatedMinutesWatched,subscribersGained,subscribersLost,impressions,impressionsClickThroughRate,averageViewPercentage',
    'access_token' => $token,
]);
$r           = $ana['rows'][0] ?? [0,0,0,0,0,0,0];
$views       = (int)$r[0];
$watch_hours = round((int)$r[1] / 60, 1);
$subs_gained = (int)$r[2];
$subs_lost   = (int)$r[3];
$ctr         = round((float)($r[5] ?? 0) * 100, 2);
$retention   = round((float)($r[6] ?? 0), 1);

// ── 3. Top 10 vidéos ──
$search = api_get('https://www.googleapis.com/youtube/v3/search', [
    'part' => 'snippet', 'forMine' => 'true', 'type' => 'video',
    'order' => 'viewCount', 'maxResults' => '10', 'access_token' => $token,
]);
$top_videos = [];
foreach ($search['items'] ?? [] as $item) {
    $vid = $item['id']['videoId'] ?? '';
    if (!$vid) continue;
    $vstat = api_get('https://www.googleapis.com/youtube/v3/videos', [
        'part' => 'statistics', 'id' => $vid, 'access_token' => $token,
    ]);
    $vs = $vstat['items'][0]['statistics'] ?? [];
    $va = api_get('https://youtubeanalytics.googleapis.com/v2/reports', [
        'ids' => 'channel==MINE', 'startDate' => $start, 'endDate' => $end,
        'filters' => 'video==' . $vid,
        'metrics' => 'views,impressionsClickThroughRate,averageViewPercentage,estimatedMinutesWatched',
        'access_token' => $token,
    ]);
    $vr = $va['rows'][0] ?? [0, 0, 0, 0];
    $top_videos[] = [
        'title'      => $item['snippet']['title'] ?? '',
        'video_id'   => $vid,
        'url'        => 'https://www.youtube.com/watch?v=' . $vid,
        'views'      => (int)($vs['viewCount'] ?? 0),
        'likes'      => (int)($vs['likeCount']    ?? 0),
        'comments'   => (int)($vs['commentCount'] ?? 0),
        'ctr'        => round((float)($vr[1] ?? 0) * 100, 1),
        'retention'  => round((float)($vr[2] ?? 0), 1),
        'watch_hours'=> round((int)($vr[3] ?? 0) / 60, 1),
    ];
}
usort($top_videos, fn($a, $b) => $b['views'] <=> $a['views']);

// ── 4. Sources de trafic ──
$traffic = api_get('https://youtubeanalytics.googleapis.com/v2/reports', [
    'ids' => 'channel==MINE', 'startDate' => $start, 'endDate' => $end,
    'dimensions' => 'insightTrafficSourceType', 'metrics' => 'views',
    'sort' => '-views', 'maxResults' => '6', 'access_token' => $token,
]);
$src_labels = []; $src_data = [];
$src_names = [
    'YT_SEARCH'=>'Recherche YT','SUGGESTED_VIDEOS'=>'Suggestions',
    'EXT_URL'=>'Externe','SUBSCRIBER'=>'Abonnés','YT_CHANNEL'=>'Page chaîne',
    'NO_LINK_OTHER'=>'Autres','SHORTS'=>'Shorts','PLAYLIST'=>'Playlists',
    'HASHTAGS'=>'Hashtags','YT_OTHER_PAGE'=>'Autres pages YT',
];
if (!empty($traffic['rows'])) {
    $total_src = array_sum(array_column($traffic['rows'], 1));
    foreach ($traffic['rows'] as $r2) {
        $src_labels[] = $src_names[$r2[0]] ?? $r2[0];
        $src_data[]   = $total_src > 0 ? round($r2[1] / $total_src * 100, 1) : 0;
    }
}

// ── 5. Graphique : vues par jour sur la période ──
$daily = api_get('https://youtubeanalytics.googleapis.com/v2/reports', [
    'ids' => 'channel==MINE', 'startDate' => $start, 'endDate' => $end,
    'dimensions' => 'day', 'metrics' => 'views', 'sort' => 'day',
    'access_token' => $token,
]);
$chart_labels = [];
$chart_data   = [];
foreach ($daily['rows'] ?? [] as $row) {
    $chart_labels[] = date('d/m', strtotime($row[0]));
    $chart_data[]   = (int)$row[1];
}
// Si trop de points (> 60), agréger par semaine
if (count($chart_labels) > 60) {
    $wl = []; $wd = []; $tmp = 0; $wk = 1;
    foreach ($chart_data as $i => $v) {
        $tmp += $v;
        if (($i + 1) % 7 === 0 || $i === count($chart_data) - 1) {
            $wl[] = 'S' . $wk++;
            $wd[] = $tmp;
            $tmp  = 0;
        }
    }
    $chart_labels = $wl;
    $chart_data   = $wd;
}

// ── Recommandations ──
$recos = [];
if ($ctr > 0 && $ctr < 4)    $recos[] = ['priority'=>'haute',   'text'=>"CTR à {$ctr}% : trop bas. Tester miniatures avec visage + texte choc."];
elseif ($ctr >= 6)            $recos[] = ['priority'=>'basse',   'text'=>"CTR excellent à {$ctr}% : maintenir le style actuel."];
if ($retention > 0 && $retention < 35) $recos[] = ['priority'=>'haute','text'=>"Rétention à {$retention}% : améliorer les 30 premières secondes."];
if ($subs_gained > 5)         $recos[] = ['priority'=>'moyenne', 'text'=>"+{$subs_gained} abonnés sur la période : identifier les vidéos qui ont converti."];
if (!empty($src_labels) && stripos($src_labels[0], 'Short') !== false)
    $recos[] = ['priority'=>'moyenne', 'text'=>"Les Shorts dominent votre trafic. Créez des Shorts qui renvoient vers vos longues vidéos."];
if (empty($recos)) $recos[] = ['priority'=>'basse', 'text'=>'Continuez à publier régulièrement pour améliorer votre référencement.'];

$output = [
    'meta'             => ['period' => $period_label, 'start' => $start, 'end' => $end, 'updated' => date('Y-m-d H:i'), 'channel' => $channel_name],
    'channel'          => ['name' => $channel_name, 'videos_count' => $videos_count, 'views_lifetime' => $views_lifetime],
    'subscribers'      => ['total' => $subscribers_total, 'new' => $subs_gained, 'lost' => $subs_lost, 'net' => $subs_gained - $subs_lost, 'change_pct' => 0.0],
    'views'            => ['total' => $views, 'lifetime' => $views_lifetime, 'change_pct' => 0.0],
    'watch_time_hours' => ['total' => $watch_hours, 'change_pct' => 0.0],
    'ctr'              => ['value' => $ctr, 'change_pct' => 0.0],
    'retention'        => ['avg' => $retention, 'change_pct' => 0.0],
    'chart'            => ['labels' => $chart_labels, 'data' => $chart_data],
    'monthly_views'    => ['labels' => $chart_labels, 'data' => $chart_data],
    'top_videos'       => array_slice($top_videos, 0, 10),
    'traffic_sources'  => ['labels' => $src_labels, 'data' => $src_data],
    'recommendations'  => $recos,
];

file_put_contents(DATA_PATH . 'youtube.json', json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode([
    'ok'      => true,
    'message' => "YouTube syncé : {$views} vues sur la période \"{$period_label}\", {$subscribers_total} abonnés.",
    'updated' => date('Y-m-d H:i:s'),
    'data'    => $output,
]);
