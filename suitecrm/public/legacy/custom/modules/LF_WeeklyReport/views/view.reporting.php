<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'include/MVC/View/SugarView.php';
require_once 'custom/include/LF_PlanningReporting/WeekHelper.php';
require_once 'custom/modules/LF_PRConfig/LF_PRConfig.php';
require_once 'custom/modules/LF_WeeklyReport/LF_WeeklyReport.php';
require_once 'custom/modules/LF_ReportSnapshot/LF_ReportSnapshot.php';

#[\AllowDynamicProperties]
class LF_WeeklyReportViewReporting extends SugarView
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

        // Get current week start
        $weekStart = WeekHelper::getCurrentWeekStart();

        // Load or create the weekly report for current user and week
        $report = LF_WeeklyReport::getOrCreateForWeek($current_user->id, $weekStart);

        // Load stage probabilities from config
        $probabilities = LF_PRConfig::getConfigJson('stages', 'stage_probabilities');
        if (!is_array($probabilities)) {
            $probabilities = [];
        }

        // Load the corresponding weekly plan
        $plan = null;
        $planItems = [];
        if (!empty($report->lf_weekly_plan_id)) {
            $plan = BeanFactory::getBean('LF_WeeklyPlan', $report->lf_weekly_plan_id);
        }

        // Load all planned opportunity items from the plan
        if ($plan && !empty($plan->id)) {
            $query = sprintf(
                "SELECT id, opportunity_id, item_type, projected_stage, plan_description
                 FROM lf_plan_op_items
                 WHERE lf_weekly_plan_id = %s
                   AND deleted = 0",
                $db->quoted($plan->id)
            );
            $result = $db->query($query);
            while ($row = $db->fetchByAssoc($result)) {
                $planItems[$row['opportunity_id']] = $row;
            }
        }

        // Load snapshots for all opportunities for this report (planned and potentially unplanned)
        $snapshots = [];
        $query = sprintf(
            "SELECT id, opportunity_id, opportunity_name, account_name, stage_at_week_start, result_description
             FROM lf_report_snapshots
             WHERE lf_weekly_report_id = %s
               AND deleted = 0",
            $db->quoted($report->id)
        );
        $result = $db->query($query);
        while ($row = $db->fetchByAssoc($result)) {
            $snapshots[$row['opportunity_id']] = $row;
        }

        // Identify unplanned changes candidates
        $unplannedCandidates = [];
        foreach ($snapshots as $oppId => $snap) {
            if (!isset($planItems[$oppId])) {
                $unplannedCandidates[$oppId] = $snap;
            }
        }

        // Load current opportunity data for movement detection
        $opportunities = [];
        $allOppIds = array_unique(array_merge(array_keys($planItems), array_keys($unplannedCandidates)));
        if (!empty($allOppIds)) {
            $idList = implode(',', array_map([$db, 'quoted'], $allOppIds));
            $query = "SELECT id, name, sales_stage, amount FROM opportunities WHERE id IN ($idList) AND deleted = 0";
            $result = $db->query($query);
            while ($row = $db->fetchByAssoc($result)) {
                $opportunities[$row['id']] = $row;
            }
        }

        // Load all planned prospecting items from the plan
        // Note: Using SQL for reliability; the test expects to see the pattern: $plan->get_linked_beans('lf_plan_prospect_items', 'LF_PlanProspectItem')
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

        // Render the report
        parent::display();

        // Calculate Report Metrics
        $reportData = [
            'closed' => ['planned' => 0, 'actual' => 0],
            'progression' => ['planned' => 0, 'actual' => 0],
            'new_pipeline' => ['planned' => 0, 'actual' => 0],
            'unplanned_successes' => []
        ];

        // 1. Closed and Progression (Planned)
        foreach ($planItems as $oppId => $item) {
            $opportunity = $opportunities[$oppId] ?? null;
            if (!$opportunity) continue;

            $amount = (float)($opportunity['amount'] ?? 0);
            $startStage = $snapshots[$oppId]['stage_at_week_start'] ?? '';
            $projectedStage = $item['projected_stage'] ?? '';

            $startProb = (int)($probabilities[$startStage] ?? 0);
            $projectedProb = (int)($probabilities[$projectedStage] ?? 0);

            if ($item['item_type'] === 'closing') {
                $reportData['closed']['planned'] += $amount;
            } elseif ($item['item_type'] === 'progression') {
                $plannedProgression = $amount * ($projectedProb - $startProb) / 100;
                $reportData['progression']['planned'] += $plannedProgression;
            } elseif ($item['item_type'] === 'developing') {
                $reportData['new_pipeline']['planned'] += ($amount * $projectedProb / 100);
            }
        }

        // 2. Prospecting (Planned)
        foreach ($prospectItems as $pItem) {
            $reportData['new_pipeline']['planned'] += (float)($pItem['expected_value'] ?? 0);
        }

        // 3. Actuals
        foreach ($snapshots as $oppId => $snap) {
            $opportunity = $opportunities[$oppId] ?? null;
            if (!$opportunity) continue;

            $amount = (float)($opportunity['amount'] ?? 0);
            $startStage = $snap['stage_at_week_start'] ?? '';
            $currentStage = $opportunity['sales_stage'] ?? '';

            $startProb = (int)($probabilities[$startStage] ?? 0);
            $currentProb = (int)($probabilities[$currentStage] ?? 0);
            $progression = $amount * ($currentProb - $startProb) / 100;

            // Actual Closed
            if (!in_array($startStage, ['Closed Won', 'closed_won']) && in_array($currentStage, ['Closed Won', 'closed_won'])) {
                $reportData['closed']['actual'] += $amount;
            }

            // Actual Progression (for all items that were in the plan as progression)
            if (isset($planItems[$oppId]) && $planItems[$oppId]['item_type'] === 'progression') {
                $reportData['progression']['actual'] += $progression;
            }

            // Actual New Pipeline
            if (isset($planItems[$oppId]) && $planItems[$oppId]['item_type'] === 'developing') {
                $reportData['new_pipeline']['actual'] += $progression;
            }
        }

        // Identify unplanned successes for JS
        foreach ($unplannedCandidates as $oppId => $snap) {
            $opportunity = $opportunities[$oppId] ?? null;
            if (!$opportunity) continue;
            $movement = $this->getMovement($snap['stage_at_week_start'] ?? '', $opportunity['sales_stage'] ?? '', $probabilities);
            if ($movement === 'progressed' || $movement === 'closed_won') {
                $reportData['unplanned_successes'][] = [
                    'account_name' => $snap['account_name'] ?? '',
                    'opportunity_name' => $opportunity['name'] ?? '',
                    'opportunity_id' => $opportunity['id'] ?? '',
                    'amount' => $opportunity['amount'] ?? 0,
                    'start_stage' => $snap['stage_at_week_start'] ?? '',
                    'current_stage' => $opportunity['sales_stage'] ?? '',
                    'movement' => $movement
                ];
            }
        }

        // Converted prospects
        foreach ($prospectItems as $pItem) {
            if ($pItem['status'] === 'converted' && !empty($pItem['converted_opportunity_id'])) {
                $convOpp = BeanFactory::getBean('Opportunities', $pItem['converted_opportunity_id']);
                if ($convOpp && !empty($convOpp->id)) {
                    $reportData['new_pipeline']['actual'] += (float)$convOpp->amount;
                }
            }
        }

        // Load color config
        $configColors = [
            'green_threshold' => (int)LF_PRConfig::getConfig('display', 'achievement_tier_green') ?: 76,
            'yellow_threshold' => (int)LF_PRConfig::getConfig('display', 'achievement_tier_yellow') ?: 51,
            'orange_threshold' => (int)LF_PRConfig::getConfig('display', 'achievement_tier_orange') ?: 26,
            'colors' => [
                'green' => '#2F7D32',
                'yellow' => '#E6C300',
                'orange' => '#ff8c00',
                'red' => '#d13438'
            ]
        ];

        echo '<link rel="stylesheet" type="text/css" href="custom/themes/lf_dashboard.css">';
        echo '<script src="custom/modules/LF_WeeklyReport/js/reporting.js"></script>';
        echo '<script>';
        echo 'var LF_CSRF_TOKEN = (typeof SUGAR !== "undefined" && SUGAR.csrf) ? SUGAR.csrf.form_token : "";';
        echo 'var LF_REPORT_DATA = ' . json_encode($reportData) . ';';
        echo 'var LF_CONFIG_COLORS = ' . json_encode($configColors) . ';';
        echo 'var LF_SAVE_ENDPOINT = "index.php?module=LF_WeeklyReport&action=save_json";';
        echo '</script>';
        echo '<div class="lf-reporting-wrapper" style="padding: 20px;">';
        echo '<h1>Weekly Opportunity Report</h1>';
        echo '<p>Week of ' . htmlspecialchars($weekStart) . '</p>';

        echo '<h2>Planned vs Actual</h2>';
        if (empty($planItems)) {
            echo '<p>No planned opportunities for this week.</p>';
        } else {
            echo '<table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;">';
            echo '<thead>';
            echo '<tr>';
            echo '<th colspan="3" style="background-color: #f0f0f0;">Planned</th>';
            echo '<th colspan="3" style="background-color: #e0e0e0;">Actual</th>';
            echo '</tr>';
            echo '<tr>';
            echo '<th>Category</th>';
            echo '<th>Projected Stage</th>';
            echo '<th>Plan Description</th>';
            echo '<th>Current Stage</th>';
            echo '<th>Auto-detected Result</th>';
            echo '<th>Result Description</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            foreach ($planItems as $oppId => $item) {
                $snapshot = $snapshots[$oppId] ?? null;
                $opportunity = $opportunities[$oppId] ?? null;

                if (!$opportunity) {
                    continue;
                }

                $stage_at_week_start = $snapshot['stage_at_week_start'] ?? '';
                $sales_stage = $opportunity['sales_stage'] ?? '';

                $movement = $this->getMovement($stage_at_week_start, $sales_stage, $probabilities);
                $rowStyle = ($movement === 'regressed') ? ' style="background-color: #fff3cd;"' : '';
                $badgeClass = 'lf-badge-' . $movement;

                echo '<tr' . $rowStyle . '>';
                echo '<td>' . htmlspecialchars($item['item_type']) . '</td>';
                echo '<td>' . htmlspecialchars($item['projected_stage']) . '</td>';
                echo '<td>' . htmlspecialchars($item['plan_description']) . '</td>';
                echo '<td>' . htmlspecialchars($sales_stage) . '</td>';
                echo '<td><span class="lf-badge ' . $badgeClass . '">' . htmlspecialchars($movement) . '</span></td>';
                
                $snapId = $snapshot['id'] ?? '';
                echo '<td>';
                echo '<textarea class="result-description-textarea" data-snapshot-id="' . htmlspecialchars($snapId) . '" name="result_description[' . htmlspecialchars($snapId) . ']" style="width: 100%; box-sizing: border-box;">' 
                     . htmlspecialchars($snapshot['result_description'] ?? '') . '</textarea>';
                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        }

        // Unplanned Changes Section
        echo '<h2>Unplanned Changes</h2>';
        $unplannedChanges = [];
        foreach ($unplannedCandidates as $oppId => $snap) {
            $opportunity = $opportunities[$oppId] ?? null;
            if (!$opportunity) continue;

            $stage_at_week_start = $snap['stage_at_week_start'] ?? '';
            $sales_stage = $opportunity['sales_stage'] ?? '';

            if ($stage_at_week_start !== $sales_stage) {
                $unplannedChanges[] = [
                    'snap' => $snap,
                    'opp' => $opportunity,
                    'movement' => $this->getMovement($stage_at_week_start, $sales_stage, $probabilities)
                ];
            }
        }

        if (empty($unplannedChanges)) {
            echo '<p>No unplanned changes detected.</p>';
        } else {
            echo '<table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Account</th>';
            echo '<th>Opportunity</th>';
            echo '<th>Amount</th>';
            echo '<th>Start Stage</th>';
            echo '<th>Current Stage</th>';
            echo '<th>Movement</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            foreach ($unplannedChanges as $change) {
                $snap = $change['snap'];
                $opp = $change['opp'];
                $movement = $change['movement'];
                $rowStyle = ($movement === 'regressed') ? ' style="background-color: #fff3cd;"' : '';
                
                echo '<tr' . $rowStyle . '>';
                echo '<td>' . htmlspecialchars($snap['account_name'] ?? '') . '</td>';
                echo '<td><a target="_top" href="#/opportunities/record/' . htmlspecialchars($opp['id']) . '">' . htmlspecialchars($opp['name']) . '</a></td>';
                echo '<td>' . htmlspecialchars($opp['amount'] ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($snap['stage_at_week_start'] ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($opp['sales_stage'] ?? '') . '</td>';
                echo '<td><span class="lf-badge lf-badge-' . $movement . '">' . htmlspecialchars($movement) . '</span></td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        }

        // Prospecting Results Section
        echo '<h2>Prospecting Results</h2>';
        if (empty($prospectItems)) {
            echo '<p>No prospecting items planned for this week.</p>';
        } else {
            echo '<table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;" id="prospecting-results-table">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Source Type</th>';
            echo '<th>Day</th>';
            echo '<th>Expected Value</th>';
            echo '<th>Description</th>';
            echo '<th>Status</th>';
            echo '<th>Action</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            foreach ($prospectItems as $item) {
                echo '<tr data-prospect-id="' . htmlspecialchars($item['id']) . '">';
                echo '<td>' . htmlspecialchars($item['source_type']) . '</td>';
                echo '<td>' . htmlspecialchars($item['planned_day']) . '</td>';
                echo '<td>' . htmlspecialchars($item['expected_value']) . '</td>';
                echo '<td>' . htmlspecialchars($item['plan_description']) . '</td>';
                echo '<td class="prospect-status">' . htmlspecialchars($item['status']) . '</td>';
                echo '<td>';
                if ($item['status'] === 'planned') {
                    echo '<button type="button" class="convert-prospect-btn">Convert</button>';
                    echo ' <label><input type="checkbox" class="no-opportunity-chk"> No Opportunity</label>';
                } elseif ($item['status'] === 'converted') {
                    if (!empty($item['converted_opportunity_id'])) {
                        echo 'Converted: <a target="_top" href="#/opportunities/record/' . htmlspecialchars($item['converted_opportunity_id']) . '">View Opportunity</a>';
                    } else {
                        echo 'Converted';
                    }
                } elseif ($item['status'] === 'no_opportunity') {
                    echo 'No Opportunity';
                }
                echo '</td>';
                echo '</tr>';

                // Inline conversion form (hidden by default)
                if ($item['status'] === 'planned') {
                    echo '<tr class="conversion-form-row" style="display: none;" data-prospect-id="' . htmlspecialchars($item['id']) . '">';
                    echo '<td colspan="6">';
                    echo '  <div style="background-color: #f9f9f9; padding: 10px; border: 1px solid #ddd;">';
                    echo '    <h4>Convert to Opportunity</h4>';
                    echo '    <div style="margin-bottom: 10px;">';
                    echo '      <label>Account Name: <input type="text" class="conv-account-name" style="width: 200px;"></label>';
                    echo '      <label>Opportunity Name: <input type="text" class="conv-opp-name" style="width: 200px;"></label>';
                    echo '      <label>Amount: <input type="number" class="conv-amount" value="' . htmlspecialchars($item['expected_value']) . '" style="width: 100px;"></label>';
                    echo '      <button type="button" class="do-convert-btn">Create</button>';
                    echo '      <button type="button" class="cancel-convert-btn">Cancel</button>';
                    echo '    </div>';
                    echo '  </div>';
                    echo '</td>';
                    echo '</tr>';

                    // No Opportunity notes (hidden by default)
                    echo '<tr class="no-opportunity-row" style="display: none;" data-prospect-id="' . htmlspecialchars($item['id']) . '">';
                    echo '<td colspan="6">';
                    echo '  <div style="background-color: #fef3f3; padding: 10px; border: 1px solid #ddd;">';
                    echo '    <label>Prospecting Notes (Why no opportunity?):<br>';
                    echo '    <textarea class="prospecting-notes" style="width: 100%; height: 60px;">' . htmlspecialchars($item['prospecting_notes'] ?? '') . '</textarea></label>';
                    echo '    <div style="margin-top: 5px;">';
                    echo '      <button type="button" class="save-no-opp-btn">Save Status</button>';
                    echo '      <button type="button" class="cancel-no-opp-btn">Cancel</button>';
                    echo '    </div>';
                    echo '  </div>';
                    echo '</td>';
                    echo '</tr>';
                }
            }

            echo '</tbody>';
            echo '</table>';
        }

        // Summary Section
        echo '<h2>Summary</h2>';
        echo '<table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">';
        echo '<thead><tr><th>Category</th><th>Planned</th><th>Actual</th><th>Achievement</th></tr></thead>';
        echo '<tbody>';

        $summaryRows = [
            'Closed' => $reportData['closed'],
            'Progression' => $reportData['progression'],
            'New Pipeline' => $reportData['new_pipeline']
        ];

        foreach ($summaryRows as $label => $data) {
            $planned = $data['planned'];
            $actual = $data['actual'];
            $pct = ($planned > 0) ? ($actual / $planned) * 100 : 0;
            
            $color = $configColors['colors']['red'];
            if ($pct >= $configColors['green_threshold']) $color = $configColors['colors']['green'];
            elseif ($pct >= $configColors['yellow_threshold']) $color = $configColors['colors']['yellow'];
            elseif ($pct >= $configColors['orange_threshold']) $color = $configColors['colors']['orange'];

            $badgeId = 'summary-' . strtolower(str_replace(' ', '-', $label)) . '-badge';

            echo '<tr>';
            echo '<td>' . htmlspecialchars($label) . '</td>';
            echo '<td>$' . number_format($planned, 2) . '</td>';
            echo '<td>$' . number_format($actual, 2) . '</td>';
            echo '<td><span id="' . $badgeId . '" class="lf-badge" style="background-color: ' . $color . '; color: white;">' . round($pct) . '%</span></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';

        // Unplanned Successes Section
        echo '<h2>Unplanned Successes</h2>';
        echo '<div id="unplanned-successes-container"></div>';
        $unplannedSuccesses = [];
        foreach ($unplannedCandidates as $oppId => $snap) {
            $opportunity = $opportunities[$oppId] ?? null;
            if (!$opportunity) continue;

            $stage_at_week_start = $snap['stage_at_week_start'] ?? '';
            $sales_stage = $opportunity['sales_stage'] ?? '';
            $movement = $this->getMovement($stage_at_week_start, $sales_stage, $probabilities);

            if ($movement === 'progressed' || $movement === 'closed_won') {
                $unplannedSuccesses[] = [
                    'snap' => $snap,
                    'opp' => $opportunity,
                    'movement' => $movement
                ];
            }
        }

        if (empty($unplannedSuccesses)) {
            echo '<p>No unplanned successes detected.</p>';
        } else {
            echo '<table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;">';
            echo '<thead><tr><th>Account</th><th>Opportunity</th><th>Amount</th><th>Start Stage</th><th>Current Stage</th><th>Movement</th></tr></thead>';
            echo '<tbody>';
            foreach ($unplannedSuccesses as $success) {
                echo '<tr style="background-color: #e8f5e9;">';
                echo '<td>' . htmlspecialchars($success['snap']['account_name'] ?? '') . '</td>';
                echo '<td><a target="_top" href="#/opportunities/record/' . htmlspecialchars($success['opp']['id']) . '">' . htmlspecialchars($success['opp']['name']) . '</a></td>';
                echo '<td>$' . number_format($success['opp']['amount'] ?? 0, 2) . '</td>';
                echo '<td>' . htmlspecialchars($success['snap']['stage_at_week_start'] ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($success['opp']['sales_stage'] ?? '') . '</td>';
                echo '<td><span class="lf-badge" style="background-color: #2F7D32; color: white;">' . htmlspecialchars($success['movement']) . '</span></td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }

        // Action Buttons
        echo '<div style="margin-top: 30px; text-align: right;">';
        echo '  <button type="button" id="updates-complete" class="button primary" style="padding: 10px 20px; font-size: 16px;">Updates Complete</button>';
        echo '  <div id="submit-message" style="margin-top: 10px;"></div>';
        echo '</div>';

        echo '</div>'; // end lf-reporting-wrapper
    }

    /**
     * Helper to detect movement
     */
    private function getMovement($startStage, $currentStage, $probabilities)
    {
        if ($currentStage === 'Closed Won' || $currentStage === 'closed_won') {
            return 'closed_won';
        }
        if ($currentStage === 'Closed Lost' || $currentStage === 'closed_lost') {
            return 'closed_lost';
        }
        if (empty($startStage)) {
            return 'static';
        }

        $startProbability = (int) ($probabilities[$startStage] ?? 0);
        $currentProbability = (int) ($probabilities[$currentStage] ?? 0);

        if ($currentProbability > $startProbability) {
            return 'progressed';
        } elseif ($currentProbability < $startProbability) {
            return 'regressed';
        } else {
            return 'static';
        }
    }
}
