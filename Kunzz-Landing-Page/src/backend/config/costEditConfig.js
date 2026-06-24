export const REPORT_LABELS = {
  kpi: 'KPI 报表',
  cost: '成本报表',
};

export const YEAR_OPTIONS_START = 2022;

export const EDITABLE_FIELDS = ['c_beverage', 'c_kitchen', 'c_grab', 'c_foodpanda', 'c_shopee'];

export const PASTE_FIELDS = ['c_beverage', 'c_kitchen', 'c_grab', 'c_foodpanda', 'c_shopee'];

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
