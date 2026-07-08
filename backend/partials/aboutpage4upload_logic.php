<?php

require_once __DIR__ . '/../heic_convert.php';

function aboutpage4upload_getLanguage() {
    if (isset($_POST['lang'])) {
        return ((string)$_POST['lang'] === 'en') ? 'en' : 'zh';
    }
    if (isset($_GET['lang'])) {
        return ((string)$_GET['lang'] === 'en') ? 'en' : 'zh';
    }

    return 'zh';
}

function aboutpage4upload_getReturnTo() {
    if (isset($_POST['return_to'])) {
        return (string)$_POST['return_to'];
    }
    if (isset($_GET['return_to'])) {
        return (string)$_GET['return_to'];
    }

    return '';
}

function aboutpage4upload_redirectAfterAction($query = '') {
    $lang = aboutpage4upload_getLanguage();
    $langQuery = '?lang=' . urlencode($lang) . $query;
    $returnTo = aboutpage4upload_getReturnTo();

    if ($returnTo === 'v2') {
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/backend')), '/');
        header('Location: ' . $base . '/aboutpage4upload-v2' . $langQuery);
        exit();
    }

    header('Location: aboutpage4upload.php' . $langQuery);
    exit();
}

function aboutpage4upload_redirectToV2WithError($error, $language = null) {
    $lang = $language ?? aboutpage4upload_getLanguage();
    $lang = ($lang === 'en') ? 'en' : 'zh';
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/backend')), '/');
    $query = '?lang=' . urlencode($lang) . '&error=' . urlencode($error);
    header('Location: ' . $base . '/aboutpage4upload-v2' . $query);
    exit();
}

