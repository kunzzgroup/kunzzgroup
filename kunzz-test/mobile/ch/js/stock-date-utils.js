/**
 * 库存/货单日期工具（本地日历日，避免 toISOString UTC 偏移）
 */
(function (global) {
    'use strict';

    function getLocalDateString(date) {
        const d = date instanceof Date ? date : new Date();
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function createWorkDateManager(storageKey) {
        let currentWorkDate = null;

        function readStoredWorkDate() {
            try {
                const saved = sessionStorage.getItem(storageKey) || localStorage.getItem(storageKey);
                if (saved && /^\d{4}-\d{2}-\d{2}$/.test(saved)) {
                    return saved;
                }
            } catch (e) {
                /* ignore storage errors */
            }
            return null;
        }

        function getTodayDateString() {
            return getLocalDateString(new Date());
        }

        function getDefaultWorkDate() {
            if (currentWorkDate && /^\d{4}-\d{2}-\d{2}$/.test(currentWorkDate)) {
                return currentWorkDate;
            }
            const stored = readStoredWorkDate();
            if (stored) {
                currentWorkDate = stored;
                return stored;
            }
            return getTodayDateString();
        }

        function setDefaultWorkDate(dateStr) {
            if (!dateStr || !/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
                return;
            }
            currentWorkDate = dateStr;
            try {
                localStorage.setItem(storageKey, dateStr);
                sessionStorage.setItem(storageKey, dateStr);
            } catch (e) {
                /* ignore storage errors */
            }
        }

        function restoreWorkDateFromStorage() {
            const stored = readStoredWorkDate();
            if (stored) {
                currentWorkDate = stored;
            }
        }

        function confirmWorkDateBeforeSave(workDate) {
            if (!workDate || !/^\d{4}-\d{2}-\d{2}$/.test(workDate)) {
                return false;
            }
            const today = getTodayDateString();
            if (workDate === today) {
                return true;
            }
            return confirm(
                `货单将保存到 ${workDate}（今天为 ${today}），是否继续？`
            );
        }

        return {
            getTodayDateString,
            getDefaultWorkDate,
            setDefaultWorkDate,
            restoreWorkDateFromStorage,
            confirmWorkDateBeforeSave,
            readStoredWorkDate
        };
    }

    global.StockDateUtils = {
        getLocalDateString,
        createWorkDateManager
    };
})(typeof window !== 'undefined' ? window : this);
