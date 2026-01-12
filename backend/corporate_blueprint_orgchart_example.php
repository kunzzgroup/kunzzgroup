<?php
/**
 * 方案2示例：使用 OrgChart.js 实现组织架构图
 * 这是一个示例文件，展示如何使用专业的图表库来实现树形组织架构图
 */

session_start();
ob_start();
header('Content-Type: text/html; charset=UTF-8');

// 加载JSON数据
$jsonFile = __DIR__ . '/corporate_strategy.json';
$strategyData = null;

if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $strategyData = json_decode($jsonContent, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $strategyData = null;
    }
}

// 转换组织架构数据为 OrgChart.js 所需的格式
function convertToOrgChartFormat($orgStructure) {
    $nodes = [];
    
    // CEO节点
    if (!empty($orgStructure['ceo'])) {
        $ceoTitle = $orgStructure['ceo']['title'] ?? $orgStructure['ceo']['fullTitle'] ?? 'CEO';
        $ceoName = $orgStructure['ceo']['name'] ?? '';
        
        $ceoNode = [
            'id' => 'ceo',
            'pid' => null,
            'name' => $ceoName ?: '—',
            'title' => $ceoTitle,
            'level' => 'ceo'
        ];
        $nodes[] = $ceoNode;
    }
    
    // C-Level节点
    if (!empty($orgStructure['cLevel']) && is_array($orgStructure['cLevel'])) {
        foreach ($orgStructure['cLevel'] as $index => $member) {
            $memberTitle = $member['title'] ?? $member['fullTitle'] ?? '';
            $memberName = $member['name'] ?? '';
            
            $cLevelNode = [
                'id' => 'clevel_' . $index,
                'pid' => 'ceo',
                'name' => $memberName ?: '—',
                'title' => $memberTitle,
                'level' => 'clevel'
            ];
            $nodes[] = $cLevelNode;
            
            // 处理下属
            if (!empty($member['subordinates']) && is_array($member['subordinates'])) {
                foreach ($member['subordinates'] as $subIndex => $sub) {
                    $subTitle = $sub['title'] ?? $sub['fullTitle'] ?? '';
                    $subName = $sub['name'] ?? '';
                    
                    $subNode = [
                        'id' => 'sub_' . $index . '_' . $subIndex,
                        'pid' => 'clevel_' . $index,
                        'name' => $subName ?: '—',
                        'title' => $subTitle,
                        'level' => 'subordinate'
                    ];
                    $nodes[] = $subNode;
                }
            }
        }
    }
    
    // PA节点
    if (!empty($orgStructure['pa'])) {
        $paTitle = $orgStructure['pa']['title'] ?? $orgStructure['pa']['fullTitle'] ?? 'PA';
        $paName = $orgStructure['pa']['name'] ?? '';
        
        $paNode = [
            'id' => 'pa',
            'pid' => 'ceo',
            'name' => $paName ?: '—',
            'title' => $paTitle,
            'level' => 'pa'
        ];
        $nodes[] = $paNode;
    }
    
    return $nodes;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>组织架构图 - OrgChart.js 示例</title>
    
    <!-- 引入 OrgChart.js 库 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/orgchart@2.1.9/dist/css/jquery.orgchart.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/orgchart@2.1.9/dist/js/jquery.orgchart.min.js"></script>
    
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Microsoft YaHei', sans-serif;
            background-color: #faf7f2;
            padding: 20px;
        }
        
        .orgchart-container {
            background: #ffffff;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }
        
        .orgchart-title {
            font-size: 28px;
            font-weight: 700;
            color: #ff5c00;
            margin-bottom: 30px;
            text-align: center;
        }
        
        /* 自定义 OrgChart 样式 */
        .orgchart {
            background: transparent;
        }
        
        .orgchart .node {
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .orgchart .node:hover {
            border-color: #ff5c00;
            box-shadow: 0 4px 12px rgba(255, 92, 0, 0.2);
            transform: translateY(-2px);
        }
        
        /* CEO节点样式 */
        .orgchart .node.level-ceo {
            background: linear-gradient(135deg, #ff5c00 0%, #ff8c42 100%);
            border-color: #ff5c00;
            color: #ffffff;
        }
        
        .orgchart .node.level-ceo .title {
            color: #ffffff;
            font-weight: 700;
            font-size: 18px;
        }
        
        .orgchart .node.level-ceo .content {
            color: #ffffff;
            font-size: 16px;
        }
        
        /* C-Level节点样式 */
        .orgchart .node.level-clevel {
            background: #fff5e6;
            border-color: #ff5c00;
        }
        
        .orgchart .node.level-clevel .title {
            color: #ff5c00;
            font-weight: 700;
        }
        
        /* PA节点样式 */
        .orgchart .node.level-pa {
            background: #ffffff;
            border-color: #e2e8f0;
        }
        
        /* 下属节点样式 */
        .orgchart .node.level-subordinate {
            background: #ffffff;
            border-color: #cbd5e1;
        }
        
        .node-title {
            font-weight: 700;
            margin-bottom: 8px;
            font-size: 16px;
        }
        
        .node-content {
            font-size: 14px;
            color: #64748b;
        }
        
        .node.level-ceo .node-content {
            color: #ffffff;
        }
    </style>
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
        
        <script>
        $(document).ready(function() {
            // 组织架构数据
            const orgData = <?php echo json_encode($orgChartData, JSON_UNESCAPED_UNICODE); ?>;
            
            // 转换为 OrgChart.js 所需格式
            const chartData = {};
            
            orgData.forEach(node => {
                const nodeId = node.id;
                chartData[nodeId] = {
                    'id': nodeId,
                    'name': node.name,
                    'title': node.title,
                    'relationship': node.pid ? node.pid : '',
                    'level': node.level
                };
            });
            
            // 初始化组织架构图
            $('#orgchart').orgchart({
                'data': chartData,
                'nodeContent': 'title',
                'nodeId': 'id',
                'pan': true,
                'zoom': true,
                'createNode': function($node, data) {
                    // 自定义节点样式
                    const level = data.level || '';
                    $node.addClass('level-' + level);
                    
                    // 自定义节点内容
                    $node.html(
                        '<div class="node-title">' + data.title + '</div>' +
                        '<div class="node-content">' + data.name + '</div>'
                    );
                }
            });
        });
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

