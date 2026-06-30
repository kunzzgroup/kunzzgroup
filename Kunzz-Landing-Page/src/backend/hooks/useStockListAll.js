import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { useSearchParams } from 'react-router-dom';

import {
  fetchLowStockAlerts,
  fetchLowStockSettings,
  fetchPagePermissions,
  fetchStockListConfig,
  fetchStockSummary,
  fetchSupplyTotal,
  refreshSession,
} from '../api/stockListApi.js';
import {
  CENTRAL_SUPPLY_SYSTEMS,
  SYSTEM_OPTIONS,
  TYPE_FILTER_OPTIONS,
  VIEW_OPTIONS,
  VIEW_REDIRECT_MAP,
} from '../config/stockListConstants.js';
import { exportStockPdf } from '../utils/stockListExport.js';
import {
  filterStockItems,
  formatDateForInput,
} from '../utils/stockListCalculations.js';
import { getBackendBase } from '../../config.js';

const VALID_SYSTEMS = ['central', 'j1', 'j2', 'j3'];

function createEmptyTypeFilters() {
  return {
    j1: new Set(),
    j2: new Set(),
    j3: new Set(),
  };
}

export function useStockListAll() {
  const [searchParams, setSearchParams] = useSearchParams();
  const urlSystem = searchParams.get('system');
  const initialSystem = VALID_SYSTEMS.includes(urlSystem) ? urlSystem : 'central';

  const [config, setConfig] = useState(null);
  const [system, setSystem] = useState(initialSystem);
  const [allowedSystems, setAllowedSystems] = useState(SYSTEM_OPTIONS.map((o) => o.value));
  const [allowedViews, setAllowedViews] = useState(VIEW_OPTIONS.map((o) => o.value));
  const [loading, setLoading] = useState(true);
  const [stockData, setStockData] = useState([]);
  const [summaryData, setSummaryData] = useState(null);
  const [supplyTotals, setSupplyTotals] = useState({ j1: '0.00', j2: '0.00', j3: '0.00' });
  const [lowStockSettings, setLowStockSettings] = useState({});
  const [searchTerm, setSearchTerm] = useState('');
  const [typeFilters, setTypeFilters] = useState(createEmptyTypeFilters);
  const [toast, setToast] = useState(null);
  const [lowStockAlerts, setLowStockAlerts] = useState(null);
  const [exportOpen, setExportOpen] = useState(false);
  const [exportDates, setExportDates] = useState({ startDate: '', endDate: '', quickType: 'this_month' });
  const [exporting, setExporting] = useState(false);

  const [permissionsReady, setPermissionsReady] = useState(false);
  const loadRequestIdRef = useRef(0);

  const showToast = useCallback((message, type = 'info') => {
    setToast({ message, type });
    window.clearTimeout(showToast._timer);
    showToast._timer = window.setTimeout(() => setToast(null), 4000);
  }, []);

  const filteredItems = useMemo(
    () => filterStockItems(stockData, searchTerm, typeFilters[system], system, lowStockSettings),
    [stockData, searchTerm, typeFilters, system, lowStockSettings],
  );

  const loadSupplyTotals = useCallback(async () => {
    try {
      const entries = await Promise.all(
        CENTRAL_SUPPLY_SYSTEMS.map(async (sys) => {
          const data = await fetchSupplyTotal(sys);
          return [sys, data.formatted_total_value || '0.00'];
        }),
      );
      setSupplyTotals(Object.fromEntries(entries));
    } catch {
      // silent — same as original
    }
  }, []);

  const loadSystemData = useCallback(async (targetSystem) => {
    const requestId = loadRequestIdRef.current + 1;
    loadRequestIdRef.current = requestId;
    setLoading(true);

    try {
      const settings = await fetchLowStockSettings();
      if (requestId !== loadRequestIdRef.current) return;
      setLowStockSettings(settings);

      const data = await fetchStockSummary(targetSystem);
      if (requestId !== loadRequestIdRef.current) return;

      setSummaryData(data);
      setStockData(data.summary || []);

      if (targetSystem === 'central') {
        await loadSupplyTotals();
      }

      if (requestId !== loadRequestIdRef.current) return;

      if ((data.summary || []).length === 0) {
        showToast(`当前没有${targetSystem === 'central' ? '中央' : targetSystem.toUpperCase()}数据`, 'info');
      }
    } catch (error) {
      if (requestId !== loadRequestIdRef.current) return;

      setStockData([]);
      setSummaryData(null);
      if (error.code === 'SESSION_EXPIRED') {
        showToast('会话已过期，请重新登录', 'error');
      } else {
        showToast(error.message || '加载数据失败', 'error');
      }
    } finally {
      if (requestId === loadRequestIdRef.current) {
        setLoading(false);
      }
    }
  }, [loadSupplyTotals, showToast]);

  const applyPermissions = useCallback(async () => {
    try {
      const result = await fetchPagePermissions();
      if (!result.success) return;

      const stockPerms = result.page_permissions?.stock_inventory || {};
      const systems = stockPerms.system?.length ? stockPerms.system : SYSTEM_OPTIONS.map((o) => o.value);
      const views = (stockPerms.views || stockPerms.view)?.length
        ? (stockPerms.views || stockPerms.view)
        : VIEW_OPTIONS.map((o) => o.value);

      setAllowedSystems(systems);
      setAllowedViews(views);

      let activeSystem = system;
      if (systems.length > 0 && !systems.includes(activeSystem)) {
        activeSystem = systems[0];
        setSystem(activeSystem);
        setSearchParams({ system: activeSystem }, { replace: true });
      }

      if (views.length > 0 && !views.includes('list')) {
        const viewOrder = ['records', 'remark', 'product', 'sot'];
        const viewToOpen = viewOrder.find((view) => views.includes(view));
        if (viewToOpen) {
          const backendBase = getBackendBase();
          const target = VIEW_REDIRECT_MAP[viewToOpen];
          const systemParam = viewToOpen === 'remark' || viewToOpen === 'sot'
            ? '?system=central'
            : `?system=${activeSystem || 'central'}`;
          window.location.href = `${backendBase}/${target}${systemParam}`;
          return false;
        }
      }

      return true;
    } catch {
      return true;
    }
  }, [system, setSearchParams]);

  const checkAlerts = useCallback(async () => {
    try {
      const data = await fetchLowStockAlerts();
      if (data.alerts?.length > 0) {
        setLowStockAlerts(data.alerts);
      }
    } catch {
      // ignore
    }
  }, []);

  useEffect(() => {
    let cancelled = false;

    async function init() {
      try {
        const cfg = await fetchStockListConfig();
        if (!cancelled) setConfig(cfg);
      } catch (error) {
        if (!cancelled) {
          showToast(error.message || '加载配置失败', 'error');
        }
      }

      const canStay = await applyPermissions();
      if (cancelled || canStay === false) return;

      if (!cancelled) setPermissionsReady(true);
      await checkAlerts();
    }

    init();

    return () => {
      cancelled = true;
    };
  }, []);

  useEffect(() => {
    if (!permissionsReady) return;
    loadSystemData(system);
  }, [permissionsReady, system, loadSystemData]);

  useEffect(() => {
    const interval = window.setInterval(async () => {
      if (document.hidden) return;
      try {
        const result = await refreshSession();
        if (!result.success && result.code === 'SESSION_EXPIRED') {
          showToast('会话已过期，请重新登录', 'error');
        }
      } catch {
        // ignore
      }
    }, 5 * 60 * 1000);

    return () => window.clearInterval(interval);
  }, [showToast]);

  useEffect(() => {
    const refresh = () => {
      fetchLowStockSettings()
        .then(setLowStockSettings)
        .then(() => loadSystemData(system));
    };

    const onVisibility = () => {
      if (!document.hidden) refresh();
    };

    window.addEventListener('focus', refresh);
    document.addEventListener('visibilitychange', onVisibility);

    return () => {
      window.removeEventListener('focus', refresh);
      document.removeEventListener('visibilitychange', onVisibility);
    };
  }, [system, loadSystemData]);

  useEffect(() => {
    const interval = window.setInterval(() => {
      if (!document.hidden) loadSystemData(system);
    }, 300000);

    return () => window.clearInterval(interval);
  }, [system, loadSystemData]);

  const switchSystem = useCallback((nextSystem) => {
    if (!VALID_SYSTEMS.includes(nextSystem)) return;
    if (allowedSystems.length > 0 && !allowedSystems.includes(nextSystem)) return;
    if (nextSystem === system) return;

    setStockData([]);
    setSummaryData(null);
    setLoading(true);
    setSystem(nextSystem);
    setSearchTerm('');
    setTypeFilters(createEmptyTypeFilters());
    setSearchParams({ system: nextSystem }, { replace: true });
  }, [allowedSystems, setSearchParams, system]);

  const switchView = useCallback((view) => {
    if (view === 'list') return;

    const backendBase = getBackendBase();
    const systemParam = view === 'remark' || view === 'sot'
      ? '?system=central'
      : `?system=${system || 'central'}`;

    window.location.href = `${backendBase}/${VIEW_REDIRECT_MAP[view]}${systemParam}`;
  }, [system]);

  const toggleTypeFilter = useCallback((type) => {
    if (!TYPE_FILTER_OPTIONS[system]) return;

    setTypeFilters((prev) => {
      const next = { ...prev, [system]: new Set(prev[system]) };
      if (next[system].has(type)) {
        next[system].delete(type);
      } else {
        next[system].add(type);
      }
      return next;
    });
  }, [system]);

  const goToMinimumSettings = useCallback(() => {
    window.location.href = `${getBackendBase()}/stockminimum.php?system=${system}`;
  }, [system]);

  const openExport = useCallback(() => {
    if (filteredItems.length === 0 && stockData.length === 0) {
      showToast('没有数据可导出', 'error');
      return;
    }

    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    setExportDates({
      startDate: formatDateForInput(firstDay),
      endDate: formatDateForInput(today),
      quickType: 'this_month',
    });
    setExportOpen(true);
  }, [filteredItems.length, stockData.length, showToast]);

  const setQuickDateRange = useCallback((type) => {
    const today = new Date();
    let startDate = '';
    let endDate = formatDateForInput(today);

    switch (type) {
      case 'today':
        startDate = formatDateForInput(today);
        break;
      case 'this_month':
        startDate = formatDateForInput(new Date(today.getFullYear(), today.getMonth(), 1));
        break;
      case 'last_month':
        startDate = formatDateForInput(new Date(today.getFullYear(), today.getMonth() - 1, 1));
        endDate = formatDateForInput(new Date(today.getFullYear(), today.getMonth(), 0));
        break;
      case 'all':
        startDate = '';
        break;
      default:
        break;
    }

    setExportDates({ startDate, endDate, quickType: type });
  }, []);

  const confirmExport = useCallback(async () => {
    const { startDate, endDate } = exportDates;
    if (!endDate) {
      showToast('请选择结束日期', 'error');
      return;
    }
    if (startDate && startDate > endDate) {
      showToast('开始日期不能晚于结束日期', 'error');
      return;
    }

    setExportOpen(false);
    setExporting(true);
    showToast('正在准备导出数据...', 'info');

    try {
      const today = formatDateForInput(new Date());
      const usePageData = (!startDate && !endDate) || (!startDate && endDate === today);

      let dataToExport;
      if (usePageData) {
        dataToExport = filteredItems.length > 0 ? [...filteredItems] : [...stockData];
        if (dataToExport.length === 0) {
          showToast('没有数据可导出，请先刷新页面', 'error');
          return;
        }
      } else {
        showToast('正在获取指定日期范围库存数据...', 'info');
        const data = await fetchStockSummary(system, { startDate, endDate });
        dataToExport = data.summary || [];
      }

      if (system === 'j2') {
        dataToExport = dataToExport.filter((item) => item.type !== 'Sake');
      }

      if (!dataToExport.length) {
        showToast('所选日期范围没有数据可导出', 'error');
        return;
      }

      await exportStockPdf(system, dataToExport, lowStockSettings, startDate, endDate);
      showToast('PDF导出成功', 'success');
    } catch (error) {
      showToast(`导出失败: ${error.message}`, 'error');
    } finally {
      setExporting(false);
    }
  }, [exportDates, filteredItems, stockData, system, lowStockSettings, showToast]);

  const systemOptions = useMemo(
    () => SYSTEM_OPTIONS.filter((opt) => allowedSystems.includes(opt.value)),
    [allowedSystems],
  );

  const viewOptions = useMemo(
    () => VIEW_OPTIONS.filter((opt) => allowedViews.includes(opt.value)),
    [allowedViews],
  );

  return {
    config,
    system,
    systemOptions,
    viewOptions,
    loading,
    exporting,
    stockData,
    summaryData,
    supplyTotals,
    lowStockSettings,
    filteredItems,
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
    showToast,
  };
}
