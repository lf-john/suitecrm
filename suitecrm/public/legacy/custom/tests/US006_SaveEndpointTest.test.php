<?php
/**
 * US-006: Save Endpoint Structural Tests
 *
 * Tests that custom/modules/LF_WeeklyPlan/views/view.save_json.php exists
 * and follows the correct SugarView AJAX endpoint pattern:
 *   - Extends SugarView
 *   - show_header=false, show_footer=false
 *   - Reads JSON from php://input
 *   - Uses $db->query() and $db->quote() for SQL operations
 *   - Returns JSON response with success/failure
 *   - Creates/updates lf_plan_op_items and lf_plan_prospect_items
 *   - Has sugarEntry guard and AllowDynamicProperties attribute
 *
 * These tests MUST FAIL until the implementation is created.
 */

// CLI-only guard
if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

ini_set('assert.exception', '1');
ini_set('zend.assertions', '1');

// ============================================================
// Configuration
// ============================================================

$customDir = dirname(__DIR__);

$saveFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_WeeklyPlan'
    . DIRECTORY_SEPARATOR . 'views'
    . DIRECTORY_SEPARATOR . 'view.save_json.php';


// ============================================================
// Test Harness
// ============================================================

$passCount = 0;
$failCount = 0;
$failures = [];

function test_assert(bool $condition, string $message): void
{
    global $passCount, $failCount, $failures;
    if ($condition) {
        $passCount++;
        echo "  [PASS] {$message}\n";
    } else {
        $failCount++;
        $failures[] = $message;
        echo "  [FAIL] {$message}\n";
    }
}


// ============================================================
// Section 1: File Existence
// ============================================================
echo "Section 1: Save Endpoint File Exists\n";

test_assert(
    file_exists($saveFile),
    "view.save_json.php must exist at custom/modules/LF_WeeklyPlan/views/view.save_json.php"
);

// If file doesn't exist, abort remaining tests
if (!file_exists($saveFile)) {
    echo "\n[ABORT] Cannot continue tests without the save endpoint file.\n";
    echo "\n" . str_repeat('=', 60) . "\n";
    echo "SUMMARY\n";
    echo str_repeat('=', 60) . "\n";
    echo "Total: " . ($passCount + $failCount) . "\n";
    echo "Passed: " . $passCount . "\n";
    echo "Failed: " . $failCount . "\n";
    if (count($failures) > 0) {
        echo "\nFailed tests:\n";
        foreach ($failures as $f) {
            echo "  - {$f}\n";
        }
    }
    echo str_repeat('=', 60) . "\n";
    exit($failCount > 0 ? 1 : 0);
}

$content = file_get_contents($saveFile);
assert($content !== false, "Should be able to read the save endpoint file");


// ============================================================
// Section 2: SugarEntry Guard
// ============================================================
echo "\nSection 2: SugarEntry Guard\n";

test_assert(
    str_contains($content, 'sugarEntry'),
    "Save endpoint must have sugarEntry guard"
);

test_assert(
    str_contains($content, "die('Not A Valid Entry Point')") || str_contains($content, 'die("Not A Valid Entry Point")'),
    "Save endpoint must die with 'Not A Valid Entry Point' message"
);


// ============================================================
// Section 3: Class Definition
// ============================================================
echo "\nSection 3: Class Definition\n";

test_assert(
    str_contains($content, '#[\\AllowDynamicProperties]') || str_contains($content, '#[\AllowDynamicProperties]'),
    "Save endpoint class must have #[\\AllowDynamicProperties] attribute"
);

test_assert(
    str_contains($content, 'class LF_WeeklyPlanViewSave_json'),
    "Save endpoint must define class LF_WeeklyPlanViewSave_json"
);

test_assert(
    str_contains($content, 'extends SugarView'),
    "Save endpoint class must extend SugarView"
);

test_assert(
    str_contains($content, "require_once('include/MVC/View/SugarView.php')") ||
    str_contains($content, 'require_once("include/MVC/View/SugarView.php")') ||
    str_contains($content, "require_once 'include/MVC/View/SugarView.php'"),
    "Save endpoint must require SugarView.php"
);


// ============================================================
// Section 4: Header/Footer Disabled
// ============================================================
echo "\nSection 4: Header/Footer Disabled\n";

test_assert(
    str_contains($content, 'show_header') && str_contains($content, 'false'),
    "Save endpoint must set show_header to false"
);

test_assert(
    str_contains($content, 'show_footer') && str_contains($content, 'false'),
    "Save endpoint must set show_footer to false"
);


// ============================================================
// Section 5: JSON Input Handling
// ============================================================
echo "\nSection 5: JSON Input Handling\n";

test_assert(
    str_contains($content, 'php://input'),
    "Save endpoint must read from php://input"
);

test_assert(
    str_contains($content, 'file_get_contents'),
    "Save endpoint must use file_get_contents() to read input"
);

test_assert(
    str_contains($content, 'json_decode'),
    "Save endpoint must use json_decode() to parse JSON input"
);


// ============================================================
// Section 6: JSON Response
// ============================================================
echo "\nSection 6: JSON Response\n";

