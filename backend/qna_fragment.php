<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('hr', 'staff_management');
require_once __DIR__ . '/session_check.php';
require_once __DIR__ . '/partials/qna_logic.php';

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

extract(qna_prepareViewData());

include __DIR__ . '/partials/qna_content.php';
