<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>餐厅排班系统诊断</title>
    <link rel="stylesheet" href="../css/check_restaurant_setup.css">
</head>
<body>

    <h1>餐厅排班系统诊断</h1>
    <hr>

    <div class="section">
        <h2>0. 检查数据表是否存在</h2>
        <div id="table-exists-container">Loading...</div>
    </div>
    
    <hr>

    <div class="section">
        <h2>1. 检查 schedule_employees 表结构</h2>
        <div id="table-structure-container">Loading...</div>
    </div>
    
    <hr>

    <div class="section">
        <h2>2. 当前员工列表</h2>
        <div id="employee-list-container">Loading...</div>
    </div>
    
    <hr>

    <div class="section">
        <h2>3. 各餐厅员工统计</h2>
        <div id="stats-container">Loading...</div>
    </div>
    
    <hr>

    <div class="section">
        <h2>4. 快速修复方案</h2>
        <div class="alert-info">
            <h3>如果所有员工都显示为J1：</h3>
            <p>这是正常的，因为默认值是J1。你可以：</p>
            <ol>
                <li><strong>方案1：手动分配现有员工</strong>
                    <pre class="code-block">
-- 将ID为1,2,3的员工分配到J2
UPDATE schedule_employees SET restaurant = 'J2' WHERE id IN (1, 2, 3);

-- 将ID为4,5,6的员工分配到J3
UPDATE schedule_employees SET restaurant = 'J3' WHERE id IN (4, 5, 6);
                    </pre>
                </li>
                <li><strong>方案2：为J2、J3重新添加员工</strong><br>
                    在页面上选择J2餐厅，然后通过"员工管理"添加新员工，这些员工会自动属于J2。
                </li>
            </ol>
        </div>
    </div>

    <hr>
    <p><a href="schedule_manager" class="text-orange">← 返回排班管理</a></p>

    <script src="../js/check_restaurant_setup.js"></script>
</body>
</html>
