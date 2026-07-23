<?php
require_once __DIR__ . '/media_config.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
if (!is_string($path) || !preg_match('#/media/([^/.]+)#', $path, $matches)) {
    http_response_code(404);
    exit;
}

$mediaType = $matches[1];
$allowedTypes = ['home_background', 'about_background', 'joinus_background'];
if (!in_array($mediaType, $allowedTypes, true)) {
    http_response_code(404);
    exit;
}

$media = getMediaConfig($mediaType);
$filePath = $media['file'] ?? '';

if ($filePath === '' || strpos($filePath, 'http') === 0) {
    http_response_code(404);
    exit;
}

/**
 * Build candidate absolute paths for a configured media file path.
 * Accepts both "video/video/foo.webm" and legacy "../video/video/foo.webm".
 */
function media_path_candidates(string $filePath): array
{
    $normalized = ltrim(str_replace('\\', '/', $filePath), '/');
    $normalized = preg_replace('#^(\.\./)+#', '', $normalized);

    $candidates = [
        __DIR__ . '/' . $normalized,
        __DIR__ . '/' . $filePath,
    ];

    // Prefer sibling format if configured file is missing (webm <-> mp4).
    $ext = strtolower(pathinfo($normalized, PATHINFO_EXTENSION));
    if ($ext === 'webm') {
        $candidates[] = __DIR__ . '/' . preg_replace('/\.webm$/i', '.mp4', $normalized);
    } elseif ($ext === 'mp4') {
        $candidates[] = __DIR__ . '/' . preg_replace('/\.mp4$/i', '.webm', $normalized);
    }

    // Default fallbacks for homepage background when config points at a missing file.
    if (str_contains($normalized, 'home_background')) {
        $candidates[] = __DIR__ . '/video/video/home_background.webm';
        $candidates[] = __DIR__ . '/video/video/home_background.mp4';
    }

    return array_values(array_unique($candidates));
}

$resolved = null;
foreach (media_path_candidates($filePath) as $candidate) {
    $real = realpath($candidate);
    if ($real && is_file($real) && str_starts_with($real, realpath(__DIR__))) {
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
];

header('Content-Type: ' . ($mimeTypes[$extension] ?? 'application/octet-stream'));
header('Cache-Control: public, max-age=3600');
readfile($resolved);
