<?php
session_start();

// 检查是否已登录
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$jsonFile = __DIR__ . '/corporate_strategy.json';
$success = '';
$error = '';

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                    $data['cultureExplanation'][] = [
                        'key' => $explanation['key'] ?? '',
                        'description' => $explanation['description'] ?? ''
                    ];
                }
            }
        }
        
        // 更新价值观解释
        if (isset($_POST['valuesExplanation']) && is_array($_POST['valuesExplanation'])) {
            $data['valuesExplanation'] = [];
            foreach ($_POST['valuesExplanation'] as $explanation) {
                if (!empty($explanation['key']) || !empty($explanation['description'])) {
                    $data['valuesExplanation'][] = [
                        'key' => $explanation['key'] ?? '',
                        'description' => $explanation['description'] ?? ''
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
        if (file_put_contents($jsonFile, $jsonContent)) {
            $success = "数据保存成功！";
        } else {
            $error = "数据保存失败！";
        }
    } catch (Exception $e) {
        $error = "保存时发生错误：" . $e->getMessage();
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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Microsoft YaHei', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            min-height: 100vh;
            padding: 30px 20px;
            position: relative;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255, 92, 0, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(0, 0, 0, 0.02) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }
        
        /* 适配 sidebar 的样式 */
        body.has-sidebar {
            margin-left: clamp(140px, 13.02vw, 250px);
            transition: margin-left 0.3s ease;
        }
        
        body.sidebar-collapsed {
            margin-left: clamp(50px, 3.65vw, 70px);
        }
        
        @media (max-width: 768px) {
            body.has-sidebar {
                margin-left: 0;
            }
            body {
                padding: 15px 10px;
            }
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        
        .header {
            background: linear-gradient(135deg, #ff5c00 0%, #ff7a2e 100%);
            color: white;
            padding: clamp(30px, 3vw, 50px);
            border-radius: 20px 20px 0 0;
            box-shadow: 0 10px 40px rgba(255, 92, 0, 0.3);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            filter: blur(60px);
        }
        
        .header h1 {
            font-size: clamp(28px, 3vw, 42px);
            margin-bottom: 10px;
            font-weight: 700;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .header p {
            opacity: 0.95;
            font-size: clamp(14px, 1.5vw, 18px);
            position: relative;
            z-index: 1;
        }
        
        .content {
            padding: 0;
        }
        
        .alert {
            padding: 16px 24px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
        }
        
        .alert-error {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: none;
        }
        
        .section {
            background: white;
            border-radius: 20px;
            padding: clamp(30px, 3vw, 40px);
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(180deg, #ff5c00 0%, #ff7a2e 100%);
        }
        
        .section:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.12);
        }
        
        .section h2 {
            color: #1f2937;
            margin-bottom: 30px;
            font-size: clamp(22px, 2.5vw, 28px);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .section h2::before {
            content: '';
            width: 4px;
            height: 28px;
            background: linear-gradient(180deg, #ff5c00 0%, #ff7a2e 100%);
            border-radius: 2px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 10px;
            font-size: 14px;
            letter-spacing: 0.3px;
        }
        
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="date"],
        .form-group textarea {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: #f9fafb;
            color: #1f2937;
        }
        
        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group input[type="date"]:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ff5c00;
            background: white;
            box-shadow: 0 0 0 4px rgba(255, 92, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
            line-height: 1.6;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        .sub-section {
            background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 20px;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .sub-section:hover {
            border-color: #ff5c00;
            box-shadow: 0 8px 24px rgba(255, 92, 0, 0.1);
        }
        
        .sub-section h3 {
            font-size: 18px;
            color: #1f2937;
            margin-bottom: 20px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sub-section h3::before {
            content: '▸';
            color: #ff5c00;
            font-size: 16px;
        }
        
        .btn {
            background: linear-gradient(135deg, #ff5c00 0%, #ff7a2e 100%);
            color: white;
            border: none;
            padding: 14px 32px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 92, 0, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 92, 0, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3);
        }
        
        .btn-secondary:hover {
            box-shadow: 0 6px 20px rgba(107, 114, 128, 0.4);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }
        
        .btn-danger:hover {
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }
        
        .btn-small {
            padding: 8px 16px;
            font-size: 13px;
        }
        
        .btn-group {
            display: flex;
            gap: 12px;
            margin-top: 15px;
        }
        
        .actions {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #e5e7eb;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .remove-btn {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        }
        
        .remove-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }
        
        .add-btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .add-btn::before {
            content: '+';
            font-size: 20px;
            font-weight: 300;
        }
        
        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }
        
        /* 卡片式项目样式 */
        .clevel-item,
        .department-item {
            background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 20px;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .clevel-item:hover,
        .department-item:hover {
            border-color: #ff5c00;
            box-shadow: 0 8px 24px rgba(255, 92, 0, 0.15);
            transform: translateY(-3px);
        }
        
        .position-item {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 15px;
            margin-bottom: 15px;
            align-items: end;
            padding: 16px;
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        
        .position-item:hover {
            border-color: #ff5c00;
            box-shadow: 0 4px 12px rgba(255, 92, 0, 0.1);
        }
        
        .positions-container h3 {
            font-size: 16px;
            margin-bottom: 15px;
            color: #374151;
            font-weight: 600;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        /* 时间线、文化、价值观等项目的样式 */
        .timeline-item,
        .culture-item,
        .values-item {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 15px;
            margin-bottom: 15px;
            align-items: end;
            padding: 16px;
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        
        .timeline-item {
            grid-template-columns: 150px 1fr auto;
        }
        
        .timeline-item:hover,
        .culture-item:hover,
        .values-item:hover {
            border-color: #ff5c00;
            box-shadow: 0 4px 12px rgba(255, 92, 0, 0.1);
        }
        
        .culture-explanation-item,
        .values-explanation-item {
            margin-bottom: 20px;
            padding: 24px;
            background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
            border-radius: 16px;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        
        .culture-explanation-item:hover,
        .values-explanation-item:hover {
            border-color: #ff5c00;
            box-shadow: 0 8px 24px rgba(255, 92, 0, 0.15);
        }
        
        .year-objectives {
            margin-bottom: 30px;
            padding: 30px;
            background: linear-gradient(135deg, #ffffff 0%, #fff5f0 100%);
            border-radius: 20px;
            border: 2px solid #ff5c00;
            box-shadow: 0 8px 24px rgba(255, 92, 0, 0.2);
        }
        
        .year-objectives h3 {
            margin-bottom: 20px;
            color: #ff5c00;
            font-size: 22px;
            font-weight: 700;
        }
        
        .objective-item {
            margin-bottom: 20px;
            padding: 24px;
            background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
            border-radius: 16px;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        
        .objective-item:hover {
            border-color: #ff5c00;
            box-shadow: 0 8px 24px rgba(255, 92, 0, 0.1);
        }
    </style>
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
            
            <form method="POST" action="">
                <!-- 公司概述 -->
                <div class="section">
                    <h2>公司概述</h2>
                    <div class="form-group">
                        <label>公司名称 *</label>
                        <input type="text" name="companyName" value="<?php echo htmlspecialchars($companyOverview['companyName'] ?? ''); ?>" required>
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
                <div class="section">
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
                <div class="section">
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
                                    <label>部门名称 *</label>
                                    <input type="text" name="departments[<?php echo $deptIndex; ?>][name]" value="<?php echo htmlspecialchars($dept['name'] ?? ''); ?>" required>
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
                
                <div class="actions">
                    <button type="submit" class="btn">保存更改</button>
                    <a href="corporate_blueprint.php" class="btn btn-secondary">返回查看</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        let clevelIndex = <?php echo count($orgStructure['cLevel'] ?? []); ?>;
        let deptIndex = <?php echo count($internalOrg['departments'] ?? []); ?>;
        
        function addCLevel() {
            const container = document.getElementById('clevel-container');
            const html = `
                <div class="clevel-item">
                    <div class="form-row">
                        <div class="form-group">
                            <label>姓名</label>
                            <input type="text" name="clevel[${clevelIndex}][name]" value="">
                        </div>
                        <div class="form-group">
                            <label>职位</label>
                            <input type="text" name="clevel[${clevelIndex}][title]" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>完整职位名称</label>
                        <input type="text" name="clevel[${clevelIndex}][fullTitle]" value="">
                    </div>
                    <div class="form-group">
                        <label>汇报对象</label>
                        <input type="text" name="clevel[${clevelIndex}][reportsTo]" value="CEO">
                    </div>
                    <button type="button" class="remove-btn" onclick="removeCLevel(this)">删除</button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            clevelIndex++;
        }
        
        function removeCLevel(btn) {
            if (confirm('确定要删除这个C-Level高管吗？')) {
                btn.closest('.clevel-item').remove();
            }
        }
        
        function addDepartment() {
            const container = document.getElementById('departments-container');
            const html = `
                <div class="department-item">
                    <div class="form-group">
                        <label>部门名称 *</label>
                        <input type="text" name="departments[${deptIndex}][name]" value="" required>
                    </div>
                    <div class="positions-container">
                        <h3>职位列表</h3>
                        <div class="position-item">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>职位</label>
                                <input type="text" name="departments[${deptIndex}][positions][0][title]" value="" placeholder="职位名称">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>姓名</label>
                                <input type="text" name="departments[${deptIndex}][positions][0][name]" value="" placeholder="人员姓名">
                            </div>
                            <button type="button" class="remove-btn" onclick="removePosition(this)">删除</button>
                        </div>
                    </div>
                    <button type="button" class="add-btn" onclick="addPosition(this)">添加职位</button>
                    <button type="button" class="remove-btn" onclick="removeDepartment(this)" style="margin-left: 10px;">删除部门</button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            deptIndex++;
        }
        
        function removeDepartment(btn) {
            if (confirm('确定要删除这个部门吗？所有职位也将被删除。')) {
                btn.closest('.department-item').remove();
            }
        }
        
        function addPosition(btn) {
            const departmentItem = btn.closest('.department-item');
            const deptIndexAttr = departmentItem.querySelector('input[name*="[name]"]').name.match(/departments\[(\d+)\]/)[1];
            const positionsContainer = departmentItem.querySelector('.positions-container');
            const existingPositions = positionsContainer.querySelectorAll('.position-item');
            const posIndex = existingPositions.length;
            
            const html = `
                <div class="position-item">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>职位</label>
                        <input type="text" name="departments[${deptIndexAttr}][positions][${posIndex}][title]" value="" placeholder="职位名称">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>姓名</label>
                        <input type="text" name="departments[${deptIndexAttr}][positions][${posIndex}][name]" value="" placeholder="人员姓名">
                    </div>
                    <button type="button" class="remove-btn" onclick="removePosition(this)">删除</button>
                </div>
            `;
            positionsContainer.querySelector('h3').insertAdjacentHTML('afterend', html);
        }
        
        function removePosition(btn) {
            if (confirm('确定要删除这个职位吗？')) {
                btn.closest('.position-item').remove();
            }
        }
        
        // Timeline functions
        let timelineIndex = <?php echo count($timeline); ?>;
        function addTimeline() {
            const container = document.getElementById('timeline-container');
            const html = `
                <div class="timeline-item">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>年份</label>
                        <input type="number" name="timeline[${timelineIndex}][year]" value="" placeholder="2024">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>目标</label>
                        <input type="text" name="timeline[${timelineIndex}][goal]" value="" placeholder="创建X间子公司">
                    </div>
                    <button type="button" class="remove-btn" onclick="removeTimeline(this)">删除</button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            timelineIndex++;
        }
        
        function removeTimeline(btn) {
            if (confirm('确定要删除这个时间线项目吗？')) {
                btn.closest('.timeline-item').remove();
            }
        }
        
        // Culture functions
        let cultureIndex = <?php echo count($corporateCore['culture'] ?? []); ?>;
        function addCulture() {
            const container = document.getElementById('culture-container');
            const html = `
                <div class="culture-item">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>文化项</label>
                        <input type="text" name="culture[${cultureIndex}]" value="" placeholder="例如：Innovation">
                    </div>
                    <button type="button" class="remove-btn" onclick="removeCulture(this)">删除</button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            cultureIndex++;
        }
        
        function removeCulture(btn) {
            if (confirm('确定要删除这个文化项吗？')) {
                btn.closest('.culture-item').remove();
            }
        }
        
        // Values functions
        let valuesIndex = <?php echo count($corporateCore['values'] ?? []); ?>;
        function addValue() {
            const container = document.getElementById('values-container');
            const html = `
                <div class="values-item">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>价值观</label>
                        <input type="text" name="values[${valuesIndex}]" value="" placeholder="例如：Customer First">
                    </div>
                    <button type="button" class="remove-btn" onclick="removeValue(this)">删除</button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            valuesIndex++;
        }
        
        function removeValue(btn) {
            if (confirm('确定要删除这个价值观吗？')) {
                btn.closest('.values-item').remove();
            }
        }
        
        // Culture Explanation functions
        let cultureExplanationIndex = <?php echo count($cultureExplanation); ?>;
        function addCultureExplanation() {
            const container = document.getElementById('culture-explanation-container');
            const html = `
                <div class="culture-explanation-item">
                    <div class="form-group">
                        <label>关键词 (Key)</label>
                        <input type="text" name="cultureExplanation[${cultureExplanationIndex}][key]" value="" placeholder="例如：Innovation">
                    </div>
                    <div class="form-group">
                        <label>描述 (Description)</label>
                        <textarea name="cultureExplanation[${cultureExplanationIndex}][description]" rows="3"></textarea>
                    </div>
                    <button type="button" class="remove-btn" onclick="removeCultureExplanation(this)">删除</button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            cultureExplanationIndex++;
        }
        
        function removeCultureExplanation(btn) {
            if (confirm('确定要删除这个文化解释吗？')) {
                btn.closest('.culture-explanation-item').remove();
            }
        }
        
        // Values Explanation functions
        let valuesExplanationIndex = <?php echo count($valuesExplanation); ?>;
        function addValuesExplanation() {
            const container = document.getElementById('values-explanation-container');
            const html = `
                <div class="values-explanation-item">
                    <div class="form-group">
                        <label>关键词 (Key)</label>
                        <input type="text" name="valuesExplanation[${valuesExplanationIndex}][key]" value="" placeholder="例如：Customer First">
                    </div>
                    <div class="form-group">
                        <label>描述 (Description)</label>
                        <textarea name="valuesExplanation[${valuesExplanationIndex}][description]" rows="3"></textarea>
                    </div>
                    <button type="button" class="remove-btn" onclick="removeValuesExplanation(this)">删除</button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            valuesExplanationIndex++;
        }
        
        function removeValuesExplanation(btn) {
            if (confirm('确定要删除这个价值观解释吗？')) {
                btn.closest('.values-explanation-item').remove();
            }
        }
        
        // Strategic Objectives functions
        function addYear() {
            const year = prompt('请输入年份（例如：2024）:');
            if (!year) return;
            
            const container = document.getElementById('strategic-objectives-container');
            const html = `
                <div class="year-objectives">
                    <h3>${year}年</h3>
                    <div class="objectives-list" data-year="${year}">
                    </div>
                    <button type="button" class="add-btn" onclick="addObjective('${year}')">添加${year}年目标</button>
                    <button type="button" class="remove-btn" onclick="removeYear('${year}')" style="margin-left: 10px;">删除${year}年</button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            addObjective(year);
        }
        
        function removeYear(year) {
            if (confirm(`确定要删除${year}年的所有目标吗？`)) {
                const yearDivs = document.querySelectorAll('.year-objectives');
                yearDivs.forEach(div => {
                    const h3 = div.querySelector('h3');
                    if (h3 && h3.textContent.includes(year)) {
                        div.remove();
                    }
                });
            }
        }
        
        function addObjective(year) {
            const yearDivs = document.querySelectorAll('.year-objectives');
            let yearDiv = null;
            yearDivs.forEach(div => {
                const h3 = div.querySelector('h3');
                if (h3 && h3.textContent.includes(year)) {
                    yearDiv = div;
                }
            });
            if (!yearDiv) return;
            
            const objectivesList = yearDiv.querySelector('.objectives-list');
            const objIndex = objectivesList.querySelectorAll('.objective-item').length;
            
            const html = `
                <div class="objective-item">
                    <div class="form-row">
                        <div class="form-group">
                            <label>部门</label>
                            <input type="text" name="strategicObjectives[${year}][${objIndex}][department]" value="" placeholder="例如：Technology">
                        </div>
                        <div class="form-group">
                            <label>负责人 (PIC)</label>
                            <input type="text" name="strategicObjectives[${year}][${objIndex}][pic]" value="" placeholder="例如：CTO">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>策略</label>
                        <textarea name="strategicObjectives[${year}][${objIndex}][strategy]" rows="2"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>开始日期</label>
                            <input type="date" name="strategicObjectives[${year}][${objIndex}][startDate]" value="">
                        </div>
                        <div class="form-group">
                            <label>结束日期</label>
                            <input type="date" name="strategicObjectives[${year}][${objIndex}][endDate]" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>仪表板指标 (每行一个)</label>
                        <textarea name="strategicObjectives[${year}][${objIndex}][dashboardMetrics]" rows="3" placeholder="System Uptime (%)
Infrastructure Cost Reduction (%)
Implementation Timeline Adherence (%)"></textarea>
                        <small style="color: #666;">每行一个指标</small>
                    </div>
                    <button type="button" class="remove-btn" onclick="removeObjective(this, '${year}')">删除</button>
                </div>
            `;
            objectivesList.insertAdjacentHTML('beforeend', html);
        }
        
        function removeObjective(btn, year) {
            if (confirm('确定要删除这个目标吗？')) {
                btn.closest('.objective-item').remove();
            }
        }
    </script>
</body>
</html>

