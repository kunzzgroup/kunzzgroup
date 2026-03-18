/**
 * Custom Multi-select Dropdown Helpers
 */
function toggleMultiSelect(header) {
    const container = header.parentElement;
    const isActive = container.classList.contains('active');
    
    // Close all other multi-selects first
    document.querySelectorAll('.custom-multi-select').forEach(ms => {
        if (ms !== container) ms.classList.remove('active');
    });
    
    container.classList.toggle('active');
}

function updateMultiSelectText(checkbox) {
    const container = checkbox.closest('.custom-multi-select');
    refreshMultiSelectHeader(container);
}

function refreshMultiSelectHeader(container) {
    const checkboxes = container.querySelectorAll('input[type="checkbox"]');
    const selectedTextSpan = container.querySelector('.selected-text');
    const checked = Array.from(checkboxes).filter(cb => cb.checked);
    
    if (checked.length === 0) {
        selectedTextSpan.textContent = "请选择区域运营单位";
        selectedTextSpan.style.color = "#999";
    } else if (checked.length <= 2) {
        selectedTextSpan.textContent = checked.map(cb => cb.parentElement.textContent.trim().split(' ')[0]).join(', ');
        selectedTextSpan.style.color = "#111827";
    } else {
        selectedTextSpan.textContent = `已选择 ${checked.length} 个单位`;
        selectedTextSpan.style.color = "#111827";
    }
}

// Global click listener to close dropdowns
document.addEventListener('click', function(e) {
    if (!e.target.closest('.custom-multi-select')) {
        document.querySelectorAll('.custom-multi-select').forEach(ms => {
            ms.classList.remove('active');
        });
    }
});

// 职位配置 - 根据账号类型显示不同职位选项
const positionsByAccountType = {
    'special': [
        'BOSS',
        'PA',
        'CAO',
        'CSO',
        'COO'
    ],
    'hr': [
        'CHO',
        'VP OF HR',
        'HR DIRECTOR',
        'SENIOR HR MANAGER',
        'HR MANAGER',
        'HR SUPERVISOR',
        'SENIOR HR EXECUTIVE',
        'HR EXECUTIVE',
        'JUNIOR HR EXECUTIVE',
        'HR INTERN'
    ],
    'account': [
        'CFO',
        'FINANCE MANAGER',
        'ACCOUNT SUPERVISOR',
        'ACCOUNT EXECUTIVE',
        'ACCOUNT INTERN'
    ],
    'media': [
        'CVO',
        'VP VISUAL',
        'VISUAL DIRECTOR',
        'SR.MEDIA MANAGER',
        'MEDIA MANAGER',
        'MEDIA LEAD',
        'SR.VIDEO CREATOR',
        'VIDEO CREATOR',
        'JR.VIDEO CREATOR',
        'MEDIA INTERN'
    ],
    'marketing': [
        'CMO',
        'VP OF MARKETING',
        'MARKETING DIRECTOR',
        'SR.MARKETING MANAGER',
        'MARKETING MANAGER',
        'ASST.MARKETING MANAGER',
        'SR.MARKETING EXEC',
        'MARKETING EXEC',
        'JR.MARKETING EXEC',
        'MARKETING INTERN'
    ],
    'support': [
        'VO OF KS',
        'KITCHEN.SUP DIRECTOR',
        'SENIOR KITCHEN SUP MANAGER',
        'KITCHEN SUP MANAGER',
        'KITCHEN SUPPORT LEAD',
        'SENIOR KITCHEN SUPPORT',
        'KITCHEN SUPPORT',
        'JUNIOR KITCHEN SUPPORT',
        'KITCHEN SUPPORT INTERN'
    ],
    'production': [
        'VP OF OPERATIONS',
        'OPERATIONS DIRECTOR',
        'SNR.OPERATIONS MANAGER',
        'PRODUCTION MANAGER',
        'TEAM LEAD',
        'SENIOR PRODUCTION',
        'OPERATOR',
        'JUNIOR OPERATOR',
        'OPERATOR INTERN'
    ],
    'r&d': [
        'VP PF R&D',
        'R&D DIRECTOR',
        'SENIOR R&D MANAGER',
        'R&D MANAGER',
        'LEAD R&D',
        'SENIOR R&D',
        'R&D',
        'JUNIOR R&D',
        'R&D INTERN'
    ],
    'technical': [
        'CTO',
        'VP OF TECH',
        'TECH DIRECTOR',
        'SR.ENGN.MANAGER',
        'ENG.MANAGER',
        'TECH LEAD',
        'SR.TECH ENGINEER',
        'TECH ENGINEER',
        'JR.TECH ENGINEER',
        'ENGINEER INTERN'
    ],
    'design': [
        'CBO',
        'VP OF DESIGN',
        'DESIGN DIRECTOR',
        'SENIOR DESIGN MANAGER',
        'DESIGN MANAGER',
        'DESIGN SUPERVISOR',
        'GRAPHIC DESIGNER',
        'JUNIOR GRAPHIC DESIGNER',
        'DESIGN ASSISTANT',
        'DESIGNER INTERN'
    ],
    'operation': [
        'OPERATION MANAGER'
    ],
    'service': [
        'MANAGER',
        'ASST.MANAGER',
        'SUPERVISOR',
        'SENIOR CAPTAIN',
        'CAPTAIN',
        'SENIOR WAITER',
        'WAITER'
    ],
    'sushi': [
        'HEAD CHEF',
        'OUTLET CHEF',
        'ASST.CHEF',
        'COMIS 1',
        'COMIS 2',
        'COMIS 3',
        'SUSHI HELPER'
    ],
    'kitchen': [
        'HEAD CHEF',
        'OUTLET CHEF',
        'ASST.CHEF',
        'COMIS 1',
        'COMIS 2',
        'COMIS 3',
        'KITCHEN HELPER'
    ]
};

// 避免 DOM 型 XSS：安全地转义 HTML 特殊字符
function escapeHTML(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#39;");
}

// 更新职位下拉选项
function updatePositionOptions(accountType, positionSelectId) {
    const positionSelect = document.getElementById(positionSelectId);

    // 清空现有选项
    positionSelect.innerHTML = '';

    if (!accountType || !positionsByAccountType[accountType]) {
        positionSelect.innerHTML = '<option value="">请先选择账号类型</option>';
        positionSelect.disabled = true;
        return;
    }

    // 启用职位选择框
    positionSelect.disabled = false;

    // 添加默认选项
    positionSelect.innerHTML = '<option value="">请选择职位</option>';

    // 添加对应账号类型的职位选项
    const positions = positionsByAccountType[accountType];
    positions.forEach(position => {
        const option = document.createElement('option');
        option.value = position;
        option.textContent = position;
        positionSelect.appendChild(option);
    });
}

// 输入格式化和过滤函数
function formatAndFilterInput(input, field) {
    let value = input.value;

    switch (field) {
        case 'username':
        case 'emergency_contact_name':
        case 'bank_account_holder_en':
        case 'position':  // 添加这一行
            // 只允许大写字母和空格，自动转换为大写
            value = value.toUpperCase().replace(/[^A-Z\s]/g, '');
            break;

        case 'username_cn':
            // 只允许中文字符，但保留当前输入状态
            // 不进行实时过滤，只在失去焦点时验证
            break;

        case 'email':
            // 只允许小写字母、数字、@和点号，自动转换为小写
            value = value.toLowerCase().replace(/[^a-z0-9@.]/g, '');
            break;

        case 'ic_number':
        case 'phone_number':
        case 'emergency_phone_number':
        case 'bank_account':
            // 只允许数字
            value = value.replace(/[^\d]/g, '');
            break;

        case 'home_address':
            // 只允许大写字母、数字、空格和常见符号，自动转换为大写
            value = value.toUpperCase().replace(/[^A-Z0-9\s\.,\-\#\/\(\)]/g, '');
            break;
    }

    input.value = value;
}

// 添加实时格式化
function addInputFormatting(input, field) {
    if (field === 'username_cn') {
        // 中文名字特殊处理：只在失去焦点时验证
        input.addEventListener('blur', function () {
            // 失去焦点时过滤非中文字符
            let value = this.value;
            value = value.replace(/[^\u4e00-\u9fff]/g, '');
            this.value = value;
        });

        // 粘贴时格式化
        input.addEventListener('paste', function (e) {
            setTimeout(() => {
                let value = this.value;
                value = value.replace(/[^\u4e00-\u9fff]/g, '');
                this.value = value;
            }, 0);
        });
    } else {
        // 其他字段的实时格式化
        input.addEventListener('input', function () {
            formatAndFilterInput(this, field);
        });

        // 粘贴时格式化
        input.addEventListener('paste', function (e) {
            setTimeout(() => {
                formatAndFilterInput(this, field);
            }, 0);
        });
    }
}

// 简单验证函数（用于最终提交验证）
function validateField(field, value) {
    if (!value) return true; // 空值通过验证

    switch (field) {
        case 'username':
        case 'emergency_contact_name':
        case 'bank_account_holder_en':
            // 至少两个单词
            return /^[A-Z]+(\s[A-Z]+)+$/.test(value);

        case 'username_cn':
            // 至少两个中文字符
            return /^[\u4e00-\u9fff]{2,}$/.test(value);

        case 'email':
            // 通用邮箱格式：支持 Gmail、Outlook、QQ、企业邮箱等（含点、子域名、大小写）
            return /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value);

        default:
            return true;
    }
}

// 页面加载时获取数据
document.addEventListener('DOMContentLoaded', function () {
    // 启动会话自动刷新
    startSessionRefresh();

    loadCodesAndUsers();

    // 添加实时搜索功能
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function (e) {
            filterTable(e.target.value);
        });
    }

    // 初始化事件监听器
    rebindEventListeners();
});

