<?php
$system = isset($system) ? $system : 'central';
$allowed_systems = isset($allowed_systems) ? $allowed_systems : [];
$display_name = isset($display_name) ? $display_name : '中央';
?>
<div id="stockminimum-context"
    data-system="<?php echo htmlspecialchars($system, ENT_QUOTES, 'UTF-8'); ?>"
    data-allowed-systems="<?php echo htmlspecialchars(json_encode($allowed_systems), ENT_QUOTES, 'UTF-8'); ?>"
    hidden></div>

<div class="container">
    <div class="header">
        <div class="header-left">
            <h1 id="page-title">最低库存设置 — <?php echo htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8'); ?></h1>
        </div>
        <div class="header-right-group">
            <button class="btn btn-secondary" onclick="goBack()">
                <i class="fas fa-arrow-left"></i> 返回库存管理
            </button>
        </div>
    </div>

    <div class="controls-bar">
        <div class="system-tabs">
            <?php if (in_array('central', $allowed_systems, true)): ?>
            <button class="tab-btn <?php echo $system === 'central' ? 'active' : ''; ?>" data-system="central" onclick="switchSystem('central')">
                <i class="fas fa-warehouse"></i> 中央
            </button>
            <?php endif; ?>
            <?php if (in_array('j1', $allowed_systems, true)): ?>
            <button class="tab-btn <?php echo $system === 'j1' ? 'active' : ''; ?>" data-system="j1" onclick="switchSystem('j1')">
                <i class="fas fa-store"></i> J1
            </button>
            <?php endif; ?>
            <?php if (in_array('j2', $allowed_systems, true)): ?>
            <button class="tab-btn <?php echo $system === 'j2' ? 'active' : ''; ?>" data-system="j2" onclick="switchSystem('j2')">
                <i class="fas fa-store"></i> J2
            </button>
            <?php endif; ?>
            <?php if (in_array('j3', $allowed_systems, true)): ?>
            <button class="tab-btn <?php echo $system === 'j3' ? 'active' : ''; ?>" data-system="j3" onclick="switchSystem('j3')">
                <i class="fas fa-store"></i> J3
            </button>
            <?php endif; ?>
        </div>
        <div class="controls-right">
            <div class="header-search">
                <div class="smartSearchWrapper">
                    <i class="fas fa-search smartSearch-icon"></i>
                    <input type="text" id="unified-filter" class="smartSearch-input" placeholder="搜索货品名称或编号...">
                </div>
            </div>
            <button class="btn btn-warning" onclick="saveAllSettings()" id="saveAllBtn">
                <i class="fas fa-save"></i> 批量保存
            </button>
        </div>
    </div>

    <div class="table-container">
        <div class="table-header">
            <h3 id="table-title">最低库存设置 — <?php echo htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8'); ?></h3>
            <div id="table-stats">
                显示 <span id="displayed-count">0</span> 个货品
            </div>
        </div>

        <div class="table-scroll-container">
            <table class="settings-table" id="settings-table">
                <thead>
                    <tr>
                        <th>序号</th>
                        <th>货品编号</th>
                        <th>货品名称</th>
                        <th>规格</th>
                        <th>最低库存数量</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody id="settings-tbody">
                </tbody>
            </table>
        </div>
    </div>
</div>
