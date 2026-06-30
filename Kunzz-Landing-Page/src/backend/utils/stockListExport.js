import {
  formatCurrency,
  formatStockQuantity,
  getMinimumStockDisplay,
} from './stockListCalculations.js';

const JSPDF_URL = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
const AUTOTABLE_URL = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js';

let scriptsPromise = null;

function loadScript(src, id) {
  if (document.getElementById(id)) {
    return Promise.resolve();
  }

  return new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.id = id;
    script.src = src;
    script.async = true;
    script.onload = () => resolve();
    script.onerror = () => reject(new Error(`Failed to load ${src}`));
    document.head.appendChild(script);
  });
}

export function loadJsPdfScripts() {
  if (scriptsPromise) return scriptsPromise;

  scriptsPromise = loadScript(JSPDF_URL, 'stocklist-jspdf')
    .then(() => loadScript(AUTOTABLE_URL, 'stocklist-jspdf-autotable'));

  return scriptsPromise;
}

function formatDateForDisplay(dateStr) {
  if (!dateStr) return 'All';
  const match = String(dateStr).trim().match(/^(\d{4})-(\d{2})-(\d{2})/);
  if (match) {
    return `${match[2]}/${match[3]}/${match[1]}`;
  }

  const date = new Date(dateStr);
  if (Number.isNaN(date.getTime())) return dateStr;
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${month}/${day}/${date.getFullYear()}`;
}

export async function exportStockPdf(system, dataToExport, lowStockSettings, startDate, endDate) {
  await loadJsPdfScripts();

  const { jsPDF } = window.jspdf;
  const doc = new jsPDF('landscape', 'mm', 'a4');

  const systemNameMap = {
    central: 'Central',
    j1: 'J1',
    j2: 'J2',
    j3: 'J3',
  };
  const systemName = systemNameMap[system] || system.toUpperCase();
  const title = `${systemName} Stock Summary Report`;

  doc.setFontSize(16);
  doc.setFont(undefined, 'bold');
  doc.text(title, 14, 15);

  doc.setFontSize(10);
  doc.setFont(undefined, 'normal');
  const exportTimeStr = new Date().toLocaleString('en-US', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  });
  doc.text(`Export Time: ${exportTimeStr}`, 14, 22);

  if (startDate) {
    doc.text(`Date Range: ${formatDateForDisplay(startDate)} - ${formatDateForDisplay(endDate)}`, 14, 28);
  } else {
    doc.text(`As of Date: ${formatDateForDisplay(endDate)}`, 14, 28);
  }
  doc.text(`Records: ${dataToExport.length}`, 200, 22);

  const headers = [['No.', 'Product Name', 'Code Number', 'Minimum Stock', 'Total Stock', 'Specification', 'Unit Price', 'Total Price']];
  const tableData = [];
  let totalValue = 0;

  dataToExport.forEach((item, index) => {
    if (!item) return;

    const productName = (item.product_name || '').trim();
    const minimumQuantity = lowStockSettings[productName] || 0;
    const minimumStockDisplay = getMinimumStockDisplay(item, minimumQuantity);

    const priceValue = parseFloat(item.total_price) || 0;
    totalValue += priceValue;

    tableData.push([
      (item.no || (index + 1)).toString(),
      item.product_name || '-',
      item.code_number || '-',
      minimumStockDisplay,
      item.formatted_stock || formatStockQuantity(item),
      item.specification || '-',
      item.formatted_price || item.price || '0.00',
      item.formatted_total_price || item.total_price || '0.00',
    ]);
  });

  tableData.push(['', 'Total', '', '', '', '', '', `RM ${formatCurrency(totalValue)}`]);

  doc.autoTable({
    head: headers,
    body: tableData,
    startY: 34,
    styles: {
      fontSize: 8,
      cellPadding: 2,
      overflow: 'linebreak',
      cellWidth: 'wrap',
    },
    headStyles: {
      fillColor: [99, 99, 99],
      textColor: [255, 255, 255],
      fontStyle: 'bold',
      fontSize: 9,
    },
    alternateRowStyles: {
      fillColor: [245, 245, 245],
    },
    columnStyles: {
      0: { cellWidth: 18 },
      1: { cellWidth: 55 },
      2: { cellWidth: 35 },
      3: { cellWidth: 28 },
      4: { cellWidth: 28 },
      5: { cellWidth: 25 },
      6: { cellWidth: 35 },
      7: { cellWidth: 35 },
    },
    margin: { top: 28, left: 14, right: 14 },
    didDrawPage(data) {
      doc.setFontSize(8);
      doc.text(
        `Page ${data.pageNumber}`,
        doc.internal.pageSize.width / 2,
        doc.internal.pageSize.height - 10,
        { align: 'center' },
      );
    },
  });

  const formatDateForFileName = (dateStr) => (dateStr ? dateStr.replace(/-/g, '') : '');
  let fileName;
  if (startDate) {
    fileName = `${system}_stock_summary_${formatDateForFileName(startDate)}_to_${formatDateForFileName(endDate)}.pdf`;
  } else {
    fileName = `${system}_stock_summary_${formatDateForFileName(endDate)}.pdf`;
  }

  doc.save(fileName);
}
