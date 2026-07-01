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

/* ── DOM injection removed to use Native Box Model ── */

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

var smartSearchDocumentClickWired = false;

function wireSmartSearchWrappers(root) {
    var scope = root || document;
    var wrappers = scope.querySelectorAll('.smartSearchWrapper');

    wrappers.forEach(function (wrapper) {
        if (wrapper.dataset.smartSearchWired === '1') {
            return;
        }
        wrapper.dataset.smartSearchWired = '1';

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
            updateClearButton(input);

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
        }
    });

    if (!smartSearchDocumentClickWired) {
        smartSearchDocumentClickWired = true;
        document.addEventListener('click', function (e) {
            document.querySelectorAll('.smartSearchWrapper').forEach(function (wrapper) {
                if (wrapper.dataset.expanded && !wrapper.contains(e.target)) {
                    collapseSmartSearch(wrapper);
                }
            });
        });
    }
}

function wireSmartSearchTableFilters(root) {
    var scope = root || document;
    var autoInputs = scope.querySelectorAll('.smartSearch-input[data-table]');

    autoInputs.forEach(function (input) {
        if (input.dataset.smartSearchTableWired === '1') {
            return;
        }
        input.dataset.smartSearchTableWired = '1';

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
}

window.wireSmartSearchWrappers = wireSmartSearchWrappers;

/* ── Auto-init: DOMContentLoaded ───────────────────────────── */
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        wireSmartSearchWrappers(document);
        wireSmartSearchTableFilters(document);
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
