<?php
$host = 'localhost';

if (file_exists(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
} else {
    $httpHost = $_SERVER['HTTP_HOST'] ?? '';
    $isProduction = str_contains($httpHost, 'kunzzgroup.com');

    if ($isProduction) {
        $dbname = 'u690174784_kunzz';
        $dbuser = 'u690174784_kunzz';
        $dbpass = 'Kunzz1688';
    } else {
        $dbname = 'kunzz';
        $dbuser = 'root';
        $dbpass = '';
    }
}

if (!function_exists('get_mysqli_connection')) {
    function get_mysqli_connection(): mysqli
    {
        global $host, $dbname, $dbuser, $dbpass;

        $conn = new mysqli($host, $dbuser, $dbpass, $dbname);
        if ($conn->connect_error) {
            throw new RuntimeException('Database connection failed: ' . $conn->connect_error);
        }

        $conn->set_charset('utf8mb4');
        return $conn;
    }
}

$pdo = null;
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4;connect_timeout=5",
        $dbuser,
        $dbpass,
        [PDO::ATTR_TIMEOUT => 5]
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET time_zone = '+08:00'");
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    $pdo = null;
}
