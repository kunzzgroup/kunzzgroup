export const CURRENCY_FIELDS = ['gross_sales', 'discounts', 'tax', 'service_fee', 'adj_amount'];
export const INTEGER_FIELDS = ['tables_used', 'diners', 'new_customers', 'returning_customers'];
export const OPERATION_MANAGER_FIELDS = ['new_customers', 'returning_customers'];

export const PASTE_FIELDS = [
  'gross_sales',
  'discounts',
  'tax',
  'service_fee',
  'adj_amount',
  'tables_used',
  'diners',
];

export const REPORT_LABELS = {
  kpi: 'KPI 报表',
  cost: '成本报表',
};

export const YEAR_OPTIONS_START = 2023;

export function getYearOptions() {
  const currentYear = new Date().getFullYear();
  const years = [];
  for (let year = YEAR_OPTIONS_START; year <= currentYear + 2; year += 1) {
    years.push(year);
  }
  return years;
}

export function getMonthDateRange(year, month) {
  const lastDay = new Date(year, month, 0).getDate();
  const monthStr = String(month).padStart(2, '0');
  return {
    startDate: `${year}-${monthStr}-01`,
    endDate: `${year}-${monthStr}-${String(lastDay).padStart(2, '0')}`,
    daysInMonth: lastDay,
  };
}
