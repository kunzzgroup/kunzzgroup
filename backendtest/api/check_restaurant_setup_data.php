<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

// Database configuration
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$username = 'u690174784_kunzz';
$password = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => "Database connection failed: " . $e->getMessage()]);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'check_table_exists':
        checkTableExists($pdo);
        break;
    case 'check_table_structure':
        checkTableStructure($pdo);
        break;
    case 'get_employees':
        getEmployees($pdo);
        break;
    case 'get_stats':
        getStats($pdo);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function checkTableExists($pdo) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'schedule_employees'");
        $exists = $stmt->rowCount() > 0;
        echo json_encode(['success' => true, 'exists' => $exists]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function checkTableStructure($pdo) {
    try {
        $stmt = $pdo->query("DESCRIBE schedule_employees");
        $columns = $stmt->fetchAll();
        $hasRestaurantColumn = false;
        
        foreach ($columns as $col) {
            if ($col['Field'] === 'restaurant') {
                $hasRestaurantColumn = true;
                break;
            }
        }
        
        echo json_encode([
            'success' => true, 
            'columns' => $columns, 
            'hasRestaurantColumn' => $hasRestaurantColumn
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function getEmployees($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, name, phone, position, work_area, restaurant, is_active 
                             FROM schedule_employees 
                             ORDER BY restaurant, work_area, name");
        $employees = $stmt->fetchAll();
        echo json_encode(['success' => true, 'employees' => $employees]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function getStats($pdo) {
    try {
        $stmt = $pdo->query("SELECT 
                                COALESCE(restaurant, '未设置') as restaurant,
                                work_area,
                                COUNT(*) as count
                             FROM schedule_employees
                             WHERE is_active = 1
                             GROUP BY restaurant, work_area
                             ORDER BY restaurant, work_area");
        $stats = $stmt->fetchAll();
        echo json_encode(['success' => true, 'stats' => $stats]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
