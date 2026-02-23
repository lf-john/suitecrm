<?php
/**
 * US-006: Planning View - Health Summary, Totals Color Coding, Save/Submit Buttons
 *
 * Tests that view.planning.php has been extended to include:
 *
 *   1. Pipeline Health Summary section (below data entry sections):
 *      - Closed YTD (from OpportunityQuery::getClosedYTD())
 *      - Remaining Quota (annual_quota - closed_ytd)
 *      - Pipeline Target (remaining_quota * coverage_multiplier)
 *      - Current Pipeline Total (sum of open pipeline amounts)
 *      - Gap to Target (pipeline_target - current_pipeline, red when pipeline < target)
 *      - Coverage Ratio (current_pipeline / remaining_quota)
 *
 *   2. Totals row compared against configured weekly targets with color coding:
 *      - Green if meeting target, red if below
 *      - Weekly targets passed to JS via window variables
 *
 *   3. Save button that calls AJAX endpoint via fetch()
 *
 *   4. 'Updates Complete' button for plan submission
 *
 * These tests MUST FAIL until the implementation is updated.
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

$jsFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_WeeklyPlan'
    . DIRECTORY_SEPARATOR . 'js'
    . DIRECTORY_SEPARATOR . 'planning.js';


// ============================================================
// Pre-check: View File Exists
// ============================================================
echo "Pre-check: View File Exists\n";

assert(
    file_exists($viewFile),
    "View file should exist at: custom/modules/LF_WeeklyPlan/views/view.planning.php"
);
echo "  [PASS] View file exists\n";

$viewContent = file_get_contents($viewFile);
assert($viewContent !== false, "Should be able to read the view file");


// ============================================================
// Section 1: Pipeline Health Summary Section Existence
// ============================================================
echo "\nSection 1: Pipeline Health Summary Section\n";

test_assert(
    preg_match('/Pipeline\s+Health\s+Summary/i', $viewContent) === 1 ||
    preg_match('/Health\s+Summary/i', $viewContent) === 1 ||
    str_contains($viewContent, 'health-summary') ||
    str_contains($viewContent, 'pipeline-health'),
    "View must contain a Pipeline Health Summary section heading or container"
);


// ============================================================
// Section 2: Health Summary - Closed YTD
// ============================================================
echo "\nSection 2: Health Summary - Closed YTD\n";

test_assert(
    str_contains($viewContent, 'getClosedYTD'),
    "View must call OpportunityQuery::getClosedYTD() for Closed YTD data"
);

test_assert(
    str_contains($viewContent, 'closed_ytd') || str_contains($viewContent, 'closedYtd') ||
    str_contains($viewContent, 'Closed YTD') || str_contains($viewContent, 'closed-ytd'),
    "View must display Closed YTD value in the health summary"
);


// ============================================================
// Section 3: Health Summary - Remaining Quota
// ============================================================
echo "\nSection 3: Health Summary - Remaining Quota\n";

// View must load annual_quota config (from rep_targets or default config)
test_assert(
    str_contains($viewContent, 'annual_quota') || str_contains($viewContent, 'default_annual_quota'),
    "View must reference annual_quota (from rep targets or config default)"
);

test_assert(
    str_contains($viewContent, 'remaining_quota') || str_contains($viewContent, 'remainingQuota') ||
    str_contains($viewContent, 'Remaining Quota') || str_contains($viewContent, 'remaining-quota'),
    "View must display Remaining Quota value (annual_quota - closed_ytd)"
);


// ============================================================
// Section 4: Health Summary - Pipeline Target
// ============================================================
echo "\nSection 4: Health Summary - Pipeline Target\n";

test_assert(
    str_contains($viewContent, 'pipeline_coverage_multiplier') || str_contains($viewContent, 'coverage_multiplier'),
    "View must reference pipeline_coverage_multiplier config for Pipeline Target calculation"
);

test_assert(
    str_contains($viewContent, 'pipeline_target') || str_contains($viewContent, 'pipelineTarget') ||
    str_contains($viewContent, 'Pipeline Target') || str_contains($viewContent, 'pipeline-target'),
    "View must display Pipeline Target value (remaining_quota * coverage_multiplier)"
);


// ============================================================
// Section 5: Health Summary - Current Pipeline Total
// ============================================================
echo "\nSection 5: Health Summary - Current Pipeline Total\n";

test_assert(
    str_contains($viewContent, 'current_pipeline') || str_contains($viewContent, 'currentPipeline') ||
    str_contains($viewContent, 'Current Pipeline') || str_contains($viewContent, 'current-pipeline'),
    "View must display Current Pipeline Total (sum of all open pipeline amounts)"
);


// ============================================================
// Section 6: Health Summary - Gap to Target
// ============================================================
echo "\nSection 6: Health Summary - Gap to Target\n";

test_assert(
    str_contains($viewContent, 'gap_to_target') || str_contains($viewContent, 'gapToTarget') ||
    str_contains($viewContent, 'Gap to Target') || str_contains($viewContent, 'gap-to-target'),
    "View must display Gap to Target value (pipeline_target - current_pipeline)"
);

// Gap to Target must be styled with red accent when pipeline < target
test_assert(
    (str_contains($viewContent, 'gap') || str_contains($viewContent, 'Gap')) &&
    (str_contains($viewContent, 'red') || str_contains($viewContent, 'danger') ||
     str_contains($viewContent, 'negative') || str_contains($viewContent, '#d13438') ||
     str_contains($viewContent, 'lf-gap-negative') || str_contains($viewContent, 'gap-negative')),
    "Gap to Target must have red accent styling when pipeline < target"
);


// ============================================================
// Section 7: Health Summary - Coverage Ratio
// ============================================================
echo "\nSection 7: Health Summary - Coverage Ratio\n";

test_assert(
    str_contains($viewContent, 'coverage_ratio') || str_contains($viewContent, 'coverageRatio') ||
    str_contains($viewContent, 'Coverage Ratio') || str_contains($viewContent, 'coverage-ratio'),
    "View must display Coverage Ratio (current_pipeline / remaining_quota)"
);


// ============================================================
// Section 8: Health Summary Section Positioning
// ============================================================
echo "\nSection 8: Health Summary Section Positioning\n";

// Health summary must appear AFTER the data entry sections (Existing Pipeline, Developing, Prospecting)
$prospectingPos = strpos($viewContent, 'prospecting-table');
$healthSummaryPos = false;
// Search for health summary container
if (preg_match('/(?:health-summary|pipeline-health|Health Summary|Pipeline Health)/i', $viewContent, $matches, PREG_OFFSET_CAPTURE)) {
    $healthSummaryPos = $matches[0][1];
}

test_assert(
    $healthSummaryPos !== false && $prospectingPos !== false && $healthSummaryPos > $prospectingPos,
    "Pipeline Health Summary must appear AFTER the prospecting table section"
);


// ============================================================
// Section 9: Totals Row - Weekly Targets Passed to JS
// ============================================================
echo "\nSection 9: Totals Row - Weekly Targets for Color Coding\n";

// The view must pass configured weekly targets to JS for color-coding logic
test_assert(
    str_contains($viewContent, 'weekly_closed') || str_contains($viewContent, 'default_closed_target') ||
    str_contains($viewContent, 'weeklyClosedTarget') || str_contains($viewContent, 'WEEKLY_TARGETS') ||
    str_contains($viewContent, 'weekly_targets'),
    "View must pass weekly closed target to JS for totals color coding"
);

test_assert(
    str_contains($viewContent, 'weekly_new_pipeline') || str_contains($viewContent, 'default_new_pipeline_target') ||
    str_contains($viewContent, 'weeklyNewPipelineTarget') || str_contains($viewContent, 'WEEKLY_TARGETS'),
    "View must pass weekly new pipeline target to JS for totals color coding"
);

test_assert(
    str_contains($viewContent, 'weekly_progression') || str_contains($viewContent, 'default_progression_target') ||
    str_contains($viewContent, 'weeklyProgressionTarget') || str_contains($viewContent, 'WEEKLY_TARGETS'),
    "View must pass weekly progression target to JS for totals color coding"
);


// ============================================================
// Section 10: Totals Row - Color Coding Elements
// ============================================================
echo "\nSection 10: Totals Row - Color Coding Elements\n";

// Each total box should have a data attribute or class mechanism for JS color coding
test_assert(
    str_contains($viewContent, 'data-target') ||
    str_contains($viewContent, 'data-weekly-target') ||
    str_contains($viewContent, 'LF_WEEKLY_TARGETS') ||
    str_contains($viewContent, 'weeklyTargets'),
    "Total boxes must expose weekly targets via data attributes or JS variable for color coding"
);


// ============================================================
// Section 11: Save Button
// ============================================================
echo "\nSection 11: Save Button\n";

test_assert(
    str_contains($viewContent, 'save') || str_contains($viewContent, 'Save'),
    "View must contain a Save button"
);

// Save button must be a clickable element
test_assert(
    (str_contains($viewContent, 'save-plan') || str_contains($viewContent, 'save_plan') ||
     str_contains($viewContent, 'btn-save') || str_contains($viewContent, 'lf-save')) &&
    str_contains($viewContent, '<button'),
    "View must have a Save button element with identifiable ID/class (e.g., 'save-plan')"
);


// ============================================================
// Section 12: Updates Complete Button
// ============================================================
echo "\nSection 12: Updates Complete Button\n";

test_assert(
    str_contains($viewContent, 'Updates Complete') || str_contains($viewContent, 'updates-complete') ||
    str_contains($viewContent, 'submit-plan') || str_contains($viewContent, 'updates_complete'),
    "View must contain an 'Updates Complete' button"
);

// Must be a button element
test_assert(
    (str_contains($viewContent, 'updates-complete') || str_contains($viewContent, 'submit-plan') ||
     str_contains($viewContent, 'updates_complete')) &&
    (str_contains($viewContent, '<button') || str_contains($viewContent, 'type="button"')),
    "'Updates Complete' must be a clickable button element"
);


// ============================================================
// Section 13: CSRF Token Reference
// ============================================================
echo "\nSection 13: CSRF Token\n";

// View must either output the CSRF token or ensure JS knows about SUGAR.csrf.form_token
test_assert(
    str_contains($viewContent, 'csrf') || str_contains($viewContent, 'CSRF') ||
    str_contains($viewContent, 'form_token') || str_contains($viewContent, 'SUGAR.csrf'),
    "View must reference CSRF token for AJAX save operations"
);


// ============================================================
// Section 14: Rep Targets or Config for Annual Quota
// ============================================================
echo "\nSection 14: Rep Targets / Config Lookup\n";

// View must look up rep-specific targets (lf_rep_targets) or fall back to config defaults
test_assert(
    str_contains($viewContent, 'lf_rep_targets') || str_contains($viewContent, 'LF_RepTargets') ||
    str_contains($viewContent, 'getConfig') && str_contains($viewContent, 'default_annual_quota'),
    "View must look up rep targets or fall back to default_annual_quota config"
);


// ============================================================
// Section 15: Health Summary - Numerical Calculations in PHP
// ============================================================
echo "\nSection 15: Health Summary - Calculations\n";

// Remaining Quota calculation: annual_quota - closed_ytd
// At least one subtraction for remaining quota
test_assert(
    preg_match('/\$\w*quota\w*\s*-\s*\$\w*closed/i', $viewContent) === 1 ||
    preg_match('/\$\w*annual\w*\s*-\s*\$\w*closed/i', $viewContent) === 1 ||
    str_contains($viewContent, '- $closed') ||
    str_contains($viewContent, '- $closedYtd') ||
    (str_contains($viewContent, 'remaining') && str_contains($viewContent, 'closed')),
    "View must calculate Remaining Quota as annual_quota minus closed YTD"
);

// Pipeline Target calculation: remaining_quota * coverage_multiplier
test_assert(
    preg_match('/\$\w*remaining\w*\s*\*\s*\$\w*(coverage|multiplier)/i', $viewContent) === 1 ||
    preg_match('/\$\w*(coverage|multiplier)\w*\s*\*\s*\$\w*remaining/i', $viewContent) === 1 ||
    (str_contains($viewContent, 'pipeline_target') && str_contains($viewContent, 'multiplier')),
    "View must calculate Pipeline Target as remaining_quota * coverage_multiplier"
);


// ============================================================
// Section 16: Health Summary - Uses number_format for Display
// ============================================================
echo "\nSection 16: Health Summary - Formatted Numbers\n";

// Health summary section should format currency values
// Count number_format usage - must increase from US-005 baseline
$numberFormatCount = substr_count($viewContent, 'number_format');
test_assert(
    $numberFormatCount >= 5,
    "View must use number_format() at least 5 times for health summary + pipeline amounts, found: {$numberFormatCount}"
);


// ============================================================
// Section 17: Message Display Container
// ============================================================
echo "\nSection 17: Message Display Container\n";

// View must have a container for displaying save success/error messages without page reload
test_assert(
    str_contains($viewContent, 'save-message') || str_contains($viewContent, 'lf-message') ||
    str_contains($viewContent, 'status-message') || str_contains($viewContent, 'notification'),
    "View must have a message display container for save success/error feedback (e.g., 'save-message')"
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
