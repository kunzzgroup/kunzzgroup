<?php
session_start();

// 检查是否已登录（根据你的登录系统调整）
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

include_once '../media_config.php';

// 处理语言版本切换
$language = isset($_GET['lang']) ? $_GET['lang'] : 'zh';
$isEnglish = ($language === 'en');
$configFile = $isEnglish ? '../timeline_config_en.json' : '../timeline_config.json';
$uploadDir = '../images/images/';

// 安全写入：规范化为扁平结构 + 文件锁 + 原子重命名
function normalizeToFlatArray($raw) {
    if (!is_array($raw)) return [];
    // 已是扁平数组
    if (array_keys($raw) === range(0, count($raw) - 1)) {
        // 仅保留数组项
        return array_values(array_filter($raw, 'is_array'));
    }
    // 按年分组
    $flat = [];
    foreach ($raw as $yearKey => $entries) {
        if (!is_array($entries)) continue;
        $isList = array_keys($entries) === range(0, count($entries) - 1);
        if ($isList) {
            foreach ($entries as $entry) {
                $entryArray = is_array($entry) ? $entry : [ 'title' => (string)$entry ];
                $flat[] = array_merge($entryArray, [
                    'year' => (string)$yearKey,
                    'month' => isset($entryArray['month']) ? (int)$entryArray['month'] : 0,
                ]);
            }
        } else {
            $entryArray = $entries;
            $flat[] = array_merge($entryArray, [
                'year' => (string)$yearKey,
                'month' => isset($entryArray['month']) ? (int)$entryArray['month'] : 0,
            ]);
        }
    }
    return $flat;
}

