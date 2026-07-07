
function isBgMusicReactV2Page() {
    return /bgmusicupload-v2/.test(window.location.pathname || '');
}

function initApp() {
    const fileInput = document.querySelector('.file-input');
    const fileInputElement = document.getElementById('music-file');
    const uploadForm = document.getElementById('bgmusic-upload-form');

    if (!fileInput || !fileInputElement || !uploadForm) {
        return;
    }

    if (fileInput.dataset.bgMusicBound === '1') {
        return;
    }

    fileInput.dataset.bgMusicBound = '1';

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
            const allowedTypes = ['audio/mp3', 'audio/wav', 'audio/ogg', 'audio/m4a', 'audio/mpeg'];

            if (!allowedTypes.includes(file.type) && !file.name.match(/\.(mp3|wav|ogg|m4a)$/i)) {
                showToast('请选择有效的音频文件（MP3, WAV, OGG, M4A）', 'error');
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                showToast('文件大小不能超过 10MB', 'error');
                return;
            }

            fileInputElement.files = files;
            updateFileInputText(file.name);
        }

        fileInput.style.borderColor = '#FF5C00';
        fileInput.style.background = '#fff9f5';
    });

    fileInputElement.addEventListener('change', function () {
        if (this.files.length > 0) {
            const file = this.files[0];

            if (file.size > 10 * 1024 * 1024) {
                showToast('文件大小不能超过 10MB', 'error');
                this.value = '';
                return;
            }

            updateFileInputText(file.name);
        }
    });

    uploadForm.addEventListener('submit', function (e) {
        if (!fileInputElement.files.length) {
            e.preventDefault();
            showToast('请先选择要上传的音乐文件', 'warning');
        }
    });
}

function updateFileInputText(fileName) {
    const textDiv = document.querySelector('.file-input-text');
    if (!textDiv) {
        return;
    }

    textDiv.innerHTML = `🎵 已选择: ${fileName}<br><small>点击"上传新音乐"按钮完成上传</small>`;
}

function bootBgMusicUpload() {
    initApp();
}

window.bootBgMusicUpload = bootBgMusicUpload;
window.reinitBgMusicUpload = bootBgMusicUpload;

if (!isBgMusicReactV2Page()) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initApp);
    } else {
        initApp();
    }
}
