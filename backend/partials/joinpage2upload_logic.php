<?php

function joinpage2upload_getReturnTo() {
    if (isset($_POST['return_to'])) {
        return (string)$_POST['return_to'];
    }
    if (isset($_GET['return_to'])) {
        return (string)$_GET['return_to'];
    }

    return '';
}

function joinpage2upload_redirectAfterAction($query = '') {
    $returnTo = joinpage2upload_getReturnTo();
    if ($returnTo === 'v2') {
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/backend')), '/');
        header('Location: ' . $base . '/joinpage2upload-v2' . $query);
        exit();
    }

    $self = basename($_SERVER['SCRIPT_NAME'] ?? 'joinpage2upload.php');
    header('Location: ' . $self . $query);
    exit();
}

function joinpage2upload_getSubdomainPhysicalPath() {
    return '/home/u857194726/domains/media.kunzzgroup.com/public_html/comphotos/';
}

function joinpage2upload_loadConfig() {
    $configFile = __DIR__ . '/../../media_config.json';
    if (!file_exists($configFile)) {
        return [];
    }

    return json_decode(file_get_contents($configFile), true) ?: [];
}

function joinpage2upload_configFilePath() {
    return __DIR__ . '/../../media_config.json';
}

function joinpage2upload_diskPath($filePath) {
    if (!$filePath) {
        return '';
    }

    if (strpos($filePath, '../') === 0) {
        return __DIR__ . '/../../' . substr($filePath, 3);
    }

    if ($filePath[0] === '/') {
        return $filePath;
    }

    return __DIR__ . '/../../' . ltrim($filePath, '/');
}

function joinpage2upload_resolveMediaSrc($filePath) {
    if (!$filePath) {
        return '';
    }

    if (strpos($filePath, 'http') === 0 || $filePath[0] === '/') {
        return $filePath;
    }

    if (strpos($filePath, '../') !== 0) {
        return '../' . $filePath;
    }

    return $filePath;
}

function joinpage2upload_photoExists($config, $photoNumber) {
    $photoKey = 'comphoto_' . $photoNumber;
    if (!isset($config[$photoKey])) {
        return false;
    }

    $filePath = $config[$photoKey]['file'] ?? '';
    $subdomainPath = joinpage2upload_getSubdomainPhysicalPath() . basename($filePath);

    if ($subdomainPath && file_exists($subdomainPath)) {
        return true;
    }

    $diskPath = joinpage2upload_diskPath($filePath);
    return $diskPath !== '' && file_exists($diskPath);
}

function joinpage2upload_getDisplayUrl($config, $photoNumber) {
    $photoKey = 'comphoto_' . $photoNumber;
    if (!isset($config[$photoKey]) || !joinpage2upload_photoExists($config, $photoNumber)) {
        return '';
    }

    if (!empty($config[$photoKey]['url'])) {
        return $config[$photoKey]['url'];
    }

    return joinpage2upload_resolveMediaSrc($config[$photoKey]['file'] ?? '');
}

function joinpage2upload_countUploaded($config, $photoCount = 30) {
    $uploadedCount = 0;
    for ($i = 1; $i <= $photoCount; $i++) {
        if (joinpage2upload_photoExists($config, $i)) {
            $uploadedCount++;
        }
    }

    return $uploadedCount;
}

