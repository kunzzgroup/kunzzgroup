import { useCallback, useEffect, useMemo, useState } from 'react';

import { fetchAllJRestaurants, fetchKpiList } from '../api/kpiApi.js';
import { RESTAURANT_CONFIG } from '../config/kpiConfig.js';
import { getDefaultDateRange, getMonthRangeFromKey, getQuickRangeDates } from '../utils/dateUtils.js';
import {
  aggregateDataByPeriod,
  computeSummary,
  convertToKpiFormat,
  filterByDateRange,
  mergeAllRestaurantsData,
  prepareComparisonSeries,
} from '../utils/kpiCalculations.js';

export function useKpiDashboard() {
  const [restaurant, setRestaurant] = useState(null);
  const [restaurantLabel, setRestaurantLabel] = useState('--');
  const [dateRange, setDateRange] = useState(getDefaultDateRange);
  const [chartDataType, setChartDataType] = useState('netSales');
  const [rawData, setRawData] = useState([]);
  const [allRestaurantsData, setAllRestaurantsData] = useState({});
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [isDrillDownMode, setIsDrillDownMode] = useState(false);
  const [originalDateRange, setOriginalDateRange] = useState(null);

  const loadData = useCallback(async (nextRestaurant, nextDateRange) => {
    if (!nextRestaurant) return;

    setLoading(true);
    setError('');

    try {
      if (nextRestaurant === 'total') {
        const mergedSource = await fetchAllJRestaurants(nextDateRange.startDate, nextDateRange.endDate);
        setAllRestaurantsData(mergedSource);
        setRawData(mergeAllRestaurantsData(mergedSource));
      } else {
        const data = await fetchKpiList(nextRestaurant, nextDateRange.startDate, nextDateRange.endDate);
        setAllRestaurantsData({});
        setRawData(data);
      }
    } catch (err) {
      setError(err.message || '加载数据失败');
      setRawData([]);
      setAllRestaurantsData({});
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    if (!restaurant) return;
    loadData(restaurant, dateRange);
  }, [restaurant, dateRange, loadData]);

  const filteredData = useMemo(
    () => filterByDateRange(convertToKpiFormat(rawData), dateRange.startDate, dateRange.endDate),
    [rawData, dateRange],
  );

  const summary = useMemo(() => computeSummary(filteredData), [filteredData]);

  const chartSeries = useMemo(() => {
    if (!restaurant) return null;

    const aggregated = aggregateDataByPeriod(filteredData, dateRange);

    if (restaurant === 'total') {
      return prepareComparisonSeries(allRestaurantsData, dateRange, chartDataType);
    }

    return {
      labels: aggregated.map((item) =>
        item.displayDate || String(new Date(item.date).getDate()),
      ),
      monthKeys: aggregated.filter((item) => item.displayDate).map((item) => item.date),
      isMonthly: aggregated.some((item) => item.displayDate),
      datasets: [
        {
          restaurant,
          data: aggregated.map((item) => {
            switch (chartDataType) {
              case 'netSales':
                return item.netSales;
              case 'tables':
                return item.tablesUsed;
              case 'returningRate':
                return item.returningRate;
              case 'diners':
                return item.diners;
              default:
                return item.netSales;
            }
          }),
        },
      ],
    };
  }, [restaurant, filteredData, dateRange, chartDataType, allRestaurantsData]);

  const selectRestaurant = useCallback((letter, number) => {
    if (number === 'total') {
      setRestaurant('total');
      setRestaurantLabel(`${letter}总`);
      return;
    }

    const key = `${letter.toLowerCase()}${number}`;
    setRestaurant(key);
    setRestaurantLabel(`${letter}${number}`);
  }, []);

  const applyQuickRange = useCallback((rangeId) => {
    const nextRange = getQuickRangeDates(rangeId);
    if (!nextRange) return;
    setIsDrillDownMode(false);
    setOriginalDateRange(null);
    setDateRange(nextRange);
  }, []);

  const enterDrillDown = useCallback(
    (monthKey) => {
      if (isDrillDownMode) return;
      setOriginalDateRange(dateRange);
      setIsDrillDownMode(true);
      setDateRange(getMonthRangeFromKey(monthKey));
    },
    [dateRange, isDrillDownMode],
  );

  const exitDrillDown = useCallback(() => {
    if (!originalDateRange) return;
    setDateRange(originalDateRange);
    setOriginalDateRange(null);
    setIsDrillDownMode(false);
  }, [originalDateRange]);

  const dateInfo = restaurant
    ? `已选择 ${summary.totalDays} 天的数据 - ${RESTAURANT_CONFIG[restaurant]?.name || restaurantLabel}`
    : '请先选择餐厅';

  return {
    restaurant,
    restaurantLabel,
    dateRange,
    setDateRange,
    chartDataType,
    setChartDataType,
    filteredData,
    summary,
    chartSeries,
    loading,
    error,
    isDrillDownMode,
    selectRestaurant,
    applyQuickRange,
    enterDrillDown,
    exitDrillDown,
    dateInfo,
  };
}
