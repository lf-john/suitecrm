/**
 * LF Weekly Reporting Dashboard JavaScript
 *
 * Handles:
 * - View toggle (Manager/Territory)
 * - Week navigation
 * - Rep selection
 * - Data rendering from window.LF_REPORTING_DATA
 */

(function() {
    'use strict';

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        hideFooterModals();
        injectSubNav();
        initViewToggle();
        initWeekSelector();
        initRepSelector();
    });

    /**
     * Hide SuiteCRM footer modals (Reset Password dialog, etc.)
     */
    function hideFooterModals() {
        // Hide the Bootstrap modal that appears at the bottom
        const modals = document.querySelectorAll('.modal, .modal-generic, .modal-backdrop');
        modals.forEach(modal => {
            modal.style.display = 'none';
            modal.style.visibility = 'hidden';
        });

        // Also hide via CSS injection for elements added later
        const style = document.createElement('style');
        style.textContent = '.modal, .modal-generic, .modal-backdrop { display: none !important; visibility: hidden !important; }';
        document.head.appendChild(style);
    }

    /**
     * Inject sub-navigation tabs
     */
    function injectSubNav() {
        const placeholder = document.getElementById('lf-subnav-placeholder');
        if (!placeholder) return;

        const activePage = placeholder.getAttribute('data-active') || 'report';
        const isAdmin = placeholder.getAttribute('data-admin') === 'true';

        const links = [
            { id: 'planning', label: 'Rep Plan', url: 'index.php?module=LF_WeeklyPlan&action=planning' },
            { id: 'plan', label: 'Plan Dashboard', url: 'index.php?module=LF_WeeklyPlan&action=plan' },
            { id: 'report', label: 'Report Dashboard', url: 'index.php?module=LF_WeeklyPlan&action=report' }
        ];

        let html = '<nav class="lf-subnav">';

        links.forEach(link => {
            const activeClass = (link.id === activePage) ? ' active' : '';
            html += `<a href="${link.url}" class="lf-subnav-link${activeClass}">${link.label}</a>`;
        });

        if (isAdmin) {
            html += '<div class="lf-subnav-admin">';
            html += '<a href="index.php?module=LF_PRConfig&action=config" class="lf-subnav-link">Config</a>';
            html += '<a href="index.php?module=LF_RepTargets&action=manage" class="lf-subnav-link">Rep Targets</a>';
            html += '</div>';
        }

        html += '</nav>';
        placeholder.innerHTML = html;
    }

    /**
     * Initialize view toggle buttons
     */
    function initViewToggle() {
        // New view toggle buttons - support both old and new IDs
        const companyBtn = document.getElementById('company-view-btn') || document.getElementById('team-view-btn');
        const repBtn = document.getElementById('rep-view-btn');

        if (companyBtn) {
            companyBtn.addEventListener('click', function() {
                switchView('team');
            });
        }

        if (repBtn) {
            repBtn.addEventListener('click', function() {
                switchView('rep');
            });
        }
    }

    /**
     * Switch between company and rep view
     */
    function switchView(view) {
        // Update button states - support both old and new IDs
        const companyBtn = document.getElementById('company-view-btn') || document.getElementById('team-view-btn');
        const repBtn = document.getElementById('rep-view-btn');
        const repSelector = document.getElementById('rep-selector');

        if (view === 'team') {
            if (companyBtn) {
                companyBtn.classList.add('active');
                companyBtn.style.background = '#125EAD';
                companyBtn.style.color = 'white';
            }
            if (repBtn) {
                repBtn.classList.remove('active');
                repBtn.style.background = 'transparent';
                repBtn.style.color = '#605e5c';
            }
            if (repSelector) {
                repSelector.style.display = 'none';
            }
        } else {
            if (companyBtn) {
                companyBtn.classList.remove('active');
                companyBtn.style.background = 'transparent';
                companyBtn.style.color = '#605e5c';
            }
            if (repBtn) {
                repBtn.classList.add('active');
                repBtn.style.background = '#125EAD';
                repBtn.style.color = 'white';
            }
            if (repSelector) {
                repSelector.style.display = 'block';
            }
        }

        // Reload page with new view mode
        const url = new URL(window.location.href);
        url.searchParams.set('view_mode', view);
        if (view === 'team') {
            url.searchParams.delete('rep_id');
        }
        window.location.href = url.toString();
    }

    /**
     * Initialize week selector controls
     */
    function initWeekSelector() {
        // Support multiple ID formats
        const weekSelect = document.getElementById('week-select') || document.getElementById('lf-week-select');
        const backBtn = document.getElementById('week-back') || document.getElementById('lf-week-back');
        const nextBtn = document.getElementById('week-next') || document.getElementById('lf-week-next');
        const currentBtn = document.getElementById('week-current') || document.getElementById('lf-week-current');

        if (weekSelect) {
            weekSelect.addEventListener('change', function() {
                navigateToWeek(this.value);
            });
        }

        if (backBtn) {
            backBtn.addEventListener('click', function() {
                if (weekSelect && weekSelect.selectedIndex > 0) {
                    weekSelect.selectedIndex--;
                    navigateToWeek(weekSelect.value);
                }
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                if (weekSelect && weekSelect.selectedIndex < weekSelect.options.length - 1) {
                    weekSelect.selectedIndex++;
                    navigateToWeek(weekSelect.value);
                }
            });
        }

        if (currentBtn) {
            currentBtn.addEventListener('click', function() {
                // Find the current week option (marked with *)
                if (weekSelect) {
                    for (var i = 0; i < weekSelect.options.length; i++) {
                        if (weekSelect.options[i].text.indexOf('*') > -1) {
                            weekSelect.selectedIndex = i;
                            navigateToWeek(weekSelect.value);
                            break;
                        }
                    }
                }
            });
        }
    }

    /**
     * Navigate to a specific week
     */
    function navigateToWeek(weekStart) {
        const url = new URL(window.location.href);
        url.searchParams.set('week_start', weekStart);
        window.location.href = url.toString();
    }

    /**
     * Initialize rep selector
     */
    function initRepSelector() {
        // Old rep selector
        const repSelect = document.getElementById('lf-rep-selector');
        if (repSelect) {
            repSelect.addEventListener('change', function() {
                const url = new URL(window.location.href);
                if (this.value) {
                    url.searchParams.set('rep_id', this.value);
                } else {
                    url.searchParams.delete('rep_id');
                }
                window.location.href = url.toString();
            });
        }

        // New rep selector (matching dashboard)
        const newRepSelect = document.getElementById('rep-selector');
        if (newRepSelect) {
            newRepSelect.addEventListener('change', function() {
                updateRepNameLabel();
                const url = new URL(window.location.href);
                if (this.value) {
                    url.searchParams.set('rep_id', this.value);
                    url.searchParams.set('view_mode', 'rep');
                } else {
                    url.searchParams.delete('rep_id');
                }
                window.location.href = url.toString();
            });
        }
    }

    /**
     * Update the rep name label shown to the left of the dropdown
     */
    function updateRepNameLabel() {
        const repNameLabel = document.getElementById('rep-name-label');
        const repSelector = document.getElementById('rep-selector');

        if (!repNameLabel) return;

        if (repSelector && repSelector.value) {
            const selectedOption = repSelector.options[repSelector.selectedIndex];
            if (selectedOption && selectedOption.text && selectedOption.value) {
                repNameLabel.textContent = selectedOption.text + ':';
            } else {
                repNameLabel.textContent = '';
            }
        } else {
            repNameLabel.textContent = '';
        }
    }

    /**
     * Format currency value
     */
    function formatCurrency(value) {
        return '$' + parseFloat(value || 0).toLocaleString('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }

    /**
     * Get achievement tier class based on percentage
     */
    function getAchievementClass(rate, tiers) {
        if (rate >= tiers.green) return 'tier-green';
        if (rate >= tiers.yellow) return 'tier-yellow';
        if (rate >= tiers.orange) return 'tier-orange';
        return 'tier-red';
    }

})();
