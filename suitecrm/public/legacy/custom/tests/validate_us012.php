<?php
/**
 * Simple validation script for US-012 files
 * Checks that all required files exist and contain required patterns
 */

$projectRoot = dirname(dirname(__DIR__));
$schedulerExtFile = $projectRoot . '/custom/Extension/modules/Schedulers/Ext/ScheduledTasks/LF_WeeklySnapshot.php';
$schedulerLangFile = $projectRoot . '/custom/Extension/modules/Schedulers/Ext/Language/en_us.LF_WeeklySnapshot.php';

echo "US-012: File Validation\n";
echo str_repeat('=', 60) . "\n\n";

// Check scheduler extension file
echo "Checking scheduler extension file...\n";
if (file_exists($schedulerExtFile)) {
    echo "  [OK] File exists\n";
    $schedulerContent = file_get_contents($schedulerExtFile);

    if (str_starts_with(trim($schedulerContent), '<?php')) {
        echo "  [OK] Starts with <?php\n";
    }
    if (str_contains($schedulerContent, "defined('sugarEntry')")) {
        echo "  [OK] Has sugarEntry guard\n";
    }
    if (preg_match('/function\s+LF_WeeklySnapshotJob\s*\(\s*\)/', $schedulerContent)) {
        echo "  [OK] Defines LF_WeeklySnapshotJob() with no parameters\n";
    }
    if (str_contains($schedulerContent, 'LF_RepTargets::getActiveReps()')) {
        echo "  [OK] Calls LF_RepTargets::getActiveReps()\n";
    }
    if (str_contains($schedulerContent, 'LF_WeeklyReport::getOrCreateForWeek')) {
        echo "  [OK] Calls LF_WeeklyReport::getOrCreateForWeek()\n";
    }
    if (str_contains($schedulerContent, 'LF_ReportSnapshot::createSnapshotsForWeek')) {
        echo "  [OK] Calls LF_ReportSnapshot::createSnapshotsForWeek()\n";
    }
    if (str_contains($schedulerContent, 'WeekHelper::getCurrentWeekStart()')) {
        echo "  [OK] Calls WeekHelper::getCurrentWeekStart()\n";
    }
    if (str_contains($schedulerContent, 'return true')) {
        echo "  [OK] Returns true\n";
    }
} else {
    echo "  [FAIL] File does not exist\n";
}

echo "\n";

// Check language file
echo "Checking language file...\n";
if (file_exists($schedulerLangFile)) {
    echo "  [OK] File exists\n";
    $langContent = file_get_contents($schedulerLangFile);

    if (str_starts_with(trim($langContent), '<?php')) {
        echo "  [OK] Starts with <?php\n";
    }
    if (str_contains($langContent, "defined('sugarEntry')")) {
        echo "  [OK] Has sugarEntry guard\n";
    }
    if (str_contains($langContent, '$job_strings[\'LF_WeeklySnapshotJob\']')) {
        echo "  [OK] Defines \$job_strings['LF_WeeklySnapshotJob']\n";
    }
    if (str_contains($langContent, 'LF: Weekly Opportunity Snapshot')) {
        echo "  [OK] Has descriptive label\n";
    }
} else {
    echo "  [FAIL] File does not exist\n";
}

echo "\n";
echo str_repeat('=', 60) . "\n";
echo "Validation complete!\n";