// 页面卸载时停止会话刷新
window.addEventListener('beforeunload', function () {
    stopSessionRefresh();
});

// 会话自动刷新机制
let sessionRefreshInterval;

function startSessionRefresh() {
    // 每5分钟刷新一次会话
    sessionRefreshInterval = setInterval(async () => {
        try {
            const response = await fetch('session_refresh_api.php');
            const result = await response.json();

            if (!result.success && result.code === 'SESSION_EXPIRED') {
                clearInterval(sessionRefreshInterval);
                showSessionExpiredMessage();
            }
        } catch (error) {
            console.error('会话刷新失败:', error);
        }
    }, 5 * 60 * 1000); // 5分钟
}

function stopSessionRefresh() {
    if (sessionRefreshInterval) {
        clearInterval(sessionRefreshInterval);
    }
}

// 显示会话过期消息
function showSessionExpiredMessage() {
    const tableBody = document.getElementById('tableBody');
    if (tableBody) {
        tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: #C62828;">
                            <div style="background: #ffebee; border: 1px solid #f44336; border-radius: 8px; padding: 20px; margin: 10px;">
                                <h3 style="color: #C62828; margin: 0 0 10px 0;">🔒 会话已过期</h3>
                                <p style="margin: 0 0 15px 0;">您的登录会话已过期，请重新登录以继续使用。</p>
                                <button onclick="window.location.href='../frontend/login'" 
                                        style="background: #C62828; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">
                                    重新登录
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
    }
}

