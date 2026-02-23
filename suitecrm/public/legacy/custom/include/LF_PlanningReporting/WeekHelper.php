<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'custom/modules/LF_PRConfig/LF_PRConfig.php';

/**
 * WeekHelper - Pure static utility class for week-based date calculations.
 *
 * Provides methods for computing week start/end dates, formatting week ranges,
 * and generating week lists for the LF Planning & Reporting module.
 *
 * All date calculations use PHP DateTime objects. The $weekStartDay parameter
 * follows PHP's date('w') convention: 0=Sunday, 1=Monday, ..., 6=Saturday.
 * The default week start day is 5 (Friday).
 */
class WeekHelper
{
    /**
     * Get the start date of the current week.
     *
     * Returns the most recent occurrence of the configured week start day
     * on or before today. Uses the current user's timezone preference.
     *
     * @param int $weekStartDay Day of week (0=Sun, 1=Mon, ..., 6=Sat). Default 5 (Friday).
     * @return string Date in Y-m-d format.
     */
    public static function getCurrentWeekStart($weekStartDay = 5)
    {
        $timezone = self::getUserTimezone();
        $today = new DateTime('now', new DateTimeZone($timezone));
        return self::getWeekStart($today->format('Y-m-d'), $weekStartDay);
    }

    /**
     * Get the current user's timezone, falling back to system default.
     *
     * @return string Timezone identifier (e.g., 'America/New_York')
     */
    private static function getUserTimezone()
    {
        global $current_user, $sugar_config;

        // Try user preference first
        if (!empty($current_user) && method_exists($current_user, 'getPreference')) {
            $userTz = $current_user->getPreference('timezone');
            if (!empty($userTz)) {
                return $userTz;
            }
        }

        // Fall back to system default timezone
        if (!empty($sugar_config['default_timezone'])) {
            return $sugar_config['default_timezone'];
        }

        // Final fallback
        return date_default_timezone_get();
    }

    /**
     * Get the week start date for a given date.
     *
     * Returns the most recent occurrence of $weekStartDay on or before $date.
     * If $date itself falls on $weekStartDay, that same date is returned.
     *
     * @param string $date Date string in Y-m-d format.
     * @param int $weekStartDay Day of week (0=Sun, 1=Mon, ..., 6=Sat). Default 5 (Friday).
     * @return string Date in Y-m-d format.
     */
    public static function getWeekStart($date, $weekStartDay = 5)
    {
        $dt = new DateTime($date);
        $currentDow = (int) $dt->format('w');

        $diff = $currentDow - $weekStartDay;
        if ($diff < 0) {
            $diff += 7;
        }

        if ($diff > 0) {
            $dt->modify('-' . $diff . ' days');
        }

        return $dt->format('Y-m-d');
    }

    /**
     * Get the end date of a week given its start date.
     *
     * The week end is always 6 days after the week start (7-day week).
     *
     * @param string $weekStart Week start date in Y-m-d format.
     * @return string Date in Y-m-d format (weekStart + 6 days).
     */
    public static function getWeekEnd($weekStart)
    {
        $dt = new DateTime($weekStart);
        $dt->modify('+6 days');
        return $dt->format('Y-m-d');
    }

    /**
     * Get a list of weeks centered on the current week.
     *
     * Returns an array of associative arrays, each containing:
     *   - weekStart: string (Y-m-d)
     *   - weekEnd: string (Y-m-d)
     *   - label: string (formatted week range)
     *   - isCurrent: bool
     *
     * The current week is placed at the center of the list. For a count of 5,
     * there will be 2 past weeks, the current week, and 2 future weeks.
     *
     * @param int $count Number of weeks to return.
     * @param int $weekStartDay Day of week (0=Sun, 1=Mon, ..., 6=Sat). Default 5 (Friday).
     * @return array[] List of week data arrays.
     */
    public static function getWeekList($count, $weekStartDay = 5)
    {
        $currentWeekStart = self::getCurrentWeekStart($weekStartDay);
        $currentDt = new DateTime($currentWeekStart);

        // Calculate how many weeks before the current week
        $weeksBefore = (int) floor(($count - 1) / 2);

        // Start from the earliest week
        $startDt = clone $currentDt;
        $startDt->modify('-' . ($weeksBefore * 7) . ' days');

        $weeks = [];
        for ($i = 0; $i < $count; $i++) {
            $weekStart = $startDt->format('Y-m-d');
            $weekEnd = self::getWeekEnd($weekStart);
            $label = self::formatWeekRange($weekStart);
            $isCurrent = self::isCurrentWeek($weekStart, $weekStartDay);

            $weeks[] = [
                'weekStart' => $weekStart,
                'weekEnd' => $weekEnd,
                'label' => $label,
                'isCurrent' => $isCurrent,
            ];

            $startDt->modify('+7 days');
        }

        return $weeks;
    }

    /**
     * Check if a given week start date is the current week.
     *
     * @param string $weekStart Week start date in Y-m-d format.
     * @param int $weekStartDay Day of week (0=Sun, 1=Mon, ..., 6=Sat). Default 5 (Friday).
     * @return bool True if $weekStart matches the current week's start date.
     */
    public static function isCurrentWeek($weekStart, $weekStartDay = 5)
    {
        return $weekStart === self::getCurrentWeekStart($weekStartDay);
    }

    /**
     * Format a week range as a human-readable string.
     *
     * Format: "Mon D - Mon D, YYYY" when start and end are in the same year.
     * Format: "Mon D, YYYY - Mon D, YYYY" when crossing a year boundary.
     * Day numbers have no leading zeros.
     *
     * @param string $weekStart Week start date in Y-m-d format.
     * @return string Formatted week range string.
     */
    public static function formatWeekRange($weekStart)
    {
        $startDt = new DateTime($weekStart);
        $endDt = new DateTime($weekStart);
        $endDt->modify('+6 days');

        $startYear = $startDt->format('Y');
        $endYear = $endDt->format('Y');

        if ($startYear !== $endYear) {
            // Cross-year: "Dec 26, 2025 - Jan 1, 2026"
            return $startDt->format('M j') . ', ' . $startYear . ' - ' . $endDt->format('M j') . ', ' . $endYear;
        }

        // Same year: "Jan 30 - Feb 5, 2026"
        return $startDt->format('M j') . ' - ' . $endDt->format('M j') . ', ' . $endYear;
    }

    /**
     * Get the configured week start day from the LF Planning & Reporting configuration.
     *
     * This is a facade method that reads the 'week_start_day' setting from
     * LF_PRConfig. All other methods accept $weekStartDay as a parameter
     * for testability; this method provides the configured default.
     *
     * @return int Day of week (0=Sun, 1=Mon, ..., 6=Sat).
     */
    public static function getConfiguredWeekStartDay()
    {
        $value = LF_PRConfig::getConfig('weeks', 'week_start_day');
        if ($value !== null) {
            return (int) $value;
        }
        return 5;
    }
}
