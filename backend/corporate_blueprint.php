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
    <title>企业蓝图 - KUNZZ HOLDINGS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Microsoft YaHei', 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
            background-color: #f5f7fa;
            color: #1a1a1a;
            min-height: 100vh;
            overflow-x: hidden;
            overflow-y: auto;
            line-height: 1.7;
        }

        /* 主内容容器 */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: clamp(24px, 2.6vw, 48px) clamp(32px, 3.13vw, 64px);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* 标题区域 */
        .header {
            margin-bottom: clamp(32px, 3.13vw, 56px);
            padding-bottom: clamp(16px, 1.67vw, 24px);
            border-bottom: 2px solid #1a1a1a;
        }

        .header-title {
            font-size: clamp(28px, 3.13vw, 42px);
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: clamp(8px, 0.83vw, 12px);
            letter-spacing: -0.5px;
        }

        .header-subtitle {
            font-size: clamp(13px, 1.25vw, 16px);
            color: #64748b;
            font-weight: 400;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        /* 章节样式 */
        .section {
            margin-bottom: clamp(48px, 5.21vw, 80px);
        }

        .section-title {
            font-size: clamp(22px, 2.34vw, 28px);
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: clamp(24px, 2.6vw, 32px);
            padding-bottom: clamp(12px, 1.25vw, 16px);
            border-bottom: 2px solid #1a1a1a;
            display: block;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 2px;
            background-color: #ff5c00;
        }

        /* 卡片样式 */
        .card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: clamp(32px, 3.13vw, 48px);
            margin-bottom: clamp(24px, 2.6vw, 32px);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        /* Header Section */
        .header-section {
            background: #ffffff;
            border: 2px solid #1a1a1a;
            padding: clamp(48px, 5.21vw, 80px) clamp(40px, 4.17vw, 64px);
            text-align: center;
            position: relative;
        }

        .company-name-large {
            font-size: clamp(36px, 4.17vw, 52px);
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: clamp(16px, 1.67vw, 24px);
            letter-spacing: 1px;
            line-height: 1.3;
        }

        .plan-title {
            font-size: clamp(20px, 2.34vw, 28px);
            font-weight: 600;
            color: #475569;
            margin-bottom: clamp(12px, 1.25vw, 16px);
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .year-range {
            font-size: clamp(16px, 1.67vw, 20px);
            color: #64748b;
            font-weight: 500;
            padding: clamp(8px, 0.83vw, 12px) clamp(20px, 2.08vw, 32px);
            display: inline-block;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
        }

        /* Timeline Section */
        .timeline {
            position: relative;
            padding: clamp(24px, 2.6vw, 32px) 0 clamp(24px, 2.6vw, 32px) clamp(32px, 3.13vw, 48px);
            border-left: 3px solid #1a1a1a;
        }

        .timeline-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: clamp(32px, 3.65vw, 48px);
            position: relative;
            padding-left: clamp(24px, 2.6vw, 32px);
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-year {
            font-size: clamp(16px, 1.88vw, 20px);
            font-weight: 700;
            color: #ffffff;
            background: #1a1a1a;
            border: 2px solid #1a1a1a;
            width: clamp(56px, 5.73vw, 72px);
            height: clamp(56px, 5.73vw, 72px);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-left: clamp(-60px, -6.25vw, -76px);
            margin-right: clamp(24px, 2.6vw, 32px);
            position: relative;
            z-index: 2;
        }

        .timeline-content {
            flex: 1;
            padding-top: clamp(12px, 1.25vw, 16px);
        }

        .timeline-goal {
            font-size: clamp(15px, 1.46vw, 18px);
            color: #334155;
            line-height: 1.8;
            font-weight: 400;
        }

        /* Corporate Core Section */
        .core-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: clamp(24px, 2.6vw, 32px);
        }

        .core-card {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: clamp(32px, 3.13vw, 40px);
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .core-card:hover {
            border-color: #1a1a1a;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .core-card-title {
            font-size: clamp(18px, 1.88vw, 22px);
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: clamp(20px, 2.08vw, 24px);
            padding-bottom: clamp(12px, 1.25vw, 16px);
            border-bottom: 2px solid #1a1a1a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .core-card-content {
            font-size: clamp(14px, 1.46vw, 16px);
            color: #475569;
            line-height: 1.9;
            font-weight: 400;
        }

        .core-card-list {
            list-style: none;
            padding: 0;
        }

        .core-card-list li {
            padding: clamp(10px, 1.04vw, 14px) 0;
            font-size: clamp(14px, 1.46vw, 16px);
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
            padding-left: clamp(24px, 2.6vw, 32px);
            position: relative;
        }

        .core-card-list li:last-child {
            border-bottom: none;
        }

        .core-card-list li::before {
            content: '▸';
            color: #ff5c00;
            font-weight: bold;
            position: absolute;
            left: 0;
            font-size: clamp(12px, 1.25vw, 14px);
        }

        /* Culture & Values Explanation */
        .explanation-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: clamp(24px, 2.6vw, 32px);
        }

        .explanation-card {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: clamp(28px, 2.86vw, 36px);
            border-left: 4px solid #ff5c00;
        }

        .explanation-key {
            font-size: clamp(18px, 1.88vw, 22px);
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: clamp(16px, 1.67vw, 20px);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .explanation-description {
            font-size: clamp(14px, 1.46vw, 16px);
            color: #475569;
            line-height: 1.9;
            font-weight: 400;
        }

        /* Organization Structure */
        .org-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: clamp(32px, 3.65vw, 48px);
        }

        .org-section {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: clamp(32px, 3.13vw, 40px);
        }

        .org-section-title {
            font-size: clamp(20px, 2.08vw, 24px);
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: clamp(24px, 2.6vw, 32px);
            padding-bottom: clamp(12px, 1.25vw, 16px);
            border-bottom: 2px solid #1a1a1a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .org-list {
            list-style: none;
            padding: 0;
        }

        .org-list-item {
            padding: clamp(14px, 1.46vw, 18px) 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .org-list-item:last-child {
            border-bottom: none;
        }

        .org-name {
            font-size: clamp(15px, 1.56vw, 18px);
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: clamp(4px, 0.42vw, 6px);
        }

        .org-title {
            font-size: clamp(13px, 1.35vw, 15px);
            color: #64748b;
            font-weight: 400;
        }

        /* Strategic Objectives */
        .objectives-container {
            display: flex;
            flex-direction: column;
            gap: clamp(40px, 4.69vw, 64px);
        }

        .year-section {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: clamp(32px, 3.65vw, 48px);
        }

        .year-title {
            font-size: clamp(24px, 2.6vw, 30px);
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: clamp(28px, 2.86vw, 36px);
            padding-bottom: clamp(12px, 1.25vw, 16px);
            border-bottom: 2px solid #1a1a1a;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
        }

        .year-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 80px;
            height: 2px;
            background-color: #ff5c00;
        }

        .objectives-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: clamp(20px, 2.08vw, 28px);
        }

        .objective-card {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: clamp(24px, 2.6vw, 32px);
            border-left: 4px solid #ff5c00;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .objective-card:hover {
            border-color: #1a1a1a;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .objective-department {
            font-size: clamp(16px, 1.67vw, 18px);
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: clamp(12px, 1.25vw, 16px);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .objective-strategy {
            font-size: clamp(14px, 1.46vw, 16px);
            color: #475569;
            margin-bottom: clamp(16px, 1.67vw, 20px);
            line-height: 1.8;
            font-weight: 400;
        }

        .objective-metrics {
            margin-bottom: clamp(16px, 1.67vw, 20px);
        }

        .objective-metrics-title {
            font-size: clamp(13px, 1.35vw, 14px);
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: clamp(10px, 1.04vw, 12px);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .objective-metrics-list {
            list-style: none;
            padding: 0;
        }

        .objective-metrics-list li {
            font-size: clamp(13px, 1.35vw, 14px);
            color: #475569;
            padding: clamp(6px, 0.63vw, 8px) 0;
            padding-left: clamp(20px, 2.08vw, 24px);
            position: relative;
            line-height: 1.6;
        }

        .objective-metrics-list li::before {
            content: '•';
            position: absolute;
            left: 0;
            color: #ff5c00;
            font-weight: bold;
            font-size: clamp(16px, 1.67vw, 18px);
        }

        .objective-meta {
            display: flex;
            flex-wrap: wrap;
            gap: clamp(20px, 2.08vw, 24px);
            padding-top: clamp(16px, 1.67vw, 20px);
            border-top: 1px solid #e2e8f0;
            font-size: clamp(12px, 1.25vw, 13px);
            color: #64748b;
        }

        .objective-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .objective-meta-label {
            font-weight: 600;
            color: #64748b;
        }

        .objective-meta-value {
            color: #1a1a1a;
            font-weight: 500;
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
            .core-grid {
                grid-template-columns: 1fr;
            }

            .org-container {
                grid-template-columns: 1fr;
            }

            .objectives-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .main-container {
                padding: clamp(20px, 2.6vw, 24px);
            }

            .timeline {
                padding-left: clamp(24px, 2.6vw, 32px);
            }

            .timeline-item {
                padding-left: clamp(16px, 1.67vw, 24px);
            }

            .timeline-year {
                width: clamp(40px, 4.17vw, 48px);
                height: clamp(40px, 4.17vw, 48px);
                font-size: clamp(12px, 1.25vw, 14px);
                margin-left: clamp(-44px, -4.69vw, -52px);
                margin-right: clamp(16px, 1.67vw, 20px);
            }

            .explanation-grid {
                grid-template-columns: 1fr;
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
                <div class="header-subtitle">Corporate Strategic Plan</div>
            </div>

            <?php if ($strategyData): ?>
                <!-- Header Section -->
                <div class="section">
                    <div class="card header-section">
                        <div class="company-name-large">
                            <?php echo htmlspecialchars($strategyData['companyOverview']['companyName'] ?? 'KUNZZ HOLDINGS SDN BHD'); ?>
                        </div>
                        <div class="plan-title">
                            <?php echo htmlspecialchars($strategyData['companyOverview']['planTitle'] ?? 'Corporate Strategic Plan'); ?>
                        </div>
                        <div class="year-range">
                            <?php 
                            $startYear = $strategyData['companyOverview']['strategyStartYear'] ?? '';
                            $endYear = $strategyData['companyOverview']['strategyEndYear'] ?? '';
                            echo $startYear . ' - ' . $endYear;
                            ?>
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

