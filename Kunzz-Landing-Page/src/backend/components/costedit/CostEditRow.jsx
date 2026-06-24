import { EDITABLE_FIELDS } from '../../config/costEditConfig.js';
import { computeRowMetrics, getInputColorClass } from '../../utils/costEditCalculations.js';

const WEEKDAY_LABELS = ['日', '一', '二', '三', '四', '五', '六'];

function CurrencyInput({ field, day, value, editable, colorClass, autoFilled, onChange, onPaste }) {
  return (
    <div className={`input-container${autoFilled ? ' auto-filled-container' : ''}`}>
      <span className="currency-prefix">RM</span>
      <input
        type="number"
        className={`excel-input currency-input ${colorClass}${autoFilled ? ' auto-filled readonly' : ''}${editable ? '' : ' readonly'}`}
        data-field={field}
        data-day={day}
        value={value}
        min="0"
        step="0.01"
        placeholder="0.00"
        readOnly={!editable}
        disabled={!editable}
        title={autoFilled ? '销售额自动从KPI净销售额获取，不可手动编辑' : undefined}
        onChange={(event) => onChange(day, field, event.target.value)}
        onPaste={onPaste}
      />
    </div>
  );
}

export default function CostEditRow({
  day,
  month,
  year,
  fields,
  isEditing,
  saving,
  onFieldChange,
  onToggleEdit,
  onCancelEdit,
  onClearDay,
  onPaste,
}) {
  const date = new Date(year, month - 1, day);
  const isWeekend = date.getDay() === 0 || date.getDay() === 6;
  const metrics = computeRowMetrics(fields);

  const handlePaste = (event, field) => {
    if (!isEditing) return;
    event.preventDefault();
    onPaste(day, field, event.clipboardData.getData('text'));
  };

  return (
    <tr className={isEditing ? 'editing-row' : undefined}>
      <td className={`date-cell${isWeekend ? ' weekend' : ''}`}>
        {month}月{day}
        <small> (周{WEEKDAY_LABELS[date.getDay()]})</small>
      </td>

      <td>
        <CurrencyInput
          field="sales"
          day={day}
          value={fields.sales}
          editable={false}
          colorClass={getInputColorClass(fields, 'sales')}
          autoFilled
          onChange={onFieldChange}
          onPaste={(event) => handlePaste(event, 'sales')}
        />
      </td>

      {EDITABLE_FIELDS.map((field) => (
        <td key={field}>
          <CurrencyInput
            field={field}
            day={day}
            value={fields[field]}
            editable={isEditing}
            colorClass={getInputColorClass(fields, field)}
            onChange={onFieldChange}
            onPaste={(event) => handlePaste(event, field)}
          />
        </td>
      ))}

      <td className="calculated-cell" id={`c-total-${day}`}>
        RM {metrics.cTotal.toFixed(2)}
      </td>
      <td className={`calculated-cell${metrics.grossTotal < 0 ? ' negative' : ''}`} id={`gross-total-${day}`}>
        RM {metrics.grossTotal.toFixed(2)}
      </td>
      <td className="calculated-cell" id={`cost-percent-${day}`}>
        {metrics.costPercent.toFixed(2)}%
      </td>
      <td className="action-cell">
        <button
          type="button"
          className={`edit-btn${isEditing ? ' save-mode' : ''}`}
          id={`edit-btn-${day}`}
          title={isEditing ? `保存${day}日数据` : `编辑${day}日数据`}
          disabled={saving}
          onClick={() => onToggleEdit(day, isEditing)}
        >
          <i className={`fas ${isEditing ? 'fa-save' : 'fa-edit'}`} />
        </button>
        {isEditing && (
          <button
            type="button"
            className="cancel-edit-btn"
            id={`cancel-btn-${day}`}
            title="取消编辑"
            disabled={saving}
            onClick={() => onCancelEdit(day)}
          >
            <i className="fas fa-times" />
          </button>
        )}
        {!isEditing && (
          <button
            type="button"
            className="delete-day-btn"
            id={`delete-btn-${day}`}
            title={`清空${day}日成本（保留销售额）`}
            disabled={saving}
            onClick={() => onClearDay(day)}
          >
            <i className="fas fa-trash-alt" />
          </button>
        )}
      </td>
    </tr>
  );
}
