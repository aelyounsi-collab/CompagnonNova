<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config-api.php';

function redirect_sync(string $msg, string $type = 'error'): void {
    header('Location: ../pages/sync.php?msg=' . urlencode($msg) . '&type=' . $type);
    exit;
}

// Vérification state CSRF
if (empty($_GET['state']) || $_GET['state'] !== ($_SESSION['oauth_state'] ?? '')) {
    redirect_sync('Sécurité : state OAuth invalide.');
}
unset($_SESSION['oauth_state']);

if (isset($_GET['error'])) {
    redirect_sync('Accès refusé par Google : ' . htmlspecialchars($_GET['error']));
}

if (empty($_GET['code'])) {
    redirect_sync('Code d\'autorisation manquant.');
}

// Échange du code contre les tokens
$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS     => http_build_query([
        'code'          => $_GET['code'],
        'client_id'     => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri'  => GOOGLE_REDIRECT_URI,
        'grant_type'    => 'authorization_code',
    ]),
]);
$resp = json_decode(curl_exec($ch), true);
curl_close($ch);

if (empty($resp['access_token'])) {
    redirect_sync('Erreur token Google : ' . ($resp['error_description'] ?? 'inconnue'));
}

// Sauvegarde du token
$token = [
    'access_token'  => $resp['access_token'],
    'refresh_token' => $resp['refresh_token'] ?? null,
    'expires_at'    => time() + ($resp['expires_in'] ?? 3600),
    'created_at'    => date('Y-m-d H:i:s'),
];

file_put_contents(__DIR__ . '/../tokens/google.json', json_encode($token, JSON_PRETTY_PRINT));

redirect_sync('Compte Google connecté avec succès !', 'success');
