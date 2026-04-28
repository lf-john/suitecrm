<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/MVC/View/SugarView.php');
require_once('custom/include/LF_PlanningReporting/WeekHelper.php');
require_once('custom/include/LF_PlanningReporting/OpportunityQuery.php');
require_once('custom/include/LF_PlanningReporting/LF_SubHeader.php');
require_once('custom/modules/LF_WeeklyPlan/LF_WeeklyPlan.php');
require_once('custom/modules/LF_PRConfig/LF_PRConfig.php');
require_once('custom/modules/LF_PlanOpItem/LF_PlanOpItem.php');

#[\AllowDynamicProperties]
class LF_WeeklyPlanViewPlanning extends SugarView
{
    public function __construct()
    {
        parent::__construct();
        $this->options['show_header'] = true;
        $this->options['show_footer'] = false;  // Disable footer to prevent Reset Password modal
    }

    public function display()
    {
        global $current_user, $app_list_strings;
        $db = DBManagerFactory::getInstance();

        // Allow admin to select which user to view
        $selectedUserId = $current_user->id;
        $selectedUserName = $current_user->full_name;
        if ($current_user->is_admin && !empty($_REQUEST['rep_id'])) {
            $selectedUserId = $_REQUEST['rep_id'];
            // Get the selected user's name
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
        $weekRange = WeekHelper::formatWeekRange($weekStart);
        $isCurrentWeek = WeekHelper::isCurrentWeek($weekStart);
        // Always get-or-create so admin viewing another rep sees the full plan UI
        $plan = LF_WeeklyPlan::getOrCreateForWeek($selectedUserId, $weekStart);

        // M2: Read analysis stage from config instead of hard-coding
        $analysisStage = LF_PRConfig::getConfig('stages', 'analysis_stage') ?: '2-Analysis (0%)';

        // Check if a snapshot exists for this week
        $weekEndAt = OpportunityQuery::getSnapshotWeekEndAt($weekStart);
        $hasSnapshot = OpportunityQuery::hasSnapshot($weekEndAt);

        // Load opportunities — from snapshot if available, live data for current week, empty for past weeks without snapshot
        if ($hasSnapshot) {
            $pipelineOpps = OpportunityQuery::getSnapshotPipelineOpportunities($weekEndAt, $selectedUserId, $analysisStage);
            $devPipelineOpps = OpportunityQuery::getSnapshotAnalysisOpportunities($weekEndAt, $selectedUserId, $analysisStage);

            // Snapshot rows have account_id but no account_name — pre-fetch so sort works
            $allSnapshotAccountIds = array_filter(array_unique(array_merge(
                array_column($pipelineOpps, 'account_id'),
                array_column($devPipelineOpps, 'account_id')
            )));
            $snapshotAccountNames = [];
            if (!empty($allSnapshotAccountIds)) {
                $idList = implode(',', array_map([$db, 'quoted'], $allSnapshotAccountIds));
                $acctRes = $db->query("SELECT id, name FROM accounts WHERE id IN ($idList) AND deleted = 0");
                while ($acctRow = $db->fetchByAssoc($acctRes)) {
                    $snapshotAccountNames[$acctRow['id']] = html_entity_decode($acctRow['name'], ENT_QUOTES | ENT_HTML5);
                }
            }
            foreach ($pipelineOpps as &$opp) {
                $opp['account_name'] = $snapshotAccountNames[$opp['account_id'] ?? ''] ?? 'No Account';
            }
            unset($opp);
            foreach ($devPipelineOpps as &$opp) {
                $opp['account_name'] = $snapshotAccountNames[$opp['account_id'] ?? ''] ?? 'No Account';
            }
            unset($opp);
        } elseif ($isCurrentWeek) {
            // Current week with no snapshot yet — use live data
            $allOpenOpps = OpportunityQuery::getOpenOpportunities($selectedUserId);
            $pipelineOpps = [];
            foreach ($allOpenOpps as $opp) {
                if (strpos($opp['sales_stage'], $analysisStage) === false) {
                    $pipelineOpps[] = $opp;
                }
            }
            $devPipelineOpps = OpportunityQuery::getAnalysisOpportunities($selectedUserId);
        } else {
            // Past/future week with no snapshot — fall back to current live data
            $allOpenOpps = OpportunityQuery::getOpenOpportunities($selectedUserId);
            $pipelineOpps = [];
            foreach ($allOpenOpps as $opp) {
                if (strpos($opp['sales_stage'], $analysisStage) === false) {
                    $pipelineOpps[] = $opp;
                }
            }
            $devPipelineOpps = OpportunityQuery::getAnalysisOpportunities($selectedUserId);
        }

        // For live data paths, batch-resolve account names so the pre-render sort works
        if (!$hasSnapshot) {
            $liveOppIds = array_merge(
                array_column($pipelineOpps, 'id'),
                array_column($devPipelineOpps, 'id')
            );
            if (!empty($liveOppIds)) {
                $idList = implode(',', array_map([$db, 'quoted'], $liveOppIds));
                $acctRes = $db->query(
                    "SELECT ao.opportunity_id, a.name
                     FROM accounts_opportunities ao
                     JOIN accounts a ON ao.account_id = a.id
                     WHERE ao.opportunity_id IN ($idList) AND ao.deleted = 0 AND a.deleted = 0"
                );
                $liveAccountNames = [];
                while ($acctRow = $db->fetchByAssoc($acctRes)) {
                    $liveAccountNames[$acctRow['opportunity_id']] = html_entity_decode($acctRow['name'], ENT_QUOTES | ENT_HTML5);
                }
                foreach ($pipelineOpps as &$opp) {
                    $opp['account_name'] = $liveAccountNames[$opp['id']] ?? '';
                }
                unset($opp);
                foreach ($devPipelineOpps as &$opp) {
                    $opp['account_name'] = $liveAccountNames[$opp['id']] ?? '';
                }
                unset($opp);
            }
        }

        // Load existing plan items
        $planItems = [];
        $query = sprintf(
            "SELECT * FROM lf_plan_op_items WHERE lf_weekly_plan_id = %s AND deleted = 0",
            $db->quoted($plan->id)
        );
        $result = $db->query($query);
        while ($row = $db->fetchByAssoc($result)) {
            $planItems[$row['opportunity_id']] = $row;
        }

        // Get stage config - combine SuiteCRM defaults with actual stages from opportunities
        global $app_list_strings;
        $defaultStages = $app_list_strings['sales_stage_dom'] ?? [];
        $defaultProbs = $app_list_strings['sales_probability_dom'] ?? [];

        // Get all distinct stages actually used in opportunities
        $stageQuery = "SELECT DISTINCT sales_stage FROM opportunities WHERE deleted = 0 AND sales_stage IS NOT NULL AND sales_stage != ''";
        $stageResult = $db->query($stageQuery);
        $actualStages = [];
        while ($row = $db->fetchByAssoc($stageResult)) {
            $actualStages[] = $row['sales_stage'];
        }

        // Build stage probabilities - extract from stage name or use defaults
        $stageProbabilities = [];
        $stageOrder = [];
        foreach ($actualStages as $stage) {
            // Try to extract probability from stage name like "5-Specifications (30%)"
            if (preg_match('/\((\d+)%\)/', $stage, $matches)) {
                $stageProbabilities[$stage] = (int)$matches[1];
            } elseif (isset($defaultProbs[$stage])) {
                $stageProbabilities[$stage] = (int)$defaultProbs[$stage];
            } else {
                // Default probability based on common patterns
                if (stripos($stage, 'closed_won') !== false || stripos($stage, 'Closed Won') !== false) {
                    $stageProbabilities[$stage] = 100;
                } elseif (stripos($stage, 'closed') !== false) {
                    $stageProbabilities[$stage] = 0;
                } else {
                    $stageProbabilities[$stage] = 50; // Default middle probability
                }
            }

            // Only include non-closed stages in order (for dropdown)
            if (stripos($stage, 'closed') === false) {
                $stageOrder[] = $stage;
            }
        }

        // Sort stages by probability
        usort($stageOrder, function($a, $b) use ($stageProbabilities) {
            return ($stageProbabilities[$a] ?? 0) - ($stageProbabilities[$b] ?? 0);
        });

        // Add Closed Won at the end (use actual DB stage name if it exists, otherwise title case)
        $closedWonLabel = in_array('closed_won', $actualStages) ? 'closed_won' : 'Closed Won';
        $stageOrder[] = $closedWonLabel;
        $stageProbabilities[$closedWonLabel] = 100;
        // Ensure both variants are recognized
        $stageProbabilities['Closed Won'] = 100;
        $stageProbabilities['closed_won'] = 100;

        // Get weekly targets for color coding
        $weeklyTargets = [
            'closing' => (float)LF_PRConfig::getConfig('targets', 'default_closed_target'),
            'new_pipeline' => (float)LF_PRConfig::getConfig('targets', 'default_new_pipeline_target'),
            'progression' => (float)LF_PRConfig::getConfig('targets', 'default_progression_target'),
        ];

        // Get health data
        // C1: getClosedYTD requires year as first param, returns array with 'total_amount'
        $closedYtdData = OpportunityQuery::getClosedYTD(date('Y'), $selectedUserId);
        $closedYtdAmount = (float)($closedYtdData['total_amount'] ?? 0);
        $annualQuota = (float)LF_PRConfig::getConfig('quotas', 'default_annual_quota');
        // M4: Filter rep target by fiscal year and active status
        // Use direct query instead of deprecated retrieve_by_string_fields()
        $repTargetQuery = sprintf(
            "SELECT id, annual_quota FROM lf_rep_targets
             WHERE assigned_user_id = %s
               AND fiscal_year = %s
               AND is_active = 1
               AND deleted = 0
             LIMIT 1",
            $db->quoted($selectedUserId),
            $db->quoted(date('Y'))
        );
        $repTargetResult = $db->query($repTargetQuery);
        $repTargetRow = $db->fetchByAssoc($repTargetResult);
        if ($repTargetRow && !empty($repTargetRow['annual_quota'])) {
            $annualQuota = (float)$repTargetRow['annual_quota'];
        }
        // C2: Config category is 'quotas', not 'pipeline'
        $coverageMultiplier = (float)LF_PRConfig::getConfig('quotas', 'pipeline_coverage_multiplier') ?: 4.0;

        // Get all active users for admin user selector
        $allUsers = [];
        if ($current_user->is_admin) {
            $usersQuery = "SELECT id, first_name, last_name FROM users WHERE deleted = 0 AND status = 'Active' ORDER BY last_name, first_name";
            $usersResult = $db->query($usersQuery);
            while ($row = $db->fetchByAssoc($usersResult)) {
                $allUsers[] = $row;
            }
        }

        // Include CSS and JS
        echo '<link rel="stylesheet" href="custom/themes/lf_dashboard.css">';
        echo '<script src="custom/modules/LF_WeeklyPlan/js/planning.js?v=2"></script>';

        // Render sub-header CSS and JS
        LF_SubHeader::renderCSS();
        LF_SubHeader::renderJS();

        // Render SuiteCRM-style sub-header with user selector and week navigation
        $weekList = WeekHelper::getWeekList(9);
        LF_SubHeader::render('Sales Rep Planning', [
            'showUserSelector' => $current_user->is_admin,
            'users' => $allUsers,
            'selectedUserId' => $selectedUserId,
            'showWeekSelector' => true,
            'weekList' => $weekList,
            'currentWeek' => $weekStart,
        ]);

        // Start content wrapper
        echo '<div class="lf-content-wrapper">';

        // Placeholder for JS-injected subnav
        $isAdmin = $current_user->is_admin ? 'true' : 'false';
        echo '<div id="lf-subnav-placeholder" data-active="planning" data-admin="' . $isAdmin . '"></div>';

        echo '<div class="lf-planning-wrapper" style="padding: 24px; max-width: 1400px;">';
        echo '<script>';
        echo 'var LF_PLAN_ID = ' . json_encode($plan->id) . ';';
        echo 'var LF_STAGE_PROBS = ' . json_encode($stageProbabilities) . ';';
        echo 'var stageProbabilities = ' . json_encode($stageProbabilities) . ';';
        echo 'var LF_WEEKLY_TARGETS = ' . json_encode($weeklyTargets) . ';';
        echo 'var LF_HEALTH_DATA = ' . json_encode([
            'closed_ytd' => $closedYtdAmount,
            'annual_quota' => $annualQuota,
            'coverage_multiplier' => $coverageMultiplier
        ]) . ';';
        // Generate and store CSRF token in session
        if (empty($_SESSION['lf_csrf_token'])) {
            $_SESSION['lf_csrf_token'] = bin2hex(random_bytes(32));
        }
        echo 'var LF_CSRF_TOKEN = ' . json_encode($_SESSION['lf_csrf_token']) . ';';
        $isOwnPlan = ($current_user->id === $selectedUserId);
        echo 'var LF_IS_OWN_PLAN = ' . ($isOwnPlan ? 'true' : 'false') . ';';
        echo '</script>';

        // Week and status info bar
        $statusLabel = isset($app_list_strings['lf_plan_status_dom'][$plan->status])
            ? $app_list_strings['lf_plan_status_dom'][$plan->status]
            : $plan->status;

        echo '<div class="lf-info-bar" style="background: white; padding: 16px; border-radius: 8px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #edebe9;">';
        echo '<div style="font-size: 16px; font-weight: 600; color: #323130;">' . htmlspecialchars($selectedUserName) . ' &mdash; ' . htmlspecialchars($weekRange) . '</div>';
        echo '<div style="display: flex; align-items: center; gap: 12px;">';
        if ($hasSnapshot) {
            $snapshotTime = LF_PRConfig::getConfig('weeks', 'snapshot_time') ?: '09:00';
            echo '<span style="background: #e8f5e9; color: #2e7d32; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 600;">Snapshot: ' . htmlspecialchars($weekStart) . ' ' . htmlspecialchars($snapshotTime) . ' MT</span>';
        } elseif (!$isCurrentWeek) {
            echo '<span style="background: #f5f5f5; color: #666; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 600;">No snapshot available</span>';
        }
        echo '<span class="badge lf-status-' . htmlspecialchars($plan->status) . '">' . htmlspecialchars($statusLabel) . '</span>';
        echo '</div>';
        echo '</div>';

        // Container for JS event delegation
        echo '<div id="lf-planning-container" data-plan-id="' . htmlspecialchars($plan->id) . '" data-plan-status="' . htmlspecialchars($plan->status) . '">';

        // Totals Row — use frozen values when plan is submitted
        // Bypass SugarBean vardefs cache (Redis + file cache issues) — load frozen values directly from DB
        $frozenClosing = 0;
        $frozenProgression = 0;
        $frozenNewPipeline = 0;
        if ($plan->status === 'submitted') {
            $frozenQuery = sprintf(
                "SELECT frozen_closing, frozen_progression, frozen_new_pipeline FROM lf_weekly_plan WHERE id = %s AND deleted = 0",
                $db->quoted($plan->id)
            );
            $frozenResult = $db->query($frozenQuery);
            $frozenRow = $db->fetchByAssoc($frozenResult);
            if ($frozenRow) {
                $frozenClosing = (float)($frozenRow['frozen_closing'] ?? 0);
                $frozenProgression = (float)($frozenRow['frozen_progression'] ?? 0);
                $frozenNewPipeline = (float)($frozenRow['frozen_new_pipeline'] ?? 0);
            }
        }
        $useFrozen = ($plan->status === 'submitted' && ($frozenClosing > 0 || $frozenProgression > 0 || $frozenNewPipeline > 0));

        // Calculate At Risk total from plan items (not a frozen value — calculated from is_at_risk flags)
        $atRiskTotal = 0;
        if ($plan->status === 'submitted') {
            $atRiskQuery = sprintf(
                "SELECT SUM(COALESCE(s.profit, o.opportunity_profit, 0)) AS at_risk_total
                 FROM lf_plan_op_items poi
                 LEFT JOIN opportunities o ON poi.opportunity_id = o.id AND o.deleted = 0
                 LEFT JOIN opportunity_weekly_snapshot s ON s.opportunity_id = poi.opportunity_id
                     AND s.week_end_at = %s AND s.deleted = 0
                 WHERE poi.lf_weekly_plan_id = %s AND poi.deleted = 0 AND poi.is_at_risk = 1",
                $db->quoted($weekEndAt),
                $db->quoted($plan->id)
            );
            $atRiskResult = $db->query($atRiskQuery);
            $atRiskRow = $db->fetchByAssoc($atRiskResult);
            $atRiskTotal = (float)($atRiskRow['at_risk_total'] ?? 0);
        }

        echo '<div id="totals-row" class="lf-totals-container">';
        if ($useFrozen) {
            echo '  <div id="total-closing-box" class="total-box">Closing: <span id="total-closing" data-value="' . $frozenClosing . '">$' . number_format($frozenClosing, 0) . '</span></div>';
            echo '  <div id="total-at-risk-box" class="total-box">At Risk: <span id="total-at-risk" data-value="' . $atRiskTotal . '">$' . number_format($atRiskTotal, 0) . '</span></div>';
            echo '  <div id="total-progression-box" class="total-box">Progression: <span id="total-progression" data-value="' . $frozenProgression . '">$' . number_format($frozenProgression, 0) . '</span></div>';
            echo '  <div id="total-new-pipeline-box" class="total-box">New Pipeline: <span id="total-new-pipeline" data-value="' . $frozenNewPipeline . '">$' . number_format($frozenNewPipeline, 0) . '</span></div>';
        } else {
            echo '  <div id="total-closing-box" class="total-box">Closing: <span id="total-closing" data-value="0">0</span></div>';
            echo '  <div id="total-at-risk-box" class="total-box">At Risk: <span id="total-at-risk" data-value="0">0</span></div>';
            echo '  <div id="total-progression-box" class="total-box">Progression: <span id="total-progression" data-value="0">0</span></div>';
            echo '  <div id="total-new-pipeline-box" class="total-box">New Pipeline: <span id="total-new-pipeline" data-value="0">0</span></div>';
        }
        echo '</div>';

        // Sort both pipeline arrays by account name A→Z
        usort($pipelineOpps, function($a, $b) {
            return strcasecmp($a['account_name'] ?? '', $b['account_name'] ?? '');
        });
        usort($devPipelineOpps, function($a, $b) {
            return strcasecmp($a['account_name'] ?? '', $b['account_name'] ?? '');
        });

        // Existing Pipeline Table
        echo '<h2>Existing Pipeline</h2>';
        echo '<table id="pipeline-table" class="list view table-responsive">';
        echo '<thead><tr>';
        echo '<th>Account</th>';
        echo '<th>Opportunity</th>';
        echo '<th>Revenue</th>';
        echo '<th>Profit</th>';
        echo '<th>Current Stage</th>';
        echo '<th>Projected Stage</th>';
        echo '<th>Progression</th>';
        echo '<th>Category</th>';
        echo '<th>At Risk</th>';
        echo '<th>Day</th>';
        echo '<th>Plan</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        foreach ($pipelineOpps as $opp) {
            $oppId = $opp['id'];
            $item = isset($planItems[$oppId]) ? $planItems[$oppId] : null;

            // Get account name — pre-fetched into $opp['account_name'] for snapshot weeks
            if (!empty($opp['account_name'])) {
                $accountName = $opp['account_name'];
            } elseif ($hasSnapshot && !empty($opp['account_id'])) {
                $acctQuery = "SELECT name FROM accounts WHERE id = " . $db->quoted($opp['account_id']) . " AND deleted = 0";
                $acctResult = $db->query($acctQuery);
                $acctRow = $db->fetchByAssoc($acctResult);
                $accountName = $acctRow ? html_entity_decode($acctRow['name'], ENT_QUOTES | ENT_HTML5) : 'No Account';
            } else {
                $oppBean = BeanFactory::getBean('Opportunities', $oppId);
                if (!$oppBean || empty($oppBean->id)) {
                    continue;
                }
                $accountName = $oppBean->account_name;
            }
            $profit = isset($opp['opportunity_profit']) ? (float)$opp['opportunity_profit'] : 0;

            echo '<tr data-opportunity-id="' . htmlspecialchars($oppId) . '">';
            echo '<td>' . htmlspecialchars($accountName) . '</td>';
            echo '<td><a href="javascript:void(0)" onclick="window.top.location.href=\'/#/opportunities/record/' . htmlspecialchars($oppId) . '\'">' . htmlspecialchars($opp['name']) . '</a></td>';
            echo '<td class="amount" data-amount="' . htmlspecialchars($opp['amount']) . '">' . htmlspecialchars(number_format($opp['amount'], 0)) . '</td>';
            echo '<td class="profit" data-profit="' . htmlspecialchars($profit) . '">' . htmlspecialchars(number_format($profit, 0)) . '</td>';
            // Get current stage probability
            $currentStage = $opp['sales_stage'];
            $currentProb = isset($stageProbabilities[$currentStage]) ? (int)$stageProbabilities[$currentStage] : 0;
            echo '<td class="current-stage" data-stage="' . htmlspecialchars($currentStage) . '" data-prob="' . $currentProb . '">' . htmlspecialchars($currentStage) . '</td>';

            // Projected Stage Dropdown - show stages with higher probability than current
            $savedProjectedStage = $item ? ($item['projected_stage'] ?? '') : '';
            if ($plan->status === 'submitted') {
                // Read-only: show static text
                $displayStage = $savedProjectedStage ?: 'None';
                echo '<td>' . htmlspecialchars($displayStage) . '</td>';
            } else {
                echo '<td><select name="projected_stage[' . htmlspecialchars($oppId) . ']" class="projected-stage-select" data-opp-id="' . htmlspecialchars($oppId) . '">';
                echo '<option value="">-- Select --</option>';
                foreach ($stageOrder as $stage) {
                    $stageProb = isset($stageProbabilities[$stage]) ? (int)$stageProbabilities[$stage] : 0;
                    if ($stageProb > $currentProb) {
                        $selected = ($savedProjectedStage === $stage) ? ' selected' : '';
                        echo '<option value="' . htmlspecialchars($stage) . '"' . $selected . '>' . htmlspecialchars($stage) . '</option>';
                    }
                }
                echo '</select></td>';
            }

            // Progression value column (calculated by JS, shows per-row progression)
            echo '<td class="pipeline-progression" data-value="0">0</td>';

            // Category - Auto-calculated display (read-only)
            // Logic: Closing (100%), Progression (>=10% to <100%), New (1% to higher)
            $category = '--';
            $categoryClass = '';
            if ($savedProjectedStage) {
                $projectedProb = isset($stageProbabilities[$savedProjectedStage]) ? (int)$stageProbabilities[$savedProjectedStage] : 0;
                if ($projectedProb >= 100) {
                    $category = 'Closing';
                    $categoryClass = 'category-closing';
                } elseif ($currentProb >= 10 && $projectedProb > $currentProb) {
                    $category = 'Progression';
                    $categoryClass = 'category-progression';
                } elseif ($currentProb <= 1 && $projectedProb > $currentProb) {
                    $category = 'New';
                    $categoryClass = 'category-new';
                }
            }
            $savedCategory = $item ? ($item['item_type'] ?? '') : '';
            echo '<td class="category-cell ' . $categoryClass . '" data-category="' . htmlspecialchars($savedCategory ?: strtolower($category)) . '">';
            echo '<span class="category-display">' . htmlspecialchars($category) . '</span>';
            echo '<input type="hidden" name="category[' . htmlspecialchars($oppId) . ']" class="category-value" value="' . htmlspecialchars($savedCategory ?: strtolower($category)) . '">';
            echo '</td>';

            // At Risk Checkbox
            $isAtRisk = $item && !empty($item['is_at_risk']);
            $atRiskChecked = $isAtRisk ? ' checked' : '';
            echo '<td class="at-risk-cell"><input type="checkbox" name="at_risk[' . htmlspecialchars($oppId) . ']" class="at-risk-checkbox" value="1"' . $atRiskChecked . '></td>';

            // Day Dropdown
            echo '<td><select name="day[' . htmlspecialchars($oppId) . ']" class="day-select">';
            $days = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday'];
            foreach ($days as $val => $lbl) {
                $selected = ($item && $item['planned_day'] == $val) ? ' selected' : '';
                echo '<option value="' . htmlspecialchars($val) . '"' . $selected . '>' . htmlspecialchars($lbl) . '</option>';
            }
            echo '</select></td>';

            // Plan Text Input
            $planDesc = $item ? $item['plan_description'] : '';
            echo '<td><input type="text" name="plan[' . htmlspecialchars($oppId) . ']" value="' . htmlspecialchars($planDesc) . '" style="width: 100%; min-width: 150px;"></td>';

            echo '</tr>';
        }

        echo '</tbody></table>';

        // Developing Pipeline Section
        echo '<h2>Developing Pipeline</h2>';

        echo '<table id="developing-pipeline-table" class="list view table-responsive">';
        echo '<thead><tr>';
        echo '<th>Account</th>';
        echo '<th>Opportunity</th>';
        echo '<th>Revenue</th>';
        echo '<th>Profit</th>';
        echo '<th>Projected Stage</th>';
        echo '<th>Day</th>';
        echo '<th>Plan</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        // When submitted, show frozen developing pipeline from plan items (not live data)
        if ($plan->status === 'submitted') {
            // Collect, resolve account names, sort A→Z, then render
            $submittedDevRows = [];
            foreach ($planItems as $oppId => $item) {
                if ($item['item_type'] !== 'developing') continue;
                $oppBean = BeanFactory::getBean('Opportunities', $oppId);
                if (!$oppBean || empty($oppBean->id)) continue;
                $submittedDevRows[] = [
                    'oppId'       => $oppId,
                    'oppBean'     => $oppBean,
                    'item'        => $item,
                    'account_name' => $oppBean->account_name,
                ];
            }
            usort($submittedDevRows, function($a, $b) {
                return strcasecmp($a['account_name'], $b['account_name']);
            });
            foreach ($submittedDevRows as $sdr) {
                $oppId   = $sdr['oppId'];
                $oppBean = $sdr['oppBean'];
                $item    = $sdr['item'];
                $accountName = $sdr['account_name'];
                $profit  = !empty($item['original_profit']) ? (float)$item['original_profit'] : (float)$oppBean->opportunity_profit;
                $revenue = (float)$oppBean->amount;

                echo '<tr class="developing-pipeline-row" data-opportunity-id="' . htmlspecialchars($oppId) . '">';
                echo '<td>' . htmlspecialchars($accountName) . '</td>';
                echo '<td><a href="javascript:void(0)" onclick="window.top.location.href=\'/#/opportunities/record/' . htmlspecialchars($oppId) . '\'">' . htmlspecialchars($oppBean->name) . '</a></td>';
                echo '<td class="dev-amount" data-amount="' . htmlspecialchars($revenue) . '">' . htmlspecialchars(number_format($revenue, 0)) . '</td>';
                echo '<td class="dev-profit" data-profit="' . htmlspecialchars($profit) . '">' . htmlspecialchars(number_format($profit, 0)) . '</td>';

                $displayStage = $item['projected_stage'] ?: 'None';
                echo '<td>' . htmlspecialchars($displayStage) . '</td>';

                $dayLabel = ucfirst($item['planned_day'] ?? '');
                echo '<td>' . htmlspecialchars($dayLabel) . '</td>';

                echo '<td>' . htmlspecialchars($item['plan_description'] ?? '') . '</td>';
                echo '</tr>';
            }
        } else {
            // Live/snapshot developing pipeline for editing
            foreach ($devPipelineOpps as $opp) {
                $oppId = $opp['id'];
                $item = (isset($planItems[$oppId]) && $planItems[$oppId]['item_type'] === 'developing') ? $planItems[$oppId] : null;

                // Get account name — pre-fetched into $opp['account_name'] for snapshot weeks
                if (!empty($opp['account_name'])) {
                    $accountName = $opp['account_name'];
                } elseif ($hasSnapshot && !empty($opp['account_id'])) {
                    $acctQuery = "SELECT name FROM accounts WHERE id = " . $db->quoted($opp['account_id']) . " AND deleted = 0";
                    $acctResult = $db->query($acctQuery);
                    $acctRow = $db->fetchByAssoc($acctResult);
                    $accountName = $acctRow ? html_entity_decode($acctRow['name'], ENT_QUOTES | ENT_HTML5) : 'No Account';
                } else {
                    $oppBean = BeanFactory::getBean('Opportunities', $oppId);
                    if (!$oppBean || empty($oppBean->id)) continue;
                    $accountName = $oppBean->account_name;
                }
                $profit = isset($opp['opportunity_profit']) ? (float)$opp['opportunity_profit'] : 0;

                echo '<tr class="developing-pipeline-row" data-opportunity-id="' . htmlspecialchars($oppId) . '">';
                echo '<td>' . htmlspecialchars($accountName) . '</td>';
                echo '<td><a href="javascript:void(0)" onclick="window.top.location.href=\'/#/opportunities/record/' . htmlspecialchars($oppId) . '\'">' . htmlspecialchars($opp['name']) . '</a></td>';
                echo '<td class="dev-amount" data-amount="' . htmlspecialchars($opp['amount']) . '">' . htmlspecialchars(number_format($opp['amount'], 0)) . '</td>';
                echo '<td class="dev-profit" data-profit="' . htmlspecialchars($profit) . '">' . htmlspecialchars(number_format($profit, 0)) . '</td>';

                $devSavedStage = $item ? ($item['projected_stage'] ?? '') : '';
                echo '<td><select name="dev_projected_stage[' . htmlspecialchars($oppId) . ']" class="dev-projected-stage-select">';
                echo '<option value="">-- Select --</option>';
                $analysisProb = isset($stageProbabilities[$analysisStage]) ? (int)$stageProbabilities[$analysisStage] : 1;
                foreach ($stageOrder as $stage) {
                    $stageProb = isset($stageProbabilities[$stage]) ? (int)$stageProbabilities[$stage] : 0;
                    if ($stageProb > $analysisProb && $stageProb < 100) {
                        $selected = ($item && $item['projected_stage'] == $stage) ? ' selected' : '';
                        echo '<option value="' . htmlspecialchars($stage) . '"' . $selected . '>' . htmlspecialchars($stage) . '</option>';
                    }
                }
                echo '</select></td>';

                echo '<td><select name="dev_day[' . htmlspecialchars($oppId) . ']" class="dev-day-select">';
                $days = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday'];
                foreach ($days as $val => $lbl) {
                    $selected = ($item && $item['planned_day'] == $val) ? ' selected' : '';
                    echo '<option value="' . htmlspecialchars($val) . '"' . $selected . '>' . htmlspecialchars($lbl) . '</option>';
                }
                echo '</select></td>';

                $planDesc = $item ? $item['plan_description'] : '';
                echo '<td><input type="text" name="dev_plan[' . htmlspecialchars($oppId) . ']" value="' . htmlspecialchars($planDesc) . '"></td>';
                echo '</tr>';
            }
        }

        echo '</tbody></table>';

        // Prospecting Section
        echo '<h2>Prospecting</h2>';

        // Load source types from config
        $sourceTypes = LF_PRConfig::getConfigJson('prospecting', 'source_types') ?: [];
        echo '<script>var LF_SOURCE_TYPES = ' . json_encode($sourceTypes) . ';</script>';

        // Load existing prospecting items for this week's plan
        $prospectItems = [];
        $prospectQuery = sprintf(
            "SELECT * FROM lf_plan_prospect_items WHERE lf_weekly_plan_id = %s AND deleted = 0",
            $db->quoted($plan->id)
        );
        $prospectResult = $db->query($prospectQuery);
        while ($row = $db->fetchByAssoc($prospectResult)) {
            $prospectItems[] = $row;
        }

        echo '<table id="prospecting-table" class="list view table-responsive">';
        echo '<thead><tr>';
        echo '<th>Source Type</th>';
        echo '<th>Day</th>';
        echo '<th>Expected Revenue</th>';
        echo '<th>Expected Profit</th>';
        echo '<th>Description</th>';
        echo '<th>Action</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        foreach ($prospectItems as $idx => $item) {
            echo '<tr class="prospecting-row" data-prospect-index="' . htmlspecialchars($idx) . '" data-prospect-id="' . htmlspecialchars($item['id']) . '">';

            // Source Type Dropdown
            echo '<td><select name="prospect_source[' . htmlspecialchars($idx) . ']" class="prospect-source">';
            echo '<option value="">-- Select --</option>';
            foreach ($sourceTypes as $sourceType) {
                $selected = ($item['source_type'] == $sourceType) ? ' selected' : '';
                echo '<option value="' . htmlspecialchars($sourceType) . '"' . $selected . '>' . htmlspecialchars($sourceType) . '</option>';
            }
            echo '</select></td>';

            // Day Dropdown
            echo '<td><select name="prospect_day[' . htmlspecialchars($idx) . ']" class="prospect-day">';
            $days = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday'];
            foreach ($days as $val => $lbl) {
                $selected = ($item['planned_day'] == $val) ? ' selected' : '';
                echo '<option value="' . htmlspecialchars($val) . '"' . $selected . '>' . htmlspecialchars($lbl) . '</option>';
            }
            echo '</select></td>';

            // Expected Revenue
            $revVal = (int)(float)($item['expected_revenue'] ?? $item['expected_value'] ?? 0);
            echo '<td><input type="number" name="prospect_revenue[' . htmlspecialchars($idx) . ']" class="prospect-revenue" value="' . ($revVal ?: '') . '"></td>';

            // Expected Profit
            $profVal = (int)(float)($item['expected_profit'] ?? 0);
            echo '<td><input type="number" name="prospect_profit[' . htmlspecialchars($idx) . ']" class="prospect-profit" value="' . ($profVal ?: '') . '"></td>';

            // Description
            echo '<td><input type="text" name="prospect_description[' . htmlspecialchars($idx) . ']" value="' . htmlspecialchars($item['plan_description']) . '"></td>';

            // Remove Button
            echo '<td><button type="button" class="remove-prospect-row">Remove</button></td>';

            echo '</tr>';
        }

