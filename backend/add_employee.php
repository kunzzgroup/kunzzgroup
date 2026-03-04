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
        /* ── 书本双页布局 ── */
        body {
            background: #e8e0d8;
        }

        /* 主包裹：紧贴 sidebar 下方，填满剩余高度 */
        .add-employee-page {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 0px);
            padding: 0;
            margin: 0;
            overflow: hidden;
        }

        /* 顶部标题栏 */
        .page-header-bar {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 28px;
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            flex-shrink: 0;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
            z-index: 10;
        }
        .page-header-bar h1 {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }
        .page-header-bar .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 14px;
            background: #f3f4f6;
            color: #374151;
            border: none;
            border-radius: 7px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            transition: background .18s;
        }
        .page-header-bar .back-btn:hover { background: #e5e7eb; }

        /* 消息区域 */
        #messageArea {
            padding: 0 28px;
            flex-shrink: 0;
        }

        /* ── 书本容器 ── */
        .book-wrapper {
            flex: 1;
            display: flex;
            min-height: 0;           /* 让 flex 子项可收缩 */
            padding: 16px 20px;
            gap: 0;
        }

        /* 书页公共样式 */
        .book-page {
            flex: 1;
            background: #fff;
            overflow-y: auto;
            padding: 22px 24px 24px;
            /* 独立滚动 */
            scrollbar-width: thin;
            scrollbar-color: #f97316 #fde8d8;
        }
        .book-page::-webkit-scrollbar { width: 5px; }
        .book-page::-webkit-scrollbar-track { background: #fde8d8; }
        .book-page::-webkit-scrollbar-thumb { background: #f97316; border-radius: 4px; }

        /* 左页：圆角左侧，阴影朝右 */
        .book-page-left {
            border-radius: 4px 0 0 4px;
            box-shadow: -3px 0 10px rgba(0,0,0,.08), 6px 0 18px rgba(0,0,0,.10);
        }

        /* 书脊分割线 */
        .book-spine {
            width: 6px;
            background: linear-gradient(to right, #d97706, #f97316, #d97706);
            flex-shrink: 0;
            box-shadow: 0 0 8px rgba(0,0,0,.25);
        }

        /* 右页：圆角右侧 */
        .book-page-right {
            border-radius: 0 4px 4px 0;
            box-shadow: 6px 0 10px rgba(0,0,0,.08), -3px 0 18px rgba(0,0,0,.10);
        }

        /* 页码标签 */
        .page-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #9ca3af;
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f3f4f6;
        }

        /* ── 底部动作栏 ── */
        .page-action-bar {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding: 10px 28px;
            background: #fff;
            border-top: 1px solid #e5e7eb;
            flex-shrink: 0;
            box-shadow: 0 -2px 8px rgba(0,0,0,.05);
        }
        .page-action-bar .btn-action {
            padding: 10px 28px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all .18s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }
        .page-action-bar .btn-save {
            background: #f97316;
            color: #fff;
            box-shadow: 0 3px 10px rgba(249,115,22,.30);
        }
        .page-action-bar .btn-save:hover { background: #ea6b0a; transform: translateY(-1px); }
        .page-action-bar .btn-cancel {
            background: #f3f4f6;
            color: #374151;
        }
        .page-action-bar .btn-cancel:hover { background: #e5e7eb; }

        /* ── form-section 小调整，让两页内间距更紧凑 ── */
        .book-page .form-section {
            margin-bottom: 16px;
        }
        .book-page .form-section-header {
            font-size: 12px;
            padding: 6px 12px;
        }
        .book-page .form-section-content {
            padding: 12px 14px;
        }
        .book-page .form-group label {
            font-size: 12px;
        }
        .book-page .form-group input,
        .book-page .form-group select,
        .book-page .form-group textarea {
            padding: 7px 10px;
            font-size: 13px;
        }

        /* 权限树在右页铺满 */
        .book-page-right .perm-tree-container {
            max-height: none !important;
        }
        .book-page-right .perm-layout-container {
            display: block;
        }

        /* ── 表单字段样式覆盖（页面级，替代原 #addUserModal 选择器） ── */
        .book-page .form-group {
            margin-bottom: 0;
            display: flex;
            flex-direction: column;
        }
        .book-page .form-group label {
            display: block;
            margin-bottom: 4px;
            color: #1f2937;
            font-weight: 600;
            font-size: 12px;
        }
        .book-page .form-group input,
        .book-page .form-group select,
        .book-page .form-group textarea {
            width: 100%;
            padding: 6px 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 13px;
            font-family: inherit;
            background: #fff;
            color: #111827;
            transition: border-color .18s;
            box-sizing: border-box;
            height: auto;
        }
        .book-page .form-group input:focus,
        .book-page .form-group select:focus,
        .book-page .form-group textarea:focus {
            outline: none;
            border-color: #f97316;
            box-shadow: 0 0 0 3px rgba(249,115,22,.15);
        }
        .book-page .form-group textarea {
            min-height: 54px;
            resize: vertical;
        }
        /* Override the aggressive height !important from generatecode.css */
        .book-page .form-group input[type="text"],
        .book-page .form-group input[type="email"],
        .book-page .form-group input[type="tel"],
        .book-page .form-group input[type="date"],
        .book-page .form-group select {
            height: 34px !important;
            padding: 0 10px !important;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="add-employee-page">

            <!-- 顶部标题栏 -->
            <div class="page-header-bar">
                <a href="generatecode.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> 返回职员列表
                </a>
                <h1><i class="fas fa-user-plus"></i> 添加新职员</h1>
            </div>

            <!-- 消息提示区 -->
            <div id="messageArea"></div>

            <!-- 书本双页布局 -->
            <form id="addUserForm">
            <div class="book-wrapper">

                <!-- ── 左页：员工信息 ── -->
                <div class="book-page book-page-left">
                    <div class="page-label">📋 员工信息</div>
                <!-- 基本信息区块 -->
                <div class="form-section">
                    <div class="form-section-header">基本信息</div>
                    <div class="form-section-content">
                        <div class="form-row-2col">
                            <div class="form-group">
                                <label for="add_username">英文姓名 *</label>
                                <input type="text" id="add_username" name="username" required maxlength="50">
                            </div>
                            
                            <div class="form-group">
                                <label for="add_username_cn">中文姓名</label>
                                <input type="text" id="add_username_cn" name="username_cn" maxlength="100">
                            </div>
                        </div>
                        
                        <div class="form-row-2col">
                            <div class="form-group">
                                <label for="add_nickname">昵称</label>
                                <input type="text" id="add_nickname" name="nickname" maxlength="50">
                            </div>
                            
                            <div class="form-group">
                                <label for="add_email">邮箱 *</label>
                                <input type="email" id="add_email" name="email" required maxlength="100">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 个人资料区块 -->
                <div class="form-section">
                    <div class="form-section-header">个人资料</div>
                    <div class="form-section-content">
                        <div class="form-row-3col">
                            <div class="form-group">
                                <label for="add_ic_number">身份证号码</label>
                                <input type="text" id="add_ic_number" name="ic_number" maxlength="20">
                            </div>
                            
                            <div class="form-group">
                                <label for="add_phone_number">联络号码</label>
                                <input type="tel" id="add_phone_number" name="phone_number" maxlength="20">
                            </div>
                            
                            <div class="form-group">
                                <label for="add_date_of_birth">出生日期</label>
                                <input type="date" id="add_date_of_birth" name="date_of_birth">
                            </div>
                        </div>
                        
                        <div class="form-row-3col">
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
                        
                        <div class="form-row-1col">
                            <div class="form-group">
                                <label for="add_home_address">住址</label>
                                <textarea id="add_home_address" name="home_address" rows="2" maxlength="255"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 银行信息区块 -->
                <div class="form-section">
                    <div class="form-section-header">银行信息</div>
                    <div class="form-section-content">
                        <div class="form-row-2col">
                            <div class="form-group">
                                <label for="add_bank_account_holder_en">银行账户持有人</label>
                                <input type="text" id="add_bank_account_holder_en" name="bank_account_holder_en" maxlength="50">
                            </div>
                            
                            <div class="form-group">
                                <label for="add_bank_account">银行账号</label>
                                <input type="text" id="add_bank_account" name="bank_account" maxlength="30">
                            </div>
                        </div>
                        
                        <div class="form-row-1col">
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
                </div>

                <!-- 紧急联络人区块 -->
                <div class="form-section">
                    <div class="form-section-header">紧急联络人</div>
                    <div class="form-section-content">
                        <div class="form-row-2col">
                            <div class="form-group">
                                <label for="add_emergency_contact_name">紧急联系人</label>
                                <input type="text" id="add_emergency_contact_name" name="emergency_contact_name" maxlength="100">
                            </div>
                            
                            <div class="form-group">
                                <label for="add_emergency_phone_number">紧急联系人电话</label>
                                <input type="tel" id="add_emergency_phone_number" name="emergency_phone_number" maxlength="20">
                            </div>
                        </div>
                    </div>
                </div>
                </div><!-- /book-page-left -->

                <!-- ── 书脊 ── -->
                <div class="book-spine"></div>

                <!-- ── 右页：账号 & 权限 ── -->
                <div class="book-page book-page-right">
                    <div class="page-label">⚙️ 账号 & 权限</div>
                <!-- 账号设置区块 -->
                <div class="form-section">
                    <div class="form-section-header">账号设置</div>
                    <div class="form-section-content">
                        <div class="form-row-2col">
                            <div class="form-group">
                                <label for="add_account_type">账号类型 *</label>
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
                            </div>
                            
                            <div class="form-group">
                                <label for="add_position">职位</label>
                                <select id="add_position" name="position">
                                    <option value="">请先选择账号类型</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 权限设置区块 -->
                <div class="form-section addUserPermLayout" style="margin-top: 20px;">
                    <div class="form-section-header">权限设置</div>
                    <div class="form-section-content">
                        <!-- 权限配置布局 -->
                        <div class="perm-layout-container">
                            <!-- 权限树形结构 (单列设计) -->
                            <div class="perm-tree-container" style="max-height: none;">
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
                                        <!-- KUNZZ HOLDINGS SDN BHD -->
                                        <div class="perm-level-2-item has-level-3" data-sub="kunzz_holdings">
                                            <label class="perm-checkbox-label">
                                                <input type="checkbox" class="perm-l2-check" data-parent="brand" value="kunzz_holdings">
                                                <span class="perm-arrow-sub">▶</span>
                                                <span>KUNZZ HOLDINGS SDN BHD</span>
                                            </label>
                                            <!-- 内联三级面板 -->
                                            <div class="perm-level-3-panel-inline" data-for="kunzz_holdings">
                                                <div class="perm-level-3-section">
                                                    <div class="perm-section-title">页面权限</div>
                                                    <label><input type="checkbox" class="perm-page-blueprint" data-brand="kunzz_holdings" value="blueprint"> 企业蓝图</label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- TOKYO JAPANESE CUISINE SDN BHD -->
                                        <div class="perm-level-2-item has-level-3" data-sub="tokyo_cuisine">
                                            <label class="perm-checkbox-label">
                                                <input type="checkbox" class="perm-l2-check" data-parent="brand" value="tokyo_cuisine">
                                                <span class="perm-arrow-sub">▶</span>
                                                <span>TOKYO JAPANESE CUISINE SDN BHD</span>
                                            </label>
                                            <!-- 内联三级面板 -->
                                            <div class="perm-level-3-panel-inline" data-for="tokyo_cuisine">
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
                                        </div>
                                        
                                        <!-- TOKYO IZAKAYA SDN BHD -->
                                        <div class="perm-level-2-item has-level-3" data-sub="tokyo_izakaya">
                                            <label class="perm-checkbox-label">
                                                <input type="checkbox" class="perm-l2-check" data-parent="brand" value="tokyo_izakaya">
                                                <span class="perm-arrow-sub">▶</span>
                                                <span>TOKYO IZAKAYA SDN BHD</span>
                                            </label>
                                            <!-- 内联三级面板 -->
                                            <div class="perm-level-3-panel-inline" data-for="tokyo_izakaya">
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
                                            <!-- 内联三级面板 -->
                                            <div class="perm-level-3-panel-inline" data-for="kpi_upload">
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
                                            <!-- 内联三级面板 -->
                                            <div class="perm-level-3-panel-inline" data-for="stock_inventory">
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
                        </div>
                        
                        <!-- 权限验证和展示警告 -->
                        <div class="perm-warning" style="display: none; color: #dc2626; font-size: 13px; font-weight: bold; margin-top: 10px; text-align: center;">
                            <i class="fas fa-exclamation-triangle"></i> 请至少选择一项权限
                        </div>
                    </div>
                </div>
                </div><!-- /book-page-right -->

            </div><!-- /book-wrapper -->
            </form>

            <!-- 底部操作按钮 -->
            <div class="page-action-bar">
                <button type="submit" form="addUserForm" class="btn-action btn-save">
                    <i class="fas fa-user-plus"></i> 添加职员
                </button>
                <a href="generatecode.php" class="btn-action btn-cancel">
                    取消
                </a>
            </div>

        </div><!-- /add-employee-page -->
    </div><!-- /container -->

    <script src="js/generatecode.js?v=<?php echo time(); ?>"></script>
    <script>
        // 页面专用初始化：add_employee 页面无需加载表格数据
        document.addEventListener('DOMContentLoaded', function () {
            startSessionRefresh();

            // 初始化账号类型 → 职位联动
            const accountTypeSelect = document.getElementById('add_account_type');
            if (accountTypeSelect) {
                accountTypeSelect.addEventListener('change', function () {
                    updatePositionOptions(this.value, 'add_position');
                });
            }

            // 初始化权限树
            const permContainer = document.querySelector('.addUserPermLayout');
            if (permContainer) {
                initPermissionTreeEvents(permContainer);
            }

            // 表单提交
            const form = document.getElementById('addUserForm');
            if (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    addNewUserAndRedirect();
                });
            }
        });

        window.addEventListener('beforeunload', function () {
            stopSessionRefresh();
        });

        // 覆盖 addNewUser 使其在成功后跳转回 generatecode.php
        async function addNewUserAndRedirect() {
            const formData = new FormData(document.getElementById('addUserForm'));
            const userData = {};

            for (let [key, value] of formData.entries()) {
                userData[key] = value.trim();
            }

            // 验证必填字段
            if (!userData.username || !userData.email || !userData.account_type) {
                showMessage('请填写所有必填字段（英文姓名、邮箱、账号类型）！', 'error');
                return;
            }

            // 验证字段格式
            const fieldsToValidate = ['username', 'username_cn', 'email'];
            for (let field of fieldsToValidate) {
                if (userData[field] && !validateField(field, userData[field])) {
                    const fieldNames = {
                        'username': '英文姓名需要至少两个单词',
                        'username_cn': '中文姓名需要至少两个字',
                        'email': '邮箱格式不正确'
                    };
                    showMessage(fieldNames[field], 'error');
                    return;
                }
            }

            // 提取权限数据
            const modal = document.getElementById('addUserForm').closest('.add-employee-page') || document.body;
            const checkedBoxes = Array.from(document.querySelectorAll('.addUserPermLayout .perm-l1-check, .addUserPermLayout .perm-l2-check, .addUserPermLayout .perm-stock-system, .addUserPermLayout .perm-stock-view, .addUserPermLayout .perm-upload-system, .addUserPermLayout .perm-upload-type, .addUserPermLayout .perm-page-schedule, .addUserPermLayout .perm-page-blueprint'))
                .filter(cb => cb.checked && !cb.disabled);

            if (checkedBoxes.length === 0) {
                const warningDiv = document.querySelector('.perm-warning');
                if (warningDiv) warningDiv.style.display = 'block';
                showMessage('请至少选择一项用户权限', 'error');
                return;
            }

            const permContainer = document.querySelector('.addUserPermLayout');
            const { perms, submenuPermissions, pagePermissions, brandPermissions, reportPermissions, restaurantPermissions } = extractPermissionsData(permContainer);

            // 显示加载状态
            const submitBtn = document.querySelector('#addUserForm .btn-save');
            const originalHTML = submitBtn.innerHTML;
            submitBtn.innerHTML = '<div class="loading"></div>添加中...';
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

                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                const result = await response.json();

                if (result.success) {
                    // 成功后跳转回职员列表
                    window.location.href = 'generatecode.php';
                } else {
                    showMessage(result.message || '添加失败，请重试！', 'error');
                    submitBtn.innerHTML = originalHTML;
                    submitBtn.disabled = false;
                }
            } catch (error) {
                showMessage(`网络错误：${error.message}`, 'error');
                submitBtn.innerHTML = originalHTML;
                submitBtn.disabled = false;
            }
        }
    </script>
</body>
</html>
