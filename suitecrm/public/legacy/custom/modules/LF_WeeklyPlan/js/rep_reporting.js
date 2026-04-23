/**
 * Rep Report JS — handles notes auto-save, prospect conversion,
 * and report submission. All AJAX routes through LF_WeeklyPlan.
 */
document.addEventListener('DOMContentLoaded', function() {
    var container = document.getElementById('lf-report-container');
    if (!container) return;

    var saveEndpoint = window.LF_SAVE_ENDPOINT || 'index.php?module=LF_WeeklyPlan&action=report_save_json';

    function getCSRFToken() {
        // Always use LF_CSRF_TOKEN (matches server-side lf_csrf_token session key)
        if (typeof LF_CSRF_TOKEN !== 'undefined') return LF_CSRF_TOKEN;
        return '';
    }

    // =============================================
    // Auto-save notes on blur (result descriptions)
    // =============================================
    container.addEventListener('blur', function(e) {
        if (e.target.classList.contains('result-description-textarea')) {
            var textarea = e.target;
            var snapshotId = textarea.dataset.snapshotId;
            if (!snapshotId) return;

            var payload = {
                    action: 'save_result_description',
                    snapshot_id: snapshotId,
                    result_description: textarea.value
                };
            console.log('[LF REPORT SAVE] Saving result description:', JSON.stringify(payload));
            fetch(saveEndpoint + '&sugar_body_only=true', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': getCSRFToken()
                },
                body: JSON.stringify(payload)
            })
            .then(function(r) {
                console.log('[LF REPORT SAVE] Response status:', r.status);
                return r.json();
            })
            .then(function(data) {
                console.log('[LF REPORT SAVE] Response:', JSON.stringify(data));
                textarea.style.borderColor = data.success ? '#2F7D32' : 'red';
                setTimeout(function() { textarea.style.borderColor = ''; }, 2000);
            })
            .catch(function(err) {
                console.error('[LF REPORT SAVE] Error:', err);
                textarea.style.borderColor = 'red';
            });
        }

        // Auto-save prospect notes
        if (e.target.classList.contains('prospect-notes-textarea')) {
            var textarea = e.target;
            var prospectId = textarea.dataset.prospectId;
            if (!prospectId) return;

            fetch(saveEndpoint + '&sugar_body_only=true', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': getCSRFToken()
                },
                body: JSON.stringify({
                    action: 'save_prospect_notes',
                    id: prospectId,
                    notes: textarea.value
                })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                textarea.style.borderColor = data.success ? '#2F7D32' : 'red';
                setTimeout(function() { textarea.style.borderColor = ''; }, 2000);
            })
            .catch(function() {
                textarea.style.borderColor = 'red';
            });
        }
    }, true);

    // =============================================
    // Click event delegation
    // =============================================
    container.addEventListener('click', function(e) {
        var target = e.target;
        var row = target.closest('tr');
        if (!row) return;
        var prospectId = row.dataset.prospectId;

        // Convert button — show inline form
        if (target.classList.contains('convert-prospect-btn')) {
            var formRow = container.querySelector('.conversion-form-row[data-prospect-id="' + prospectId + '"]');
            if (formRow) {
                formRow.style.display = formRow.style.display === 'none' ? 'table-row' : 'none';
            }
        }

        // No Opportunity checkbox
        if (target.classList.contains('no-opportunity-chk')) {
            if (target.checked) {
                // Hide conversion form if open
                var formRow = container.querySelector('.conversion-form-row[data-prospect-id="' + prospectId + '"]');
                if (formRow) formRow.style.display = 'none';

                if (confirm('Mark this prospect as "No Opportunity"?')) {
                    handleNoOpportunity(prospectId, '');
                } else {
                    target.checked = false;
                }
            }
        }

        // Cancel convert
        if (target.classList.contains('cancel-convert-btn')) {
            var formRow = target.closest('.conversion-form-row');
            if (formRow) formRow.style.display = 'none';
        }

        // Do convert
        if (target.classList.contains('do-convert-btn')) {
            handleConvert(prospectId);
        }

        // Updates Complete
        if (target.id === 'updates-complete') {
            handleSubmit();
        }
    });

    // =============================================
    // Convert prospect to opportunity
    // =============================================
    function handleConvert(prospectId) {
        var formRow = container.querySelector('.conversion-form-row[data-prospect-id="' + prospectId + '"]');
        if (!formRow) return;

        var accountName = formRow.querySelector('.conv-account-name').value.trim();
        var oppName = formRow.querySelector('.conv-opp-name').value.trim();
        var amount = formRow.querySelector('.conv-amount').value;

        if (!accountName || !oppName || !amount) {
            alert('Please fill in Account Name, Opportunity Name, and Amount.');
            return;
        }

        var btn = formRow.querySelector('.do-convert-btn');
        btn.disabled = true;
        btn.textContent = 'Creating...';

        fetch(saveEndpoint + '&sugar_body_only=true', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': getCSRFToken()
            },
            body: JSON.stringify({
                action: 'convert',
                id: prospectId,
                account_name: accountName,
                opportunity_name: oppName,
                amount: amount
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                alert('Opportunity created successfully!');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
                btn.disabled = false;
                btn.textContent = 'Create';
            }
        })
        .catch(function(err) {
            console.error('Error:', err);
            alert('An error occurred during conversion.');
            btn.disabled = false;
            btn.textContent = 'Create';
        });
    }

    // =============================================
    // Mark prospect as No Opportunity
    // =============================================
    function handleNoOpportunity(prospectId, notes) {
        fetch(saveEndpoint + '&sugar_body_only=true', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': getCSRFToken()
            },
            body: JSON.stringify({
                action: 'no_opportunity',
                id: prospectId,
                notes: notes
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(function(err) {
            console.error('Error:', err);
            alert('An error occurred.');
        });
    }

    // =============================================
    // Submit report
    // =============================================
    function handleSubmit() {
        var btn = document.getElementById('updates-complete');
        var messageEl = document.getElementById('submit-message');

        btn.disabled = true;
        if (messageEl) messageEl.textContent = 'Submitting...';

        var now = new Date();
        var submittedDate = now.toISOString().slice(0, 19).replace('T', ' ');

        fetch(saveEndpoint + '&sugar_body_only=true', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': getCSRFToken()
            },
            body: JSON.stringify({
                action: 'submit',
                status: 'submitted',
                submitted_date: submittedDate
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                if (messageEl) {
                    messageEl.textContent = 'Report submitted successfully!';
                    messageEl.style.color = '#2F7D32';
                }
                setTimeout(function() { location.reload(); }, 1500);
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
                btn.disabled = false;
                if (messageEl) messageEl.textContent = '';
            }
        })
        .catch(function(err) {
            console.error('Error:', err);
            alert('An error occurred during submission.');
            btn.disabled = false;
            if (messageEl) messageEl.textContent = '';
        });
    }
});
