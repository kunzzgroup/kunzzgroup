import { RESTAURANT_COLORS } from '../config/costConfig.js';

export function convertToCostFormat(data) {
  return data.map((item) => {
    const sales = parseFloat(item.sales) || 0;
    const cBeverage = parseFloat(item.c_beverage) || 0;
    const cKitchen = parseFloat(item.c_kitchen) || 0;
    const cGrab = parseFloat(item.c_grab) || 0;
    const cFoodpanda = parseFloat(item.c_foodpanda) || 0;
    const cShopee = parseFloat(item.c_shopee) || 0;
    const cTotal = cBeverage + cKitchen;
    const finalSales = sales + (cGrab + cFoodpanda + cShopee) / 2;
    const grossTotal = finalSales - cTotal;
    const costPercent = finalSales > 0 ? (cTotal / finalSales) * 100 : 0;

    return {
      date: item.date,
      sales: finalSales,
      cBeverage,
      cKitchen,
      cGrab,
      cFoodpanda,
      cShopee,
      cTotal,
      grossTotal,
      costPercent,
    };
  });
}

export function fillMissingDates(costData, dateRange) {
  if (!dateRange?.startDate || !dateRange?.endDate) {
    return costData;
  }

  const start = new Date(dateRange.startDate);
  const end = new Date(dateRange.endDate);
  if (start > end) return [];

  const costDataMap = new Map(costData.map((item) => [item.date, item]));
  const filledData = [];

  for (let current = new Date(start); current <= end; current.setDate(current.getDate() + 1)) {
    const dateKey = current.toISOString().split('T')[0];
    filledData.push(
      costDataMap.get(dateKey) || {
        date: dateKey,
        sales: 0,
        cBeverage: 0,
        cKitchen: 0,
        cGrab: 0,
        cFoodpanda: 0,
        cShopee: 0,
        cTotal: 0,
        grossTotal: 0,
        costPercent: 0,
      },
    );
  }

  return filledData;
}

export function filterByDateRange(data, startDate, endDate) {
  const start = new Date(startDate);
  const end = new Date(endDate);
  return data
    .filter((item) => {
      const itemDate = new Date(item.date);
      return itemDate >= start && itemDate <= end;
    })
    .sort((a, b) => new Date(a.date) - new Date(b.date));
}

export function mergeAllRestaurantsCostData(allRestaurantsData) {
  const dateMap = new Map();

  Object.values(allRestaurantsData).forEach((restaurantData) => {
    restaurantData.forEach((item) => {
      const date = item.date;
      if (!dateMap.has(date)) {
        dateMap.set(date, {
          date,
          sales: 0,
          c_beverage: 0,
          c_kitchen: 0,
          c_grab: 0,
          c_foodpanda: 0,
          c_shopee: 0,
        });
      }

      const existing = dateMap.get(date);
      existing.sales += parseFloat(item.sales) || 0;
      existing.c_beverage += parseFloat(item.c_beverage) || 0;
      existing.c_kitchen += parseFloat(item.c_kitchen) || 0;
      existing.c_grab += parseFloat(item.c_grab) || 0;
      existing.c_foodpanda += parseFloat(item.c_foodpanda) || 0;
      existing.c_shopee += parseFloat(item.c_shopee) || 0;
    });
  });

  return Array.from(dateMap.values()).sort((a, b) => new Date(a.date) - new Date(b.date));
}

export function aggregateByMonth(data) {
  const monthMap = new Map();

  data.forEach((item) => {
    const date = new Date(item.date);
    const monthKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;

    if (!monthMap.has(monthKey)) {
      monthMap.set(monthKey, {
        date: monthKey,
        displayDate: `${date.getFullYear()}年${date.getMonth() + 1}月`,
        sales: 0,
        cBeverage: 0,
        cKitchen: 0,
        cGrab: 0,
        cFoodpanda: 0,
        cShopee: 0,
        cTotal: 0,
        grossTotal: 0,
        daysCount: 0,
      });
    }

    const monthData = monthMap.get(monthKey);
    monthData.sales += item.sales;
    monthData.cBeverage += item.cBeverage;
    monthData.cKitchen += item.cKitchen;
    monthData.cGrab += item.cGrab || 0;
    monthData.cFoodpanda += item.cFoodpanda || 0;
    monthData.cShopee += item.cShopee || 0;
    monthData.cTotal += item.cTotal;
    monthData.grossTotal += item.grossTotal;
    monthData.daysCount += 1;
  });

  return Array.from(monthMap.values())
    .map((item) => ({
      ...item,
      costPercent: item.sales > 0 ? (item.cTotal / item.sales) * 100 : 0,
    }))
    .sort((a, b) => a.date.localeCompare(b.date));
}

