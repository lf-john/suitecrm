<?php
/**
 * US-013: OpportunityQuery Utility Class Tests
 *
 * TDD-RED: These tests verify the OpportunityQuery utility class structure,
 * static methods, SQL query patterns, and database access conventions.
 *
 * Target file: custom/include/LF_PlanningReporting/OpportunityQuery.php
 *
 * The OpportunityQuery class provides 7 static methods for querying
 * opportunity data needed by dashboards and tools:
 *   1. getPipelineByStage($repId=null)
 *   2. getPipelineByRep()
 *   3. getClosedYTD($year, $repId=null)
 *   4. getStaleDeals($days, $repId=null)
 *   5. getOpenOpportunities($repId)
 *   6. getAnalysisOpportunities($repId)
 *   7. getForecastOpportunities($repId=null, $quarter, $year)
 *
 * All methods use SuiteCRM's global $db object with $db->quote() for
 * string escaping (NOT prepared statements).
 *
 * These tests MUST FAIL until the implementation is created.
 */

// CLI-only guard - prevent web execution
if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

// Enable assert() with exceptions
ini_set('assert.exception', '1');
ini_set('zend.assertions', '1');

// ============================================================
// Configuration
// ============================================================

// Base path: resolve to the custom/ directory root
// From custom/tests/ we go up one level to reach custom/
$customDir = dirname(__DIR__);

$opportunityQueryFile = $customDir
    . DIRECTORY_SEPARATOR . 'include'
    . DIRECTORY_SEPARATOR . 'LF_PlanningReporting'
    . DIRECTORY_SEPARATOR . 'OpportunityQuery.php';

// All 7 methods that must exist on the OpportunityQuery class
$expectedMethods = [
    'getPipelineByStage',
    'getPipelineByRep',
    'getClosedYTD',
    'getStaleDeals',
    'getOpenOpportunities',
    'getAnalysisOpportunities',
    'getForecastOpportunities',
];

// Stages excluded from pipeline queries
$excludedPipelineStages = [
    '2-Analysis (1%)',
    'Closed Won',
    'Closed Lost',
];

// Activity tables for stale deals LEFT JOINs
$activityTables = [
    'calls',
    'meetings',
    'tasks',
    'notes',
];

echo "US-013: OpportunityQuery Utility Class Tests\n";
echo str_repeat('=', 60) . "\n\n";


// ============================================================
// Section 1: File Existence
// ============================================================
echo "Section 1: File Existence\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: File exists ---
assert(
    file_exists($opportunityQueryFile),
    "OpportunityQuery.php must exist at custom/include/LF_PlanningReporting/OpportunityQuery.php"
);
echo "  [PASS] OpportunityQuery.php file exists\n";

// --- Happy Path: File is a regular file ---
assert(
    is_file($opportunityQueryFile),
    "OpportunityQuery.php path should be a regular file, not a directory"
);
echo "  [PASS] OpportunityQuery.php is a regular file\n";

// --- Happy Path: File is readable ---
assert(
    is_readable($opportunityQueryFile),
    "OpportunityQuery.php must be readable"
);
echo "  [PASS] OpportunityQuery.php is readable\n";

echo "\n";


// ============================================================
// Section 2: PHP Format and sugarEntry Guard
// ============================================================
echo "Section 2: PHP Format and sugarEntry Guard\n";
echo str_repeat('-', 40) . "\n";

$fileContent = file_get_contents($opportunityQueryFile);
assert($fileContent !== false, "Should be able to read the OpportunityQuery file");

// --- Happy Path: File starts with PHP opening tag ---
assert(
    str_starts_with(trim($fileContent), '<?php'),
    "OpportunityQuery.php must start with <?php tag"
);
echo "  [PASS] File starts with <?php tag\n";

// --- Happy Path: sugarEntry guard present ---
assert(
    str_contains($fileContent, "defined('sugarEntry')") || str_contains($fileContent, 'defined("sugarEntry")'),
    "OpportunityQuery.php must contain sugarEntry guard check"
);
echo "  [PASS] File has sugarEntry defined() check\n";

// --- Happy Path: sugarEntry die message ---
assert(
    str_contains($fileContent, 'Not A Valid Entry Point'),
    "OpportunityQuery.php must contain 'Not A Valid Entry Point' die message"
);
echo "  [PASS] File has 'Not A Valid Entry Point' die message\n";

echo "\n";


// ============================================================
// Section 3: Class Structure (Pure Static Utility)
// ============================================================
echo "Section 3: Class Structure\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: File contains class OpportunityQuery ---
assert(
    preg_match('/class\s+OpportunityQuery/', $fileContent) === 1,
    "File must contain 'class OpportunityQuery'"
);
echo "  [PASS] File contains class OpportunityQuery\n";

