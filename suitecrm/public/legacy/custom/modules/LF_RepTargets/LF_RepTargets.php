<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * LF_RepTargets Bean Class
 *
 * Module for storing sales rep target configurations including
 * annual quotas and weekly activity targets per fiscal year.
 */
#[\AllowDynamicProperties]
class LF_RepTargets extends SugarBean
{
    public $table_name = 'lf_rep_targets';
    public $object_name = 'LF_RepTargets';
    public $module_name = 'LF_RepTargets';
    public $module_dir = 'LF_RepTargets';

    /**
     * Get all active reps with their user details
     *
     * Retrieves active rep target records (is_active=1, deleted=0)
     * joined with the users table to include first_name and last_name.
     *
     * @return array Array of active rep target rows with user details
     */
    public static function getActiveReps()
    {
        $db = DBManagerFactory::getInstance();

        $query = sprintf(
            "SELECT rt.*, u.first_name, u.last_name
             FROM lf_rep_targets rt
             INNER JOIN users u ON rt.assigned_user_id = u.id
             WHERE rt.is_active = 1 AND rt.deleted = 0"
        );

        $result = $db->query($query);

        $reps = [];
        while ($row = $db->fetchByAssoc($result)) {
            $reps[] = $row;
        }

        return $reps;
    }

    /**
     * Get all active target records for a specified fiscal year
     *
     * Retrieves all active target records for the specified fiscal_year
     * where deleted=0 and is_active=1.
     *
     * @param int $year The fiscal year to query
     * @return array Array of target rows for the specified year
     */
    public static function getTargetsForYear($year)
    {
        $db = DBManagerFactory::getInstance();

        $query = sprintf(
            "SELECT * FROM lf_rep_targets WHERE fiscal_year = %s AND is_active = 1 AND deleted = 0",
            $db->quoted($year)
        );

        $result = $db->query($query);

        $targets = [];
        while ($row = $db->fetchByAssoc($result)) {
            $targets[] = $row;
        }

        return $targets;
    }
}
