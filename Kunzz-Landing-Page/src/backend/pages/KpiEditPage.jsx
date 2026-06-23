import BackendLayout from '../components/layout/BackendLayout.jsx';
import KpiEditHeader from '../components/kpiedit/KpiEditHeader.jsx';
import KpiEditMonthSelector from '../components/kpiedit/KpiEditMonthSelector.jsx';
import KpiEditStatsBar from '../components/kpiedit/KpiEditStatsBar.jsx';
import KpiEditTable from '../components/kpiedit/KpiEditTable.jsx';
import KpiEditToast from '../components/kpiedit/KpiEditToast.jsx';
import { useKpiEdit } from '../hooks/useKpiEdit.js';

export default function KpiEditPage() {
  const {
    config,
    restaurant,
    changeRestaurant,
    year,
    setYear,
    month,
    setMonth,
    daysInMonth,
    rowFields,
    editingDays,
    loading,
    saving,
    toast,
    monthStats,
    updateField,
    startEdit,
    cancelEdit,
    saveRow,
    clearDay,
    saveAll,
    applyPaste,
  } = useKpiEdit();

  const handleToggleEdit = (day, isEditing) => {
    if (isEditing) {
      saveRow(day);
    } else {
      startEdit(day);
    }
  };

  const restaurantName = config?.restaurantConfig?.[restaurant]?.name || '--';

  if (loading && !config) {
    return (
      <BackendLayout stylesheet="kpiedit.css">
        <div className="container">
          <p style={{ padding: 24 }}>正在加载...</p>
        </div>
      </BackendLayout>
    );
  }

  return (
    <BackendLayout stylesheet="kpiedit.css">
      <div className="container">
        <KpiEditHeader config={config} restaurant={restaurant} onRestaurantChange={changeRestaurant} />

        <div id="alert-container" />

        <KpiEditMonthSelector
          year={year}
          month={month}
          restaurantName={restaurantName}
          onYearChange={setYear}
          onMonthChange={setMonth}
        />

        <div className="excel-container">
          <KpiEditStatsBar stats={monthStats} saving={saving} onSaveAll={saveAll} />
          <KpiEditTable
            year={year}
            month={month}
            daysInMonth={daysInMonth}
            rowFields={rowFields}
            editingDays={editingDays}
            isOperationManager={config?.isOperationManager}
            saving={saving}
            loading={loading}
            onFieldChange={updateField}
            onToggleEdit={handleToggleEdit}
            onCancelEdit={cancelEdit}
            onClearDay={clearDay}
            onPaste={applyPaste}
          />
        </div>
      </div>

      <KpiEditToast toast={toast} />
    </BackendLayout>
  );
}
