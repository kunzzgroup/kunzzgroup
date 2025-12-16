<?php
// 员工排班系统API
session_start();
header('Content-Type: application/json');

// 数据库配置
$host = 'localhost';
$dbname = 'u857194726_kunzzgroup';
$username = 'u857194726_kunzzgroup';
$password = 'Kholdings1688@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => '数据库连接失败']);
    exit;
}

// 获取请求方法和操作类型
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// API路由
switch($action) {
    // 获取所有员工
    case 'get_employees':
        if ($method === 'GET') {
            $work_area = $_GET['work_area'] ?? null;
            $restaurant = $_GET['restaurant'] ?? null;
            
            $sql = "SELECT * FROM schedule_employees WHERE 1=1";
            
            $params = [];
            if ($restaurant) {
                $sql .= " AND restaurant = :restaurant";
                $params['restaurant'] = $restaurant;
            }
            if ($work_area) {
                $sql .= " AND work_area = :work_area";
                $params['work_area'] = $work_area;
            }
            $sql .= " ORDER BY work_area, name";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $employees]);
        }
        break;
    
    // 添加员工
    case 'add_employee':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $sql = "INSERT INTO schedule_employees (name, phone, position, work_area, restaurant) 
                    VALUES (:name, :phone, :position, :work_area, :restaurant)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $data['name'],
                ':phone' => $data['phone'],
                ':position' => $data['position'],
                ':work_area' => $data['work_area'],
                ':restaurant' => $data['restaurant'] ?? 'J1'
            ]);
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        }
        break;
    
    // 更新员工
    case 'update_employee':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $sql = "UPDATE schedule_employees 
                    SET name = :name, phone = :phone, position = :position, work_area = :work_area 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $data['name'],
                ':phone' => $data['phone'],
                ':position' => $data['position'],
                ':work_area' => $data['work_area'],
                ':id' => $data['id']
            ]);
            echo json_encode(['success' => true]);
        }
        break;
    
    // 删除员工（软删除）
    // 删除员工（真正删除数据）
    case 'delete_employee':
        if ($method === 'POST') {
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                
                // 先删除该员工的所有排班记录
                $sql1 = "DELETE FROM schedule_records WHERE employee_id = :id";
                $stmt1 = $pdo->prepare($sql1);
                $stmt1->execute([':id' => $data['id']]);
                
                // 再删除员工
                $sql2 = "DELETE FROM schedule_employees WHERE id = :id";
                $stmt2 = $pdo->prepare($sql2);
                $stmt2->execute([':id' => $data['id']]);
                
                echo json_encode(['success' => true, 'deleted_schedules' => $stmt1->rowCount()]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
        break;
    
    // 获取所有班次
    case 'get_shifts':
        if ($method === 'GET') {
            $restaurant = $_GET['restaurant'] ?? null;
            
            $sql = "SELECT * FROM schedule_shifts WHERE 1=1";
            $params = [];
            
            if ($restaurant) {
                $sql .= " AND restaurant = :restaurant";
                $params['restaurant'] = $restaurant;
            }
            
            $sql .= " ORDER BY shift_code";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $shifts]);
        }
        break;
    
    // 添加班次
    case 'add_shift':
        if ($method === 'POST') {
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                $sql = "INSERT INTO schedule_shifts (shift_code, restaurant, start_time, end_time) 
                        VALUES (:shift_code, :restaurant, :start_time, :end_time)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':shift_code' => strtoupper($data['shift_code']),
                    ':restaurant' => $data['restaurant'] ?? 'J1',
                    ':start_time' => $data['start_time'],
                    ':end_time' => $data['end_time']
                ]);
                echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
        break;
    
    // 更新班次
    case 'update_shift':
        if ($method === 'POST') {
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                $sql = "UPDATE schedule_shifts 
                        SET start_time = :start_time, end_time = :end_time 
                        WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':start_time' => $data['start_time'],
                    ':end_time' => $data['end_time'],
                    ':id' => $data['id']
                ]);
                echo json_encode(['success' => true]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
        break;
    
    // 删除班次（真正删除数据）
    case 'delete_shift':
        if ($method === 'POST') {
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                $sql = "DELETE FROM schedule_shifts WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute([':id' => $data['id']]);
                
                if ($result) {
                    echo json_encode(['success' => true, 'affected_rows' => $stmt->rowCount()]);
                } else {
                    echo json_encode(['success' => false, 'error' => '删除失败']);
                }
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
        break;
    
    // 获取假期类型
    case 'get_leave_types':
        if ($method === 'GET') {
            $sql = "SELECT * FROM schedule_leave_types WHERE is_active = 1 ORDER BY type, code";
            $stmt = $pdo->query($sql);
            $leave_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $leave_types]);
        }
        break;
    
    // 获取排班记录
    case 'get_schedules':
        if ($method === 'GET') {
            $year = $_GET['year'] ?? date('Y');
            $month = $_GET['month'] ?? date('m');
            $work_area = $_GET['work_area'] ?? null;
            $restaurant = $_GET['restaurant'] ?? null;
            
            // 获取该月的所有排班记录
            $sql = "SELECT sr.*, se.name as employee_name, se.work_area 
                    FROM schedule_records sr
                    JOIN schedule_employees se ON sr.employee_id = se.id
                    WHERE YEAR(sr.schedule_date) = :year 
                    AND MONTH(sr.schedule_date) = :month
                    AND se.is_active = 1";
            
            $params = [
                'year' => $year,
                'month' => $month
            ];
            
            if ($restaurant) {
                $sql .= " AND se.restaurant = :restaurant";
                $params['restaurant'] = $restaurant;
            }
            
            if ($work_area) {
                $sql .= " AND se.work_area = :work_area";
                $params['work_area'] = $work_area;
            }
            
            $sql .= " ORDER BY sr.schedule_date, se.name";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $schedules]);
        }
        break;
    
    // 保存/更新排班记录
    case 'save_schedule':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // 如果是班次，可能需要保留公共假期背景
            if ($data['value_type'] === 'shift') {
                // 检查该日期是否有公共假期
                $checkSql = "SELECT * FROM schedule_records 
                             WHERE employee_id = :employee_id 
                             AND schedule_date = :schedule_date 
                             AND value_type = 'holiday'";
                $checkStmt = $pdo->prepare($checkSql);
                $checkStmt->execute([
                    ':employee_id' => $data['employee_id'],
                    ':schedule_date' => $data['schedule_date']
                ]);
                $existingHoliday = $checkStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existingHoliday) {
                    // 如果有公共假期，保存班次到notes字段，保留假期类型
                    $sql = "UPDATE schedule_records 
                            SET notes = :shift_code, created_by = :created_by, updated_at = CURRENT_TIMESTAMP
                            WHERE employee_id = :employee_id AND schedule_date = :schedule_date";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':shift_code' => strtoupper($data['value_code']),
                        ':employee_id' => $data['employee_id'],
                        ':schedule_date' => $data['schedule_date'],
                        ':created_by' => $_SESSION['id'] ?? null
                    ]);
                } else {
                    // 没有公共假期，正常保存班次
                    $sql = "REPLACE INTO schedule_records (employee_id, schedule_date, value_type, value_code, notes, created_by) 
                            VALUES (:employee_id, :schedule_date, :value_type, :value_code, :notes, :created_by)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':employee_id' => $data['employee_id'],
                        ':schedule_date' => $data['schedule_date'],
                        ':value_type' => $data['value_type'],
                        ':value_code' => strtoupper($data['value_code']),
                        ':notes' => $data['notes'] ?? null,
                        ':created_by' => $_SESSION['id'] ?? null
                    ]);
                }
            } else {
                // 请假或假期类型，直接保存
                $sql = "REPLACE INTO schedule_records (employee_id, schedule_date, value_type, value_code, notes, created_by) 
                        VALUES (:employee_id, :schedule_date, :value_type, :value_code, :notes, :created_by)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':employee_id' => $data['employee_id'],
                    ':schedule_date' => $data['schedule_date'],
                    ':value_type' => $data['value_type'],
                    ':value_code' => strtoupper($data['value_code']),
                    ':notes' => $data['notes'] ?? null,
                    ':created_by' => $_SESSION['id'] ?? null
                ]);
            }
            echo json_encode(['success' => true]);
        }
        break;
    
    // 批量保存排班记录
    case 'save_schedules_batch':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $pdo->beginTransaction();
            try {
                $sql = "REPLACE INTO schedule_records (employee_id, schedule_date, value_type, value_code, notes, created_by) 
                        VALUES (:employee_id, :schedule_date, :value_type, :value_code, :notes, :created_by)";
                $stmt = $pdo->prepare($sql);
                
                foreach ($data['schedules'] as $schedule) {
                    $stmt->execute([
                        ':employee_id' => $schedule['employee_id'],
                        ':schedule_date' => $schedule['schedule_date'],
                        ':value_type' => $schedule['value_type'],
                        ':value_code' => strtoupper($schedule['value_code']),
                        ':notes' => $schedule['notes'] ?? null,
                        ':created_by' => $_SESSION['id'] ?? null
                    ]);
                }
                
                $pdo->commit();
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
        break;
    
    // 删除排班记录
    case 'delete_schedule':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $sql = "DELETE FROM schedule_records 
                    WHERE employee_id = :employee_id AND schedule_date = :schedule_date";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':employee_id' => $data['employee_id'],
                ':schedule_date' => $data['schedule_date']
            ]);
            echo json_encode(['success' => true]);
        }
        break;
    
    default:
        echo json_encode(['success' => false, 'error' => '无效的操作']);
}
?>