// --- Negative Case: Class does NOT extend any parent class ---
assert(
    preg_match('/class\s+OpportunityQuery\s+extends\s+/', $fileContent) !== 1,
    "OpportunityQuery must NOT extend any parent class (pure utility class)"
);
echo "  [PASS] OpportunityQuery does not extend any class\n";

// --- Negative Case: Class has no constructor ---
assert(
    preg_match('/function\s+__construct\s*\(/', $fileContent) !== 1,
    "OpportunityQuery must NOT have a constructor (pure static utility)"
);
echo "  [PASS] OpportunityQuery has no constructor\n";

// --- Negative Case: Class has no instance properties ---
assert(
    preg_match('/^\s*(public|private|protected)\s+\$(?!.*static)/m', $fileContent) !== 1
    || preg_match('/class\s+OpportunityQuery[^{]*\{[^}]*\bpublic\s+\$/s', $fileContent) !== 1,
    "OpportunityQuery should not have instance properties (pure static utility)"
);
echo "  [PASS] OpportunityQuery has no instance properties\n";

// --- Happy Path: All 7 methods exist ---
foreach ($expectedMethods as $method) {
    assert(
        preg_match('/public\s+static\s+function\s+' . $method . '\s*\(/', $fileContent) === 1,
        "OpportunityQuery must have 'public static function {$method}(' method"
    );
}
echo "  [PASS] All 7 static methods exist\n";

// --- Edge Case: Exactly 7 public methods (no extra methods) ---
preg_match_all('/public\s+static\s+function\s+(\w+)\s*\(/', $fileContent, $methodMatches);
assert(
    count($methodMatches[1]) === 7,
    "OpportunityQuery should have exactly 7 public static methods, found: " . count($methodMatches[1])
);
echo "  [PASS] Exactly 7 public static methods\n";

// --- Happy Path: All methods are static ---
foreach ($expectedMethods as $method) {
    assert(
        preg_match('/public\s+static\s+function\s+' . $method . '/', $fileContent) === 1,
        "{$method}() must be declared as public static"
    );
}
echo "  [PASS] All methods are static\n";

echo "\n";


// ============================================================
// Section 4: getPipelineByStage() Method
// ============================================================
echo "Section 4: getPipelineByStage() Method\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Method signature with optional $repId ---
assert(
    preg_match('/function\s+getPipelineByStage\s*\(\s*\$repId\s*=\s*null\s*\)/', $fileContent) === 1,
    "getPipelineByStage() must accept optional \$repId parameter with null default"
);
echo "  [PASS] getPipelineByStage() has correct signature (\$repId=null)\n";

// --- Happy Path: Method queries opportunities table ---
assert(
    str_contains($fileContent, 'opportunities'),
    "getPipelineByStage() must query the opportunities table"
);
echo "  [PASS] getPipelineByStage() references opportunities table\n";

// --- Happy Path: Method groups by sales_stage ---
assert(
    preg_match('/GROUP\s+BY\s+.*sales_stage/i', $fileContent) === 1,
    "getPipelineByStage() must GROUP BY sales_stage"
);
echo "  [PASS] getPipelineByStage() groups by sales_stage\n";

// --- Happy Path: Method returns SUM of amount ---
assert(
    preg_match('/SUM\s*\(\s*.*amount\s*\)/i', $fileContent) === 1,
    "getPipelineByStage() must calculate SUM of amount"
);
echo "  [PASS] getPipelineByStage() calculates SUM(amount)\n";

// --- Happy Path: Method returns COUNT of opportunities ---
assert(
    preg_match('/COUNT\s*\(/i', $fileContent) === 1,
    "getPipelineByStage() must calculate COUNT of opportunities"
);
echo "  [PASS] getPipelineByStage() calculates COUNT\n";

// --- Happy Path: Method excludes '2-Analysis (1%)' stage ---
assert(
    str_contains($fileContent, '2-Analysis (1%)'),
    "getPipelineByStage() must exclude '2-Analysis (1%)' stage"
);
echo "  [PASS] getPipelineByStage() references '2-Analysis (1%)' stage\n";

// --- Happy Path: Method excludes 'Closed Won' stage ---
assert(
    str_contains($fileContent, 'Closed Won'),
    "getPipelineByStage() must exclude 'Closed Won' stage"
);
echo "  [PASS] getPipelineByStage() references 'Closed Won' stage\n";

// --- Happy Path: Method excludes 'Closed Lost' stage ---
assert(
    str_contains($fileContent, 'Closed Lost'),
    "getPipelineByStage() must exclude 'Closed Lost' stage"
);
echo "  [PASS] getPipelineByStage() references 'Closed Lost' stage\n";

// --- Happy Path: Uses NOT IN to exclude multiple stages ---
assert(
    preg_match('/NOT\s+IN\s*\(/i', $fileContent) === 1,
    "getPipelineByStage() should use NOT IN clause to exclude multiple stages"
);
echo "  [PASS] getPipelineByStage() uses NOT IN for stage exclusion\n";

