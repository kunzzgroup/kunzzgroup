<?php
/**
 * 方案2示例：使用 OrgChart.js 实现组织架构图
 * 这是一个示例文件，展示如何使用专业的图表库来实现树形组织架构图
 */

session_start();
ob_start();
header('Content-Type: text/html; charset=UTF-8');

// 加载JSON数据
$jsonFile = __DIR__ . '/../../backend/corporate_strategy.json';
$strategyData = null;

if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $strategyData = json_decode($jsonContent, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $strategyData = null;
    }
}

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
            background-color: #fff8f0;
            padding: 20px;
            margin: 0;
        }
        
        .orgchart-container {
            background: #fff8f0;
            border-radius: 12px;
            padding: 40px;
            position: relative;
            overflow-x: auto;
            min-height: 600px;
        }
        
        /* 背景箭头图案 */
        .orgchart-container::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(0deg);
            width: 800px;
            height: 800px;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><path d="M 20 100 L 180 100 M 150 70 L 180 100 L 150 130" stroke="%23ffd4a3" stroke-width="8" fill="none"/></svg>') no-repeat center;
            background-size: contain;
            opacity: 0.15;
            z-index: 0;
            pointer-events: none;
        }
        
        .orgchart-title {
            position: relative;
            z-index: 2;
            font-size: 32px;
            font-weight: 700;
            color: #ffffff;
            background: #ff5c00;
            padding: 20px 40px;
            border-radius: 0 30px 30px 0;
            display: inline-block;
            margin-bottom: 40px;
            box-shadow: 0 4px 12px rgba(255, 92, 0, 0.3);
        }
        
        /* OrgChart 容器 */
        .orgchart {
            background: transparent;
            position: relative;
            z-index: 1;
        }
        
        /* 连线颜色 - 黑色 */
        .orgchart svg.edge {
            stroke: #000000 !important;
            stroke-width: 2px !important;
        }
        
        .orgchart svg path {
            stroke: #000000 !important;
            stroke-width: 2px !important;
        }
        
        .orgchart .lines .topEdge {
            border-top-color: #000000 !important;
            border-top-width: 2px !important;
        }
        
        .orgchart .lines .rightEdge {
            border-right-color: #000000 !important;
            border-right-width: 2px !important;
        }
        
        .orgchart .lines .leftEdge {
            border-left-color: #000000 !important;
            border-left-width: 2px !important;
        }
        
        .orgchart .lines .bottomEdge {
            border-bottom-color: #000000 !important;
            border-bottom-width: 2px !important;
        }
        
        /* 确保所有连线都是黑色 */
        .orgchart .lines {
            border-color: #000000 !important;
        }
        
        .orgchart .horizontalEdge {
            border-color: #000000 !important;
        }
        
        .orgchart .verticalEdge {
            border-color: #000000 !important;
        }
        
        /* 节点样式 - 橙色圆角矩形 */
        .orgchart .node {
            background: #ff5c00 !important;
            border: none !important;
            border-radius: 12px;
            padding: 20px 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            width: auto;
            min-width: 140px;
            text-align: center;
        }
        
        .orgchart .node:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
        }
        
        /* 所有节点文字颜色 - 白色 */
        .orgchart .node .title,
        .orgchart .node .content {
            color: #ffffff !important;
        }
        
        /* CEO节点样式 */
        .orgchart .node.level-ceo {
            background: #ff5c00 !important;
            border: none !important;
        }
        
        .node-title {
            font-weight: 700;
            margin-bottom: 8px;
            font-size: 18px;
            color: #ffffff;
            line-height: 1.3;
        }
        
        .node-content {
            font-size: 14px;
            color: #ffffff;
            line-height: 1.4;
        }
        
        /* 隐藏默认的节点内容样式 */
        .orgchart .node .title,
        .orgchart .node .content {
            color: #ffffff !important;
            background: transparent !important;
        }
    </style>
    <script>
        const BASE_URL = "/backendtest/";
    </script>
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
            // 组织架构数据（已经是树形结构）
            const orgData = <?php echo json_encode($orgChartData, JSON_UNESCAPED_UNICODE); ?>;
            
            if (!orgData) {
                console.error('组织架构数据为空');
                $('#orgchart').html('<p style="text-align: center; color: #6b7280; padding: 40px;">无法加载组织架构数据</p>');
                return;
            }
            
            console.log('组织架构数据:', orgData);
            
            // 初始化组织架构图 - OrgChart.js 使用树形结构
            $('#orgchart').orgchart({
                'data': orgData,
                'nodeContent': 'title',
                'nodeId': 'id',
                'pan': true,
                'zoom': true,
                'toggleSiblingsResp': true,
                'createNode': function($node, data) {
                    // 自定义节点样式
                    const level = data.level || '';
                    $node.addClass('level-' + level);
                    
                    // 自定义节点内容
                    const title = data.title || '—';
                    const name = data.name || '—';
                    
                    $node.html(
                        '<div class="node-title">' + title + '</div>' +
                        '<div class="node-content">' + name + '</div>'
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

