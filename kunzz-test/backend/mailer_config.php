<?php
/**
 * ── SMTP 邮件配置 ──
 * 使用 Gmail App Password（应用密码）
 *
 * 获取 App Password 步骤：
 * 1. 登录 Google 账号 → myaccount.google.com
 * 2. 安全性 → 两步验证（必须开启）
 * 3. 应用密码 → 选择"邮件" + "其他设备" → 生成
 * 4. 将 16 位密码填入下方 SMTP_PASS
 */

define('SMTP_HOST',     'smtp.gmail.com');
define('SMTP_PORT',     587);
define('SMTP_SECURE',   'tls');
define('SMTP_USER',     'kunzzsup@gmail.com');
define('SMTP_PASS',     'pobc jkvr yygb dhyk');
define('SMTP_FROM',     'kunzzsup@gmail.com');
define('SMTP_FROM_NAME','Kunzz Group');

// Autoload 路径（相对于此文件）
define('VENDOR_AUTOLOAD', __DIR__ . '/../vendor/autoload.php');
