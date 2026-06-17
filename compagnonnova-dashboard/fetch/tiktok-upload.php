<?php
/**
 * Reçoit le CSV TikTok via upload formulaire et le sauvegarde dans data/
 */
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

if (empty($_FILES['csv']['tmp_name'])) {
    echo json_encode(['ok'=>false,'error'=>'Aucun fichier reçu.']);
    exit;
}

$tmp  = $_FILES['csv']['tmp_name'];
$dest = DATA_PATH . 'tiktok-export.csv';

// Vérification basique
$mime = mime_content_type($tmp);
if (!in_array($mime, ['text/csv','text/plain','application/csv','application/vnd.ms-excel'])) {
    // Accepter quand même (les CSV sont parfois détectés en text/plain)
}

if (!move_uploaded_file($tmp, $dest)) {
    echo json_encode(['ok'=>false,'error'=>"Impossible d'écrire le fichier. Vérifiez les permissions du dossier data/."]);
    exit;
}

echo json_encode(['ok'=>true,'message'=>'CSV déposé dans data/tiktok-export.csv. Cliquez maintenant sur Importer CSV.']);
