<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'include/MVC/View/SugarView.php';
require_once 'custom/include/LF_PlanningReporting/WeekHelper.php';
require_once 'custom/include/LF_PlanningReporting/LF_SubHeader.php';
require_once 'custom/include/LF_PlanningReporting/OpportunityQuery.php';
require_once 'custom/modules/LF_PRConfig/LF_PRConfig.php';
require_once 'custom/modules/LF_WeeklyReport/LF_WeeklyReport.php';
require_once 'custom/modules/LF_ReportSnapshot/LF_ReportSnapshot.php';

#[\AllowDynamicProperties]
class LF_WeeklyPlanViewRep_report extends SugarView
{
    public function __construct()
    {
        parent::__construct();
        $this->options['show_header'] = true;
        $this->options['show_footer'] = true;
    }

    public function display()
    {
        global $current_user;
        $db = DBManagerFactory::getInstance();

        // Allow admin to select which user to view
        $selectedUserId = $current_user->id;
        $selectedUserName = $current_user->full_name;
        if ($current_user->is_admin && !empty($_REQUEST['rep_id'])) {
            $selectedUserId = $_REQUEST['rep_id'];
            $userQuery = "SELECT first_name, last_name FROM users WHERE id = " . $db->quoted($selectedUserId) . " AND deleted = 0";
            $userResult = $db->query($userQuery);
            $userRow = $db->fetchByAssoc($userResult);
            if ($userRow) {
                $selectedUserName = trim($userRow['first_name'] . ' ' . $userRow['last_name']);
            }
        }

        if (!empty($_REQUEST['week_start'])) {
            $weekStart = WeekHelper::getWeekStart($_REQUEST['week_start']);
        } else {
            $weekStart = WeekHelper::getCurrentWeekStart();
        }
        $weekEnd = WeekHelper::getWeekEnd($weekStart);

        // Load or create the weekly report
        $report = LF_WeeklyReport::getOrCreateForWeek($selectedUserId, $weekStart);

        // Load stage probabilities
        $probabilities = LF_PRConfig::getConfigJson('stages', 'stage_probabilities');
        if (!is_array($probabilities)) {
            $probabilities = [];
        }
        $probabilities['Closed Won'] = 100;
        $probabilities['closed_won'] = 100;
        $probabilities['Closed Lost'] = 0;
        $probabilities['closed_lost'] = 0;

        // Load the corresponding weekly plan
        $plan = null;
        $planItems = [];
        if (!empty($report->lf_weekly_plan_id)) {
            $plan = BeanFactory::getBean('LF_WeeklyPlan', $report->lf_weekly_plan_id);
        }

        // Load all planned opportunity items grouped by type
        $existingPipelineItems = []; // closing + progression
        $developingItems = [];       // developing
        $unplannedPlanOppIds = [];   // items in plan but with no projected_stage (treat as unplanned)
        if ($plan && !empty($plan->id)) {
            $query = sprintf(
                "SELECT id, opportunity_id, item_type, projected_stage, plan_description, is_at_risk
                 FROM lf_plan_op_items
                 WHERE lf_weekly_plan_id = %s AND deleted = 0",
                $db->quoted($plan->id)
            );
            $result = $db->query($query);
            while ($row = $db->fetchByAssoc($result)) {
                // Items with empty projected_stage or '--' item_type are unplanned
                if (empty($row['projected_stage']) || $row['item_type'] === '--') {
                    $unplannedPlanOppIds[$row['opportunity_id']] = true;
                    continue;
                }
                if ($row['item_type'] === 'developing') {
                    $developingItems[$row['opportunity_id']] = $row;
                } else {
                    $existingPipelineItems[$row['opportunity_id']] = $row;
                }
                $planItems[$row['opportunity_id']] = $row;
            }
        }

        // Load snapshots
        $snapshots = [];
        $query = sprintf(
            "SELECT id, opportunity_id, opportunity_name, account_name,
                    stage_at_week_start, stage_at_week_end, movement,
                    was_planned, plan_category, result_description
             FROM lf_report_snapshots
             WHERE lf_weekly_report_id = %s AND deleted = 0",
            $db->quoted($report->id)
        );
        $result = $db->query($query);
        while ($row = $db->fetchByAssoc($result)) {
            $snapshots[$row['opportunity_id']] = $row;
        }

        // Check for weekly snapshot data
        $weekEndAt = OpportunityQuery::getSnapshotWeekEndAt($weekStart);
        $hasWeeklySnapshot = OpportunityQuery::hasSnapshot($weekEndAt);

        // Load opportunity data — from weekly snapshot if available, otherwise live
        $opportunities = [];
        $allOppIds = array_unique(array_merge(array_keys($planItems), array_keys($snapshots), array_keys($unplannedPlanOppIds)));
        if (!empty($allOppIds)) {
            $idList = implode(',', array_map([$db, 'quoted'], $allOppIds));
            if ($hasWeeklySnapshot) {
                // Use snapshot data for amount/profit, live name/stage
                $query = "SELECT o.id, o.name, o.sales_stage,
                                 COALESCE(s.revenue, o.amount) as amount,
                                 COALESCE(s.profit, o.opportunity_profit) as opportunity_profit
                          FROM opportunities o
                          LEFT JOIN opportunity_weekly_snapshot s ON s.opportunity_id = o.id
                              AND s.week_end_at = " . $db->quoted($weekEndAt) . " AND s.deleted = 0
                          WHERE o.id IN ($idList) AND o.deleted = 0";
            } else {
                $query = "SELECT id, name, sales_stage, amount, opportunity_profit FROM opportunities WHERE id IN ($idList) AND deleted = 0";
            }
            $result = $db->query($query);
            while ($row = $db->fetchByAssoc($result)) {
                $opportunities[$row['id']] = $row;
            }
        }

        // Get account names via BeanFactory for planned items
        $accountNames = [];
        foreach ($allOppIds as $oppId) {
            $snap = $snapshots[$oppId] ?? null;
            if ($snap && !empty($snap['account_name'])) {
                $accountNames[$oppId] = $snap['account_name'];
            } else {
                $oppBean = BeanFactory::getBean('Opportunities', $oppId);
                if ($oppBean && !empty($oppBean->id)) {
                    $accountNames[$oppId] = $oppBean->account_name ?? '';
                }
            }
        }

        // Load start-of-week stages from opportunity_weekly_snapshot (needed for unplanned detection + summary)
        $snapshotStages = [];
        if ($hasWeeklySnapshot) {
            $snapStageQuery = "SELECT opportunity_id, stage_name, stage_pct FROM opportunity_weekly_snapshot WHERE week_end_at = " . $db->quoted($weekEndAt) . " AND deleted = 0";
            $snapStageResult = $db->query($snapStageQuery);
            while ($row = $db->fetchByAssoc($snapStageResult)) {
                $snapshotStages[$row['opportunity_id']] = $row;
            }
        }

        // Identify unplanned movers (snapshots for opps NOT in the plan that changed stage)
        $unplannedExisting = [];
        $unplannedDeveloping = [];
        $analysisStage = LF_PRConfig::getConfig('stages', 'analysis_stage') ?: '2-Analysis (0%)';
        $analysisProb = (int)($probabilities[$analysisStage] ?? 0);

        // Check report snapshots for unplanned movers
        foreach ($snapshots as $oppId => $snap) {
            if (isset($planItems[$oppId]) && !isset($unplannedPlanOppIds[$oppId])) continue;
            $opp = $opportunities[$oppId] ?? null;
            if (!$opp) continue;

            $startStage = $snap['stage_at_week_start'] ?? '';
            $currentStage = $opp['sales_stage'] ?? '';
            if ($startStage === $currentStage) continue;

            $startProb = (int)($probabilities[$startStage] ?? 0);
            if ($startProb <= $analysisProb) {
                $unplannedDeveloping[$oppId] = $snap;
            } else {
                $unplannedExisting[$oppId] = $snap;
            }
        }

        // Also detect unplanned items from plan items with empty projected_stage
        // These won't be in $snapshots (lf_report_snapshots) but will be in $snapshotStages (opportunity_weekly_snapshot)
        foreach ($unplannedPlanOppIds as $oppId => $flag) {
            if (isset($unplannedExisting[$oppId]) || isset($unplannedDeveloping[$oppId])) continue; // already detected
            $opp = $opportunities[$oppId] ?? null;
            if (!$opp) continue;

            $weeklySnap = $snapshotStages[$oppId] ?? null;
            $startStage = $weeklySnap ? ($weeklySnap['stage_name'] ?? '') : '';
            $currentStage = $opp['sales_stage'] ?? '';
            if (empty($startStage) || $startStage === $currentStage) continue;

            $startProb = (int)($probabilities[$startStage] ?? 0);
            $currentProb = (int)($probabilities[$currentStage] ?? 0);
            if ($currentProb <= $startProb) continue; // no forward movement

            // Create a synthetic snapshot entry for rendering
            $synthSnap = ['stage_at_week_start' => $startStage, 'id' => '', 'result_description' => ''];
            if ($startProb <= $analysisProb) {
                $unplannedDeveloping[$oppId] = $synthSnap;
            } else {
                $unplannedExisting[$oppId] = $synthSnap;
            }
        }

        // Load prospect items
        $prospectItems = [];
        if ($plan && !empty($plan->id)) {
            $query = sprintf(
                "SELECT * FROM lf_plan_prospect_items WHERE lf_weekly_plan_id = %s AND deleted = 0",
                $db->quoted($plan->id)
            );
            $result = $db->query($query);
            while ($row = $db->fetchByAssoc($result)) {
                $prospectItems[] = $row;
            }
        }

        // Get all active users for admin user selector
        $allUsers = [];
        if ($current_user->is_admin) {
            $usersQuery = "SELECT id, first_name, last_name FROM users WHERE deleted = 0 AND status = 'Active' ORDER BY last_name, first_name";
            $usersResult = $db->query($usersQuery);
            while ($row = $db->fetchByAssoc($usersResult)) {
                $allUsers[] = $row;
            }
        }

        // Render
        parent::display();

        $isAdmin = $current_user->is_admin;
        $weekRange = date('M j', strtotime($weekStart)) . ' - ' . date('M j, Y', strtotime($weekEnd));

        // Report status
        $statusLabel = $report->status ?? 'in_progress';

        // CSS and JS
        echo '<link rel="stylesheet" type="text/css" href="custom/themes/lf_dashboard.css">';
        echo '<script src="custom/modules/LF_WeeklyPlan/js/planning.js"></script>';

        // Render sub-header with user selector for admins
        LF_SubHeader::renderCSS();
        LF_SubHeader::renderJS();
        $weekList = WeekHelper::getWeekList(9);
        LF_SubHeader::render('Rep Report', [
            'showUserSelector' => $isAdmin,
            'users' => $allUsers,
            'selectedUserId' => $selectedUserId,
            'showWeekSelector' => true,
            'weekList' => $weekList,
            'currentWeek' => $weekStart,
        ]);

        // Start content wrapper
        echo '<div class="lf-content-wrapper">';

        // Subnav placeholder
        echo '<div id="lf-subnav-placeholder" data-active="rep_report" data-admin="' . ($isAdmin ? 'true' : 'false') . '"></div>';

        echo '<div style="padding: 20px;">';

        // Info bar (mirrors Rep Plan)
        echo '<div class="lf-info-bar" style="background: white; padding: 16px; border-radius: 8px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #edebe9;">';
        echo '<div style="font-size: 16px; font-weight: 600; color: #323130;">' . htmlspecialchars($selectedUserName) . ' &mdash; Report: ' . htmlspecialchars($weekRange) . '</div>';
        echo '<span class="badge lf-status-' . htmlspecialchars($statusLabel) . '">' . htmlspecialchars(ucfirst(str_replace('_', ' ', $statusLabel))) . '</span>';
        echo '</div>';

        // JS data
        echo '<script src="custom/modules/LF_WeeklyPlan/js/rep_reporting.js"></script>';
        echo '<script>';
        echo 'var LF_CSRF_TOKEN = (typeof SUGAR !== "undefined" && SUGAR.csrf) ? SUGAR.csrf.form_token : "";';
        echo 'var LF_SAVE_ENDPOINT = "index.php?module=LF_WeeklyPlan&action=report_save_json";';
        echo '</script>';

        // Summary card - ACTUAL performance based on snapshot vs live stage comparison
        // $snapshotStages already loaded above for unplanned detection
        $summaryTotals = ['closing' => 0, 'at_risk' => 0, 'progression' => 0, 'new_pipeline' => 0];

        // Existing Pipeline actuals: compare snapshot stage vs live stage
        foreach ($existingPipelineItems as $oppId => $item) {
            $opp = $opportunities[$oppId] ?? null;
            if (!$opp) continue;
            $profit = (float)($opp['opportunity_profit'] ?? 0);

            // At Risk uses planned value (it's a flag, not a stage comparison)
            if (!empty($item['is_at_risk'])) {
                $summaryTotals['at_risk'] += $profit;
            }

            // Calculate actuals based on stage movement
            $currentStage = $opp['sales_stage'] ?? '';
            $snapData = $snapshotStages[$oppId] ?? null;
            $snapshotPct = $snapData ? (int)$snapData['stage_pct'] : 0;

            // Get current stage probability
            $currentPct = 0;
            if (preg_match('/\((\d+)%\)/', $currentStage, $m)) {
                $currentPct = (int)$m[1];
            } elseif (in_array($currentStage, ['Closed Won', 'closed_won'])) {
                $currentPct = 100;
            }

            // Closing actual = profit for opps that moved to Closed Won
            if (in_array($currentStage, ['Closed Won', 'closed_won'])) {
                $summaryTotals['closing'] += $profit;
            }

            // Progression actual = Profit × (Current% - Snapshot%) / 100 for opps that progressed
            // Includes both progression AND closing items
            if ($currentPct > $snapshotPct) {
                $summaryTotals['progression'] += $profit * ($currentPct - $snapshotPct) / 100;
            }
        }

        // Developing Pipeline actuals: developing opps that progressed past analysis
        $analysisProb = (int)($probabilities[$analysisStage] ?? 0);
        foreach ($developingItems as $oppId => $item) {
            $opp = $opportunities[$oppId] ?? null;
            if (!$opp) continue;
            $profit = (float)($opp['opportunity_profit'] ?? 0);
            $currentStage = $opp['sales_stage'] ?? '';

            $currentPct = 0;
            if (preg_match('/\((\d+)%\)/', $currentStage, $m)) {
                $currentPct = (int)$m[1];
            } elseif (in_array($currentStage, ['Closed Won', 'closed_won'])) {
                $currentPct = 100;
            }

            // New Pipeline actual = entire profit for developing opps that moved past analysis
            if ($currentPct > $analysisProb) {
                $summaryTotals['new_pipeline'] += $profit;
            }

            // Developing also counts toward Progression: Profit × (Current% - Snapshot%) / 100
            $snapData = $snapshotStages[$oppId] ?? null;
            $snapshotPct = $snapData ? (int)$snapData['stage_pct'] : $analysisProb;
            if ($currentPct > $snapshotPct) {
                $summaryTotals['progression'] += $profit * ($currentPct - $snapshotPct) / 100;
            }
        }

        // Unplanned movers: add to progression (and new_pipeline for developing-level)
        foreach (array_merge($unplannedExisting, $unplannedDeveloping) as $oppId => $snap) {
            $opp = $opportunities[$oppId] ?? null;
            if (!$opp) continue;
            $profit = (float)($opp['opportunity_profit'] ?? 0);
            $currentStage = $opp['sales_stage'] ?? '';

            $currentPct = 0;
            if (preg_match('/\((\d+)%\)/', $currentStage, $m)) {
                $currentPct = (int)$m[1];
            } elseif (in_array($currentStage, ['Closed Won', 'closed_won'])) {
                $currentPct = 100;
            }

            $startStage = $snap['stage_at_week_start'] ?? '';
            $startPct = (int)($probabilities[$startStage] ?? 0);

            if ($currentPct > $startPct) {
                $summaryTotals['progression'] += $profit * ($currentPct - $startPct) / 100;
            }
            // Developing-level unplanned also count toward New Pipeline
            if (isset($unplannedDeveloping[$oppId]) && $currentPct > $analysisProb) {
                $summaryTotals['new_pipeline'] += $profit;
            }
        }

        // Prospecting actuals: add profit from converted prospecting items
        foreach ($prospectItems as $item) {
            if (($item['status'] ?? '') === 'converted' && !empty($item['converted_opportunity_id'])) {
                $convOppId = $item['converted_opportunity_id'];
                $convQuery = "SELECT opportunity_profit FROM opportunities WHERE id = " . $db->quoted($convOppId) . " AND deleted = 0";
                $convResult = $db->query($convQuery);
                $convRow = $db->fetchByAssoc($convResult);
                if ($convRow) {
                    $convProfit = (float)($convRow['opportunity_profit'] ?? 0);
                    $summaryTotals['new_pipeline'] += $convProfit;
                    // Prospecting also counts toward Progression
                    $summaryTotals['progression'] += $convProfit;
                }
            }
        }

        echo '<div class="lf-totals-container" style="display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap;">';
        echo '  <div class="total-box" style="flex: 1; min-width: 150px; background: white; padding: 16px; border-radius: 8px; border: 1px solid #edebe9; text-align: center;">';
        echo '    <div style="font-size: 12px; color: #605e5c; text-transform: uppercase; margin-bottom: 4px;">Closing</div>';
        echo '    <div style="font-size: 20px; font-weight: 700; color: #d13438;">$' . number_format($summaryTotals['closing'], 0) . '</div>';
        echo '  </div>';
        echo '  <div class="total-box" style="flex: 1; min-width: 150px; background: white; padding: 16px; border-radius: 8px; border: 1px solid #edebe9; text-align: center;">';
        echo '    <div style="font-size: 12px; color: #605e5c; text-transform: uppercase; margin-bottom: 4px;">At Risk</div>';
        echo '    <div style="font-size: 20px; font-weight: 700; color: #ff8c00;">$' . number_format($summaryTotals['at_risk'], 0) . '</div>';
        echo '  </div>';
        echo '  <div class="total-box" style="flex: 1; min-width: 150px; background: white; padding: 16px; border-radius: 8px; border: 1px solid #edebe9; text-align: center;">';
        echo '    <div style="font-size: 12px; color: #605e5c; text-transform: uppercase; margin-bottom: 4px;">Progression</div>';
        echo '    <div style="font-size: 20px; font-weight: 700; color: #4BB74E;">$' . number_format($summaryTotals['progression'], 0) . '</div>';
        echo '  </div>';
        echo '  <div class="total-box" style="flex: 1; min-width: 150px; background: white; padding: 16px; border-radius: 8px; border: 1px solid #edebe9; text-align: center;">';
        echo '    <div style="font-size: 12px; color: #605e5c; text-transform: uppercase; margin-bottom: 4px;">New Pipeline</div>';
        echo '    <div style="font-size: 20px; font-weight: 700; color: #125EAD;">$' . number_format($summaryTotals['new_pipeline'], 0) . '</div>';
        echo '  </div>';
        echo '</div>';

        // Container for JS event delegation
        echo '<div id="lf-report-container">';

        // ============================================================
        // SECTION 1: EXISTING PIPELINE
        // ============================================================
        echo '<h2>Existing Pipeline</h2>';
        echo '<table id="existing-pipeline-table" class="list view table-responsive">';
        echo '<thead><tr>';
        echo '<th>Account</th>';
        echo '<th>Opportunity</th>';
        echo '<th>Revenue</th>';
        echo '<th>Profit</th>';
        echo '<th>Planned Stage</th>';
        echo '<th>Current Stage</th>';
        echo '<th>Result</th>';
        echo '<th>Notes</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        // Planned existing pipeline items
        foreach ($existingPipelineItems as $oppId => $item) {
            $opp = $opportunities[$oppId] ?? null;
            if (!$opp) continue;
            $snap = $snapshots[$oppId] ?? null;
            // Use opportunity_weekly_snapshot as baseline if report snapshot missing
            $weeklySnap = $snapshotStages[$oppId] ?? null;
            $this->renderPipelineRow($opp, $item, $snap, $accountNames[$oppId] ?? '', $probabilities, false, $weeklySnap);
        }

        // Unplanned existing pipeline movers
        foreach ($unplannedExisting as $oppId => $snap) {
            $opp = $opportunities[$oppId] ?? null;
            if (!$opp) continue;
            $weeklySnap = $snapshotStages[$oppId] ?? null;
            $this->renderPipelineRow($opp, null, $snap, $accountNames[$oppId] ?? '', $probabilities, true, $weeklySnap);
        }

        if (empty($existingPipelineItems) && empty($unplannedExisting)) {
            echo '<tr><td colspan="8" style="text-align: center; padding: 20px; color: #605e5c;">No existing pipeline items for this week.</td></tr>';
        }

        echo '</tbody></table>';

        // ============================================================
        // SECTION 2: DEVELOPING PIPELINE
        // ============================================================
        echo '<h2>Developing Pipeline</h2>';
        echo '<table id="developing-pipeline-table" class="list view table-responsive">';
        echo '<thead><tr>';
        echo '<th>Account</th>';
        echo '<th>Opportunity</th>';
        echo '<th>Revenue</th>';
        echo '<th>Profit</th>';
        echo '<th>Planned Stage</th>';
        echo '<th>Current Stage</th>';
        echo '<th>Result</th>';
        echo '<th>Notes</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        foreach ($developingItems as $oppId => $item) {
            $opp = $opportunities[$oppId] ?? null;
            if (!$opp) continue;
            $snap = $snapshots[$oppId] ?? null;
            $weeklySnap = $snapshotStages[$oppId] ?? null;
            $this->renderPipelineRow($opp, $item, $snap, $accountNames[$oppId] ?? '', $probabilities, false, $weeklySnap);
        }

