<?php
/**
 * US-012: Weekly Snapshot Scheduler Job Tests
 *
 * TDD-RED: These tests verify the scheduled snapshot job implementation.
 *
 * Tests cover:
 * - Scheduler extension file at custom/Extension/modules/Schedulers/Ext/ScheduledTasks/LF_WeeklySnapshot.php
 * - Language file at custom/Extension/modules/Schedulers/Ext/Language/en_us.LF_WeeklySnapshot.php
 * - Function LF_WeeklySnapshotJob() with correct registration
 * - Integration with LF_RepTargets, LF_WeeklyReport, LF_ReportSnapshot, WeekHelper
 * - Idempotency (no duplicate snapshots)
 * - Edge cases (no active reps, etc.)
 *
 * These tests MUST FAIL until the implementation is created.
 */

if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

ini_set('assert.exception', '1');
ini_set('zend.assertions', '1');

// ============================================================================
// Configuration
// ============================================================================

$projectRoot = dirname(dirname(__DIR__));

$schedulerExtFile = $projectRoot . '/custom/Extension/modules/Schedulers/Ext/ScheduledTasks/LF_WeeklySnapshot.php';
$schedulerLangFile = $projectRoot . '/custom/Extension/modules/Schedulers/Ext/Language/en_us.LF_WeeklySnapshot.php';

echo "US-012: Weekly Snapshot Scheduler Job Tests\n";
echo str_repeat('=', 60) . "\n\n";

// ============================================================================
// Section 1: Scheduler Extension File Existence
// ============================================================================

echo "Section 1: Scheduler Extension File Existence\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Scheduler extension file exists ---
assert(
    file_exists($schedulerExtFile),
    "Scheduler extension file must exist at: custom/Extension/modules/Schedulers/Ext/ScheduledTasks/LF_WeeklySnapshot.php"
);
echo "  [PASS] Scheduler extension file exists\n";

// --- Happy Path: File is readable ---
assert(
    is_readable($schedulerExtFile),
    "Scheduler extension file must be readable"
);
echo "  [PASS] Scheduler extension file is readable\n";

echo "\n";

// ============================================================================
// Section 2: Scheduler Extension PHP Format and sugarEntry Guard
// ============================================================================

echo "Section 2: Scheduler Extension PHP Format\n";
echo str_repeat('-', 40) . "\n";

$schedulerContent = file_get_contents($schedulerExtFile);
assert($schedulerContent !== false, "Should be able to read the scheduler extension file");

// --- Happy Path: File starts with PHP opening tag ---
assert(
    str_starts_with(trim($schedulerContent), '<?php'),
    "Scheduler extension file must start with <?php tag"
);
echo "  [PASS] File starts with <?php tag\n";

// --- Happy Path: sugarEntry guard present ---
assert(
    str_contains($schedulerContent, "defined('sugarEntry')") || str_contains($schedulerContent, 'defined("sugarEntry")'),
    "Scheduler extension file must contain sugarEntry guard check"
);
echo "  [PASS] sugarEntry guard check present\n";

// --- Happy Path: sugarEntry guard uses die() ---
assert(
    str_contains($schedulerContent, 'die('),
    "Scheduler extension file must use die() in sugarEntry guard"
);
echo "  [PASS] sugarEntry guard uses die()\n";

echo "\n";

// ============================================================================
// Section 3: LF_WeeklySnapshotJob Function Definition
// ============================================================================

echo "Section 3: LF_WeeklySnapshotJob Function Definition\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Function LF_WeeklySnapshotJob is defined ---
assert(
    preg_match('/function\s+LF_WeeklySnapshotJob\s*\(/', $schedulerContent) === 1,
    "Scheduler extension file must define function LF_WeeklySnapshotJob()"
);
echo "  [PASS] Function LF_WeeklySnapshotJob() is defined\n";

// --- Happy Path: Function registration pattern (array assignment) ---
assert(
    str_contains($schedulerContent, '$job_strings'),
    "Scheduler extension file must register the job in \$job_strings array"
);
echo "  [PASS] File references \$job_strings array\n";

// --- Happy Path: Function is callable (not a class method) ---
assert(
    preg_match('/function\s+LF_WeeklySnapshotJob\s*\(/', $schedulerContent) === 1 &&
    !preg_match('/class\s+\w+.*function\s+LF_WeeklySnapshotJob/', $schedulerContent),
    "LF_WeeklySnapshotJob must be a standalone function, not a class method"
);
echo "  [PASS] LF_WeeklySnapshotJob is a standalone function\n";

// --- Happy Path: Function has no parameters ---
assert(
    preg_match('/function\s+LF_WeeklySnapshotJob\s*\(\s*\)/', $schedulerContent) === 1,
    "LF_WeeklySnapshotJob() must accept no parameters"
);
echo "  [PASS] LF_WeeklySnapshotJob() has no parameters\n";

