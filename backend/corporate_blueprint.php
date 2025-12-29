<?php
session_start();
ob_start();

// 设置字符编码
header('Content-Type: text/html; charset=UTF-8');

// 加载JSON数据 - 文件在backend目录中
$jsonFile = __DIR__ . '/corporate_strategy.json';
$strategyData = null;

if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $strategyData = json_decode($jsonContent, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $strategyData = null;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>企业蓝图</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Microsoft YaHei', sans-serif;
            background-color: #faf7f2;
            color: #000000;
            min-height: 100vh;
            overflow-x: hidden;
            overflow-y: auto;
            line-height: 1.6;
        }

        /* 主内容容器 */
        .main-container {
            max-width: 1800px;
            margin: 0 auto;
            padding: clamp(16px, 1.25vw, 24px) 24px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* 标题区域 */
        .header {
            margin-bottom: clamp(24px, 2.08vw, 40px);
        }

        .header-title {
            font-size: clamp(24px, 2.6vw, 50px);
            font-weight: bold;
            color: #000000ff;
            margin-bottom: clamp(8px, 0.63vw, 12px);
        }

        .header-subtitle {
            font-size: clamp(14px, 1.25vw, 18px);
            color: #6b7280;
        }

        /* 章节样式 */
        .section {
            margin-bottom: clamp(32px, 3.13vw, 60px);
        }

        .section-title {
            font-size: clamp(20px, 2.08vw, 32px);
            font-weight: bold;
            color: #000000ff;
            margin-bottom: clamp(16px, 1.67vw, 24px);
            padding-bottom: clamp(8px, 0.83vw, 12px);
            border-bottom: 3px solid #ff5c00;
            display: inline-block;
        }

        /* 卡片样式 */
        .card {
            background: rgba(255, 255, 255, 1);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            padding: clamp(20px, 2.08vw, 32px);
            margin-bottom: clamp(16px, 1.67vw, 24px);
        }

        /* Header Section */
        .header-section-wrapper {
            position: relative;
            background: #ffffff;
            padding: clamp(60px, 7.29vw, 100px) clamp(40px, 4.69vw, 80px);
            overflow: hidden;
        }

        .header-section-wrapper::before {
            content: '';
            position: absolute;
            top: -100px;
            left: -150px;
            width: clamp(400px, 41.67vw, 600px);
            height: clamp(400px, 41.67vw, 600px);
            background: radial-gradient(ellipse at center, rgba(255, 215, 0, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }

        .header-section-wrapper::after {
            content: '';
            position: absolute;
            bottom: -80px;
            right: -100px;
            width: clamp(300px, 31.25vw, 450px);
            height: clamp(300px, 31.25vw, 450px);
            background: radial-gradient(ellipse at center, rgba(255, 215, 0, 0.12) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }

        /* Central white panel */
        .header-panel {
            background: #ffffff;
            border-radius: clamp(16px, 1.67vw, 24px);
            padding: clamp(48px, 5.21vw, 80px) clamp(56px, 6.25vw, 96px);
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: clamp(48px, 5.21vw, 80px);
        }

        /* Left side text content */
        .header-text-content {
            flex: 1;
            text-align: left;
        }

        .header-label {
            font-size: clamp(12px, 1.25vw, 16px);
            color: #ffd700;
            font-weight: 500;
            margin-bottom: clamp(16px, 1.67vw, 24px);
            display: flex;
            align-items: center;
            gap: clamp(8px, 0.83vw, 12px);
            letter-spacing: 0.5px;
        }

        .header-label span {
            color: #ff5c00;
        }

        .header-label-line {
            width: 3px;
            height: clamp(16px, 1.67vw, 20px);
            background: #ff5c00;
        }

        .company-name-large {
            font-size: clamp(36px, 4.69vw, 64px);
            font-weight: 700;
            color: #000000;
            margin-bottom: clamp(12px, 1.25vw, 16px);
            letter-spacing: 1px;
            line-height: 1.2;
        }

        .company-subtitle {
            font-size: clamp(18px, 2.08vw, 28px);
            font-weight: 600;
            color: #000000;
            margin-bottom: clamp(20px, 2.08vw, 28px);
        }

        .plan-title-en {
            font-size: clamp(12px, 1.25vw, 16px);
            color: #ffd700;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: clamp(8px, 0.83vw, 12px);
            letter-spacing: 1px;
        }

        .plan-title-en span {
            color: #ff5c00;
        }

        .plan-title-en-line {
            width: 3px;
            height: clamp(16px, 1.67vw, 20px);
            background: #ff5c00;
        }

        /* Right side logo */
        .header-logo-container {
            flex-shrink: 0;
            position: relative;
            width: clamp(140px, 14.58vw, 200px);
            height: clamp(140px, 14.58vw, 200px);
        }

        .header-logo {
            width: 100%;
            height: 100%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        /* Logo reflection */
        .logo-reflection {
            position: absolute;
            bottom: clamp(-30px, -3.13vw, -40px);
            left: 50%;
            transform: translateX(-50%) scaleY(-1);
            width: 80%;
            height: 20%;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.1) 0%, transparent 100%);
            border-radius: 50%;
            opacity: 0.3;
            filter: blur(2px);
            z-index: 0;
        }

        /* Timeline Section */
        .timeline {
            position: relative;
            padding: clamp(20px, 2.08vw, 32px) 0;
        }

        .timeline-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: clamp(24px, 2.6vw, 40px);
            position: relative;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 32px;
            bottom: -24px;
            width: 2px;
            background: #e5e7eb;
        }

        .timeline-item:last-child::before {
            display: none;
        }

        .timeline-year {
            font-size: clamp(18px, 2.08vw, 24px);
            font-weight: bold;
            color: #ff5c00;
            background: #fff;
            border: 2px solid #ff5c00;
            border-radius: 50%;
            width: clamp(32px, 3.13vw, 48px);
            height: clamp(32px, 3.13vw, 48px);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-right: clamp(16px, 2.08vw, 24px);
            position: relative;
            z-index: 1;
        }

        .timeline-content {
            flex: 1;
            padding-top: clamp(4px, 0.52vw, 8px);
        }

        .timeline-goal {
            font-size: clamp(14px, 1.25vw, 18px);
            color: #374151;
        }

        /* Corporate Core Section */
        .core-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: clamp(16px, 1.67vw, 24px);
        }

        .core-card {
            background: #fff;
            border-radius: 8px;
            padding: clamp(20px, 2.08vw, 32px);
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .core-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .core-card-title {
            font-size: clamp(16px, 1.56vw, 20px);
            font-weight: bold;
            color: #ff5c00;
            margin-bottom: clamp(12px, 1.04vw, 16px);
            padding-bottom: clamp(8px, 0.83vw, 12px);
            border-bottom: 2px solid #ff5c00;
        }

        .core-card-content {
            font-size: clamp(13px, 1.04vw, 16px);
            color: #374151;
            line-height: 1.8;
        }

        .core-card-list {
            list-style: none;
            padding: 0;
        }

        .core-card-list li {
            padding: clamp(6px, 0.63vw, 10px) 0;
            font-size: clamp(13px, 1.04vw, 16px);
            color: #374151;
            border-bottom: 1px solid #f3f4f6;
        }

        .core-card-list li:last-child {
            border-bottom: none;
        }

        .core-card-list li::before {
            content: '•';
            color: #ff5c00;
            font-weight: bold;
            margin-right: 8px;
        }

        /* Culture & Values Explanation */
        .explanation-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: clamp(16px, 1.67vw, 24px);
        }

        .explanation-card {
            background: #fff;
            border-radius: 8px;
            padding: clamp(20px, 2.08vw, 32px);
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .explanation-key {
            font-size: clamp(16px, 1.56vw, 20px);
            font-weight: bold;
            color: #ff5c00;
            margin-bottom: clamp(8px, 0.83vw, 12px);
        }

        .explanation-description {
            font-size: clamp(13px, 1.04vw, 16px);
            color: #374151;
            line-height: 1.8;
        }

        /* Organization Structure */
        .org-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: clamp(24px, 2.6vw, 40px);
        }

        .org-section {
            background: #fff;
            border-radius: 8px;
            padding: clamp(20px, 2.08vw, 32px);
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .org-section-title {
            font-size: clamp(18px, 1.88vw, 24px);
            font-weight: bold;
            color: #000000ff;
            margin-bottom: clamp(16px, 1.67vw, 24px);
            padding-bottom: clamp(8px, 0.83vw, 12px);
            border-bottom: 2px solid #ff5c00;
        }

        .org-list {
            list-style: none;
            padding: 0;
        }

        .org-list-item {
            padding: clamp(10px, 1.04vw, 16px) 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .org-list-item:last-child {
            border-bottom: none;
        }

        .org-name {
            font-size: clamp(14px, 1.25vw, 18px);
            font-weight: 600;
            color: #000000ff;
            margin-bottom: 4px;
        }

        .org-title {
            font-size: clamp(12px, 1.04vw, 16px);
            color: #6b7280;
        }

        /* Strategic Objectives */
        .objectives-container {
            display: flex;
            flex-direction: column;
            gap: clamp(32px, 3.13vw, 48px);
        }

        .year-section {
            background: #fff;
            border-radius: 8px;
            padding: clamp(24px, 2.6vw, 40px);
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .year-title {
            font-size: clamp(20px, 2.08vw, 28px);
            font-weight: bold;
            color: #ff5c00;
            margin-bottom: clamp(20px, 2.08vw, 32px);
            padding-bottom: clamp(8px, 0.83vw, 12px);
            border-bottom: 3px solid #ff5c00;
        }

        .objectives-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: clamp(16px, 1.67vw, 24px);
        }

        .objective-card {
            background: #f9fafb;
            border-radius: 8px;
            padding: clamp(20px, 2.08vw, 32px);
            border-left: 4px solid #ff5c00;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .objective-card:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .objective-department {
            font-size: clamp(14px, 1.25vw, 18px);
            font-weight: bold;
            color: #ff5c00;
            margin-bottom: clamp(8px, 0.83vw, 12px);
        }

        .objective-strategy {
            font-size: clamp(13px, 1.04vw, 16px);
            color: #374151;
            margin-bottom: clamp(12px, 1.04vw, 16px);
            line-height: 1.7;
        }

        .objective-metrics {
            margin-bottom: clamp(12px, 1.04vw, 16px);
        }

        .objective-metrics-title {
            font-size: clamp(12px, 1.04vw, 14px);
            font-weight: 600;
            color: #6b7280;
            margin-bottom: clamp(6px, 0.63vw, 8px);
        }

        .objective-metrics-list {
            list-style: none;
            padding: 0;
        }

        .objective-metrics-list li {
            font-size: clamp(11px, 0.94vw, 13px);
            color: #374151;
            padding: clamp(4px, 0.42vw, 6px) 0;
            padding-left: clamp(16px, 1.56vw, 24px);
            position: relative;
        }

        .objective-metrics-list li::before {
            content: '→';
            position: absolute;
            left: 0;
            color: #ff5c00;
        }

        .objective-meta {
            display: flex;
            flex-wrap: wrap;
            gap: clamp(12px, 1.25vw, 16px);
            padding-top: clamp(12px, 1.04vw, 16px);
            border-top: 1px solid #e5e7eb;
            font-size: clamp(11px, 0.94vw, 13px);
            color: #6b7280;
        }

        .objective-meta-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .objective-meta-label {
            font-weight: 600;
            color: #6b7280;
        }

        .objective-meta-value {
            color: #374151;
        }

        /* 自定义滚动条样式 */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        html {
            scrollbar-width: thin;
            scrollbar-color: #c1c1c1 #f1f1f1;
        }

        /* 响应式设计 */
        @media (max-width: 1024px) {
            .header-panel {
                flex-direction: column;
                text-align: center;
            }

            .header-text-content {
                text-align: center;
            }

            .header-logo-container {
                margin-top: clamp(32px, 3.13vw, 48px);
            }
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 16px;
            }

            .header-section-wrapper {
                padding: clamp(40px, 4.17vw, 60px) clamp(24px, 2.5vw, 32px);
            }

            .header-panel {
                padding: clamp(32px, 3.33vw, 48px) clamp(32px, 3.33vw, 48px);
            }

            .core-grid,
            .explanation-grid,
            .objectives-grid {
                grid-template-columns: 1fr;
            }

            .org-container {
                grid-template-columns: 1fr;
            }

            .timeline-item::before {
                left: 11px;
            }

            .timeline-year {
                width: 24px;
                height: 24px;
                font-size: 12px;
                margin-right: 12px;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <!-- 主内容区域 -->
    <div class="main-content">
        <div class="main-container">
            <!-- 页面标题 -->
            <div class="header">
                <h1 class="header-title">企业蓝图</h1>
            </div>

            <?php if ($strategyData): ?>
                <!-- Header Section -->
                <div class="section">
                    <div class="header-section-wrapper">
                        <div class="header-panel">
                                <!-- Left side text content -->
                                <div class="header-text-content">
                                    <div class="header-label">
                                        <div class="header-label-line"></div>
                                        <span>企业蓝图</span>
                                    </div>
                                    <div class="company-name-large">
                                        KUNZZ HOLDINGS
                                    </div>
                                    <div class="company-subtitle">
                                        SDN BHD 战略计划
                                    </div>
                                    <div class="plan-title-en">
                                        <div class="plan-title-en-line"></div>
                                        <span>CORPORATE STRATEGIC PLAN</span>
                                    </div>
                                </div>

                                <!-- Right side logo -->
                                <div class="header-logo-container">
                                    <div class="header-logo">
                                        <img src="../images/images/logo.png" alt="KUNZZ HOLDINGS Logo">
                                        <div class="logo-reflection"></div>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline Section -->
                <?php if (!empty($strategyData['timeline'])): ?>
                <div class="section">
                    <h2 class="section-title">战略时间线</h2>
                    <div class="timeline">
                        <?php foreach ($strategyData['timeline'] as $item): ?>
                        <div class="timeline-item">
                            <div class="timeline-year"><?php echo htmlspecialchars($item['year'] ?? ''); ?></div>
                            <div class="timeline-content">
                                <div class="timeline-goal"><?php echo htmlspecialchars($item['goal'] ?? ''); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Corporate Core Section -->
                <?php if (!empty($strategyData['corporateCore'])): ?>
                <div class="section">
                    <h2 class="section-title">企业核心</h2>
                    <div class="core-grid">
                        <!-- Mission -->
                        <div class="core-card">
                            <div class="core-card-title">使命 Mission</div>
                            <div class="core-card-content">
                                <?php echo htmlspecialchars($strategyData['corporateCore']['mission'] ?? ''); ?>
                            </div>
                        </div>

                        <!-- Vision -->
                        <div class="core-card">
                            <div class="core-card-title">愿景 Vision</div>
                            <div class="core-card-content">
                                <?php echo htmlspecialchars($strategyData['corporateCore']['vision'] ?? ''); ?>
                            </div>
                        </div>

                        <!-- Culture -->
                        <div class="core-card">
                            <div class="core-card-title">文化 Culture</div>
                            <ul class="core-card-list">
                                <?php if (!empty($strategyData['corporateCore']['culture'])): ?>
                                    <?php foreach ($strategyData['corporateCore']['culture'] as $culture): ?>
                                        <li><?php echo htmlspecialchars($culture); ?></li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <!-- Values -->
                        <div class="core-card">
                            <div class="core-card-title">价值观 Values</div>
                            <ul class="core-card-list">
                                <?php if (!empty($strategyData['corporateCore']['values'])): ?>
                                    <?php foreach ($strategyData['corporateCore']['values'] as $value): ?>
                                        <li><?php echo htmlspecialchars($value); ?></li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Culture Explanation -->
                <?php if (!empty($strategyData['cultureExplanation'])): ?>
                <div class="section">
                    <h2 class="section-title">文化阐述</h2>
                    <div class="explanation-grid">
                        <?php foreach ($strategyData['cultureExplanation'] as $culture): ?>
                        <div class="explanation-card">
                            <div class="explanation-key"><?php echo htmlspecialchars($culture['key'] ?? ''); ?></div>
                            <div class="explanation-description">
                                <?php echo htmlspecialchars($culture['description'] ?? ''); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Values Explanation -->
                <?php if (!empty($strategyData['valuesExplanation'])): ?>
                <div class="section">
                    <h2 class="section-title">价值观阐述</h2>
                    <div class="explanation-grid">
                        <?php foreach ($strategyData['valuesExplanation'] as $value): ?>
                        <div class="explanation-card">
                            <div class="explanation-key"><?php echo htmlspecialchars($value['key'] ?? ''); ?></div>
                            <div class="explanation-description">
                                <?php echo htmlspecialchars($value['description'] ?? ''); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Organization Structure -->
                <?php if (!empty($strategyData['organizationStructure'])): ?>
                <div class="section">
                    <h2 class="section-title">组织结构</h2>
                    <div class="org-container">
                        <!-- Executives -->
                        <?php if (!empty($strategyData['organizationStructure']['executives'])): ?>
                        <div class="org-section">
                            <div class="org-section-title">管理层</div>
                            <ul class="org-list">
                                <?php foreach ($strategyData['organizationStructure']['executives'] as $exec): ?>
                                <li class="org-list-item">
                                    <div class="org-name"><?php echo htmlspecialchars($exec['name'] ?? ''); ?></div>
                                    <div class="org-title"><?php echo htmlspecialchars($exec['title'] ?? ''); ?></div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <!-- Departments -->
                        <?php if (!empty($strategyData['organizationStructure']['departments'])): ?>
                        <div class="org-section">
                            <div class="org-section-title">部门</div>
                            <ul class="org-list">
                                <?php foreach ($strategyData['organizationStructure']['departments'] as $dept): ?>
                                <li class="org-list-item">
                                    <div class="org-name"><?php echo htmlspecialchars($dept['name'] ?? ''); ?></div>
                                    <div class="org-title"><?php echo htmlspecialchars($dept['head'] ?? ''); ?></div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Strategic Objectives -->
                <?php if (!empty($strategyData['strategicObjectives'])): ?>
                <div class="section">
                    <h2 class="section-title">战略目标</h2>
                    <div class="objectives-container">
                        <?php 
                        // Sort years in ascending order
                        $years = array_keys($strategyData['strategicObjectives']);
                        sort($years, SORT_NUMERIC);
                        
                        foreach ($years as $year): 
                            $objectives = $strategyData['strategicObjectives'][$year];
                            if (empty($objectives)) continue;
                        ?>
                        <div class="year-section">
                            <div class="year-title"><?php echo htmlspecialchars($year); ?> 年</div>
                            <div class="objectives-grid">
                                <?php foreach ($objectives as $objective): ?>
                                <div class="objective-card">
                                    <div class="objective-department">
                                        <?php echo htmlspecialchars($objective['department'] ?? ''); ?>
                                    </div>
                                    <div class="objective-strategy">
                                        <?php echo htmlspecialchars($objective['strategy'] ?? ''); ?>
                                    </div>
                                    
                                    <?php if (!empty($objective['dashboardMetrics'])): ?>
                                    <div class="objective-metrics">
                                        <div class="objective-metrics-title">关键指标：</div>
                                        <ul class="objective-metrics-list">
                                            <?php foreach ($objective['dashboardMetrics'] as $metric): ?>
                                            <li><?php echo htmlspecialchars($metric); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>

                                    <div class="objective-meta">
                                        <?php if (!empty($objective['pic'])): ?>
                                        <div class="objective-meta-item">
                                            <span class="objective-meta-label">负责人：</span>
                                            <span class="objective-meta-value"><?php echo htmlspecialchars($objective['pic']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($objective['startDate']) || !empty($objective['endDate'])): ?>
                                        <div class="objective-meta-item">
                                            <span class="objective-meta-label">时间：</span>
                                            <span class="objective-meta-value">
                                                <?php 
                                                if (!empty($objective['startDate']) && !empty($objective['endDate'])) {
                                                    echo date('Y-m-d', strtotime($objective['startDate'])) . ' ~ ' . date('Y-m-d', strtotime($objective['endDate']));
                                                } elseif (!empty($objective['startDate'])) {
                                                    echo date('Y-m-d', strtotime($objective['startDate']));
                                                }
                                                ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- 如果没有JSON数据，显示错误信息 -->
                <div class="card">
                    <p style="text-align: center; color: #6b7280; padding: 40px;">
                        无法加载战略计划数据。请确保 corporate_strategy.json 文件存在于backend目录且格式正确。
                    </p>
                </div>
            <?php endif; ?>

        </div>
    </div>

</body>
</html>
<?php
ob_end_flush();
?>

