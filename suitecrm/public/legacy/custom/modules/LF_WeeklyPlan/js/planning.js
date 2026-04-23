(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        // Hide SuiteCRM footer modals (Reset Password dialog, etc.)
        hideFooterModals();

        // Inject sub-navigation
        injectSubNav();

        const container = document.getElementById('lf-planning-container');
        if (!container) return;

        // Event Delegation
        container.addEventListener('change', function(e) {
            const target = e.target;
            if (target.matches('.projected-stage-select')) {
                updateCategoryForRow(target.closest('tr'));
                updateAll();
            } else if (target.matches('.dev-projected-stage-select') ||
                target.matches('.prospect-revenue') ||
                target.matches('.prospect-profit') ||
                target.matches('.at-risk-checkbox')) {
                updateAll();
            }
        });

        container.addEventListener('click', function(e) {
            const target = e.target;
            if (target.id === 'add-prospect-row') {
                addProspectRow();
            } else if (target.matches('.remove-prospect-row')) {
                removeProspectRow(target);
            } else if (target.id === 'save-plan') {
                savePlan('in_progress');
            } else if (target.id === 'updates-complete') {
                savePlan('submitted');
            }
        });

        function getStageProb(stageName) {
            if (!stageName) return 0;
            const probs = window.LF_STAGE_PROBS || window.stageProbabilities || {};
            const prob = probs[stageName];
            if (prob === undefined || prob === null || isNaN(prob)) {
                return 0;
            }
            return parseFloat(prob) || 0;
        }

        function updateCategoryForRow(row) {
            if (!row) return;

            const stageCell = row.querySelector('.current-stage');
            const projSelect = row.querySelector('.projected-stage-select');
            const categoryCell = row.querySelector('.category-cell');
            const categoryDisplay = row.querySelector('.category-display');
            const categoryInput = row.querySelector('.category-value');

            if (!stageCell || !projSelect || !categoryCell) return;

            const currentProb = parseInt(stageCell.getAttribute('data-prob')) || 0;
            const projectedStage = projSelect.value;
            const projectedProb = getStageProb(projectedStage);

            let category = '--';
            let categoryValue = '';
            let categoryClass = '';

            if (projectedStage) {
                if (projectedProb >= 100) {
                    category = 'Closing';
                    categoryValue = 'closing';
                    categoryClass = 'category-closing';
                } else if (currentProb >= 10 && projectedProb > currentProb) {
                    category = 'Progression';
                    categoryValue = 'progression';
                    categoryClass = 'category-progression';
                } else if (currentProb <= 1 && projectedProb > currentProb) {
                    category = 'New';
                    categoryValue = 'new';
                    categoryClass = 'category-new';
                }
            }

            if (categoryDisplay) {
                categoryDisplay.textContent = category;
            }
            if (categoryInput) {
                categoryInput.value = categoryValue;
            }

            categoryCell.className = 'category-cell ' + categoryClass;
            categoryCell.setAttribute('data-category', categoryValue);
        }

        function updateAll() {
            var planStatus = container.getAttribute('data-plan-status');
            if (planStatus === 'submitted') return;

            updateProgression();
            updateTotals();
            updateHealthSummary();
        }

        function updateProgression() {
            const rows = container.querySelectorAll('#pipeline-table tbody tr');
            rows.forEach(row => {
                const profitCell = row.querySelector('.profit');
                const stageCell = row.querySelector('.current-stage');
                const projSelect = row.querySelector('.projected-stage-select');

                if (!profitCell || !stageCell || !projSelect) return;

                const profit = parseFloat(profitCell.getAttribute('data-profit')) || 0;
                const currentStage = stageCell.getAttribute('data-stage');
                const projectedStage = projSelect.value;

                const currentProb = getStageProb(currentStage);
                const projectedProb = getStageProb(projectedStage);

                let progression = 0;
                if (projectedStage) {
                    progression = profit * (projectedProb - currentProb) / 100;
                }

                const progCell = row.querySelector('.pipeline-progression');
                if (progCell) {
                    progCell.textContent = Math.round(progression).toLocaleString();
                    progCell.setAttribute('data-value', progression);
                }
            });
        }

        function updateTotals() {
            let totalClosing = 0;
            let totalAtRisk = 0;
            let totalProgression = 0;
            let totalNewPipeline = 0;

            const pipelineRows = container.querySelectorAll('#pipeline-table tbody tr');
            pipelineRows.forEach(row => {
                const profitCell = row.querySelector('.profit');
                const categoryInput = row.querySelector('.category-value');
                const projSelect = row.querySelector('.projected-stage-select');
                const atRiskCheckbox = row.querySelector('.at-risk-checkbox');

                if (!profitCell || !projSelect) return;

                const profit = parseFloat(profitCell.getAttribute('data-profit')) || 0;
                const category = categoryInput ? categoryInput.value : '';
                const projectedStage = projSelect.value;
                const projectedProb = getStageProb(projectedStage);
                const isAtRisk = atRiskCheckbox && atRiskCheckbox.checked;

                if (isAtRisk) {
                    totalAtRisk += profit;
                }

                if (category === 'closing') {
                    totalClosing += profit;
                }

                if (category === 'closing' || category === 'progression') {
                    const currentProb = parseInt(row.querySelector('.current-stage')?.getAttribute('data-prob')) || 0;
                    const progression = profit * (projectedProb - currentProb) / 100;
                    totalProgression += progression;
                }
            });

            const devPipelineRows = container.querySelectorAll('#developing-pipeline-table tbody tr');
            devPipelineRows.forEach(row => {
                const profitCell = row.querySelector('.dev-profit');
                const projSelect = row.querySelector('.dev-projected-stage-select');

                if (!profitCell || !projSelect) return;

                const profit = parseFloat(profitCell.getAttribute('data-profit')) || 0;
                const projectedStage = projSelect.value;

                if (projectedStage) {
                    totalNewPipeline += profit;
                }
            });

            const prospectRows = container.querySelectorAll('#prospecting-table tbody tr');
            prospectRows.forEach(row => {
                const profitInput = row.querySelector('.prospect-profit');
                if (profitInput) {
                    totalNewPipeline += parseFloat(profitInput.value) || 0;
                }
            });

            updateTotalElement('total-closing', totalClosing, 'closing');
            updateTotalElement('total-at-risk', totalAtRisk, 'at_risk');
            updateTotalElement('total-progression', totalProgression, 'progression');
            updateTotalElement('total-new-pipeline', totalNewPipeline, 'new_pipeline');
        }

        function updateTotalElement(id, value, targetKey) {
            const el = document.getElementById(id);
            if (el) {
                el.textContent = Math.round(value).toLocaleString();
                el.setAttribute('data-value', value);

                const targets = window.LF_WEEKLY_TARGETS || {};
                const target = targets[targetKey] || 0;
                const box = document.getElementById(id + '-box');
                if (box) {
                    if (targetKey === 'at_risk') return;

                    if (value >= target) {
                        box.style.color = '#2F7D32';
                        addClass(box, 'on-target');
                        removeClass(box, 'off-target');
                    } else {
                        box.style.color = '#d13438';
                        addClass(box, 'off-target');
                        removeClass(box, 'on-target');
                    }
                }
            }
        }

        function updateHealthSummary() {
            const healthSummary = document.getElementById('health-summary');
            if (!healthSummary) return;

            const healthData = window.LF_HEALTH_DATA || {};
            const closedYtd = parseFloat(healthData.closed_ytd) || 0;
            const annualQuota = parseFloat(healthData.annual_quota) || 0;
            const coverageMultiplier = parseFloat(healthData.coverage_multiplier) || 4;

            let currentPipelineTotal = parseFloat(healthData.current_pipeline);
            if (isNaN(currentPipelineTotal)) {
                currentPipelineTotal = 0;
                const pipelineRows = container.querySelectorAll('#pipeline-table tbody tr');
                pipelineRows.forEach(row => {
                    const amountCell = row.querySelector('.amount');
                    if (amountCell) {
                        currentPipelineTotal += parseFloat(amountCell.getAttribute('data-amount')) || 0;
                    }
                });
                const devPipelineRows = container.querySelectorAll('#developing-pipeline-table tbody tr');
                devPipelineRows.forEach(row => {
                    const amountCell = row.querySelector('.dev-amount');
                    if (amountCell) {
                        currentPipelineTotal += parseFloat(amountCell.getAttribute('data-amount')) || 0;
                    }
                });
            }

            const remainingQuota = Math.max(0, annualQuota - closedYtd);
            const pipelineTarget = remainingQuota * coverageMultiplier;
            const gapToTarget = pipelineTarget - currentPipelineTotal;
            const coverageRatio = remainingQuota > 0 ? currentPipelineTotal / remainingQuota : 0;

            updateHealthElement('health-remaining-quota', remainingQuota, true);
            updateHealthElement('health-pipeline-target', pipelineTarget, true);
            updateHealthElement('health-current-pipeline', currentPipelineTotal, true);
            updateHealthElement('health-gap-to-target', gapToTarget, true);
            updateHealthElement('health-coverage-ratio', coverageRatio, false, true);

            const gapEl = document.getElementById('health-gap-to-target');
            if (gapEl) {
                if (currentPipelineTotal < pipelineTarget) {
                    gapEl.style.color = '#d13438';
                    addClass(gapEl, 'gap-negative');
                } else {
                    gapEl.style.color = '';
                    removeClass(gapEl, 'gap-negative');
                }
            }
        }

        function updateHealthElement(id, value, isCurrency, isRatio) {
            const el = document.getElementById(id);
            if (el) {
                el.setAttribute('data-value', value);
                if (isCurrency) {
                    el.textContent = '$' + Math.round(value).toLocaleString();
                } else if (isRatio) {
                    el.textContent = value.toFixed(2) + 'x';
                } else {
                    el.textContent = value.toLocaleString();
                }
            }
        }

        function addClass(el, className) {
            if (el.classList) {
                el.classList.add(className);
            } else {
                const current = el.className || '';
                const classes = current.split(/\s+/);
                if (classes.indexOf(className) === -1) {
                    el.className = (current + ' ' + className).trim();
                }
            }
        }

        function removeClass(el, className) {
            if (el.classList) {
                el.classList.remove(className);
            } else {
                const current = el.className || '';
                el.className = current.split(/\s+/).filter(c => c !== className).join(' ');
            }
        }

        function addProspectRow() {
            const table = document.getElementById('prospecting-table');
            if (!table) return;
            const tbody = table.querySelector('tbody');
            if (!tbody) return;

            const rows = tbody.querySelectorAll('tr.prospecting-row');
            const index = tbody.children.length;
            let newRow;

            if (rows.length > 0) {
                const template = rows[0];
                newRow = template.cloneNode(true);
                newRow.querySelectorAll('input').forEach(input => {
                    input.value = '';
                    const name = input.getAttribute('name');
                    if (name) {
                        input.setAttribute('name', name.replace(/\[\d+\]/, '[' + index + ']'));
                    }
                });
                newRow.querySelectorAll('select').forEach(select => {
                    select.selectedIndex = 0;
                    const name = select.getAttribute('name');
                    if (name) {
                        select.setAttribute('name', name.replace(/\[\d+\]/, '[' + index + ']'));
                    }
                });
                newRow.setAttribute('data-prospect-index', index);
            } else {
                newRow = document.createElement('tr');
                newRow.className = 'prospecting-row';
                newRow.setAttribute('data-prospect-index', index);

                const td1 = document.createElement('td');
                const select1 = document.createElement('select');
                select1.name = 'prospect_source[' + index + ']';
                select1.className = 'prospect-source';
                const opt0 = document.createElement('option');
                opt0.value = '';
                opt0.textContent = '-- Select --';
                select1.appendChild(opt0);
                const sourceTypes = window.LF_SOURCE_TYPES || [];
                sourceTypes.forEach(function(st) {
                    const opt = document.createElement('option');
                    opt.value = st;
                    opt.textContent = st;
                    select1.appendChild(opt);
                });
                td1.appendChild(select1);
                newRow.appendChild(td1);

                const td2 = document.createElement('td');
                const select2 = document.createElement('select');
                select2.name = 'prospect_day[' + index + ']';
                select2.className = 'prospect-day';
                const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                days.forEach(function(d) {
                    const opt = document.createElement('option');
                    opt.value = d;
                    opt.textContent = d.charAt(0).toUpperCase() + d.slice(1);
                    select2.appendChild(opt);
                });
                td2.appendChild(select2);
                newRow.appendChild(td2);

                const td3 = document.createElement('td');
                const input3 = document.createElement('input');
                input3.type = 'number';
                input3.name = 'prospect_revenue[' + index + ']';
                input3.className = 'prospect-revenue';
                td3.appendChild(input3);
                newRow.appendChild(td3);

                const td3b = document.createElement('td');
                const input3b = document.createElement('input');
                input3b.type = 'number';
                input3b.name = 'prospect_profit[' + index + ']';
                input3b.className = 'prospect-profit';
                td3b.appendChild(input3b);
                newRow.appendChild(td3b);

                const td4 = document.createElement('td');
                const input4 = document.createElement('input');
                input4.type = 'text';
                input4.name = 'prospect_description[' + index + ']';
                td4.appendChild(input4);
                newRow.appendChild(td4);

                const td5 = document.createElement('td');
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'remove-prospect-row';
                btn.textContent = 'Remove';
                td5.appendChild(btn);
                newRow.appendChild(td5);
            }
            tbody.appendChild(newRow);
            updateTotals();
        }

        function removeProspectRow(button) {
            const row = button.closest('tr');
            if (row) {
                row.remove();
                updateTotals();
            }
        }

        function collectFormData() {
            const planId = container.getAttribute('data-plan-id') || window.LF_PLAN_ID;
            const opItems = [];
            const prospectItems = [];

            // Pipeline items
            const pipelineRows = container.querySelectorAll('#pipeline-table tbody tr');
            pipelineRows.forEach(row => {
                const oppId = row.getAttribute('data-opportunity-id');
                const projSelect = row.querySelector('.projected-stage-select');
                const categoryInput = row.querySelector('.category-value');
                const atRiskCheckbox = row.querySelector('.at-risk-checkbox');
                const daySelect = row.querySelector('.day-select');
                const planInput = row.querySelector('input[name^="plan"]');

                if (oppId) {
                    opItems.push({
                        opportunity_id: oppId,
                        projected_stage: projSelect ? projSelect.value : '',
                        item_type: categoryInput ? categoryInput.value : '',
                        is_at_risk: atRiskCheckbox ? (atRiskCheckbox.checked ? 1 : 0) : 0,
                        planned_day: daySelect ? daySelect.value : '',
                        plan_description: planInput ? planInput.value : ''
                    });
                }
            });

            // Developing items (merged into op_items with item_type='developing')
            const devRows = container.querySelectorAll('#developing-pipeline-table tbody tr');
            devRows.forEach(row => {
                const oppId = row.getAttribute('data-opportunity-id');
                const projSelect = row.querySelector('.dev-projected-stage-select');
                const daySelect = row.querySelector('.dev-day-select');
                const planInput = row.querySelector('input[name^="dev_plan"]');

                if (oppId) {
                    opItems.push({
                        opportunity_id: oppId,
                        projected_stage: projSelect ? projSelect.value : '',
                        item_type: 'developing',
                        planned_day: daySelect ? daySelect.value : '',
                        plan_description: planInput ? planInput.value : ''
                    });
                }
            });

            // Prospecting items
            const prospectRows = container.querySelectorAll('#prospecting-table tbody tr');
            prospectRows.forEach(row => {
                const sourceSelect = row.querySelector('.prospect-source');
                const daySelect = row.querySelector('.prospect-day');
                const revenueInput = row.querySelector('.prospect-revenue');
                const profitInput = row.querySelector('.prospect-profit');
                const descInput = row.querySelector('input[name^="prospect_description"]');

                prospectItems.push({
                    id: row.getAttribute('data-prospect-id') || '',
                    source_type: sourceSelect ? sourceSelect.value : '',
                    planned_day: daySelect ? daySelect.value : '',
                    expected_revenue: revenueInput ? revenueInput.value : 0,
                    expected_profit: profitInput ? profitInput.value : 0,
                    expected_value: revenueInput ? revenueInput.value : 0,
                    plan_description: descInput ? descInput.value : ''
                });
            });

            return {
                plan_id: planId,
                op_items: opItems,
                prospect_items: prospectItems
            };
        }

        function savePlan(status) {
            const data = collectFormData();
            data.status = status;

            // --- DIAGNOSTIC LOGGING ---
            console.log('[LF PLAN SAVE] Status:', status);
            console.log('[LF PLAN SAVE] Plan ID:', data.plan_id);
            console.log('[LF PLAN SAVE] Op items:', data.op_items.length);
            data.op_items.forEach(function(item, i) {
                console.log('[LF PLAN SAVE]   Item ' + i + ': opp=' + item.opportunity_id + ' stage=[' + item.projected_stage + '] type=' + item.item_type + ' day=' + item.planned_day + ' desc=[' + (item.plan_description || '').substring(0, 40) + ']');
            });
            console.log('[LF PLAN SAVE] Prospect items:', data.prospect_items.length);
            // --- END DIAGNOSTIC ---

            // When submitting, include frozen totals for snapshot
            if (status === 'submitted') {
                data.frozen_closing = parseFloat(document.getElementById('total-closing')?.getAttribute('data-value')) || 0;
                data.frozen_progression = parseFloat(document.getElementById('total-progression')?.getAttribute('data-value')) || 0;
                data.frozen_new_pipeline = parseFloat(document.getElementById('total-new-pipeline')?.getAttribute('data-value')) || 0;
                console.log('[LF PLAN SAVE] Frozen totals: closing=' + data.frozen_closing + ' progression=' + data.frozen_progression + ' new_pipeline=' + data.frozen_new_pipeline);
            }

            const messageEl = document.getElementById('save-message');
            if (messageEl) {
                messageEl.textContent = 'Saving...';
                messageEl.style.color = 'inherit';
            }

            var bodyStr = JSON.stringify(data);
            console.log('[LF PLAN SAVE] Sending ' + bodyStr.length + ' bytes to server');

            fetch('index.php?module=LF_WeeklyPlan&action=save_json', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': typeof LF_CSRF_TOKEN !== 'undefined' ? LF_CSRF_TOKEN : ''
                },
                body: bodyStr
            })
            .then(function(response) {
                console.log('[LF PLAN SAVE] Response status:', response.status);
                return response.json();
            })
            .then(function(result) {
                console.log('[LF PLAN SAVE] Response:', JSON.stringify(result));
                if (messageEl) {
                    messageEl.textContent = result.message || (result.success ? 'Saved successfully' : 'Save failed');
                    messageEl.style.color = result.success ? '#2F7D32' : '#d13438';
                }
                if (result.success && status === 'submitted') {
                    location.reload();
                }
            })
            .catch(function(error) {
                console.error('[LF PLAN SAVE] Error:', error);
                if (messageEl) {
                    messageEl.textContent = 'Error saving plan';
                    messageEl.style.color = '#d13438';
                }
            });
        }

        // Initial calculation
        updateAll();
    });

    function hideFooterModals() {
        const modals = document.querySelectorAll('.modal, .modal-generic, .modal-backdrop');
        modals.forEach(modal => {
            modal.style.display = 'none';
            modal.style.visibility = 'hidden';
        });

        const style = document.createElement('style');
        style.textContent = '.modal, .modal-generic, .modal-backdrop { display: none !important; visibility: hidden !important; }';
        document.head.appendChild(style);
    }

    function injectSubNav() {
        const placeholder = document.getElementById('lf-subnav-placeholder');
        if (!placeholder) return;

        const activePage = placeholder.getAttribute('data-active') || 'planning';
        const isAdmin = placeholder.getAttribute('data-admin') === 'true';

        const links = [
            { id: 'planning', label: 'Rep Plan', url: 'index.php?module=LF_WeeklyPlan&action=planning' },
            { id: 'rep_report', label: 'Rep Report', url: 'index.php?module=LF_WeeklyPlan&action=rep_report' },
            { id: 'plan', label: 'Plan Dashboard', url: 'index.php?module=LF_WeeklyPlan&action=plan' },
            { id: 'report', label: 'Report Dashboard', url: 'index.php?module=LF_WeeklyPlan&action=report' }
        ];

        let html = '<nav class="lf-subnav">';

        links.forEach(link => {
            const activeClass = (link.id === activePage) ? ' active' : '';
            html += '<a href="' + link.url + '" class="lf-subnav-link' + activeClass + '">' + link.label + '</a>';
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
})();
