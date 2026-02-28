<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'include/MVC/View/SugarView.php';
require_once 'custom/include/LF_PlanningReporting/WeekHelper.php';
require_once 'custom/modules/LF_PRConfig/LF_PRConfig.php';
require_once 'custom/modules/LF_RepTargets/LF_RepTargets.php';
require_once 'custom/modules/LF_WeeklyReport/LF_WeeklyReport.php';
require_once 'custom/modules/LF_ReportSnapshot/LF_ReportSnapshot.php';
require_once 'custom/modules/LF_WeeklyPlan/LF_WeeklyPlan.php';

#[\AllowDynamicProperties]
class LF_WeeklyReportViewDashboard extends SugarView
{
    public function __construct()
    {
        parent::__construct();
        $this->options['show_header'] = true;
        $this->options['show_footer'] = true;
    }

    public function display()
    {
        global $current_user, $db;

        // Get and clean input FIRST for better defense in depth
        $rawViewMode = $_REQUEST['view_mode'] ?? 'team';
        $rawSelectedRepId = $_REQUEST['rep_id'] ?? null;

        $configWeekStartDay = WeekHelper::getConfiguredWeekStartDay();
        $rawWeekStart = $_REQUEST['week_start'] ?? WeekHelper::getCurrentWeekStart($configWeekStartDay);

        // Clean input BEFORE using it
        $viewMode = htmlspecialchars($rawViewMode);
        $selectedRepId = htmlspecialchars($rawSelectedRepId);
        $weekStart = htmlspecialchars($rawWeekStart);

        // Use cleaned values for validation
        try {
            $weekStart = WeekHelper::getWeekStart($weekStart, $configWeekStartDay);
        } catch (Exception $e) {
            $weekStart = WeekHelper::getCurrentWeekStart($configWeekStartDay);
        }

        // Gather Data
        $data = $this->gatherDashboardData($weekStart, $selectedRepId);

        // Include CSS/JS
        echo '<link rel="stylesheet" type="text/css" href="custom/themes/lf_dashboard.css">';
        echo '<script src="custom/modules/LF_WeeklyReport/js/dashboard.js"></script>';

        // Inject Data
        echo '<script>window.LF_DASHBOARD_DATA = ' . json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';</script>';

        // Render HTML
        echo '<div class="lf-dashboard-wrapper" style="background: #f5f5f5; min-height: 80vh;">';

        $this->renderHeader();
        $this->renderToolbar($viewMode, $selectedRepId, $weekStart, $data['reps'], $data['weekList']);
        $this->renderDashboardContainer();

        echo '</div>';
    }

