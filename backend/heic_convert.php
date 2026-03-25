<?php
/**
 * HEIC/HEIF 转 JPG 工具函数
 * 需要服务器安装: apt install imagemagick libheif1 -y
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
    
    // 使用 ImageMagick 转换
    $escapedInput = escapeshellarg($filePath);
    $escapedOutput = escapeshellarg($newPath);
    exec("magick {$escapedInput} {$escapedOutput} 2>&1", $output, $returnCode);
    
    if ($returnCode === 0 && file_exists($newPath)) {
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
    
    // 转换失败，返回原始文件
    error_log("HEIC 转换失败: " . implode("\n", $output));
    return [
        'path' => $filePath,
        'extension' => $extension,
        'converted' => false,
        'error' => implode("\n", $output)
    ];
}
