<?php
// refactor.php
// Script to reorganize backend files and update include paths

// 1. Define File Mappings
$mapping = [
    'core' => [
        'check_permissions.php',
        'check_restaurant_setup.php',
        'get_fontkit.php',
        'logout.php',
        'session_check.php',
        'sidebar.php'
    ],
    'api' => [
        'costapi.php',
        'dishware_api.php',
        'evaluation_form_api.php',
        'generatecodeapi.php',
        'get_jobs_api.php',
        'j1stockeditapi.php',
        'j1stockeditpageapi.php',
        'j1stocklistapi.php',
        'j2stockeditapi.php',
        'j2stockeditpageapi.php',
        'j2stocklistapi.php',
        'j3stockeditpageapi.php',
        'j3stocklistapi.php',
        'job_positions_api.php',
        'kpiapi.php',
        'menucost_api.php',
        'menucostdata_api.php',
        'price_api.php',
        'qnaapi.php',
        'schedule_api.php',
        'stockapi.php',
        'stockeditapi.php',
        'stocklistapi.php',
        'stockminimumapi.php',
        'stockremarkapi.php',
        'stocksotapi.php',
        'supply_api.php'
    ],
    'cms' => [
        'aboutpage1upload.php',
        'aboutpage4upload.php',
        'bgmusicupload.php',
        'dishware_upload.php',
        'homepage1upload.php',
        'joinpage1upload.php',
        'joinpage2upload.php',
        'joinpage3upload.php',
        'media_manager.php',
        'phone_manage.php',
        'qna.php',
        'tokyopage1upload.php',
        'tokyopage5upload.php'
    ],
    'modules' => [
        'centerstockproductname.php',
        'corporate_blueprint.php',
        'corporate_blueprint_edit.php',
        'cost.php',
        'costedit.php',
        'dishware_index.php',
        'dishware_stock.php',
        'evaluation_form.php',
        'export_branch_stock_excel.php',
        'j1stocklist.php',
        'j1stockproductname.php',
        'j2stocklist.php',
        'j2stockproductname.php',
        'j3stockinoutpage.php',
        'j3stocklist.php',
        'j3stockproductname.php',
        'kpi.php',
        'kpiedit.php',
        'menucost.php',
        'menucostdata.php',
        'price.php',
        'schedule_manager.php',
        'stockedit.php',
        'stockedit_j3.php',
        'stockeditall.php',
        'stocklist.php',
        'stocklistall.php',
        'stockminimum.php',
        'stockproductname.php',
        'stockremark.php',
        'stocksot.php',
        'supply.php'
    ],
    'pages' => [
        'dashboard.php'
    ],
    'migration' => [
        'generatecode.php',
        'migrate_stocksot_to_inout.php',
        'migration_add_chargeable_quantity.sql',
        'migration_add_restaurants.sql',
        'migration_remove_code_column.sql',
        'update_system_assign_multiselect.sql'
    ],
    'dev' => [
        '_debug_build.php',
        'corporate_blueprint_orgchart_example.php',
        'diagnose_cost_tables.php',
        'test_cost_update.php',
        'test_dishware_break_system.php',
        'test_dishware_system.php',
        'upload_sample_photos.php'
    ]
];

// Create reverse mapping: filename => folder
$fileLocations = [];
foreach ($mapping as $folder => $files) {
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }
    foreach ($files as $file) {
        $fileLocations[$file] = $folder;
    }
}

// 2. Move Files
echo "Moving files...\n";
foreach ($fileLocations as $file => $folder) {
    if (file_exists($file)) {
        $dest = $folder . '/' . $file;
        if (rename($file, $dest)) {
            echo "Moved $file to $folder/\n";
        } else {
            echo "Failed to move $file\n";
        }
    } else {
        echo "Warning: File $file not found.\n";
    }
}

// 3. Update Include Paths
echo "Updating include paths...\n";

// Helper to calculate relative path
function getRelativePath($fromFolder, $toFolder) {
    if ($fromFolder === $toFolder) return '';
    
    // Simple logic since we are only 1 level deep from root
    // Root is parent of both
    return '../' . $toFolder . '/';
}

$allFolders = array_keys($mapping);

foreach ($allFolders as $currentFolder) {
    $dir = new DirectoryIterator($currentFolder);
    foreach ($dir as $fileinfo) {
        if (!$fileinfo->isDot() && $fileinfo->getExtension() === 'php') {
            $filePath = $fileinfo->getPathname();
            $content = file_get_contents($filePath);
            $originalContent = $content;
            
            // Regex to find include/require/require_once with quotes
            // Matches: require_once 'filename.php'; or "filename.php"
            // We need to capture the filename
            $pattern = '/(include|require|require_once)\s*[\(\s]*[\'"]([^\'"]+)[\'"]\s*[\)\s]*;/i';
            
            $content = preg_replace_callback($pattern, function($matches) use ($fileLocations, $currentFolder) {
                $statement = $matches[1]; // include/require...
                $targetFile = $matches[2]; // filename.php
                
                // Check if the target file is one of the moved files
                $targetBasename = basename($targetFile);
                
                if (isset($fileLocations[$targetBasename])) {
                    $targetFolder = $fileLocations[$targetBasename];
                    
                    // If target is in the same folder, just use basename
                    if ($targetFolder === $currentFolder) {
                         $newPath = $targetBasename;
                    } else {
                        // Calculate relative path
                         $rel = getRelativePath($currentFolder, $targetFolder);
                         $newPath = $rel . $targetBasename;
                    }
                    
                    return "$statement '$newPath';";
                }
                
                // If not in our map, leave it alone (it might be external or relative path we don't know)
                return $matches[0];
            }, $content);
            
            if ($content !== $originalContent) {
                file_put_contents($filePath, $content);
                echo "Updated paths in $filePath\n";
            }
        }
    }
}

echo "Refactoring complete.\n";
?>