export function aggregateDataByPeriod(data, dateRange) {
  const startDate = new Date(dateRange.startDate);
  const endDate = new Date(dateRange.endDate);
  const daysDiff = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
  return daysDiff > 60 ? aggregateByMonth(data) : data;
}

export function getChartDataByType(item, dataType) {
  switch (dataType) {
    case 'costPercent':
      return item.costPercent;
    case 'grossTotal':
      return item.grossTotal;
    case 'totalCost':
      return item.cTotal;
    default:
      return item.cTotal;
  }
}

function getDeliveryTotal(item) {
  return (item.cGrab || 0) + (item.cFoodpanda || 0) + (item.cShopee || 0);
}

function buildGrossSplitDatasets(data) {
  const rawData = data.map((item) => item.grossTotal);
  const positiveData = rawData.map((val) => (val >= 0 ? val : 0));
  const negativeData = rawData.map((val) => (val < 0 ? val : 0));

  return [
    {
      label: '盈利',
      data: positiveData,
      borderColor: '#22c55e',
      backgroundColor: 'rgba(34, 197, 94, 0.25)',
      fill: 'origin',
      tension: 0.4,
      pointRadius: 0,
    },
    {
      label: '亏损',
      data: negativeData,
      borderColor: '#ef4444',
      backgroundColor: 'rgba(239, 68, 68, 0.25)',
      fill: 'origin',
      tension: 0.4,
      pointRadius: 0,
    },
  ];
}

function buildDeliveryPlatformDatasets(data) {
  return [
    {
      label: 'Grab 成本',
      data: data.map((item) => item.cGrab || 0),
      borderColor: '#00B14F',
      backgroundColor: 'rgba(0, 177, 79, 0.2)',
      fill: true,
      tension: 0.4,
      pointRadius: 0,
    },
    {
      label: 'Foodpanda 成本',
      data: data.map((item) => item.cFoodpanda || 0),
      borderColor: '#D70F64',
      backgroundColor: 'rgba(215, 15, 100, 0.2)',
      fill: true,
      tension: 0.4,
      pointRadius: 0,
    },
    {
      label: 'Shopee 成本',
      data: data.map((item) => item.cShopee || 0),
      borderColor: '#EE4D2D',
      backgroundColor: 'rgba(238, 77, 45, 0.2)',
      fill: true,
      tension: 0.4,
      pointRadius: 0,
    },
  ];
}

function buildSingleRestaurantDatasets(data, chartDataType) {
  if (chartDataType === 'grossTotal') {
    return buildGrossSplitDatasets(data);
  }

  if (chartDataType === 'deliveryCost') {
    return buildDeliveryPlatformDatasets(data);
  }

  const labels = {
    totalCost: '总成本',
    costPercent: '成本率',
  };

  return [
    {
      label: labels[chartDataType] || '总成本',
      data: data.map((item) => getChartDataByType(item, chartDataType)),
      borderColor: '#583e04',
      backgroundColor: 'rgba(88, 62, 4, 0.25)',
      fill: true,
      tension: 0.4,
      pointRadius: 0,
    },
  ];
}