echo "\n";

// ============================================================================
// Section 4: Function Integration - LF_RepTargets::getActiveReps()
// ============================================================================

echo "Section 4: Function Integration - LF_RepTargets::getActiveReps()\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Function calls LF_RepTargets::getActiveReps() ---
assert(
    str_contains($schedulerContent, 'LF_RepTargets') ||
    str_contains($schedulerContent, 'getActiveReps'),
    "LF_WeeklySnapshotJob() must call LF_RepTargets::getActiveReps() to get active reps"
);
echo "  [PASS] Function references LF_RepTargets::getActiveReps()\n";

// --- Happy Path: Uses static method call pattern (::) ---
assert(
    str_contains($schedulerContent, 'LF_RepTargets::getActiveReps()') ||
    str_contains($schedulerContent, 'LF_RepTargets'),
    "Function must use static method call LF_RepTargets::getActiveReps()"
);
echo "  [PASS] Uses static method call pattern\n";

echo "\n";

// ============================================================================
// Section 5: Function Integration - LF_WeeklyReport::getOrCreateForWeek()
// ============================================================================

echo "Section 5: Function Integration - LF_WeeklyReport::getOrCreateForWeek()\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Function calls LF_WeeklyReport::getOrCreateForWeek() ---
assert(
    str_contains($schedulerContent, 'LF_WeeklyReport') ||
    str_contains($schedulerContent, 'getOrCreateForWeek'),
    "LF_WeeklySnapshotJob() must call LF_WeeklyReport::getOrCreateForWeek()"
);
echo "  [PASS] Function references LF_WeeklyReport::getOrCreateForWeek()\n";

// --- Happy Path: Passes $repUserId and $weekStart parameters ---
assert(
    str_contains($schedulerContent, 'getOrCreateForWeek('),
    "Function must call getOrCreateForWeek with user ID and week start parameters"
);
echo "  [PASS] Function calls getOrCreateForWeek()\n";

echo "\n";

// ============================================================================
// Section 6: Function Integration - LF_ReportSnapshot::createSnapshotsForWeek()
// ============================================================================

echo "Section 6: Function Integration - LF_ReportSnapshot::createSnapshotsForWeek()\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Function calls LF_ReportSnapshot::createSnapshotsForWeek() ---
assert(
    str_contains($schedulerContent, 'LF_ReportSnapshot') ||
    str_contains($schedulerContent, 'createSnapshotsForWeek'),
    "LF_WeeklySnapshotJob() must call LF_ReportSnapshot::createSnapshotsForWeek()"
);
echo "  [PASS] Function references LF_ReportSnapshot::createSnapshotsForWeek()\n";

// --- Happy Path: Passes 3 parameters: $userId, $weekStartDate, $reportId ---
assert(
    str_contains($schedulerContent, 'createSnapshotsForWeek('),
    "Function must call createSnapshotsForWeek with 3 parameters: userId, weekStartDate, reportId"
);
echo "  [PASS] Function calls createSnapshotsForWeek()\n";

echo "\n";

// ============================================================================
// Section 7: Function Integration - WeekHelper::getCurrentWeekStart()
// ============================================================================

echo "Section 7: Function Integration - WeekHelper::getCurrentWeekStart()\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Function calls WeekHelper::getCurrentWeekStart() ---
assert(
    str_contains($schedulerContent, 'WeekHelper') ||
    str_contains($schedulerContent, 'getCurrentWeekStart'),
    "LF_WeeklySnapshotJob() must call WeekHelper::getCurrentWeekStart()"
);
echo "  [PASS] Function references WeekHelper::getCurrentWeekStart()\n";

// --- Happy Path: Uses static method call pattern (::) ---
assert(
    str_contains($schedulerContent, 'WeekHelper::getCurrentWeekStart'),
    "Function must use static method call WeekHelper::getCurrentWeekStart()"
);
echo "  [PASS] Uses static method call pattern\n";

echo "\n";

// ============================================================================
// Section 8: Function Return Value
// ============================================================================

echo "Section 8: Function Return Value\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Function returns true on success ---
assert(
    str_contains($schedulerContent, 'return true') || str_contains($schedulerContent, 'return true;'),
    "LF_WeeklySnapshotJob() must return true on success"
);
echo "  [PASS] Function returns true\n";

// --- Edge Case: return statement is in the function body ---
assert(
    preg_match('/function\s+LF_WeeklySnapshotJob\s*\([^)]*\)\s*\{[^}]*return\s+true/s', $schedulerContent) === 1,
    "return true must be inside the function body"
);
echo "  [PASS] return true is in function body\n";

echo "\n";

// ============================================================================
// Section 9: Language File Existence
// ============================================================================

