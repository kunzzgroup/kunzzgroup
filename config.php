<?php
$host = 'localhost';
$httpHost = $_SERVER['HTTP_HOST'] ?? '';
$isProduction = str_contains($httpHost, 'kunzzgroup.com');

// config.local.php is for local dev only — never load it on production.
if ($isProduction) {
    $dbname = 'u690174784_kunzz';
    $dbuser = 'u690174784_kunzz';
    $dbpass = 'Kunzz1688';
} elseif (is_readable(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
} else {
    $dbname = 'kunzz';
    $dbuser = 'root';
    $dbpass = '';
}

if (!function_exists('app_url')) {
    function app_url(string $path = ''): string
    {
        static $basePath = null;

        if ($basePath === null) {
            global $appBasePath;

            if (!empty($appBasePath)) {
                $basePath = '/' . trim((string) $appBasePath, '/');
            } else {
                $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
                $projectRoot = rtrim(str_replace('\\', '/', realpath(__DIR__) ?: __DIR__), '/');
                $basePath = '';

                if ($docRoot !== '' && str_starts_with($projectRoot, $docRoot)) {
                    $basePath = rtrim(substr($projectRoot, strlen($docRoot)), '/');
                } elseif ($docRoot !== '' && !empty($_SERVER['SCRIPT_NAME'])) {
                    $script = str_replace('\\', '/', (string) $_SERVER['SCRIPT_NAME']);
                    if (preg_match('#^/([^/]+)/#', $script, $matches)) {
                        $candidate = '/' . $matches[1];
                        if (is_file($docRoot . $candidate . '/config.php')) {
                            $basePath = $candidate;
                        }
                    }
                }
            }
        }

        $path = ltrim($path, '/');
        if ($path === '') {
            return $basePath === '' ? '/' : $basePath . '/';
        }

        return ($basePath === '' ? '' : $basePath) . '/' . $path;
    }
}

if (!function_exists('is_production_host')) {
    function is_production_host(): bool
    {
        $httpHost = $_SERVER['HTTP_HOST'] ?? '';
        return str_contains($httpHost, 'kunzzgroup.com');
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

if (!function_exists('get_pdo_connection')) {
    function get_pdo_connection(): PDO
    {
        global $pdo, $host, $dbname, $dbuser, $dbpass;

        if ($pdo instanceof PDO) {
            return $pdo;
        }

        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4;connect_timeout=5",
            $dbuser,
            $dbpass,
            [PDO::ATTR_TIMEOUT => 5]
        );

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("SET time_zone = '+08:00'");

        return $pdo;
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
