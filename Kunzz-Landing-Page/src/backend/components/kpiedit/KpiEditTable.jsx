import { createEmptyRowFields } from '../../utils/kpiEditCalculations.js';
import KpiEditRow from './KpiEditRow.jsx';

export default function KpiEditTable({
  year,
  month,
  daysInMonth,
  rowFields,
  editingDays,
  isOperationManager,
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
            <th style={{ width: '7%' }}>日期</th>
            <th style={{ width: '8%' }}>总销售额</th>
            <th style={{ width: '6%' }}>折扣</th>
            <th style={{ width: '8%' }}>净销售额</th>
            <th style={{ width: '7%' }}>税</th>
            <th style={{ width: '7%' }}>服务费</th>
            <th style={{ width: '7%' }}>调整金额</th>
            <th style={{ width: '8%' }}>投标金额</th>
            <th style={{ width: '5%' }}>桌数总数</th>
            <th style={{ width: '5%' }}>顾客总数</th>
            <th style={{ width: '8%' }}>人均消费</th>
            <th style={{ width: '5%' }}>新客人数</th>
            <th style={{ width: '5%' }}>常客人数</th>
            <th style={{ width: '7%' }}>常客人率 (%)</th>
            <th style={{ width: '9%' }}>操作</th>
          </tr>
        </thead>
        <tbody id="excel-tbody">
          {loading ? (
            <tr>
              <td colSpan={15} style={{ textAlign: 'center', padding: 24 }}>
                正在加载数据...
              </td>
            </tr>
          ) : (
            Array.from({ length: daysInMonth }, (_, index) => index + 1).map((day) => (
              <KpiEditRow
                key={day}
                day={day}
                month={month}
                year={year}
                fields={rowFields[day] || createEmptyRowFields()}
                isEditing={editingDays.has(day)}
                isOperationManager={isOperationManager}
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
