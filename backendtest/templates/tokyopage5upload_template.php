<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tokyo 位置信息管理 - KUNZZ HOLDINGS</title>
    <link rel="stylesheet" href="../css/tokyopage5upload.css">
</head>
<body class="has-sidebar">
    <?php $basePath = '../'; include '../pages/sidebar.php'; ?>
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
            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if (isset($error) && $error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
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
                    <?php 
                    $storeIndex = 1;
                    foreach ($currentConfig as $storeKey => $storeData): 
                        if ($storeKey === 'section_title') continue; 
                    ?>
                        <div class="store-section" data-store-key="<?php echo htmlspecialchars($storeKey); ?>">
                            <h3>
                                <span><?php echo $storeIndex++; ?></span>
                                <div class="section-actions">
                                    <?php if (!in_array($storeKey, ['main_store', 'branch_store'])): ?>
                                    <button type="button" class="btn btn-danger" onclick="deleteStore('<?php echo htmlspecialchars($storeKey); ?>')">
                                        删除
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="<?php echo $storeKey; ?>_label">标签文字</label>
                                    <input type="text" id="<?php echo $storeKey; ?>_label" name="<?php echo $storeKey; ?>_label" class="form-input" 
                                        value="<?php echo htmlspecialchars($storeData['label'] ?? ''); ?>">
                                    <div class="help-text">例如：总店：、分店：</div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="<?php echo $storeKey; ?>_address">地址</label>
                                    <textarea id="<?php echo $storeKey; ?>_address" name="<?php echo $storeKey; ?>_address" class="form-input textarea"><?php echo htmlspecialchars($storeData['address'] ?? ''); ?></textarea>
                                    <div class="help-text">请输入完整的店铺地址</div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="<?php echo $storeKey; ?>_phone">电话号码</label>
                                    <input type="text" id="<?php echo $storeKey; ?>_phone" name="<?php echo $storeKey; ?>_phone" class="form-input" 
                                        value="<?php echo htmlspecialchars($storeData['phone'] ?? ''); ?>">
                                    <div class="help-text">例如：+60 19-710 8090</div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="<?php echo $storeKey; ?>_map_url">地图链接</label>
                                    <input type="url" id="<?php echo $storeKey; ?>_map_url" name="<?php echo $storeKey; ?>_map_url" class="form-input" 
                                        value="<?php echo htmlspecialchars($storeData['map_url'] ?? ''); ?>">
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
                <div class="store-section new-store">
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
                            <input type="text" class="form-input">
                            <div class="help-text">例如：三店：、旗舰店：</div>
                        </div>
                        <div class="form-group">
                            <label>地址</label>
                            <textarea class="form-input textarea"></textarea>
                            <div class="help-text">请输入完整的店铺地址</div>
                        </div>
                        <div class="form-group">
                            <label>电话号码</label>
                            <input type="text" class="form-input">
                            <div class="help-text">例如：+60 12-345 6789</div>
                        </div>
                        <div class="form-group">
                            <label>地图链接</label>
                            <input type="url" class="form-input">
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
    
    <script src="../js/tokyopage5upload.js"></script>
</body>
</html>
