function toggleDepartmentField() {
    const companySelect = document.getElementById('job_category');
    const departmentGroup = document.getElementById('department-group');
    const departmentSelect = document.getElementById('company_department');

    if (companySelect && (companySelect.value === 'TOKYO JAPANESE CUISINE' || companySelect.value === 'TOKYO IZAKAYA')) {
        departmentGroup.style.display = 'flex';
        departmentSelect.required = true;
    } else if (departmentGroup) {
        departmentGroup.style.display = 'none';
        departmentSelect.required = false;
        departmentSelect.value = '';
    }
}

// 通知系统
function showAlert(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    let existingToasts = container.querySelectorAll('.toast');
    while (existingToasts.length >= 3) {
        const firstToast = existingToasts[0];
        if (firstToast.parentNode) {
            firstToast.parentNode.removeChild(firstToast);
        }
        existingToasts = container.querySelectorAll('.toast');
    }

    const toastId = 'toast-' + Date.now();
    const iconClass = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle'
    }[type] || 'fa-check-circle';

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.id = toastId;
    toast.innerHTML = `
        <i class="fas ${iconClass} toast-icon"></i>
        <div class="toast-content">${message}</div>
        <button class="toast-close" onclick="closeToast('${toastId}')">
            <i class="fas fa-times"></i>
        </button>
        <div class="toast-progress"></div>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('show');
    }, 0);

    setTimeout(() => {
        closeToast(toastId);
    }, 4000);
}

function closeToast(toastId) {
    const toast = document.getElementById(toastId);
    if (toast) {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    toggleDepartmentField();
});
