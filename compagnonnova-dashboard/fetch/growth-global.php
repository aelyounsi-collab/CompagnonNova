<?php
/**
 * Consolide les 4 JSON plateformes -> met à jour growth-global.json
 * Appelé automatiquement après chaque sync plateforme
 */
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$yt = loadData('youtube');
$tk = loadData('tiktok');
$ig = loadData('instagram');
$fb = loadData('facebook');

$yt_views = $yt['views']['total'] ?? 0;
$tk_views = $tk['views']['total'] ?? 0;
$ig_reach = $ig['reach']['total'] ?? 0;
$fb_reach = $fb['organic_reach']['total'] ?? 0;
$total_views = $yt_views + $tk_views + $ig_reach + $fb_reach;

$new_yt = $yt['subscribers']['new'] ?? 0;
$new_tk = $tk['followers']['new']   ?? 0;
$new_ig = $ig['followers']['new']   ?? 0;
$new_fb = $fb['fans']['new']        ?? 0;
$new_followers = $new_yt + $new_tk + $new_ig + $new_fb;

// Meilleure plateforme (croissance %)
$growths = [
    'YouTube'   => $yt['subscribers']['change_pct'] ?? 0,
    'TikTok'    => $tk['followers']['change_pct']   ?? 0,
    'Instagram' => $ig['followers']['change_pct']   ?? 0,
    'Facebook'  => $fb['fans']['change_pct']        ?? 0,
];
arcmax($growths);
arsort($growths);
$best_platform = array_key_first($growths);

// Répartition des vues
$total_v2 = max(1, $yt_views + $tk_views + $ig_reach + $fb_reach);
$shares = [
    round($yt_views / $total_v2 * 100),
    round($tk_views / $total_v2 * 100),
    round($ig_reach / $total_v2 * 100),
    round($fb_reach / $total_v2 * 100),
];

// Croissance mensuelle consolidée
$labels = $yt['monthly_views']['labels'] ?? ['Jan','Fév','Mar','Avr','Mai','Jun'];

// Top sujets : agrégation des top vidéos de chaque plateforme
$subjects = [];
foreach (array_merge(
    $yt['top_videos']  ?? [],
    $tk['top_videos']  ?? [],
    $ig['top_reels']   ?? [],
    $fb['top_posts']   ?? []
) as $item) {
    $t = $item['title'] ?? '';
    $v = $item['views'] ?? $item['reach'] ?? 0;
    // Groupement simple par mots-clés
    foreach (['douleur','toxique','processionnaire','urgence','vaccination','alimentation'] as $kw) {
        if (stripos($t, $kw) !== false) {
            $subjects[$kw] = ($subjects[$kw] ?? 0) + $v;
        }
    }
}
arsort($subjects);
$top_subjects = [];
foreach (array_slice($subjects, 0, 5, true) as $kw => $views) {
    $top_subjects[] = ['subject' => ucfirst($kw), 'total_views' => $views, 'platforms' => 4];
}
if (empty($top_subjects)) {
    $top_subjects = [['subject'=>'Données à collecter','total_views'=>0,'platforms'=>0]];
}

$old_gg  = loadData('growth-global');
$old_tv  = $old_gg['total_views']['total']    ?? $total_views;
$old_nf  = $old_gg['new_followers']['total']  ?? $new_followers;
$tv_chg  = $old_tv > 0 ? round(($total_views   - $old_tv) / $old_tv * 100, 1) : 0;
$nf_chg  = $old_nf > 0 ? round(($new_followers - $old_nf) / $old_nf * 100, 1) : 0;

$output = [
    'meta'           => ['period' => date('M Y'), 'updated' => date('Y-m-d')],
    'total_views'    => ['total' => $total_views, 'change_pct' => $tv_chg],
    'new_followers'  => ['total' => $new_followers, 'change_pct' => $nf_chg],
    'best_platform'  => $best_platform,
    'best_subject'   => $top_subjects[0]['subject'] ?? 'N/A',
    'platform_share' => [
        'labels' => ['YouTube','TikTok','Instagram','Facebook'],
        'data'   => $shares,
        'colors' => ['#FF0000','#69C9D0','#E1306C','#1877F2'],
    ],
    'monthly_growth' => [
        'labels'    => $labels,
        'youtube'   => $yt['monthly_views']['data']  ?? array_fill(0,6,0),
        'tiktok'    => $tk['monthly_views']['data']  ?? array_fill(0,6,0),
        'instagram' => $ig['monthly_reach']['data']  ?? array_fill(0,6,0),
        'facebook'  => $fb['monthly_reach']['data']  ?? array_fill(0,6,0),
    ],
    'top_subjects'   => $top_subjects,
    'experiments'    => $old_gg['experiments']    ?? [],
    'weekly_actions' => $old_gg['weekly_actions'] ?? [
        ['day'=>'Lundi',   'task'=>'Collecte analytics toutes plateformes'],
        ['day'=>'Mardi',   'task'=>'Analyse IA (agents Claude) + rapport hebdo'],
        ['day'=>'Mercredi','task'=>'Décisions éditoriales + calendrier semaine suivante'],
        ['day'=>'Jeudi',   'task'=>'Production scripts + enregistrement HeyGen'],
        ['day'=>'Vendredi','task'=>'Publication + programmation posts'],
    ],
];

file_put_contents(DATA_PATH . 'growth-global.json', json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['ok'=>true, 'message'=>"Growth Global consolidé : ".number_format($total_views)." vues totales, {$new_followers} nouveaux followers."]);
