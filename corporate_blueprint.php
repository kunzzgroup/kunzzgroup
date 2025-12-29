<?php
session_start();
ob_start();

// 设置字符编码
header('Content-Type: text/html; charset=UTF-8');

// 职业规划数据（可以从数据库获取）
$careerData = [
    [
        'level' => 6,
        'rank' => '',
        'title' => 'TECH LEAD',
        'requirements' => [
            '学习适应 (5)',
            '任务执行 (5)',
            '文档整理 (5)',
            '技术决策 (3)',
            '培养一名SR.TECH.ENR',
            '代码规范 (5)',
            '健康管理 (4)',
            '测试验证 (4)',
            '文化价值观 (5)',
            '问题排查 (5)',
            '团队协作 (5)',
            '需求理解 (4)',
            '连续3个月绩效评分 ≥ 85分'
        ],
        'benefits' => ['旅游奖励', '花红奖励'],
        'salary' => 'RM4600 - RM5800'
    ],
    [
        'level' => 7,
        'rank' => '',
        'title' => 'SR. TECH ENGINEER',
        'requirements' => [
            '学习适应 (5)',
            '任务执行 (5)',
            '文档整理 (5)',
            '文化价值观 (4)',
            '代码规范 (5)',
            '健康管理 (4)',
            '测试验证 (4)',
            '连续3个月绩效评分 ≥ 85分',
            '问题排查 (4)',
            '团队协作 (5)',
            '需求理解 (3)'
        ],
        'benefits' => ['旅游奖励', '花红奖励'],
        'salary' => 'RM3300 - RM4500'
    ],
    [
        'level' => 8,
        'rank' => '基层人员',
        'title' => 'TECH ENGINEER',
        'requirements' => [
            '学习适应 (5)',
            '任务执行 (4)',
            '文档整理 (4)',
            '连续3个月绩效评分 ≥ 85分',
            '代码规范 (4)',
            '健康管理 (3)',
            '测试验证 (3)',
            '问题排查 (4)',
            '团队协作 (4)',
            '文化价值观 (4)'
        ],
        'benefits' => ['旅游奖励', '花红奖励'],
        'salary' => 'RM2600 - RM3200'
    ],
    [
        'level' => 9,
        'rank' => '',
        'title' => 'JR. TECH ENGINEER',
        'requirements' => [
            '学习适应 (4)',
            '任务执行 (3)',
            '文档整理 (3)',
            '代码规范 (3)',
            '健康管理 (3)',
            '文化价值观 (3)',
            '问题排查 (3)',
            '团队协作 (3)',
            '连续3个月绩效评分 ≥ 85分'
        ],
        'benefits' => ['旅游奖励', '花红奖励'],
        'salary' => 'RM1900 - RM2500'
    ],
    [
        'level' => 10,
        'rank' => '',
        'title' => 'ENGINEER INTERN',
        'requirements' => [
            '学习适应 (3)',
            '任务执行 (3)',
            '文化价值观 (3)',
            '代码规范 (3)',
            '健康管理 (3)',
            '问题排查 (3)',
            '团队协作 (3)'
        ],
        'benefits' => [],
        'salary' => 'RM1200 - RM1800'
    ]
];

// 将数据转换为JSON供JavaScript使用
$careerDataJson = json_encode($careerData, JSON_UNESCAPED_UNICODE);