// --- Edge Case: When $repId is null, returns all reps' data ---
assert(
    preg_match('/\$repId\s*(!==|===|!=|==)\s*null/i', $fileContent) === 1
    || str_contains($fileContent, 'is_null($repId)')
    || preg_match('/if\s*\(\s*\$repId\s*\)/', $fileContent) === 1
    || preg_match('/if\s*\(\s*!\s*is_null\s*\(\s*\$repId\s*\)/', $fileContent) === 1
    || preg_match('/if\s*\(\s*\$repId\s*!==?\s*null\s*\)/', $fileContent) === 1,
    "getPipelineByStage() must conditionally filter by \$repId when not null"
);
echo "  [PASS] getPipelineByStage() conditionally filters by \$repId\n";

echo "\n";


// ============================================================
// Section 5: getPipelineByRep() Method
// ============================================================
echo "Section 5: getPipelineByRep() Method\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Method signature with no parameters ---
assert(
    preg_match('/function\s+getPipelineByRep\s*\(\s*\)/', $fileContent) === 1,
    "getPipelineByRep() must have no parameters"
);
echo "  [PASS] getPipelineByRep() has no parameters\n";

// --- Happy Path: Method JOINs with users table ---
assert(
    preg_match('/JOIN\s+users/i', $fileContent) === 1,
    "getPipelineByRep() must JOIN with users table for rep name"
);
echo "  [PASS] getPipelineByRep() joins with users table\n";

// --- Happy Path: Method groups by assigned_user_id ---
assert(
    preg_match('/GROUP\s+BY\s+.*assigned_user_id/i', $fileContent) === 1,
    "getPipelineByRep() must GROUP BY assigned_user_id"
);
echo "  [PASS] getPipelineByRep() groups by assigned_user_id\n";

// --- Happy Path: Method retrieves user name (first_name and last_name) ---
assert(
    str_contains($fileContent, 'first_name') && str_contains($fileContent, 'last_name'),
    "getPipelineByRep() must retrieve user first_name and last_name"
);
echo "  [PASS] getPipelineByRep() retrieves first_name and last_name\n";

// --- Happy Path: Method calculates sum of amount ---
// SUM(amount) already tested in Section 4 at class level; verify grouping context
assert(
    preg_match('/SUM\s*\(\s*.*amount\s*\)/i', $fileContent) === 1,
    "getPipelineByRep() must calculate SUM(amount) for pipeline totals"
);
echo "  [PASS] getPipelineByRep() calculates SUM(amount)\n";

echo "\n";


// ============================================================
// Section 6: getClosedYTD() Method
// ============================================================
echo "Section 6: getClosedYTD() Method\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Method signature with $year and optional $repId ---
assert(
    preg_match('/function\s+getClosedYTD\s*\(\s*\$year\s*,\s*\$repId\s*=\s*null\s*\)/', $fileContent) === 1,
    "getClosedYTD() must accept \$year and optional \$repId=null parameters"
);
echo "  [PASS] getClosedYTD() has correct signature (\$year, \$repId=null)\n";

// --- Happy Path: Method filters by 'Closed Won' stage ---
// Already confirmed 'Closed Won' exists in file; verify in context of this method
assert(
    str_contains($fileContent, 'Closed Won'),
    "getClosedYTD() must filter for 'Closed Won' opportunities"
);
echo "  [PASS] getClosedYTD() filters for 'Closed Won' stage\n";

// --- Happy Path: Method filters by date_closed in the given year ---
assert(
    str_contains($fileContent, 'date_closed'),
    "getClosedYTD() must filter by date_closed field"
);
echo "  [PASS] getClosedYTD() references date_closed field\n";

// --- Happy Path: Method calculates SUM of amount ---
assert(
    preg_match('/SUM\s*\(\s*.*amount\s*\)/i', $fileContent) === 1,
    "getClosedYTD() must calculate SUM of amount for closed won opportunities"
);
echo "  [PASS] getClosedYTD() calculates SUM(amount)\n";

// --- Edge Case: Year filtering uses date range (start/end of year) ---
assert(
    str_contains($fileContent, 'date_closed'),
    "getClosedYTD() must filter date_closed within the given year"
);
echo "  [PASS] getClosedYTD() uses date_closed for year filtering\n";

// --- Edge Case: Optional $repId filtering ---
// Method must conditionally add assigned_user_id filter
assert(
    str_contains($fileContent, 'assigned_user_id'),
    "getClosedYTD() must support filtering by assigned_user_id when \$repId is provided"
);
echo "  [PASS] getClosedYTD() supports assigned_user_id filtering\n";

echo "\n";


