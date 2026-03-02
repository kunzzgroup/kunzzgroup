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
    <title>职员管理系统</title>
    
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <!-- 页面标题 -->
        <div class="header">
            <h1>职员管理系统</h1>
        </div>

        <!-- 生成代码表单 -->
        <div class="generate-form">
            
            <div id="messageArea"></div>

            <form id="generateForm">
                <form id="generateForm">
                    <div class="form-row" style="justify-content: space-between; align-items: end;">
                        <!-- 添加新职员 + 下载申请表 按钮组 -->
                        <div class="form-group" style="flex: 0 0 auto; display: flex; align-items: center; gap: 12px;">
                            <button type="button" class="btn-generate" onclick="openAddUserModal()" >
                                <i class="fas fa-user-plus"></i> 添加新职员
                            </button>
                            <button type="button" class="btn-generate" onclick="openDownloadModal()">
                                <i class="fas fa-download"></i> 下载面试表
                            </button>
                        </div>
                        
                        <div class="form-group" style="flex: 0 0 auto; position: relative; display: flex; align-items: center; gap: 10px;">
                            <div style="position: relative;">
                                <input type="text" id="searchInput" placeholder="输入英文姓名或邮箱进行搜索..."
                                    style="padding: 10px 40px 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: clamp(8px, 0.74vw, 14px);">
                                <button type="button" onclick="clearSearch()" 
                                        style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #999; cursor: pointer; font-size: 16px;"
                                        title="清除搜索">
                                    ×
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </form>
        </div>

        <!-- 代码和职员列表 -->
        <div class="table-container">
            <div class="table-title">
                职员列表
            </div>
            
            <div class="table-wrapper">
                <table id="codesTable">
                    <thead>
                        <tr>
                            <th>序号</th>
                            <th>职位</th>
                            <th>英文姓名</th>
                            <th>邮箱</th>
                            <th>联络号码</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px;">
                                <div class="loading"></div>
                                正在加载数据...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 用户权限模态框 -->
    <div id="permissionsModal" class="modal">
        <div class="modal-content" style="max-width: 1200px !important; width: 85vw !important;">
            <div class="modal-header" style="color: #ff5c00; font-size: 24px; margin-bottom: 20px; font-weight: 600;">
                <i class="fas fa-user-shield"></i> <span id="perm_modal_title">用户权限设定</span>
            </div>
            <input type="hidden" id="perm_current_user_id">
            <div class="modal-body">
                
                <!-- 权限配置布局 -->
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
                            <!-- KUNZZ HOLDINGS SDN BHD -->
                            <div class="perm-level-2-item has-level-3" data-sub="kunzz_holdings">
                                <label class="perm-checkbox-label">
                                    <input type="checkbox" class="perm-l2-check" data-parent="brand" value="kunzz_holdings">
                                    <span class="perm-arrow-sub">▶</span>
                                    <span>KUNZZ HOLDINGS SDN BHD</span>
                                </label>
                            </div>
                            
                            <!-- TOKYO JAPANESE CUISINE SDN BHD -->
                            <div class="perm-level-2-item has-level-3" data-sub="tokyo_cuisine">
                                <label class="perm-checkbox-label">
                                    <input type="checkbox" class="perm-l2-check" data-parent="brand" value="tokyo_cuisine">
                                    <span class="perm-arrow-sub">▶</span>
                                    <span>TOKYO JAPANESE CUISINE SDN BHD</span>
                                </label>
                            </div>
                            
                            <!-- TOKYO IZAKAYA SDN BHD -->
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
                        
                        <!-- 所有三级面板移到这里 -->
                        <div class="perm-detail-content" id="perm-detail-content-main">
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
                            
                            <!-- 集团架构 - TOKYO CUISINE -->
                            <div class="perm-level-3-panel" data-for="tokyo_cuisine">
                                <div class="perm-detail-header">
                                    <strong>TOKYO JAPANESE CUISINE SDN BHD</strong>
                                    <button type="button" class="perm-close-btn" onclick="closeDetailPanel()">×</button>
                                </div>
                                <div class="perm-level-3-section">
                                    <div class="perm-section-title">店面</div>
                                    <!-- J1 店面 - 可展开/收缩 -->
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
                                    <!-- J2 店面 - 可展开/收缩 -->
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
                            
                            <!-- 集团架构 - TOKYO IZAKAYA -->
                            <div class="perm-level-3-panel" data-for="tokyo_izakaya">
                                <div class="perm-detail-header">
                                    <strong>TOKYO IZAKAYA SDN BHD</strong>
                                    <button type="button" class="perm-close-btn" onclick="closeDetailPanel()">×</button>
                                </div>
                                <div class="perm-level-3-section">
                                    <div class="perm-section-title">店面</div>
                                    <!-- J3 店面 - 可展开/收缩 -->
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
                            
                            <!-- 营收数据 - 数据上传 -->
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
                            
                            <!-- 资源总库 - 库存 -->
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

                <div class="modal-buttons">
                    <button type="button" class="btn-action btn-save" onclick="savePermissions()">保存</button>
                    <button type="button" class="btn-action btn-cancel" onclick="closePermissionsModal()">取消</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 下载申请表模态框 -->
    <div id="downloadModal" class="modal">
        <div class="modal-content" style="max-width: 520px;">
            <div class="modal-header" style="color:#000000ff;">
                <i class="fas fa-download"></i> 下载面试表
            </div>
            <div class="modal-body">
                <div class="form-group" style="margin-bottom:20px;">
                    <label for="company_select" style="font-size: 14px; font-weight: 600; margin-bottom: 10px; display: block;">请选择公司/店铺</label>
                    <select id="company_select" style="width: 100%; padding: 12px; border: 2px solid #f99e00; border-radius: 8px; font-size: 14px;">
                        <option value="">请选择...</option>
                        <option value="KUNZZHOLDINGS">KUNZZHOLDINGS</option>
                        <option value="TOKYO_J1">TOKYO (J1)</option>
                        <option value="TOKYO_J2">TOKYO (J2)</option>
                        <option value="TOKYO_J3">TOKYO (J3)</option>
                    </select>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn-action btn-save" onclick="confirmDownload()">
                        <i class="fas fa-check"></i> 确认下载
                    </button>
                    <button type="button" class="btn-action btn-cancel" onclick="closeDownloadModal()">
                        <i class="fas fa-times"></i> 取消
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 添加职员模态框 -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header" style="color: #000000ff;">
                <i class="fas fa-user-plus"></i> 添加新职员
            </div>
            <div class="modal-body">
                <!-- Tab Navigation -->
                <div class="modal-tabs">
                    <button type="button" class="tab-btn active" onclick="switchModalTab('basic', this)">基本信息</button>
                    <button type="button" class="tab-btn" onclick="switchModalTab('personal', this)">个人资料</button>
                    <button type="button" class="tab-btn" onclick="switchModalTab('bank', this)">银行 & 紧急联络</button>
                    <button type="button" class="tab-btn" onclick="switchModalTab('account', this)">账号 & 权限</button>
                </div>

                <form id="addUserForm">
                    <!-- 基本信息标签页 -->
                    <div id="tab-basic" class="tab-content active">
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
                    </div>

                    <!-- 个人资料标签页 -->
                    <div id="tab-personal" class="tab-content">
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
                                            <!-- ... (options pruned for brevity) -->
                                            <option value="Malaysia" selected>Malaysia</option>
                                            <option value="China">China</option>
                                            <option value="Singapore">Singapore</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="add_race">种族</label>
                                        <select id="add_race" name="race">
                                            <option value="">请选择种族</option>
                                            <option value="Malay">Malay</option>
                                            <option value="Chinese">Chinese</option>
                                            <option value="Indian">Indian</option>
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
                    </div>

                    <!-- 银行 & 接触标签页 -->
                    <div id="tab-bank" class="tab-content">
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
                                <div class="form-group">
                                    <label for="add_bank_name">银行名称</label>
                                    <select id="add_bank_name" name="bank_name">
                                        <option value="">请选择银行</option>
                                        <option value="Maybank">Maybank</option>
                                        <option value="CIMB Bank">CIMB Bank</option>
                                        <option value="Public Bank">Public Bank</option>
                                    </select>
                                </div>
                            </div>
                        </div>
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
                    </div>

                    <!-- 账号 & 权限标签页 -->
                    <div id="tab-account" class="tab-content">
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
                                            <!-- ... (other options) -->
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
                    
                    <div class="form-section" style="margin-top: 20px;">
                        <div class="form-section-header">权限设置</div>
                        <div class="form-section-content">
                            <!-- 权限配置布局 -->
                            <div class="perm-layout-container compact-perm-layout">
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
                                            <!-- KUNZZ HOLDINGS SDN BHD -->
                                            <div class="perm-level-2-item has-level-3" data-sub="kunzz_holdings">
                                                <label class="perm-checkbox-label">
                                                    <input type="checkbox" class="perm-l2-check" data-parent="brand" value="kunzz_holdings">
                                                    <span class="perm-arrow-sub">▶</span>
                                                    <span>KUNZZ HOLDINGS SDN BHD</span>
                                                </label>
                                            </div>
                                            
                                            <!-- TOKYO JAPANESE CUISINE SDN BHD -->
                                            <div class="perm-level-2-item has-level-3" data-sub="tokyo_cuisine">
                                                <label class="perm-checkbox-label">
                                                    <input type="checkbox" class="perm-l2-check" data-parent="brand" value="tokyo_cuisine">
                                                    <span class="perm-arrow-sub">▶</span>
                                                    <span>TOKYO JAPANESE CUISINE SDN BHD</span>
                                                </label>
                                            </div>
                                            
                                            <!-- TOKYO IZAKAYA SDN BHD -->
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
                                    
                                    <!-- 所有三级面板移到这里 -->
                                    <div class="perm-detail-content" style="height: auto;">
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
                                        
                                        <!-- 集团架构 - TOKYO CUISINE -->
                                        <div class="perm-level-3-panel" data-for="tokyo_cuisine">
                                            <div class="perm-detail-header">
                                                <strong>TOKYO JAPANESE CUISINE SDN BHD</strong>
                                                <button type="button" class="perm-close-btn" onclick="closeDetailPanel()">×</button>
                                            </div>
                                            <div class="perm-level-3-section">
                                                <div class="perm-section-title">店面</div>
                                                <!-- J1 店面 - 可展开/收缩 -->
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
                                                <!-- J2 店面 - 可展开/收缩 -->
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
                                        
                                        <!-- 集团架构 - TOKYO IZAKAYA -->
                                        <div class="perm-level-3-panel" data-for="tokyo_izakaya">
                                            <div class="perm-detail-header">
                                                <strong>TOKYO IZAKAYA SDN BHD</strong>
                                                <button type="button" class="perm-close-btn" onclick="closeDetailPanel()">×</button>
                                            </div>
                                            <div class="perm-level-3-section">
                                                <div class="perm-section-title">店面</div>
                                                <!-- J3 店面 - 可展开/收缩 -->
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
                                        
                                        <!-- 营收数据 - 数据上传 -->
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
                                        
                                        <!-- 资源总库 - 库存 -->
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
                    
                    <div class="modal-buttons">
                        <button type="submit" class="btn-action btn-save">
                            添加职员
                        </button>
                        <button type="button" class="btn-action btn-cancel" onclick="closeAddUserModal()">
                            取消
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 编辑职员模态框 -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header" style="color: #f59e0b;">
                <i class="fas fa-user-edit"></i> 编辑职员信息
            </div>
            <div class="modal-body">
                <!-- Tab Navigation -->
                <div class="modal-tabs">
                    <button type="button" class="tab-btn active" onclick="switchModalTab('edit-basic', this)">基本信息</button>
                    <button type="button" class="tab-btn" onclick="switchModalTab('edit-personal', this)">个人资料</button>
                    <button type="button" class="tab-btn" onclick="switchModalTab('edit-bank', this)">银行 & 紧急联络</button>
                    <button type="button" class="tab-btn" onclick="switchModalTab('edit-account', this)">账号设置</button>
                </div>

                <form id="editUserForm">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    
                    <!-- 基本信息内容 -->
                    <div id="tab-edit-basic" class="tab-content active">
                        <div class="form-section">
                            <div class="form-section-header">基本信息</div>
                            <div class="form-section-content">
                                <div class="form-row-2col">
                                    <div class="form-group">
                                        <label for="edit_username">英文姓名 *</label>
                                        <input type="text" id="edit_username" name="username" required maxlength="50">
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_username_cn">中文姓名</label>
                                        <input type="text" id="edit_username_cn" name="username_cn" maxlength="100">
                                    </div>
                                </div>
                                <div class="form-row-2col">
                                    <div class="form-group">
                                        <label for="edit_nickname">昵称</label>
                                        <input type="text" id="edit_nickname" name="nickname" maxlength="50">
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_email">邮箱 *</label>
                                        <input type="email" id="edit_email" name="email" required maxlength="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 个人资料内容 -->
                    <div id="tab-edit-personal" class="tab-content">
                        <div class="form-section">
                            <div class="form-section-header">个人资料</div>
                            <div class="form-section-content">
                                <div class="form-row-3col">
                                    <div class="form-group">
                                        <label for="edit_ic_number">身份证号码</label>
                                        <input type="text" id="edit_ic_number" name="ic_number" maxlength="20">
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_phone_number">联络号码</label>
                                        <input type="tel" id="edit_phone_number" name="phone_number" maxlength="20">
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_date_of_birth">出生日期</label>
                                        <input type="date" id="edit_date_of_birth" name="date_of_birth">
                                    </div>
                                </div>
                                <div class="form-row-3col">
                                    <div class="form-group">
                                        <label for="edit_gender">性别</label>
                                        <select id="edit_gender" name="gender">
                                            <option value="">请选择</option>
                                            <option value="male">男</option>
                                            <option value="female">女</option>
                                            <option value="other">其他</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_nationality">国籍</label>
                                        <select id="edit_nationality" name="nationality">
                                            <option value="">请选择国籍</option>
                                            <option value="Malaysia">Malaysia</option>
                                            <option value="China">China</option>
                                            <option value="Singapore">Singapore</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_race">种族</label>
                                        <select id="edit_race" name="race">
                                            <option value="">请选择种族</option>
                                            <option value="Malay">Malay</option>
                                            <option value="Chinese">Chinese</option>
                                            <option value="Indian">Indian</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="edit_home_address">住址</label>
                                    <textarea id="edit_home_address" name="home_address" rows="2" maxlength="255"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 银行 & 接触内容 -->
                    <div id="tab-edit-bank" class="tab-content">
                        <div class="form-section">
                            <div class="form-section-header">银行信息</div>
                            <div class="form-section-content">
                                <div class="form-row-2col">
                                    <div class="form-group">
                                        <label for="edit_bank_account_holder_en">银行账户持有人</label>
                                        <input type="text" id="edit_bank_account_holder_en" name="bank_account_holder_en" maxlength="50">
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_bank_account">银行账号</label>
                                        <input type="text" id="edit_bank_account" name="bank_account" maxlength="30">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="edit_bank_name">银行名称</label>
                                    <select id="edit_bank_name" name="bank_name">
                                        <option value="">请选择银行</option>
                                        <option value="Maybank">Maybank</option>
                                        <option value="CIMB Bank">CIMB Bank</option>
                                        <option value="Public Bank">Public Bank</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-section">
                            <div class="form-section-header">紧急联络人</div>
                            <div class="form-section-content">
                                <div class="form-row-2col">
                                    <div class="form-group">
                                        <label for="edit_emergency_contact_name">紧急联系人</label>
                                        <input type="text" id="edit_emergency_contact_name" name="emergency_contact_name" maxlength="100">
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_emergency_phone_number">紧急联系人电话</label>
                                        <input type="tel" id="edit_emergency_phone_number" name="emergency_phone_number" maxlength="20">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 账号设置内容 -->
                    <div id="tab-edit-account" class="tab-content">
                        <div class="form-section">
                            <div class="form-section-header">账号设置</div>
                            <div class="form-section-content">
                                <div class="form-row-2col">
                                    <div class="form-group">
                                        <label for="edit_account_type">账号类型 *</label>
                                        <select id="edit_account_type" name="account_type" required>
                                            <option value="">请选择账号类型</option>
                                            <option value="special">特殊 (Special)</option>
                                            <option value="hr">人事部 (HR)</option>
                                            <!-- ... (other options) -->
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_position">职位</label>
                                        <select id="edit_position" name="position">
                                            <option value="">请先选择账号类型</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-buttons">
                        <button type="submit" class="btn-action btn-save">
                            <i class="fas fa-save"></i> 保存修改
                        </button>
                        <button type="button" class="btn-action btn-cancel" onclick="closeEditUserModal()">
                            <i class="fas fa-times"></i> 取消
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 回到顶部按钮 -->
    <button class="back-to-top" id="back-to-top-btn" onclick="scrollToTop()" title="回到顶部">
        <i class="fas fa-chevron-up"></i>
    </button>

    <div class="toast-container" id="toast-container">
        <!-- 动态通知内容 -->
    </div>
    <script src="js/generatecode.js?v=<?php echo time(); ?>"></script>
</body>
</html>
