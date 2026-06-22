export function convertToKpiFormat(data) {
  return data.map((item) => {
    const diners = parseInt(item.diners, 10) || 0;
    const returningCustomers = parseInt(item.returning_customers, 10) || 0;
    const newCustomers = parseInt(item.new_customers, 10) || 0;
    const totalCustomers = returningCustomers + newCustomers;
    const grossSales = parseFloat(item.gross_sales) || 0;
    const discounts = parseFloat(item.discounts) || 0;
    const netSales = item.net_sales ? parseFloat(item.net_sales) : grossSales - discounts;

    return {
      date: item.date,
      totalSales: parseFloat(item.tender_amount) || 0,
      netSales,
      diners,
      tablesUsed: parseInt(item.tables_used, 10) || 0,
      returningCustomers,
      newCustomers,
      avgSalesPerDiner: diners > 0 ? netSales / diners : 0,
      returningRate: totalCustomers > 0 ? (returningCustomers / totalCustomers) * 100 : 0,
    };
  });
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

export function mergeAllRestaurantsData(allRestaurantsData) {
  const dateMap = new Map();

  Object.values(allRestaurantsData).forEach((restaurantData) => {
    restaurantData.forEach((item) => {
      const date = item.date;
      if (!dateMap.has(date)) {
        dateMap.set(date, {
          date,
          gross_sales: 0,
          net_sales: 0,
          tender_amount: 0,
          discounts: 0,
          tax: 0,
          service_fee: 0,
          adj_amount: 0,
          diners: 0,
          tables_used: 0,
          returning_customers: 0,
          new_customers: 0,
        });
      }

      const existing = dateMap.get(date);
      const grossSales = parseFloat(item.gross_sales) || 0;
      const discounts = parseFloat(item.discounts) || 0;
      const netSales = item.net_sales ? parseFloat(item.net_sales) : grossSales - discounts;

      existing.gross_sales += grossSales;
      existing.tender_amount += parseFloat(item.tender_amount) || 0;
      existing.net_sales += netSales;
      existing.discounts += discounts;
      existing.tax += parseFloat(item.tax) || 0;
      existing.service_fee += parseFloat(item.service_fee) || 0;
      existing.adj_amount += parseFloat(item.adj_amount) || 0;
      existing.diners += parseInt(item.diners, 10) || 0;
      existing.tables_used += parseInt(item.tables_used, 10) || 0;
      existing.returning_customers += parseInt(item.returning_customers, 10) || 0;
      existing.new_customers += parseInt(item.new_customers, 10) || 0;
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
        totalSales: 0,
        netSales: 0,
        diners: 0,
        tablesUsed: 0,
        returningCustomers: 0,
        newCustomers: 0,
        daysCount: 0,
      });
    }

    const monthData = monthMap.get(monthKey);
    monthData.totalSales += item.totalSales;
    monthData.netSales += item.netSales;
    monthData.diners += item.diners;
    monthData.tablesUsed += item.tablesUsed;
    monthData.returningCustomers += item.returningCustomers;
    monthData.newCustomers += item.newCustomers;
    monthData.daysCount += 1;
  });

  return Array.from(monthMap.values())
    .map((item) => ({
      ...item,
      avgSalesPerDiner: item.diners > 0 ? item.netSales / item.diners : 0,
      returningRate:
        item.returningCustomers + item.newCustomers > 0
          ? (item.returningCustomers / (item.returningCustomers + item.newCustomers)) * 100
          : 0,
    }))
    .sort((a, b) => a.date.localeCompare(b.date));
}

export function aggregateDataByPeriod(data, dateRange) {
  const startDate = new Date(dateRange.startDate);
  const endDate = new Date(dateRange.endDate);
  const daysDiff = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
  return daysDiff > 60 ? aggregateByMonth(data) : data;
}

export function computeSummary(filteredData) {
  if (filteredData.length === 0) {
    return {
      totalSales: 0,
      netSales: 0,
      totalTables: 0,
      totalDiners: 0,
      returningRate: 0,
      avgPerDiner: 0,
      totalDays: 0,
    };
  }

  const totals = filteredData.reduce(
    (acc, item) => {
      acc.totalSales += item.totalSales;
      acc.netSales += item.netSales;
      acc.totalTables += item.tablesUsed;
      acc.totalDiners += item.diners;
      acc.returningCustomers += item.returningCustomers;
      acc.newCustomers += item.newCustomers;
      return acc;
    },
    {
      totalSales: 0,
      netSales: 0,
      totalTables: 0,
      totalDiners: 0,
      returningCustomers: 0,
      newCustomers: 0,
    },
  );

  const totalCustomers = totals.returningCustomers + totals.newCustomers;

  return {
    totalSales: totals.totalSales,
    netSales: totals.netSales,
    totalTables: totals.totalTables,
    totalDiners: totals.totalDiners,
    returningRate: totalCustomers > 0 ? (totals.returningCustomers / totalCustomers) * 100 : 0,
    avgPerDiner: totals.totalDiners > 0 ? totals.netSales / totals.totalDiners : 0,
    totalDays: filteredData.length,
  };
}

export function getChartValue(item, dataType) {
  switch (dataType) {
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
}

export function prepareComparisonSeries(allRestaurantsData, dateRange, dataType) {
  const restaurants = ['j1', 'j2', 'j3'];
  const converted = {};
  restaurants.forEach((restaurant) => {
    converted[restaurant] = filterByDateRange(
      convertToKpiFormat(allRestaurantsData[restaurant] || []),
      dateRange.startDate,
      dateRange.endDate,
    );
  });

  const dateSet = new Set();
  restaurants.forEach((restaurant) => {
    converted[restaurant].forEach((item) => dateSet.add(item.date));
  });
  const dates = Array.from(dateSet).sort();

  const startDate = new Date(dateRange.startDate);
  const endDate = new Date(dateRange.endDate);
  const daysDiff = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
  const isMonthly = daysDiff > 60;

  if (isMonthly) {
    const aggregated = {};
    restaurants.forEach((restaurant) => {
      aggregated[restaurant] = aggregateByMonth(converted[restaurant]);
    });
    const monthSet = new Set();
    restaurants.forEach((restaurant) => {
      aggregated[restaurant].forEach((item) => monthSet.add(item.date));
    });
    const months = Array.from(monthSet).sort();
    return {
      labels: months.map((monthKey) => {
        const [year, month] = monthKey.split('-');
        return `${year}年${Number(month)}月`;
      }),
      monthKeys: months,
      isMonthly: true,
      datasets: restaurants.map((restaurant) => ({
        restaurant,
        data: months.map((monthKey) => {
          const item = aggregated[restaurant].find((row) => row.date === monthKey);
          return item ? getChartValue(item, dataType) : 0;
        }),
      })),
    };
  }

  return {
    labels: dates.map((date) => String(new Date(date).getDate())),
    monthKeys: [],
    isMonthly: false,
    datasets: restaurants.map((restaurant) => ({
      restaurant,
      data: dates.map((date) => {
        const item = converted[restaurant].find((row) => row.date === date);
        return item ? getChartValue(item, dataType) : 0;
      }),
    })),
  };
}

export function formatCurrency(value) {
  return parseFloat(value || 0).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

export function formatNumber(value) {
  return (value || 0).toLocaleString();
}
