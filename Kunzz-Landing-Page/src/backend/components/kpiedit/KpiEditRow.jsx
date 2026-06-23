import {
  computeRowMetrics,
  getInputColorClass,
  isFieldEditable,
} from '../../utils/kpiEditCalculations.js';

const WEEKDAY_LABELS = ['日', '一', '二', '三', '四', '五', '六'];

function CurrencyInput({ field, day, value, editable, colorClass, onChange, onPaste }) {
  return (
    <div className="input-container">
      <span className="currency-prefix">RM</span>
      <input
        type="number"
        className={`excel-input currency-input ${colorClass}${editable ? '' : ' readonly'}`}
        data-field={field}
        data-day={day}
        value={value}
        min="0"
        step="0.01"
        placeholder="0.00"
        readOnly={!editable}
        disabled={!editable}
        onChange={(event) => onChange(day, field, event.target.value)}
        onPaste={onPaste}
      />
    </div>
  );
}

function IntegerInput({ field, day, value, editable, colorClass, max, onChange, onPaste }) {
  return (
    <input
      type="number"
      className={`excel-input ${colorClass}${editable ? '' : ' readonly'}`}
      data-field={field}
      data-day={day}
      value={value}
      min="0"
      max={max}
      placeholder="0"
      readOnly={!editable}
      disabled={!editable}
      onChange={(event) => onChange(day, field, event.target.value)}
      onPaste={onPaste}
    />
  );
}

export default function KpiEditRow({
  day,
  month,
  year,
  fields,
  isEditing,
  isOperationManager,
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
  const canEditField = (field) => isFieldEditable(field, isOperationManager, isEditing);

  const handlePaste = (event, field) => {
    if (!isEditing) return;
    event.preventDefault();
    const text = event.clipboardData.getData('text');
    onPaste(day, field, text);
  };

  return (
    <tr className={isEditing ? 'editing-row' : undefined}>
      <td className={`date-cell${isWeekend ? ' weekend' : ''}`}>
        {month}月{day}
        <small> (周{WEEKDAY_LABELS[date.getDay()]})</small>
      </td>

      <td>
        <CurrencyInput
          field="gross_sales"
          day={day}
          value={fields.gross_sales}
          editable={canEditField('gross_sales')}
          colorClass={getInputColorClass(fields, 'gross_sales')}
          onChange={onFieldChange}
          onPaste={(event) => handlePaste(event, 'gross_sales')}
        />
      </td>
      <td>
        <CurrencyInput
          field="discounts"
          day={day}
          value={fields.discounts}
          editable={canEditField('discounts')}
          colorClass={getInputColorClass(fields, 'discounts')}
          onChange={onFieldChange}
          onPaste={(event) => handlePaste(event, 'discounts')}
        />
      </td>
      <td className="calculated-cell" id={`net-sales-${day}`}>
        RM {metrics.netSales.toFixed(2)}
      </td>
      <td>
        <CurrencyInput
          field="tax"
          day={day}
          value={fields.tax}
          editable={canEditField('tax')}
          colorClass={getInputColorClass(fields, 'tax')}
          onChange={onFieldChange}
          onPaste={(event) => handlePaste(event, 'tax')}
        />
      </td>
      <td>
        <CurrencyInput
          field="service_fee"
          day={day}
          value={fields.service_fee}
          editable={canEditField('service_fee')}
          colorClass={getInputColorClass(fields, 'service_fee')}
          onChange={onFieldChange}
          onPaste={(event) => handlePaste(event, 'service_fee')}
        />
      </td>
      <td>
        <CurrencyInput
          field="adj_amount"
          day={day}
          value={fields.adj_amount}
          editable={canEditField('adj_amount')}
          colorClass={getInputColorClass(fields, 'adj_amount')}
          onChange={onFieldChange}
          onPaste={(event) => handlePaste(event, 'adj_amount')}
        />
      </td>
      <td className="calculated-cell" id={`tender-amount-${day}`}>
        RM {metrics.tenderAmount.toFixed(2)}
      </td>
      <td>
        <IntegerInput
          field="tables_used"
          day={day}
          value={fields.tables_used}
          editable={canEditField('tables_used')}
          colorClass={getInputColorClass(fields, 'tables_used')}
          max={50}
          onChange={onFieldChange}
          onPaste={(event) => handlePaste(event, 'tables_used')}
        />
      </td>
      <td>
        <IntegerInput
          field="diners"
          day={day}
          value={fields.diners}
          editable={canEditField('diners')}
          colorClass={getInputColorClass(fields, 'diners')}
          onChange={onFieldChange}
          onPaste={(event) => handlePaste(event, 'diners')}
        />
      </td>
      <td className="calculated-cell" id={`avg-per-diner-${day}`}>
        RM {metrics.avgPerDiner.toFixed(2)}
      </td>
      <td>
        <IntegerInput
          field="new_customers"
          day={day}
          value={fields.new_customers}
          editable={canEditField('new_customers')}
          colorClass={getInputColorClass(fields, 'new_customers')}
          onChange={onFieldChange}
          onPaste={(event) => handlePaste(event, 'new_customers')}
        />
      </td>
      <td>
        <IntegerInput
          field="returning_customers"
          day={day}
          value={fields.returning_customers}
          editable={canEditField('returning_customers')}
          colorClass={getInputColorClass(fields, 'returning_customers')}
          onChange={onFieldChange}
          onPaste={(event) => handlePaste(event, 'returning_customers')}
        />
      </td>
      <td className="calculated-cell" id={`returning-customer-rate-${day}`}>
        {metrics.returningRate.toFixed(2)}%
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
        {!isOperationManager && !isEditing && (
          <button
            type="button"
            className="delete-day-btn"
            id={`delete-btn-${day}`}
            title={`清空${day}日数据`}
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
