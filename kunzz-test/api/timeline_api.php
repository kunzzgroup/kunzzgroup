<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once dirname(__DIR__) . '/media_config.php';

$lang = isset($_GET['lang']) ? $_GET['lang'] : 'zh';
$language = $lang === 'en' ? 'en' : 'zh';
$items = getTimelineItems($language);

echo json_encode(['items' => $items], JSON_UNESCAPED_UNICODE);
