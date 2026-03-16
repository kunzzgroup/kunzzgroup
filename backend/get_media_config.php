<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Cache-Control: no-cache, must-revalidate");

// Optional: Enable error reporting for debugging, remove in production
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Look for media_config.json in possible locations
$possiblePaths = [
    __DIR__ . '/../media_config.json',
    __DIR__ . '/../../media_config.json',
    __DIR__ . '/media_config.json',
    dirname(__DIR__) . '/media_config.json',
    dirname(dirname(__DIR__)) . '/media_config.json',
    $_SERVER['DOCUMENT_ROOT'] . '/media_config.json'
];

$configFile = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        $configFile = $path;
        break;
    }
}

if ($configFile) {
    echo file_get_contents($configFile);
}
else {
    // Return default media if config doesn't exist
    echo json_encode([
        'tokyo_background' => [
            'type' => 'image',
            'file' => 'image/sushi-dish-asian-restaurant.jpg'
        ]
    ]);
}
?>
