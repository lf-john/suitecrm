/**
 * Reports List — Create Button Injection
 * Adds a "Create" button to the Reports list page header
 * since SuiteCRM 8 doesn't include one by default.
 */
(function() {
    'use strict';

    function isReportsListPage() {
        return window.location.hash === '#/reports' ||
               window.location.hash.startsWith('#/reports?') ||
               window.location.hash === '#/reports/';
    }

    function addCreateButton() {
        if (!isReportsListPage()) return;

        // Don't add if already added
        if (document.getElementById('lf-reports-create-btn')) return;

        // Find the settings menu area in the list header
        const settingsMenu = document.querySelector('scrm-list-header scrm-settings-menu .list-view-settings');
        if (!settingsMenu) return;

        // Create button element matching the existing Filter/Insights button style
        const createBtn = document.createElement('button');
        createBtn.id = 'lf-reports-create-btn';
        createBtn.className = 'button-group-button settings-button btn btn-sm';
        createBtn.textContent = 'Create';
        createBtn.style.cssText = 'margin-right: 20px; background: #125EAD; color: #ffffff; border: 1px solid #125EAD; border-radius: 6px; padding: 4px 16px; font-size: 13px; font-weight: 600; cursor: pointer; font-family: Lato, sans-serif; vertical-align: middle; display: inline-flex; align-items: center; align-self: center;';

        createBtn.addEventListener('mouseenter', function() {
            this.style.background = '#0A3D6B';
            this.style.borderColor = '#0A3D6B';
        });
        createBtn.addEventListener('mouseleave', function() {
            this.style.background = '#125EAD';
            this.style.borderColor = '#125EAD';
        });

        createBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = window.location.origin + '/legacy/index.php?module=AOR_Reports&action=EditView';
        });

        // Insert before other buttons
        settingsMenu.insertBefore(createBtn, settingsMenu.firstChild);
    }

    // Run on hash change and DOM mutations
    function init() {
        addCreateButton();

        // Watch for hash changes (SPA navigation)
        window.addEventListener('hashchange', function() {
            // Remove button if navigating away from reports list
            const btn = document.getElementById('lf-reports-create-btn');
            if (btn && !isReportsListPage()) {
                btn.remove();
            }
            // Add button if navigating to reports list
            setTimeout(addCreateButton, 1000);
            setTimeout(addCreateButton, 3000);
        });

        // MutationObserver for when Angular renders the list header
        if (typeof MutationObserver !== 'undefined') {
            const observer = new MutationObserver(function() {
                if (isReportsListPage()) {
                    addCreateButton();
                }
            });
            if (document.body) {
                observer.observe(document.body, { childList: true, subtree: true });
            } else {
                document.addEventListener('DOMContentLoaded', function() {
                    observer.observe(document.body, { childList: true, subtree: true });
                });
            }
        }
    }

    // Start
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
