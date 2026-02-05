// 文件拖拽和选择功能
document.querySelectorAll('.file-input').forEach(input => {
    input.addEventListener('dragover', (e) => {
        e.preventDefault();
        input.style.borderColor = '#0ea5e9';
        input.style.background = '#e0f2fe';
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
            textDiv.innerHTML = `已选择: ${files[0].name}`;
        }
    });

    // 点击触发
    input.onclick = function () {
        input.querySelector('input[type="file"]').click();
    };

    // 阻止冒泡
    input.querySelector('input[type="file"]').onclick = function (e) {
        e.stopPropagation();
    };
});

document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function () {
        const textDiv = this.parentElement.querySelector('.file-input-text');
        if (this.files.length > 0) {
            textDiv.innerHTML = `已选择: ${this.files[0].name}`;
        }
    });
});
