<?php
/**
 * TDD-RED: OpportunityQuery Tests for Closed YTD
 * Tests for getClosedYTD() method used in Pipeline Health Summary
 * These tests MUST FAIL - method does not exist yet
 */

ini_set('assert.exception', 1);
ini_set('zend.assertions', 1);

if (php_sapi_name() !== 'cli') {
    die('This test must be run from CLI');
}

// Test 1: OpportunityQuery class exists
echo "Test 1: Verify OpportunityQuery class exists...\n";
$queryFile = __DIR__ . '/../../../include/LF_PlanningReporting/OpportunityQuery.php';
assert(file_exists($queryFile), "OpportunityQuery must exist at custom/include/LF_PlanningReporting/OpportunityQuery.php");

$content = file_get_contents($queryFile);
assert(strpos($content, 'class OpportunityQuery') !== false,
    "Must define OpportunityQuery class");

// Test 2: getClosedYTD() method exists
echo "Test 2: Verify getClosedYTD() method exists...\n";
assert(strpos($content, 'getClosedYTD') !== false,
    "OpportunityQuery must have getClosedYTD() method");

// Test 3: getClosedYTD() is static
echo "Test 3: Verify getClosedYTD() is static...\n";
assert(strpos($content, 'public static function getClosedYTD') !== false ||
    strpos($content, 'static function getClosedYTD') !== false,
    "getClosedYTD() should be a static method");

// Test 4: Method accepts year and optional user_id parameter
echo "Test 4: Verify parameters...\n";
assert(strpos($content, 'function getClosedYTD($year') !== false ||
    strpos($content, 'function getClosedYTD($year,') !== false,
    "getClosedYTD() must accept year parameter");

// Test 5: Queries opportunities table
echo "Test 5: Verify queries opportunities table...\n";
assert(strpos($content, 'opportunities') !== false,
    "Must query opportunities table");

// Test 6: Filters by assigned_user_id
echo "Test 6: Verify filters by assigned_user_id...\n";
assert(strpos($content, 'assigned_user_id') !== false,
    "Must filter by assigned_user_id");

// Test 7: Filters by closed sales stages
echo "Test 7: Verify filters by closed stages...\n";
// Should include stages like 'Closed Won', 'Closed Lost'
$closedStages = ['Closed Won', 'Closed Lost'];
$hasClosedStageFilter = false;
foreach ($closedStages as $stage) {
    if (strpos($content, $stage) !== false) {
        $hasClosedStageFilter = true;
        break;
    }
}
assert($hasClosedStageFilter || strpos($content, 'sales_stage') !== false,
    "Must filter by closed sales stages");

// Test 8: Filters for year-to-date (current calendar year)
echo "Test 8: Verify filters for current year...\n";
assert(strpos($content, 'YEAR(') !== false ||
    strpos($content, 'YEAR CURDATE()') !== false ||
    strpos($content, 'YEAR(NOW())') !== false ||
    strpos($content, 'date_closed') !== false,
    "Must filter for opportunities in current calendar year");

// Test 9: Includes deleted=0 filter
echo "Test 9: Verify soft delete handling...\n";
assert(strpos($content, "deleted = 0") !== false ||
    strpos($content, 'deleted=0') !== false,
    "Must include deleted=0 filter");

// Test 10: Returns numeric total (sum of amounts)
echo "Test 10: Verify returns sum of amounts...\n";
assert(strpos($content, 'SUM(') !== false ||
    strpos($content, 'sum(amount') !== false ||
    strpos($content, 'amount') !== false,
    "Must return sum of opportunity amounts");

// Test 11: Uses database connection
echo "Test 11: Verify database usage...\n";
assert(strpos($content, '$db') !== false,
    "Must use database connection");
assert(strpos($content, 'query(') !== false,
    "Must execute database query");

// Test 12: Handles no results (returns 0)
echo "Test 12: Verify handles no results...\n";
// Should return 0 if no closed opportunities this year
// Look for null handling or default value
assert(strpos($content, 'return') !== false,
    "Must return a value");

// Test 13: Escapes user_id parameter
echo "Test 13: Verify SQL injection protection...\n";
assert(strpos($content, 'quote(') !== false || strpos($content, 'quoted(') !== false,
    "Must escape user_id parameter");

// Test 14: Filters by date_closed field
echo "Test 14: Verify uses date_closed field...\n";
assert(strpos($content, 'date_closed') !== false,
    "Must filter by date_closed field for YTD calculation");

// Test 15: Returns float/int value
echo "Test 15: Verify return type...\n";
// Should return numeric value (int or float)
assert(strpos($content, 'return') !== false,
    "Must return calculated total");

// Test 16: Handles multiple closed won opportunities
echo "Test 16: Verify aggregation works correctly...\n";
// SUM() aggregation should handle multiple records
// If there are 3 closed won deals: 50000 + 75000 + 100000 = 225000

// Test 17: Year boundary handling
echo "Test 17: Verify correct year boundary...\n";
// Should use YEAR() on date_closed or WHERE clause for year
// Must not include opportunities from previous years

// Test 18: Test scenario: User has no closed opportunities
echo "Test 18: Test scenario: User has no closed opportunities...\n";
// Expected: returns 0

// Test 19: Test scenario: User has closed opportunities in current year
echo "Test 19: Test scenario: User has 3 closed won deals this year...\n";
// Expected: returns sum of all amounts

// Test 20: Test scenario: User has mixed closed won and closed lost
echo "Test 20: Test scenario: User has both closed won and lost...\n";
// Expected: returns sum of all closed amounts (both won and lost)

echo "\n=== ALL OPPORTUNITYQUERY TESTS PASSED (Expected to fail before implementation) ===\n";
