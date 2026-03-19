<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('resource', 'dishware');

// 防止浏览器/代理缓存，确保修改能立刻生效
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

// 在输出任何内容前完成 session 与认证检查，避免未勾选「记住我」时白屏
session_start();
define('SESSION_TIMEOUT_DISHWARE', 60);

if (isset($_SESSION['user_id'])) {
    if (
        isset($_SESSION['last_activity']) &&
        (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT_DISHWARE) &&
        (!isset($_COOKIE['remember_token']) || $_COOKIE['remember_token'] !== '1')
    ) {
        session_unset();
        session_destroy();
        setcookie('user_id', '', time() - 60, "/");
        setcookie('username', '', time() - 60, "/");
        setcookie('position', '', time() - 60, "/");
        setcookie('remember_token', '', time() - 60, "/");
        header("Location: ../frontend/login.html");
        exit();
    }
    $_SESSION['last_activity'] = time();
} elseif (
    isset($_COOKIE['user_id']) &&
    isset($_COOKIE['username']) &&
    isset($_COOKIE['remember_token']) &&
    $_COOKIE['remember_token'] === '1'
) {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['username'] = $_COOKIE['username'];
    $_SESSION['position'] = isset($_COOKIE['position']) ? $_COOKIE['position'] : null;
    $_SESSION['account_type'] = isset($_COOKIE['account_type']) ? $_COOKIE['account_type'] : null;
    $_SESSION['last_activity'] = time();
} else {
    header("Location: ../frontend/login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>碗碟库存管理 - 库存管理系统</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/backend/css/dishware_stock.css?v=<?php echo time(); ?>">
   
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <div>
                <h1 id="page-title">总库存</h1>
            </div>
            <div class="controls">
                <div class="view-selector">
                    <button class="selector-button" onclick="toggleViewSelector()">
                        <span id="current-view">总库存</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="selector-dropdown" id="view-selector-dropdown">
                        <div class="dropdown-item active" onclick="switchPage('stock')">总库存</div>
                        <div class="dropdown-item" onclick="switchPage('j1')">破损记录</div>
                        <div class="dropdown-item" onclick="switchPage('transfer')">碗碟转卖</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Toast Notifications -->
        <div class="toast-container" id="toast-container">
            <!-- 动态通知内容 -->
        </div>
        
        
         <!-- 统一顶部行 -->
         <div class="unified-header-row">
             <div class="header-center-section">
                <div class="header-search">
                    <span style="font-size: clamp(8px, 0.74vw, 14px); font-weight: 600; color: #000000ff; white-space: nowrap;">搜索</span>
                    <input type="text" id="unified-filter" class="unified-search-input" 
                        placeholder="搜索碗碟名称、编号或分类...">
                </div>
                
                <!-- 破损记录页：月份选择 + 快速选择（仅 j1/j2/j3 时显示） -->
                <div id="break-date-filter" class="break-date-filter" style="display: none;">
                    <div class="break-month-picker">
                        <span style="font-size: clamp(8px, 0.74vw, 14px); font-weight: 600; color: #000000ff; white-space: nowrap;"><i class="fas fa-calendar" style="margin-right: 4px;"></i>选择年份和月份</span>
                        <div class="break-month-picker-inner" style="position: relative;">
                            <button type="button" id="break-month-picker-trigger" class="break-month-picker-trigger" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-calendar" style="margin-right: 6px;"></i>
                                <span id="break-month-picker-text">选择年份和月份</span>
                                <i class="fas fa-chevron-down" style="margin-left: 6px;"></i>
                            </button>
                            <div id="break-month-picker-popup" class="break-month-picker-popup" style="display: none;" role="dialog" aria-label="选择年份和月份">
                                <div class="break-picker-year-row">
                                    <button type="button" class="break-picker-year-btn" id="break-picker-year-prev" aria-label="上一年"><i class="fas fa-chevron-up"></i></button>
                                    <span id="break-picker-year-display">2026</span>
                                    <button type="button" class="break-picker-year-btn" id="break-picker-year-next" aria-label="下一年"><i class="fas fa-chevron-down"></i></button>
                                </div>
                                <div class="break-picker-month-grid" id="break-picker-month-grid"></div>
                                <div class="break-picker-footer"><button type="button" id="break-picker-clear" class="break-picker-clear-btn">无</button></div>
                            </div>
                        </div>
                    </div>
                    <div class="break-quick-select">
                        <span style="font-size: clamp(8px, 0.74vw, 14px); font-weight: 600; color: #000000ff; white-space: nowrap;"><i class="fas fa-clock" style="margin-right: 4px;"></i>快速选择</span>
                        <div class="dropdown" style="position: relative;">
                            <button type="button" class="btn btn-warning break-quick-select-btn" id="break-quick-select-btn" onclick="toggleBreakQuickSelectDropdown()" style="padding: clamp(4px, 0.42vw, 8px) clamp(10px, 0.83vw, 16px); font-size: clamp(8px, 0.74vw, 14px);">
                                <i class="fas fa-calendar-alt"></i>
                                <span id="break-quick-select-text">时段</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="dropdown-menu break-quick-select-dropdown" id="break-quick-select-dropdown" style="display: none; position: absolute; top: 100%; left: 0; z-index: 1000; min-width: 120px; background: #fff; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-top: 4px;">
                                <button type="button" class="dropdown-item" onclick="selectBreakQuickRange('month1')">1月</button>
                                <button type="button" class="dropdown-item" onclick="selectBreakQuickRange('month2')">2月</button>
                                <button type="button" class="dropdown-item" onclick="selectBreakQuickRange('month3')">3月</button>
                                <button type="button" class="dropdown-item" onclick="selectBreakQuickRange('month4')">4月</button>
                                <button type="button" class="dropdown-item" onclick="selectBreakQuickRange('month5')">5月</button>
                                <button type="button" class="dropdown-item" onclick="selectBreakQuickRange('month6')">6月</button>
                                <button type="button" class="dropdown-item" onclick="selectBreakQuickRange('month7')">7月</button>
                                <button type="button" class="dropdown-item" onclick="selectBreakQuickRange('month8')">8月</button>
                                <button type="button" class="dropdown-item" onclick="selectBreakQuickRange('month9')">9月</button>
                                <button type="button" class="dropdown-item" onclick="selectBreakQuickRange('month10')">10月</button>
                                <button type="button" class="dropdown-item" onclick="selectBreakQuickRange('month11')">11月</button>
                                <button type="button" class="dropdown-item" onclick="selectBreakQuickRange('month12')">12月</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="category-filter">
                    <span style="font-size: clamp(8px, 0.74vw, 14px); font-weight: 600; color: #000000ff; white-space: nowrap;">分类</span>
                    <select id="category-filter" class="unified-search-input">
                        <option value="">全部分类</option>
                        <option value="AG">AG</option>
                        <option value="CU">CU</option>
                        <option value="DN">DN</option>
                        <option value="DR">DR</option>
                        <option value="IP">IP</option>
                        <option value="MA">MA</option>
                        <option value="ME">ME</option>
                        <option value="MU">MU</option>
                        <option value="OM">OM</option>
                        <option value="OT">OT</option>
                        <option value="SA">SA</option>
                        <option value="SK">SK</option>
                        <option value="SU">SU</option>
                        <option value="SAR">SAR</option>
                        <option value="SER">SER</option>
                        <option value="SET">SET</option>
                        <option value="TA">TA</option>
                        <option value="TE">TE</option>
                        <option value="WAN">WAN</option>
                        <option value="YA">YA</option>
                        <option value="用具">用具</option>
                    </select>
                </div>
                <div class="item-type-filter" id="item-type-filter-wrap" style="display: none;">
                    <span style="font-size: clamp(8px, 0.74vw, 14px); font-weight: 600; color: #000000ff; white-space: nowrap;">类型</span>
                    <select id="item-type-filter" class="unified-search-input">
                        <option value="all">全部</option>
                        <option value="single">单品</option>
                        <option value="set">套装</option>
                    </select>
                </div>
            </div>
            
            <div class="header-right-section">
                <button class="btn btn-info" onclick="openRestaurantModal()" id="manage-restaurants-btn" style="background-color: #17a2b8; border-color: #17a2b8; color: white;">
                    <i class="fas fa-store" style="color: white;"></i>
                    管理餐厅店面
                </button>
                
                <button class="btn btn-success" onclick="openAddModal()" id="add-dishware-btn">
                    <i class="fas fa-plus"></i>
                    添加碗碟
                </button>
                
                <button class="btn btn-warning" onclick="exportData()">
                    <i class="fas fa-download"></i>
                    导出数据
                </button>
                
                <div class="header-stats">
                    <span>显示记录: <span class="stat-value" id="displayed-records">0</span></span>
                    <span>总记录: <span class="stat-value" id="total-count">0</span></span>
                </div>
            </div>
        </div>
        

        <!-- 页面内容区域 -->
        <div id="page-content">
            <!-- 库存管理页面 -->
            <div id="stock-page" class="page-content">
                <!-- 视图切换按钮 -->
                <!-- <div style="display: flex; gap: 8px; margin-bottom: 16px; padding: 0 20px;">
                    <button class="btn" id="stock-view-btn" onclick="switchStockView('dishware')" style="background: #f99e00; color: white; border: 1px solid #ddd; padding: clamp(4px, 0.42vw, 8px) clamp(12px, 1.25vw, 24px); font-size: clamp(10px, 0.83vw, 16px);">
                        碗碟
                    </button>
                    <button class="btn" id="sets-view-btn" onclick="switchStockView('sets')" style="background: white; color: #333; border: 1px solid #ddd; padding: clamp(4px, 0.42vw, 8px) clamp(12px, 1.25vw, 24px); font-size: clamp(10px, 0.83vw, 16px);">
                        套装
                    </button>
                </div> -->
                
                <!-- 碗碟视图 -->
                <div id="dishware-view">
                    <!-- 单个分类或搜索结果的表格容器 -->
                    <div class="table-container" id="single-table-container">
                        <div class="table-scroll-container">
                            <table class="stock-table" id="stock-table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>照片</th>
                                        <th>产品名称</th>
                                        <th>编号</th>
                                        <th>分类</th>
                                        <th>尺寸</th>
                                        <th>单价</th>
                                        <th>文化楼</th>
                                        <th>中央</th>
                                        <th>J1</th>
                                        <th>J2</th>
                                        <th>J3</th>
                                        <th>总数</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody id="stock-tbody">
                                    <!-- Dynamic content -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- 全部分类的容器（按分类分组显示） -->
                    <div id="categories-container" class="categories-container">
                        <!-- 动态生成分类容器 -->
                    </div>
                </div>
                
                <!-- 套装视图 -->
                <div id="sets-view" style="display: none;">
                    <div class="table-container">
                        <div class="table-scroll-container">
                            <table class="stock-table" id="sets-table">
                                <thead>
                                    <tr>
                                        <th>序号</th>
                                        <th>套装名称</th>
                                        <th>套装编号</th>
                                        <th>包含项目</th>
                                        <th>单价 (RM)</th>
                                        <th>文华楼</th>
                                        <th>中央</th>
                                        <th>J1</th>
                                        <th>J2</th>
                                        <th>J3</th>
                                        <th>总库存</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody id="sets-tbody">
                                    <!-- 动态填充 -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 合并的破损记录页面（J1、J2、J3左右排列） -->
            <div id="j1-page" class="page-content" style="display: none;">
                <div class="table-scroll-container" style="overflow-x: auto; overflow-y: visible; width: 100%;">
                    <div id="break-records-container" class="break-records-container">
                        <!-- 动态生成三个店铺的表格 -->
                    </div>
                </div>
            </div>
            
            <!-- J2和J3页面指向同一个合并页面 -->
            <div id="j2-page" class="page-content" style="display: none;">
                <div class="table-scroll-container" style="overflow-x: auto; overflow-y: visible; width: 100%;">
                    <div id="break-records-container-j2" class="break-records-container">
                        <!-- 动态生成三个店铺的表格 -->
                    </div>
                </div>
            </div>
            
            <div id="j3-page" class="page-content" style="display: none;">
                <div class="table-scroll-container" style="overflow-x: auto; overflow-y: visible; width: 100%;">
                    <div id="break-records-container-j3" class="break-records-container">
                        <!-- 动态生成三个店铺的表格 -->
                    </div>
                </div>
            </div>

            <!-- 碗碟转卖页面 -->
            <div id="transfer-page" class="page-content" style="display: none;">
                <div class="table-scroll-container" style="overflow-x: auto; overflow-y: visible; width: 100%;">
                    <div id="transfer-records-container" class="break-records-container">
                        <!-- 动态生成餐厅的转卖记录表格 -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 添加碗碟模态框 -->
    <div id="addModal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2 class="modal-title">添加碗碟信息</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="add-form" enctype="multipart/form-data">
                <div class="modal-form" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                    <div class="form-group">
                        <label class="required">碗碟名称</label>
                        <input type="text" id="add-product-name" name="product_name" required>
                    </div>
                    <div class="form-group">
                        <label class="required">分类</label>
                        <select id="add-category" name="category" required>
                            <option value="">请选择分类</option>
                            <option value="AG">AG</option>
                            <option value="CU">CU</option>
                            <option value="DN">DN</option>
                            <option value="DR">DR</option>
                            <option value="IP">IP</option>
                            <option value="MA">MA</option>
                            <option value="ME">ME</option>
                            <option value="MU">MU</option>
                            <option value="OM">OM</option>
                            <option value="OT">OT</option>
                            <option value="SA">SA</option>
                            <option value="SK">SK</option>
                            <option value="SU">SU</option>
                            <option value="SAR">SAR</option>
                            <option value="SER">SER</option>
                            <option value="SET">SET</option>
                            <option value="TA">TA</option>
                            <option value="TE">TE </option>
                            <option value="WAN">WAN</option>
                            <option value="YA">YA</option>
                            <option value="用具">用具</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>产品编号</label>
                        <input type="text" id="add-code-number" name="code_number" placeholder="001" maxlength="10">
                    </div>
                    <div class="form-group">
                        <label>尺寸规格</label>
                        <input type="text" id="add-size" name="size" placeholder="例如：直径15cm">
                    </div>
                    <div class="form-group">
                        <label>单价 (RM)</label>
                        <input type="number" id="add-unit-price" name="unit_price" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>照片上传</label>
                        <div class="photo-upload-area" onclick="document.getElementById('add-photo').click()">
                            <div class="photo-upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="photo-upload-text">点击上传照片或拖拽照片到此处</div>
                            <div class="photo-upload-hint">支持 JPG, PNG, GIF 格式，最大 5MB</div>
                            <img id="add-photo-preview" class="photo-preview" style="display: none;">
                        </div>
                        <input type="file" id="add-photo" name="photo" class="file-input" accept="image/*">
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">取消</button>
                    <button type="submit" class="btn btn-primary" id="add-submit-btn">
                        <i class="fas fa-save"></i>
                        保存碗碟信息
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 编辑碗碟信息模态框 -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">编辑碗碟信息</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="edit-form" enctype="multipart/form-data">
                <div class="modal-form" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                    <div class="form-group">
                        <label class="required">碗碟名称</label>
                        <input type="text" id="edit-product-name" name="product_name" required>
                    </div>
                    <div class="form-group">
                        <label class="required">分类</label>
                        <select id="edit-category" name="category" required>
                            <option value="">请选择分类</option>
                            <option value="AG">AG</option>
                            <option value="CU">CU</option>
                            <option value="DN">DN</option>
                            <option value="DR">DR</option>
                            <option value="IP">IP</option>
                            <option value="MA">MA</option>
                            <option value="ME">ME</option>
                            <option value="MU">MU</option>
                            <option value="OM">OM</option>
                            <option value="OT">OT</option>
                            <option value="SA">SA</option>
                            <option value="SK">SK</option>
                            <option value="SU">SU</option>
                            <option value="SAR">SAR</option>
                            <option value="SER">SER</option>
                            <option value="SET">SET</option>
                            <option value="TA">TA</option>
                            <option value="TE">TE</option>
                            <option value="WAN">WAN</option>
                            <option value="YA">YA</option>
                            <option value="用具">用具</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>产品编号</label>
                        <input type="text" id="edit-code-number" name="code_number" placeholder="001" maxlength="10">
                    </div>
                    <div class="form-group">
                        <label>尺寸规格</label>
                        <input type="text" id="edit-size" name="size" placeholder="例如：直径15cm">
                    </div>
                    <div class="form-group">
                        <label class="required">单价 (RM)</label>
                        <input type="number" id="edit-unit-price" name="unit_price" step="0.01" min="0" required placeholder="0.00">
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>库存数量</label>
                        <div class="quantity-row" id="edit-restaurant-quantities">
                            <!-- 动态生成餐厅店面输入框 -->
                        </div>
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>照片上传</label>
                        <div class="photo-upload-area" onclick="document.getElementById('edit-photo').click()">
                            <div class="photo-upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="photo-upload-text">点击上传新照片或拖拽照片到此处</div>
                            <div class="photo-upload-hint">支持 JPG, PNG, GIF 格式，最大 5MB</div>
                            <img id="edit-photo-preview" class="photo-preview" style="display: none;">
                        </div>
                        <input type="file" id="edit-photo" name="photo" class="file-input" accept="image/*">
                        <input type="hidden" id="delete-photo-flag" name="delete_photo" value="0">
                        <input type="hidden" id="edit-current-photo-path" name="current_photo_path" value="">
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>套装设置</label>
                        <div id="set-settings-container" style="border: 1px solid #ddd; border-radius: 8px; padding: 16px; background: #f9fafb;">
                            <div style="margin-bottom: 12px;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; flex-wrap: wrap;">
                                    <span style="font-weight: 600; white-space: nowrap; flex-shrink: 0;">当前套装成员：</span>
                                    <span id="current-set-members" style="color: #666;">暂无</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <label style="font-weight: 600; margin: 0; white-space: nowrap; flex-shrink: 0;">添加套装成员：</label>
                                    <select id="set-member-select" style="flex: 1; min-width: 180px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                        <option value="">请选择要加入套装的碗碟</option>
                                    </select>
                                    <button type="button" onclick="addSetMember()" class="btn btn-primary" style="padding: 8px 16px; white-space: nowrap; flex-shrink: 0;">
                                        <i class="fas fa-plus"></i> 添加
                                    </button>
                                </div>
                            </div>
                            <div id="selected-set-members" style="margin-top: 12px;">
                                <!-- 动态显示已选择的套装成员 -->
                            </div>
                            <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #ddd;">
                                <button type="button" onclick="removeFromSet()" class="btn btn-secondary" style="padding: 8px 16px;">
                                    <i class="fas fa-unlink"></i> 从套装中移除
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">取消</button>
                    <button type="submit" class="btn btn-primary" id="edit-submit-btn">
                        <i class="fas fa-save"></i>
                        保存更改
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 添加破损记录模态框 -->
    <div id="damageModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2 class="modal-title" id="damage-modal-title">添加破损记录</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="damage-form">
                <div class="modal-form" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                    <div class="form-group">
                        <label class="required">破损日期</label>
                        <input type="date" id="damage-date" name="break_date" required>
                    </div>
                    <div class="form-group">
                        <label>产品编号</label>
                        <select id="damage-code-select" name="code_number" onchange="handleDamageCodeChange(this)">
                            <option value="">请选择编号</option>
                            <!-- 动态填充选项 -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="required">产品名称</label>
                        <select id="damage-product-select" name="product_name" required onchange="handleDamageProductChange(this)">
                            <option value="">请选择产品</option>
                            <!-- 动态填充选项 -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="required">破损数量</label>
                        <input type="number" id="damage-quantity" name="break_quantity" min="1" required onchange="calculateDamageTotal()">
                    </div>
                    <div class="form-group">
                        <label>单价 (RM)</label>
                        <input type="number" id="damage-unit-price" name="unit_price" step="0.01" min="0" readonly style="background: #f3f4f6;" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>总价 (RM)</label>
                        <input type="number" id="damage-total-price" name="total_price" step="0.01" readonly style="background: #f3f4f6;" placeholder="0.00">
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">取消</button>
                    <button type="submit" class="btn btn-primary" id="damage-submit-btn">
                        <i class="fas fa-save"></i>
                        保存破损记录
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 添加破损记录行数选择弹窗 -->
    <div id="break-rows-modal" class="modal-overlay">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3 class="modal-title">新增破损记录</h3>
                <button class="modal-close" onclick="closeBreakRowsModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666;">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="break-rows-count" style="display: block; margin-bottom: 8px; font-weight: 600;">要创建的行数 *</label>
                    <input type="number" id="break-rows-count" class="form-input" min="1" max="50" value="1" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                <button class="btn btn-secondary" onclick="closeBreakRowsModal()">取消</button>
                <button class="btn btn-primary" onclick="createMultipleBreakRows()">
                    <i class="fas fa-plus"></i>
                    创建记录
                </button>
            </div>
        </div>
    </div>

    <!-- 餐厅店面管理模态框 -->
    <div id="restaurantModal" class="modal">
        <div class="modal-content" style="max-width: 900px;">
            <div class="modal-header">
                <h2 class="modal-title">管理餐厅店面</h2>
                <span class="close" onclick="closeRestaurantModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div style="margin-bottom: 20px;">
                    <button class="btn btn-success" onclick="openAddRestaurantModal()">
                        <i class="fas fa-plus"></i>
                        添加餐厅店面
                    </button>
                </div>
                <div class="table-container">
                    <div class="table-scroll-container">
                        <table class="stock-table" id="restaurants-table">
                            <thead>
                                <tr>
                                    <th>序号</th>
                                    <th>餐厅店面名称</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody id="restaurants-tbody">
                                <!-- 动态填充 -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeRestaurantModal()">关闭</button>
            </div>
        </div>
    </div>

    <!-- 添加/编辑餐厅店面模态框 -->
    <div id="addRestaurantModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2 class="modal-title" id="restaurant-modal-title">添加餐厅店面</h2>
                <span class="close" onclick="closeRestaurantModal()">&times;</span>
            </div>
            <form id="restaurant-form">
                <input type="hidden" id="restaurant-id" name="id">
                <div class="modal-form" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="required">餐厅店面名称</label>
                        <input type="text" id="restaurant-name" name="name" required placeholder="例如：新店">
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeRestaurantModal()">取消</button>
                    <button type="submit" class="btn btn-primary" id="restaurant-submit-btn">
                        <i class="fas fa-save"></i>
                        保存
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 转卖记录行数选择弹窗 -->
    <div id="transfer-rows-modal" class="modal-overlay">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3 class="modal-title">新增转卖记录</h3>
                <button class="modal-close" onclick="closeTransferRowsModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666;">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="transfer-rows-count" style="display: block; margin-bottom: 8px; font-weight: 600;">要创建的行数 *</label>
                    <input type="number" id="transfer-rows-count" class="form-input" min="1" max="50" value="1" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                <button class="btn btn-secondary" onclick="closeTransferRowsModal()">取消</button>
                <button class="btn btn-primary" onclick="createMultipleTransferRows()">
                    <i class="fas fa-plus"></i>
                    创建记录
                </button>
            </div>
        </div>
    </div>

    <!-- 套装管理模态框 -->
    <div id="setModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="set-modal-title">添加套装</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="set-form">
                <input type="hidden" name="set_id" id="set-id">
                <div class="modal-form">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="required">套装名称</label>
                        <input type="text" id="set-name" name="set_name" required placeholder="例如：日式茶具套装">
                    </div>
                    <div class="form-group">
                        <label>套装价格 (RM)</label>
                        <input type="number" id="set-price" name="set_price" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="required">选择碗碟</label>
                        <div id="set-items-container">
                            <div class="set-item-row">
                                <select name="dishware_id[]" class="dishware-select" required>
                                    <option value="">请选择碗碟</option>
                                </select>
                                <button type="button" onclick="removeSetItem(this)" class="btn-remove">删除</button>
                            </div>
                        </div>
                        <button type="button" onclick="addSetItem()" class="btn-add-item">+ 添加碗碟</button>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">取消</button>
                    <button type="submit" class="btn btn-primary" id="set-submit-btn">
                        <i class="fas fa-save"></i>
                        保存套装
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script src="js/dishware_stock.js?v=<?php echo time(); ?>"></script>
</body>
</html>
