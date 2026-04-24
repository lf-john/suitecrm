<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * LF Auto-Submit Job
 *
 * Runs hourly. Checks configured plan_day/plan_time and report deadline, then
 * auto-submits any in_progress weekly plans or reports that are past their cutoff.
 *
 * Times are stored in Mountain Time (America/Denver) to match snapshot_time.
 *
 * @return bool
 */
function LF_AutoSubmitJob()
{
    require_once 'custom/modules/LF_PRConfig/LF_PRConfig.php';
    require_once 'custom/include/LF_PlanningReporting/WeekHelper.php';
    require_once 'custom/modules/LF_RepTargets/LF_RepTargets.php';

    $db = DBManagerFactory::getInstance();

    $tz = new DateTimeZone('America/Denver');
    $now = new DateTime('now', $tz);
    $todayDow = (int)$now->format('N'); // 1=Mon ... 7=Sun
    $todayTime = $now->format('H:i');

    $configWeekStartDay = WeekHelper::getConfiguredWeekStartDay();
    $weekStart = WeekHelper::getCurrentWeekStart($configWeekStartDay);

    // ── Plan auto-submit ─────────────────────────────────────────────────────
    // Config: plan_day (ISO day 1-7, default 1=Monday), plan_time (HH:MM, default 10:00)
    $planDay  = (int)(LF_PRConfig::getConfig('weeks', 'plan_day') ?: 1);
    $planTime = LF_PRConfig::getConfig('weeks', 'plan_time') ?: '10:00';

    if ($todayDow === $planDay && $todayTime >= $planTime) {
        $sql = "SELECT id FROM lf_weekly_plan
                WHERE week_start_date = " . $db->quoted($weekStart) . "
                  AND status = 'in_progress' AND deleted = 0";
        $r = $db->query($sql);
        $now_sql = gmdate('Y-m-d H:i:s');
        while ($row = $db->fetchByAssoc($r)) {
            $db->query("UPDATE lf_weekly_plan SET status = 'submitted',
                        submitted_date = " . $db->quoted($now_sql) . ",
                        date_modified = " . $db->quoted($now_sql) . "
                        WHERE id = " . $db->quoted($row['id']));
            LoggerManager::getLogger()->info("LF_AutoSubmit: auto-submitted plan " . $row['id']);
        }
    }

    // ── Report auto-submit ───────────────────────────────────────────────────
    // Reports are for the PREVIOUS week. Auto-submit when the current week starts
    // (week_start_day) and time >= snapshot_time.
    $snapshotTime = LF_PRConfig::getConfig('weeks', 'snapshot_time') ?: '09:00';

    if ($todayDow === $configWeekStartDay && $todayTime >= $snapshotTime) {
        // Previous week = 7 days before current week_start
        $prevWeekStart = date('Y-m-d', strtotime($weekStart . ' -7 days'));
        $sql = "SELECT id FROM lf_weekly_report
                WHERE week_start_date = " . $db->quoted($prevWeekStart) . "
                  AND status = 'in_progress' AND deleted = 0";
        $r = $db->query($sql);
        $now_sql = gmdate('Y-m-d H:i:s');
        while ($row = $db->fetchByAssoc($r)) {
            $db->query("UPDATE lf_weekly_report SET status = 'submitted',
                        submitted_date = " . $db->quoted($now_sql) . ",
                        date_modified = " . $db->quoted($now_sql) . "
                        WHERE id = " . $db->quoted($row['id']));
            LoggerManager::getLogger()->info("LF_AutoSubmit: auto-submitted report " . $row['id']);
        }
    }

    return true;
}

$job_strings[] = 'LF_AutoSubmitJob';
