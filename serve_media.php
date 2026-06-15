<?php
require_once __DIR__ . '/media_config.php';

$basename = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

$aliases = [
    'home_background.webm' => 'home_background',
    'home.mp4' => 'home_background',
];

$mediaType = $aliases[$basename] ?? null;
if (!$mediaType) {
    http_response_code(404);
    exit;
}

$media = getMediaConfig($mediaType);
$filePath = $media['file'];

$candidates = [$filePath];
if (strpos($filePath, '/') !== 0 && strpos($filePath, 'http') !== 0) {
    $candidates = [
        __DIR__ . '/' . $filePath,
        $filePath,
        __DIR__ . '/../' . $filePath,
    ];
}

$resolved = null;
foreach ($candidates as $candidate) {
    if (is_string($candidate) && file_exists($candidate)) {
        $resolved = $candidate;
        break;
    }
}

if (!$resolved) {
    http_response_code(404);
    exit;
}

$extension = strtolower(pathinfo($resolved, PATHINFO_EXTENSION));
$mimeTypes = [
    'webm' => 'video/webm',
    'mp4' => 'video/mp4',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'webp' => 'image/webp',
];

header('Content-Type: ' . ($mimeTypes[$extension] ?? 'application/octet-stream'));
header('Cache-Control: public, max-age=3600');
readfile($resolved);