        echo '</tbody></table>';

        // Add Row Button
        echo '<button type="button" id="add-prospect-row" class="button">Add Row</button>';

        // Pipeline Health Summary
        // C1: Use extracted amount, not raw array
        $remainingQuota = max(0, $annualQuota - $closedYtdAmount);
        $pipelineTarget = $remainingQuota * $coverageMultiplier;

        echo '<div class="lf-health-summary-section">';
        echo '<h2>Pipeline Health Summary</h2>';
        echo '<div id="health-summary" class="lf-health-container">';
        echo '  <div class="health-box">Closed YTD: <span id="health-closed-ytd" data-value="' . $closedYtdAmount . '">$' . number_format($closedYtdAmount, 0) . '</span></div>';
        echo '  <div class="health-box">Remaining Quota: <span id="health-remaining-quota" data-value="' . $remainingQuota . '">$' . number_format($remainingQuota, 0) . '</span></div>';
        echo '  <div class="health-box">Pipeline Target: <span id="health-pipeline-target" data-value="' . $pipelineTarget . '">$' . number_format($pipelineTarget, 0) . '</span></div>';
        echo '  <div class="health-box">Current Pipeline: <span id="health-current-pipeline" data-value="0">$0</span></div>';
        // Gap to Target styled with red accent when pipeline < target (via gap-negative class in JS)
        echo '  <div class="health-box">Gap to Target: <span id="health-gap-to-target" data-value="0">$0</span></div>';
        echo '  <div class="health-box">Coverage Ratio: <span id="health-coverage-ratio" data-value="0">0.0x</span></div>';
        echo '</div>';
        echo '</div>';

