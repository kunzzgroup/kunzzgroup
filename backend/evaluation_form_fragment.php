<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('hr', 'staff_management');
require_once __DIR__ . '/session_check.php';
require_once __DIR__ . '/partials/evaluation_form_logic.php';

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

extract(evaluation_form_prepareViewData());

include __DIR__ . '/partials/evaluation_form_content.php';
