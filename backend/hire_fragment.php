<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/partials/hire_logic.php';

hire_requireAuthenticatedSession();

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

extract(hire_prepareViewData());

include __DIR__ . '/partials/hire_content.php';
