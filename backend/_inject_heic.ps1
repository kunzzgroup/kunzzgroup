# PowerShell script to inject HEIC conversion code into upload handlers
# This handles CRLF line endings properly

$heicConversionCode = @"
            // HEIC/HEIF 自动转换为 JPG
            `$converted = convertHeicToJpg(`$targetPath, `$fileExtension);
            if (`$converted['converted']) {
                `$targetPath = `$converted['path'];
                `$newFileName = `$converted['filename'];
                `$fileExtension = 'jpg';
            }
"@

# Files that need conversion code injected after "move_uploaded_file" line
$files = @(
    "aboutpage1upload.php",
    "homepage1upload.php",
    "joinpage1upload.php",
    "tokyopage1upload.php",
    "media_manager.php"
)

foreach ($file in $files) {
    $path = Join-Path $PSScriptRoot $file
    if (Test-Path $path) {
        $content = Get-Content $path -Raw
        
        # Check if already injected
        if ($content -match "convertHeicToJpg") {
            Write-Host "SKIP: $file (already has HEIC conversion)"
            continue
        }
        
        # Insert after the first "move_uploaded_file" success block
        # Pattern: after "if (move_uploaded_file($file['tmp_name'], $targetPath)) {"
        # We insert before the "// 更新配置文件" comment
        $needle = "            // 更新配置文件`r`n"
        $replacement = "$heicConversionCode`r`n`r`n            // 更新配置文件`r`n"
        
        $newContent = $content -replace [regex]::Escape($needle), $replacement
        
        if ($newContent -ne $content) {
            Set-Content -Path $path -Value $newContent -NoNewline -Encoding UTF8
            Write-Host "DONE: $file"
        } else {
            Write-Host "WARN: $file - pattern not found, trying alternate..."
            # Try without \r\n for LF-only files
            $needle2 = "            // 更新配置文件`n"
            $replacement2 = "$heicConversionCode`n`n            // 更新配置文件`n"
            $newContent = $content -replace [regex]::Escape($needle2), $replacement2
            if ($newContent -ne $content) {
                Set-Content -Path $path -Value $newContent -NoNewline -Encoding UTF8
                Write-Host "DONE: $file (LF mode)"
            } else {
                Write-Host "FAIL: $file - could not find insertion point"
            }
        }
    } else {
        Write-Host "NOT FOUND: $path"
    }
}

