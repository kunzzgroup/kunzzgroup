<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
}

require_once __DIR__ . '/session_check.php';

ob_start();
include __DIR__ . '/sidebar.php';
$output = ob_get_clean();

$output = preg_replace('#<link\b[^>]*>#i', '', $output);
$output = preg_replace('#<style\b[^>]*>.*?</style>#is', '', $output);
$output = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $output);

echo $output;
