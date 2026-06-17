<?php
$root = dirname(__DIR__);
$pattern = '/require(?:_once)?\s+.*[\'"](?:\.\.\/|\/)*config\.php[\'"]/';
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
$missing = [];
foreach ($it as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }
    $path = $file->getPathname();
    if (str_contains($path, 'vendor') || str_contains($path, 'node_modules') || str_contains($path, 'scripts' . DIRECTORY_SEPARATOR)) {
        continue;
    }
    $content = file_get_contents($path);
    if (!str_contains($content, 'get_pdo_connection') && !str_contains($content, 'get_mysqli_connection')) {
        continue;
    }
    if (!preg_match($pattern, $content)) {
        $missing[] = str_replace($root . DIRECTORY_SEPARATOR, '', $path);
    }
}
echo implode(PHP_EOL, $missing) . PHP_EOL;
