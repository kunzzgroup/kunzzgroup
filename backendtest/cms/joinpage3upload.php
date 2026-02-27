<?php
require_once dirname(__DIR__) . '/core/init.php';
require_once CORE_PATH . '/session_check.php';

include_once '../media_config.php';

// 处理语言版本切换
$language = isset($_GET['lang']) ? $_GET['lang'] : 'zh';
$isEnglish = ($language === 'en');

// 数据库配置
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("数据库连接失败：" . $e->getMessage());
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    if ($action === 'add') {
        // 添加职位
        try {
            $stmt = $pdo->prepare("
                INSERT INTO job_positions 
                (job_title, work_experience, recruitment_count, publish_date, company_category, company_department, salary, job_description, company_location, language) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                trim($_POST['job_title']),
                trim($_POST['job_experience']),
                trim($_POST['job_count']),
                $_POST['publish_date'],
                $_POST['job_category'],
                $_POST['company_department'] ?? '',
                $_POST['salary'] ?? '',
                trim($_POST['job_description']),
                $_POST['company_location'] ?? '',
                $language
            ]);
            
            if ($result) {
                // 添加成功后重定向，避免重复提交
                $successMsg = $isEnglish ? "Job position added successfully!" : "职位添加成功！";
                header("Location: joinpage3upload.php?lang={$language}&success=" . urlencode($successMsg));
                exit();
            } else {
                $error = $isEnglish ? "Failed to add job position!" : "职位添加失败！";
            }
        } catch (PDOException $e) {
            $error = $isEnglish ? "Failed to add job position: " . $e->getMessage() : "添加职位失败：" . $e->getMessage();
        }
        
    } elseif ($action === 'edit') {
        // 编辑职位
        try {
            $stmt = $pdo->prepare("
                UPDATE job_positions 
                SET job_title = ?, work_experience = ?, recruitment_count = ?, publish_date = ?, 
                    company_category = ?, company_department = ?, salary = ?, job_description = ?, company_location = ?
                WHERE id = ? AND language = ?
            ");
            
            $result = $stmt->execute([
                trim($_POST['job_title']),
                trim($_POST['job_experience']),
                trim($_POST['job_count']),
                $_POST['publish_date'],
                $_POST['job_category'],
                $_POST['company_department'] ?? '',
                $_POST['salary'] ?? '',
                trim($_POST['job_description']),
                $_POST['company_location'] ?? '',
                $_POST['job_id'],
                $language
            ]);
            
            if ($result) {
                $successMsg = $isEnglish ? "Job position updated successfully!" : "职位更新成功！";
                // 编辑成功后重定向，避免重复提交
                header("Location: joinpage3upload.php?lang={$language}&success=" . urlencode($successMsg));
                exit();
            } else {
                $error = $isEnglish ? "Failed to update job position!" : "职位更新失败！";
            }
        } catch (PDOException $e) {
            $error = $isEnglish ? "Failed to update job position: " . $e->getMessage() : "更新职位失败：" . $e->getMessage();
        }
        
    } elseif ($action === 'delete') {
        // 删除职位
        try {
            $stmt = $pdo->prepare("DELETE FROM job_positions WHERE id = ? AND language = ?");
            $result = $stmt->execute([$_POST['job_id'], $language]);
            
            if ($result) {
                $successMsg = $isEnglish ? "Job position deleted successfully!" : "职位删除成功！";
                // 删除成功后重定向，避免重复提交
                header("Location: joinpage3upload.php?lang={$language}&success=" . urlencode($successMsg));
                exit();
            } else {
                $error = $isEnglish ? "Failed to delete job position!" : "职位删除失败！";
            }
        } catch (PDOException $e) {
            $error = $isEnglish ? "Failed to delete job position: " . $e->getMessage() : "删除职位失败：" . $e->getMessage();
        }
    }
}

// 读取现有职位
try {
    $stmt = $pdo->prepare("SELECT * FROM job_positions WHERE language = ? ORDER BY publish_date DESC, id DESC");
    $stmt->execute([$language]);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $jobs = [];
    $error = $isEnglish ? "Failed to read job data: " . $e->getMessage() : "读取职位数据失败：" . $e->getMessage();
}

// 处理编辑请求
$editJob = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    foreach ($jobs as $job) {
        if ($job['id'] == $editId && $job['language'] == $language) {
            $editJob = $job;
            break;
        }
    }
}

// 处理URL参数中的成功消息
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}

require __DIR__ . '/templates/joinpage3upload.php';