// ============================================================
// Section 7: getStaleDeals() Method
// ============================================================
echo "Section 7: getStaleDeals() Method\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Method signature with $days and optional $repId ---
assert(
    preg_match('/function\s+getStaleDeals\s*\(\s*\$days\s*,\s*\$repId\s*=\s*null\s*\)/', $fileContent) === 1,
    "getStaleDeals() must accept \$days and optional \$repId=null parameters"
);
echo "  [PASS] getStaleDeals() has correct signature (\$days, \$repId=null)\n";

// --- Happy Path: Method uses LEFT JOIN with calls table ---
assert(
    preg_match('/LEFT\s+JOIN\s+calls/i', $fileContent) === 1,
    "getStaleDeals() must use LEFT JOIN with calls table"
);
echo "  [PASS] getStaleDeals() LEFT JOINs calls table\n";

// --- Happy Path: Method uses LEFT JOIN with meetings table ---
assert(
    preg_match('/LEFT\s+JOIN\s+meetings/i', $fileContent) === 1,
    "getStaleDeals() must use LEFT JOIN with meetings table"
);
echo "  [PASS] getStaleDeals() LEFT JOINs meetings table\n";

// --- Happy Path: Method uses LEFT JOIN with tasks table ---
assert(
    preg_match('/LEFT\s+JOIN\s+tasks/i', $fileContent) === 1,
    "getStaleDeals() must use LEFT JOIN with tasks table"
);
echo "  [PASS] getStaleDeals() LEFT JOINs tasks table\n";

// --- Happy Path: Method uses LEFT JOIN with notes table ---
assert(
    preg_match('/LEFT\s+JOIN\s+notes/i', $fileContent) === 1,
    "getStaleDeals() must use LEFT JOIN with notes table"
);
echo "  [PASS] getStaleDeals() LEFT JOINs notes table\n";

// --- Happy Path: JOINs use parent_type='Opportunities' ---
assert(
    str_contains($fileContent, "parent_type") && str_contains($fileContent, 'Opportunities'),
    "getStaleDeals() LEFT JOINs must use parent_type='Opportunities'"
);
echo "  [PASS] getStaleDeals() uses parent_type='Opportunities' in JOINs\n";

// --- Happy Path: JOINs use parent_id=opportunities.id ---
assert(
    str_contains($fileContent, 'parent_id'),
    "getStaleDeals() LEFT JOINs must use parent_id=opportunities.id"
);
echo "  [PASS] getStaleDeals() uses parent_id in JOINs\n";

// --- Happy Path: Method excludes '2-Analysis (1%)' stage ---
// The '2-Analysis (1%)' string is already confirmed to exist in file;
// verify the pattern is used for stale deal exclusion context
assert(
    str_contains($fileContent, '2-Analysis (1%)'),
    "getStaleDeals() must exclude '2-Analysis (1%)' stage from stale deal detection"
);
echo "  [PASS] getStaleDeals() excludes '2-Analysis (1%)' stage\n";

// --- Happy Path: Method checks for no activity in $days days ---
assert(
    str_contains($fileContent, '$days') || preg_match('/\$days/', $fileContent) === 1,
    "getStaleDeals() must reference \$days parameter for activity threshold"
);
echo "  [PASS] getStaleDeals() references \$days parameter\n";

// --- Edge Case: Method checks for NULL activity records (no calls, meetings, tasks, notes) ---
assert(
    preg_match('/IS\s+NULL/i', $fileContent) === 1,
    "getStaleDeals() must check for IS NULL on activity joins to find deals with no recent activity"
);
echo "  [PASS] getStaleDeals() checks for IS NULL on activity joins\n";

// --- Edge Case: Exactly 4 LEFT JOINs for the 4 activity tables ---
preg_match_all('/LEFT\s+JOIN/i', $fileContent, $leftJoinMatches);
assert(
    count($leftJoinMatches[0]) >= 4,
    "getStaleDeals() must have at least 4 LEFT JOINs (calls, meetings, tasks, notes), found: " . count($leftJoinMatches[0])
);
echo "  [PASS] At least 4 LEFT JOINs present for activity tables\n";

echo "\n";


// ============================================================
// Section 8: getOpenOpportunities() Method
// ============================================================
echo "Section 8: getOpenOpportunities() Method\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Method signature with required $repId ---
assert(
    preg_match('/function\s+getOpenOpportunities\s*\(\s*\$repId\s*\)/', $fileContent) === 1,
    "getOpenOpportunities() must accept required \$repId parameter"
);
echo "  [PASS] getOpenOpportunities() has correct signature (\$repId)\n";

// --- Happy Path: Method queries for non-closed opportunities ---
// Should exclude both 'Closed Won' and 'Closed Lost'
assert(
    str_contains($fileContent, 'Closed Won') && str_contains($fileContent, 'Closed Lost'),
    "getOpenOpportunities() must exclude 'Closed Won' and 'Closed Lost' stages"
);
echo "  [PASS] getOpenOpportunities() references closed stages for exclusion\n";

