<?php

function bgmusicupload_formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);

    return round($bytes, 2) . ' ' . $units[$pow];
}

function bgmusicupload_getAudioInfo($filePath) {
    if (!file_exists($filePath)) {
        return null;
    }

    $info = [];
    $info['size'] = filesize($filePath);
    $info['size_formatted'] = bgmusicupload_formatFileSize($info['size']);
    $info['modified'] = date('Y-m-d H:i:s', filemtime($filePath));

    return $info;
}

function bgmusicupload_loadConfig() {
    $configFile = __DIR__ . '/../../music_config.json';
    if (!file_exists($configFile)) {
        return [];
    }

    return json_decode(file_get_contents($configFile), true) ?: [];
}

function bgmusicupload_getReturnTo() {
    if (isset($_POST['return_to'])) {
        return (string)$_POST['return_to'];
    }
    if (isset($_GET['return_to'])) {
        return (string)$_GET['return_to'];
    }

    return '';
}

function bgmusicupload_redirectAfterAction($query = '') {
    $returnTo = bgmusicupload_getReturnTo();
    if ($returnTo === 'v2') {
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/backend')), '/');
        header('Location: ' . $base . '/bgmusicupload-v2' . $query);
        exit();
    }

    $self = basename($_SERVER['SCRIPT_NAME'] ?? 'bgmusicupload.php');
    header('Location: ' . $self . $query);
    exit();
}

function bgmusicupload_handlePost(&$success, &$error) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $uploadDir = __DIR__ . '/../../audio/audio/';
    $configFile = __DIR__ . '/../../music_config.json';

    if (isset($_FILES['music_file'])) {
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $file = $_FILES['music_file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = '文件上传失败，错误代码：' . $file['error'];
            return;
        }

        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedAudio = ['mp3', 'wav', 'ogg', 'm4a'];

        if (!in_array($fileExtension, $allowedAudio, true)) {
            $error = '不支持的文件类型！请上传 MP3、WAV、OGG 或 M4A 格式的音频文件。';
            return;
        }

        $oldConfig = [];
        if (file_exists($configFile)) {
            $oldConfig = json_decode(file_get_contents($configFile), true) ?: [];
        }

        foreach (['mp3', 'wav', 'ogg', 'm4a'] as $ext) {
            $oldFile = $uploadDir . 'music.' . $ext;
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        if (isset($oldConfig['background_music']['file']) && file_exists($oldConfig['background_music']['file'])) {
            unlink($oldConfig['background_music']['file']);
        }

        $newFileName = 'music.' . $fileExtension;
        $targetPath = $uploadDir . $newFileName;
        $webPath = 'audio/audio/' . $newFileName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $error = '文件移动失败！请检查目录权限。';
            return;
        }

        $config = file_exists($configFile)
            ? (json_decode(file_get_contents($configFile), true) ?: [])
            : [];

        $config['background_music'] = [
            'file' => $webPath,
            'type' => 'audio',
            'format' => $fileExtension,
            'updated' => date('Y-m-d H:i:s'),
            'filesize' => filesize($targetPath),
            'original_name' => $file['name'],
        ];

        file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        bgmusicupload_redirectAfterAction('?success=1&t=' . time());
    }

    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        if (!file_exists($configFile)) {
            $error = '文件不存在！';
            return;
        }

        $config = json_decode(file_get_contents($configFile), true) ?: [];
        $storedFile = $config['background_music']['file'] ?? '';
        $diskPath = $storedFile;

        if ($storedFile && !file_exists($diskPath) && strpos($storedFile, '../') === 0) {
            $diskPath = __DIR__ . '/../../' . substr($storedFile, 3);
        }

        if ($diskPath && file_exists($diskPath)) {
            if (unlink($diskPath)) {
                unset($config['background_music']);
                file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                if (bgmusicupload_getReturnTo() === 'v2') {
                    bgmusicupload_redirectAfterAction('?deleted=1&t=' . time());
                }
                $success = '音乐文件已删除！';
                return;
            }

            $error = '删除文件时出错！';
            return;
        }

        $error = '文件不存在！';
    }
}

function bgmusicupload_resolveAudioSrc($webPath) {
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

function bgmusicupload_audioVersion($webPath) {
    $diskPath = $webPath;
    if (strpos($webPath, '../') === 0) {
        $diskPath = __DIR__ . '/../../' . substr($webPath, 3);
    }

    if (file_exists($diskPath)) {
        return filemtime($diskPath);
    }

    return time();
}
