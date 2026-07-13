<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('brand');

if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}
?>
<?php
ob_start();

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
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>企业蓝图</title>
    <!-- 引入 OrgChart.js 库 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/orgchart@2.1.9/dist/css/jquery.orgchart.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/orgchart@2.1.9/dist/js/jquery.orgchart.min.js"></script>
    <link rel="stylesheet" href="css/corporate_blueprint.css?v=<?php echo time(); ?>">
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
                <!-- Header Section - 新设计 -->
                <div class="section">
                    <div class="header-panel">
                        <!-- 飘动的模糊圆球 -->
                        <div class="floating-orb floating-orb-1"></div>
                        <div class="floating-orb floating-orb-2"></div>
                        <div class="floating-orb floating-orb-3"></div>
                        <div class="floating-orb floating-orb-4"></div>
                        <div class="floating-orb floating-orb-5"></div>
                        
                        <!-- Logo - 居中显示 -->
                        <div class="header-logo-container">
                            <div class="header-logo">
                                <?php 
                                $logoPath = '../images/images/logo.png';
                                $logoFullPath = __DIR__ . '/../images/images/logo.png';
                                if (file_exists($logoFullPath)): 
                                ?>
                                <img src="<?php echo htmlspecialchars($logoPath); ?>" 
                                     alt="KUNZZ HOLDINGS Logo"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <?php else: ?>
                                <!-- 如果图片不存在，显示占位符 -->
                                <div class="logo-fallback" style="display: block;"></div>
                                <?php endif; ?>
                                <!-- 图片加载失败时的备用占位符 -->
                                <div class="logo-fallback" style="display: none;"></div>
                            </div>
                        </div>

                        <!-- 文本内容 - 居中显示 -->
                        <div class="header-text-content">
                            <div class="company-name-large"><?php echo htmlspecialchars($strategyData['companyOverview']['companyName'] ?? 'KUNZZ HOLDINGS SDN BHD'); ?></div>
                            <div class="company-subtitle">企业蓝图 · 战略计划</div>
                        </div>
                    </div>
                </div>

                <!-- Timeline Section -->
                <?php if (!empty($strategyData['timeline'])): ?>
                <div class="section">
                    <div class="timeline-container">
                        <div class="timeline-header">
                            <div class="timeline-main-title">以终为始</div>
                        </div>
                        
                        <div class="timeline-wrapper">
                            <!-- Map-style SVG path -->
                            <svg class="map-timeline-svg" viewBox="0 0 600 600" preserveAspectRatio="none">
                                <defs>
                                    <linearGradient id="routeGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" style="stop-color:rgba(255, 92, 0, 0.3);stop-opacity:1" />
                                        <stop offset="50%" style="stop-color:#ff5c00;stop-opacity:1" />
                                        <stop offset="100%" style="stop-color:rgba(255, 92, 0, 0.3);stop-opacity:1" />
                                    </linearGradient>
                                </defs>
                                <!-- Route glow -->
                                <path class="map-route-glow" d="M 15 300 Q 180 180, 300 300 Q 420 420, 585 300" stroke="url(#routeGradient)"/>
                                <!-- Main route path -->
                                <path class="map-route-path" d="M 15 300 Q 180 180, 300 300 Q 420 420, 585 300" stroke="#ff5c00"/>
                            </svg>

                            <!-- Map milestones -->
                            <?php 
                            if (!empty($strategyData['timeline'])): 
                                $totalItems = count($strategyData['timeline']);
                                
                                // Function to calculate point on quadratic Bezier curve
                                // B(t) = (1-t)²P₀ + 2(1-t)tP₁ + t²P₂
                                function bezierQuad($t, $p0, $p1, $p2) {
                                    $mt = 1 - $t;
                                    return [
                                        $mt * $mt * $p0[0] + 2 * $mt * $t * $p1[0] + $t * $t * $p2[0],
                                        $mt * $mt * $p0[1] + 2 * $mt * $t * $p1[1] + $t * $t * $p2[1]
                                    ];
                                }
                                
                                // SVG path: M 15 300 Q 180 180, 300 300 Q 420 420, 585 300
                                // ViewBox: 600x600
                                // First curve: M 15 300 Q 180 180, 300 300
                                $p0_1 = [15, 300];    // Start point
                                $p1_1 = [180, 180];   // Control point
                                $p2_1 = [300, 300];   // End point
                                
                                // Second curve: Q 420 420, 585 300
                                $p0_2 = [300, 300];   // Start (same as p2_1)
                                $p1_2 = [420, 420];   // Control point
                                $p2_2 = [585, 300];   // End point
                                
                                foreach ($strategyData['timeline'] as $index => $item):
                                    $t = $totalItems > 1 ? $index / ($totalItems - 1) : 0; // 0 to 1
                                    
                                    // Determine which curve segment this point belongs to
                                    // Split the path roughly in half
                                    if ($t <= 0.5) {
                                        // First half: use first Bezier curve
                                        $t_curve = $t * 2; // Map to 0-1 for first curve
                                        $point = bezierQuad($t_curve, $p0_1, $p1_1, $p2_1);
                                    } else {
                                        // Second half: use second Bezier curve
                                        $t_curve = ($t - 0.5) * 2; // Map to 0-1 for second curve
                                        $point = bezierQuad($t_curve, $p0_2, $p1_2, $p2_2);
                                    }
                                    
                                    // Convert SVG coordinates (0-600, 0-600) to percentage
                                    // SVG is 83.33% width and centered, so adjust left position accordingly
                                    $svgWidthPercent = 83.33;
                                    $svgLeftOffset = (100 - $svgWidthPercent) / 2; // 8.335%
                                    $xPercentRelative = ($point[0] / 600) * 100; // Position within SVG (0-100%)
                                    $xPercent = $svgLeftOffset + ($xPercentRelative * $svgWidthPercent / 100); // Actual position in container
                                    $yPercent = ($point[1] / 600) * 100;
                                    
                                    // 单数索引（第1、3、5个，index 0,2,4）往下，双数索引（第2、4个，index 1,3）往上
                                    $cardPosition = ($index % 2 == 0) ? 'bottom' : 'top';
                            ?>
                            <div class="map-milestone milestone-<?php echo $cardPosition; ?>" 
                                 style="left: <?php echo $xPercent; ?>%; top: <?php echo $yPercent; ?>%;"
                                 data-year="<?php echo htmlspecialchars($item['year'] ?? ''); ?>">
                                <div class="milestone-pin"></div>
                                <div class="milestone-card">
                                    <div class="milestone-year"><?php echo htmlspecialchars($item['year'] ?? ''); ?>年</div>
                                    <div class="milestone-goal"><?php echo htmlspecialchars($item['goal'] ?? ''); ?></div>
                                </div>
                            </div>
                            <?php 
                                endforeach; 
                            endif; 
                            ?>

                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Corporate Core Section -->
                <?php if (!empty($strategyData['corporateCore'])): 
                    $corporateCore = $strategyData['corporateCore'];
                ?>
                <div class="section">
                    <div class="core-header">
                        <div class="core-main-title">企业核心</div>
                    </div>
                    <div class="core-grid">
                        <!-- 01 Mission -->
                        <?php if (!empty($corporateCore['mission'])): ?>
                        <div class="core-card">
                            <div class="core-card-number">01</div>
                            <div class="core-card-content-wrapper">
                                <div class="core-card-title">使命:初心&感性的目标</div>
                                <div class="core-card-content">
                                    <?php echo nl2br(htmlspecialchars($corporateCore['mission'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- 02 Vision -->
                        <?php if (!empty($corporateCore['vision'])): ?>
                        <div class="core-card">
                            <div class="core-card-number">02</div>
                            <div class="core-card-content-wrapper">
                                <div class="core-card-title">愿景:理性可具体化的目标</div>
                                <div class="core-card-content">
                                    <?php echo nl2br(htmlspecialchars($corporateCore['vision'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- 03 Culture -->
                        <?php if (!empty($corporateCore['culture']) && is_array($corporateCore['culture'])): ?>
                        <div class="core-card">
                            <div class="core-card-number">03</div>
                            <div class="core-card-content-wrapper">
                                <div class="core-card-title">文化:做人的态度</div>
                                <div class="core-card-content">
                                    <?php echo htmlspecialchars(implode(', ', $corporateCore['culture'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- 04 Values -->
                        <?php if (!empty($corporateCore['values']) && is_array($corporateCore['values'])): ?>
                        <div class="core-card">
                            <div class="core-card-number">04</div>
                            <div class="core-card-content-wrapper">
                                <div class="core-card-title">价值观:做事的态度</div>
                                <div class="core-card-content">
                                    <?php echo htmlspecialchars(implode(', ', $corporateCore['values'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Culture Explanation - 新设计 -->
                <?php if (!empty($strategyData['cultureExplanation']) && is_array($strategyData['cultureExplanation'])): ?>
                <div class="section">
                    <div class="culture-explanation-header">
                        <div class="culture-explanation-title-cn">文化解说&考核</div>
                    </div>
                    <div class="culture-explanation-grid">
                        <?php foreach ($strategyData['cultureExplanation'] as $index => $explanation): ?>
                        <div class="culture-explanation-card">
                            <div class="culture-explanation-content">
                                <div class="culture-explanation-number"><?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?></div>
                                <div class="culture-explanation-key"><?php echo htmlspecialchars($explanation['key'] ?? ''); ?></div>
                                <div class="culture-explanation-description">
                                    <?php echo nl2br(htmlspecialchars($explanation['description'] ?? '')); ?>
                                </div>
                            </div>
                            <?php if (!empty($explanation['scoring']) && is_array($explanation['scoring'])): ?>
                            <div class="culture-scoring">
                                <div class="culture-scoring-title">评分标准:</div>
                                <?php 
                                // 按分数排序
                                usort($explanation['scoring'], function($a, $b) {
                                    return ($a['point'] ?? 0) <=> ($b['point'] ?? 0);
                                });
                                foreach ($explanation['scoring'] as $score): ?>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point"><?php echo intval($score['point'] ?? 0); ?>分:</div>
                                    <div class="culture-scoring-description"><?php echo htmlspecialchars($score['description'] ?? ''); ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Values Explanation - 新设计，风格与 Culture Explanation 一致 -->
                <?php if (!empty($strategyData['valuesExplanation']) && is_array($strategyData['valuesExplanation'])): ?>
                <div class="section">
                    <div class="values-explanation-header">
                        <div class="values-explanation-title-cn">价值观解说&考核</div>
                    </div>
                    <div class="culture-explanation-grid">
                        <?php foreach ($strategyData['valuesExplanation'] as $index => $explanation): ?>
                        <div class="culture-explanation-card">
                            <div class="culture-explanation-content">
                                <div class="culture-explanation-number"><?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?></div>
                                <div class="culture-explanation-key"><?php echo htmlspecialchars($explanation['key'] ?? ''); ?></div>
                                <div class="culture-explanation-description">
                                    <?php echo nl2br(htmlspecialchars($explanation['description'] ?? '')); ?>
                                </div>
                            </div>
                            <?php if (!empty($explanation['scoring']) && is_array($explanation['scoring'])): ?>
                            <div class="culture-scoring">
                                <div class="culture-scoring-title">评分标准:</div>
                                <?php 
                                // 按分数排序
                                usort($explanation['scoring'], function($a, $b) {
                                    return ($a['point'] ?? 0) <=> ($b['point'] ?? 0);
                                });
                                foreach ($explanation['scoring'] as $score): ?>
                                <div class="culture-scoring-item">
                                    <div class="culture-scoring-point"><?php echo intval($score['point'] ?? 0); ?>分:</div>
                                    <div class="culture-scoring-description"><?php echo htmlspecialchars($score['description'] ?? ''); ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Organization Structure - 使用 OrgChart.js -->
                <?php 
                // 转换组织架构数据为 OrgChart.js 所需的树形格式
                function convertToOrgChartFormat($orgStructure) {
                    // CEO节点（根节点）
                    if (empty($orgStructure['ceo'])) {
                        return null;
                    }
                    
                    $ceoTitle = $orgStructure['ceo']['title'] ?? $orgStructure['ceo']['fullTitle'] ?? 'CEO';
                    $ceoName = $orgStructure['ceo']['name'] ?? '';
                    
                    $ceoNode = [
                        'id' => 'ceo',
                        'name' => $ceoName ?: '—',
                        'title' => $ceoTitle,
                        'level' => 'ceo',
                        'children' => []
                    ];
                    
                    // C-Level节点作为CEO的子节点
                    if (!empty($orgStructure['cLevel']) && is_array($orgStructure['cLevel'])) {
                        foreach ($orgStructure['cLevel'] as $index => $member) {
                            $memberTitle = $member['title'] ?? $member['fullTitle'] ?? '';
                            $memberName = $member['name'] ?? '';
                            
                            $cLevelNode = [
                                'id' => 'clevel_' . $index,
                                'name' => $memberName ?: '—',
                                'title' => $memberTitle,
                                'level' => 'clevel',
                                'children' => []
                            ];
                            
                            // 处理下属
                            if (!empty($member['subordinates']) && is_array($member['subordinates'])) {
                                foreach ($member['subordinates'] as $subIndex => $sub) {
                                    $subTitle = $sub['title'] ?? $sub['fullTitle'] ?? '';
                                    $subName = $sub['name'] ?? '';
                                    
                                    $subNode = [
                                        'id' => 'sub_' . $index . '_' . $subIndex,
                                        'name' => $subName ?: '—',
                                        'title' => $subTitle,
                                        'level' => 'subordinate'
                                    ];
                                    $cLevelNode['children'][] = $subNode;
                                }
                            }
                            
                            $ceoNode['children'][] = $cLevelNode;
                        }
                    }
                    
                    // PA节点也作为CEO的子节点
                    if (!empty($orgStructure['pa'])) {
                        $paTitle = $orgStructure['pa']['title'] ?? $orgStructure['pa']['fullTitle'] ?? 'PA';
                        $paName = $orgStructure['pa']['name'] ?? '';
                        
                        $paNode = [
                            'id' => 'pa',
                            'name' => $paName ?: '—',
                            'title' => $paTitle,
                            'level' => 'pa'
                        ];
                        $ceoNode['children'][] = $paNode;
                    }
                    
                    return $ceoNode;
                }
                
                $orgChartData = null;
                if (!empty($strategyData['organizationStructure'])): 
                    $orgChartData = convertToOrgChartFormat($strategyData['organizationStructure']);
                ?>
                <div class="section">
                    <div class="orgchart-container-wrapper">
                        <h1 class="orgchart-title-wrapper">高层组织架构图</h1>
                        <div id="orgchart-container" style="width: 100%; min-height: 600px;"></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php
                // 转换内部组织架构数据为 OrgChart.js 所需的树形格式
                // 返回每个部门作为独立的树形结构数组
                function convertInternalOrgToOrgChartFormat($internalOrgData) {
                    if (empty($internalOrgData) || empty($internalOrgData['departments'])) {
                        return [];
                    }
                    
                    $departmentTrees = [];
                    $departments = $internalOrgData['departments'];
                    
                    foreach ($departments as $deptIndex => $dept) {
                        $deptName = $dept['name'] ?? '';
                        $positions = $dept['positions'] ?? [];
                        
                        if (empty($positions)) {
                            continue;
                        }
                        
                        // 部门根节点（使用第一个职位作为部门头）
                        $firstPosition = $positions[0];
                        $deptTitle = $firstPosition['title'] ?? $deptName;
                        $deptNameValue = $firstPosition['name'] ?? '';
                        
                        $deptRootNode = [
                            'id' => 'dept_' . $deptIndex,
                            'name' => $deptNameValue ?: '—',
                            'title' => $deptTitle,
                            'level' => 'department',
                            'departmentName' => $deptName, // 保存部门名称用于显示
                            'children' => []
                        ];
                        
                        // 添加该部门的其他职位作为子节点
                        for ($i = 1; $i < count($positions); $i++) {
                            $pos = $positions[$i];
                            $posTitle = $pos['title'] ?? '';
                            $posName = $pos['name'] ?? '';
                            
                            $posNode = [
                                'id' => 'dept_' . $deptIndex . '_pos_' . $i,
                                'name' => $posName ?: '—',
                                'title' => $posTitle,
                                'level' => 'position'
                            ];
                            
                            $deptRootNode['children'][] = $posNode;
                        }
                        
                        $departmentTrees[] = $deptRootNode;
                    }
                    
                    return $departmentTrees;
                }
                
                // 内部组织架构数据 - 从JSON读取
                $internalOrgData = $strategyData['internalOrganization'] ?? null;
                
                $internalOrgChartData = $internalOrgData ? convertInternalOrgToOrgChartFormat($internalOrgData) : [];
                if (!empty($internalOrgChartData) && is_array($internalOrgChartData)):
                ?>

                <!-- 内部组织架构图 -->
                <div class="section">
                    <div class="orgchart-container-wrapper">
                        <h1 class="orgchart-title-wrapper">内部组织架构图</h1>
                        
                        <!-- 部门切换按钮组 -->
                        <div class="internal-dept-buttons">
                            <?php foreach ($internalOrgChartData as $deptIndex => $deptTree): ?>
                                <button 
                                    class="internal-dept-btn <?php echo $deptIndex === 0 ? 'active' : ''; ?>" 
                                    data-dept-index="<?php echo $deptIndex; ?>"
                                    onclick="switchInternalDept(<?php echo $deptIndex; ?>)"
                                >
                                    <?php echo htmlspecialchars($deptTree['departmentName'] ?? ''); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- 组织架构图容器 -->
                        <div id="internal-orgchart-container" style="width: 100%; min-height: 600px;">
                            <?php foreach ($internalOrgChartData as $deptIndex => $deptTree): ?>
                                <div class="internal-dept-chart-wrapper <?php echo $deptIndex === 0 ? 'active' : ''; ?>" data-dept-index="<?php echo $deptIndex; ?>">
                                    <div class="internal-dept-orgchart" id="internal-dept-chart-<?php echo $deptIndex; ?>" style="width: 100%; min-height: 500px;"></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Strategic Objectives -->
                <?php 
                $strategicObjectives = $strategyData['strategicObjectives'] ?? [];
                $ultimateGoal = $strategyData['companyOverview']['ultimateGoal'] ?? '';
                $strategyEndYear = $strategyData['companyOverview']['strategyEndYear'] ?? date('Y') + 5;
                
                // 将所有年份的目标合并到一个数组中，用于显示
                $allObjectives = [];
                foreach ($strategicObjectives as $year => $objectives) {
                    foreach ($objectives as $obj) {
                        $allObjectives[] = array_merge($obj, ['year' => $year]);
                    }
                }
                ?>
                <?php if (!empty($allObjectives)): ?>
                <div class="strategic-objectives-section">
                    <!-- 背景装饰 -->
                    <div class="strategic-bg-decor"></div>
                    
                    <div class="strategic-container">
                        <!-- 头部区域 -->
                        <header class="strategic-header">
                            <div class="strategic-header-content">
                                <div class="strategic-header-left">
                                    <div class="strategic-badge">
                                        <svg class="strategy-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"/>
                                            <path d="M12 6v6l4 2"/>
                                        </svg>
                                        <span>最终目标</span>
                                    </div>
                                    <h1 class="strategic-main-title">
                                        <?php echo htmlspecialchars($strategyEndYear); ?>年
                                        <?php if (!empty($ultimateGoal)): ?>
                                        <span class="strategic-year"><?php echo htmlspecialchars($ultimateGoal); ?></span>
                                        <?php endif; ?>
                                    </h1>
                                    </div>
                                </div>
                        </header>

                        <!-- 主要内容区域 -->
                        <main class="strategic-main">
                            <!-- 策略列表 -->
                            <div class="strategic-list-wrapper">
                                <h2 class="strategic-list-title">
                                    策略 · 检核
                                    <span class="strategic-list-count" id="strategicListCount">5</span>
                                </h2>
                                <div class="strategic-list" id="strategicList">
                                    <?php foreach ($allObjectives as $index => $obj): ?>
                                    <button 
                                        class="strategy-card <?php echo $index === 0 ? 'active' : ''; ?>" 
                                        data-strategy-index="<?php echo $index; ?>"
                                        onclick="selectStrategy(<?php echo $index; ?>)"
                                    >
                                        <div class="strategy-icon-wrapper">
                                            <svg class="strategy-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                                <circle cx="9" cy="7" r="4"/>
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                            </svg>
                                        </div>
                                        <div class="strategy-content">
                                            <div class="strategy-meta">
                                                <span class="strategy-id">S<?php echo $index + 1; ?>-<?php echo htmlspecialchars($obj['department'] ?? ''); ?> • <?php echo htmlspecialchars($obj['year'] ?? ''); ?></span>
                                                <svg class="strategy-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="<?php echo $index === 0 ? '' : 'display: none;'; ?>">
                                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                                    <polyline points="22 4 12 14.01 9 11.01"/>
                                                </svg>
                                            </div>
                                            <h3 class="strategy-title"><?php echo htmlspecialchars($obj['strategy'] ?? ''); ?></h3>
                                            <p class="strategy-description"><?php echo htmlspecialchars($obj['department'] ?? ''); ?></p>
                                        </div>
                                        <svg class="strategy-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="9 18 15 12 9 6"/>
                                        </svg>
                                    </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- 详细视图 -->
                            <div class="strategic-details" id="strategicDetails">
                                <div class="details-header">
                                    <svg class="details-header-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                    </svg>
                                    <div>
                                        <div class="details-badge" id="detailsBadge">人事部</div>
                                        <h2 class="details-title" id="detailsTitle">建立高效且有吸引力的人才管理体系</h2>
                                    </div>
                                </div>

                                <div class="details-body">
                                    <!-- 指标和措施 -->
                                    <div class="details-section">
                                        <h4 class="details-section-title">
                                            <svg class="details-section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                                            </svg>
                                            策略 · 检核
                                        </h4>
                                        
                                        <div class="measure-item">
                                            <div class="measure-header">
                                                <span class="measure-badge">D1</span>
                                                <span class="measure-label">关键指标</span>
                                            </div>
                                            <ul class="measure-list" id="measureList">
                                                <li class="measure-list-item">
                                                    <div class="measure-dot"></div>
                                                    <span class="measure-text">人才引进与储备</span>
                                                </li>
                                                <li class="measure-list-item">
                                                    <div class="measure-dot"></div>
                                                    <span class="measure-text">文化宣传</span>
                                                </li>
                                        </ul>
                                    </div>
                                    </div>

                                    <!-- 执行计划 -->
                                    <div class="details-section">
                                        <h4 class="details-section-title">
                                            <svg class="details-section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                                <line x1="16" y1="2" x2="16" y2="6"/>
                                                <line x1="8" y1="2" x2="8" y2="6"/>
                                                <line x1="3" y1="10" x2="21" y2="10"/>
                                            </svg>
                                            行动计划
                                        </h4>
                                        
                                        <div class="execution-plan">
                                            <div class="execution-pic">
                                                <div class="execution-pic-icon">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                                        <circle cx="12" cy="7" r="4"/>
                                                    </svg>
                                        </div>
                                                <div class="execution-pic-info">
                                                    <span class="execution-pic-label">负责人</span>
                                                    <span class="execution-pic-name" id="picName">Paris</span>
                                                </div>
                                            </div>
                                            
                                            <div class="execution-dates">
                                                <div class="execution-date-item">
                                                    <span class="execution-date-label">开始日期</span>
                                                    <span class="execution-date-value" id="startDate">—</span>
                                        </div>
                                                <div class="execution-date-item execution-date-divider">
                                                    <span class="execution-date-label">完成日期</span>
                                                    <span class="execution-date-value" id="endDate">—</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                        </main>
                    </div>
                </div>
                <?php endif; ?>
                
                <script>
                // Pass data from PHP
                const strategiesData = <?php echo json_encode($allObjectives, JSON_UNESCAPED_UNICODE); ?>;
                const orgData = <?php echo !empty($orgChartData) ? json_encode($orgChartData, JSON_UNESCAPED_UNICODE) : 'null'; ?>;
                const internalOrgData = <?php echo !empty($internalOrgChartData) ? json_encode($internalOrgChartData, JSON_UNESCAPED_UNICODE) : 'null'; ?>;
                </script>
                <script src="js/corporate_blueprint.js?v=<?php echo time(); ?>"></script>

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

