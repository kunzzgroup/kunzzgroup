<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tokyo 位置信息管理 - KUNZZ HOLDINGS</title>
    <link rel="stylesheet" href="css/tokyopage5upload.css">
</head>
<body>
    <?php include CORE_PATH . '/sidebar.php'; ?>
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
    
    <script src="js/tokyopage5upload.js"></script>
    <script>
        // 初始化店铺计数器
        let storeCounter = <?php echo count(array_filter($currentConfig, function($key) { return $key !== 'section_title'; }, ARRAY_FILTER_USE_KEY)); ?>;
    </script>
</body>
</html>
