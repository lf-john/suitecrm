<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'include/MVC/View/SugarView.php';
require_once 'custom/include/LF_PlanningReporting/WeekHelper.php';
require_once 'custom/include/LF_PlanningReporting/OpportunityQuery.php';
require_once 'custom/include/LF_PlanningReporting/LF_SubHeader.php';
require_once 'custom/modules/LF_PRConfig/LF_PRConfig.php';
require_once 'custom/modules/LF_RepTargets/LF_RepTargets.php';
require_once 'custom/modules/LF_WeeklyPlan/LF_WeeklyPlan.php';

#[\AllowDynamicProperties]
class LF_WeeklyPlanViewReporting extends SugarView
{
    public function __construct()
    {
        parent::__construct();
        $this->options['show_header'] = true;
        $this->options['show_footer'] = false;  // Disable footer to prevent Reset Password modal
    }

    public function display()
    {
        global $current_user;
        $db = DBManagerFactory::getInstance();

        $selectedRepId = $_REQUEST['rep_id'] ?? null;
        $viewMode = $_REQUEST['view_mode'] ?? 'team';

        $configWeekStartDay = WeekHelper::getConfiguredWeekStartDay();
        $rawWeekStart = $_REQUEST['week_start'] ?? WeekHelper::getCurrentWeekStart($configWeekStartDay);

        try {
            $weekStart = WeekHelper::getWeekStart($rawWeekStart, $configWeekStartDay);
        } catch (Exception $e) {
            $weekStart = WeekHelper::getCurrentWeekStart($configWeekStartDay);
        }

        // Clean inputs
        $viewMode = htmlspecialchars($viewMode ?? 'team');
        $selectedRepId = $selectedRepId !== null ? htmlspecialchars($selectedRepId) : null;
        $weekStart = htmlspecialchars($weekStart ?? '');

        // Gather Data
        $data = $this->gatherReportingData($weekStart, $selectedRepId);

        // Include CSS/JS
        echo '<link rel="stylesheet" href="custom/themes/lf_dashboard.css">';
        echo '<script src="custom/modules/LF_WeeklyPlan/js/reporting.js"></script>';

        // Inject Data
        echo '<script>window.LF_REPORTING_DATA = ' . json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';</script>';

        // Main content wrapper with gradient background
        echo '<div class="lf-main-content">';

        // Placeholder for JS-injected subnav
        $isAdmin = $current_user->is_admin ? 'true' : 'false';
        echo '<div id="lf-subnav-placeholder" data-active="report" data-admin="' . $isAdmin . '"></div>';

        // Title Card
        $this->renderTitleCard($viewMode, $selectedRepId, $weekStart, $data['weekList']);

        // Dashboard Grid - 3 equal-height columns
        echo '<div class="lf-dashboard-grid">';

        // Column 1: Commitment Review
        $this->renderCommitmentReview($data);

        // Column 2: Stage Progression
        $this->renderStageProgression($data);

        // Column 3: Forecast Pulse
        $this->renderForecastPulse($data);

        echo '</div>'; // end dashboard grid

        echo '</div>'; // end lf-main-content
    }

