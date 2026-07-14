<?php

function corporate_blueprint_edit_getReturnTo() {
    if (isset($_POST['return_to'])) {
        return (string)$_POST['return_to'];
    }
    if (isset($_GET['return_to'])) {
        return (string)$_GET['return_to'];
    }

    return '';
}

function corporate_blueprint_edit_getBackendWebBase() {
    return rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/backend')), '/');
}

function corporate_blueprint_edit_getPageUrl($returnTo = null) {
    $returnTo = $returnTo ?? corporate_blueprint_edit_getReturnTo();
    $base = corporate_blueprint_edit_getBackendWebBase();

    if ($returnTo === 'v2') {
        return $base . '/corporate_blueprint_edit-v2';
    }

    return 'corporate_blueprint_edit.php';
}

function corporate_blueprint_edit_getFormActionUrl() {
    $base = corporate_blueprint_edit_getBackendWebBase();

    return $base . '/corporate_blueprint_edit.php';
}

function corporate_blueprint_edit_getViewUrl($returnTo = null) {
    $returnTo = $returnTo ?? corporate_blueprint_edit_getReturnTo();
    $base = corporate_blueprint_edit_getBackendWebBase();

    if ($returnTo === 'v2') {
        return $base . '/corporate_blueprint-v2';
    }

    return 'corporate_blueprint.php';
}

function corporate_blueprint_edit_redirectAfterSave($successMsg) {
    $base = corporate_blueprint_edit_getBackendWebBase();
    $params = [];

    if ($successMsg !== null && $successMsg !== '') {
        $params['success'] = $successMsg;
    }

    $query = empty($params) ? '' : ('?' . http_build_query($params));
    header('Location: ' . $base . '/corporate_blueprint_edit-v2' . $query);
    exit();
}

function corporate_blueprint_edit_getJsonFile() {
    return dirname(__DIR__) . '/corporate_strategy.json';
}

