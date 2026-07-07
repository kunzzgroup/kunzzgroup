<?php

function homepage1upload_getReturnTo() {
    if (isset($_POST['return_to'])) {
        return (string)$_POST['return_to'];
    }
    if (isset($_GET['return_to'])) {
        return (string)$_GET['return_to'];
    }

    return '';
}

function homepage1upload_redirectAfterAction($query = '') {
    $returnTo = homepage1upload_getReturnTo();
    if ($returnTo === 'v2') {
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/backend')), '/');
        header('Location: ' . $base . '/homepage1upload-v2' . $query);
        exit();
    }

    $self = basename($_SERVER['SCRIPT_NAME'] ?? 'homepage1upload.php');
    header('Location: ' . $self . $query);
    exit();
}

function homepage1upload_loadConfig() {
    $configFile = __DIR__ . '/../../media_config.json';
    if (!file_exists($configFile)) {
        return [];
    }

    return json_decode(file_get_contents($configFile), true) ?: [];
}

function homepage1upload_resolveMediaSrc($webPath) {
    if (!$webPath) {
        return '';
    }

    if (strpos($webPath, 'http') === 0 || $webPath[0] === '/') {
        return $webPath;
    }

    if (strpos($webPath, '../') !== 0) {
        return '../' . $webPath;
    }

    return $webPath;
}

function homepage1upload_mediaVersion($webPath) {
    $diskPath = $webPath;
    if (strpos($webPath, '../') === 0) {
        $diskPath = __DIR__ . '/../../' . substr($webPath, 3);
    }

    if (file_exists($diskPath)) {
        return filemtime($diskPath);
    }

    return time();
}

function homepage1upload_handlePost(&$success, &$error) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['media_file'])) {
        return;
    }

    require_once __DIR__ . '/../heic_convert.php';

    $configFile = __DIR__ . '/../../media_config.json';
    $allowedVideo = ['mp4', 'webm', 'mov', 'avi'];
    $allowedImage = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'];
    $allowedTypes = array_merge($allowedVideo, $allowedImage);

    $file = $_FILES['media_file'];
    $mediaType = $_POST['media_type'] ?? 'home_background';

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = '文件上传失败，错误代码：' . $file['error'];
        return;
    }

    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $isVideo = in_array($fileExtension, $allowedVideo, true);
    $uploadDir = $isVideo ? __DIR__ . '/../../video/video/' : __DIR__ . '/../../images/images/';
    $webDir = $isVideo ? 'video/video/' : 'images/images/';

    if (!in_array($fileExtension, $allowedTypes, true)) {
        $error = '不支持的文件类型！';
        return;
    }

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $newFileName = $mediaType . '.' . $fileExtension;
    $targetPath = $uploadDir . $newFileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        $error = '文件上传失败！';
        return;
    }

    $converted = convertHeicToJpg($targetPath, $fileExtension);
    if ($converted['converted']) {
        $targetPath = $converted['path'];
        $newFileName = $converted['filename'];
        $fileExtension = 'jpg';
        $isVideo = false;
    }

    $config = file_exists($configFile)
        ? (json_decode(file_get_contents($configFile), true) ?: [])
        : [];

    $webPath = $webDir . $newFileName;

    $config[$mediaType] = [
        'file' => $webPath,
        'type' => $isVideo ? 'video' : 'image',
        'updated' => date('Y-m-d H:i:s'),
    ];

    file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    homepage1upload_redirectAfterAction('?success=1&t=' . time());
}