// 加载代码和职员数据
async function loadCodesAndUsers() {
    const tableBody = document.getElementById('tableBody');
    if (!tableBody) return;

    try {
        const response = await fetch('generatecodeapi.php?action=list');
        const result = await response.json();

        if (result.success) {
            displayData(result.data);
        } else {
            // 检查是否是会话过期
            if (result.code === 'SESSION_EXPIRED') {
                showSessionExpiredMessage();
                return;
            }

            tableBody.innerHTML = `
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px; color: #C62828;">
                                ❌ 加载失败: ${result.message}
                            </td>
                        </tr>
                    `;
        }
    } catch (error) {
        console.error('Error:', error);
        tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: #C62828;">
                            ❌ 网络错误，请检查连接
                        </td>
                    </tr>
                `;
    }

    // 重新绑定事件监听器
    rebindEventListeners();
}

// 重新绑定事件监听器
function rebindEventListeners() {
    // 重新绑定添加职员表单提交事件
    const addUserForm = document.getElementById('addUserForm');
    if (addUserForm) {
        addUserForm.removeEventListener('submit', handleAddUserSubmit);
        addUserForm.addEventListener('submit', handleAddUserSubmit);
    }

    // 重新绑定编辑职员表单提交事件
    const editUserForm = document.getElementById('editUserForm');
    if (editUserForm) {
        editUserForm.removeEventListener('submit', handleEditUserSubmit);
        editUserForm.addEventListener('submit', handleEditUserSubmit);
    }

    // 重新绑定模态框外部点击关闭事件
    const addUserModal = document.getElementById('addUserModal');
    if (addUserModal) {
        addUserModal.onclick = function (event) {
            if (event.target === this) {
                closeAddUserModal();
            }
        };
    }

    // 绑定编辑模态框外部点击关闭事件
    const editUserModal = document.getElementById('editUserModal');
    if (editUserModal) {
        editUserModal.onclick = function (event) {
            if (event.target === this) {
                closeEditUserModal();
            }
        };
    }
}

// 提取表单提交处理函数
function handleAddUserSubmit(e) {
    e.preventDefault();
    addNewUser();
}

// 生成6位随机代码（数字字母结合）
function generateRandomCode() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let result = '';
    for (let i = 0; i < 6; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
}

// 返回仪表盘
function goBack() {
    window.location.href = 'dashboard';
}

// 显示数据
function displayData(data) {
    const tableBody = document.getElementById('tableBody');
    if (!tableBody) return;

    if (!data || data.length === 0) {
        tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: #666;">
                            📝 暂无数据
                        </td>
                    </tr>
                `;
        return;
    }

    // 定义账号类型的排序顺序
    const typeOrder = {
        'special': 1,
        'hr': 2,
        'account': 3,
        'media': 4,
        'marketing': 5,
        'support': 6,
        'production': 7,
        'r&d': 8,
        'technical': 9,
        'design': 10,
        'operation': 11,
        'service': 12,
        'sushi': 13,
        'kitchen': 14
    };

    // 按照指定顺序排序数据（先按账号类型，相同类型内按职位顺序）
    const sortedData = [...data].sort((a, b) => {
        const orderA = typeOrder[a.account_type] || 999;
        const orderB = typeOrder[b.account_type] || 999;

        // 如果账号类型不同，按类型顺序排序
        if (orderA !== orderB) {
            return orderA - orderB;
        }

        // 相同账号类型内，按职位顺序排序
        const accountType = a.account_type;
        const positions = positionsByAccountType[accountType] || [];

        // 获取职位在配置数组中的索引
        const positionA = (a.position || '').trim();
        const positionB = (b.position || '').trim();

        const indexA = positions.indexOf(positionA);
        const indexB = positions.indexOf(positionB);

        // 如果职位在配置中，按索引排序（索引小的在前）
        if (indexA !== -1 && indexB !== -1) {
            if (indexA !== indexB) {
                return indexA - indexB;
            }
        } else if (indexA !== -1) {
            // A在配置中，B不在，A排在前面
            return -1;
        } else if (indexB !== -1) {
            // B在配置中，A不在，B排在前面
            return 1;
        } else {
            // 都不在配置中，按职位名称字母顺序排序
            if (positionA && positionB) {
                const compare = positionA.localeCompare(positionB);
                if (compare !== 0) {
                    return compare;
                }
            } else if (positionA) {
                return -1; // A有职位，B没有，A排在前面
            } else if (positionB) {
                return 1; // B有职位，A没有，B排在前面
            }
        }

        // 如果职位也相同或都为空，按创建时间正序（旧的在前）
        const timeA = new Date(a.created_at || 0).getTime();
        const timeB = new Date(b.created_at || 0).getTime();
        if (timeA !== timeB) {
            return timeA - timeB;
        }

        // 如果创建时间也相同，按ID正序
        return (a.id || 0) - (b.id || 0);
    });

    const rows = sortedData.map((item, index) => `
                <tr id="row-${item.id}" data-id="${item.id}" data-branch="${item.branch || ''}" data-user='${escapeHTML(JSON.stringify(item))}'>
                    <td style="text-align: center; font-weight: bold; color: black;">${index + 1}</td>
                    <td>
                        <div style="font-weight: 700; color: #333; margin-bottom: 4px;">${item.position ? escapeHTML(item.position) : '-'}</div>
                        ${item.branch ? item.branch.split(',').map(b => `<span class="branch-tag">${escapeHTML(b.trim().toUpperCase())}</span>`).join('') : '<em style="color:#bbb;font-size:0.75em;">无</em>'}
                    </td>
                    <td>
                        <div style="font-weight: 500;">${item.username ? escapeHTML(item.username) : '<em style="color: #999;">-</em>'}</div>
                    </td>
                    <td>${item.email ? escapeHTML(item.email) : '<em style="color: #999;">-</em>'}</td>
                    <td>${item.phone_number ? escapeHTML(item.phone_number) : '<em style="color: #999;">-</em>'}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-action btn-edit" onclick="openEditModal(${item.id})" title="编辑">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-action btn-save" onclick="openPermissionsModal(${item.id})" title="权限设定" style="background: #ff8019;">
                                <i class="fas fa-user-shield"></i>
                            </button>
                            <button class="btn-action btn-delete" onclick="confirmDelete(${item.id}, '${item.username || '未知职员'}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');

    tableBody.innerHTML = rows;

    // 保存原始数据用于搜索
    originalTableData = sortedData;

    // 如果有搜索词，重新应用过滤
    const searchInput = document.getElementById('searchInput');
    if (searchInput && searchInput.value.trim()) {
        filterTable(searchInput.value);
    }
}

// 格式化账号类型
function formatAccountType(type) {
    const types = {
        'special': '特殊',
        'hr': '人事部',
        'account': '会计部',
        'media': '媒体制作部',
        'marketing': '推广部',
        'support': '支援部',
        'production': '生产部',
        'r&d': '研发部',
        'technical': '科技部',
        'design': '设计部',
        'operation': 'Operation',
        'service': '前台',
        'sushi': 'Sushi Bar',
        'kitchen': '厨房'
    };
    return types[type] || type;
}

// 格式化性别
function formatGender(gender) {
    const genders = {
        'male': '男',
        'female': '女',
        'other': '其他'
    };
    return genders[gender] || gender;
}

// 完全替换现有的 showMessage 函数
function showMessage(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    // 先检查并限制通知数量（在添加新通知之前）
    const existingToasts = container.querySelectorAll('.toast');
    let toastList = Array.from(existingToasts);
    while (toastList.length >= 3) {
        closeToast(toastList[0].id);
        // 立即从DOM移除，不等待动画
        if (toastList[0].parentNode) {
            toastList[0].parentNode.removeChild(toastList[0]);
        }
        toastList.shift();
    }

    const toastId = 'toast-' + Date.now();
    const iconClass = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'info': 'fa-info-circle',
        'warning': 'fa-exclamation-triangle'
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

    // 显示动画
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);

    // 自动关闭
    setTimeout(() => {
        closeToast(toastId);
    }, 800);
}

// 添加关闭通知的函数
function closeToast(toastId) {
    const toast = document.getElementById(toastId);
    if (toast) {
        toast.classList.remove('show');
        toast.classList.add('hide');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }
}

// 刷新表格
function refreshTable() {
    loadCodesAndUsers();
}

// ── 分类筛选器逻辑 ──
let branchL1Current = 'all';   // 'all' | 'kunzz' | 'branch'
let branchL2Current = 'all';   // 'all' | 'j1' | 'j2' | 'j3'

const branchL2Options = {
    'all': [],                                           // 不需要二层
    'kunzz': [],                                           // 只有一个选项"-"，不展开
    'branch': [
        { value: 'all', label: '全部分店' },
        { value: 'j1', label: 'J1' },
        { value: 'j2', label: 'J2' },
        { value: 'j3', label: 'J3' },
    ]
};

function toggleBranchL1() {
    const d = document.getElementById('branchL1Dropdown');
    const open = d ? (d.style.display !== 'none') : false;
    closeBranchDropdowns();
    if (d && !open) d.style.display = 'block';
}

function toggleBranchL2() {
    // 如果第一层是 kunzz，二层不可点（只显示"-"）
    if (branchL1Current === 'all' || branchL1Current === 'kunzz') return;
    const d = document.getElementById('branchL2Dropdown');
    const open = d ? (d.style.display !== 'none') : false;
    closeBranchDropdowns();
    if (d && !open) d.style.display = 'block';
}

function closeBranchDropdowns() {
    const d1 = document.getElementById('branchL1Dropdown');
    const d2 = document.getElementById('branchL2Dropdown');
    if (d1) d1.style.display = 'none';
    if (d2) d2.style.display = 'none';
}

function selectBranchL1(value, label) {
    branchL1Current = value;
    branchL2Current = 'all';

    const lbl1 = document.getElementById('branchL1Label');
    if (lbl1) lbl1.textContent = label;

    // 更新 active 样式
    document.querySelectorAll('.bl1-item').forEach(item => {
        if (item.getAttribute('data-value') === value) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });

    const l2Btn = document.getElementById('branchL2Btn');
    const l2Label = document.getElementById('branchL2Label');
    const l2Chevron = l2Btn ? l2Btn.querySelector('.fa-chevron-down') : null;

    if (l2Btn && l2Label) {
        if (value === 'all' || value === 'kunzz') {
            l2Label.textContent = '-';
            l2Btn.style.cursor = 'default';
            l2Btn.style.opacity = '0.45';
            if (l2Chevron) l2Chevron.style.display = 'none';
        } else {
            l2Label.textContent = '全部分店';
            l2Btn.style.cursor = 'pointer';
            l2Btn.style.opacity = '1';
            if (l2Chevron) l2Chevron.style.display = '';
            buildBranchL2Dropdown();
        }
    }

    closeBranchDropdowns();
    applyBranchFilter();
}

function selectBranchL2(value, label) {
    branchL2Current = value;
    const lbl2 = document.getElementById('branchL2Label');
    if (lbl2) lbl2.textContent = label;

    // 更新 active 样式
    document.querySelectorAll('.bl2-item').forEach(item => {
        if (item.getAttribute('data-value') === value) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });

    closeBranchDropdowns();
    applyBranchFilter();
}

function buildBranchL2Dropdown() {
    const d = document.getElementById('branchL2Dropdown');
    if (!d) return;
    const options = branchL2Options['branch'];
    d.innerHTML = options.map(opt =>
        `<div class="bl2-item ${branchL2Current === opt.value ? 'active' : ''}" 
              data-value="${opt.value}"
              onclick="selectBranchL2('${opt.value}','${opt.label}')">
            ${opt.label}
        </div>`
    ).join('');
}

function applyBranchFilter() {
    const tableBody = document.getElementById('tableBody');
    if (!tableBody) return;
    const rows = tableBody.getElementsByTagName('tr');

    for (let row of rows) {
        if (row.cells.length <= 1) continue;  // 跳过加载中/空行

        // 读取该行的 branch 值（来自 data-branch 或从行数据推断）
        const rowBranch = (row.getAttribute('data-branch') || '').toLowerCase().trim();

        let show = true;

        if (branchL1Current === 'kunzz') {
            // 只显示显式属于 kh 的职员
            show = (rowBranch.split(',').includes('kh'));
        } else if (branchL1Current === 'branch') {
            // 只显示有具体分店 (j1/j2/j3) 的职员
            const branches = rowBranch.split(',').map(b => b.trim()).filter(b => b !== '' && b !== 'null');
            const storeBranches = branches.filter(b => b !== 'kh');
            
            if (storeBranches.length === 0) {
                show = false;
            } else if (branchL2Current !== 'all') {
                show = storeBranches.includes(branchL2Current);
            }
        }
        // 'all' → 全部显示

        if (show) {
            row.classList.remove('hidden-row');
        } else {
            row.classList.add('hidden-row');
        }
    }
    reindexTable();
}

// 点击页面其他地方关闭 dropdown
document.addEventListener('click', function (e) {
    const wrap = document.querySelector('.branch-filter-wrap');
    if (wrap && !wrap.contains(e.target)) {
        closeBranchDropdowns();
    }
});

// 重新排列序号
function reindexTable() {
    const tableBody = document.getElementById('tableBody');
    if (!tableBody) return;
    const rows = tableBody.getElementsByTagName('tr');
    let visibleIndex = 1;
    for (let row of rows) {
        if (!row.classList.contains('hidden-row') && row.cells.length > 1) {
            if (row.cells[0]) {
                row.cells[0].textContent = visibleIndex++;
            }
        }
    }
}

// hover 样式注入
(function () {
    const style = document.createElement('style');
    style.textContent = '.bl1-item:hover, .bl2-item:hover { background: #f9fafb !important; }';
    document.head.appendChild(style);
})();

// 全局变量存储原始数据
let originalTableData = [];

// 实时过滤表格（搜索英文姓名和邮箱列）
function filterTable(searchTerm) {
    const tableBody = document.getElementById('tableBody');
    if (!tableBody) return;
    const rows = tableBody.getElementsByTagName('tr');

    // 如果没有搜索词，恢复并重新应用分类筛选
    if (!searchTerm || !searchTerm.trim()) {
        for (let row of rows) {
            row.classList.remove('hidden-row');
        }
        // 重新应用分类筛选
        if (typeof applyBranchFilter === 'function') applyBranchFilter();
        return;
    }

    const searchLower = searchTerm.toLowerCase();

    // 遍历每一行进行过滤
    for (let row of rows) {
        // 跳过加载中或无数据的行
        if (row.cells.length === 1 && row.cells[0].colSpan > 1) {
            continue;
        }

        // 检查英文姓名列（第3列，索引为2）和邮箱列（第4列，索引为3）
        const usernameCell = row.cells[2]; // 英文姓名列
        const emailCell = row.cells[3]; // 邮箱列

        let isMatch = false;

        // 检查英文姓名
        if (usernameCell) {
            const usernameText = usernameCell.textContent.toLowerCase();
            if (usernameText.includes(searchLower)) {
                isMatch = true;
            }
        }

        // 检查邮箱
        if (!isMatch && emailCell) {
            const emailText = emailCell.textContent.toLowerCase();
            if (emailText.includes(searchLower)) {
                isMatch = true;
            }
        }

        // 显示或隐藏行
        if (isMatch) {
            row.classList.remove('hidden-row');
        } else {
            row.classList.add('hidden-row');
        }
    }
    reindexTable();
}

// 清除搜索
function clearSearch() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) searchInput.value = '';
    filterTable('');
}

// 回到顶部功能
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// 打开编辑模态框
function openEditModal(id) {
    const row = document.getElementById(`row-${id}`);
    if (!row) return;
    const userData = JSON.parse(row.getAttribute('data-user').replace(/&apos;/g, "'"));

    // 先展示模态框以便获取尺寸
    const modal = document.getElementById('editUserModal');
    if (!modal) return;
    modal.style.display = 'block';

    // 填充表单数据
    document.getElementById('edit_user_id').value = userData.id;
    document.getElementById('edit_username').value = userData.username || '';
    document.getElementById('edit_username_cn').value = userData.username_cn || '';
    document.getElementById('edit_nickname').value = userData.nickname || '';
    document.getElementById('edit_email').value = userData.email || '';
    document.getElementById('edit_ic_number').value = userData.ic_number || '';
    document.getElementById('edit_date_of_birth').value = userData.date_of_birth || '';
    document.getElementById('edit_nationality').value = userData.nationality || '';
    document.getElementById('edit_gender').value = userData.gender || '';
    document.getElementById('edit_race').value = userData.race || '';
    document.getElementById('edit_phone_number').value = userData.phone_number || '';
    document.getElementById('edit_home_address').value = userData.home_address || '';
    document.getElementById('edit_bank_account_holder_en').value = userData.bank_account_holder_en || '';
    document.getElementById('edit_bank_account').value = userData.bank_account || '';
    document.getElementById('edit_bank_name').value = userData.bank_name || '';
    document.getElementById('edit_emergency_contact_name').value = userData.emergency_contact_name || '';
    document.getElementById('edit_emergency_phone_number').value = userData.emergency_phone_number || '';
    document.getElementById('edit_account_type').value = userData.account_type || '';
    
    // 设置分店多选框
    const branchCheckboxes = modal.querySelectorAll('input[name="branch[]"]');
    const userBranches = userData.branch ? userData.branch.split(',').map(b => b.trim()) : [];
    branchCheckboxes.forEach(cb => {
        cb.checked = userBranches.includes(cb.value);
    });
    
    // 刷新多选框头部文字
    const multiSelect = modal.querySelector('.custom-multi-select');
    if (multiSelect) refreshMultiSelectHeader(multiSelect);

    // 先设置账号类型，然后更新职位选项
    if (userData.account_type) {
        updatePositionOptions(userData.account_type, 'edit_position');
        // 在职位选项加载后设置职位值
        setTimeout(() => {
            const posSelect = document.getElementById('edit_position');
            if (posSelect) posSelect.value = userData.position || '';
        }, 50);
    }

    // 添加账号类型变化监听器
    const accountTypeSelect = document.getElementById('edit_account_type');
    if (accountTypeSelect) {
        accountTypeSelect.addEventListener('change', function () {
            updatePositionOptions(this.value, 'edit_position');
        });
    }

    // 添加输入格式化
    const fieldsToFormat = [
        'username', 'username_cn', 'email', 'ic_number',
        'phone_number', 'emergency_phone_number', 'bank_account',
        'bank_account_holder_en', 'emergency_contact_name', 'home_address'
    ];

    fieldsToFormat.forEach(field => {
        const input = document.getElementById(`edit_${field}`);
        if (input) {
            addInputFormatting(input, field);
        }
    });
}

// 关闭编辑模态框
function closeEditUserModal() {
    const modal = document.getElementById('editUserModal');
    if (modal) modal.style.display = 'none';
    const form = document.getElementById('editUserForm');
    if (form) form.reset();
}

// 处理编辑表单提交
async function handleEditUserSubmit(e) {
    e.preventDefault();

    const form = document.getElementById('editUserForm');
    if (!form) return;
    const formData = new FormData(form);
    const userData = {};

    // 收集表单数据
    for (let [key, value] of formData.entries()) {
        if (key === 'branch[]') continue; // 单独处理分店数组
        userData[key] = value.trim();
    }

    // 处理分店多选
    const checkedBranches = Array.from(form.querySelectorAll('input[name="branch[]"]:checked')).map(cb => cb.value);
    userData['branch'] = checkedBranches.join(',');

    // 验证必填字段
    if (!userData.username || !userData.email || !userData.account_type) {
        showMessage('请填写所有必填字段（英文姓名、邮箱、账号类型）！', 'error');
        return;
    }

    // 验证所有字段格式
    const fieldsToValidate = ['username', 'username_cn', 'email'];

    for (let field of fieldsToValidate) {
        if (userData[field] && !validateField(field, userData[field])) {
            const fieldNames = {
                'username': '英文姓名需要至少两个单词',
                'username_cn': '中文姓名需要至少两个字',
                'email': '邮箱格式不正确'
            };
            showMessage(fieldNames[field], 'error');
            return;
        }
    }

    // 显示加载状态
    const submitBtn = form.querySelector('.btn-save');
    const originalText = submitBtn ? submitBtn.innerHTML : '';
    if (submitBtn) {
        submitBtn.innerHTML = '<div class="loading"></div>保存中...';
        submitBtn.disabled = true;
    }

    try {
        const response = await fetch('generatecodeapi.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update',
                id: userData.user_id,
                ...userData
            })
        });

        const result = await response.json();

        if (result.success) {
            showMessage('修改成功！', 'success');
            closeEditUserModal();
            loadCodesAndUsers(); // 刷新表格
        } else {
            showMessage(result.message || '修改失败！', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('网络错误，请检查连接！', 'error');
    } finally {
        // 恢复按钮状态
        if (submitBtn) {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }
}

// 确认删除
function confirmDelete(id, username) {
    // 先关闭已存在的模态框
    closeModal();

    // 创建模态框
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.id = `deleteModal_${id}`; // 添加唯一ID
    modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <i class="fas fa-exclamation-triangle"></i> 确认删除
                    </div>
                    <div class="modal-body">
                        确定要删除职员 "<strong style="color: #f44336;">${username}</strong>" 吗？<br><br>
                        <strong style="color: #ff9800;">⚠️ 此操作不可撤销！</strong>
                    </div>
                    <div class="modal-buttons">
                        <button class="btn-action btn-delete" onclick="deleteRowAndClose(${id})">
                            <i class="fas fa-trash"></i> 确认删除
                        </button>
                        <button class="btn-action btn-cancel" onclick="closeModal()">
                            <i class="fas fa-times"></i> 取消
                        </button>
                    </div>
                </div>
            `;

    document.body.appendChild(modal);
    modal.style.display = 'block';

    // 点击模态框外部关闭
    modal.onclick = function (event) {
        if (event.target === modal) {
            closeModal();
        }
    };

    // ESC 键关闭
    const escHandler = function (e) {
        if (e.key === 'Escape') {
            closeModal();
            document.removeEventListener('keydown', escHandler);
        }
    };
    document.addEventListener('keydown', escHandler);
}

// 获取字段最大长度
function getFieldMaxLength(field) {
    const maxLengths = {
        'username': 50,
        'username_cn': 100,
        'nickname': 50,
        'nationality': 50,
        'position': 100,
        'emergency_contact_name': 100,
        'bank_name': 100,
        'bank_account_holder_en': 50,
        'race': 50
    };
    return maxLengths[field] || 100;
}

// 获取字段占位符文本
function getFieldPlaceholder(field) {
    const placeholders = {
        'username': '全名（英）',
        'username_cn': '全名（中）',
        'nickname': '小名',
        'nationality': '国籍',
        'position': '职位',
        'emergency_contact_name': '紧急联络人',
        'bank_name': '银行名称',
        'bank_account_holder_en': '银行持有人',
        'race': '种族'
    };
    return placeholders[field] || field;
}

// 关闭模态框
function closeModal() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.style.display = 'none';
        // 延迟移除，确保动画完成
        setTimeout(() => {
            if (modal.parentNode) {
                modal.parentNode.removeChild(modal);
            }
        }, 100);
    });
}

