<?php
if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}
?>
<?php
// 包含会话验证
require_once 'session_check.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/generatecode.css?v=<?php echo time(); ?>">
    <title>添加职员 - 职员管理系统</title>
    <style>
body { background: #f3f4f6; }

.add-employee-page {
    display: flex;
    flex-direction: column;
    height: 100vh;
    overflow: hidden;
}

/* 顶部标题栏 */
.page-header-bar {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 24px;
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
    flex-shrink: 0;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
    z-index: 10;
}
.page-header-bar h1 {
    font-size: 20px;
    font-weight: 700;
    color: #111827;
    margin: 0;
}
.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: #f97316;
    color: #ffffff;
    border: 1px solid transparent;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(249,115,22,0.2);
}
.back-btn:hover { 
    background: #ea6b0a;
    color: #fff;
    transform: translateY(-1px);
}

/* ── Scrollable form area ── */
.form-scroll-area {
    flex: 1;
    overflow: hidden;
    padding: 10px 14px;
    display: flex;
    justify-content: center;
    align-items: stretch;
    min-height: 0;
}

#addUserForm {
    width: 100%;
    max-width: 1600px;
    display: grid;
    grid-template-columns: 35fr 55fr;
    gap: 10px;
    align-items: stretch;
    height: 100%;
    min-height: 0;
}

.form-col {
    display: flex;
    flex-direction: column;
    gap: 14px;
    height: 100%;
    min-height: 0;
    overflow: hidden; /* 防止 col 本身溢出 */
}

/* 左侧主卡片 — 关键滚动容器 */
.form-col > .left-card {
    display: block;
    flex: 1 1 0;           /* 强制占用剩余空间 */
    min-height: 0;         /* 关键：允许子元素溢出触发滚动 */
    max-height: 100%;      /* 关键：限制高度上限 */
    overflow-y: auto !important;
    overflow-x: hidden;
    padding-right: 0px;
    padding-bottom: 16px;
    box-sizing: border-box;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
}

/* Global Scrollbar styling */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}
::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* 右侧 col 最后一个 section 填满剩余高度 */
.form-col > .form-section:last-child {
    flex: 1;
    min-height: 0;
}

.form-col > .editUserPermLayout:last-child {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-height: 0;
}

.editUserPermLayout .form-section-content {
    flex: 1;
    min-height: 0;
    display: flex;
    flex-direction: column;
    overflow: auto;
}

/* 表头不缩小 */
.form-section-header,
.form-section-header-bank {
    flex-shrink: 0;
}

/* Permission section */
.editUserPermLayout .perm-layout-container {
    height: 520px !important;
    display: flex !important;
    gap: 16px;
    align-items: stretch;
}

.editUserPermLayout .perm-tree-container {
    flex: 1;
    max-height: 100% !important;
    overflow-y: auto;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 15px;
    background: #fff;
}

.editUserPermLayout .perm-detail-card {
    flex: 0 0 420px !important;
    max-height: 100% !important;
    position: relative;
    overflow: hidden !important; /* Card itself doesn't scroll, content does */
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #fff;
}

/* Ensure detail content scrolls properly and doesn't conflict */
.editUserPermLayout .perm-detail-content {
    height: 100% !important;
    overflow-y: auto !important;
    padding: 15px;
    box-sizing: border-box;
    display: none;
}

.editUserPermLayout .perm-detail-content.active {
    display: block !important;
}

/* Force hide placeholder when content is active */
.editUserPermLayout .perm-detail-placeholder.hidden {
    display: none !important;
}


/* ── 平板视图 (<1200px) ── */
@media (max-width: 1200px) {
    .form-scroll-area {
        overflow-y: auto;
        align-items: flex-start;
    }
    #addUserForm {
        grid-template-columns: 1fr;
        max-width: 100%;
        height: auto;
    }
    .form-col {
        height: auto;
        overflow-y: visible;
        overflow: visible;
    }
    .left-card {
        flex: none;
        height: auto;
        min-height: unset;
        overflow-y: visible;
        padding-bottom: 16px;
    }
    .form-col > .form-section:last-child,
    .form-col > .editUserPermLayout:last-child {
        flex: none;
        overflow: visible;
    }
}

