import { useCallback, useEffect, useMemo, useState } from 'react';

import {
  fetchAllJCostRestaurants,
  fetchCostList,
  fetchCostSummary,
  fetchStockData,
  fetchSupplyData,
} from '../api/costApi.js';
import { RESTAURANT_CONFIG } from '../config/costConfig.js';
import { getDefaultDateRange, getMonthRangeFromKey, getQuickRangeDates } from '../utils/dateUtils.js';
import {
  EMPTY_COST_SUMMARY,
  aggregateDataByPeriod,
  buildCostChartSeries,
  computeCostSummaryFromFiltered,
  convertToCostFormat,
  fillMissingDates,
  filterByDateRange,
  finalizeCostSummary,
  mergeAllRestaurantsCostData,
} from '../utils/costCalculations.js';

export function useCostDashboard() {
  const [restaurant, setRestaurant] = useState(null);
  const [restaurantLabel, setRestaurantLabel] = useState('--');
  const [dateRange, setDateRange] = useState(getDefaultDateRange);
  const [chartDataType, setChartDataType] = useState('totalCost');
  const [rawData, setRawData] = useState([]);
  const [allRestaurantsData, setAllRestaurantsData] = useState({});
  const [apiSummary, setApiSummary] = useState({});
  const [stockData, setStockData] = useState({ last_stock: 0, current_stock: 0 });
  const [supplyData, setSupplyData] = useState({ j2_supply: 0, j3_supply: 0 });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [isDrillDownMode, setIsDrillDownMode] = useState(false);
  const [originalDateRange, setOriginalDateRange] = useState(null);

  const loadData = useCallback(async (nextRestaurant, nextDateRange) => {
    if (!nextRestaurant) return;

    setLoading(true);
    setError('');

    try {
      const summaryPromise = fetchCostSummary(
        nextRestaurant,
        nextDateRange.startDate,
        nextDateRange.endDate,
      );
      const stockPromise = fetchStockData(
        nextRestaurant,
        nextDateRange.startDate,
        nextDateRange.endDate,
      );

      if (nextRestaurant === 'total') {
        const [mergedSource, summary, stock] = await Promise.all([
          fetchAllJCostRestaurants(nextDateRange.startDate, nextDateRange.endDate),
          summaryPromise,
          stockPromise,
        ]);
        setAllRestaurantsData(mergedSource);
        setRawData(mergeAllRestaurantsCostData(mergedSource));
        setApiSummary(summary);
        setStockData(stock);
        setSupplyData({ j2_supply: 0, j3_supply: 0 });
      } else {
        const [data, summary, stock] = await Promise.all([
          fetchCostList(nextRestaurant, nextDateRange.startDate, nextDateRange.endDate),
          summaryPromise,
          stockPromise,
        ]);
        setAllRestaurantsData({});
        setRawData(data);
        setApiSummary(summary);
        setStockData(stock);

        if (nextRestaurant === 'j1') {
          const supply = await fetchSupplyData(nextDateRange.startDate, nextDateRange.endDate);
          setSupplyData({
            j2_supply: parseFloat(supply.supply_to_j2 || 0),
            j3_supply: parseFloat(supply.supply_to_j3 || 0),
          });
        } else {
          setSupplyData({ j2_supply: 0, j3_supply: 0 });
        }
      }
    } catch (err) {
      setError(err.message || '加载数据失败');
      setRawData([]);
      setAllRestaurantsData({});
      setApiSummary({});
      setStockData({ last_stock: 0, current_stock: 0 });
      setSupplyData({ j2_supply: 0, j3_supply: 0 });
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    if (!restaurant) return;
    loadData(restaurant, dateRange);
  }, [restaurant, dateRange, loadData]);

  const filteredData = useMemo(() => {
    if (!restaurant) return [];

    const converted = convertToCostFormat(rawData);
    const filled = fillMissingDates(converted, dateRange);
    return filterByDateRange(filled, dateRange.startDate, dateRange.endDate);
  }, [restaurant, rawData, dateRange]);

  const tableData = useMemo(() => {
    if (!restaurant) return [];
    return aggregateDataByPeriod(filteredData, dateRange);
  }, [restaurant, filteredData, dateRange]);

  const summary = useMemo(() => {
    if (!restaurant) return EMPTY_COST_SUMMARY;

    const displaySummary = computeCostSummaryFromFiltered(filteredData, apiSummary, stockData);
    return finalizeCostSummary(displaySummary, restaurant, supplyData);
  }, [filteredData, apiSummary, stockData, restaurant, supplyData]);

  const chartSeries = useMemo(() => {
    if (!restaurant) return null;

    return buildCostChartSeries({
      filteredData,
      allRestaurantsData,
      restaurant,
      dateRange,
      chartDataType,
    });
  }, [filteredData, allRestaurantsData, restaurant, dateRange, chartDataType]);

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
    ? `已选择 ${summary.totalDays || 0} 天的数据 - ${RESTAURANT_CONFIG[restaurant]?.name || restaurantLabel}`
    : '请先选择餐厅';

  return {
    restaurant,
    restaurantLabel,
    dateRange,
    setDateRange,
    chartDataType,
    setChartDataType,
    filteredData: tableData,
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
