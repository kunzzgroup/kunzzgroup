<?php
$uploadActionUrl = isset($uploadActionUrl) ? $uploadActionUrl : 'aboutpage4upload.php';
$returnTo = isset($returnTo) ? $returnTo : '';
if (!is_array($items)) {
    $items = [];
}
$years = array_values(array_unique(array_map(function ($it) {
    return (string)($it['year'] ?? '');
}, $items)));
$years = array_filter($years, function ($y) {
    return !empty($y);
});
$years = array_values($years);
sort($years, SORT_NUMERIC);
$firstYear = !empty($years) ? $years[0] : '';
?>
<div class="container" data-aboutpage4-content-root data-lang="<?php echo htmlspecialchars($language, ENT_QUOTES, 'UTF-8'); ?>" data-action-url="<?php echo htmlspecialchars($uploadActionUrl, ENT_QUOTES, 'UTF-8'); ?>">
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
        <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
            <div class="alert alert-success"><?php echo $isEnglish ? 'Operation completed successfully!' : '操作成功！'; ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if (!empty($_GET['error'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars((string)$_GET['error'], ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <div class="timeline-section">
            <h2><?php echo $isEnglish ? 'Timeline Content Management' : '时间线内容管理'; ?></h2>

            <div class="year-management">
                <div class="year-tabs">
                    <?php if (empty($years)): ?>
                        <span style="color: #666;"><?php echo $isEnglish ? 'No records yet' : '暂无记录'; ?></span>
                    <?php else: ?>
                        <?php foreach ($years as $index => $year): ?>
                            <button class="year-tab <?php echo $index === 0 ? 'active' : ''; ?>" onclick="showYear('<?php echo htmlspecialchars($year, ENT_QUOTES, 'UTF-8'); ?>')"><?php echo htmlspecialchars($year, ENT_QUOTES, 'UTF-8'); ?><?php echo $isEnglish ? '' : '年'; ?></button>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="year-actions" style="display: flex !important; gap: 10px !important; align-items: center !important;">
                    <button type="button" class="btn btn-add" onclick="showAddRecordModal()" style="display: inline-block !important; visibility: visible !important; opacity: 1 !important;">+ <?php echo $isEnglish ? 'Add Record' : '新增记录'; ?></button>
                </div>
            </div>

            <div id="addRecordModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <h3><?php echo $isEnglish ? 'Add New Record' : '新增发展记录'; ?></h3>
                    <form action="<?php echo htmlspecialchars($uploadActionUrl, ENT_QUOTES, 'UTF-8'); ?>" method="post">
                        <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($returnTo, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="lang" value="<?php echo htmlspecialchars($language, ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="form-group">
                            <label><?php echo $isEnglish ? 'Year' : '年份'; ?></label>
                            <input type="number" name="new_year" class="form-input" min="1900" max="2100" placeholder="<?php echo $isEnglish ? 'Enter year, e.g.: 2024' : '输入年份，例如：2024'; ?>" required>
                        </div>
                        <div class="form-group">
                            <label><?php echo $isEnglish ? 'Month' : '月份'; ?></label>
                            <?php if ($isEnglish): ?>
                            <select name="new_month" class="form-input" required>
                                <option value="">Select month...</option>
                                <?php foreach (['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $mi => $mn): ?>
                                <option value="<?php echo $mi + 1; ?>"><?php echo htmlspecialchars($mn, ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php else: ?>
                            <input type="number" name="new_month" class="form-input" min="1" max="12" placeholder="输入月份，1-12" required>
                            <?php endif; ?>
                        </div>
                        <div class="form-actions">
                            <button type="submit" name="add_record" class="btn"><?php echo $isEnglish ? 'Add Record' : '新增记录'; ?></button>
                            <button type="button" class="btn btn-secondary" onclick="hideAddRecordModal()"><?php echo $isEnglish ? 'Cancel' : '取消'; ?></button>
                        </div>
                    </form>
                </div>
            </div>

            <?php foreach ($years as $year): ?>
                <?php
                $yearItems = array_values(array_filter($items, function ($it) use ($year) {
                    return (string)($it['year'] ?? '') === (string)$year;
                }));
                ?>
            <div class="timeline-content <?php echo (string)$year === (string)$firstYear ? 'active' : ''; ?>" id="content-<?php echo htmlspecialchars($year, ENT_QUOTES, 'UTF-8'); ?>">
                <h3 style="margin-bottom: 20px; color: #333; border-bottom: 2px solid #f99e00; padding-bottom: 10px;">
                    <?php echo htmlspecialchars($year, ENT_QUOTES, 'UTF-8'); ?><?php echo $isEnglish ? '' : '年'; ?> - <?php echo $isEnglish ? 'Records' : '发展记录'; ?>
                </h3>

                <?php if (empty($yearItems)): ?>
                    <div class="no-entries" style="text-align: center; padding: 40px; color: #666; background: #f8f9fa; border-radius: 10px; margin: 20px 0;">
                        <p><?php echo $isEnglish ? 'No records for this year. Click "Add Record" to create one.' : '此年份暂无记录。点“新增记录”创建。'; ?></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($yearItems as $idx => $data): ?>
                        <?php
                        $entryIndex = $idx + 1;
                        $recordId = $data['id'] ?? ('rec_' . $year . '_' . $entryIndex);
                        $preview = aboutpage4upload_getImagePreview($data);
                        ?>
                    <div class="entry-container" data-record-id="<?php echo htmlspecialchars($recordId, ENT_QUOTES, 'UTF-8'); ?>" style="border: 1px solid #dee2e6; border-radius: 10px; padding: 20px; margin-bottom: 20px; background: white;">
                        <div class="entry-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h4 style="margin: 0; color: #555;"><?php echo $isEnglish ? 'Record' : '记录'; ?> #<?php echo (int)$entryIndex; ?><?php if (!empty($data['month'])) {
                                $enMonths = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                                echo ' · ' . ($isEnglish ? ($enMonths[(int)$data['month'] - 1] ?? 'Month ' . (int)$data['month']) : (int)$data['month'] . '月');
                            } ?></h4>
                            <button type="button" class="btn btn-danger" onclick="confirmDeleteRecord('<?php echo htmlspecialchars($recordId, ENT_QUOTES, 'UTF-8'); ?>')" style="padding: 5px 10px; font-size: 12px;">
                                <?php echo $isEnglish ? 'Delete' : '删除'; ?>
                            </button>
                        </div>

                        <form action="<?php echo htmlspecialchars($uploadActionUrl, ENT_QUOTES, 'UTF-8'); ?>" method="post" enctype="multipart/form-data" class="upload-form">
                            <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($returnTo, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="lang" value="<?php echo htmlspecialchars($language, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="record_id" value="<?php echo htmlspecialchars($recordId, ENT_QUOTES, 'UTF-8'); ?>">

                            <div class="form-group">
                                <label><?php echo $isEnglish ? 'Upload Photo for Entry #' . $entryIndex : '上传条目 #' . $entryIndex . ' 的照片'; ?></label>
                                <div class="file-input" onclick="document.getElementById('image-<?php echo htmlspecialchars($recordId, ENT_QUOTES, 'UTF-8'); ?>').click()">
                                    <input type="file" id="image-<?php echo htmlspecialchars($recordId, ENT_QUOTES, 'UTF-8'); ?>" name="timeline_image" accept="image/*">
                                    <div class="file-input-text">
                                        <?php echo $isEnglish ? 'Click to select photo or drag here' : '点击选择照片或拖拽到此处'; ?><br>
                                        <small><?php echo $isEnglish ? 'Supports JPG, PNG, WebP formats, recommended size 800x600' : '支持 JPG, PNG, WebP 格式（HEIC 自动转换），建议尺寸 800x600'; ?></small>
                                    </div>
                                </div>

                                <?php if ($preview['hasImage']): ?>
                                    <div class="current-file">
                                        <strong><?php echo $isEnglish ? 'Current Photo:' : '当前照片:'; ?></strong> <?php echo htmlspecialchars(basename($data['image']), ENT_QUOTES, 'UTF-8'); ?><br>
                                        <small><?php echo $isEnglish ? 'Updated:' : '更新时间:'; ?> <?php echo htmlspecialchars($data['updated'] ?? ($isEnglish ? 'Unknown' : '未知'), ENT_QUOTES, 'UTF-8'); ?></small>

                                        <div class="preview-container">
                                            <img class="preview-image" src="<?php echo htmlspecialchars($preview['displayPath'], ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo isset($data['updated']) ? (int)strtotime($data['updated']) : time(); ?>" alt="<?php echo htmlspecialchars($year, ENT_QUOTES, 'UTF-8') . ($isEnglish ? ' Photo' : '年照片'); ?>">
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <button type="submit" class="btn"><?php echo $isEnglish ? 'Upload Photo' : '上传照片'; ?></button>
                        </form>

                        <div class="content-form">
                            <h4><?php echo $isEnglish ? 'Edit Record #' . $entryIndex . ' Content' : '编辑记录 #' . $entryIndex . ' 文案内容'; ?></h4>
                            <form action="<?php echo htmlspecialchars($uploadActionUrl, ENT_QUOTES, 'UTF-8'); ?>" method="post">
                                <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($returnTo, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="lang" value="<?php echo htmlspecialchars($language, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="record_id" value="<?php echo htmlspecialchars($recordId, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="update_content" value="1">

                                <div class="form-group">
                                    <label><?php echo $isEnglish ? 'Year' : '年份'; ?></label>
                                    <input type="number" name="year" class="form-input" min="1900" max="2100" value="<?php echo htmlspecialchars($data['year'] ?? $year, ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label><?php echo $isEnglish ? 'Month' : '月份'; ?></label>
                                    <?php
                                    $currentMonth = (int)($data['month'] ?? 0);
                                    if ($isEnglish): ?>
                                    <select name="month" class="form-input">
                                        <option value="0">-- No specific month --</option>
                                        <?php foreach (['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $mi => $mn): ?>
                                        <option value="<?php echo $mi + 1; ?>" <?php echo $currentMonth === ($mi + 1) ? 'selected' : ''; ?>><?php echo htmlspecialchars($mn, ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php else: ?>
                                    <input type="number" name="month" class="form-input" min="1" max="12" value="<?php echo htmlspecialchars((string)($data['month'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label><?php echo $isEnglish ? 'Title' : '标题'; ?></label>
                                    <input type="text" name="title" class="form-input"
                                           value="<?php echo htmlspecialchars($data['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                           placeholder="<?php echo $isEnglish ? 'Enter title...' : '输入标题...'; ?>">
                                </div>

                                <div class="form-group">
                                    <label><?php echo $isEnglish ? 'First Description' : '第一段描述'; ?></label>
                                    <textarea name="description1" class="form-textarea"
                                              placeholder="<?php echo $isEnglish ? 'Enter first description...' : '输入第一段描述...'; ?>"><?php echo htmlspecialchars($data['description1'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label><?php echo $isEnglish ? 'Second Description' : '第二段描述'; ?></label>
                                    <textarea name="description2" class="form-textarea"
                                              placeholder="<?php echo $isEnglish ? 'Enter second description...' : '输入第二段描述...'; ?>"><?php echo htmlspecialchars($data['description2'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
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