// 删除行数据
async function deleteRow(id) {
    try {
        const response = await fetch('generatecodeapi.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'delete',
                id: id
            })
        });

        const result = await response.json();

        if (result.success) {
            showMessage('删除成功！', 'success');
            loadCodesAndUsers(); // 重新加载数据
        } else {
            showMessage(result.message || '删除失败！', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('网络错误，请检查连接！', 'error');
    }
}

// 获取账号类型的键值（用于取消编辑时）
function getAccountTypeKey(displayName) {
    const typeMap = {
        '老板': 'boss',
        '管理员': 'admin',
        '人事部': 'hr',
        '设计部': 'design',
        '支援部': 'support',
        '技术部': 'IT',
        '摄影部': 'photograph',
        '研发部': 'r&d',
        '生产部': 'production'
    };
    return typeMap[displayName] || displayName;
}

// 打开添加职员模态框
function openAddUserModal() {
    const modal = document.getElementById('addUserModal');
    if (!modal) return;
    modal.style.display = 'block';

    // 重置表单
    const form = document.getElementById('addUserForm');
    if (form) form.reset();

    // 重置并初始化权限树及验证系统
    resetPermissionTree(modal);
    initPermissionTreeEvents(modal);

    // 强制赋予所有权限并触发表单校验状态
    setDefaultAllPermissions(modal);
    updatePermissionValidationState(modal);

    // 重置职位选择框
    const positionSelect = document.getElementById('add_position');
    if (positionSelect) {
        positionSelect.innerHTML = '<option value="">请先选择账号类型</option>';
        positionSelect.disabled = true;
    }

    // 添加账号类型变化监听器
    const accountTypeSelect = document.getElementById('add_account_type');
    if (accountTypeSelect) {
        accountTypeSelect.addEventListener('change', function () {
            updatePositionOptions(this.value, 'add_position');
        });
    }
    
    // 初始化分店多选框标题
    const multiSelect = modal.querySelector('.custom-multi-select');
    if (multiSelect) refreshMultiSelectHeader(multiSelect);

    // 添加输入格式化
    const fieldsToFormat = [
        'username', 'username_cn', 'email', 'ic_number',
        'phone_number', 'emergency_phone_number', 'bank_account',
        'bank_account_holder_en', 'emergency_contact_name', 'home_address'
    ];

    fieldsToFormat.forEach(field => {
        const input = document.getElementById(`add_${field}`);
        if (input) {
            addInputFormatting(input, field);
        }
    });
}

