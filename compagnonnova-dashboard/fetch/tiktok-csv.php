<?php
/**
 * Import CSV export TikTok Creator Center -> met à jour data/tiktok.json
 *
 * Comment exporter depuis TikTok :
 * TikTok Studio > Analytiques > Exporter les données > CSV
 * Déposez le fichier dans : data/tiktok-export.csv
 */
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$csv_path = DATA_PATH . 'tiktok-export.csv';

if (!file_exists($csv_path)) {
    echo json_encode(['ok' => false, 'error' => "Fichier introuvable : data/tiktok-export.csv\nExportez vos analytics depuis TikTok Studio > Analytiques > Exporter."]);
    exit;
}

$rows = array_map('str_getcsv', file($csv_path));
$headers = array_map('trim', array_shift($rows));

$data = [];
foreach ($rows as $row) {
    if (count($row) === count($headers)) {
        $data[] = array_combine($headers, array_map('trim', $row));
    }
}

if (empty($data)) {
    echo json_encode(['ok' => false, 'error' => 'CSV vide ou format non reconnu.']);
    exit;
}

// Champs TikTok Creator Center (noms courants)
$find = function(array $row, array $keys): int|float {
    foreach ($keys as $k) {
        foreach ($row as $col => $val) {
            if (stripos($col, $k) !== false && is_numeric($val)) {
                return (float)str_replace([',',' '], ['.',''], $val);
            }
        }
    }
    return 0;
};

// Agrégation sur toutes les lignes
$views        = 0;
$likes        = 0;
$comments     = 0;
$shares       = 0;
$followers    = 0;
$new_followers= 0;
$completions  = [];

foreach ($data as $row) {
    $views        += $find($row, ['vue','view','impression']);
    $likes        += $find($row, ['like','j\'aime','j\'aim']);
    $comments     += $find($row, ['comment']);
    $shares       += $find($row, ['share','partage']);
    $c = $find($row, ['completion','taux de lecture','average watch']);
    if ($c > 0) $completions[] = $c;
    $nf = $find($row, ['nouveau','new follower','abonné gagné']);
    if ($nf > 0) $new_followers += $nf;
}

// Followers total depuis la première colonne si disponible
$old_tk = loadData('tiktok');
$followers_total = $old_tk['followers']['total'] ?? 0;
if ($new_followers > 0) $followers_total += (int)$new_followers;

$avg_completion = count($completions) > 0 ? round(array_sum($completions) / count($completions), 1) : 0;
$month          = date('M Y');

// Création des top vidéos (depuis les lignes triées par vues)
usort($data, function($a, $b) use ($find) {
    return $find($b, ['vue','view']) <=> $find($a, ['vue','view']);
});

$top_videos = [];
foreach (array_slice($data, 0, 5) as $row) {
    $title = $row['Titre'] ?? $row['Title'] ?? $row['Video'] ?? 'Vidéo sans titre';
    $v = (int)$find($row, ['vue','view']);
    $c = $find($row, ['completion','taux de lecture']);
    $s = (int)$find($row, ['share','partage']);
    if ($v > 0) $top_videos[] = ['title'=>$title, 'views'=>$v, 'completion'=>$c ?: $avg_completion, 'shares'=>$s];
}

// Croissance mensuelle (6 mois) — le CSV ne donne que le mois courant
$monthly_labels = [];
$monthly_data   = [];
for ($i = 5; $i >= 0; $i--) {
    $monthly_labels[] = date('M', strtotime("-$i months"));
    $monthly_data[]   = $i === 0 ? (int)$views : ($old_tk['monthly_views']['data'][$i] ?? 0);
}

$old_views = $old_tk['views']['total'] ?? (int)$views;
$view_change = $old_views > 0 ? round(((int)$views - $old_views) / $old_views * 100, 1) : 0;

$output = [
    'meta'            => ['period' => $month, 'updated' => date('Y-m-d'), 'source' => 'CSV TikTok Creator Center'],
    'followers'       => ['total' => $followers_total, 'new' => (int)$new_followers, 'change_pct' => 0],
    'views'           => ['total' => (int)$views, 'change_pct' => $view_change],
    'completion_rate' => ['avg' => $avg_completion, 'change_pct' => 0],
    'favorites'       => ['total' => (int)$likes,    'change_pct' => 0],
    'shares'          => ['total' => (int)$shares,   'change_pct' => 0],
    'comments'        => ['total' => (int)$comments, 'change_pct' => 0],
    'monthly_views'   => ['labels' => $monthly_labels, 'data' => $monthly_data],
    'top_videos'      => $top_videos ?: $old_tk['top_videos'] ?? [],
    'hooks_performance'=> $old_tk['hooks_performance'] ?? [],
    'recommendations' => [
        ['priority'=>'haute',   'text'=>"Import CSV TikTok réussi : ". number_format((int)$views) ." vues ce mois."],
        ['priority'=>'moyenne', 'text'=>"Taux de complétion moyen : {$avg_completion}%. Viser 65%+ avec des hooks plus forts."],
    ],
];

// Supprimer le CSV après import
unlink($csv_path);

file_put_contents(DATA_PATH . 'tiktok.json', json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['ok'=>true, 'message'=>"TikTok importé depuis CSV : ".(int)$views." vues, ".(int)$shares." partages.", 'updated'=>date('Y-m-d H:i:s')]);