# ===== joinpage2upload.php (has MIME validation + different structure) =====
$jp2Path = Join-Path $PSScriptRoot "joinpage2upload.php"
if (Test-Path $jp2Path) {
    $content = Get-Content $jp2Path -Raw
    if ($content -notmatch "convertHeicToJpg") {
        # Add require_once
        $content = $content -replace "require_once __DIR__ \. '/permission_guard\.php';", "require_once __DIR__ . '/permission_guard.php';`r`nrequire_once __DIR__ . '/heic_convert.php';"
        
        # Add heic/heif to allowed extensions
        $content = $content -replace "\['jpg', 'jpeg', 'png', 'webp'\]", "['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif']"
        
        # Add heic/heif to allowed MIME types
        $content = $content -replace "\['image/jpeg', 'image/png', 'image/webp'\]", "['image/jpeg', 'image/png', 'image/webp', 'image/heic', 'image/heif']"
        
        # Add conversion after move_uploaded_file success, before chmod
        $jp2Conversion = @"
                if (move_uploaded_file(`$file['tmp_name'], `$targetPath)) {
                    // HEIC/HEIF 自动转换为 JPG
                    `$converted = convertHeicToJpg(`$targetPath, `$fileExtension);
                    if (`$converted['converted']) {
                        `$targetPath = `$converted['path'];
                        `$newFileName = basename(`$converted['path']);
                        `$fileExtension = 'jpg';
                    }

                    // 设置文件权限
"@
        $content = $content -replace [regex]::Escape("if (move_uploaded_file(`$file['tmp_name'], `$targetPath)) {`r`n                    // 设置文件权限"), $jp2Conversion
        
        # Update hint text
        $content = $content -replace "支持 JPG, PNG, WebP", "支持 JPG, PNG, WebP（HEIC 自动转换）"
        
        Set-Content -Path $jp2Path -Value $content -NoNewline -Encoding UTF8
        Write-Host "DONE: joinpage2upload.php"
    } else {
        Write-Host "SKIP: joinpage2upload.php (already has HEIC conversion)"
    }
}

# ===== tokyopage2upload.php (multi-file upload with loop) =====
$tp2Path = Join-Path $PSScriptRoot "tokyopage2upload.php"
if (Test-Path $tp2Path) {
    $content = Get-Content $tp2Path -Raw
    if ($content -notmatch "convertHeicToJpg") {
        $content = $content -replace "require_once __DIR__ \. '/permission_guard\.php';", "require_once __DIR__ . '/permission_guard.php';`nrequire_once __DIR__ . '/heic_convert.php';"
        $content = $content -replace "\['jpg', 'jpeg', 'png', 'webp'\]", "['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif']"
        
        # Insert conversion after move_uploaded_file in the loop
        $tp2Needle = "if (move_uploaded_file(`$file['tmp_name'], `$targetPath)) {"
        $tp2Replace = @"
if (move_uploaded_file(`$file['tmp_name'], `$targetPath)) {
                    // HEIC/HEIF 自动转换为 JPG
                    `$converted = convertHeicToJpg(`$targetPath, `$fileExtension);
                    if (`$converted['converted']) {
                        `$targetPath = `$converted['path'];
                        `$newFileName = basename(`$converted['path']);
                        `$fileExtension = 'jpg';
                    }
"@
        $content = $content -replace [regex]::Escape($tp2Needle), $tp2Replace
        Set-Content -Path $tp2Path -Value $content -NoNewline -Encoding UTF8
        Write-Host "DONE: tokyopage2upload.php"
    } else {
        Write-Host "SKIP: tokyopage2upload.php"
    }
}

# ===== tokyopage3upload.php (multi-file upload with loop, similar to tp2) =====
$tp3Path = Join-Path $PSScriptRoot "tokyopage3upload.php"
if (Test-Path $tp3Path) {
    $content = Get-Content $tp3Path -Raw
    if ($content -notmatch "convertHeicToJpg") {
        $content = $content -replace "require_once __DIR__ \. '/permission_guard\.php';", "require_once __DIR__ . '/permission_guard.php';`nrequire_once __DIR__ . '/heic_convert.php';"
        $content = $content -replace "\['jpg', 'jpeg', 'png', 'webp'\]", "['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif']"
        $tp3Needle = "if (move_uploaded_file(`$file['tmp_name'], `$targetPath)) {"
        $tp3Replace = @"
if (move_uploaded_file(`$file['tmp_name'], `$targetPath)) {
                    // HEIC/HEIF 自动转换为 JPG
                    `$converted = convertHeicToJpg(`$targetPath, `$fileExtension);
                    if (`$converted['converted']) {
                        `$targetPath = `$converted['path'];
                        `$newFileName = basename(`$converted['path']);
                        `$fileExtension = 'jpg';
                    }
"@
        $content = $content -replace [regex]::Escape($tp3Needle), $tp3Replace
        Set-Content -Path $tp3Path -Value $content -NoNewline -Encoding UTF8
        Write-Host "DONE: tokyopage3upload.php"
    } else {
        Write-Host "SKIP: tokyopage3upload.php"
    }
}

# ===== tokyopage4upload.php =====
$tp4Path = Join-Path $PSScriptRoot "tokyopage4upload.php"
if (Test-Path $tp4Path) {
    $content = Get-Content $tp4Path -Raw
    if ($content -notmatch "convertHeicToJpg") {
        $content = $content -replace "require_once __DIR__ \. '/permission_guard\.php';", "require_once __DIR__ . '/permission_guard.php';`nrequire_once __DIR__ . '/heic_convert.php';"
        $content = $content -replace "\['jpg', 'jpeg', 'png', 'webp'\]", "['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif']"
        $tp4Needle = "if (move_uploaded_file(`$file['tmp_name'], `$targetPath)) {"
        $tp4Replace = @"
if (move_uploaded_file(`$file['tmp_name'], `$targetPath)) {
                    // HEIC/HEIF 自动转换为 JPG
                    `$converted = convertHeicToJpg(`$targetPath, `$fileExtension);
                    if (`$converted['converted']) {
                        `$targetPath = `$converted['path'];
                        `$newFileName = basename(`$converted['path']);
                        `$fileExtension = 'jpg';
                    }
"@
        $content = $content -replace [regex]::Escape($tp4Needle), $tp4Replace
        Set-Content -Path $tp4Path -Value $content -NoNewline -Encoding UTF8
        Write-Host "DONE: tokyopage4upload.php"
    } else {
        Write-Host "SKIP: tokyopage4upload.php"
    }
}

# ===== aboutpage4upload.php (timeline photo upload) =====
$ap4Path = Join-Path $PSScriptRoot "aboutpage4upload.php"
if (Test-Path $ap4Path) {
    $content = Get-Content $ap4Path -Raw
    if ($content -notmatch "convertHeicToJpg") {
        $content = $content -replace "require_once __DIR__ \. '/permission_guard\.php';", "require_once __DIR__ . '/permission_guard.php';`r`nrequire_once __DIR__ . '/heic_convert.php';"
        $content = $content -replace "\['jpg', 'jpeg', 'png', 'webp'\]", "['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif']"
        
        $ap4Needle = "if (move_uploaded_file(`$file['tmp_name'], `$targetPath)) {"
        $ap4Replace = @"
if (move_uploaded_file(`$file['tmp_name'], `$targetPath)) {
                // HEIC/HEIF 自动转换为 JPG
                `$converted = convertHeicToJpg(`$targetPath, `$fileExtension);
                if (`$converted['converted']) {
                    `$targetPath = `$converted['path'];
                    `$newFileName = basename(`$converted['path']);
                    `$fileExtension = 'jpg';
                }
"@
        $content = $content -replace [regex]::Escape($ap4Needle), $ap4Replace
        
        # Update hint text
        $content = $content -replace "支持 JPG, PNG, WebP 格式", "支持 JPG, PNG, WebP 格式（HEIC 自动转换）"
        
        Set-Content -Path $ap4Path -Value $content -NoNewline -Encoding UTF8
        Write-Host "DONE: aboutpage4upload.php"
    } else {
        Write-Host "SKIP: aboutpage4upload.php"
    }
}

# ===== menu_api.php (uploadImage function) =====
$menuPath = Join-Path $PSScriptRoot "menu_api.php"
if (Test-Path $menuPath) {
    $content = Get-Content $menuPath -Raw
    if ($content -notmatch "convertHeicToJpg") {
        $content = $content -replace "require_once __DIR__ \. '/permission_guard\.php';", "require_once __DIR__ . '/permission_guard.php';`nrequire_once __DIR__ . '/heic_convert.php';"
        
        # Add heic/heif MIME types
        $content = $content -replace "\['image/jpeg', 'image/png', 'image/webp', 'image/gif'\]", "['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/heic', 'image/heif']"
        
        # Add conversion after move_uploaded_file in uploadImage function
        $menuNeedle = "return `$menu_type . '/' . `$filename; // Stored path (relative to UPLOAD_BASE)"
        $menuReplace = @"
    // HEIC/HEIF 自动转换为 JPG
    `$converted = convertHeicToJpg(`$dest, `$ext);
    if (`$converted['converted']) {
        `$dest = `$converted['path'];
        `$filename = `$converted['filename'];
    }

    return `$menu_type . '/' . `$filename; // Stored path (relative to UPLOAD_BASE)
"@
        $content = $content -replace [regex]::Escape($menuNeedle), $menuReplace
        Set-Content -Path $menuPath -Value $content -NoNewline -Encoding UTF8
        Write-Host "DONE: menu_api.php"
    } else {
        Write-Host "SKIP: menu_api.php"
    }
}