// PDF文件路径配置（可以从数据库或配置文件中读取）
// 注意：根据Figma设计，公司战略页面显示封面，PDF将在封面后显示
$strategyPdfPath = isset($_GET['pdf']) ? $_GET['pdf'] : 'pdfs/corporate_strategic_plan.pdf'; // 默认PDF路径
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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #faf7f2;
            color: #000000;
            min-height: 100vh;
            overflow-x: hidden;
            overflow-y: auto;
        }

        /* 主内容区域样式 - sidebar.php 已经处理了 body 的 margin-left */
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: clamp(16px, 1.67vw, 32px);
        }

        .header-title {
            font-size: clamp(20px, 2.6vw, 50px);
            font-weight: bold;
            color: #000000ff;
            margin-bottom: 20px;
        }

        /* 标签页 */
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
        }

        .tab {
            padding: clamp(8px, 0.63vw, 12px) clamp(16px, 1.25vw, 24px);
            border-radius: 8px;
            border: none;
            font-size: clamp(14px, 1.04vw, 16px);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            background: #e5e7eb;
            color: #6b7280;
        }

        .tab.active {
            background: #ff5c00;
            color: #fff;
        }

        .tab:hover:not(.active) {
            background: #d1d5db;
            color: #374151;
        }

        /* 内容区域 */
        .content-area {
            flex: 1;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* 卡片样式 */
        .card {
            background: rgba(255, 255, 255, 1);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }

        .card-body {
            padding: clamp(16px, 1.25vw, 24px);
        }

        /* 幻灯片容器 */
        .slide-container {
            width: 100%;
            position: relative;
            background: rgba(255, 255, 255, 1);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            padding: clamp(24px, 2.08vw, 40px);
            min-height: 600px;
            position: relative;
            overflow: hidden;
        }


        /* 导航按钮 */
        .nav-button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: #e5e7eb;
            border: 1px solid #d1d5db;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #6b7280;
            transition: all 0.2s;
            z-index: 10;
        }

        .nav-button:hover {
            background: #ff5c00;
            color: #fff;
            border-color: #ff5c00;
        }

        .nav-button.prev {
            left: -25px;
        }

        .nav-button.next {
            right: -25px;
        }

        .nav-button:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        /* 公司战略页面样式 */
        .strategic-slide {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            height: 100%;
            position: relative;
            z-index: 1;
        }

        .slide-header {
            display: flex;
            align-items: center;
            margin-bottom: 40px;
            width: 100%;
        }

        .slide-header-line {
            width: 4px;
            height: 40px;
            background: #ff5c00;
            margin-right: 15px;
            flex-shrink: 0;
            border-radius: 2px;
        }

        .slide-title {
            font-size: clamp(18px, 1.56vw, 24px);
            font-weight: 700;
            color: #000000ff;
        }

        /* 公司信息区域 */
        .company-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            width: 100%;
            position: relative;
            z-index: 1;
        }

        .company-name {
            font-size: clamp(32px, 3.13vw, 48px);
            font-weight: 700;
            color: #000000ff;
            margin-bottom: 10px;
            letter-spacing: 2px;
            line-height: 1.2;
        }

        .company-subtitle {
            font-size: clamp(24px, 2.34vw, 36px);
            font-weight: 700;
            color: #000000ff;
            margin-bottom: 10px;
            letter-spacing: 1px;
            line-height: 1.2;
        }

        .company-subtitle-en {
            font-size: clamp(14px, 1.04vw, 18px);
            color: #6b7280;
            letter-spacing: 3px;
            margin-top: 10px;
            text-transform: uppercase;
            font-weight: 500;
        }

        /* Logo容器 */
        .logo-container {
            position: absolute;
            right: 60px;
            top: 60px;
            z-index: 2;
        }

        .logo {
            width: clamp(120px, 9.38vw, 150px);
            height: clamp(120px, 9.38vw, 150px);
            background: #ff5c00;
            border-radius: 50%;
            border: 3px solid #ffd700;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 0 4px 15px rgba(255, 92, 0, 0.3);
        }

        .logo-k {
            font-size: 60px;
            font-weight: 700;
            color: #fff;
            position: absolute;
            left: 30px;
            line-height: 1;
        }

        .logo-arrows {
            position: absolute;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .logo-arrow {
            width: 0;
            height: 0;
            border-left: 15px solid #fff;
            border-top: 8px solid transparent;
            border-bottom: 8px solid transparent;
        }

        /* 个人考核页面样式 */
        .assessment-slide {
            display: none;
            position: relative;
            z-index: 1;
        }

        .assessment-slide.active {
            display: block;
        }

        .career-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .career-table th,
        .career-table td {
            padding: 15px 12px;
            text-align: left;
            border: 1px solid #e0e0e0;
        }

        .career-table th {
            background: #fff;
            color: #000000ff;
            font-weight: bold;
            font-size: clamp(12px, 0.84vw, 14px);
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .career-table td {
            background: #fff;
            font-size: clamp(11px, 0.73vw, 13px);
        }

        .career-table tr:nth-child(even) td {
            background: #f9fafb;
        }

        .career-table tr:hover td {
            background: #f3f4f6;
        }

        .table-title {
            font-size: clamp(18px, 1.56vw, 24px);
            font-weight: 700;
            color: #000000ff;
            margin-bottom: 8px;
        }

        .table-subtitle {
            font-size: clamp(14px, 1.04vw, 16px);
            color: #6b7280;
            margin-bottom: 25px;
            font-weight: 400;
        }

        .requirement-list {
            font-size: 13px;
            line-height: 1.8;
            max-width: 400px;
        }

        .requirement-item {
            margin-bottom: 6px;
            color: #374151;
            font-size: clamp(11px, 0.73vw, 13px);
        }

        /* 页面指示器 */
        .page-indicator {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #f3f4f6;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: clamp(12px, 0.84vw, 14px);
            color: #6b7280;
            font-weight: 600;
            z-index: 2;
            border: 1px solid #e5e7eb;
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

        /* Firefox 滚动条样式 */
        html {
            scrollbar-width: thin;
            scrollbar-color: #c1c1c1 #f1f1f1;
        }

        /* 响应式设计 */
        @media (max-width: 1024px) {
            .slide-container {
                padding: clamp(20px, 2.08vw, 40px);
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }

            .main-content.sidebar-collapsed {
                margin-left: 0;
            }

            body.sidebar-collapsed .main-content {
                margin-left: 0;
            }

            .main-container {
                padding: 20px;
            }

            .slide-container {
                padding: clamp(16px, 1.25vw, 24px);
                min-height: 500px;
            }

            .logo-container {
                position: relative;
                right: auto;
                top: auto;
                margin: 20px 0;
                align-self: center;
            }

            .nav-button {
                width: 36px;
                height: 36px;
                font-size: 16px;
            }

            .nav-button.prev {
                left: -18px;
            }

            .nav-button.next {
                right: -18px;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <!-- 主内容区域 -->
    <div class="main-content">
        <div class="main-container">
        <!-- 标题和标签页 -->
        <div class="header">
            <h1 class="header-title">企业蓝图</h1>
            <div class="tabs">
                <button class="tab active" data-tab="strategy">公司战略</button>
                <button class="tab" data-tab="assessment">个人考核</button>
            </div>
        </div>

        <!-- 内容区域 -->
        <div class="content-area">
            <!-- 导航按钮 -->
            <button class="nav-button prev" id="prevBtn">‹</button>
            <button class="nav-button next" id="nextBtn">›</button>

            <!-- 幻灯片容器 -->
            <div class="slide-container">
                <!-- 公司战略页面 -->
                <div class="strategic-slide" id="strategySlide">
                    <div class="slide-header">
                        <div class="slide-header-line"></div>
                        <div class="slide-title">企业蓝图</div>
                    </div>
                    <div class="company-info">
                        <div class="company-name">KUNZZ HOLDINGS</div>
                        <div class="company-subtitle">SDN BHD 战略计划</div>
                        <div class="company-subtitle-en">CORPORATE STRATEGIC PLAN</div>
                    </div>
                    <div class="logo-container">
                        <div class="logo">
                            <div class="logo-k">K</div>
                            <div class="logo-arrows">
                                <div class="logo-arrow"></div>
                                <div class="logo-arrow"></div>
                            </div>
                        </div>
                    </div>
                    <div class="page-indicator">第1页</div>
                </div>

                <!-- 个人考核页面 -->
                <div class="assessment-slide" id="assessmentSlide">
                    <div class="table-title">职业生涯规划图</div>
                    <div class="table-subtitle">Company Roadmap</div>
                    <table class="career-table">
                        <thead>
                            <tr>
                                <th>级别</th>
                                <th>等级</th>
                                <th>职位</th>
                                <th>升职条件</th>
                                <th>升职福利</th>
                                <th>薪资范围</th>
                            </tr>
                        </thead>
                        <tbody id="careerTableBody">
                            <!-- 表格内容将通过JavaScript动态生成 -->
                        </tbody>
                    </table>
                    <div class="page-indicator">第1页</div>
                </div>
            </div>
        </div>
        </div>
    </div>

    <script>
        // 从PHP获取职业规划数据
        const careerData = <?php echo $careerDataJson; ?>;

        // 当前标签页
        let currentTab = 'strategy';
        let currentPage = 1;

        // 初始化
        function init() {
            // 标签页切换
            document.querySelectorAll('.tab').forEach(tab => {
                tab.addEventListener('click', () => {
                    const tabName = tab.dataset.tab;
                    switchTab(tabName);
                });
            });

            // 导航按钮
            document.getElementById('prevBtn').addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    updatePage();
                }
            });

            document.getElementById('nextBtn').addEventListener('click', () => {
                if (currentTab === 'strategy') {
                    // 公司战略只有一页
                    return;
                } else {
                    // 个人考核可以有多页，这里暂时只有一页
                    currentPage++;
                    updatePage();
                }
            });

            // 生成职业规划表格
            generateCareerTable();
        }

        // 切换标签页
        function switchTab(tabName) {
            currentTab = tabName;
            currentPage = 1;

            // 更新标签页样式
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
                if (tab.dataset.tab === tabName) {
                    tab.classList.add('active');
                }
            });

            // 显示/隐藏内容
            const strategySlide = document.getElementById('strategySlide');
            const assessmentSlide = document.getElementById('assessmentSlide');

            if (tabName === 'strategy') {
                strategySlide.style.display = 'flex';
                assessmentSlide.classList.remove('active');
            } else {
                strategySlide.style.display = 'none';
                assessmentSlide.classList.add('active');
            }

            updatePage();
        }

        // 更新页面
        function updatePage() {
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');

            if (currentTab === 'strategy') {
                prevBtn.disabled = true;
                nextBtn.disabled = true;
            } else {
                prevBtn.disabled = currentPage === 1;
                // 这里可以根据实际页数设置nextBtn的disabled状态
                nextBtn.disabled = false;
            }

            // 更新页面指示器
            const indicators = document.querySelectorAll('.page-indicator');
            indicators.forEach(indicator => {
                indicator.textContent = `第${currentPage}页`;
            });
        }

        // 生成职业规划表格
        function generateCareerTable() {
            const tbody = document.getElementById('careerTableBody');
            tbody.innerHTML = '';

            careerData.forEach(item => {
                const row = document.createElement('tr');
                
                // 级别
                const levelCell = document.createElement('td');
                levelCell.textContent = item.level;
                row.appendChild(levelCell);

                // 等级
                const rankCell = document.createElement('td');
                rankCell.textContent = item.rank || '-';
                row.appendChild(rankCell);

                // 职位
                const titleCell = document.createElement('td');
                titleCell.textContent = item.title;
                row.appendChild(titleCell);

                // 升职条件
                const reqCell = document.createElement('td');
                const reqList = document.createElement('div');
                reqList.className = 'requirement-list';
                item.requirements.forEach(req => {
                    const reqItem = document.createElement('div');
                    reqItem.className = 'requirement-item';
                    reqItem.textContent = req;
                    reqList.appendChild(reqItem);
                });
                reqCell.appendChild(reqList);
                row.appendChild(reqCell);

                // 升职福利
                const benefitCell = document.createElement('td');
                if (item.benefits.length > 0) {
                    benefitCell.textContent = item.benefits.join('、');
                } else {
                    benefitCell.textContent = '-';
                }
                row.appendChild(benefitCell);

                // 薪资范围
                const salaryCell = document.createElement('td');
                salaryCell.textContent = item.salary;
                row.appendChild(salaryCell);

                tbody.appendChild(row);
            });
        }

        // 页面加载完成后初始化
        document.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>
<?php
ob_end_flush();
?>

