import { getBackendApiUrl, getBackendBase } from '../../config.js';

const API_URL = getBackendApiUrl('generatecodeapi.php');
const CONFIG_URL = `${getBackendBase()}/generatecode_config.php`;

async function apiCall(url, options = {}) {
  const response = await fetch(url, {
    credentials: 'include',
    headers: {
      'Content-Type': 'application/json',
      ...options.headers,
    },
    ...options,
  });

  if (!response.ok) {
    throw new Error(`HTTP ${response.status}`);
  }

  const data = await response.json();
  if (data.code === 'SESSION_EXPIRED') {
    const error = new Error('SESSION_EXPIRED');
    error.code = 'SESSION_EXPIRED';
    throw error;
  }

  return data;
}

export async function fetchGenerateCodeConfig() {
  const result = await apiCall(CONFIG_URL);
  if (!result.success) {
    throw new Error(result.message || '加载配置失败');
  }
  return result.data;
}

export async function fetchStaffList() {
  const result = await apiCall(`${API_URL}?action=list`);
  if (!result.success) {
    throw new Error(result.message || '加载职员列表失败');
  }
  return result.data || [];
}

export async function updateStaffRecord(payload) {
  return apiCall(API_URL, {
    method: 'POST',
    body: JSON.stringify({ action: 'update', ...payload }),
  });
}

export async function deleteStaffRecord(id) {
  return apiCall(API_URL, {
    method: 'POST',
    body: JSON.stringify({ action: 'delete', id }),
  });
}

export async function fetchUserPermissions(userId) {
  return apiCall(API_URL, {
    method: 'POST',
    body: JSON.stringify({ action: 'get_permissions', user_id: userId }),
  });
}

export async function saveUserPermissions(payload) {
  return apiCall(API_URL, {
    method: 'POST',
    body: JSON.stringify({ action: 'save_permissions', ...payload }),
  });
}

export async function addStaffUser(payload) {
  return apiCall(API_URL, {
    method: 'POST',
    body: JSON.stringify({ action: 'add_user', ...payload }),
  });
}

export async function refreshSession() {
  const response = await fetch(`${getBackendBase()}/session_refresh_api.php`, {
    credentials: 'include',
  });
  return response.json();
}
