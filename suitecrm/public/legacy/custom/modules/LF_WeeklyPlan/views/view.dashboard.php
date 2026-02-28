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
class LF_WeeklyPlanViewDashboard extends SugarView
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

        $viewMode = $_REQUEST['view_mode'] ?? 'team';
        $selectedRepId = $_REQUEST['rep_id'] ?? null;

        $configWeekStartDay = WeekHelper::getConfiguredWeekStartDay();
        $rawWeekStart = $_REQUEST['week_start'] ?? WeekHelper::getCurrentWeekStart($configWeekStartDay);

        try {
            // Validate and normalize: ensure it aligns with configured start day
            $weekStart = WeekHelper::getWeekStart($rawWeekStart, $configWeekStartDay);
        } catch (Exception $e) {
            // Fallback to current week if date is invalid
            $weekStart = WeekHelper::getCurrentWeekStart($configWeekStartDay);
        }

        // Clean input (handle null values for PHP 8.1+ compatibility)
        $viewMode = htmlspecialchars($viewMode ?? 'team');
        $selectedRepId = $selectedRepId !== null ? htmlspecialchars($selectedRepId) : null;
        $weekStart = htmlspecialchars($weekStart ?? '');

        // Gather Data
        $data = $this->gatherDashboardData($weekStart, $selectedRepId);

        // Get all active users for admin user selector
        $allUsers = [];
        if ($current_user->is_admin) {
            $allUsers = $this->getActiveUsers();
        }

        // Include CSS/JS
        echo '<link rel="stylesheet" href="custom/themes/lf_dashboard.css">';
        echo '<script src="custom/modules/LF_WeeklyPlan/js/dashboard.js"></script>';

        // Inject Data
        echo '<script>window.LF_DASHBOARD_DATA = ' . json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';</script>';

        // Main content wrapper with gradient background
        echo '<div class="lf-main-content">';

        // Placeholder for JS-injected subnav
        $isAdmin = $current_user->is_admin ? 'true' : 'false';
        echo '<div id="lf-subnav-placeholder" data-active="plan" data-admin="' . $isAdmin . '"></div>';

        // Title Card
        $this->renderTitleCard($viewMode, $selectedRepId, $weekStart, $data['weekList']);

        // Dashboard Grid
        $this->renderDashboardContainer($viewMode, $selectedRepId);

        echo '</div>'; // end lf-main-content
    }

    /**
     * Render the title card with view toggle and week selector
     */
    private function renderTitleCard($viewMode, $selectedRepId, $weekStart, $weekList)
    {
        $isRepView = ($viewMode === 'rep');
        $companyBtnClass = $isRepView ? '' : ' active';
        $repBtnClass = $isRepView ? ' active' : '';
        $repSelectorDisplay = $isRepView ? '' : ' style="display: none;"';

        echo '<div class="lf-page-header">';
        echo '  <div class="lf-title-card">';

        // Left side: Title + View Toggle
        echo '    <div class="lf-title-left">';
        echo '      <h1 class="lf-page-title">WEEKLY PLANNING</h1>';
        echo '      <div class="lf-view-toggle">';
        echo '        <button type="button" id="company-view-btn" class="lf-view-button' . $companyBtnClass . '">Company View</button>';
        echo '        <button type="button" id="rep-view-btn" class="lf-view-button' . $repBtnClass . '">Rep View</button>';
        echo '      </div>';

        // Rep selector (hidden in company view)
        echo '      <select id="rep-selector" class="lf-rep-dropdown"' . $repSelectorDisplay . '>';
        echo '        <option value="">Select Sales Rep...</option>';

        // Get active reps
        $db = DBManagerFactory::getInstance();
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

    /**
     * Get all active users for admin user selector
     */
    private function getActiveUsers()
    {
        $db = DBManagerFactory::getInstance();
        $users = [];
        $sql = "SELECT id, first_name, last_name FROM users WHERE deleted = 0 AND status = 'Active' ORDER BY last_name, first_name";
        $result = $db->query($sql);
        while ($row = $db->fetchByAssoc($result)) {
            $users[] = $row;
        }
        return $users;
    }

    /**
     * Gathers all dashboard data server-side
     */
    private function gatherDashboardData($weekStart, $repId = null)
    {
        $staleDays = (int)LF_PRConfig::getConfig('risk', 'stale_deal_days') ?: 14;
        $currentYear = (int)date('Y', strtotime($weekStart));
        $configWeekStartDay = WeekHelper::getConfiguredWeekStartDay();
        $isCurrentWeek = WeekHelper::isCurrentWeek($weekStart, $configWeekStartDay);

        // Check if snapshot exists for this week
        $weekEndAt = OpportunityQuery::getSnapshotWeekEndAt($weekStart);
        $hasSnapshot = OpportunityQuery::hasSnapshot($weekEndAt);

        // Get pipeline data — from snapshot if available, live for current week, empty otherwise
        if ($hasSnapshot) {
            $pipelineByStageRaw = OpportunityQuery::getSnapshotPipelineByStage($weekEndAt, $repId);
        } elseif ($isCurrentWeek) {
            $pipelineByStageRaw = OpportunityQuery::getPipelineByStage($repId);
        } else {
            // Past/future week with no snapshot — empty
            $pipelineByStageRaw = [];
        }
        $pipelineByStage = [];
        foreach ($pipelineByStageRaw as $row) {
            $stageName = $row['sales_stage'] ?? 'Unknown';
            $pipelineByStage[$stageName] = [
                'amount' => (float)($row['total_amount'] ?? 0),
                'profit' => (float)($row['total_profit'] ?? 0),
                'count' => (int)($row['deal_count'] ?? 0)
            ];
        }

        // Transform pipelineByRep to include byStage data per rep
        // Only show rep pipeline data for current week (live) or weeks with snapshots
        $pipelineByRepRaw = ($isCurrentWeek || $hasSnapshot) ? OpportunityQuery::getPipelineByRep() : [];
        $pipelineByRep = [];
        foreach ($pipelineByRepRaw as $row) {
            $repUserId = $row['assigned_user_id'];
            if (!isset($pipelineByRep[$repUserId])) {
                $pipelineByRep[$repUserId] = [
                    'name' => trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
                    'total' => 0,
                    'totalProfit' => 0,
                    'byStage' => []
                ];
            }
            $pipelineByRep[$repUserId]['total'] += (float)($row['total_amount'] ?? 0);
            $pipelineByRep[$repUserId]['totalProfit'] += (float)($row['total_profit'] ?? 0);
        }

        // Get byStage data for each rep — from snapshot if available
        foreach (array_keys($pipelineByRep) as $userId) {
            if ($hasSnapshot) {
                $repPipelineRaw = OpportunityQuery::getSnapshotPipelineByStage($weekEndAt, $userId);
            } else {
                $repPipelineRaw = OpportunityQuery::getPipelineByStage($userId);
            }
            foreach ($repPipelineRaw as $row) {
                $stageName = $row['sales_stage'] ?? 'Unknown';
                $pipelineByRep[$userId]['byStage'][$stageName] = [
                    'amount' => (float)($row['total_amount'] ?? 0),
                    'profit' => (float)($row['total_profit'] ?? 0),
                    'count' => (int)($row['deal_count'] ?? 0)
                ];
            }
        }

        // Transform stale deals to include opportunity_name for display
        // Stale/at-risk deals are current-state indicators — only show for current week
        $staleDealsRaw = $isCurrentWeek ? OpportunityQuery::getStaleDeals($staleDays, $repId) : [];
        $staleDeals = [];
        foreach ($staleDealsRaw as $deal) {
            $staleDeals[] = [
                'id' => $deal['id'],
                'name' => $deal['name'],
                'opportunity_name' => $deal['name'],
                'account_name' => $deal['account_name'] ?? 'No Account',
                'sales_stage' => $deal['sales_stage'],
                'amount' => (float)($deal['amount'] ?? 0),
                'profit' => (float)($deal['opportunity_profit'] ?? 0),
                'date_closed' => $deal['date_closed'],
                'assigned_user_id' => $deal['assigned_user_id'],
                'days_since_activity' => (int)($deal['days_since_activity'] ?? 0)
            ];
        }

        return [
            'config' => [
                'stale_deal_days' => $staleDays,
                'deal_risk' => [
                    'activity_types' => LF_PRConfig::getConfigJson('risk', 'activity_types')
                ],
                'default_annual_quota' => (float)LF_PRConfig::getConfig('quotas', 'default_annual_quota'),
                'pipeline_coverage_multiplier' => (float)LF_PRConfig::getConfig('quotas', 'pipeline_coverage_multiplier'),
                'stageProbabilities' => json_decode(LF_PRConfig::getConfig('stages', 'stage_probabilities') ?: '[]', true),
                'brand_blue' => '#125EAD',
                'brand_green' => '#4BB74E',
                'default_weekly_new_pipeline' => (float)LF_PRConfig::getConfig('targets', 'default_new_pipeline_target'),
                'default_weekly_progression' => (float)LF_PRConfig::getConfig('targets', 'default_progression_target'),
                'default_weekly_closed' => (float)LF_PRConfig::getConfig('targets', 'default_closed_target')
            ],
            'reps' => LF_RepTargets::getActiveReps(),
            'repTargets' => LF_RepTargets::getTargetsForYear($currentYear),
            'weekInfo' => [
                'currentWeek' => $weekStart,
                'weekEnd' => WeekHelper::getWeekEnd($weekStart),
            ],
            'weekList' => WeekHelper::getWeekList(12, $configWeekStartDay),
            'pipelineByStage' => $pipelineByStage,
            'pipelineByRep' => $pipelineByRep,
            'staleDeals' => $staleDeals,
            'atRiskDeals' => $isCurrentWeek ? OpportunityQuery::getAtRiskDeals($repId) : [],
            'planItems' => ($isCurrentWeek || $hasSnapshot) ? $this->getPlanItemsForWeek($weekStart, $repId) : [],
            'closedYtd' => OpportunityQuery::getClosedYTD($currentYear, $repId),
        ];
    }

    /**
     * Helper to get plan items for the selected week
     */
    private function getPlanItemsForWeek($weekStart, $repId)
    {
        $db = DBManagerFactory::getInstance();
        $items = [];

        // Find plans for this week
        $sql = "SELECT id, assigned_user_id FROM lf_weekly_plan
                WHERE week_start_date = " . $db->quoted($weekStart) . " AND deleted = 0";
        if ($repId) {
            $sql .= " AND assigned_user_id = " . $db->quoted($repId);
        }

        $res = $db->query($sql);
        $planIds = [];
        while ($row = $db->fetchByAssoc($res)) {
            $planIds[] = $row['id'];
        }

        if (empty($planIds)) return $items;

        $planIdList = "'" . implode("','", array_map(fn($id) => $db->quote($id), $planIds)) . "'";

        // Check for snapshot data
        $weekEndAt = OpportunityQuery::getSnapshotWeekEndAt($weekStart);
        $hasSnapshot = OpportunityQuery::hasSnapshot($weekEndAt);

        // Get Op Items — use snapshot for amount/profit/stage when available
        if ($hasSnapshot) {
            $sql = "SELECT poi.*,
                           COALESCE(s.revenue, o.amount) AS amount,
                           COALESCE(s.profit, o.opportunity_profit) AS profit,
                           o.name AS opportunity_name,
                           a.name AS account_name,
                           wp.assigned_user_id,
                           COALESCE(s.stage_name, o.sales_stage) AS current_stage
                    FROM lf_plan_op_items poi
                    LEFT JOIN opportunities o ON poi.opportunity_id = o.id AND o.deleted = 0
                    LEFT JOIN accounts_opportunities ao ON ao.opportunity_id = o.id AND ao.deleted = 0
                    LEFT JOIN accounts a ON a.id = ao.account_id AND a.deleted = 0
                    LEFT JOIN opportunity_weekly_snapshot s ON s.opportunity_id = poi.opportunity_id
                        AND s.week_end_at = " . $db->quoted($weekEndAt) . " AND s.deleted = 0
                    LEFT JOIN lf_weekly_plan wp ON poi.lf_weekly_plan_id = wp.id
                    WHERE poi.lf_weekly_plan_id IN ($planIdList) AND poi.deleted = 0
                      AND poi.projected_stage IS NOT NULL AND poi.projected_stage != ''";
        } else {
            $sql = "SELECT poi.*, o.amount, o.opportunity_profit AS profit, o.name AS opportunity_name,
                           a.name AS account_name,
                           wp.assigned_user_id,
                           o.sales_stage AS current_stage
                    FROM lf_plan_op_items poi
                    LEFT JOIN opportunities o ON poi.opportunity_id = o.id AND o.deleted = 0
                    LEFT JOIN accounts_opportunities ao ON ao.opportunity_id = o.id AND ao.deleted = 0
                    LEFT JOIN accounts a ON a.id = ao.account_id AND a.deleted = 0
                    LEFT JOIN lf_weekly_plan wp ON poi.lf_weekly_plan_id = wp.id
                    WHERE poi.lf_weekly_plan_id IN ($planIdList) AND poi.deleted = 0
                      AND poi.projected_stage IS NOT NULL AND poi.projected_stage != ''";
        }
        $res = $db->query($sql);
        while ($row = $db->fetchByAssoc($res)) {
            $row['item_category'] = $row['item_type'] ?? 'opportunity';
            $items[] = $row;
        }

        // Get Prospect Items
        $sql = "SELECT pi.*, wp.assigned_user_id
                FROM lf_plan_prospect_items pi
                LEFT JOIN lf_weekly_plan wp ON pi.lf_weekly_plan_id = wp.id
                WHERE pi.lf_weekly_plan_id IN ($planIdList) AND pi.deleted = 0";
        $res = $db->query($sql);
        while ($row = $db->fetchByAssoc($res)) {
            $row['item_category'] = 'prospecting';
            $row['amount'] = (float)($row['expected_revenue'] ?? $row['expected_value'] ?? 0);
            $row['profit'] = (float)($row['expected_profit'] ?? 0);
            $items[] = $row;
        }

        return $items;
    }

    private function renderHeader()
    {
        echo '<div class="lf-header" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 25px; background: #fff; border-bottom: 2px solid #125EAD;">';
        echo '  <h1 style="color: #125EAD; margin: 0; font-size: 24px;">Weekly Planning Dashboard</h1>';
        echo '  <div class="lf-brand-logo" style="color: #4BB74E; font-weight: bold;">Logical Front</div>';
        echo '</div>';
    }

    private function renderToolbar($viewMode, $selectedRepId, $weekStart, $reps, $weekList)
    {
        echo '<div class="lf-toolbar" style="padding: 10px 25px; background: #fff; border-bottom: 1px solid #ddd; display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">';
        
        // View Toggle
        echo '  <div class="lf-view-toggle" style="display: flex; border: 1px solid #125EAD; border-radius: 4px; overflow: hidden;">';
        echo '    <button id="team-view-btn" class="lf-btn' . ($viewMode === 'team' ? ' lf-active' : '') . '" style="padding: 8px 20px; cursor: pointer; border: none; font-weight: 600; background: ' . ($viewMode === 'team' ? '#125EAD' : '#fff') . '; color: ' . ($viewMode === 'team' ? '#fff' : '#125EAD') . ';">Team View</button>';
        echo '    <button id="rep-view-btn" class="lf-btn' . ($viewMode === 'rep' ? ' lf-active' : '') . '" style="padding: 8px 20px; cursor: pointer; border: none; font-weight: 600; background: ' . ($viewMode === 'rep' ? '#125EAD' : '#fff') . '; color: ' . ($viewMode === 'rep' ? '#fff' : '#125EAD') . ';">Rep View</button>';
        echo '  </div>';

        // Rep Selector
        if ($viewMode === 'team') {
            echo '  <div id="rep-selector-container" class="lf-rep-selector-container hidden" style="display: none;">';
            echo '    <select id="rep-selector" class="lf-select hidden" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; min-width: 200px;">';
        } else {
            echo '  <div id="rep-selector-container" class="lf-rep-selector-container">';
            echo '    <select id="rep-selector" class="lf-select" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; min-width: 200px;">';
        }
        
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

    private function renderDashboardContainer($viewMode = 'team', $selectedRepId = null)
    {
        // Dashboard grid - Column order: Health | Risk | Priorities (matches mockup)
        echo '<div id="lf-dashboard-container" class="lf-dashboard-grid">';
        echo '  <div id="pipeline-health-column"></div>';
        echo '  <div id="deal-risk-column"></div>';
        echo '  <div id="weekly-priorities-column"></div>';
        echo '  <div id="dashboard-loading" style="display: none; grid-column: span 3; text-align: center; padding: 50px; color: white;">';
        echo '    <div style="font-size: 18px;">Gathering dashboard insights...</div>';
        echo '  </div>';
        echo '</div>';
    }
}