/**
 * US-008: Create planning dashboard JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    const data = window.LF_DASHBOARD_DATA || {};

    // Hide SuiteCRM footer modals (Reset Password dialog, etc.)
    hideFooterModals();

    // Inject sub-navigation
    injectSubNav();

    // State management - support multiple ID formats
    const companyBtnInitial = document.getElementById('company-view-btn') || document.getElementById('team-view-btn');
    const weekSelectorInitial = document.getElementById('week-select') || document.getElementById('week-selector');
    let state = {
        viewMode: (companyBtnInitial && (companyBtnInitial.className.includes('active'))) ? 'team' : 'rep',
        selectedRepId: document.getElementById('rep-selector') ? document.getElementById('rep-selector').value : '',
        selectedWeek: weekSelectorInitial ? weekSelectorInitial.value : ''
    };

    /**
     * Initialize event listeners
     */
    function init() {
        // View Toggle - support both old (team-view-btn) and new (company-view-btn) IDs
        const companyBtn = document.getElementById('company-view-btn') || document.getElementById('team-view-btn');
        if (companyBtn) {
            companyBtn.addEventListener('click', function() {
                handleViewToggle('team');
            });
        }

        const repBtn = document.getElementById('rep-view-btn');
        if (repBtn) {
            repBtn.addEventListener('click', function() {
                handleViewToggle('rep');
            });
        }

        // Rep Selector
        const repSelector = document.getElementById('rep-selector');
        if (repSelector) {
            repSelector.addEventListener('change', function(e) {
                state.selectedRepId = (e && e.target && e.target.value) || repSelector.value;
                updateRepNameLabel();
                if (state.selectedRepId) {
                    handleViewToggle('rep');
                } else {
                    handleViewToggle('team');
                }
            });
        }

        // Week Navigation - support multiple ID formats
        const weekBackBtn = document.getElementById('week-back') || document.getElementById('week-back-btn');
        if (weekBackBtn) {
            weekBackBtn.addEventListener('click', function() {
                handleWeekNav(-1);
            });
        }

        const weekNextBtn = document.getElementById('week-next') || document.getElementById('week-next-btn');
        if (weekNextBtn) {
            weekNextBtn.addEventListener('click', function() {
                handleWeekNav(1);
            });
        }

        const weekCurrentBtn = document.getElementById('week-current') || document.getElementById('week-current-btn');
        if (weekCurrentBtn) {
            weekCurrentBtn.addEventListener('click', function() {
                if (data.weekList) {
                    const currentWeek = data.weekList.find(w => w.isCurrent);
                    if (currentWeek) {
                        const weekSel = document.getElementById('week-select') || document.getElementById('week-selector');
                        if (weekSel) {
                            weekSel.value = currentWeek.weekStart;
                            state.selectedWeek = currentWeek.weekStart;
                            renderAllColumns();
                        }
                    }
                }
            });
        }

        const weekSelector = document.getElementById('week-select') || document.getElementById('week-selector');
        if (weekSelector) {
            weekSelector.addEventListener('change', function(e) {
                state.selectedWeek = (e && e.target && e.target.value) || weekSelector.value;
                renderAllColumns();
            });
        }

        // Initial render
        renderAllColumns();
    }

    /**
     * Handle view mode toggle
     */
    function handleViewToggle(mode) {
        state.viewMode = mode;

        // Support both old (team-view-btn) and new (company-view-btn) IDs
        const companyBtn = document.getElementById('company-view-btn') || document.getElementById('team-view-btn');
        const repBtn = document.getElementById('rep-view-btn');
        const repSelectorContainer = document.getElementById('rep-selector-container');
        const repSelector = document.getElementById('rep-selector');

        if (mode === 'team') {
            if (companyBtn) {
                companyBtn.classList.add('active');
                companyBtn.style.background = '#125EAD';
                companyBtn.style.color = '#fff';
            }

            if (repBtn) {
                repBtn.classList.remove('active');
                repBtn.style.background = 'transparent';
                repBtn.style.color = '#605e5c';
            }

            if (repSelectorContainer) {
                repSelectorContainer.style.display = 'none';
            }
            if (repSelector) {
                repSelector.style.display = 'none';
            }
            state.selectedRepId = '';
        } else {
            if (repBtn) {
                repBtn.classList.add('active');
                repBtn.style.background = '#125EAD';
                repBtn.style.color = '#fff';
            }

            if (companyBtn) {
                companyBtn.classList.remove('active');
                companyBtn.style.background = 'transparent';
                companyBtn.style.color = '#605e5c';
            }

            if (repSelectorContainer) {
                repSelectorContainer.style.display = 'flex';
            }
            if (repSelector) {
                repSelector.style.display = 'block';
                if (!state.selectedRepId) {
                    state.selectedRepId = repSelector.value;
                }
            }

            updateRepNameLabel();
        }

        renderAllColumns();
    }

    /**
     * Update the rep name label shown to the left of the dropdown
     */
    function updateRepNameLabel() {
        const repNameLabel = document.getElementById('rep-name-label');
        const repSelector = document.getElementById('rep-selector');

        if (!repNameLabel) return;

        if (repSelector && repSelector.value) {
            // Get the selected option text (rep name)
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
     * Handle week navigation (Back/Next)
     */
    function handleWeekNav(direction) {
        const weekSelector = document.getElementById('week-select') || document.getElementById('week-selector');
        if (!weekSelector) return;

        const options = weekSelector.options || weekSelector.children;
        const currentIndex = weekSelector.selectedIndex;
        const newIndex = currentIndex + direction;

        if (newIndex >= 0 && newIndex < options.length) {
            weekSelector.selectedIndex = newIndex;
            state.selectedWeek = weekSelector.value;
            renderAllColumns();
        }
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /**
     * Format numbers with commas
     */
    function formatCurrency(amount) {
        try {
            return new Intl.NumberFormat('en-US').format(Math.round(amount || 0));
        } catch (e) {
            return Math.round(amount || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
    }

    /**
     * Render all three dashboard columns
     */
    function renderAllColumns() {
        renderPipelineHealth();
        renderWeeklyPriorities();
        renderDealRisk();
    }

    /**
     * Column 1: Pipeline Health Check
     */
    function renderPipelineHealth() {
        const container = document.getElementById('pipeline-health-column');
        if (!container) return;

        const config = data.config || {};
        const multiplier = parseFloat(config.pipeline_coverage_multiplier || 3.0);
        const defaultQuota = parseFloat(config.default_annual_quota || 0);
        const currentYear = new Date().getFullYear();

        let totalQuota = 0;
        let closedYtd = 0;
        let pipelineByStage = {};
        let pipelineByRep = data.pipelineByRep || {};
        let reps = data.reps || [];
        let headerTitle = 'Pipeline Health Check';

        if (state.viewMode === 'team') {
            // Team View
            reps.forEach(rep => {
                const repQuota = (data.repTargets && data.repTargets[rep.assigned_user_id]) 
                    ? parseFloat(data.repTargets[rep.assigned_user_id].quota) 
                    : defaultQuota;
                totalQuota += repQuota;
            });
            closedYtd = (data.closedYtd && data.closedYtd.team) || 0;
            pipelineByStage = data.pipelineByStage || {};
        } else {
            // Rep View
            const rep = reps.find(r => r.assigned_user_id === state.selectedRepId);
            totalQuota = (data.repTargets && data.repTargets[state.selectedRepId])
                ? parseFloat(data.repTargets[state.selectedRepId].quota)
                : defaultQuota;
            closedYtd = (data.closedYtd && data.closedYtd.byRep && data.closedYtd.byRep[state.selectedRepId]) || 0;
            pipelineByStage = (data.pipelineByRep && data.pipelineByRep[state.selectedRepId] && data.pipelineByRep[state.selectedRepId].byStage) || {};
        }

        const remainingQuota = Math.max(0, totalQuota - closedYtd);
        const target = remainingQuota * multiplier;
        
        let pipelineTotal = 0;
        Object.values(pipelineByStage).forEach(s => pipelineTotal += (s.profit || 0));

        const gapToTarget = Math.max(0, target - pipelineTotal);
        const coverageRatio = remainingQuota > 0 ? (pipelineTotal / remainingQuota) : 0;

        let html = `
            <div class="lf-card" style="background: #fff; border: 1px solid #edebe9; border-radius: 12px; overflow: hidden; height: 100%; box-shadow: 0 4px 8px rgba(0,0,0,0.12);">
                <div class="lf-card-header" style="background: linear-gradient(to bottom, white, #f3f2f1); padding: 16px; border-bottom: 1px solid #edebe9;">
                    <h2 style="font-size: 16px; font-weight: 600; color: #323130; margin: 0 0 4px 0;">${headerTitle}</h2>
                    <div style="font-size: 12px; color: #605e5c; font-weight: 500;">5 minutes</div>
                </div>
                <div class="lf-card-body" style="padding: 16px;">
                    <!-- Green Closed YTD Banner -->
                    <div style="background: #4BB74E; color: white; padding: 12px; border-radius: 8px; margin-bottom: 12px; text-align: center;">
                        <div style="font-size: 20px; font-weight: 700;">Closed for ${currentYear}: $${formatCurrency(closedYtd)}</div>
                        <div style="font-size: 12px; opacity: 0.9;">
                            Team Quota: ${state.viewMode === 'team' ? `${reps.length} reps x ` : ''} $${formatCurrency(totalQuota)}
                        </div>
                    </div>

                    <!-- Target Calculation -->
                    <div style="font-size: 12px; color: #605e5c; margin-bottom: 8px; padding: 8px; background: #faf9f8; border-radius: 6px;">
                        Target = ($${formatCurrency(totalQuota)} - $${formatCurrency(closedYtd)}) x ${multiplier} = <strong style="color: #d13438;">$${formatCurrency(target)}</strong>
                    </div>

                    <h4 style="margin: 20px 0 10px 0; font-size: 14px; color: #333;">Pipeline Profit by Stage</h4>
                    ${renderStackedBar(pipelineByStage, pipelineTotal)}

                    <div class="lf-gap-alert" style="background: #fdeaea; border: 1px solid #d13438; color: #d13438; padding: 12px; border-radius: 6px; margin-top: 15px; text-align: center; font-weight: bold;">
                        <span style="font-size: 12px; text-transform: uppercase;">Gap to Target</span>
                        <span style="font-size: 18px; display: block;">$${formatCurrency(gapToTarget)}</span>
                    </div>

                    <div style="font-size: 14px; color: #666; margin-top: 15px;">
                        Coverage Ratio: <span style="font-weight: bold; color: #125EAD;">${coverageRatio.toFixed(2)}x</span>
                    </div>

                    <h4 style="margin: 25px 0 10px 0; font-size: 14px; color: #333;">Pipeline by Rep</h4>
                    <div class="lf-pipeline-by-rep" style="display: flex; flex-direction: column; gap: 15px;">`;

        if (state.viewMode === 'team') {
            reps.forEach(rep => {
                const repId = rep.assigned_user_id;
                const repPipeline = (pipelineByRep[repId] && pipelineByRep[repId].byStage) || {};
                let repTotal = 0;
                Object.values(repPipeline).forEach(s => repTotal += (s.profit || 0));

                const repDisplayName = rep.name || rep.full_name || (rep.first_name && rep.last_name ? (rep.first_name + ' ' + rep.last_name) : 'Rep');

                html += `
                    <div class="lf-rep-pipeline-item">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px;">
                            <span>${escapeHtml(repDisplayName)}</span>
                            <span style="font-weight: bold;">$${formatCurrency(repTotal)}</span>
                        </div>
                        ${renderStackedBar(repPipeline, repTotal, true)}
                    </div>`;
            });
        } else {
            const repId = state.selectedRepId;
            const rep = reps.find(r => r.assigned_user_id === repId);
            const repPipeline = (pipelineByRep[repId] && pipelineByRep[repId].byStage) || {};
            let repTotal = 0;
            Object.values(repPipeline).forEach(s => repTotal += (s.profit || 0));

            const repDisplayName = rep ? (rep.name || rep.full_name || (rep.first_name && rep.last_name ? (rep.first_name + ' ' + rep.last_name) : 'Selected Rep')) : 'Selected Rep';

            html += `
                <div class="lf-rep-pipeline-item">
                    <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px;">
                        <span>${escapeHtml(repDisplayName)}</span>
                        <span style="font-weight: bold;">$${formatCurrency(repTotal)}</span>
                    </div>
                    ${renderStackedBar(repPipeline, repTotal, true)}
                </div>`;
        }

        html += `
                    </div>
                </div>
            </div>`;

        container.innerHTML = html;
    }

    /**
     * Helper to render VERTICAL stacked bar chart (matching mockup design)
     * Each stage gets its own horizontal bar, stacked vertically
     */
    function renderStackedBar(pipelineData, total, mini = false) {
        if (!total || total <= 0) {
            return `<div style="height: ${mini ? '30px' : '200px'}; background: #f3f2f1; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #999;">No pipeline data</div>`;
        }

        // Stage colors matching mockup
        const stageColorMap = {
            'Missing': '#d13438',
            '3-Confirmation': '#125EAD',
            '3-Lead': '#0A3D6B',
            '4-Qualified': '#4A90E2',
            '5-Proposal': '#4BB74E',
            '5-Specifications': '#4BB74E',
            '6-Solution': '#2F7D32',
            '6-Negotiation': '#7BC97B',
            '7-Closing': '#ff8c00'
        };

        const defaultColors = ['#125EAD', '#4BB74E', '#ff8c00', '#E6C300', '#d13438', '#7b1fa2', '#00796b'];

        const stages = Object.keys(pipelineData).sort((a, b) => {
            // Sort by profit descending
            return (pipelineData[b].profit || pipelineData[b].amount || 0) - (pipelineData[a].profit || pipelineData[a].amount || 0);
        });

        if (mini) {
            // Mini version - simple horizontal bar
            let html = `<div style="height: 12px; display: flex; border-radius: 4px; overflow: hidden; background: #eee;">`;
            stages.forEach((stage, index) => {
                const amount = pipelineData[stage].profit || pipelineData[stage].amount || 0;
                const percent = (amount / total) * 100;
                const color = Object.keys(stageColorMap).find(k => stage.includes(k))
                    ? stageColorMap[Object.keys(stageColorMap).find(k => stage.includes(k))]
                    : defaultColors[index % defaultColors.length];
                if (percent > 0) {
                    html += `<div title="${escapeHtml(stage)}: $${formatCurrency(amount)}" style="width: ${percent.toFixed(1)}%; background: ${color};"></div>`;
                }
            });
            html += `</div>`;
            return html;
        }

        // Full vertical stacked bar chart
        const chartHeight = 200;
        const maxAmount = Math.max(...stages.map(s => pipelineData[s].profit || pipelineData[s].amount || 0));

        let html = `<div class="lf-vertical-chart" style="background: #f3f2f1; border-radius: 8px; padding: 16px; position: relative; min-height: ${chartHeight}px;">`;
        html += `<div class="lf-stacked-bars" style="display: flex; flex-direction: column; gap: 3px;">`;

        stages.forEach((stage, index) => {
            const amount = pipelineData[stage].profit || pipelineData[stage].amount || 0;
            const heightPercent = maxAmount > 0 ? (amount / maxAmount) * 100 : 0;
            const barHeight = Math.max(20, Math.min(60, 20 + (heightPercent * 0.4)));
            const color = Object.keys(stageColorMap).find(k => stage.includes(k))
                ? stageColorMap[Object.keys(stageColorMap).find(k => stage.includes(k))]
                : defaultColors[index % defaultColors.length];

            html += `
                <div class="lf-stacked-bar" style="background: ${color}; border-radius: 6px; height: ${barHeight}px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 12px; padding: 0 12px; transition: transform 0.2s, box-shadow 0.2s; cursor: default;"
                     onmouseenter="this.style.transform='scaleX(1.02)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.2)';"
                     onmouseleave="this.style.transform='scaleX(1)'; this.style.boxShadow='none';">
                    ${escapeHtml(stage.split('(')[0].trim())} - $${formatCurrency(amount)}
                </div>`;
        });

        html += `</div></div>`;

        // Legend
        html += `<div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 12px;">`;
        stages.forEach((stage, index) => {
            const amount = pipelineData[stage].profit || pipelineData[stage].amount || 0;
            if (amount > 0) {
                const color = Object.keys(stageColorMap).find(k => stage.includes(k))
                    ? stageColorMap[Object.keys(stageColorMap).find(k => stage.includes(k))]
                    : defaultColors[index % defaultColors.length];
                html += `
                    <div style="display: flex; align-items: center; gap: 4px; font-size: 11px;">
                        <div style="width: 10px; height: 10px; background: ${color}; border-radius: 2px;"></div>
                        <span>${escapeHtml(stage)}: $${formatCurrency(amount)}</span>
                    </div>`;
            }
        });
        html += `</div>`;

        return html;
    }

    /**
     * Column 2: Weekly Priorities
     *
     * Team View: Shows target cards per rep with items grouped by category
     * Rep View: Filters to selected rep, splits into Pipeline Progression and New Pipeline sections
     */
    function renderWeeklyPriorities() {
        const container = document.getElementById('weekly-priorities-column');
        if (!container) return;

        const config = data.config || {};
        const defaultNewPipeline = parseFloat(config.default_weekly_new_pipeline || 10000);
        const defaultProgression = parseFloat(config.default_weekly_progression || 5000);
        const reps = data.reps || [];
        const repTargets = data.repTargets || {};

        // Combine planItems and prospectItems
        const planItems = data.planItems || [];
        const prospectItems = data.prospectItems || [];
        const allItems = [...planItems, ...prospectItems];

        // Category definitions with colors
        // Note: DB stores 'developing' as item_type, map both variants
        const categories = {
            'closing': { label: 'Closing', color: '#d13438', order: 1 },
            'at_risk': { label: 'At Risk', color: '#ff8c00', order: 2 },
            'progression': { label: 'Progression', color: '#4BB74E', order: 3 },
            'developing': { label: 'Developing Pipeline', color: '#125EAD', order: 4 },
            'prospecting': { label: 'Prospecting', color: '#7b1fa2', order: 5 }
        };

        // Pipeline Progression categories (for Rep View split)
        const progressionCategories = ['closing', 'at_risk', 'progression'];
        const newPipelineCategories = ['developing', 'prospecting'];

        let html = `
            <div class="lf-card" style="background: #fff; border: 1px solid #edebe9; border-radius: 12px; overflow: hidden; height: 100%; box-shadow: 0 4px 8px rgba(0,0,0,0.12);">
                <div class="lf-card-header" style="background: linear-gradient(to bottom, white, #f3f2f1); padding: 16px; border-bottom: 1px solid #edebe9;">
                    <h2 style="font-size: 16px; font-weight: 600; color: #323130; margin: 0 0 4px 0;">Weekly Priorities</h2>
                    <div style="font-size: 12px; color: #605e5c; font-weight: 500;">15 minutes</div>
                </div>
                <div class="lf-card-body" style="padding: 16px; overflow-y: auto; max-height: calc(100vh - 250px);">
                    <!-- Planned vs Expected header -->`;

        // Filter plan items by selected rep when in rep view
        const allPlanItemsRaw = data.planItems || [];
        const allPlanItems = (state.viewMode === 'rep' && state.selectedRepId)
            ? allPlanItemsRaw.filter(item => item.assigned_user_id === state.selectedRepId)
            : allPlanItemsRaw;
        const stageProbabilities = data.config?.stageProbabilities || {};
        const defaultClosed = parseFloat(config.default_weekly_closed || 10000);

        // Helper to get stage probability from stage name
        function getStageProbability(stageName) {
            if (!stageName) return 0;
            if (stageProbabilities[stageName] !== undefined) return parseFloat(stageProbabilities[stageName]);
            // Extract from stage name like "5-Specifications (30%)"
            const m = stageName.match(/\((\d+)%\)/);
            if (m) return parseInt(m[1]);
            if (stageName.toLowerCase().includes('closed_won') || stageName.toLowerCase().includes('closed won')) return 100;
            return 0;
        }

        let totalPlannedClosing = 0;
        let totalPlannedProgression = 0;
        let totalPlannedNewPipeline = 0;
        allPlanItems.forEach(item => {
            const cat = (item.item_category || item.item_type || '').toLowerCase();
            const profit = parseFloat(item.profit) || 0;
            const projectedProb = getStageProbability(item.projected_stage);
            const currentProb = getStageProbability(item.current_stage);

            if (cat === 'closing') {
                totalPlannedClosing += profit;
                // Closing items also contribute to progression: Profit × (Projected% - Current%) / 100
                totalPlannedProgression += profit * (projectedProb - currentProb) / 100;
            } else if (cat === 'at_risk' || cat === 'progression') {
                totalPlannedProgression += profit * (projectedProb - currentProb) / 100;
            } else if (cat === 'developing' || cat === 'prospecting') {
                totalPlannedNewPipeline += profit;
            }
        });

        // Calculate expected targets — rep view uses individual target, company view multiplies by reps with plans
        let expectedClosed, expectedProgression, expectedNewPipeline;
        if (state.viewMode === 'rep') {
            const selectedRepId = state.selectedRepId;
            const targets = repTargets[selectedRepId] || {};
            expectedClosed = parseFloat(targets.weekly_closed || defaultClosed);
            expectedProgression = parseFloat(targets.weekly_progression || defaultProgression);
            expectedNewPipeline = parseFloat(targets.weekly_new_pipeline || defaultNewPipeline);
        } else {
            const repsWithPlans = new Set(allPlanItems.map(item => item.assigned_user_id)).size || 1;
            expectedClosed = repsWithPlans * defaultClosed;
            expectedProgression = repsWithPlans * defaultProgression;
            expectedNewPipeline = repsWithPlans * defaultNewPipeline;
        }

        const closingPct = expectedClosed > 0 ? Math.round((totalPlannedClosing / expectedClosed) * 100) : 0;
        const progressionPct = expectedProgression > 0 ? Math.round((totalPlannedProgression / expectedProgression) * 100) : 0;
        const newPipelinePct = expectedNewPipeline > 0 ? Math.round((totalPlannedNewPipeline / expectedNewPipeline) * 100) : 0;

        // 3 cards stacked vertically: Closing, Progression, New Pipeline
        html += `
                    <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px;">
                        <div style="background: #faf9f8; padding: 12px; border-radius: 8px; text-align: center;">
                            <div style="font-size: 20px; font-weight: 700; color: #d13438;">$${formatCurrency(totalPlannedClosing)}</div>
                            <div style="font-size: 12px; color: #605e5c;">Closing Planned</div>
                            <div style="font-size: 11px; color: #8a8886; margin-top: 4px;">vs $${formatCurrency(expectedClosed)} expected (${closingPct}%)</div>
                        </div>
                        <div style="background: #faf9f8; padding: 12px; border-radius: 8px; text-align: center;">
                            <div style="font-size: 20px; font-weight: 700; color: #4BB74E;">$${formatCurrency(totalPlannedProgression)}</div>
                            <div style="font-size: 12px; color: #605e5c;">Progression Planned</div>
                            <div style="font-size: 11px; color: #8a8886; margin-top: 4px;">vs $${formatCurrency(expectedProgression)} expected (${progressionPct}%)</div>
                        </div>
                        <div style="background: #faf9f8; padding: 12px; border-radius: 8px; text-align: center;">
                            <div style="font-size: 20px; font-weight: 700; color: #125EAD;">$${formatCurrency(totalPlannedNewPipeline)}</div>
                            <div style="font-size: 12px; color: #605e5c;">New Pipeline Planned</div>
                            <div style="font-size: 11px; color: #8a8886; margin-top: 4px;">vs $${formatCurrency(expectedNewPipeline)} expected (${newPipelinePct}%)</div>
                        </div>
                    </div>`;

        if (state.viewMode === 'team') {
            // TEAM VIEW
            let teamTotals = { closing: 0, at_risk: 0, progression: 0, developing_pipeline: 0, prospecting: 0 };

            reps.forEach(rep => {
                const repId = rep.assigned_user_id;
                const repName = rep.full_name || rep.name || `${rep.first_name || ''} ${rep.last_name || ''}`.trim() || 'Unknown Rep';
                const targets = repTargets[repId] || {};
                const newPipelineTarget = parseFloat(targets.weekly_new_pipeline || defaultNewPipeline);
                const progressionTarget = parseFloat(targets.weekly_progression || defaultProgression);

                // Get this rep's items
                const repItems = allItems.filter(item => item.assigned_user_id === repId);

                // Group by category
                const itemsByCategory = {};
                Object.keys(categories).forEach(cat => { itemsByCategory[cat] = []; });
                repItems.forEach(item => {
                    const cat = (item.item_category || 'prospecting').toLowerCase().replace(' ', '_');
                    if (itemsByCategory[cat]) {
                        itemsByCategory[cat].push(item);
                    }
                });

                // Calculate rep totals
                let repCategoryTotals = {};
                Object.keys(categories).forEach(cat => {
                    const catTotal = itemsByCategory[cat].reduce((sum, item) => sum + (parseFloat(item.profit) || 0), 0);
                    repCategoryTotals[cat] = catTotal;
                    teamTotals[cat] += catTotal;
                });

                html += `
                    <div class="lf-rep-card" style="margin-bottom: 20px; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;">
                        <div style="background: #f5f5f5; padding: 12px 15px; border-bottom: 1px solid #e0e0e0;">
                            <div style="font-weight: bold; font-size: 14px; color: #333;">${escapeHtml(repName)}</div>
                            <div style="display: flex; gap: 20px; margin-top: 8px; font-size: 12px;">
                                <div><span style="color: #666;">New Pipeline Target:</span> <strong style="color: #125EAD;">$${formatCurrency(newPipelineTarget)}</strong></div>
                                <div><span style="color: #666;">Progression Target:</span> <strong style="color: #4BB74E;">$${formatCurrency(progressionTarget)}</strong></div>
                            </div>
                        </div>
                        <div style="padding: 12px 15px;">`;

                // Render items grouped by category
                Object.keys(categories)
                    .sort((a, b) => categories[a].order - categories[b].order)
                    .forEach(cat => {
                        const catItems = itemsByCategory[cat];
                        if (catItems.length > 0) {
                            const catInfo = categories[cat];
                            html += `
                                <div style="margin-bottom: 12px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                        <span style="font-size: 11px; font-weight: 600; color: ${catInfo.color}; text-transform: uppercase;">${catInfo.label}</span>
                                        <span style="font-size: 11px; color: #666;">Total: $${formatCurrency(repCategoryTotals[cat])}</span>
                                    </div>`;
                            catItems.forEach(item => {
                                html += renderPriorityItem(item, catInfo.color);
                            });
                            html += `</div>`;
                        }
                    });

                html += `
                        </div>
                    </div>`;
            });

            // Team Aggregate Totals
            const teamTotal = Object.values(teamTotals).reduce((sum, val) => sum + val, 0);
            html += `
                <div class="lf-team-totals" style="background: #f0f8ff; padding: 15px; border-radius: 8px; margin-top: 10px;">
                    <div style="font-weight: bold; font-size: 14px; color: #125EAD; margin-bottom: 10px;">Team Aggregate Totals</div>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px; font-size: 12px;">`;
            Object.keys(categories)
                .sort((a, b) => categories[a].order - categories[b].order)
                .forEach(cat => {
                    const catInfo = categories[cat];
                    html += `<div style="background: #fff; padding: 8px 12px; border-radius: 4px; border-left: 3px solid ${catInfo.color};">
                        <span style="color: #666;">${catInfo.label}:</span> <strong>$${formatCurrency(teamTotals[cat])}</strong>
                    </div>`;
                });
            html += `
                        <div style="background: #125EAD; color: #fff; padding: 8px 12px; border-radius: 4px;">
                            <span>Total:</span> <strong>$${formatCurrency(teamTotal)}</strong>
                        </div>
                    </div>
                </div>`;

        } else {
            // REP VIEW
            const selectedRepId = state.selectedRepId;
            const rep = reps.find(r => r.assigned_user_id === selectedRepId);
            const repName = rep ? (rep.full_name || rep.name || `${rep.first_name || ''} ${rep.last_name || ''}`.trim()) : 'Selected Rep';
            const targets = repTargets[selectedRepId] || {};
            const newPipelineTarget = parseFloat(targets.weekly_new_pipeline || defaultNewPipeline);
            const progressionTarget = parseFloat(targets.weekly_progression || defaultProgression);

            // Filter items to selected rep
            const repItems = allItems.filter(item => item.assigned_user_id === selectedRepId);

            // Split into 3 sections: Closing, Progression, New Pipeline
            const closingItems = repItems.filter(item => {
                const cat = (item.item_category || '').toLowerCase().replace(' ', '_');
                return cat === 'closing';
            });
            const progressionOnlyItems = repItems.filter(item => {
                const cat = (item.item_category || '').toLowerCase().replace(' ', '_');
                return cat === 'progression' || cat === 'at_risk';
            });
            const newPipelineItems = repItems.filter(item => {
                const cat = (item.item_category || '').toLowerCase().replace(' ', '_');
                return newPipelineCategories.includes(cat);
            });

            // Calculate totals
            const closingTotal = closingItems.reduce((sum, item) => sum + (parseFloat(item.profit) || 0), 0);
            // Progression: Profit × (Projected% - Current%) / 100 for closing + progression + at_risk items
            const allProgressionContributors = [...closingItems, ...progressionOnlyItems];
            const progressionTotal = allProgressionContributors.reduce((sum, item) => {
                const profit = parseFloat(item.profit) || 0;
                const projectedProb = getStageProbability(item.projected_stage);
                const currentProb = getStageProbability(item.current_stage);
                return sum + (profit * (projectedProb - currentProb) / 100);
            }, 0);
            const newPipelineTotal = newPipelineItems.reduce((sum, item) => sum + (parseFloat(item.profit) || 0), 0);

            // --- CLOSING SECTION ---
            html += `
                <h4 style="margin: 20px 0 10px 0; font-size: 13px; color: #d13438; border-bottom: 2px solid #d13438; padding-bottom: 5px;">
                    Closing Priorities
                    <span style="float: right; font-weight: normal; color: #666;">Total: $${formatCurrency(closingTotal)}</span>
                </h4>`;

            if (closingItems.length === 0) {
                html += `<div style="padding: 15px; text-align: center; color: #999; font-size: 12px;">No closing priorities this week</div>`;
            } else {
                closingItems.forEach(item => {
                    html += renderPriorityItem(item, '#d13438');
                });
            }

            // --- PROGRESSION SECTION ---
            html += `
                <h4 style="margin: 25px 0 10px 0; font-size: 13px; color: #4BB74E; border-bottom: 2px solid #4BB74E; padding-bottom: 5px;">
                    Progression Priorities
                    <span style="float: right; font-weight: normal; color: #666;">Total: $${formatCurrency(progressionTotal)}</span>
                </h4>`;

            if (progressionOnlyItems.length === 0) {
                html += `<div style="padding: 15px; text-align: center; color: #999; font-size: 12px;">No progression priorities this week</div>`;
            } else {
                // Group by category within progression
                const groupedProgression = {};
                ['at_risk', 'progression'].forEach(cat => { groupedProgression[cat] = []; });
                progressionOnlyItems.forEach(item => {
                    const cat = (item.item_category || '').toLowerCase().replace(' ', '_');
                    if (groupedProgression[cat]) groupedProgression[cat].push(item);
                });

                ['at_risk', 'progression'].forEach(cat => {
                    const catItems = groupedProgression[cat];
                    if (catItems.length > 0) {
                        const catInfo = categories[cat];
                        html += `<div style="margin-bottom: 10px;">
                            <div style="font-size: 11px; font-weight: 600; color: ${catInfo.color}; text-transform: uppercase; margin-bottom: 5px;">${catInfo.label}</div>`;
                        catItems.forEach(item => {
                            html += renderPriorityItem(item, catInfo.color);
                        });
                        html += `</div>`;
                    }
                });
            }

            // --- NEW PIPELINE SECTION ---
            html += `
                <h4 style="margin: 25px 0 10px 0; font-size: 13px; color: #125EAD; border-bottom: 2px solid #125EAD; padding-bottom: 5px;">
                    New Pipeline Priorities
                    <span style="float: right; font-weight: normal; color: #666;">Total: $${formatCurrency(newPipelineTotal)}</span>
                </h4>`;

            if (newPipelineItems.length === 0) {
                html += `<div style="padding: 15px; text-align: center; color: #999; font-size: 12px;">No new pipeline priorities this week</div>`;
            } else {
                // Group by category within new pipeline
                const groupedNewPipeline = {};
                newPipelineCategories.forEach(cat => { groupedNewPipeline[cat] = []; });
                newPipelineItems.forEach(item => {
                    const cat = (item.item_category || '').toLowerCase().replace(' ', '_');
                    if (groupedNewPipeline[cat]) groupedNewPipeline[cat].push(item);
                });

                newPipelineCategories.forEach(cat => {
                    const catItems = groupedNewPipeline[cat];
                    if (catItems.length > 0) {
                        const catInfo = categories[cat];
                        html += `<div style="margin-bottom: 10px;">
                            <div style="font-size: 11px; font-weight: 600; color: ${catInfo.color}; text-transform: uppercase; margin-bottom: 5px;">${catInfo.label}</div>`;
                        catItems.forEach(item => {
                            html += renderPriorityItem(item, catInfo.color);
                        });
                        html += `</div>`;
                    }
                });
            }
        }

        html += `
                </div>
            </div>`;

        container.innerHTML = html;
    }

    /**
     * Helper to render a single priority item with all 6 data points
     */
    function renderPriorityItem(item, accentColor) {
        const accountName = item.account_name || 'Unknown Account';
        const oppName = item.opportunity_name || item.name || 'Unknown Opportunity';
        const amount = parseFloat(item.profit) || 0;
        const stage = item.projected_stage || item.stage || 'Unknown Stage';
        const day = item.planned_day || item.day || '';
        const description = item.description || item.plan_description || '';

        return `
            <div class="lf-priority-item" style="margin-bottom: 8px; padding: 10px; border: 1px solid #e8e8e8; border-radius: 6px; border-left: 3px solid ${accentColor}; background: #fafafa;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 4px;">
                    <span style="font-size: 12px; font-weight: 600; color: #333;">${escapeHtml(accountName)}</span>
                    <span style="font-size: 12px; font-weight: bold; color: #125EAD;">$${formatCurrency(amount)}</span>
                </div>
                <div style="font-size: 11px; color: #555; margin-bottom: 3px;">${escapeHtml(oppName)}</div>
                <div style="display: flex; gap: 10px; font-size: 10px; color: #888;">
                    <span style="background: #e8e8e8; padding: 2px 6px; border-radius: 3px;">${escapeHtml(stage)}</span>
                    ${day ? `<span style="background: #e8f4fd; padding: 2px 6px; border-radius: 3px;">${escapeHtml(day)}</span>` : ''}
                </div>
                ${description ? `<div style="font-size: 10px; color: #666; margin-top: 4px; font-style: italic;">${escapeHtml(description)}</div>` : ''}
            </div>`;
    }

    /**
     * Column 3: Deal Risk Assessment
     * 
     * Shows opportunities with no activity exceeding configured stale_deal_days (default 14).
     * Excludes '2-Analysis (0%)' stage.
     * Sorted by days since last activity (most stale first).
     */
    function renderDealRisk() {
        const container = document.getElementById('deal-risk-column');
        if (!container) return;

        const config = data.config || {};
        const staleThreshold = parseInt(config.stale_deal_days || 14);
        
        let staleDeals = data.staleDeals || [];
        
        // Filter by threshold and exclude Analysis stage
        staleDeals = staleDeals.filter(deal => {
            const days = parseInt(deal.days_since_activity || 0);
            return (days >= staleThreshold) && 
                   (deal.sales_stage !== '2-Analysis (0%)');
        });

        if (state.viewMode === 'rep' && state.selectedRepId) {
            staleDeals = staleDeals.filter(deal => deal.assigned_user_id === state.selectedRepId);
        }

        // Sort by staleness (most stale first)
        staleDeals.sort((a, b) => parseInt(b.days_since_activity || 0) - parseInt(a.days_since_activity || 0));

        const dealCount = staleDeals.length;
        const countText = dealCount === 1 ? '1 deal' : `${dealCount} deals`;
        
        // Get week label for header (required for US-008 re-render test)
        const selectedWeekObj = (data.weekList || []).find(w => w.weekStart === state.selectedWeek);
        const weekLabel = selectedWeekObj ? ` - ${selectedWeekObj.label}` : '';

        let html = `
            <div class="lf-card" style="background: #fff; border: 1px solid #edebe9; border-radius: 12px; overflow: hidden; height: 100%; box-shadow: 0 4px 8px rgba(0,0,0,0.12);">
                <div class="lf-card-header" style="background: linear-gradient(to bottom, white, #f3f2f1); padding: 16px; border-bottom: 1px solid #edebe9;">
                    <h2 style="font-size: 16px; font-weight: 600; color: #323130; margin: 0 0 4px 0;">Deal Risk Assessment</h2>
                    <div style="font-size: 12px; color: #605e5c; font-weight: 500;">10 minutes</div>
                </div>
                <div class="lf-card-body" style="padding: 16px; overflow-y: auto; max-height: calc(100vh - 250px);">`;

        if (staleDeals.length === 0) {
            html += `<div style="padding: 20px; text-align: center; color: #999;">No high-risk deals identified</div>`;
        } else {
            // Red header with prominent count (matching mockup)
            html += `
                <div style="background: #d13438; color: white; padding: 8px; border-radius: 8px 8px 0 0; text-align: center; margin-bottom: 0;">
                    <div style="font-size: 20px; font-weight: 700;">${dealCount}</div>
                    <div style="font-size: 11px; text-transform: uppercase;">NO ACTIVITY > ${staleThreshold} DAYS</div>
                </div>
                <div style="background: #faf9f8; border-radius: 0 0 8px 8px; padding: 8px; max-height: 200px; overflow-y: auto; margin-bottom: 16px;">`;

            staleDeals.slice(0, 10).forEach(deal => {
                const days = parseInt(deal.days_since_activity || 0);
                const amount = parseFloat(deal.opportunity_profit || deal.profit || deal.amount || 0);
                const accountName = deal.account_name || 'No Account';
                const oppName = deal.opportunity_name || deal.name || 'Unknown Opportunity';

                // Display "No activity" for deals with no logged activity (9999 days)
                let activityText;
                if (days >= 9999) {
                    activityText = 'Never';
                } else if (days === 1) {
                    activityText = '1 day ago';
                } else {
                    activityText = `${days} days ago`;
                }

                html += `
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px solid #edebe9;">
                        <div style="flex: 1;">
                            <div style="font-size: 12px; font-weight: 600; color: #323130;">${escapeHtml(accountName)} - $${formatCurrency(amount)}</div>
                            <div style="font-size: 10px; color: #605e5c;">Last contact: ${activityText}</div>
                        </div>
                        <div style="width: 8px; height: 8px; background: #d13438; border-radius: 50%;"></div>
                    </div>`;
            });

            html += `</div>`;
        }

        // Marked At Risk section (replaces "Missing Key Buyers" per user request 089)
        let atRiskDeals = data.atRiskDeals || [];
        if (state.viewMode === 'rep' && state.selectedRepId) {
            atRiskDeals = atRiskDeals.filter(deal => deal.assigned_user_id === state.selectedRepId);
        }

        const atRiskCount = atRiskDeals.length;
        if (atRiskCount > 0) {
            html += `
                <div style="background: #ff8c00; color: white; padding: 8px; border-radius: 8px 8px 0 0; text-align: center; margin-top: 16px; margin-bottom: 0;">
                    <div style="font-size: 20px; font-weight: 700;">${atRiskCount}</div>
                    <div style="font-size: 11px; text-transform: uppercase;">MARKED AT RISK</div>
                </div>
                <div style="background: #faf9f8; border-radius: 0 0 8px 8px; padding: 8px; max-height: 200px; overflow-y: auto;">`;

            atRiskDeals.slice(0, 10).forEach(deal => {
                const amount = parseFloat(deal.opportunity_profit || deal.profit || deal.amount || 0);
                const accountName = deal.account_name || 'No Account';
                const stageName = deal.sales_stage || 'Unknown Stage';

                html += `
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px solid #edebe9;">
                        <div style="flex: 1;">
                            <div style="font-size: 12px; font-weight: 600; color: #323130;">${escapeHtml(accountName)} - $${formatCurrency(amount)}</div>
                            <div style="font-size: 10px; color: #605e5c;">Stage: ${escapeHtml(stageName)}</div>
                        </div>
                        <div style="width: 8px; height: 8px; background: #ff8c00; border-radius: 50%;"></div>
                    </div>`;
            });

            html += `</div>`;
        }

        html += `
                </div>
            </div>`;

        container.innerHTML = html;
    }

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
        // Subnav removed per user request (089)
        const placeholder = document.getElementById('lf-subnav-placeholder');
        if (placeholder) {
            placeholder.innerHTML = '';
            placeholder.style.display = 'none';
        }
        return;

        // Original code kept for reference (disabled)
        const activePage = placeholder.getAttribute('data-active') || 'plan';
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

    init();
});