function buildTotalRestaurantDatasets(convertedByRestaurant, dates, chartDataType) {
  const restaurants = ['j1', 'j2', 'j3'];

  if (chartDataType === 'deliveryCost') {
    return restaurants.map((restaurant) => ({
      label: `${restaurant.toUpperCase()} 总外卖成本`,
      data: dates.map((date) => {
        const item = convertedByRestaurant[restaurant]?.find((row) => row.date === date);
        return item ? getDeliveryTotal(item) : 0;
      }),
      borderColor: RESTAURANT_COLORS[restaurant].primary,
      backgroundColor: RESTAURANT_COLORS[restaurant].fill,
      fill: true,
      tension: 0.4,
      pointRadius: 0,
    }));
  }

  if (chartDataType === 'grossTotal') {
    return restaurants.map((restaurant) => ({
      label: `${restaurant.toUpperCase()} 毛利润`,
      data: dates.map((date) => {
        const item = convertedByRestaurant[restaurant]?.find((row) => row.date === date);
        return item ? item.grossTotal : 0;
      }),
      borderColor: RESTAURANT_COLORS[restaurant].primary,
      backgroundColor: RESTAURANT_COLORS[restaurant].fill,
      fill: true,
      tension: 0.4,
      pointRadius: 0,
    }));
  }

  const typeLabels = {
    totalCost: '总成本',
    costPercent: '成本率',
  };

  return restaurants.map((restaurant) => ({
    label: `${restaurant.toUpperCase()} ${typeLabels[chartDataType] || '总成本'}`,
    data: dates.map((date) => {
      const item = convertedByRestaurant[restaurant]?.find((row) => row.date === date);
      return item ? getChartDataByType(item, chartDataType) : 0;
    }),
    borderColor: RESTAURANT_COLORS[restaurant].primary,
    backgroundColor: RESTAURANT_COLORS[restaurant].fill,
    fill: true,
    tension: 0.4,
    pointRadius: 0,
  }));
}

export function buildCostChartSeries({
  filteredData,
  allRestaurantsData,
  restaurant,
  dateRange,
  chartDataType,
}) {
  if (!restaurant) return null;

  if (restaurant === 'total') {
    const restaurants = ['j1', 'j2', 'j3'];
    const convertedByRestaurant = {};
    restaurants.forEach((key) => {
      const raw = allRestaurantsData[key] || [];
      convertedByRestaurant[key] = fillMissingDates(
        convertToCostFormat(raw),
        dateRange,
      );
    });

    const dateSet = new Set();
    restaurants.forEach((key) => {
      convertedByRestaurant[key].forEach((item) => dateSet.add(item.date));
    });
    const dates = Array.from(dateSet)
      .filter((date) => {
        const itemDate = new Date(date);
        const start = new Date(dateRange.startDate);
        const end = new Date(dateRange.endDate);
        return itemDate >= start && itemDate <= end;
      })
      .sort();

    const startDate = new Date(dateRange.startDate);
    const endDate = new Date(dateRange.endDate);
    const daysDiff = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
    const isMonthly = daysDiff > 60;

    if (isMonthly) {
      const aggregated = {};
      restaurants.forEach((key) => {
        aggregated[key] = aggregateByMonth(
          filterByDateRange(convertedByRestaurant[key], dateRange.startDate, dateRange.endDate),
        );
      });
      const monthSet = new Set();
      restaurants.forEach((key) => {
        aggregated[key].forEach((item) => monthSet.add(item.date));
      });
      const months = Array.from(monthSet).sort();

      const monthDataByRestaurant = {};
      restaurants.forEach((key) => {
        monthDataByRestaurant[key] = months.map((monthKey) => {
          return aggregated[key].find((row) => row.date === monthKey) || null;
        });
      });

      const pseudoConverted = {};
      restaurants.forEach((key) => {
        pseudoConverted[key] = months.map((monthKey, index) => {
          const item = monthDataByRestaurant[key][index];
          return item || { date: monthKey, cGrab: 0, cFoodpanda: 0, cShopee: 0, grossTotal: 0, cTotal: 0, costPercent: 0 };
        });
      });

      return {
        labels: months.map((monthKey) => {
          const [year, month] = monthKey.split('-');
          return `${year}年${Number(month)}月`;
        }),
        monthKeys: months,
        isMonthly: true,
        datasets: buildTotalRestaurantDatasets(pseudoConverted, months, chartDataType),
        yAxisPercent: chartDataType === 'costPercent',
      };
    }

    return {
      labels: dates.map((date) => String(new Date(date).getDate())),
      monthKeys: [],
      isMonthly: false,
      datasets: buildTotalRestaurantDatasets(convertedByRestaurant, dates, chartDataType),
      yAxisPercent: chartDataType === 'costPercent',
    };
  }

  const aggregated = aggregateDataByPeriod(filteredData, dateRange);

  return {
    labels: aggregated.map((item) => item.displayDate || String(new Date(item.date).getDate())),
    monthKeys: aggregated.filter((item) => item.displayDate).map((item) => item.date),
    isMonthly: aggregated.some((item) => item.displayDate),
    datasets: buildSingleRestaurantDatasets(aggregated, chartDataType),
    yAxisPercent: chartDataType === 'costPercent',
  };
}

