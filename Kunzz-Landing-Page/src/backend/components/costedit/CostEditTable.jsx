import { createEmptyRowFields } from '../../utils/costEditCalculations.js';
import CostEditRow from './CostEditRow.jsx';

export default function CostEditTable({
  year,
  month,
  daysInMonth,
  rowFields,
  editingDays,
  saving,
  loading,
  onFieldChange,
  onToggleEdit,
  onCancelEdit,
  onClearDay,
  onPaste,
}) {
  return (
    <div className="table-scroll-container">
      <table className="excel-table" id="excel-table">
        <thead>
          <tr>
            <th style={{ width: '10%' }}>日期</th>
            <th style={{ width: '12%' }}>销售额</th>
            <th style={{ width: '10%' }}>饮料成本</th>
            <th style={{ width: '10%' }}>厨房成本</th>
            <th style={{ width: '10%' }}>Grab Food</th>
            <th style={{ width: '10%' }}>Foodpanda</th>
            <th style={{ width: '10%' }}>Shopee Food</th>
            <th style={{ width: '10%' }}>总成本</th>
            <th style={{ width: '12%' }}>毛利润</th>
            <th style={{ width: '10%' }}>成本率 (%)</th>
            <th style={{ width: '10%' }}>操作</th>
          </tr>
        </thead>
        <tbody id="excel-tbody">
          {loading ? (
            <tr>
              <td colSpan={11} style={{ textAlign: 'center', padding: 24 }}>
                正在加载数据...
              </td>
            </tr>
          ) : (
            Array.from({ length: daysInMonth }, (_, index) => index + 1).map((day) => (
              <CostEditRow
                key={day}
                day={day}
                month={month}
                year={year}
                fields={rowFields[day] || createEmptyRowFields()}
                isEditing={editingDays.has(day)}
                saving={saving}
                onFieldChange={onFieldChange}
                onToggleEdit={onToggleEdit}
                onCancelEdit={onCancelEdit}
                onClearDay={onClearDay}
                onPaste={onPaste}
              />
            ))
          )}
        </tbody>
      </table>
    </div>
  );
}
