<?php
require_once dirname(__DIR__) . '/core/init.php';
require_once CORE_PATH . '/session_check.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <link rel="icon" type="image/png" href="../images/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>碗碟信息上传 - 库存管理系统</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/dishware_upload.css">
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
                        <input type="number" id="unit_price" name="unit_price" class="form-input" step="0.01" min="0" required>
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

    <script>
        // API 配置
        const API_BASE_URL = 'dishware_api.php';
        
        // 应用状态
        let isUploading = false;
        let selectedPhoto = null;

        // 初始化应用
        function initApp() {
            setupEventListeners();
        }

        // 设置事件监听器
        function setupEventListeners() {
            // 照片上传
            const photoInput = document.getElementById('photo');
            const photoUploadArea = document.querySelector('.photo-upload-area');
            
            photoInput.addEventListener('change', handlePhotoSelect);
            
            // 拖拽上传
            photoUploadArea.addEventListener('dragover', handleDragOver);
            photoUploadArea.addEventListener('dragleave', handleDragLeave);
            photoUploadArea.addEventListener('drop', handleDrop);
            
            
            // 表单提交
            document.getElementById('single-upload-form').addEventListener('submit', handleFormSubmit);
        }

        // 返回上一页
        function goBack() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '/';
            }
        }

        // 处理照片选择
        function handlePhotoSelect(event) {
            const file = event.target.files[0];
            if (file) {
                selectedPhoto = file;
                previewPhoto(file);
            }
        }

        // 预览照片
        function previewPhoto(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('photo-preview');
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }

        // 拖拽处理
        function handleDragOver(event) {
            event.preventDefault();
            event.currentTarget.classList.add('dragover');
        }

        function handleDragLeave(event) {
            event.currentTarget.classList.remove('dragover');
        }

        function handleDrop(event) {
            event.preventDefault();
            event.currentTarget.classList.remove('dragover');
            
            const files = event.dataTransfer.files;
            if (files.length > 0) {
                selectedPhoto = files[0];
                document.getElementById('photo').files = files;
                previewPhoto(files[0]);
            }
        }


        // 处理表单提交
        async function handleFormSubmit(event) {
            event.preventDefault();
            
            if (isUploading) return;
            
            const formData = new FormData();
            const form = event.target;
            
            // 添加表单数据
            formData.append('action', 'add');
            formData.append('product_name', form.product_name.value);
            formData.append('code_number', form.code_number.value);
            formData.append('category', form.category.value);
            formData.append('size', form.size.value);
            formData.append('unit_price', form.unit_price.value);
            
            // 如果有照片，先上传照片
            if (selectedPhoto) {
                try {
                    const photoFormData = new FormData();
                    photoFormData.append('action', 'upload_photo');
                    photoFormData.append('photo', selectedPhoto);
                    
                    const photoResponse = await fetch(API_BASE_URL, {
                        method: 'POST',
                        body: photoFormData
                    });
                    
                    const photoResult = await photoResponse.json();
                    
                    if (photoResult.success) {
                        formData.append('photo_path', photoResult.data.photo_path);
                    } else {
                        showAlert('照片上传失败：' + photoResult.message, 'error');
                        return;
                    }
                } catch (error) {
                    showAlert('照片上传失败：' + error.message, 'error');
                    return;
                }
            }
            
            // 提交碗碟信息
            try {
                isUploading = true;
                setLoadingState(true);
                
                const response = await fetch(API_BASE_URL, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert('碗碟信息保存成功！', 'success');
                    resetForm();
                } else {
                    showAlert('保存失败：' + result.message, 'error');
                }
                
            } catch (error) {
                showAlert('网络错误：' + error.message, 'error');
            } finally {
                isUploading = false;
                setLoadingState(false);
            }
        }


        // 重置表单
        function resetForm() {
            document.getElementById('single-upload-form').reset();
            selectedPhoto = null;
            document.getElementById('photo-preview').style.display = 'none';
            showAlert('表单已重置', 'info');
        }

        // 设置加载状态
        function setLoadingState(loading) {
            const button = document.getElementById('submit-btn');
            
            if (loading) {
                button.disabled = true;
                button.innerHTML = '<div class="loading"></div> 处理中...';
            } else {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-save"></i> 保存碗碟信息';
            }
        }

        // 显示提示信息
        function showAlert(message, type = 'success') {
            const alertContainer = document.getElementById('alert-container');
            const alertClass = type === 'error' ? 'alert-error' : type === 'info' ? 'alert-info' : 'alert-success';
            const iconClass = type === 'error' ? 'fa-exclamation-circle' : type === 'info' ? 'fa-info-circle' : 'fa-check-circle';
            
            const alertElement = document.createElement('div');
            alertElement.className = `alert ${alertClass}`;
            alertElement.innerHTML = `
                <i class="fas ${iconClass}"></i>
                <span>${message}</span>
            `;
            
            alertContainer.appendChild(alertElement);
            
            setTimeout(() => {
                alertElement.remove();
            }, 5000);
        }

        // 页面加载完成后初始化
        document.addEventListener('DOMContentLoaded', initApp);
    </script>
</body>
</html>
