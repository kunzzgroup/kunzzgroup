import { useCallback, useEffect, useMemo, useState } from 'react';

import {
  deleteKpiRecord,
  fetchKpiEditConfig,
  fetchKpiEditMonthList,
  refreshSession,
  saveKpiRecord,
} from '../api/kpiEditApi.js';
import { getMonthDateRange, PASTE_FIELDS } from '../config/kpiEditConfig.js';
import {
  buildRecordPayload,
  cleanPasteValue,
  computeMonthStats,
  createEmptyRowFields,
  parsePasteLine,
  recordToRowFields,
  rowHasSaveableData,
} from '../utils/kpiEditCalculations.js';

function mapRecordsByDay(records) {
  const byDay = {};
  records.forEach((item) => {
    const day = parseInt(item.date.split('-')[2], 10);
    byDay[day] = item;
  });
  return byDay;
}

function buildRowFieldsMap(daysInMonth, dbRecords) {
  const map = {};
  for (let day = 1; day <= daysInMonth; day += 1) {
    map[day] = recordToRowFields(dbRecords[day] || {});
  }
  return map;
}

export function useKpiEdit() {
  const now = new Date();
  const [config, setConfig] = useState(null);
  const [restaurant, setRestaurant] = useState(null);
  const [year, setYear] = useState(now.getFullYear());
  const [month, setMonth] = useState(now.getMonth() + 1);
  const [dbRecords, setDbRecords] = useState({});
  const [rowFields, setRowFields] = useState({});
  const [editingDays, setEditingDays] = useState(new Set());
  const [preservedRows, setPreservedRows] = useState({});
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [toast, setToast] = useState(null);

  const { startDate, endDate, daysInMonth } = useMemo(
    () => getMonthDateRange(year, month),
    [year, month],
  );

  const showToast = useCallback((message, type = 'success') => {
    setToast({ message, type });
    window.clearTimeout(showToast._timer);
    showToast._timer = window.setTimeout(() => setToast(null), 4000);
  }, []);

  useEffect(() => {
    let active = true;
    (async () => {
      try {
        const nextConfig = await fetchKpiEditConfig();
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
        // ignore background refresh errors
      }
    }, 5 * 60 * 1000);
    return () => window.clearInterval(interval);
  }, [showToast]);

  const loadMonthData = useCallback(async () => {
    if (!restaurant) return;
    setLoading(true);
    setEditingDays(new Set());
    setPreservedRows({});
    try {
      const records = await fetchKpiEditMonthList(restaurant, startDate, endDate);
      const byDay = mapRecordsByDay(records);
      setDbRecords(byDay);
      setRowFields(buildRowFieldsMap(daysInMonth, byDay));
    } catch (error) {
      if (error.code === 'SESSION_EXPIRED') {
        showToast('会话已过期，请重新登录', 'error');
      } else {
        showToast(error.message || '加载数据失败', 'error');
      }
      setDbRecords({});
      setRowFields(buildRowFieldsMap(daysInMonth, {}));
    } finally {
      setLoading(false);
    }
  }, [restaurant, startDate, endDate, daysInMonth, showToast]);

  useEffect(() => {
    if (!restaurant) return;
    loadMonthData();
  }, [restaurant, year, month, loadMonthData]);

  const monthStats = useMemo(
    () => computeMonthStats(rowFields, daysInMonth),
    [rowFields, daysInMonth],
  );

  const updateField = useCallback((day, field, value) => {
    setRowFields((prev) => ({
      ...prev,
      [day]: {
        ...(prev[day] || createEmptyRowFields()),
        [field]: value,
      },
    }));
  }, []);

  const startEdit = useCallback((day) => {
    setPreservedRows((prev) => ({
      ...prev,
      [day]: { ...(rowFields[day] || createEmptyRowFields()) },
    }));
    setEditingDays((prev) => new Set(prev).add(day));
  }, [rowFields]);

  const cancelEdit = useCallback((day) => {
    setRowFields((prev) => ({
      ...prev,
      [day]: preservedRows[day] || recordToRowFields(dbRecords[day] || {}),
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
  }, [preservedRows, dbRecords]);

  const saveRow = useCallback(
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
        const payload = buildRecordPayload({
          fields,
          day,
          year,
          month,
          restaurant,
          dbRecord: dbRecords[day],
        });
        const result = await saveKpiRecord(payload, Boolean(dbRecords[day]?.id));
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
        await loadMonthData();
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
      if (!window.confirm(`确定要清空${day}日的所有数据吗？此操作不可恢复！`)) return;

      setSaving(true);
      try {
        if (dbRecords[day]?.id) {
          const result = await deleteKpiRecord(dbRecords[day].id, restaurant);
          if (!result.success) throw new Error(result.message || '删除失败');
          showToast(`${day}日数据已从数据库删除`, 'success');
        } else {
          showToast(`${day}日数据已清空`, 'info');
        }
        await loadMonthData();
      } catch (error) {
        showToast(`删除${day}日数据失败: ${error.message}`, 'error');
      } finally {
        setSaving(false);
      }
    },
    [dbRecords, restaurant, showToast, loadMonthData],
  );

  const saveAll = useCallback(async () => {
    setSaving(true);
    let successCount = 0;
    let skipCount = 0;
    let errorCount = 0;
    const errors = [];

    try {
      for (let day = 1; day <= daysInMonth; day += 1) {
        const fields = rowFields[day] || createEmptyRowFields();
        if (!rowHasSaveableData(fields, dbRecords[day])) continue;

        const payload = buildRecordPayload({
          fields,
          day,
          year,
          month,
          restaurant,
          dbRecord: dbRecords[day],
        });

        try {
          const result = await saveKpiRecord(payload, Boolean(dbRecords[day]?.id));
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
        if (errorCount > 0) message += `，${errorCount} 条记录保存失败`;
        showToast(message, successCount > 0 ? 'success' : 'info');
        await loadMonthData();
      } else if (errorCount > 0) {
        showToast(`保存失败：${errors.join('; ')}`, 'error');
      } else {
        showToast('没有需要保存的数据', 'info');
      }
    } finally {
      setSaving(false);
    }
  }, [rowFields, dbRecords, daysInMonth, year, month, restaurant, showToast, loadMonthData]);

  const applyPaste = useCallback(
    (targetDay, startField, pasteText) => {
      const lines = pasteText.trim().split('\n').filter((line) => line.trim() !== '');
      if (lines.length === 0) return 0;

      const startIndex = startField && PASTE_FIELDS.includes(startField) ? PASTE_FIELDS.indexOf(startField) : 0;
      const editingDayList = [];
      for (let day = targetDay; day <= daysInMonth; day += 1) {
        if (editingDays.has(day)) editingDayList.push(day);
      }

      if (editingDayList.length === 0) {
        showToast('没有找到处于编辑模式的行', 'error');
        return 0;
      }

      if (lines.length > editingDayList.length) {
        showToast(`数据有 ${lines.length} 行，但只有 ${editingDayList.length} 行在编辑模式`, 'info');
      }

      let totalPasteCount = 0;
      setRowFields((prev) => {
        const next = { ...prev };
        for (let lineIndex = 0; lineIndex < Math.min(lines.length, editingDayList.length); lineIndex += 1) {
          const values = parsePasteLine(lines[lineIndex]);
          const day = editingDayList[lineIndex];
          const currentStartIndex = lineIndex === 0 ? startIndex : 0;
          const row = { ...(next[day] || createEmptyRowFields()) };

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
      return totalPasteCount;
    },
    [daysInMonth, editingDays, showToast],
  );

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
    dbRecords,
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
    showToast,
  };
}
