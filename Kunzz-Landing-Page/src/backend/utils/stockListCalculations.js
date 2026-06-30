import { TYPE_FILTER_OPTIONS } from '../config/stockListConstants.js';

export function normalizeItemType(type) {
  if (!type) return '';
  if (type === 'Drinks' || type === 'drinks') return 'Service Line';
  return type;
}

export function isTypeFilterActive(system, selectedTypes) {
  if (!selectedTypes || selectedTypes.size === 0) return false;
  const allTypes = TYPE_FILTER_OPTIONS[system];
  if (!allTypes) return selectedTypes.size > 0;
  return !(selectedTypes.size >= allTypes.length && allTypes.every((t) => selectedTypes.has(t)));
}

export function filterStockItems(items, searchTerm, selectedTypes, system, lowStockSettings) {
  const term = (searchTerm || '').toLowerCase();

  return items.filter((item) => {
    const itemType = normalizeItemType(item.type);

    if (isTypeFilterActive(system, selectedTypes) && !selectedTypes.has(itemType)) {
      return false;
    }

    if (!term) return true;

    const productName = (item.product_name || '').trim();
    const minimumQuantity = lowStockSettings[productName] || 0;
    const minimumStockStr = minimumQuantity > 0 ? minimumQuantity.toString() : '';

    return (
      (item.no && item.no.toString().includes(term))
      || (item.product_name && item.product_name.toLowerCase().includes(term))
      || (item.code_number && item.code_number.toLowerCase().includes(term))
      || (minimumStockStr && minimumStockStr.includes(term))
      || (item.total_stock && item.total_stock.toString().includes(term))
      || (item.specification && item.specification.toLowerCase().includes(term))
      || (item.price && item.price.toString().includes(term))
      || (item.total_price && item.total_price.toString().includes(term))
      || (item.formatted_total_price && item.formatted_total_price.includes(term))
    );
  });
}

export function formatCurrency(value) {
  if (!value || value === '' || value === '0') return '0.00';
  const num = parseFloat(value);
  if (Number.isNaN(num)) return '0.00';
  return num.toFixed(2);
}

export function formatStockQuantity(item) {
  const specification = item.specification ? item.specification.trim().toLowerCase() : '';
  const rawStock = parseFloat(item.total_stock);
  const fallbackFormatted = item.formatted_stock || item.total_stock || '';

  if (specification === 'kilo') {
    if (!Number.isNaN(rawStock)) return rawStock.toFixed(3);
    const parsedFallback = parseFloat(fallbackFormatted);
    if (!Number.isNaN(parsedFallback)) return parsedFallback.toFixed(3);
    return fallbackFormatted || '0.000';
  }

  return fallbackFormatted || '0.00';
}

export function getMinimumStockDisplay(item, minimumQuantity) {
  if (!minimumQuantity || minimumQuantity <= 0) return '-';
  const specification = (item.specification || '').trim().toLowerCase();
  if (specification === 'kilo') {
    return parseFloat(minimumQuantity).toFixed(3);
  }
  return parseFloat(minimumQuantity).toFixed(2);
}

export function isLowStockRow(stockValue, minimumQuantity) {
  if (!minimumQuantity || minimumQuantity <= 0) return false;
  const minQty = parseFloat(minimumQuantity);
  const currentQty = parseFloat(stockValue) || 0;
  return currentQty - minQty <= 0.001;
}

export function formatDateForInput(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}
