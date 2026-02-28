<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'custom/modules/LF_PRConfig/LF_PRConfig.php';

/**
 * LF_ReportSnapshot Bean Class
 *
 * Stores weekly report snapshots for each opportunity, tracking
 * stage movement and planned status.
 *
 * Snapshot lifecycle:
 *   1. At week start (or when snapshot job runs), createSnapshotsForWeek() creates
 *      one snapshot per open opportunity. stage_at_week_start and stage_at_week_end
 *      are both set to the current stage. probability_at_start is set from config.
 *   2. At week end (before the reporting dashboard is viewed), updateSnapshotsForWeekEnd()
 *      refreshes stage_at_week_end and probability_at_end from the live opportunity data,
 *      then calls detectMovement() to set the movement field.
 *   3. Snapshots are then read-only for the reporting dashboard.
 */
#[\AllowDynamicProperties]
class LF_ReportSnapshot extends SugarBean
{
    public $table_name = 'lf_report_snapshots';
    public $object_name = 'LF_ReportSnapshot';
    public $module_name = 'LF_ReportSnapshot';
    public $module_dir = 'LF_ReportSnapshot';

    /**
     * Detect movement direction by comparing stage probabilities.
     *
     * Reads the stage_probabilities JSON blob from lf_pr_config and compares
     * the probability values of stage_at_week_start and stage_at_week_end.
     * Also sets $this->movement to the detected value.
     *
     * @return string Movement type: 'new', 'closed_won', 'closed_lost', 'forward', 'backward', or 'static'
     */
    public function detectMovement()
    {
        $startStage = $this->stage_at_week_start;
        $endStage = $this->stage_at_week_end;

        // Handle new opportunities (no start stage)
        if (empty($startStage)) {
            $this->movement = 'new';
            return 'new';
        }

        // Handle closed_won
        if ($endStage === 'Closed Won' || $endStage === 'closed_won') {
            $this->movement = 'closed_won';
            return 'closed_won';
        }

        // Handle closed_lost
        if ($endStage === 'Closed Lost' || $endStage === 'closed_lost') {
            $this->movement = 'closed_lost';
            return 'closed_lost';
        }

        // Look up probabilities from the stage_probabilities JSON config
        $probabilities = LF_PRConfig::getConfigJson('stages', 'stage_probabilities');
        if (!is_array($probabilities)) {
            $probabilities = [];
        }

        $startProbability = (int) ($probabilities[$startStage] ?? 0);
        $endProbability = (int) ($probabilities[$endStage] ?? 0);

        if ($endProbability > $startProbability) {
            $this->movement = 'forward';
            return 'forward';
        }

        if ($endProbability < $startProbability) {
            $this->movement = 'backward';
            return 'backward';
        }

        $this->movement = 'static';
        return 'static';
    }

    /**
     * Build a lookup map of planned opportunity IDs to their item_type.
     *
     * @param string $userId The assigned user ID
     * @param string $weekStartDate The week start date (Y-m-d)
     * @return array Map of opportunity_id => item_type for planned items
     */
    private static function getPlannedOpportunityMap($userId, $weekStartDate)
    {
        $db = DBManagerFactory::getInstance();

        $query = sprintf(
            "SELECT poi.opportunity_id, poi.item_type
             FROM lf_plan_op_items poi
             INNER JOIN lf_weekly_plan wp ON poi.lf_weekly_plan_id = wp.id
             WHERE wp.assigned_user_id = %s
               AND wp.week_start_date = %s
               AND poi.deleted = 0
               AND wp.deleted = 0",
            $db->quoted($userId),
            $db->quoted($weekStartDate)
        );

        $result = $db->query($query);
        $map = [];
        while ($row = $db->fetchByAssoc($result)) {
            $map[$row['opportunity_id']] = $row['item_type'];
        }

        return $map;
    }