        foreach ($unplannedDeveloping as $oppId => $snap) {
            $opp = $opportunities[$oppId] ?? null;
            if (!$opp) continue;
            $weeklySnap = $snapshotStages[$oppId] ?? null;
            $this->renderPipelineRow($opp, null, $snap, $accountNames[$oppId] ?? '', $probabilities, true, $weeklySnap);
        }

        if (empty($developingItems) && empty($unplannedDeveloping)) {
            echo '<tr><td colspan="8" style="text-align: center; padding: 20px; color: #605e5c;">No developing pipeline items for this week.</td></tr>';
        }

        echo '</tbody></table>';

        // ============================================================
        // SECTION 3: PROSPECTING
        // ============================================================
        echo '<h2>Prospecting</h2>';
        echo '<table id="prospecting-table" class="list view table-responsive">';
        echo '<thead><tr>';
        echo '<th>Source Type</th>';
        echo '<th>Day</th>';
        echo '<th>Expected Revenue</th>';
        echo '<th>Expected Profit</th>';
        echo '<th>Description</th>';
        echo '<th>Status</th>';
        echo '<th>Action</th>';
        echo '<th>Notes</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        if (empty($prospectItems)) {
            echo '<tr><td colspan="8" style="text-align: center; padding: 20px; color: #605e5c;">No prospecting items planned for this week.</td></tr>';
        } else {
            foreach ($prospectItems as $item) {
                $expectedRevenue = (float)($item['expected_revenue'] ?? $item['expected_value'] ?? 0);
                $expectedProfit = (float)($item['expected_profit'] ?? 0);
                echo '<tr data-prospect-id="' . htmlspecialchars($item['id']) . '">';
                echo '<td>' . htmlspecialchars($item['source_type']) . '</td>';
                echo '<td>' . htmlspecialchars(ucfirst($item['planned_day'] ?? '')) . '</td>';
                echo '<td>$' . number_format($expectedRevenue, 0) . '</td>';
                echo '<td>$' . number_format($expectedProfit, 0) . '</td>';
                echo '<td>' . htmlspecialchars($item['plan_description']) . '</td>';

                // Status
                $status = $item['status'] ?? 'planned';
                $statusBadge = $this->getStatusBadge($status);
                echo '<td>' . $statusBadge . '</td>';

                // Action
                echo '<td class="prospect-action-cell">';
                if ($status === 'planned') {
                    echo '<button type="button" class="button convert-prospect-btn">Create Opportunity</button>';
                    echo ' <label style="margin-left: 8px;"><input type="checkbox" class="no-opportunity-chk"> No Opportunity</label>';
                } elseif ($status === 'converted' && !empty($item['converted_opportunity_id'])) {
                    echo '<a href="javascript:void(0)" onclick="window.top.location.href=\'/#/opportunities/record/' . htmlspecialchars($item['converted_opportunity_id']) . '\'">View Opportunity</a>';
                } elseif ($status === 'converted') {
                    echo 'Converted';
                } elseif ($status === 'no_opportunity') {
                    echo 'No Opportunity';
                }
                echo '</td>';

                // Notes
                echo '<td>';
                echo '<textarea class="prospect-notes-textarea" data-prospect-id="' . htmlspecialchars($item['id']) . '" '
                     . 'style="width: 100%; min-width: 120px; height: 60px; box-sizing: border-box;">'
                     . htmlspecialchars($item['prospecting_notes'] ?? '') . '</textarea>';
                echo '</td>';
                echo '</tr>';

                // Inline conversion form (hidden by default)
                if ($status === 'planned') {
                    echo '<tr class="conversion-form-row" style="display: none;" data-prospect-id="' . htmlspecialchars($item['id']) . '">';
                    echo '<td colspan="8">';
                    echo '<div style="background-color: #f9f9f9; padding: 12px; border: 1px solid #ddd; border-radius: 4px;">';
                    echo '<h4 style="margin: 0 0 8px;">Create Opportunity</h4>';
                    echo '<div style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">';
                    echo '<label>Account Name:<br><input type="text" class="conv-account-name" style="width: 200px;"></label>';
                    echo '<label>Opportunity Name:<br><input type="text" class="conv-opp-name" style="width: 200px;"></label>';
                    $convRevenue = (int)(float)($item['expected_revenue'] ?? $item['expected_value'] ?? 0);
                    $convProfit = (int)(float)($item['expected_profit'] ?? 0);
                    echo '<label>Revenue:<br><input type="number" class="conv-amount" value="' . $convRevenue . '" style="width: 120px;"></label>';
                    echo '<label>Profit:<br><input type="number" class="conv-profit" value="' . $convProfit . '" style="width: 120px;"></label>';
                    echo '<button type="button" class="button do-convert-btn">Create</button>';
                    echo '<button type="button" class="button cancel-convert-btn">Cancel</button>';
                    echo '</div>';
                    echo '</div>';
                    echo '</td>';
                    echo '</tr>';
                }
            }
        }

        echo '</tbody></table>';

        // Action Buttons
        echo '<div class="lf-planning-actions" style="margin-top: 30px; text-align: right;">';
        echo '<button type="button" id="updates-complete" class="button primary" style="padding: 10px 20px; font-size: 16px;">Updates Complete</button>';
        echo '<div id="submit-message" class="lf-message" style="margin-top: 10px;"></div>';
        echo '</div>';

        echo '</div>'; // end #lf-report-container
        echo '</div>'; // end padding div
        echo '</div>'; // end .lf-content-wrapper
    }

    /**
     * Render a single pipeline row (used for both existing and developing sections)
     */
    private function renderPipelineRow($opp, $planItem, $snapshot, $accountName, $probabilities, $isUnplanned, $weeklySnap = null)
    {
        $oppId = $opp['id'];
        $currentStage = $opp['sales_stage'] ?? '';
        $amount = (float)($opp['amount'] ?? 0);
        $profit = (float)($opp['opportunity_profit'] ?? 0);

        $plannedStage = '';
        $startStage = '';

        if ($planItem) {
            $plannedStage = $planItem['projected_stage'] ?? '';
        }
        if ($snapshot && !empty($snapshot['stage_at_week_start'])) {
            $startStage = $snapshot['stage_at_week_start'];
        } elseif ($weeklySnap) {
            // Fall back to opportunity_weekly_snapshot as baseline
            $startStage = $weeklySnap['stage_name'] ?? '';
        }

        // Detect result
        $result = $this->detectResult($startStage, $currentStage, $plannedStage, $probabilities, $isUnplanned);
        $resultBadge = $this->getResultBadge($result);

        // Row styling
        $rowStyle = '';
        if ($result === 'regressed' || $result === 'closed_lost') {
            $rowStyle = ' style="background-color: #fff3cd;"';
        } elseif ($result === 'success' || $result === 'closed_won') {
            $rowStyle = ' style="background-color: #e8f5e9;"';
        } elseif ($isUnplanned) {
            $rowStyle = ' style="background-color: #e3f2fd;"';
        }

        $snapId = $snapshot['id'] ?? '';

        echo '<tr' . $rowStyle . ' data-opportunity-id="' . htmlspecialchars($oppId) . '">';
        echo '<td>' . htmlspecialchars($accountName) . '</td>';
        echo '<td><a href="javascript:void(0)" onclick="window.top.location.href=\'/#/opportunities/record/' . htmlspecialchars($oppId) . '\'">' . htmlspecialchars($opp['name']) . '</a></td>';
        echo '<td>$' . number_format($amount, 0) . '</td>';
        echo '<td>$' . number_format($profit, 0) . '</td>';

        // Planned Stage column
        if ($isUnplanned) {
            echo '<td><span class="lf-badge" style="background-color: #0078d4; color: white;">Unplanned</span></td>';
        } else {
            echo '<td>' . htmlspecialchars($plannedStage) . '</td>';
        }

        echo '<td>' . htmlspecialchars($currentStage) . '</td>';
        echo '<td>' . $resultBadge . '</td>';

        // Notes textarea (auto-saves)
        echo '<td>';
        echo '<textarea class="result-description-textarea" data-snapshot-id="' . htmlspecialchars($snapId) . '" '
             . 'style="width: 100%; min-width: 120px; height: 60px; box-sizing: border-box;">'
             . htmlspecialchars($snapshot['result_description'] ?? '') . '</textarea>';
        echo '</td>';

        echo '</tr>';
    }

    /**
     * Detect the result for a planned or unplanned opportunity
     */
    private function detectResult($startStage, $currentStage, $plannedStage, $probabilities, $isUnplanned)
    {
        // Closed Won/Lost are special
        if (in_array($currentStage, ['Closed Won', 'closed_won'])) {
            return 'closed_won';
        }
        if (in_array($currentStage, ['Closed Lost', 'closed_lost'])) {
            return 'closed_lost';
        }

        $startProb = (int)($probabilities[$startStage] ?? 0);
        $currentProb = (int)($probabilities[$currentStage] ?? 0);

        if ($isUnplanned) {
            // Unplanned: show as bonus if progressed
            if ($currentProb > $startProb) return 'unplanned';
            if ($currentProb < $startProb) return 'regressed';
            return 'no_change';
        }

        // Planned: compare against projected stage
        $plannedProb = (int)($probabilities[$plannedStage] ?? 0);

        if ($currentProb >= $plannedProb && $plannedProb > 0) {
            return 'success';
        } elseif ($currentProb > $startProb && $currentProb < $plannedProb) {
            return 'partial';
        } elseif ($currentProb < $startProb) {
            return 'regressed';
        } else {
            return 'no_change';
        }
    }

    /**
     * Generate HTML badge for a result
     */
    private function getResultBadge($result)
    {
        $badges = [
            'success'    => ['label' => 'Success',    'bg' => '#2F7D32', 'color' => 'white'],
            'partial'    => ['label' => 'Partial',    'bg' => '#E6C300', 'color' => '#323130'],
            'no_change'  => ['label' => 'No Change',  'bg' => '#a0a0a0', 'color' => 'white'],
            'regressed'  => ['label' => 'Regressed',  'bg' => '#d13438', 'color' => 'white'],
            'closed_won' => ['label' => 'Closed Won', 'bg' => '#2F7D32', 'color' => 'white'],
            'closed_lost'=> ['label' => 'Closed Lost','bg' => '#d13438', 'color' => 'white'],
            'progressed' => ['label' => 'Progressed', 'bg' => '#0078d4', 'color' => 'white'],
            'unplanned'  => ['label' => 'Unplanned',  'bg' => '#0078d4', 'color' => 'white'],
        ];

        $badge = $badges[$result] ?? ['label' => $result, 'bg' => '#a0a0a0', 'color' => 'white'];
        return '<span class="lf-badge" style="background-color: ' . $badge['bg'] . '; color: ' . $badge['color'] . '; padding: 3px 8px; border-radius: 3px; font-size: 12px;">'
             . htmlspecialchars($badge['label']) . '</span>';
    }

    /**
     * Generate HTML badge for prospect status
     */
    private function getStatusBadge($status)
    {
        $badges = [
            'planned'        => ['label' => 'Planned',        'bg' => '#0078d4', 'color' => 'white'],
            'converted'      => ['label' => 'Converted',      'bg' => '#2F7D32', 'color' => 'white'],
            'no_opportunity' => ['label' => 'No Opportunity',  'bg' => '#a0a0a0', 'color' => 'white'],
        ];

        $badge = $badges[$status] ?? ['label' => ucfirst($status), 'bg' => '#a0a0a0', 'color' => 'white'];
        return '<span class="lf-badge" style="background-color: ' . $badge['bg'] . '; color: ' . $badge['color'] . '; padding: 3px 8px; border-radius: 3px; font-size: 12px;">'
             . htmlspecialchars($badge['label']) . '</span>';
    }
}
