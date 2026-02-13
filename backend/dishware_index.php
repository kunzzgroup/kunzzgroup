<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>碗碟库存管理系统</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/backend/css/dishware_index.css">
</head>
<body>
    <a href="/" class="back-button">
        <i class="fas fa-arrow-left"></i>
        返回首页
    </a>
    
    <div class="container">
        <div class="header">
            <h1>碗碟库存管理系统</h1>
            <p>高效管理餐厅碗碟库存，支持多地点库存跟踪，实时更新库存状态</p>
        </div>
        
        <div class="card-grid">
            <div class="card">
                <i class="fas fa-upload card-icon"></i>
                <h3>碗碟信息上传</h3>
                <p>上传碗碟照片、设置尺寸价格、选择分类，支持单个上传和批量CSV导入</p>
                <a href="dishware_upload" class="card-btn">
                    <i class="fas fa-plus"></i>
                    开始上传
                </a>
            </div>
            
            <div class="card">
                <i class="fas fa-warehouse card-icon"></i>
                <h3>库存管理</h3>
                <p>查看所有碗碟信息，管理各地点库存数量，实时计算总库存价值</p>
                <a href="dishware_stock" class="card-btn">
                    <i class="fas fa-chart-bar"></i>
                    管理库存
                </a>
            </div>
        </div>
        
        <div class="features">
            <h2>系统功能特性</h2>
            <div class="features-grid">
                <div class="feature-item">
                    <i class="fas fa-camera feature-icon"></i>
                    <div class="feature-content">
                        <h4>照片管理</h4>
                        <p>支持上传碗碟照片，拖拽上传，自动预览功能</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <i class="fas fa-tags feature-icon"></i>
                    <div class="feature-content">
                        <h4>分类管理</h4>
                        <p>19种分类：AG、CU、DN、DR、IP、MA、ME、MU、OM、OT、SA、SU、SAR、SER、SET、TA、TE、WAN、YA</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <i class="fas fa-map-marker-alt feature-icon"></i>
                    <div class="feature-content">
                        <h4>多地点库存</h4>
                        <p>支持文化楼、中央、J1、J2、J3五个地点的库存管理</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <i class="fas fa-calculator feature-icon"></i>
                    <div class="feature-content">
                        <h4>自动计算</h4>
                        <p>自动计算总数量和总价值，实时更新库存状态</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <i class="fas fa-file-csv feature-icon"></i>
                    <div class="feature-content">
                        <h4>批量导入</h4>
                        <p>支持CSV文件批量导入碗碟信息，提高工作效率</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <i class="fas fa-search feature-icon"></i>
                    <div class="feature-content">
                        <h4>智能搜索</h4>
                        <p>支持按产品名称、编号、分类进行快速搜索和筛选</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <i class="fas fa-download feature-icon"></i>
                    <div class="feature-content">
                        <h4>数据导出</h4>
                        <p>支持导出CSV格式的库存报表，便于数据分析和备份</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <i class="fas fa-mobile-alt feature-icon"></i>
                    <div class="feature-content">
                        <h4>响应式设计</h4>
                        <p>完美适配桌面端和移动端，随时随地管理库存</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="/backend/js/dishware_index.js"></script>
</body>
</html>
