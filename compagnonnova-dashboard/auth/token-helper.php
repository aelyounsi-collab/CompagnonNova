<?php
// Fonctions de gestion des tokens OAuth
require_once __DIR__ . '/../config-api.php';

function google_get_valid_token(): ?string {
    $path = __DIR__ . '/../tokens/google.json';
    if (!file_exists($path)) return null;

    $token = json_decode(file_get_contents($path), true);
    if (empty($token)) return null;

    // Si le token est encore valide (avec 5min de marge)
    if ($token['expires_at'] > time() + 300) {
        return $token['access_token'];
    }

    // Rafraîchissement via refresh_token
    if (empty($token['refresh_token'])) return null;

    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'client_id'     => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'refresh_token' => $token['refresh_token'],
            'grant_type'    => 'refresh_token',
        ]),
    ]);
    $resp = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (empty($resp['access_token'])) return null;

    $token['access_token'] = $resp['access_token'];
    $token['expires_at']   = time() + ($resp['expires_in'] ?? 3600);
    file_put_contents($path, json_encode($token, JSON_PRETTY_PRINT));

    return $token['access_token'];
}

function google_is_connected(): bool {
    return file_exists(__DIR__ . '/../tokens/google.json');
}

function meta_is_connected(): bool {
    return defined('META_ACCESS_TOKEN') && strlen(META_ACCESS_TOKEN) > 20 && META_ACCESS_TOKEN !== 'VOTRE_META_LONG_LIVED_TOKEN';
}

function api_get(string $url, array $params = [], ?string $token = null): ?array {
    if ($token) $params['access_token'] = $token;
    $full = $url . (str_contains($url, '?') ? '&' : '?') . http_build_query($params);
    $ch = curl_init($full);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_USERAGENT      => 'CompagnonNova-Dashboard/1.0',
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    return $resp ? json_decode($resp, true) : null;
}