    /**
     * Gathers all dashboard data server-side
     */
    private function gatherDashboardData($weekStart, $repId = null)
    {
        $currentYear = (int)date('Y', strtotime($weekStart));
        $configWeekStartDay = WeekHelper::getConfiguredWeekStartDay();

        // Load stage probabilities
        $stageProbabilities = LF_PRConfig::getConfigJson('stages', 'stage_probabilities');
        if (!is_array($stageProbabilities)) {
            $stageProbabilities = [];
        }

        // Load achievement thresholds
        $achievementConfig = [
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

        // Gather commitment data
        $commitmentData = $this->gatherCommitmentData($weekStart, $repId, $stageProbabilities, $achievementConfig);

        // Gather report snapshots for Stage Progression column (US-018)
        $reportSnapshots = $this->gatherReportSnapshots($weekStart, $stageProbabilities);

        return [
            'config' => [
                'brand_blue' => '#125EAD',
                'brand_green' => '#4BB74E',
                'achievement' => $achievementConfig
            ],
            'reps' => LF_RepTargets::getActiveReps(),
            'repTargets' => LF_RepTargets::getTargetsForYear($currentYear),
            'weekInfo' => [
                'currentWeek' => $weekStart,
                'weekEnd' => WeekHelper::getWeekEnd($weekStart),
            ],
            'weekList' => WeekHelper::getWeekList(12, $configWeekStartDay),
            'commitmentData' => $commitmentData,
            'reportSnapshots' => $reportSnapshots
        ];
    }

    /**
     * Gather commitment review data for all reps or a specific rep
     */
    private function gatherCommitmentData($weekStart, $repId, $stageProbabilities, $achievementConfig)
    {
        $db = DBManagerFactory::getInstance();

        // Get all plans for this week
        $weekEnd = WeekHelper::getWeekEnd($weekStart);
        $sqlPlans = sprintf(
            "SELECT id, assigned_user_id FROM lf_weekly_plan
             WHERE week_start_date = %s AND deleted = 0",
            $db->quoted($weekStart)
        );
        if ($repId) {
            $sqlPlans .= sprintf(" AND assigned_user_id = %s", $db->quoted($repId));
        }

        $result = $db->query($sqlPlans);
        $plans = [];
        $planIds = [];
        while ($row = $db->fetchByAssoc($result)) {
            $plans[$row['assigned_user_id']] = $row['id'];
            $planIds[] = $row['id'];
        }

        // Get active reps
        $reps = LF_RepTargets::getActiveReps();
        $repTargets = LF_RepTargets::getTargetsForYear((int)date('Y', strtotime($weekStart)));

        // Build targets map
        $targetsByRep = [];
        foreach ($repTargets as $target) {
            $targetsByRep[$target['assigned_user_id']] = $target;
        }

        // Initialize commitment data structure
        $commitmentData = [
            'overall_achievement_rate' => 0,
            'aggregate_new_pipeline' => ['planned' => 0, 'actual' => 0, 'percent' => 0],
            'aggregate_progression' => ['planned' => 0, 'actual' => 0, 'percent' => 0],
            'rep_data' => []
        ];

        $totalNewPipelinePlanned = 0;
        $totalNewPipelineActual = 0;
        $totalProgressionPlanned = 0;
        $totalProgressionActual = 0;
        $achievementRates = [];

        foreach ($reps as $rep) {
            $repId = $rep['assigned_user_id'];
            $planId = $plans[$repId] ?? null;
            $targets = $targetsByRep[$repId] ?? [];

            $newPipelineTarget = (float)($targets['weekly_new_pipeline'] ?? 10000);
            $progressionTarget = (float)($targets['weekly_progression'] ?? 5000);

            // Get plan items for this rep
            $repNewPipelinePlanned = 0;
            $repProgressionPlanned = 0;
            $achievedItems = [];
            $missedItems = [];
            $unplannedSuccesses = [];

            if ($planId) {
                // Get planned opportunity items
                $sqlOpItems = sprintf(
                    "SELECT poi.*, opp.name as opportunity_name, opp.amount, acc.name as account_name, opp.sales_stage
                     FROM lf_plan_op_items poi
                     LEFT JOIN opportunities opp ON poi.opportunity_id = opp.id
                     LEFT JOIN accounts acc ON opp.account_id = acc.id
                     WHERE poi.lf_weekly_plan_id = %s AND poi.deleted = 0",
                    $db->quoted($planId)
                );
                $result = $db->query($sqlOpItems);

                $planItems = [];
                while ($row = $db->fetchByAssoc($result)) {
                    $planItems[$row['opportunity_id']] = $row;
                }

                // Get snapshots for this plan
                $sqlSnapshots = sprintf(
                    "SELECT rs.*, opp.name as opportunity_name, opp.amount as current_amount, opp.sales_stage as current_stage
                     FROM lf_report_snapshots rs
                     LEFT JOIN opportunities opp ON rs.opportunity_id = opp.id
                     WHERE rs.lf_weekly_plan_id = %s AND rs.deleted = 0",
                    $db->quoted($planId)
                );
                $result = $db->query($sqlSnapshots);

                $snapshots = [];
                while ($row = $db->fetchByAssoc($result)) {
                    $snapshots[$row['opportunity_id']] = $row;
                }

                // Calculate planned values
                foreach ($planItems as $oppId => $item) {
                    $snapshot = $snapshots[$oppId] ?? null;
                    $startStage = $snapshot['stage_at_week_start'] ?? '';
                    $projectedStage = $item['projected_stage'] ?? '';
                    $currentStage = $snapshot['current_stage'] ?? '';
                    $amount = (float)($item['amount'] ?? 0);

                    $startProb = (int)($stageProbabilities[$startStage] ?? 0);
                    $projectedProb = (int)($stageProbabilities[$projectedStage] ?? 0);
                    $currentProb = (int)($stageProbabilities[$currentStage] ?? 0);

                    if ($item['item_type'] === 'developing') {
                        $plannedValue = $amount * $projectedProb / 100;
                        $repNewPipelinePlanned += $plannedValue;
                    } elseif ($item['item_type'] === 'progression') {
                        $plannedValue = $amount * ($projectedProb - $startProb) / 100;
                        $repProgressionPlanned += $plannedValue;
                    }
                }

                // Get prospecting items
                $sqlProspects = sprintf(
                    "SELECT * FROM lf_plan_prospect_items
                     WHERE lf_weekly_plan_id = %s AND deleted = 0",
                    $db->quoted($planId)
                );
                $result = $db->query($sqlProspects);

                $prospectItems = [];
                $prospectsActual = 0;
                while ($row = $db->fetchByAssoc($result)) {
                    $prospectItems[] = $row;
                    $repNewPipelinePlanned += (float)($row['expected_value'] ?? 0);

                    if ($row['status'] === 'converted' && !empty($row['converted_opportunity_id'])) {
                        $convOpp = BeanFactory::getBean('Opportunities', $row['converted_opportunity_id']);
                        if ($convOpp) {
                            $prospectsActual += (float)$convOpp->amount;
                        }
                    }
                }

                // Calculate actual values and categorize items
                $repNewPipelineActual = 0;
                $repProgressionActual = 0;

                foreach ($planItems as $oppId => $item) {
                    $snapshot = $snapshots[$oppId] ?? null;
                    if (!$snapshot) continue;

                    $startStage = $snapshot['stage_at_week_start'] ?? '';
                    $currentStage = $snapshot['current_stage'] ?? '';
                    $projectedStage = $item['projected_stage'] ?? '';
                    $amount = (float)($item['amount'] ?? 0);

                    $startProb = (int)($stageProbabilities[$startStage] ?? 0);
                    $projectedProb = (int)($stageProbabilities[$projectedStage] ?? 0);
                    $currentProb = (int)($stageProbabilities[$currentStage] ?? 0);

                    $movement = $this->getMovement($startStage, $currentStage, $stageProbabilities);

                    $actualValue = $amount * ($currentProb - $startProb) / 100;

                    if ($item['item_type'] === 'developing') {
                        $repNewPipelineActual += $actualValue;
                    } elseif ($item['item_type'] === 'progression') {
                        $repProgressionActual += $actualValue;
                    }

                    // Categorize as achieved or missed
                    $achieved = ($movement === 'forward');

                    $itemData = [
                        'account_name' => $snapshot['account_name'] ?? $planItems[$oppId]['account_name'] ?? '',
                        'opportunity_name' => $snapshot['opportunity_name'] ?? '',
                        'amount' => $amount,
                        'projected_stage' => $projectedStage,
                        'result_description' => $snapshot['result_description'] ?? '',
                        'movement' => $movement
                    ];

                    if ($achieved) {
                        $achievedItems[] = $itemData;
                    } else {
                        $missedItems[] = $itemData;
                    }
                }

                // Add prospect conversions to actual new pipeline
                $repNewPipelineActual += $prospectsActual;

                // Track converted prospects as unplanned successes
                foreach ($prospectItems as $prospect) {
                    if ($prospect['status'] === 'converted' && !empty($prospect['converted_opportunity_id'])) {
                        $unplannedSuccesses[] = [
                            'source_type' => $prospect['source_type'],
                            'expected_value' => (float)($prospect['expected_value'] ?? 0),
                            'converted_opportunity_id' => $prospect['converted_opportunity_id']
                        ];
                    }
                }
            }

            // Calculate percentages and colors
            $newPipelinePercent = $repNewPipelinePlanned > 0
                ? round(($repNewPipelineActual / $repNewPipelinePlanned) * 100, 1)
                : 0;
            $progressionPercent = $repProgressionPlanned > 0
                ? round(($repProgressionActual / $repProgressionPlanned) * 100, 1)
                : 0;

            $newPipelineColor = $this->getColorForPercent($newPipelinePercent, $achievementConfig);
            $progressionColor = $this->getColorForPercent($progressionPercent, $achievementConfig);

            $commitmentData['rep_data'][$rep['assigned_user_id']] = [
                'rep_name' => htmlspecialchars($rep['first_name'] . ' ' . $rep['last_name']),
                'new_pipeline' => [
                    'planned' => $repNewPipelinePlanned,
                    'actual' => $repNewPipelineActual,
                    'percent' => $newPipelinePercent,
                    'color' => $newPipelineColor
                ],
                'progression' => [
                    'planned' => $repProgressionPlanned,
                    'actual' => $repProgressionActual,
                    'percent' => $progressionPercent,
                    'color' => $progressionColor
                ],
                'achieved_items' => $achievedItems,
                'missed_items' => $missedItems,
                'unplanned_successes' => $unplannedSuccesses
            ];

            // Accumulate for aggregates
            $totalNewPipelinePlanned += $repNewPipelinePlanned;
            $totalNewPipelineActual += $repNewPipelineActual;
            $totalProgressionPlanned += $repProgressionPlanned;
            $totalProgressionActual += $repProgressionActual;

            // Track achievement rate for this rep
            $repAchievement = 0;
            $totalTarget = $newPipelineTarget + $progressionTarget;
            if ($totalTarget > 0) {
                $repAchievement = (($repNewPipelineActual + $repProgressionActual) / $totalTarget) * 100;
            }
            $achievementRates[] = $repAchievement;
        }

        // Calculate aggregates
        $commitmentData['aggregate_new_pipeline'] = [
            'planned' => $totalNewPipelinePlanned,
            'actual' => $totalNewPipelineActual,
            'percent' => $totalNewPipelinePlanned > 0 ? round(($totalNewPipelineActual / $totalNewPipelinePlanned) * 100, 1) : 0
        ];

        $commitmentData['aggregate_progression'] = [
            'planned' => $totalProgressionPlanned,
            'actual' => $totalProgressionActual,
            'percent' => $totalProgressionPlanned > 0 ? round(($totalProgressionActual / $totalProgressionPlanned) * 100, 1) : 0
        ];

        // Calculate overall achievement rate
        if (count($achievementRates) > 0) {
            $commitmentData['overall_achievement_rate'] = round(array_sum($achievementRates) / count($achievementRates), 1);
        }

        return $commitmentData;
    }

    /**
     * Gather report snapshots for Stage Progression column (US-018)
     * Returns array of snapshot data with movement field for forward/backward/static categorization
     */
    private function gatherReportSnapshots($weekStart, $stageProbabilities)
    {
        $db = DBManagerFactory::getInstance();

        // Get all snapshots for this week with opportunity and account info
        $sql = sprintf(
            "SELECT rs.id, rs.opportunity_id, rs.assigned_user_id, rs.lf_weekly_plan_id,
                    rs.stage_at_week_start, rs.stage_at_week_end, rs.amount,
                    rs.probability_at_start, rs.probability_at_end,
                    opp.name as opportunity_name, acc.name as account_name
             FROM lf_report_snapshots rs
             LEFT JOIN opportunities opp ON rs.opportunity_id = opp.id
             LEFT JOIN accounts acc ON opp.account_id = acc.id
             LEFT JOIN lf_weekly_plan wp ON rs.lf_weekly_plan_id = wp.id
             WHERE wp.week_start_date = %s
               AND rs.deleted = 0
               AND wp.deleted = 0
             ORDER BY rs.assigned_user_id, rs.amount DESC",
            $db->quoted($weekStart)
        );

        $result = $db->query($sql);
        $snapshots = [];

        while ($row = $db->fetchByAssoc($result)) {
            $startStage = $row['stage_at_week_start'] ?? '';
            $endStage = $row['stage_at_week_end'] ?? '';

            // Determine movement
            $movement = $this->getMovement($startStage, $endStage, $stageProbabilities);

            // Handle new opportunities (no start stage)
            if (empty($startStage) && !empty($endStage)) {
                $movement = 'new';
            }

            $snapshots[] = [
                'opportunity_id' => $row['opportunity_id'],
                'opportunity_name' => htmlspecialchars($row['opportunity_name'] ?? ''),
                'account_name' => htmlspecialchars($row['account_name'] ?? ''),
                'amount' => (float)($row['amount'] ?? 0),
                'stage_at_week_start' => htmlspecialchars($startStage),
                'stage_at_week_end' => htmlspecialchars($endStage),
                'probability_at_start' => (int)($row['probability_at_start'] ?? 0),
                'probability_at_end' => (int)($row['probability_at_end'] ?? 0),
                'movement' => $movement,
                'assigned_user_id' => $row['assigned_user_id'],
                'lf_weekly_plan_id' => $row['lf_weekly_plan_id']
            ];
        }

        return $snapshots;
    }

    /**
     * Get color based on achievement percentage
     */
    private function getColorForPercent($percent, $config)
    {
        if ($percent >= $config['green_threshold']) {
            return $config['colors']['green'];
        } elseif ($percent >= $config['yellow_threshold']) {
            return $config['colors']['yellow'];
        } elseif ($percent >= $config['orange_threshold']) {
            return $config['colors']['orange'];
        } else {
            return $config['colors']['red'];
        }
    }

    /**
     * Helper to detect movement
     */
    private function getMovement($startStage, $currentStage, $probabilities)
    {
        if ($currentStage === 'Closed Won' || $currentStage === 'closed_won') {
            return 'forward';
        }
        if ($currentStage === 'Closed Lost' || $currentStage === 'closed_lost') {
            return 'backward';
        }
        if (empty($startStage)) {
            return 'static';
        }

        $startProbability = (int) ($probabilities[$startStage] ?? 0);
        $currentProbability = (int) ($probabilities[$currentStage] ?? 0);

        if ($currentProbability > $startProbability) {
            return 'forward';
        } elseif ($currentProbability < $startProbability) {
            return 'backward';
        } else {
            return 'static';
        }
    }

    private function renderHeader()
    {
        echo '<div class="lf-header" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 25px; background: #fff; border-bottom: 2px solid #125EAD;">';
        echo '  <h1 style="color: #125EAD; margin: 0; font-size: 24px;">Weekly Reporting Dashboard</h1>';
        echo '  <div class="lf-brand-logo" style="color: #4BB74E; font-weight: bold;">Logical Front</div>';
        echo '</div>';
    }

    private function renderToolbar($viewMode, $selectedRepId, $weekStart, $reps, $weekList)
    {
        echo '<div class="lf-toolbar" style="padding: 10px 25px; background: #fff; border-bottom: 1px solid #ddd; display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">';

        // View Toggle
        echo '  <div class="lf-view-toggle" style="display: flex; border: 1px solid #125EAD; border-radius: 4px; overflow: hidden;">';
        echo '    <button id="team-view-btn" class="lf-btn lf-active" style="padding: 8px 20px; cursor: pointer; border: none; font-weight: 600; background: #125EAD; color: #fff;">Team View</button>';
        echo '    <button id="rep-view-btn" class="lf-btn" style="padding: 8px 20px; cursor: pointer; border: none; font-weight: 600; background: #fff; color: #125EAD;">Rep View</button>';
        echo '  </div>';

        // Rep Selector
        echo '  <div id="rep-selector-container" class="lf-rep-selector-container lf-hidden" style="display: none;">';
        echo '    <select id="rep-selector" class="lf-select" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; min-width: 200px;">';
        echo '      <option value="">Select Sales Rep...</option>';
        if (!empty($reps)) {
            foreach ($reps as $rep) {
                $selected = $rep['assigned_user_id'] === $selectedRepId ? ' selected' : '';
                $repName = htmlspecialchars(($rep['first_name'] ?? '') . ' ' . ($rep['last_name'] ?? ''));
                echo '      <option value="' . htmlspecialchars($rep['assigned_user_id']) . '"' . $selected . '>' . $repName . '</option>';
            }
        }
        echo '    </select>';
        echo '  </div>';

        // Week Selector
        echo '  <div class="lf-week-selector" style="display: flex; align-items: center; gap: 8px; margin-left: auto;">';
        echo '    <button id="week-back-btn" class="lf-btn-nav" style="padding: 8px 12px; cursor: pointer; background: #fff; border: 1px solid #ddd; border-radius: 4px;">&lt;</button>';
        echo '    <select id="week-selector" class="lf-select" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; min-width: 220px;">';
        foreach ($weekList as $week) {
            $selected = $week['weekStart'] === $weekStart ? ' selected' : '';
            $currentMarker = $week['isCurrent'] ? ' *' : '';
            echo '      <option value="' . htmlspecialchars($week['weekStart']) . '"' . $selected . '>' . htmlspecialchars($week['label']) . $currentMarker . '</option>';
        }
        echo '    </select>';
        echo '    <button id="week-next-btn" class="lf-btn-nav" style="padding: 8px 12px; cursor: pointer; background: #fff; border: 1px solid #ddd; border-radius: 4px;">&gt;</button>';
        echo '    <button id="week-current-btn" class="lf-btn" style="padding: 8px 15px; cursor: pointer; background: #4BB74E; color: #fff; border: none; border-radius: 4px; font-weight: 600; margin-left: 10px;">Current Week</button>';
        echo '  </div>';

        echo '</div>';
    }

    private function renderDashboardContainer()
    {
        echo '<div id="lf-dashboard-container" class="lf-dashboard" style="padding: 25px; display: grid; grid-template-columns: repeat(4, 1fr); gap: 25px;">';
        echo '  <div id="commitment-review-column"></div>';
        echo '  <div id="stage-progression-column"></div>';
        echo '  <div id="reporting-column"></div>';
        echo '  <div id="analysis-column"></div>';
        echo '</div>';
    }
}
