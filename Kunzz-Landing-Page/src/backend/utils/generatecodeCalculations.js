import { ACCOUNT_TYPE_ORDER, POSITIONS_BY_ACCOUNT_TYPE } from '../config/generatecodeConstants.js';

export function escapeHTML(str) {
  if (str === null || str === undefined) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

export function sortStaff(data) {
  if (!Array.isArray(data)) return [];

  return [...data].sort((a, b) => {
    const orderA = ACCOUNT_TYPE_ORDER[a.account_type] || 999;
    const orderB = ACCOUNT_TYPE_ORDER[b.account_type] || 999;

    if (orderA !== orderB) return orderA - orderB;

    const accountType = a.account_type;
    const positions = POSITIONS_BY_ACCOUNT_TYPE[accountType] || [];
    const positionA = (a.position || '').trim();
    const positionB = (b.position || '').trim();
    const indexA = positions.indexOf(positionA);
    const indexB = positions.indexOf(positionB);

    if (indexA !== -1 && indexB !== -1 && indexA !== indexB) return indexA - indexB;
    if (indexA !== -1 && indexB === -1) return -1;
    if (indexB !== -1 && indexA === -1) return 1;

    if (positionA && positionB) {
      const compare = positionA.localeCompare(positionB);
      if (compare !== 0) return compare;
    } else if (positionA) return -1;
    else if (positionB) return 1;

    const timeA = new Date(a.created_at || 0).getTime();
    const timeB = new Date(b.created_at || 0).getTime();
    if (timeA !== timeB) return timeA - timeB;

    return (a.id || 0) - (b.id || 0);
  });
}

export function parseBranchList(branchStr) {
  if (!branchStr) return [];
  return branchStr.split(',').map((b) => b.trim().toLowerCase()).filter(Boolean);
}

export function matchesBranchFilter(staff, branchL1, branchL2) {
  const branches = parseBranchList(staff.branch);

  if (branchL1 === 'kunzz') {
    return branches.includes('kh');
  }

  if (branchL1 === 'branch') {
    const storeBranches = branches.filter((b) => b !== 'kh');
    if (storeBranches.length === 0) return false;
    if (branchL2 === 'all') return true;
    return storeBranches.includes(branchL2);
  }

  return true;
}

export function matchesSearch(staff, searchTerm) {
  if (!searchTerm?.trim()) return true;
  const searchLower = searchTerm.trim().toLowerCase();
  const username = (staff.username || '').toLowerCase();
  const email = (staff.email || '').toLowerCase();
  return username.includes(searchLower) || email.includes(searchLower);
}

export function filterStaffList(staffList, { searchTerm, branchL1, branchL2 }) {
  return sortStaff(staffList).filter(
    (staff) => matchesSearch(staff, searchTerm) && matchesBranchFilter(staff, branchL1, branchL2),
  );
}

export function formatFieldInput(field, value) {
  if (value === null || value === undefined) return '';

  switch (field) {
    case 'username':
    case 'emergency_contact_name':
    case 'bank_account_holder_en':
    case 'position':
      return String(value).toUpperCase().replace(/[^A-Z\s]/g, '');
    case 'email':
      return String(value).toLowerCase().replace(/[^a-z0-9@.]/g, '');
    case 'ic_number':
    case 'phone_number':
    case 'emergency_phone_number':
    case 'bank_account':
      return String(value).replace(/[^\d]/g, '');
    case 'home_address':
      return String(value).toUpperCase().replace(/[^A-Z0-9\s.,\-#/()]/g, '');
    case 'username_cn':
      return String(value).replace(/[^\u4e00-\u9fff]/g, '');
    default:
      return String(value);
  }
}
  
export function validateStaffField(field, value) {
  if (!value) return true;

  switch (field) {
    case 'username':
    case 'emergency_contact_name':
    case 'bank_account_holder_en':
      return /^[A-Z]+(\s[A-Z]+)+$/.test(value);
    case 'username_cn':
      return /^[\u4e00-\u9fff]{2,}$/.test(value);
    case 'email':
      return /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value);
    default:
      return true;
  }
}

export function validateEditForm(form) {
  if (!form.username?.trim() || !form.email?.trim() || !form.account_type) {
    return '请填写所有必填字段（英文姓名、邮箱、账号类型）！';
  }

  const checks = [
    ['username', '英文姓名需要至少两个单词'],
    ['username_cn', '中文姓名需要至少两个字'],
    ['email', '邮箱格式不正确'],
  ];

  for (const [field, message] of checks) {
    if (form[field] && !validateStaffField(field, form[field])) {
      return message;
    }
  }

  return null;
}

export function staffToEditForm(staff) {
  return {
    user_id: staff.id,
    username: staff.username || '',
    username_cn: staff.username_cn || '',
    nickname: staff.nickname || '',
    email: staff.email || '',
    ic_number: staff.ic_number || '',
    phone_number: staff.phone_number || '',
    date_of_birth: staff.date_of_birth || '',
    gender: staff.gender || '',
    nationality: staff.nationality || '',
    race: staff.race || '',
    home_address: staff.home_address || '',
    bank_account_holder_en: staff.bank_account_holder_en || '',
    bank_account: staff.bank_account || '',
    bank_name: staff.bank_name || '',
    emergency_contact_name: staff.emergency_contact_name || '',
    emergency_phone_number: staff.emergency_phone_number || '',
    account_type: staff.account_type || '',
    position: staff.position || '',
    branch: parseBranchList(staff.branch),
  };
}

export function editFormToPayload(form) {
  return {
    id: form.user_id,
    username: form.username.trim(),
    username_cn: form.username_cn.trim(),
    nickname: form.nickname.trim(),
    email: form.email.trim(),
    ic_number: form.ic_number.trim(),
    phone_number: form.phone_number.trim(),
    date_of_birth: form.date_of_birth,
    gender: form.gender,
    nationality: form.nationality,
    race: form.race,
    home_address: form.home_address.trim(),
    bank_account_holder_en: form.bank_account_holder_en.trim(),
    bank_account: form.bank_account.trim(),
    bank_name: form.bank_name,
    emergency_contact_name: form.emergency_contact_name.trim(),
    emergency_phone_number: form.emergency_phone_number.trim(),
    account_type: form.account_type,
    position: form.position,
    branch: (form.branch || []).join(','),
  };
}

export function createEmptyAddEmployeeForm() {
  return {
    username: '',
    username_cn: '',
    nickname: '',
    email: '',
    ic_number: '',
    phone_number: '',
    gender: '',
    nationality: '',
    race: '',
    home_address: '',
    emergency_contact_name: '',
    emergency_phone_number: '',
    bank_account_holder_en: '',
    bank_account: '',
    bank_name: '',
    account_type: '',
    position: '',
    branch: [],
  };
}

export function validateAddEmployeeForm(form) {
  const fieldErrors = {};

  if (!form.username?.trim() || form.username.trim().split(/\s+/).filter(Boolean).length < 2) {
    fieldErrors.username = true;
  }
  if (!form.email?.trim()) {
    fieldErrors.email = true;
  } else if (!validateStaffField('email', form.email.trim())) {
    fieldErrors.email = true;
  }
  if (!form.account_type) {
    fieldErrors.account_type = true;
  }

  if (Object.keys(fieldErrors).length > 0) {
    return { valid: false, fieldErrors, message: '请填写所有必填项（*）' };
  }

  return { valid: true, fieldErrors, message: null };
}

export function addFormToPayload(form) {
  return {
    username: form.username.trim(),
    username_cn: form.username_cn.trim(),
    nickname: form.nickname.trim(),
    email: form.email.trim(),
    ic_number: form.ic_number.trim(),
    phone_number: form.phone_number.trim(),
    gender: form.gender,
    nationality: form.nationality,
    race: form.race,
    home_address: form.home_address.trim(),
    emergency_contact_name: form.emergency_contact_name.trim(),
    emergency_phone_number: form.emergency_phone_number.trim(),
    bank_account_holder_en: form.bank_account_holder_en.trim(),
    bank_account: form.bank_account.trim(),
    bank_name: form.bank_name,
    account_type: form.account_type,
    position: form.position,
    branch: (form.branch || []).join(','),
  };
}

export function getBranchMultiSelectLabel(selectedBranches) {
  if (!selectedBranches?.length) {
    return { text: '请选择区域运营单位', muted: true };
  }
  if (selectedBranches.length <= 2) {
    return { text: selectedBranches.map((b) => b.toUpperCase()).join(', '), muted: false };
  }
  return { text: `已选择 ${selectedBranches.length} 个单位`, muted: false };
}
