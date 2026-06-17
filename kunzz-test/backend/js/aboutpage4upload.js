// 年份切换功能
function showYear(year) {
    // 隐藏所有内容
    document.querySelectorAll('.timeline-content').forEach(content => {
        content.classList.remove('active');
    });

    // 移除所有标签的active状态
    document.querySelectorAll('.year-tab').forEach(tab => {
        tab.classList.remove('active');
    });

    // 显示选中年份的内容
    const targetContent = document.getElementById('content-' + year);
    if (targetContent) {
        targetContent.classList.add('active');
    }

    // 激活选中的标签
    if (event && event.target) {
        event.target.classList.add('active');
    }
}

// 新增记录模态框
function showAddRecordModal() {
    document.getElementById('addRecordModal').style.display = 'flex';
}

function hideAddRecordModal() {
    document.getElementById('addRecordModal').style.display = 'none';
}

// 确认删除记录
// 依赖全局变量 isEnglish，由模板文件定义
function confirmDeleteRecord(recordId) {
    const message = (typeof isEnglish !== 'undefined' && isEnglish) ?
        `Are you sure you want to delete this record? This action cannot be undone!` :
        `确定要删除这个记录吗？此操作不可撤销！`;

    if (confirm(message)) {
        const form = document.createElement('form');
        form.method = 'post';
        form.innerHTML = `
            <input type="hidden" name="delete_record" value="1">
            <input type="hidden" name="record_id" value="${recordId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// 点击模态框外部关闭
document.addEventListener('click', function (e) {
    const modal = document.getElementById('addRecordModal');
    if (e.target === modal) {
        hideAddRecordModal();
    }
});

// 照片上传成功后刷新图片显示
function refreshImageDisplayByRecord(recordId) {
    const imageElement = document.querySelector(`.entry-container[data-record-id="${recordId}"] .preview-image`);
    if (imageElement) {
        const currentSrc = imageElement.src;
        // 只有当src不包含时间戳参数或者为了刷新需要加新的时间戳
        const newSrc = currentSrc.split('?')[0] + '?v=' + Date.now();
        imageElement.src = newSrc;
    }
}

// 监听照片上传表单提交
document.querySelectorAll('form[enctype="multipart/form-data"]').forEach(form => {
    form.addEventListener('submit', function (e) {
        const recordInput = this.querySelector('input[name="record_id"]');
        if (recordInput) {
            const recordId = recordInput.value;
            // 延迟刷新，等待服务器处理完成
            setTimeout(() => {
                refreshImageDisplayByRecord(recordId);
            }, 1000);
        }
    });
});

// 重置表单
function resetForm(year) {
    const form = document.querySelector(`#content-${year} .content-form form`);
    if (!form) return;

    const message = (typeof isEnglish !== 'undefined' && isEnglish) ?
        'Are you sure you want to reset the form? All unsaved changes will be lost.' :
        '确定要重置表单吗？所有未保存的更改将丢失。';

    if (confirm(message)) {
        form.reset();
    }
}

// 文件拖拽和选择功能
document.querySelectorAll('.file-input').forEach(input => {
    input.addEventListener('dragover', (e) => {
        e.preventDefault();
        input.style.borderColor = '#e54a00';
        input.style.background = '#fff5f0';
    });

    input.addEventListener('dragleave', (e) => {
        e.preventDefault();
        input.style.borderColor = '#FF5C00';
        input.style.background = '#fff9f5';
    });

    input.addEventListener('drop', (e) => {
        e.preventDefault();
        const files = e.dataTransfer.files;
        const fileInput = input.querySelector('input[type="file"]');
        fileInput.files = files;

        input.style.borderColor = '#FF5C00';
        input.style.background = '#fff9f5';

        if (files.length > 0) {
            const textDiv = input.querySelector('.file-input-text');
            const isEng = (typeof isEnglish !== 'undefined' && isEnglish);
            textDiv.innerHTML = isEng ? `Selected: ${files[0].name}` : `已选择: ${files[0].name}`;
        }
    });
});

document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function () {
        const textDiv = this.parentElement.querySelector('.file-input-text');
        if (this.files.length > 0) {
            const isEng = (typeof isEnglish !== 'undefined' && isEnglish);
            textDiv.innerHTML = isEng ? `Selected: ${this.files[0].name}` : `已选择: ${this.files[0].name}`;
        }
    });
});

// 表单验证
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function (e) {
        // 如果是multipart表单且没有选择文件，可能不需要拦截（因为可能有默认行为或仅仅是尝试上传）
        // 这里主要针对必填字段
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.style.borderColor = '#dc3545';
            } else {
                field.style.borderColor = '#e9ecef';
            }
        });

        if (!isValid) {
            e.preventDefault();
            const message = (typeof isEnglish !== 'undefined' && isEnglish) ?
                'Please fill in all required fields' : '请填写所有必填字段';
            showToast(message, 'error');
        }
    });
});
