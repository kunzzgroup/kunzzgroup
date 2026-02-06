<?php
// fix_links.php
// Updates HTML/JS links and image paths after refactoring

// 1. Definition of File Locations (Same as refactor.php/ps1)
$mapping = [
    'core' => [
        'check_permissions.php', 'check_restaurant_setup.php', 'get_fontkit.php', 'logout.php', 'session_check.php', 'sidebar.php'
    ],
    'api' => [
        'costapi.php', 'dishware_api.php', 'evaluation_form_api.php', 'generatecodeapi.php', 'get_jobs_api.php',
        'j1stockeditapi.php', 'j1stockeditpageapi.php', 'j1stocklistapi.php', 'j2stockeditapi.php', 'j2stockeditpageapi.php',
        'j2stocklistapi.php', 'j3stockeditpageapi.php', 'j3stocklistapi.php', 'job_positions_api.php', 'kpiapi.php',
        'menucost_api.php', 'menucostdata_api.php', 'price_api.php', 'qnaapi.php', 'schedule_api.php',
        'stockapi.php', 'stockeditapi.php', 'stocklistapi.php', 'stockminimumapi.php', 'stockremarkapi.php',
        'stocksotapi.php', 'supply_api.php'
    ],
    'cms' => [
        'aboutpage1upload.php', 'aboutpage4upload.php', 'bgmusicupload.php', 'dishware_upload.php', 'homepage1upload.php',
        'joinpage1upload.php', 'joinpage2upload.php', 'joinpage3upload.php', 'media_manager.php', 'phone_manage.php',
        'qna.php', 'tokyopage1upload.php', 'tokyopage5upload.php'
    ],
    'modules' => [
        'centerstockproductname.php', 'corporate_blueprint.php', 'corporate_blueprint_edit.php', 'cost.php', 'costedit.php',
        'dishware_index.php', 'dishware_stock.php', 'evaluation_form.php', 'export_branch_stock_excel.php',
        'j1stocklist.php', 'j1stockproductname.php', 'j2stocklist.php', 'j2stockproductname.php', 'j3stockinoutpage.php',
        'j3stocklist.php', 'j3stockproductname.php', 'kpi.php', 'kpiedit.php', 'menucost.php', 'menucostdata.php',
        'price.php', 'schedule_manager.php', 'stockedit.php', 'stockedit_j3.php', 'stockeditall.php', 'stocklist.php',
        'stocklistall.php', 'stockminimum.php', 'stockproductname.php', 'stockremark.php', 'stocksot.php', 'supply.php'
    ],
    'pages' => [
        'dashboard.php'
    ],
    'migration' => [
        'generatecode.php', 'migrate_stocksot_to_inout.php', 
    ],
    'dev' => [
        '_debug_build.php', 'corporate_blueprint_orgchart_example.php', 'diagnose_cost_tables.php', 'test_cost_update.php',
        'test_dishware_break_system.php', 'test_dishware_system.php', 'upload_sample_photos.php'
    ]
];

// Build reverse map: filename => folder
$fileLocations = [];
foreach ($mapping as $folder => $files) {
    foreach ($files as $file) {
        $fileLocations[$file] = $folder;
    }
}

// 2. Scan Directories
$dirsToScan = array_keys($mapping);

foreach ($dirsToScan as $currentDir) {
    if (!is_dir($currentDir)) continue;
    
    $files = glob($currentDir . '/*.php');
    foreach ($files as $filePath) {
        $content = file_get_contents($filePath);
        $originalContent = $content;
        $modified = false;

        // Pattern 1: HTML href/action and JS locations
        // Matches: href="file.php", action='file.php', location.href = "file.php", fetch("file.php")
        // We look for any string ending in .php inside quotes
        
        $content = preg_replace_callback('/([\'"])([\w\-\.]+\.php)([\'"])/', function($matches) use ($fileLocations, $currentDir) {
            $quote = $matches[1]; // " or '
            $targetBasename = $matches[2]; // stocklist.php
            $closingQuote = $matches[3]; // " or '
            
            // If target is in our map
            if (isset($fileLocations[$targetBasename])) {
                $targetFolder = $fileLocations[$targetBasename];
                
                // Calculate new path relative to $currentDir
                if ($targetFolder === $currentDir) {
                    $newPath = $targetBasename; // Same folder: stocklist.php
                } else {
                    $newPath = '../' . $targetFolder . '/' . $targetBasename; // Diff folder: ../modules/stocklist.php
                }
                
                // Only replace if logical (don't break existing absolute paths or complex strings)
                return $quote . $newPath . $closingQuote;
            }
            return $matches[0];
        }, $content);

        // Pattern 2: Fix Image paths
        // Many files use "../images/..." which now needs to be "../../images/..." 
        // because they moved 1 level deeper (root -> modules)
        // Original: backendtest/file.php -> href="../images/logo.png" (refers to parent/images)
        // New: backendtest/modules/file.php -> needs href="../../images/logo.png"
        
        // We only apply this if the file WAS in root (which they all were basically) 
        // AND it's not 'sidebar.php' which might handle paths differently? 
        // No, sidebar.php also moved to core/.
        
        // Regex: look for "../images/" inside quotes
        $content = preg_replace('/([\'"])\.\.\/images\//', '$1../../images/', $content);
        
        // Pattern 3: Fix CSS/JS paths if they follow same "../css" pattern
         $content = preg_replace('/([\'"])\.\.\/css\//', '$1../../css/', $content);
         $content = preg_replace('/([\'"])\.\.\/js\//', '$1../../js/', $content);
         
        // Pattern 4: Special case for login.html
        // It was likely valid as "login.html" (sibling) or "../login.html" (parent)
        // If it was "login.html", it now needs to be "../login.html" (assuming login.html stayed in root/parent)
        // If it was "../login.html", it needs to be "../../login.html"
        // Let's assume most linked to "login.html" directly if they were in backendtest/
        $content = preg_replace('/([\'"])login\.html([\'"])/', '$1../login.html$2', $content);
        $content = preg_replace('/([\'"])index\.php([\'"])/', '$1../index.php$2', $content); // Assuming index.php is outside

        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            echo "Updated links in $filePath\n";
        }
    }
}
echo "Link Fixes Complete.\n";
?>