function writeTimelineConfig($configFile, $config) {
    // 统一为扁平结构
    $flat = normalizeToFlatArray($config);
    // 排序
    usort($flat, function($a,$b){
        $ay=(int)($a['year']??0); $by=(int)($b['year']??0);
        if ($ay===$by) { return (int)($a['month']??0) - (int)($b['month']??0); }
        return $ay - $by;
    });
    $dir = dirname($configFile);
    if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
    $fp = @fopen($configFile, 'c+');
    if ($fp) { @flock($fp, LOCK_EX); }
    $tmp = $configFile . '.tmp.' . getmypid();
    $ok = @file_put_contents($tmp, json_encode($flat, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    if ($ok !== false) {
        @rename($tmp, $configFile);
    } else {
        @unlink($tmp);
    }
    if ($fp) { @flock($fp, LOCK_UN); @fclose($fp); }
    return $ok !== false;
}

// 处理新增发展记录（年+月）
if (isset($_POST['add_record'])) {
    try {
        $newYear = trim($_POST['new_year']);
        $newMonth = trim($_POST['new_month']);
        if ($newYear && is_numeric($newYear) && $newYear >= 1900 && $newYear <= 2100 &&
            $newMonth && is_numeric($newMonth) && $newMonth >= 1 && $newMonth <= 12) {
            $defaultData = [
                'title' => $isEnglish ? 'New Milestone ✨' : '新的里程碑 ✨',
                'description1' => $isEnglish ? 'Please fill in the first description here...' : '请在这里填写第一段描述...',
                'description2' => $isEnglish ? 'Please fill in the second description here...' : '请在这里填写第二段描述...',
                'image' => 'images/images/default.jpg',
                'year' => (string)$newYear,
                'month' => (int)$newMonth,
                'created' => date('Y-m-d H:i:s')
            ];
            // 读取现有（扁平）配置或从旧结构迁移
            $config = [];
            if (file_exists($configFile)) {
                $raw = json_decode(file_get_contents($configFile), true) ?: [];
                // 旧结构：按年份分组
                if ($raw && array_keys($raw) !== range(0, count($raw) - 1)) {
                    foreach ($raw as $yearKey => $entries) {
                        if (is_array($entries)) {
                            foreach ($entries as $entry) {
                                $entryArray = is_array($entry) ? $entry : [ 'title' => (string)$entry ];
                                $config[] = array_merge($entryArray, [
                                    'year' => (string)$yearKey,
                                    'month' => isset($entryArray['month']) ? (int)$entryArray['month'] : 0,
                                ]);
                            }
                        }
                    }
                } else {
                    $config = $raw;
                }
            }
            // 生成唯一ID
            $defaultData['id'] = 'rec_' . time() . '_' . mt_rand(1000, 9999);
            $config[] = $defaultData;
            // 排序：年升序，月升序
            usort($config, function($a, $b) {
                $ay = (int)($a['year'] ?? 0); $by = (int)($b['year'] ?? 0);
                if ($ay === $by) { return (int)($a['month'] ?? 0) - (int)($b['month'] ?? 0); }
                return $ay - $by;
            });
            if (writeTimelineConfig($configFile, $config)) {
                $success = $isEnglish ? "Record added: {$newYear}/{$newMonth}" : "新增成功：{$newYear}年{$newMonth}月";
            } else {
                $error = $isEnglish ? "Failed to add record!" : "新增失败！";
            }
        } else {
            $error = $isEnglish ? "Please enter valid year (1900-2100) and month (1-12)!" : "请输入有效的年份（1900-2100）与月份（1-12）！";
        }
    } catch (Exception $e) {
        error_log("Error adding year entry: " . $e->getMessage());
        $error = $isEnglish ? "An error occurred while adding the record!" : "新增记录时发生错误！";
    }
}

// 处理删除记录（按id）
if (isset($_POST['delete_record'])) {
    try {
        $recordId = $_POST['record_id'];
        if (!file_exists($configFile)) { throw new Exception('config not found'); }
        $config = json_decode(file_get_contents($configFile), true) ?: [];
        // 兼容旧结构：先扁平化
        if ($config && array_keys($config) !== range(0, count($config) - 1)) {
            $flat = [];
            foreach ($config as $yearKey => $entries) {
                foreach ($entries as $entry) {
                    $entryArray = is_array($entry) ? $entry : [ 'title' => (string)$entry ];
                    $flat[] = array_merge($entryArray, [ 'year' => (string)$yearKey ]);
                }
            }
            $config = $flat;
        }
        $before = count($config);
        $configAfterId = array_values(array_filter($config, function($item) use ($recordId) { return ($item['id'] ?? '') !== $recordId; }));
        if (count($configAfterId) < $before) {
            $config = $configAfterId;
            $saved = writeTimelineConfig($configFile, $config);
            if ($saved) {
                $success = $isEnglish ? "Record deleted successfully!" : "记录删除成功！";
            } else {
                $error = $isEnglish ? "Failed to delete record!" : "删除记录失败！";
            }
        } else {
            // Fallback: 支持删除没有id的旧记录（使用渲染时生成的 rec_YEAR_INDEX 伪ID）
            $deleted = false;
            if (preg_match('/^rec_(\d{4})_(\d+)$/', $recordId, $m)) {
                $targetYear = $m[1];
                $targetIndex = max(0, (int)$m[2] - 1);
                // 取出该年份的记录顺序并定位第 targetIndex 条
                $yearPositions = [];
                foreach ($config as $idx => $item) {
                    if ((string)($item['year'] ?? '') === (string)$targetYear) {
                        $yearPositions[] = $idx;
                    }
                }
                if (isset($yearPositions[$targetIndex])) {
                    $removeIdx = $yearPositions[$targetIndex];
                    array_splice($config, $removeIdx, 1);
                    $deleted = true;
                }
            }
            if ($deleted && writeTimelineConfig($configFile, $config)) {
                $success = $isEnglish ? "Record deleted successfully!" : "记录删除成功！";
            } else {
                $error = $isEnglish ? "Failed to delete record!" : "删除记录失败！";
            }
        }
    } catch (Exception $e) {
        error_log("Error deleting entry: " . $e->getMessage());
        $error = $isEnglish ? "An error occurred while deleting the record!" : "删除记录时发生错误！";
    }
}

// 处理文件上传和文案修改
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 确保上传目录存在
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // 处理照片上传（按记录ID）
    if (isset($_FILES['timeline_image']) && $_FILES['timeline_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['timeline_image'];
        $recordId = $_POST['record_id'] ?? '';
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // 允许的文件类型
        $allowedImage = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($fileExtension, $allowedImage)) {
            // 生成新文件名（以记录ID）
            $newFileName = 'timeline_' . ($recordId ?: ('rec_' . time())) . '_发展.' . $fileExtension;
            $targetPath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // 更新配置文件
                $config = [];
                if (file_exists($configFile)) {
                    $config = json_decode(file_get_contents($configFile), true) ?: [];
                }
                // 兼容旧结构：扁平化
                if ($config && array_keys($config) !== range(0, count($config) - 1)) {
                    $flat = [];
                    foreach ($config as $yearKey => $entries) {
                        foreach ($entries as $entry) {
                            $entryArray = is_array($entry) ? $entry : [ 'title' => (string)$entry ];
                            $flat[] = array_merge($entryArray, [ 'year' => (string)$yearKey ]);
                        }
                    }
                    $config = $flat;
                }
                // 定位记录
                $found = false;
                foreach ($config as &$item) {
                    if (($item['id'] ?? '') === $recordId) {
                        $relativePath = 'images/images/' . $newFileName;
                        $item['image'] = $relativePath;
                        $item['updated'] = date('Y-m-d H:i:s');
                        $found = true;
                        break;
                    }
                }
                unset($item);
                if (!$found) {
                    $error = $isEnglish ? "Record not found! Please add the record first." : "记录未找到！请先添加记录。";
                }
                
                error_log("Photo uploaded: $targetPath, stored as: $relativePath");
                
                if ($found && writeTimelineConfig($configFile, $config)) {
                    $success = $isEnglish ? "Photo uploaded successfully!" : "照片上传成功！";
                    // 重新加载配置以更新页面显示
                    $config = json_decode(file_get_contents($configFile), true) ?: [];
                    error_log("Config reloaded after photo upload: " . json_encode($config[$year][$entryId] ?? 'not found'));
                } else if (!$found) {
                    // 错误已在上面设置
                } else {
                    $error = $isEnglish ? "Photo upload failed!" : "照片上传失败！";
                }
            } else {
                $error = $isEnglish ? "Photo upload failed!" : "照片上传失败！";
            }
        } else {
            $error = $isEnglish ? "Unsupported file type! Only JPG, PNG, WebP formats are supported" : "不支持的文件类型！仅支持 JPG, PNG, WebP 格式";
        }
    }
    
    // 处理文案与时间更新（记录ID + 年/月）
    if (isset($_POST['update_content'])) {
        $recordId = $_POST['record_id'] ?? '';
        $title = $_POST['title'];
        $description1 = $_POST['description1'];
        $description2 = $_POST['description2'];
        $year = $_POST['year'];
        $month = (int)($_POST['month'] ?? 0);
        
        // 更新配置文件
        $config = [];
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true) ?: [];
        }
        // 兼容旧结构：扁平化
        if ($config && array_keys($config) !== range(0, count($config) - 1)) {
            $flat = [];
            foreach ($config as $yearKey => $entries) {
                foreach ($entries as $entry) {
                    $entryArray = is_array($entry) ? $entry : [ 'title' => (string)$entry ];
                    $flat[] = array_merge($entryArray, [ 'year' => (string)$yearKey ]);
                }
            }
            $config = $flat;
        }
        // 更新或新建
        $updated = false;
        foreach ($config as &$item) {
            if (($item['id'] ?? '') === $recordId) {
                $item['title'] = $title;
                $item['description1'] = $description1;
                $item['description2'] = $description2;
                $item['year'] = (string)$year;
                $item['month'] = $month;
                $item['updated'] = date('Y-m-d H:i:s');
                $updated = true;
                break;
            }
        }
        unset($item);
        if (!$updated) {
            $error = $isEnglish ? "Record not found! Please add the record first." : "记录未找到！请先添加记录。";
        }
        if ($updated) {
            // 排序
            usort($config, function($a,$b){
                $ay=(int)($a['year']??0);$by=(int)($b['year']??0);
                if($ay===$by){return (int)($a['month']??0)-(int)($b['month']??0);}return $ay-$by;
            });
            
            if (writeTimelineConfig($configFile, $config)) {
                $success = $isEnglish ? "Content updated successfully!" : "文案更新成功！";
                // 重新加载配置以更新页面显示
                $config = json_decode(file_get_contents($configFile), true) ?: [];
            } else {
                $error = $isEnglish ? "Content update failed!" : "文案更新失败！";
            }
        }
    }
}

