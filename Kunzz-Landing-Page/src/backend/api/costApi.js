import { getBackendApiUrl } from '../../config.js';

const API_URL = getBackendApiUrl('costapi.php');

async function apiCall(queryString, options = {}) {
  const response = await fetch(`${API_URL}${queryString}`, {
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

  return response.json();
}

export async function fetchCostList(restaurant, startDate, endDate) {
  const params = new URLSearchParams({
    action: 'list',
    restaurant,
    start_date: startDate,
    end_date: endDate,
  });
  const result = await apiCall(`?${params.toString()}`);
  return result.data || [];
}

export async function fetchCostSummary(restaurant, startDate, endDate) {
  const params = new URLSearchParams({
    action: 'summary',
    restaurant,
    start_date: startDate,
    end_date: endDate,
  });
  const result = await apiCall(`?${params.toString()}`);
  return result.data || {};
}

export async function fetchSupplyData(startDate, endDate) {
  const params = new URLSearchParams({
    action: 'get_supply',
    restaurant: 'j1',
    start_date: startDate,
    end_date: endDate,
  });
  const result = await apiCall(`?${params.toString()}`);
  return result.data || { supply_to_j2: 0, supply_to_j3: 0 };
}

export async function fetchMonthStock(restaurant, yearMonth) {
  const params = new URLSearchParams({
    action: 'get_month_stock',
    restaurant,
    year_month: yearMonth,
  });
  const result = await apiCall(`?${params.toString()}`);
  return result.data || null;
}

/** Matches legacy cost.js loadStockData — uses cost_month_stock, not getStockSupplyData. */
export async function fetchStockData(restaurant, startDate, endDate) {
  const end = new Date(endDate);
  const currentYearMonth = `${end.getFullYear()}-${String(end.getMonth() + 1).padStart(2, '0')}`;
  const lastMonthDate = new Date(end.getFullYear(), end.getMonth() - 1, 1);
  const lastYearMonth = `${lastMonthDate.getFullYear()}-${String(lastMonthDate.getMonth() + 1).padStart(2, '0')}`;

  if (restaurant === 'total') {
    const restaurants = ['j1', 'j2', 'j3'];
    const results = await Promise.all(
      restaurants.map(async (key) => {
        const [current, last] = await Promise.all([
          fetchMonthStock(key, currentYearMonth),
          fetchMonthStock(key, lastYearMonth),
        ]);
        return {
          current: parseFloat(current?.current_stock || 0),
          last: parseFloat(last?.current_stock || 0),
        };
      }),
    );

    return results.reduce(
      (acc, item) => ({
        last_stock: acc.last_stock + item.last,
        current_stock: acc.current_stock + item.current,
      }),
      { last_stock: 0, current_stock: 0 },
    );
  }

  const [current, last] = await Promise.all([
    fetchMonthStock(restaurant, currentYearMonth),
    fetchMonthStock(restaurant, lastYearMonth),
  ]);

  return {
    current_stock: parseFloat(current?.current_stock || 0),
    last_stock: parseFloat(last?.current_stock || 0),
  };
}

export async function fetchAllJCostRestaurants(startDate, endDate) {
  const restaurants = ['j1', 'j2', 'j3'];
  const results = await Promise.all(
    restaurants.map(async (restaurant) => {
      try {
        const data = await fetchCostList(restaurant, startDate, endDate);
        return { restaurant, data };
      } catch {
        return { restaurant, data: [] };
      }
    }),
  );

  return results.reduce((acc, { restaurant, data }) => {
    acc[restaurant] = data;
    return acc;
  }, {});
}