# ===== edit_menu.php =====
$editMenuPath = Join-Path $PSScriptRoot "edit_menu.php"
if (Test-Path $editMenuPath) {
    $content = Get-Content $editMenuPath -Raw
    if ($content -notmatch "convertHeicToJpg") {
        $content = $content -replace "require_once __DIR__ \. '/permission_guard\.php';", "require_once __DIR__ . '/permission_guard.php';`nrequire_once __DIR__ . '/heic_convert.php';"
        $content = $content -replace "\['jpg','jpeg','png','webp'\]", "['jpg','jpeg','png','webp','heic','heif']"
        
        # Convert after move_uploaded_file
        $emNeedle = "if (move_uploaded_file(`$_FILES['menu_image']['tmp_name'], `$target)) {"
        $emReplace = @"
if (move_uploaded_file(`$_FILES['menu_image']['tmp_name'], `$target)) {
                // HEIC/HEIF 自动转换为 JPG
                `$converted = convertHeicToJpg(`$target, `$ext);
                if (`$converted['converted']) {
                    `$target = `$converted['path'];
                }
"@
        $content = $content -replace [regex]::Escape($emNeedle), $emReplace
        
        # Update hint text (keep original style)
        $content = $content -replace "支持 JPG · PNG · WEBP · 最大 5MB", "支持 JPG · PNG · WEBP · HEIC（自动转换）· 最大 5MB"
        
        Set-Content -Path $editMenuPath -Value $content -NoNewline -Encoding UTF8
        Write-Host "DONE: edit_menu.php"
    } else {
        Write-Host "SKIP: edit_menu.php"
    }
}

