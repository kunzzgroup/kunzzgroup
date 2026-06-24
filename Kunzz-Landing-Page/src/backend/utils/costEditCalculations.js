import { EDITABLE_FIELDS } from '../config/costEditConfig.js';

export function formatCurrencyDisplay(value) {
  if (!value || value === '') return '';
  const num = parseFloat(value);
  if (Number.isNaN(num) || num === 0) return '';
  return num.toFixed(2);
}

export function parseFieldValue(value) {
  if (value === '' || value === null || value === undefined) return 0;
  const num = parseFloat(value);
  return Number.isNaN(num) ? 0 : num;
}

export function createEmptyRowFields() {
  return {
    sales: '',
    c_beverage: '',
    c_kitchen: '',
    c_grab: '',
    c_foodpanda: '',
    c_shopee: '',
  };
}

export function recordToRowFields(record = {}, kpiNetSales) {
  const sales = kpiNetSales !== undefined ? kpiNetSales : record.sales;
  return {
    sales: formatCurrencyDisplay(sales),
    c_beverage: formatCurrencyDisplay(record.c_beverage),
    c_kitchen: formatCurrencyDisplay(record.c_kitchen),
    c_grab: formatCurrencyDisplay(record.c_grab),
    c_foodpanda: formatCurrencyDisplay(record.c_foodpanda),
    c_shopee: formatCurrencyDisplay(record.c_shopee),
  };
}

export function computeRowMetrics(fields) {
  const sales = parseFieldValue(fields.sales);
  const cBeverage = parseFieldValue(fields.c_beverage);
  const cKitchen = parseFieldValue(fields.c_kitchen);
  const cGrab = parseFieldValue(fields.c_grab);
  const cFoodpanda = parseFieldValue(fields.c_foodpanda);
  const cShopee = parseFieldValue(fields.c_shopee);

  const cTotal = cBeverage + cKitchen;
  const finalSales = sales + (cGrab + cFoodpanda + cShopee) / 2;
  const grossTotal = finalSales - cTotal;
  const costPercent = finalSales > 0 ? (cTotal / finalSales) * 100 : 0;

  return { cTotal, finalSales, grossTotal, costPercent };
}

export function rowHasSaveableData(fields, dbRecord) {
  const hasCostData =
    parseFieldValue(fields.c_beverage) > 0 ||
    parseFieldValue(fields.c_kitchen) > 0 ||
    parseFieldValue(fields.c_grab) > 0 ||
    parseFieldValue(fields.c_foodpanda) > 0 ||
    parseFieldValue(fields.c_shopee) > 0;

  return hasCostData || Boolean(dbRecord?.id);
}

export function buildCostRecordPayload({ fields, day, year, month, restaurant, dbRecord }) {
  const monthStr = String(month).padStart(2, '0');
  const dayStr = String(day).padStart(2, '0');
  const cBeverage = parseFieldValue(fields.c_beverage);
  const cKitchen = parseFieldValue(fields.c_kitchen);

  const payload = {
    date: `${year}-${monthStr}-${dayStr}`,
    c_beverage: cBeverage,
    c_kitchen: cKitchen,
    c_grab: parseFieldValue(fields.c_grab),
    c_foodpanda: parseFieldValue(fields.c_foodpanda),
    c_shopee: parseFieldValue(fields.c_shopee),
    c_total: cBeverage + cKitchen,
    restaurant,
  };

  if (dbRecord?.id) {
    payload.id = dbRecord.id;
  }

  return payload;
}

export function buildClearCostPayload({ day, year, month, restaurant, dbRecord }) {
  const monthStr = String(month).padStart(2, '0');
  const dayStr = String(day).padStart(2, '0');
  const payload = {
    date: `${year}-${monthStr}-${dayStr}`,
    c_beverage: 0,
    c_kitchen: 0,
    c_grab: 0,
    c_foodpanda: 0,
    c_shopee: 0,
    c_total: 0,
    restaurant,
  };

  if (dbRecord?.id) {
    payload.id = dbRecord.id;
  }

  return payload;
}

export function computeMonthStats(rowFieldsByDay, daysInMonth) {
  let filledDays = 0;
  let totalSales = 0;
  let totalCost = 0;
  let totalProfit = 0;

  for (let day = 1; day <= daysInMonth; day += 1) {
    const fields = rowFieldsByDay[day] || createEmptyRowFields();
    const { finalSales, cTotal, grossTotal } = computeRowMetrics(fields);
    const sales = parseFieldValue(fields.sales);
    const cBeverage = parseFieldValue(fields.c_beverage);
    const cKitchen = parseFieldValue(fields.c_kitchen);
    const cGrab = parseFieldValue(fields.c_grab);
    const cFoodpanda = parseFieldValue(fields.c_foodpanda);
    const cShopee = parseFieldValue(fields.c_shopee);

    if (sales > 0 || cBeverage > 0 || cKitchen > 0 || cGrab > 0 || cFoodpanda > 0 || cShopee > 0) {
      filledDays += 1;
    }

    totalSales += finalSales;
    totalCost += cTotal;
    totalProfit += grossTotal;
  }

  return {
    filledDays,
    totalSales,
    totalCost,
    totalProfit,
    avgCostPercent: totalSales > 0 ? (totalCost / totalSales) * 100 : 0,
  };
}

export function getInputColorClass(fields, field) {
  const value = fields[field];
  const hasValue = value !== '' && value !== '0' && value !== '0.00';
  return hasValue ? 'has-data' : 'no-data';
}

export function formatStatNumber(value, decimals = 2) {
  if (decimals === 0) {
    return Number(value || 0).toLocaleString('en-US');
  }
  return Number(value || 0).toLocaleString('en-US', {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals,
  });
}

export function mergeCostAndKpiData({ costData, kpiData, year, month, costLoadSucceeded }) {
  const kpiByDay = {};
  kpiData.forEach((item) => {
    const day = parseInt(item.date.split('-')[2], 10);
    const grossSales = parseFloat(item.gross_sales) || 0;
    const discounts = parseFloat(item.discounts) || 0;
    kpiByDay[day] = grossSales - discounts;
  });

  const dbRecords = {};
  const rowFields = {};

  costData.forEach((item) => {
    const day = parseInt(item.date.split('-')[2], 10);
    dbRecords[day] = item;
    const netSales = kpiByDay[day] !== undefined ? kpiByDay[day] : item.sales;
    rowFields[day] = recordToRowFields(item, netSales);
  });

  if (costLoadSucceeded !== false) {
    Object.entries(kpiByDay).forEach(([dayKey, netSales]) => {
      const day = parseInt(dayKey, 10);
      if (!dbRecords[day]) {
        const monthStr = String(month).padStart(2, '0');
        const dayStr = String(day).padStart(2, '0');
        dbRecords[day] = {
          date: `${year}-${monthStr}-${dayStr}`,
          sales: netSales,
        };
        rowFields[day] = recordToRowFields({}, netSales);
      }
    });
  }

  return { dbRecords, rowFields, kpiByDay };
}

export function clearCostFields(fields) {
  const next = { ...fields };
  EDITABLE_FIELDS.forEach((field) => {
    next[field] = '';
  });
  return next;
}

export { splitWithNumberProtection, parsePasteLine, cleanPasteValue } from './kpiEditCalculations.js';