export function computeCostSummaryFromFiltered(filteredData, apiSummary, stockData) {
  const lastStock = parseFloat(stockData?.last_stock ?? apiSummary.last_stock ?? 0);
  const currentStock = parseFloat(stockData?.current_stock ?? apiSummary.current_stock ?? 0);

  if (filteredData.length > 0) {
    return {
      total_sales: filteredData.reduce((sum, item) => sum + item.sales, 0),
      data_total_cost: filteredData.reduce((sum, item) => sum + item.cTotal, 0),
      total_profit: filteredData.reduce((sum, item) => sum + item.grossTotal, 0),
      total_grab_cost: filteredData.reduce((sum, item) => sum + (item.cGrab || 0), 0),
      total_foodpanda_cost: filteredData.reduce((sum, item) => sum + (item.cFoodpanda || 0), 0),
      total_shopee_cost: filteredData.reduce((sum, item) => sum + (item.cShopee || 0), 0),
      total_days: filteredData.length,
      last_stock: lastStock,
      current_stock: currentStock,
    };
  }

  return {
    total_sales: parseFloat(apiSummary.total_sales || 0),
    data_total_cost: parseFloat(apiSummary.total_cost || 0),
    total_profit: parseFloat(apiSummary.total_profit || 0),
    total_grab_cost: parseFloat(apiSummary.total_grab_cost || 0),
    total_foodpanda_cost: parseFloat(apiSummary.total_foodpanda_cost || 0),
    total_shopee_cost: parseFloat(apiSummary.total_shopee_cost || 0),
    total_days: parseInt(apiSummary.total_days || 0, 10),
    last_stock: lastStock,
    current_stock: currentStock,
  };
}

export const EMPTY_COST_SUMMARY = {
  totalSales: 0,
  totalCost: 0,
  grossTotal: 0,
  costPercent: 0,
  grabCost: 0,
  foodpandaCost: 0,
  shopeeCost: 0,
  lastStock: 0,
  currentStock: 0,
  j2Supply: 0,
  j3Supply: 0,
  totalDays: 0,
};

export function finalizeCostSummary(displaySummary, restaurant, supplyData) {
  let j2Supply = 0;
  let j3Supply = 0;
  let actualTotalCost;

  if (restaurant === 'j1') {
    j2Supply = parseFloat(supplyData?.j2_supply || 0);
    j3Supply = parseFloat(supplyData?.j3_supply || 0);
    actualTotalCost =
      displaySummary.last_stock -
      displaySummary.current_stock +
      displaySummary.data_total_cost -
      j2Supply -
      j3Supply;
  } else {
    actualTotalCost =
      displaySummary.last_stock - displaySummary.current_stock + displaySummary.data_total_cost;
  }

  const totalSales = displaySummary.total_sales;
  const avgCostPercent = totalSales > 0 ? (actualTotalCost / totalSales) * 100 : 0;

  return {
    totalSales,
    totalCost: actualTotalCost,
    grossTotal: totalSales - actualTotalCost,
    costPercent: avgCostPercent,
    grabCost: displaySummary.total_grab_cost,
    foodpandaCost: displaySummary.total_foodpanda_cost,
    shopeeCost: displaySummary.total_shopee_cost,
    lastStock: displaySummary.last_stock,
    currentStock: displaySummary.current_stock,
    j2Supply,
    j3Supply,
    totalDays: displaySummary.total_days,
  };
}

export function formatCurrency(value) {
  return parseFloat(value || 0).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

export function formatPercent(value) {
  return `${parseFloat(value || 0).toFixed(2)}%`;
}
