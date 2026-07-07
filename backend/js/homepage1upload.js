
function isHomepage1ReactV2Page() {
    return /homepage1upload-v2/.test(window.location.pathname || '');
}

function initApp() {
    const fileInputs = document.querySelectorAll('.file-input');
    if (!fileInputs.length || document.body.dataset.homepage1Bound === '1') {
        return;
    }

    document.body.dataset.homepage1Bound = '1';

    fileInputs.forEach((input) => {
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
            if (!fileInput) {
                return;
            }

            fileInput.files = files;
            input.style.borderColor = '#667eea';
            input.style.background = '#f8f9ff';

            if (files.length > 0) {
                const textDiv = input.querySelector('.file-input-text');
                if (textDiv) {
                    textDiv.innerHTML = `已选择: ${files[0].name}`;
                }
            }
        });
    });

    document.querySelectorAll('input[type="file"]').forEach((input) => {
        input.addEventListener('change', function () {
            const textDiv = this.parentElement?.querySelector('.file-input-text');
            if (textDiv && this.files.length > 0) {
                textDiv.innerHTML = `已选择: ${this.files[0].name}`;
            }
        });
    });

    const uploadForm = document.getElementById('homepage1-upload-form');
    const fileInput = document.getElementById('home-page1-file');
    if (uploadForm && fileInput) {
        uploadForm.addEventListener('submit', function (e) {
            if (!fileInput.files.length) {
                e.preventDefault();
                if (typeof showToast === 'function') {
                    showToast('请先选择要上传的文件', 'warning');
                }
            }
        });
    }
}

function bootHomepage1Upload() {
    initApp();
}

window.bootHomepage1Upload = bootHomepage1Upload;
window.reinitHomepage1Upload = bootHomepage1Upload;

if (!isHomepage1ReactV2Page()) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initApp);
    } else {
        initApp();
    }
}