test_assert(
    str_contains($content, 'json_encode'),
    "Save endpoint must use json_encode() for JSON response"
);

test_assert(
    str_contains($content, 'Content-Type') && str_contains($content, 'application/json'),
    "Save endpoint must set Content-Type: application/json header"
);

test_assert(
    str_contains($content, "'success'") || str_contains($content, '"success"'),
    "Save endpoint response must include 'success' field"
);


// ============================================================
// Section 7: Database Operations
// ============================================================
echo "\nSection 7: Database Operations\n";

test_assert(
    str_contains($content, '$db->query(') || str_contains($content, '$db->query ('),
    "Save endpoint must use \$db->query() for database operations"
);

test_assert(
    str_contains($content, '$db->quote(') || str_contains($content, '$db->quoted('),
    "Save endpoint must use \$db->quote() or \$db->quoted() for SQL escaping"
);


// ============================================================
// Section 8: Plan Op Items Save
// ============================================================
echo "\nSection 8: Plan Op Items Save\n";

test_assert(
    str_contains($content, 'lf_plan_op_items'),
    "Save endpoint must reference lf_plan_op_items table for opportunity-based items"
);

test_assert(
    str_contains($content, 'opportunity_id'),
    "Save endpoint must handle opportunity_id field for op items"
);

test_assert(
    str_contains($content, 'projected_stage'),
    "Save endpoint must handle projected_stage field for op items"
);

test_assert(
    str_contains($content, 'item_type'),
    "Save endpoint must handle item_type field to distinguish pipeline/developing items"
);

test_assert(
    str_contains($content, 'planned_day'),
    "Save endpoint must handle planned_day field for op items"
);

test_assert(
    str_contains($content, 'plan_description'),
    "Save endpoint must handle plan_description field for op items"
);


// ============================================================
// Section 9: Plan Prospect Items Save
// ============================================================
echo "\nSection 9: Plan Prospect Items Save\n";

test_assert(
    str_contains($content, 'lf_plan_prospect_items'),
    "Save endpoint must reference lf_plan_prospect_items table for prospecting items"
);

test_assert(
    str_contains($content, 'source_type'),
    "Save endpoint must handle source_type field for prospect items"
);

test_assert(
    str_contains($content, 'expected_value'),
    "Save endpoint must handle expected_value field for prospect items"
);


// ============================================================
// Section 10: Create/Update Logic (Upsert)
// ============================================================
echo "\nSection 10: Create/Update Logic\n";

// Must handle both INSERT and UPDATE (or use create_guid for new records)
test_assert(
    (str_contains($content, 'INSERT') || str_contains($content, 'insert')) &&
    (str_contains($content, 'UPDATE') || str_contains($content, 'update')),
    "Save endpoint must handle both INSERT (new) and UPDATE (existing) operations"
);

test_assert(
    str_contains($content, 'create_guid') || str_contains($content, 'create_guid()'),
    "Save endpoint must use create_guid() for generating new record IDs"
);

test_assert(
    str_contains($content, 'lf_weekly_plan_id'),
    "Save endpoint must associate items with the weekly plan via lf_weekly_plan_id"
);


// ============================================================
// Section 11: Submit/Updates Complete Support
// ============================================================
echo "\nSection 11: Submit/Updates Complete Support\n";

test_assert(
    str_contains($content, "'submitted'") || str_contains($content, '"submitted"'),
    "Save endpoint must handle 'submitted' status value"
);

test_assert(
    str_contains($content, 'submitted_date'),
    "Save endpoint must set submitted_date when plan is submitted"
);

test_assert(
    str_contains($content, 'lf_weekly_plan') && (
        str_contains($content, 'status') ||
        str_contains($content, 'UPDATE lf_weekly_plan')
    ),
    "Save endpoint must update lf_weekly_plan status when submitting"
);


// ============================================================
// Section 12: Display Method
// ============================================================
echo "\nSection 12: Display Method\n";

test_assert(
    preg_match('/function\s+display\s*\(/', $content) === 1,
    "Save endpoint must define a display() method"
);

test_assert(
    str_contains($content, '$current_user') || str_contains($content, 'global $current_user'),
    "Save endpoint must access \$current_user for authorization context"
);


// ============================================================
// Section 13: Security - Deleted Flag
// ============================================================
echo "\nSection 13: Security - Deleted Flag\n";

test_assert(
    str_contains($content, 'deleted'),
    "Save endpoint queries must include deleted flag handling"
);


// ============================================================
// Summary
// ============================================================
echo "\n" . str_repeat('=', 60) . "\n";
echo "SUMMARY\n";
echo str_repeat('=', 60) . "\n";
echo "Total: " . ($passCount + $failCount) . "\n";
echo "Passed: " . $passCount . "\n";
echo "Failed: " . $failCount . "\n";

if (count($failures) > 0) {
    echo "\nFailed tests:\n";
    foreach ($failures as $f) {
        echo "  - {$f}\n";
    }
}

echo str_repeat('=', 60) . "\n";

exit($failCount > 0 ? 1 : 0);
