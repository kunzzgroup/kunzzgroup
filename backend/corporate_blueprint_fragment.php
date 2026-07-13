<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('brand');
require_once __DIR__ . '/session_check.php';
require_once __DIR__ . '/partials/corporate_blueprint_logic.php';

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

extract(corporate_blueprint_prepareViewData());

include __DIR__ . '/partials/corporate_blueprint_content.php';
