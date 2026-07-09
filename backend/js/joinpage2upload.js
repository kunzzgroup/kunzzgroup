
function isJoinpage2ReactV2Page() {
    return /joinpage2upload-v2/.test(window.location.pathname || '');
}

function getJoinpage2Context() {
    const root = document.querySelector('[data-joinpage2-content-root]');
    const backendBase = window.__KUNZZ_BACKEND_BASE__ || '';
    const actionUrl = root?.dataset?.actionUrl || `${backendBase}/joinpage2upload.php`;

    return {
        root,
        actionUrl,
        returnTo: root?.dataset?.returnTo || (isJoinpage2ReactV2Page() ? 'v2' : ''),
    };
}

function getJoinpage2UploadUrl() {
    const ctx = getJoinpage2Context();
    return ctx.actionUrl || 'joinpage2upload.php';
}

function initScrollRestore() {
    const key = 'joinpage2_scrollY';
    const saved = sessionStorage.getItem(key);
    if (saved !== null) {
        window.scrollTo(0, parseInt(saved, 10));
        sessionStorage.removeItem(key);
    }
}

function initToast() {
    if (document.getElementById('toast-container')) {
        return;
    }

    const container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);

    window.showToast = function (msg, type) {
        const colors = {
            success: { bg: '#1a7f4b', icon: '✓' },
            error: { bg: '#c0392b', icon: '✕' },
            info: { bg: '#1a5276', icon: 'ℹ' },
        };
        const { icon } = colors[type] || colors.info;

        const toast = document.createElement('div');
        toast.className = `toast toast--${type || 'info'}`;
        toast.innerHTML = `<span class="toast__icon">${icon}</span><span>${msg}</span>`;
        container.appendChild(toast);

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                toast.classList.add('toast--visible');
            });
        });

        const dismiss = () => {
            toast.classList.remove('toast--visible');
            setTimeout(() => toast.remove(), 320);
        };
        toast.addEventListener('click', dismiss);
        setTimeout(dismiss, 3500);
    };
}

function updateStats(delta) {
    const nums = document.querySelectorAll('.stats-number');
    if (nums.length >= 3) {
        nums[1].textContent = Math.max(0, parseInt(nums[1].textContent, 10) + delta);
        nums[2].textContent = Math.max(0, parseInt(nums[2].textContent, 10) - delta);
    }
}

function wireDeleteForm(form) {
    if (!form || form.dataset.joinpage2DeleteBound === '1') {
        return;
    }

    form.dataset.joinpage2DeleteBound = '1';
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!confirm('确定要删除这张照片吗？此操作无法复原！')) {
            return;
        }

        const photoNumber = this.querySelector('[name=photo_number]').value;
        const fd = new FormData(this);
        fetch(getJoinpage2UploadUrl(), {
            method: 'POST',
            body: fd,
        })
            .then((r) => r.json())
            .then((data) => {
                if (data.ok) {
                    showToast(data.msg, 'success');
                    updateStats(-1);
                    const card = this.closest('.photo-card');
                    if (card) {
                        const imgWrap = card.querySelector('.current-image');
                        if (imgWrap) {
                            imgWrap.remove();
                        }
                        const uploadBtn = card.querySelector('.upload-btn');
                        if (uploadBtn) {
                            uploadBtn.textContent = '上传照片';
                        }
                    }
                } else {
                    showToast(data.msg || '删除失败', 'error');
                }
            })
            .catch(() => showToast('网络错误，请重试', 'error'));
    });
}

function wireLightbox(img) {
    if (!img || img.dataset.joinpage2LightboxBound === '1') {
        return;
    }

    img.dataset.joinpage2LightboxBound = '1';
    img.addEventListener('click', function (e) {
        e.stopPropagation();
        const lightbox = document.getElementById('photo-lightbox');
        const lbImg = document.getElementById('photo-lightbox-img');
        if (!lightbox || !lbImg) {
            return;
        }
        lbImg.src = this.src;
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
    });
}

function closeLightbox() {
    const lightbox = document.getElementById('photo-lightbox');
    const lbImg = document.getElementById('photo-lightbox-img');
    if (!lightbox || !lbImg) {
        return;
    }
    lightbox.classList.remove('active');
    lbImg.src = '';
    document.body.style.overflow = '';
}

