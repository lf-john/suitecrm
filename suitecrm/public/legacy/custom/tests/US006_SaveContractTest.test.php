<?php
/**
 * US-006: Save Endpoint Data Contract Tests
 *
 * Tests that the save endpoint (view.save_json.php) and the planning JS
 * (planning.js) use a consistent data contract for the save payload.
 *
 * Key contract requirements:
 *   - JS must send plan_id in the save payload
 *   - JS must send op_items (combined pipeline + developing) matching endpoint expectations
 *   - JS must send prospect_items matching endpoint expectations
 *   - View must expose plan_id to JavaScript via a data attribute or JS variable
 *   - Save endpoint must handle the status field for 'in_progress' saves
 *   - Save endpoint must validate plan ownership (current_user check)
 *
 * These tests MUST FAIL until the data contract is aligned between JS and PHP.
 */

// CLI-only guard
if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

ini_set('assert.exception', '1');
ini_set('zend.assertions', '1');

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
// Configuration
// ============================================================

$customDir = dirname(__DIR__);

$viewFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_WeeklyPlan'
    . DIRECTORY_SEPARATOR . 'views'
    . DIRECTORY_SEPARATOR . 'view.planning.php';

$saveFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_WeeklyPlan'
    . DIRECTORY_SEPARATOR . 'views'
    . DIRECTORY_SEPARATOR . 'view.save_json.php';

$jsFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_WeeklyPlan'
    . DIRECTORY_SEPARATOR . 'js'
    . DIRECTORY_SEPARATOR . 'planning.js';

// Pre-check: files exist
assert(file_exists($viewFile), "View file must exist");
assert(file_exists($saveFile), "Save endpoint file must exist");
assert(file_exists($jsFile), "Planning JS file must exist");

$viewContent = file_get_contents($viewFile);
$saveContent = file_get_contents($saveFile);
$jsContent = file_get_contents($jsFile);


// ============================================================
// Section 1: View Exposes Plan ID to JavaScript
// ============================================================
echo "Section 1: View Exposes Plan ID to JavaScript\n";

// The view must expose the plan ID so JS can include it in save requests.
// This can be via a JS variable (e.g., LF_PLAN_ID) or a data attribute
// on the container element (e.g., data-plan-id).
test_assert(
    str_contains($viewContent, 'LF_PLAN_ID') ||
    str_contains($viewContent, 'data-plan-id'),
    "View must expose plan ID to JavaScript via LF_PLAN_ID variable or data-plan-id attribute"
);

test_assert(
    preg_match('/\$plan->id/', $viewContent) === 1 &&
    (str_contains($viewContent, 'LF_PLAN_ID') || str_contains($viewContent, 'data-plan-id')),
    "View must pass \$plan->id value to JS for save operations"
);


// ============================================================
// Section 2: JS Sends plan_id in Save Payload
// ============================================================
echo "\nSection 2: JS Sends plan_id in Save Payload\n";

// The JS savePlan() or collectFormData() must include plan_id in the payload
// because the save endpoint requires it and returns error without it.
test_assert(
    str_contains($jsContent, 'plan_id') ||
    str_contains($jsContent, 'planId') ||
    str_contains($jsContent, 'LF_PLAN_ID'),
    "JS must reference plan_id for inclusion in save payload"
);

test_assert(
    preg_match('/data\s*[\.\[]\s*[\'"]?plan_id/', $jsContent) === 1 ||
    preg_match('/plan_id\s*[:=]/', $jsContent) === 1 ||
    preg_match('/planId\s*[:=]/', $jsContent) === 1 ||
    str_contains($jsContent, 'LF_PLAN_ID'),
    "JS must set plan_id field in the save data object"
);


// ============================================================
// Section 3: JS Save Payload Uses op_items Key
// ============================================================
echo "\nSection 3: JS Save Payload Uses op_items Key\n";

// The save endpoint reads $input['op_items'] for opportunity-based items.
// The JS must send this key (not just 'pipeline' / 'developing').
test_assert(
    str_contains($jsContent, 'op_items') || str_contains($jsContent, 'opItems'),
    "JS must use 'op_items' key in save payload to match save endpoint contract"
);


// ============================================================
// Section 4: JS Save Payload Uses prospect_items Key
// ============================================================
echo "\nSection 4: JS Save Payload Uses prospect_items Key\n";

// The save endpoint reads $input['prospect_items'] for prospecting items.
// The JS must send this key (not just 'prospecting').
test_assert(
    str_contains($jsContent, 'prospect_items') || str_contains($jsContent, 'prospectItems'),
    "JS must use 'prospect_items' key in save payload to match save endpoint contract"
);


// ============================================================
// Section 5: Save Endpoint Handles in_progress Status
// ============================================================
echo "\nSection 5: Save Endpoint Handles in_progress Status\n";

// When save is called with status='in_progress', the endpoint should update
// the plan's status to 'in_progress' as well (or at minimum not error).
test_assert(
    str_contains($saveContent, "'in_progress'") || str_contains($saveContent, '"in_progress"'),
    "Save endpoint must handle 'in_progress' status for regular saves"
);


// ============================================================
// Section 6: Save Endpoint Updates Plan date_modified on Every Save
// ============================================================
echo "\nSection 6: Save Endpoint Updates Plan date_modified on Every Save\n";

// When saving (not just submitting), the plan's date_modified should be updated
// to track the last save time. Currently only submitted status updates the plan record.
// A regular save (in_progress) should also update lf_weekly_plan.date_modified.
test_assert(
    preg_match('/in_progress.*UPDATE\s+lf_weekly_plan\s+SET\s+.*date_modified/si', $saveContent) === 1 ||
    (str_contains($saveContent, "'in_progress'") && preg_match('/UPDATE\s+lf_weekly_plan/i', $saveContent) === 1),
    "Save endpoint must update lf_weekly_plan.date_modified on regular in_progress saves (not just submits)"
);


// ============================================================
// Section 7: JS Merges Pipeline and Developing Items for op_items
// ============================================================
echo "\nSection 7: JS Merges Pipeline and Developing into op_items\n";

// The save endpoint expects a single 'op_items' array for both pipeline and
// developing items (distinguished by item_type field). The JS must combine them.
test_assert(
    str_contains($jsContent, 'op_items') &&
    (str_contains($jsContent, 'concat') || str_contains($jsContent, 'push') || str_contains($jsContent, 'spread')),
    "JS must merge pipeline and developing items into a single op_items array for the save endpoint"
);


// ============================================================
// Section 8: View Hidden Input or Data Attribute for Plan ID
// ============================================================
echo "\nSection 8: View Plan ID Accessibility\n";

// The plan ID must be accessible in the DOM for JavaScript to read.
// This can be a hidden input, data attribute on container, or JS variable.
test_assert(
    preg_match('/data-plan-id\s*=\s*["\']/', $viewContent) === 1 ||
    preg_match('/var\s+LF_PLAN_ID\s*=/', $viewContent) === 1 ||
    preg_match('/LF_PLAN_ID\s*=\s*["\']/', $viewContent) === 1 ||
    preg_match('/id\s*=\s*["\']plan-id["\']/', $viewContent) === 1,
    "View must have plan ID accessible via data-plan-id attribute, hidden input, or LF_PLAN_ID JS variable"
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
