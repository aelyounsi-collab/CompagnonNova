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

$end        = date('Y-m-d');
$start_month= date('Y-m-01');
$start_90   = date('Y-m-d', strtotime('-90 days'));
$start_all  = '2020-01-01';
$month      = date('M Y');

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

// ── 2. Analytics mois en cours ──
$ana_month = api_get('https://youtubeanalytics.googleapis.com/v2/reports', [
    'ids'          => 'channel==MINE',
    'startDate'    => $start_month,
    'endDate'      => $end,
    'metrics'      => 'views,estimatedMinutesWatched,subscribersGained,subscribersLost,impressions,impressionsClickThroughRate,averageViewPercentage',
    'access_token' => $token,
]);
$rm          = $ana_month['rows'][0] ?? [0,0,0,0,0,0,0];
$views_month = (int)$rm[0];
$watch_month = round((int)$rm[1] / 60, 1);
$subs_gained = (int)$rm[2];
$subs_lost   = (int)$rm[3];
$ctr_month   = round((float)($rm[5] ?? 0) * 100, 2);
$ret_month   = round((float)($rm[6] ?? 0), 1);

// ── 3. Analytics 90 jours (pour CTR + rétention plus fiables) ──
$ana_90 = api_get('https://youtubeanalytics.googleapis.com/v2/reports', [
    'ids'          => 'channel==MINE',
    'startDate'    => $start_90,
    'endDate'      => $end,
    'metrics'      => 'views,estimatedMinutesWatched,subscribersGained,impressionsClickThroughRate,averageViewPercentage',
    'access_token' => $token,
]);
$r90        = $ana_90['rows'][0] ?? [0,0,0,0,0];
$views_90   = (int)$r90[0];
$watch_90   = round((int)$r90[1] / 60, 1);
$subs_90    = (int)$r90[2];
$ctr_90     = round((float)($r90[3] ?? 0) * 100, 2);
$ret_90     = round((float)($r90[4] ?? 0), 1);

// Utiliser 90j si le mois en cours donne 0
$ctr_display = $ctr_month > 0 ? $ctr_month : $ctr_90;
$ret_display = $ret_month > 0 ? $ret_month : $ret_90;
$views_display = $views_month > 0 ? $views_month : $views_90;
$watch_display = $watch_month > 0 ? $watch_month : $watch_90;
$period_label  = $views_month > 0 ? date('M Y') : '90 derniers jours';

// ── 4. Top 10 vidéos (lifetime) ──
$search = api_get('https://www.googleapis.com/youtube/v3/search', [
    'part' => 'snippet', 'forMine' => 'true', 'type' => 'video',
    'order' => 'viewCount', 'maxResults' => '10', 'access_token' => $token,
]);

