
// ── 滚动位置保存 / 恢复 ──────────────────────────────
(function () {
    const KEY = 'joinpage2_scrollY';
    // 恢复滚动位置
    const saved = sessionStorage.getItem(KEY);
    if (saved !== null) {
        window.scrollTo(0, parseInt(saved, 10));
        sessionStorage.removeItem(KEY);
    }

    // 提交任意表单前保存滚动位置
    document.addEventListener('submit', function () {
        sessionStorage.setItem(KEY, window.scrollY);
    }, true);
})();

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

// 表单提交验证（跳过删除表单）
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function (e) {
        if (this.classList.contains('delete-form')) return; // 删除表单不验证
        const fileInput = this.querySelector('input[type="file"]');
        if (fileInput && fileInput.files.length === 0) {
            e.preventDefault();
            alert("请先点击虚线框选择要上传的照片，然后再点击上传按钮！");
        }
    });
});

// ── Lightbox 照片查看器 ───────────────────────────────
(function () {
    const lightbox = document.getElementById('photo-lightbox');
    const lbImg    = document.getElementById('photo-lightbox-img');
    const lbClose  = document.getElementById('photo-lightbox-close');

    function open(src) {
        lbImg.src = src;
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function close() {
        lightbox.classList.remove('active');
        lbImg.src = '';
        document.body.style.overflow = '';
    }

    // 点击照片缩略图打开
    document.querySelectorAll('.current-image img').forEach(img => {
        img.addEventListener('click', function (e) {
            e.stopPropagation();
            // 去掉 URL 里的 ?v=... 缓存参数外，保留原始 src
            open(this.src);
        });
    });

    // 点击背景或关闭按钮关闭
    lightbox.addEventListener('click', close);
    lbClose.addEventListener('click', function (e) { e.stopPropagation(); close(); });
    lbImg.addEventListener('click', function (e) { e.stopPropagation(); }); // 点图片本身不关闭

    // ESC 关闭
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') close();
    });
})();
