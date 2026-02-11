<?php
/**
 * 方案2示例：使用 OrgChart.js 实现组织架构图
 * 这是一个示例文件，展示如何使用专业的图表库来实现树形组织架构图
 */

require_once dirname(__DIR__) . '/core/init.php';
require_once CORE_PATH . '/session_check.php';
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

require __DIR__ . '/templates/corporate_blueprint_orgchart_example.php';
?>
