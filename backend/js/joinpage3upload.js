
function isJoinpage3ReactV2Page() {
    return /joinpage3upload-v2/.test(window.location.pathname || '');
}

function toggleDepartmentField() {
    const companySelect = document.getElementById('job_category');
    const departmentGroup = document.getElementById('department-group');
    const departmentSelect = document.getElementById('company_department');

    if (!companySelect || !departmentGroup || !departmentSelect) {
        return;
    }

    if (
        companySelect.value === 'TOKYO JAPANESE CUISINE'
        || companySelect.value === 'TOKYO IZAKAYA'
    ) {
        departmentGroup.style.display = 'flex';
        departmentSelect.required = true;
    } else {
        departmentGroup.style.display = 'none';
        departmentSelect.required = false;
        departmentSelect.value = '';
    }
}

function initDeleteConfirm() {
    document.querySelectorAll('.joinpage3-delete-form').forEach((form) => {
        if (form.dataset.joinpage3DeleteBound === '1') {
            return;
        }

        form.dataset.joinpage3DeleteBound = '1';
        form.addEventListener('submit', function (e) {
            const root = document.querySelector('[data-joinpage3-content-root]');
            const isEnglish = root?.dataset?.lang === 'en';
            const message = isEnglish
                ? 'Are you sure you want to delete this job position?'
                : '确定要删除这个职位吗？';

            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
}

function initDepartmentToggle() {
    const companySelect = document.getElementById('job_category');
    if (!companySelect || companySelect.dataset.joinpage3Bound === '1') {
        return;
    }

    companySelect.dataset.joinpage3Bound = '1';
    companySelect.addEventListener('change', toggleDepartmentField);
    toggleDepartmentField();
}

function initApp() {
    const root = document.querySelector('[data-joinpage3-content-root]');
    if (!root || root.dataset.joinpage3Bound === '1') {
        return;
    }

    root.dataset.joinpage3Bound = '1';
    initDepartmentToggle();
    initDeleteConfirm();
}

function bootJoinpage3Upload() {
    initApp();
}

window.bootJoinpage3Upload = bootJoinpage3Upload;
window.reinitJoinpage3Upload = bootJoinpage3Upload;
window.toggleDepartmentField = toggleDepartmentField;

if (!isJoinpage3ReactV2Page()) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initApp);
    } else {
        initApp();
    }
}
