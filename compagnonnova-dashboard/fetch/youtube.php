<?php
/**
 * Fetch YouTube Analytics -> met à jour data/youtube.json
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config-api.php';
require_once __DIR__ . '/../auth/token-helper.php';

header('Content-Type: application/json');

$token = google_get_valid_token();
if (!$token) {
    echo json_encode(['ok' => false, 'error' => 'Token Google invalide. Reconnectez votre compte Google.']);
    exit;
}

$start = date('Y-m-01');
$end   = date('Y-m-d');
$month = date('M Y', strtotime($start));

// ── 1. Infos chaîne (abonnés + stats globales) ──
$channel = api_get('https://www.googleapis.com/youtube/v3/channels', [
    'part'         => 'statistics,snippet',
    'mine'         => 'true',
    'access_token' => $token,
]);

if (empty($channel['items'][0])) {
    echo json_encode(['ok' => false, 'error' => 'Impossible de récupérer les infos de la chaîne YouTube.']);
    exit;
}

$stats             = $channel['items'][0]['statistics'];
$channel_name      = $channel['items'][0]['snippet']['title'] ?? 'CompagnonNova';
$subscribers_total = (int)($stats['subscriberCount'] ?? 0);
$views_lifetime    = (int)($stats['viewCount']       ?? 0);
$videos_count      = (int)($stats['videoCount']      ?? 0);

// ── 2. Analytics du mois en cours ──
$analytics = api_get('https://youtubeanalytics.googleapis.com/v2/reports', [
    'ids'          => 'channel==MINE',
    'startDate'    => $start,
    'endDate'      => $end,
    'metrics'      => 'views,estimatedMinutesWatched,subscribersGained,subscribersLost,impressions,impressionsClickThroughRate,averageViewPercentage',
    'access_token' => $token,
]);

$row         = $analytics['rows'][0] ?? [0,0,0,0,0,0,0];
$views_month = (int)$row[0];
$watch_min   = (int)$row[1];
$subs_gained = (int)$row[2];
$subs_lost   = (int)$row[3];
$ctr         = round((float)($row[5] ?? 0) * 100, 1);
$retention   = round((float)($row[6] ?? 0), 1);
$watch_hours = round($watch_min / 60, 1);

// ── 3. Analytics 28 derniers jours (pour la vue principale) ──
$start_28 = date('Y-m-d', strtotime('-28 days'));
$analytics_28 = api_get('https://youtubeanalytics.googleapis.com/v2/reports', [
    'ids'          => 'channel==MINE',
    'startDate'    => $start_28,
    'endDate'      => $end,
    'metrics'      => 'views,estimatedMinutesWatched,subscribersGained,subscribersLost,impressionsClickThroughRate,averageViewPercentage',
    'access_token' => $token,
]);
$row28        = $analytics_28['rows'][0] ?? [0,0,0,0,0,0];
$views_28     = (int)$row28[0];
$watch_min_28 = (int)$row28[1];
$subs_28      = (int)$row28[2];
$ctr_28       = round((float)($row28[4] ?? 0) * 100, 1);
$retention_28 = round((float)($row28[5] ?? 0), 1);
$watch_hours_28 = round($watch_min_28 / 60, 1);

// ── 4. Top 10 vidéos (toutes périodes, triées par vues) ──
$search = api_get('https://www.googleapis.com/youtube/v3/search', [
    'part'         => 'snippet',
    'forMine'      => 'true',
    'type'         => 'video',
    'order'        => 'viewCount',
    'maxResults'   => '10',
    'access_token' => $token,
]);

$top_videos = [];
foreach ($search['items'] ?? [] as $item) {
    $vid = $item['id']['videoId'] ?? '';
    if (!$vid) continue;
    // Stats de la vidéo (lifetime)
    $vstat = api_get('https://www.googleapis.com/youtube/v3/videos', [
        'part'         => 'statistics',
        'id'           => $vid,
        'access_token' => $token,
    ]);
    $vs = $vstat['items'][0]['statistics'] ?? [];
    // Analytics de la vidéo
    $va = api_get('https://youtubeanalytics.googleapis.com/v2/reports', [
        'ids'          => 'channel==MINE',
        'startDate'    => '2020-01-01',
        'endDate'      => $end,
        'filters'      => 'video==' . $vid,
        'metrics'      => 'views,impressionsClickThroughRate,averageViewPercentage,estimatedMinutesWatched',
        'access_token' => $token,
    ]);
    $vr = $va['rows'][0] ?? [0, 0, 0, 0];
    $top_videos[] = [
        'title'      => $item['snippet']['title'] ?? '',
        'video_id'   => $vid,
        'url'        => 'https://www.youtube.com/watch?v=' . $vid,
        'thumbnail'  => $item['snippet']['thumbnails']['medium']['url'] ?? '',
        'views'      => (int)($vs['viewCount'] ?? $vr[0]),
        'likes'      => (int)($vs['likeCount'] ?? 0),
        'comments'   => (int)($vs['commentCount'] ?? 0),
        'ctr'        => round((float)$vr[1] * 100, 1),
        'retention'  => round((float)$vr[2], 1),
        'watch_hours'=> round((int)$vr[3] / 60, 1),
    ];
}

// Tri par vues
usort($top_videos, fn($a, $b) => $b['views'] <=> $a['views']);

// ── 5. Sources de trafic (28 jours) ──
$traffic = api_get('https://youtubeanalytics.googleapis.com/v2/reports', [
    'ids'          => 'channel==MINE',
    'startDate'    => $start_28,
    'endDate'      => $end,
    'dimensions'   => 'insightTrafficSourceType',
    'metrics'      => 'views',
    'sort'         => '-views',
    'maxResults'   => '5',
    'access_token' => $token,
]);

$src_labels = [];
$src_data   = [];
if (!empty($traffic['rows'])) {
    $total_src = array_sum(array_column($traffic['rows'], 1));
    $src_names = [
        'YT_SEARCH'          => 'Recherche YouTube',
        'SUGGESTED_VIDEOS'   => 'Suggestions',
        'EXT_URL'            => 'Externe',
        'SUBSCRIBER'         => 'Abonnés',
        'YT_CHANNEL'         => 'Page chaîne',
        'NO_LINK_OTHER'      => 'Autres',
        'PLAYLIST'           => 'Playlists',
    ];
    foreach ($traffic['rows'] as $r) {
        $src_labels[] = $src_names[$r[0]] ?? $r[0];
        $src_data[]   = $total_src > 0 ? round($r[1] / $total_src * 100, 1) : 0;
    }
}

// ── 6. Croissance mensuelle (6 mois) ──
$monthly_labels = [];
$monthly_data   = [];
for ($i = 5; $i >= 0; $i--) {
    $ts = strtotime("-$i months", strtotime($start));
    $ms = date('Y-m-01', $ts);
    $me = date('Y-m-t', $ts);
    $mv = api_get('https://youtubeanalytics.googleapis.com/v2/reports', [
        'ids'          => 'channel==MINE',
        'startDate'    => $ms,
        'endDate'      => $me,
        'metrics'      => 'views',
        'access_token' => $token,
    ]);
    $monthly_labels[] = date('M', $ts);
    $monthly_data[]   = (int)($mv['rows'][0][0] ?? 0);
}

// ── Recommandations dynamiques ──
$recos = [];
if ($ctr_28 < 4)     $recos[] = ['priority'=>'haute',   'text'=>"CTR miniatures à {$ctr_28}% sur 28j : trop bas. Tester miniatures avec visage + texte choc."];
if ($ctr_28 >= 6)    $recos[] = ['priority'=>'basse',   'text'=>"CTR excellent à {$ctr_28}% : maintenir le style de miniatures actuel."];
if ($retention_28 < 35) $recos[] = ['priority'=>'haute','text'=>"Rétention à {$retention_28}% : améliorer les 30 premières secondes de chaque vidéo."];
if ($subs_28 >= 10)  $recos[] = ['priority'=>'moyenne', 'text'=>"+{$subs_28} abonnés en 28j : identifier les vidéos qui convertissent le plus."];
if ($views_28 > 1000)$recos[] = ['priority'=>'basse',   'text'=>num($views_28) . " vues en 28j : régulier. Augmenter la cadence pour accélérer."];
if (empty($recos))   $recos[] = ['priority'=>'basse',   'text'=>'Continuez à publier régulièrement. Chaque vidéo améliore votre référencement.'];

// ── JSON final ──
$output = [
    'meta'             => ['period' => '28 derniers jours', 'updated' => date('Y-m-d'), 'channel' => $channel_name],
    'channel'          => ['name' => $channel_name, 'videos_count' => $videos_count, 'views_lifetime' => $views_lifetime],
    'subscribers'      => ['total' => $subscribers_total, 'new' => $subs_28, 'lost' => $subs_lost, 'net' => $subs_28 - $subs_lost, 'change_pct' => 0.0],
    'views'            => ['total' => $views_28, 'lifetime' => $views_lifetime, 'change_pct' => 0.0],
    'watch_time_hours' => ['total' => $watch_hours_28, 'change_pct' => 0.0],
    'ctr'              => ['value' => $ctr_28, 'change_pct' => 0.0],
    'retention'        => ['avg' => $retention_28, 'change_pct' => 0.0],
    'monthly_views'    => ['labels' => $monthly_labels, 'data' => $monthly_data],
    'top_videos'       => array_slice($top_videos, 0, 10),
    'traffic_sources'  => ['labels' => $src_labels, 'data' => $src_data],
    'recommendations'  => $recos,
];

file_put_contents(DATA_PATH . 'youtube.json', json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode([
    'ok'      => true,
    'message' => "YouTube synchronisé : {$views_28} vues (28j), {$subscribers_total} abonnés, " . count($top_videos) . " vidéos récupérées.",
    'updated' => date('Y-m-d H:i:s'),
]);
