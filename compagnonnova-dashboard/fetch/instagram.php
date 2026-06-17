<?php
/**
 * Fetch Instagram Graph API + Facebook Pages API -> met à jour instagram.json + facebook.json
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config-api.php';
require_once __DIR__ . '/../auth/token-helper.php';

header('Content-Type: application/json');

if (!meta_is_connected()) {
    echo json_encode(['ok' => false, 'error' => 'Token Meta non configuré. Ajoutez META_ACCESS_TOKEN dans config-api.php.']);
    exit;
}

$token  = META_ACCESS_TOKEN;
$ig_id  = INSTAGRAM_ACCOUNT_ID;
$fb_id  = FACEBOOK_PAGE_ID;
$period = 'this_month';
$month  = date('M Y');

// ── INSTAGRAM ────────────────────────────────────

// 1. Compte (followers)
$acc = api_get("https://graph.facebook.com/v19.0/{$ig_id}", [
    'fields' => 'followers_count,media_count',
    'access_token' => $token,
]);
$followers_total = (int)($acc['followers_count'] ?? 0);

// 2. Insights du compte (reach, impressions)
$ins = api_get("https://graph.facebook.com/v19.0/{$ig_id}/insights", [
    'metric'       => 'reach,impressions,follower_count',
    'period'       => 'month',
    'access_token' => $token,
]);

$reach    = 0;
$new_followers_ig = 0;
foreach ($ins['data'] ?? [] as $metric) {
    $val = array_sum(array_column($metric['values'] ?? [], 'value'));
    if ($metric['name'] === 'reach')          $reach = (int)$val;
    if ($metric['name'] === 'follower_count') $new_followers_ig = (int)$val;
}

// 3. Top médias (Reels)
$media = api_get("https://graph.facebook.com/v19.0/{$ig_id}/media", [
    'fields'       => 'caption,media_type,like_count,comments_count,reach,saved,shares_count',
    'limit'        => '10',
    'access_token' => $token,
]);

$top_reels = [];
$saves_total = 0;
$shares_total = 0;
foreach ($media['data'] ?? [] as $m) {
    $saves_total  += (int)($m['saved'] ?? 0);
    $shares_total += (int)($m['shares_count'] ?? 0);
    if (count($top_reels) < 5) {
        $caption = $m['caption'] ?? 'Post sans légende';
        $top_reels[] = [
            'title'  => mb_substr($caption, 0, 60) . (mb_strlen($caption) > 60 ? '...' : ''),
            'reach'  => (int)($m['reach'] ?? 0),
            'saves'  => (int)($m['saved'] ?? 0),
            'shares' => (int)($m['shares_count'] ?? 0),
        ];
    }
}

// Tri par reach
usort($top_reels, fn($a, $b) => $b['reach'] <=> $a['reach']);

// 4. Croissance mensuelle (6 mois)
$monthly_labels = [];
$monthly_data   = [];
for ($i = 5; $i >= 0; $i--) {
    $monthly_labels[] = date('M', strtotime("-$i months"));
    $monthly_data[]   = 0; // L'API Instagram ne fournit pas l'historique > 30j sans export
}
$monthly_data[5] = $reach;

// Calcul variations
$old_ig = loadData('instagram');
$old_followers = $old_ig['followers']['total'] ?? $followers_total;
$old_reach     = $old_ig['reach']['total']     ?? $reach;
$old_saves     = $old_ig['saves']['total']     ?? $saves_total;

$foll_change  = $old_followers > 0 ? round(($followers_total - $old_followers) / $old_followers * 100, 1) : 0;
$reach_change = $old_reach     > 0 ? round(($reach - $old_reach) / $old_reach * 100, 1) : 0;
$saves_change = $old_saves     > 0 ? round(($saves_total - $old_saves) / $old_saves * 100, 1) : 0;

$ig_output = [
    'meta'            => ['period' => $month, 'updated' => date('Y-m-d')],
    'followers'       => ['total' => $followers_total, 'new' => $new_followers_ig, 'change_pct' => $foll_change],
    'reach'           => ['total' => $reach, 'change_pct' => $reach_change],
    'saves'           => ['total' => $saves_total, 'change_pct' => $saves_change],
    'shares'          => ['total' => $shares_total, 'change_pct' => 0],
    'engagement_rate' => ['value' => $followers_total > 0 ? round(($saves_total + $shares_total) / $followers_total * 100, 1) : 0, 'change_pct' => 0],
    'monthly_reach'   => ['labels' => $monthly_labels, 'data' => $monthly_data],
    'top_reels'       => $top_reels ?: [['title'=>'Aucun média ce mois','reach'=>0,'saves'=>0,'shares'=>0]],
    'content_mix'     => ['labels'=>['Reels','Carrousels','Stories','Posts'],'data'=>[58,24,12,6]],
    'recommendations' => [
        ['priority'=>'haute', 'text'=> $reach > 10000 ? "Excellente portée à " . num($reach) . " : amplifier avec Stories de relai" : "Portée à améliorer : augmenter la fréquence des Reels à 5/semaine"],
        ['priority'=>'moyenne','text'=> $saves_total > 0 ? "{$saves_total} sauvegardes : créer des carrousels 'checklist' pour doubler ce KPI" : 'Activer les sauvegardes : ajouter CTA \'Mémorisez cette liste\' dans les légendes'],
    ],
];

file_put_contents(DATA_PATH . 'instagram.json', json_encode($ig_output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// ── FACEBOOK ────────────────────────────────────

$page = api_get("https://graph.facebook.com/v19.0/{$fb_id}", [
    'fields'       => 'fan_count,name',
    'access_token' => $token,
]);
$fans_total = (int)($page['fan_count'] ?? 0);

$fb_insights = api_get("https://graph.facebook.com/v19.0/{$fb_id}/insights", [
    'metric'       => 'page_post_engagements,page_reach,page_views_total',
    'period'       => 'month',
    'access_token' => $token,
]);

$organic_reach = 0;
$fb_engagements = 0;
foreach ($fb_insights['data'] ?? [] as $metric) {
    $val = (int)($metric['values'][0]['value'] ?? 0);
    if ($metric['name'] === 'page_reach')             $organic_reach   = $val;
    if ($metric['name'] === 'page_post_engagements')  $fb_engagements  = $val;
}

// Top posts Facebook
$fb_posts = api_get("https://graph.facebook.com/v19.0/{$fb_id}/posts", [
    'fields'       => 'message,shares,comments.summary(true)',
    'limit'        => '5',
    'access_token' => $token,
]);

$top_posts = [];
foreach ($fb_posts['data'] ?? [] as $p) {
    $msg = $p['message'] ?? 'Post sans texte';
    $top_posts[] = [
        'title'    => mb_substr($msg, 0, 70) . (mb_strlen($msg) > 70 ? '...' : ''),
        'reach'    => 0,
        'shares'   => (int)($p['shares']['count'] ?? 0),
        'comments' => (int)($p['comments']['summary']['total_count'] ?? 0),
    ];
}

$old_fb = loadData('facebook');
$old_fans  = $old_fb['fans']['total']          ?? $fans_total;
$old_reach = $old_fb['organic_reach']['total'] ?? $organic_reach;
$fans_change  = $old_fans  > 0 ? round(($fans_total  - $old_fans)  / $old_fans  * 100, 1) : 0;
$reach_change = $old_reach > 0 ? round(($organic_reach - $old_reach) / $old_reach * 100, 1) : 0;

$fb_output = [
    'meta'            => ['period' => $month, 'updated' => date('Y-m-d')],
    'fans'            => ['total' => $fans_total, 'new' => 0, 'change_pct' => $fans_change],
    'organic_reach'   => ['total' => $organic_reach, 'change_pct' => $reach_change],
    'viral_reach'     => ['total' => 0, 'change_pct' => 0],
    'shares'          => ['total' => array_sum(array_column($top_posts, 'shares')), 'change_pct' => 0],
    'comments'        => ['total' => array_sum(array_column($top_posts, 'comments')), 'change_pct' => 0],
    'engagement_rate' => ['value' => $fans_total > 0 ? round($fb_engagements / $fans_total * 100, 1) : 0, 'change_pct' => 0],
    'monthly_reach'   => ['labels' => $monthly_labels, 'data' => array_fill(0, 5, 0) + [5 => $organic_reach]],
    'top_posts'       => $top_posts ?: [['title'=>'Aucun post ce mois','reach'=>0,'shares'=>0,'comments'=>0]],
    'reach_split'     => ['labels'=>['Organique','Viral','Payant'],'data'=>[72,28,0]],
    'recommendations' => [
        ['priority'=>'haute',  'text'=>'Publier les Reels Instagram en natif sur Facebook pour +40% de portée organique'],
        ['priority'=>'moyenne','text'=>'Posts avec CTA \'Partagez pour protéger\' : génèrent 3x plus de viral reach'],
    ],
];

file_put_contents(DATA_PATH . 'facebook.json', json_encode($fb_output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['ok' => true, 'message' => "Instagram ({$followers_total} followers) + Facebook ({$fans_total} fans) synchronisés.", 'updated' => date('Y-m-d H:i:s')]);
