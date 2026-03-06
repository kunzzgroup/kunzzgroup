<?php
require_once __DIR__ . '/xss_protect.php';

header("Content-Type: text/html; charset=UTF-8");

echo "<h2>XSS 防护机制测试页面</h2>";
echo "<hr>";

// 1. 测试递归清理函数 (这也正是 get_safe_json_input 使用的核心逻辑)
echo "<h3>1. 模拟 JSON/API 注入测试</h3>";
$malicious_json_string = '{
    "username": "<script>alert(\'XSS Attack!\')</script>",
    "nested_data": {
        "bio": "Hello <b onmouseover=\'alert(1)\'>Admin</b>",
        "link": "javascript:alert(2)"
    }
}';

$decoded_array = json_decode($malicious_json_string, true);
// 模拟 get_safe_json_input() 的内部处理流程
$sanitized_array = sanitize_input_recursive($decoded_array);

echo "<strong>原始恶意 JSON 字符串：</strong><br>";
echo "<pre style='background:#fee; padding:10px;'>" . htmlspecialchars($malicious_json_string) . "</pre>";

echo "<strong>经过 sanitize_input_recursive() 清理后的安全 PHP 数组：</strong><br>";
echo "<p style='color:green;'>注意：&lt; 和 &gt; 等符号已经被转换成了安全的 HTML 实体，不会被浏览器当做代码执行。</p>";
echo "<pre style='background:#efe; padding:10px;'>";
print_r($sanitized_array);
echo "</pre>";


// 2. 测试全局 $_GET 注入
echo "<hr>";
echo "<h3>2. 测试全局 \$_GET / \$_POST 拦截</h3>";
echo "<p>尝试在 URL 后面加上测试参数： <code>?test=&lt;script&gt;alert('GET XSS')&lt;/script&gt;</code></p>";

if (isset($_GET['test'])) {
    echo "<strong>接收到的 \$_GET['test'] 内容（由于 xss_protect.php 已经在文件顶部拦截，这里打印出来是安全的）：</strong><br>";
    echo "<pre style='background:#efe; padding:10px;'>" . $_GET['test'] . "</pre>";
    
    // 我们可以强行打印，看看浏览器会不会弹窗。
    // 如果没有被防御，下面这行就会触发弹窗；如果被防御了，就会直接显示文本。
    echo "<p>原始输出测试（不应弹窗）： " . $_GET['test'] . "</p>";
} else {
    echo "<p style='color:gray;'>当前 URL 没有 ?test=... 参数</p>";
}

// 3. 测试 SQL 注入过滤
echo "<hr>";
echo "<h3>3. 测试 SQL 注入 (SQLi) 拦截</h3>";
$malicious_sql_json = '{
    "username": "admin\' UNION SELECT password FROM users --",
    "details": {
        "action": "DROP TABLE users;",
        "safe_text": "hello world"
    }
}';
$decoded_sql_array = json_decode($malicious_sql_json, true);
$sanitized_sql_array = sanitize_sql_injection(sanitize_input_recursive($decoded_sql_array));

echo "<strong>原始恶意 SQL 注入数据：</strong><br>";
echo "<pre style='background:#fee; padding:10px;'>" . htmlspecialchars($malicious_sql_json) . "</pre>";

echo "<strong>经过 xss_protect.php 双重过滤 (XSS + SQLi) 后的安全数据：</strong><br>";
echo "<p style='color:green;'>注意：危险的 SQL 关键字 (如 UNION SELECT, DROP TABLE) 已被替换为 [SQL_BLOCKED]！</p>";
echo "<pre style='background:#efe; padding:10px;'>";
print_r($sanitized_sql_array);
echo "</pre>";

echo "<h3>3.1 URL 参数 SQL 注入测试</h3>";
echo "<p>尝试在 URL 后面加上： <code>?id=1 UNION SELECT * FROM admin</code></p>";

if (isset($_GET['id'])) {
    echo "<strong>接收到的 \$_GET['id'] 内容：</strong><br>";
    echo "<pre style='background:#efe; padding:10px;'>" . $_GET['id'] . "</pre>";
} else {
    echo "<p style='color:gray;'>当前 URL 没有 ?id=... 参数</p>";
}

?>
