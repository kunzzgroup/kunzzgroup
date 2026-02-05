<?php
// PHP logic (session check) should already be handled by the shell or session_check.php
// This is just the HTML skeleton
?>
<link rel="stylesheet" href="<?php echo $basePath ?? '../'; ?>css/sidebar.css">

<div class="informationmenu">
    <div class="informationmenu-header" id="user-info-container">
        <!-- populated by JS -->
    </div>

    <div class="informationmenu-content" id="menu-content">
        <!-- populated by JS -->
    </div>
</div>

<script src="<?php echo $basePath ?? '../'; ?>js/sidebar.js" data-base-path="<?php echo $basePath ?? '../'; ?>"></script>
