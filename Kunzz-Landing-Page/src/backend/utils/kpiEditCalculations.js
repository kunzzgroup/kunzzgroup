import { CURRENCY_FIELDS, INTEGER_FIELDS } from '../config/kpiEditConfig.js';

export function formatCurrencyDisplay(value) {
  if (value === null || value === undefined || value === '') return '';
  const num = parseFloat(value);
  if (Number.isNaN(num) || num === 0) return '';
  return num.toFixed(2);
}

export function displayIntValue(val) {
  if (val === null || val === undefined || val === '') return '';
  const n = parseInt(val, 10);
  if (Number.isNaN(n)) return '';
  return String(n);
}

export function parseFieldValue(field, value) {
  if (value === '' || value === null || value === undefined) return 0;
  const num = INTEGER_FIELDS.includes(field) ? parseInt(value, 10) : parseFloat(value);
  return Number.isNaN(num) ? 0 : num;
}

export function createEmptyRowFields() {
  return {
    gross_sales: '',
    discounts: '',
    tax: '',
    service_fee: '',
    adj_amount: '',
    tables_used: '',
    diners: '',
    new_customers: '',
    returning_customers: '',
  };
}

export function recordToRowFields(record = {}) {
  return {
    gross_sales: formatCurrencyDisplay(record.gross_sales),
    discounts: formatCurrencyDisplay(record.discounts),
    tax: formatCurrencyDisplay(record.tax),
    service_fee: formatCurrencyDisplay(record.service_fee),
    adj_amount: formatCurrencyDisplay(record.adj_amount),
    tables_used: displayIntValue(record.tables_used),
    diners: displayIntValue(record.diners),
    new_customers: displayIntValue(record.new_customers),
    returning_customers: displayIntValue(record.returning_customers),
  };
}

export function computeRowMetrics(fields) {
  const grossSales = parseFieldValue('gross_sales', fields.gross_sales);
  const discounts = parseFieldValue('discounts', fields.discounts);
  const tax = parseFieldValue('tax', fields.tax);
  const serviceFee = parseFieldValue('service_fee', fields.service_fee);
  const adjAmount = parseFieldValue('adj_amount', fields.adj_amount);
  const diners = parseFieldValue('diners', fields.diners);
  const returningCustomers = parseFieldValue('returning_customers', fields.returning_customers);
  const newCustomers = parseFieldValue('new_customers', fields.new_customers);

  const netSales = grossSales - discounts;
  const tenderAmount = netSales + tax + serviceFee + adjAmount;
  const avgPerDiner = diners > 0 ? (netSales + adjAmount) / diners : 0;
  const totalCustomers = returningCustomers + newCustomers;
  const returningRate = totalCustomers > 0 ? (returningCustomers / totalCustomers) * 100 : 0;

  return { netSales, tenderAmount, avgPerDiner, returningRate };
}

export function rowHasSaveableData(fields, dbRecord) {
  const hasInputData =
    parseFieldValue('gross_sales', fields.gross_sales) > 0 ||
    parseFieldValue('diners', fields.diners) > 0 ||
    parseFieldValue('discounts', fields.discounts) > 0 ||
    parseFieldValue('tax', fields.tax) > 0 ||
    parseFieldValue('service_fee', fields.service_fee) > 0 ||
    parseFieldValue('adj_amount', fields.adj_amount) !== 0 ||
    parseFieldValue('tables_used', fields.tables_used) > 0 ||
    parseFieldValue('returning_customers', fields.returning_customers) > 0 ||
    parseFieldValue('new_customers', fields.new_customers) > 0;

  return hasInputData || Boolean(dbRecord);
}

export function buildRecordPayload({ fields, day, year, month, restaurant, dbRecord }) {
  const monthStr = String(month).padStart(2, '0');
  const dayStr = String(day).padStart(2, '0');
  const grossSales = parseFieldValue('gross_sales', fields.gross_sales);
  const discounts = parseFieldValue('discounts', fields.discounts);
  const tax = parseFieldValue('tax', fields.tax);
  const serviceFee = parseFieldValue('service_fee', fields.service_fee);
  const adjAmount = parseFieldValue('adj_amount', fields.adj_amount);

  const payload = {
    date: `${year}-${monthStr}-${dayStr}`,
    gross_sales: grossSales,
    discounts,
    tax,
    service_fee: serviceFee,
    adj_amount: adjAmount,
    tender_amount: grossSales - discounts + tax + serviceFee + adjAmount,
    diners: parseFieldValue('diners', fields.diners),
    tables_used: parseFieldValue('tables_used', fields.tables_used),
    returning_customers: parseFieldValue('returning_customers', fields.returning_customers),
    new_customers: parseFieldValue('new_customers', fields.new_customers),
    restaurant,
  };

  if (dbRecord?.id) {
    payload.id = dbRecord.id;
  }

  return payload;
}