/* ── 手机视图 (<768px) ── */
@media (max-width: 768px) {
    .page-header-bar {
        padding: 12px 14px;
    }
    .page-header-bar h1 {
        font-size: 16px;
    }
    .back-btn {
        padding: 6px 12px;
        font-size: 13px;
    }
    .form-scroll-area {
        padding: 10px;
    }
    .form-grid-3, .form-grid-2, .form-row-2col {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    .form-section-content {
        padding: 14px 14px;
    }
    .form-section-header,
    .form-section-header-bank {
        padding: 10px 14px;
        font-size: 13px;
    }
    .page-action-bar {
        padding: 12px 16px;
        flex-direction: column-reverse;
        gap: 8px;
    }
    .btn-save, .btn-back-action {
        width: 100%;
        justify-content: center;
        padding: 12px;
        font-size: 15px;
    }
    #toast-container {
        bottom: 110px;
        left: 50%;
        right: auto;
        transform: translateX(-50%);
        align-items: center;
        width: 90vw;
    }
}

/* Cards */
.form-section {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
}
.form-section-header {
    background: #f97316;
    padding: 13px 20px;
    font-size: 15px;
    font-weight: 600;
    color: #fff;
}
.form-section-header-bank {
    background: #f97316;
    padding: 13px 20px;
    font-size: 15px;
    font-weight: 600;
    color: #fff;
    margin-top: 10px; /* 与个人资料区块分隔 */
}
.form-section-content { padding: 20px 24px; }

/* Grids */
.form-grid-2   { display: grid; grid-template-columns: 1fr 1fr;       gap: 14px 22px; }
.form-grid-3   { display: grid; grid-template-columns: 1fr 1fr 1fr;   gap: 14px 16px; }
.form-grid-1   { display: grid; grid-template-columns: 1fr;           gap: 14px; }
.form-row-2col { display: grid; grid-template-columns: 1fr 1fr;       gap: 14px 16px; }
.form-row-1col { display: grid; grid-template-columns: 1fr;           gap: 14px; }

/* Form fields */
.form-group { display: flex; flex-direction: column; }
.form-group label { 
    font-size: 13px; 
    font-weight: 600; 
    color: #374151; 
    margin-bottom: 6px; 
    min-height: 20px; /* Standardize height to ensure consistent box alignment */
    display: flex;
    align-items: center;
}
.form-group input,
.form-group select,
.form-group textarea {
    padding: 9px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    color: #111827;
    background: #fff;
    transition: border-color .2s, box-shadow .2s;
    width: 100%;
    box-sizing: border-box;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249,115,22,.12);
}
.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="tel"],
.form-group input[type="date"],
.form-group select { height: 36px !important; }
.form-group textarea { min-height: 35px; resize: none !important; overflow-y: auto; }

