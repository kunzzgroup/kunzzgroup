import BackendLayout from '../components/layout/BackendLayout.jsx';
import CostEditHeader from '../components/costedit/CostEditHeader.jsx';
import CostEditMonthSelector from '../components/costedit/CostEditMonthSelector.jsx';
import CostEditStatsBar from '../components/costedit/CostEditStatsBar.jsx';
import CostEditTable from '../components/costedit/CostEditTable.jsx';
import KpiEditToast from '../components/kpiedit/KpiEditToast.jsx';
import { useCostEdit } from '../hooks/useCostEdit.js';

export default function CostEditPage() {
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
    currentStock,
    setCurrentStock,
    loading,
    saving,
    toast,
    monthStats,
    updateField,
    startEdit,
    cancelEdit,
    persistRow,
    clearDay,
    saveAll,
    applyPaste,
  } = useCostEdit();

  const handleToggleEdit = (day, isEditing) => {
    if (isEditing) {
      persistRow(day);
    } else {
      startEdit(day);
    }
  };

  const restaurantName = config?.restaurantConfig?.[restaurant]?.name || '--';

  if (loading && !config) {
    return (
      <BackendLayout stylesheet="costedit.css">
        <div className="container">
          <p style={{ padding: 24 }}>正在加载...</p>
        </div>
      </BackendLayout>
    );
  }

  return (
    <BackendLayout stylesheet="costedit.css">
      <div className="container">
        <CostEditHeader config={config} restaurant={restaurant} onRestaurantChange={changeRestaurant} />

        <div id="alert-container" />

        <CostEditMonthSelector
          year={year}
          month={month}
          restaurantName={restaurantName}
          onYearChange={setYear}
          onMonthChange={setMonth}
        />

        <div className="excel-container">
          <CostEditStatsBar
            stats={monthStats}
            currentStock={currentStock}
            saving={saving}
            onStockChange={setCurrentStock}
            onSaveAll={saveAll}
          />
          <CostEditTable
            year={year}
            month={month}
            daysInMonth={daysInMonth}
            rowFields={rowFields}
            editingDays={editingDays}
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
