<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEnglish ? 'Timeline Management' : '发展历史管理'; ?> - KUNZZ HOLDINGS</title>
    <link rel="stylesheet" href="css/aboutpage4upload.css">
</head>
<body>
    <?php include CORE_PATH . '/sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <h1><?php echo $isEnglish ? 'Timeline Management' : '发展历史管理'; ?></h1>
            <div class="language-switch">
                <a href="?lang=zh" class="btn <?php echo !$isEnglish ? 'active' : ''; ?>">中文</a>
                <a href="?lang=en" class="btn <?php echo $isEnglish ? 'active' : ''; ?>">English</a>
            </div>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php"><?php echo $isEnglish ? 'Dashboard' : '仪表板'; ?></a> > 
            <a href="media_manager.php"><?php echo $isEnglish ? 'Media Management' : '媒体管理'; ?></a> > 
            <span><?php echo $isEnglish ? 'Timeline Management' : '发展历史管理'; ?></span>
        </div>
        
        <div class="content">           
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="timeline-section">
                <h2><?php echo $isEnglish ? 'Timeline Content Management' : '时间线内容管理'; ?></h2>
                
                <!-- 管理动作：新增记录（年+月） -->
                <div class="year-management">
                    <div class="year-tabs">
                        <?php 
                        // 确保 $items 是数组
                        if (!is_array($items)) {
                            $items = [];
                        }
                        
                        // 调试信息（临时，用于诊断）
                        $debugInfo = "调试：显示时 items 数量: " . count($items);
                        if (!empty($items)) {
                            $debugInfo .= ", 第一条记录的 year 字段: " . ($items[0]['year'] ?? '不存在');
                        }
                        error_log($debugInfo);
                        
                        $years = array_values(array_unique(array_map(function($it){ 
                            return (string)($it['year'] ?? ''); 
                        }, $items)));
                        // 过滤空值
                        $years = array_filter($years, function($y) { return !empty($y); });
                        $years = array_values($years);
                        sort($years, SORT_NUMERIC);
                        
                        error_log("调试：提取的年份数量: " . count($years) . ", 年份列表: " . implode(', ', $years));
                        
                        if (empty($years)) {
                            echo '<span style="color: #666;">暂无记录</span>';
                            // 调试：如果 items 不为空但 years 为空，说明 year 字段有问题
                            if (!empty($items)) {
                                error_log("警告：items 有 " . count($items) . " 条记录，但无法提取年份。第一条记录: " . json_encode($items[0] ?? null, JSON_UNESCAPED_UNICODE));
                            }
                        } else {
                            foreach ($years as $index => $year): 
                        ?>
                            <button class="year-tab <?php echo $index === 0 ? 'active' : ''; ?>" onclick="showYear('<?php echo htmlspecialchars($year, ENT_QUOTES); ?>')"><?php echo htmlspecialchars($year); ?><?php echo $isEnglish ? '' : '年'; ?></button>
                        <?php 
                            endforeach;
                        }
                        ?>
                    </div>
                    
                    <div class="year-actions" style="display: flex !important; gap: 10px !important; align-items: center !important;">
                        <button type="button" class="btn btn-add" onclick="showAddRecordModal()" style="display: inline-block !important; visibility: visible !important; opacity: 1 !important;">+ <?php echo $isEnglish ? 'Add Record' : '新增记录'; ?></button>
                    </div>
                </div>

                <!-- 新增记录模态框 -->
                <div id="addRecordModal" class="modal" style="display: none;">
                    <div class="modal-content">
                        <h3><?php echo $isEnglish ? 'Add New Record' : '新增发展记录'; ?></h3>
                        <form method="post">
                            <div class="form-group">
                                <label><?php echo $isEnglish ? 'Year' : '年份'; ?></label>
                                <input type="number" name="new_year" class="form-input" min="1900" max="2100" placeholder="<?php echo $isEnglish ? 'Enter year, e.g.: 2024' : '输入年份，例如：2024'; ?>" required>
                            </div>
                            <div class="form-group">
                                <label><?php echo $isEnglish ? 'Month' : '月份'; ?></label>
                                <input type="number" name="new_month" class="form-input" min="1" max="12" placeholder="<?php echo $isEnglish ? 'Enter month, 1-12' : '输入月份，1-12'; ?>" required>
                            </div>
                            <div class="form-actions">
                                <button type="submit" name="add_record" class="btn"><?php echo $isEnglish ? 'Add Record' : '新增记录'; ?></button>
                                <button type="button" class="btn btn-secondary" onclick="hideAddRecordModal()"><?php echo $isEnglish ? 'Cancel' : '取消'; ?></button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php 
                $years = array_values(array_unique(array_map(function($it){ return (string)($it['year'] ?? ''); }, $items)));
                sort($years, SORT_NUMERIC);
                foreach ($years as $year): 
                    $yearItems = array_values(array_filter($items, function($it) use ($year){ return (string)($it['year'] ?? '') === (string)$year; }));
                ?>
                <div class="timeline-content <?php echo $year == '2022' ? 'active' : ''; ?>" id="content-<?php echo $year; ?>">
                    <h3 style="margin-bottom: 20px; color: #333; border-bottom: 2px solid #f99e00; padding-bottom: 10px;">
                        <?php echo $year; ?><?php echo $isEnglish ? '' : '年'; ?> - <?php echo $isEnglish ? 'Records' : '发展记录'; ?>
                    </h3>
                    
                    <?php if (empty($yearItems)): ?>
                        <div class="no-entries" style="text-align: center; padding: 40px; color: #666; background: #f8f9fa; border-radius: 10px; margin: 20px 0;">
                            <p><?php echo $isEnglish ? 'No records for this year. Click "Add Record" to create one.' : '此年份暂无记录。点“新增记录”创建。'; ?></p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($yearItems as $idx => $data): $entryIndex = $idx + 1; $recordId = $data['id'] ?? ('rec_' . $year . '_' . $entryIndex); ?>
                    <div class="entry-container" data-record-id="<?php echo htmlspecialchars($recordId); ?>" style="border: 1px solid #dee2e6; border-radius: 10px; padding: 20px; margin-bottom: 20px; background: white;">
                        <div class="entry-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h4 style="margin: 0; color: #555;"><?php echo $isEnglish ? 'Record' : '记录'; ?> #<?php echo $entryIndex; ?><?php echo $data['month'] ? ' · ' . ($isEnglish ? 'Month ' . (int)$data['month'] : (int)$data['month'] . '月') : ''; ?></h4>
                            <button type="button" class="btn btn-danger" onclick="confirmDeleteRecord('<?php echo $recordId; ?>')" style="padding: 5px 10px; font-size: 12px;">
                                <?php echo $isEnglish ? 'Delete' : '删除'; ?>
                            </button>
                        </div>
                        
                        <!-- 照片上传表单 -->
                        <form method="post" enctype="multipart/form-data" class="upload-form">
                            <input type="hidden" name="record_id" value="<?php echo htmlspecialchars($recordId); ?>">
                            
                            <div class="form-group">
                                <label><?php echo $isEnglish ? 'Upload Photo for Entry #' . $entryIndex : '上传条目 #' . $entryIndex . ' 的照片'; ?></label>
                                <div class="file-input" onclick="document.getElementById('image-<?php echo $recordId; ?>').click()">
                                    <input type="file" id="image-<?php echo $recordId; ?>" name="timeline_image" accept="image/*">
                                    <div class="file-input-text">
                                        <?php echo $isEnglish ? 'Click to select photo or drag here' : '点击选择照片或拖拽到此处'; ?><br>
                                        <small><?php echo $isEnglish ? 'Supports JPG, PNG, WebP formats, recommended size 800x600' : '支持 JPG, PNG, WebP 格式，建议尺寸 800x600'; ?></small>
                                    </div>
                                </div>
                                
                                <?php 
                                $imagePath = '';
                                $displayPath = '';
                                if (isset($data['image'])) {
                                    // 检查文件是否存在（使用原始路径）
                                    $originalPath = $data['image'];
                                    $fullPath = '';
                                    
                                    // 尝试多个可能的路径
                                    $possiblePaths = [
                                        $originalPath,
                                        '../' . $originalPath,
                                        '../../' . $originalPath,
                                        $uploadDir . basename($originalPath)
                                    ];
                                    
                                    foreach ($possiblePaths as $testPath) {
                                        if (file_exists($testPath)) {
                                            $fullPath = $testPath;
                                            break;
                                        }
                                    }
                                    
                                    if ($fullPath) {
                                        // 为显示生成正确的相对路径
                                        if (strpos($originalPath, '/') !== 0 && strpos($originalPath, 'http') !== 0) {
                                            $displayPath = '../' . $originalPath;
                                        } else {
                                            $displayPath = $originalPath;
                                        }
                                        $imagePath = $fullPath;
                                    }
                                }
                                if ($imagePath && $displayPath): 
                                ?>
                                    <div class="current-file">
                                        <strong><?php echo $isEnglish ? 'Current Photo:' : '当前照片:'; ?></strong> <?php echo basename($data['image']); ?><br>
                                        <small><?php echo $isEnglish ? 'Updated:' : '更新时间:'; ?> <?php echo $data['updated'] ?? ($isEnglish ? 'Unknown' : '未知'); ?></small>
                                        
                                        <div class="preview-container">
                                            <img class="preview-image" src="<?php echo $displayPath; ?>?v=<?php echo isset($data['updated']) ? strtotime($data['updated']) : time(); ?>" alt="<?php echo $isEnglish ? $year . ' Photo' : $year . '年照片'; ?>">
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <button type="submit" class="btn"><?php echo $isEnglish ? 'Upload Photo' : '上传照片'; ?></button>
                        </form>
                        
                        <!-- 文案编辑表单 -->
                        <div class="content-form">
                            <h4><?php echo $isEnglish ? 'Edit Record #' . $entryIndex . ' Content' : '编辑记录 #' . $entryIndex . ' 文案内容'; ?></h4>
                            <form method="post">
                                <input type="hidden" name="record_id" value="<?php echo htmlspecialchars($recordId); ?>">
                                <input type="hidden" name="update_content" value="1">
                                
                                <div class="form-group">
                                    <label><?php echo $isEnglish ? 'Year' : '年份'; ?></label>
                                    <input type="number" name="year" class="form-input" min="1900" max="2100" value="<?php echo htmlspecialchars($data['year'] ?? $year); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label><?php echo $isEnglish ? 'Month' : '月份'; ?></label>
                                    <input type="number" name="month" class="form-input" min="1" max="12" value="<?php echo htmlspecialchars((string)($data['month'] ?? '')); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label><?php echo $isEnglish ? 'Title' : '标题'; ?></label>
                                    <input type="text" name="title" class="form-input" 
                                           value="<?php echo htmlspecialchars($data['title'] ?? ''); ?>" 
                                           placeholder="<?php echo $isEnglish ? 'Enter title...' : '输入标题...'; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label><?php echo $isEnglish ? 'First Description' : '第一段描述'; ?></label>
                                    <textarea name="description1" class="form-textarea" 
                                              placeholder="<?php echo $isEnglish ? 'Enter first description...' : '输入第一段描述...'; ?>"><?php echo htmlspecialchars($data['description1'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label><?php echo $isEnglish ? 'Second Description' : '第二段描述'; ?></label>
                                    <textarea name="description2" class="form-textarea" 
                                              placeholder="<?php echo $isEnglish ? 'Enter second description...' : '输入第二段描述...'; ?>"><?php echo htmlspecialchars($data['description2'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn"><?php echo $isEnglish ? 'Save Content' : '保存文案'; ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php 
                endforeach; 
                ?>
            </div>
        </div>
    </div>
    
    <script>
        // 设置页面语言变量供JS使用
        window.PAGE_LANG = '<?php echo $isEnglish ? 'en' : 'zh'; ?>';
    </script>
    <script src="js/aboutpage4upload.js"></script>
</body>
</html>