// 关闭添加职员模态框
function closeAddUserModal() {
    const modal = document.getElementById('addUserModal');
    if (modal) modal.style.display = 'none';
    const form = document.getElementById('addUserForm');
    if (form) form.reset();
}

// 修改 addNewUser 函数，添加更多调试信息
async function addNewUser() {
    const form = document.getElementById('addUserForm');
    if (!form) return;
    const formData = new FormData(form);
    const userData = {};

    // 收集表单数据
    for (let [key, value] of formData.entries()) {
        if (key === 'branch[]') continue;
        userData[key] = value.trim();
    }

    // 处理分店多选
    const checkedBranches = Array.from(form.querySelectorAll('input[name="branch[]"]:checked')).map(cb => cb.value);
    userData['branch'] = checkedBranches.join(',');

    console.log('发送的数据:', userData); // 调试信息

    // 验证必填字段
    if (!userData.username || !userData.email || !userData.account_type) {
        showMessage('请填写所有必填字段（英文姓名、邮箱、账号类型）！', 'error');
        return;
    }

    // 验证所有字段格式
    const fieldsToValidate = ['username', 'username_cn', 'email'];

    for (let field of fieldsToValidate) {
        if (userData[field] && !validateField(field, userData[field])) {
            const fieldNames = {
                'username': '英文姓名需要至少两个单词',
                'username_cn': '中文姓名需要至少两个字',
                'email': '邮箱格式不正确'
            };
            showMessage(fieldNames[field], 'error');
            return;
        }
    }

    // 提取权限数据
    const modal = document.getElementById('addUserModal');
    const checkedBoxes = Array.from(modal.querySelectorAll('.perm-l1-check, .perm-l2-check, .perm-stock-system, .perm-stock-view, .perm-upload-system, .perm-upload-type, .perm-page-schedule, .perm-page-blueprint'))
        .filter(cb => cb.checked && !cb.disabled);

    if (checkedBoxes.length === 0) {
        const warningDiv = modal.querySelector('.perm-warning');
        if (warningDiv) {
            warningDiv.style.display = 'block';
        }
        showMessage('请至少选择一项用户权限', 'error');
        return;
    }

    const { perms, submenuPermissions, pagePermissions, brandPermissions, reportPermissions, restaurantPermissions } = extractPermissionsData(modal);

    // 显示加载状态
    const submitBtn = form.querySelector('.btn-save');
    const originalText = submitBtn ? submitBtn.innerHTML : '';
    if (submitBtn) {
        submitBtn.innerHTML = '<div class="loading"></div>添加中...';
        submitBtn.disabled = true;
    }

    try {
        const response = await fetch('generatecodeapi.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add_user',
                permissions: perms,
                page_permissions: pagePermissions,
                submenu_permissions: submenuPermissions,
                report_permissions: reportPermissions,
                restaurant_permissions: restaurantPermissions,
                brand_permissions: brandPermissions,
                ...userData
            })
        });

        console.log('响应状态:', response.status); // 调试信息

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        console.log('服务器响应:', result); // 调试信息

        if (result.success) {
            let message = `职员 "${result.data.username}" 添加成功！`;
            if (result.data.email_sent) {
                message += ` 登录信息已发送到 ${result.data.email}`;
            } else {
                message += ` 申请码：${result.data.code}，临时密码：${result.data.default_password}`;
            }
            showMessage(message, 'success');
            closeAddUserModal();
            loadCodesAndUsers(); // 刷新表格
        } else {
            showMessage(result.message || '添加失败，请重试！', 'error');
        }
    } catch (error) {
        console.error('详细错误信息:', error);
        showMessage(`网络错误：${error.message}`, 'error');
    } finally {
        // 恢复按钮状态
        if (submitBtn) {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }
}

// 删除行数据并关闭模态框
async function deleteRowAndClose(id) {
    // 显示删除中状态
    const modal = document.querySelector('.modal');
    const deleteBtn = modal ? modal.querySelector('.btn-delete') : null;
    const cancelBtn = modal ? modal.querySelector('.btn-cancel') : null;

    // 禁用按钮并显示加载状态
    if (deleteBtn) {
        deleteBtn.innerHTML = '<div class="loading"></div>删除中...';
        deleteBtn.disabled = true;
    }
    if (cancelBtn) cancelBtn.disabled = true;

    try {
        const response = await fetch('generatecodeapi.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'delete',
                id: id
            })
        });

        const result = await response.json();

        // 确保关闭模态框
        closeModal();

        if (result.success) {
            showMessage('删除成功！', 'success');
            loadCodesAndUsers(); // 重新加载数据
        } else {
            showMessage(result.message || '删除失败！', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        closeModal(); // 确保出错时也关闭模态框
        showMessage('网络错误，请检查连接！', 'error');
    }
}

// 监听滚动事件，控制回到顶部按钮显示
let scrollTimeout;
window.addEventListener('scroll', function () {
    // 使用防抖优化性能
    clearTimeout(scrollTimeout);
    scrollTimeout = setTimeout(function () {
        const backToTopBtn = document.getElementById('back-to-top-btn');
        const scrollThreshold = 150; // 滚动超过150px后显示按钮

        if (backToTopBtn) {
            if (window.pageYOffset > scrollThreshold) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        }
    }, 10);
});

// 用户权限逻辑 - 重构版
const sidebarSubOptions = {
    analytics: ['kpi_report', 'kpi_upload'],
    hr: ['staff_management'],
    resource: ['stock_inventory', 'dishware', 'price_comparison'],
    brand: ['kunzz_holdings', 'tokyo_cuisine', 'tokyo_izakaya']
};
// const STOCK_SYSTEM_KEYS = ['central', 'j1', 'j2', 'j3'];
// const STOCK_VIEW_KEYS = ['list', 'records', 'remark', 'product', 'sot'];
// const reportTypeOptions = ['kpi', 'cost'];
// const restaurantOptions = ['j1', 'j2', 'j3'];

