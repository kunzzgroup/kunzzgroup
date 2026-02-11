<?php
require_once dirname(__DIR__) . '/core/init.php';
require_once CORE_PATH . '/session_check.php';

include_once '../media_config.php';

// 处理语言版本切换
$language = isset($_GET['lang']) ? $_GET['lang'] : 'zh';
$isEnglish = ($language === 'en');
$configFileName = $isEnglish ? 'timeline_config_en.json' : 'timeline_config.json';

// 尝试多个可能的配置文件路径
$possiblePaths = [
    __DIR__ . '/../' . $configFileName,  // 从 backend 目录访问根目录（绝对路径）
    '../' . $configFileName,              // 相对路径
    '../../' . $configFileName,            // 从其他子目录
    $configFileName,                       // 当前目录
];

$configFile = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        $configFile = $path;
        break;
    }
}

// 如果找不到文件，使用默认路径（用于创建新文件）
if (!$configFile) {
    $configFile = __DIR__ . '/../' . $configFileName;
    error_log("警告：找不到配置文件，将使用默认路径: $configFile");
} else {
    error_log("信息：找到配置文件: $configFile");
}

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
    try {
        // 统一为扁平结构
        $flat = normalizeToFlatArray($config);
        // 排序
        usort($flat, function($a,$b){
            $ay=(int)($a['year']??0); $by=(int)($b['year']??0);
            if ($ay===$by) { return (int)($a['month']??0) - (int)($b['month']??0); }
            return $ay - $by;
        });
        $dir = dirname($configFile);
        if (!is_dir($dir)) { 
            if (!@mkdir($dir, 0777, true)) {
                error_log("错误：无法创建配置目录: $dir");
                return false;
            }
        }
        
        // 检查目录是否可写
        if (!is_writable($dir)) {
            error_log("错误：配置目录不可写: $dir");
            return false;
        }
        
        $fp = @fopen($configFile, 'c+');
        if ($fp) { @flock($fp, LOCK_EX); }
        $tmp = $configFile . '.tmp.' . getmypid();
        $jsonData = json_encode($flat, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($jsonData === false) {
            error_log("错误：JSON 编码失败: " . json_last_error_msg());
            if ($fp) { @flock($fp, LOCK_UN); @fclose($fp); }
            return false;
        }
        $ok = @file_put_contents($tmp, $jsonData);
        if ($ok !== false) {
            if (!@rename($tmp, $configFile)) {
                error_log("错误：无法重命名临时文件到: $configFile");
                @unlink($tmp);
                $ok = false;
            }
        } else {
            error_log("错误：无法写入临时文件: $tmp");
            @unlink($tmp);
        }
        if ($fp) { @flock($fp, LOCK_UN); @fclose($fp); }
        return $ok !== false;
    } catch (Exception $e) {
        error_log("错误：写入配置文件时发生异常: " . $e->getMessage());
        return false;
    }
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
if (file_exists($configFile) && is_readable($configFile)) {
    $content = @file_get_contents($configFile);
    if ($content !== false) {
        $raw = json_decode($content, true);
        if ($raw === null && json_last_error() !== JSON_ERROR_NONE) {
            error_log("错误：配置文件 JSON 解析失败: " . json_last_error_msg() . " (文件: $configFile)");
            $raw = [];
        }
        $raw = $raw ?: [];
        
        // 检查数组结构：如果是关联数组（按年份分组），转换为扁平数组
        if ($raw && array_keys($raw) !== range(0, count($raw) - 1)) {
            // 按年份分组的结构
            foreach ($raw as $yearKey => $entries) {
                if (is_array($entries)) {
                    // 检查 entries 是否是索引数组（多条记录）还是关联数组（单条记录）
                    if (array_keys($entries) === range(0, count($entries) - 1)) {
                        // 多条记录
                        foreach ($entries as $entry) {
                            $entryArray = is_array($entry) ? $entry : [ 'title' => (string)$entry ];
                            $items[] = array_merge($entryArray, [ 
                                'year' => (string)$yearKey, 
                                'month' => isset($entryArray['month']) ? (int)$entryArray['month'] : 0 
                            ]);
                        }
                    } else {
                        // 单条记录
                        $entryArray = $entries;
                        $items[] = array_merge($entryArray, [ 
                            'year' => (string)$yearKey, 
                            'month' => isset($entryArray['month']) ? (int)$entryArray['month'] : 0 
                        ]);
                    }
                } else {
                    // 非数组条目
                    $items[] = [
                        'title' => (string)$entries,
                        'year' => (string)$yearKey,
                        'month' => 0
                    ];
                }
            }
        } else {
            // 扁平数组结构
            $items = $raw;
        }
        
        // 调试信息（生产环境可移除）
        if (empty($items)) {
            error_log("警告：读取配置文件后 items 为空 (文件: $configFile, 原始数据数量: " . count($raw) . ", 数组类型: " . (is_array($raw) && array_keys($raw) === range(0, count($raw) - 1) ? '扁平数组' : '关联数组') . ")");
        } else {
            error_log("信息：成功读取 " . count($items) . " 条记录 (文件: $configFile)");
        }
    } else {
        error_log("错误：无法读取配置文件: $configFile");
    }
} else {
    error_log("信息：配置文件不存在或不可读: $configFile");
}

// 默认时间线数据已移除 - 不再自动添加默认记录

// 移除自动添加默认数据的逻辑
// if (empty($items)) {
//     $items = $defaultTimeline;

require __DIR__ . '/templates/aboutpage4upload.php';

