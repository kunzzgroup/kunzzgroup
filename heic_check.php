<?php
/**
 * HEIC 支持诊断页面 - 用完请删除
 */
echo "<h2>HEIC Support Check</h2><pre>";

// 1. PHP Imagick
echo "1. PHP Imagick: ";
if (extension_loaded('imagick')) {
    echo "YES\n";
    $formats = Imagick::queryFormats('HEIC');
    echo "   HEIC support: " . (!empty($formats) ? 'YES' : 'NO') . "\n";
} else {
    echo "NO\n";
}

// 2. exec()
echo "\n2. exec(): ";
$disabled = array_map('trim', explode(',', ini_get('disable_functions')));
if (function_exists('exec') && !in_array('exec', $disabled)) {
    echo "YES\n";
    @exec("magick --version 2>&1", $out1, $code1);
    echo "   magick: " . ($code1 === 0 ? "YES " . ($out1[0] ?? '') : "NO") . "\n";
    @exec("convert --version 2>&1", $out2, $code2);
    echo "   convert: " . ($code2 === 0 ? "YES " . ($out2[0] ?? '') : "NO") . "\n";
} else {
    echo "NO (disabled)\n";
}

// 3. GD
echo "\n3. GD: ";
echo extension_loaded('gd') ? "YES" : "NO";
echo "\n";

// 4. PHP version
echo "\n4. PHP: " . phpversion() . "\n";

echo "</pre>";