        // Action Buttons
        echo '<div class="lf-planning-actions">';
        echo '  <button type="button" id="updates-complete" class="button primary">Submit</button>';
        echo '  <div id="save-message" class="lf-message"></div>';
        echo '</div>';

        echo '</div>'; // end #lf-planning-container

        // Read-only mode after plan is submitted
        if ($plan->status === 'submitted') {
            echo '<style>';
            echo '#lf-planning-container select, #lf-planning-container input[type="text"], #lf-planning-container input[type="number"], #lf-planning-container input[type="checkbox"], #lf-planning-container textarea { pointer-events: none; opacity: 0.6; background-color: #f3f2f1; }';
            echo '.lf-planning-actions { display: none; }';
            echo '#add-prospect-row { display: none; }';
            echo '.remove-prospect-row { display: none; }';
            echo '</style>';
            echo '<div style="background: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 12px 16px; border-radius: 8px; margin-top: 16px; text-align: center; font-weight: 600;">';
            echo 'This plan has been submitted and is now read-only.';
            echo '</div>';
        }

        // Read-only mode for past and future weeks (only current week is editable)
        if (!$isCurrentWeek && $plan->status !== 'submitted') {
            echo '<style>';
            echo '#lf-planning-container select, #lf-planning-container input[type="text"], #lf-planning-container input[type="number"], #lf-planning-container input[type="checkbox"], #lf-planning-container textarea { pointer-events: none; opacity: 0.6; background-color: #f3f2f1; }';
            echo '.lf-planning-actions { display: none; }';
            echo '#add-prospect-row { display: none; }';
            echo '.remove-prospect-row { display: none; }';
            echo '</style>';
            echo '<div style="background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 12px 16px; border-radius: 8px; margin-top: 16px; text-align: center; font-weight: 600;">';
            echo 'Viewing a ' . ($weekStart < WeekHelper::getCurrentWeekStart() ? 'past' : 'future') . ' week. Only the current week can be edited.';
            echo '</div>';
        }

