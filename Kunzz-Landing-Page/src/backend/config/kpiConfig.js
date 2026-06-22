export const RESTAURANT_CONFIG = {
  j1: { name: 'J1', label: 'J1' },
  j2: { name: 'J2', label: 'J2' },
  j3: { name: 'J3', label: 'J3' },
  total: { name: '总', label: 'J总' },
};

export const CHART_DATA_TYPES = [
  { id: 'netSales', label: '净销售额', title: '净销售额趋势' },
  { id: 'tables', label: '桌子数量', title: '桌子数量趋势' },
  { id: 'diners', label: '人数', title: '人数趋势' },
  { id: 'returningRate', label: '常客(%)', title: '常客百分比趋势' },
];

export const QUICK_RANGES = [
  { id: 'today', label: '今天' },
  { id: 'yesterday', label: '昨天' },
  { id: 'thisWeek', label: '本周' },
  { id: 'lastWeek', label: '上周' },
  { id: 'thisMonth', label: '这个月' },
  { id: 'lastMonth', label: '上个月' },
  { id: 'thisYear', label: '今年' },
  { id: 'lastYear', label: '去年' },
];

export const RESTAURANT_COLORS = {
  j1: { primary: '#583e04', fill: 'rgba(88, 62, 4, 0.3)' },
  j2: { primary: '#d97706', fill: 'rgba(217, 119, 6, 0.3)' },
  j3: { primary: '#dc2626', fill: 'rgba(220, 38, 38, 0.3)' },
};

export const KPI_CARDS = [
  { id: 'totalSales', label: '总销售额 (RM)', icon: 'fa-dollar-sign', colorClass: 'text-green' },
  { id: 'netSales', label: '净销售额 (RM)', icon: 'fa-chart-line', colorClass: 'text-green' },
  { id: 'totalTables', label: '桌子总数', icon: 'table', colorClass: 'dynamic-color' },
  { id: 'totalDiners', label: '顾客总数', icon: 'fa-users', colorClass: 'dynamic-color' },
  { id: 'returningRate', label: '常客%', icon: 'fa-user-check', colorClass: 'dynamic-color' },
  { id: 'avgPerDiner', label: '人均消费 (RM)', icon: 'fa-calculator', colorClass: 'dynamic-color' },
];
