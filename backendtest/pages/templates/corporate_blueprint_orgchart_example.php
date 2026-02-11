<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>组织架构图 - OrgChart.js 示例</title>
    
    <!-- 引入 OrgChart.js 库 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/orgchart@2.1.9/dist/css/jquery.orgchart.min.css">
    <link rel="stylesheet" href="css/corporate_blueprint_orgchart_example.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/orgchart@2.1.9/dist/js/jquery.orgchart.min.js"></script>
</head>
<body>
    <?php if ($strategyData && !empty($strategyData['organizationStructure'])): ?>
        <?php 
        $orgChartData = convertToOrgChartFormat($strategyData['organizationStructure']);
        ?>
        
        <div class="orgchart-container">
            <h1 class="orgchart-title">高层组织架构图</h1>
            <div id="orgchart" style="width: 100%; height: 600px;"></div>
        </div>
        
        <script src="js/corporate_blueprint_orgchart_example.js"></script>
        <script>
        // 组织架构数据由PHP传递
        const orgData = <?php echo json_encode($orgChartData, JSON_UNESCAPED_UNICODE); ?>;
        initOrgChart(orgData);
        </script>
        
    <?php else: ?>
        <div class="orgchart-container">
            <p style="text-align: center; color: #6b7280; padding: 40px;">
                无法加载组织架构数据。
            </p>
        </div>
    <?php endif; ?>
</body>
</html>
<?php
ob_end_flush();
?>