function aboutpage4upload_normalizeToFlatArray($raw) {
    if (!is_array($raw)) {
        return [];
    }

    if (array_keys($raw) === range(0, count($raw) - 1)) {
        return array_values(array_filter($raw, 'is_array'));
    }

    $flat = [];
    foreach ($raw as $yearKey => $entries) {
        if (!is_array($entries)) {
            continue;
        }

        $isList = array_keys($entries) === range(0, count($entries) - 1);
        if ($isList) {
            foreach ($entries as $entry) {
                $entryArray = is_array($entry) ? $entry : ['title' => (string)$entry];
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

function aboutpage4upload_writeTimelineConfig($configFile, $config) {
    try {
        $flat = aboutpage4upload_normalizeToFlatArray($config);
        usort($flat, function ($a, $b) {
            $ay = (int)($a['year'] ?? 0);
            $by = (int)($b['year'] ?? 0);
            if ($ay === $by) {
                return (int)($a['month'] ?? 0) - (int)($b['month'] ?? 0);
            }

            return $ay - $by;
        });

        $dir = dirname($configFile);
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0777, true)) {
                error_log("错误：无法创建配置目录: $dir");
                return false;
            }
        }

        if (!is_writable($dir)) {
            error_log("错误：配置目录不可写: $dir");
            return false;
        }

        $jsonData = json_encode($flat, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($jsonData === false) {
            error_log('错误：JSON 编码失败: ' . json_last_error_msg());
            return false;
        }

        $tmp = $configFile . '.tmp.' . getmypid();
        $ok = @file_put_contents($tmp, $jsonData, LOCK_EX);
        if ($ok === false) {
            error_log("错误：无法写入临时文件: $tmp");
            @unlink($tmp);
            return false;
        }

        if (!@rename($tmp, $configFile)) {
            $ok = @file_put_contents($configFile, $jsonData, LOCK_EX);
            @unlink($tmp);
            if ($ok === false) {
                error_log("错误：无法写入配置文件: $configFile");
                return false;
            }
        }

        return true;
    } catch (Exception $e) {
        error_log('错误：写入配置文件时发生异常: ' . $e->getMessage());
        return false;
    }
}

function aboutpage4upload_resolveConfigFile($language) {
    $configFileName = ($language === 'en') ? 'timeline_config_en.json' : 'timeline_config.json';
    $possiblePaths = [
        __DIR__ . '/../../' . $configFileName,
        __DIR__ . '/../' . $configFileName,
    ];

    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }

    return __DIR__ . '/../../' . $configFileName;
}

function aboutpage4upload_loadItems($configFile) {
    $items = [];

    if (!file_exists($configFile) || !is_readable($configFile)) {
        return $items;
    }

    $content = @file_get_contents($configFile);
    if ($content === false) {
        error_log("错误：无法读取配置文件: $configFile");
        return $items;
    }

    $raw = json_decode($content, true);
    if ($raw === null && json_last_error() !== JSON_ERROR_NONE) {
        error_log('错误：配置文件 JSON 解析失败: ' . json_last_error_msg() . " (文件: $configFile)");
        $raw = [];
    }

    $raw = $raw ?: [];

    if ($raw && array_keys($raw) !== range(0, count($raw) - 1)) {
        foreach ($raw as $yearKey => $entries) {
            if (is_array($entries)) {
                if (array_keys($entries) === range(0, count($entries) - 1)) {
                    foreach ($entries as $entry) {
                        $entryArray = is_array($entry) ? $entry : ['title' => (string)$entry];
                        $items[] = array_merge($entryArray, [
                            'year' => (string)$yearKey,
                            'month' => isset($entryArray['month']) ? (int)$entryArray['month'] : 0,
                        ]);
                    }
                } else {
                    $entryArray = $entries;
                    $items[] = array_merge($entryArray, [
                        'year' => (string)$yearKey,
                        'month' => isset($entryArray['month']) ? (int)$entryArray['month'] : 0,
                    ]);
                }
            } else {
                $items[] = [
                    'title' => (string)$entries,
                    'year' => (string)$yearKey,
                    'month' => 0,
                ];
            }
        }
    } else {
        $items = $raw;
    }

    return $items;
}

function aboutpage4upload_getImagePreview($data) {
    $uploadDir = __DIR__ . '/../../images/images/';
    $displayPath = '';
    $hasImage = false;

    if (!isset($data['image'])) {
        return [
            'displayPath' => $displayPath,
            'hasImage' => $hasImage,
        ];
    }

    $originalPath = $data['image'];
    $fullPath = '';
    $possiblePaths = [
        $originalPath,
        '../' . $originalPath,
        '../../' . $originalPath,
        $uploadDir . basename($originalPath),
        __DIR__ . '/../../' . ltrim($originalPath, '/'),
    ];

    foreach ($possiblePaths as $testPath) {
        if (file_exists($testPath)) {
            $fullPath = $testPath;
            break;
        }
    }

    if ($fullPath) {
        if (strpos($originalPath, '/') !== 0 && strpos($originalPath, 'http') !== 0) {
            $displayPath = '../' . $originalPath;
        } else {
            $displayPath = $originalPath;
        }
        $hasImage = true;
    }

    return [
        'displayPath' => $displayPath,
        'hasImage' => $hasImage,
    ];
}

function aboutpage4upload_handlePost(&$success, &$error, $language) {
    $isEnglish = ($language === 'en');
    $configFile = aboutpage4upload_resolveConfigFile($language);
    $uploadDir = __DIR__ . '/../../images/images/';

    if (isset($_POST['add_record'])) {
        try {
            $newYear = trim($_POST['new_year']);
            $newMonth = trim($_POST['new_month']);
            if ($newYear && is_numeric($newYear) && $newYear >= 1900 && $newYear <= 2100
                && $newMonth && is_numeric($newMonth) && $newMonth >= 1 && $newMonth <= 12) {
                $defaultData = [
                    'title' => $isEnglish ? 'New Milestone ✨' : '新的里程碑 ✨',
                    'description1' => $isEnglish ? 'Please fill in the first description here...' : '请在这里填写第一段描述...',
                    'description2' => $isEnglish ? 'Please fill in the second description here...' : '请在这里填写第二段描述...',
                    'image' => 'images/images/default.jpg',
                    'year' => (string)$newYear,
                    'month' => (int)$newMonth,
                    'created' => date('Y-m-d H:i:s'),
                ];

                $config = [];
                if (file_exists($configFile)) {
                    $raw = json_decode(file_get_contents($configFile), true) ?: [];
                    if ($raw && array_keys($raw) !== range(0, count($raw) - 1)) {
                        foreach ($raw as $yearKey => $entries) {
                            if (is_array($entries)) {
                                foreach ($entries as $entry) {
                                    $entryArray = is_array($entry) ? $entry : ['title' => (string)$entry];
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

                $defaultData['id'] = 'rec_' . time() . '_' . mt_rand(1000, 9999);
                $config[] = $defaultData;
                usort($config, function ($a, $b) {
                    $ay = (int)($a['year'] ?? 0);
                    $by = (int)($b['year'] ?? 0);
                    if ($ay === $by) {
                        return (int)($a['month'] ?? 0) - (int)($b['month'] ?? 0);
                    }

                    return $ay - $by;
                });

                if (aboutpage4upload_writeTimelineConfig($configFile, $config)) {
                    aboutpage4upload_redirectAfterAction('&success=1&t=' . time());
                } else {
                    $error = $isEnglish ? 'Failed to add record!' : '新增失败！';
                }
            } else {
                $error = $isEnglish
                    ? 'Please enter valid year (1900-2100) and month (1-12)!'
                    : '请输入有效的年份（1900-2100）与月份（1-12）！';
            }
        } catch (Exception $e) {
            error_log('Error adding year entry: ' . $e->getMessage());
            $error = $isEnglish ? 'An error occurred while adding the record!' : '新增记录时发生错误！';
        }

        return;
    }

    if (isset($_POST['delete_record'])) {
        try {
            $recordId = $_POST['record_id'];
            if (!file_exists($configFile)) {
                throw new Exception('config not found');
            }

            $config = json_decode(file_get_contents($configFile), true) ?: [];
            if ($config && array_keys($config) !== range(0, count($config) - 1)) {
                $flat = [];
                foreach ($config as $yearKey => $entries) {
                    foreach ($entries as $entry) {
                        $entryArray = is_array($entry) ? $entry : ['title' => (string)$entry];
                        $flat[] = array_merge($entryArray, ['year' => (string)$yearKey]);
                    }
                }
                $config = $flat;
            }
            $before = count($config);
            $configAfterId = array_values(array_filter($config, function ($item) use ($recordId) {
                return ($item['id'] ?? '') !== $recordId;
            }));

            if (count($configAfterId) < $before) {
                $config = $configAfterId;
                if (aboutpage4upload_writeTimelineConfig($configFile, $config)) {
                    aboutpage4upload_redirectAfterAction('&success=1&t=' . time());
                } else {
                    $error = $isEnglish ? 'Failed to delete record!' : '删除记录失败！';
                }
            } else {
                $deleted = false;
                if (preg_match('/^rec_(\d{4})_(\d+)$/', $recordId, $m)) {
                    $targetYear = $m[1];
                    $targetIndex = max(0, (int)$m[2] - 1);
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

                if ($deleted && aboutpage4upload_writeTimelineConfig($configFile, $config)) {
                    aboutpage4upload_redirectAfterAction('&success=1&t=' . time());
                } else {
                    $error = $isEnglish ? 'Failed to delete record!' : '删除记录失败！';
                }
            }
        } catch (Exception $e) {
            error_log('Error deleting entry: ' . $e->getMessage());
            $error = $isEnglish ? 'An error occurred while deleting the record!' : '删除记录时发生错误！';
        }

        return;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (isset($_FILES['timeline_image']) && $_FILES['timeline_image']['error'] === UPLOAD_ERR_OK) {
        require_once __DIR__ . '/../heic_convert.php';

        $file = $_FILES['timeline_image'];
        $recordId = $_POST['record_id'] ?? '';
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedImage = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'];

        if (in_array($fileExtension, $allowedImage, true)) {
            $newFileName = 'timeline_' . ($recordId ?: ('rec_' . time())) . '_发展.' . $fileExtension;
            $targetPath = $uploadDir . $newFileName;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $converted = convertHeicToJpg($targetPath, $fileExtension);
                if ($converted['converted']) {
                    $targetPath = $converted['path'];
                    $newFileName = basename($converted['path']);
                    $fileExtension = 'jpg';
                }

                $config = [];
                if (file_exists($configFile)) {
                    $config = json_decode(file_get_contents($configFile), true) ?: [];
                }
                if ($config && array_keys($config) !== range(0, count($config) - 1)) {
                    $flat = [];
                    foreach ($config as $yearKey => $entries) {
                        foreach ($entries as $entry) {
                            $entryArray = is_array($entry) ? $entry : ['title' => (string)$entry];
                            $flat[] = array_merge($entryArray, ['year' => (string)$yearKey]);
                        }
                    }
                    $config = $flat;
                }

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
                    $error = $isEnglish ? 'Record not found! Please add the record first.' : '记录未找到！请先添加记录。';
                } elseif (aboutpage4upload_writeTimelineConfig($configFile, $config)) {
                    aboutpage4upload_redirectAfterAction('&success=1&t=' . time());
                } else {
                    $error = $isEnglish ? 'Photo upload failed!' : '照片上传失败！';
                }
            } else {
                $error = $isEnglish ? 'Photo upload failed!' : '照片上传失败！';
            }
        } else {
            $error = $isEnglish
                ? 'Unsupported file type! Only JPG, PNG, WebP formats are supported'
                : '不支持的文件类型！仅支持 JPG, PNG, WebP 格式（HEIC 自动转换）';
        }

        return;
    }

    if (isset($_POST['update_content'])) {
        $recordId = $_POST['record_id'] ?? '';
        $title = $_POST['title'];
        $description1 = $_POST['description1'];
        $description2 = $_POST['description2'];
        $year = $_POST['year'];
        $month = (int)($_POST['month'] ?? 0);

        $config = [];
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true) ?: [];
        }
        if ($config && array_keys($config) !== range(0, count($config) - 1)) {
            $flat = [];
            foreach ($config as $yearKey => $entries) {
                foreach ($entries as $entry) {
                    $entryArray = is_array($entry) ? $entry : ['title' => (string)$entry];
                    $flat[] = array_merge($entryArray, ['year' => (string)$yearKey]);
                }
            }
            $config = $flat;
        }

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
            $error = $isEnglish ? 'Record not found! Please add the record first.' : '记录未找到！请先添加记录。';
        } elseif (aboutpage4upload_writeTimelineConfig($configFile, $config)) {
            aboutpage4upload_redirectAfterAction('&success=1&t=' . time());
        } else {
            $error = $isEnglish ? 'Content update failed!' : '文案更新失败！';
        }
    }
}
