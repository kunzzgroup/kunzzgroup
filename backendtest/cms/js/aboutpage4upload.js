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
    document.getElementById('content-' + year).classList.add('active');

    // 激活选中的标签
    event.target.classList.add('active');
}

// 新增记录模态框
function showAddRecordModal() {
    document.getElementById('addRecordModal').style.display = 'flex';
}

function hideAddRecordModal() {
    document.getElementById('addRecordModal').style.display = 'none';
}

// 确认删除记录
function confirmDeleteRecord(recordId) {
    const isEnglish = <? php echo $isEnglish ? 'true' : 'false'; ?>;
    const message = isEnglish ? `Are you sure you want to delete this record? This action cannot be undone!` : `确定要删除这个记录吗？此操作不可撤销！`;

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
document.getElementById('addRecordModal').addEventListener('click', function (e) {
    if (e.target === this) {
        hideAddRecordModal();
    }
});

// 修改showYear函数，支持动态年份
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
    event.target.classList.add('active');
}

// 照片上传成功后刷新图片显示
function refreshImageDisplayByRecord(recordId) {
    const imageElement = document.querySelector(`.entry-container[data-record-id="${recordId}"] .preview-image`);
    if (imageElement) {
        const currentSrc = imageElement.src;
        const newSrc = currentSrc.split('?')[0] + '?v=' + Date.now();
        imageElement.src = newSrc;
    }
}

// 监听照片上传表单提交
document.querySelectorAll('form[enctype="multipart/form-data"]').forEach(form => {
    form.addEventListener('submit', function (e) {
        const recordId = this.querySelector('input[name="record_id"]').value;

        // 延迟刷新，等待服务器处理完成
        setTimeout(() => {
            refreshImageDisplayByRecord(recordId);
        }, 1000);
    });
});

// 重置表单
function resetForm(year) {
    const form = document.querySelector(`#content-${year} .content-form form`);
    const isEnglish = <? php echo $isEnglish ? 'true' : 'false'; ?>;
    const message = isEnglish ? 'Are you sure you want to reset the form? All unsaved changes will be lost.' : '确定要重置表单吗？所有未保存的更改将丢失。';
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
            const isEnglish = <? php echo $isEnglish ? 'true' : 'false'; ?>;
            textDiv.innerHTML = isEnglish ? `Selected: ${files[0].name}` : `已选择: ${files[0].name}`;
        }
    });
});

document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function () {
        const textDiv = this.parentElement.querySelector('.file-input-text');
        if (this.files.length > 0) {
            const isEnglish = <? php echo $isEnglish ? 'true' : 'false'; ?>;
            textDiv.innerHTML = isEnglish ? `Selected: ${this.files[0].name}` : `已选择: ${this.files[0].name}`;
        }
    });
});

// 表单验证
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function (e) {
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
            const isEnglish = <? php echo $isEnglish ? 'true' : 'false'; ?>;
            const message = isEnglish ? 'Please fill in all required fields' : '请填写所有必填字段';
            alert(message);
        }
    });
});
