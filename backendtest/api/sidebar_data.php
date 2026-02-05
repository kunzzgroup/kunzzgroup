<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Session check
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$start_time = microtime(true);

$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $userId = $_SESSION['user_id'];
    $username = $_SESSION['username'] ?? 'User';
    $position = $_SESSION['position'] ?? 'User';
    $avatarLetter = strtoupper(substr($username, 0, 1));
    
    // Default Permissions
    $permissions = [
        'analytics' => ['kpi_report' => true, 'kpi_upload' => true],
        'hr' => ['staff_management' => true, 'schedule' => true],
        'resource' => ['stock_inventory' => true, 'dishware' => true, 'price_comparison' => true],
        'visual' => [
            'bgmusic' => true, 
            'homepage1' => true, 'about1' => true, 'about4' => true, 
            'tokyo1' => true, 'tokyo5' => true, 
            'join1' => true, 'join2' => true, 'join3' => true
        ],
        'brand' => [
            'kunzz_holdings' => true, 'tokyo_cuisine' => true, 'tokyo_izakaya' => true,
            'j1' => true, 'j2' => true, 'j3' => true,
            'j1_schedule' => true, 'j2_schedule' => true, 'j3_schedule' => true
        ],
        'sections' => [
            'analytics' => true, 'hr' => true, 'resource' => true, 'visual' => true, 'brand' => true
        ]
    ];

    // Fetch Custom Permissions
    $stmt = $pdo->prepare("SELECT * FROM user_sidebar_permissions WHERE user_id = ?");
    $stmt->execute([$userId]);
    $permRow = $stmt->fetch();

    if ($permRow) {
        // Main Sections
        if (!empty($permRow['permissions_json'])) {
            $allowedSections = json_decode($permRow['permissions_json'], true);
            foreach ($permissions['sections'] as $key => $val) {
                $permissions['sections'][$key] = in_array($key, $allowedSections);
            }
        }

        // Submenus
        if (!empty($permRow['submenu_permissions_json'])) {
            $subPerms = json_decode($permRow['submenu_permissions_json'], true);
            
            // Analytics, HR, Resource, Visual (Simple logic)
            foreach (['analytics', 'hr', 'resource'] as $section) {
                if (isset($subPerms[$section]) && is_array($subPerms[$section])) {
                    if (empty($subPerms[$section])) {
                        // Empty array means default true
                    } else {
                        foreach ($permissions[$section] as $key => $val) {
                            $permissions[$section][$key] = in_array($key, $subPerms[$section]);
                        }
                    }
                }
            }

            // Brand Submenu (Level 2)
            if (isset($subPerms['brand']) && is_array($subPerms['brand']) && !empty($subPerms['brand'])) {
                $permissions['brand']['kunzz_holdings'] = in_array('kunzz_holdings', $subPerms['brand']);
                $permissions['brand']['tokyo_cuisine'] = in_array('tokyo_cuisine', $subPerms['brand']);
                $permissions['brand']['tokyo_izakaya'] = in_array('tokyo_izakaya', $subPerms['brand']);
            }
        }

        // Brand Permissions (Level 3 & 4)
        if (!empty($permRow['brand_permissions_json'])) {
            $brandPerms = json_decode($permRow['brand_permissions_json'], true);
            
            // Tokyo Cuisine (J1, J2)
            if (isset($brandPerms['tokyo_cuisine'])) {
                if (is_array($brandPerms['tokyo_cuisine']) && isset($brandPerms['tokyo_cuisine'][0])) {
                    // Old Array Format
                    $permissions['brand']['j1'] = in_array('j1', $brandPerms['tokyo_cuisine']);
                    $permissions['brand']['j2'] = in_array('j2', $brandPerms['tokyo_cuisine']);
                } else if (is_array($brandPerms['tokyo_cuisine'])) {
                    // New Object Format
                    if (!empty($brandPerms['tokyo_cuisine'])) {
                       $permissions['brand']['j1'] = isset($brandPerms['tokyo_cuisine']['j1']);
                       $permissions['brand']['j2'] = isset($brandPerms['tokyo_cuisine']['j2']);
                       
                       // J1 Schedule
                       if (isset($brandPerms['tokyo_cuisine']['j1']) && is_array($brandPerms['tokyo_cuisine']['j1'])) {
                           $permissions['brand']['j1_schedule'] = in_array('schedule', $brandPerms['tokyo_cuisine']['j1']);
                       }
                       // J2 Schedule
                       if (isset($brandPerms['tokyo_cuisine']['j2']) && is_array($brandPerms['tokyo_cuisine']['j2'])) {
                           $permissions['brand']['j2_schedule'] = in_array('schedule', $brandPerms['tokyo_cuisine']['j2']);
                       }
                    }
                }
            }

            // Tokyo Izakaya (J3)
            if (isset($brandPerms['tokyo_izakaya'])) {
                if (is_array($brandPerms['tokyo_izakaya']) && isset($brandPerms['tokyo_izakaya'][0])) {
                    // Old Array Format
                    $permissions['brand']['j3'] = in_array('j3', $brandPerms['tokyo_izakaya']);
                } else if (is_array($brandPerms['tokyo_izakaya'])) {
                    // New Object Format
                    if (!empty($brandPerms['tokyo_izakaya'])) {
                        $permissions['brand']['j3'] = isset($brandPerms['tokyo_izakaya']['j3']);
                        
                        // J3 Schedule
                        if (isset($brandPerms['tokyo_izakaya']['j3']) && is_array($brandPerms['tokyo_izakaya']['j3'])) {
                            $permissions['brand']['j3_schedule'] = in_array('schedule', $brandPerms['tokyo_izakaya']['j3']);
                        }
                    }
                }
            }
        }
    }

    // Apply main section hiding to sub items (cascade)
    foreach (['analytics', 'hr', 'resource', 'visual', 'brand'] as $sec) {
        if (!$permissions['sections'][$sec]) {
            foreach ($permissions[$sec] as &$val) $val = false;
        }
    }

    // Determine KPI Upload URL
    $kpiUploadUrl = 'analytics/kpiedit.php'; // Default
    if (!empty($permRow['page_permissions_json'])) {
        $pagePerms = json_decode($permRow['page_permissions_json'], true);
        if (is_array($pagePerms) && isset($pagePerms['kpi_upload']) && isset($pagePerms['kpi_upload']['type'])) {
            $uploadTypes = array_values(array_intersect($pagePerms['kpi_upload']['type'] ?? [], ['kpi', 'cost']));
            if (count($uploadTypes) === 1 && $uploadTypes[0] === 'cost') {
                $kpiUploadUrl = 'analytics/costedit.php';
            }
        }
    }

    echo json_encode([
        'success' => true,
        'user' => [
            'username' => $username,
            'position' => $position,
            'avatar_letter' => $avatarLetter
        ],
        'permissions' => $permissions,
        'urls' => [
            'kpi_upload' => $kpiUploadUrl
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
