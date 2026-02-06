<?php
/**
 * Join us Page 3 Upload Shell (Job Positions)
 */
require_once '../system/session_check.php';

// Handle Language Switching
$language = isset($_GET['lang']) ? $_GET['lang'] : 'zh';
$isEnglish = ($language === 'en');

// Database Configuration
// Note: Ideally move these to a shared config file in the future
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

$success = "";
$error = "";

// Handle Form Submissions (Add, Edit, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO job_positions 
                (job_title, work_experience, recruitment_count, publish_date, company_category, company_department, salary, job_description, company_location, language) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
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
            $successMsg = $isEnglish ? "Job position added successfully!" : "职位添加成功！";
            header("Location: joinpage3upload?lang={$language}&success=" . urlencode($successMsg));
            exit();
        } catch (PDOException $e) {
            $error = ($isEnglish ? "Failed: " : "失败：") . $e->getMessage();
        }
    } elseif ($action === 'edit') {
        try {
            $stmt = $pdo->prepare("
                UPDATE job_positions 
                SET job_title = ?, work_experience = ?, recruitment_count = ?, publish_date = ?, 
                    company_category = ?, company_department = ?, salary = ?, job_description = ?, company_location = ?
                WHERE id = ? AND language = ?
            ");
            $stmt->execute([
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
            $successMsg = $isEnglish ? "Job position updated successfully!" : "职位更新成功！";
            header("Location: joinpage3upload?lang={$language}&success=" . urlencode($successMsg));
            exit();
        } catch (PDOException $e) {
            $error = ($isEnglish ? "Update failed: " : "更新失败：") . $e->getMessage();
        }
    } elseif ($action === 'delete') {
        try {
            $stmt = $pdo->prepare("DELETE FROM job_positions WHERE id = ? AND language = ?");
            $stmt->execute([$_POST['job_id'], $language]);
            $successMsg = $isEnglish ? "Job position deleted successfully!" : "职位删除成功！";
            header("Location: joinpage3upload?lang={$language}&success=" . urlencode($successMsg));
            exit();
        } catch (PDOException $e) {
            $error = ($isEnglish ? "Delete failed: " : "删除失败：") . $e->getMessage();
        }
    }
}

// Fetch existing jobs for the selected language
try {
    $stmt = $pdo->prepare("SELECT * FROM job_positions WHERE language = ? ORDER BY publish_date DESC, id DESC");
    $stmt->execute([$language]);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $jobs = [];
    $error = ($isEnglish ? "Failed to read data: " : "读取数据失败：") . $e->getMessage();
}

// Prepare edit data if requested
$editJob = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    foreach ($jobs as $job) {
        if ($job['id'] == $editId) {
            $editJob = $job;
            break;
        }
    }
}

// Success message from redirect
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}

// Include the template
include '../templates/joinpage3upload_template.php';
?>
