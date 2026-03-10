<?php
// Simulate the environment in menu.php
$_SERVER['HTTPS'] = 'off';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/tokyo-japanese-cuisine/menu.php';

$type = 'grand';
define('API_BASE', '../backend/menu_api.php');

$host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
      . '://' . $_SERVER['HTTP_HOST']
      . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/' . API_BASE;

echo "Target API URL: " . $host . "\n";

// We can't actually call it via HTTP on the local system since we don't have a web server running,
// but we can check if the file exists at the relative path.

$relativePath = rtrim(dirname(__DIR__ . '/tokyo-japanese-cuisine/menu.php'), '/') . '/' . API_BASE;
echo "Resolved File Path: " . realpath($relativePath) . "\n";

if (file_exists($relativePath)) {
    echo "SUCCESS: API file found at path.\n";
} else {
    echo "FAILURE: API file NOT found at path.\n";
}

// Test image path construction
require_once __DIR__ . '/backend/menu_api.php';
echo "Test Image URL: " . buildImageUrl('grand/test.jpg') . "\n";
?>