function joinpage2upload_isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function joinpage2upload_jsonResponse($payload) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function joinpage2upload_handleDelete() {
    $photoNumber = intval($_POST['photo_number'] ?? 0);
    $configFile = joinpage2upload_configFilePath();
    $subdomainPhysicalPath = joinpage2upload_getSubdomainPhysicalPath();
    $config = joinpage2upload_loadConfig();
    $photoKey = 'comphoto_' . $photoNumber;

    if (!isset($config[$photoKey])) {
        joinpage2upload_jsonResponse(['ok' => false, 'msg' => "找不到照片 #{$photoNumber} 的记录！"]);
    }

    $filePath = $config[$photoKey]['file'] ?? '';
    $diskPath = joinpage2upload_diskPath($filePath);
    if ($diskPath && file_exists($diskPath)) {
        unlink($diskPath);
    }

    $subdomainFile = $subdomainPhysicalPath . basename($filePath);
    if ($subdomainFile && file_exists($subdomainFile)) {
        unlink($subdomainFile);
    }

    unset($config[$photoKey]);
    file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    joinpage2upload_jsonResponse(['ok' => true, 'msg' => "照片 #{$photoNumber} 已成功删除！"]);
}

function joinpage2upload_handlePost(&$success, &$error) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        joinpage2upload_handleDelete();
    }

    if (!isset($_FILES['media_file'])) {
        return;
    }

    require_once __DIR__ . '/../heic_convert.php';

    $uploadDir = __DIR__ . '/../../images/images/';
    $configFile = joinpage2upload_configFilePath();
    $allowedImage = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/heic', 'image/heif'];

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        $error = '无法创建上传目录：' . $uploadDir . ' (请检查服务器路径配置)';
    } elseif (!is_writable($uploadDir)) {
        $error = '上传目录不可写：' . $uploadDir . ' (请检查文件夹权限)';
    } else {
        $file = $_FILES['media_file'];
        $photoNumber = intval($_POST['photo_number'] ?? 0);
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            $error = '请先选择要上传的照片！';
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $error = '文件上传错误，错误代码：' . $file['error'];
        } elseif ($file['size'] > 10 * 1024 * 1024) {
            $error = '文件大小超过10MB限制！';
        } elseif (!in_array($fileExtension, $allowedImage, true)) {
            $error = '只支持图片格式（JPG, PNG, WebP）！';
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedMimes, true) && !isHeicMimeType($mimeType, $fileExtension)) {
                $error = '文件MIME类型验证失败！';
            } else {
                $newFileName = $photoNumber . '.' . $fileExtension;
                $targetPath = $uploadDir . $newFileName;
                $webPath = '../images/images/' . $newFileName;

                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $converted = convertHeicToJpg($targetPath, $fileExtension);
                    if ($converted['converted']) {
                        $targetPath = $converted['path'];
                        $newFileName = basename($converted['path']);
                        $webPath = '../images/images/' . $newFileName;
                    }

                    chmod($targetPath, 0644);

                    $config = joinpage2upload_loadConfig();
                    $photoKey = 'comphoto_' . $photoNumber;
                    $config[$photoKey] = [
                        'file' => $webPath,
                        'type' => 'image',
                        'updated' => date('Y-m-d H:i:s'),
                    ];

                    file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                    $displayUrl = joinpage2upload_getDisplayUrl($config, $photoNumber);
                    if ($displayUrl === '') {
                        $displayUrl = joinpage2upload_resolveMediaSrc($webPath);
                    }

                    if (joinpage2upload_isAjaxRequest()) {
                        joinpage2upload_jsonResponse([
                            'ok' => true,
                            'msg' => "照片 #{$photoNumber} 上传成功！",
                            'url' => $displayUrl,
                            'updated' => date('Y-m-d H:i:s'),
                            'photo' => $photoNumber,
                        ]);
                    }

                    $success = "照片 #{$photoNumber} 上传成功！";
                    joinpage2upload_redirectAfterAction('?success=1&photo=' . $photoNumber . '&t=' . time());
                } elseif (joinpage2upload_isAjaxRequest()) {
                    joinpage2upload_jsonResponse(['ok' => false, 'msg' => '照片上传失败！']);
                } else {
                    $error = '照片上传失败！无法移动文件到：' . $targetPath;
                }
            }
        }
    }

    if ($error && joinpage2upload_isAjaxRequest()) {
        joinpage2upload_jsonResponse(['ok' => false, 'msg' => $error]);
    }
}
