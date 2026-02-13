
// 文件拖拽功能
document.querySelectorAll('.file-input').forEach(input => {
    input.addEventListener('dragover', (e) => {
        e.preventDefault();
        input.style.borderColor = '#5a6fd8';
        input.style.background = '#f0f2ff';
    });

    input.addEventListener('dragleave', (e) => {
        e.preventDefault();
        input.style.borderColor = '#667eea';
        input.style.background = '#f8f9ff';
    });

    input.addEventListener('drop', (e) => {
        e.preventDefault();
        const files = e.dataTransfer.files;
        const fileInput = input.querySelector('input[type="file"]');
        fileInput.files = files;

        input.style.borderColor = '#667eea';
        input.style.background = '#f8f9ff';

        // 显示文件名
        if (files.length > 0) {
            const textDiv = input.querySelector('.file-input-text');
            textDiv.innerHTML = `已选择: ${files[0].name}`;
        }
    });
});

// 文件选择时显示文件名
document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function () {
        const textDiv = this.parentElement.querySelector('.file-input-text');
        if (this.files.length > 0) {
            textDiv.innerHTML = `已选择: ${this.files[0].name}`;
        }
    });
});