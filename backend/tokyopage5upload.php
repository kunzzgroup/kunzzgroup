<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('visual');

if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}
?>
<?php
session_start();
include_once '../media_config.php';

// 检查是否已登录（根据你的登录系统调整）
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . app_url('frontend/login.html'));
    exit();
}

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
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/tokyopage5upload.css?v=<?php echo time(); ?>">
    <title>Tokyo 位置信息管理 - KUNZZ HOLDINGS</title>
    
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <h1>Tokyo 位置信息管理</h1>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">仪表板</a> > 
            <a href="media_manager.php">媒体管理</a> > 
            <span>Tokyo 位置信息</span>
        </div>
        
        <div class="content">          
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="post" id="mainForm" class="form-section">
                <h2>编辑位置信息</h2>
                
                <!-- 标题编辑区域 -->
                <div class="store-section">
                    <h3>节标题设置</h3>
                    <div class="form-group">
                        <label for="section_title">标题文字</label>
                        <input type="text" id="section_title" name="section_title" class="form-input" 
                            value="<?php echo htmlspecialchars($currentConfig['section_title'] ?? '我们在这'); ?>" required>
                        <div class="help-text">显示在位置信息顶部的标题</div>
                    </div>
                </div>

                <div class="alert alert-info" style="margin-bottom: 20px; background-color: #e3f2fd; border-left: 5px solid #2196f3; padding: 15px; border-radius: 4px;">
                    <h4 style="margin-top: 0; color: #0d47a1;">💡 如何获取地图嵌入代码？</h4>
                    <ol style="margin-bottom: 0; font-size: 0.9em; padding-left: 20px;">
                        <li>在 Google Maps 中找到您的店铺。</li>
                        <li>点击“分享” (Share)。</li>
                        <li>选择“嵌入地图” (Embed a map) 标签。</li>
                        <li>点击“复制 HTML” (Copy HTML) 按钮。</li>
                        <li>将复制的内容直接粘贴到下方的“地图链接”输入框中，系统会自动提取链接。</li>
                    </ol>
                </div>
                
                <button type="button" class="btn btn-add" onclick="addNewStore()">
                    + 添加新店铺
                </button>
                
                <div id="storesContainer">
                    <?php foreach ($currentConfig as $storeKey => $storeData): ?>
                        <?php if ($storeKey === 'section_title') continue; // 跳过标题配置 ?>
                        <div class="store-section" data-store-key="<?php echo $storeKey; ?>">
                            <!-- 店铺编辑内容保持不变 -->
                            <h3>
                                <span>
                                    <?php 
                                    // 获取所有非标题的店铺键名
                                    $storeKeys = array_filter(array_keys($currentConfig), function($key) { 
                                        return $key !== 'section_title'; 
                                    });
                                    // 重新索引数组，从0开始
                                    $storeKeys = array_values($storeKeys);
                                    // 找到当前店铺的位置并+1
                                    echo array_search($storeKey, $storeKeys) + 1;
                                    ?>
                                </span>
                                <div class="section-actions">
                                    <?php if (!in_array($storeKey, ['main_store', 'branch_store'])): ?>
                                    <button type="button" class="btn btn-danger" onclick="deleteStore('<?php echo $storeKey; ?>')">
                                        删除
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </h3>
                            <!-- 其余店铺表单字段保持不变 -->
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="<?php echo $storeKey; ?>_label">标签文字</label>
                                    <input type="text" id="<?php echo $storeKey; ?>_label" name="<?php echo $storeKey; ?>_label" class="form-input" 
                                        value="<?php echo htmlspecialchars($storeData['label']); ?>">
                                    <div class="help-text">例如：总店：、分店：、三店：</div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="<?php echo $storeKey; ?>_address">地址</label>
                                    <textarea id="<?php echo $storeKey; ?>_address" name="<?php echo $storeKey; ?>_address" class="form-input textarea"><?php echo htmlspecialchars($storeData['address']); ?></textarea>
                                    <div class="help-text">请输入完整的店铺地址</div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="<?php echo $storeKey; ?>_phone">电话号码</label>
                                    <input type="text" id="<?php echo $storeKey; ?>_phone" name="<?php echo $storeKey; ?>_phone" class="form-input" 
                                        value="<?php echo htmlspecialchars($storeData['phone']); ?>">
                                    <div class="help-text">例如：+60 19-710 8090</div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="<?php echo $storeKey; ?>_map_url">地图链接</label>
                                    <input type="url" id="<?php echo $storeKey; ?>_map_url" name="<?php echo $storeKey; ?>_map_url" class="form-input" 
                                        value="<?php echo htmlspecialchars($storeData['map_url']); ?>">
                                    <div class="help-text">Google Maps 分享链接</div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <button type="submit" class="btn">保存所有更改</button>
            </form>
            
            <!-- 隐藏的店铺模板 -->
            <div class="store-template" id="storeTemplate">
                <div class="store-section new-store" data-store-key="">
                    <h3>
                        <span></span>
                        <div class="section-actions">
                            <button type="button" class="btn btn-danger" onclick="removeNewStore(this)">
                                移除
                            </button>
                        </div>
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>标签文字</label>
                            <input type="text" class="form-input" name="">
                            <div class="help-text">例如：三店：、四店：、旗舰店：</div>
                        </div>
                        
                        <div class="form-group">
                            <label>地址</label>
                            <textarea class="form-input textarea" name=""></textarea>
                            <div class="help-text">请输入完整的店铺地址</div>
                        </div>
                        
                        <div class="form-group">
                            <label>电话号码</label>
                            <input type="text" class="form-input" name="">
                            <div class="help-text">例如：+60 12-345 6789</div>
                        </div>
                        
                        <div class="form-group">
                            <label>地图链接</label>
                            <input type="url" class="form-input" name="">
                            <div class="help-text">Google Maps 分享链接</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 删除确认表单 -->
    <form id="deleteForm" method="post" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="store_key" id="deleteStoreKey">
    </form>
    
    <script>
window.TOKYO_UPLOAD = {
    storeCounter: <?php
        echo count(array_filter(
            $currentConfig,
            function ($key) { return $key !== 'section_title'; },
            ARRAY_FILTER_USE_KEY
        ));
    ?>
};
</script>

<script src="js/tokyopage5upload.js?v=<?php echo time(); ?>"></script>
</body>
</html>