// 初始化权限树事件监听器
function initPermissionTreeEvents(container) {
    if (!container) return;

    // 如果已经绑定过，直接返回
    if (container.dataset.permValidationInit === 'true') return;
    container.dataset.permValidationInit = 'true';

    // 绑定所有的 checkbox 的 change 事件来触发验证
    const allCheckboxes = container.querySelectorAll('.perm-l1-check, .perm-l2-check, .perm-stock-system, .perm-stock-view, .perm-upload-system, .perm-upload-type, .perm-page-schedule, .perm-page-blueprint');
    allCheckboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            updatePermissionValidationState(container);
        });
    });

    // 初始验证状态
    updatePermissionValidationState(container);

    // 阻止label的默认行为，防止点击label时触发checkbox
    container.querySelectorAll('.perm-checkbox-label').forEach(label => {
        label.addEventListener('click', function (e) {
            // 如果点击的是checkbox，允许默认行为
            if (e.target.tagName === 'INPUT') {
                return;
            }
            // 点击其他部分（文字、箭头等），只阻止默认行为，不阻止冒泡
            e.preventDefault();
        });
    });

    // 额外权限区域的label也需要阻止
    container.querySelectorAll('.extra-perm-section label').forEach(label => {
        label.addEventListener('click', function (e) {
            if (e.target.tagName !== 'INPUT') {
                e.preventDefault();
            }
        });
    });

    // 三级面板的label也需要阻止
    container.querySelectorAll('.perm-detail-content label').forEach(label => {
        label.addEventListener('click', function (e) {
            if (e.target.tagName !== 'INPUT') {
                e.preventDefault();
            }
        });
    });

    // 四级分类点击展开/折叠
    container.querySelectorAll('.perm-level-4-item').forEach(item => {
        item.addEventListener('click', function (e) {
            // 如果点击的是复选框，不处理展开
            if (e.target.tagName === 'INPUT') {
                e.stopPropagation();
                return;
            }

            const subContainer = item.querySelector('.perm-level-4-container');
            if (!subContainer) return;

            const isCurrentlyExpanded = item.classList.contains('expanded');

            // 如果当前项未展开，先关闭所有其他四级分类
            if (!isCurrentlyExpanded) {
                container.querySelectorAll('.perm-level-4-item.expanded').forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('expanded');
                        const otherContainer = otherItem.querySelector('.perm-level-4-container');
                        if (otherContainer) {
                            otherContainer.classList.remove('expanded');
                        }
                    }
                });
            }

            // 切换当前项的展开状态
            item.classList.toggle('expanded');
            subContainer.classList.toggle('expanded');
        });
    });

    // 一级分类点击展开/折叠
    container.querySelectorAll('.perm-level-1-item').forEach(item => {
        item.addEventListener('click', function (e) {
            // 如果点击的是复选框，不处理展开
            if (e.target.tagName === 'INPUT') {
                e.stopPropagation();
                return;
            }

            const parent = item.getAttribute('data-perm');
            const subContainer = container.querySelector(`.perm-level-2-container[data-parent="${parent}"]`);
            const isCurrentlyExpanded = item.classList.contains('expanded');
            const detailContent = container.querySelector('.perm-detail-content');
            const placeholder = container.querySelector('.perm-detail-placeholder');
            const hasLevel3 = item.classList.contains('has-level-3');
            const sub = item.getAttribute('data-sub');

            // 如果是一级分类有三级配置
            if (hasLevel3 && sub) {
                // 先尝试寻找当前项内部的内联面板
                let panel = item.querySelector('.perm-level-3-panel-inline');

                // 如果没找到内联面板，则去外部详细卡片找
                if (!panel) {
                    panel = container.querySelector(`.perm-detail-content .perm-level-3-panel[data-for="${sub}"]`);
                }

                // 如果当前项未展开，先关闭所有其他一级分类和三级面板
                if (!isCurrentlyExpanded) {
                    container.querySelectorAll('.perm-level-1-item.expanded').forEach(otherItem => {
                        if (otherItem !== item) {
                            otherItem.classList.remove('expanded');
                            const otherParent = otherItem.getAttribute('data-perm');
                            const otherContainer = container.querySelector(`.perm-level-2-container[data-parent="${otherParent}"]`);
                            if (otherContainer) {
                                otherContainer.classList.remove('expanded');
                            }
                        }
                    });

                    // 关闭所有有三级配置的二级项和右侧详细配置卡片
                    container.querySelectorAll('.perm-level-2-item.has-level-3.expanded').forEach(level2Item => {
                        level2Item.classList.remove('expanded');
                    });
                    container.querySelectorAll('.perm-level-1-item.has-level-3.expanded').forEach(level1Item => {
                        if (level1Item !== item) {
                            level1Item.classList.remove('expanded');
                        }
                    });

                    // 统一处理面板隐藏
                    container.querySelectorAll('.perm-level-3-panel, .perm-level-3-panel-inline').forEach(p => {
                        if (p !== panel) p.classList.remove('show');
                    });
                }

                // 切换当前项的展开状态
                item.classList.toggle('expanded');

                // 切换三级面板显示
                if (panel) {
                    const isPanelShowing = panel.classList.contains('show');
                    if (!isPanelShowing) {
                        // 显示面板
                        container.querySelectorAll('.perm-level-3-panel, .perm-level-3-panel-inline').forEach(p => p.classList.remove('show'));
                        panel.classList.add('show');
                        if (detailContent) detailContent.classList.add('active');
                        if (placeholder) placeholder.classList.add('hidden');
                    } else {
                        // 隐藏面板
                        panel.classList.remove('show');
                        if (detailContent) detailContent.classList.remove('active');
                        if (placeholder) placeholder.classList.remove('hidden');
                    }
                }
                return;
            }

            // 普通一级分类（有二级容器）
            if (!subContainer) return;

            // 如果当前项未展开，先关闭所有其他一级分类
            if (!isCurrentlyExpanded) {
                container.querySelectorAll('.perm-level-1-item.expanded').forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('expanded');
                        const otherParent = otherItem.getAttribute('data-perm');
                        const otherContainer = container.querySelector(`.perm-level-2-container[data-parent="${otherParent}"]`);
                        if (otherContainer) {
                            otherContainer.classList.remove('expanded');
                        }
                    }
                });

                // 关闭所有有三级配置的二级项和右侧详细配置卡片
                container.querySelectorAll('.perm-level-2-item.has-level-3.expanded').forEach(level2Item => {
                    level2Item.classList.remove('expanded');
                });
                container.querySelectorAll('.perm-level-1-item.has-level-3.expanded').forEach(level1Item => {
                    level1Item.classList.remove('expanded');
                });
                container.querySelectorAll('.perm-level-3-panel').forEach(p => {
                    p.classList.remove('show');
                });
                if (detailContent) detailContent.classList.remove('active');
                if (placeholder) placeholder.classList.remove('hidden');
            }

            // 切换当前项的展开状态
            item.classList.toggle('expanded');
            subContainer.classList.toggle('expanded');
        });
    });

    // 一级复选框变化 - 同步二级权限状态
    container.querySelectorAll('.perm-l1-check').forEach(checkbox => {
        if (!checkbox.dataset.fromChild) {
            checkbox.dataset.fromChild = 'false';
        }

        checkbox.addEventListener('change', function () {
            const parentValue = this.value;
            const isChecked = this.checked;
            const isFromChild = this.dataset.fromChild === 'true';

            // 重置标记
            this.dataset.fromChild = 'false';

            if (!isFromChild) {
                syncLevel2Permissions(container, parentValue, isChecked);
            }
        });
    });

    // 二级复选框变化 - 检查父级状态并同步三级权限
    container.querySelectorAll('.perm-l2-check').forEach(checkbox => {
        if (!checkbox.dataset.fromChild) {
            checkbox.dataset.fromChild = 'false';
        }

        checkbox.addEventListener('change', function () {
            const level2Value = this.value;
            const isChecked = this.checked;
            const parent = this.dataset.parent;
            const isFromChild = this.dataset.fromChild === 'true';

            // 重置标记
            this.dataset.fromChild = 'false';

            // 检查父级状态
            const parentCheckbox = container.querySelector(`.perm-l1-check[value="${parent}"]`);
            if (parentCheckbox && !parentCheckbox.checked && isChecked) {
                parentCheckbox.dataset.fromChild = 'true';
                parentCheckbox.checked = true;
                parentCheckbox.dispatchEvent(new Event('change'));
            }

            // 同步三级权限（仅在不是从子级触发时，才向下联动）
            if (!isFromChild) {
                syncLevel3Permissions(container, level2Value, isChecked);
            }

            // 取消勾选时，若无其他同级，则向上取消父级
            if (!isChecked) {
                const otherChildren = container.querySelectorAll(`.perm-l2-check[data-parent="${parent}"]:checked`);
                if (otherChildren.length === 0 && parentCheckbox) {
                    parentCheckbox.dataset.fromChild = 'true';
                    parentCheckbox.checked = false;
                    parentCheckbox.dispatchEvent(new Event('change'));
                }
            }
        });
    });

    // 二级有三级的项目 - 在右侧卡片显示三级面板
    container.querySelectorAll('.perm-level-2-item.has-level-3').forEach(item => {
        item.addEventListener('click', function (e) {
            // 如果点击的是复选框，不处理展开
            if (e.target.tagName === 'INPUT') {
                e.stopPropagation();
                return;
            }

            const sub = item.getAttribute('data-sub');
            const detailContent = container.querySelector('.perm-detail-content');
            const placeholder = container.querySelector('.perm-detail-placeholder');
            const isCurrentlyExpanded = item.classList.contains('expanded');

            // 先尝试寻找当前项内部的内联面板
            let panel = item.querySelector('.perm-level-3-panel-inline');

            // 如果没找到内联面板，则去外部详细卡片找
            if (!panel) {
                panel = container.querySelector(`.perm-detail-content .perm-level-3-panel[data-for="${sub}"]`);
            }

            // 如果当前项未展开，先关闭所有其他有三级配置的二级项
            if (!isCurrentlyExpanded) {
                container.querySelectorAll('.perm-level-2-item.has-level-3.expanded').forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('expanded');
                        container.querySelectorAll('.perm-level-3-panel, .perm-level-3-panel-inline').forEach(p => {
                            p.classList.remove('show');
                        });
                    }
                });
            }

            // 关闭所有三级面板（除了当前要显示的）
            container.querySelectorAll('.perm-level-3-panel, .perm-level-3-panel-inline').forEach(p => {
                if (p !== panel) p.classList.remove('show');
            });

            // 切换当前面板
            item.classList.toggle('expanded');

            if (!isCurrentlyExpanded) {
                // 展开：显示内容
                if (panel) panel.classList.add('show');
                if (detailContent) detailContent.classList.add('active');
                if (placeholder) placeholder.classList.add('hidden');
            } else {
                // 折叠：关闭面板
                if (panel) panel.classList.remove('show');
                if (detailContent) detailContent.classList.remove('active');
                if (placeholder) placeholder.classList.remove('hidden');
            }
        });
    });

    // 店面项展开/收缩功能
    container.querySelectorAll('.perm-store-item').forEach(item => {
        const label = item.querySelector('.perm-checkbox-label');
        if (label) {
            label.addEventListener('click', function (e) {
                // 如果点击的是checkbox，不处理展开
                if (e.target.tagName === 'INPUT') {
                    return;
                }
                e.preventDefault();
                e.stopPropagation();
                item.classList.toggle('expanded');
            });
        }
    });

    // 三级页面权限和库存/上传权限的向上联动
    container.querySelectorAll('.perm-stock-system, .perm-stock-view, .perm-upload-system, .perm-upload-type, .perm-page-schedule, .perm-page-blueprint').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            let level2Value = '';
            if (this.classList.contains('perm-stock-system') || this.classList.contains('perm-stock-view')) {
                level2Value = 'stock_inventory';
            } else if (this.classList.contains('perm-upload-system') || this.classList.contains('perm-upload-type')) {
                level2Value = 'kpi_upload';
            } else if (this.classList.contains('perm-page-schedule')) {
                level2Value = this.dataset.brand || '';
            } else if (this.classList.contains('perm-page-blueprint')) {
                level2Value = this.dataset.brand || '';
            }

            if (!level2Value) return;

            const level2Checkbox = container.querySelector(`.perm-l2-check[value="${level2Value}"]`);
            if (!level2Checkbox) return;

            if (this.checked) {
                if (!level2Checkbox.checked) {
                    level2Checkbox.dataset.fromChild = 'true';
                    level2Checkbox.checked = true;
                    level2Checkbox.dispatchEvent(new Event('change'));
                }
            } else {
                let otherChecked = 0;
                if (level2Value === 'stock_inventory') {
                    otherChecked = container.querySelectorAll('.perm-stock-system:checked, .perm-stock-view:checked').length;
                } else if (level2Value === 'kpi_upload') {
                    otherChecked = container.querySelectorAll('.perm-upload-system:checked, .perm-upload-type:checked').length;
                } else if (level2Value === 'kunzz_holdings') {
                    otherChecked = container.querySelectorAll(`.perm-page-blueprint[data-brand="${level2Value}"]:checked`).length;
                } else {
                    otherChecked = container.querySelectorAll(`.perm-page-schedule[data-brand="${level2Value}"]:checked`).length;
                }

                if (otherChecked === 0) {
                    level2Checkbox.dataset.fromChild = 'true';
                    level2Checkbox.checked = false;
                    level2Checkbox.dispatchEvent(new Event('change'));
                }
            }
        });
    });
}

