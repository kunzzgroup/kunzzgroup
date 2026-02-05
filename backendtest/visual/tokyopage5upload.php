<?php
/**
 * Tokyo Page 5 Upload Shell (Store Locations)
 */
require_once '../system/session_check.php';

$configFile = '../tokyo_locations.json';

/**
 * Get Tokyo Location Config
 */
function getTokyoLocationConfig($file) {
    if (file_exists($file)) {
        return json_decode(file_get_contents($file), true) ?: ['section_title' => '我们在这'];
    }
    return ['section_title' => '我们在这'];
}

/**
 * Save Tokyo Location Config
 */
function saveTokyoLocationConfig($file, $config) {
    return file_put_contents($file, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

$success = "";
$error = "";

// Handle Delete Action
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['store_key'])) {
    $currentConfig = getTokyoLocationConfig($configFile);
    unset($currentConfig[$_POST['store_key']]);
    if (saveTokyoLocationConfig($configFile, $currentConfig)) {
        $success = "店铺信息删除成功！";
    } else {
        $error = "删除失败，请重试！";
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] !== 'delete')) {
    $config = [];
    $currentConfig = getTokyoLocationConfig($configFile);

    if (isset($_POST['section_title'])) {
        $config['section_title'] = trim($_POST['section_title']);
    }
    
    foreach ($_POST as $key => $value) {
        if (strpos($key, '_label') !== false) {
            $storeKey = str_replace('_label', '', $key);
            $label = trim($value);
            $address = isset($_POST[$storeKey . '_address']) ? trim($_POST[$storeKey . '_address']) : '';
            $phone = isset($_POST[$storeKey . '_phone']) ? trim($_POST[$storeKey . '_phone']) : '';
            $map_url = isset($_POST[$storeKey . '_map_url']) ? trim($_POST[$storeKey . '_map_url']) : '';
            
            if (!empty($label) || !empty($address)) {
                $order = isset($currentConfig[$storeKey]['order']) ? $currentConfig[$storeKey]['order'] : (count($config) + 1);
                $config[$storeKey] = [
                    'label' => $label,
                    'address' => $address,
                    'phone' => $phone,
                    'map_url' => $map_url,
                    'order' => $order,
                    'updated' => date('Y-m-d H:i:s')
                ];
            }
        }
    }
    
    if (empty($config) || (!isset($config['section_title']) && count($config) == 0)) {
        $config['section_title'] = '我们在这';
    }
    
    if (saveTokyoLocationConfig($configFile, $config)) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=1&updated=" . time());
        exit();
    } else {
        $error = "更新失败，请重试！";
    }
}

if (isset($_GET['success'])) {
    $success = "位置信息更新成功！";
}

// Read current config for template
$currentConfig = getTokyoLocationConfig($configFile);

// Include the template
include '../templates/tokyopage5upload_template.php';
?>
