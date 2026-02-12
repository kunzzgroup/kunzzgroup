
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
    reader.onload = function (e) {
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