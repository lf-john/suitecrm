<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * OpportunityQuery - Utility class for querying opportunities in SuiteCRM.
 *
 * Provides static methods for querying opportunity data needed by
 * dashboards and planning/reporting tools.
 *
 * All methods use SuiteCRM's DBManagerFactory for database access
 * and $db->quoted() / $db->quote() for parameter escaping.
 *
 * @package LF_PlanningReporting
 */
class OpportunityQuery
{
    /**
     * Get pipeline data grouped by sales stage.
     *
     * Excludes '2-Analysis (1%)', 'closed_won', and 'closed_lost' stages.
     *
     * @param string|null $repId Optional assigned_user_id to filter by
     * @return array[] Each row: ['sales_stage' => string, 'total_amount' => string, 'deal_count' => string]
     */
    public static function getPipelineByStage(?string $repId = null): array
    {
        global $db;

        $sql = "SELECT sales_stage, SUM(amount) AS total_amount, COUNT(*) AS deal_count
                FROM opportunities
                WHERE deleted = 0
                  AND sales_stage NOT IN ('2-Analysis (1%)', 'Closed Won', 'Closed Lost', 'closed_won', 'closed_lost')";

        if ($repId !== null) {
            $sql .= " AND assigned_user_id = " . $db->quoted($repId);
        }

        $sql .= " GROUP BY sales_stage";

        $result = $db->query($sql);
        $rows = [];
        while ($row = $db->fetchByAssoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Get pipeline data grouped by sales rep.
     *
     * JOINs with users table to retrieve rep first_name and last_name.
     * Excludes '2-Analysis (1%)', 'closed_won', and 'closed_lost' stages.
     *
     * @return array[] Each row: ['assigned_user_id' => string, 'first_name' => string, 'last_name' => string, 'total_amount' => string, 'deal_count' => string]
     */
    public static function getPipelineByRep()
    {
        global $db;
        $sql = "SELECT o.assigned_user_id, u.first_name, u.last_name,
                       SUM(o.amount) AS total_amount, COUNT(*) AS deal_count
                FROM opportunities o
                JOIN users u ON o.assigned_user_id = u.id
                WHERE o.deleted = 0
                  AND o.sales_stage NOT IN ('2-Analysis (1%)', 'Closed Won', 'Closed Lost', 'closed_won', 'closed_lost')
                GROUP BY o.assigned_user_id, u.first_name, u.last_name";

        $result = $db->query($sql);
        $rows = [];
        while ($row = $db->fetchByAssoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Get closed won total year-to-date for a given year.
     *
     * Filters for 'closed_won' stage and date_closed within the given year.
     * Uses >= and < for proper date boundary inclusiveness.
     *
     * @param int $year The year to filter by
     * @param string|null $repId Optional assigned_user_id to filter by
     * @return array Single row: ['total_amount' => string|null, 'deal_count' => string]
     */
    public static function getClosedYTD($year, $repId = null)
    {
        global $db;
        $safeYear = (int) $year;

        $sql = sprintf(
            "SELECT SUM(amount) AS total_amount, COUNT(*) AS deal_count
             FROM opportunities
             WHERE deleted = 0
               AND sales_stage IN ('Closed Won', 'closed_won')
               AND date_closed >= '%d-01-01'
               AND date_closed < '%d-01-01'",
            $safeYear,
            $safeYear + 1
        );

        if ($repId !== null) {
            $sql .= " AND assigned_user_id = " . $db->quoted($repId);
        }

        $result = $db->query($sql);
        $rows = [];
        while ($row = $db->fetchByAssoc($result)) {
            $rows[] = $row;
        }
        return !empty($rows) ? $rows[0] : ['total_amount' => null, 'deal_count' => '0'];
    }

    /**
     * Get stale deals with no recent activity within $days days.
     *
     * Uses a derived table to compute the last activity date across calls,
     * meetings, tasks, and notes for each opportunity. Returns deals where
     * the last activity is older than $days days, ordered by most stale first.
     *
     * Excludes '2-Analysis (1%)', 'closed_won', and 'closed_lost' stages.
     *
     * @param int $days Number of days threshold for stale detection
     * @param string|null $repId Optional assigned_user_id to filter by
     * @return array[] Each row: ['id' => string, 'name' => string, 'account_name' => string, 'sales_stage' => string, 'amount' => string, 'date_closed' => string, 'assigned_user_id' => string, 'last_activity_date' => string, 'days_since_activity' => string]
     */
    public static function getStaleDeals($days, $repId = null)
    {
        global $db;
        $safeDays = (int) $days;

        $where = "WHERE o.deleted = 0
                    AND o.sales_stage NOT IN ('2-Analysis (1%)', 'Closed Won', 'Closed Lost', 'closed_won', 'closed_lost')";

        if ($repId !== null) {
            $where .= "\n                    AND o.assigned_user_id = " . $db->quoted($repId);
        }

        $sql = "SELECT o.id, o.name, COALESCE(a.name, 'No Account') AS account_name, o.sales_stage, o.amount,
                       o.date_closed, o.assigned_user_id,
                       GREATEST(
                           CASE WHEN MAX(c.date_start) IS NULL THEN '1970-01-01' ELSE MAX(c.date_start) END,
                           CASE WHEN MAX(m.date_start) IS NULL THEN '1970-01-01' ELSE MAX(m.date_start) END,
                           CASE WHEN MAX(t.date_due) IS NULL THEN '1970-01-01' ELSE MAX(t.date_due) END,
                           CASE WHEN MAX(n.date_entered) IS NULL THEN '1970-01-01' ELSE MAX(n.date_entered) END,
                           CASE WHEN MAX(e.date_sent_received) IS NULL THEN '1970-01-01' ELSE MAX(e.date_sent_received) END
                       ) AS last_activity_date
                FROM opportunities o
                LEFT JOIN accounts_opportunities ao ON o.id = ao.opportunity_id AND ao.deleted = 0
                LEFT JOIN accounts a ON ao.account_id = a.id AND a.deleted = 0
                LEFT JOIN calls c ON c.parent_id = o.id AND c.parent_type = 'Opportunities' AND c.deleted = 0
                LEFT JOIN meetings m ON m.parent_id = o.id AND m.parent_type = 'Opportunities' AND m.deleted = 0
                LEFT JOIN tasks t ON t.parent_id = o.id AND t.parent_type = 'Opportunities' AND t.deleted = 0
                LEFT JOIN notes n ON n.parent_id = o.id AND n.parent_type = 'Opportunities' AND n.deleted = 0
                LEFT JOIN emails_beans eb ON eb.bean_id = o.id AND eb.bean_module = 'Opportunities' AND eb.deleted = 0
                LEFT JOIN emails e ON eb.email_id = e.id AND e.deleted = 0
                {$where}
                GROUP BY o.id, o.name, a.name, o.sales_stage, o.amount, o.date_closed, o.assigned_user_id
                HAVING last_activity_date < DATE_SUB(NOW(), INTERVAL {$safeDays} DAY)
                ORDER BY last_activity_date ASC";

        $result = $db->query($sql);
        $rows = [];
        while ($row = $db->fetchByAssoc($result)) {
            $rows[] = $row;
        }

        // Add computed days_since_activity to each row
        foreach ($rows as &$row) {
            if ($row['last_activity_date'] === '1970-01-01') {
                // No activity ever recorded - treat as very stale (9999 days)
                $row['days_since_activity'] = '9999';
            } else {
                $activityDate = new DateTime($row['last_activity_date']);
                $now = new DateTime();
                $row['days_since_activity'] = (string) $now->diff($activityDate)->days;
            }
        }
        unset($row);

        return $rows;
    }

    /**
     * Get open opportunities for a specific rep.
     *
     * Returns all non-closed opportunities (excludes 'closed_won' and 'closed_lost')
     * assigned to the given user.
     *
     * @param string $repId The assigned_user_id to filter by
     * @return array[] Each row: ['id' => string, 'name' => string, 'sales_stage' => string, 'amount' => string, 'date_closed' => string, 'assigned_user_id' => string]
     */
    public static function getOpenOpportunities($repId)
    {
        global $db;

        $sql = "SELECT id, name, sales_stage, amount, date_closed, assigned_user_id
             FROM opportunities
             WHERE deleted = 0
               AND sales_stage NOT IN ('Closed Won', 'Closed Lost', 'closed_won', 'closed_lost')
               AND assigned_user_id = " . $db->quoted($repId);

        $result = $db->query($sql);
        $rows = [];
        while ($row = $db->fetchByAssoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Get opportunities in '2-Analysis (1%)' stage for a specific rep.
     *
     * @param string $repId The assigned_user_id to filter by
     * @return array[] Each row: ['id' => string, 'name' => string, 'sales_stage' => string, 'amount' => string, 'date_closed' => string, 'assigned_user_id' => string]
     */
    public static function getAnalysisOpportunities($repId)
    {
        global $db;

        $sql = "SELECT id, name, sales_stage, amount, date_closed, assigned_user_id
             FROM opportunities
             WHERE deleted = 0
               AND sales_stage = '2-Analysis (1%)'
               AND assigned_user_id = " . $db->quoted($repId);

        $result = $db->query($sql);
        $rows = [];
        while ($row = $db->fetchByAssoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Get forecast opportunities for a given quarter and year.
     *
     * Filters by date_closed within the quarter boundaries.
     * Quarter boundaries: Q1=Jan-Mar, Q2=Apr-Jun, Q3=Jul-Sep, Q4=Oct-Dec.
     * Excludes 'closed_lost' deals. Uses >= and < for proper date boundary inclusiveness.
     *
     * @param int $quarter The quarter number (1-4)
     * @param int $year The year
     * @param string|null $repId Optional assigned_user_id to filter by
     * @return array[] Each row: ['id' => string, 'name' => string, 'sales_stage' => string, 'amount' => string, 'date_closed' => string, 'assigned_user_id' => string]
     */
    public static function getForecastOpportunities($quarter, $year, $repId = null)
    {
        global $db;

        // Validate quarter bounds
        $safeQuarter = (int) $quarter;
        if ($safeQuarter < 1 || $safeQuarter > 4) {
            throw new InvalidArgumentException("Quarter must be 1-4, got: $safeQuarter");
        }

        $safeYear = (int) $year;
        $startMonth = ($safeQuarter - 1) * 3 + 1;
        $quarterStart = sprintf('%d-%02d-01', $safeYear, $startMonth);

        if ($safeQuarter < 4) {
            $endMonth = $startMonth + 3;
            $quarterEnd = sprintf('%d-%02d-01', $safeYear, $endMonth);
        } else {
            $quarterEnd = sprintf('%d-01-01', $safeYear + 1);
        }

        $sql = sprintf(
            "SELECT id, name, sales_stage, amount, date_closed, assigned_user_id
             FROM opportunities
             WHERE deleted = 0
               AND sales_stage NOT IN ('Closed Lost', 'closed_lost')
               AND date_closed >= '%s'
               AND date_closed < '%s'",
            $quarterStart,
            $quarterEnd
        );

        if ($repId !== null) {
            $sql .= " AND assigned_user_id = " . $db->quoted($repId);
        }

        $result = $db->query($sql);
        $rows = [];
        while ($row = $db->fetchByAssoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Get opportunities marked as At Risk in their plan items.
     *
     * Joins lf_plan_op_items (where is_at_risk = 1) with opportunities.
     * Returns opportunity details for display in Deal Risk section.
     *
     * @param string|null $repId Optional assigned_user_id to filter by
     * @return array[] Each row: ['id' => string, 'name' => string, 'account_name' => string, 'sales_stage' => string, 'amount' => string, 'date_closed' => string, 'assigned_user_id' => string]
     */
    public static function getAtRiskDeals(?string $repId = null): array
    {
        global $db;

        $sql = "SELECT DISTINCT o.id, o.name, COALESCE(a.name, 'No Account') AS account_name,
                       o.sales_stage, o.amount, o.date_closed, o.assigned_user_id
                FROM opportunities o
                INNER JOIN lf_plan_op_items poi ON poi.opportunity_id = o.id AND poi.deleted = 0 AND poi.is_at_risk = 1
                LEFT JOIN accounts_opportunities ao ON o.id = ao.opportunity_id AND ao.deleted = 0
                LEFT JOIN accounts a ON ao.account_id = a.id AND a.deleted = 0
                WHERE o.deleted = 0
                  AND o.sales_stage NOT IN ('Closed Won', 'Closed Lost', 'closed_won', 'closed_lost')";

        if ($repId !== null) {
            $sql .= " AND o.assigned_user_id = " . $db->quoted($repId);
        }

        $sql .= " ORDER BY o.amount DESC";

        $result = $db->query($sql);
        $rows = [];
        while ($row = $db->fetchByAssoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }
}