/* Validation */
.form-group.has-error input,
.form-group.has-error select { border-color: #ef4444; background: #fef2f2; }
.error-msg { color: #ef4444; font-size: 10px; margin-top: 4px; display: none; }
.has-error .error-msg { display: block; }

/* ── Sticky action bar at bottom ── */
.page-action-bar {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 12px;
    padding: 14px 32px;
    background: #fff;
    border-top: 1px solid #e5e7eb;
    flex-shrink: 0;
    box-shadow: 0 -2px 6px rgba(0,0,0,.04);
}
.btn-save {
    padding: 10px 28px;
    background: #f97316;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 2px 6px rgba(249,115,22,.3);
    transition: all .2s;
}
.btn-save:hover { background: #ea6b0a; transform: translateY(-1px); }
.btn-back-action {
    padding: 10px 22px;
    background: #fff;
    color: #374151;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background .2s;
}
.btn-back-action:hover { background: #f3f4f6; }

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ── Toast 通知 ── */
#toast-container {
    position: fixed;
    bottom: 80px;
    right: 32px;
    z-index: 99999;
    display: flex;
    flex-direction: column-reverse;
    align-items: flex-end;
    gap: 8px;
    pointer-events: none;
    max-width: 340px;
}
.toast {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 600;
    box-shadow: 0 6px 24px rgba(0,0,0,.16);
    pointer-events: all;
    animation: toastIn .3s cubic-bezier(.21,1.02,.73,1) forwards;
    width: 100%;
}
.toast.toast-error   { background:#fff1f2; color:#9f1239; border:1.5px solid #fca5a5; }
.toast.toast-success { background:#f0fdf4; color:#166534; border:1.5px solid #86efac; }
.toast.toast-warning { background:#fffbeb; color:#92400e; border:1.5px solid #fcd34d; }
.toast.toast-out     { animation: toastOut .25s ease forwards; }
@keyframes toastIn  { from{opacity:0;transform:translateY(16px) scale(.96)} to{opacity:1;transform:translateY(0) scale(1)} }
@keyframes toastOut { from{opacity:1;transform:translateY(0) scale(1)}      to{opacity:0;transform:translateY(10px) scale(.96)} }

.required-mark {
    color: #ef4444;
    font-size: 14px;
    margin-left: 2px;
    line-height: 1;
}
</style>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container" style="padding:0; height:100vh; max-width:100%;">
        <div class="add-employee-page">


            <!-- 顶部标题栏 -->
            <div class="page-header-bar">
                <a href="generatecode" class="back-btn">
                    <i class="fas fa-arrow-left"></i> 返回列表
                </a>
                <h1><i class="fas fa-user-plus"></i> 添加新职员</h1>
            </div>

            <!-- Message area hidden, toast used instead -->
            <div id="messageArea" style="display:none;"></div>

            <!-- Single-page scroll area -->
            <div class="form-scroll-area">
            <form id="addUserForm" style="animation: fadeIn .3s ease;">
                <div class="form-col">
                <div class="form-section left-card">
    <!-- ── 个人资料 PERSONAL DETAILS ── -->
    <div class="form-section-header" style="text-transform: uppercase;">个人资料 PERSONAL DETAILS</div>
    <div class="form-section-content">
        <div class="form-grid-3">
            <div class="form-group" id="group-add-username">
                <label for="add_username">英语姓名 English Name <span class="required-mark">*</span></label>
                <input type="text" id="add_username" name="username" required maxlength="50" placeholder="E.G. JOHN DOE" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                <div class="error-msg">请填写英文姓名，至少包含两个单词</div>
            </div>
            
            <div class="form-group" id="group-add-username-cn">
                <label for="add_username_cn">中文姓名 Chinese Name</label>
                <input type="text" id="add_username_cn" name="username_cn" maxlength="100" placeholder="E.G. 刘德华">
                <div class="error-msg">中文姓名至少需要两个汉字</div>
            </div>
            
            <div class="form-group" id="group-add-nickname">
                <label for="add_nickname">昵称 Nickname</label>
                <input type="text" id="add_nickname" name="nickname" maxlength="50" placeholder="E.G. JACKIE">
            </div>
            
            <div class="form-group" id="group-add-email">
                <label for="add_email">邮箱 Email <span class="required-mark">*</span></label>
                <input type="email" id="add_email" name="email" required maxlength="100" placeholder="e.g. user@example.com">
                <div class="error-msg">请填写有效的邮箱地址</div>
            </div>
            
            <div class="form-group">
                <label for="add_ic_number">身份证号码</label>
                <input type="text" id="add_ic_number" name="ic_number" maxlength="20">
            </div>
            
            <div class="form-group">
                <label for="add_phone_number">联络号码</label>
                <input type="tel" id="add_phone_number" name="phone_number" maxlength="20">
            </div>
            
            <div class="form-group">
                <label for="add_gender">性别</label>
                <select id="add_gender" name="gender">
                    <option value="">请选择</option>
                    <option value="male">男</option>
                    <option value="female">女</option>
                    <option value="other">其他</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="add_nationality">国籍</label>
                <select id="add_nationality" name="nationality">
                    <option value="">请选择国籍</option>
                    <option value="Afghanistan">Afghanistan</option>
                    <option value="Armenia">Armenia</option>
                    <option value="Azerbaijan">Azerbaijan</option>
                    <option value="Bahrain">Bahrain</option>
                    <option value="Bangladesh">Bangladesh</option>
                    <option value="Bhutan">Bhutan</option>
                    <option value="Brunei">Brunei</option>
                    <option value="Cambodia">Cambodia</option>
                    <option value="China">China</option>
                    <option value="Cyprus">Cyprus</option>
                    <option value="East Timor (Timor-Leste)">East Timor (Timor-Leste)</option>
                    <option value="Georgia">Georgia</option>
                    <option value="India">India</option>
                    <option value="Indonesia">Indonesia</option>
                    <option value="Iran">Iran</option>
                    <option value="Iraq">Iraq</option>
                    <option value="Israel">Israel</option>
                    <option value="Japan">Japan</option>
                    <option value="Jordan">Jordan</option>
                    <option value="Kazakhstan">Kazakhstan</option>
                    <option value="Kuwait">Kuwait</option>
                    <option value="Kyrgyzstan">Kyrgyzstan</option>
                    <option value="Laos">Laos</option>
                    <option value="Lebanon">Lebanon</option>
                    <option value="Malaysia">Malaysia</option>
                    <option value="Maldives">Maldives</option>
                    <option value="Mongolia">Mongolia</option>
                    <option value="Myanmar (Burma)">Myanmar (Burma)</option>
                    <option value="Nepal">Nepal</option>
                    <option value="North Korea">North Korea</option>
                    <option value="Oman">Oman</option>
                    <option value="Pakistan">Pakistan</option>
                    <option value="Palestine">Palestine</option>
                    <option value="Philippines">Philippines</option>
                    <option value="Qatar">Qatar</option>
                    <option value="Saudi Arabia">Saudi Arabia</option>
                    <option value="Singapore">Singapore</option>
                    <option value="South Korea">South Korea</option>
                    <option value="Sri Lanka">Sri Lanka</option>
                    <option value="Syria">Syria</option>
                    <option value="Taiwan">Taiwan</option>
                    <option value="Tajikistan">Tajikistan</option>
                    <option value="Thailand">Thailand</option>
                    <option value="Turkey">Turkey</option>
                    <option value="Turkmenistan">Turkmenistan</option>
                    <option value="United Arab Emirates">United Arab Emirates</option>
                    <option value="Uzbekistan">Uzbekistan</option>
                    <option value="Vietnam">Vietnam</option>
                    <option value="Yemen">Yemen</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="add_race">种族</label>
                <select id="add_race" name="race">
                    <option value="">请选择种族</option>
                    <option value="Malay">Malay</option>
                    <option value="Chinese">Chinese</option>
                    <option value="Indian">Indian</option>
                    <option value="Bumiputera (Sabah/Sarawak)">Bumiputera (Sabah/Sarawak)</option>
                    <option value="Indonesian">Indonesian</option>
                    <option value="Bangladeshi">Bangladeshi</option>
                    <option value="Nepali">Nepali</option>
                    <option value="Myanmar">Myanmar</option>
                    <option value="Filipino">Filipino</option>
                    <option value="Indian (Foreign)">Indian (Foreign)</option>
                    <option value="Pakistani">Pakistani</option>
                    <option value="Vietnamese">Vietnamese</option>
                    <option value="Cambodian">Cambodian</option>
                    <option value="Others (Foreign)">Others (Foreign)</option>
                </select>
            </div>
        </div>
        
        <div class="form-grid-1" style="margin-top: 14px;">
            <div class="form-group">
                <label for="add_home_address">地址</label>
                <textarea id="add_home_address" name="home_address" rows="2" maxlength="255" style="resize: none;"></textarea>
            </div>
        </div>

        <div class="form-row-2col" style="margin-top: 14px;">
            <div class="form-group">
                <label for="add_emergency_contact_name">紧急联系人</label>
                <input type="text" id="add_emergency_contact_name" name="emergency_contact_name" maxlength="100">
            </div>
            
            <div class="form-group">
                <label for="add_emergency_phone_number">紧急联系人号码</label>
                <input type="tel" id="add_emergency_phone_number" name="emergency_phone_number" maxlength="20">
            </div>
        </div>
    </div>

    <!-- ── 银行信息 BANK INFORMATION ── 移进 left-card 内 -->
    <div class="form-section-header-bank" style="text-transform: uppercase;">银行信息 BANK INFORMATION</div>
    <div class="form-section-content" style="flex-shrink: 0; padding-top: 15px;">
        <div class="form-grid-3">
            <div class="form-group">
                <label for="add_bank_account_holder_en">银行账户持有人</label>
                <input type="text" id="add_bank_account_holder_en" name="bank_account_holder_en" maxlength="50">
            </div>
            
            <div class="form-group">
                <label for="add_bank_account">银行账号</label>
                <input type="text" id="add_bank_account" name="bank_account" maxlength="30">
            </div>
            
            <div class="form-group">
                <label for="add_bank_name">银行名称</label>
                <select id="add_bank_name" name="bank_name">
                    <option value="">请选择银行</option>
                    <option value="Maybank (Malayan Banking Berhad)">Maybank (Malayan Banking Berhad)</option>
                    <option value="CIMB Bank">CIMB Bank</option>
                    <option value="Public Bank">Public Bank</option>
                    <option value="RHB Bank">RHB Bank</option>
                    <option value="Hong Leong Bank">Hong Leong Bank</option>
                    <option value="AmBank">AmBank</option>
                    <option value="Alliance Bank">Alliance Bank</option>
                    <option value="Affin Bank">Affin Bank</option>
                    <option value="Bank Islam Malaysia">Bank Islam Malaysia</option>
                    <option value="Agrobank">Agrobank</option>
                    <option value="Bank Simpanan Nasional (BSN)">Bank Simpanan Nasional (BSN)</option>
                    <option value="HSBC Bank Malaysia">HSBC Bank Malaysia</option>
                    <option value="OCBC Bank (Malaysia)">OCBC Bank (Malaysia)</option>
                    <option value="Standard Chartered Bank Malaysia">Standard Chartered Bank Malaysia</option>
                    <option value="United Overseas Bank (UOB Malaysia)">United Overseas Bank (UOB Malaysia)</option>
                    <option value="Bank of China (Malaysia)">Bank of China (Malaysia)</option>
                </select>
            </div>
        </div>
    </div>

</div>  <!-- ← left-card 在这里才关闭 -->

                
                </div> <!-- /Left Column -->

                <div class="form-col">
                    <!-- Moved Account Settings to Step 1 -->
                    <div class="form-section">
                        <div class="form-section-header" style="text-transform: uppercase;">账号设置 ACCOUNT SETTINGS</div>
                        <div class="form-section-content">
                            <div class="form-grid-2">
                                <div class="form-group" id="group-add-account-type">
                                    <label for="add_account_type">账号类型 Account Type <span class="required-mark">*</span></label>
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

                                <div class="form-group" id="group-add-branch">
                                    <label for="add_branch">所属分店 Branch</label>
                                    <select id="add_branch" name="branch">
                                        <option value="">总部 / 未指定</option>
                                        <option value="j1">J1 (Midvalley Southkey)</option>
                                        <option value="j2">J2 (Paradigm Mall)</option>
                                        <option value="j3">J3 (Desa Tebrau)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                <!-- ── Permissions ── -->

                    <!-- 权限设置区块 -->
                <!-- 编辑职员专用权限布局 (复用至添加页面) -->
                <div class="form-section editUserPermLayout">
                    <div class="form-section-header" style="text-transform: uppercase;">权限管理 PERMISSION MANAGEMENT</div>
                    <div class="form-section-content">
                        <!-- 权限配置布局 (维持双列) -->
                        <div class="perm-layout-container">
                            <!-- 左侧：权限树形结构 -->
                            <div class="perm-tree-container">
                                <!-- 一级：集团架构 -->
                                <div class="perm-level-1">
                                    <div class="perm-level-1-item" data-perm="brand">
                                        <label class="perm-checkbox-label">
                                            <input type="checkbox" class="perm-l1-check" value="brand">
                                            <span class="perm-arrow">▶</span>
                                            <strong>集团架构</strong>
                                        </label>
                                    </div>
                                    <div class="perm-level-2-container" data-parent="brand">
                                        <div class="perm-level-2-item has-level-3" data-sub="kunzz_holdings">
                                            <label class="perm-checkbox-label">
                                                <input type="checkbox" class="perm-l2-check" data-parent="brand" value="kunzz_holdings">
                                                <span class="perm-arrow-sub">▶</span>
                                                <span>KUNZZ HOLDINGS SDN BHD</span>
                                            </label>
                                        </div>
                                        <div class="perm-level-2-item has-level-3" data-sub="tokyo_cuisine">
                                            <label class="perm-checkbox-label">
                                                <input type="checkbox" class="perm-l2-check" data-parent="brand" value="tokyo_cuisine">
                                                <span class="perm-arrow-sub">▶</span>
                                                <span>TOKYO JAPANESE CUISINE SDN BHD</span>
                                            </label>
                                        </div>
                                        <div class="perm-level-2-item has-level-3" data-sub="tokyo_izakaya">
                                            <label class="perm-checkbox-label">
                                                <input type="checkbox" class="perm-l2-check" data-parent="brand" value="tokyo_izakaya">
                                                <span class="perm-arrow-sub">▶</span>
                                                <span>TOKYO IZAKAYA SDN BHD</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- 一级：营收数据 -->
                                <div class="perm-level-1">
                                    <div class="perm-level-1-item" data-perm="analytics">
                                        <label class="perm-checkbox-label">
                                            <input type="checkbox" class="perm-l1-check" value="analytics">
                                            <span class="perm-arrow">▶</span>
                                            <strong>营收数据</strong>
                                        </label>
                                    </div>
                                    <div class="perm-level-2-container" data-parent="analytics">
                                        <div class="perm-level-2-item">
                                            <label class="perm-checkbox-label">
                                                <input type="checkbox" class="perm-l2-check" data-parent="analytics" value="kpi_report">
                                                <span>KPI报表</span>
                                            </label>
                                        </div>
                                        <div class="perm-level-2-item has-level-3" data-sub="kpi_upload">
                                            <label class="perm-checkbox-label">
                                                <input type="checkbox" class="perm-l2-check" data-parent="analytics" value="kpi_upload">
                                                <span class="perm-arrow-sub">▶</span>
                                                <span>数据上传</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- 一级：人事管理 -->
                                <div class="perm-level-1">
                                    <div class="perm-level-1-item" data-perm="hr">
                                        <label class="perm-checkbox-label">
                                            <input type="checkbox" class="perm-l1-check" value="hr">
                                            <span class="perm-arrow">▶</span>
                                            <strong>人事管理</strong>
                                        </label>
                                    </div>
                                    <div class="perm-level-2-container" data-parent="hr">
                                        <div class="perm-level-2-item">
                                            <label class="perm-checkbox-label">
                                                <input type="checkbox" class="perm-l2-check" data-parent="hr" value="staff_management">
                                                <span>职员管理</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- 一级：资源总库 -->
                                <div class="perm-level-1">
                                    <div class="perm-level-1-item" data-perm="resource">
                                        <label class="perm-checkbox-label">
                                            <input type="checkbox" class="perm-l1-check" value="resource">
                                            <span class="perm-arrow">▶</span>
                                            <strong>资源总库</strong>
                                        </label>
                                    </div>
                                    <div class="perm-level-2-container" data-parent="resource">
                                        <div class="perm-level-2-item has-level-3" data-sub="stock_inventory">
                                            <label class="perm-checkbox-label">
                                                <input type="checkbox" class="perm-l2-check" data-parent="resource" value="stock_inventory">
                                                <span class="perm-arrow-sub">▶</span>
                                                <span>库存</span>
                                            </label>
                                        </div>
                                        <div class="perm-level-2-item">
                                            <label class="perm-checkbox-label">
                                                <input type="checkbox" class="perm-l2-check" data-parent="resource" value="dishware">
                                                <span>碗碟</span>
                                            </label>
                                        </div>
                                        <div class="perm-level-2-item">
                                            <label class="perm-checkbox-label">
                                                <input type="checkbox" class="perm-l2-check" data-parent="resource" value="price_comparison">
                                                <span>价格对比</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- 一级：视觉管理 -->
                                <div class="perm-level-1">
                                    <div class="perm-level-1-item" data-perm="visual">
                                        <label class="perm-checkbox-label">
                                            <input type="checkbox" class="perm-l1-check" value="visual">
                                            <strong>视觉管理</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- 右侧：三级详细配置卡片 -->
                            <div class="perm-detail-card">
                                <div class="perm-detail-placeholder">
                                    <i class="fas fa-hand-pointer" style="font-size: 48px; color: #d1d5db; margin-bottom: 15px;"></i>
                                    <p style="color: #9ca3af; font-size: 14px;">点击左侧带有箭头的选项<br>查看详细配置</p>
                                </div>
                                <div class="perm-detail-content">
                                    <!-- 面板内容由JS根据 context 动态查找或初始化时存在 -->
                                    <!-- 为保证逻辑统一，这里也放入三级面板，但样式会由 .editUserPermLayout 控制 -->
                                    <!-- 集团架构 - KUNZZ HOLDINGS -->
                                    <div class="perm-level-3-panel" data-for="kunzz_holdings">
                                        <div class="perm-detail-header">
                                            <strong>KUNZZ HOLDINGS SDN BHD</strong>
                                            <button type="button" class="perm-close-btn" onclick="closeDetailPanel()">×</button>
                                        </div>
                                        <div class="perm-level-3-section">
                                            <div class="perm-section-title">页面权限</div>
                                            <label><input type="checkbox" class="perm-page-blueprint" data-brand="kunzz_holdings" value="blueprint"> 企业蓝图</label>
                                        </div>
                                    </div>
                                    <!-- TOKYO CUISINE -->
                                    <div class="perm-level-3-panel" data-for="tokyo_cuisine">
                                        <div class="perm-detail-header">
                                            <strong>TOKYO JAPANESE CUISINE SDN BHD</strong>
                                            <button type="button" class="perm-close-btn" onclick="closeDetailPanel()">×</button>
                                        </div>
                                        <div class="perm-level-3-section">
                                            <div class="perm-section-title">店面</div>
                                            <div class="perm-store-item" data-store="j1">
                                                <label class="perm-checkbox-label">
                                                    <span class="perm-arrow-store">▶</span>
                                                    <span>J1 (Midvalley Southkey)</span>
                                                </label>
                                                <div class="perm-store-content">
                                                    <div class="perm-section-title">页面权限</div>
                                                    <label><input type="checkbox" class="perm-page-schedule" data-store="j1" data-brand="tokyo_cuisine" value="schedule"> 员工排班表</label>
                                                </div>
                                            </div>
                                            <div class="perm-store-item" data-store="j2">
                                                <label class="perm-checkbox-label">
                                                    <span class="perm-arrow-store">▶</span>
                                                    <span>J2 (Paradigm Mall)</span>
                                                </label>
                                                <div class="perm-store-content">
                                                    <div class="perm-section-title">页面权限</div>
                                                    <label><input type="checkbox" class="perm-page-schedule" data-store="j2" data-brand="tokyo_cuisine" value="schedule"> 员工排班表</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- TOKYO IZAKAYA -->
                                    <div class="perm-level-3-panel" data-for="tokyo_izakaya">
                                        <div class="perm-detail-header">
                                            <strong>TOKYO IZAKAYA SDN BHD</strong>
                                            <button type="button" class="perm-close-btn" onclick="closeDetailPanel()">×</button>
                                        </div>
                                        <div class="perm-level-3-section">
                                            <div class="perm-section-title">店面</div>
                                            <div class="perm-store-item" data-store="j3">
                                                <label class="perm-checkbox-label">
                                                    <span class="perm-arrow-store">▶</span>
                                                    <span>J3 (Desa Tebrau)</span>
                                                </label>
                                                <div class="perm-store-content">
                                                    <div class="perm-section-title">页面权限</div>
                                                    <label><input type="checkbox" class="perm-page-schedule" data-store="j3" data-brand="tokyo_izakaya" value="schedule"> 员工排班表</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- KPI UPLOAD -->
                                    <div class="perm-level-3-panel" data-for="kpi_upload">
                                        <div class="perm-detail-header">
                                            <strong>数据上传</strong>
                                            <button type="button" class="perm-close-btn" onclick="closeDetailPanel()">×</button>
                                        </div>
                                        <div class="perm-level-3-section">
                                            <div class="perm-section-title">系统选项</div>
                                            <label><input type="checkbox" class="perm-upload-system" value="j1"> J1</label>
                                            <label><input type="checkbox" class="perm-upload-system" value="j2"> J2</label>
                                            <label><input type="checkbox" class="perm-upload-system" value="j3"> J3</label>
                                        </div>
                                        <div class="perm-level-3-section">
                                            <div class="perm-section-title">上传类型</div>
                                            <label><input type="checkbox" class="perm-upload-type" value="kpi"> KPI</label>
                                            <label><input type="checkbox" class="perm-upload-type" value="cost"> 成本</label>
                                        </div>
                                    </div>
                                    <!-- STOCK INVENTORY -->
                                    <div class="perm-level-3-panel" data-for="stock_inventory">
                                        <div class="perm-detail-header">
                                            <strong>库存</strong>
                                            <button type="button" class="perm-close-btn" onclick="closeDetailPanel()">×</button>
                                        </div>
                                        <div class="perm-level-3-section">
                                            <div class="perm-section-title">系统选项</div>
                                            <label><input type="checkbox" class="perm-stock-system" value="central"> 中央</label>
                                            <label><input type="checkbox" class="perm-stock-system" value="j1"> J1</label>
                                            <label><input type="checkbox" class="perm-stock-system" value="j2"> J2</label>
                                            <label><input type="checkbox" class="perm-stock-system" value="j3"> J3</label>
                                        </div>
                                        <div class="perm-level-3-section">
                                            <div class="perm-section-title">视图选项</div>
                                            <label><input type="checkbox" class="perm-stock-view" value="list"> 总库存</label>
                                            <label><input type="checkbox" class="perm-stock-view" value="records"> 进出货</label>
                                            <label><input type="checkbox" class="perm-stock-view" value="remark"> 货品备注</label>
                                            <label><input type="checkbox" class="perm-stock-view" value="product"> 货品种类</label>
                                            <label><input type="checkbox" class="perm-stock-view" value="sot"> 货品异常</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 权限验证和展示警告 -->
                        <div class="perm-warning" style="display: none; color: #dc2626; font-size: 13px; font-weight: bold; margin-top: 10px; text-align: center;">
                            <i class="fas fa-exclamation-triangle"></i> 请至少选择一项权限
                        </div>
                    </div>
                </div>
                </div> <!-- /Right Column -->

            </form>
            </div><!-- /form-scroll-area -->

            <!-- Sticky action bar -->
            <div class="page-action-bar">
                <a href="generatecode" class="btn-back-action">
                    <i class="fas fa-times"></i> 取消
                </a>
                <button type="button" id="btn-save" onclick="addNewUserAndRedirect()" class="btn-save">
                    <i class="fas fa-save"></i> 保存职员
                </button>
            </div>

        </div><!-- /add-employee-page -->
    </div><!-- /container -->

    <!-- Toast container: position:fixed, 完全脱离文档流 -->
    <div id="toast-container"></div>

    <script src="js/generatecode.js?v=<?php echo time(); ?>"></script>
    <script>
        // 避免 DOM 型 XSS：安全地转义 HTML 特殊字符
        function escapeHTML(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#39;");
        }

        document.addEventListener('DOMContentLoaded', function () {
            startSessionRefresh();

            // Position dropdown
            const accountTypeSelect = document.getElementById('add_account_type');
            if (accountTypeSelect) {
                accountTypeSelect.addEventListener('change', function () {
                    updatePositionOptions(this.value, 'add_position');
                    this.closest('.form-group').classList.remove('has-error');
                });
            }

            // Remove error on input
            document.querySelectorAll('#addUserForm input, #addUserForm select').forEach(input => {
                input.addEventListener('input', function() {
                    this.closest('.form-group')?.classList.remove('has-error');
                });
            });

            // Init permission tree
            const permContainer = document.querySelector('.editUserPermLayout');
            if (permContainer) initPermissionTreeEvents(permContainer);
        });

        window.addEventListener('beforeunload', stopSessionRefresh);

        function showInlineMessage(msg, type = 'error') {
            const container = document.getElementById('toast-container');
            if (!container) return;
            
            // 清除之前的 toast，限制最多只显示一个
            container.innerHTML = '';
            
            const icon = type === 'success' ? 'fa-check-circle'
                       : type === 'warning' ? 'fa-exclamation-triangle'
                       : 'fa-times-circle';
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `<i class="fas ${icon}"></i><span>${escapeHTML(msg)}</span>`;
            container.appendChild(toast);
            
            const delay = type === 'success' ? 3000 : 4500;
            setTimeout(() => {
                if (toast.parentNode === container) {
                    toast.classList.add('toast-out');
                    setTimeout(() => {
                        if (toast.parentNode === container) toast.remove();
                    }, 320);
                }
            }, delay);
        }

        async function addNewUserAndRedirect() {
            const formData = new FormData(document.getElementById('addUserForm'));
            const userData = {};
            for (let [key, value] of formData.entries()) userData[key] = value.trim();

            // Validate required fields
            let hasError = false;
            if (!userData.username || userData.username.split(/\s+/).filter(w=>w).length < 2) {
                document.getElementById('add_username').closest('.form-group').classList.add('has-error');
                hasError = true;
            }
            if (!userData.email) {
                document.getElementById('add_email').closest('.form-group').classList.add('has-error');
                hasError = true;
            }
            if (!userData.account_type) {
                document.getElementById('add_account_type').closest('.form-group').classList.add('has-error');
                hasError = true;
            }
            if (hasError) {
                showInlineMessage('请填写所有必填项（*）', 'error');
                return;
            }

            // Check permissions
            const permContainer = document.querySelector('.editUserPermLayout');
            const { perms, submenuPermissions, pagePermissions, brandPermissions, reportPermissions, restaurantPermissions } = extractPermissionsData(permContainer);
            const checkedAny = document.querySelectorAll('.editUserPermLayout .perm-l1-check:checked, .editUserPermLayout .perm-l2-check:checked').length;
            if (checkedAny === 0) {
                const warn = document.querySelector('.perm-warning');
                if (warn) warn.style.display = 'block';
                showInlineMessage('请至少选择一项权限', 'error');
                warn?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            const submitBtn = document.getElementById('btn-save');
            const originalHTML = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 保存中...';
            submitBtn.disabled = true;

            try {
                const response = await fetch('generatecodeapi.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'add_user',
                        permissions: perms,
                        page_permissions: pagePermissions,
                        submenu_permissions: submenuPermissions,
                        report_permissions: reportPermissions,
                        restaurant_permissions: restaurantPermissions,
                        brand_permissions: brandPermissions,
                        ...userData
                    })
                });

                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                const result = await response.json();

                if (result.success) {
                    let msg = `职员 "${result.data?.username || userData.username}" 添加成功！`;
                    if (result.data?.email_sent) {
                        msg += ` 登录信息已发送至 ${result.data.email}`;
                    } else {
                        msg += ` 临时密码：${result.data?.default_password || ''}`;
                    }
                    showInlineMessage(msg, 'success');
                    setTimeout(() => { window.location.href = 'generatecode'; }, 1500);
                } else {
                    showInlineMessage(result.message || '添加失败，请重试！', 'error');
                    submitBtn.innerHTML = originalHTML;
                    submitBtn.disabled = false;
                }
            } catch (error) {
                showInlineMessage(`网络错误：${error.message}`, 'error');
                submitBtn.innerHTML = originalHTML;
                submitBtn.disabled = false;
            }
        }
    </script>
</body>
</html>

