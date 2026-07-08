function isAboutpage4ReactV2Page() {
    return /aboutpage4upload-v2/.test(window.location.pathname || '');
}

function getAboutpage4Context() {
    const root = document.querySelector('[data-aboutpage4-content-root]');
    const lang = root?.dataset?.lang || 'zh';

    return {
        root,
        isEnglish: lang === 'en',
        actionUrl: root?.dataset?.actionUrl || 'aboutpage4upload.php',
        returnTo: isAboutpage4ReactV2Page() ? 'v2' : '',
        lang,
    };
}

function showYear(year) {
    document.querySelectorAll('.timeline-content').forEach((content) => {
        content.classList.remove('active');
    });

    document.querySelectorAll('.year-tab').forEach((tab) => {
        tab.classList.remove('active');
    });

    const targetContent = document.getElementById(`content-${year}`);
    if (targetContent) {
        targetContent.classList.add('active');
    }

    if (typeof event !== 'undefined' && event?.target) {
        event.target.classList.add('active');
    }
}

function showAddRecordModal() {
    const modal = document.getElementById('addRecordModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function hideAddRecordModal() {
    const modal = document.getElementById('addRecordModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function confirmDeleteRecord(recordId) {
    const ctx = getAboutpage4Context();
    const message = ctx.isEnglish
        ? 'Are you sure you want to delete this record? This action cannot be undone!'
        : '确定要删除这个记录吗？此操作不可撤销！';

    if (!confirm(message)) {
        return;
    }

    const form = document.createElement('form');
    form.method = 'post';
    form.action = ctx.actionUrl;
    form.innerHTML = `
        <input type="hidden" name="delete_record" value="1">
        <input type="hidden" name="record_id" value="${recordId}">
        <input type="hidden" name="return_to" value="${ctx.returnTo}">
        <input type="hidden" name="lang" value="${ctx.lang}">
    `;
    document.body.appendChild(form);
    form.submit();
}

function refreshImageDisplayByRecord(recordId) {
    const imageElement = document.querySelector(`.entry-container[data-record-id="${recordId}"] .preview-image`);
    if (imageElement) {
        const currentSrc = imageElement.src;
        imageElement.src = `${currentSrc.split('?')[0]}?v=${Date.now()}`;
    }
}

function resetForm(year) {
    const ctx = getAboutpage4Context();
    const form = document.querySelector(`#content-${year} .content-form form`);
    if (!form) {
        return;
    }

    const message = ctx.isEnglish
        ? 'Are you sure you want to reset the form? All unsaved changes will be lost.'
        : '确定要重置表单吗？所有未保存的更改将丢失。';

    if (confirm(message)) {
        form.reset();
    }
}

function initAboutpage4Upload() {
    const ctx = getAboutpage4Context();
    const { root, isEnglish } = ctx;

    if (!root || root.dataset.aboutpage4Bound === '1') {
        return;
    }

    root.dataset.aboutpage4Bound = '1';

    root.addEventListener('click', (e) => {
        const modal = document.getElementById('addRecordModal');
        if (e.target === modal) {
            hideAddRecordModal();
        }
    });

    root.querySelectorAll('form[enctype="multipart/form-data"]').forEach((form) => {
        form.addEventListener('submit', function () {
            const recordInput = this.querySelector('input[name="record_id"]');
            if (recordInput) {
                const recordId = recordInput.value;
                setTimeout(() => {
                    refreshImageDisplayByRecord(recordId);
                }, 1000);
            }
        });
    });

    root.querySelectorAll('.file-input').forEach((input) => {
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
                    textDiv.innerHTML = isEnglish ? `Selected: ${files[0].name}` : `已选择: ${files[0].name}`;
                }
            }
        });
    });

    root.querySelectorAll('input[type="file"]').forEach((input) => {
        input.addEventListener('change', function () {
            const textDiv = this.parentElement?.querySelector('.file-input-text');
            if (textDiv && this.files.length > 0) {
                textDiv.innerHTML = isEnglish ? `Selected: ${this.files[0].name}` : `已选择: ${this.files[0].name}`;
            }
        });
    });

    root.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', function (e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach((field) => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#dc3545';
                } else {
                    field.style.borderColor = '#e9ecef';
                }
            });

            if (!isValid) {
                e.preventDefault();
                const message = isEnglish ? 'Please fill in all required fields' : '请填写所有必填字段';
                if (typeof showToast === 'function') {
                    showToast(message, 'error');
                }
            }
        });
    });
}

function bootAboutpage4Upload() {
    const root = document.querySelector('[data-aboutpage4-content-root]');
    if (root) {
        delete root.dataset.aboutpage4Bound;
    }
    initAboutpage4Upload();
}

window.showYear = showYear;
window.showAddRecordModal = showAddRecordModal;
window.hideAddRecordModal = hideAddRecordModal;
window.confirmDeleteRecord = confirmDeleteRecord;
window.resetForm = resetForm;
window.bootAboutpage4Upload = bootAboutpage4Upload;
window.reinitAboutpage4Upload = bootAboutpage4Upload;

if (!isAboutpage4ReactV2Page()) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAboutpage4Upload);
    } else {
        initAboutpage4Upload();
    }
}
