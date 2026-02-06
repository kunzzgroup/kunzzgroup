/* Tokyo Location Management JS */
let storeCounter = 0;

function addNewStore() {
    const template = document.getElementById('storeTemplate');
    const newStore = template.cloneNode(true);
    newStore.style.display = 'block';
    newStore.id = '';

    const storeKey = 'store_' + Date.now();
    const storeSection = newStore.querySelector('.store-section');
    storeSection.setAttribute('data-store-key', storeKey);

    // Add to container
    document.getElementById('storesContainer').appendChild(storeSection);

    // Update counters
    updateStoreCounters();

    // Update form field names
    const inputs = storeSection.querySelectorAll('input, textarea');
    const labels = storeSection.querySelectorAll('label');

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

        // Add placeholders
        inputs[0].placeholder = '例如：三店：';
        inputs[2].placeholder = '+60 19-710 8090';
        inputs[3].placeholder = 'https://maps.app.goo.gl/...';

        // Add event listeners for preview
        inputs.forEach(input => {
            input.addEventListener('input', updatePreview);
        });
    }

    // Scroll to new store
    storeSection.scrollIntoView({ behavior: 'smooth' });
}

function removeNewStore(button) {
    if (confirm('确定要移除这个新店铺吗？')) {
        button.closest('.store-section').remove();
        updateStoreCounters();
        updatePreview();
    }
}

function deleteStore(storeKey) {
    if (confirm('确定要删除这个店铺吗？此操作不可撤销！')) {
        document.getElementById('deleteStoreKey').value = storeKey;
        document.getElementById('deleteForm').submit();
    }
}

function updateStoreCounters() {
    const stores = document.querySelectorAll('.store-section[data-store-key]');
    stores.forEach((store, index) => {
        const titleSpan = store.querySelector('h3 span');
        if (titleSpan) {
            titleSpan.textContent = index + 1;
        }
    });
    storeCounter = stores.length;
}

function updatePreview() {
    const previewContent = document.getElementById('previewContent');
    if (!previewContent) return;

    const stores = document.querySelectorAll('.store-section[data-store-key]');
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

document.addEventListener('DOMContentLoaded', function () {
    // Initialize store counter
    const existingStores = document.querySelectorAll('.store-section[data-store-key]');
    storeCounter = existingStores.length;
    updateStoreCounters();

    // Add event listeners to existing inputs
    document.querySelectorAll('.form-input').forEach(input => {
        input.addEventListener('input', updatePreview);
    });

    // Main form validation
    const mainForm = document.getElementById('mainForm');
    if (mainForm) {
        mainForm.addEventListener('submit', function (e) {
            const sectionTitle = document.getElementById('section_title');
            if (!sectionTitle.value.trim()) {
                e.preventDefault();
                alert('请至少填写标题文字！');
                sectionTitle.style.borderColor = '#dc3545';
                sectionTitle.scrollIntoView({ behavior: 'smooth' });
                sectionTitle.focus();
                return;
            }

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

            if (!hasValidStore) {
                const confirmSave = confirm('当前没有填写任何店铺信息，确定要保存吗？');
                if (!confirmSave) {
                    e.preventDefault();
                }
            }
        });
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function (e) {
        if (e.ctrlKey && e.key === 'n') {
            e.preventDefault();
            addNewStore();
        }
        if (e.ctrlKey && e.key === 's' && mainForm) {
            e.preventDefault();
            mainForm.submit();
        }
    });
});
