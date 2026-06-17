<?php
define('CN_VERSION', '1.0.0');
define('CN_TITLE', 'CompagnonNova');
define('DATA_PATH', __DIR__ . '/data/');

function loadData(string $name): array {
    $path = DATA_PATH . $name . '.json';
    if (!file_exists($path)) return [];
    $json = file_get_contents($path);
    return json_decode($json, true) ?? [];
}

function num(int|float $n): string {
    if ($n >= 1000000) return number_format($n / 1000000, 1) . 'M';
    if ($n >= 1000) return number_format($n / 1000, 1) . 'k';
    return number_format($n, 0, ',', ' ');
}

function pct(float $n): string {
    return number_format($n, 1, ',', ' ') . '%';
}

function badge_pct(float $val): string {
    $cls = $val >= 0 ? 'badge-up' : 'badge-down';
    $sign = $val >= 0 ? '+' : '';
    return "<span class=\"badge $cls\">$sign" . pct($val) . "</span>";
}

function badge_priority(string $p): string {
    $map = [
        'haute'   => ['badge-red',    '🔴 Haute'],
        'moyenne' => ['badge-orange', '🟡 Moyenne'],
        'basse'   => ['badge-green',  '🟢 Basse'],
    ];
    [$cls, $label] = $map[strtolower($p)] ?? ['badge-green', $p];
    return "<span class=\"badge $cls\">$label</span>";
}

function render_sidebar(string $active = 'home'): void {
    $links = [
        ['href' => '../index.php',          'icon' => '📊', 'label' => 'Vue Globale',  'id' => 'home'],
        ['href' => '../pages/youtube.php',  'icon' => '▶️',  'label' => 'YouTube',      'id' => 'youtube'],
        ['href' => '../pages/tiktok.php',   'icon' => '🎵', 'label' => 'TikTok',       'id' => 'tiktok'],
        ['href' => '../pages/instagram.php','icon' => '📸', 'label' => 'Instagram',    'id' => 'instagram'],
        ['href' => '../pages/facebook.php', 'icon' => '👥', 'label' => 'Facebook',     'id' => 'facebook'],
        ['href' => '../pages/growth.php',   'icon' => '🚀', 'label' => 'Growth Lab',   'id' => 'growth'],
    ];
    echo '<nav class="sidebar">';
    echo '<div class="sidebar-logo"><span class="logo-icon">🐾</span><span class="logo-text">CompagnonNova</span></div>';
    echo '<ul class="sidebar-nav">';
    foreach ($links as $l) {
        $cls = $l['id'] === $active ? ' class="active"' : '';
        echo "<li><a href=\"{$l['href']}\"$cls>{$l['icon']} {$l['label']}</a></li>";
    }
    echo '</ul>';
    echo '<div class="sidebar-footer">v' . CN_VERSION . '</div>';
    echo '</nav>';
}

function page_open(string $title, string $active = 'home'): void {
    $root = $active === 'home' ? './' : '../';
    echo '<!DOCTYPE html><html lang="fr"><head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo "<title>$title — CompagnonNova</title>";
    echo "<link rel=\"stylesheet\" href=\"{$root}assets/css/style.css\">";
    echo '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>';
    echo '</head><body>';
    render_sidebar($active);
    echo '<main class="main-content">';
    echo "<div class=\"page-header\"><h1>$title</h1></div>";
}

function page_close(string $jsRoot = './'): void {
    echo '</main>';
    echo "<script src=\"{$jsRoot}assets/js/charts.js\"></script>";
    echo '</body></html>';
}
