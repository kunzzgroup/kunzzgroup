import { useMemo } from 'react';

import BackendLayout from '../components/layout/BackendLayout.jsx';
import CostCards from '../components/cost/CostCards.jsx';
import CostChart from '../components/cost/CostChart.jsx';
import CostDataTable from '../components/cost/CostDataTable.jsx';
import CostFilters, { CostChartTypeButtons } from '../components/cost/CostFilters.jsx';
import { CHART_DATA_TYPES } from '../config/costConfig.js';
import { useCostDashboard } from '../hooks/useCostDashboard.js';
import { formatDisplayRange } from '../utils/dateUtils.js';

export default function CostDashboardPage() {
  const {
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
  } = useCostDashboard();

  const bodyClass = restaurant ? `restaurant-${restaurant}` : '';

  const chartTitle = useMemo(() => {
    const base = CHART_DATA_TYPES.find((item) => item.id === chartDataType)?.title || '总成本趋势';
    if (restaurant === 'total') {
      return `${base} (三店合计)`;
    }
    return base;
  }, [chartDataType, restaurant]);

  return (
    <BackendLayout className={bodyClass} bodyClassName={bodyClass} stylesheet="cost.css">
      <div className="container">
        <div className="header">
          <div>
            <h1>成本分析仪表盘</h1>
          </div>
        </div>

        <div className="date-info" id="date-info">
          {loading ? '正在加载数据...' : dateInfo}
        </div>

        <div id="app">
          <CostFilters
            restaurantLabel={restaurantLabel}
            dateRange={dateRange}
            onDateRangeChange={setDateRange}
            onQuickRange={applyQuickRange}
            onRestaurantSelect={selectRestaurant}
          />

          <CostCards summary={summary} restaurant={restaurant} hasRestaurant={Boolean(restaurant)} />

          <div className="main-chart-container">
            <div className="card">
              <div className="card-body">
                <div className="chart-header">
                  <h3 id="main-chart-title">{chartTitle}</h3>

                  <CostChartTypeButtons chartDataType={chartDataType} onChange={setChartDataType} />

                  <div className="date-range-display" id="chart-date-range">
                    {formatDisplayRange(dateRange.startDate, dateRange.endDate)}
                  </div>
                </div>

                <CostChart
                  chartSeries={chartSeries}
                  isDrillDownMode={isDrillDownMode}
                  onDrillDown={enterDrillDown}
                  onExitDrillDown={exitDrillDown}
                />
              </div>
            </div>
          </div>

          <CostDataTable rows={filteredData} restaurant={restaurant} loading={loading} error={error} />
        </div>
      </div>
    </BackendLayout>
  );
}