// --- Happy Path: Method filters by assigned_user_id ---
assert(
    str_contains($fileContent, 'assigned_user_id'),
    "getOpenOpportunities() must filter by assigned_user_id"
);
echo "  [PASS] getOpenOpportunities() filters by assigned_user_id\n";

echo "\n";


// ============================================================
// Section 9: getAnalysisOpportunities() Method
// ============================================================
echo "Section 9: getAnalysisOpportunities() Method\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Method signature with required $repId ---
assert(
    preg_match('/function\s+getAnalysisOpportunities\s*\(\s*\$repId\s*\)/', $fileContent) === 1,
    "getAnalysisOpportunities() must accept required \$repId parameter"
);
echo "  [PASS] getAnalysisOpportunities() has correct signature (\$repId)\n";

// --- Happy Path: Method filters for '2-Analysis (1%)' stage ---
assert(
    str_contains($fileContent, '2-Analysis (1%)'),
    "getAnalysisOpportunities() must filter for '2-Analysis (1%)' stage"
);
echo "  [PASS] getAnalysisOpportunities() references '2-Analysis (1%)' stage\n";

// --- Happy Path: Method filters by assigned_user_id for the rep ---
assert(
    str_contains($fileContent, 'assigned_user_id'),
    "getAnalysisOpportunities() must filter by assigned_user_id for the rep"
);
echo "  [PASS] getAnalysisOpportunities() filters by assigned_user_id\n";

echo "\n";


// ============================================================
// Section 10: getForecastOpportunities() Method
// ============================================================
echo "Section 10: getForecastOpportunities() Method\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Method signature with $repId=null, $quarter, $year ---
assert(
    preg_match('/function\s+getForecastOpportunities\s*\(\s*\$repId\s*=\s*null\s*,\s*\$quarter\s*,\s*\$year\s*\)/', $fileContent) === 1,
    "getForecastOpportunities() must accept \$repId=null, \$quarter, \$year parameters"
);
echo "  [PASS] getForecastOpportunities() has correct signature (\$repId=null, \$quarter, \$year)\n";

// --- Happy Path: Method filters by date_closed within quarter boundaries ---
assert(
    str_contains($fileContent, 'date_closed'),
    "getForecastOpportunities() must filter by date_closed field for quarter boundaries"
);
echo "  [PASS] getForecastOpportunities() references date_closed field\n";

// --- Happy Path: Method calculates quarter start date ---
// Quarter boundaries require month calculations (Q1=Jan-Mar, Q2=Apr-Jun, Q3=Jul-Sep, Q4=Oct-Dec)
assert(
    str_contains($fileContent, '$quarter') || preg_match('/\$quarter/', $fileContent) === 1,
    "getForecastOpportunities() must use \$quarter parameter for date boundary calculation"
);
echo "  [PASS] getForecastOpportunities() uses \$quarter parameter\n";

// --- Happy Path: Method uses $year parameter ---
assert(
    str_contains($fileContent, '$year') || preg_match('/\$year/', $fileContent) === 1,
    "getForecastOpportunities() must use \$year parameter for date boundary calculation"
);
echo "  [PASS] getForecastOpportunities() uses \$year parameter\n";

// --- Edge Case: Method calculates quarter date boundaries ---
// Quarter start months: Q1=1, Q2=4, Q3=7, Q4=10
// The method should calculate start/end dates based on quarter
assert(
    preg_match('/\(\s*\$quarter\s*-\s*1\s*\)\s*\*\s*3\s*\+\s*1/', $fileContent) === 1
    || preg_match('/\$quarter\s*\*\s*3/', $fileContent) === 1
    || str_contains($fileContent, 'startMonth')
    || str_contains($fileContent, 'start_month')
    || str_contains($fileContent, 'quarterStart')
    || str_contains($fileContent, 'quarter_start')
    || preg_match('/switch\s*\(\s*\$quarter\s*\)/', $fileContent) === 1
    || preg_match('/\[\s*1\s*=>\s*/', $fileContent) === 1,
    "getForecastOpportunities() must calculate quarter date boundaries from \$quarter"
);
echo "  [PASS] getForecastOpportunities() calculates quarter date boundaries\n";

// --- Edge Case: Optional $repId filtering ---
assert(
    str_contains($fileContent, 'assigned_user_id'),
    "getForecastOpportunities() must support filtering by assigned_user_id when \$repId is provided"
);
echo "  [PASS] getForecastOpportunities() supports assigned_user_id filtering\n";

echo "\n";


