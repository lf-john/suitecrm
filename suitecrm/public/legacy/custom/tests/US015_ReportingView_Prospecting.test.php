<?php
/**
 * US-015: Reporting View Prospecting Section Tests (TDD-RED)
 *
 * Tests for the Prospecting Results section in LF_WeeklyReportViewReporting.
 *
 * Target: custom/modules/LF_WeeklyReport/views/view.reporting.php
 *
 * This test file verifies:
 * - Loading of lf_plan_prospect_items
 * - Display of Prospecting Results table/section
 * - Columns: Source Type, Planned Day, Expected Value, Description, Status
 * - Convert button logic (only for 'planned')
 * - Inline form for conversion (Account, Opp Name, Amount)
 * - "No Opportunity Found" checkbox and notes field
 * - CSRF token inclusion
 *
 * These tests MUST FAIL until the implementation is updated.
 */

// CLI-only guard
if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

// Enable assert() with exceptions
ini_set('assert.exception', '1');
ini_set('zend.assertions', '1');

// Configuration
$customDir = dirname(__DIR__);
$viewFile = $customDir . '/modules/LF_WeeklyReport/views/view.reporting.php';

echo "US-015: Reporting View Prospecting Section Tests
";
echo str_repeat('=', 60) . "

";

// Ensure file exists (it should, from US-013)
assert(file_exists($viewFile), "View file must exist: $viewFile");

$fileContent = file_get_contents($viewFile);
assert($fileContent !== false, "Should read view file");

// ============================================================
// Section 1: Data Loading
// ============================================================
echo "Section 1: Data Loading
";

// --- Happy Path: Loads prospect items ---
assert(
    str_contains($fileContent, 'get_linked_beans') && (str_contains($fileContent, 'lf_plan_prospect_items') || str_contains($fileContent, 'LF_PlanProspectItem')),
    "View must load linked 'lf_plan_prospect_items'"
);
echo "  [PASS] Loads prospect items
";


// ============================================================
// Section 2: Prospecting Results Section UI
// ============================================================
echo "
Section 2: Prospecting Results UI
";

// --- Happy Path: "Prospecting Results" Header ---
assert(
    str_contains($fileContent, 'Prospecting Results') || str_contains($fileContent, 'LBL_PROSPECTING_RESULTS'),
    "View must contain a header for 'Prospecting Results'"
);
echo "  [PASS] Has Prospecting Results header
";

// --- Happy Path: Column Headers ---
$requiredHeaders = ['Source Type', 'Day', 'Expected Value', 'Description', 'Status'];
foreach ($requiredHeaders as $header) {
    // Checking for either literal string or a label variable that implies it
    // Using loose regex to catch simple occurrences
    assert(
        stripos($fileContent, $header) !== false || preg_match("/LBL_" . strtoupper(str_replace(' ', '_', $header)) . "/", $fileContent),
        "View must display column header: $header"
    );
}
echo "  [PASS] Has required column headers
";


// ============================================================
// Section 3: Row Content & Logic
// ============================================================
echo "
Section 3: Row Content & Logic
";

// --- Happy Path: Iterates prospect items ---
assert(
    str_contains($fileContent, 'foreach') && str_contains($fileContent, 'prospect'),
    "View must iterate over prospect items"
);
echo "  [PASS] Iterates prospect items
";

// --- Happy Path: Display fields ---
$requiredFields = ['source_type', 'planned_day', 'expected_value', 'plan_description', 'status'];
foreach ($requiredFields as $field) {
    assert(
        str_contains($fileContent, $field),
        "View must access field: $field"
    );
}
echo "  [PASS] Accesses required bean fields
";

// --- Happy Path: Convert Button Condition ---
// Should check if status == 'planned'
assert(
    preg_match('/if\s*\(.*[\'"]planned[\'"].*\)/', $fileContent) || preg_match('/==\s*[\'"]planned[\'"]/', $fileContent),
    "View must check if status is 'planned' before showing Convert button"
);
echo "  [PASS] Checks status='planned'
";

// --- Happy Path: Convert Button Existence ---
assert(
    stripos($fileContent, 'Convert') !== false || str_contains($fileContent, 'LBL_CONVERT'),
    "View must have a 'Convert' button/link"
);
echo "  [PASS] Has Convert button
";


// ============================================================
// Section 4: Conversion Form (Inline or Expanded)
// ============================================================
echo "
Section 4: Conversion Form
";

// --- Happy Path: Account Name Input ---
assert(
    str_contains($fileContent, '<input') && (stripos($fileContent, 'Account') !== false || str_contains($fileContent, 'account_name')),
    "View must have input for Account Name"
);
echo "  [PASS] Has Account Name input
";

// --- Happy Path: Opportunity Name Input ---
assert(
    str_contains($fileContent, '<input') && (stripos($fileContent, 'Opportunity') !== false || str_contains($fileContent, 'opportunity_name')),
    "View must have input for Opportunity Name"
);
echo "  [PASS] Has Opportunity Name input
";

// --- Happy Path: Amount Input (Pre-filled) ---
assert(
    str_contains($fileContent, '<input') && (str_contains($fileContent, 'amount') || str_contains($fileContent, 'expected_value')),
    "View must have input for Amount (likely pre-filled with expected_value)"
);
echo "  [PASS] Has Amount input
";


// ============================================================
// Section 5: No Opportunity Logic
// ============================================================
echo "
Section 5: No Opportunity Logic
";

// --- Happy Path: No Opportunity Checkbox ---
assert(
    str_contains($fileContent, 'checkbox') && (stripos($fileContent, 'No Opportunity') !== false || str_contains($fileContent, 'no_opportunity')),
    "View must have 'No Opportunity Found' checkbox"
);
echo "  [PASS] Has No Opportunity checkbox
";

// --- Happy Path: Prospecting Notes Textarea ---
assert(
    str_contains($fileContent, '<textarea') && (str_contains($fileContent, 'notes') || str_contains($fileContent, 'prospecting_notes')),
    "View must have textarea for prospecting notes"
);
echo "  [PASS] Has Prospecting Notes textarea
";


// ============================================================
// Section 6: Security (CSRF)
// ============================================================
echo "
Section 6: Security (CSRF)
";

// --- Happy Path: Include CSRF token in JS ---
assert(
    str_contains($fileContent, 'SUGAR.csrf.form_token') || str_contains($fileContent, 'csrf_token'),
    "View must include SUGAR.csrf.form_token for AJAX calls"
);
echo "  [PASS] Includes CSRF token
";


echo "
==============================
";
echo "US-015: Reporting View Prospecting Section tests passed (Validation Logic)!
";
echo "==============================
";
