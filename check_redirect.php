<?php
$url = "https://kunzzgroup.com/joinus.php";

// 初始化 cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_NOBODY, true); // 只请求头部
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // 不自动跟随跳转
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// 执行请求
curl_exec($ch);

// 获取 HTTP 状态码
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP 状态码: " . $http_code;
?>
