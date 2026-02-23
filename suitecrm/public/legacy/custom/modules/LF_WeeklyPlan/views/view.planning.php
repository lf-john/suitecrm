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

        $weekStart = WeekHelper::getCurrentWeekStart();
        $weekRange = WeekHelper::formatWeekRange($weekStart);
        $plan = LF_WeeklyPlan::getOrCreateForWeek($selectedUserId, $weekStart);

        // M2: Read analysis stage from config instead of hard-coding
        $analysisStage = LF_PRConfig::getConfig('stages', 'analysis_stage') ?: '2-Analysis (1%)';

        // Load opportunities
        $allOpenOpps = OpportunityQuery::getOpenOpportunities($selectedUserId);
        $pipelineOpps = [];
        foreach ($allOpenOpps as $opp) {
            if (strpos($opp['sales_stage'], $analysisStage) === false) {
                $pipelineOpps[] = $opp;
            }
        }
        $devPipelineOpps = OpportunityQuery::getAnalysisOpportunities($selectedUserId);

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
        echo '<script src="custom/modules/LF_WeeklyPlan/js/planning.js"></script>';

        // Render sub-header CSS and JS
        LF_SubHeader::renderCSS();
        LF_SubHeader::renderJS();

        // Render SuiteCRM-style sub-header with user selector for admins
        LF_SubHeader::render('Sales Rep Planning', [
            'showUserSelector' => $current_user->is_admin,
            'users' => $allUsers,
            'selectedUserId' => $selectedUserId,
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
        // Reference CSRF token for AJAX save operations
        echo 'var LF_CSRF_TOKEN = (typeof SUGAR !== "undefined" && SUGAR.csrf) ? SUGAR.csrf.form_token : "";';
        echo '</script>';

        // Week and status info bar
        $statusLabel = isset($app_list_strings['lf_plan_status_dom'][$plan->status])
            ? $app_list_strings['lf_plan_status_dom'][$plan->status]
            : $plan->status;

        echo '<div class="lf-info-bar" style="background: white; padding: 16px; border-radius: 8px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #edebe9;">';
        echo '<div style="font-size: 16px; font-weight: 600; color: #323130;">' . htmlspecialchars($selectedUserName) . ' &mdash; ' . htmlspecialchars($weekRange) . '</div>';
        echo '<span class="badge lf-status-' . htmlspecialchars($plan->status) . '">' . htmlspecialchars($statusLabel) . '</span>';
        echo '</div>';

        // Container for JS event delegation
        echo '<div id="lf-planning-container" data-plan-id="' . htmlspecialchars($plan->id) . '">';

        // Totals Row
        echo '<div id="totals-row" class="lf-totals-container">';
        echo '  <div id="total-closing-box" class="total-box">Closing: <span id="total-closing" data-value="0">0</span></div>';
        echo '  <div id="total-at-risk-box" class="total-box">At Risk: <span id="total-at-risk" data-value="0">0</span></div>';
        echo '  <div id="total-progression-box" class="total-box">Progression: <span id="total-progression" data-value="0">0</span></div>';
        echo '  <div id="total-new-pipeline-box" class="total-box">New Pipeline: <span id="total-new-pipeline" data-value="0">0</span></div>';
        echo '</div>';

        // Existing Pipeline Table
        echo '<h2>Existing Pipeline</h2>';
        echo '<table id="pipeline-table" class="list view table-responsive">';
        echo '<thead><tr>';
        echo '<th>Account</th>';
        echo '<th>Opportunity</th>';
        echo '<th>Amount</th>';
        echo '<th>Current Stage</th>';
        echo '<th>Projected Stage</th>';
        echo '<th>Category</th>';
        echo '<th>At Risk</th>';
        echo '<th>Day</th>';
        echo '<th>Plan</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        foreach ($pipelineOpps as $opp) {
            $oppId = $opp['id'];
            $item = isset($planItems[$oppId]) ? $planItems[$oppId] : null;
            
            // M3: Null check on BeanFactory::getBean
            $oppBean = BeanFactory::getBean('Opportunities', $oppId);
            if (!$oppBean || empty($oppBean->id)) {
                continue;
            }
            $accountName = $oppBean->account_name;

            echo '<tr data-opportunity-id="' . htmlspecialchars($oppId) . '">';
            echo '<td>' . htmlspecialchars($accountName) . '</td>';
            echo '<td><a target="_top" href="#/opportunities/record/' . htmlspecialchars($oppId) . '">' . htmlspecialchars($opp['name']) . '</a></td>';
            echo '<td class="amount" data-amount="' . htmlspecialchars($opp['amount']) . '">' . htmlspecialchars(number_format($opp['amount'], 2)) . '</td>';
            // Get current stage probability
            $currentStage = $opp['sales_stage'];
            $currentProb = isset($stageProbabilities[$currentStage]) ? (int)$stageProbabilities[$currentStage] : 0;
            echo '<td class="current-stage" data-stage="' . htmlspecialchars($currentStage) . '" data-prob="' . $currentProb . '">' . htmlspecialchars($currentStage) . '</td>';

            // Projected Stage Dropdown - show stages with higher probability than current
            $savedProjectedStage = $item ? ($item['projected_stage'] ?? '') : '';
            echo '<td><select name="projected_stage[' . htmlspecialchars($oppId) . ']" class="projected-stage-select" data-opp-id="' . htmlspecialchars($oppId) . '">';
            echo '<option value="">-- Select --</option>';
            // Use probability comparison instead of array index to be more robust
            foreach ($stageOrder as $stage) {
                $stageProb = isset($stageProbabilities[$stage]) ? (int)$stageProbabilities[$stage] : 0;
                // Show stages with probability higher than current stage
                if ($stageProb > $currentProb) {
                    $selected = ($savedProjectedStage === $stage) ? ' selected' : '';
                    echo '<option value="' . htmlspecialchars($stage) . '"' . $selected . '>' . htmlspecialchars($stage) . '</option>';
                }
            }
            echo '</select></td>';

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
        echo '<th>Amount</th>';
        echo '<th>Projected Stage</th>';
        echo '<th>Day</th>';
        echo '<th>Plan</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        foreach ($devPipelineOpps as $opp) {
            $oppId = $opp['id'];
            // Filter by item_type = 'developing'
            $item = (isset($planItems[$oppId]) && $planItems[$oppId]['item_type'] === 'developing') ? $planItems[$oppId] : null;

            // M3: Null check on BeanFactory::getBean
            $oppBean = BeanFactory::getBean('Opportunities', $oppId);
            if (!$oppBean || empty($oppBean->id)) {
                continue;
            }
            $accountName = $oppBean->account_name;

            echo '<tr class="developing-pipeline-row" data-opportunity-id="' . htmlspecialchars($oppId) . '">';
            echo '<td>' . htmlspecialchars($accountName) . '</td>';
            echo '<td><a target="_top" href="#/opportunities/record/' . htmlspecialchars($oppId) . '">' . htmlspecialchars($opp['name']) . '</a></td>';
            echo '<td class="dev-amount" data-amount="' . htmlspecialchars($opp['amount']) . '">' . htmlspecialchars(number_format($opp['amount'], 2)) . '</td>';

            // Projected Stage Dropdown (stages above 2-Analysis)
            echo '<td><select name="dev_projected_stage[' . htmlspecialchars($oppId) . ']" class="dev-projected-stage-select">';
            echo '<option value="">-- Select --</option>';
            // M2: Config-driven analysis stage - get its probability
            $analysisProb = isset($stageProbabilities[$analysisStage]) ? (int)$stageProbabilities[$analysisStage] : 1;
            // Use probability comparison to show stages higher than analysis stage
            foreach ($stageOrder as $stage) {
                $stageProb = isset($stageProbabilities[$stage]) ? (int)$stageProbabilities[$stage] : 0;
                // Show stages with probability higher than analysis stage but below 100%
                if ($stageProb > $analysisProb && $stageProb < 100) {
                    $selected = ($item && $item['projected_stage'] == $stage) ? ' selected' : '';
                    echo '<option value="' . htmlspecialchars($stage) . '"' . $selected . '>' . htmlspecialchars($stage) . '</option>';
                }
            }
            echo '</select></td>';

            // Day Dropdown
            echo '<td><select name="dev_day[' . htmlspecialchars($oppId) . ']" class="dev-day-select">';
            $days = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday'];
            foreach ($days as $val => $lbl) {
                $selected = ($item && $item['planned_day'] == $val) ? ' selected' : '';
                echo '<option value="' . htmlspecialchars($val) . '"' . $selected . '>' . htmlspecialchars($lbl) . '</option>';
            }
            echo '</select></td>';

            // Plan Text Input
            $planDesc = $item ? $item['plan_description'] : '';
            echo '<td><input type="text" name="dev_plan[' . htmlspecialchars($oppId) . ']" value="' . htmlspecialchars($planDesc) . '"></td>';

            echo '</tr>';
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
        echo '<th>Expected Value</th>';
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

            // Expected Value
            echo '<td><input type="number" name="prospect_amount[' . htmlspecialchars($idx) . ']" class="prospect-amount" value="' . htmlspecialchars($item['expected_value']) . '"></td>';

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
        echo '  <button type="button" id="save-plan" class="button primary">Save</button>';
        echo '  <button type="button" id="updates-complete" class="button">Updates Complete</button>';
        echo '  <div id="save-message" class="lf-message"></div>';
        echo '</div>';

        echo '</div>'; // end #lf-planning-container
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
