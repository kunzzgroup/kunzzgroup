<?php
/**
 * About Page 4 (Timeline) Upload Shell
 */
require_once '../system/session_check.php';

// Handle Language
$language = isset($_GET['lang']) ? $_GET['lang'] : 'zh';
$isEnglish = ($language === 'en');
$configFileName = $isEnglish ? 'timeline_config_en.json' : 'timeline_config.json';
$configFile = __DIR__ . '/../' . $configFileName;
$uploadDir = '../images/images/';

$success = "";
$error = "";

// Helper: Normalize and Write Config
function writeTimelineConfig($file, $config) {
    // Basic sorting and normalization before write
    usort($config, function($a, $b) {
        $ay = (int)($a['year'] ?? 0); $by = (int)($b['year'] ?? 0);
        if ($ay === $by) return (int)($a['month'] ?? 0) - (int)($b['month'] ?? 0);
        return $ay - $by;
    });
    return file_put_contents($file, json_encode(array_values($config), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Read Current Data
function readTimelineConfig($file) {
    if (!file_exists($file)) return [];
    $data = json_decode(file_get_contents($file), true) ?: [];
    // Handle old associative structure if exists
    if (!empty($data) && array_keys($data) !== range(0, count($data) - 1)) {
        $flat = [];
        foreach ($data as $year => $entries) {
            if (is_array($entries)) {
                foreach ($entries as $entry) {
                    $entry['year'] = (string)$year;
                    $flat[] = $entry;
                }
            }
        }
        return $flat;
    }
    return $data;
}

$items = readTimelineConfig($configFile);

// Action: Add Record
if (isset($_POST['add_record'])) {
    $newYear = trim($_POST['new_year']);
    $newMonth = intval($_POST['new_month'] ?? 0);
    if ($newYear && is_numeric($newYear)) {
        $items[] = [
            'id' => 'rec_' . time() . '_' . mt_rand(1000, 9999),
            'year' => (string)$newYear,
            'month' => $newMonth,
            'title' => $isEnglish ? 'New Milestone' : '新的里程碑',
            'description1' => '',
            'description2' => '',
            'image' => 'images/images/default.jpg',
            'created' => date('Y-m-d H:i:s')
        ];
        if (writeTimelineConfig($configFile, $items)) {
            $success = "记录添加成功";
        } else {
            $error = "添加失败";
        }
    }
}

// Action: Delete Record
if (isset($_POST['delete_record'])) {
    $recordId = $_POST['record_id'];
    $items = array_filter($items, function($it) use ($recordId) { return ($it['id'] ?? '') !== $recordId; });
    if (writeTimelineConfig($configFile, $items)) {
        $success = "记录已删除";
    } else {
        $error = "删除失败";
    }
}

// Action: Update Content
if (isset($_POST['update_content'])) {
    $recordId = $_POST['record_id'] ?? '';
    foreach ($items as &$item) {
        if (($item['id'] ?? '') === $recordId) {
            $item['title'] = $_POST['title'] ?? $item['title'];
            $item['description1'] = $_POST['description1'] ?? $item['description1'];
            $item['description2'] = $_POST['description2'] ?? $item['description2'];
            $item['year'] = (string)($_POST['year'] ?? $item['year']);
            $item['month'] = intval($_POST['month'] ?? $item['month']);
            $item['updated'] = date('Y-m-d H:i:s');
            break;
        }
    }
    if (writeTimelineConfig($configFile, $items)) {
        $success = "内容更新完毕";
    }
}

// Action: Upload Photo
if (isset($_POST['upload_photo']) && isset($_FILES['timeline_image'])) {
    require_once '../api/media_upload_api.php';
    // Adapt $_POST for handleMediaUpload if needed
    $_POST['media_type'] = 'timeline_' . $_POST['record_id'];
    $_POST['upload_dir'] = '../images/images/';
    $result = handleMediaUpload($_POST, $_FILES);
    if ($result['success']) {
        foreach ($items as &$item) {
            if (($item['id'] ?? '') === $_POST['record_id']) {
                $item['image'] = ltrim($result['webPath'], '../'); // Ensure it matches the frontend's expectation
                $item['updated'] = date('Y-m-d H:i:s');
                break;
            }
        }
        writeTimelineConfig($configFile, $items);
        $success = "照片上传成功";
    } else {
        $error = $result['message'];
    }
}

// Re-read items if success to refresh template
$items = readTimelineConfig($configFile);

// Include the template
include '../templates/aboutpage4upload_template.php';
?>
