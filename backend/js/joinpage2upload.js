
// ── 滚动位置保存 / 恢复（兜底，防万一整页刷新）────────────────
(function () {
    const KEY = 'joinpage2_scrollY';
    const saved = sessionStorage.getItem(KEY);
    if (saved !== null) {
        window.scrollTo(0, parseInt(saved, 10));
        sessionStorage.removeItem(KEY);
    }
})();

// ── Toast 通知 ───────────────────────────────────────────────
(function () {
    // 创建容器
    const container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);

    window.showToast = function (msg, type) {
        // type: 'success' | 'error' | 'info'
        const colors = {
            success: { bg: '#1a7f4b', icon: '✓' },
            error:   { bg: '#c0392b', icon: '✕' },
            info:    { bg: '#1a5276', icon: 'ℹ' }
        };
        const { bg, icon } = colors[type] || colors.info;

        const toast = document.createElement('div');
        toast.className = `toast toast--${type || 'info'}`;
        toast.innerHTML = `<span class="toast__icon">${icon}</span><span>${msg}</span>`;
        container.appendChild(toast);

        // 进入动画
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
})();

// ── 统计数字实时更新 ───────────────────────────────────────
// delta: +1 = 新上传, -1 = 删除
function updateStats(delta) {
    const nums = document.querySelectorAll('.stats-number');
    // nums[0]=总数(30), nums[1]=已上传, nums[2]=待上传
    if (nums.length >= 3) {
        nums[1].textContent = Math.max(0, parseInt(nums[1].textContent) + delta);
        nums[2].textContent = Math.max(0, parseInt(nums[2].textContent) - delta);
    }
}

// ── 文件选择时显示文件名 ──────────────────────────────────────
document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function () {
        const textDiv = this.parentElement.querySelector('.file-input-text');
        if (this.files.length > 0) {
            textDiv.innerHTML = `已选择: ${this.files[0].name}<br><small>点击上传按钮完成上传</small>`;
        }
    });
});

// ── 拖拽功能 ──────────────────────────────────────────────────
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

// ── AJAX 上传 ─────────────────────────────────────────────────
document.querySelectorAll('form:not(.delete-form)').forEach(form => {
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
        if (btn) { btn.textContent = '上传中…'; btn.disabled = true; }

        const fd = new FormData(this);
        fetch(location.pathname, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                showToast(data.msg, 'success');
                // 动态更新卡片缩略图
                const card = form.closest('.photo-card');
                if (card && data.url) {
                    let imgWrap = card.querySelector('.current-image');
                    const isNewPhoto = !imgWrap; // 记录是否是全新照片（之前无图）
                    if (isNewPhoto) {
                        // 如果之前没有图片区块，插入一个
                        imgWrap = document.createElement('div');
                        imgWrap.className = 'current-image';
                        imgWrap.innerHTML = `
                            <img src="" alt="照片 ${photoNumber}">
                            <form class="delete-form" method="post"
                                  onsubmit="return confirm('确定要删除这张照片吗？此操作无法复原！')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="photo_number" value="${photoNumber}">
                                <button type="submit" class="delete-btn" title="删除照片">✕</button>
                            </form>
                            <div class="image-info">
                                <strong>已上传</strong><br>
                                <small>更新: ${data.updated}</small>
                            </div>`;
                        card.insertBefore(imgWrap, card.querySelector('form:not(.delete-form)'));
                        wireDeleteForm(imgWrap.querySelector('.delete-form'));
                        wireLightbox(imgWrap.querySelector('img'));
                    }
                    const img = imgWrap.querySelector('img');
                    img.src = data.url + '?v=' + Date.now();
                    imgWrap.querySelector('.image-info').innerHTML =
                        `<strong>已上传</strong><br><small>更新: ${data.updated}</small>`;
                    // 更新按钮文字
                    if (btn) btn.textContent = '更新照片';
                    // 只有全新照片才计入统计
                    if (isNewPhoto) updateStats(+1);
                }
                // 重置文件选择框
                form.reset();
                const textDiv = form.querySelector('.file-input-text');
                if (textDiv) textDiv.innerHTML = '点击选择图片<br><small>支持 JPG, PNG, WebP（HEIC 自动转换）</small>';
            } else {
                showToast(data.msg || '上传失败', 'error');
            }
        })
        .catch(() => showToast('网络错误，请重试', 'error'))
        .finally(() => { if (btn) { btn.disabled = false; if (!btn.textContent.includes('更新')) btn.textContent = originalText; } });
    });
});

// ── AJAX 删除 ─────────────────────────────────────────────────
function wireDeleteForm(form) {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!confirm('确定要删除这张照片吗？此操作无法复原！')) return;
        const photoNumber = this.querySelector('[name=photo_number]').value;
        const fd = new FormData(this);
        fetch(location.pathname, {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                showToast(data.msg, 'success');
                updateStats(-1); // 统计：减少已上传
                // 移除缩略图区块
                const card = this.closest('.photo-card');
                if (card) {
                    const imgWrap = card.querySelector('.current-image');
                    if (imgWrap) imgWrap.remove();
                    // 改按钮文字
                    const uploadBtn = card.querySelector('.upload-btn');
                    if (uploadBtn) uploadBtn.textContent = '上传照片';
                }
            } else {
                showToast(data.msg || '删除失败', 'error');
            }
        })
        .catch(() => showToast('网络错误，请重试', 'error'));
    });
}
document.querySelectorAll('.delete-form').forEach(wireDeleteForm);

// ── Lightbox 照片查看器 ───────────────────────────────────────
const lightbox  = document.getElementById('photo-lightbox');
const lbImg     = document.getElementById('photo-lightbox-img');
const lbClose   = document.getElementById('photo-lightbox-close');

function wireLightbox(img) {
    img.addEventListener('click', function (e) {
        e.stopPropagation();
        lbImg.src = this.src;
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
    });
}
function closeLightbox() {
    lightbox.classList.remove('active');
    lbImg.src = '';
    document.body.style.overflow = '';
}

document.querySelectorAll('.current-image img').forEach(wireLightbox);
lightbox.addEventListener('click', closeLightbox);
lbClose.addEventListener('click', function (e) { e.stopPropagation(); closeLightbox(); });
lbImg.addEventListener('click', function (e) { e.stopPropagation(); });
document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeLightbox(); });