function corporate_blueprint_edit_handlePost(&$success, &$error) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    ini_set('max_input_vars', 5000);

    $jsonFile = corporate_blueprint_edit_getJsonFile();

    // 调试信息
    error_log("POST 请求已接收");
    error_log("POST 数据数量: " . count($_POST));
    error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? '未设置'));
    error_log("Content-Length: " . ($_SERVER['CONTENT_LENGTH'] ?? '未设置'));
    
    // 检查是否有 POST 数据
    if (empty($_POST) && empty($_FILES)) {
        $error = "未接收到任何 POST 数据。可能原因：表单字段过多超过了 max_input_vars 限制，或表单未正确提交。";
        error_log("警告：POST 请求但没有数据");
    }
    
    try {
        // 读取现有数据（保留所有现有数据）
        $data = [];
        if (file_exists($jsonFile)) {
            $content = file_get_contents($jsonFile);
            $data = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $data = [];
            }
        }
        
        // 初始化不存在的字段，但保留现有数据
        if (!isset($data['companyOverview'])) {
            $data['companyOverview'] = [];
        }
        if (!isset($data['organizationStructure'])) {
            $data['organizationStructure'] = ['ceo' => [], 'pa' => [], 'cLevel' => []];
        }
        if (!isset($data['internalOrganization'])) {
            $data['internalOrganization'] = ['departments' => []];
        }
        if (!isset($data['timeline'])) {
            $data['timeline'] = [];
        }
        if (!isset($data['corporateCore'])) {
            $data['corporateCore'] = ['mission' => '', 'vision' => '', 'culture' => [], 'values' => []];
        }
        if (!isset($data['cultureExplanation'])) {
            $data['cultureExplanation'] = [];
        }
        if (!isset($data['valuesExplanation'])) {
            $data['valuesExplanation'] = [];
        }
        if (!isset($data['strategicObjectives'])) {
            $data['strategicObjectives'] = [];
        }
        
        // 更新公司概述（只更新表单提交的字段）
        if (isset($_POST['companyName'])) {
            $data['companyOverview']['companyName'] = $_POST['companyName'];
            $data['companyOverview']['planTitle'] = $_POST['planTitle'] ?? ($data['companyOverview']['planTitle'] ?? '');
            $data['companyOverview']['strategyStartYear'] = intval($_POST['strategyStartYear'] ?? ($data['companyOverview']['strategyStartYear'] ?? date('Y')));
            $data['companyOverview']['strategyEndYear'] = intval($_POST['strategyEndYear'] ?? ($data['companyOverview']['strategyEndYear'] ?? date('Y') + 5));
            $data['companyOverview']['ultimateGoal'] = $_POST['ultimateGoal'] ?? ($data['companyOverview']['ultimateGoal'] ?? '');
        }
        
        // 更新组织架构 - CEO
        if (isset($_POST['ceo_name'])) {
            $data['organizationStructure']['ceo']['name'] = $_POST['ceo_name'];
            $data['organizationStructure']['ceo']['title'] = $_POST['ceo_title'] ?? ($data['organizationStructure']['ceo']['title'] ?? 'CEO');
        }
        
        // 更新组织架构 - PA
        if (isset($_POST['pa_name'])) {
            $data['organizationStructure']['pa']['name'] = $_POST['pa_name'];
            $data['organizationStructure']['pa']['title'] = $_POST['pa_title'] ?? ($data['organizationStructure']['pa']['title'] ?? 'PA');
        }
        
        // 更新C-Level高管（如果提交了数据，则更新）
        if (isset($_POST['clevel']) && is_array($_POST['clevel'])) {
            $newCLevel = [];
            foreach ($_POST['clevel'] as $index => $clevel) {
                if (!empty($clevel['name']) || !empty($clevel['title'])) {
                    // 保留现有的subordinates和其他字段数据（如果存在）
                    $existingCLevel = $data['organizationStructure']['cLevel'][$index] ?? null;
                    $subordinates = $existingCLevel['subordinates'] ?? [];
                    
                    $newCLevel[] = [
                        'name' => $clevel['name'] ?? '',
                        'title' => $clevel['title'] ?? '',
                        'reportsTo' => $clevel['reportsTo'] ?? ($existingCLevel['reportsTo'] ?? 'CEO'),
                        'fullTitle' => $clevel['fullTitle'] ?? ($existingCLevel['fullTitle'] ?? ''),
                        'subordinates' => $subordinates  // 保留现有subordinates数据
                    ];
                }
            }
            $data['organizationStructure']['cLevel'] = $newCLevel;
        }
        
        // 更新内部组织架构 - 部门
        if (isset($_POST['departments']) && is_array($_POST['departments'])) {
            $data['internalOrganization']['departments'] = [];
            foreach ($_POST['departments'] as $deptIndex => $dept) {
                if (!empty($dept['name'])) {
                    $department = [
                        'name' => $dept['name'],
                        'positions' => []
                    ];
                    
                    // 添加职位
                    if (isset($dept['positions']) && is_array($dept['positions'])) {
                        foreach ($dept['positions'] as $pos) {
                            if (!empty($pos['title'])) {
                                $department['positions'][] = [
                                    'title' => $pos['title'],
                                    'name' => $pos['name'] ?? ''
                                ];
                            }
                        }
                    }
                    
                    $data['internalOrganization']['departments'][] = $department;
                }
            }
        }
        
        // 更新时间线
        if (isset($_POST['timeline']) && is_array($_POST['timeline'])) {
            $data['timeline'] = [];
            foreach ($_POST['timeline'] as $timeline) {
                if (!empty($timeline['year']) || !empty($timeline['goal'])) {
                    $data['timeline'][] = [
                        'year' => intval($timeline['year'] ?? 0),
                        'goal' => $timeline['goal'] ?? ''
                    ];
                }
            }
            // 按年份排序
            usort($data['timeline'], function($a, $b) {
                return $a['year'] <=> $b['year'];
            });
        }
        
        // 更新企业核心
        if (isset($_POST['mission'])) {
            $data['corporateCore']['mission'] = $_POST['mission'] ?? '';
            $data['corporateCore']['vision'] = $_POST['vision'] ?? '';
            
            // 更新文化列表
            if (isset($_POST['culture']) && is_array($_POST['culture'])) {
                $data['corporateCore']['culture'] = array_filter($_POST['culture'], function($item) {
                    return !empty(trim($item));
                });
                $data['corporateCore']['culture'] = array_values($data['corporateCore']['culture']);
            }
            
            // 更新价值观列表
            if (isset($_POST['values']) && is_array($_POST['values'])) {
                $data['corporateCore']['values'] = array_filter($_POST['values'], function($item) {
                    return !empty(trim($item));
                });
                $data['corporateCore']['values'] = array_values($data['corporateCore']['values']);
            }
        }
        
        // 更新文化解释
        if (isset($_POST['cultureExplanation']) && is_array($_POST['cultureExplanation'])) {
            $data['cultureExplanation'] = [];
            foreach ($_POST['cultureExplanation'] as $explanation) {
                if (!empty($explanation['key']) || !empty($explanation['description'])) {
                    $scoring = [];
                    if (isset($explanation['scoring']) && is_array($explanation['scoring'])) {
                        foreach ($explanation['scoring'] as $score) {
                            if (!empty($score['description'])) {
                                $scoring[] = [
                                    'point' => intval($score['point'] ?? 0),
                                    'description' => $score['description'] ?? ''
                                ];
                            }
                        }
                    }
                    $data['cultureExplanation'][] = [
                        'key' => $explanation['key'] ?? '',
                        'description' => $explanation['description'] ?? '',
                        'scoring' => $scoring
                    ];
                }
            }
        }
        
        // 更新价值观解释
        if (isset($_POST['valuesExplanation']) && is_array($_POST['valuesExplanation'])) {
            $data['valuesExplanation'] = [];
            foreach ($_POST['valuesExplanation'] as $explanation) {
                if (!empty($explanation['key']) || !empty($explanation['description'])) {
                    $scoring = [];
                    if (isset($explanation['scoring']) && is_array($explanation['scoring'])) {
                        foreach ($explanation['scoring'] as $score) {
                            if (!empty($score['description'])) {
                                $scoring[] = [
                                    'point' => intval($score['point'] ?? 0),
                                    'description' => $score['description'] ?? ''
                                ];
                            }
                        }
                    }
                    $data['valuesExplanation'][] = [
                        'key' => $explanation['key'] ?? '',
                        'description' => $explanation['description'] ?? '',
                        'scoring' => $scoring
                    ];
                }
            }
        }
        
        // 更新战略目标
        if (isset($_POST['strategicObjectives']) && is_array($_POST['strategicObjectives'])) {
            $data['strategicObjectives'] = [];
            foreach ($_POST['strategicObjectives'] as $year => $objectives) {
                if (is_array($objectives)) {
                    $yearObjectives = [];
                    foreach ($objectives as $obj) {
                        if (!empty($obj['department']) || !empty($obj['strategy'])) {
                            // 处理仪表板指标（从textarea转换为数组，每行一个指标）
                            $dashboardMetrics = [];
                            if (isset($obj['dashboardMetrics'])) {
                                if (is_string($obj['dashboardMetrics'])) {
                                    // 从textarea的换行符分割
                                    $lines = explode("\n", $obj['dashboardMetrics']);
                                    $dashboardMetrics = array_filter(array_map('trim', $lines), function($item) {
                                        return !empty($item);
                                    });
                                } elseif (is_array($obj['dashboardMetrics'])) {
                                    $dashboardMetrics = array_filter($obj['dashboardMetrics'], function($item) { 
                                        return !empty(trim(is_string($item) ? $item : '')); 
                                    });
                                }
                            }
                            
                            $yearObjectives[] = [
                                'department' => $obj['department'] ?? '',
                                'strategy' => $obj['strategy'] ?? '',
                                'dashboardMetrics' => array_values($dashboardMetrics),
                                'pic' => $obj['pic'] ?? '',
                                'startDate' => $obj['startDate'] ?? '',
                                'endDate' => $obj['endDate'] ?? ''
                            ];
                        }
                    }
                    if (!empty($yearObjectives)) {
                        $data['strategicObjectives'][$year] = $yearObjectives;
                    }
                }
            }
        }
        
        // 保存到JSON文件（保留所有现有数据，只更新修改的字段）
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        // 检查JSON编码是否有错误
        if ($jsonContent === false) {
            $error = "JSON编码失败：" . json_last_error_msg();
        } else {
            // 确保目录存在
            $jsonDir = dirname($jsonFile);
            if (!is_dir($jsonDir)) {
                if (!mkdir($jsonDir, 0755, true)) {
                    $error = "无法创建目录：" . $jsonDir;
                }
            }
            
            // 尝试保存文件（仅当尚无错误时）
            if (empty($error)) {
                $bytesWritten = file_put_contents($jsonFile, $jsonContent, LOCK_EX);
                if ($bytesWritten === false) {
                    $error = "数据保存失败！文件路径：" . $jsonFile . " 请检查文件权限。";
                } else {
                    $success = "数据保存成功！已写入 " . $bytesWritten . " 字节。";
                }
            }
        }
    } catch (Exception $e) {
        $error = "保存时发生错误：" . $e->getMessage();
        error_log("保存错误: " . $e->getMessage());
        error_log("错误堆栈: " . $e->getTraceAsString());
    } catch (Error $e) {
        $error = "保存时发生致命错误：" . $e->getMessage();
        error_log("致命错误: " . $e->getMessage());
    }

    if (empty($error) && !empty($success) && corporate_blueprint_edit_getReturnTo() === 'v2') {
        corporate_blueprint_edit_redirectAfterSave($success);
    }
}

