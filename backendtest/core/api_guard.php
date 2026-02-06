<?php
require_once __DIR__.'/session_check.php';

$host = $_SERVER['HTTP_HOST'] ?? '';
$referer = $_SERVER['HTTP_REFERER'] ?? '';

if ($referer !== '') {
    if (parse_url($referer, PHP_URL_HOST) !== $host) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error'=>'CROSS_ORIGIN_BLOCKED']);
        exit;
    }
}

if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('X-API-PROTECTED: true');
}
?>
