import { useMemo } from 'react';

import BackendLayout from '../components/layout/BackendLayout.jsx';
import KpiCards from '../components/kpi/KpiCards.jsx';
import KpiChart from '../components/kpi/KpiChart.jsx';
import KpiDataTable from '../components/kpi/KpiDataTable.jsx';
import KpiFilters, { KpiChartTypeButtons } from '../components/kpi/KpiFilters.jsx';
import { CHART_DATA_TYPES } from '../config/kpiConfig.js';
import { useKpiDashboard } from '../hooks/useKpiDashboard.js';
import { formatDisplayRange } from '../utils/dateUtils.js';

export default function KpiDashboardPage() {
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
  } = useKpiDashboard();

  const chartTitle = useMemo(() => {
    const base = CHART_DATA_TYPES.find((item) => item.id === chartDataType)?.title || '净销售额趋势';
    if (restaurant === 'total') {
      return `${base} (三店合计)`;
    }
    return base;
  }, [chartDataType, restaurant]);

  return (
    <BackendLayout className="restaurant-j1">
      <div className="container">
        <div className="header">
          <div>
            <h1>KPI 仪表盘</h1>
          </div>
        </div>

        <div className="date-info" id="date-info" style={{ marginBottom: 16, border: '1px solid #e5e7eb' }}>
          {loading ? '正在加载数据...' : dateInfo}
        </div>

        <div id="app">
          <KpiFilters
            restaurantLabel={restaurantLabel}
            dateRange={dateRange}
            onDateRangeChange={setDateRange}
            onQuickRange={applyQuickRange}
            onRestaurantSelect={selectRestaurant}
          />

          <KpiCards summary={summary} hasRestaurant={Boolean(restaurant)} />

          <div className="main-chart-container">
            <div className="card" style={{ height: 400 }}>
              <div className="card-body" style={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                <div
                  className="chart-header"
                  style={{
                    display: 'flex',
                    justifyContent: 'space-between',
                    alignItems: 'center',
                    marginBottom: 16,
                  }}
                >
                  <h3
                    id="main-chart-title"
                    style={{ fontSize: 'clamp(14px, 1.04vw, 20px)', fontWeight: 600, color: '#111827', margin: 0 }}
                  >
                    {chartTitle}
                  </h3>

                  <KpiChartTypeButtons chartDataType={chartDataType} onChange={setChartDataType} />

                  <div
                    className="date-range-display"
                    id="chart-date-range"
                    style={{ fontSize: 'clamp(8px, 0.74vw, 14px)', color: '#6b7280', fontWeight: 500 }}
                  >
                    {formatDisplayRange(dateRange.startDate, dateRange.endDate)}
                  </div>
                </div>

                <KpiChart
                  chartSeries={chartSeries}
                  chartDataType={chartDataType}
                  isDrillDownMode={isDrillDownMode}
                  onDrillDown={enterDrillDown}
                  onExitDrillDown={exitDrillDown}
                />
              </div>
            </div>
          </div>

          <KpiDataTable rows={filteredData} restaurant={restaurant} loading={loading} error={error} />
        </div>
      </div>
    </BackendLayout>
  );
}
