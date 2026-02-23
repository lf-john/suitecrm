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
        $currentQuarter = ceil(date('n', strtotime($weekStart)) / 3);

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
            'commitments' => $this->getWeekCommitments($weekStart, $repId),
            'progressionData' => $this->getStageProgressionData($weekStart, $repId),
            'forecastData' => OpportunityQuery::getForecastOpportunities($currentQuarter, $currentYear, $repId),
            'closedYtd' => OpportunityQuery::getClosedYTD($currentYear, $repId),
        ];
    }

    private function getWeekCommitments($weekStart, $repId = null)
    {
        $db = DBManagerFactory::getInstance();
        $commitments = [];

        // Get plans for this week
        $sql = "SELECT wp.id, wp.assigned_user_id, u.first_name, u.last_name
                FROM lf_weekly_plan wp
                JOIN users u ON wp.assigned_user_id = u.id
                WHERE wp.week_start_date = " . $db->quoted($weekStart) . "
                  AND wp.deleted = 0 AND u.deleted = 0";

        if ($repId) {
            $sql .= " AND wp.assigned_user_id = " . $db->quoted($repId);
        }

        $result = $db->query($sql);
        while ($row = $db->fetchByAssoc($result)) {
            $planId = $row['id'];
            $userId = $row['assigned_user_id'];

            // Get plan items for this plan
            $itemSql = "SELECT * FROM lf_plan_op_items WHERE lf_weekly_plan_id = " . $db->quoted($planId) . " AND deleted = 0";
            $itemResult = $db->query($itemSql);

            $planItems = [];
            $totalClosing = 0;
            $totalProgression = 0;
            $totalNewPipeline = 0;

            while ($item = $db->fetchByAssoc($itemResult)) {
                $planItems[] = $item;
                $amount = (float)($item['planned_amount'] ?? 0);

                if ($item['item_type'] === 'closing') {
                    $totalClosing += $amount;
                } elseif ($item['item_type'] === 'progression') {
                    $totalProgression += $amount;
                }
            }

            // Get prospect items for new pipeline
            $prospectSql = "SELECT * FROM lf_plan_prospect_items WHERE lf_weekly_plan_id = " . $db->quoted($planId) . " AND deleted = 0";
            $prospectResult = $db->query($prospectSql);
            while ($prospect = $db->fetchByAssoc($prospectResult)) {
                $totalNewPipeline += (float)($prospect['expected_value'] ?? 0);
            }

            $commitments[] = [
                'user_id' => $userId,
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'plan_id' => $planId,
                'closing' => $totalClosing,
                'progression' => $totalProgression,
                'new_pipeline' => $totalNewPipeline,
                'items' => $planItems,
            ];
        }

        return $commitments;
    }

    private function getStageProgressionData($weekStart, $repId = null)
    {
        // This would calculate actual progression vs planned
        // For now, return mock structure that JS can render
        return [
            'forward' => 0,
            'backward' => 0,
            'static' => 0,
            'new_pipeline_actual' => 0,
            'progression_actual' => 0,
        ];
    }

    private function renderCommitmentReview($data)
    {
        $reps = $data['reps'];
        $commitments = $data['commitments'];
        $targets = $data['config']['targets'];
        $tiers = $data['config']['achievement_tiers'];

        echo '<div class="lf-section-card">';
        echo '<div class="lf-card-header">';
        echo '<h2>Commitment Review</h2>';
        echo '<div class="timer">10 minutes</div>';
        echo '</div>';
        echo '<div class="lf-card-content">';

        // Overall Achievement Rate
        $totalTarget = count($reps) * ($targets['new_pipeline'] + $targets['progression']);
        $totalActual = 0;
        foreach ($commitments as $c) {
            $totalActual += $c['new_pipeline'] + $c['progression'];
        }
        $overallRate = $totalTarget > 0 ? round(($totalActual / $totalTarget) * 100) : 0;
        $rateClass = $this->getAchievementClass($overallRate, $tiers);

        echo '<div class="lf-info-box" style="text-align: center; margin-bottom: 16px;">';
        echo '<div style="font-size: 32px; font-weight: 700;" class="' . $rateClass . '">' . $overallRate . '%</div>';
        echo '<div style="font-size: 12px; color: #8a8886; text-transform: uppercase;">Overall Achievement Rate</div>';
        echo '</div>';

        // Rep cards
        if (empty($commitments)) {
            echo '<p style="color: #666; text-align: center;">No commitments recorded for this week.</p>';
        } else {
            foreach ($commitments as $commit) {
                $repName = trim($commit['first_name'] . ' ' . $commit['last_name']);
                $newRate = $targets['new_pipeline'] > 0 ? round(($commit['new_pipeline'] / $targets['new_pipeline']) * 100) : 0;
                $progRate = $targets['progression'] > 0 ? round(($commit['progression'] / $targets['progression']) * 100) : 0;
                $avgRate = round(($newRate + $progRate) / 2);
                $tierClass = $this->getAchievementClass($avgRate, $tiers);
                $badgeClass = $this->getBadgeClass($avgRate, $tiers);

                echo '<div class="lf-rep-priority-card" style="margin-bottom: 12px;">';
                echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">';
                echo '<div class="lf-rep-name">' . htmlspecialchars($repName) . '</div>';
                echo '<span class="lf-badge ' . $badgeClass . '">' . $avgRate . '%</span>';
                echo '</div>';
                echo '<div style="display: flex; gap: 12px; flex-wrap: wrap; font-size: 11px;">';
                echo '<span style="background: #125EAD; color: white; padding: 2px 8px; border-radius: 4px;">New: $' . number_format($commit['new_pipeline']) . ' / $' . number_format($targets['new_pipeline']) . ' = ' . $newRate . '%</span>';
                echo '<span style="background: #4BB74E; color: white; padding: 2px 8px; border-radius: 4px;">Progression: $' . number_format($commit['progression']) . ' / $' . number_format($targets['progression']) . ' = ' . $progRate . '%</span>';
                echo '</div>';
                echo '</div>';
            }
        }

        echo '</div>'; // end card-content
        echo '</div>'; // end section-card
    }

    private function renderStageProgression($data)
    {
        $targets = $data['config']['targets'];
        $progression = $data['progressionData'];

        echo '<div class="lf-section-card">';
        echo '<div class="lf-card-header">';
        echo '<h2>Stage Progression</h2>';
        echo '<div class="timer">10 minutes</div>';
        echo '</div>';
        echo '<div class="lf-card-content">';

        // Metrics grid
        echo '<div class="lf-metrics-grid" style="margin-bottom: 16px;">';
        echo '<div class="lf-metric-card" style="border: 2px solid #ff8c00;">';
        echo '<div class="lf-metric-value warning">$' . number_format($progression['new_pipeline_actual']) . ' / $' . number_format($targets['new_pipeline']) . '</div>';
        echo '<div class="lf-metric-label">New Pipeline</div>';
        echo '</div>';
        echo '<div class="lf-metric-card" style="border: 2px solid #4BB74E;">';
        echo '<div class="lf-metric-value success">$' . number_format($progression['progression_actual']) . ' / $' . number_format($targets['progression']) . '</div>';
        echo '<div class="lf-metric-label">Progressed Pipeline</div>';
        echo '</div>';
        echo '</div>';

        // Progression chart
        echo '<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-bottom: 16px;">';
        echo '<div class="lf-metric-card"><div class="lf-metric-value" style="color: #4BB74E;">+' . $progression['forward'] . '</div><div class="lf-metric-label">Forward</div></div>';
        echo '<div class="lf-metric-card"><div class="lf-metric-value" style="color: #d13438;">-' . $progression['backward'] . '</div><div class="lf-metric-label">Backward</div></div>';
        echo '<div class="lf-metric-card"><div class="lf-metric-value" style="color: #8a8886;">' . $progression['static'] . '</div><div class="lf-metric-label">Static</div></div>';
        echo '</div>';

        // Placeholder for success/regression lists
        echo '<div class="lf-info-box">';
        echo '<div class="lf-info-box-title" style="color: #4BB74E;">Progression Success</div>';
        echo '<div class="lf-info-box-content">Data will be populated from actual opportunity changes during the week.</div>';
        echo '</div>';

        echo '</div>'; // end card-content
        echo '</div>'; // end section-card
    }

    private function renderForecastPulse($data)
    {
        $forecastOpps = $data['forecastData'];
        $weekInfo = $data['weekInfo'];

        echo '<div class="lf-section-card">';
        echo '<div class="lf-card-header">';
        echo '<h2>Forecast Pulse</h2>';
        echo '<div class="timer">10 minutes</div>';
        echo '</div>';
        echo '<div class="lf-card-content">';

        echo '<div class="lf-section-title">Q' . $weekInfo['quarter'] . ' ' . $weekInfo['year'] . ' Forecast</div>';

        if (empty($forecastOpps)) {
            echo '<p style="color: #666; text-align: center;">No opportunities in forecast for this quarter.</p>';
        } else {
            $totalForecast = 0;
            foreach ($forecastOpps as $opp) {
                $totalForecast += (float)$opp['amount'];
            }

            echo '<div class="lf-info-box" style="background: #125EAD; color: white; text-align: center; margin-bottom: 16px;">';
            echo '<div style="font-size: 24px; font-weight: 700;">$' . number_format($totalForecast) . '</div>';
            echo '<div style="font-size: 12px; opacity: 0.9;">Total Quarterly Forecast (' . count($forecastOpps) . ' opportunities)</div>';
            echo '</div>';

            echo '<div style="max-height: 300px; overflow-y: auto;">';
            foreach ($forecastOpps as $opp) {
                $probability = (int)preg_replace('/[^0-9]/', '', $opp['sales_stage']);
                $barColor = $probability >= 75 ? '#4BB74E' : ($probability >= 50 ? '#ff8c00' : '#d13438');

                echo '<div class="lf-deal-item" style="padding: 12px; margin-bottom: 8px; background: #f3f2f1; border-radius: 8px;">';
                echo '<div style="display: flex; justify-content: space-between; margin-bottom: 8px;">';
                echo '<div class="lf-deal-name">' . htmlspecialchars($opp['name']) . '</div>';
                echo '<div style="color: #4BB74E; font-weight: 600;">$' . number_format($opp['amount']) . '</div>';
                echo '</div>';
                echo '<div style="background: #e1dfdd; height: 8px; border-radius: 4px; overflow: hidden;">';
                echo '<div style="background: ' . $barColor . '; height: 100%; width: ' . $probability . '%;"></div>';
                echo '</div>';
                echo '<div style="font-size: 11px; color: #8a8886; margin-top: 4px;">' . htmlspecialchars($opp['sales_stage']) . ' &mdash; Close: ' . htmlspecialchars($opp['date_closed']) . '</div>';
                echo '</div>';
            }
            echo '</div>';
        }

        echo '</div>'; // end card-content
        echo '</div>'; // end section-card
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
