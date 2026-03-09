<?php
/**
 * TOKYO JAPANESE CUISINE — Menu Management Dashboard
 * Redesigned to match the current backend style and integrate sidebar.php
 */
session_start();

// Authentication guard
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management – TOKYO JAPANESE</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@300;400;500;700&family=Cinzel:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/menu_dashboard.css?v=<?php echo time(); ?>">
    <link rel="icon" type="image/png" href="../images/logo.png">
</head>
<body class="has-sidebar">
    
    <!-- Sidebar Integration -->
    <?php include 'sidebar.php'; ?>

    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <h1>🍽️ 菜单管理</h1>
            <p>管理 Grand Menu 及 Sushi Menu 的分类与菜品信息</p>
            <div class="breadcrumb">视觉管理 › 菜单管理 › <span id="bc-tab">Grand Menu</span></div>
        </div>

        <!-- Dashboard Tabs -->
        <div class="menu-tabs">
            <button class="menu-tab active" id="tab-grand" onclick="switchTab(this,'grand')">
                <div class="tab-icon">🍽️</div>
                <div class="tab-label">
                    <div class="t1">Grand Menu</div>
                    <div class="t2" id="tab-grand-count">正在统计...</div>
                </div>
            </button>
            <button class="menu-tab" id="tab-sushi" onclick="switchTab(this,'sushi')">
                <div class="tab-icon">🍣</div>
                <div class="tab-label">
                    <div class="t1">Sushi Menu</div>
                    <div class="t2" id="tab-sushi-count">正在统计...</div>
                </div>
            </button>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            
            <!-- Category Sidebar -->
            <div class="cat-panel">
                <div class="cat-panel-header">
                    <span>📂 分类管理</span>
                    <button class="btn-add-cat" onclick="openAddCatModal()" title="新增分类">+</button>
                </div>
                <div class="cat-list" id="cat-list">
                    <!-- Categories will be rendered here by JS -->
                    <div class="skeleton" style="height:40px;margin:10px;border-radius:8px"></div>
                    <div class="skeleton" style="height:40px;margin:10px;border-radius:8px"></div>
                </div>
            </div>

            <!-- Main Management Area -->
            <div class="right-panel">
                
                <!-- Add New Item Card -->
                <div class="card">
                    <div class="card-deco-corner corner-tl"></div><div class="card-deco-corner corner-tr"></div><div class="card-deco-corner corner-bl"></div><div class="card-deco-corner corner-br"></div>
                    <div class="card-header">
                        <h3>➕ 新增菜单项目</h3>
                        <div style="font-size:12px;color:var(--muted)">当前位置：<span id="cur-cat-label" style="color:var(--gold);font-weight:700">加载中...</span></div>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="fg">
                                <label>菜单编号</label>
                                <input type="text" id="inp-code" placeholder="例如: H01">
                            </div>
                            <div class="fg">
                                <label>英文名称 *</label>
                                <input type="text" id="inp-name" placeholder="例如: ATSUYAKI TAMAGO">
                            </div>
                            <div class="fg">
                                <label>中文名称</label>
                                <input type="text" id="inp-cn" placeholder="例如: 厚玉子烧">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="fg">
                                <label>食材/描述</label>
                                <input type="text" id="inp-desc" placeholder="例如: Japanese Omelette">
                            </div>
                            <div class="fg">
                                <label>价格 (RM)</label>
                                <input type="text" id="inp-price" placeholder="13.90">
                            </div>
                            <div class="fg">
                                <label>展示状态</label>
                                <select id="inp-status">
                                    <option value="published">✅ 已发布</option>
                                    <option value="draft">📝 草稿</option>
                                </select>
                            </div>
                        </div>

                        <!-- Image Upload Area -->
                        <div class="upload-zone" id="drop-zone">
                            <input type="file" id="file-input" accept="image/*">
                            <span class="uz-icon">🖼️</span>
                            <p>将菜品图片拖拽至此，或 <strong>点击选择文件</strong></p>
                            <span class="hint">建议尺寸 800x600 · 支持 JPG, PNG, WEBP · 最大 5MB</span>
                        </div>

                        <!-- Preview -->
                        <div id="preview-wrap" style="display:none">
                            <img id="preview-img" src="">
                            <div id="preview-name" style="font-size:12px;color:var(--gold);margin-top:10px"></div>
                        </div>

                        <div style="display:flex;gap:12px;margin-top:20px">
                            <button class="btn btn-primary" id="btn-submit" onclick="handleAdd()">📤 上传并保存</button>
                            <button class="btn btn-secondary" onclick="resetForm()">↩️ 重置表单</button>
                        </div>
                    </div>
                </div>

                <!-- Items List Table -->
                <div class="card">
                    <div class="card-deco-corner corner-tl"></div><div class="card-deco-corner corner-tr"></div><div class="card-deco-corner corner-bl"></div><div class="card-deco-corner corner-br"></div>
                    <div class="card-header">
                        <h3>📋 <span id="table-title">记录列表</span> <span class="badge" id="item-count" style="margin-left:10px;font-size:12px;padding:2px 10px;background:var(--cream);border-radius:20px">0 项</span></h3>
                        <div class="search-box">
                            <input type="text" id="search-input" placeholder="🔍 搜索项目名称..." oninput="handleSearch(this.value)" style="padding:8px 15px;border-radius:10px;border:1px solid var(--border);font-size:13px;width:220px">
                        </div>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th width="45%">项目详情</th>
                                    <th width="12%">价格</th>
                                    <th width="12%">资源状态</th>
                                    <th width="12%">发布状态</th>
                                    <th width="10%">日期</th>
                                    <th width="9%">操作</th>
                                </tr>
                            </thead>
                            <tbody id="menu-tbody">
                                <!-- Items will be rendered here by JS -->
                                <tr>
                                    <td colspan="6" style="text-align:center;padding:60px;color:var(--muted)">正在初始化系统...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Modals & Toasts -->
    <div class="toast" id="toast">
        <span id="toast-msg"></span>
    </div>

    <!-- Delete Item Modal -->
    <div class="modal-overlay" id="del-modal">
        <div class="modal">
            <h3>🗑️ 确认删除</h3>
            <p>确定要永久移除 <strong id="del-item-name"></strong> 吗？<br>此操作将不可撤销。</p>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('del-modal')">取消</button>
                <button class="btn btn-danger" id="btn-confirm-del" onclick="doDelete()">立即删除</button>
            </div>
        </div>
    </div>

    <!-- Delete Category Modal -->
    <div class="modal-overlay" id="del-cat-modal">
        <div class="modal">
            <h3>🗑️ 删除分类</h3>
            <p>确认删除分类 <strong id="del-cat-name"></strong> 吗？<br>该分类下的所有菜品信息都将被同步清除。</p>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('del-cat-modal')">取消</button>
                <button class="btn btn-danger" onclick="doDeleteCat()">确认删除</button>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal-overlay" id="add-cat-modal">
        <div class="modal">
            <h3>📂 新增分类</h3>
            <div class="fg">
                <label>分类名称 *</label>
                <input type="text" id="new-cat-name" placeholder="例如: House Special">
            </div>
            <div class="fg" style="margin-top:15px">
                <label>排序权重</label>
                <input type="number" id="new-cat-order" value="0">
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('add-cat-modal')">关闭</button>
                <button class="btn btn-primary" onclick="doAddCat()">确认创建</button>
            </div>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div class="modal-overlay" id="edit-modal">
        <div class="modal" style="max-width:600px">
            <h3>✏️ 编辑项目信息</h3>
            <input type="hidden" id="edit-id">
            <div id="edit-thumb-wrap" style="text-align:center;margin-bottom:20px"></div>
            <div class="form-row">
                <div class="fg">
                    <label>菜单编号</label>
                    <input type="text" id="edit-code">
                </div>
                <div class="fg">
                    <label>英文名称 *</label>
                    <input type="text" id="edit-name">
                </div>
            </div>
            <div class="form-row">
                <div class="fg">
                    <label>中文名称</label>
                    <input type="text" id="edit-cn">
                </div>
                <div class="fg">
                    <label>食材描述</label>
                    <input type="text" id="edit-desc">
                </div>
            </div>
            <div class="form-row">
                <div class="fg">
                    <label>价格 (RM)</label>
                    <input type="text" id="edit-price">
                </div>
                <div class="fg">
                    <label>状态</label>
                    <select id="edit-status">
                        <option value="published">✅ 已发布</option>
                        <option value="draft">📝 草稿</option>
                    </select>
                </div>
            </div>
            <div class="fg">
                <label>更新图片 (留空则保留原图)</label>
                <input type="file" id="edit-image" accept="image/*" style="border:1.5px solid var(--border);padding:8px;width:100%;border-radius:10px;font-size:12px">
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('edit-modal')">取消</button>
                <button class="btn btn-primary" id="btn-edit-save" onclick="doEdit()">💾 保存更改</button>
            </div>
        </div>
    </div>

    <!-- Custom JS -->
    <script src="js/menu_dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>
