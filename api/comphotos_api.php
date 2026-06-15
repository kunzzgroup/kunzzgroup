<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once dirname(__DIR__) . '/media_config.php';

$photos = getCompanyPhotos();

if (count($photos) < 30) {
    $comphotoDir = dirname(__DIR__) . '/comphoto/comphoto/';
    if (is_dir($comphotoDir)) {
        $files = glob($comphotoDir . '*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE);
        if ($files) {
            sort($files);
            foreach ($files as $file) {
                if (count($photos) >= 30) {
                    break;
                }
                $photoPath = '/comphoto/comphoto/' . basename($file);
                if (!in_array($photoPath, $photos, true)) {
                    $photos[] = $photoPath . '?v=' . filemtime($file);
                }
            }
        }
    }
}

if (count($photos) > 30) {
    $photos = array_slice($photos, 0, 30);
}

echo json_encode([
    'success' => true,
    'photos' => array_values($photos),
], JSON_UNESCAPED_UNICODE);