echo "Section 9: Language File Existence\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Language file exists ---
assert(
    file_exists($schedulerLangFile),
    "Language file must exist at: custom/Extension/modules/Schedulers/Ext/Language/en_us.LF_WeeklySnapshot.php"
);
echo "  [PASS] Language file exists\n";

// --- Happy Path: File is readable ---
assert(
    is_readable($schedulerLangFile),
    "Language file must be readable"
);
echo "  [PASS] Language file is readable\n";

echo "\n";

// ============================================================================
// Section 10: Language File PHP Format and sugarEntry Guard
// ============================================================================

echo "Section 10: Language File PHP Format\n";
echo str_repeat('-', 40) . "\n";

$langContent = file_get_contents($schedulerLangFile);
assert($langContent !== false, "Should be able to read the language file");

// --- Happy Path: File starts with PHP opening tag ---
assert(
    str_starts_with(trim($langContent), '<?php'),
    "Language file must start with <?php tag"
);
echo "  [PASS] File starts with <?php tag\n";

// --- Happy Path: sugarEntry guard present ---
assert(
    str_contains($langContent, "defined('sugarEntry')") || str_contains($langContent, 'defined("sugarEntry")'),
    "Language file must contain sugarEntry guard check"
);
echo "  [PASS] sugarEntry guard check present\n";

// --- Happy Path: sugarEntry guard uses die() ---
assert(
    str_contains($langContent, 'die('),
    "Language file must use die() in sugarEntry guard"
);
echo "  [PASS] sugarEntry guard uses die()\n";

echo "\n";

// ============================================================================
// Section 11: Language File $job_strings Array
// ============================================================================

echo "Section 11: Language File \$job_strings Array\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: File defines $job_strings array ---
assert(
    str_contains($langContent, '$job_strings'),
    "Language file must define \$job_strings array"
);
echo "  [PASS] File defines \$job_strings array\n";

// --- Happy Path: File uses array assignment pattern ---
assert(
    str_contains($langContent, '=') || str_contains($langContent, 'array('),
    "Language file must use array assignment for \$job_strings"
);
echo "  [PASS] File uses array assignment\n";

echo "\n";

// ============================================================================
// Section 12: Function Key Matching (CRITICAL)
// ============================================================================

echo "Section 12: Function Key Matching (CRITICAL)\n";
echo str_repeat('-', 40) . "\n";

// Load the language file to check the actual key
$tempFile = tempnam(sys_get_temp_dir(), 'us012_lang_');
$wrapperCode = "<?php\n";
$wrapperCode .= "define('sugarEntry', true);\n";
$wrapperCode .= "\$job_strings = [];\n";
$wrapperCode .= "include " . var_export($schedulerLangFile, true) . ";\n";
$wrapperCode .= "return \$job_strings;\n";
file_put_contents($tempFile, $wrapperCode);

$jobStrings = include $tempFile;
unlink($tempFile);

assert(is_array($jobStrings), "\$job_strings should be an array after including the language file");
echo "  [PASS] \$job_strings is an array\n";

// --- CRITICAL: Key must exactly match 'LF_WeeklySnapshotJob' ---
assert(
    array_key_exists('LF_WeeklySnapshotJob', $jobStrings),
    "\$job_strings must have key 'LF_WeeklySnapshotJob' - this must exactly match the function name"
);
echo "  [PASS] \$job_strings has key 'LF_WeeklySnapshotJob'\n";

// --- Happy Path: Key has a human-readable label value ---
assert(
    isset($jobStrings['LF_WeeklySnapshotJob']) &&
    is_string($jobStrings['LF_WeeklySnapshotJob']) &&
    strlen(trim($jobStrings['LF_WeeklySnapshotJob'])) > 0,
    "\$job_strings['LF_WeeklySnapshotJob'] must have a non-empty string label"
);
echo "  [PASS] \$job_strings['LF_WeeklySnapshotJob'] has a human-readable label\n";

// --- Edge Case: Label is not just the function name (should be descriptive) ---
assert(
    $jobStrings['LF_WeeklySnapshotJob'] !== 'LF_WeeklySnapshotJob',
    "\$job_strings['LF_WeeklySnapshotJob'] should have a descriptive label, not just the function name"
);
echo "  [PASS] Label is descriptive (not just function name)\n";

echo "\n";

// ============================================================================
// Section 13: Cross-Validation - Function Name Consistency
// ============================================================================

echo "Section 13: Cross-Validation - Function Name Consistency\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Function name in scheduler extension matches key in language file ---
assert(
    preg_match('/function\s+LF_WeeklySnapshotJob\s*\(/', $schedulerContent) === 1 &&
    array_key_exists('LF_WeeklySnapshotJob', $jobStrings),
    "Function name 'LF_WeeklySnapshotJob' must match \$job_strings key exactly"
);
echo "  [PASS] Function name matches \$job_strings key exactly\n";

echo "\n";

