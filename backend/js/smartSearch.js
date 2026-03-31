/**
 * smartSearch.js  —  Global Smart Search Wrapper Utilities
 *
 * Architecture: Layout (header-search) ≠ Component (smartSearchWrapper)
 *
 * Behavior (matches hire.php):
 *   - Collapsed by default (icon only, 40px wide)
 *   - Click wrapper → expand + focus input
 *   - Click outside → collapse (only if input is empty)
 *   - input event → debounced callback / table filter
 *
 * Provides:
 *   debounce(fn, delay)
 *   initSmartSearch(inputId, callback, delay)
 *   Auto-init: optional data-table attribute for zero-config table filtering
 */

/* ── Debounce ──────────────────────────────────────────────── */
function debounce(fn, delay) {
    let timer;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}

/* ── Expand / Collapse a single smartSearchWrapper ─────────── */
function expandSmartSearch(wrapper) {
    wrapper.classList.add('expanded');
    wrapper.dataset.expanded = '1';
    var input = wrapper.querySelector('.smartSearch-input');
    if (input) setTimeout(() => input.focus(), 150);
}

function collapseSmartSearch(wrapper) {
    var input = wrapper.querySelector('.smartSearch-input');
    // Only collapse if input is empty (keep expanded when user typed something)
    if (input && input.value.trim() !== '') return;
    wrapper.classList.remove('expanded');
    wrapper.dataset.expanded = '';
}

/* ── Core: wire a search input by ID ─────────────────────── */
/**
 * Initialise a smart search input with debounced callback.
 *
 * @param {string}   inputId   - ID of the .smartSearch-input element
 * @param {Function} callback  - called with (value:string) on each debounced input
 * @param {number}   [delay]   - debounce ms, default 300
 * @returns {{ destroy: Function }}
 */
function initSmartSearch(inputId, callback, delay) {
    delay = (delay === undefined) ? 300 : delay;

    var el = document.getElementById(inputId);
    if (!el) {
        console.warn('[smartSearch] Element not found: #' + inputId);
        return { destroy: function () {} };
    }

    var debouncedCb = debounce(function (e) {
        var val = e.target.value;
        updateClearButton(el);
        if (typeof callback === 'function') callback(val);
    }, delay);

    el.addEventListener('input', debouncedCb);

    /* clear-button wiring */
    updateClearButton(el);

    var clearBtn = el.parentElement && el.parentElement.querySelector('.smartSearch-clear');
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            el.value = '';
            updateClearButton(el);
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.focus();
        });
    }

    return {
        destroy: function () {
            el.removeEventListener('input', debouncedCb);
        }
    };
}

/* ── Helper: toggle has-value class for clear button ───────── */
function updateClearButton(inputEl) {
    var wrapper = inputEl.parentElement;
    if (!wrapper) return;
    if (inputEl.value && inputEl.value.length > 0) {
        wrapper.classList.add('has-value');
    } else {
        wrapper.classList.remove('has-value');
    }
}

/* ── Auto-init: DOMContentLoaded wires all wrappers ─────────
 *
 * 1. Expand/collapse behavior (click-to-expand, outside-click-to-collapse)
 * 2. data-table mode for zero-config table filtering
 *
 * Usage in PHP (zero-config table filter):
 *   <input class="smartSearch-input"
 *          data-table="my-table-id"
 *          data-cols="0,1,2"          (column indexes to search, optional)
 *          placeholder="搜索...">
 * ─────────────────────────────────────────────────────────── */
(function () {
    document.addEventListener('DOMContentLoaded', function () {

        /* ── 1. Expand/collapse behavior for every .smartSearchWrapper ── */
        var wrappers = document.querySelectorAll('.smartSearchWrapper');

        wrappers.forEach(function (wrapper) {
            var input = wrapper.querySelector('.smartSearch-input');

            /* Click on wrapper → expand */
            wrapper.addEventListener('click', function (e) {
                if (!wrapper.dataset.expanded) {
                    e.stopPropagation();
                    expandSmartSearch(wrapper);
                }
            });

            if (input) {
                /* input event: update clear button state */
                input.addEventListener('input', function () {
                    updateClearButton(input);
                });
            }
        });

        /* Click outside any expanded wrapper → collapse it */
        document.addEventListener('click', function (e) {
            wrappers.forEach(function (wrapper) {
                if (wrapper.dataset.expanded && !wrapper.contains(e.target)) {
                    collapseSmartSearch(wrapper);
                }
            });
        });

        /* ── 2. Auto-wire data-table inputs ───────────────────────────── */
        var autoInputs = document.querySelectorAll('.smartSearch-input[data-table]');
        autoInputs.forEach(function (input) {
            var tableId = input.getAttribute('data-table');
            var colsAttr = input.getAttribute('data-cols');
            var cols = colsAttr
                ? colsAttr.split(',').map(function (c) { return parseInt(c.trim(), 10); })
                : null;

            initSmartSearch(input.id, function (val) {
                filterTable(tableId, val, cols);
            }, 250);
        });

        /* ── 3. Init has-value state + clear buttons for all wrappers ─── */
        var allInputs = document.querySelectorAll('.smartSearch-input');
        allInputs.forEach(function (input) {
            updateClearButton(input);

            var clearBtn = input.parentElement && input.parentElement.querySelector('.smartSearch-clear');
            if (clearBtn && !clearBtn._smartSearchWired) {
                clearBtn._smartSearchWired = true;
                clearBtn.addEventListener('click', function () {
                    input.value = '';
                    updateClearButton(input);
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.focus();
                });
            }
        });
    });
})();

/* ── Table filter helper ─────────────────────────────────── */
/**
 * Filter <tbody> rows of a table by a search string.
 *
 * @param {string}   tableId  - element ID of the <table>
 * @param {string}   val      - search term
 * @param {number[]|null} cols - column indexes to check (null = all)
 */
function filterTable(tableId, val, cols) {
    var table = document.getElementById(tableId);
    if (!table) return;
    var tbody = table.querySelector('tbody');
    if (!tbody) return;
    var term = val.trim().toLowerCase();
    var rows = tbody.querySelectorAll('tr');

    rows.forEach(function (row) {
        if (!term) {
            row.style.display = '';
            return;
        }
        var cells = row.querySelectorAll('td');
        var match = false;
        cells.forEach(function (cell, i) {
            if (cols && cols.indexOf(i) === -1) return;
            if (cell.textContent.toLowerCase().includes(term)) match = true;
        });
        row.style.display = match ? '' : 'none';
    });
}
