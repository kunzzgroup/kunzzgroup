<?php
if (!headers_sent()) {
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
}
?>
<?php
session_start();

// 检查是否已登录
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// 增加 max_input_vars 限制（如果表单字段太多）
ini_set('max_input_vars', 5000);

$jsonFile = __DIR__ . '/corporate_strategy.json';
$success = '';
$error = '';

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
}

// 读取当前数据
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
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>企业蓝图管理 - KUNZZ HOLDINGS</title>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <link rel="stylesheet" href="css/corporate_blueprint_edit.css?v=2026">
</head>
<body class="has-sidebar">
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <h1>企业蓝图管理</h1>
            <p>编辑企业蓝图数据和咨询信息</p>
        </div>
        
        <div class="content">
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($success) && empty($error)): ?>
                <div class="alert alert-error">表单已提交，但未检测到保存操作。请检查表单数据是否正确提交。POST 数据数量: <?php echo count($_POST); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" id="corporate-form" onsubmit="return handleFormSubmit(event)">
                <!-- 标签导航栏 -->
                <div class="tab-navigation">
                    <button type="button" class="tab-btn active" onclick="switchTab('overview', this)">公司概述</button>
                    <button type="button" class="tab-btn" onclick="switchTab('timeline', this)">时间线</button>
                    <button type="button" class="tab-btn" onclick="switchTab('corporate-core', this)">企业核心</button>
                    <button type="button" class="tab-btn" onclick="switchTab('culture-explanation', this)">文化解说</button>
                    <button type="button" class="tab-btn" onclick="switchTab('values-explanation', this)">价值观解说</button>
                    <button type="button" class="tab-btn" onclick="switchTab('org-structure', this)">高层组织架构</button>
                    <button type="button" class="tab-btn" onclick="switchTab('internal-org', this)">内部组织架构</button>
                    <button type="button" class="tab-btn" onclick="switchTab('strategic-objectives', this)">战略目标</button>
                </div>
                
                <!-- 公司概述 -->
                <div class="section tab-section active" data-tab="overview">
                    <h2>公司概述</h2>
                    <div class="form-group">
                        <label>公司名称</label>
                        <input type="text" name="companyName" value="<?php echo htmlspecialchars($companyOverview['companyName'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>计划标题</label>
                        <input type="text" name="planTitle" value="<?php echo htmlspecialchars($companyOverview['planTitle'] ?? ''); ?>">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>战略开始年份</label>
                            <input type="number" name="strategyStartYear" value="<?php echo htmlspecialchars($companyOverview['strategyStartYear'] ?? date('Y')); ?>">
                        </div>
                        <div class="form-group">
                            <label>战略结束年份</label>
                            <input type="number" name="strategyEndYear" value="<?php echo htmlspecialchars($companyOverview['strategyEndYear'] ?? date('Y') + 5); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>终极目标</label>
                        <textarea name="ultimateGoal"><?php echo htmlspecialchars($companyOverview['ultimateGoal'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <!-- 高层组织架构 -->
                <div class="section tab-section" data-tab="org-structure">
                    <h2>高层组织架构</h2>
                    
                    <div class="sub-section">
                        <h3>CEO</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>姓名</label>
                                <input type="text" name="ceo_name" value="<?php echo htmlspecialchars($orgStructure['ceo']['name'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>职位</label>
                                <input type="text" name="ceo_title" value="<?php echo htmlspecialchars($orgStructure['ceo']['title'] ?? 'CEO'); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="sub-section">
                        <h3>PA (个人助理)</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>姓名</label>
                                <input type="text" name="pa_name" value="<?php echo htmlspecialchars($orgStructure['pa']['name'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>职位</label>
                                <input type="text" name="pa_title" value="<?php echo htmlspecialchars($orgStructure['pa']['title'] ?? 'PA'); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="sub-section">
                        <h3>C-Level 高管</h3>
                        <div id="clevel-container">
                            <?php 
                            $clevelList = $orgStructure['cLevel'] ?? [];
                            if (empty($clevelList)) {
                                $clevelList = [['name' => '', 'title' => '', 'reportsTo' => 'CEO', 'fullTitle' => '']];
                            }
                            foreach ($clevelList as $index => $clevel): ?>
                                <div class="clevel-item">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>姓名</label>
                                            <input type="text" name="clevel[<?php echo $index; ?>][name]" value="<?php echo htmlspecialchars($clevel['name'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>职位</label>
                                            <input type="text" name="clevel[<?php echo $index; ?>][title]" value="<?php echo htmlspecialchars($clevel['title'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>完整职位名称</label>
                                        <input type="text" name="clevel[<?php echo $index; ?>][fullTitle]" value="<?php echo htmlspecialchars($clevel['fullTitle'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>汇报对象</label>
                                        <input type="text" name="clevel[<?php echo $index; ?>][reportsTo]" value="<?php echo htmlspecialchars($clevel['reportsTo'] ?? 'CEO'); ?>">
                                    </div>
                                    <button type="button" class="remove-btn" onclick="removeCLevel(this)">删除</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="add-btn" onclick="addCLevel()">添加 C-Level 高管</button>
                    </div>
                </div>
                
                <!-- 内部组织架构 -->
                <div class="section tab-section" data-tab="internal-org">
                    <h2>内部组织架构</h2>
                    <div id="departments-container">
                        <?php 
                        $departments = $internalOrg['departments'] ?? [];
                        if (empty($departments)) {
                            $departments = [['name' => '', 'positions' => [['title' => '', 'name' => '']]]];
                        }
                        foreach ($departments as $deptIndex => $dept): ?>
                            <div class="department-item">
                                <div class="form-group">
                                    <label>部门名称</label>
                                    <input type="text" name="departments[<?php echo $deptIndex; ?>][name]" value="<?php echo htmlspecialchars($dept['name'] ?? ''); ?>">
                                </div>
                                
                                <div class="positions-container">
                                    <h3>职位列表</h3>
                                    <?php 
                                    $positions = $dept['positions'] ?? [];
                                    if (empty($positions)) {
                                        $positions = [['title' => '', 'name' => '']];
                                    }
                                    foreach ($positions as $posIndex => $pos): ?>
                                        <div class="position-item">
                                            <div class="form-group" style="margin-bottom: 0;">
                                                <label>职位</label>
                                                <input type="text" name="departments[<?php echo $deptIndex; ?>][positions][<?php echo $posIndex; ?>][title]" value="<?php echo htmlspecialchars($pos['title'] ?? ''); ?>" placeholder="职位名称">
                                            </div>
                                            <div class="form-group" style="margin-bottom: 0;">
                                                <label>姓名</label>
                                                <input type="text" name="departments[<?php echo $deptIndex; ?>][positions][<?php echo $posIndex; ?>][name]" value="<?php echo htmlspecialchars($pos['name'] ?? ''); ?>" placeholder="人员姓名">
                                            </div>
                                            <button type="button" class="remove-btn" onclick="removePosition(this)">删除</button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" class="add-btn" onclick="addPosition(this)">添加职位</button>
                                <button type="button" class="remove-btn" onclick="removeDepartment(this)" style="margin-left: 10px;">删除部门</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="add-btn" onclick="addDepartment()">添加部门</button>
                </div>
                
                <!-- 时间线 -->
                <div class="section tab-section" data-tab="timeline">
                    <h2>时间线</h2>
                    <div id="timeline-container">
                        <?php 
                        if (empty($timeline)) {
                            $timeline = [['year' => '', 'goal' => '']];
                        }
                        foreach ($timeline as $index => $item): ?>
                            <div class="timeline-item">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label>年份</label>
                                    <input type="number" name="timeline[<?php echo $index; ?>][year]" value="<?php echo htmlspecialchars($item['year'] ?? ''); ?>" placeholder="2024">
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label>目标</label>
                                    <input type="text" name="timeline[<?php echo $index; ?>][goal]" value="<?php echo htmlspecialchars($item['goal'] ?? ''); ?>" placeholder="创建X间子公司">
                                </div>
                                <button type="button" class="remove-btn" onclick="removeTimeline(this)">删除</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="add-btn" onclick="addTimeline()">添加时间线项目</button>
                </div>
                
                <!-- 企业核心 -->
                <div class="section tab-section" data-tab="corporate-core">
                    <h2>企业核心</h2>
                    <div class="form-group">
                        <label>使命 (Mission)</label>
                        <textarea name="mission" rows="3"><?php echo htmlspecialchars($corporateCore['mission'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>愿景 (Vision)</label>
                        <textarea name="vision" rows="3"><?php echo htmlspecialchars($corporateCore['vision'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="sub-section">
                        <h3>文化 (Culture)</h3>
                        <div id="culture-container">
                            <?php 
                            $cultureList = $corporateCore['culture'] ?? [];
                            if (empty($cultureList)) {
                                $cultureList = [''];
                            }
                            foreach ($cultureList as $index => $culture): ?>
                                <div class="culture-item">
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label>文化项</label>
                                        <input type="text" name="culture[<?php echo $index; ?>]" value="<?php echo htmlspecialchars($culture); ?>" placeholder="例如：Innovation">
                                    </div>
                                    <button type="button" class="remove-btn" onclick="removeCulture(this)">删除</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="add-btn" onclick="addCulture()">添加文化项</button>
                    </div>
                    
                    <div class="sub-section">
                        <h3>价值观 (Values)</h3>
                        <div id="values-container">
                            <?php 
                            $valuesList = $corporateCore['values'] ?? [];
                            if (empty($valuesList)) {
                                $valuesList = [''];
                            }
                            foreach ($valuesList as $index => $value): ?>
                                <div class="values-item">
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label>价值观</label>
                                        <input type="text" name="values[<?php echo $index; ?>]" value="<?php echo htmlspecialchars($value); ?>" placeholder="例如：Customer First">
                                    </div>
                                    <button type="button" class="remove-btn" onclick="removeValue(this)">删除</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="add-btn" onclick="addValue()">添加价值观</button>
                    </div>
                </div>
                
                <!-- 文化解说 -->
                <div class="section tab-section" data-tab="culture-explanation">
                    <h2>文化解说 & 考核</h2>
                    <div id="culture-explanation-container">
                        <?php 
                        if (empty($cultureExplanation)) {
                            $cultureExplanation = [['key' => '', 'description' => '', 'scoring' => []]];
                        }
                        foreach ($cultureExplanation as $index => $explanation): 
                            $scoring = $explanation['scoring'] ?? [];
                            if (empty($scoring)) {
                                $scoring = [
                                    ['point' => 1, 'description' => ''],
                                    ['point' => 2, 'description' => ''],
                                    ['point' => 3, 'description' => ''],
                                    ['point' => 4, 'description' => ''],
                                    ['point' => 5, 'description' => '']
                                ];
                            }
                        ?>
                            <div class="culture-explanation-item">
                                <div class="form-group">
                                    <label>关键词 (Key)</label>
                                    <input type="text" name="cultureExplanation[<?php echo $index; ?>][key]" value="<?php echo htmlspecialchars($explanation['key'] ?? ''); ?>" placeholder="例如：积极向上">
                                </div>
                                <div class="form-group">
                                    <label>描述 (Description)</label>
                                    <textarea name="cultureExplanation[<?php echo $index; ?>][description]" rows="4"><?php echo htmlspecialchars($explanation['description'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>评分标准</label>
                                    <?php foreach ($scoring as $scoreIndex => $score): ?>
                                        <div style="display: grid; grid-template-columns: 80px 1fr; gap: 10px; margin-bottom: 10px; align-items: center;">
                                            <label style="margin: 0; font-weight: 600;"><?php echo ($score['point'] ?? ($scoreIndex + 1)); ?>分:</label>
                                            <input type="text" name="cultureExplanation[<?php echo $index; ?>][scoring][<?php echo $scoreIndex; ?>][description]" value="<?php echo htmlspecialchars($score['description'] ?? ''); ?>" placeholder="评分描述">
                                            <input type="hidden" name="cultureExplanation[<?php echo $index; ?>][scoring][<?php echo $scoreIndex; ?>][point]" value="<?php echo ($score['point'] ?? ($scoreIndex + 1)); ?>">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" class="remove-btn" onclick="removeCultureExplanation(this)">删除</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="add-btn" onclick="addCultureExplanation()">添加文化解说</button>
                </div>
                
                <!-- 价值观解说 -->
                <div class="section tab-section" data-tab="values-explanation">
                    <h2>价值观解说 & 考核</h2>
                    <div id="values-explanation-container">
                        <?php 
                        if (empty($valuesExplanation)) {
                            $valuesExplanation = [['key' => '', 'description' => '', 'scoring' => []]];
                        }
                        foreach ($valuesExplanation as $index => $explanation): 
                            $scoring = $explanation['scoring'] ?? [];
                            if (empty($scoring)) {
                                $scoring = [
                                    ['point' => 1, 'description' => ''],
                                    ['point' => 2, 'description' => ''],
                                    ['point' => 3, 'description' => ''],
                                    ['point' => 4, 'description' => ''],
                                    ['point' => 5, 'description' => '']
                                ];
                            }
                        ?>
                            <div class="values-explanation-item">
                                <div class="form-group">
                                    <label>关键词 (Key)</label>
                                    <input type="text" name="valuesExplanation[<?php echo $index; ?>][key]" value="<?php echo htmlspecialchars($explanation['key'] ?? ''); ?>" placeholder="例如：目标导向">
                                </div>
                                <div class="form-group">
                                    <label>描述 (Description)</label>
                                    <textarea name="valuesExplanation[<?php echo $index; ?>][description]" rows="4"><?php echo htmlspecialchars($explanation['description'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>评分标准</label>
                                    <?php foreach ($scoring as $scoreIndex => $score): ?>
                                        <div style="display: grid; grid-template-columns: 80px 1fr; gap: 10px; margin-bottom: 10px; align-items: center;">
                                            <label style="margin: 0; font-weight: 600;"><?php echo ($score['point'] ?? ($scoreIndex + 1)); ?>分:</label>
                                            <input type="text" name="valuesExplanation[<?php echo $index; ?>][scoring][<?php echo $scoreIndex; ?>][description]" value="<?php echo htmlspecialchars($score['description'] ?? ''); ?>" placeholder="评分描述">
                                            <input type="hidden" name="valuesExplanation[<?php echo $index; ?>][scoring][<?php echo $scoreIndex; ?>][point]" value="<?php echo ($score['point'] ?? ($scoreIndex + 1)); ?>">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" class="remove-btn" onclick="removeValuesExplanation(this)">删除</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="add-btn" onclick="addValuesExplanation()">添加价值观解说</button>
                </div>
                
                <!-- 战略目标 -->
                <div class="section tab-section" data-tab="strategic-objectives">
                    <h2>战略目标</h2>
                    <div id="strategic-objectives-container">
                        <?php 
                        if (!empty($strategicObjectives)):
                            foreach ($strategicObjectives as $year => $objectives): ?>
                                <div class="year-objectives">
                                    <h3><?php echo htmlspecialchars($year); ?>年</h3>
                                    <div class="objectives-list" data-year="<?php echo htmlspecialchars($year); ?>">
                                        <?php foreach ($objectives as $objIndex => $obj): ?>
                                            <div class="objective-item">
                                                <div class="form-row">
                                                    <div class="form-group">
                                                        <label>部门</label>
                                                        <input type="text" name="strategicObjectives[<?php echo htmlspecialchars($year); ?>][<?php echo $objIndex; ?>][department]" value="<?php echo htmlspecialchars($obj['department'] ?? ''); ?>" placeholder="例如：Technology">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>负责人 (PIC)</label>
                                                        <input type="text" name="strategicObjectives[<?php echo htmlspecialchars($year); ?>][<?php echo $objIndex; ?>][pic]" value="<?php echo htmlspecialchars($obj['pic'] ?? ''); ?>" placeholder="例如：CTO">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>策略</label>
                                                    <textarea name="strategicObjectives[<?php echo htmlspecialchars($year); ?>][<?php echo $objIndex; ?>][strategy]" rows="2"><?php echo htmlspecialchars($obj['strategy'] ?? ''); ?></textarea>
                                                </div>
                                                <div class="form-row">
                                                    <div class="form-group">
                                                        <label>开始日期</label>
                                                        <input type="date" name="strategicObjectives[<?php echo htmlspecialchars($year); ?>][<?php echo $objIndex; ?>][startDate]" value="<?php echo htmlspecialchars($obj['startDate'] ?? ''); ?>">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>结束日期</label>
                                                        <input type="date" name="strategicObjectives[<?php echo htmlspecialchars($year); ?>][<?php echo $objIndex; ?>][endDate]" value="<?php echo htmlspecialchars($obj['endDate'] ?? ''); ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>仪表板指标 (每行一个)</label>
                                                    <textarea name="strategicObjectives[<?php echo htmlspecialchars($year); ?>][<?php echo $objIndex; ?>][dashboardMetrics]" rows="3" placeholder="System Uptime (%)
Infrastructure Cost Reduction (%)
Implementation Timeline Adherence (%)"><?php echo htmlspecialchars(implode("\n", $obj['dashboardMetrics'] ?? [])); ?></textarea>
                                                    <small style="color: #666;">每行一个指标</small>
                                                </div>
                                                <button type="button" class="remove-btn" onclick="removeObjective(this, '<?php echo htmlspecialchars($year); ?>')">删除</button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <button type="button" class="add-btn" onclick="addObjective('<?php echo htmlspecialchars($year); ?>')">添加<?php echo htmlspecialchars($year); ?>年目标</button>
                                    <button type="button" class="remove-btn" onclick="removeYear('<?php echo htmlspecialchars($year); ?>')" style="margin-left: 10px;">删除<?php echo htmlspecialchars($year); ?>年</button>
                                </div>
                            <?php endforeach;
                        endif; ?>
                    </div>
                    <button type="button" class="add-btn" onclick="addYear()">添加年份</button>
                </div>
                
                <!-- 固定操作按钮 -->
                <div class="fixed-actions">
                    <button type="submit" class="btn">保存更改</button>
                    <a href="corporate_blueprint.php" class="btn btn-secondary">返回查看</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Pass PHP variables to JS
        let clevelIndex = <?php echo count($orgStructure['cLevel'] ?? []); ?>;
        let deptIndex = <?php echo count($internalOrg['departments'] ?? []); ?>;
        let timelineIndex = <?php echo count($timeline); ?>;
        let cultureIndex = <?php echo count($corporateCore['culture'] ?? []); ?>;
        let valuesIndex = <?php echo count($corporateCore['values'] ?? []); ?>;
        let cultureExplanationIndex = <?php echo count($cultureExplanation); ?>;
        let valuesExplanationIndex = <?php echo count($valuesExplanation); ?>;
    </script>
    <script src="js/corporate_blueprint_edit.js?v=2026"></script>
</body>
</html>

