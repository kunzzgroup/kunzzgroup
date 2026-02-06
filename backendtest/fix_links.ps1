# fix_links.ps1
# PowerShell script to update HTML/JS links and image paths

# 1. Mappings (Same as refactor)
$mapping = @{
    "core"      = @(
        "check_permissions.php", "check_restaurant_setup.php", "get_fontkit.php", "logout.php", "session_check.php", "sidebar.php"
    )
    "api"       = @(
        "costapi.php", "dishware_api.php", "evaluation_form_api.php", "generatecodeapi.php", "get_jobs_api.php",
        "j1stockeditapi.php", "j1stockeditpageapi.php", "j1stocklistapi.php", "j2stockeditapi.php", "j2stockeditpageapi.php",
        "j2stocklistapi.php", "j3stockeditpageapi.php", "j3stocklistapi.php", "job_positions_api.php", "kpiapi.php",
        "menucost_api.php", "menucostdata_api.php", "price_api.php", "qnaapi.php", "schedule_api.php",
        "stockapi.php", "stockeditapi.php", "stocklistapi.php", "stockminimumapi.php", "stockremarkapi.php",
        "stocksotapi.php", "supply_api.php"
    )
    "cms"       = @(
        "aboutpage1upload.php", "aboutpage4upload.php", "bgmusicupload.php", "dishware_upload.php", "homepage1upload.php",
        "joinpage1upload.php", "joinpage2upload.php", "joinpage3upload.php", "media_manager.php", "phone_manage.php",
        "qna.php", "tokyopage1upload.php", "tokyopage5upload.php"
    )
    "modules"   = @(
        "centerstockproductname.php", "corporate_blueprint.php", "corporate_blueprint_edit.php", "cost.php", "costedit.php",
        "dishware_index.php", "dishware_stock.php", "evaluation_form.php", "export_branch_stock_excel.php",
        "j1stocklist.php", "j1stockproductname.php", "j2stocklist.php", "j2stockproductname.php", "j3stockinoutpage.php",
        "j3stocklist.php", "j3stockproductname.php", "kpi.php", "kpiedit.php", "menucost.php", "menucostdata.php",
        "price.php", "schedule_manager.php", "stockedit.php", "stockedit_j3.php", "stockeditall.php", "stocklist.php",
        "stocklistall.php", "stockminimum.php", "stockproductname.php", "stockremark.php", "stocksot.php", "supply.php"
    )
    "pages"     = @(
        "dashboard.php"
    )
    "migration" = @(
        "generatecode.php", "migrate_stocksot_to_inout.php" 
    )
    "dev"       = @(
        "_debug_build.php", "corporate_blueprint_orgchart_example.php", "diagnose_cost_tables.php", "test_cost_update.php",
        "test_dishware_break_system.php", "test_dishware_system.php", "upload_sample_photos.php"
    )
}

# Build map: lowercase filename -> folder (for case insensitivity safety)
$fileLocations = @{}
foreach ($folder in $mapping.Keys) {
    foreach ($file in $mapping[$folder]) {
        $fileLocations[$file.ToLower()] = $folder
    }
}

# 2. Get All PHP Files recursively
$filesToUpdate = Get-ChildItem -Path . -Filter "*.php" -Recurse

Write-Host "Found $($filesToUpdate.Count) PHP files to scan."

foreach ($fileInfo in $filesToUpdate) {
    # Skip the script itself and refactor scripts
    if ($fileInfo.Name -match "fix_links|refactor") { continue }

    $content = Get-Content -Path $fileInfo.FullName -Raw
    $originalContent = $content
    $hasChanges = $false

    # Pattern 1: Links to PHP files 'filename.php' or "filename.php"
    # Capture quotes to preserve them
    $pattern = "(['""])([\w\-\.]+\.php)(['""])"
    
    $content = [regex]::Replace($content, $pattern, {
            param($match)
            $quote1 = $match.Groups[1].Value
            $filename = $match.Groups[2].Value
            $quote2 = $match.Groups[3].Value
        
            $lowerName = $filename.ToLower()

            if ($fileLocations.ContainsKey($lowerName)) {
                $folder = $fileLocations[$lowerName]
                # Always use ../folder/file structure for consistency and safety (especially for included files)
                return "$quote1../$folder/$filename$quote2"
            }
            return $match.Value
        }, [System.Text.RegularExpressions.RegexOptions]::IgnoreCase)

    # Pattern 2: Assertions for login.html and index.php (Assuming in parent/root)
    $content = $content -replace "(['""])login\.html(['""])", '$1../login.html$2'
    $content = $content -replace "(['""])index\.php(['""])", '$1../index.php$2'
    
    # Pattern 3: Assets (images, css, js) - Shift from ../ to ../../
    # Only if the path starts with ../images/ etc.
    $content = $content -replace "(['""])\.\./images/", '$1../../images/'
    $content = $content -replace "(['""])\.\./css/", '$1../../css/'
    $content = $content -replace "(['""])\.\./js/", '$1../../js/'
    
    # Pattern 4: Sidebar includes (special check)
    # The previous refactor script updated includes to '../core/sidebar.php'.
    # Our regex above (Pattern 1) might try to change 'sidebar.php' again if it finds it quoted inside the include string?
    # NO, PHP includes usually look like include '../core/sidebar.php'; now.
    # Our regex matches "filename.php". It WON'T match "../core/filename.php" because of the slashes.
    # So existing include paths like '../core/session_check.php' should be safe.
    
    if ($content -ne $originalContent) {
        Set-Content -Path $fileInfo.FullName -Value $content -NoNewline
        Write-Host "Updated links in $($fileInfo.Name)" -ForegroundColor Green
    }
}

Write-Host "Link adjustment complete."