function initLightbox() {
    const lightbox = document.getElementById('photo-lightbox');
    const lbImg = document.getElementById('photo-lightbox-img');
    const lbClose = document.getElementById('photo-lightbox-close');
    if (!lightbox || lightbox.dataset.joinpage2LightboxInit === '1') {
        return;
    }

    lightbox.dataset.joinpage2LightboxInit = '1';
    lightbox.addEventListener('click', closeLightbox);
    if (lbClose) {
        lbClose.addEventListener('click', function (e) {
            e.stopPropagation();
            closeLightbox();
        });
    }
    if (lbImg) {
        lbImg.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }
    document.addEventListener('keydown', function onEscape(e) {
        if (e.key === 'Escape') {
            closeLightbox();
        }
    });
}

function initApp() {
    const ctx = getJoinpage2Context();
    if (!ctx.root || ctx.root.dataset.joinpage2Bound === '1') {
        return;
    }

    ctx.root.dataset.joinpage2Bound = '1';
    initScrollRestore();
    initToast();
    initLightbox();

    document.querySelectorAll('input[type="file"]').forEach((input) => {
        input.addEventListener('change', function () {
            const textDiv = this.parentElement?.querySelector('.file-input-text');
            if (textDiv && this.files.length > 0) {
                textDiv.innerHTML = `已选择: ${this.files[0].name}<br><small>点击上传按钮完成上传</small>`;
            }
        });
    });

    document.querySelectorAll('.file-input').forEach((input) => {
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
                    textDiv.innerHTML = `已选择: ${files[0].name}<br><small>点击上传按钮完成上传</small>`;
                }
            }
        });
    });

    document.querySelectorAll('form.upload-form').forEach((form) => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const fileInput = this.querySelector('input[type="file"]');
            if (!fileInput || fileInput.files.length === 0) {
                showToast('请先选择要上传的照片！', 'error');
                return;
            }

            const photoNumber = this.querySelector('[name=photo_number]').value;
            const btn = this.querySelector('.upload-btn');
            const originalText = btn ? btn.textContent : '';
            if (btn) {
                btn.textContent = '上传中…';
                btn.disabled = true;
            }

            const fd = new FormData(this);
            fetch(getJoinpage2UploadUrl(), {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd,
            })
                .then((r) => r.json())
                .then((data) => {
                    if (data.ok) {
                        showToast(data.msg, 'success');
                        const card = form.closest('.photo-card');
                        if (card && data.url) {
                            let imgWrap = card.querySelector('.current-image');
                            const isNewPhoto = !imgWrap;
                            if (isNewPhoto) {
                                imgWrap = document.createElement('div');
                                imgWrap.className = 'current-image';
                                imgWrap.innerHTML = `
                            <img src="" alt="照片 ${photoNumber}">
                            <form class="delete-form" method="post">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="photo_number" value="${photoNumber}">
                                <button type="submit" class="delete-btn" title="删除照片">✕</button>
                            </form>
                            <div class="image-info">
                                <strong>已上传</strong><br>
                                <small>更新: ${data.updated}</small>
                            </div>`;
                                card.insertBefore(imgWrap, card.querySelector('form.upload-form'));
                                wireDeleteForm(imgWrap.querySelector('.delete-form'));
                                wireLightbox(imgWrap.querySelector('img'));
                            }
                            const img = imgWrap.querySelector('img');
                            img.src = `${data.url}?v=${Date.now()}`;
                            imgWrap.querySelector('.image-info').innerHTML =
                                `<strong>已上传</strong><br><small>更新: ${data.updated}</small>`;
                            if (btn) {
                                btn.textContent = '更新照片';
                            }
                            if (isNewPhoto) {
                                updateStats(+1);
                            }
                        }
                        form.reset();
                        const textDiv = form.querySelector('.file-input-text');
                        if (textDiv) {
                            textDiv.innerHTML = '点击选择图片<br><small>支持 JPG, PNG, WebP（HEIC 自动转换）</small>';
                        }
                    } else {
                        showToast(data.msg || '上传失败', 'error');
                    }
                })
                .catch(() => showToast('网络错误，请重试', 'error'))
                .finally(() => {
                    if (btn) {
                        btn.disabled = false;
                        if (!btn.textContent.includes('更新')) {
                            btn.textContent = originalText;
                        }
                    }
                });
        });
    });

    document.querySelectorAll('.delete-form').forEach(wireDeleteForm);
    document.querySelectorAll('.current-image img').forEach(wireLightbox);
}

function bootJoinpage2Upload() {
    initApp();
}

window.bootJoinpage2Upload = bootJoinpage2Upload;
window.reinitJoinpage2Upload = bootJoinpage2Upload;

if (!isJoinpage2ReactV2Page()) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initApp);
    } else {
        initApp();
    }
}
