/**
 * smartSearch.js  —  Global Smart Search Wrapper Utilities
 *
 * Method 1: Absolute Overlay (As requested)
 * Wraps content in .smartSearch-inner so it can float over
 * without squishing adjacent buttons.
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

/* ── DOM injection to support pure CSS Absolute positioning without messy HTML ── */
function ensureInnerWrapper(wrapper) {
    if (wrapper.querySelector('.smartSearch-inner')) return;

    var inner = document.createElement('div');
    inner.className = 'smartSearch-inner';

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
    if (input && input.value.trim() !== '') return;
    wrapper.classList.remove('expanded');
    wrapper.dataset.expanded = '';
}

/* ── Core: wire a search input by ID ─────────────────────── */
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

    updateClearButton(el);

    var wrapper = el.closest ? el.closest('.smartSearchWrapper') : el.parentElement.parentElement;
    if (wrapper) {
        var clearBtn = wrapper.querySelector('.smartSearch-clear');
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                el.value = '';
                updateClearButton(el);
                el.dispatchEvent(new Event('input', { bubbles: true }));
                el.focus();
            });
        }
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
                                  : inputEl.parentElement.parentElement;
    if (!wrapper) return;
    if (inputEl.value && inputEl.value.length > 0) {
        wrapper.classList.add('has-value');
    } else {
        wrapper.classList.remove('has-value');
    }
}

/* ── Auto-init: DOMContentLoaded ───────────────────────────── */
(function () {
    document.addEventListener('DOMContentLoaded', function () {

        var wrappers = document.querySelectorAll('.smartSearchWrapper');

        /* 1. inject inner wrapper for overlay positioning */
        wrappers.forEach(function (wrapper) {
            ensureInnerWrapper(wrapper);
        });

        /* 2. expand / collapse behavior */
        wrappers.forEach(function (wrapper) {
            var input = wrapper.querySelector('.smartSearch-input');

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

        /* 3. auto-wire data-table inputs */
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

        /* 4. wire unwired clear buttons */
        var allInputs = document.querySelectorAll('.smartSearch-input');
        allInputs.forEach(function (input) {
            updateClearButton(input);

            var wrapper = input.closest ? input.closest('.smartSearchWrapper')
                                        : input.parentElement.parentElement;
            if (!wrapper) return;

            var clearBtn = wrapper.querySelector('.smartSearch-clear');
            if (clearBtn && !clearBtn._smartSearchWired) {
                clearBtn._smartSearchWired = true;
                clearBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
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
