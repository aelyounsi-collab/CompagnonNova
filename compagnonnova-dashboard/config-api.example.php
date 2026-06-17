<?php
// ============================================================
// COMPAGNONNOVA — Configuration API
// Copiez ce fichier en : config-api.php
// NE JAMAIS committer config-api.php sur GitHub !
// ============================================================

// ── Google / YouTube Analytics ────────────────────────────
// 1. Allez sur https://console.cloud.google.com
// 2. Créez un projet, activez "YouTube Analytics API" + "YouTube Data API v3"
// 3. Crédentiels > OAuth 2.0 > Type : Application Web
// 4. URI de redirection : http://localhost/compagnonnova-dashboard/auth/google-callback.php
define('GOOGLE_CLIENT_ID',     'VOTRE_CLIENT_ID.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'VOTRE_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URI',  'http://localhost/compagnonnova-dashboard/auth/google-callback.php');

// ── Meta (Instagram + Facebook) ───────────────────────────
// 1. Allez sur https://developers.facebook.com
// 2. Créez une app > Type : Business
// 3. Ajoutez les produits : Instagram Graph API + Pages API
// 4. Générez un token longue durée (60 jours) dans l'Explorateur d'API Graph
//    Permissions requises : instagram_basic, instagram_manage_insights,
//                           pages_show_list, pages_read_engagement, read_insights
define('META_ACCESS_TOKEN',    'VOTRE_META_LONG_LIVED_TOKEN');
define('INSTAGRAM_ACCOUNT_ID', '17841400000000000');  // ID compte Instagram Pro
define('FACEBOOK_PAGE_ID',     '100000000000000');     // ID page Facebook

// ── TikTok ───────────────────────────────────────────────
// L'API TikTok Analytics est très restreinte (vérification business requise).
// Méthode : Export CSV depuis TikTok Creator Center
// 1. TikTok Studio > Analytiques > Télécharger le rapport
// 2. Déposez le fichier CSV dans : compagnonnova-dashboard/data/tiktok-export.csv
// 3. Cliquez "Importer CSV" dans la page Synchronisation
// (pas de token requis pour cette méthode)
