<?php
// 检查餐厅功能设置 shell page
// No explicit session check in original file, but good practice to have one if it's protected.
// However, the original file had DB credentials but no session variables usage other than potentially implicit ones.
// I will keep it simple and just include the template as logic is moved to API.

include '../templates/check_restaurant_setup_template.php';
?>
