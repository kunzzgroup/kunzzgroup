<?php
/**
 * Corporate Blueprint (Monolithic)
 * Serves the corporate blueprint UI with inline HTML/CSS/JS.
 */

require_once __DIR__ . '/../core/session_check.php';
require_login();

// Strategy Data logic
$strategyFile = __DIR__ . '/../../backend/corporate_strategy.json';
$strategyData = null;

if (file_exists($strategyFile)) {
    $json = file_get_contents($strategyFile);
    $strategyData = json_decode($json, true);
}

// User Info for Sidebar (if needed by shell directly)
$username = $_SESSION['username'] ?? 'User';
$position = $_SESSION['position'] ?? 'Member';

?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>企业蓝图 - KUNZZ HOLDINGS</title>
    <link rel="icon" type="image/png" href="../../images/images/logo.png">
    
    <!-- Base Configuration -->
    <script>
        window.BASE_URL = "/backendtest/";
        window.API_BASE = "/backendtest/backend/api/";
        window.PAGE_NAME = "corporate_blueprint";
        window.STRATEGY_DATA = <?php echo json_encode($strategyData, JSON_UNESCAPED_UNICODE); ?>;
    </script>
    
    <!-- External Styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/orgchart@3.1.1/dist/css/jquery.orgchart.min.css">
    
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
            overflow-y: auto;
            line-height: 1.6;
            overflow-x: hidden;
        }

        .section {
            margin-bottom: clamp(24px, 2.08vw, 40px);
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
            margin-bottom: 10px;
            text-align: left;
        }

        .header-title::after {
            content: "";
            display: block;
            height: 3px;
            width: 100%;
            margin-top: 16px;
            background: linear-gradient(90deg, rgba(255,92,0,0) 0%, rgba(0, 0, 0, 1) 25%, rgba(0, 0, 0, 1) 75%, rgba(255,92,0,0) 100%);
        }

        .header-subtitle {
            font-size: clamp(14px, 1.25vw, 18px);
            color: #6b7280;
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

        /* Header Section - 新设计（匹配图片） */
        .header-panel {
            background: linear-gradient(135deg, 
                #fef9f5 0%, 
                #fff5eb 30%, 
                #ffe8d6 60%, 
                #ffddd0 100%);
            border-radius: clamp(16px, 1.67vw, 24px);
            padding: clamp(30px, 3.13vw, 45px) clamp(30px, 3.13vw, 45px);
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
            min-height: clamp(280px, 29.17vw, 400px);
        }

        /* 柔和的圆形模糊背景效果 */
        .header-panel::before {
            content: '';
            position: absolute;
            top: -20%;
            left: -10%;
            width: 40%;
            height: 40%;
            background: radial-gradient(circle, rgba(255, 200, 150, 0.4) 0%, transparent 70%);
            border-radius: 50%;
            filter: blur(60px);
            z-index: 0;
        }

        .header-panel::after {
            content: '';
            position: absolute;
            bottom: -15%;
            right: -5%;
            width: 35%;
            height: 35%;
            background: radial-gradient(circle, rgba(255, 180, 120, 0.3) 0%, transparent 70%);
            border-radius: 50%;
            filter: blur(50px);
            z-index: 0;
        }

        /* 飘动的模糊圆球 - 更明显 */
        .floating-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(35px);
            z-index: 0;
            pointer-events: none;
            opacity: 0.6;
        }

        .floating-orb-1 {
            width: clamp(150px, 15.63vw, 220px);
            height: clamp(150px, 15.63vw, 220px);
            background: radial-gradient(circle, rgba(255, 180, 120, 0.8) 0%, rgba(255, 160, 100, 0.5) 40%, rgba(255, 140, 80, 0.2) 70%, transparent 100%);
            top: 10%;
            left: 15%;
            animation: float1 20s ease-in-out infinite;
        }

        .floating-orb-2 {
            width: clamp(130px, 13.54vw, 180px);
            height: clamp(130px, 13.54vw, 180px);
            background: radial-gradient(circle, rgba(255, 160, 100, 0.75) 0%, rgba(255, 140, 80, 0.45) 40%, rgba(255, 120, 60, 0.2) 70%, transparent 100%);
            top: 60%;
            right: 20%;
            animation: float2 25s ease-in-out infinite;
        }

        .floating-orb-3 {
            width: clamp(110px, 11.46vw, 160px);
            height: clamp(110px, 11.46vw, 160px);
            background: radial-gradient(circle, rgba(255, 200, 140, 0.7) 0%, rgba(255, 180, 120, 0.4) 40%, rgba(255, 160, 100, 0.2) 70%, transparent 100%);
            bottom: 20%;
            left: 25%;
            animation: float3 18s ease-in-out infinite;
        }

        .floating-orb-4 {
            width: clamp(120px, 12.5vw, 170px);
            height: clamp(120px, 12.5vw, 170px);
            background: radial-gradient(circle, rgba(255, 170, 110, 0.7) 0%, rgba(255, 150, 90, 0.4) 40%, rgba(255, 130, 70, 0.2) 70%, transparent 100%);
            top: 30%;
            right: 35%;
            animation: float4 22s ease-in-out infinite;
        }

        .floating-orb-5 {
            width: clamp(100px, 10.42vw, 140px);
            height: clamp(100px, 10.42vw, 140px);
            background: radial-gradient(circle, rgba(255, 190, 130, 0.65) 0%, rgba(255, 170, 110, 0.4) 40%, rgba(255, 150, 90, 0.2) 70%, transparent 100%);
            bottom: 40%;
            right: 10%;
            animation: float5 24s ease-in-out infinite;
        }

        /* 飘动动画 */
        @keyframes float1 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(30px, -40px) scale(1.1); }
            50% { transform: translate(-20px, -60px) scale(0.9); }
            75% { transform: translate(-30px, -20px) scale(1.05); }
        }

        @keyframes float2 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(-40px, 30px) scale(1.15); }
            66% { transform: translate(25px, -35px) scale(0.85); }
        }

        @keyframes float3 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            30% { transform: translate(35px, 25px) scale(1.2); }
            60% { transform: translate(-25px, 40px) scale(0.8); }
            90% { transform: translate(15px, -15px) scale(1.1); }
        }

        @keyframes float4 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            20% { transform: translate(-30px, -25px) scale(1.1); }
            40% { transform: translate(20px, -45px) scale(0.9); }
            60% { transform: translate(35px, 20px) scale(1.15); }
            80% { transform: translate(-15px, 30px) scale(0.95); }
        }

        @keyframes float5 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(20px, 35px) scale(1.05); }
            50% { transform: translate(-35px, 20px) scale(0.9); }
            75% { transform: translate(25px, -30px) scale(1.1); }
        }

        /* Logo 容器 - 居中显示 */
        .header-logo-container {
            position: relative;
            z-index: 2;
            margin-bottom: clamp(20px, 2.08vw, 28px);
            width: clamp(100px, 10.42vw, 140px);
            height: clamp(100px, 10.42vw, 140px);
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
            position: relative;
            z-index: 3;
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
            max-width: 100%;
            max-height: 100%;
            visibility: visible;
            opacity: 1;
        }

        /* 文本内容容器 */
        .header-text-content {
            position: relative;
            z-index: 2;
            text-align: center;
            width: 100%;
        }

        /* 英文公司名 */
        .company-name-large {
            font-size: clamp(24px, 2.5vw, 40px);
            font-weight: 700;
            color: #000000;
            margin-bottom: clamp(12px, 1.25vw, 18px);
            letter-spacing: 2px;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .company-name-large::after {
            content: '';
            display: block;
            width: clamp(240px, 35vw, 520px);
            height: 3px;
            background: #ff5c00;
            margin: clamp(10px, 1.04vw, 14px) auto 0;
            border-radius: 2px;
        }

        /* ... Include other necessary CSS from corporate_blueprint.css here if selective ... */
        /* For this task, including critical styles */
        
        .timeline-container, .orgchart-container-wrapper, .strategic-objectives-section {
            /* Ensure these containers have basic layout */
            position: relative;
        }
        
    </style>
    
    <!-- Required Libraries -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/orgchart@3.1.1/dist/js/jquery.orgchart.min.js"></script>
</head>
<body class="blueprint-page">
    <div id="app">
        <!-- Sidebar -->
        <?php include '../core/sidebar.php'; ?>
        
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
                                <img src="/backendtest/images/images/logo.png" alt="Logo" id="header-logo-img">
                                <div class="logo-fallback" style="display: none;"></div>
                            </div>
                        </div>

                        <!-- 文本内容 - 居中显示 -->
                        <div class="header-text-content">
                            <div class="company-name-large"><span id="company-name-display">KUNZZ HOLDINGS SDN BHD</span></div>
                        </div>
                    </div>
                </div>
                
                <!-- Internal Org Chart -->
                <?php
                // Internal Org Chart Logic Helper
                function convertInternalOrgToOrgChartFormat($internalOrgData) {
                    if (empty($internalOrgData) || empty($internalOrgData['departments'])) return [];
                    
                    $departmentTrees = [];
                    $departments = $internalOrgData['departments'];
                    
                    foreach ($departments as $deptIndex => $dept) {
                        $positions = $dept['positions'] ?? [];
                        if (empty($positions)) continue;
                        
                        $firstPosition = $positions[0];
                        $deptRootNode = [
                            'id' => 'dept_' . $deptIndex,
                            'name' => $firstPosition['name'] ?? '—',
                            'title' => $firstPosition['title'] ?? $dept['name'],
                            'level' => 'department',
                            'departmentName' => $dept['name'],
                            'children' => []
                        ];
                        
                        for ($i = 1; $i < count($positions); $i++) {
                            $pos = $positions[$i];
                            $deptRootNode['children'][] = [
                                'id' => 'dept_' . $deptIndex . '_pos_' . $i,
                                'name' => $pos['name'] ?? '—',
                                'title' => $pos['title'] ?? '',
                                'level' => 'position'
                            ];
                        }
                        $departmentTrees[] = $deptRootNode;
                    }
                    return $departmentTrees;
                }
                
                $internalOrgData = $strategyData['internalOrganization'] ?? null;
                $internalOrgChartData = $internalOrgData ? convertInternalOrgToOrgChartFormat($internalOrgData) : [];
                
                if (!empty($internalOrgChartData) && is_array($internalOrgChartData)):
                ?>
                <div class="section">
                    <div class="orgchart-container-wrapper">
                        <h1 class="orgchart-title-wrapper">内部组织架构图</h1>
                        <div class="internal-dept-buttons" id="internal-dept-buttons">
                        </div>
                        <div id="internal-orgchart-container" style="width: 100%; min-height: 600px;"></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- JS will handle other sections dynamic rendering based on STRATEGY_DATA -->
                <div id="timeline-section" class="section"></div>
                <div id="core-section" class="section"></div>
                
            <?php else: ?>
                <div class="card">
                    <p style="text-align: center; color: #6b7280; padding: 40px;">
                        无法加载战略计划数据。请确保 corporate_strategy.json 文件存在于backend目录且格式正确。
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Application Script -->
    <script>
        // Inline JS Logic for Corporate Blueprint
        
        function initializeDynamicContent() {
            if (!window.STRATEGY_DATA) {
                console.error("STRATEGY_DATA not found");
                return;
            }

            const companyName = window.STRATEGY_DATA.companyOverview?.companyName || "KUNZZ HOLDINGS SDN BHD";
            const nameDisplay = document.getElementById("company-name-display");
            if (nameDisplay) nameDisplay.textContent = companyName;

            renderInternalOrgChart();
            console.log("Dynamic content initialization complete.");
        }

        function renderInternalOrgChart() {
            const internalOrgData = window.STRATEGY_DATA.internalOrganization;
            if (!internalOrgData || !internalOrgData.departments) return;

            const buttonContainer = document.getElementById('internal-dept-buttons');
            const chartContainer = document.getElementById('internal-orgchart-container');
            if(!buttonContainer || !chartContainer) return;

            // Re-convert for client-side usage matching the PHP logic
            function clientConvert(data) {
                return data.departments.map((dept, deptIndex) => {
                    const positions = dept.positions || [];
                    if (positions.length === 0) return null;
                    const first = positions[0];
                    const root = {
                        'id': 'dept_' + deptIndex,
                        'name': first.name || '—',
                        'title': first.title || dept.name,
                        'level': 'department',
                        'departmentName': dept.name,
                        'children': []
                    };
                    for (let i = 1; i < positions.length; i++) {
                        root.children.push({
                            'id': 'dept_' + deptIndex + '_pos_' + i,
                            'name': positions[i].name || '—',
                            'title': positions[i].title || '',
                            'level': 'position'
                        });
                    }
                    return root;
                }).filter(Boolean);
            }

            const internalOrgChartData = clientConvert(internalOrgData);
            window.internalOrgChartData = internalOrgChartData;
            window.initializedCharts = {};

            buttonContainer.innerHTML = internalOrgChartData.map((dept, index) => `
                <button class="internal-dept-btn ${index === 0 ? 'active' : ''}" 
                        data-dept-index="${index}"
                        onclick="switchInternalDept(${index})"
                        style="margin: 0 10px; padding: 10px 20px; cursor: pointer;">
                    ${dept.departmentName}
                </button>
            `).join('');

            chartContainer.innerHTML = internalOrgChartData.map((dept, index) => `
                <div class="internal-dept-chart-wrapper ${index === 0 ? 'active' : ''}" data-dept-index="${index}" style="display: ${index === 0 ? 'block' : 'none'};">
                    <div class="internal-dept-orgchart" id="internal-dept-chart-${index}" style="width: 100%; min-height: 500px;"></div>
                </div>
            `).join('');

            if (internalOrgChartData.length > 0) {
                setTimeout(() => switchInternalDept(0), 100);
            }
        }

        window.switchInternalDept = function (deptIndex) {
            // Simple toggle logic
            document.querySelectorAll('.internal-dept-chart-wrapper').forEach(el => el.style.display = 'none');
            const targetWrapper = document.querySelector(`.internal-dept-chart-wrapper[data-dept-index="${deptIndex}"]`);
            if(targetWrapper) targetWrapper.style.display = 'block';

            if (!window.initializedCharts[deptIndex] && window.internalOrgChartData[deptIndex]) {
                initializeInternalDeptChart(deptIndex, window.internalOrgChartData[deptIndex]);
            }
        };

        function initializeInternalDeptChart(index, deptTree) {
            $(`#internal-dept-chart-${index}`).orgchart({
                'data': deptTree,
                'nodeContent': 'title',
                'nodeId': 'id',
                'pan': false,
                'zoom': false,
                'toggleSiblingsResp': true,
                'createNode': function ($node, data) {
                    $node.addClass('level-' + (data.level || ''));
                    $node.html(`
                        <div class="orgchart-node-title" style="font-weight:bold;">${data.title || '—'}</div>
                        <div class="orgchart-node-content">${data.name || '—'}</div>
                    `);
                },
                'draggable': false,
                'direction': 't2b'
            });
            window.initializedCharts[index] = true;
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', initializeDynamicContent);
    </script>
</body>
</html>
