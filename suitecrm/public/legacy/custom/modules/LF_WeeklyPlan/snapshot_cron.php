<?php
/**
 * Opportunity Weekly Snapshot Cron Job
 *
 * Takes a point-in-time snapshot of all open opportunities (and recently closed ones).
 * Run via SuiteCRM's cron scheduler or manually.
 *
 * Usage:
 *   php snapshot_cron.php                    # Normal run (uses config for day/time check)
 *   php snapshot_cron.php --force            # Force run now regardless of day/time
 *   php snapshot_cron.php --backfill YYYY-MM-DD  # Backfill for a specific date
 */

if (!defined('sugarEntry')) {
    define('sugarEntry', true);
}

// Determine if running from CLI or via SuiteCRM scheduler
if (php_sapi_name() === 'cli') {
    chdir(dirname(__FILE__) . '/../../../');
    if (file_exists('include/entryPoint.php')) {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SERVER_SOFTWARE'] = 'Apache';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        require_once('include/entryPoint.php');
    }
}

require_once('custom/modules/LF_PRConfig/LF_PRConfig.php');

/**
 * Main snapshot function
 */
function runOpportunitySnapshot($overrideWeekEndAt = null)
{
    $db = DBManagerFactory::getInstance();
    $now = gmdate('Y-m-d H:i:s');

    // Read config
    $weekStartDay = (int)LF_PRConfig::getConfig('weeks', 'week_start_day') ?: 5; // Friday
    $snapshotTime = LF_PRConfig::getConfig('weeks', 'snapshot_time') ?: '09:00';

    // Calculate week_end_at (the boundary timestamp in UTC)
    if ($overrideWeekEndAt) {
        $weekEndAt = $overrideWeekEndAt;
    } else {
        // Calculate current week's boundary: Start Day at Snapshot Time Mountain
        $dayMap = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
        $dayName = $dayMap[$weekStartDay] ?? 'Friday';

        // Find the most recent occurrence of the configured day
        $mt = new DateTimeZone('America/Denver');
        $today = new DateTime('now', $mt);
        $targetDay = new DateTime("last $dayName", $mt);

        // If today IS the configured day, use today
        if ($today->format('l') === $dayName) {
            $targetDay = clone $today;
        }

        $targetDay->setTime((int)substr($snapshotTime, 0, 2), (int)substr($snapshotTime, 3, 2), 0);

        // Convert to UTC for storage
        $targetDay->setTimezone(new DateTimeZone('UTC'));
        $weekEndAt = $targetDay->format('Y-m-d H:i:s');
    }

    // Check if snapshot already exists for this week
    $checkQuery = sprintf(
        "SELECT COUNT(*) as cnt FROM opportunity_weekly_snapshot WHERE week_end_at = %s AND deleted = 0",
        $db->quoted($weekEndAt)
    );
    $checkResult = $db->query($checkQuery);
    $checkRow = $db->fetchByAssoc($checkResult);
    if ($checkRow && (int)$checkRow['cnt'] > 0) {
        echo "Snapshot already exists for $weekEndAt. Skipping.\n";
        return ['status' => 'skipped', 'week_end_at' => $weekEndAt, 'count' => (int)$checkRow['cnt']];
    }

    // Calculate previous week boundary for closed_at propagation
    $prevWeekEnd = new DateTime($weekEndAt, new DateTimeZone('UTC'));
    $prevWeekEnd->modify('-7 days');
    $prevWeekEndAt = $prevWeekEnd->format('Y-m-d H:i:s');

    // Get all opportunities that are currently open OR were closed this week
    // "Going forward we will not re-open old opportunities" — so we snapshot all non-deleted opps
    // that are either open or recently closed
    $oppQuery = "
        SELECT
            o.id,
            o.assigned_user_id,
            o.sales_stage,
            o.amount,
            o.opportunity_profit,
            o.date_entered,
            o.deleted,
            a.id as account_id
        FROM opportunities o
        LEFT JOIN accounts_opportunities ao ON ao.opportunity_id = o.id AND ao.deleted = 0
        LEFT JOIN accounts a ON a.id = ao.account_id AND a.deleted = 0
        WHERE o.deleted = 0
          AND (
            o.sales_stage NOT IN ('Closed Won', 'Closed Lost', 'closed_won', 'closed_lost')
            OR o.date_modified >= " . $db->quoted($prevWeekEndAt) . "
          )
    ";

    $oppResult = $db->query($oppQuery);
    $count = 0;
    $errors = 0;

    while ($opp = $db->fetchByAssoc($oppResult)) {
        $oppId = $opp['id'];

        // Extract stage percentage from stage name (e.g., "5-Specifications (30%)" -> 30)
        $stagePct = 0;
        $stageName = $opp['sales_stage'] ?? '';
        if (preg_match('/\((\d+)%\)/', $stageName, $matches)) {
            $stagePct = (int)$matches[1];
        } elseif (stripos($stageName, 'closed_won') !== false || stripos($stageName, 'Closed Won') !== false) {
            $stagePct = 100;
        } elseif (stripos($stageName, 'closed') !== false) {
            $stagePct = 0;
        }

        // Determine closed_status
        $closedStatus = 'OPEN';
        if (stripos($stageName, 'closed_won') !== false || stripos($stageName, 'Closed Won') !== false) {
            $closedStatus = 'WON';
        } elseif (stripos($stageName, 'closed_lost') !== false || stripos($stageName, 'Closed Lost') !== false) {
            $closedStatus = 'LOST';
        }

        // closed_at propagation: check previous week's snapshot
        $closedAt = 'NULL';
        $prevQuery = sprintf(
            "SELECT closed_at FROM opportunity_weekly_snapshot WHERE opportunity_id = %s AND week_end_at = %s AND deleted = 0",
            $db->quoted($oppId),
            $db->quoted($prevWeekEndAt)
        );
        $prevResult = $db->query($prevQuery);
        $prevRow = $db->fetchByAssoc($prevResult);

        if ($prevRow && !empty($prevRow['closed_at'])) {
            // Carry forward existing closed_at
            $closedAt = $db->quoted($prevRow['closed_at']);
        } elseif ($closedStatus !== 'OPEN') {
            // First time closed — set closed_at to now (or the snapshot boundary)
            $closedAt = $db->quoted($weekEndAt);
        }

        $newId = create_guid();
        $revenue = (float)($opp['amount'] ?? 0);
        $profit = (float)($opp['opportunity_profit'] ?? 0);
        $openAt = !empty($opp['date_entered']) ? $db->quoted($opp['date_entered']) : 'NULL';
        $accountId = !empty($opp['account_id']) ? $db->quoted($opp['account_id']) : 'NULL';

        $insertQuery = sprintf(
            "INSERT INTO opportunity_weekly_snapshot
                (id, opportunity_id, assigned_user_id, account_id, week_end_at, stage_name, stage_pct, revenue, profit, open_at, closed_status, closed_at, date_entered, date_modified, deleted)
            VALUES
                (%s, %s, %s, %s, %s, %s, %d, %s, %s, %s, %s, %s, %s, %s, 0)",
            $db->quoted($newId),
            $db->quoted($oppId),
            $db->quoted($opp['assigned_user_id'] ?? ''),
            $accountId,
            $db->quoted($weekEndAt),
            $db->quoted($stageName),
            $stagePct,
            $revenue,
            $profit,
            $openAt,
            $db->quoted($closedStatus),
            $closedAt,
            $db->quoted($now),
            $db->quoted($now)
        );

        $result = $db->query($insertQuery);
        if ($result === false) {
            $errors++;
            echo "ERROR: Failed to insert snapshot for opportunity $oppId\n";
        } else {
            $count++;
        }
    }

    echo "Snapshot complete for $weekEndAt: $count opportunities captured, $errors errors.\n";
    return ['status' => 'success', 'week_end_at' => $weekEndAt, 'count' => $count, 'errors' => $errors];
}

