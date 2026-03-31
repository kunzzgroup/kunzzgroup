/**
 * smartSearch.js  —  Global Smart Search Wrapper Utilities
 *
 * Architecture: Layout (header-search) ≠ Component (smartSearchWrapper)
 *
 * Behavior (matches hire.php):
 *   - Collapsed by default (icon only, 40px wide)
 *   - Click wrapper → expand as OVERLAY (position:absolute) to the left
 *     → the 40px slot in the flex row NEVER grows → siblings unaffected
 *   - Click outside → collapse (only if input is empty)
 *   - input event → debounced callback / table filter
 *
 * Key technique: JS auto-injects .smartSearch-inner around icon+input
 *   so the CSS can use position:absolute on that inner div for overlay.
 *
 * Provides:
 *   debounce(fn, delay)
 *   initSmartSearch(inputId, callback, delay)
 *   Auto-init: optional data-table attribute for zero-config table filtering
 */

/* ── Debounce ──────────────────────────────────────────────── */
function debounce(fn, delay) {
    var timer;
    return function () {
        var args = arguments;
        var ctx = this;
        clearTimeout(timer);
        timer = setTimeout(function () { fn.apply(ctx, args); }, delay);
    };
}

/* ── Inject .smartSearch-inner wrapper (once per wrapper) ──── *
 * Wraps the icon + input inside a .smartSearch-inner div so that
 * CSS can use position:absolute on it for overlay expansion.
 * Idempotent — safe to call multiple times.
 */
function ensureInnerWrapper(wrapper) {
    if (wrapper.querySelector('.smartSearch-inner')) return; /* already done */

    var inner = document.createElement('div');
    inner.className = 'smartSearch-inner';

    /* Move all direct children into inner */
    while (wrapper.firstChild) {
        inner.appendChild(wrapper.firstChild);
    }
    wrapper.appendChild(inner);
}

/* ── Expand a single smartSearchWrapper ─────────────────────── */
function expandSmartSearch(wrapper) {
    wrapper.classList.add('expanded');
    wrapper.dataset.expanded = '1';
    var input = wrapper.querySelector('.smartSearch-input');
    if (input) {
        setTimeout(function () { input.focus(); }, 50);
    }
}

/* ── Collapse a single smartSearchWrapper ───────────────────── */
function collapseSmartSearch(wrapper) {
    var input = wrapper.querySelector('.smartSearch-input');
    /* Only collapse if input is empty */
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

    var debouncedCb = debounce(function () {
        var val = el.value;
        updateClearButton(el);
        if (typeof callback === 'function') callback(val);
    }, delay);

    el.addEventListener('input', debouncedCb);

    /* clear-button wiring */
    updateClearButton(el);

    var clearBtn = el.parentElement && el.parentElement.querySelector('.smartSearch-clear');
    if (!clearBtn && el.parentElement && el.parentElement.parentElement) {
        clearBtn = el.parentElement.parentElement.querySelector('.smartSearch-clear');
    }
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
    var wrapper = inputEl.closest ? inputEl.closest('.smartSearchWrapper')
                                  : null;
    if (!wrapper && inputEl.parentElement) {
        wrapper = inputEl.parentElement.parentElement; /* inner → wrapper */
    }
    if (!wrapper) return;
    if (inputEl.value && inputEl.value.length > 0) {
        wrapper.classList.add('has-value');
    } else {
        wrapper.classList.remove('has-value');
    }
}

/* ── Auto-init: DOMContentLoaded ─────────────────────────────
 *
 * 1. Injects .smartSearch-inner inside every .smartSearchWrapper
 * 2. Wires expand (click) / collapse (outside click) behavior
 * 3. Auto data-table filtering
 * ─────────────────────────────────────────────────────────── */
(function () {
    document.addEventListener('DOMContentLoaded', function () {

        var wrappers = document.querySelectorAll('.smartSearchWrapper');

        /* ── Step 1: inject inner wrapper for overlay positioning ── */
        wrappers.forEach(function (wrapper) {
            ensureInnerWrapper(wrapper);
        });

        /* ── Step 2: expand / collapse behavior ─────────────────── */
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

        /* ── Step 3: auto-wire data-table inputs ────────────────── */
        var autoInputs = document.querySelectorAll('.smartSearch-input[data-table]');
        autoInputs.forEach(function (input) {
            var tableId = input.getAttribute('data-table');
            var colsAttr = input.getAttribute('data-cols');
            var cols = colsAttr
                ? colsAttr.split(',').map(function (c) { return parseInt(c.trim(), 10); })
                : null;

            if (input.id) {
                initSmartSearch(input.id, function (val) {
                    filterTable(tableId, val, cols);
                }, 250);
            }
        });

        /* ── Step 4: init has-value + wire clear buttons ─────────── */
        var allInputs = document.querySelectorAll('.smartSearch-input');
        allInputs.forEach(function (input) {
            updateClearButton(input);

            var wrapper = input.closest ? input.closest('.smartSearchWrapper')
                                        : null;
            if (!wrapper) return;

            var clearBtn = wrapper.querySelector('.smartSearch-clear');
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
 * @param {string}        tableId  - element ID of the <table>
 * @param {string}        val      - search term
 * @param {number[]|null} cols     - column indexes to check (null = all)
 */
function filterTable(tableId, val, cols) {
    var table = document.getElementById(tableId);
    if (!table) return;
    var tbody = table.querySelector('tbody');
    if (!tbody) return;
    var term = val.trim().toLowerCase();
    var rows = tbody.querySelectorAll('tr');

    rows.forEach(function (row) {
        if (!term) { row.style.display = ''; return; }
        var cells = row.querySelectorAll('td');
        var match = false;
        cells.forEach(function (cell, i) {
            if (cols && cols.indexOf(i) === -1) return;
            if (cell.textContent.toLowerCase().indexOf(term) !== -1) match = true;
        });
        row.style.display = match ? '' : 'none';
    });
}
