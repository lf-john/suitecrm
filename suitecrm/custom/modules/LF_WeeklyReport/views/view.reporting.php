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
                "SELECT id, opportunity_id, item_type, projected_stage
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

        // Load snapshots for all planned opportunities
        $snapshots = [];
        if (!empty($planItems)) {
            $opportunityIds = array_keys($planItems);
            foreach ($opportunityIds as $oppId) {
                $query = sprintf(
                    "SELECT id, opportunity_id, opportunity_name, stage_at_week_start
                     FROM lf_report_snapshots
                     WHERE lf_weekly_report_id = %s
                       AND opportunity_id = %s
                       AND deleted = 0",
                    $db->quoted($report->id),
                    $db->quoted($oppId)
                );
                $result = $db->query($query);
                while ($row = $db->fetchByAssoc($result)) {
                    $snapshots[$row['opportunity_id']] = $row;
                }
            }
        }

        // Load current opportunity data for movement detection
        $opportunities = [];
        if (!empty($planItems)) {
            $opportunityIds = array_keys($planItems);
            foreach ($opportunityIds as $oppId) {
                $query = sprintf(
                    "SELECT id, name, sales_stage
                     FROM opportunities
                     WHERE id = %s
                       AND deleted = 0",
                    $db->quoted($oppId)
                );
                $result = $db->query($query);
                while ($row = $db->fetchByAssoc($result)) {
                    $opportunities[$row['id']] = $row;
                }
            }
        }

        // Render the report
        parent::display();

        echo '<div class="lf-reporting-wrapper" style="padding: 20px;">';
        echo '<h1>Weekly Opportunity Report</h1>';
        echo '<p>Week of ' . htmlspecialchars($weekStart) . '</p>';

        if (empty($planItems)) {
            echo '<p>No planned opportunities for this week.</p>';
        } else {
            echo '<table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Opportunity</th>';
            echo '<th>Stage at Week Start</th>';
            echo '<th>Current Stage</th>';
            echo '<th>Movement</th>';
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

                // Detect movement using probability comparison
                $movement = 'static';
                if ($sales_stage === 'Closed Won') {
                    $movement = 'closed_won';
                } elseif ($sales_stage === 'Closed Lost') {
                    $movement = 'closed_lost';
                } elseif (!empty($stage_at_week_start)) {
                    $startProbability = (int) ($probabilities[$stage_at_week_start] ?? 0);
                    $currentProbability = (int) ($probabilities[$sales_stage] ?? 0);
                    if ($currentProbability > $startProbability) {
                        $movement = 'progressed';
                    } elseif ($currentProbability < $startProbability) {
                        $movement = 'regressed';
                    } else {
                        $movement = 'static';
                    }
                }

                echo '<tr>';
                // Opportunity name as link to detail view
                echo '<td><a href="index.php?module=Opportunities&action=DetailView&record=' . htmlspecialchars($oppId) . '">' . htmlspecialchars($opportunity['name']) . '</a></td>';
                echo '<td>' . htmlspecialchars($stage_at_week_start) . '</td>';
                echo '<td>' . htmlspecialchars($sales_stage) . '</td>';
                echo '<td>' . htmlspecialchars($movement) . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        }

        echo '</div>';
    }
}