// CLI execution
if (php_sapi_name() === 'cli') {
    $args = $argv ?? [];
    $force = in_array('--force', $args);
    $backfillIdx = array_search('--backfill', $args);

    if ($backfillIdx !== false && isset($args[$backfillIdx + 1])) {
        // Backfill mode: use specified date with configured snapshot time
        $backfillDate = $args[$backfillIdx + 1];
        $snapshotTime = LF_PRConfig::getConfig('weeks', 'snapshot_time') ?: '09:00';

        // Convert to UTC
        $mt = new DateTimeZone('America/Denver');
        $dt = new DateTime("$backfillDate $snapshotTime:00", $mt);
        $dt->setTimezone(new DateTimeZone('UTC'));
        $weekEndAt = $dt->format('Y-m-d H:i:s');

        echo "Backfilling snapshot for $backfillDate $snapshotTime MT (UTC: $weekEndAt)...\n";
        runOpportunitySnapshot($weekEndAt);
    } elseif ($force) {
        echo "Force running snapshot...\n";
        runOpportunitySnapshot();
    } else {
        // Normal mode: check if it's the right day/time
        $weekStartDay = (int)LF_PRConfig::getConfig('weeks', 'week_start_day') ?: 5;
        $snapshotTime = LF_PRConfig::getConfig('weeks', 'snapshot_time') ?: '09:00';

        $dayMap = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
        $expectedDay = $dayMap[$weekStartDay] ?? 'Friday';

        $mt = new DateTimeZone('America/Denver');
        $nowMT = new DateTime('now', $mt);
        $currentDay = $nowMT->format('l');
        $currentTime = $nowMT->format('H:i');

        if ($currentDay !== $expectedDay) {
            echo "Not the configured snapshot day ($expectedDay). Current day: $currentDay. Skipping.\n";
            exit(0);
        }

        if ($currentTime < $snapshotTime) {
            echo "Before snapshot time ($snapshotTime MT). Current time: $currentTime MT. Skipping.\n";
            exit(0);
        }

        echo "Running scheduled snapshot...\n";
        runOpportunitySnapshot();
    }
}