export function computeMonthStats(rowFieldsByDay, daysInMonth) {
  let filledDays = 0;
  let totalNetSales = 0;
  let totalTenderAmount = 0;
  let totalDiners = 0;
  let totalTables = 0;

  for (let day = 1; day <= daysInMonth; day += 1) {
    const fields = rowFieldsByDay[day] || createEmptyRowFields();
    const grossSales = parseFieldValue('gross_sales', fields.gross_sales);
    const discounts = parseFieldValue('discounts', fields.discounts);
    const adjAmount = parseFieldValue('adj_amount', fields.adj_amount);
    const tax = parseFieldValue('tax', fields.tax);
    const serviceFee = parseFieldValue('service_fee', fields.service_fee);
    const diners = parseFieldValue('diners', fields.diners);
    const tables = parseFieldValue('tables_used', fields.tables_used);

    if (grossSales > 0 || diners > 0) {
      filledDays += 1;
    }

    const netSales = grossSales - discounts;
    totalNetSales += netSales;
    totalTenderAmount += netSales + tax + serviceFee + adjAmount;
    totalDiners += diners;
    totalTables += tables;
  }

  return {
    filledDays,
    totalNetSales,
    totalTenderAmount,
    totalDiners,
    totalTables,
    avgPerCustomer: totalDiners > 0 ? totalNetSales / totalDiners : 0,
  };
}

export function getInputColorClass(fields, field) {
  const grossSales = parseFieldValue('gross_sales', fields.gross_sales);
  const diners = parseFieldValue('diners', fields.diners);
  const tax = parseFieldValue('tax', fields.tax);
  const serviceFee = parseFieldValue('service_fee', fields.service_fee);
  const tablesUsed = parseFieldValue('tables_used', fields.tables_used);
  const newCustomers = parseFieldValue('new_customers', fields.new_customers);
  const returningCustomers = parseFieldValue('returning_customers', fields.returning_customers);

  let filledKeyFields = 0;
  if (grossSales > 0) filledKeyFields += 1;
  if (diners > 0) filledKeyFields += 1;
  if (tax > 0) filledKeyFields += 1;
  if (serviceFee > 0) filledKeyFields += 1;
  if (tablesUsed > 0) filledKeyFields += 1;
  if (newCustomers > 0) filledKeyFields += 1;
  if (returningCustomers > 0) filledKeyFields += 1;

  const rowHasKeyData = filledKeyFields >= 4;

  if (field === 'discounts') {
    return rowHasKeyData ? 'has-data' : 'no-data';
  }

  const value = fields[field];
  const hasValue = value !== '' && value !== '0' && value !== '0.00';
  return hasValue ? 'has-data' : 'no-data';
}

export function isFieldEditable(field, isOperationManager, isEditing) {
  if (!isEditing) return false;
  if (isOperationManager) {
    return ['new_customers', 'returning_customers'].includes(field);
  }
  return true;
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

export function splitWithNumberProtection(text) {
  const values = [];
  let current = '';

  for (let i = 0; i < text.length; i += 1) {
    const char = text[i];
    const nextChar = text[i + 1];
    const prevChar = text[i - 1];

    if (char === ',') {
      const isThousandsSeparator =
        /\d/.test(prevChar) &&
        /\d/.test(nextChar) &&
        /^\d{1,3}($|[,\s\t])/.test(text.substring(i + 1));

      if (isThousandsSeparator) {
        current += char;
      } else if (current.trim()) {
        values.push(current.trim());
        current = '';
      }
    } else if (/\s/.test(char)) {
      if (current.trim()) values.push(current.trim());
      current = '';
    } else {
      current += char;
    }
  }

  if (current.trim()) values.push(current.trim());
  return values;
}

export function parsePasteLine(line) {
  if (line.includes('\t')) return line.split('\t');
  if (line.includes(',')) {
    const numberPattern = /^[\d,]+\.?\d*$/;
    if (numberPattern.test(line.trim())) return [line.trim()];
    return splitWithNumberProtection(line);
  }
  return line.split(/\s+/);
}

export function cleanPasteValue(value) {
  let cleanValue = value.replace(/[^\d.,-]/g, '').replace(/,/g, '');
  const numValue = parseFloat(cleanValue);
  return Number.isNaN(numValue) ? null : cleanValue;
}
