<?php
require_once __DIR__ . '/permission_guard.php';
requirePermission('resource', 'dishware');

if (!headers_sent()) {
    header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate");
    header("Pragma: no-cache");
    header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>碗碟信息上传 - 库存管理系统</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/dishware_upload.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>碗碟信息上传</h1>
            </div>
            <div>
                <button class="back-button" onclick="goBack()">
                    <i class="fas fa-arrow-left"></i>
                    返回上一页
                </button>
            </div>
        </div>
        
        <!-- Alert Messages -->
        <div id="alert-container"></div>
        
        <!-- 单个上传表单 -->
        <div class="form-container">
            <h2 class="form-title">单个碗碟信息上传</h2>
            
            <form id="single-upload-form" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="product_name" class="required">碗碟名称</label>
                        <input type="text" id="product_name" name="product_name" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="code_number">产品编号</label>
                        <input type="text" id="code_number" name="code_number" class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label for="category" class="required">分类</label>
                        <select id="category" name="category" class="form-select" required>
                            <option value="">请选择分类</option>
                            <option value="AG">AG - 餐具</option>
                            <option value="CU">CU - 杯子</option>
                            <option value="DN">DN - 碟子</option>
                            <option value="DR">DR - 盘子</option>
                            <option value="IP">IP - 盘子</option>
                            <option value="MA">MA - 餐具</option>
                            <option value="ME">ME - 餐具</option>
                            <option value="MU">MU - 餐具</option>
                            <option value="OM">OM - 其他</option>
                            <option value="OT">OT - 其他</option>
                            <option value="SA">SA - 餐具</option>
                            <option value="SU">SU - 餐具</option>
                            <option value="SAR">SAR - 餐具</option>
                            <option value="SER">SER - 餐具</option>
                            <option value="SET">SET - 套装</option>
                            <option value="TA">TA - 餐具</option>
                            <option value="TE">TE - 餐具</option>
                            <option value="WAN">WAN - 碗</option>
                            <option value="YA">YA - 餐具</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="size">尺寸规格</label>
                        <input type="text" id="size" name="size" class="form-input" placeholder="例如：直径15cm">
                    </div>
                    
                    <div class="form-group">
                        <label for="unit_price" class="required">单价 (RM)</label>
                        <input type="number" id="unit_price" name="unit_price" class="form-input" step="0.01" min="0" required placeholder="0.00">
                    </div>
                    
                    <div class="form-group full-width">
                        <label>照片上传</label>
                        <div class="photo-upload-area" onclick="document.getElementById('photo').click()">
                            <div class="photo-upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="photo-upload-text">点击上传照片或拖拽照片到此处</div>
                            <div class="photo-upload-hint">支持 JPG, PNG, GIF 格式，最大 5MB</div>
                            <img id="photo-preview" class="photo-preview" style="display: none;">
                        </div>
                        <input type="file" id="photo" name="photo" class="file-input" accept="image/*">
                    </div>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="btn btn-primary" id="submit-btn">
                        <i class="fas fa-save"></i>
                        保存碗碟信息
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="resetForm()">
                        <i class="fas fa-refresh"></i>
                        重置表单
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script src="js/dishware_upload.js?v=<?php echo time(); ?>"></script>
</body>
</html>
