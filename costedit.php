<?php
// 统一入口：避免误访问到不存在/旧路径的 costedit.php
// 直接跳转到实际页面 backend/costedit.php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Location: backend/costedit.php', true, 302);
exit;


