<?php
/**
 * US-005: Planning View - JS Interop Integration Tests
 *
 * Tests that view.planning.php renders the correct HTML structure (IDs, classes,
 * data attributes) that planning.js depends on for event delegation, calculation,
 * and dynamic row management.
 *
 * The planning.js relies on specific DOM selectors:
 *   - #lf-planning-container (wrapper for event delegation)
 *   - #pipeline-table (existing pipeline table)
 *   - #developing-pipeline-table (developing pipeline table)
 *   - #prospecting-table (prospecting table)
 *   - .amount, .current-stage, .projected-stage-select, .category-select (existing pipeline cells)
 *   - .dev-amount, .dev-projected-stage-select (developing pipeline cells)
 *   - .prospect-source, .prospect-day, .prospect-amount (prospecting cells)
 *   - .pipeline-progression (calculated progression cells)
 *   - #total-closing, #total-at-risk, #total-progression, #total-new-pipeline (totals)
 *   - #add-prospect-row, .remove-prospect-row (buttons)
 *   - data-amount, data-stage attributes on cells
 *
 * These tests MUST FAIL until the view is updated to include all JS interop elements.
 */

// CLI-only guard - prevent web execution
if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

// Enable assert() with exceptions
ini_set('assert.exception', '1');
ini_set('zend.assertions', '1');

// ============================================================
// Test Harness (try/catch pattern to show all failures)
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

// Calculate positions for section extraction
$existingPipelinePos = strpos($viewContent, 'Existing Pipeline');
$devPipelinePos = strpos($viewContent, 'developing-pipeline-table');
$prospectingPos = strpos($viewContent, 'prospecting-table');

// Extract existing pipeline section (between Existing Pipeline heading and developing-pipeline-table)
$existingSection = '';
if ($existingPipelinePos !== false && $devPipelinePos !== false) {
    $existingSection = substr($viewContent, $existingPipelinePos, $devPipelinePos - $existingPipelinePos);
}


// ============================================================
// Section 1: Container Wrapper for JS Event Delegation
// ============================================================
echo "\nSection 1: Container Wrapper for JS Event Delegation\n";

// planning.js uses: document.getElementById('lf-planning-container')
// All three sections must be wrapped in a container with this ID
test_assert(
    str_contains($viewContent, 'id="lf-planning-container"') || str_contains($viewContent, "id='lf-planning-container'"),
    "View must have a wrapper div with id='lf-planning-container' for JS event delegation"
);

// The container must wrap all sections (appear before Existing Pipeline)
if ($existingPipelinePos !== false) {
    $containerPos = strpos($viewContent, 'lf-planning-container');
    test_assert(
        $containerPos !== false && $containerPos < $existingPipelinePos,
        "Container wrapper must appear BEFORE Existing Pipeline heading"
    );
} else {
    test_assert(false, "Container wrapper must appear BEFORE Existing Pipeline heading (Existing Pipeline not found)");
}


// ============================================================
// Section 2: Existing Pipeline Table ID
// ============================================================
echo "\nSection 2: Existing Pipeline Table ID\n";

// planning.js uses: container.querySelectorAll('#pipeline-table tbody tr')
test_assert(
    str_contains($viewContent, 'id="pipeline-table"') || str_contains($viewContent, "id='pipeline-table'"),
    "Existing Pipeline table must have id='pipeline-table' for JS selector"
);

// Verify the pipeline-table ID appears BEFORE the developing-pipeline-table ID
$pipelineTableIdPos = strpos($viewContent, 'pipeline-table');
$devPipelineTableIdPos = strpos($viewContent, 'developing-pipeline-table');
test_assert(
    $pipelineTableIdPos !== false && $devPipelineTableIdPos !== false && $pipelineTableIdPos < $devPipelineTableIdPos,
    "pipeline-table ID must appear BEFORE developing-pipeline-table ID"
);


// ============================================================
// Section 3: Existing Pipeline - CSS Classes for JS Selectors
// ============================================================
echo "\nSection 3: Existing Pipeline - CSS Classes for JS\n";

// planning.js uses: row.querySelector('.amount') with data-amount attribute
test_assert(
    str_contains($existingSection, 'class="amount"') || str_contains($existingSection, "class='amount'")
    || preg_match('/class\s*=\s*["\'][^"\']*\bamount\b/', $existingSection) === 1,
    "Existing Pipeline amount cells must have class='amount' for JS selector"
);

// data-amount attribute on amount cells
test_assert(
    str_contains($existingSection, 'data-amount'),
    "Existing Pipeline amount cells must have data-amount attribute for JS calculation"
);

// planning.js uses: row.querySelector('.current-stage') with data-stage attribute
test_assert(
    str_contains($existingSection, 'class="current-stage"') || str_contains($existingSection, "class='current-stage'")
    || preg_match('/class\s*=\s*["\'][^"\']*\bcurrent-stage\b/', $existingSection) === 1,
    "Existing Pipeline stage cells must have class='current-stage' for JS selector"
);

// data-stage attribute on current stage cells
test_assert(
    str_contains($existingSection, 'data-stage'),
    "Existing Pipeline stage cells must have data-stage attribute for JS calculation"
);

// planning.js uses: row.querySelector('.projected-stage-select')
test_assert(
    str_contains($existingSection, 'projected-stage-select'),
    "Existing Pipeline projected stage selects must have class 'projected-stage-select'"
);

// planning.js uses: row.querySelector('.category-select')
test_assert(
    str_contains($existingSection, 'category-select'),
    "Existing Pipeline category selects must have class 'category-select'"
);


