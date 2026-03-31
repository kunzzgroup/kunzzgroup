/**
 * Global Toast Notification System
 * Loaded via sidebar.php — available on every backend page.
 *
 * Public API:
 *   showToast(message, type, duration)
 *     - type: 'success' | 'error' | 'warning' | 'info'  (default: 'success')
 *     - duration: ms before auto-dismiss (default: 4000, 0 = never)
 *
 *   showAlert(message, type)   — legacy alias for showToast, keeps backward compatibility
 *   closeToast(id)             — close a specific toast by element id
 *
 * The container #global-toast-container is injected by sidebar.php.
 * Individual pages that already have #toast-container will still work;
 * showAlert checks both containers and prefers #global-toast-container.
 */

(function (global) {
    'use strict';

    const MAX_TOASTS = 5;
    const DEFAULT_DURATION = 4000;

    const ICONS = {
        success: 'fa-check-circle',
        error:   'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info:    'fa-info-circle',
    };

    /**
     * Ensure the global container exists (created by sidebar.php, but
     * guard in case this script loads on a page without sidebar).
     */
    function getContainer() {
        let container = document.getElementById('global-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'global-toast-container';
            document.body.appendChild(container);
        }
        return container;
    }

    /**
     * Close a toast by its element id.
     */
    function closeToast(toastId) {
        const el = document.getElementById(toastId);
        if (!el) return;
        el.classList.remove('g-toast--visible');
        el.classList.add('g-toast--hiding');
        setTimeout(function () {
            if (el.parentNode) el.parentNode.removeChild(el);
        }, 320);
    }

    /**
     * Show a toast notification.
     * @param {string} message  - Text to display
     * @param {string} [type]   - 'success' | 'error' | 'warning' | 'info'
     * @param {number} [duration] - Auto-dismiss delay in ms (0 = sticky)
     * @returns {string} Toast element id
     */
    function showToast(message, type, duration) {
        type     = (type && ICONS[type]) ? type : 'success';
        duration = (duration !== undefined) ? Number(duration) : DEFAULT_DURATION;

        var container = getContainer();

        // Evict oldest toasts if at max
        var existing = container.querySelectorAll('.g-toast');
        var toRemove = existing.length - (MAX_TOASTS - 1);
        if (toRemove > 0) {
            for (var i = 0; i < toRemove; i++) {
                if (existing[i]) closeToast(existing[i].id);
            }
        }

        var id = 'g-toast-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
        var autoDismissClass = duration > 0 ? ' g-toast--auto-dismiss' : '';
        var durationStyle = duration > 0 ? ' style="--toast-duration:' + (duration / 1000) + 's"' : '';

        var toast = document.createElement('div');
        toast.className = 'g-toast g-toast--' + type + autoDismissClass;
        toast.id = id;
        if (duration > 0) {
            toast.style.setProperty('--toast-duration', (duration / 1000) + 's');
        }

        toast.innerHTML = [
            '<i class="fas ', ICONS[type], ' g-toast__icon"></i>',
            '<div class="g-toast__body">',
                '<span class="g-toast__message">', escapeHtml(message), '</span>',
            '</div>',
            '<button class="g-toast__close" onclick="closeToast(\'', id, '\')" aria-label="关闭">',
                '<i class="fas fa-times"></i>',
            '</button>',
            '<div class="g-toast__progress"></div>',
        ].join('');

        container.appendChild(toast);

        // Trigger transition on next frame
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                toast.classList.add('g-toast--visible');
            });
        });

        // Auto-dismiss
        if (duration > 0) {
            setTimeout(function () {
                closeToast(id);
            }, duration);
        }

        return id;
    }

    /**
     * Legacy showAlert alias — replaces all per-file implementations.
     * Signature: showAlert(message, type = 'success')
     */
    function showAlert(message, type) {
        return showToast(message, type, DEFAULT_DURATION);
    }

    /** Simple HTML escaping to prevent XSS in toast messages. */
    function escapeHtml(text) {
        if (text == null) return '';
        return String(text)
            .replace(/&/g,  '&amp;')
            .replace(/</g,  '&lt;')
            .replace(/>/g,  '&gt;')
            .replace(/"/g,  '&quot;')
            .replace(/'/g,  '&#39;');
    }

    // Expose globally
    global.showToast   = showToast;
    global.showAlert   = showAlert;
    global.closeToast  = closeToast;

}(window));
