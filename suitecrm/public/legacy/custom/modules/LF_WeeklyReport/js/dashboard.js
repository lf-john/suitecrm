/**
 * US-017: Create reporting dashboard JavaScript
 * US-018: Add Stage Progression column
 * US-019: Create reporting dashboard - Forecast Pulse column
 */

document.addEventListener('DOMContentLoaded', function() {
    const data = window.LF_DASHBOARD_DATA || {};

    // State management
    const teamBtnInitial = document.getElementById('team-view-btn');
    let state = {
        viewMode: (teamBtnInitial && (teamBtnInitial.className.includes('lf-active') || teamBtnInitial.className.includes('active'))) ? 'team' : 'rep',
        selectedRepId: document.getElementById('rep-selector') ? document.getElementById('rep-selector').value : '',
        selectedWeek: document.getElementById('week-selector') ? document.getElementById('week-selector').value : ''
    };

    /**
     * Initialize event listeners
     */
    function init() {
        // View Toggle
        const teamBtn = document.getElementById('team-view-btn');
        if (teamBtn) {
            teamBtn.addEventListener('click', function() {
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
                if (state.selectedRepId) {
                    handleViewToggle('rep');
                } else {
                    handleViewToggle('team');
                }
            });
        }

        // Week Navigation
        const weekBackBtn = document.getElementById('week-back-btn');
        if (weekBackBtn) {
            weekBackBtn.addEventListener('click', function() {
                handleWeekNav(-1);
            });
        }

        const weekNextBtn = document.getElementById('week-next-btn');
        if (weekNextBtn) {
            weekNextBtn.addEventListener('click', function() {
                handleWeekNav(1);
            });
        }

        const weekCurrentBtn = document.getElementById('week-current-btn');
        if (weekCurrentBtn) {
            weekCurrentBtn.addEventListener('click', function() {
                if (data.weekList) {
                    const currentWeek = data.weekList.find(w => w.isCurrent);
                    if (currentWeek) {
                        const weekSelector = document.getElementById('week-selector');
                        if (weekSelector) {
                            weekSelector.value = currentWeek.weekStart;
                            state.selectedWeek = currentWeek.weekStart;
                            renderDashboard();
                        }
                    }
                }
            });
        }

        const weekSelector = document.getElementById('week-selector');
        if (weekSelector) {
            weekSelector.addEventListener('change', function(e) {
                state.selectedWeek = (e && e.target && e.target.value) || weekSelector.value;
                renderDashboard();
            });
        }

        // Initial render
        renderDashboard();
    }

    /**
     * Render all dashboard components
     */
    function renderDashboard() {
        renderCommitmentReview();
        renderStageProgression();
        renderForecastPulse();
    }

    /**
     * Handle view mode toggle
     */
    function handleViewToggle(mode) {
        state.viewMode = mode;

        const teamBtn = document.getElementById('team-view-btn');
        const repBtn = document.getElementById('rep-view-btn');
        const repSelectorContainer = document.getElementById('rep-selector-container');

        if (mode === 'team') {
            if (teamBtn) {
                teamBtn.style.background = '#125EAD';
                teamBtn.style.color = '#fff';
                teamBtn.className = 'lf-btn lf-active';
            }

            if (repBtn) {
                repBtn.style.background = '#fff';
                repBtn.style.color = '#125EAD';
                repBtn.className = 'lf-btn';
            }

            if (repSelectorContainer) {
                repSelectorContainer.className = 'lf-rep-selector-container lf-hidden';
                repSelectorContainer.style.display = 'none';
            }
            state.selectedRepId = '';
        } else {
            if (repBtn) {
                repBtn.style.background = '#125EAD';
                repBtn.style.color = '#fff';
                repBtn.className = 'lf-btn lf-active';
            }

            if (teamBtn) {
                teamBtn.style.background = '#fff';
                teamBtn.style.color = '#125EAD';
                teamBtn.className = 'lf-btn';
            }

            if (repSelectorContainer) {
                repSelectorContainer.className = 'lf-rep-selector-container';
                repSelectorContainer.style.display = 'block';
            }

            const repSelector = document.getElementById('rep-selector');
            if (repSelector && !state.selectedRepId) {
                state.selectedRepId = repSelector.value;
            }
        }

        renderDashboard();
    }

    /**
     * Handle week navigation (Back/Next)
     */
    function handleWeekNav(direction) {
        const weekSelector = document.getElementById('week-selector');
        if (!weekSelector) return;

        const options = weekSelector.options || weekSelector.children;
        const currentIndex = weekSelector.selectedIndex;
        const newIndex = currentIndex + direction;

        if (newIndex >= 0 && newIndex < options.length) {
            weekSelector.selectedIndex = newIndex;
            state.selectedWeek = weekSelector.value;
            renderDashboard();
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
     * Get color for achievement percentage
     * Green >= 76%, Yellow >= 51%, Orange >= 26%, Red < 26%
     */
    function getColorForPercent(percent) {
        const green_threshold = (data.config && data.config.achievement && data.config.achievement.green_threshold) || 76;
        const yellow_threshold = (data.config && data.config.achievement && data.config.achievement.yellow_threshold) || 51;
        const orange_threshold = (data.config && data.config.achievement && data.config.achievement.orange_threshold) || 26;
        
        // Colors from config or defaults
        const colors = (data.config && data.config.achievement && data.config.achievement.colors) || {};
        const green = colors.green || '#2F7D32';
        const yellow = colors.yellow || '#E6C300';
        const orange = colors.orange || '#ff8c00';
        const red = colors.red || '#d13438';

        if (percent >= green_threshold) {
            return green;
        } else if (percent >= yellow_threshold) {
            return yellow;
        } else if (percent >= orange_threshold) {
            return orange;
        } else {
            return red;
        }
    }

    /**
     * Column 1: Commitment Review
     */
    function renderCommitmentReview() {
        const container = document.getElementById('commitment-review-column');
        if (!container) return;

        const commitmentData = data.commitmentData || {};
        
        let html = `
            <div class="lf-card" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; height: 100%; display: flex; flex-direction: column;">
                <div class="lf-card-header" style="background: #125EAD; color: #fff; padding: 15px; font-weight: bold; font-size: 16px;">
                    Commitment Review
                </div>
                <div class="lf-card-body" style="padding: 20px; overflow-y: auto; flex: 1;">`;

        if (state.viewMode === 'team') {
            // TEAM VIEW
            const overallRate = commitmentData.overall_achievement_rate || 0;
            const aggregate = commitmentData.aggregate_new_pipeline || {};
            const aggregateProgression = commitmentData.aggregate_progression || {};

            html += `
                <div style="margin-bottom: 20px; padding: 15px; background: #f0f8ff; border-radius: 8px; border-left: 4px solid #125EAD;">
                    <div style="font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 0.5px;">Overall Achievement Rate</div>
                    <div style="font-size: 32px; font-weight: bold; color: #125EAD;">${overallRate}%</div>
                </div>

                <h4 style="margin: 20px 0 10px 0; font-size: 14px; color: #333;">Team Aggregate</h4>
                <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                    <div style="flex: 1; padding: 12px; background: #f9f9f9; border-radius: 6px; text-align: center;">
                        <div style="font-size: 11px; color: #666; text-transform: uppercase;">New Pipeline</div>
                        <div style="font-size: 16px; font-weight: bold; color: #125EAD;">$${formatCurrency(aggregate.actual || 0)}</div>
                        <div style="font-size: 11px; color: #999;">of $${formatCurrency(aggregate.planned || 0)} (${aggregate.percent || 0}%)</div>
                    </div>
                    <div style="flex: 1; padding: 12px; background: #f9f9f9; border-radius: 6px; text-align: center;">
                        <div style="font-size: 11px; color: #666; text-transform: uppercase;">Progression</div>
                        <div style="font-size: 16px; font-weight: bold; color: #4BB74E;">$${formatCurrency(aggregateProgression.actual || 0)}</div>
                        <div style="font-size: 11px; color: #999;">of $${formatCurrency(aggregateProgression.planned || 0)} (${aggregateProgression.percent || 0}%)</div>
                    </div>
                </div>`;

            // Per-rep cards
            const repData = commitmentData.rep_data || {};
            Object.keys(repData).forEach(repId => {
                const rep = repData[repId];
                html += renderRepCard(rep);
            });

        } else {
            // REP VIEW
            if (!state.selectedRepId) {
                html += `<div style="padding: 30px; text-align: center; color: #999;">Please select a sales rep to view details</div>`;
            } else {
                const repData = commitmentData.rep_data || {};
                const rep = repData[state.selectedRepId];

                if (!rep) {
                    html += `<div style="padding: 30px; text-align: center; color: #999;">No data available for selected rep</div>`;
                } else {
                    html += `
                        <div style="margin-bottom: 20px; padding: 15px; background: #f5f5f5; border-radius: 6px;">
                            <div style="font-weight: bold; font-size: 16px; color: #333;">${escapeHtml(rep.rep_name)}</div>
                            <div style="display: flex; gap: 20px; margin-top: 10px; font-size: 13px;">
                                <div>
                                    <span style="color: #666;">New Pipeline:</span>
                                    <strong style="color: ${(rep.new_pipeline || {}).color || '#125EAD'};">
                                        $${formatCurrency((rep.new_pipeline || {}).actual || 0)} / $${formatCurrency((rep.new_pipeline || {}).planned || 0)} = ${(rep.new_pipeline || {}).percent || 0}%
                                    </strong>
                                </div>
                                <div>
                                    <span style="color: #666;">Progression:</span>
                                    <strong style="color: ${(rep.progression || {}).color || '#4BB74E'};">
                                        $${formatCurrency((rep.progression || {}).actual || 0)} / $${formatCurrency((rep.progression || {}).planned || 0)} = ${(rep.progression || {}).percent || 0}%
                                    </strong>
                                </div>
                            </div>
                        </div>

                        <h4 style="margin: 20px 0 10px 0; font-size: 13px; color: #4BB74E; border-bottom: 2px solid #4BB74E; padding-bottom: 5px;">
                            Pipeline Progression
                        </h4>`;

                    const achievedItems = rep.achieved_items || [];
                    const missedItems = rep.missed_items || [];

                    // Filter progression items
                    const progressionAchieved = achievedItems.filter(item => {
                        // Determine if this is a progression item based on stage
                        return true;
                    });
                    const progressionMissed = missedItems.filter(item => {
                        return true;
                    });

                    if (progressionAchieved.length === 0 && progressionMissed.length === 0) {
                        html += `<div style="padding: 15px; text-align: center; color: #999; font-size: 12px;">No progression items this week</div>`;
                    } else {
                        progressionAchieved.forEach(item => {
                            html += renderPlanItem(item, true);
                        });
                        progressionMissed.forEach(item => {
                            html += renderPlanItem(item, false);
                        });
                    }

                    html += `
                        <h4 style="margin: 25px 0 10px 0; font-size: 13px; color: #125EAD; border-bottom: 2px solid #125EAD; padding-bottom: 5px;">
                            New Pipeline
                        </h4>`;

                    // For new pipeline, show achieved and missed
                    if (achievedItems.length === 0 && missedItems.length === 0) {
                        html += `<div style="padding: 15px; text-align: center; color: #999; font-size: 12px;">No new pipeline items this week</div>`;
                    } else {
                        // Show all achieved items as new pipeline
                        achievedItems.forEach(item => {
                            html += renderPlanItem(item, true);
                        });
                        missedItems.forEach(item => {
                            html += renderPlanItem(item, false);
                        });
                    }

                    // Unplanned successes
                    const unplanned = rep.unplanned_successes || [];
                    if (unplanned.length > 0) {
                        html += `
                            <h4 style="margin: 25px 0 10px 0; font-size: 13px; color: #2F7D32; border-bottom: 2px solid #2F7D32; padding-bottom: 5px;">
                                Unplanned Successes
                            </h4>`;
                        unplanned.forEach(item => {
                            html += renderUnplannedSuccess(item);
                        });
                    }
                }
            }
        }

        html += `
                </div>
            </div>`;

        container.innerHTML = html;
    }

    /**
     * Column 2: Stage Progression
     */
    function renderStageProgression() {
        const container = document.getElementById('stage-progression-column');
        if (!container) return;

        const commitmentData = data.commitmentData || {};
        const snapshots = data.reportSnapshots || [];
        
        let html = `
            <div class="lf-card" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; height: 100%; display: flex; flex-direction: column;">
                <div class="lf-card-header" style="background: #125EAD; color: #fff; padding: 15px; font-weight: bold; font-size: 16px;">
                    Stage Progression
                </div>
                <div class="lf-card-body" style="padding: 20px; overflow-y: auto; flex: 1;">`;

        // Determine data context
        let newPipelineData = {};
        let progressionData = {};
        let filteredSnapshots = [];

        if (state.viewMode === 'team') {
            newPipelineData = commitmentData.aggregate_new_pipeline || {};
            progressionData = commitmentData.aggregate_progression || {};
            filteredSnapshots = snapshots; // Show all in team view
        } else {
            if (!state.selectedRepId) {
                container.innerHTML = html + `<div style="padding: 30px; text-align: center; color: #999;">Please select a sales rep to view details</div></div></div>`;
                return;
            }
            const repData = (commitmentData.rep_data || {})[state.selectedRepId] || {};
            newPipelineData = repData.new_pipeline || {};
            progressionData = repData.progression || {};
            
            // Filter snapshots by rep
            filteredSnapshots = snapshots.filter(s => s.assigned_user_id === state.selectedRepId);
        }

        // Summary Sections
        html += `
            <div style="margin-bottom: 20px;">
                <h4 style="margin: 0 0 10px 0; font-size: 13px; color: #666; text-transform: uppercase;">New Pipeline</h4>
                <div style="padding: 15px; background: #f9f9f9; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                         <div style="font-size: 18px; font-weight: bold; color: #333;">$${formatCurrency(newPipelineData.actual || 0)}</div>
                         <div style="font-size: 11px; color: #999;">Target: $${formatCurrency(newPipelineData.planned || 0)}</div>
                    </div>
                    <div style="font-size: 18px; font-weight: bold; color: ${getColorForPercent(newPipelineData.percent || 0)};">
                        ${newPipelineData.percent || 0}%
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <h4 style="margin: 0 0 10px 0; font-size: 13px; color: #666; text-transform: uppercase;">Progressed Pipeline</h4>
                <div style="padding: 15px; background: #f9f9f9; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                         <div style="font-size: 18px; font-weight: bold; color: #333;">$${formatCurrency(progressionData.actual || 0)}</div>
                         <div style="font-size: 11px; color: #999;">Target: $${formatCurrency(progressionData.planned || 0)}</div>
                    </div>
                    <div style="font-size: 18px; font-weight: bold; color: ${getColorForPercent(progressionData.percent || 0)};">
                        ${progressionData.percent || 0}%
                    </div>
                </div>
            </div>`;

        // Movement Counts
        const forwardCount = filteredSnapshots.filter(s => s.movement === 'forward').length;
        const backwardCount = filteredSnapshots.filter(s => s.movement === 'backward').length;
        const staticCount = filteredSnapshots.filter(s => s.movement === 'static').length;
        html += `
            <div style="display: flex; gap: 10px; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                <div style="flex: 1; text-align: center; padding: 10px; background: #f0f9f0; border-radius: 6px; border: 1px solid #e0e0e0;">
                    <div style="font-size: 14px; font-weight: bold; color: #2F7D32;">Forward: ${forwardCount}</div>
                </div>
                <div style="flex: 1; text-align: center; padding: 10px; background: #fff5f5; border-radius: 6px; border: 1px solid #e0e0e0;">
                    <div style="font-size: 14px; font-weight: bold; color: #d13438;">Backward: ${backwardCount}</div>
                </div>
                <div style="flex: 1; text-align: center; padding: 10px; background: #f5f5f5; border-radius: 6px; border: 1px solid #e0e0e0;">
                    <div style="font-size: 14px; font-weight: bold; color: #666;">Static: ${staticCount}</div>
                </div>
            </div>`;

        // Success List (Forward + New)
        const successes = filteredSnapshots.filter(s => s.movement === 'forward' || s.movement === 'new');
        if (successes.length > 0) {
            html += `
                <h4 style="margin: 0 0 10px 0; font-size: 13px; color: #2F7D32; border-bottom: 2px solid #2F7D32; padding-bottom: 5px;">
                    Success (Advanced)
                </h4>
                <div style="margin-bottom: 25px;">`;
            
            successes.forEach(s => {
                html += renderSnapshotItem(s, 'success');
            });
            html += `</div>`;
        }

        // Regression List (Backward)
        const regressions = filteredSnapshots.filter(s => s.movement === 'backward');
        if (regressions.length > 0) {
            html += `
                <h4 style="margin: 0 0 10px 0; font-size: 13px; color: #ff8c00; border-bottom: 2px solid #ff8c00; padding-bottom: 5px;">
                    Regression (Backward)
                </h4>
                <div style="margin-bottom: 20px;">`;
            
            regressions.forEach(s => {
                html += renderSnapshotItem(s, 'warning');
            });
            html += `</div>`;
        }
        
        if (successes.length === 0 && regressions.length === 0) {
             html += `<div style="text-align: center; color: #999; padding: 20px; font-style: italic;">No movement data to display</div>`;
        }

        html += `
                </div>
            </div>`;

        container.innerHTML = html;
    }

    /**
     * Column 3: Forecast Pulse
     */
    function renderForecastPulse() {
        const container = document.getElementById('forecast-pulse-column');
        if (!container) return;

        const forecastData = data.forecastOpportunities || {};
        const currentQ = forecastData.current || {};
        const nextQ = forecastData.next || {};
        
        // Helper to safe-guard against missing arrays
        const currentOpps = currentQ.opportunities || [];
        const nextOpps = nextQ.opportunities || [];
        
        // Read fiscal config
        const fiscalStartMonth = (data.config && data.config.fiscal_year_start_month) || 1;

        let html = `
            <div class="lf-card" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; height: 100%; display: flex; flex-direction: column;">
                <div class="lf-card-header" style="background: #125EAD; color: #fff; padding: 15px; font-weight: bold; font-size: 16px;">
                    Forecast Pulse
                </div>
                <div class="lf-card-body" style="padding: 20px; overflow-y: auto; flex: 1;">`;

        if (currentOpps.length === 0 && nextOpps.length === 0) {
             html += `<div style="padding: 20px; text-align: center; color: #999;">No forecast data available</div>`;
             html += `</div></div>`;
             container.innerHTML = html;
             return;
        }

        // Render Current Quarter
        html += renderQuarterSection(currentQ, 'Current Quarter', fiscalStartMonth);
        
        // Separator
        html += `<div style="height: 20px; border-bottom: 1px dashed #ddd; margin-bottom: 20px;"></div>`;

        // Render Next Quarter
        html += renderQuarterSection(nextQ, 'Next Quarter', fiscalStartMonth);

        html += `
                </div>
            </div>`;

        container.innerHTML = html;
    }

    function getQuarterDateRange(quarterNum, fiscalStartMonth) {
        // quarterNum is 1-based (Q1, Q2...)
        // fiscalStartMonth is 1-12
        
        const startMonthIndex = (fiscalStartMonth - 1) + (quarterNum - 1) * 3;
        
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        const m1 = months[startMonthIndex % 12];
        const m3 = months[(startMonthIndex + 2) % 12];
        
        return `${m1}-${m3}`;
    }

    function renderQuarterSection(quarterData, titlePrefix, fiscalStartMonth) {
        const quarterNum = quarterData.quarter || 1;
        const year = quarterData.year || '';
        const opps = quarterData.opportunities || [];
        
        const dateRange = getQuarterDateRange(quarterNum, fiscalStartMonth);
        const title = `${titlePrefix} (Q${quarterNum} ${year}: ${dateRange})`;

        // Filter opps if in Rep View
        let filteredOpps = opps;
        if (state.viewMode !== 'team') {
             if (!state.selectedRepId) {
                 return `
                    <h4 style="margin: 0 0 15px 0; font-size: 14px; color: #333; border-bottom: 2px solid #eee; padding-bottom: 8px;">
                        ${title}
                    </h4>
                    <div style="padding: 15px; text-align: center; color: #999;">Please select a sales rep</div>`;
             }
             filteredOpps = opps.filter(o => o.assigned_user_id === state.selectedRepId);
        }

        // Calculate Totals
        let totalPipeline = 0;
        let totalWeighted = 0;

        filteredOpps.forEach(o => {
            const amt = parseFloat(o.amount) || 0;
            const prob = parseFloat(o.probability) || 0;
            totalPipeline += amt;
            totalWeighted += (amt * prob / 100);
        });

        // Totals Row HTML
        const totalsHtml = `
            <div style="margin-top: 15px; padding: 10px; background: #f0f8ff; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #d0e0f0;">
                <div style="font-weight: bold; color: #125EAD;">TOTAL</div>
                <div style="text-align: right;">
                    <div style="font-size: 14px; font-weight: bold; color: #333;">$${formatCurrency(totalPipeline)}</div>
                    <div style="font-size: 11px; color: #666;">Weighted: $${formatCurrency(totalWeighted)}</div>
                </div>
            </div>`;

        let contentHtml = '';

        if (state.viewMode === 'team') {
            // TEAM VIEW: Per-rep cards
            // Group by Rep
            const repStats = {};
            filteredOpps.forEach(o => {
                const repId = o.assigned_user_id;
                if (!repStats[repId]) {
                    // Find rep name from data.reps
                    const repInfo = (data.reps || []).find(r => r.assigned_user_id === repId) || {};
                    repStats[repId] = {
                        name: repInfo.full_name || 'Unknown Rep',
                        pipeline: 0,
                        weighted: 0
                    };
                }
                const amt = parseFloat(o.amount) || 0;
                const prob = parseFloat(o.probability) || 0;
                repStats[repId].pipeline += amt;
                repStats[repId].weighted += (amt * prob / 100);
            });

            const repIds = Object.keys(repStats);
            if (repIds.length === 0) {
                 contentHtml = `<div style="padding: 10px; text-align: center; color: #999; font-size: 12px;">No opportunities found</div>`;
            } else {
                repIds.forEach(repId => {
                    const stats = repStats[repId];
                    const confidence = stats.pipeline > 0 ? (stats.weighted / stats.pipeline * 100) : 0;
                    
                    contentHtml += `
                        <div style="margin-bottom: 10px; padding: 10px; border: 1px solid #eee; border-radius: 6px; background: #fff;">
                            <div style="font-weight: bold; font-size: 13px; color: #333; margin-bottom: 5px;">${escapeHtml(stats.name)}</div>
                            <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 3px;">
                                <span style="color: #666;">Pipeline:</span>
                                <span style="font-weight: 600;">$${formatCurrency(stats.pipeline)}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 3px;">
                                <span style="color: #666;">Weighted:</span>
                                <span style="font-weight: 600;">$${formatCurrency(stats.weighted)}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 11px;">
                                <span style="color: #666;">Confidence:</span>
                                <span style="font-weight: 600; color: ${confidence > 50 ? '#2F7D32' : '#ff8c00'};">${confidence.toFixed(1)}%</span>
                            </div>
                        </div>`;
                });
            }

        } else {
            // REP VIEW: Individual Opportunities
            if (filteredOpps.length === 0) {
                contentHtml = `<div style="padding: 10px; text-align: center; color: #999; font-size: 12px;">No opportunities found</div>`;
            } else {
                filteredOpps.forEach(o => {
                    const amt = parseFloat(o.amount) || 0;
                    const prob = parseFloat(o.probability) || 0;
                    const weighted = amt * prob / 100;
                    
                    contentHtml += `
                        <div style="margin-bottom: 10px; padding: 10px; border: 1px solid #e8e8e8; border-radius: 6px; background: #f9f9f9;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                                <span style="font-weight: 600; font-size: 12px; color: #333;">${escapeHtml(o.account_name)}</span>
                                <span style="font-weight: bold; font-size: 12px; color: #333;">$${formatCurrency(amt)}</span>
                            </div>
                            <div style="font-size: 11px; color: #125EAD; margin-bottom: 4px;">${escapeHtml(o.opportunity_name)}</div>
                            <div style="display: flex; justify-content: space-between; font-size: 11px; color: #666;">
                                <span>${escapeHtml(o.sales_stage)} (${prob}%)</span>
                                <span>${escapeHtml(o.date_closed)}</span>
                            </div>
                            <div style="margin-top: 4px; padding-top: 4px; border-top: 1px dashed #ddd; font-size: 11px; text-align: right; color: #555;">
                                Weighted: <strong>$${formatCurrency(weighted)}</strong>
                            </div>
                        </div>`;
                });
            }
        }

        return `
            <div style="margin-bottom: 10px;">
                <h4 style="margin: 0 0 15px 0; font-size: 14px; color: #333; border-bottom: 2px solid #eee; padding-bottom: 8px;">
                    ${title}
                </h4>
                ${contentHtml}
                ${totalsHtml}
            </div>`;
    }

    /**
     * Render a snapshot item (Success/Regression)
     */
    function renderSnapshotItem(snapshot, type) {
        const isSuccess = type === 'success';
        const borderColor = isSuccess ? '#2F7D32' : '#ff8c00'; // Green vs Orange
        const bgColor = isSuccess ? '#f0f9f0' : '#fff9e6';
        
        // Format stage transition
        const oldStage = snapshot.stage_at_week_start || 'Unknown';
        const newStage = snapshot.stage_at_week_end || 'Unknown';
        
        return `
            <div style="margin-bottom: 10px; padding: 10px; border: 1px solid #e8e8e8; border-radius: 6px; border-left: 3px solid ${borderColor}; background: ${bgColor};">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 4px;">
                    <span style="font-size: 12px; font-weight: 600; color: #333;">${escapeHtml(snapshot.account_name)}</span>
                    <span style="font-size: 12px; font-weight: bold; color: #333;">$${formatCurrency(snapshot.amount)}</span>
                </div>
                <div style="font-size: 11px; color: #555; margin-bottom: 6px;">${escapeHtml(snapshot.opportunity_name)}</div>
                <div style="font-size: 10px; color: #666; background: rgba(255,255,255,0.5); padding: 4px; border-radius: 4px;">
                    ${escapeHtml(oldStage)} <span style="color: ${borderColor}; font-weight: bold;">&rarr;</span> ${escapeHtml(newStage)}
                </div>
            </div>`;
    }

    /**
     * Render a rep card for Team View
     */
    function renderRepCard(rep) {
        const newPipeline = rep.new_pipeline || {};
        const progression = rep.progression || {};
        const achievedItems = rep.achieved_items || [];
        const missedItems = rep.missed_items || [];
        const unplanned = rep.unplanned_successes || [];

        let html = `
            <div class="lf-rep-card" style="margin-bottom: 20px; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;">
                <div style="background: #f5f5f5; padding: 12px 15px; border-bottom: 1px solid #e0e0e0;">
                    <div style="font-weight: bold; font-size: 14px; color: #333;">${escapeHtml(rep.rep_name)}</div>
                    <div style="display: flex; gap: 20px; margin-top: 8px; font-size: 12px;">
                        <div>
                            <span style="color: #666;">New Pipeline:</span>
                            <strong style="color: ${newPipeline.color || '#125EAD'};">
                                ${newPipeline.percent || 0}%
                            </strong>
                        </div>
                        <div>
                            <span style="color: #666;">Progression:</span>
                            <strong style="color: ${progression.color || '#4BB74E'};">
                                ${progression.percent || 0}%
                            </strong>
                        </div>
                    </div>
                </div>
                <div style="padding: 12px 15px;">`;

        // Achieved items
        if (achievedItems.length > 0) {
            html += `<div style="margin-bottom: 10px;">`;
            achievedItems.forEach(item => {
                html += renderPlanItem(item, true, true);
            });
            html += `</div>`;
        }

        // Missed items
        if (missedItems.length > 0) {
            html += `<div style="margin-bottom: 10px;">`;
            missedItems.forEach(item => {
                html += renderPlanItem(item, false, true);
            });
            html += `</div>`;
        }

        // Unplanned successes
        if (unplanned.length > 0) {
            html += `<div style="margin-top: 10px; padding: 10px; background: #e8f5e9; border-radius: 6px; border-left: 3px solid #2F7D32;">`;
            html += `<div style="font-size: 11px; font-weight: 600; color: #2F7D32; text-transform: uppercase; margin-bottom: 5px;">Unplanned Successes</div>`;
            unplanned.forEach(item => {
                html += renderUnplannedSuccess(item);
            });
            html += `</div>`;
        }

        html += `
                </div>
            </div>`;

        return html;
    }

    /**
     * Render a single plan item
     */
    function renderPlanItem(item, achieved, compact = false) {
        const icon = achieved ? '&#10003;' : '&times;';
        const iconColor = achieved ? '#2F7D32' : '#d13438';
        const bgColor = achieved ? '#f0f9f0' : '#fff5f5';
        const accountName = item.account_name || 'Unknown Account';
        const oppName = item.opportunity_name || 'Unknown Opportunity';
        const amount = item.amount || 0;
        const description = item.result_description || '';

        if (compact) {
            return `
                <div style="display: flex; gap: 8px; align-items: start; margin-bottom: 6px; font-size: 11px;">
                    <span style="color: ${iconColor}; font-size: 14px; flex-shrink: 0;">${icon}</span>
                    <span style="flex: 1;">
                        <span style="font-weight: 600; color: #333;">${escapeHtml(accountName)}</span>
                        ${description ? `<span style="color: #666;"> - ${escapeHtml(description)}</span>` : ''}
                    </span>
                </div>`;
        }

        return `
            <div class="lf-plan-item" style="margin-bottom: 10px; padding: 10px; border: 1px solid #e8e8e8; border-radius: 6px; border-left: 3px solid ${iconColor}; background: ${bgColor};">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 4px;">
                    <span style="font-size: 12px; font-weight: 600; color: #333;">${escapeHtml(accountName)}</span>
                    <span style="color: ${iconColor}; font-size: 16px;">${icon}</span>
                </div>
                <div style="font-size: 11px; color: #555; margin-bottom: 3px;">${escapeHtml(oppName)}</div>
                ${description ? `<div style="font-size: 11px; color: #666;">${escapeHtml(description)}</div>` : ''}
            </div>`;
    }

    /**
     * Render an unplanned success item
     */
    function renderUnplannedSuccess(item) {
        const sourceType = item.source_type || 'Unknown';
        const expectedValue = item.expected_value || 0;

        return `
            <div style="display: flex; gap: 8px; align-items: start; margin-bottom: 6px; font-size: 11px; padding: 6px; background: #f1f8f4; border-radius: 4px;">
                <span style="color: #2F7D32; font-size: 12px; flex-shrink: 0;">&#10003;</span>
                <span style="flex: 1; color: #2F7D32;">
                    <strong>${escapeHtml(sourceType)}</strong> - $${formatCurrency(expectedValue)}
                </span>
            </div>`;
    }

    init();
});