// 设置默认全选所有权限
function setDefaultAllPermissions(container) {
    if (!container) return;

    // 先确保所有checkbox都是active的（不设置disabled）
    container.querySelectorAll('input[type="checkbox"]').forEach(cb => {
        cb.disabled = false;
    });

    // 全选所有一级权限
    container.querySelectorAll('.perm-l1-check').forEach(cb => {
        cb.checked = true;
    });

    // 全选所有二级权限
    container.querySelectorAll('.perm-l2-check').forEach(cb => {
        cb.checked = true;
    });

    // 全选所有三级权限
    container.querySelectorAll('.perm-stock-system, .perm-stock-view, .perm-upload-system, .perm-upload-type, .perm-page-schedule, .perm-page-blueprint').forEach(cb => {
        cb.checked = true;
    });
}

// 权限校验系统
function updatePermissionValidationState(container) {
    if (!container) return;

    const submitBtn = container.querySelector('button[type="submit"]');
    const warningDiv = container.querySelector('.perm-warning');

    const checkedBoxes = Array.from(container.querySelectorAll('.perm-l1-check, .perm-l2-check, .perm-stock-system, .perm-stock-view, .perm-upload-system, .perm-upload-type, .perm-page-schedule, .perm-page-blueprint'))
        .filter(cb => cb.checked && !cb.disabled);

    const hasSelection = checkedBoxes.length > 0;

    if (container.id === 'addUserModal' || container.id === 'editUserModal' || container.id === 'permissionsModal') {
        if (warningDiv) {
            warningDiv.style.display = hasSelection ? 'none' : 'block';
        }
        if (submitBtn) {
            submitBtn.disabled = !hasSelection;
            submitBtn.style.opacity = hasSelection ? '1' : '0.5';
            submitBtn.style.cursor = hasSelection ? 'pointer' : 'not-allowed';
        }
    }
}


// 清空并折叠权限树
function resetPermissionTree(container) {
    if (!container) return;

    container.querySelectorAll('input[type="checkbox"]').forEach(cb => {
        cb.checked = false;
        cb.dataset.fromChild = 'false';
    });

    container.querySelectorAll('.expanded').forEach(item => item.classList.remove('expanded'));
    container.querySelectorAll('.show').forEach(item => item.classList.remove('show'));

    const detailContent = container.querySelector('.perm-detail-content');
    const placeholder = container.querySelector('.perm-detail-placeholder');
    if (detailContent) detailContent.classList.remove('active');
    if (placeholder) placeholder.classList.remove('hidden');

    updatePermissionValidationState(container);
}

// 同步二级权限状态
function syncLevel2Permissions(container, parentValue, parentChecked) {
    container.querySelectorAll(`.perm-l2-check[data-parent="${parentValue}"]`).forEach(cb => {
        if (parentChecked) {
            if (!cb.checked) {
                cb.checked = true;
            }
            syncLevel3Permissions(container, cb.value, true);
        } else {
            cb.checked = false;
            syncLevel3Permissions(container, cb.value, false);
        }
    });
}

// 同步三级权限状态
function syncLevel3Permissions(container, level2Value, level2Checked) {
    if (level2Value === 'stock_inventory') {
        container.querySelectorAll('.perm-stock-system, .perm-stock-view').forEach(cb => {
            cb.checked = level2Checked;
        });
    }
    if (level2Value === 'kpi_upload') {
        container.querySelectorAll('.perm-upload-system, .perm-upload-type').forEach(cb => {
            cb.checked = level2Checked;
        });
    }
    if (level2Value === 'kunzz_holdings') {
        container.querySelectorAll('.perm-page-blueprint[data-brand="kunzz_holdings"]').forEach(cb => {
            cb.checked = level2Checked;
        });
    }
    if (level2Value === 'tokyo_cuisine') {
        container.querySelectorAll('.perm-page-schedule[data-store="j1"], .perm-page-schedule[data-store="j2"]').forEach(cb => {
            cb.checked = level2Checked;
        });
    }
    if (level2Value === 'tokyo_izakaya') {
        container.querySelectorAll('.perm-page-schedule[data-store="j3"]').forEach(cb => {
            cb.checked = level2Checked;
        });
    }
}

function openPermissionsModal(userId) {
    if (!userId) {
        showMessage('无效的用户ID', 'error');
        return;
    }

    const modal = document.getElementById('permissionsModal');
    const userIdInput = document.getElementById('perm_current_user_id');
    const titleEl = document.getElementById('perm_modal_title');

    if (userIdInput) userIdInput.value = userId;

    const user = originalTableData.find(u => u.id == userId);
    if (titleEl) {
        titleEl.textContent = user ? `用户权限设定 - ${user.username || '未命名用户'}` : '用户权限设定';
    }

    loadUserPermissions(userId);
    initPermissionTreeEvents(modal);

    if (modal) modal.style.display = 'block';
}

function closePermissionsModal() {
    const modal = document.getElementById('permissionsModal');
    if (!modal) return;

    resetPermissionTree(modal);

    const titleEl = document.getElementById('perm_modal_title');
    if (titleEl) titleEl.textContent = '用户权限设定';
    const userIdInput = document.getElementById('perm_current_user_id');
    if (userIdInput) userIdInput.value = '';

    modal.style.display = 'none';
}

