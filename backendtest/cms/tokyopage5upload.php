<?php
require_once dirname(__DIR__) . '/core/init.php';
require_once CORE_PATH . '/session_check.php';
include_once '../media_config.php';

// 处理删除店铺
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['store_key'])) {
    $currentConfig = getTokyoLocationConfig();
    unset($currentConfig[$_POST['store_key']]);
    if (saveTokyoLocationConfig($currentConfig)) {
        $success = "店铺信息删除成功！";
    } else {
        $error = "删除失败，请重试！";
    }
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] !== 'delete')) {
    $config = [];
    
    // 获取当前配置以保持order
    $currentConfig = getTokyoLocationConfig();

    // 添加标题处理
    if (isset($_POST['section_title'])) {
        $config['section_title'] = trim($_POST['section_title']);
    }
    
    // 处理所有店铺（包括新添加的）
    foreach ($_POST as $key => $value) {
        if (strpos($key, '_label') !== false) {
            $storeKey = str_replace('_label', '', $key);
            
            // 检查是否所有相关字段都存在
            $label = trim($value);
            $address = isset($_POST[$storeKey . '_address']) ? trim($_POST[$storeKey . '_address']) : '';
            $phone = isset($_POST[$storeKey . '_phone']) ? trim($_POST[$storeKey . '_phone']) : '';
            $map_url = isset($_POST[$storeKey . '_map_url']) ? trim($_POST[$storeKey . '_map_url']) : '';
            
            // 如果至少有标签或地址，就保存这个店铺
            if (!empty($label) || !empty($address)) {
                // 保持原有的order值（如果存在），否则分配新的order
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
    
    // 确保至少保留标题
    if (empty($config) || (!isset($config['section_title']) && count($config) == 0)) {
        $config['section_title'] = '我们在这';
    }
    
    try {
        if (saveTokyoLocationConfig($config)) {
            $success = "位置信息更新成功！";
            // 重定向避免重复提交
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=1&updated=" . time());
            exit();
        } else {
            $error = "更新失败，请重试！";
        }
    } catch (Exception $e) {
        $error = "保存过程中发生错误：" . $e->getMessage();
    }
}

// 处理成功消息显示
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success = "位置信息更新成功！";
}

// 读取当前配置
$currentConfig = getTokyoLocationConfig();

require __DIR__ . '/templates/tokyopage5upload.php';