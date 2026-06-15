<?php
$reactIndex = __DIR__ . '/Kunzz-Landing-Page/dist/index.html';

if (file_exists($reactIndex)) {
    header('Content-Type: text/html; charset=UTF-8');
    readfile($reactIndex);
    exit;
}

// Fallback to legacy PHP frontend if React build is missing
header('Location: frontend/index');
exit;
?>
