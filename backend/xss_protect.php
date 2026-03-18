<?php
/**
 * 全球统一的 XSS 和安全防护脚本 (Global XSS Security Script)
 * 用法:
 * 在每个受攻击威胁的 PHP 文件顶部引入此脚本：
 * require_once 'xss_protect.php';
 */

// 1. 设置基础的安全 HTTP 响应头
if (!headers_sent()) {
    // 阻止浏览器执行与声明 Content-Type 不符的 MIME 类型 (防止 MIME 欺骗)
    header("X-Content-Type-Options: nosniff");
    // 防止被恶意网站用 iframe 嵌入 (防点击劫持)
    header("X-Frame-Options: SAMEORIGIN");
    // 开启旧版浏览器的 XSS 过滤保护规则
    header("X-XSS-Protection: 1; mode=block");
    
    // 内容安全策略 (CSP) - 如果你的前端没有内联脚本或者内联样式可以使用，否则容易造成功能破坏。
    // 暂时注销 CSP，如果需要极其严格的安全，解开注释：
    // header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' https: data: blob:;");
}

// 2. 提供公共的安全输出函数
if (!function_exists('e')) {
    /**
     * HTML 转义函数 (推荐在输出变量到 HTML 时使用)
     * 代替单纯的 echo $var;
     * 用法: echo e($user_input);
     */
    function e($string) {
        if ($string === null) return '';
        if (is_array($string)) return ''; 
        return htmlspecialchars((string)$string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

// 3. 通用输入过滤递归函数 
if (!function_exists('sanitize_input_recursive')) {
    /**
     * 将输入数据进行深度 XSS 过滤（将特殊字符转换为 HTML 实体）
     */
    function sanitize_input_recursive($data, $keyName = '') {
        if (is_array($data)) {
            $sanitized = [];
            foreach ($data as $key => $value) {
                // 清洗键名
                $clean_key = htmlspecialchars((string)$key, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $sanitized[$clean_key] = sanitize_input_recursive($value, $key);
            }
            return $sanitized;
        } else if (is_string($data)) {
            // 如果是密码字段，跳过 XSS 转义，防止由于 HTML 实体化（如 & -> &amp;）导致验证失败
            if (strtolower($keyName) === 'password') {
                return trim($data);
            }
            // 使用 strip_tags 过滤 HTML 标签是种选择，但 htmlspecialchars 可以完整保留用户意图而不被执行
            return htmlspecialchars(trim($data), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        } else {
            return $data; // 数字、布尔值等直接返回
        }
    }
}

// 4. API 数据过滤函数 (处理 application/json 格式发送的 Payload)
if (!function_exists('get_safe_json_input')) {
    /**
     * 获取经过 XSS 清洗的 JSON 分发参数 (适用于 fetch 等异步请求的 payload)
     * 用法替代原有的: json_decode(file_get_contents('php://input'), true);
     */
    function get_safe_json_input() {
        $rawInput = file_get_contents('php://input');
        if (empty($rawInput)) return [];
        
        $decodedInput = json_decode($rawInput, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedInput)) {
            // 返回深度清理的输入 (同时经过 XSS 和 SQLi 过滤)
            return sanitize_sql_injection(sanitize_input_recursive($decodedInput));
        }
        return [];
    }
}

// 5. 简易 SQL 注入 (SQLi) 关键字过滤器 / WAF
if (!function_exists('sanitize_sql_injection')) {
    /**
     * 检测并拦截常见的 SQL 注入关键字
     * 注意：最佳的防 SQL 注入方式始终是使用 PDO 预处理语句 (Prepared Statements)。
     * 此函数作为额外的防线，防止在未按规范使用预处理的遗留代码中发生注入。
     */
    function sanitize_sql_injection($data, $keyName = '') {
        if (is_array($data)) {
            $sanitized = [];
            foreach ($data as $key => $value) {
                // 不对键名做SQL拦截，仅过滤值
                $sanitized[$key] = sanitize_sql_injection($value, $key);
            }
            return $sanitized;
        } else if (is_string($data)) {
            // 如果是密码字段，直接返回原始值，防止损坏
            if (strtolower($keyName) === 'password') {
                return $data;
            }
            // 常见的 SQL 注入恶意关键字特征正则 (忽略大小写)
            $sql_pattern = '/\b(UNION\s+SELECT|DROP\s+TABLE|ALTER\s+TABLE|INSERT\s+INTO|DELETE\s+FROM|UPDATE\s+.*?\s+SET|EXEC(\s|\+)+(s|x)p_)\b/i';
            
            // 如果检测到高度危险的SQL语法，直接清空或阻断
            if (preg_match($sql_pattern, $data)) {
                return preg_replace($sql_pattern, '[SQL_BLOCKED]', $data);
            }
            
            return $data;
        } else {
            return $data;
        }
    }
}

// 6. [全局激活] 自动拦截 $_GET、$_POST、$_COOKIE 和 $_REQUEST 
//    将其自动转换为安全格式（防患于未然全局防护）。
// 注意: 此操作会使得保存到数据库的字符实体化(如 < 变成 &lt;)。
// 要获取原始未经转义的数据，请在使用前小心设计。
$_GET = sanitize_sql_injection(sanitize_input_recursive($_GET));
$_POST = sanitize_sql_injection(sanitize_input_recursive($_POST));
$_COOKIE = sanitize_sql_injection(sanitize_input_recursive($_COOKIE));
$_REQUEST = sanitize_sql_injection(sanitize_input_recursive($_REQUEST));

// 7. 密码安全处理 (防彩虹表和暴力破解)
if (!function_exists('secure_hash_password')) {
    /**
     * 将明文密码进行安全哈希处理
     * 优先使用目前公认最安全的 Argon2id 算法，如果服务器不支持则回退到高强度的 Bcrypt(cost=12)
     * password_hash 本身会为每次加密自动生成独一无二的随机盐值(Salt)，无惧彩虹表攻击。
     */
    function secure_hash_password($password) {
        $options = [
            'memory_cost' => 65536, // Argon2 memory cost: 64MB
            'time_cost'   => 4,     // Argon2 time cost: 4 iterations
            'threads'     => 1,     // Argon2 threads
            'cost'        => 12     // Bcrypt cost (工作因子升级到12)
        ];
        
        if (defined('PASSWORD_ARGON2ID')) {
            return password_hash($password, PASSWORD_ARGON2ID, $options);
        } else {
            return password_hash($password, PASSWORD_BCRYPT, $options);
        }
    }
}

if (!function_exists('verify_secure_password')) {
    /**
     * 安全地验证哈希密码
     * 自动从密文中提取盐值并与自带的安全体系进行校验匹配
     */
    function verify_secure_password($password, $hashed_password) {
        return password_verify($password, $hashed_password);
    }
}

?>
