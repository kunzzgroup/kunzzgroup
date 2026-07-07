<?php
require_once __DIR__ . '/media_config.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (!is_string($path) || !preg_match('#/media/([^/.]+)#', $path, $matches)) {
    http_response_code(404);
    exit;
}

$mediaType = $matches[1];

if ($mediaType === 'background_music') {
    $configFile = __DIR__ . '/music_config.json';
    $filePath = '';
    if (file_exists($configFile)) {
        $config = json_decode(file_get_contents($configFile), true);
        $filePath = $config['background_music']['file'] ?? '';
    }
    if ($filePath === '') {
        $filePath = 'audio/audio/music.mp3';
    }
} else {
    $allowedTypes = ['home_background', 'about_background', 'joinus_background'];
    if (!in_array($mediaType, $allowedTypes, true)) {
        http_response_code(404);
        exit;
    }

    $media = getMediaConfig($mediaType);
    $filePath = $media['file'] ?? '';
}

if ($filePath === '' || strpos($filePath, 'http') === 0) {
    http_response_code(404);
    exit;
}

$candidates = [];
if ($filePath[0] === '/') {
    $candidates[] = $filePath;
} else {
    $candidates[] = __DIR__ . '/' . $filePath;
    $candidates[] = __DIR__ . '/' . preg_replace('#^\.\./#', '', $filePath);
}

$resolved = null;
foreach ($candidates as $candidate) {
    $real = realpath($candidate);
    if ($real && is_file($real)) {
        $resolved = $real;
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
    'mov' => 'video/quicktime',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'webp' => 'image/webp',
    'mp3' => 'audio/mpeg',
    'wav' => 'audio/wav',
    'ogg' => 'audio/ogg',
    'm4a' => 'audio/mp4',
];

header('Content-Type: ' . ($mimeTypes[$extension] ?? 'application/octet-stream'));
header('Cache-Control: public, max-age=3600');
readfile($resolved);
