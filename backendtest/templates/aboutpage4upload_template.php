<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEnglish ? 'Timeline Management' : '发展历史管理'; ?> - KUNZZ HOLDINGS</title>
    <link rel="stylesheet" href="../css/aboutpage4upload.css">
</head>
<body class="has-sidebar">
    <?php $basePath = '../'; include '../pages/sidebar.php'; ?>
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
            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if (isset($error) && $error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="timeline-section">
                <h2><?php echo $isEnglish ? 'Timeline Content Management' : '时间线内容管理'; ?></h2>
                
                <!-- 管理动作：新增记录（年+月） -->
                <div class="year-management">
                    <div class="year-tabs">
                        <?php 
                        $currentYears = array_values(array_unique(array_map(function($it){ return (string)($it['year'] ?? ''); }, $items)));
                        $currentYears = array_filter($currentYears, function($y) { return !empty($y); });
                        sort($currentYears, SORT_NUMERIC);
                        
                        if (empty($currentYears)): ?>
                            <span style="color: #666;"><?php echo $isEnglish ? 'No records' : '暂无记录'; ?></span>
                        <?php else: ?>
                            <?php foreach ($currentYears as $index => $year): ?>
                                <button class="year-tab <?php echo $index === 0 ? 'active' : ''; ?>" 
                                        data-year="<?php echo htmlspecialchars($year); ?>"
                                        onclick="switchYear('<?php echo htmlspecialchars($year); ?>')">
                                    <?php echo htmlspecialchars($year); ?><?php echo $isEnglish ? '' : '年'; ?>
                                </button>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="year-actions">
                        <button type="button" class="btn btn-add" onclick="document.getElementById('addRecordModal').style.display = 'flex'">
                            + <?php echo $isEnglish ? 'Add Record' : '新增记录'; ?>
                        </button>
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
                                <button type="button" class="btn btn-secondary" onclick="document.getElementById('addRecordModal').style.display = 'none'"><?php echo $isEnglish ? 'Cancel' : '取消'; ?></button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php foreach ($currentYears as $index => $year): 
                    $yearItems = array_values(array_filter($items, function($it) use ($year){ return (string)($it['year'] ?? '') === (string)$year; }));
                ?>
                <div class="timeline-content <?php echo $index === 0 ? 'active' : ''; ?>" id="timeline-<?php echo $year; ?>">
                    <h3 style="margin-bottom: 20px; color: #333; border-bottom: 2px solid #f99e00; padding-bottom: 10px;">
                        <?php echo $year; ?><?php echo $isEnglish ? '' : '年'; ?> - <?php echo $isEnglish ? 'Records' : '发展记录'; ?>
                    </h3>
                    
                    <?php if (empty($yearItems)): ?>
                        <div class="no-entries" style="text-align: center; padding: 40px; color: #666; background: #f8f9fa; border-radius: 10px; margin: 20px 0;">
                            <p><?php echo $isEnglish ? 'No records for this year. Click "Add Record" to create one.' : '此年份暂无记录。点“新增记录”创建。'; ?></p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($yearItems as $idx => $data): 
                            $entryIndex = $idx + 1; 
                            $recordId = $data['id'] ?? ('rec_' . $year . '_' . $entryIndex); 
                        ?>
                        <div class="entry-container" data-record-id="<?php echo htmlspecialchars($recordId); ?>" style="border: 1px solid #dee2e6; border-radius: 10px; padding: 20px; margin-bottom: 20px; background: white;">
                            <div class="entry-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <h4 style="margin: 0; color: #555;"><?php echo $isEnglish ? 'Record' : '记录'; ?> #<?php echo $entryIndex; ?><?php echo $data['month'] ? ' · ' . ($isEnglish ? 'Month ' . (int)$data['month'] : (int)$data['month'] . '月') : ''; ?></h4>
                                <form method="post" onsubmit="return confirm('<?php echo $isEnglish ? 'Are you sure you want to delete this record?' : '确定要删除这个记录吗？'; ?>')">
                                    <input type="hidden" name="delete_record" value="1">
                                    <input type="hidden" name="record_id" value="<?php echo htmlspecialchars($recordId); ?>">
                                    <button type="submit" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;">
                                        <?php echo $isEnglish ? 'Delete' : '删除'; ?>
                                    </button>
                                </form>
                            </div>
                            
                            <!-- 照片上传表单 -->
                            <form method="post" enctype="multipart/form-data" class="upload-form">
                                <input type="hidden" name="record_id" value="<?php echo htmlspecialchars($recordId); ?>">
                                <input type="hidden" name="upload_photo" value="1">
                                
                                <div class="form-group">
                                    <label><?php echo $isEnglish ? 'Upload Photo' : '上传照片'; ?></label>
                                    <div class="file-input">
                                        <input type="file" name="timeline_image" accept="image/*">
                                        <div class="file-input-text">
                                            <?php echo $isEnglish ? 'Click to select photo or drag here' : '点击选择照片或拖拽到此处'; ?><br>
                                            <small><?php echo $isEnglish ? 'JPG, PNG, WebP (800x600 recommended)' : '支持 JPG, PNG, WebP 格式，建议尺寸 800x600'; ?></small>
                                        </div>
                                    </div>
                                    
                                    <?php if (isset($data['image']) && $data['image']): 
                                        $displayPath = (strpos($data['image'], '/') !== 0 && strpos($data['image'], 'http') !== 0) ? '../' . $data['image'] : $data['image'];
                                    ?>
                                        <div class="current-file">
                                            <strong><?php echo $isEnglish ? 'Current Photo:' : '当前照片:'; ?></strong> <?php echo htmlspecialchars(basename($data['image'])); ?><br>
                                            <small><?php echo $isEnglish ? 'Updated:' : '更新时间:'; ?> <?php echo htmlspecialchars($data['updated'] ?? 'N/A'); ?></small>
                                            
                                            <div class="preview-container">
                                                <img class="preview-image" src="<?php echo htmlspecialchars($displayPath); ?>?v=<?php echo time(); ?>" alt="Photo">
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <button type="submit" class="btn"><?php echo $isEnglish ? 'Upload Photo' : '上传照片'; ?></button>
                            </form>
                            
                            <!-- 文案编辑表单 -->
                            <div class="content-form">
                                <h4><?php echo $isEnglish ? 'Edit Details' : '编辑文案内容'; ?></h4>
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
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <script src="../js/aboutpage4upload.js"></script>
</body>
</html>
