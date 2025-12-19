<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("需要登录");
}

$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $userId = $_SESSION['user_id'];
    
    echo "<h1>权限检查 - aboutpage4upload.php</h1>";
    echo "<p>用户ID: $userId</p>";
    echo "<p>用户名: " . ($_SESSION['username'] ?? '未知') . "</p>";
    
    // 检查主模块权限
    $canSeeVisual = true; // 默认值
    $canSeeAnalytics = true;
    $canSeeHR = true;
    $canSeeResource = true;
    $canSeeBrand = true;
    
    // 检查用户权限表
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS user_sidebar_permissions (
            user_id INT PRIMARY KEY,
            permissions_json TEXT NULL,
            page_permissions_json TEXT NULL,
            submenu_permissions_json TEXT NULL,
            brand_permissions_json TEXT NULL
        )");
        
        $permStmt = $pdo->prepare("SELECT permissions_json, submenu_permissions_json FROM user_sidebar_permissions WHERE user_id = ?");
        $permStmt->execute([$userId]);
        $permRow = $permStmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h2>1. 主模块权限 (permissions_json)</h2>";
        if ($permRow && !empty($permRow['permissions_json'])) {
            $list = json_decode($permRow['permissions_json'], true);
            if (is_array($list) && !empty($list)) {
                $map = array_flip($list);
                $canSeeAnalytics = isset($map['analytics']);
                $canSeeHR = isset($map['hr']);
                $canSeeResource = isset($map['resource']);
                $canSeeVisual = isset($map['visual']);
                $canSeeBrand = isset($map['brand']);
                
                echo "<pre>" . print_r($list, true) . "</pre>";
                echo "<p>视觉管理模块可见: " . ($canSeeVisual ? "<span style='color: green;'>✓ 是</span>" : "<span style='color: red;'>✗ 否</span>") . "</p>";
            } else {
                echo "<p style='color: orange;'>权限数组为空，使用默认值（全部可见）</p>";
            }
        } else {
            echo "<p style='color: green;'>没有权限记录，使用默认值（全部可见）</p>";
        }
        
        echo "<h2>2. 子菜单权限 (submenu_permissions_json)</h2>";
        $submenuVisibility = [
            'visual' => [
                'bgmusic' => true,
                'homepage1' => true,
                'about1' => true,
                'about4' => true,
                'tokyo1' => true,
                'tokyo5' => true,
                'join1' => true,
                'join2' => true,
                'join3' => true,
            ],
        ];
        
        if ($permRow && isset($permRow['submenu_permissions_json']) && !empty($permRow['submenu_permissions_json'])) {
            $subList = json_decode($permRow['submenu_permissions_json'], true);
            echo "<pre>" . print_r($subList, true) . "</pre>";
            
            if (is_array($subList) && !empty($subList) && isset($subList['visual'])) {
                $allowed = $subList['visual'];
                if (is_array($allowed) && !empty($allowed)) {
                    foreach ($submenuVisibility['visual'] as $key => $value) {
                        $submenuVisibility['visual'][$key] = in_array($key, $allowed, true);
                    }
                }
            }
            
            // 如果主模块可见，所有子选项都可见
            if ($canSeeVisual) {
                foreach ($submenuVisibility['visual'] as $key => $value) {
                    $submenuVisibility['visual'][$key] = true;
                }
            }
        } else {
            echo "<p style='color: green;'>没有子菜单权限记录，使用默认值（全部可见）</p>";
        }
        
        echo "<h2>3. about4 权限状态</h2>";
        $about4Visible = $submenuVisibility['visual']['about4'] ?? true;
        echo "<p>about4 可见: " . ($about4Visible ? "<span style='color: green;'>✓ 是</span>" : "<span style='color: red;'>✗ 否</span>") . "</p>";
        echo "<p>视觉管理主模块可见: " . ($canSeeVisual ? "<span style='color: green;'>✓ 是</span>" : "<span style='color: red;'>✗ 否</span>") . "</p>";
        
        echo "<h2>4. 最终权限判断</h2>";
        if ($canSeeVisual && $about4Visible) {
            echo "<p style='color: green; font-size: 20px; font-weight: bold;'>✓ 用户有权限访问 aboutpage4upload.php</p>";
            echo "<p>按钮应该正常显示。如果按钮仍然看不到，可能是其他原因（CSS、JavaScript、PHP执行错误等）。</p>";
        } else {
            echo "<p style='color: red; font-size: 20px; font-weight: bold;'>✗ 用户没有权限访问 aboutpage4upload.php</p>";
            if (!$canSeeVisual) {
                echo "<p>原因：视觉管理主模块权限被禁用</p>";
            }
            if (!$about4Visible) {
                echo "<p>原因：about4 子菜单权限被禁用</p>";
            }
        }
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>数据库错误: " . $e->getMessage() . "</p>";
    }
    
} catch (PDOException $e) {
    die("数据库连接失败：" . $e->getMessage());
}

echo "<hr>";
echo "<h2>建议</h2>";
echo "<ul>";
echo "<li>如果权限被禁用，需要在数据库中更新权限设置</li>";
echo "<li>如果权限正常但按钮仍然看不到，检查浏览器控制台和PHP错误日志</li>";
echo "<li>检查 aboutpage4upload.php 中是否有条件隐藏按钮的代码</li>";
echo "</ul>";

echo "<p><a href='aboutpage4upload.php'>返回 aboutpage4upload.php</a></p>";
?>

