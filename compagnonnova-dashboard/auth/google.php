<?php
require_once __DIR__ . '/../config.php';

if (!file_exists(__DIR__ . '/../config-api.php')) {
    die('Erreur : config-api.php introuvable. Copiez config-api.example.php en config-api.php et remplissez vos credentials.');
}
require_once __DIR__ . '/../config-api.php';

$scopes = implode(' ', [
    'https://www.googleapis.com/auth/youtube.readonly',
    'https://www.googleapis.com/auth/yt-analytics.readonly',
]);

$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;
session_start();

$params = http_build_query([
    'client_id'             => GOOGLE_CLIENT_ID,
    'redirect_uri'          => GOOGLE_REDIRECT_URI,
    'response_type'         => 'code',
    'scope'                 => $scopes,
    'access_type'           => 'offline',
    'prompt'                => 'consent',
    'state'                 => $state,
]);

header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $params);
exit;
