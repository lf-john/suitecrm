<?php
/**
 * US-012: WeekHelper Utility Class Tests
 *
 * TDD-RED: These tests verify the WeekHelper utility class structure,
 * static methods, date calculations, and formatting.
 *
 * Target file: custom/include/LF_PlanningReporting/WeekHelper.php
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
$weekHelperFile = $projectRoot . '/custom/include/LF_PlanningReporting/WeekHelper.php';

// All 7 methods that must exist on the WeekHelper class
$expectedMethods = [
    'getCurrentWeekStart',
    'getWeekStart',
    'getWeekEnd',
    'getWeekList',
    'isCurrentWeek',
    'formatWeekRange',
    'getConfiguredWeekStartDay',
];

// The 6 pure calculation methods that accept $weekStartDay parameter
$pureMethodsWithWeekStartDay = [
    'getCurrentWeekStart',
    'getWeekStart',
    'getWeekList',
    'isCurrentWeek',
];

// Methods that must be static
$staticMethods = [
    'getCurrentWeekStart',
    'getWeekStart',
    'getWeekEnd',
    'getWeekList',
    'isCurrentWeek',
    'formatWeekRange',
    'getConfiguredWeekStartDay',
];

echo "US-012: WeekHelper Utility Class Tests\n";
echo str_repeat('=', 60) . "\n\n";

// ============================================================================
// Section 1: File Existence
// ============================================================================

echo "Section 1: File Existence\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: File exists
assert(
    file_exists($weekHelperFile),
    "WeekHelper.php must exist at custom/include/LF_PlanningReporting/WeekHelper.php"
);
echo "  [PASS] WeekHelper.php file exists\n";

// --- Happy Path: File is readable
assert(
    is_readable($weekHelperFile),
    "WeekHelper.php must be readable"
);
echo "  [PASS] WeekHelper.php is readable\n";

echo "\n";

// ============================================================================
// Section 2: PHP Format and sugarEntry Guard
// ============================================================================

echo "Section 2: PHP Format and sugarEntry Guard\n";
echo str_repeat('-', 40) . "\n";

$fileContent = file_get_contents($weekHelperFile);

// --- Happy Path: File starts with PHP opening tag
assert(
    str_starts_with(trim($fileContent), '<?php'),
    "WeekHelper.php must start with <?php tag"
);
echo "  [PASS] File starts with <?php tag\n";

// --- Happy Path: sugarEntry guard present
assert(
    str_contains($fileContent, "defined('sugarEntry')") || str_contains($fileContent, 'defined("sugarEntry")'),
    "WeekHelper.php must contain sugarEntry guard check"
);
echo "  [PASS] sugarEntry guard check present\n";

// --- Happy Path: sugarEntry guard uses die()
assert(
    str_contains($fileContent, 'die('),
    "WeekHelper.php must use die() in sugarEntry guard"
);
echo "  [PASS] sugarEntry guard uses die()\n";

// --- Happy Path: Guard at top of file (within first 5 lines)
$lines = explode("\n", $fileContent);
$guardFound = false;
for ($i = 0; $i < min(5, count($lines)); $i++) {
    if (str_contains($lines[$i], 'sugarEntry')) {
        $guardFound = true;
        break;
    }
}
assert($guardFound, "sugarEntry guard must be within first 5 lines of file");
echo "  [PASS] sugarEntry guard is near top of file\n";

echo "\n";

// ============================================================================
// Section 3: Class Structure
// ============================================================================

echo "Section 3: Class Structure\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Class WeekHelper is defined
assert(
    preg_match('/class\s+WeekHelper\b/', $fileContent) === 1,
    "File must define class WeekHelper"
);
echo "  [PASS] Class WeekHelper is defined\n";

// --- Happy Path: All 7 methods exist
foreach ($expectedMethods as $method) {
    assert(
        preg_match('/function\s+' . $method . '\s*\(/', $fileContent) === 1,
        "WeekHelper must have method '{$method}'"
    );
    echo "  [PASS] Method '{$method}' exists\n";
}

// --- Happy Path: All methods are public static
foreach ($staticMethods as $method) {
    assert(
        preg_match('/public\s+static\s+function\s+' . $method . '\s*\(/', $fileContent) === 1,
        "Method '{$method}' must be public static"
    );
    echo "  [PASS] Method '{$method}' is public static\n";
}

// --- Happy Path: Exactly 7 methods (no extra methods)
preg_match_all('/public\s+static\s+function\s+(\w+)\s*\(/', $fileContent, $methodMatches);
assert(
    count($methodMatches[1]) === 7,
    "WeekHelper must have exactly 7 public static methods, found " . count($methodMatches[1])
);
echo "  [PASS] Exactly 7 public static methods defined\n";

echo "\n";

// ============================================================================
// Section 4: Method Signatures - $weekStartDay Parameter
// ============================================================================

echo "Section 4: Method Signatures - \$weekStartDay Parameter\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: getCurrentWeekStart accepts $weekStartDay with default 5
assert(
    preg_match('/function\s+getCurrentWeekStart\s*\(\s*\$weekStartDay\s*=\s*5\s*\)/', $fileContent) === 1,
    "getCurrentWeekStart must accept \$weekStartDay parameter with default 5"
);
echo "  [PASS] getCurrentWeekStart(\$weekStartDay=5) signature correct\n";

// --- Happy Path: getWeekStart accepts $date and $weekStartDay with default 5
assert(
    preg_match('/function\s+getWeekStart\s*\(\s*\$date\s*,\s*\$weekStartDay\s*=\s*5\s*\)/', $fileContent) === 1,
    "getWeekStart must accept \$date and \$weekStartDay=5 parameters"
);
echo "  [PASS] getWeekStart(\$date, \$weekStartDay=5) signature correct\n";

// --- Happy Path: getWeekEnd accepts $weekStart only
assert(
    preg_match('/function\s+getWeekEnd\s*\(\s*\$weekStart\s*\)/', $fileContent) === 1,
    "getWeekEnd must accept \$weekStart parameter"
);
echo "  [PASS] getWeekEnd(\$weekStart) signature correct\n";

// --- Happy Path: getWeekList accepts $count and $weekStartDay with default 5
assert(
    preg_match('/function\s+getWeekList\s*\(\s*\$count\s*,\s*\$weekStartDay\s*=\s*5\s*\)/', $fileContent) === 1,
    "getWeekList must accept \$count and \$weekStartDay=5 parameters"
);
echo "  [PASS] getWeekList(\$count, \$weekStartDay=5) signature correct\n";

// --- Happy Path: isCurrentWeek accepts $weekStart and $weekStartDay with default 5
assert(
    preg_match('/function\s+isCurrentWeek\s*\(\s*\$weekStart\s*,\s*\$weekStartDay\s*=\s*5\s*\)/', $fileContent) === 1,
    "isCurrentWeek must accept \$weekStart and \$weekStartDay=5 parameters"
);
echo "  [PASS] isCurrentWeek(\$weekStart, \$weekStartDay=5) signature correct\n";

// --- Happy Path: formatWeekRange accepts $weekStart only
assert(
    preg_match('/function\s+formatWeekRange\s*\(\s*\$weekStart\s*\)/', $fileContent) === 1,
    "formatWeekRange must accept \$weekStart parameter"
);
echo "  [PASS] formatWeekRange(\$weekStart) signature correct\n";

// --- Happy Path: getConfiguredWeekStartDay accepts no parameters
assert(
    preg_match('/function\s+getConfiguredWeekStartDay\s*\(\s*\)/', $fileContent) === 1,
    "getConfiguredWeekStartDay must accept no parameters"
);
echo "  [PASS] getConfiguredWeekStartDay() signature correct (no parameters)\n";

echo "\n";

// ============================================================================
// Section 5: DateTime Usage
// ============================================================================

echo "Section 5: DateTime Usage\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: File uses DateTime class
assert(
    str_contains($fileContent, 'DateTime'),
    "WeekHelper must use PHP DateTime objects for date calculations"
);
echo "  [PASS] File references DateTime class\n";

// --- Happy Path: File uses DateTime for creation (new DateTime or DateTime::)
assert(
    str_contains($fileContent, 'new DateTime') || str_contains($fileContent, 'new \DateTime'),
    "WeekHelper must create DateTime objects with 'new DateTime'"
);
echo "  [PASS] File creates DateTime objects\n";

// --- Happy Path: File uses DateTime format method
assert(
    str_contains($fileContent, "->format("),
    "WeekHelper must use DateTime format() method for date formatting"
);
echo "  [PASS] File uses DateTime format() method\n";

// --- Negative Case: File should NOT use mktime() for date calculations
assert(
    !str_contains($fileContent, 'mktime('),
    "WeekHelper must NOT use mktime() - should use DateTime objects instead"
);
echo "  [PASS] File does not use mktime()\n";

// --- Negative Case: File should NOT use strtotime() for date calculations
assert(
    !str_contains($fileContent, 'strtotime('),
    "WeekHelper must NOT use strtotime() - should use DateTime objects instead"
);
echo "  [PASS] File does not use strtotime()\n";

echo "\n";

// ============================================================================
// Section 6: getConfiguredWeekStartDay() Facade
// ============================================================================

echo "Section 6: getConfiguredWeekStartDay() Facade\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: References LF_PRConfig for configuration reading
assert(
    str_contains($fileContent, 'LF_PRConfig'),
    "getConfiguredWeekStartDay must reference LF_PRConfig for config reading"
);
echo "  [PASS] File references LF_PRConfig\n";

// --- Happy Path: Uses getConfig method
assert(
    str_contains($fileContent, 'getConfig'),
    "getConfiguredWeekStartDay must use LF_PRConfig::getConfig() to read config"
);
echo "  [PASS] File uses getConfig method\n";

// --- Happy Path: References week_start_day configuration
assert(
    str_contains($fileContent, 'week_start_day'),
    "getConfiguredWeekStartDay must read 'week_start_day' configuration"
);
echo "  [PASS] File references 'week_start_day' config key\n";

echo "\n";

// ============================================================================
// Section 7: Load Class via Temp File Wrapper and Test getCurrentWeekStart()
// ============================================================================

echo "Section 7: getCurrentWeekStart() Functional Tests\n";
echo str_repeat('-', 40) . "\n";

// Load the class using temp file wrapper (sugarEntry guard)
$tempFile = tempnam(sys_get_temp_dir(), 'us012_');
$wrapperCode = "<?php\n";
$wrapperCode .= "define('sugarEntry', true);\n";
$wrapperCode .= "if (!class_exists('SugarBean')) { class SugarBean {} }\n";
$wrapperCode .= "if (!class_exists('DBManagerFactory')) { class DBManagerFactory { public static function getInstance() { return new class { public function quoted(\$s) { return \"'\".\$s.\"' \"; } public function getOne(\$q) { return null; } }; } } }\n";
$wrapperCode .= "include " . var_export($weekHelperFile, true) . ";\n";
file_put_contents($tempFile, $wrapperCode);
include $tempFile;
unlink($tempFile);

// Verify class is loadable
assert(
    class_exists('WeekHelper'),
    "WeekHelper class must be loadable after including file"
);
echo "  [PASS] WeekHelper class is loadable\n";

// --- Happy Path: getCurrentWeekStart with default (Friday=5)
$currentWeekStart = WeekHelper::getCurrentWeekStart();
assert(
    is_string($currentWeekStart),
    "getCurrentWeekStart() must return a string"
);
echo "  [PASS] getCurrentWeekStart() returns a string\n";

// --- Happy Path: Return value matches Y-m-d format
assert(
    preg_match('/^\d{4}-\d{2}-\d{2}$/', $currentWeekStart) === 1,
    "getCurrentWeekStart() must return date in Y-m-d format, got: {$currentWeekStart}"
);
echo "  [PASS] getCurrentWeekStart() returns Y-m-d format\n";

// --- Happy Path: The returned date is a Friday (day of week = 5)
$currentWeekStartDate = new DateTime($currentWeekStart);
assert(
    (int) $currentWeekStartDate->format('w') === 5,
    "getCurrentWeekStart(5) must return a Friday, got day " . $currentWeekStartDate->format('w') . " for date {$currentWeekStart}"
);
echo "  [PASS] getCurrentWeekStart(5) returns a Friday\n";

// --- Happy Path: The returned date is not in the future
$today = new DateTime('today');
assert(
    $currentWeekStartDate <= $today,
    "getCurrentWeekStart() must not return a future date"
);
echo "  [PASS] getCurrentWeekStart() does not return a future date\n";

// --- Happy Path: The returned date is within the last 7 days
$sevenDaysAgo = (new DateTime('today'))->modify('-6 days');
assert(
    $currentWeekStartDate >= $sevenDaysAgo,
    "getCurrentWeekStart() must be within last 7 days, got: {$currentWeekStart}"
);
echo "  [PASS] getCurrentWeekStart() is within last 7 days\n";

// --- Edge Case: getCurrentWeekStart with weekStartDay=0 (Sunday)
$sundayWeekStart = WeekHelper::getCurrentWeekStart(0);
$sundayDate = new DateTime($sundayWeekStart);
assert(
    (int) $sundayDate->format('w') === 0,
    "getCurrentWeekStart(0) must return a Sunday, got day " . $sundayDate->format('w')
);
echo "  [PASS] getCurrentWeekStart(0) returns a Sunday\n";

// --- Edge Case: getCurrentWeekStart with weekStartDay=1 (Monday)
$mondayWeekStart = WeekHelper::getCurrentWeekStart(1);
$mondayDate = new DateTime($mondayWeekStart);
assert(
    (int) $mondayDate->format('w') === 1,
    "getCurrentWeekStart(1) must return a Monday, got day " . $mondayDate->format('w')
);
echo "  [PASS] getCurrentWeekStart(1) returns a Monday\n";

echo "\n";

// ============================================================================
// Section 8: getWeekStart() Functional Tests
// ============================================================================

echo "Section 8: getWeekStart() Functional Tests\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: getWeekStart for a known Friday returns that Friday
$result = WeekHelper::getWeekStart('2026-01-30', 5); // Jan 30, 2026 is a Friday
assert(
    $result === '2026-01-30',
    "getWeekStart('2026-01-30', 5) must return '2026-01-30' (it IS a Friday), got: {$result}"
);
echo "  [PASS] getWeekStart returns same date when date IS the start day (Friday)\n";

// --- Happy Path: getWeekStart for a Saturday returns previous Friday
$result = WeekHelper::getWeekStart('2026-01-31', 5); // Jan 31, 2026 is a Saturday
assert(
    $result === '2026-01-30',
    "getWeekStart('2026-01-31', 5) must return '2026-01-30' (previous Friday), got: {$result}"
);
echo "  [PASS] getWeekStart returns previous Friday for Saturday\n";

// --- Happy Path: getWeekStart for a Sunday returns previous Friday
$result = WeekHelper::getWeekStart('2026-02-01', 5); // Feb 1, 2026 is a Sunday
assert(
    $result === '2026-01-30',
    "getWeekStart('2026-02-01', 5) must return '2026-01-30' (previous Friday), got: {$result}"
);
echo "  [PASS] getWeekStart returns previous Friday for Sunday\n";

// --- Happy Path: getWeekStart for a Monday returns previous Friday
$result = WeekHelper::getWeekStart('2026-02-02', 5); // Feb 2, 2026 is a Monday
assert(
    $result === '2026-01-30',
    "getWeekStart('2026-02-02', 5) must return '2026-01-30' (previous Friday), got: {$result}"
);
echo "  [PASS] getWeekStart returns previous Friday for Monday\n";

// --- Happy Path: getWeekStart for a Thursday returns previous Friday
$result = WeekHelper::getWeekStart('2026-02-05', 5); // Feb 5, 2026 is a Thursday
assert(
    $result === '2026-01-30',
    "getWeekStart('2026-02-05', 5) must return '2026-01-30' (previous Friday), got: {$result}"
);
echo "  [PASS] getWeekStart returns previous Friday for Thursday\n";

// --- Happy Path: getWeekStart for the next Friday returns that Friday
$result = WeekHelper::getWeekStart('2026-02-06', 5); // Feb 6, 2026 is a Friday
assert(
    $result === '2026-02-06',
    "getWeekStart('2026-02-06', 5) must return '2026-02-06' (it IS a Friday), got: {$result}"
);
echo "  [PASS] getWeekStart returns same date for next Friday\n";

// --- Edge Case: getWeekStart with weekStartDay=1 (Monday) on a Monday
$result = WeekHelper::getWeekStart('2026-02-02', 1); // Feb 2, 2026 is a Monday
assert(
    $result === '2026-02-02',
    "getWeekStart('2026-02-02', 1) must return '2026-02-02' (it IS a Monday), got: {$result}"
);
echo "  [PASS] getWeekStart with Monday start returns Monday when date is Monday\n";

// --- Edge Case: getWeekStart with weekStartDay=1 (Monday) on a Wednesday
$result = WeekHelper::getWeekStart('2026-02-04', 1); // Feb 4, 2026 is a Wednesday
assert(
    $result === '2026-02-02',
    "getWeekStart('2026-02-04', 1) must return '2026-02-02' (previous Monday), got: {$result}"
);
echo "  [PASS] getWeekStart with Monday start returns previous Monday for Wednesday\n";

// --- Edge Case: getWeekStart with weekStartDay=0 (Sunday) on a Sunday
$result = WeekHelper::getWeekStart('2026-02-01', 0); // Feb 1, 2026 is a Sunday
assert(
    $result === '2026-02-01',
    "getWeekStart('2026-02-01', 0) must return '2026-02-01' (it IS a Sunday), got: {$result}"
);
echo "  [PASS] getWeekStart with Sunday start returns Sunday when date is Sunday\n";

// --- Edge Case: getWeekStart with weekStartDay=0 (Sunday) on a Saturday
$result = WeekHelper::getWeekStart('2026-01-31', 0); // Jan 31, 2026 is a Saturday
assert(
    $result === '2026-01-25',
    "getWeekStart('2026-01-31', 0) must return '2026-01-25' (previous Sunday), got: {$result}"
);
echo "  [PASS] getWeekStart with Sunday start returns previous Sunday for Saturday\n";

// --- Edge Case: getWeekStart across year boundary
$result = WeekHelper::getWeekStart('2026-01-01', 5); // Jan 1, 2026 is a Thursday
assert(
    $result === '2025-12-26',
    "getWeekStart('2026-01-01', 5) must return '2025-12-26' (previous Friday across year boundary), got: {$result}"
);
echo "  [PASS] getWeekStart handles year boundary correctly\n";

// --- Edge Case: getWeekStart across month boundary
$result = WeekHelper::getWeekStart('2026-03-01', 5); // Mar 1, 2026 is a Sunday
assert(
    $result === '2026-02-27',
    "getWeekStart('2026-03-01', 5) must return '2026-02-27' (previous Friday across month boundary), got: {$result}"
);
echo "  [PASS] getWeekStart handles month boundary correctly\n";

// --- Happy Path: Return type is string
$result = WeekHelper::getWeekStart('2026-01-30', 5);
assert(
    is_string($result),
    "getWeekStart() must return a string"
);
echo "  [PASS] getWeekStart() returns a string\n";

echo "\n";

// ============================================================================
// Section 9: getWeekEnd() Functional Tests
// ============================================================================

echo "Section 9: getWeekEnd() Functional Tests\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: getWeekEnd returns weekStart + 6 days
$result = WeekHelper::getWeekEnd('2026-01-30'); // Friday Jan 30
assert(
    $result === '2026-02-05',
    "getWeekEnd('2026-01-30') must return '2026-02-05' (Jan 30 + 6 days), got: {$result}"
);
echo "  [PASS] getWeekEnd returns weekStart + 6 days\n";

// --- Happy Path: Return value matches Y-m-d format
assert(
    preg_match('/^\d{4}-\d{2}-\d{2}$/', $result) === 1,
    "getWeekEnd() must return date in Y-m-d format"
);
echo "  [PASS] getWeekEnd() returns Y-m-d format\n";

// --- Happy Path: Return type is string
assert(
    is_string($result),
    "getWeekEnd() must return a string"
);
echo "  [PASS] getWeekEnd() returns a string\n";

// --- Edge Case: getWeekEnd across month boundary
$result = WeekHelper::getWeekEnd('2026-02-27'); // Friday Feb 27
assert(
    $result === '2026-03-05',
    "getWeekEnd('2026-02-27') must return '2026-03-05' (crosses month boundary), got: {$result}"
);
echo "  [PASS] getWeekEnd handles month boundary correctly\n";

// --- Edge Case: getWeekEnd across year boundary
$result = WeekHelper::getWeekEnd('2025-12-26'); // Friday Dec 26
assert(
    $result === '2026-01-01',
    "getWeekEnd('2025-12-26') must return '2026-01-01' (crosses year boundary), got: {$result}"
);
echo "  [PASS] getWeekEnd handles year boundary correctly\n";

// --- Edge Case: getWeekEnd in February (non-leap year)
$result = WeekHelper::getWeekEnd('2026-02-23'); // Monday Feb 23
assert(
    $result === '2026-03-01',
    "getWeekEnd('2026-02-23') must return '2026-03-01' (crosses Feb boundary), got: {$result}"
);
echo "  [PASS] getWeekEnd handles February boundary correctly\n";

// --- Edge Case: getWeekEnd in leap year February
$result = WeekHelper::getWeekEnd('2028-02-25'); // Friday Feb 25
assert(
    $result === '2028-03-02',
    "getWeekEnd('2028-02-25') must return '2028-03-02' (crosses Feb boundary in leap year), got: {$result}"
);
echo "  [PASS] getWeekEnd handles leap year February correctly\n";

echo "\n";

// ============================================================================
// Section 10: formatWeekRange() Functional Tests
// ============================================================================

echo "Section 10: formatWeekRange() Functional Tests\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Format within same month
$result = WeekHelper::formatWeekRange('2026-01-02'); // Jan 2 - Jan 8, 2026
assert(
    $result === 'Jan 2 - Jan 8, 2026',
    "formatWeekRange('2026-01-02') must return 'Jan 2 - Jan 8, 2026', got: '{$result}'"
);
echo "  [PASS] formatWeekRange formats same-month range correctly\n";

// --- Happy Path: Format crossing month boundary
$result = WeekHelper::formatWeekRange('2026-01-30'); // Jan 30 - Feb 5, 2026
assert(
    $result === 'Jan 30 - Feb 5, 2026',
    "formatWeekRange('2026-01-30') must return 'Jan 30 - Feb 5, 2026', got: '{$result}'"
);
echo "  [PASS] formatWeekRange formats cross-month range correctly\n";

// --- Happy Path: Format crossing year boundary
$result = WeekHelper::formatWeekRange('2025-12-26'); // Dec 26 - Jan 1, 2026
assert(
    $result === 'Dec 26, 2025 - Jan 1, 2026',
    "formatWeekRange('2025-12-26') must return 'Dec 26, 2025 - Jan 1, 2026', got: '{$result}'"
);
echo "  [PASS] formatWeekRange formats cross-year range correctly\n";

// --- Happy Path: Return type is string
assert(
    is_string($result),
    "formatWeekRange() must return a string"
);
echo "  [PASS] formatWeekRange() returns a string\n";

// --- Edge Case: Format for date in middle of year
$result = WeekHelper::formatWeekRange('2026-06-05'); // Jun 5 - Jun 11, 2026
assert(
    $result === 'Jun 5 - Jun 11, 2026',
    "formatWeekRange('2026-06-05') must return 'Jun 5 - Jun 11, 2026', got: '{$result}'"
);
echo "  [PASS] formatWeekRange formats mid-year range correctly\n";

// --- Edge Case: Verify no leading zeros in day numbers
$result = WeekHelper::formatWeekRange('2026-01-02');
assert(
    !preg_match('/[A-Z][a-z]{2} 0\d/', $result),
    "formatWeekRange must not have leading zeros in day numbers, got: '{$result}'"
);
echo "  [PASS] formatWeekRange has no leading zeros in day numbers\n";

echo "\n";

// ============================================================================
// Section 11: isCurrentWeek() Functional Tests
// ============================================================================

echo "Section 11: isCurrentWeek() Functional Tests\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Current week start returns true
$currentWeekStart = WeekHelper::getCurrentWeekStart(5);
$result = WeekHelper::isCurrentWeek($currentWeekStart, 5);
assert(
    $result === true,
    "isCurrentWeek() must return true for current week start date"
);
echo "  [PASS] isCurrentWeek returns true for current week start\n";

// --- Happy Path: Return type is boolean
assert(
    is_bool($result),
    "isCurrentWeek() must return a boolean"
);
echo "  [PASS] isCurrentWeek() returns a boolean\n";

// --- Negative Case: Past week returns false
$pastDate = (new DateTime($currentWeekStart))->modify('-7 days')->format('Y-m-d');
$result = WeekHelper::isCurrentWeek($pastDate, 5);
assert(
    $result === false,
    "isCurrentWeek() must return false for previous week start date"
);
echo "  [PASS] isCurrentWeek returns false for previous week\n";

// --- Negative Case: Future week returns false
$futureDate = (new DateTime($currentWeekStart))->modify('+7 days')->format('Y-m-d');
$result = WeekHelper::isCurrentWeek($futureDate, 5);
assert(
    $result === false,
    "isCurrentWeek() must return false for future week start date"
);
echo "  [PASS] isCurrentWeek returns false for future week\n";

// --- Edge Case: isCurrentWeek with different weekStartDay
$mondayWeekStart = WeekHelper::getCurrentWeekStart(1);
$result = WeekHelper::isCurrentWeek($mondayWeekStart, 1);
assert(
    $result === true,
    "isCurrentWeek() must return true for current Monday week start with weekStartDay=1"
);
echo "  [PASS] isCurrentWeek with Monday start returns true for current Monday\n";

// --- Negative Case: Arbitrary non-week-start date returns false
$result = WeekHelper::isCurrentWeek('2020-01-15', 5);
assert(
    $result === false,
    "isCurrentWeek() must return false for an old date"
);
echo "  [PASS] isCurrentWeek returns false for old date\n";

echo "\n";

// ============================================================================
// Section 12: getWeekList() Functional Tests
// ============================================================================

echo "Section 12: getWeekList() Functional Tests\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Returns an array
$weekList = WeekHelper::getWeekList(5, 5);
assert(
    is_array($weekList),
    "getWeekList() must return an array"
);
echo "  [PASS] getWeekList() returns an array\n";

// --- Happy Path: Array has correct count
assert(
    count($weekList) === 5,
    "getWeekList(5) must return 5 elements, got " . count($weekList)
);
echo "  [PASS] getWeekList(5) returns 5 elements\n";

// --- Happy Path: Each element has required keys
$requiredKeys = ['weekStart', 'weekEnd', 'label', 'isCurrent'];
foreach ($weekList as $index => $week) {
    foreach ($requiredKeys as $key) {
        assert(
            array_key_exists($key, $week),
            "Week element at index {$index} must have key '{$key}'"
        );
    }
}
echo "  [PASS] All week elements have required keys (weekStart, weekEnd, label, isCurrent)\n";

// --- Happy Path: weekStart values are in Y-m-d format
foreach ($weekList as $index => $week) {
    assert(
        preg_match('/^\d{4}-\d{2}-\d{2}$/', $week['weekStart']) === 1,
        "weekStart at index {$index} must be Y-m-d format, got: {$week['weekStart']}"
    );
}
echo "  [PASS] All weekStart values are in Y-m-d format\n";

// --- Happy Path: weekEnd values are in Y-m-d format
foreach ($weekList as $index => $week) {
    assert(
        preg_match('/^\d{4}-\d{2}-\d{2}$/', $week['weekEnd']) === 1,
        "weekEnd at index {$index} must be Y-m-d format, got: {$week['weekEnd']}"
    );
}
echo "  [PASS] All weekEnd values are in Y-m-d format\n";

// --- Happy Path: weekEnd is weekStart + 6 days for each element
foreach ($weekList as $index => $week) {
    $expectedEnd = (new DateTime($week['weekStart']))->modify('+6 days')->format('Y-m-d');
    assert(
        $week['weekEnd'] === $expectedEnd,
        "weekEnd at index {$index} must be weekStart + 6 days. Expected {$expectedEnd}, got {$week['weekEnd']}"
    );
}
echo "  [PASS] All weekEnd values equal weekStart + 6 days\n";

// --- Happy Path: isCurrent is boolean
foreach ($weekList as $index => $week) {
    assert(
        is_bool($week['isCurrent']),
        "isCurrent at index {$index} must be a boolean"
    );
}
echo "  [PASS] All isCurrent values are booleans\n";

// --- Happy Path: label is a string
foreach ($weekList as $index => $week) {
    assert(
        is_string($week['label']),
        "label at index {$index} must be a string"
    );
}
echo "  [PASS] All label values are strings\n";

// --- Happy Path: Exactly one element has isCurrent = true
$currentCount = 0;
foreach ($weekList as $week) {
    if ($week['isCurrent'] === true) {
        $currentCount++;
    }
}
assert(
    $currentCount === 1,
    "Exactly one week must have isCurrent=true, found {$currentCount}"
);
echo "  [PASS] Exactly one week has isCurrent=true\n";

// --- Happy Path: The current week's weekStart matches getCurrentWeekStart()
$currentWeekStart = WeekHelper::getCurrentWeekStart(5);
$foundCurrentWeek = false;
foreach ($weekList as $week) {
    if ($week['isCurrent'] === true) {
        assert(
            $week['weekStart'] === $currentWeekStart,
            "Current week's weekStart must match getCurrentWeekStart(). Expected {$currentWeekStart}, got {$week['weekStart']}"
        );
        $foundCurrentWeek = true;
        break;
    }
}
assert($foundCurrentWeek, "Must find a current week in the list");
echo "  [PASS] Current week's weekStart matches getCurrentWeekStart()\n";

// --- Happy Path: Weeks are in chronological order
for ($i = 1; $i < count($weekList); $i++) {
    $prevStart = new DateTime($weekList[$i - 1]['weekStart']);
    $currStart = new DateTime($weekList[$i]['weekStart']);
    assert(
        $currStart > $prevStart,
        "Weeks must be in chronological order. Week {$i} start ({$weekList[$i]['weekStart']}) must be after week " . ($i - 1) . " start ({$weekList[$i - 1]['weekStart']})"
    );
}
echo "  [PASS] Weeks are in chronological order\n";

// --- Happy Path: Consecutive weeks are exactly 7 days apart
for ($i = 1; $i < count($weekList); $i++) {
    $prevStart = new DateTime($weekList[$i - 1]['weekStart']);
    $currStart = new DateTime($weekList[$i]['weekStart']);
    $diff = $prevStart->diff($currStart)->days;
    assert(
        $diff === 7,
        "Consecutive weeks must be 7 days apart. Week {$i} and " . ($i - 1) . " are {$diff} days apart"
    );
}
echo "  [PASS] Consecutive weeks are exactly 7 days apart\n";

// --- Happy Path: All weekStart dates fall on Friday (weekStartDay=5)
foreach ($weekList as $index => $week) {
    $dayOfWeek = (int)(new DateTime($week['weekStart']))->format('w');
    assert(
        $dayOfWeek === 5,
        "weekStart at index {$index} must be a Friday (day 5), got day {$dayOfWeek} for date {$week['weekStart']}"
    );
}
echo "  [PASS] All weekStart dates are Fridays\n";

// --- Happy Path: Labels match formatWeekRange output
foreach ($weekList as $index => $week) {
    $expectedLabel = WeekHelper::formatWeekRange($week['weekStart']);
    assert(
        $week['label'] === $expectedLabel,
        "Label at index {$index} must match formatWeekRange output. Expected '{$expectedLabel}', got '{$week['label']}'"
    );
}
echo "  [PASS] All labels match formatWeekRange() output\n";

// --- Edge Case: getWeekList with count=1
$singleWeekList = WeekHelper::getWeekList(1, 5);
assert(
    count($singleWeekList) === 1,
    "getWeekList(1) must return exactly 1 element, got " . count($singleWeekList)
);
echo "  [PASS] getWeekList(1) returns exactly 1 element\n";

// --- Edge Case: Single week should be current
assert(
    $singleWeekList[0]['isCurrent'] === true,
    "getWeekList(1) single element must have isCurrent=true"
);
echo "  [PASS] getWeekList(1) single element is current week\n";

// --- Edge Case: getWeekList with weekStartDay=1 (Monday)
$mondayWeekList = WeekHelper::getWeekList(3, 1);
foreach ($mondayWeekList as $index => $week) {
    $dayOfWeek = (int)(new DateTime($week['weekStart']))->format('w');
    assert(
        $dayOfWeek === 1,
        "weekStart at index {$index} must be a Monday (day 1) when weekStartDay=1, got day {$dayOfWeek}"
    );
}
echo "  [PASS] getWeekList with Monday start has all Mondays\n";

// --- Edge Case: Centered on current week (equal past/future weeks for odd counts)
// With count=5, there should be 2 past weeks, current week, 2 future weeks
$weekList5 = WeekHelper::getWeekList(5, 5);
$currentIdx = null;
foreach ($weekList5 as $i => $w) {
    if ($w['isCurrent'] === true) {
        $currentIdx = $i;
        break;
    }
}
assert(
    $currentIdx !== null,
    "Must find current week in list of 5"
);
assert(
    $currentIdx === 2,
    "Current week should be at index 2 (centered) in a 5-element list, got index {$currentIdx}"
);
echo "  [PASS] getWeekList(5) has current week centered at index 2\n";

echo "\n";

// ============================================================================
// Section 13: Edge Cases - Date Boundary Testing
// ============================================================================

echo "Section 13: Edge Cases - Date Boundary Testing\n";
echo str_repeat('-', 40) . "\n";

// --- Edge Case: getWeekStart for all 7 days of a week (Friday-Thursday cycle)
// Week of Jan 30, 2026 (Friday) to Feb 5, 2026 (Thursday)
$expectedFriday = '2026-01-30';
$daysInWeek = [
    '2026-01-30' => 'Friday',    // Start day itself
    '2026-01-31' => 'Saturday',
    '2026-02-01' => 'Sunday',
    '2026-02-02' => 'Monday',
    '2026-02-03' => 'Tuesday',
    '2026-02-04' => 'Wednesday',
    '2026-02-05' => 'Thursday',
];
foreach ($daysInWeek as $date => $dayName) {
    $result = WeekHelper::getWeekStart($date, 5);
    assert(
        $result === $expectedFriday,
        "getWeekStart('{$date}', 5) [{$dayName}] must return '{$expectedFriday}', got: {$result}"
    );
}
echo "  [PASS] getWeekStart returns correct Friday for all 7 days of a week\n";

// --- Edge Case: getWeekStart for weekStartDay=6 (Saturday)
$result = WeekHelper::getWeekStart('2026-01-31', 6); // Jan 31 is a Saturday
assert(
    $result === '2026-01-31',
    "getWeekStart('2026-01-31', 6) must return '2026-01-31' (it IS a Saturday), got: {$result}"
);
echo "  [PASS] getWeekStart with Saturday start works correctly\n";

// --- Edge Case: getWeekStart for weekStartDay=4 (Thursday)
$result = WeekHelper::getWeekStart('2026-02-05', 4); // Feb 5 is a Thursday
assert(
    $result === '2026-02-05',
    "getWeekStart('2026-02-05', 4) must return '2026-02-05' (it IS a Thursday), got: {$result}"
);
echo "  [PASS] getWeekStart with Thursday start works correctly\n";

// --- Edge Case: getWeekEnd for last day of year
$result = WeekHelper::getWeekEnd('2025-12-29');
assert(
    $result === '2026-01-04',
    "getWeekEnd('2025-12-29') must return '2026-01-04', got: {$result}"
);
echo "  [PASS] getWeekEnd crossing year boundary from Dec 29\n";

// --- Edge Case: February 28 in non-leap year
$result = WeekHelper::getWeekEnd('2026-02-25'); // Feb 25 + 6 = Mar 3
assert(
    $result === '2026-03-03',
    "getWeekEnd('2026-02-25') must return '2026-03-03', got: {$result}"
);
echo "  [PASS] getWeekEnd crossing Feb 28 in non-leap year\n";

// --- Edge Case: February 29 in leap year (2028)
$result = WeekHelper::getWeekEnd('2028-02-23'); // Feb 23 + 6 = Feb 29
assert(
    $result === '2028-02-29',
    "getWeekEnd('2028-02-23') must return '2028-02-29' (leap year), got: {$result}"
);
echo "  [PASS] getWeekEnd landing on Feb 29 in leap year\n";

echo "\n";

// ============================================================================
// Section 14: Negative Cases
// ============================================================================

echo "Section 14: Negative Cases\n";
echo str_repeat('-', 40) . "\n";

// --- Negative Case: Class should not have constructor (pure utility)
assert(
    preg_match('/function\s+__construct\s*\(/', $fileContent) !== 1,
    "WeekHelper should not have a constructor (pure static utility class)"
);
echo "  [PASS] WeekHelper has no constructor\n";

// --- Negative Case: No instance properties
assert(
    preg_match('/\b(public|private|protected)\s+\$/', $fileContent) !== 1,
    "WeekHelper should not have instance properties (pure static utility class)"
);
echo "  [PASS] WeekHelper has no instance properties\n";

// --- Negative Case: No database access in pure methods (only in getConfiguredWeekStartDay)
// The class should NOT use DBManagerFactory directly
assert(
    !str_contains($fileContent, 'DBManagerFactory'),
    "WeekHelper should not access database directly - use LF_PRConfig::getConfig() facade instead"
);
echo "  [PASS] WeekHelper does not use DBManagerFactory directly\n";

// --- Negative Case: File should not extend any class
assert(
    preg_match('/class\s+WeekHelper\s+extends\s+/', $fileContent) !== 1,
    "WeekHelper should not extend any class"
);
echo "  [PASS] WeekHelper does not extend any class\n";

echo "\n";

// ============================================================================
// Section 15: Cross-Validation
// ============================================================================

echo "Section 15: Cross-Validation\n";
echo str_repeat('-', 40) . "\n";

// --- Cross-validate: isCurrentWeek matches getCurrentWeekStart for all weekStartDay values
for ($day = 0; $day <= 6; $day++) {
    $weekStart = WeekHelper::getCurrentWeekStart($day);
    $isCurrent = WeekHelper::isCurrentWeek($weekStart, $day);
    assert(
        $isCurrent === true,
        "isCurrentWeek(getCurrentWeekStart({$day}), {$day}) must return true"
    );
}
echo "  [PASS] isCurrentWeek matches getCurrentWeekStart for all weekStartDay values 0-6\n";

// --- Cross-validate: getWeekEnd(getWeekStart(date)) always returns date within same week
$testDates = ['2026-01-30', '2026-02-15', '2026-06-20', '2025-12-31'];
foreach ($testDates as $date) {
    $start = WeekHelper::getWeekStart($date, 5);
    $end = WeekHelper::getWeekEnd($start);
    $startDt = new DateTime($start);
    $endDt = new DateTime($end);
    $diff = $startDt->diff($endDt)->days;
    assert(
        $diff === 6,
        "getWeekEnd(getWeekStart('{$date}')) must span exactly 6 days, got {$diff}"
    );
}
echo "  [PASS] getWeekEnd(getWeekStart(date)) always spans exactly 6 days\n";

// --- Cross-validate: formatWeekRange uses getWeekEnd internally
$weekStart = '2026-01-30';
$formatted = WeekHelper::formatWeekRange($weekStart);
$weekEnd = WeekHelper::getWeekEnd($weekStart);
$endDate = new DateTime($weekEnd);
$endDay = (int)$endDate->format('j');
assert(
    str_contains($formatted, (string)$endDay),
    "formatWeekRange must include the end day ({$endDay}) in the formatted string"
);
echo "  [PASS] formatWeekRange output includes the computed end day\n";

// --- Cross-validate: getWeekList labels are consistent with formatWeekRange
$weekList = WeekHelper::getWeekList(3, 5);
foreach ($weekList as $week) {
    $expected = WeekHelper::formatWeekRange($week['weekStart']);
    assert(
        $week['label'] === $expected,
        "Week label must match formatWeekRange(weekStart)"
    );
}
echo "  [PASS] getWeekList labels are consistent with formatWeekRange\n";

echo "\n";

// ============================================================================
// Summary
// ============================================================================

echo str_repeat('=', 60) . "\n";
echo "US-012: All WeekHelper tests PASSED!\n";
echo str_repeat('=', 60) . "\n";
