import {
  CategoryScale,
  Chart as ChartJS,
  Filler,
  Legend,
  LineElement,
  LinearScale,
  PointElement,
  Tooltip,
} from 'chart.js';
import { Line } from 'react-chartjs-2';

import { CHART_DATA_TYPES, RESTAURANT_COLORS } from '../../config/kpiConfig.js';
import { formatKpiAxisValue, formatKpiTooltipLabel } from '../../utils/kpiCalculations.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Tooltip, Legend, Filler);

const DATASET_LABELS = {
  j1: { netSales: 'J1 净销售额', tables: 'J1 桌子数量', returningRate: 'J1 常客', diners: 'J1 人数' },
  j2: { netSales: 'J2 净销售额', tables: 'J2 桌子数量', returningRate: 'J2 常客', diners: 'J2 人数' },
  j3: { netSales: 'J3 净销售额', tables: 'J3 桌子数量', returningRate: 'J3 常客', diners: 'J3 人数' },
};

function buildDataset(restaurant, data, dataType, isMulti, rowMeta) {
  const colors = RESTAURANT_COLORS[restaurant] || RESTAURANT_COLORS.j1;
  const label = isMulti
    ? DATASET_LABELS[restaurant]?.[dataType] || restaurant.toUpperCase()
    : CHART_DATA_TYPES.find((item) => item.id === dataType)?.label || '数据';

  return {
    label,
    data,
    rowMeta,
    borderColor: colors.primary,
    backgroundColor: colors.fill,
    fill: true,
    tension: 0.35,
    pointRadius: 3,
  };
}

export default function KpiChart({
  chartSeries,
  chartDataType,
  isDrillDownMode,
  onDrillDown,
  onExitDrillDown,
}) {
  if (!chartSeries) {
    return (
      <div className="chart-container" style={{ flex: 1, display: 'grid', placeItems: 'center', color: '#6b7280' }}>
        请先选择餐厅
      </div>
    );
  }

  const isMulti = chartSeries.datasets.length > 1;

  const chartData = {
    labels: chartSeries.labels,
    datasets: chartSeries.datasets.map(({ restaurant, data, rowMeta }) =>
      buildDataset(restaurant, data, chartDataType, isMulti, rowMeta),
    ),
  };

  const options = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    onClick: (_, elements) => {
      if (!chartSeries.isMonthly || isDrillDownMode || elements.length === 0) return;
      const index = elements[0].index;
      const monthKey = chartSeries.monthKeys[index];
      if (monthKey) onDrillDown(monthKey);
    },
    plugins: {
      legend: { display: isMulti },
      tooltip: {
        mode: 'index',
        intersect: false,
        callbacks: {
          label: (context) => formatKpiTooltipLabel(chartDataType, context, chartSeries),
        },
      },
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: (value) => formatKpiAxisValue(chartDataType, value),
        },
      },
    },
  };

  return (
    <div className="chart-container" style={{ flex: 1, position: 'relative' }}>
      {isDrillDownMode && (
        <button type="button" className="chart-back-button" onClick={onExitDrillDown}>
          <i className="fas fa-arrow-left" /> 返回年度视图
        </button>
      )}
      <Line data={chartData} options={options} />
    </div>
  );
}
