
function isAboutpage1ReactV2Page() {
    return /aboutpage1upload-v2/.test(window.location.pathname || '');
}

function initApp() {
    const fileInputs = document.querySelectorAll('.file-input');
    if (!fileInputs.length || document.body.dataset.aboutpage1Bound === '1') {
        return;
    }

    document.body.dataset.aboutpage1Bound = '1';

    fileInputs.forEach((input) => {
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
            if (!fileInput) {
                return;
            }

            fileInput.files = files;
            input.style.borderColor = '#FF5C00';
            input.style.background = '#fff9f5';

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

    const uploadForm = document.getElementById('aboutpage1-upload-form');
    const fileInput = document.getElementById('about-page1-file');
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

function bootAboutpage1Upload() {
    initApp();
}

window.bootAboutpage1Upload = bootAboutpage1Upload;
window.reinitAboutpage1Upload = bootAboutpage1Upload;

if (!isAboutpage1ReactV2Page()) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initApp);
    } else {
        initApp();
    }
}
