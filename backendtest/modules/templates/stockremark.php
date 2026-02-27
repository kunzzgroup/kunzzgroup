<body>
    <?php include CORE_PATH . '/sidebar.php'; ?>
    <div class="container">
        <div class="header">
            <div>
                <h1>货品备注</h1>
            </div>
            <div class="controls">
                <div class="view-selector">
                    <button class="selector-button" onclick="toggleViewSelector()">
                        <span id="current-view">货品备注</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="selector-dropdown" id="view-selector-dropdown">
                        <div class="dropdown-item" onclick="switchView('list')">总库存</div>
                        <div class="dropdown-item" onclick="switchView('records')">进出货</div>
                        <div class="dropdown-item active" onclick="switchView('remark')">货品备注</div>
                        <div class="dropdown-item" onclick="switchView('product')">货品种类</div>
                        <div class="dropdown-item" onclick="switchView('sot')">货品异常</div>
                    </div>
                </div>
                <button class="selector-button" style="justify-content: center;">
                    <span id="current-stock-type">中央</span>
                </button>
            </div>
        </div>
        
        <!-- Alert Messages -->
        <div id="alert-container"></div>
        
        <!-- 搜索和过滤区域 -->
        <div class="filter-section">
            <div style="display: flex; align-items: end; gap: 26px; margin-bottom: clamp(10px, 0.83vw, 16px);">
                <div class="filter-group" style="flex: 1;">
                    <label for="product-filter">搜索货品</label>
                    <input type="text" id="product-filter" class="filter-input" placeholder="输入关键字搜索...">
                </div>
            </div>
        </div>

        <!-- 货品列表 -->
        <div id="products-container">
            <!-- Dynamic content -->
        </div>
    </div>

    <div class="toast-container" id="toast-container">
    <!-- 动态通知内容 -->
    </div>

    <!-- 回到顶部按钮 -->
    <button class="back-to-top" id="back-to-top-btn" onclick="scrollToTop()" title="回到顶部">
        <i class="fas fa-chevron-up"></i>
    </button>