function setPermCheckboxes(perms, pagePerms, submenuPerms, reportPerms, restaurantPerms, brandPerms) {
    const values = new Set(Array.isArray(perms) ? perms : []);

    document.querySelectorAll('.perm-l1-check').forEach(cb => {
        cb.checked = values.has(cb.value);
    });

    const submenuData = (submenuPerms && typeof submenuPerms === 'object') ? submenuPerms : {};
    document.querySelectorAll('.perm-l2-check').forEach(cb => {
        const parent = cb.dataset.parent;
        const parentEnabled = values.has(parent);
        const source = submenuData[parent];
        const allowed = Array.isArray(source) ? source : (sidebarSubOptions[parent] || []);
        cb.checked = parentEnabled && allowed.includes(cb.value);
    });

    const stockPagePerms = (pagePerms && typeof pagePerms === 'object') ? (pagePerms.stock_inventory || {}) : {};
    const stockSystems = Array.isArray(stockPagePerms.system) ? stockPagePerms.system : [];
    const stockViews = Array.isArray(stockPagePerms.views) ? stockPagePerms.views : (Array.isArray(stockPagePerms.view) ? stockPagePerms.view : []);
    const systemSet = new Set(stockSystems);
    const viewSet = new Set(stockViews);

    document.querySelectorAll('.perm-stock-system').forEach(cb => cb.checked = systemSet.has(cb.value));
    document.querySelectorAll('.perm-stock-view').forEach(cb => cb.checked = viewSet.has(cb.value));

    const uploadPagePerms = (pagePerms && typeof pagePerms === 'object') ? (pagePerms.kpi_upload || {}) : {};
    const uploadSystems = Array.isArray(uploadPagePerms.system) ? uploadPagePerms.system : [];
    const uploadTypes = Array.isArray(uploadPagePerms.type) ? uploadPagePerms.type : [];
    const uploadSysSet = new Set(uploadSystems);
    const uploadTypeSet = new Set(uploadTypes);

    document.querySelectorAll('.perm-upload-system').forEach(cb => cb.checked = uploadSysSet.has(cb.value));
    document.querySelectorAll('.perm-upload-type').forEach(cb => cb.checked = uploadTypeSet.has(cb.value));

    const brandData = (brandPerms && typeof brandPerms === 'object') ? brandPerms : {};

    if (brandData.kunzz_holdings && brandData.kunzz_holdings.blueprint) {
        document.querySelectorAll('.perm-page-blueprint[data-brand="kunzz_holdings"]').forEach(cb => {
            cb.checked = brandData.kunzz_holdings.blueprint.includes('blueprint');
        });
    }

    const cuisinePerms = brandData.tokyo_cuisine || {};
    document.querySelectorAll('.perm-page-schedule[data-store="j1"]').forEach(cb => {
        cb.checked = (cuisinePerms.j1 && cuisinePerms.j1.includes('schedule'));
    });
    document.querySelectorAll('.perm-page-schedule[data-store="j2"]').forEach(cb => {
        cb.checked = (cuisinePerms.j2 && cuisinePerms.j2.includes('schedule'));
    });

    const izakayaPerms = brandData.tokyo_izakaya || {};
    document.querySelectorAll('.perm-page-schedule[data-store="j3"]').forEach(cb => {
        cb.checked = (izakayaPerms.j3 && izakayaPerms.j3.includes('schedule'));
    });

    const rSet = new Set(Array.isArray(reportPerms) ? reportPerms : []);
    document.querySelectorAll('.perm-report').forEach(cb => cb.checked = rSet.has(cb.value));

    const resSet = new Set(Array.isArray(restaurantPerms) ? restaurantPerms : []);
    document.querySelectorAll('.perm-restaurant').forEach(cb => cb.checked = resSet.has(cb.value));

    document.querySelectorAll('#permissionsModal input[type="checkbox"]').forEach(cb => cb.disabled = false);
}

async function loadUserPermissions(userId) {
    try {
        const modal = document.getElementById('permissionsModal');
        setDefaultAllPermissions(modal);

        const res = await fetch('generatecodeapi.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'get_permissions', user_id: userId })
        });
        const data = await res.json();
        if (data.success) {
            setPermCheckboxes(
                data.permissions || [],
                data.page_permissions || {},
                data.submenu_permissions || {},
                data.report_permissions || [],
                data.restaurant_permissions || [],
                data.brand_permissions || {}
            );
        }
    } catch (e) {
        console.error('加载权限失败:', e);
    }
}

async function savePermissions() {
    const userIdInput = document.getElementById('perm_current_user_id');
    const userId = userIdInput ? userIdInput.value : '';
    const modal = document.getElementById('permissionsModal');

    const { perms, submenuPermissions, pagePermissions, brandPermissions, reportPermissions, restaurantPermissions } = extractPermissionsData(modal);

    const btn = modal.querySelector('.btn-save');
    const old = btn ? btn.innerHTML : '';
    if (btn) {
        btn.innerHTML = '<div class="loading"></div>保存中...';
        btn.disabled = true;
    }

    try {
        const res = await fetch('generatecodeapi.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'save_permissions',
                user_id: userId,
                permissions: perms,
                page_permissions: pagePermissions,
                submenu_permissions: submenuPermissions,
                report_permissions: reportPermissions,
                restaurant_permissions: restaurantPermissions,
                brand_permissions: brandPermissions
            })
        });
        const data = await res.json();
        if (data.success) {
            showMessage('权限已保存', 'success');
            closePermissionsModal();
        } else {
            showMessage(data.message || '保存失败', 'error');
        }
    } catch (e) {
        showMessage('网络错误，稍后重试', 'error');
    } finally {
        if (btn) {
            btn.innerHTML = old;
            btn.disabled = false;
        }
    }
}

function extractPermissionsData(container) {
    if (!container) return {};

    const perms = Array.from(container.querySelectorAll('.perm-l1-check:checked')).map(cb => cb.value);

    // Submenu permissions (L2 checkboxes)
    const submenuPermissions = {};
    container.querySelectorAll('.perm-l1-check').forEach(l1 => {
        const parent = l1.value;
        const selectedSubs = Array.from(container.querySelectorAll(`.perm-l2-check[data-parent="${parent}"]:checked`)).map(cb => cb.value);
        submenuPermissions[parent] = selectedSubs;
    });

    // Page permissions (Detail panel checkboxes)
    const pagePermissions = {
        kpi_upload: {
            system: Array.from(container.querySelectorAll('.perm-level-3-panel[data-for="kpi_upload"] .perm-stock-system:checked')).map(cb => cb.value),
            views: Array.from(container.querySelectorAll('.perm-level-3-panel[data-for="kpi_upload"] .perm-stock-view:checked')).map(cb => cb.value)
        },
        stock_inventory: {
            system: Array.from(container.querySelectorAll('.perm-level-3-panel[data-for="stock_inventory"] .perm-stock-system:checked')).map(cb => cb.value),
            views: Array.from(container.querySelectorAll('.perm-level-3-panel[data-for="stock_inventory"] .perm-stock-view:checked')).map(cb => cb.value)
        },
        annual_summary: {
            system: Array.from(container.querySelectorAll('.perm-level-3-panel[data-for="annual_summary"] .perm-annual-system:checked')).map(cb => cb.value)
        },
        branch_comparison: {
            views: Array.from(container.querySelectorAll('.perm-level-3-panel[data-for="branch_comparison"] .perm-comp-view:checked')).map(cb => cb.value)
        }
    };

    // Store/Brand permissions
    const brandPermissions = {
        kunzz_holdings: container.querySelector('.perm-page-blueprint[data-brand="kunzz_holdings"]')?.checked ? { blueprint: ['blueprint'] } : {},
        tokyo_cuisine: {
            j1: container.querySelector('.perm-page-schedule[data-store="j1"][data-brand="tokyo_cuisine"]')?.checked ? ['schedule'] : [],
            j2: container.querySelector('.perm-page-schedule[data-store="j2"][data-brand="tokyo_cuisine"]')?.checked ? ['schedule'] : []
        },
        tokyo_izakaya: {
            j3: container.querySelector('.perm-page-schedule[data-store="j3"][data-brand="tokyo_izakaya"]')?.checked ? ['schedule'] : []
        }
    };

    return {
        perms,
        submenuPermissions,
        pagePermissions,
        brandPermissions,
        reportPermissions: [],
        restaurantPermissions: []
    };
}

function openDownloadModal() {
    const modal = document.getElementById('downloadModal');
    if (modal) {
        const companySelect = document.getElementById('company_select');
        if (companySelect) companySelect.value = '';
        modal.style.display = 'block';
    }
}

function closeDownloadModal() {
    const modal = document.getElementById('downloadModal');
    if (modal) modal.style.display = 'none';
}

function confirmDownload() {
    const select = document.getElementById('company_select');
    if (!select) return;
    const company = select.value;

    if (!company) {
        showMessage('请选择一个公司/店铺', 'warning');
        return;
    }

    const pdfFiles = {
        'KUNZZHOLDINGS': '../form/kh.pdf',
        'TOKYO_J1': '../form/j1.pdf',
        'TOKYO_J2': '../form/j2.pdf',
        'TOKYO_J3': '../form/j3.pdf'
    };

    const pdfPath = pdfFiles[company];

    if (pdfPath) {
        const link = document.createElement('a');
        link.href = pdfPath;
        link.download = pdfPath.split('/').pop();
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        showMessage(`正在下载 ${select.options[select.selectedIndex].text} 的申请表...`, 'success');
        closeDownloadModal();
    } else {
        showMessage('下载失败，文件不存在', 'error');
    }
}

const downloadModalEl = document.getElementById('downloadModal');
if (downloadModalEl) {
    downloadModalEl.onclick = function (event) {
        if (event.target === this) {
            closeDownloadModal();
        }
    };
}