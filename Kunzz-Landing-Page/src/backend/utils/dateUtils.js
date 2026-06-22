export function formatDateLocal(date) {
  const y = date.getFullYear();
  const m = String(date.getMonth() + 1).padStart(2, '0');
  const d = String(date.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
}

export function getDefaultDateRange() {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
  return {
    startDate: formatDateLocal(firstDay),
    endDate: formatDateLocal(today),
  };
}

export function getQuickRangeDates(rangeId) {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  let startDate;
  let endDate;

  switch (rangeId) {
    case 'today':
      startDate = new Date(today);
      endDate = new Date(today);
      break;
    case 'yesterday': {
      const yesterday = new Date(today);
      yesterday.setDate(yesterday.getDate() - 1);
      startDate = yesterday;
      endDate = yesterday;
      break;
    }
    case 'thisWeek': {
      const weekStart = new Date(today);
      const dayOfWeek = weekStart.getDay();
      const daysToMonday = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
      weekStart.setDate(weekStart.getDate() - daysToMonday);
      startDate = weekStart;
      endDate = new Date(today);
      break;
    }
    case 'lastWeek': {
      const lastWeekEnd = new Date(today);
      const lastWeekDayOfWeek = lastWeekEnd.getDay();
      const daysToLastSunday = lastWeekDayOfWeek === 0 ? 0 : lastWeekDayOfWeek;
      lastWeekEnd.setDate(lastWeekEnd.getDate() - daysToLastSunday - 1);
      const lastWeekStart = new Date(lastWeekEnd);
      lastWeekStart.setDate(lastWeekStart.getDate() - 6);
      startDate = lastWeekStart;
      endDate = lastWeekEnd;
      break;
    }
    case 'thisMonth':
      startDate = new Date(today.getFullYear(), today.getMonth(), 1);
      endDate = new Date(today);
      break;
    case 'lastMonth':
      startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
      endDate = new Date(today.getFullYear(), today.getMonth(), 0);
      break;
    case 'thisYear':
      startDate = new Date(today.getFullYear(), 0, 1);
      endDate = new Date(today);
      break;
    case 'lastYear':
      startDate = new Date(today.getFullYear() - 1, 0, 1);
      endDate = new Date(today.getFullYear() - 1, 11, 31);
      break;
    default:
      return null;
  }

  return {
    startDate: formatDateLocal(startDate),
    endDate: formatDateLocal(endDate),
  };
}

export function formatDisplayRange(startDate, endDate) {
  const fmt = (value) => {
    const [y, m, d] = value.split('-');
    return `${y}年${m}月${d}日`;
  };
  return `${fmt(startDate)} - ${fmt(endDate)}`;
}

export function getMonthRangeFromKey(monthKey) {
  const [year, month] = monthKey.split('-');
  const lastDay = new Date(Number(year), Number(month), 0).getDate();
  return {
    startDate: `${year}-${month}-01`,
    endDate: `${year}-${month}-${String(lastDay).padStart(2, '0')}`,
  };
}
