<?php
session_start();

// 检查是否已登录
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '未登录']);
    exit();
}

$jsonFile = dirname(__DIR__) . '/visual/corporate_strategy.json';

// 处理 GET 请求 - 获取当前数据
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    if (file_exists($jsonFile)) {
        $content = file_get_contents($jsonFile);
        echo $content;
    } else {
        echo json_encode([]);
    }
    exit();
}

// 处理 POST 请求 - 保存数据
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isInternal = isset($_POST['internal_save']);
    if (!$isInternal) {
        header('Content-Type: application/json');
    }
    
    // 增加 max_input_vars 限制
    ini_set('max_input_vars', 5000);
    
    try {
        // ... (data processing logic)
        
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($jsonContent === false) throw new Exception("JSON encoding failed");
        
        if (!file_put_contents($jsonFile, $jsonContent, LOCK_EX)) {
            throw new Exception("Failed to write to JSON file");
        }
        
        if ($isInternal) {
            $api_response = ['success' => true, 'message' => '数据保存成功'];
        } else {
            echo json_encode(['success' => true, 'message' => '数据保存成功']);
            exit();
        }
        
    } catch (Exception $e) {
        if ($isInternal) {
            $api_response = ['success' => false, 'message' => $e->getMessage()];
        } else {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit();
        }
    }
}
