<?php
session_start();

// 检查是否已登录（根据你的登录系统调整）
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

include_once 'media_config.php';

// 处理语言版本切换
$language = isset($_GET['lang']) ? $_GET['lang'] : 'zh';
$isEnglish = ($language === 'en');

// 数据库配置
$host = 'localhost';
$dbname = 'u857194726_kunzzgroup';
$dbuser = 'u857194726_kunzzgroup';
$dbpass = 'Kholdings1688@';

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
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <link rel="icon" type="image/png" href="images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEnglish ? 'Job Positions Management' : '招聘职位管理'; ?> - KUNZZ HOLDINGS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            font-size: clamp(8px, 0.74vw, 14px);
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #faf7f2;
            min-height: 100vh;
            padding: 0px;
        }
        
        .container {
            max-width: 1800px;
            margin: 0 auto;
            padding: clamp(16px, 1.25vw, 24px) 24px;
            background: #faf7f2;
            border-radius: 15px;
            overflow: hidden;
        }
        
        .header {
            background: transparent;
            color: #000000ff;
            text-align: center;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .header h1 {
            font-size: clamp(20px, 2.6vw, 50px);
            margin-bottom: 0;
            text-align: left;
        }
        
        .language-switch {
            display: flex;
            gap: 5px;
        }
        
        .language-switch .btn {
            padding: 5px 10px;
            font-size: clamp(6px, 0.63vw, 12px);
            background: #6c757d;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
        }
        
        .language-switch .btn.active {
            background: #f99e00;
        }
        
        .language-switch .btn:hover {
            background: #f99e00;
            transform: translateY(-1px);
        }
        
        .breadcrumb {
            padding: clamp(2px, 1.04vw, 20px) 0px clamp(10px, 1.04vw, 20px);
            background: transparent;
        }
        
        .breadcrumb a {
            font-size: clamp(8px, 0.74vw, 14px);
            color: #f99e00;
            text-decoration: none;
        }
        
        .content {
            padding: 0;
        }
        
        .form-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: clamp(18px, 1.56vw, 30px);
            margin-bottom: clamp(20px, 1.56vw, 30px);
            border-left: 5px solid #000000ff;
            box-shadow: 0px 5px 15px rgb(0 0 0 / 60%);
        }
        
        .form-section h2 {
            color: #333;
            margin-bottom: clamp(10px, 1.04vw, 20px);
            font-size: clamp(12px, 1.04vw, 20px);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: clamp(10px, 1.04vw, 20px);
            margin-bottom: clamp(10px, 1.04vw, 20px);
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            font-weight: 600;
            color: #555;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            border: 2px solid #e0e0e0 !important;
            border-radius: clamp(4px, 0.42vw, 8px)!important;
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px)!important;
            font-size: clamp(8px, 0.84vw, 16px)!important;
            font-family: 'Inter', sans-serif !important;
            transition: all 0.3s ease!important;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none !important;
            border-color: #000000ff !important;
            box-shadow: 0 0 0 3px rgba(34, 13, 0, 0.1) !important;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .btn {
            background: #f99e00;
            color: white;
            border: none;
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            border-radius: clamp(4px, 0.42vw, 8px);
            font-size: clamp(8px, 0.74vw, 14px);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background: #f98500ff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 92, 0, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }

        /* 图标按钮样式 */
        .action-btn {
            background: #ef4444;
            color: white;
            border: none;
            border-radius: clamp(4px, 0.32vw, 6px);
            width: clamp(20px, 2.08vw, 40px);
            height: clamp(20px, 2.08vw, 40px);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            margin-left: 8px;
            font-size: 18px;
        }

        .action-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        }

        .action-btn.edit-btn {
            background: #f59e0b;
        }

        .action-btn.edit-btn:hover {
            background: #d97706;
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
        }

        .action-btn.delete-btn {
            background: #ef4444;
        }

        .action-btn.delete-btn:hover {
            background: #dc2626;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        }

        /* 通知容器 */
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* 通知基础样式 */
        .toast {
            min-width: 300px;
            max-width: 400px;
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
            overflow: hidden;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }

        .toast.hide {
            transform: translateX(100%);
            opacity: 0;
        }

        /* 通知类型样式 */
        .toast-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.9), rgba(5, 150, 105, 0.9));
            color: white;
            border-color: rgba(16, 185, 129, 0.3);
        }

        .toast-error {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.9), rgba(220, 38, 38, 0.9));
            color: white;
            border-color: rgba(239, 68, 68, 0.3);
        }

        .toast-info {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.9), rgba(37, 99, 235, 0.9));
            color: white;
            border-color: rgba(59, 130, 246, 0.3);
        }

        .toast-warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.9), rgba(217, 119, 6, 0.9));
            color: white;
            border-color: rgba(245, 158, 11, 0.3);
        }

        /* 通知图标 */
        .toast-icon {
            font-size: 18px;
            flex-shrink: 0;
        }

        /* 通知内容 */
        .toast-content {
            flex: 1;
            font-weight: 500;
            line-height: 1.4;
        }

        /* 关闭按钮 */
        .toast-close {
            background: none;
            border: none;
            color: inherit;
            font-size: 16px;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            opacity: 0.7;
            transition: all 0.2s;
        }

        .toast-close:hover {
            opacity: 1;
            background: rgba(255, 255, 255, 0.1);
        }

        /* 进度条 */
        .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 0 0 8px 8px;
            transform-origin: left;
            animation: toastProgress 4s linear forwards;
        }

        @keyframes toastProgress {
            from { transform: scaleX(1); }
            to { transform: scaleX(0); }
        }
        
        .btn-small {
            padding: 8px 16px;
            font-size: 0.9em;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .jobs-list {
            background: white;
            border-radius: 10px;
            padding: clamp(18px, 1.56vw, 30px);
            border-left: 5px solid #000000ff;
            box-shadow: 0px 5px 15px rgb(0 0 0 / 60%);
        }
        
        .jobs-list h2 {
            color: #333;
            margin-bottom: clamp(15px, 1.3vw, 25px);
            font-size: clamp(12px, 1.04vw, 20px);
        }
        
        .jobs-list .job-item {
        /* 重置style.css中的所有冲突样式 */
        background: white !important;  /* 覆盖linear-gradient */
        color: #333 !important;        /* 覆盖white色彩 */
        border-radius: 10px !important; /* 覆盖原来的border-radius */
        box-shadow: none !important;    /* 重置box-shadow */
        text-align: left !important;    /* 重置text-align */
        cursor: default !important;     /* 重置cursor */
        
        /* 应用你想要的样式 */
        border: 1px solid #e0e0e0 !important;
        padding: clamp(10px, 1.04vw, 20px) !important;
        margin-bottom: clamp(10px, 1.04vw, 20px) !important;
        transition: all 0.3s ease !important;
    }

    .jobs-list .job-item:hover {
        /* 覆盖hover状态的所有样式 */
        background: white !important;   /* 保持白色背景 */
        transform: none !important;     /* 重置transform */
        box-shadow: 0 5px 15px rgba(255, 92, 0, 0.1) !important; /* 你想要的shadow */
        border-color: #583e04 !important;
    }
        
        .job-header-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .job-title-item {
            font-size: clamp(10px, 0.73vw, 14px);
            font-weight: 700;
            color: #000000ff;
            margin-bottom: 10px;
        }
        
        .job-meta-list {
            display: flex;
            gap: 20px;
            margin-bottom: clamp(10px, 0.78vw, 15px);
        }
        
        .job-meta-item-list {
            font-size: clamp(8px, 0.74vw, 14px);
            color: #666;
        }
        
        .job-actions {
            display: flex;
            gap: 0px;
            margin-top: clamp(30px, 1.82vw, 35px);
        }
        
        .job-description-preview {
            color: #555;
            line-height: 1.6;
            max-height: 60px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .back-btn {
            display: inline-block;
            background: #6c757d;
            color: white;
            text-decoration: none;
            padding: clamp(4px, 0.42vw, 8px) clamp(6px, 0.63vw, 12px);
            font-size: clamp(8px, 0.74vw, 14px);
            border-radius: clamp(4px, 0.32vw, 6px);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }
        
        .form-buttons {
            display: flex;
            gap: 15px;
            margin-top: clamp(15px, 1.3vw, 25px);
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .content {
                padding: 20px;
            }
            
            .job-header-item {
                flex-direction: column;
                gap: 15px;
            }
            
            .job-meta-list {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
    <script>
        function toggleDepartmentField() {
            const companySelect = document.getElementById('job_category');
            const departmentGroup = document.getElementById('department-group');
            const departmentSelect = document.getElementById('company_department');
            
            if (companySelect.value === 'TOKYO JAPANESE CUISINE' || companySelect.value === 'TOKYO IZAKAYA') {
                departmentGroup.style.display = 'flex';
                departmentSelect.required = true;
            } else {
                departmentGroup.style.display = 'none';
                departmentSelect.required = false;
                departmentSelect.value = '';
            }
        }
        
        // 页面加载时检查是否需要显示部门字段
        document.addEventListener('DOMContentLoaded', function() {
            toggleDepartmentField();
            
            // 检查是否有成功或错误消息需要显示
            <?php if (isset($success)): ?>
                showAlert('<?php echo addslashes($success); ?>', 'success');
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                showAlert('<?php echo addslashes($error); ?>', 'error');
            <?php endif; ?>
        });

        // 通知系统
        function showAlert(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;

            // 先检查并限制通知数量（在添加新通知之前）
            let existingToasts = container.querySelectorAll('.toast');
            while (existingToasts.length >= 3) {
                closeToast(existingToasts[0].id);
                // 立即从DOM移除，不等待动画
                if (existingToasts[0].parentNode) {
                    existingToasts[0].parentNode.removeChild(existingToasts[0]);
                }
                // 重新获取当前通知列表
                existingToasts = container.querySelectorAll('.toast');
            }

            const toastId = 'toast-' + Date.now();
            const iconClass = {
                'success': 'fa-check-circle',
                'error': 'fa-exclamation-circle', 
                'info': 'fa-info-circle',
                'warning': 'fa-exclamation-triangle'
            }[type] || 'fa-check-circle';

            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.id = toastId;
            toast.innerHTML = `
                <i class="fas ${iconClass} toast-icon"></i>
                <div class="toast-content">${message}</div>
                <button class="toast-close" onclick="closeToast('${toastId}')">
                    <i class="fas fa-times"></i>
                </button>
                <div class="toast-progress"></div>
            `;

            container.appendChild(toast);

            // 显示动画
            setTimeout(() => {
                toast.classList.add('show');
            }, 0);

            // 自动关闭
            setTimeout(() => {
                closeToast(toastId);
            }, 4000);
        }

        // 添加关闭通知的函数
        function closeToast(toastId) {
            const toast = document.getElementById(toastId);
            if (toast) {
                toast.classList.remove('show');
                toast.classList.add('hide');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }
        }

        // 添加关闭所有通知的函数（可选）
        function closeAllToasts() {
            const toasts = document.querySelectorAll('.toast');
            toasts.forEach(toast => {
                closeToast(toast.id);
            });
        }
    </script>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <h1><?php echo $isEnglish ? 'Job Positions Management' : '招聘职位管理'; ?></h1>
            <div class="language-switch">
                <a href="?lang=zh" class="btn <?php echo !$isEnglish ? 'active' : ''; ?>">中文</a>
                <a href="?lang=en" class="btn <?php echo $isEnglish ? 'active' : ''; ?>">English</a>
            </div>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php"><?php echo $isEnglish ? 'Dashboard' : '仪表板'; ?></a> > 
            <a href="media_manager.php"><?php echo $isEnglish ? 'Media Management' : '媒体管理'; ?></a> > 
            <span><?php echo $isEnglish ? 'Job Positions Management' : '招聘职位管理'; ?></span>
        </div>
        
        <div class="content">       
            <!-- 添加/编辑职位表单 -->
            <div class="form-section">
                <h2><?php echo $editJob ? ($isEnglish ? 'Edit Job Position' : '编辑职位') : ($isEnglish ? 'Add New Job Position' : '添加新职位'); ?></h2>
                <form method="post">
                    <input type="hidden" name="action" value="<?php echo $editJob ? 'edit' : 'add'; ?>">
                    <?php if ($editJob): ?>
                        <input type="hidden" name="job_id" value="<?php echo $editJob['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="job_title"><?php echo $isEnglish ? 'Job Title' : '职位名称'; ?> *</label>
                            <input type="text" id="job_title" name="job_title" 
                                   value="<?php echo $editJob ? htmlspecialchars($editJob['job_title']) : ''; ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="job_count"><?php echo $isEnglish ? 'Recruitment Count' : '招聘人数'; ?> *</label>
                            <input type="text" id="job_count" name="job_count" 
                                   value="<?php echo $editJob ? htmlspecialchars($editJob['recruitment_count']) : ''; ?>" 
                                   placeholder="<?php echo $isEnglish ? 'e.g.: 1 person' : '例如：1人'; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="job_experience"><?php echo $isEnglish ? 'Work Experience Required' : '工作经验要求'; ?> *</label>
                            <input type="text" id="job_experience" name="job_experience" 
                                   value="<?php echo $editJob ? htmlspecialchars($editJob['work_experience']) : ''; ?>" 
                                   placeholder="<?php echo $isEnglish ? 'e.g.: 3' : '例如：3'; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="publish_date"><?php echo $isEnglish ? 'Publish Date' : '发布日期'; ?> *</label>
                            <input type="date" id="publish_date" name="publish_date" 
                                   value="<?php echo $editJob ? $editJob['publish_date'] : date('Y-m-d'); ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="job_category"><?php echo $isEnglish ? 'Company Category' : '公司分类'; ?> *</label>
                            <select id="job_category" name="job_category" required onchange="toggleDepartmentField()">
                                <option value=""><?php echo $isEnglish ? 'Please select company' : '请选择公司'; ?></option>
                                <option value="KUNZZ HOLDINGS" <?php echo ($editJob && $editJob['company_category'] === 'KUNZZ HOLDINGS') ? 'selected' : ''; ?>>KUNZZ HOLDINGS</option>
                                <option value="TOKYO JAPANESE CUISINE" <?php echo ($editJob && $editJob['company_category'] === 'TOKYO JAPANESE CUISINE') ? 'selected' : ''; ?>>TOKYO JAPANESE CUISINE</option>
                                <option value="TOKYO IZAKAYA" <?php echo ($editJob && $editJob['company_category'] === 'TOKYO IZAKAYA') ? 'selected' : ''; ?>>TOKYO IZAKAYA</option>
                            </select>
                        </div>
                        
                        <div class="form-group" id="department-group" style="display: none;">
                            <label for="company_department"><?php echo $isEnglish ? 'Department' : '部门'; ?> *</label>
                            <select id="company_department" name="company_department">
                                <option value=""><?php echo $isEnglish ? 'Please select department' : '请选择部门'; ?></option>
                                <option value="<?php echo $isEnglish ? 'Front Desk' : '前台'; ?>" <?php echo ($editJob && $editJob['company_department'] === ($isEnglish ? 'Front Desk' : '前台')) ? 'selected' : ''; ?>><?php echo $isEnglish ? 'Front Desk' : '前台'; ?></option>
                                <option value="<?php echo $isEnglish ? 'Kitchen' : '厨房'; ?>" <?php echo ($editJob && $editJob['company_department'] === ($isEnglish ? 'Kitchen' : '厨房')) ? 'selected' : ''; ?>><?php echo $isEnglish ? 'Kitchen' : '厨房'; ?></option>
                                <option value="sushi bar" <?php echo ($editJob && $editJob['company_department'] === 'sushi bar') ? 'selected' : ''; ?>>sushi bar</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="salary"><?php echo $isEnglish ? 'Salary Range' : '薪资范围'; ?> *</label>
                            <input type="text" id="salary" name="salary" 
                                   value="<?php echo $editJob ? htmlspecialchars($editJob['salary']) : ''; ?>" 
                                   placeholder="<?php echo $isEnglish ? 'e.g.: 3000-5000' : '例如：3000-5000'; ?>" 
                                   pattern="\d+-\d+" 
                                   title="<?php echo $isEnglish ? 'Please enter salary range' : '请输入薪资范围'; ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="company_location"><?php echo $isEnglish ? 'Company Address' : '公司地址'; ?></label>
                            <input type="text" id="company_location" name="company_location" 
                                   value="<?php echo $editJob ? htmlspecialchars($editJob['company_location']) : ''; ?>" 
                                   placeholder="25, Jln Tanjong 3, Taman Desa Cemerlang, 81800 Ulu Tiram, Johor">
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="job_description"><?php echo $isEnglish ? 'Job Description' : '职位详情'; ?> *</label>
                            <textarea id="job_description" name="job_description" 
                                      placeholder="<?php echo $isEnglish ? 'Please enter detailed job description...' : '请输入详细的职位描述...'; ?>" required><?php echo $editJob ? htmlspecialchars($editJob['job_description']) : ''; ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" class="btn">
                            <?php echo $editJob ? ($isEnglish ? 'Update Job Position' : '更新职位') : ($isEnglish ? 'Add Job Position' : '添加职位'); ?>
                        </button>
                        <?php if ($editJob): ?>
                            <a href="joinpage3upload.php?lang=<?php echo $language; ?>" class="btn btn-secondary"><?php echo $isEnglish ? 'Cancel Edit' : '取消编辑'; ?></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <!-- 现有职位列表 -->
            <div class="jobs-list">
                <h2><?php echo $isEnglish ? 'Current Job Positions' : '现有职位列表'; ?> (<?php echo count($jobs); ?>)</h2>
                
                <?php if (empty($jobs)): ?>
                    <p style="text-align: center; color: #999; padding: 40px;"><?php echo $isEnglish ? 'No job positions available' : '暂无职位信息'; ?></p>
                <?php else: ?>
                    <?php foreach ($jobs as $job): ?>
                        <div class="job-item">
                            <div class="job-header-item">
                                <div>
                                    <div class="job-title-item"><?php echo htmlspecialchars($job['job_title']); ?></div>
                                    <div class="job-meta-list">
                                        <span class="job-meta-item-list">👥 <?php echo $isEnglish ? 'Count:' : '人数'; ?> <?php echo htmlspecialchars($job['recruitment_count']); ?></span>
                                        <span class="job-meta-item-list">💼 <?php echo $isEnglish ? 'Experience:' : '经验'; ?> <?php echo htmlspecialchars($job['work_experience']); ?></span>
                                        <span class="job-meta-item-list">📅 <?php echo $isEnglish ? 'Published:' : '发布'; ?> <?php echo $job['publish_date']; ?></span>
                                        <span class="job-meta-item-list">🏷️ <?php echo $isEnglish ? 'Company:' : '公司'; ?> <?php echo htmlspecialchars($job['company_category'] ?? ($isEnglish ? 'Uncategorized' : '未分类')); ?></span>
                                        <?php if (!empty($job['company_department'])): ?>
                                        <span class="job-meta-item-list">🏢 <?php echo $isEnglish ? 'Department:' : '部门'; ?> <?php echo htmlspecialchars($job['company_department']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($job['salary'])): ?>
                                        <span class="job-meta-item-list">💰 <?php echo $isEnglish ? 'Salary:' : '薪资'; ?> <?php echo htmlspecialchars($job['salary']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($job['company_location'])): ?>
                                        <span class="job-meta-item-list">📍 <?php echo $isEnglish ? 'Address:' : '地址'; ?> <?php echo htmlspecialchars($job['company_location']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="job-description-preview">
                                        <strong><?php echo $isEnglish ? 'Job Description:' : '职位详情：'; ?></strong><?php echo htmlspecialchars($job['job_description']); ?>
                                    </div>
                                </div>
                                <div class="job-actions">
                                    <a href="?lang=<?php echo $language; ?>&edit=<?php echo $job['id']; ?>" class="action-btn edit-btn" title="<?php echo $isEnglish ? 'Edit' : '编辑'; ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="post" style="display: inline-block;" 
                                          onsubmit="return confirm('<?php echo $isEnglish ? 'Are you sure you want to delete this job position?' : '确定要删除这个职位吗？'; ?>')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                        <button type="submit" class="action-btn delete-btn" title="<?php echo $isEnglish ? 'Delete' : '删除'; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 通知容器 -->
    <div class="toast-container" id="toast-container">
        <!-- 动态通知内容 -->
    </div>
</body>
</html>