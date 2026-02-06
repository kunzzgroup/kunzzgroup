<?php
// 包含会话验证
require_once '../system/session_check.php';

// 防止浏览器缓存旧版 JS/HTML
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

// 包含HTML模板
include '../templates/stocklist.html';
?>
