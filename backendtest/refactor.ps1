# refactor.ps1

# Define mappings
$mapping = @{
    "core" = @(
        "check_permissions.php", "check_restaurant_setup.php", "get_fontkit.php", "logout.php", "session_check.php", "sidebar.php"
    )
    "api" = @(
        "costapi.php", "dishware_api.php", "evaluation_form_api.php", "generatecodeapi.php", "get_jobs_api.php",
        "j1stockeditapi.php", "j1stockeditpageapi.php", "j1stocklistapi.php", "j2stockeditapi.php", "j2stockeditpageapi.php",
        "j2stocklistapi.php", "j3stockeditpageapi.php", "j3stocklistapi.php", "job_positions_api.php", "kpiapi.php",
        "menucost_api.php", "menucostdata_api.php", "price_api.php", "qnaapi.php", "schedule_api.php",
        "stockapi.php", "stockeditapi.php", "stocklistapi.php", "stockminimumapi.php", "stockremarkapi.php",
        "stocksotapi.php", "supply_api.php"
    )
    "cms" = @(
        "aboutpage1upload.php", "aboutpage4upload.php", "bgmusicupload.php", "dishware_upload.php", "homepage1upload.php",
        "joinpage1upload.php", "joinpage2upload.php", "joinpage3upload.php", "media_manager.php", "phone_manage.php",
        "qna.php", "tokyopage1upload.php", "tokyopage5upload.php"
    )
    "modules" = @(
        "centerstockproductname.php", "corporate_blueprint.php", "corporate_blueprint_edit.php", "cost.php", "costedit.php",
        "dishware_index.php", "dishware_stock.php", "evaluation_form.php", "export_branch_stock_excel.php",
        "j1stocklist.php", "j1stockproductname.php", "j2stocklist.php", "j2stockproductname.php", "j3stockinoutpage.php",
        "j3stocklist.php", "j3stockproductname.php", "kpi.php", "kpiedit.php", "menucost.php", "menucostdata.php",
        "price.php", "schedule_manager.php", "stockedit.php", "stockedit_j3.php", "stockeditall.php", "stocklist.php",
        "stocklistall.php", "stockminimum.php", "stockproductname.php", "stockremark.php", "stocksot.php", "supply.php"
    )
    "pages" = @(
        "dashboard.php"
    )
    "migration" = @(
        "generatecode.php", "migrate_stocksot_to_inout.php", "migration_add_chargeable_quantity.sql", "migration_add_restaurants.sql",
        "migration_remove_code_column.sql", "update_system_assign_multiselect.sql"
    )
    "dev" = @(
        "_debug_build.php", "corporate_blueprint_orgchart_example.php", "diagnose_cost_tables.php", "test_cost_update.php",
        "test_dishware_break_system.php", "test_dishware_system.php", "upload_sample_photos.php"
    )
}

# 1. Create Directories and Reverse Map
$fileLocations = @{}
foreach ($folder in $mapping.Keys) {
    if (-not (Test-Path -Path $folder)) {
        New-Item -ItemType Directory -Path $folder | Out-Null
        Write-Host "Created directory: $folder"
    }
    foreach ($file in $mapping[$folder]) {
        $fileLocations[$file] = $folder
    }
}

# 2. Move Files
Write-Host "Moving files..."
foreach ($file in $fileLocations.Keys) {
    $folder = $fileLocations[$file]
    if (Test-Path -Path $file) {
        Move-Item -Path $file -Destination "$folder\$file" -Force
        Write-Host "Moved $file to $folder"
    } else {
        Write-Host "Warning: File $file not found." -ForegroundColor Yellow
    }
}

# 3. Update Include Paths
Write-Host "Updating include paths..."
$folders = $mapping.Keys

foreach ($currentFolder in $folders) {
    $filesInFolder = Get-ChildItem -Path $currentFolder -Filter "*.php"
    foreach ($fileInfo in $filesInFolder) {
        $content = Get-Content -Path $fileInfo.FullName -Raw
        $originalContent = $content
        
        # Regex to match include/require statements
        # Matches include 'file.php', require "file.php", etc.
        $pattern = "(include|require|require_once)\s*[\(\s]*['""]([^'""]+\.php)['""]\s*[\)\s]*;"
        
        # We use a scriptblock for replacement logic which is tricky in pure regex replace in PS
        # So we iterate through matches manually
        
        $matches = [regex]::Matches($content, $pattern, "IgnoreCase")
        
        foreach ($match in $matches) {
            $fullMatch = $match.Value
            $statement = $match.Groups[1].Value
            $targetFile = $match.Groups[2].Value
            $targetBasename = Split-Path $targetFile -Leaf
            
            if ($fileLocations.ContainsKey($targetBasename)) {
                $targetFolder = $fileLocations[$targetBasename]
                
                $newPath = ""
                if ($targetFolder -eq $currentFolder) {
                    $newPath = $targetBasename
                } else {
                    $newPath = "../$targetFolder/$targetBasename"
                }
                
                # Replace logic
                # Only replace if the path is different
                if ($targetFile -ne $newPath) {
                    # Escape special regex chars in search string
                    $escapedMatch = [regex]::Escape($fullMatch)
                    $replacement = "$statement '$newPath';"
                    $content = $content -replace $escapedMatch, $replacement
                }
            }
        }
        
        if ($content -ne $originalContent) {
            Set-Content -Path $fileInfo.FullName -Value $content -NoNewline
            Write-Host "Updated paths in $($fileInfo.Name)" -ForegroundColor Green
        }
    }
}

Write-Host "Refactoring complete."
