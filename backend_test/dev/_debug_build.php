<?php
// 用于排查：浏览器当前访问的是否为本工作区部署出来的 PHP 文件（以及是否被 OPcache/代理缓存）
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$buildId = '2025-12-18_02';
$file = __FILE__;
$mtime = @filemtime(__FILE__);

$opcache = null;
if (function_exists('opcache_get_status')) {
    try {
        $status = @opcache_get_status(false);
        $opcache = [
            'enabled' => (bool)($status['opcache_enabled'] ?? false),
            'jit' => $status['jit'] ?? null,
        ];
    } catch (Throwable $e) {
        $opcache = ['error' => $e->getMessage()];
    }
}

echo json_encode([
    'ok' => true,
    'build_id' => $buildId,
    'file' => $file,
    'file_mtime' => $mtime ? date('c', $mtime) : null,
    'php_sapi' => PHP_SAPI,
    'opcache' => $opcache,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);


