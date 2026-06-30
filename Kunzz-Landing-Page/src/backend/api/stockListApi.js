import { getBackendApiUrl, getBackendBase } from '../../config.js';

const STOCK_API_URL = getBackendApiUrl('stocklistapi.php');
const MINIMUM_API_URL = getBackendApiUrl('stockminimumapi.php');
const PERMISSIONS_API_URL = getBackendApiUrl('generatecodeapi.php');
const CONFIG_URL = `${getBackendBase()}/stocklistall_config.php`;

async function apiCall(url, options = {}) {
  const response = await fetch(url, {
    credentials: 'include',
    cache: 'no-store',
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

function withSystem(system, endpoint) {
  const separator = endpoint.includes('?') ? '&' : '?';
  return `${STOCK_API_URL}${endpoint}${separator}system=${system}`;
}

export async function fetchStockListConfig() {
  const result = await apiCall(CONFIG_URL);
  if (!result.success) {
    throw new Error(result.message || '加载配置失败');
  }
  return result.data;
}

export async function fetchPagePermissions() {
  return apiCall(PERMISSIONS_API_URL, {
    method: 'POST',
    body: JSON.stringify({ action: 'get_page_permissions' }),
  });
}

export async function fetchStockSummary(system, { startDate, endDate } = {}) {
  let endpoint = '?action=summary';
  if (endDate) endpoint += `&end_date=${endDate}`;
  if (startDate) endpoint += `&start_date=${startDate}`;

  const result = await apiCall(withSystem(system, endpoint));
  if (!result.success) {
    throw new Error(result.message || '获取数据失败');
  }
  return result.data;
}

export async function fetchSupplyTotal(system) {
  const result = await apiCall(withSystem(system, '?action=supply_total'));
  if (!result.success) {
    throw new Error(result.message || '获取供应统计失败');
  }
  return result.data;
}

export async function fetchLowStockAlerts() {
  const result = await apiCall(withSystem('central', '?action=low_stock_alerts'));
  if (!result.success) {
    throw new Error(result.message || '获取低库存预警失败');
  }
  return result.data;
}

export async function fetchLowStockSettings() {
  const timestamp = Date.now();
  const result = await apiCall(`${MINIMUM_API_URL}?action=list&_t=${timestamp}`, {
    headers: {
      'Cache-Control': 'no-cache',
      Pragma: 'no-cache',
    },
  });

  if (!result.success) {
    throw new Error(result.message || '加载最低库存设置失败');
  }

  const settings = {};
  (result.data || []).forEach((item) => {
    const productName = (item.product_name || '').trim();
    if (!productName) return;
    const minQty = parseFloat(item.minimum_quantity);
    if (Number.isNaN(minQty)) return;
    if (!settings[productName] || minQty > settings[productName]) {
      settings[productName] = minQty;
    }
  });

  return settings;
}

export async function refreshSession() {
  const response = await fetch(`${getBackendBase()}/session_refresh_api.php`, {
    credentials: 'include',
  });
  return response.json();
}
