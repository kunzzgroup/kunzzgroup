<?php
$system = isset($system) ? $system : 'central';
$system_names = [
    'central' => '中央',
    'j1' => 'J1',
    'j2' => 'J2',
    'j3' => 'J3',
];
$display_name = isset($system_names[$system]) ? $system_names[$system] : '中央';
?>
<div class="container">
    <div class="header">
        <div>
            <h1>货品备注</h1>
        </div>
        <div class="controls">
            <div class="header-search">
                <div class="smartSearchWrapper">
                    <i class="fas fa-search smartSearch-icon"></i>
                    <input type="text" id="product-filter" class="smartSearch-input" placeholder="输入关键字搜索...">
                </div>
            </div>
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
                <span id="current-stock-type"><?php echo htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8'); ?></span>
            </button>
        </div>
    </div>

    <div id="alert-container"></div>

    <div id="products-container"></div>
</div>

<div class="toast-container" id="toast-container"></div>

<button class="back-to-top" id="back-to-top-btn" onclick="scrollToTop()" title="回到顶部">
    <i class="fas fa-chevron-up"></i>
</button>