// ============================================================
// Section 4: Pipeline Progression Cells
// ============================================================
echo "\nSection 4: Pipeline Progression Cells\n";

// planning.js writes calculated progression to: row.querySelector('.pipeline-progression')
// Each existing pipeline row must have a progression cell
test_assert(
    str_contains($existingSection, 'pipeline-progression'),
    "Existing Pipeline rows must have .pipeline-progression cells for JS calculation output"
);


// ============================================================
// Section 5: Totals Row Elements
// ============================================================
echo "\nSection 5: Totals Row Elements\n";

// planning.js uses: document.getElementById('total-closing')
test_assert(
    str_contains($viewContent, 'id="total-closing"') || str_contains($viewContent, "id='total-closing'"),
    "View must have element with id='total-closing' for JS totals display"
);

// planning.js uses: document.getElementById('total-at-risk')
test_assert(
    str_contains($viewContent, 'id="total-at-risk"') || str_contains($viewContent, "id='total-at-risk'"),
    "View must have element with id='total-at-risk' for JS totals display"
);

// planning.js uses: document.getElementById('total-progression')
test_assert(
    str_contains($viewContent, 'id="total-progression"') || str_contains($viewContent, "id='total-progression'"),
    "View must have element with id='total-progression' for JS totals display"
);

// planning.js uses: document.getElementById('total-new-pipeline')
test_assert(
    str_contains($viewContent, 'id="total-new-pipeline"') || str_contains($viewContent, "id='total-new-pipeline'"),
    "View must have element with id='total-new-pipeline' for JS totals display"
);


// ============================================================
// Section 6: Developing Pipeline - item_type Distinction
// ============================================================
echo "\nSection 6: Developing Pipeline - item_type Distinction\n";

// lf_plan_op_items are shared between existing and developing pipeline
// They must be distinguished by item_type so pre-fill selects the right items
// The developing pipeline section should either:
// 1. Filter planItems by item_type for developing, OR
// 2. Use a separate query with item_type filter, OR
// 3. Store developing items with a specific item_type value
test_assert(
    (str_contains($viewContent, "item_type") && (
        str_contains($viewContent, "'developing'") ||
        str_contains($viewContent, '"developing"') ||
        str_contains($viewContent, "'analysis'") ||
        str_contains($viewContent, '"analysis"') ||
        preg_match('/item_type\s*=\s*/', $viewContent) === 1 ||
        preg_match('/item_type.*developing/i', $viewContent) === 1
    )),
    "View must filter lf_plan_op_items by item_type to distinguish developing pipeline items from existing"
);


// ============================================================
// Section 7: Existing Pipeline Rows - data-opportunity-id
// ============================================================
echo "\nSection 7: Existing Pipeline Rows - data-opportunity-id\n";

// Existing pipeline rows should have data-opportunity-id for row identification
// consistent with developing pipeline rows
test_assert(
    str_contains($existingSection, 'data-opportunity-id'),
    "Existing Pipeline rows must have data-opportunity-id attribute"
);


// ============================================================
// Section 8: LF_SOURCE_TYPES Window Variable
// ============================================================
echo "\nSection 8: LF_SOURCE_TYPES Window Variable\n";

// planning.js uses: window.LF_SOURCE_TYPES for populating source type dropdown
test_assert(
    str_contains($viewContent, 'LF_SOURCE_TYPES'),
    "View must declare LF_SOURCE_TYPES JavaScript variable for source type dropdown"
);

// Must use json_encode to serialize source types
test_assert(
    str_contains($viewContent, 'LF_SOURCE_TYPES') && str_contains($viewContent, 'json_encode'),
    "LF_SOURCE_TYPES must be populated via json_encode()"
);


// ============================================================
// Section 9: Developing Pipeline Row Class
// ============================================================
echo "\nSection 9: Developing Pipeline Row Class\n";

// planning.js iterates developing pipeline rows via class
test_assert(
    str_contains($viewContent, 'developing-pipeline-row'),
    "Developing Pipeline rows must have class 'developing-pipeline-row'"
);


// ============================================================
// Section 10: Prospecting Table Column Headers
// ============================================================
echo "\nSection 10: Prospecting Table Column Headers\n";

$prospectSection = '';
if ($prospectingPos !== false) {
    $prospectSection = substr($viewContent, $prospectingPos);
}

$prospectColumns = ['Source Type', 'Day', 'Expected Value', 'Description'];
foreach ($prospectColumns as $col) {
    test_assert(
        str_contains($prospectSection, $col),
        "Prospecting table must have column header: '{$col}'"
    );
}


// ============================================================
// Section 11: Closing Container Div
// ============================================================
echo "\nSection 11: Container Structure Completeness\n";

// The container div opening tag must exist and have a corresponding close
// The closing </div> for the container must appear after all sections
$containerOpens = substr_count($viewContent, 'lf-planning-container');
test_assert(
    $containerOpens >= 1,
    "Container 'lf-planning-container' must appear at least once"
);


// ============================================================
// Section 12: Stage Probabilities Passed as LF_STAGE_PROBS
// ============================================================
echo "\nSection 12: Stage Probabilities JS Variable\n";

// planning.js reads from: window.LF_STAGE_PROBS || window.stageProbabilities
// The view should provide LF_STAGE_PROBS to match the primary lookup
test_assert(
    str_contains($viewContent, 'LF_STAGE_PROBS') || str_contains($viewContent, 'stageProbabilities'),
    "View must pass stage probabilities as LF_STAGE_PROBS or stageProbabilities JS variable"
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
