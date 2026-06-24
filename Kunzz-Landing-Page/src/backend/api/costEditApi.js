import { getBackendApiUrl, getBackendBase } from '../../config.js';
import { fetchKpiList } from './kpiApi.js';
import { fetchCostList, fetchMonthStock } from './costApi.js';

const COST_API_URL = getBackendApiUrl('costapi.php');
const CONFIG_URL = `${getBackendBase()}/cost_edit_config.php`;

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

export async function fetchCostEditConfig() {
  const result = await apiCall(CONFIG_URL);
  if (!result.success) {
    if (result.redirect) {
      window.location.href = `${getBackendBase()}/${result.redirect}-v2`;
      return null;
    }
    throw new Error(result.message || '加载配置失败');
  }
  return result.data;
}

export async function fetchCostEditMonthData(restaurant, startDate, endDate) {
  const [costResult, kpiData] = await Promise.all([
    apiCall(
      `${COST_API_URL}?${new URLSearchParams({
        action: 'list',
        restaurant,
        start_date: startDate,
        end_date: endDate,
      }).toString()}`,
    ),
    fetchKpiList(restaurant, startDate, endDate).catch(() => []),
  ]);

  return {
    costData: costResult.data || [],
    kpiData,
    costLoadSucceeded: costResult.success !== false,
  };
}

export async function fetchCostEditMonthStock(restaurant, yearMonth) {
  return fetchMonthStock(restaurant, yearMonth);
}

export async function getExistingCostRecordByDate(restaurant, dateStr) {
  const result = await apiCall(
    `${COST_API_URL}?${new URLSearchParams({
      action: 'list',
      restaurant,
      search_date: dateStr,
    }).toString()}`,
  );
  if (result.success && Array.isArray(result.data) && result.data.length > 0) {
    return result.data[0];
  }
  return null;
}

export async function saveCostRecord(record, isUpdate) {
  return apiCall(COST_API_URL, {
    method: isUpdate ? 'PUT' : 'POST',
    body: JSON.stringify(record),
  });
}

export async function saveCostRecordWithFallback(record, getExistingByDate) {
  if (record.id) {
    return saveCostRecord(record, true);
  }

  let result = await saveCostRecord(record, false);
  if (result.success === false && String(result.message || '').includes('已存在')) {
    const existing = await getExistingByDate(record.date);
    if (existing?.id) {
      result = await saveCostRecord({ ...record, id: existing.id }, true);
      return { result, existing };
    }
  }

  return { result, existing: null };
}

export async function saveMonthStockData(restaurant, yearMonth, currentStock) {
  return apiCall(`${COST_API_URL}?action=save_month_stock`, {
    method: 'POST',
    body: JSON.stringify({
      year_month: yearMonth,
      current_stock: parseFloat(currentStock) || 0,
      restaurant,
    }),
  });
}

export async function refreshSession() {
  const response = await fetch(`${getBackendBase()}/session_refresh_api.php`, {
    credentials: 'include',
  });
  return response.json();
}