// 读取当前配置（扁平记录列表）
$items = [];
if (file_exists($configFile)) {
    $raw = json_decode(file_get_contents($configFile), true) ?: [];
    if ($raw && array_keys($raw) !== range(0, count($raw) - 1)) {
        foreach ($raw as $yearKey => $entries) {
            foreach ($entries as $entry) {
                $entryArray = is_array($entry) ? $entry : [ 'title' => (string)$entry ];
                $items[] = array_merge($entryArray, [ 'year' => (string)$yearKey, 'month' => isset($entryArray['month']) ? (int)$entryArray['month'] : 0 ]);
            }
        }
    } else {
        $items = $raw;
    }
}

// 默认时间线数据已移除 - 不再自动添加默认记录

// 移除自动添加默认数据的逻辑
// if (empty($items)) {
//     $items = $defaultTimeline;
// }
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEnglish ? 'Timeline Management' : '发展历史管理'; ?> - KUNZZ HOLDINGS</title>
    <style>
        * {
            font-size: clamp(8px, 0.74vw, 14px);
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #faf7f2;
            min-height: 100vh;
            padding: 0px;
        }
        
        .container {
            max-width: 1800px;
            margin: 0 auto;
            padding: clamp(16px, 1.25vw, 24px) 24px;
            background: #faf7f2;
            border-radius: 15px;
            overflow: hidden;
        }
        
        .header {
            background: transparent;
            color: #000000ff;
            text-align: center;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .header h1 {
            font-size: clamp(20px, 2.6vw, 50px);
            margin-bottom: 0;
            text-align: left;
        }
        
        .language-switch {
            display: flex;
            gap: 5px;
        }
        
        .language-switch .btn {
            padding: 5px 10px;
            font-size: clamp(6px, 0.63vw, 12px);
            background: #6c757d;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
        }
        
        .language-switch .btn.active {
            background: #f99e00;
        }
        
        .language-switch .btn:hover {
            background: #f99e00;
            transform: translateY(-1px);
        }
        
        .header p {
            opacity: 0.9;
            font-size: 1.1em;
        }
        
        .breadcrumb {
            padding: clamp(2px, 1.04vw, 20px) 0px clamp(10px, 1.04vw, 20px);
            background: transparent;
        }
        
        .breadcrumb a {
            font-size: clamp(8px, 0.74vw, 14px);
            color: #f99e00;
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .content {
            padding: 0;
        }
        
        .timeline-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: clamp(18px, 1.56vw, 30px);
            margin-bottom: 30px;
            border-left: 5px solid #000000ff;
            box-shadow: 0px 5px 15px rgb(0 0 0 / 60%);
        }
        
        .timeline-section h2 {
            color: #333;
            margin-bottom: clamp(10px, 1.04vw, 20px);
            font-size: clamp(16px, 1.5vw, 28px);
            display: flex;
            align-items: center;
            gap: 0px;
        }
        
        .year-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 0px;
        }
        
        .year-tab {
            background: #6c757d;
            color: white;
            border: none;
            font-size: clamp(6px, 0.63vw, 12px);
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            border-radius: clamp(4px, 0.32vw, 6px);
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .year-tab.active {
            background: #f99e00;
        }
        
        .year-tab:hover {
            transform: translateY(-2px);
        }
        
        .timeline-content {
            display: none;
        }
        
        .timeline-content.active {
            display: block;
        }
        
        .upload-form {
            display: grid;
            gap: clamp(10px, 1.04vw, 20px);
            margin-bottom: clamp(20px, 1.56vw, 30px);
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-group label {
            font-size: clamp(10px, 0.84vw, 16px) !important;
            font-weight: 600;
            color: #555;
        }
        
        .file-input {
            border: 2px dashed #000000ff;
            border-radius: 10px;
            padding: clamp(20px, 2.08vw, 40px);
            text-align: center;
            background: #fff9f5;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .file-input:hover {
            border-color: #000000ff;
            background: #fff5f0;
        }
        
        .file-input input {
            display: none;
        }
        
        .file-input-text {
            color: #000000ff;
            font-size: clamp(10px, 0.84vw, 16px);
            font-weight: 500;
        }
        
        .current-file {
            font-size: clamp(10px, 0.84vw, 16px);
            margin-top: clamp(5px, 0.78vw, 15px);
            padding: 15px;
            background: #e8f4f8;
            border-radius: 8px;
            border-left: 4px solid #000000ff;
        }
        
        .current-file strong {
            font-size: clamp(10px, 0.84vw, 16px);
            color: #155724;
        }
        
        .preview-container {
            margin-top: clamp(10px, 1.04vw, 20px);
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .preview-image {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
        }
        
        .content-form {
            background: white;
            padding: clamp(15px, 1.3vw, 25px);
            border-radius: 10px;
            border: 1px solid #dee2e6;
            margin-top: 20px;
        }
        
        .content-form h3 {
            font-size: clamp(12px, 0.94vw, 18px);
            color: #333;
            margin-bottom: clamp(10px, 1.04vw, 20px);
            padding-bottom: 10px;
            border-bottom: 2px solid #000000ff;
        }
        
        .form-input {
            width: 100%;
            padding: clamp(4px, 0.42vw, 8px) 12px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: clamp(10px, 0.84vw, 16px);
            transition: border-color 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #583e04;
        }
        
        .form-textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 1em;
            min-height: 100px;
            resize: vertical;
            font-family: inherit;
            transition: border-color 0.3s ease;
        }
        
        .form-textarea:focus {
            outline: none;
            border-color: #000000ff;
        }
        
        .btn {
            background: #f99e00;
            color: white;
            border: none;
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            border-radius: clamp(4px, 0.42vw, 8px);
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background: #f98500ff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 92, 0, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            margin-left: 10px;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .back-btn {
            display: inline-block;
            background: #6c757d;
            color: white;
            text-decoration: none;
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            font-size: clamp(8px, 0.74vw, 14px);
            border-radius: clamp(4px, 0.32vw, 6px);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: clamp(10px, 1.04vw, 20px);
        }
        
        @media (max-width: 768px) {
            .content {
                padding: 20px;
            }
            
            .timeline-section {
                padding: 20px;
            }
            
            .year-tabs {
                flex-wrap: wrap;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn-secondary {
                margin-left: 0;
            }
        }

        .year-management {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: clamp(0px, 1.56vw, 30px);
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .year-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-add {
            background: #28a745;
            font-size: clamp(6px, 0.63vw, 12px);
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
        }
        
        .btn-add:hover {
            background: #218838;
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-danger:hover {
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
            background: #c82333;
            border-color: #bd2130;
            transform: translateY(-2px);
        }
        
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .modal-content h3 {
            margin-bottom: 20px;
            color: #333;
        }
        
        @media (max-width: 768px) {
            .year-management {
                flex-direction: column;
                align-items: stretch;
            }
            
            .year-tabs {
                justify-content: center;
            }
            
            .year-actions {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
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
                        $years = array_values(array_unique(array_map(function($it){ return (string)($it['year'] ?? ''); }, $items)));
                        sort($years, SORT_NUMERIC);
                        foreach ($years as $index => $year): 
                        ?>
                            <button class="year-tab <?php echo $index === 0 ? 'active' : ''; ?>" onclick="showYear('<?php echo $year; ?>')"><?php echo $year; ?><?php echo $isEnglish ? '' : '年'; ?></button>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="year-actions">
                        <button type="button" class="btn btn-add" onclick="showAddRecordModal()">+ <?php echo $isEnglish ? 'Add Record' : '新增记录'; ?></button>
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
        // 年份切换功能
        function showYear(year) {
            // 隐藏所有内容
            document.querySelectorAll('.timeline-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // 移除所有标签的active状态
            document.querySelectorAll('.year-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // 显示选中年份的内容
            document.getElementById('content-' + year).classList.add('active');
            
            // 激活选中的标签
            event.target.classList.add('active');
        }

        // 新增记录模态框
        function showAddRecordModal() {
            document.getElementById('addRecordModal').style.display = 'flex';
        }
        
        function hideAddRecordModal() {
            document.getElementById('addRecordModal').style.display = 'none';
        }
        
        // 确认删除记录
        function confirmDeleteRecord(recordId) {
            const isEnglish = <?php echo $isEnglish ? 'true' : 'false'; ?>;
            const message = isEnglish ? `Are you sure you want to delete this record? This action cannot be undone!` : `确定要删除这个记录吗？此操作不可撤销！`;
            
            if (confirm(message)) {
                const form = document.createElement('form');
                form.method = 'post';
                form.innerHTML = `
                    <input type="hidden" name="delete_record" value="1">
                    <input type="hidden" name="record_id" value="${recordId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // 点击模态框外部关闭
        document.getElementById('addRecordModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideAddRecordModal();
            }
        });
        
        // 修改showYear函数，支持动态年份
        function showYear(year) {
            // 隐藏所有内容
            document.querySelectorAll('.timeline-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // 移除所有标签的active状态
            document.querySelectorAll('.year-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // 显示选中年份的内容
            const targetContent = document.getElementById('content-' + year);
            if (targetContent) {
                targetContent.classList.add('active');
            }
            
            // 激活选中的标签
            event.target.classList.add('active');
        }
        
        // 照片上传成功后刷新图片显示
        function refreshImageDisplayByRecord(recordId) {
            const imageElement = document.querySelector(`.entry-container[data-record-id="${recordId}"] .preview-image`);
            if (imageElement) {
                const currentSrc = imageElement.src;
                const newSrc = currentSrc.split('?')[0] + '?v=' + Date.now();
                imageElement.src = newSrc;
            }
        }
        
        // 监听照片上传表单提交
        document.querySelectorAll('form[enctype="multipart/form-data"]').forEach(form => {
            form.addEventListener('submit', function(e) {
                const recordId = this.querySelector('input[name="record_id"]').value;
                
                // 延迟刷新，等待服务器处理完成
                setTimeout(() => {
                    refreshImageDisplayByRecord(recordId);
                }, 1000);
            });
        });
        
        // 重置表单
        function resetForm(year) {
            const form = document.querySelector(`#content-${year} .content-form form`);
            const isEnglish = <?php echo $isEnglish ? 'true' : 'false'; ?>;
            const message = isEnglish ? 'Are you sure you want to reset the form? All unsaved changes will be lost.' : '确定要重置表单吗？所有未保存的更改将丢失。';
            if (confirm(message)) {
                form.reset();
            }
        }
        
        // 文件拖拽和选择功能
        document.querySelectorAll('.file-input').forEach(input => {
            input.addEventListener('dragover', (e) => {
                e.preventDefault();
                input.style.borderColor = '#e54a00';
                input.style.background = '#fff5f0';
            });
            
            input.addEventListener('dragleave', (e) => {
                e.preventDefault();
                input.style.borderColor = '#FF5C00';
                input.style.background = '#fff9f5';
            });
            
            input.addEventListener('drop', (e) => {
                e.preventDefault();
                const files = e.dataTransfer.files;
                const fileInput = input.querySelector('input[type="file"]');
                fileInput.files = files;
                
                input.style.borderColor = '#FF5C00';
                input.style.background = '#fff9f5';
                
                if (files.length > 0) {
                    const textDiv = input.querySelector('.file-input-text');
                    const isEnglish = <?php echo $isEnglish ? 'true' : 'false'; ?>;
                    textDiv.innerHTML = isEnglish ? `Selected: ${files[0].name}` : `已选择: ${files[0].name}`;
                }
            });
        });
        
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                const textDiv = this.parentElement.querySelector('.file-input-text');
                if (this.files.length > 0) {
                    const isEnglish = <?php echo $isEnglish ? 'true' : 'false'; ?>;
                    textDiv.innerHTML = isEnglish ? `Selected: ${this.files[0].name}` : `已选择: ${this.files[0].name}`;
                }
            });
        });
        
        // 表单验证
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('[required]');
                let isValid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.style.borderColor = '#dc3545';
                    } else {
                        field.style.borderColor = '#e9ecef';
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    const isEnglish = <?php echo $isEnglish ? 'true' : 'false'; ?>;
                    const message = isEnglish ? 'Please fill in all required fields' : '请填写所有必填字段';
                    alert(message);
                }
            });
        });
    </script>
</body>
</html>