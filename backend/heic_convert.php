<?php
/**
 * HEIC/HEIF 转 JPG 工具函数
 * 支持三种转换方式（自动选择可用方式）：
 * 1. PHP Imagick 扩展（Hostinger 共享主机推荐）
 * 2. magick 命令行（VPS/独立服务器）
 * 3. convert 命令行（旧版 ImageMagick）
 */

/**
 * 检查文件是否是 HEIC/HEIF 格式，如果是则转换为 JPG
 * 
 * @param string $filePath   已保存的文件完整路径
 * @param string $extension  文件扩展名（小写）
 * @return array ['path' => 新路径, 'extension' => 新扩展名, 'converted' => bool]
 */
function convertHeicToJpg($filePath, $extension) {
    $heicExtensions = ['heic', 'heif'];
    
    if (!in_array(strtolower($extension), $heicExtensions)) {
        return [
            'path' => $filePath,
            'extension' => $extension,
            'converted' => false
        ];
    }
    
    // 生成新的 JPG 文件路径
    $dir = dirname($filePath);
    $newFileName = uniqid() . '.jpg';
    $newPath = $dir . '/' . $newFileName;
    
    $converted = false;
    $errorMsg = '';
    
    // 方式1: 使用 PHP Imagick 扩展（Hostinger 共享主机通常支持）
    if (!$converted && extension_loaded('imagick')) {
        try {
            $imagick = new Imagick($filePath);
            $imagick->setImageFormat('jpeg');
            $imagick->setImageCompressionQuality(90);
            $imagick->writeImage($newPath);
            $imagick->destroy();
            $converted = true;
        } catch (Exception $e) {
            $errorMsg .= "Imagick 失败: " . $e->getMessage() . "; ";
        }
    }
    
    // 方式2: 使用 magick 命令行（ImageMagick 7+）
    if (!$converted && function_exists('exec')) {
        $escapedInput = escapeshellarg($filePath);
        $escapedOutput = escapeshellarg($newPath);
        @exec("magick {$escapedInput} {$escapedOutput} 2>&1", $output, $returnCode);
        if ($returnCode === 0 && file_exists($newPath)) {
            $converted = true;
        } else {
            $errorMsg .= "magick 失败: " . implode(" ", $output ?? []) . "; ";
            // 方式3: 使用 convert 命令行（ImageMagick 6.x）
            @exec("convert {$escapedInput} {$escapedOutput} 2>&1", $output2, $returnCode2);
            if ($returnCode2 === 0 && file_exists($newPath)) {
                $converted = true;
            } else {
                $errorMsg .= "convert 失败: " . implode(" ", $output2 ?? []) . "; ";
            }
        }
    }
    
    // 方式4: 使用 GD（PHP 8.1+ 的 GD 可能支持 HEIC）
    if (!$converted && function_exists('imagecreatefromstring')) {
        $imageData = @file_get_contents($filePath);
        if ($imageData !== false) {
            $img = @imagecreatefromstring($imageData);
            if ($img !== false) {
                imagejpeg($img, $newPath, 90);
                imagedestroy($img);
                if (file_exists($newPath) && filesize($newPath) > 0) {
                    $converted = true;
                }
            }
        }
    }
    
    if ($converted && file_exists($newPath)) {
        // 转换成功，删除原始 HEIC 文件
        @unlink($filePath);
        @chmod($newPath, 0644);
        
        return [
            'path' => $newPath,
            'extension' => 'jpg',
            'filename' => $newFileName,
            'converted' => true
        ];
    }
    
    // 所有方式都失败 - 记录错误但不中断，保留原始文件
    error_log("HEIC 转换失败 [{$filePath}]: " . $errorMsg);
    return [
        'path' => $filePath,
        'extension' => $extension,
        'converted' => false,
        'error' => $errorMsg
    ];
}

/**
 * 检查 HEIC MIME 类型（兼容不同服务器环境）
 * 某些服务器的 finfo 可能不识别 HEIC，返回 application/octet-stream
 */
function isHeicMimeType($mimeType, $extension) {
    $heicMimes = ['image/heic', 'image/heif', 'image/heic-sequence', 'image/heif-sequence'];
    $heicExts = ['heic', 'heif'];
    
    // 直接匹配 MIME
    if (in_array(strtolower($mimeType), $heicMimes)) {
        return true;
    }
    
    // 某些服务器无法识别 HEIC MIME，通过扩展名辅助判断
    if (in_array(strtolower($extension), $heicExts) && 
        in_array($mimeType, ['application/octet-stream', 'application/x-empty', ''])) {
        return true;
    }
    
    return false;
}
