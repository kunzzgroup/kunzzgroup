<?php
$f = "c:\\Users\\kunzz\\OneDrive\\Desktop\\kunzzgroup\\backend\\add_employee.php";
$html = file_get_contents($f);

// 1. Headers replaced
$s1 = strpos($html, '<form id="addUserForm">');
$s2 = strpos($html, '<div class="form-row-3col">', $s1);

if ($s1 !== false && $s2 !== false) {
    // we want to replace from <div id="messageArea"> to $s2
    $msg_area = strpos($html, '<div id="messageArea"></div>');
    if ($msg_area !== false && $msg_area < $s1) {
        $s1 = $msg_area;
    }
    
    $new_top = '
            <!-- 向导进度条 -->
            <div class="wizard-progress">
                <div class="step-indicator active" id="indicator-step-1">
                    <div class="step-circle">1</div>
                    <span>基本信息</span>
                </div>
                <div class="step-connector" id="connector-step-1"></div>
                <div class="step-indicator" id="indicator-step-2">
                    <div class="step-circle">2</div>
                    <span>个人资料</span>
                </div>
                <div class="step-connector" id="connector-step-2"></div>
                <div class="step-indicator" id="indicator-step-3">
                    <div class="step-circle">3</div>
                    <span>权限与设置</span>
                </div>
            </div>

            <!-- 消息提示区 -->
            <div id="messageArea" style="padding: 16px 32px 0;"></div>

            <!-- 向导内容区 -->
            <div class="wizard-container">
            <form id="addUserForm">
                
                <!-- ========== STEP 1: Basic Information ========== -->
                <div class="wizard-step active" id="step-1">
                    <div class="form-section">
                        <div class="form-section-header">基本信息 Basic Information</div>
                        <div class="form-section-content form-grid-2">
                            <div class="form-group" id="group-add-username">
                                <label for="add_username">英文姓名 English Name *</label>
                                <input type="text" id="add_username" name="username" required maxlength="50" placeholder="e.g. John Doe">
                                <div class="error-msg">请填写英文姓名，至少包含两个单词</div>
                            </div>
                            
                            <div class="form-group" id="group-add-username-cn">
                                <label for="add_username_cn">中文姓名 Chinese Name</label>
                                <input type="text" id="add_username_cn" name="username_cn" maxlength="100" placeholder="e.g. 刘德华">
                                <div class="error-msg">中文姓名至少需要两个汉字</div>
                            </div>
                            
                            <div class="form-group" id="group-add-nickname">
                                <label for="add_nickname">昵称 Nickname</label>
                                <input type="text" id="add_nickname" name="nickname" maxlength="50">
                            </div>
                            
                            <div class="form-group" id="group-add-email">
                                <label for="add_email">邮箱 Email *</label>
                                <input type="email" id="add_email" name="email" required maxlength="100" placeholder="e.g. user@example.com">
                                <div class="error-msg">请填写有效的邮箱地址</div>
                            </div>
                            
                            <div class="form-group" id="group-add-account-type">
                                <label for="add_account_type">账号类型 Account Type *</label>
                                <select id="add_account_type" name="account_type" required>
                                    <option value="">请选择账号类型</option>
                                    <option value="special">特殊 (Special)</option>
                                    <option value="hr">人事部 (HR)</option>
                                    <option value="account">会计部 (Accountant)</option>
                                    <option value="media">媒体制作部 (Media Production)</option>
                                    <option value="marketing">推广部 (Marketing)</option>
                                    <option value="support">支援部 (Support)</option>
                                    <option value="production">生产部 (Production)</option>
                                    <option value="r&d">研发部 (R&D)</option>
                                    <option value="technical">科技部 (Technical)</option>
                                    <option value="design">设计部 (Design)</option>
                                    <option value="operation">Operation</option>
                                    <option value="service">前台 (Service)</option>
                                    <option value="sushi">Sushi Bar</option>
                                    <option value="kitchen">厨房 (Kitchen)</option>
                                </select>
                                <div class="error-msg">请选择账号类型</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="add_position">职位 Position</label>
                                <select id="add_position" name="position">
                                    <option value="">请先选择账号类型</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========== STEP 2: Personal Details ========== -->
                <div class="wizard-step" id="step-2">
                    <div class="form-section">
                        <div class="form-section-header">个人资料 Personal Details</div>
                        <div class="form-section-content">
';
    $html = substr($html, 0, $s1) . $new_top . substr($html, $s2);
}

// 2. Class replacers
$html = str_replace('<div class="form-row-3col">', '<div class="form-grid-3">', $html);
$html = str_replace('<div class="form-row-2col">', '<div class="form-grid-2">', $html);
$html = str_replace('<div class="form-row-1col">', '<div class="form-grid-1">', $html);

// 3. Step 3 Wrappings
$s3 = strpos($html, '<!-- 银行信息区块 -->');
if ($s3 !== false) {
    $new_step_3 = '
                    </div> <!-- End of Step 2 content -->
                </div> <!-- End of Step 2 -->

                <!-- ========== STEP 3: Bank, Emergency, Permissions ========== -->
                <div class="wizard-step" id="step-3">
                    <!-- 银行信息区块 -->
';
    $html = substr($html, 0, $s3) . $new_step_3 . substr($html, $s3 + strlen('<!-- 银行信息区块 -->'));
}

// 4. Clean up old Book wrappers
$spine = '                </div><!-- /book-page-left -->

                <!-- ── 书脊 ── -->
                <div class="book-spine"></div>

                <!-- ── 右页：账号 & 权限 ── -->
                <div class="book-page book-page-right">
                <!-- 账号设置区块 -->';
$html = str_replace($spine, '', $html);

// 5. Remove old Account Type section since it\'s moved down and empty now
$acc_start = strpos($html, '<div class="form-section">', strpos($html, '<!-- 账号设置区块 -->') - 50);
$acc_end = strpos($html, '<!-- 编辑职员专用权限布局 (复用至添加页面) -->');
if ($acc_start !== false && $acc_end !== false && $acc_start < $acc_end) {
    $html = substr($html, 0, $acc_start) . substr($html, $acc_end);
}

// remove literal comment
$html = str_replace('<!-- 账号设置区块 -->', '', $html);

// 6. Action Bar Replacement
$act_start = strpos($html, '</div><!-- /book-page-right -->');
$act_end = strpos($html, '</div><!-- /add-employee-page -->');

if ($act_start !== false && $act_end !== false) {
    $new_action_bar = '
                    </div> <!-- End of Step 3 -->
                </form>
            </div> <!-- End of Wizard container -->

            <!-- 底部操作按钮 Sticky ActionBar -->
            <div class="page-action-bar">
                <div class="action-left">
                    <a href="generatecode.php" class="btn-wizard btn-cancel">取消 Cancel</a>
                </div>
                <div class="action-right">
                    <button type="button" class="btn-wizard btn-prev" id="btn-prev" style="display: none;">
                        <i class="fas fa-arrow-left"></i> 上一步
                    </button>
                    <button type="button" class="btn-wizard btn-next" id="btn-next">
                        下一步 <i class="fas fa-arrow-right"></i>
                    </button>
                    <button type="submit" form="addUserForm" class="btn-wizard btn-next" id="btn-save" style="display: none;">
                        <i class="fas fa-check"></i> 保存职员
                    </button>
                </div>
            </div>
';
    $html = substr($html, 0, $act_start) . $new_action_bar . substr($html, $act_end);
}

file_put_contents($f, $html);
echo "done\n";
