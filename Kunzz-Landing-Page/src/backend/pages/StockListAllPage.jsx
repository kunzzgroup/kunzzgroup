import { useEffect, useState } from 'react';

import ExportDateModal from '../components/stocklistall/ExportDateModal.jsx';
import LowStockModal from '../components/stocklistall/LowStockModal.jsx';
import StockListAlert from '../components/stocklistall/StockListAlert.jsx';
import StockListHeader from '../components/stocklistall/StockListHeader.jsx';
import StockListTable from '../components/stocklistall/StockListTable.jsx';
import StockSummaryBar from '../components/stocklistall/StockSummaryBar.jsx';
import BackendLayout from '../components/layout/BackendLayout.jsx';
import { useStockListAll } from '../hooks/useStockListAll.js';

export default function StockListAllPage() {
  const {
    config,
    system,
    systemOptions,
    viewOptions,
    loading,
    exporting,
    summaryData,
    supplyTotals,
    lowStockSettings,
    filteredItems,
    stockData,
    searchTerm,
    setSearchTerm,
    typeFilters,
    toast,
    lowStockAlerts,
    setLowStockAlerts,
    switchSystem,
    switchView,
    toggleTypeFilter,
    goToMinimumSettings,
    openExport,
    exportOpen,
    setExportOpen,
    exportDates,
    setExportDates,
    setQuickDateRange,
    confirmExport,
  } = useStockListAll();

  const [showBackToTop, setShowBackToTop] = useState(false);

  useEffect(() => {
    const onScroll = () => setShowBackToTop(window.pageYOffset > 150);
    window.addEventListener('scroll', onScroll);
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  if (!config && loading && stockData.length === 0) {
    return (
      <BackendLayout stylesheet="stocklistall.css" extraStylesheets={['smartSearch.css']}>
        <div className="container">
          <p style={{ padding: 24 }}>正在加载...</p>
        </div>
      </BackendLayout>
    );
  }

  return (
    <BackendLayout stylesheet="stocklistall.css" extraStylesheets={['smartSearch.css']}>
      <div className="container">
        <StockListHeader
          system={system}
          systemOptions={systemOptions}
          viewOptions={viewOptions}
          onSwitchSystem={switchSystem}
          onSwitchView={switchView}
        />

        <StockListAlert toast={toast} />

        <div id={`${system}-page`} className="page-section active">
          <StockSummaryBar
            system={system}
            summaryData={summaryData}
            supplyTotals={supplyTotals}
            searchTerm={searchTerm}
            onSearchChange={setSearchTerm}
            onExport={openExport}
            onMinimumSettings={goToMinimumSettings}
            displayedCount={filteredItems.length}
            totalCount={stockData.length}
            typeFilters={typeFilters[system] || new Set()}
            onToggleTypeFilter={toggleTypeFilter}
          />

          <StockListTable
            system={system}
            items={filteredItems}
            lowStockSettings={lowStockSettings}
            loading={loading}
          />
        </div>
      </div>

      <LowStockModal
        alerts={lowStockAlerts}
        onClose={() => setLowStockAlerts(null)}
      />

      <ExportDateModal
        open={exportOpen}
        exportDates={exportDates}
        onClose={() => setExportOpen(false)}
        onChangeDates={setExportDates}
        onQuickRange={setQuickDateRange}
        onConfirm={confirmExport}
        exporting={exporting}
      />

      {showBackToTop && (
        <button
          type="button"
          className="back-to-top"
          onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}
          aria-label="回到顶部"
        >
          <i className="fas fa-arrow-up" />
        </button>
      )}
    </BackendLayout>
  );
}