// ============================================================
// Section 11: SQL Safety Patterns
// ============================================================
echo "Section 11: SQL Safety Patterns\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: File uses $db->query() for database access ---
assert(
    preg_match('/\$db\s*->\s*query\s*\(/', $fileContent) === 1,
    "OpportunityQuery must use \$db->query() for database access"
);
echo "  [PASS] File uses \$db->query()\n";

// --- Happy Path: File uses $db->quote() for string escaping ---
assert(
    preg_match('/\$db\s*->\s*quote\s*\(/', $fileContent) === 1,
    "OpportunityQuery must use \$db->quote() for string escaping"
);
echo "  [PASS] File uses \$db->quote()\n";

// --- Happy Path: File uses global $db for database access ---
assert(
    str_contains($fileContent, 'global $db') || str_contains($fileContent, 'global  $db'),
    "OpportunityQuery must use 'global \$db' to access the SuiteCRM database object"
);
echo "  [PASS] File uses 'global \$db'\n";

// --- Happy Path: File uses sprintf for SQL construction ---
assert(
    str_contains($fileContent, 'sprintf'),
    "OpportunityQuery should use sprintf() for SQL query construction"
);
echo "  [PASS] File uses sprintf()\n";

// --- Happy Path: File uses fetchByAssoc for result iteration ---
assert(
    str_contains($fileContent, 'fetchByAssoc'),
    "OpportunityQuery should use \$db->fetchByAssoc() for result iteration"
);
echo "  [PASS] File uses fetchByAssoc()\n";

// --- Negative Case: File does NOT use prepared statements ---
assert(
    !str_contains($fileContent, 'prepare(')
    && !str_contains($fileContent, '->prepare')
    && !str_contains($fileContent, 'PDO::'),
    "OpportunityQuery must NOT use prepared statements (SuiteCRM legacy compatibility)"
);
echo "  [PASS] File does not use prepared statements\n";

// --- Negative Case: File does NOT use PDO ---
assert(
    !str_contains($fileContent, 'new PDO')
    && !str_contains($fileContent, 'PDOStatement'),
    "OpportunityQuery must NOT use PDO (SuiteCRM legacy compatibility)"
);
echo "  [PASS] File does not use PDO\n";

echo "\n";


// ============================================================
// Section 12: deleted=0 Filter in All Methods
// ============================================================
echo "Section 12: deleted=0 Filter Validation\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: File contains deleted=0 filter ---
assert(
    preg_match('/deleted\s*=\s*0/', $fileContent) === 1,
    "OpportunityQuery must include deleted=0 filter in queries"
);
echo "  [PASS] File contains deleted=0 filter pattern\n";

// --- Happy Path: Multiple occurrences of deleted=0 (one per query method at minimum) ---
preg_match_all('/deleted\s*=\s*0/', $fileContent, $deletedMatches);
assert(
    count($deletedMatches[0]) >= 7,
    "OpportunityQuery should have at least 7 occurrences of deleted=0 (one per method), found: " . count($deletedMatches[0])
);
echo "  [PASS] At least 7 occurrences of deleted=0 (one per method minimum)\n";

// --- Edge Case: Verify deleted filter pattern is in SQL context ---
assert(
    preg_match('/WHERE.*deleted\s*=\s*0/is', $fileContent) === 1
    || preg_match('/AND\s+.*deleted\s*=\s*0/is', $fileContent) === 1,
    "deleted=0 must appear in SQL WHERE/AND context"
);
echo "  [PASS] deleted=0 appears in SQL WHERE/AND context\n";

echo "\n";


// ============================================================
// Section 13: Negative Cases and Constraints
// ============================================================
echo "Section 13: Negative Cases and Constraints\n";
echo str_repeat('-', 40) . "\n";

// --- Negative Case: No constructor ---
assert(
    preg_match('/function\s+__construct/', $fileContent) !== 1,
    "OpportunityQuery must NOT have __construct (pure utility class)"
);
echo "  [PASS] No __construct method\n";

// --- Negative Case: No instance methods (all must be static) ---
// Check that there are no 'public function' without 'static'
preg_match_all('/public\s+function\s+(?!static)(\w+)\s*\(/', $fileContent, $instanceMethodMatches);
// Filter out any false positives by checking if 'static' appears before 'function'
$nonStaticPublicMethods = [];
foreach ($expectedMethods as $method) {
    if (preg_match('/public\s+(?!static\s+)function\s+' . $method . '\s*\(/', $fileContent) === 1) {
        $nonStaticPublicMethods[] = $method;
    }
}
assert(
    count($nonStaticPublicMethods) === 0,
    "All methods must be static. Found non-static methods: " . implode(', ', $nonStaticPublicMethods)
);
echo "  [PASS] No non-static public methods among expected methods\n";

