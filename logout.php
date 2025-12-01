<?php
session_start();
session_unset();
session_destroy();

// 清除 Cookie（包含路径参数以确保生效）
setcookie('user_id', '', time() - 3600, '/');
setcookie('username', '', time() - 3600, '/');

header("Location: index.php");
exit();
?>
