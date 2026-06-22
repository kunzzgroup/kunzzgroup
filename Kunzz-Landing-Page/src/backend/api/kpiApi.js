import { getBackendApiUrl } from '../../config.js';

const API_URL = getBackendApiUrl('kpiapi.php');

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

export async function fetchKpiList(restaurant, startDate, endDate) {
  const params = new URLSearchParams({
    action: 'list',
    restaurant,
    start_date: startDate,
    end_date: endDate,
  });
  const result = await apiCall(`?${params.toString()}`);
  return result.data || [];
}

export async function fetchAllJRestaurants(startDate, endDate) {
  const restaurants = ['j1', 'j2', 'j3'];
  const results = await Promise.all(
    restaurants.map(async (restaurant) => {
      try {
        const data = await fetchKpiList(restaurant, startDate, endDate);
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
