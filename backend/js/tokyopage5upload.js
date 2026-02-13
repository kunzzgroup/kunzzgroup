
let storeCounter = window.TOKYO_UPLOAD.storeCounter;


// 添加新店铺
function addNewStore() {
    const template = document.getElementById('storeTemplate');
    const newStore = template.cloneNode(true);
    newStore.style.display = 'block';
    newStore.id = '';

    const storeKey = 'store_' + Date.now();
    const storeSection = newStore.querySelector('.store-section');
    storeSection.setAttribute('data-store-key', storeKey);

    // 先添加到容器
    document.getElementById('storesContainer').appendChild(storeSection);

    // 然后更新所有序号
    updateStoreCounters();

    // 更新表单字段名称
    const inputs = storeSection.querySelectorAll('input, textarea');
    const labels = storeSection.querySelectorAll('label');

    // 确保按正确顺序设置字段名
    if (inputs.length >= 4 && labels.length >= 4) {
        inputs[0].name = storeKey + '_label';
        inputs[0].id = storeKey + '_label';
        labels[0].setAttribute('for', storeKey + '_label');

        inputs[1].name = storeKey + '_address';
        inputs[1].id = storeKey + '_address';
        labels[1].setAttribute('for', storeKey + '_address');

        inputs[2].name = storeKey + '_phone';
        inputs[2].id = storeKey + '_phone';
        labels[2].setAttribute('for', storeKey + '_phone');

        inputs[3].name = storeKey + '_map_url';
        inputs[3].id = storeKey + '_map_url';
        labels[3].setAttribute('for', storeKey + '_map_url');

        // 为新字段添加默认提示
        inputs[0].placeholder = '例如：三店：';
        inputs[2].placeholder = '+60 19-710 8090';
        inputs[3].placeholder = 'https://maps.app.goo.gl/...';

        // 添加事件监听
        inputs.forEach(input => {
            input.addEventListener('input', updatePreview);
        });
    }

    // 滚动到新添加的店铺
    storeSection.scrollIntoView({ behavior: 'smooth' });
}

// 移除新店铺（未保存的）
function removeNewStore(button) {
    if (confirm('确定要移除这个新店铺吗？')) {
        button.closest('.store-section').remove();
        updateStoreCounters();
        updatePreview();
    }
}

// 删除已保存的店铺
function deleteStore(storeKey) {
    if (confirm('确定要删除这个店铺吗？此操作不可撤销！')) {
        document.getElementById('deleteStoreKey').value = storeKey;
        document.getElementById('deleteForm').submit();
    }
}

// 更新店铺序号
function updateStoreCounters() {
    const stores = document.querySelectorAll('.store-section[data-store-key]'); // 只选择有data-store-key的店铺
    stores.forEach((store, index) => {
        const titleSpan = store.querySelector('h3 span');
        if (titleSpan) {
            titleSpan.textContent = index + 1; // 从1开始计数
        }
    });
    storeCounter = stores.length;
}

// 实时预览功能
function updatePreview() {
    const previewContent = document.getElementById('previewContent');
    const stores = document.querySelectorAll('.store-section[data-store-key]'); // 只选择有data-store-key的店铺

    // 获取标题
    const sectionTitle = document.getElementById('section_title')?.value || '我们在这';
    let html = `<h2>${sectionTitle}</h2>`;

    stores.forEach(store => {
        const storeKey = store.getAttribute('data-store-key');
        const label = store.querySelector(`input[name="${storeKey}_label"]`)?.value || '';
        const address = store.querySelector(`textarea[name="${storeKey}_address"]`)?.value || '';
        const phone = store.querySelector(`input[name="${storeKey}_phone"]`)?.value || '';
        const mapUrl = store.querySelector(`input[name="${storeKey}_map_url"]`)?.value || '';

        if (label || address) {
            html += `<p>${label}<a href="${mapUrl}" target="_blank" class="no-style-link">${address}</a></p>`;
            html += `<p>电话：${phone}</p>`;
        }
    });

    previewContent.innerHTML = html;
}

// 为所有现有输入框添加实时预览
document.querySelectorAll('.form-input').forEach(input => {
    input.addEventListener('input', updatePreview);
});

// 表单验证 - 修改为更宽松的验证
document.getElementById('mainForm').addEventListener('submit', function (e) {
    // 只验证标题是否填写
    const sectionTitle = document.getElementById('section_title');

    if (!sectionTitle.value.trim()) {
        e.preventDefault();
        alert('请至少填写标题文字！');
        sectionTitle.style.borderColor = '#dc3545';
        sectionTitle.scrollIntoView({ behavior: 'smooth' });
        sectionTitle.focus();
        return;
    }

    // 重置所有字段的边框颜色
    document.querySelectorAll('.form-input').forEach(field => {
        field.style.borderColor = '#e9ecef';
    });

    // 可选：检查是否至少有一个店铺有基本信息
    const stores = document.querySelectorAll('.store-section[data-store-key]');
    let hasValidStore = false;

    stores.forEach(store => {
        const storeKey = store.getAttribute('data-store-key');
        const label = store.querySelector(`input[name="${storeKey}_label"]`)?.value || '';
        const address = store.querySelector(`textarea[name="${storeKey}_address"]`)?.value || '';

        if (label.trim() || address.trim()) {
            hasValidStore = true;
        }
    });

    // 如果没有任何店铺信息，给出警告但仍允许保存
    if (!hasValidStore) {
        const confirmSave = confirm('当前没有填写任何店铺信息，确定要保存吗？');
        if (!confirmSave) {
            e.preventDefault();
            return;
        }
    }
});

// 页面加载完成后更新计数器
document.addEventListener('DOMContentLoaded', function () {
    updateStoreCounters();
});

// 为标题输入框添加实时预览
document.getElementById('section_title').addEventListener('input', updatePreview);

// 键盘快捷键
document.addEventListener('keydown', function (e) {
    // Ctrl+N 添加新店铺
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        addNewStore();
    }
    // Ctrl+S 保存
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        document.getElementById('mainForm').submit();
    }
    // Ctrl+P 预览
    if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        updatePreview();
    }
});