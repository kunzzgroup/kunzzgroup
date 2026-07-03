<?php

$system = isset($system) ? $system : (isset($_GET['system']) ? $_GET['system'] : 'central');
$valid_systems = ['central', 'j1', 'j2', 'j3'];

if (!in_array($system, $valid_systems, true)) {
    $system = 'central';
}

if (!hasStockSystemPermission($system)) {
    $fallback = null;
    foreach ($valid_systems as $s) {
        if (hasStockSystemPermission($s)) {
            $fallback = $s;
            break;
        }
    }

    if ($fallback) {
        $system = $fallback;
    } else {
        requirePermission('resource', '__none__');
    }
}

$allowed_systems = [];
foreach ($valid_systems as $s) {
    if (hasStockSystemPermission($s)) {
        $allowed_systems[] = $s;
    }
}

$system_names = [
    'central' => '中央',
    'j1' => 'J1',
    'j2' => 'J2',
    'j3' => 'J3',
];
$display_name = $system_names[$system];
