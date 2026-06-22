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

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Tooltip, Legend, Filler);

function formatYAxis(value, isPercent) {
  if (isPercent) return `${value.toFixed(2)}%`;
  return `RM ${value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

export default function CostChart({
  chartSeries,
  isDrillDownMode,
  onDrillDown,
  onExitDrillDown,
}) {
  if (!chartSeries) {
    return (
      <div className="chart-container chart-fill" style={{ display: 'grid', placeItems: 'center', color: '#6b7280' }}>
        请先选择餐厅
      </div>
    );
  }

  const isMulti = chartSeries.datasets.length > 1;

  const chartData = {
    labels: chartSeries.labels,
    datasets: chartSeries.datasets.map((dataset) => ({
      ...dataset,
      pointHoverRadius: 6,
      borderWidth: 2,
    })),
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
          label(context) {
            const value = context.parsed.y;
            if (chartSeries.yAxisPercent) {
              return `${context.dataset.label}: ${value.toFixed(2)}%`;
            }
            return `${context.dataset.label}: RM ${value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
          },
        },
      },
    },
    scales: {
      y: {
        ticks: {
          callback: (value) => formatYAxis(value, chartSeries.yAxisPercent),
        },
      },
    },
  };

  return (
    <div className="chart-container chart-fill" style={{ position: 'relative' }}>
      {isDrillDownMode && (
        <button type="button" className="chart-back-button" id="cost-chart-back" onClick={onExitDrillDown}>
          <i className="fas fa-arrow-left" /> 返回年度视图
        </button>
      )}
      <Line data={chartData} options={options} />
    </div>
  );
}
