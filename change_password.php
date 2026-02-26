<?php
session_start();

// 1. 验证登录状态
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// 数据库连接配置
$host = 'localhost';
$dbname = 'u690174784_kunzz';
$dbuser = 'u690174784_kunzz';
$dbpass = 'Kunzz1688';

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

$userId = $_SESSION['user_id'];
$message = "";
$success = false;

// 2. 处理 POST 密码修改请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($newPassword) || strlen($newPassword) < 6) {
        $message = "新密码长度至少为 6 位";
    } elseif ($newPassword !== $confirmPassword) {
        $message = "两次输入的密码不一致";
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // 更新密码并清除首次登录标记
        $stmt = $conn->prepare("UPDATE users SET password = ?, is_first_login = 0 WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $userId);
        
        if ($stmt->execute()) {
            $_SESSION['is_first_login'] = 0;
            $success = true;
            $message = "密码修改成功！正在为您自动跳转...";
        } else {
            $message = "密码修改失败，请重试";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>强制修改密码 - KUNZZ GROUP</title>
    <style>
        :root {
            --primary-color: #ff5c00;
            --bg-color: #0d0d0d;
            --card-bg: #1a1a1a;
            --text-color: #ffffff;
            --border-color: #333333;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }

        .container {
            background: var(--card-bg);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 400px;
            border: 1px solid var(--border-color);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: var(--primary-color);
            margin: 0;
            font-size: 24px;
            letter-spacing: 2px;
        }

        h2 {
            font-size: 18px;
            margin-bottom: 20px;
            font-weight: 500;
            text-align: center;
        }

        .description {
            font-size: 14px;
            color: #888;
            margin-bottom: 25px;
            text-align: center;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            color: #aaa;
        }

        input {
            width: 100%;
            padding: 12px;
            background: #222;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: white;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.3s;
        }

        input:focus {
            border-color: var(--primary-color);
        }

        button {
            width: 100%;
            padding: 14px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, background 0.3s;
            margin-top: 10px;
        }

        button:hover {
            background: #e65200;
            transform: translateY(-2px);
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }

        .alert-error {
            background: rgba(217, 48, 37, 0.1);
            color: #d93025;
            border: 1px solid rgba(217, 48, 37, 0.2);
        }

        .alert-success {
            background: rgba(15, 157, 88, 0.1);
            color: #0f9d58;
            border: 1px solid rgba(15, 157, 88, 0.2);
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>KUNZZ GROUP</h1>
        </div>
        
        <h2>强制修改初始密码</h2>
        <p class="description">为了您的账户安全，首次登录必须修改由系统生成的初始密码。</p>

        <?php if ($message): ?>
            <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>">
                <?php echo $message; ?>
            </div>
            <?php if ($success): ?>
                <script>
                    setTimeout(function() {
                        window.location.href = 'backend/dashboard.php';
                    }, 2000);
                </script>
            <?php endif; ?>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="new_password">新密码</label>
                <input type="password" id="new_password" name="new_password" required placeholder="请输入新密码">
            </div>
            <div class="form-group">
                <label for="confirm_password">确认新密码</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="请再次输入新密码">
            </div>
            <button type="submit" <?php echo $success ? 'disabled' : ''; ?>>提交并激活账户</button>
        </form>

        <div class="footer">
            &copy; <?php echo date('Y'); ?> Kunzz Group. All rights reserved.
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
