import { useCallback, useEffect, useMemo, useState } from 'react';

import {
  fetchCostEditConfig,
  fetchCostEditMonthData,
  fetchCostEditMonthStock,
  getExistingCostRecordByDate,
  refreshSession,
  saveCostRecordWithFallback,
  saveMonthStockData,
} from '../api/costEditApi.js';
import { getMonthDateRange, PASTE_FIELDS } from '../config/costEditConfig.js';
import {
  buildClearCostPayload,
  buildCostRecordPayload,
  cleanPasteValue,
  clearCostFields,
  computeMonthStats,
  createEmptyRowFields,
  mergeCostAndKpiData,
  parsePasteLine,
  recordToRowFields,
  rowHasSaveableData,
} from '../utils/costEditCalculations.js';

export function useCostEdit() {
  const now = new Date();
  const [config, setConfig] = useState(null);
  const [restaurant, setRestaurant] = useState(null);
  const [year, setYear] = useState(now.getFullYear());
  const [month, setMonth] = useState(now.getMonth() + 1);
  const [dbRecords, setDbRecords] = useState({});
  const [rowFields, setRowFields] = useState({});
  const [kpiByDay, setKpiByDay] = useState({});
  const [currentStock, setCurrentStock] = useState('');
  const [editingDays, setEditingDays] = useState(new Set());
  const [preservedRows, setPreservedRows] = useState({});
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [toast, setToast] = useState(null);

  const { startDate, endDate, daysInMonth } = useMemo(
    () => getMonthDateRange(year, month),
    [year, month],
  );

  const yearMonth = `${year}-${String(month).padStart(2, '0')}`;

  const showToast = useCallback((message, type = 'success') => {
    setToast({ message, type });
    window.clearTimeout(showToast._timer);
    showToast._timer = window.setTimeout(() => setToast(null), 4000);
  }, []);

  useEffect(() => {
    let active = true;
    (async () => {
      try {
        const nextConfig = await fetchCostEditConfig();
        if (!active || !nextConfig) return;
        setConfig(nextConfig);
        setRestaurant(nextConfig.defaultRestaurant);
      } catch (error) {
        if (active) showToast(error.message || '加载配置失败', 'error');
      } finally {
        if (active) setLoading(false);
      }
    })();
    return () => {
      active = false;
    };
  }, [showToast]);

  useEffect(() => {
    const interval = window.setInterval(async () => {
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

  const loadMonthData = useCallback(
    async (preserveEditing = false) => {
      if (!restaurant) return;

      const editingSnapshot = preserveEditing ? new Set(editingDays) : new Set();
      const preservedSnapshot = preserveEditing ? { ...preservedRows } : {};

      if (!preserveEditing) {
        setEditingDays(new Set());
        setPreservedRows({});
      }

      setLoading(true);
      try {
        const [{ costData, kpiData, costLoadSucceeded }, stockRecord] = await Promise.all([
          fetchCostEditMonthData(restaurant, startDate, endDate),
          fetchCostEditMonthStock(restaurant, yearMonth),
        ]);

        const merged = mergeCostAndKpiData({
          costData,
          kpiData,
          year,
          month,
          costLoadSucceeded,
        });

        const allRowFields = {};
        for (let day = 1; day <= daysInMonth; day += 1) {
          allRowFields[day] =
            merged.rowFields[day] || recordToRowFields({}, merged.kpiByDay[day]);
        }

        setDbRecords(merged.dbRecords);
        setKpiByDay(merged.kpiByDay);
        setRowFields(allRowFields);

        if (stockRecord?.current_stock) {
          setCurrentStock(parseFloat(stockRecord.current_stock).toFixed(2));
        } else {
          setCurrentStock('');
        }

        if (preserveEditing) {
          setEditingDays(editingSnapshot);
          setPreservedRows(preservedSnapshot);
        }
      } catch (error) {
        if (error.code === 'SESSION_EXPIRED') {
          showToast('会话已过期，请重新登录', 'error');
        } else {
          showToast(error.message || '加载数据失败', 'error');
        }
        setDbRecords({});
        const emptyRows = {};
        for (let day = 1; day <= daysInMonth; day += 1) {
          emptyRows[day] = createEmptyRowFields();
        }
        setRowFields(emptyRows);
        setCurrentStock('');
      } finally {
        setLoading(false);
      }
    },
    [restaurant, startDate, endDate, year, month, daysInMonth, yearMonth, showToast],
  );

  useEffect(() => {
    if (!restaurant) return;
    loadMonthData(false);
  }, [restaurant, year, month]); // eslint-disable-line react-hooks/exhaustive-deps

  const monthStats = useMemo(
    () => computeMonthStats(rowFields, daysInMonth),
    [rowFields, daysInMonth],
  );

  const updateField = useCallback((day, field, value) => {
    if (field === 'sales') return;
    setRowFields((prev) => ({
      ...prev,
      [day]: {
        ...(prev[day] || createEmptyRowFields()),
        [field]: value,
      },
    }));
  }, []);

  const changeRestaurant = useCallback(
    (nextRestaurant) => {
      if (!config?.availableRestaurants.includes(nextRestaurant)) {
        showToast('您没有权限查看该店铺', 'warning');
        return;
      }
      setRestaurant(nextRestaurant);
    },
    [config, showToast],
  );

  const startEdit = useCallback((day) => {
    setPreservedRows((prev) => ({
      ...prev,
      [day]: { ...(rowFields[day] || createEmptyRowFields()) },
    }));
    setEditingDays((prev) => new Set(prev).add(day));
  }, [rowFields]);

  const cancelEdit = useCallback(
    (day) => {
      setRowFields((prev) => ({
        ...prev,
        [day]:
          preservedRows[day] ||
          recordToRowFields(dbRecords[day] || {}, kpiByDay[day]),
      }));
      setPreservedRows((prev) => {
        const next = { ...prev };
        delete next[day];
        return next;
      });
      setEditingDays((prev) => {
        const next = new Set(prev);
        next.delete(day);
        return next;
      });
    },
    [preservedRows, dbRecords, kpiByDay],
  );

  const persistRow = useCallback(
    async (day) => {
      const fields = rowFields[day] || createEmptyRowFields();
      if (!rowHasSaveableData(fields, dbRecords[day])) {
        showToast(`${day}日没有需要保存的数据`, 'info');
        setEditingDays((prev) => {
          const next = new Set(prev);
          next.delete(day);
          return next;
        });
        return;
      }

      setSaving(true);
      try {
        const payload = buildCostRecordPayload({
          fields,
          day,
          year,
          month,
          restaurant,
          dbRecord: dbRecords[day],
        });

        const { result } = await saveCostRecordWithFallback(payload, (dateStr) =>
          getExistingCostRecordByDate(restaurant, dateStr),
        );

        if (result.success === false) {
          const message = result.message || '';
          if (message.includes('已存在') || message.includes('无变化')) {
            showToast(`${day}日数据无需更新`, 'info');
          } else {
            throw new Error(message || '保存失败');
          }
        } else {
          showToast(`${day}日数据保存成功`, 'success');
        }

        await loadMonthData(true);
        setEditingDays((prev) => {
          const next = new Set(prev);
          next.delete(day);
          return next;
        });
      } catch (error) {
        showToast(`保存${day}日数据失败: ${error.message}`, 'error');
      } finally {
        setSaving(false);
      }
    },
    [rowFields, dbRecords, year, month, restaurant, showToast, loadMonthData],
  );

  const clearDay = useCallback(
    async (day) => {
      if (
        !window.confirm(
          `确定要清空${day}日的饮料成本/厨房成本吗？销售额将保留（从KPI自动获取）。`,
        )
      ) {
        return;
      }

      setSaving(true);
      try {
        let dbRecord = dbRecords[day];
        const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

        if (!dbRecord?.id) {
          const existing = await getExistingCostRecordByDate(restaurant, dateStr);
          if (existing?.id) dbRecord = { ...(dbRecord || {}), ...existing };
        }

        const payload = buildClearCostPayload({
          day,
          year,
          month,
          restaurant,
          dbRecord,
        });

        const { result } = await saveCostRecordWithFallback(payload, (searchDate) =>
          getExistingCostRecordByDate(restaurant, searchDate),
        );

        if (result?.success === false) {
          throw new Error(result.message || '清空成本失败');
        }

        setRowFields((prev) => ({
          ...prev,
          [day]: clearCostFields(prev[day] || createEmptyRowFields()),
        }));

        showToast(`${day}日成本已清空（销售额保留）`, 'success');
        await loadMonthData(true);
      } catch (error) {
        showToast(`清空${day}日成本失败: ${error.message}`, 'error');
      } finally {
        setSaving(false);
      }
    },
    [dbRecords, year, month, restaurant, showToast, loadMonthData],
  );

  const saveAll = useCallback(async () => {
    setSaving(true);
    let successCount = 0;
    let skipCount = 0;
    let errorCount = 0;
    const errors = [];

    try {
      if (currentStock.trim() !== '') {
        try {
          const stockResult = await saveMonthStockData(restaurant, yearMonth, currentStock);
          if (!stockResult.success) {
            showToast(`库存数据保存失败：${stockResult.message || '未知错误'}`, 'warning');
          }
        } catch {
          showToast('库存数据保存失败', 'warning');
        }
      }

      for (let day = 1; day <= daysInMonth; day += 1) {
        const fields = rowFields[day] || createEmptyRowFields();
        if (!rowHasSaveableData(fields, dbRecords[day])) continue;

        const payload = buildCostRecordPayload({
          fields,
          day,
          year,
          month,
          restaurant,
          dbRecord: dbRecords[day],
        });

        try {
          const { result } = await saveCostRecordWithFallback(payload, (dateStr) =>
            getExistingCostRecordByDate(restaurant, dateStr),
          );

          if (result.success === true || result.success === undefined) {
            successCount += 1;
          } else if (result.success === false) {
            const message = result.message || '';
            if (message.includes('已存在') || message.includes('无变化')) {
              skipCount += 1;
            } else {
              errorCount += 1;
              errors.push(`${day}日: ${message}`);
            }
          }
        } catch (error) {
          errorCount += 1;
          errors.push(`${day}日: ${error.message}`);
        }
      }

      if (successCount > 0 || skipCount > 0) {
        let message = '';
        if (successCount > 0 && skipCount > 0) {
          message = `数据处理完成！成功保存 ${successCount} 条记录，${skipCount} 条记录无需更新`;
        } else if (successCount > 0) {
          message = `数据保存成功！共保存 ${successCount} 条记录`;
        } else {
          message = `数据检查完成！${skipCount} 条记录已是最新，无需更新`;
        }
        if (currentStock.trim() !== '') message += '，库存数据已保存';
        if (errorCount > 0) message += `，${errorCount} 条记录保存失败`;
        showToast(message, successCount > 0 ? 'success' : 'info');
        await loadMonthData(false);
      } else if (errorCount > 0) {
        showToast(`保存失败：${errors.join('; ')}`, 'error');
        await loadMonthData(false);
      } else {
        showToast('没有需要保存的数据', 'info');
      }
    } finally {
      setSaving(false);
    }
  }, [
    currentStock,
    restaurant,
    yearMonth,
    daysInMonth,
    rowFields,
    dbRecords,
    year,
    month,
    showToast,
    loadMonthData,
  ]);

  const applyPaste = useCallback(
    (targetDay, startField, pasteText) => {
      const lines = pasteText.trim().split('\n').filter((line) => line.trim() !== '');
      if (lines.length === 0) return;

      let startIndex = 0;
      if (startField === 'sales') {
        showToast('销售额字段不可编辑，将从饮料成本开始粘贴', 'info');
      } else if (startField && PASTE_FIELDS.includes(startField)) {
        startIndex = PASTE_FIELDS.indexOf(startField);
      }

      const editingDayList = [];
      for (let day = targetDay; day <= daysInMonth; day += 1) {
        if (editingDays.has(day)) editingDayList.push(day);
      }

      if (editingDayList.length === 0) {
        showToast('没有找到处于编辑模式的行', 'error');
        return;
      }

      let totalPasteCount = 0;
      setRowFields((prev) => {
        const next = { ...prev };
        for (let lineIndex = 0; lineIndex < Math.min(lines.length, editingDayList.length); lineIndex += 1) {
          const values = parsePasteLine(lines[lineIndex]);
          const day = editingDayList[lineIndex];
          const row = { ...(next[day] || createEmptyRowFields()) };
          const currentStartIndex = lineIndex === 0 ? startIndex : 0;

          for (let i = 0; i < values.length && currentStartIndex + i < PASTE_FIELDS.length; i += 1) {
            const field = PASTE_FIELDS[currentStartIndex + i];
            const cleanValue = cleanPasteValue(values[i].trim());
            if (cleanValue !== null) {
              row[field] = cleanValue;
              totalPasteCount += 1;
            }
          }
          next[day] = row;
        }
        return next;
      });

      if (totalPasteCount > 0) {
        showToast(`成功粘贴 ${totalPasteCount} 个数据`, 'success');
      } else {
        showToast('未能识别有效的数据格式', 'error');
      }
    },
    [daysInMonth, editingDays, showToast],
  );

  return {
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
  };
}