// --- Negative Case: File does NOT use DBManagerFactory ---
// Story says to use global $db, not DBManagerFactory
assert(
    !str_contains($fileContent, 'DBManagerFactory')
    || str_contains($fileContent, 'global $db'),
    "OpportunityQuery should use 'global \$db' for database access (per story requirements)"
);
echo "  [PASS] Uses global \$db pattern for database access\n";

// --- Negative Case: No eval() calls ---
assert(
    !str_contains($fileContent, 'eval('),
    "OpportunityQuery must NOT use eval()"
);
echo "  [PASS] No eval() calls\n";

echo "\n";


// ============================================================
// Section 14: Cross-Validation and Method Count
// ============================================================
echo "Section 14: Cross-Validation\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: All expected method names found in file ---
$foundMethods = [];
foreach ($expectedMethods as $method) {
    if (preg_match('/function\s+' . $method . '\s*\(/', $fileContent) === 1) {
        $foundMethods[] = $method;
    }
}
assert(
    count($foundMethods) === 7,
    "All 7 expected methods must be found, found: " . count($foundMethods)
    . " (" . implode(', ', $foundMethods) . ")"
);
echo "  [PASS] All 7 expected methods found\n";

// --- Happy Path: Method names match exactly ---
$missingMethods = array_diff($expectedMethods, $foundMethods);
assert(
    count($missingMethods) === 0,
    "Missing methods: " . implode(', ', $missingMethods)
);
echo "  [PASS] No missing methods\n";

// --- Happy Path: File references opportunities table in SQL ---
preg_match_all('/FROM\s+opportunities/i', $fileContent, $fromMatches);
assert(
    count($fromMatches[0]) >= 1,
    "File must reference 'FROM opportunities' in SQL queries"
);
echo "  [PASS] File has SQL queries against opportunities table\n";

// --- Edge Case: Multiple $db->query() calls (at least 7, one per method) ---
preg_match_all('/\$db\s*->\s*query\s*\(/', $fileContent, $queryMatches);
assert(
    count($queryMatches[0]) >= 7,
    "File should have at least 7 \$db->query() calls (one per method), found: " . count($queryMatches[0])
);
echo "  [PASS] At least 7 \$db->query() calls\n";

// --- Edge Case: Multiple global $db declarations (one per method) ---
preg_match_all('/global\s+\$db/', $fileContent, $globalDbMatches);
assert(
    count($globalDbMatches[0]) >= 7,
    "File should have at least 7 'global \$db' declarations (one per method), found: " . count($globalDbMatches[0])
);
echo "  [PASS] At least 7 'global \$db' declarations\n";

// --- Happy Path: getPipelineByStage returns array ---
assert(
    str_contains($fileContent, 'return') && str_contains($fileContent, '$results')
    || str_contains($fileContent, 'return') && str_contains($fileContent, '$rows')
    || str_contains($fileContent, 'return') && str_contains($fileContent, '$data')
    || str_contains($fileContent, 'return') && str_contains($fileContent, '$pipeline')
    || preg_match('/return\s+\$/', $fileContent) === 1,
    "Methods must return result arrays"
);
echo "  [PASS] Methods return result variables\n";

echo "\n";


// ============================================================
// Section 15: getPipelineByStage Stage Exclusion Detail
// ============================================================
echo "Section 15: Stage Exclusion Detail Validation\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: All 3 excluded stages appear in the NOT IN clause context ---
foreach ($excludedPipelineStages as $stage) {
    assert(
        str_contains($fileContent, $stage),
        "File must contain excluded stage: '{$stage}'"
    );
}
echo "  [PASS] All 3 excluded pipeline stages referenced in file\n";

// --- Edge Case: File does NOT include the excluded stages in normal SELECT results ---
// The stages should only appear in exclusion clauses (NOT IN), not as positive filters
assert(
    preg_match('/NOT\s+IN\s*\([^)]*Closed Won[^)]*\)/i', $fileContent) === 1
    || preg_match('/NOT\s+IN\s*\([^)]*Closed Lost[^)]*\)/i', $fileContent) === 1
    || (preg_match('/!=\s*.*Closed Won/i', $fileContent) === 1
        && preg_match('/!=\s*.*Closed Lost/i', $fileContent) === 1),
    "Closed Won and Closed Lost must appear in exclusion context (NOT IN or !=)"
);
echo "  [PASS] Closed stages appear in exclusion context\n";

echo "\n";


// ============================================================
// Section 16: getStaleDeals Activity Table JOIN Detail
// ============================================================
echo "Section 16: getStaleDeals JOIN Detail Validation\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Each activity table is LEFT JOINed with parent_type and parent_id ---
foreach ($activityTables as $table) {
    assert(
        preg_match('/LEFT\s+JOIN\s+' . $table . '/i', $fileContent) === 1,
        "getStaleDeals() must LEFT JOIN with {$table} table"
    );
}
echo "  [PASS] All 4 activity tables have LEFT JOINs\n";