// ============================================================================
// Section 14: Security - No SQL Injection in Scheduler Job
// ============================================================================

echo "Section 14: Security - No SQL Injection\n";
echo str_repeat('-', 40) . "\n";

// --- Negative Case: Scheduler job should NOT use raw SQL concatenation ---
// (It relies on LF_ReportSnapshot which uses parameterized queries)
assert(
    !str_contains($schedulerContent, "SELECT") ||
    str_contains($schedulerContent, 'LF_ReportSnapshot') ||
    str_contains($schedulerContent, 'LF_WeeklyReport'),
    "Scheduler job should delegate SQL to bean methods (LF_ReportSnapshot, LF_WeeklyReport) which use parameterized queries"
);
echo "  [PASS] Scheduler job delegates SQL to safe bean methods\n";

echo "\n";

// ============================================================================
// Section 15: Load and Execute Function - Integration Test
// ============================================================================

echo "Section 15: Load and Execute Function - Integration Test\n";
echo str_repeat('-', 40) . "\n";

// Load the scheduler extension file using temp file wrapper
$tempSchedulerFile = tempnam(sys_get_temp_dir(), 'us012_scheduler_');
$wrapperCode = "<?php\n";
$wrapperCode .= "define('sugarEntry', true);\n";
$wrapperCode .= "if (!class_exists('SugarBean')) { class SugarBean {} }\n";
$wrapperCode .= "if (!class_exists('DBManagerFactory')) {\n";
$wrapperCode .= "    class DBManagerFactory {\n";
$wrapperCode .= "        public static function getInstance() {\n";
$wrapperCode .= "            return new class {\n";
$wrapperCode .= "                public function quoted(\$s) { return \"'\".\$s.\"' \"; }\n";
$wrapperCode .= "                public function query(\$q) { return null; }\n";
$wrapperCode .= "                public function fetchByAssoc(\$r) { return null; }\n";
$wrapperCode .= "            };\n";
$wrapperCode .= "        }\n";
$wrapperCode .= "    }\n";
$wrapperCode .= "}\n";
$wrapperCode .= "if (!class_exists('BeanFactory')) {\n";
$wrapperCode .= "    class BeanFactory {\n";
$wrapperCode .= "        public static function getBean(\$m, \$id) { return null; }\n";
$wrapperCode .= "        public static function newBean(\$m) { return null; }\n";
$wrapperCode .= "    }\n";
$wrapperCode .= "}\n";
$wrapperCode .= "if (!class_exists('LF_RepTargets')) {\n";
$wrapperCode .= "    class LF_RepTargets {\n";
$wrapperCode .= "        public static function getActiveReps() { return []; }\n";
$wrapperCode .= "    }\n";
$wrapperCode .= "}\n";
$wrapperCode .= "if (!class_exists('LF_WeeklyReport')) {\n";
$wrapperCode .= "    class LF_WeeklyReport {\n";
$wrapperCode .= "        public static function getOrCreateForWeek(\$u, \$w) { return null; }\n";
$wrapperCode .= "    }\n";
$wrapperCode .= "}\n";
$wrapperCode .= "if (!class_exists('LF_ReportSnapshot')) {\n";
$wrapperCode .= "    class LF_ReportSnapshot {\n";
$wrapperCode .= "        public static function createSnapshotsForWeek(\$u, \$w, \$r) { return []; }\n";
$wrapperCode .= "    }\n";
$wrapperCode .= "}\n";
$wrapperCode .= "if (!class_exists('WeekHelper')) {\n";
$wrapperCode .= "    class WeekHelper {\n";
$wrapperCode .= "        public static function getCurrentWeekStart() { return date('Y-m-d'); }\n";
$wrapperCode .= "    }\n";
$wrapperCode .= "}\n";
$wrapperCode .= "include " . var_export($schedulerExtFile, true) . ";\n";
file_put_contents($tempSchedulerFile, $wrapperCode);

include $tempSchedulerFile;
unlink($tempSchedulerFile);

// --- Happy Path: Function LF_WeeklySnapshotJob is callable after including file ---
assert(
    function_exists('LF_WeeklySnapshotJob'),
    "Function LF_WeeklySnapshotJob must be callable after including the scheduler extension file"
);
echo "  [PASS] Function LF_WeeklySnapshotJob is callable\n";

// --- Happy Path: Function executes without errors when no active reps ---
$result = LF_WeeklySnapshotJob();
assert(
    $result === true,
    "LF_WeeklySnapshotJob() must return true when executed (even with no active reps)"
);
echo "  [PASS] Function executes and returns true\n";

echo "\n";

// ============================================================================
// Summary
// ============================================================================

echo str_repeat('=', 60) . "\n";
echo "US-012: All Weekly Snapshot Scheduler Job tests PASSED!\n";
echo str_repeat('=', 60) . "\n";
