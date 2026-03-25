<?php
/**
 * HEIC 支持诊断页面 - 用完请删除
 */
echo "<h2>HEIC 支持诊断</h2><pre>";

// 1. PHP Imagick 扩展
echo "1. PHP Imagick 扩展: ";
if (extension_loaded('imagick')) {
    echo "✅ 已安装\n";
    $formats = Imagick::queryFormats('HEIC');
    echo "   HEIC 格式支持: " . (!empty($formats) ? '✅ 支持' : '❌ 不支持') . "\n";
} else {
    echo "❌ 未安装\n";
}

// 2. exec() 可用性
echo "\n2. exec() 函数: ";
if (function_exists('exec') && !in_array('exec', array_map('trim', explode(',', ini_get('disable_functions'))))) {
    echo "✅ 可用\n";
    
    // 检查 magick 命令
    echo "   magick 命令: ";
    @exec("magick --version 2>&1", $out1, $code1);
    echo ($code1 === 0 ? "✅ " . ($out1[0] ?? '') : "❌ 不可用") . "\n";
    
    // 检查 convert 命令
    echo "   convert 命令: ";
    @exec("convert --version 2>&1", $out2, $code2);
    echo ($code2 === 0 ? "✅ " . ($out2[0] ?? '') : "❌ 不可用") . "\n";
} else {
    echo "❌ 被禁用\n";
}

// 3. GD 扩展
echo "\n3. GD 扩展: ";
if (extension_loaded('gd')) {
    echo "✅ 已安装\n";
    $info = gd_info();
    echo "   版本: " . ($info['GD Version'] ?? '未知') . "\n";
} else {
    echo "❌ 未安装\n";
}

// 4. PHP 版本
echo "\n4. PHP 版本: " . phpversion() . "\n";

// 5. 文件上传配置
echo "\n5. 文件上传配置:\n";
echo "   upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "   post_max_size: " . ini_get('post_max_size') . "\n";
echo "   max_file_uploads: " . ini_get('max_file_uploads') . "\n";

// 6. 结论
echo "\n=== 结论 ===\n";
$canConvert = false;
if (extension_loaded('imagick')) {
    $formats = Imagick::queryFormats('HEIC');
    if (!empty($formats)) {
        echo "✅ 可以使用 PHP Imagick 转换 HEIC\n";
        $canConvert = true;
    }
}
if (!$canConvert && function_exists('exec')) {
    @exec("magick --version 2>&1", $o, $c);
    if ($c === 0) {
        echo "✅ 可以使用 magick 命令转换 HEIC\n";
        $canConvert = true;
    } else {
        @exec("convert --version 2>&1", $o2, $c2);
        if ($c2 === 0) {
            echo "✅ 可以使用 convert 命令转换 HEIC\n";
            $canConvert = true;
        }
    }
}
if (!$canConvert) {
    echo "❌ 服务器暂不支持 HEIC 转换！\n";
    echo "   建议: 联系 Hostinger 客服启用 Imagick HEIC 支持，\n";
    echo "   或改用 VPS 并安装 imagemagick + libheif\n";
}

echo "</pre>";
