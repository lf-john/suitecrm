<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * LF_WeeklyPlan Bean Class
 *
 * Manages weekly planning records for users, tracking plan status,
 * submission, and review workflow.
 */
#[\AllowDynamicProperties]
class LF_WeeklyPlan extends SugarBean
{
    public $table_name = 'lf_weekly_plan';
    public $object_name = 'LF_WeeklyPlan';
    public $module_name = 'LF_WeeklyPlan';
    public $module_dir = 'LF_WeeklyPlan';

    // Enable ACL support for this module - allows non-admin users to access
    public $acl_display_only = false;

    /**
     * Declare which features this bean implements.
     * ACL support is required for non-admin user access.
     *
     * @param string $interface The interface name to check
     * @return bool Whether this bean implements the interface
     */
    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
            default:
                return false;
        }
    }

    /**
     * Get or create a weekly plan for the given user and week start date.
     *
     * When creating a new plan, sets the name field to include the week date.
     *
     * @param string $userId The assigned user ID
     * @param string $weekStartDate The week start date in Y-m-d format
     * @return LF_WeeklyPlan The existing or newly created weekly plan bean
     */
    public static function getOrCreateForWeek($userId, $weekStartDate)
    {
        $db = DBManagerFactory::getInstance();

        $query = sprintf(
            "SELECT id FROM lf_weekly_plan
             WHERE assigned_user_id = %s
               AND week_start_date = %s
               AND deleted = 0",
            $db->quoted($userId),
            $db->quoted($weekStartDate)
        );

        $result = $db->getOne($query);

        if ($result !== false) {
            return BeanFactory::getBean('LF_WeeklyPlan', $result);
        }

        // Create new plan — wrap in try/catch to handle race condition
        // (unique index on assigned_user_id + week_start_date)
        try {
            $bean = BeanFactory::newBean('LF_WeeklyPlan');
            $bean->name = 'Plan - ' . $weekStartDate;
            $bean->assigned_user_id = $userId;
            $bean->week_start_date = $weekStartDate;
            $bean->status = 'in_progress';
            $bean->save();

            return $bean;
        } catch (Exception $e) {
            // Duplicate key — another request created it first; fetch and return
            $retryResult = $db->getOne($query);
            if ($retryResult !== false) {
                return BeanFactory::getBean('LF_WeeklyPlan', $retryResult);
            }
            throw $e;
        }
    }

    /**
     * Get an existing weekly plan for the given user and week (read-only, no creation).
     *
     * @param string $userId The assigned user ID
     * @param string $weekStartDate The week start date in Y-m-d format
     * @return LF_WeeklyPlan|null The existing plan bean, or null if not found
     */
    public static function getForWeek($userId, $weekStartDate)
    {
        $db = DBManagerFactory::getInstance();

        $query = sprintf(
            "SELECT id FROM lf_weekly_plan
             WHERE assigned_user_id = %s
               AND week_start_date = %s
               AND deleted = 0",
            $db->quoted($userId),
            $db->quoted($weekStartDate)
        );

        $result = $db->getOne($query);

        if ($result !== false) {
            return BeanFactory::getBean('LF_WeeklyPlan', $result);
        }

        return null;
    }

    /**
     * Get the week end date (6 days after week start date).
     *
     * @return string|null The week end date in Y-m-d format, or null if week_start_date is not set
     */
    public function getWeekEndDate(): ?string
    {
        if (empty($this->week_start_date)) {
            return null;
        }

        try {
            $date = new DateTime($this->week_start_date);
            $date->modify('+6 days');
            return $date->format('Y-m-d');
        } catch (Exception $e) {
            return null;
        }
    }
}
