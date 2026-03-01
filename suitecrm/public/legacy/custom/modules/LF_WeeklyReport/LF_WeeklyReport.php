<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * LF_WeeklyReport Bean Class
 *
 * Represents a weekly reporting record for a sales rep. Each report is
 * linked to a specific week (by week_start_date) and optionally linked
 * to the corresponding LF_WeeklyPlan via lf_weekly_plan_id.
 *
 * Reports contain snapshots (LF_ReportSnapshot) that capture each
 * opportunity's stage movement during the reporting week.
 *
 * Workflow:
 *   1. getOrCreateForWeek() finds or creates the report for a user/week
 *   2. LF_ReportSnapshot::createSnapshotsForWeek() populates initial snapshots
 *   3. LF_ReportSnapshot::updateSnapshotsForWeekEnd() refreshes end-of-week data
 *   4. Report is reviewed and status transitions to 'submitted' / 'reviewed'
 */
#[\AllowDynamicProperties]
class LF_WeeklyReport extends SugarBean
{
    public $table_name = 'lf_weekly_report';
    public $object_name = 'LF_WeeklyReport';
    public $module_name = 'LF_WeeklyReport';
    public $module_dir = 'LF_WeeklyReport';

    /**
     * Get an existing weekly report for the given user and week, or create a new one.
     *
     * When creating a new report, sets the name field and links to the
     * corresponding weekly plan if one exists.
     *
     * @param string $userId The assigned_user_id to query
     * @param string $weekStartDate The week_start_date to query (Y-m-d)
     * @return LF_WeeklyReport The existing or newly created bean
     */
    public static function getOrCreateForWeek($userId, $weekStartDate)
    {
        $db = DBManagerFactory::getInstance();

        $query = sprintf(
            "SELECT id FROM lf_weekly_report
             WHERE assigned_user_id = %s
               AND week_start_date = %s
               AND deleted = 0",
            $db->quoted($userId),
            $db->quoted($weekStartDate)
        );

        $existingId = $db->getOne($query);

        if ($existingId !== false) {
            return BeanFactory::getBean('LF_WeeklyReport', $existingId);
        }

        // Create new report — wrap in try/catch to handle race condition
        // (unique index on assigned_user_id + week_start_date)
        try {
            $bean = BeanFactory::newBean('LF_WeeklyReport');
            $bean->name = 'Report - ' . $weekStartDate;
            $bean->assigned_user_id = $userId;
            $bean->week_start_date = $weekStartDate;
            $bean->status = 'in_progress';

            // Link to the weekly plan if one exists for this user/week
            $planId = $db->getOne(sprintf(
                "SELECT id FROM lf_weekly_plan
                 WHERE assigned_user_id = %s
                   AND week_start_date = %s
                   AND deleted = 0",
                $db->quoted($userId),
                $db->quoted($weekStartDate)
            ));
            if ($planId !== false) {
                $bean->lf_weekly_plan_id = $planId;
            }

            $bean->save();

            return $bean;
        } catch (Exception $e) {
            // Duplicate key — another request created it first; fetch and return
            $retryResult = $db->getOne($query);
            if ($retryResult !== false) {
                return BeanFactory::getBean('LF_WeeklyReport', $retryResult);
            }
            throw $e;
        }
    }

    /**
     * Get an existing weekly report for the given user and week (read-only, no creation).
     *
     * @param string $userId The assigned_user_id to query
     * @param string $weekStartDate The week_start_date to query (Y-m-d)
     * @return LF_WeeklyReport|null The existing report bean, or null if not found
     */
    public static function getForWeek($userId, $weekStartDate)
    {
        $db = DBManagerFactory::getInstance();

        $query = sprintf(
            "SELECT id FROM lf_weekly_report
             WHERE assigned_user_id = %s
               AND week_start_date = %s
               AND deleted = 0",
            $db->quoted($userId),
            $db->quoted($weekStartDate)
        );

        $existingId = $db->getOne($query);

        if ($existingId !== false) {
            return BeanFactory::getBean('LF_WeeklyReport', $existingId);
        }

        return null;
    }
}
