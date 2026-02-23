/**
 * Record Detail/Edit Page Customizations
 * - Hides "Print as PDF" from Account/Contact Actions dropdown
 * - Hides "Save and Continue" button from edit mode
 * - Hides "Insights" from Actions dropdown (user wants it as separate button)
 * - Cancel button now styled the same as all other action buttons
 */
(function() {
    'use strict';

    // Modules where "Print as PDF" should be hidden
    const hidePdfModules = ['accounts', 'contacts'];

    function getCurrentModule() {
        const hash = window.location.hash;
        const match = hash.match(/#\/([^\/\?]+)/);
        return match ? match[1].toLowerCase() : '';
    }

    function isRecordPage() {
        const hash = window.location.hash;
        return /^#\/[^\/]+\//.test(hash);
    }

    function applyCustomizations() {
        if (!isRecordPage()) return;

        var module = getCurrentModule();

        // 1. Hide "Save and Continue" and style Cancel button in edit mode
        var allButtons = document.querySelectorAll('button.settings-button, button.button-group-button');
        allButtons.forEach(function(btn) {
            var text = btn.textContent.trim().toLowerCase().replace(/\s+/g, ' ');
            if (text === 'save and continue') {
                btn.style.setProperty('display', 'none', 'important');
                var parent = btn.closest('scrm-button');
                if (parent) parent.style.setProperty('display', 'none', 'important');
            }
        });

        // 2. Hide items from Actions dropdown when it's open
        var dropdownMenus = document.querySelectorAll('.dropdown-menu');
        dropdownMenus.forEach(function(menu) {
            var items = menu.querySelectorAll('.dropdown-item, a.settings-button, a[ngbdropdownitem]');
            items.forEach(function(item) {
                var text = item.textContent.trim().toLowerCase();

                // Hide "Print as PDF" on account/contact pages
                if (hidePdfModules.indexOf(module) !== -1 && text === 'print as pdf') {
                    item.style.setProperty('display', 'none', 'important');
                }

                // Hide "Insights" from dropdown (it's a separate button now)
                if (text === 'insights') {
                    item.style.setProperty('display', 'none', 'important');
                }
            });
        });
    }

    function init() {
        // Run on DOM mutations (Angular renders dynamically)
        if (typeof MutationObserver !== 'undefined') {
            var observer = new MutationObserver(function() {
                applyCustomizations();
            });
            if (document.body) {
                observer.observe(document.body, { childList: true, subtree: true });
            } else {
                document.addEventListener('DOMContentLoaded', function() {
                    observer.observe(document.body, { childList: true, subtree: true });
                });
            }
        }

        // Also run on hash changes
        window.addEventListener('hashchange', function() {
            setTimeout(applyCustomizations, 1000);
            setTimeout(applyCustomizations, 3000);
        });

        applyCustomizations();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
