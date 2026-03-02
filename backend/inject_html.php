<?php
$file = 'c:\Users\kunzz\OneDrive\Desktop\kunzzgroup\backend\generatecode.php';
$content = file_get_contents($file);

// Extract the permissions block
preg_match('/<div class="perm-layout-container">(.*?)<div class="modal-buttons">/s', $content, $matches);
if (!isset($matches[0])) {
    echo "Failed to extract perm block\n";
    exit;
}

$permBlock = '<div class="form-section" style="margin-top: 20px;"><div class="form-section-header">权限设置</div><div class="form-section-content">' . 
             '<div class="perm-layout-container compact-perm-layout">' . 
             $matches[1] . 
             '</div></div></div>';

// remove id inside the injected block
$permBlock = str_replace('<div id="perm-detail-content">', '<div class="perm-detail-content" style="height: auto;">', $permBlock);

// 1. Inject into Add User Modal
$addPattern = '/(<select id="add_position" name="position">.*?<\/select>.*?<\/div>.*?<\/div>.*?<\/div>.*?<\/div>)(.*?)<div class="modal-buttons">/s';
if (preg_match($addPattern, $content)) {
    $content = preg_replace($addPattern, '$1' . "\n" . $permBlock . '$2<div class="modal-buttons">', $content, 1);
    echo "Successfully injected into addUserModal.\n";
} else {
    echo "Failed to find injection point in addUserModal.\n";
}

// 2. Inject into Edit User Modal 
$editPattern = '/(<select id="edit_position" name="position">.*?<\/select>.*?<\/div>.*?<\/div>.*?<\/div>.*?<\/div>)(.*?)<div class="modal-buttons">/s';
if (preg_match($editPattern, $content)) {
    // We only want the block, not the modal-buttons which edit User modal already has
    $content = preg_replace($editPattern, '$1' . "\n" . $permBlock . '$2<div class="modal-buttons">', $content, 1);
    echo "Successfully injected into editUserModal.\n";
} else {
    echo "Failed to find injection point in editUserModal.\n";
}

// Enforce ID uniqueness for the main one
$content = preg_replace('/<div id="perm-detail-content">/', '<div class="perm-detail-content" id="perm-detail-content-main">', $content, 1);

file_put_contents($file, $content);
echo "File updated successfully.\n";
?>
