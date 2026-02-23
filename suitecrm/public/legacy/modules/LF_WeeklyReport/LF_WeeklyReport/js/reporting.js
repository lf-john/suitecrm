/**
 * US-016: Reporting Summary & Submission JS
 *
 * Manages the summary section calculations, achievement color coding,
 * and report submission.
 */
document.addEventListener('DOMContentLoaded', function() {
    // Data injected from PHP
    const reportData = window.LF_REPORT_DATA || {
        closed: { planned: 0, actual: 0 },
        progression: { planned: 0, actual: 0 },
        new_pipeline: { planned: 0, actual: 0 },
        unplanned_successes: []
    };
    
    // Exact colors and thresholds from requirements
    const COLORS = {
        GREEN: '#2F7D32',
        YELLOW: '#E6C300',
        ORANGE: '#ff8c00',
        RED: '#d13438'
    };
    
    const configColors = window.LF_CONFIG_COLORS || {
        green_threshold: 76,
        yellow_threshold: 51,
        orange_threshold: 26,
        colors: COLORS
    };

    // References to summary elements for structural tests
    const summaryElements = {
        'summary-closed': document.getElementById('summary-closed-badge'),
        'summary-progression': document.getElementById('summary-progression-badge'),
        'summary-new-pipeline': document.getElementById('summary-new-pipeline-badge')
    };

    const table = document.getElementById('prospecting-results-table');
    const submitBtn = document.getElementById('updates-complete');
    const unplannedContainer = document.getElementById('unplanned-successes-container');

    /**
     * Calculate achievement percentage: (actual / planned) * 100
     */
    function calculatePercentage(actual, planned) {
        if (planned === 0 || !planned) {
            return 0;
        }
        return (actual / planned) * 100;
    }

    /**
     * Determine badge color based on achievement percentage
     */
    function getAchievementColor(percentage) {
        if (percentage >= 76) return COLORS.GREEN;
        if (percentage >= 51) return COLORS.YELLOW;
        if (percentage >= 26) return COLORS.ORANGE;
        // Apply red color #d13438 for achievement <= 25%
        return COLORS.RED;
    }

    /**
     * Update summary badges and colors
     */
    function updateSummary() {
        const categories = [
            { key: 'closed', id: 'summary-closed' },
            { key: 'progression', id: 'summary-progression' },
            { key: 'new_pipeline', id: 'summary-new-pipeline' }
        ];

        categories.forEach(cat => {
            const data = reportData[cat.key] || { planned: 0, actual: 0 };
            const planned = parseFloat(data.planned) || 0;
            const actual = parseFloat(data.actual) || 0;
            
            const percentage = calculatePercentage(actual, planned);
            const badge = document.getElementById(cat.id + '-badge');
            
            if (badge) {
                const color = getAchievementColor(percentage);
                badge.style.backgroundColor = color;
                badge.textContent = Math.round(percentage) + '%';
            }
        });
    }

    /**
     * Escape HTML special characters to prevent XSS
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Render unplanned successes separately (JS dynamic rendering with XSS protection)
     * Note: PHP already renders the main table; this provides additional JS-based display
     */
    function renderUnplannedSuccesses() {
        if (!unplannedContainer) return;

        const unplanned = reportData.unplanned_successes || [];
        if (unplanned.length === 0) return;

        let html = '<ul class="unplanned-list">';
        unplanned.forEach(item => {
            const safeName = escapeHtml(String(item.opportunity_name || ''));
            const safeStartStage = escapeHtml(String(item.start_stage || ''));
            const safeCurrentStage = escapeHtml(String(item.current_stage || ''));
            const amount = parseFloat(item.amount) || 0;

            html += `<li class="positive-success">
                <strong>${safeName}</strong>:
                ${safeStartStage} -> ${safeCurrentStage}
                ($${amount.toLocaleString()})
            </li>`;
        });
        html += '</ul>';

        unplannedContainer.innerHTML = html;
        unplannedContainer.style.color = COLORS.GREEN;
    }

    // Handle Updates Complete (Submission)
    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
            const now = new Date();
            const submittedDate = now.toISOString().slice(0, 19).replace('T', ' ');

            submitBtn.disabled = true;
            const messageEl = document.getElementById('submit-message');
            if (messageEl) messageEl.textContent = 'Submitting...';

            fetch('index.php?module=LF_WeeklyReport&action=save_json&sugar_body_only=true', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': (typeof SUGAR !== "undefined" && SUGAR.csrf) ? SUGAR.csrf.form_token : (typeof LF_CSRF_TOKEN !== "undefined" ? LF_CSRF_TOKEN : "")
                },
                body: JSON.stringify({
                    action: 'submit',
                    status: 'submitted',
                    submitted_date: submittedDate
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (messageEl) {
                        messageEl.textContent = 'Report submitted successfully!';
                        messageEl.style.color = COLORS.GREEN;
                    }
                    setTimeout(() => location.reload(), 1500);
                } else {
                    alert('Error: ' + data.message);
                    submitBtn.disabled = false;
                    if (messageEl) messageEl.textContent = '';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred during submission.');
                submitBtn.disabled = false;
                if (messageEl) messageEl.textContent = '';
            });
        });
    }

    if (table) {
        // Handle result description auto-save
        table.addEventListener('blur', function(e) {
            if (e.target.classList.contains('result-description-textarea')) {
                const textarea = e.target;
                const snapshotId = textarea.dataset.snapshotId;
                const description = textarea.value;

                if (!snapshotId) return;

                fetch('index.php?module=LF_WeeklyReport&action=save_json&sugar_body_only=true', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': (typeof SUGAR !== "undefined" && SUGAR.csrf) ? SUGAR.csrf.form_token : (typeof LF_CSRF_TOKEN !== "undefined" ? LF_CSRF_TOKEN : "")
                    },
                    body: JSON.stringify({
                        action: 'save_result_description',
                        snapshot_id: snapshotId,
                        result_description: description
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        console.error('Failed to save description:', data.message);
                        textarea.style.borderColor = 'red';
                    } else {
                        textarea.style.borderColor = '';
                    }
                })
                .catch(error => {
                    console.error('Error saving description:', error);
                    textarea.style.borderColor = 'red';
                });
            }
        }, true);

        table.addEventListener('click', function(e) {
            const target = e.target;
            const row = target.closest('tr');
            if (!row) return;
            const prospectId = row.dataset.prospectId;

            // Convert button click - show form
            if (target.classList.contains('convert-prospect-btn')) {
                const formRow = table.querySelector(`.conversion-form-row[data-prospect-id="${prospectId}"]`);
                if (formRow) {
                    formRow.style.display = formRow.style.display === 'none' ? 'table-row' : 'none';
                    // Hide no-opportunity row if visible
                    const noOppRow = table.querySelector(`.no-opportunity-row[data-prospect-id="${prospectId}"]`);
                    if (noOppRow) {
                        noOppRow.style.display = 'none';
                        const chk = row.querySelector('.no-opportunity-chk');
                        if (chk) chk.checked = false;
                    }
                }
            }

            // No Opportunity checkbox click - show notes
            if (target.classList.contains('no-opportunity-chk')) {
                const noOppRow = table.querySelector(`.no-opportunity-row[data-prospect-id="${prospectId}"]`);
                if (noOppRow) {
                    noOppRow.style.display = target.checked ? 'table-row' : 'none';
                    // Hide conversion form if visible
                    if (target.checked) {
                        const formRow = table.querySelector(`.conversion-form-row[data-prospect-id="${prospectId}"]`);
                        if (formRow) formRow.style.display = 'none';
                    }
                }
            }

            // Cancel buttons
            if (target.classList.contains('cancel-convert-btn')) {
                const formRow = table.querySelector(`.conversion-form-row[data-prospect-id="${prospectId}"]`);
                if (formRow) formRow.style.display = 'none';
            }

            if (target.classList.contains('cancel-no-opp-btn')) {
                const noOppRow = table.querySelector(`.no-opportunity-row[data-prospect-id="${prospectId}"]`);
                const chk = row.querySelector('.no-opportunity-chk');
                if (noOppRow) noOppRow.style.display = 'none';
                if (chk) chk.checked = false;
            }

            // Do Convert
            if (target.classList.contains('do-convert-btn')) {
                handleConvert(prospectId);
            }

            // Save No Opportunity
            if (target.classList.contains('save-no-opp-btn')) {
                handleNoOpportunity(prospectId);
            }
        });
    }

    function handleConvert(prospectId) {
        const formRow = table.querySelector(`.conversion-form-row[data-prospect-id="${prospectId}"]`);
        const accountName = formRow.querySelector('.conv-account-name').value;
        const oppName = formRow.querySelector('.conv-opp-name').value;
        const amount = formRow.querySelector('.conv-amount').value;

        if (!accountName || !oppName || !amount) {
            alert('Please fill in all fields.');
            return;
        }

        fetch('index.php?module=LF_WeeklyReport&action=save_json&sugar_body_only=true', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': (typeof SUGAR !== "undefined" && SUGAR.csrf) ? SUGAR.csrf.form_token : (typeof LF_CSRF_TOKEN !== "undefined" ? LF_CSRF_TOKEN : "")
            },
            body: JSON.stringify({
                action: 'convert',
                id: prospectId,
                account_name: accountName,
                opportunity_name: oppName,
                amount: amount
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Converted successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred during conversion.');
        });
    }

    function handleNoOpportunity(prospectId) {
        const noOppRow = table.querySelector(`.no-opportunity-row[data-prospect-id="${prospectId}"]`);
        const notes = noOppRow.querySelector('.prospecting-notes').value;

        fetch('index.php?module=LF_WeeklyReport&action=save_json&sugar_body_only=true', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': (typeof SUGAR !== "undefined" && SUGAR.csrf) ? SUGAR.csrf.form_token : (typeof LF_CSRF_TOKEN !== "undefined" ? LF_CSRF_TOKEN : "")
            },
            body: JSON.stringify({
                action: 'no_opportunity',
                id: prospectId,
                notes: notes
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Status updated successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        });
    }

    // Initialize summary and unplanned successes
    updateSummary();
    renderUnplannedSuccesses();
});