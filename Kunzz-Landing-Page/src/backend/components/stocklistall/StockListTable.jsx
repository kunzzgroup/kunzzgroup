import { SYSTEM_NAMES, STOCK_COLUMN_LABEL } from '../../config/stockListConstants.js';
import {
  formatCurrency,
  formatStockQuantity,
  getMinimumStockDisplay,
  isLowStockRow,
} from '../../utils/stockListCalculations.js';

export default function StockListTable({ system, items, lowStockSettings, loading }) {
  const stockColumnLabel = STOCK_COLUMN_LABEL[system] || '库存数量';

  if (loading && items.length === 0) {
    return (
      <div className="table-container">
        <div className="table-scroll-container">
          <table className="stock-table" id={`${system}-stock-table`}>
            <thead>
              <tr>
                <th>序号.</th>
                <th>货品编号</th>
                <th>货品</th>
                <th>最低库存</th>
                <th>{stockColumnLabel}</th>
                <th>规格</th>
                <th>单价</th>
                <th>总价</th>
              </tr>
            </thead>
            <tbody id={`${system}-stock-tbody`}>
              <tr>
                <td colSpan={8} style={{ padding: 40, textAlign: 'center' }}>
                  <div className="loading" />
                  <div style={{ marginTop: 16, color: '#6b7280' }}>
                    正在加载{SYSTEM_NAMES[system]}数据...
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    );
  }

  if (items.length === 0) {
    return (
      <div className="table-container">
        <div className="table-scroll-container">
          <table className="stock-table" id={`${system}-stock-table`}>
            <thead>
              <tr>
                <th>序号.</th>
                <th>货品编号</th>
                <th>货品</th>
                <th>最低库存</th>
                <th>{stockColumnLabel}</th>
                <th>规格</th>
                <th>单价</th>
                <th>总价</th>
              </tr>
            </thead>
            <tbody id={`${system}-stock-tbody`}>
              <tr>
                <td colSpan={8} className="no-data">
                  <i className="fas fa-inbox" />
                  <div>暂无{SYSTEM_NAMES[system]}数据</div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    );
  }

  let totalValue = 0;

  const rows = items.map((item, index) => {
    const stockValue = parseFloat(item.total_stock) || 0;
    const priceValue = parseFloat(item.total_price) || 0;
    totalValue += priceValue;

    const productName = (item.product_name || '').trim();
    const minimumQuantity = lowStockSettings[productName] || 0;
    const minimumStockDisplay = getMinimumStockDisplay(item, minimumQuantity);
    const rowClass = isLowStockRow(stockValue, minimumQuantity) ? 'low-stock-row' : '';
    const stockClass = stockValue > 0 ? 'positive-value' : 'zero-value';
    const priceClass = priceValue > 0 ? 'positive-value' : 'zero-value';
    const minimumStockClass = minimumQuantity > 0 ? 'minimum-stock-value' : 'zero-value';

    return (
      <tr key={`${item.code_number || item.product_name}-${index}`} className={rowClass}>
        <td className="text-center">{index + 1}</td>
        <td className="text-center">{item.code_number || '-'}</td>
        <td><strong>{item.product_name}</strong></td>
        <td className="stock-cell">
          <div className={`currency-display ${minimumStockClass}`}>
            <span className="currency-symbol">&nbsp;</span>
            <span className="currency-amount">{minimumStockDisplay}</span>
          </div>
        </td>
        <td className="stock-cell">
          <div className={`currency-display ${stockClass}`}>
            <span className="currency-symbol">&nbsp;</span>
            <span className="currency-amount">{formatStockQuantity(item)}</span>
          </div>
        </td>
        <td className="text-center">{item.specification || '-'}</td>
        <td className="price-cell">
          <div className="currency-display">
            <span className="currency-symbol">RM</span>
            <span className="currency-amount">{item.formatted_price}</span>
          </div>
        </td>
        <td className="price-cell">
          <div className={`currency-display ${priceClass}`}>
            <span className="currency-symbol">RM</span>
            <span className="currency-amount">{item.formatted_total_price}</span>
          </div>
        </td>
      </tr>
    );
  });

  return (
    <div className="table-container">
      <div className="table-scroll-container">
        <table className="stock-table" id={`${system}-stock-table`}>
          <thead>
            <tr>
              <th>序号.</th>
              <th>货品编号</th>
              <th>货品</th>
              <th>最低库存</th>
              <th>{stockColumnLabel}</th>
              <th>规格</th>
              <th>单价</th>
              <th>总价</th>
            </tr>
          </thead>
          <tbody id={`${system}-stock-tbody`}>
            {rows}
            <tr className="total-row">
              <td
                colSpan={7}
                className="text-right"
                style={{
                  fontSize: 'clamp(10px, 0.84vw, 16px)',
                  paddingRight: 15,
                  textAlign: 'right',
                }}
              >
                总计:
              </td>
              <td className="price-cell positive-value" style={{ fontSize: 16 }}>
                <div className="currency-display">
                  <span className="currency-symbol">RM</span>
                  <span className="currency-amount">{formatCurrency(totalValue)}</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  );
}
