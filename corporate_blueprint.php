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
            font-family: 'Microsoft YaHei', 'Segoe UI', Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
            min-height: 100vh;
            display: flex;
        }

        /* 左侧边栏 */
        .sidebar {
            width: 60px;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }

        .sidebar-top {
            width: 40px;
            height: 40px;
            background: #ff6b35;
            border-radius: 50%;
        }

        .sidebar-bottom {
            width: 40px;
            height: 20px;
            background: #ff6b35;
            border-radius: 10px;
        }

        /* 主内容区域 */
        .main-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 30px;
            background: #fff;
        }

        /* 标题区域 */
        .header {
            margin-bottom: 30px;
        }

        .header-title {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
        }

        /* 标签页 */
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
        }

        .tab {
            padding: 12px 30px;
            border-radius: 20px;
            border: none;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            background: #e0e0e0;
            color: #666;
        }

        .tab.active {
            background: #ff6b35;
            color: #fff;
        }

        .tab:hover:not(.active) {
            background: #d0d0d0;
        }

        /* 内容区域 */
        .content-area {
            flex: 1;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* 幻灯片容器 */
        .slide-container {
            width: 100%;
            max-width: 1200px;
            position: relative;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 60px;
            min-height: 600px;
            background: linear-gradient(135deg, #fff 0%, #fffef0 50%, #fff 100%);
            position: relative;
            overflow: hidden;
        }

        /* 黄色渐变背景效果 */
        .slide-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -20%;
            width: 80%;
            height: 150%;
            background: radial-gradient(ellipse at center, rgba(255, 215, 0, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        /* 导航按钮 */
        .nav-button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #e0e0e0;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #666;
            transition: all 0.3s;
            z-index: 10;
        }

        .nav-button:hover {
            background: #ff6b35;
            color: #fff;
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
            background: #ff6b35;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .slide-title {
            font-size: 24px;
            font-weight: 700;
            color: #333;
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
            font-size: 48px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
            letter-spacing: 2px;
            line-height: 1.2;
        }

        .company-subtitle {
            font-size: 36px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
            letter-spacing: 1px;
            line-height: 1.2;
        }

        .company-subtitle-en {
            font-size: 18px;
            color: #666;
            letter-spacing: 3px;
            margin-top: 10px;
            text-transform: uppercase;
        }

        /* Logo容器 */
        .logo-container {
            position: absolute;
            right: 60px;
            top: 60px;
            z-index: 2;
        }

        .logo {
            width: 150px;
            height: 150px;
            background: #ff6b35;
            border-radius: 50%;
            border: 3px solid #ffd700;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
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
            background: #ffd700;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .career-table td {
            background: #fff;
            font-size: 13px;
        }

        .career-table tr:nth-child(even) td {
            background: #f9f9f9;
        }

        .career-table tr:hover td {
            background: #fff5f0;
        }

        .table-title {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
        }

        .table-subtitle {
            font-size: 16px;
            color: #666;
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
            color: #555;
        }

        /* 页面指示器 */
        .page-indicator {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #fff5f0;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 14px;
            color: #666;
            font-weight: 600;
            z-index: 2;
        }

        /* 响应式设计 */
        @media (max-width: 1024px) {
            .slide-container {
                padding: 40px;
            }

            .company-name {
                font-size: 36px;
            }

            .company-subtitle {
                font-size: 28px;
            }

            .logo {
                width: 120px;
                height: 120px;
            }

            .logo-k {
                font-size: 48px;
                left: 25px;
            }

            .logo-container {
                right: 40px;
                top: 40px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 40px;
            }

            .main-container {
                padding: 20px;
            }

            .slide-container {
                padding: 30px 20px;
                min-height: 500px;
            }

            .company-name {
                font-size: 28px;
            }

            .company-subtitle {
                font-size: 22px;
            }

            .company-subtitle-en {
                font-size: 14px;
            }

            .logo-container {
                position: relative;
                right: auto;
                top: auto;
                margin: 20px 0;
                align-self: center;
            }

            .logo {
                width: 100px;
                height: 100px;
            }

            .logo-k {
                font-size: 40px;
                left: 20px;
            }

            .nav-button {
                width: 40px;
                height: 40px;
            }

            .nav-button.prev {
                left: -20px;
            }

            .nav-button.next {
                right: -20px;
            }

            .career-table {
                font-size: 12px;
            }

            .career-table th,
            .career-table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <!-- 左侧边栏 -->
    <div class="sidebar">
        <div class="sidebar-top"></div>
        <div class="sidebar-bottom"></div>
    </div>

    <!-- 主内容区域 -->
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