    /**
     * Create initial snapshots for all open opportunities assigned to a user.
     *
     * Called at the start of a reporting week (or by the scheduled snapshot job).
     * Sets stage_at_week_start = stage_at_week_end = current stage.
     * Sets probability_at_start from the stage_probabilities config.
     * Movement and probability_at_end are populated later by updateSnapshotsForWeekEnd().
     *
     * @param string $userId The assigned user ID
     * @param string $weekStartDate The start date of the reporting week (Y-m-d)
     * @param string $reportId The lf_weekly_report ID to link snapshots to
     * @return array Array of created snapshot beans
     */
    public static function createSnapshotsForWeek($userId, $weekStartDate, $reportId)
    {
        $db = DBManagerFactory::getInstance();

        // Load probability map for setting probability_at_start
        $probabilities = LF_PRConfig::getConfigJson('stages', 'stage_probabilities');
        if (!is_array($probabilities)) {
            $probabilities = [];
        }

        // Get planned opportunities for was_planned tagging
        $plannedMap = self::getPlannedOpportunityMap($userId, $weekStartDate);

        // Query open opportunities for the given user
        $query = sprintf(
            "SELECT o.id, o.name, COALESCE(a.name, '') as account_name, o.amount, o.sales_stage
             FROM opportunities o
             LEFT JOIN accounts_opportunities ao ON ao.opportunity_id = o.id AND ao.deleted = 0
             LEFT JOIN accounts a ON a.id = ao.account_id AND a.deleted = 0
             WHERE o.assigned_user_id = %s
               AND o.sales_stage NOT IN ('Closed Won', 'Closed Lost', 'closed_won', 'closed_lost')
               AND o.deleted = 0",
            $db->quoted($userId)
        );

        $result = $db->query($query);
        $snapshots = [];

        while ($row = $db->fetchByAssoc($result)) {
            // Check for existing snapshot to ensure idempotency
            $checkQuery = sprintf(
                "SELECT id FROM lf_report_snapshots
                 WHERE lf_weekly_report_id = %s
                   AND opportunity_id = %s
                   AND deleted = 0",
                $db->quoted($reportId),
                $db->quoted($row['id'])
            );
            $existingId = $db->getOne($checkQuery);

            if ($existingId !== false) {
                $snapshots[] = BeanFactory::getBean('LF_ReportSnapshot', $existingId);
                continue;
            }

            $snapshot = BeanFactory::newBean('LF_ReportSnapshot');
            $snapshot->name = $row['name'] . ' - ' . $weekStartDate;
            $snapshot->lf_weekly_report_id = $reportId;
            $snapshot->opportunity_id = $row['id'];
            $snapshot->opportunity_name = $row['name'];
            $snapshot->account_name = $row['account_name'];
            $snapshot->amount_at_snapshot = $row['amount'];
            $snapshot->stage_at_week_start = $row['sales_stage'];
            $snapshot->stage_at_week_end = $row['sales_stage'];
            $snapshot->probability_at_start = (int) ($probabilities[$row['sales_stage']] ?? 0);
            $snapshot->was_planned = isset($plannedMap[$row['id']]) ? 1 : 0;
            $snapshot->plan_category = $plannedMap[$row['id']] ?? '';

            $snapshot->save();
            $snapshots[] = $snapshot;
        }

        return $snapshots;
    }

    /**
     * Update all snapshots for a report with current end-of-week opportunity data.
     *
     * Refreshes stage_at_week_end and probability_at_end from live opportunity records,
     * then calls detectMovement() to compute and store the movement field.
     * Call this before displaying the reporting dashboard for the week.
     *
     * @param string $reportId The lf_weekly_report ID whose snapshots to update
     * @return int Number of snapshots updated
     */
    public static function updateSnapshotsForWeekEnd($reportId)
    {
        $db = DBManagerFactory::getInstance();

        $probabilities = LF_PRConfig::getConfigJson('stages', 'stage_probabilities');
        if (!is_array($probabilities)) {
            $probabilities = [];
        }

        $query = sprintf(
            "SELECT s.id AS snapshot_id, o.sales_stage AS current_stage
             FROM lf_report_snapshots s
             INNER JOIN opportunities o ON s.opportunity_id = o.id AND o.deleted = 0
             WHERE s.lf_weekly_report_id = %s
               AND s.deleted = 0",
            $db->quoted($reportId)
        );

        $result = $db->query($query);
        $count = 0;

        while ($row = $db->fetchByAssoc($result)) {
            $snapshot = BeanFactory::getBean('LF_ReportSnapshot', $row['snapshot_id']);
            if (empty($snapshot->id)) {
                continue;
            }

            $snapshot->stage_at_week_end = $row['current_stage'];
            $snapshot->probability_at_end = (int) ($probabilities[$row['current_stage']] ?? 0);
            $snapshot->detectMovement();
            $snapshot->save();
            $count++;
        }

        return $count;
    }
}
