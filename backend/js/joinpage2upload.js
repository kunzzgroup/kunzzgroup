
// 文件选择时显示文件名
document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function () {
        const textDiv = this.parentElement.querySelector('.file-input-text');
        if (this.files.length > 0) {
            textDiv.innerHTML = `已选择: ${this.files[0].name}<br><small>点击上传按钮完成上传</small>`;
        }
    });
});

// 拖拽功能
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
            textDiv.innerHTML = `已选择: ${files[0].name}<br><small>点击上传按钮完成上传</small>`;
        }
    });
});

// 表单提交验证
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function (e) {
        const fileInput = this.querySelector('input[type="file"]');
        if (fileInput && fileInput.files.length === 0) {
            // 如果是在"更新照片"的情况下，如果没有选择新照片，可能只是误点，或者想清空？
            // 我们要求必须选择照片才能提交
            e.preventDefault();
            alert("请先点击虚线框选择要上传的照片，然后再点击上传按钮！");
        }
    });
});