function corporate_blueprint_edit_loadCurrentData() {
    $jsonFile = corporate_blueprint_edit_getJsonFile();
$currentData = [];
if (file_exists($jsonFile)) {
    $content = file_get_contents($jsonFile);
    $currentData = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $currentData = [];
    }
}

// 初始化默认值
$companyOverview = $currentData['companyOverview'] ?? [
    'companyName' => 'KUNZZ HOLDINGS SDN BHD',
    'planTitle' => 'Corporate Strategic Plan',
    'strategyStartYear' => date('Y'),
    'strategyEndYear' => date('Y') + 5,
    'ultimateGoal' => ''
];

$orgStructure = $currentData['organizationStructure'] ?? [
    'ceo' => ['name' => '', 'title' => 'CEO'],
    'pa' => ['name' => '', 'title' => 'PA'],
    'cLevel' => []
];

$internalOrg = $currentData['internalOrganization'] ?? [
    'departments' => []
];

$timeline = $currentData['timeline'] ?? [];
$corporateCore = $currentData['corporateCore'] ?? [
    'mission' => '',
    'vision' => '',
    'culture' => [],
    'values' => []
];
$cultureExplanation = $currentData['cultureExplanation'] ?? [];
$valuesExplanation = $currentData['valuesExplanation'] ?? [];
$strategicObjectives = $currentData['strategicObjectives'] ?? [];

    return [
        'companyOverview' => $companyOverview,
        'orgStructure' => $orgStructure,
        'internalOrg' => $internalOrg,
        'timeline' => $timeline,
        'corporateCore' => $corporateCore,
        'cultureExplanation' => $cultureExplanation,
        'valuesExplanation' => $valuesExplanation,
        'strategicObjectives' => $strategicObjectives,
    ];
}

function corporate_blueprint_edit_prepareViewData($returnTo = null) {
    $returnTo = $returnTo ?? corporate_blueprint_edit_getReturnTo();
    $data = corporate_blueprint_edit_loadCurrentData();

    return array_merge($data, [
        'returnTo' => $returnTo,
        'pageUrl' => corporate_blueprint_edit_getPageUrl($returnTo),
        'formActionUrl' => corporate_blueprint_edit_getFormActionUrl(),
        'viewUrl' => corporate_blueprint_edit_getViewUrl($returnTo),
        'requestMethod' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
    ]);
}