# ===== dishware_api.php (uploadPhoto function) =====
$dishwarePath = Join-Path $PSScriptRoot "dishware_api.php"
if (Test-Path $dishwarePath) {
    $content = Get-Content $dishwarePath -Raw
    if ($content -notmatch "convertHeicToJpg") {
        # Add require_once near the top (after first <?php)
        $content = $content -replace "require_once __DIR__ \. '/permission_guard\.php';", "require_once __DIR__ . '/permission_guard.php';`r`nrequire_once __DIR__ . '/heic_convert.php';"
        
        # Update allowed extensions in uploadPhoto
        $content = $content -replace "\['jpg', 'jpeg', 'png', 'gif'\]", "['jpg', 'jpeg', 'png', 'gif', 'heic', 'heif']"
        
        # Add conversion after move_uploaded_file in uploadPhoto
        $dwNeedle = "if (move_uploaded_file(`$_FILES['photo']['tmp_name'], `$file_path)) {"
        $dwReplace = @"
if (move_uploaded_file(`$_FILES['photo']['tmp_name'], `$file_path)) {
        // HEIC/HEIF 自动转换为 JPG
        `$converted = convertHeicToJpg(`$file_path, `$file_extension);
        if (`$converted['converted']) {
            `$file_path = `$converted['path'];
        }
"@
        $content = $content -replace [regex]::Escape($dwNeedle), $dwReplace
        Set-Content -Path $dishwarePath -Value $content -NoNewline -Encoding UTF8
        Write-Host "DONE: dishware_api.php"
    } else {
        Write-Host "SKIP: dishware_api.php"
    }
}

# ===== Update dishware_upload.php and dishware_stock.php hint text =====
foreach ($hintFile in @("dishware_upload.php", "dishware_stock.php")) {
    $hintPath = Join-Path $PSScriptRoot $hintFile
    if (Test-Path $hintPath) {
        $content = Get-Content $hintPath -Raw
        $content = $content -replace "支持 JPG, PNG, GIF 格式", "支持 JPG, PNG, GIF 格式（HEIC 自动转换）"
        Set-Content -Path $hintPath -Value $content -NoNewline -Encoding UTF8
        Write-Host "DONE: $hintFile (hint text)"
    }
}

Write-Host "`nAll HEIC upload support changes applied!"
