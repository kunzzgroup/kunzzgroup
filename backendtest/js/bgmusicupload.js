// 文件拖拽功能
const fileInput = document.querySelector('.file-input');
const fileInputElement = document.getElementById('music-file');

if (fileInput && fileInputElement) {
    fileInput.addEventListener('dragover', (e) => {
        e.preventDefault();
        fileInput.style.borderColor = '#e54a00';
        fileInput.style.background = '#fff5f0';
    });

    fileInput.addEventListener('dragleave', (e) => {
        e.preventDefault();
        fileInput.style.borderColor = '#FF5C00';
        fileInput.style.background = '#fff9f5';
    });

    fileInput.addEventListener('drop', (e) => {
        e.preventDefault();
        const files = e.dataTransfer.files;

        if (files.length > 0) {
            const file = files[0];

            // 验证文件类型
            const allowedTypes = ['audio/mp3', 'audio/wav', 'audio/ogg', 'audio/m4a', 'audio/mpeg'];
            if (!allowedTypes.includes(file.type) && !file.name.match(/\.(mp3|wav|ogg|m4a)$/i)) {
                alert('请选择有效的音频文件（MP3, WAV, OGG, M4A）');
                return;
            }

            // 验证文件大小（10MB）
            if (file.size > 10 * 1024 * 1024) {
                alert('文件大小不能超过 10MB');
                return;
            }

            fileInputElement.files = files;
            updateFileInputText(file.name);
        }

        fileInput.style.borderColor = '#FF5C00';
        fileInput.style.background = '#fff9f5';
    });

    // 点击触发
    fileInput.onclick = function () {
        fileInputElement.click();
    };

    // 阻止冒泡
    fileInputElement.onclick = function (e) {
        e.stopPropagation();
    };

    fileInputElement.addEventListener('change', function () {
        if (this.files.length > 0) {
            const file = this.files[0];

            // 验证文件大小
            if (file.size > 10 * 1024 * 1024) {
                alert('文件大小不能超过 10MB');
                this.value = '';
                return;
            }

            updateFileInputText(file.name);
        }
    });
}

function updateFileInputText(fileName) {
    const textDiv = document.querySelector('.file-input-text');
    if (textDiv) {
        textDiv.innerHTML = `🎵 已选择: ${fileName}<br><small>点击"上传新音乐"按钮完成上传</small>`;
    }
}

// 表单提交验证
const uploadForm = document.getElementById('upload-form');
if (uploadForm) {
    uploadForm.addEventListener('submit', function (e) {
        // 如果是点击了删除按钮，不需要验证文件
        if (e.submitter && e.submitter.name === 'action' && e.submitter.value === 'delete') {
            return;
        }

        if (!fileInputElement.files.length) {
            e.preventDefault();
            alert('请先选择要上传的音乐文件');
        }
    });
}