    /**
     * Render the title card with view toggle and week selector
     */
    private function renderTitleCard($viewMode, $selectedRepId, $weekStart, $weekList)
    {
        $db = DBManagerFactory::getInstance();
        $isRepView = ($viewMode === 'rep');
        $companyBtnClass = $isRepView ? '' : ' active';
        $repBtnClass = $isRepView ? ' active' : '';
        $repSelectorDisplay = $isRepView ? '' : ' style="display: none;"';

        echo '<div class="lf-page-header">';
        echo '  <div class="lf-title-card">';

        // Left side: Title + View Toggle
        echo '    <div class="lf-title-left">';
        echo '      <h1 class="lf-page-title">WEEKLY REPORTING</h1>';
        echo '      <div class="lf-view-toggle">';
        echo '        <button type="button" id="company-view-btn" class="lf-view-button' . $companyBtnClass . '">Company View</button>';
        echo '        <button type="button" id="rep-view-btn" class="lf-view-button' . $repBtnClass . '">Rep View</button>';
        echo '      </div>';

        // Rep selector (hidden in company view)
        echo '      <select id="rep-selector" class="lf-rep-dropdown"' . $repSelectorDisplay . '>';
        echo '        <option value="">Select Sales Rep...</option>';

        // Get active reps
        $sql = "SELECT rt.assigned_user_id, u.first_name, u.last_name
                FROM lf_rep_targets rt
                JOIN users u ON rt.assigned_user_id = u.id
                WHERE rt.deleted = 0 AND rt.is_active = 1 AND u.deleted = 0
                ORDER BY u.last_name, u.first_name";
        $result = $db->query($sql);
        while ($row = $db->fetchByAssoc($result)) {
            $repName = htmlspecialchars(trim($row['first_name'] . ' ' . $row['last_name']));
            $repId = htmlspecialchars($row['assigned_user_id']);
            $selected = ($repId === $selectedRepId) ? ' selected' : '';
            echo '        <option value="' . $repId . '"' . $selected . '>' . $repName . '</option>';
        }
        echo '      </select>';
        echo '    </div>';

        // Right side: Week selector
        echo '    <div class="lf-title-right">';
        echo '      <div class="lf-week-selector">';
        echo '        <button type="button" class="lf-week-nav-btn" id="week-back">&larr;</button>';
        echo '        <select id="week-select" class="lf-week-dropdown">';
        foreach ($weekList as $week) {
            $selected = ($week['weekStart'] === $weekStart) ? ' selected' : '';
            $marker = !empty($week['isCurrent']) ? ' *' : '';
            echo '          <option value="' . htmlspecialchars($week['weekStart']) . '"' . $selected . '>' . htmlspecialchars($week['label'] . $marker) . '</option>';
        }
        echo '        </select>';
        echo '        <button type="button" class="lf-week-nav-btn" id="week-next">&rarr;</button>';
        echo '        <button type="button" class="lf-week-nav-btn lf-btn-current" id="week-current">Current</button>';
        echo '      </div>';
        echo '    </div>';

        echo '  </div>'; // end lf-title-card
        echo '</div>'; // end lf-page-header
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

    private function gatherReportingData($weekStart, $repId = null)
    {
        $currentYear = (int)date('Y', strtotime($weekStart));
        $configWeekStartDay = WeekHelper::getConfiguredWeekStartDay();
        $isCurrentWeek = WeekHelper::isCurrentWeek($weekStart, $configWeekStartDay);
        $currentQuarter = ceil(date('n', strtotime($weekStart)) / 3);
        $nextQuarter = $currentQuarter < 4 ? $currentQuarter + 1 : 1;
        $nextQuarterYear = $currentQuarter < 4 ? $currentYear : $currentYear + 1;

        // Check if snapshot exists for this week
        $weekEndAt = OpportunityQuery::getSnapshotWeekEndAt($weekStart);
        $hasSnapshot = OpportunityQuery::hasSnapshot($weekEndAt);

        // For non-current weeks without snapshots, show empty data
        $hasData = $isCurrentWeek || $hasSnapshot;

        return [
            'config' => [
                'default_annual_quota' => (float)LF_PRConfig::getConfig('quotas', 'default_annual_quota'),
                'pipeline_coverage_multiplier' => (float)LF_PRConfig::getConfig('quotas', 'pipeline_coverage_multiplier'),
                'achievement_tiers' => [
                    'green' => (int)LF_PRConfig::getConfig('display', 'achievement_tier_green'),
                    'yellow' => (int)LF_PRConfig::getConfig('display', 'achievement_tier_yellow'),
                    'orange' => (int)LF_PRConfig::getConfig('display', 'achievement_tier_orange'),
                ],
                'targets' => [
                    'new_pipeline' => (float)LF_PRConfig::getConfig('targets', 'default_new_pipeline_target'),
                    'progression' => (float)LF_PRConfig::getConfig('targets', 'default_progression_target'),
                    'closed' => (float)LF_PRConfig::getConfig('targets', 'default_closed_target'),
                ],
            ],
            'reps' => LF_RepTargets::getActiveReps(),
            'repTargets' => LF_RepTargets::getTargetsForYear($currentYear),
            'weekInfo' => [
                'currentWeek' => $weekStart,
                'weekEnd' => WeekHelper::getWeekEnd($weekStart),
                'year' => $currentYear,
                'quarter' => $currentQuarter,
            ],
            'weekList' => WeekHelper::getWeekList(12, $configWeekStartDay),
            'commitments' => $hasData ? $this->getWeekCommitments($weekStart, $repId) : [],
            'progressionData' => $hasData ? $this->getStageProgressionData($weekStart, $repId) : [],
            'forecastData' => $isCurrentWeek ? OpportunityQuery::getForecastOpportunities($currentQuarter, $currentYear, $repId) : [],
            'nextQuarterForecast' => $isCurrentWeek ? OpportunityQuery::getForecastOpportunities($nextQuarter, $nextQuarterYear, $repId) : [],
            'nextQuarterInfo' => ['quarter' => $nextQuarter, 'year' => $nextQuarterYear],
            'closedYtd' => OpportunityQuery::getClosedYTD($currentYear, $repId),
        ];
    }

    /**
     * Extract stage probability from stage name like "5-Specifications (30%)"
     */
    private function extractStageProbability($stageName)
    {
        if (empty($stageName)) return 0;
        if (stripos($stageName, 'closed_won') !== false || stripos($stageName, 'Closed Won') !== false) return 100;
        if (stripos($stageName, 'closed_lost') !== false || stripos($stageName, 'Closed Lost') !== false) return 0;
        if (preg_match('/\((\d+)%\)/', $stageName, $m)) return (int)$m[1];
        return 0;
    }

    /**
     * Extract stage number from stage name like "5-Specifications (30%)" -> 5
     */
    private function extractStageNumber($stageName)
    {
        if (empty($stageName)) return 0;
        if (stripos($stageName, 'closed_won') !== false || stripos($stageName, 'Closed Won') !== false) return 99;
        if (stripos($stageName, 'closed_lost') !== false || stripos($stageName, 'Closed Lost') !== false) return 0;
        if (preg_match('/^(\d+)-/', $stageName, $m)) return (int)$m[1];
        return 0;
    }

    /**
     * Get week commitments with per-item scoring data for Actual vs Commitment
     */
    private function getWeekCommitments($weekStart, $repId = null)
    {
        $db = DBManagerFactory::getInstance();

        // Check for snapshot data
        $weekEndAt = OpportunityQuery::getSnapshotWeekEndAt($weekStart);
        $hasSnapshot = OpportunityQuery::hasSnapshot($weekEndAt);

        // Check for next week's snapshot (end-of-week stage)
        $configWeekStartDay = WeekHelper::getConfiguredWeekStartDay();
        $nextWeekStart = date('Y-m-d', strtotime($weekStart . ' +7 days'));
        $nextWeekEndAt = OpportunityQuery::getSnapshotWeekEndAt($nextWeekStart);
        $hasNextSnapshot = OpportunityQuery::hasSnapshot($nextWeekEndAt);

        // Get plans for this week
        $sql = "SELECT wp.id, wp.assigned_user_id, wp.status, u.first_name, u.last_name
                FROM lf_weekly_plan wp
                JOIN users u ON wp.assigned_user_id = u.id
                WHERE wp.week_start_date = " . $db->quoted($weekStart) . "
                  AND wp.deleted = 0 AND u.deleted = 0";
        if ($repId) {
            $sql .= " AND wp.assigned_user_id = " . $db->quoted($repId);
        }

        $result = $db->query($sql);
        $commitments = [];

        while ($row = $db->fetchByAssoc($result)) {
            $planId = $row['id'];
            $userId = $row['assigned_user_id'];

            // Get plan items with snapshot and live stage data
            $itemSql = "SELECT poi.*,
                               COALESCE(s.profit, o.opportunity_profit) AS profit,
                               COALESCE(s.stage_name, o.sales_stage) AS snapshot_stage,
                               o.sales_stage AS live_stage,
                               o.name AS opportunity_name,
                               a.name AS account_name";
            if ($hasNextSnapshot) {
                $itemSql .= ", ns.stage_name AS end_stage";
            }
            $itemSql .= " FROM lf_plan_op_items poi
                    LEFT JOIN opportunities o ON poi.opportunity_id = o.id AND o.deleted = 0
                    LEFT JOIN accounts_opportunities ao ON ao.opportunity_id = o.id AND ao.deleted = 0
                    LEFT JOIN accounts a ON a.id = ao.account_id AND a.deleted = 0";
            if ($hasSnapshot) {
                $itemSql .= " LEFT JOIN opportunity_weekly_snapshot s ON s.opportunity_id = poi.opportunity_id
                        AND s.week_end_at = " . $db->quoted($weekEndAt) . " AND s.deleted = 0";
            } else {
                $itemSql .= " LEFT JOIN (SELECT NULL AS profit, NULL AS stage_name, NULL AS opportunity_id) s ON FALSE";
            }
            if ($hasNextSnapshot) {
                $itemSql .= " LEFT JOIN opportunity_weekly_snapshot ns ON ns.opportunity_id = poi.opportunity_id
                        AND ns.week_end_at = " . $db->quoted($nextWeekEndAt) . " AND ns.deleted = 0";
            }
            $itemSql .= " WHERE poi.lf_weekly_plan_id = " . $db->quoted($planId) . " AND poi.deleted = 0
                      AND poi.projected_stage IS NOT NULL AND poi.projected_stage != ''";
            $itemResult = $db->query($itemSql);

            $items = [];
            $committedClosing = 0;
            $committedProgression = 0;
            $committedNewPipeline = 0;
            $actualClosing = 0;
            $actualProgression = 0;
            $actualNewPipeline = 0;
            $totalScore = 0;
            $itemCount = 0;

            while ($item = $db->fetchByAssoc($itemResult)) {
                $profit = (float)($item['profit'] ?? 0);
                $snapshotStage = $item['snapshot_stage'] ?? $item['live_stage'] ?? '';
                $projectedStage = $item['projected_stage'] ?? '';
                // Use end-of-week snapshot stage if available, otherwise live
                $currentStage = $hasNextSnapshot ? ($item['end_stage'] ?? $item['live_stage'] ?? '') : ($item['live_stage'] ?? '');

                $snapshotProb = $this->extractStageProbability($snapshotStage);
                $projectedProb = $this->extractStageProbability($projectedStage);
                $currentProb = $this->extractStageProbability($currentStage);
                $snapshotNum = $this->extractStageNumber($snapshotStage);
                $projectedNum = $this->extractStageNumber($projectedStage);
                $currentNum = $this->extractStageNumber($currentStage);

                $type = $item['item_type'] ?? '';
                $score = 0;
                $scoreLabel = '';

                if ($type === 'closing') {
                    $committedClosing += $profit;
                    // Also contributes to progression commitment
                    $committedProgression += $profit * ($projectedProb - $snapshotProb) / 100;

                    $isClosedWon = (stripos($currentStage, 'closed_won') !== false || stripos($currentStage, 'Closed Won') !== false);

                    if ($snapshotProb >= 90) {
                        // At 90%+: Closed = 100%, Did not close = 0%
                        if ($isClosedWon) {
                            $score = 100;
                            $scoreLabel = 'Closed Won';
                            $actualClosing += $profit;
                            $actualProgression += $profit * ($currentProb - $snapshotProb) / 100;
                        } else {
                            $score = 0;
                            $scoreLabel = 'Did not close';
                        }
                    } else {
                        // Below 90%: Closed = 100%, Increased but not closed = 50%, No change/backwards = 0%
                        if ($isClosedWon) {
                            $score = 100;
                            $scoreLabel = 'Closed Won';
                            $actualClosing += $profit;
                            $actualProgression += $profit * ($currentProb - $snapshotProb) / 100;
                        } elseif ($currentProb > $snapshotProb) {
                            $score = 50;
                            $scoreLabel = 'Increased, not closed';
                            $actualProgression += $profit * ($currentProb - $snapshotProb) / 100;
                        } else {
                            $score = 0;
                            $scoreLabel = 'No change or backwards';
                        }
                    }
                } elseif ($type === 'progression') {
                    $committedProgression += $profit * ($projectedProb - $snapshotProb) / 100;
                    $stagesPlanned = $projectedNum - $snapshotNum;

                    if ($stagesPlanned <= 1) {
                        // 1 stage: Hit/exceeded = 100%, Failed = 0%
                        if ($currentProb >= $projectedProb) {
                            $score = 100;
                            $scoreLabel = 'Hit target';
                            $actualProgression += $profit * ($currentProb - $snapshotProb) / 100;
                        } else {
                            $score = 0;
                            $scoreLabel = 'Failed';
                            if ($currentProb > $snapshotProb) {
                                $actualProgression += $profit * ($currentProb - $snapshotProb) / 100;
                            }
                        }
                    } else {
                        // 2+ stages: Hit/exceeded = 100%, Increased but not target = 50%, No change/backwards = 0%
                        if ($currentProb >= $projectedProb) {
                            $score = 100;
                            $scoreLabel = 'Hit target';
                            $actualProgression += $profit * ($currentProb - $snapshotProb) / 100;
                        } elseif ($currentProb > $snapshotProb) {
                            $score = 50;
                            $scoreLabel = 'Increased, not target';
                            $actualProgression += $profit * ($currentProb - $snapshotProb) / 100;
                        } else {
                            $score = 0;
                            $scoreLabel = 'No change or backwards';
                        }
                    }
                } elseif ($type === 'developing') {
                    $committedNewPipeline += $profit;
                    // Developing also counts toward progression commitment
                    $committedProgression += $profit * ($projectedProb - $snapshotProb) / 100;
                    // New pipeline: did it progress past analysis?
                    if ($currentProb > $snapshotProb) {
                        $score = 100;
                        $scoreLabel = 'Progressed';
                        $actualNewPipeline += $profit;
                        // Developing also counts toward progression actual
                        $actualProgression += $profit * ($currentProb - $snapshotProb) / 100;
                    } else {
                        $score = 0;
                        $scoreLabel = 'No progress';
                    }
                }

                $totalScore += $score;
                $itemCount++;

                $items[] = [
                    'opportunity_name' => $item['opportunity_name'] ?? '',
                    'account_name' => $item['account_name'] ?? '',
                    'profit' => $profit,
                    'item_type' => $type,
                    'snapshot_stage' => $snapshotStage,
                    'projected_stage' => $projectedStage,
                    'current_stage' => $currentStage,
                    'score' => $score,
                    'score_label' => $scoreLabel,
                ];
            }

            // Get prospect items — check converted opportunity for scoring
            $prospectSql = "SELECT pi.*, o.name AS conv_opp_name, o.opportunity_profit AS conv_profit,
                                   o.sales_stage AS conv_stage, a.name AS conv_account_name
                            FROM lf_plan_prospect_items pi
                            LEFT JOIN opportunities o ON pi.converted_opportunity_id = o.id AND o.deleted = 0
                            LEFT JOIN accounts_opportunities ao ON ao.opportunity_id = o.id AND ao.deleted = 0
                            LEFT JOIN accounts a ON a.id = ao.account_id AND a.deleted = 0
                            WHERE pi.lf_weekly_plan_id = " . $db->quoted($planId) . " AND pi.deleted = 0";
            $prospectResult = $db->query($prospectSql);
            while ($prospect = $db->fetchByAssoc($prospectResult)) {
                $committedProfit = (float)($prospect['expected_profit'] ?? 0);
                $committedNewPipeline += $committedProfit;
                // Prospecting also counts toward progression commitment
                $committedProgression += $committedProfit;

                $prospectStatus = $prospect['status'] ?? 'planned';
                $score = 0;
                $scoreLabel = 'Pending';
                $prospectOppName = $prospect['plan_description'] ?? 'Prospecting';
                $prospectAcctName = $prospect['source_type'] ?? 'Prospecting';
                $prospectStage = '';
                $actualProspectProfit = 0;

                if ($prospectStatus === 'converted' && !empty($prospect['converted_opportunity_id'])) {
                    // Dollar-based scoring: Created $X / Committed $Y
                    $actualProspectProfit = (float)($prospect['conv_profit'] ?? 0);
                    $score = $committedProfit > 0 ? round(($actualProspectProfit / $committedProfit) * 100) : 0;
                    $scoreLabel = 'Created $' . number_format($actualProspectProfit) . ' / $' . number_format($committedProfit);
                    $prospectOppName = $prospect['conv_opp_name'] ?? $prospectOppName;
                    $prospectAcctName = $prospect['conv_account_name'] ?? $prospectAcctName;
                    $prospectStage = $prospect['conv_stage'] ?? '';
                    $actualNewPipeline += $actualProspectProfit;
                    // Prospecting also counts toward progression
                    $actualProgression += $actualProspectProfit;
                } elseif ($prospectStatus === 'no_opportunity') {
                    $score = 0;
                    $scoreLabel = 'No opportunity';
                }

                $items[] = [
                    'opportunity_name' => $prospectOppName,
                    'account_name' => $prospectAcctName,
                    'profit' => $committedProfit,
                    'item_type' => 'prospecting',
                    'snapshot_stage' => '',
                    'projected_stage' => 'New Opportunity',
                    'current_stage' => $prospectStage,
                    'score' => min($score, 200), // Cap at 200% for display
                    'score_label' => $scoreLabel,
                ];
                $totalScore += $score;
                $itemCount++;
            }

            // Detect unplanned movers — snapshot opps that moved forward but aren't in the plan
            $plannedOppIds = array_column($items, 'opportunity_id'); // won't have this key
            // Get all plan item opp IDs for this plan
            $planOppIdsSql = "SELECT opportunity_id FROM lf_plan_op_items WHERE lf_weekly_plan_id = " . $db->quoted($planId) . " AND deleted = 0 AND opportunity_id IS NOT NULL AND projected_stage IS NOT NULL AND projected_stage != ''";
            $planOppIdsResult = $db->query($planOppIdsSql);
            $planOppIds = [];
            while ($r = $db->fetchByAssoc($planOppIdsResult)) {
                $planOppIds[] = $r['opportunity_id'];
            }

            // Find snapshot opportunities for this user that moved forward and aren't planned
            $unplannedSql = "SELECT s.opportunity_id, s.stage_name AS snapshot_stage, s.profit AS snapshot_profit,
                                    o.name AS opportunity_name, o.sales_stage AS live_stage,
                                    o.opportunity_profit AS live_profit,
                                    a.name AS account_name";
            if ($hasNextSnapshot) {
                $unplannedSql .= ", ns.stage_name AS end_stage";
            }
            $unplannedSql .= " FROM opportunity_weekly_snapshot s
                    LEFT JOIN opportunities o ON s.opportunity_id = o.id AND o.deleted = 0
                    LEFT JOIN accounts_opportunities ao ON ao.opportunity_id = o.id AND ao.deleted = 0
                    LEFT JOIN accounts a ON a.id = ao.account_id AND a.deleted = 0";
            if ($hasNextSnapshot) {
                $unplannedSql .= " LEFT JOIN opportunity_weekly_snapshot ns ON ns.opportunity_id = s.opportunity_id
                        AND ns.week_end_at = " . $db->quoted($nextWeekEndAt) . " AND ns.deleted = 0";
            }
            $unplannedSql .= " WHERE s.week_end_at = " . $db->quoted($weekEndAt) . " AND s.deleted = 0
                    AND o.assigned_user_id = " . $db->quoted($userId) . "
                    AND s.stage_name NOT LIKE '%Closed%' AND s.stage_name NOT LIKE '%closed%'";

            $unplannedResult = $db->query($unplannedSql);
            while ($uRow = $db->fetchByAssoc($unplannedResult)) {
                $uOppId = $uRow['opportunity_id'];
                if (in_array($uOppId, $planOppIds)) continue; // already planned

                $uSnapshotStage = $uRow['snapshot_stage'] ?? '';
                $uCurrentStage = $hasNextSnapshot ? ($uRow['end_stage'] ?? $uRow['live_stage'] ?? '') : ($uRow['live_stage'] ?? '');
                $uSnapshotProb = $this->extractStageProbability($uSnapshotStage);
                $uCurrentProb = $this->extractStageProbability($uCurrentStage);
                $uProfit = (float)($uRow['snapshot_profit'] ?? 0);

                if ($uCurrentProb > $uSnapshotProb) {
                    // Unplanned forward movement — categorize as progression or developing based on start stage
                    $analysisProb = $this->extractStageProbability(LF_PRConfig::getConfig('stages', 'analysis_stage') ?: '2-Analysis (0%)');
                    $isDeveloping = ($uSnapshotProb <= $analysisProb);
                    $items[] = [
                        'opportunity_name' => ($uRow['opportunity_name'] ?? '') . ' (Unplanned)',
                        'account_name' => $uRow['account_name'] ?? '',
                        'profit' => $uProfit,
                        'item_type' => $isDeveloping ? 'developing' : 'progression',
                        'snapshot_stage' => $uSnapshotStage,
                        'projected_stage' => 'Unplanned',
                        'current_stage' => $uCurrentStage,
                        'score' => 100,
                        'score_label' => 'Unplanned Bonus',
                    ];
                    // Add to actual progression
                    $actualProgression += $uProfit * ($uCurrentProb - $uSnapshotProb) / 100;
                    // Developing-level unplanned also counts toward New Pipeline
                    if ($isDeveloping) {
                        $actualNewPipeline += $uProfit;
                    }
                    $totalScore += 100;
                    $itemCount++;
                }
            }

            $commitments[] = [
                'user_id' => $userId,
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'plan_id' => $planId,
                'committed_closing' => $committedClosing,
                'committed_progression' => $committedProgression,
                'committed_new_pipeline' => $committedNewPipeline,
                'actual_closing' => $actualClosing,
                'actual_progression' => $actualProgression,
                'actual_new_pipeline' => $actualNewPipeline,
                'avg_score' => $itemCount > 0 ? round($totalScore / $itemCount) : 0,
                'items' => $items,
            ];
        }

        return $commitments;
    }

    /**
     * Get stage progression data — compare start-of-week snapshot vs end-of-week snapshot (or live)
     */
    private function getStageProgressionData($weekStart, $repId = null)
    {
        $db = DBManagerFactory::getInstance();

        $weekEndAt = OpportunityQuery::getSnapshotWeekEndAt($weekStart);
        $hasSnapshot = OpportunityQuery::hasSnapshot($weekEndAt);

        if (!$hasSnapshot) {
            return ['forward' => 0, 'backward' => 0, 'static' => 0,
                    'forward_value' => 0, 'backward_value' => 0, 'opportunities' => []];
        }

        // Check for next week's snapshot (end-of-week comparison)
        $nextWeekStart = date('Y-m-d', strtotime($weekStart . ' +7 days'));
        $nextWeekEndAt = OpportunityQuery::getSnapshotWeekEndAt($nextWeekStart);
        $hasNextSnapshot = OpportunityQuery::hasSnapshot($nextWeekEndAt);

        // Get all opportunities from this week's snapshot
        $sql = "SELECT s.opportunity_id, s.stage_name AS start_stage, s.profit,
                       o.name AS opportunity_name, o.sales_stage AS live_stage,
                       a.name AS account_name, o.assigned_user_id";
        if ($hasNextSnapshot) {
            $sql .= ", ns.stage_name AS end_stage";
        }
        $sql .= " FROM opportunity_weekly_snapshot s
                   LEFT JOIN opportunities o ON s.opportunity_id = o.id AND o.deleted = 0
                   LEFT JOIN accounts_opportunities ao ON ao.opportunity_id = o.id AND ao.deleted = 0
                   LEFT JOIN accounts a ON a.id = ao.account_id AND a.deleted = 0";
        if ($hasNextSnapshot) {
            $sql .= " LEFT JOIN opportunity_weekly_snapshot ns ON ns.opportunity_id = s.opportunity_id
                        AND ns.week_end_at = " . $db->quoted($nextWeekEndAt) . " AND ns.deleted = 0";
        }
        $sql .= " WHERE s.week_end_at = " . $db->quoted($weekEndAt) . " AND s.deleted = 0";
        if ($repId) {
            $sql .= " AND o.assigned_user_id = " . $db->quoted($repId);
        }
        // Exclude closed opportunities from snapshot
        $sql .= " AND s.stage_name NOT LIKE '%Closed%' AND s.stage_name NOT LIKE '%closed%'";

        $result = $db->query($sql);

        $forward = 0;
        $backward = 0;
        $static = 0;
        $forwardValue = 0;
        $backwardValue = 0;
        $opportunities = [];

        while ($row = $db->fetchByAssoc($result)) {
            $startStage = $row['start_stage'] ?? '';
            // Use end-of-week snapshot if available, otherwise live stage
            $endStage = $hasNextSnapshot ? ($row['end_stage'] ?? $row['live_stage'] ?? '') : ($row['live_stage'] ?? '');
            $profit = (float)($row['profit'] ?? 0);

            $startProb = $this->extractStageProbability($startStage);
            $endProb = $this->extractStageProbability($endStage);
            $dollarMovement = $profit * ($endProb - $startProb) / 100;

            $direction = 'static';
            if ($endProb > $startProb) {
                $direction = 'forward';
                $forward++;
                $forwardValue += $dollarMovement;
            } elseif ($endProb < $startProb) {
                $direction = 'backward';
                $backward++;
                $backwardValue += abs($dollarMovement);
            } else {
                $static++;
            }

            // Only include opportunities with movement in the detail list
            if ($direction !== 'static') {
                $opportunities[] = [
                    'name' => $row['opportunity_name'] ?? '',
                    'account_name' => $row['account_name'] ?? '',
                    'profit' => $profit,
                    'start_stage' => $startStage,
                    'end_stage' => $endStage,
                    'direction' => $direction,
                    'dollar_movement' => $dollarMovement,
                ];
            }
        }

        // Sort by absolute dollar movement descending
        usort($opportunities, function($a, $b) {
            return abs($b['dollar_movement']) - abs($a['dollar_movement']);
        });

        return [
            'forward' => $forward,
            'backward' => $backward,
            'static' => $static,
            'forward_value' => $forwardValue,
            'backward_value' => $backwardValue,
            'opportunities' => $opportunities,
        ];
    }

    private function renderCommitmentReview($data)
    {
        $commitments = $data['commitments'];
        $tiers = $data['config']['achievement_tiers'];

        echo '<div class="lf-section-card">';
        echo '<div class="lf-card-header">';
        echo '<h2>Actual vs Commitment</h2>';
        echo '<div class="timer">10 minutes</div>';
        echo '</div>';
        echo '<div class="lf-card-content">';

        if (empty($commitments)) {
            echo '<p style="color: #666; text-align: center;">No commitments recorded for this week.</p>';
            echo '</div></div>';
            return;
        }

        // Calculate aggregate totals across all reps
        $totalCommittedProgression = 0;
        $totalActualProgression = 0;
        $totalCommittedNewPipeline = 0;
        $totalActualNewPipeline = 0;
        foreach ($commitments as $c) {
            $totalCommittedProgression += $c['committed_progression'];
            $totalActualProgression += $c['actual_progression'];
            $totalCommittedNewPipeline += $c['committed_new_pipeline'];
            $totalActualNewPipeline += $c['actual_new_pipeline'];
        }

        $progressionPct = $totalCommittedProgression > 0 ? round(($totalActualProgression / $totalCommittedProgression) * 100) : 0;
        $newPipelinePct = $totalCommittedNewPipeline > 0 ? round(($totalActualNewPipeline / $totalCommittedNewPipeline) * 100) : 0;

        $progColor = $this->getPercentageColor($progressionPct);
        $newColor = $this->getPercentageColor($newPipelinePct);

        // Top section: Two large percentages
        echo '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px;">';
        echo '<div style="background: #faf9f8; padding: 16px; border-radius: 8px; text-align: center;">';
        echo '<div style="font-size: 36px; font-weight: 700; color: ' . $progColor . ';">' . $progressionPct . '%</div>';
        echo '<div style="font-size: 12px; color: #605e5c; font-weight: 600;">Progressed</div>';
        echo '<div style="font-size: 10px; color: #8a8886; margin-top: 4px;">$' . number_format($totalActualProgression) . ' / $' . number_format($totalCommittedProgression) . '</div>';
        echo '</div>';
        echo '<div style="background: #faf9f8; padding: 16px; border-radius: 8px; text-align: center;">';
        echo '<div style="font-size: 36px; font-weight: 700; color: ' . $newColor . ';">' . $newPipelinePct . '%</div>';
        echo '<div style="font-size: 12px; color: #605e5c; font-weight: 600;">New</div>';
        echo '<div style="font-size: 10px; color: #8a8886; margin-top: 4px;">$' . number_format($totalActualNewPipeline) . ' / $' . number_format($totalCommittedNewPipeline) . '</div>';
        echo '</div>';
        echo '</div>';

        // Bottom section: Per-item cards grouped by rep
        foreach ($commitments as $commit) {
            $repName = trim($commit['first_name'] . ' ' . $commit['last_name']);

            echo '<div style="margin-bottom: 16px;">';
            echo '<div style="font-weight: 600; font-size: 13px; color: #323130; margin-bottom: 8px; padding-bottom: 4px; border-bottom: 1px solid #edebe9;">';
            echo htmlspecialchars($repName);
            echo '<span style="float: right; font-size: 11px; color: #8a8886;">Avg: ' . $commit['avg_score'] . '%</span>';
            echo '</div>';

            // Group items by type
            $grouped = ['closing' => [], 'progression' => [], 'developing' => [], 'prospecting' => []];
            foreach ($commit['items'] as $item) {
                $type = $item['item_type'] ?? 'other';
                if (isset($grouped[$type])) {
                    $grouped[$type][] = $item;
                }
            }

            // Render each category
            $categoryConfig = [
                'closing' => ['label' => 'Closing', 'color' => '#d13438'],
                'progression' => ['label' => 'Progression', 'color' => '#4BB74E'],
                'developing' => ['label' => 'New Pipeline', 'color' => '#125EAD'],
                'prospecting' => ['label' => 'Prospecting', 'color' => '#7b1fa2'],
            ];

            foreach ($categoryConfig as $type => $cfg) {
                $typeItems = $grouped[$type];
                if (empty($typeItems)) continue;

                echo '<div style="margin-bottom: 8px;">';
                echo '<div style="font-size: 10px; font-weight: 600; color: ' . $cfg['color'] . '; text-transform: uppercase; margin-bottom: 4px;">' . $cfg['label'] . '</div>';

                foreach ($typeItems as $item) {
                    $scoreColor = $item['score'] >= 100 ? '#4BB74E' : ($item['score'] >= 50 ? '#ff8c00' : '#d13438');
                    $scoreBg = $item['score'] >= 100 ? '#e8f5e9' : ($item['score'] >= 50 ? '#fff3e0' : '#fce4ec');

                    echo '<div style="padding: 8px 10px; margin-bottom: 4px; background: #faf9f8; border-radius: 6px; border-left: 3px solid ' . $cfg['color'] . ';">';
                    echo '<div style="display: flex; justify-content: space-between; align-items: center;">';
                    echo '<div>';
                    echo '<div style="font-size: 12px; font-weight: 600; color: #323130;">' . htmlspecialchars($item['account_name'] ?? '') . '</div>';
                    echo '<div style="font-size: 11px; color: #605e5c;">' . htmlspecialchars($item['opportunity_name'] ?? '') . '</div>';
                    if (!empty($item['snapshot_stage'])) {
                        echo '<div style="font-size: 10px; color: #8a8886; margin-top: 2px;">';
                        echo htmlspecialchars($item['snapshot_stage']) . ' &rarr; ' . htmlspecialchars($item['projected_stage']);
                        if (!empty($item['current_stage']) && $item['current_stage'] !== $item['snapshot_stage']) {
                            echo ' (now: ' . htmlspecialchars($item['current_stage']) . ')';
                        }
                        echo '</div>';
                    }
                    echo '</div>';
                    echo '<div style="text-align: right;">';
                    echo '<div style="font-size: 11px; color: #323130; font-weight: 600;">$' . number_format($item['profit']) . '</div>';
                    echo '<div style="font-size: 11px; font-weight: 700; color: ' . $scoreColor . '; background: ' . $scoreBg . '; padding: 2px 8px; border-radius: 4px; display: inline-block;">' . $item['score'] . '% ' . htmlspecialchars($item['score_label']) . '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }

                echo '</div>';
            }

            echo '</div>';
        }

        echo '</div>'; // end card-content
        echo '</div>'; // end section-card
    }

    private function renderStageProgression($data)
    {
        $progression = $data['progressionData'];
        $targets = $data['config']['targets'];
        $repTargets = $data['repTargets'] ?? [];
        $commitments = $data['commitments'] ?? [];

        // Calculate total expected and actual
        $repCount = max(count($commitments), 1);
        $expectedProgression = $repCount * (float)($targets['progression'] ?? 0);
        $expectedNewPipeline = $repCount * (float)($targets['new_pipeline'] ?? 0);

        $actualProgression = 0;
        $actualNewPipeline = 0;
        foreach ($commitments as $c) {
            $actualProgression += $c['actual_progression'];
            $actualNewPipeline += $c['actual_new_pipeline'];
        }

        $progPct = $expectedProgression > 0 ? round(($actualProgression / $expectedProgression) * 100) : 0;
        $newPct = $expectedNewPipeline > 0 ? round(($actualNewPipeline / $expectedNewPipeline) * 100) : 0;

        echo '<div class="lf-section-card">';
        echo '<div class="lf-card-header">';
        echo '<h2>Actual vs Expected</h2>';
        echo '<div class="timer">10 minutes</div>';
        echo '</div>';
        echo '<div class="lf-card-content">';

        // Actual vs Expected summary
        echo '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">';
        echo '<div style="background: #faf9f8; padding: 12px; border-radius: 8px; text-align: center;">';
        echo '<div style="font-size: 24px; font-weight: 700; color: #4BB74E;">$' . number_format($actualProgression) . '</div>';
        echo '<div style="font-size: 11px; color: #605e5c;">Progression Actual</div>';
        echo '<div style="font-size: 10px; color: #8a8886; margin-top: 4px;">vs $' . number_format($expectedProgression) . ' expected (' . $progPct . '%)</div>';
        echo '</div>';
        echo '<div style="background: #faf9f8; padding: 12px; border-radius: 8px; text-align: center;">';
        echo '<div style="font-size: 24px; font-weight: 700; color: #125EAD;">$' . number_format($actualNewPipeline) . '</div>';
        echo '<div style="font-size: 11px; color: #605e5c;">New Pipeline Actual</div>';
        echo '<div style="font-size: 10px; color: #8a8886; margin-top: 4px;">vs $' . number_format($expectedNewPipeline) . ' expected (' . $newPct . '%)</div>';
        echo '</div>';
        echo '</div>';

        // Movement counts with dollar values
        echo '<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-bottom: 16px;">';

        echo '<div style="background: #e8f5e9; padding: 12px; border-radius: 8px; text-align: center;">';
        echo '<div style="font-size: 28px; font-weight: 700; color: #4BB74E;">+' . $progression['forward'] . '</div>';
        echo '<div style="font-size: 11px; color: #2e7d32; font-weight: 600;">Forward</div>';
        echo '<div style="font-size: 11px; color: #4BB74E; margin-top: 4px;">$' . number_format($progression['forward_value']) . '</div>';
        echo '</div>';

        echo '<div style="background: #fce4ec; padding: 12px; border-radius: 8px; text-align: center;">';
        echo '<div style="font-size: 28px; font-weight: 700; color: #d13438;">-' . $progression['backward'] . '</div>';
        echo '<div style="font-size: 11px; color: #c62828; font-weight: 600;">Backward</div>';
        echo '<div style="font-size: 11px; color: #d13438; margin-top: 4px;">$' . number_format($progression['backward_value']) . '</div>';
        echo '</div>';

        echo '<div style="background: #f5f5f5; padding: 12px; border-radius: 8px; text-align: center;">';
        echo '<div style="font-size: 28px; font-weight: 700; color: #8a8886;">' . $progression['static'] . '</div>';
        echo '<div style="font-size: 11px; color: #666; font-weight: 600;">Static</div>';
        echo '<div style="font-size: 11px; color: #8a8886; margin-top: 4px;">No change</div>';
        echo '</div>';

        echo '</div>';

        // Per-opportunity breakdown
        $opps = $progression['opportunities'] ?? [];
        if (empty($opps)) {
            echo '<div style="padding: 20px; text-align: center; color: #999; font-size: 12px;">No stage movements detected this week.</div>';
        } else {
            // Forward movements
            $forwardOpps = array_filter($opps, fn($o) => $o['direction'] === 'forward');
            $backwardOpps = array_filter($opps, fn($o) => $o['direction'] === 'backward');

            if (!empty($forwardOpps)) {
                echo '<div style="margin-bottom: 12px;">';
                echo '<div style="font-size: 11px; font-weight: 600; color: #4BB74E; text-transform: uppercase; margin-bottom: 6px;">Forward Movement</div>';
                echo '<div style="max-height: 200px; overflow-y: auto;">';
                foreach ($forwardOpps as $opp) {
                    echo '<div style="padding: 8px 10px; margin-bottom: 4px; background: #f1f8e9; border-radius: 6px; border-left: 3px solid #4BB74E;">';
                    echo '<div style="display: flex; justify-content: space-between; align-items: center;">';
                    echo '<div>';
                    echo '<div style="font-size: 12px; font-weight: 600; color: #323130;">' . htmlspecialchars($opp['account_name']) . '</div>';
                    echo '<div style="font-size: 11px; color: #605e5c;">' . htmlspecialchars($opp['name']) . '</div>';
                    echo '<div style="font-size: 10px; color: #8a8886; margin-top: 2px;">' . htmlspecialchars($opp['start_stage']) . ' &rarr; ' . htmlspecialchars($opp['end_stage']) . '</div>';
                    echo '</div>';
                    echo '<div style="text-align: right;">';
                    echo '<div style="font-size: 11px; font-weight: 600; color: #4BB74E;">+$' . number_format(abs($opp['dollar_movement'])) . '</div>';
                    echo '<div style="font-size: 10px; color: #8a8886;">$' . number_format($opp['profit']) . ' profit</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
                echo '</div>';
                echo '</div>';
            }

            if (!empty($backwardOpps)) {
                echo '<div style="margin-bottom: 12px;">';
                echo '<div style="font-size: 11px; font-weight: 600; color: #d13438; text-transform: uppercase; margin-bottom: 6px;">Backward Movement</div>';
                echo '<div style="max-height: 200px; overflow-y: auto;">';
                foreach ($backwardOpps as $opp) {
                    echo '<div style="padding: 8px 10px; margin-bottom: 4px; background: #fce4ec; border-radius: 6px; border-left: 3px solid #d13438;">';
                    echo '<div style="display: flex; justify-content: space-between; align-items: center;">';
                    echo '<div>';
                    echo '<div style="font-size: 12px; font-weight: 600; color: #323130;">' . htmlspecialchars($opp['account_name']) . '</div>';
                    echo '<div style="font-size: 11px; color: #605e5c;">' . htmlspecialchars($opp['name']) . '</div>';
                    echo '<div style="font-size: 10px; color: #8a8886; margin-top: 2px;">' . htmlspecialchars($opp['start_stage']) . ' &rarr; ' . htmlspecialchars($opp['end_stage']) . '</div>';
                    echo '</div>';
                    echo '<div style="text-align: right;">';
                    echo '<div style="font-size: 11px; font-weight: 600; color: #d13438;">-$' . number_format(abs($opp['dollar_movement'])) . '</div>';
                    echo '<div style="font-size: 10px; color: #8a8886;">$' . number_format($opp['profit']) . ' profit</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
                echo '</div>';
                echo '</div>';
            }
        }

        echo '</div>'; // end card-content
        echo '</div>'; // end section-card
    }

    private function renderForecastPulse($data)
    {
        $weekInfo = $data['weekInfo'];

        echo '<div class="lf-section-card">';
        echo '<div class="lf-card-header">';
        echo '<h2>Forecast Pulse</h2>';
        echo '<div class="timer">10 minutes</div>';
        echo '</div>';
        echo '<div class="lf-card-content">';

        // Render both current and next quarter
        $quarters = [
            ['opps' => $data['forecastData'], 'q' => $weekInfo['quarter'], 'y' => $weekInfo['year'], 'label' => 'Current Quarter'],
            ['opps' => $data['nextQuarterForecast'] ?? [], 'q' => $data['nextQuarterInfo']['quarter'] ?? '', 'y' => $data['nextQuarterInfo']['year'] ?? '', 'label' => 'Next Quarter'],
        ];

        foreach ($quarters as $qi) {
            $forecastOpps = $qi['opps'];
            echo '<div class="lf-section-title" style="margin-top: 8px;">Q' . $qi['q'] . ' ' . $qi['y'] . ' Forecast (' . $qi['label'] . ')</div>';

            // Filter out 1% (Analysis) stage opportunities
            $forecastOpps = array_filter($forecastOpps, function($opp) {
                $pct = 0;
                if (preg_match('/\((\d+)%\)/', $opp['sales_stage'], $m)) {
                    $pct = (int)$m[1];
                }
                return $pct > 1;
            });

            if (empty($forecastOpps)) {
                echo '<p style="color: #666; text-align: center; margin-bottom: 16px;">No opportunities in forecast for Q' . $qi['q'] . '.</p>';
            } else {
                $totalWeightedProfit = 0;
                $totalRawProfit = 0;
                foreach ($forecastOpps as $opp) {
                    $rawProfit = (float)($opp['opportunity_profit'] ?? 0);
                    $prob = 0;
                    if (!empty($opp['probability']) && (int)$opp['probability'] > 0) {
                        $prob = (int)$opp['probability'];
                    } elseif (preg_match('/\((\d+)%\)/', $opp['sales_stage'], $m)) {
                        $prob = (int)$m[1];
                    }
                    $totalWeightedProfit += $rawProfit * $prob / 100;
                    $totalRawProfit += $rawProfit;
                }

                echo '<div class="lf-info-box" style="background: #125EAD; color: white; text-align: center; margin-bottom: 16px;">';
                echo '<div style="font-size: 24px; font-weight: 700;">$' . number_format($totalWeightedProfit) . '</div>';
                echo '<div style="font-size: 12px; opacity: 0.9;">Weighted Forecast (' . count($forecastOpps) . ' opps, $' . number_format($totalRawProfit) . ' total profit)</div>';
                echo '</div>';

                echo '<div style="max-height: 250px; overflow-y: auto; margin-bottom: 16px;">';
                foreach ($forecastOpps as $opp) {
                    // Use probability field if set, otherwise extract from stage name
                    $probability = 0;
                    if (!empty($opp['probability']) && (int)$opp['probability'] > 0) {
                        $probability = (int)$opp['probability'];
                    } elseif (preg_match('/\((\d+)%\)/', $opp['sales_stage'], $m)) {
                        $probability = (int)$m[1];
                    }
                    $barColor = $probability >= 75 ? '#4BB74E' : ($probability >= 50 ? '#ff8c00' : '#d13438');
                    $oppProfit = (float)($opp['opportunity_profit'] ?? 0);
                    $weightedValue = $oppProfit * $probability / 100;

                    echo '<div class="lf-deal-item" style="padding: 12px; margin-bottom: 8px; background: #f3f2f1; border-radius: 8px;">';
                    echo '<div style="display: flex; justify-content: space-between; margin-bottom: 8px;">';
                    echo '<div class="lf-deal-name">' . htmlspecialchars($opp['name']) . '</div>';
                    echo '<div style="color: #4BB74E; font-weight: 600;">$' . number_format($weightedValue) . '</div>';
                    echo '</div>';
                    echo '<div style="background: #e1dfdd; height: 8px; border-radius: 4px; overflow: hidden;">';
                    echo '<div style="background: ' . $barColor . '; height: 100%; width: ' . $probability . '%;"></div>';
                    echo '</div>';
                    echo '<div style="font-size: 11px; color: #8a8886; margin-top: 8px;">' . $probability . '% &mdash; $' . number_format($oppProfit) . ' profit &mdash; Close: ' . htmlspecialchars($opp['date_closed']) . '</div>';
                    echo '</div>';
                }
                echo '</div>';
            }
        }

        echo '</div>'; // end card-content
        echo '</div>'; // end section-card
    }

    private function getPercentageColor($pct)
    {
        if ($pct >= 80) return '#4BB74E';
        if ($pct >= 50) return '#ff8c00';
        return '#d13438';
    }

    private function getAchievementClass($rate, $tiers)
    {
        if ($rate >= $tiers['green']) return 'achievement-green';
        if ($rate >= $tiers['yellow']) return 'achievement-yellow';
        if ($rate >= $tiers['orange']) return 'achievement-orange';
        return 'achievement-red';
    }

    private function getBadgeClass($rate, $tiers)
    {
        if ($rate >= $tiers['green']) return 'tier-green';
        if ($rate >= $tiers['yellow']) return 'tier-yellow';
        if ($rate >= $tiers['orange']) return 'tier-orange';
        return 'tier-red';
    }
}