$top_videos = [];
foreach ($search['items'] ?? [] as $item) {
    $vid = $item['id']['videoId'] ?? '';
    if (!$vid) continue;
    $vstat = api_get('https://www.googleapis.com/youtube/v3/videos', [
        'part' => 'statistics,contentDetails', 'id' => $vid, 'access_token' => $token,
    ]);
    $vs = $vstat['items'][0]['statistics'] ?? [];
    $va = api_get('https://youtubeanalytics.googleapis.com/v2/reports', [
        'ids'          => 'channel==MINE',
        'startDate'    => $start_90,
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
        'views'      => (int)($vs['viewCount'] ?? 0),
        'likes'      => (int)($vs['likeCount']    ?? 0),
        'comments'   => (int)($vs['commentCount'] ?? 0),
        'ctr'        => round((float)($vr[1] ?? 0) * 100, 1),
        'retention'  => round((float)($vr[2] ?? 0), 1),
        'watch_hours'=> round((int)($vr[3] ?? 0) / 60, 1),
    ];
}
usort($top_videos, fn($a, $b) => $b['views'] <=> $a['views']);

// ── 5. Sources de trafic (90j) ──
$traffic = api_get('https://youtubeanalytics.googleapis.com/v2/reports', [
    'ids'          => 'channel==MINE',
    'startDate'    => $start_90,
    'endDate'      => $end,
    'dimensions'   => 'insightTrafficSourceType',
    'metrics'      => 'views',
    'sort'         => '-views',
    'maxResults'   => '6',
    'access_token' => $token,
]);
$src_labels = [];
$src_data   = [];
if (!empty($traffic['rows'])) {
    $total_src = array_sum(array_column($traffic['rows'], 1));
    $src_names = [
        'YT_SEARCH'        => 'Recherche YT',
        'SUGGESTED_VIDEOS' => 'Suggestions',
        'EXT_URL'          => 'Externe',
        'SUBSCRIBER'       => 'Abonnés',
        'YT_CHANNEL'       => 'Page chaîne',
        'NO_LINK_OTHER'    => 'Autres',
        'SHORTS'           => 'Shorts',
        'PLAYLIST'         => 'Playlists',
        'HASHTAGS'         => 'Hashtags',
        'YT_OTHER_PAGE'    => 'Autres pages YT',
    ];
    foreach ($traffic['rows'] as $r) {
        $src_labels[] = $src_names[$r[0]] ?? $r[0];
        $src_data[]   = $total_src > 0 ? round($r[1] / $total_src * 100, 1) : 0;
    }
}

// ── 6. Croissance mensuelle 6 mois ──
$monthly_labels = [];
$monthly_data   = [];
for ($i = 5; $i >= 0; $i--) {
    $ts = strtotime("-$i months", strtotime($start_month));
    $mv = api_get('https://youtubeanalytics.googleapis.com/v2/reports', [
        'ids'          => 'channel==MINE',
        'startDate'    => date('Y-m-01', $ts),
        'endDate'      => date('Y-m-t', $ts),
        'metrics'      => 'views',
        'access_token' => $token,
    ]);
    $monthly_labels[] = date('M', $ts);
    $monthly_data[]   = (int)($mv['rows'][0][0] ?? 0);
}

// ── Recommandations ──
$recos = [];
if ($ctr_display > 0 && $ctr_display < 4)
    $recos[] = ['priority'=>'haute',   'text'=>"CTR miniatures à {$ctr_display}% : trop bas. Tester des miniatures avec visage + texte choc."];
elseif ($ctr_display >= 6)
    $recos[] = ['priority'=>'basse',   'text'=>"CTR excellent à {$ctr_display}% : maintenir le style de miniatures actuel."];
if ($ret_display > 0 && $ret_display < 35)
    $recos[] = ['priority'=>'haute',   'text'=>"Rétention à {$ret_display}% : améliorer les 30 premières secondes de chaque vidéo."];
if ($subs_gained > 5)
    $recos[] = ['priority'=>'moyenne', 'text'=>"+{$subs_gained} abonnés ce mois : identifier les vidéos qui ont converti."];
if (!empty($src_labels) && $src_labels[0] === 'Shorts')
    $recos[] = ['priority'=>'moyenne', 'text'=>"Les Shorts dominent vos sources de trafic. Créez des Shorts qui renvoient vers vos longues vidéos."];
if (empty($recos))
    $recos[] = ['priority'=>'basse', 'text'=>'Continuez à publier régulièrement pour améliorer votre référencement.'];

$output = [
    'meta'             => ['period' => $period_label, 'updated' => date('Y-m-d'), 'channel' => $channel_name],
    'channel'          => ['name' => $channel_name, 'videos_count' => $videos_count, 'views_lifetime' => $views_lifetime],
    'subscribers'      => ['total' => $subscribers_total, 'new' => $subs_gained, 'lost' => $subs_lost, 'net' => $subs_gained - $subs_lost, 'change_pct' => 0.0],
    'views'            => ['total' => $views_display, 'lifetime' => $views_lifetime, 'change_pct' => 0.0],
    'watch_time_hours' => ['total' => $watch_display, 'change_pct' => 0.0],
    'ctr'              => ['value' => $ctr_display, 'change_pct' => 0.0],
    'retention'        => ['avg' => $ret_display, 'change_pct' => 0.0],
    'monthly_views'    => ['labels' => $monthly_labels, 'data' => $monthly_data],
    'top_videos'       => array_slice($top_videos, 0, 10),
    'traffic_sources'  => ['labels' => $src_labels, 'data' => $src_data],
    'recommendations'  => $recos,
];

file_put_contents(DATA_PATH . 'youtube.json', json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode([
    'ok'      => true,
    'message' => "YouTube syncé : {$views_display} vues ({$period_label}), {$subscribers_total} abonnés, " . count($top_videos) . " vidéos.",
    'updated' => date('Y-m-d H:i:s'),
]);
