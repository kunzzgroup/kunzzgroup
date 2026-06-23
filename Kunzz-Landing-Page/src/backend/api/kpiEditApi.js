import { getBackendApiUrl, getBackendBase } from '../../config.js';

const API_URL = getBackendApiUrl('kpiapi.php');
const CONFIG_URL = `${getBackendBase()}/kpi_edit_config.php`;

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

export async function fetchKpiEditConfig() {
  const result = await apiCall(CONFIG_URL);
  if (!result.success) {
    if (result.redirect) {
      window.location.href = `${getBackendBase()}/${result.redirect}`;
      return null;
    }
    throw new Error(result.message || '加载配置失败');
  }
  return result.data;
}

export async function fetchKpiEditMonthList(restaurant, startDate, endDate) {
  const params = new URLSearchParams({
    action: 'list',
    restaurant,
    start_date: startDate,
    end_date: endDate,
  });
  const result = await apiCall(`${API_URL}?${params.toString()}`);
  return result.data || [];
}

export async function saveKpiRecord(record, isUpdate) {
  return apiCall(API_URL, {
    method: isUpdate ? 'PUT' : 'POST',
    body: JSON.stringify(record),
  });
}

export async function deleteKpiRecord(id, restaurant) {
  const params = new URLSearchParams({
    action: 'delete',
    id: String(id),
    restaurant,
  });
  return apiCall(`${API_URL}?${params.toString()}`, { method: 'DELETE' });
}

export async function refreshSession() {
  const response = await fetch(`${getBackendBase()}/session_refresh_api.php`, {
    credentials: 'include',
  });
  return response.json();
}
