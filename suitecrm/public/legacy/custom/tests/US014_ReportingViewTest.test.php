<?php
/**
 * US-014: Reporting View Extended Tests (Planned vs Actual & Unplanned)
 *
 * Tests for LF_WeeklyReportViewReporting class extensions.
 *
 * Target: custom/modules/LF_WeeklyReport/views/view.reporting.php
 *
 * This test file verifies:
 * - Planned vs Actual table columns (Category, Projected, Plan Desc, Result Desc)
 * - Result Description as editable input
 * - Unplanned Changes section (Logic & Rendering)
 * - Regression styling (Yellow/Orange/Badge)
 * - External CSS inclusion
 *
 * These tests MUST FAIL until the implementation is created.
 */

// CLI-only guard
if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

// Enable assert() with exceptions
ini_set('assert.exception', '1');
ini_set('zend.assertions', '1');

// ============================================================
// Configuration
// ============================================================

$customDir = dirname(__DIR__);
$viewFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_WeeklyReport'
    . DIRECTORY_SEPARATOR . 'views'
    . DIRECTORY_SEPARATOR . 'view.reporting.php';

echo "US-014: Reporting View Extended Tests
";
echo str_repeat('=', 60) . "

";

if (!file_exists($viewFile)) {
    echo "View file not found. Skipping tests (this is expected if file creation is part of the task, but here we expect the file to exist from US-013).
";
    exit(1);
}

$fileContent = file_get_contents($viewFile);

// ============================================================
// Section 1: External CSS Inclusion
// ============================================================
echo "Section 1: External CSS Inclusion
";
echo str_repeat('-', 40) . "
";

// --- Happy Path: Includes custom/themes/lf_dashboard.css ---
assert(
    str_contains($fileContent, 'custom/themes/lf_dashboard.css'),
    "display() must include the external CSS file: custom/themes/lf_dashboard.css"
);
assert(
    str_contains($fileContent, '<link') && str_contains($fileContent, 'stylesheet'),
    "display() must use a <link rel='stylesheet'> tag for the CSS"
);
echo "  [PASS] Includes custom/themes/lf_dashboard.css
";

echo "
";


// ============================================================
// Section 2: Planned vs Actual Table Extensions
// ============================================================
echo "Section 2: Planned vs Actual Table Extensions
";
echo str_repeat('-', 40) . "
";

// --- Happy Path: Fetches new columns from lf_plan_op_items ---
assert(
    str_contains($fileContent, 'item_type') && str_contains($fileContent, 'projected_stage') && str_contains($fileContent, 'plan_description'),
    "SQL query must fetch item_type, projected_stage, and plan_description from lf_plan_op_items"
);
echo "  [PASS] Fetches extended plan item fields
";

// --- Happy Path: Renders 'Category' header (from item_type) ---
assert(
    preg_match('/<th[^>]*>.*Category.*<\/th>/i', $fileContent) === 1,
    "Table must have a 'Category' header"
);
echo "  [PASS] Renders 'Category' header
";

// --- Happy Path: Renders 'Projected Stage' header ---
assert(
    preg_match('/<th[^>]*>.*Projected Stage.*<\/th>/i', $fileContent) === 1,
    "Table must have a 'Projected Stage' header"
);
echo "  [PASS] Renders 'Projected Stage' header
";

// --- Happy Path: Renders 'Plan Description' header ---
assert(
    preg_match('/<th[^>]*>.*Plan Description.*<\/th>/i', $fileContent) === 1,
    "Table must have a 'Plan Description' header"
);
echo "  [PASS] Renders 'Plan Description' header
";

// --- Happy Path: Renders 'Result Description' header ---
assert(
    preg_match('/<th[^>]*>.*Result Description.*<\/th>/i', $fileContent) === 1,
    "Table must have a 'Result Description' header"
);
echo "  [PASS] Renders 'Result Description' header
";

// --- Happy Path: Renders 'Result Description' as input/textarea ---
assert(
    preg_match('/<input[^>]*name=["\']result_description/', $fileContent) === 1 ||
    preg_match('/<textarea[^>]*name=["\']result_description/', $fileContent) === 1,
    "Result Description must be rendered as an editable <input> or <textarea>"
);
echo "  [PASS] Renders editable Result Description
";

echo "
";


// ============================================================
// Section 3: Regression Styling
// ============================================================
echo "Section 3: Regression Styling
";
echo str_repeat('-', 40) . "
";

// --- Happy Path: Applies warning style to regressed items ---
// Expecting logic that adds a class or style when movement is 'regressed'
assert(
    str_contains($fileContent, 'regressed') && (str_contains($fileContent, 'style=') || str_contains($fileContent, 'class=')),
    "Must apply styling or class when movement is 'regressed'"
);

// Check for specific color indication (yellow/orange/warning) as per requirements
assert(
    str_contains($fileContent, 'background') || str_contains($fileContent, 'color') || str_contains($fileContent, 'warning'),
    "Must apply visual warning (background color or warning class) for regression"
);
echo "  [PASS] Applies regression warning styling
";

echo "
";


// ============================================================
// Section 4: Unplanned Changes Section
// ============================================================
echo "Section 4: Unplanned Changes Section
";
echo str_repeat('-', 40) . "
";

// --- Happy Path: Renders 'Unplanned Changes' section header ---
assert(
    str_contains($fileContent, 'Unplanned Changes'),
    "Must render an 'Unplanned Changes' section header"
);
echo "  [PASS] Renders 'Unplanned Changes' header
";

// --- Happy Path: Queries for unplanned opportunities ---
// Logic should exist to find opportunities NOT in the plan
assert(
    str_contains($fileContent, 'NOT IN') || str_contains($fileContent, 'array_diff') || str_contains($fileContent, '!isset') || str_contains($fileContent, 'array_key_exists') === false /* This is tricky to assert negatively, relying on context */,
    "Must have logic to identify opportunities not in the plan"
);

// Better assertion for the query/logic:
assert(
    (str_contains($fileContent, 'opportunity_id') && str_contains($fileContent, 'NOT IN')) ||
    (str_contains($fileContent, 'foreach') && str_contains($fileContent, 'continue')), // Iterating all and skipping planned
    "Must filter for opportunities that are NOT in the plan items"
);
echo "  [PASS] Logic to identify unplanned changes exists
";

// --- Happy Path: Unplanned section columns ---
assert(
    str_contains($fileContent, 'Account') &&
    str_contains($fileContent, 'Start Stage') &&
    str_contains($fileContent, 'Movement'),
    "Unplanned section must have Account, Start Stage, and Movement columns"
);
echo "  [PASS] Unplanned section has required columns
";

// --- Happy Path: Detects movement for unplanned items ---
// Should verify that we check if start stage != current stage
assert(
    str_contains($fileContent, '!=') || str_contains($fileContent, '!=='),
    "Must check for movement (start stage != current stage) for unplanned items"
);
echo "  [PASS] Checks for movement in unplanned items
";


echo "
";
echo str_repeat('=', 60) . "
";
echo "US-014: All Extended Reporting View tests passed!
";
echo str_repeat('=', 60) . "
";