        echo '</div>'; // end .lf-planning-wrapper
        echo '</div>'; // end .lf-content-wrapper
    }

    /**
     * Render sub-navigation tabs for Weekly Plan views
     *
     * @param string $activePage Current page identifier ('planning', 'plan', 'report')
     * @param bool $isAdmin Whether to show admin links
     */
    private function renderSubNav($activePage, $isAdmin = false)
    {
        echo '<nav class="lf-subnav">';

        // Main navigation links
        $links = [
            'planning' => ['Rep Plan', 'index.php?module=LF_WeeklyPlan&action=planning'],
            'rep_report' => ['Rep Report', 'index.php?module=LF_WeeklyPlan&action=rep_report'],
            'plan' => ['Plan Dashboard', 'index.php?module=LF_WeeklyPlan&action=plan'],
            'report' => ['Report Dashboard', 'index.php?module=LF_WeeklyPlan&action=report'],
        ];

        foreach ($links as $page => $info) {
            $activeClass = ($page === $activePage) ? ' active' : '';
            echo '<a href="' . $info[1] . '" class="lf-subnav-link' . $activeClass . '">' . $info[0] . '</a>';
        }

        // Admin-only links
        if ($isAdmin) {
            echo '<div class="lf-subnav-admin">';
            echo '<a href="index.php?module=LF_PRConfig&action=config" class="lf-subnav-link">Config</a>';
            echo '<a href="index.php?module=LF_RepTargets&action=manage" class="lf-subnav-link">Rep Targets</a>';
            echo '</div>';
        }

        echo '</nav>';
    }
}