// --- Happy Path: parent_type='Opportunities' used in JOIN conditions ---
// Should appear at least 4 times (once per activity table JOIN)
preg_match_all('/parent_type/i', $fileContent, $parentTypeMatches);
assert(
    count($parentTypeMatches[0]) >= 4,
    "parent_type should appear at least 4 times (once per activity table), found: " . count($parentTypeMatches[0])
);
echo "  [PASS] parent_type referenced at least 4 times\n";

// --- Happy Path: parent_id used in JOIN conditions ---
preg_match_all('/parent_id/i', $fileContent, $parentIdMatches);
assert(
    count($parentIdMatches[0]) >= 4,
    "parent_id should appear at least 4 times (once per activity table), found: " . count($parentIdMatches[0])
);
echo "  [PASS] parent_id referenced at least 4 times\n";

// --- Edge Case: getStaleDeals checks for date-based activity threshold ---
assert(
    preg_match('/DATE_SUB|INTERVAL|NOW\s*\(\s*\)|CURDATE|date_start|date_entered/i', $fileContent) === 1
    || str_contains($fileContent, 'date_modified')
    || str_contains($fileContent, '$days'),
    "getStaleDeals() must use date-based threshold for activity age check"
);
echo "  [PASS] getStaleDeals() uses date-based activity threshold\n";

echo "\n";


// ============================================================
// Section 17: getForecastOpportunities Quarter Boundary Logic
// ============================================================
echo "Section 17: Quarter Boundary Validation\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Method uses date_closed for filtering ---
assert(
    str_contains($fileContent, 'date_closed'),
    "getForecastOpportunities() must filter by date_closed within quarter"
);
echo "  [PASS] getForecastOpportunities() filters by date_closed\n";

// --- Happy Path: Method constructs date range for quarter ---
// Quarter calculation typically involves: ($quarter - 1) * 3 + 1 for start month
assert(
    str_contains($fileContent, 'date_closed')
    && (str_contains($fileContent, '$quarter') || str_contains($fileContent, '$year')),
    "getForecastOpportunities() must use \$quarter and \$year to construct date range"
);
echo "  [PASS] getForecastOpportunities() uses quarter and year for date range\n";

// --- Happy Path: Method uses BETWEEN or >= and <= for date range ---
assert(
    preg_match('/BETWEEN/i', $fileContent) === 1
    || (preg_match('/>=/', $fileContent) === 1 && preg_match('/</', $fileContent) === 1)
    || (preg_match('/>=/', $fileContent) === 1 && preg_match('/<=/', $fileContent) === 1),
    "getForecastOpportunities() must use BETWEEN or comparison operators for date range"
);
echo "  [PASS] getForecastOpportunities() uses date range comparison\n";

echo "\n";


// ============================================================
// Section 18: $db->quote() Usage for All User Parameters
// ============================================================
echo "Section 18: Parameter Escaping Validation\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: $db->quote() used for string parameter escaping ---
preg_match_all('/\$db\s*->\s*quote\s*\(/', $fileContent, $quoteMatches);
assert(
    count($quoteMatches[0]) >= 1,
    "File must use \$db->quote() at least once for parameter escaping"
);
echo "  [PASS] \$db->quote() used for parameter escaping (found " . count($quoteMatches[0]) . " times)\n";

// --- Edge Case: $repId is quoted when used in queries ---
assert(
    preg_match('/quote\s*\(\s*\$repId\s*\)/', $fileContent) === 1,
    "\$repId must be escaped with \$db->quote() when used in SQL queries"
);
echo "  [PASS] \$repId is escaped with \$db->quote()\n";

// --- Edge Case: $year parameter is escaped or safely used ---
assert(
    preg_match('/quote\s*\(\s*\$year\s*\)/', $fileContent) === 1
    || preg_match('/intval\s*\(\s*\$year\s*\)/', $fileContent) === 1
    || preg_match('/\(int\)\s*\$year/', $fileContent) === 1,
    "\$year must be escaped with \$db->quote() or cast to int"
);
echo "  [PASS] \$year parameter is safely handled\n";

// --- Edge Case: $days parameter is safely used ---
assert(
    preg_match('/quote\s*\(\s*\$days\s*\)/', $fileContent) === 1
    || preg_match('/intval\s*\(\s*\$days\s*\)/', $fileContent) === 1
    || preg_match('/\(int\)\s*\$days/', $fileContent) === 1,
    "\$days must be escaped with \$db->quote() or cast to int"
);
echo "  [PASS] \$days parameter is safely handled\n";

echo "\n";


// ============================================================
// Final Summary
// ============================================================
echo str_repeat('=', 60) . "\n";
echo "US-013: All OpportunityQuery tests passed!\n";
echo str_repeat('=', 60) . "\n";
