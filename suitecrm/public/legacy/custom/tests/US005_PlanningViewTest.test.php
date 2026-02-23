<?php
/**
 * US-005: Planning View - Developing Pipeline & Prospecting Sections Tests
 *
 * Tests that the LF_WeeklyPlan planning view (view.planning.php) has been extended
 * to include two additional sections below the Existing Pipeline table:
 *
 *   1. Developing Pipeline section:
 *      - Queries opportunities at '2-Analysis (1%)' stage via OpportunityQuery::getAnalysisOpportunities()
 *      - Renders HTML table with columns: Account, Opportunity (linked), Amount, Projected Stage, Day, Plan
 *      - Projected Stage dropdown shows stages above 2-Analysis (1%)
 *      - Pre-fills from lf_plan_op_items where item_type relates to developing pipeline items
 *
 *   2. Prospecting section:
 *      - Renders below Developing Pipeline
 *      - Contains 'Add Row' button for dynamic row creation (handled by planning.js)
 *      - Each row: Source Type (dropdown from config), Day (dropdown Mon-Fri),
 *        Expected Value (number input), Description (text input), Remove button
 *      - Pre-fills existing items from lf_plan_prospect_items for this week's plan
 *      - Source types loaded via LF_PRConfig::getConfigJson('prospecting', 'source_types')
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

// Base path: from custom/tests/ up one level to custom/
$customDir = dirname(__DIR__);

$viewFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_WeeklyPlan'
    . DIRECTORY_SEPARATOR . 'views'
    . DIRECTORY_SEPARATOR . 'view.planning.php';

// Required columns for the Developing Pipeline table (6 columns)
$devPipelineColumns = [
    'Account',
    'Opportunity',
    'Amount',
    'Projected Stage',
    'Day',
    'Plan',
];

// Prospecting row field patterns
$prospectingFields = [
    'source_type',
    'planned_day',
    'expected_value',
    'plan_description',
];

// Day dropdown options (5 days)
$dayOptions = [
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
];


// ============================================================
// Section 1: View File Exists (Sanity Check)
// ============================================================
echo "Section 1: View File Exists\n";

assert(
    file_exists($viewFile),
    "View file should exist at: custom/modules/LF_WeeklyPlan/views/view.planning.php"
);
echo "  [PASS] View file exists\n";

$viewContent = file_get_contents($viewFile);
assert($viewContent !== false, "Should be able to read the view file");


// ============================================================
// Section 2: Developing Pipeline Section Heading
// ============================================================
echo "\nSection 2: Developing Pipeline Section Heading\n";

// Must contain a 'Developing Pipeline' heading
assert(
    preg_match('/Developing\s+Pipeline/i', $viewContent) === 1,
    "View must contain 'Developing Pipeline' section heading"
);
echo "  [PASS] 'Developing Pipeline' section heading found\n";

// The Developing Pipeline heading must appear AFTER the Existing Pipeline heading
$existingPipelinePos = strpos($viewContent, 'Existing Pipeline');
$developingPipelinePos = strpos($viewContent, 'Developing Pipeline');
assert(
    $existingPipelinePos !== false && $developingPipelinePos !== false,
    "Both 'Existing Pipeline' and 'Developing Pipeline' headings must exist"
);
assert(
    $developingPipelinePos > $existingPipelinePos,
    "Developing Pipeline section must appear AFTER Existing Pipeline section"
);
echo "  [PASS] Developing Pipeline appears after Existing Pipeline\n";


// ============================================================
// Section 3: Developing Pipeline - Uses getAnalysisOpportunities()
// ============================================================
echo "\nSection 3: Developing Pipeline - Uses getAnalysisOpportunities()\n";

// Must call OpportunityQuery::getAnalysisOpportunities()
assert(
    str_contains($viewContent, 'getAnalysisOpportunities'),
    "View must call OpportunityQuery::getAnalysisOpportunities() for developing pipeline data"
);
echo "  [PASS] View calls getAnalysisOpportunities()\n";

// Must pass current_user->id to getAnalysisOpportunities
assert(
    str_contains($viewContent, 'getAnalysisOpportunities') && str_contains($viewContent, '$current_user->id'),
    "View must pass \$current_user->id to getAnalysisOpportunities()"
);
echo "  [PASS] getAnalysisOpportunities() receives \$current_user->id\n";


// ============================================================
// Section 4: Developing Pipeline Table - 6 Columns
// ============================================================
echo "\nSection 4: Developing Pipeline Table - 6 Columns\n";

// The view must contain a second <table> for the Developing Pipeline section
// Count total tables - must be at least 2 (Existing Pipeline + Developing Pipeline)
$tableCount = preg_match_all('/<table\b/i', $viewContent);
assert(
    $tableCount >= 2,
    "View must contain at least 2 <table> elements (Existing Pipeline + Developing Pipeline), found: {$tableCount}"
);
echo "  [PASS] At least 2 tables found ({$tableCount} total)\n";

// Extract the content AFTER the Developing Pipeline heading to check columns
$devPipelineContent = substr($viewContent, $developingPipelinePos);

// Each required column header must appear in the Developing Pipeline section
foreach ($devPipelineColumns as $col) {
    assert(
        str_contains($devPipelineContent, $col),
        "Developing Pipeline table must include column header: '{$col}'"
    );
    echo "  [PASS] Column '{$col}' found in Developing Pipeline section\n";
}


// ============================================================
// Section 5: Developing Pipeline - Opportunity Link to CRM
// ============================================================
echo "\nSection 5: Developing Pipeline - Opportunity Link to CRM\n";

// Developing Pipeline section must also have Opportunity links to CRM detail view
// The link pattern should appear in the developing pipeline section
assert(
    str_contains($devPipelineContent, 'index.php?module=Opportunities&action=DetailView&record='),
    "Developing Pipeline Opportunity column must contain link to CRM detail view"
);
echo "  [PASS] Developing Pipeline has Opportunity links\n";


// ============================================================
// Section 6: Developing Pipeline - Amount Formatted as Currency
// ============================================================
echo "\nSection 6: Developing Pipeline - Amount Formatted as Currency\n";

// Amount in developing pipeline section must be formatted
assert(
    str_contains($devPipelineContent, 'number_format') || str_contains($devPipelineContent, 'currency_format'),
    "Developing Pipeline Amount column must format amount as currency"
);
echo "  [PASS] Developing Pipeline formats amount as currency\n";


// ============================================================
// Section 7: Developing Pipeline - Projected Stage Dropdown (Stages Above 2-Analysis)
// ============================================================
echo "\nSection 7: Developing Pipeline - Projected Stage Dropdown\n";

// Must have a <select> for projected_stage in the developing pipeline section
assert(
    str_contains($devPipelineContent, '<select') && str_contains($devPipelineContent, 'projected_stage'),
    "Developing Pipeline must render projected_stage as a <select> dropdown"
);
echo "  [PASS] Developing Pipeline has projected_stage dropdown\n";

// The stage dropdown must show stages ABOVE 2-Analysis (1%)
// Since all opps are at 2-Analysis, projected stage should include stages like 3-Confirmation and above
// The view should reference stage_order or stage configuration for building the dropdown
assert(
    str_contains($devPipelineContent, 'stage') && (
        str_contains($devPipelineContent, 'stageOrder') ||
        str_contains($devPipelineContent, 'stage_order') ||
        str_contains($devPipelineContent, 'getConfigJson') ||
        preg_match('/for\s*\(\s*\$i\s*=/', $devPipelineContent) === 1 ||
        str_contains($devPipelineContent, 'foreach')
    ),
    "Developing Pipeline must generate projected stage dropdown from stage configuration"
);
echo "  [PASS] Projected Stage dropdown uses stage configuration\n";


// ============================================================
// Section 8: Developing Pipeline - Day Dropdown (Mon-Fri)
// ============================================================
echo "\nSection 8: Developing Pipeline - Day Dropdown\n";

// Day dropdown in developing pipeline section
assert(
    str_contains($devPipelineContent, 'planned_day') || str_contains($devPipelineContent, 'day'),
    "Developing Pipeline must have a day dropdown field"
);
echo "  [PASS] Developing Pipeline has day field\n";


// ============================================================
// Section 9: Developing Pipeline - Plan Text Input
// ============================================================
echo "\nSection 9: Developing Pipeline - Plan Text Input\n";

assert(
    str_contains($devPipelineContent, 'plan_description') || str_contains($devPipelineContent, 'plan'),
    "Developing Pipeline must have a plan description text input"
);
echo "  [PASS] Developing Pipeline has plan text input\n";


// ============================================================
// Section 10: Developing Pipeline - Pre-fill from lf_plan_op_items
// ============================================================
echo "\nSection 10: Developing Pipeline - Pre-fill Logic\n";

// View must query lf_plan_op_items for developing pipeline pre-fill
// The item_type must be used to distinguish developing pipeline items from existing pipeline items
assert(
    str_contains($viewContent, 'developing') || str_contains($viewContent, 'analysis') ||
    str_contains($viewContent, 'item_type'),
    "View must handle item_type to distinguish developing pipeline items in lf_plan_op_items"
);
echo "  [PASS] View handles item_type for developing pipeline items\n";

// The pre-fill must also use lf_plan_op_items data with the developing pipeline opps
// It should query by lf_weekly_plan_id and use opportunity_id for matching
assert(
    str_contains($viewContent, 'lf_plan_op_items'),
    "View must query lf_plan_op_items for developing pipeline pre-fill data"
);
echo "  [PASS] View queries lf_plan_op_items\n";


// ============================================================
// Section 11: Prospecting Section Heading
// ============================================================
echo "\nSection 11: Prospecting Section Heading\n";

// Must contain a 'Prospecting' section heading
assert(
    preg_match('/Prospecting/i', $viewContent) === 1,
    "View must contain 'Prospecting' section heading"
);
echo "  [PASS] 'Prospecting' section heading found\n";

// Prospecting section must appear AFTER Developing Pipeline
$prospectingPos = false;
// Find 'Prospecting' that's not part of 'prospecting_notes' or other compound words
// Look for it as a heading or standalone reference
if (preg_match('/[\'">]Prospecting[<\'"]/i', $viewContent, $matches, PREG_OFFSET_CAPTURE)) {
    $prospectingPos = $matches[0][1];
}
// Fallback: just check ordering with a broader pattern
if ($prospectingPos === false) {
    // Find last occurrence of 'Prospecting' as a section element
    $tempPos = strpos($viewContent, 'Prospecting');
    if ($tempPos !== false && $tempPos > $developingPipelinePos) {
        $prospectingPos = $tempPos;
    }
}
assert(
    $prospectingPos !== false && $prospectingPos > $developingPipelinePos,
    "Prospecting section must appear AFTER Developing Pipeline section"
);
echo "  [PASS] Prospecting appears after Developing Pipeline\n";


// ============================================================
// Section 12: Prospecting - Add Row Button
// ============================================================
echo "\nSection 12: Prospecting - Add Row Button\n";

// Must have an 'Add Row' button element
assert(
    str_contains($viewContent, 'add-prospect-row') || str_contains($viewContent, 'Add Row'),
    "View must contain an 'Add Row' button for prospecting section"
);
echo "  [PASS] 'Add Row' button found\n";

// The button should be a clickable element (button or input[type=button])
assert(
    str_contains($viewContent, '<button') || str_contains($viewContent, 'type="button"'),
    "Add Row must be a clickable button element"
);
echo "  [PASS] Add Row is a button element\n";


// ============================================================
// Section 13: Prospecting - Source Type Dropdown from Config
// ============================================================
echo "\nSection 13: Prospecting - Source Type Dropdown\n";

// Source type must be loaded from LF_PRConfig::getConfigJson('prospecting', 'source_types')
assert(
    str_contains($viewContent, 'source_types'),
    "View must reference 'source_types' config for prospecting Source Type dropdown"
);
echo "  [PASS] View references source_types config\n";

assert(
    str_contains($viewContent, 'getConfigJson') && str_contains($viewContent, 'prospecting'),
    "View must call LF_PRConfig::getConfigJson('prospecting', ...) for source types"
);
echo "  [PASS] View calls getConfigJson for prospecting source types\n";

// Source Type should be rendered as a dropdown (<select>)
$prospectingContent = substr($viewContent, $prospectingPos);
assert(
    str_contains($prospectingContent, '<select') && str_contains($prospectingContent, 'source_type'),
    "Prospecting Source Type must be a <select> dropdown"
);
echo "  [PASS] Source Type rendered as dropdown\n";


// ============================================================
// Section 14: Prospecting - Day Dropdown (Mon-Fri)
// ============================================================
echo "\nSection 14: Prospecting - Day Dropdown\n";

assert(
    str_contains($prospectingContent, 'planned_day') ||
    (str_contains($prospectingContent, '<select') && str_contains($prospectingContent, 'day')),
    "Prospecting must have a Day dropdown (planned_day)"
);
echo "  [PASS] Prospecting has Day dropdown\n";


// ============================================================
// Section 15: Prospecting - Expected Value Number Input
// ============================================================
echo "\nSection 15: Prospecting - Expected Value Input\n";

// Expected Value must be a number input
assert(
    str_contains($prospectingContent, 'expected_value') || str_contains($prospectingContent, 'prospect_amount'),
    "Prospecting must have an expected_value/prospect_amount field"
);
echo "  [PASS] Prospecting has expected value field\n";

assert(
    str_contains($prospectingContent, 'type="number"') || str_contains($prospectingContent, "type='number'"),
    "Prospecting Expected Value must be a number input (type='number')"
);
echo "  [PASS] Expected Value is a number input\n";


// ============================================================
// Section 16: Prospecting - Description Text Input
// ============================================================
echo "\nSection 16: Prospecting - Description Input\n";

assert(
    str_contains($prospectingContent, 'plan_description') || str_contains($prospectingContent, 'prospect_description'),
    "Prospecting must have a description text input field"
);
echo "  [PASS] Prospecting has description field\n";


// ============================================================
// Section 17: Prospecting - Remove Row Button
// ============================================================
echo "\nSection 17: Prospecting - Remove Row Button\n";

assert(
    str_contains($prospectingContent, 'remove-prospect-row') || str_contains($prospectingContent, 'Remove'),
    "Prospecting must have a Remove Row button on each row"
);
echo "  [PASS] Remove Row button found in prospecting section\n";


// ============================================================
// Section 18: Prospecting - Pre-fill from lf_plan_prospect_items
// ============================================================
echo "\nSection 18: Prospecting - Pre-fill from lf_plan_prospect_items\n";

// View must query lf_plan_prospect_items to pre-fill existing prospecting items
assert(
    str_contains($viewContent, 'lf_plan_prospect_items') || str_contains($viewContent, 'LF_PlanProspectItem'),
    "View must query lf_plan_prospect_items or reference LF_PlanProspectItem for pre-filling prospecting items"
);
echo "  [PASS] View references lf_plan_prospect_items / LF_PlanProspectItem\n";

// Pre-fill must load by weekly plan ID
assert(
    str_contains($viewContent, 'lf_plan_prospect_items') && str_contains($viewContent, 'lf_weekly_plan_id'),
    "View must load prospecting items by lf_weekly_plan_id"
);
echo "  [PASS] Prospecting pre-fill loads by weekly plan ID\n";

// Uses fetchByAssoc for iteration
assert(
    str_contains($viewContent, 'fetchByAssoc'),
    "View must use \$db->fetchByAssoc() for prospecting item iteration"
);
echo "  [PASS] View uses fetchByAssoc() for prospecting data\n";


// ============================================================
// Section 19: Prospecting Table Structure
// ============================================================
echo "\nSection 19: Prospecting Table Structure\n";

// Must have a prospecting table with ID for JS interaction
assert(
    str_contains($viewContent, 'prospecting-table') || str_contains($viewContent, 'prospect-table'),
    "View must have a prospecting table with identifiable ID for JS interaction"
);
echo "  [PASS] Prospecting table has identifiable element\n";

// Prospecting rows must have a class for JS selection
assert(
    str_contains($viewContent, 'prospecting-row') || str_contains($viewContent, 'prospect-row'),
    "Prospecting rows must have a CSS class for JS selection (e.g., 'prospecting-row')"
);
echo "  [PASS] Prospecting rows have CSS class for JS\n";


// ============================================================
// Section 20: Developing Pipeline Table Structure
// ============================================================
echo "\nSection 20: Developing Pipeline Table Structure\n";

// Developing Pipeline table should have ID or class for JS interaction
assert(
    str_contains($viewContent, 'developing-pipeline') || str_contains($viewContent, 'dev-pipeline'),
    "Developing Pipeline table must have an identifiable ID/class (e.g., 'developing-pipeline')"
);
echo "  [PASS] Developing Pipeline table has identifiable element\n";


// ============================================================
// Section 21: Security in New Sections
// ============================================================
echo "\nSection 21: Security in New Sections\n";

// htmlspecialchars usage should increase with new sections
$htmlEscapeCount = substr_count($viewContent, 'htmlspecialchars(');
assert(
    $htmlEscapeCount >= 10,
    "View must use htmlspecialchars() at least 10 times with all three sections, found: {$htmlEscapeCount}"
);
echo "  [PASS] htmlspecialchars() used {$htmlEscapeCount} times (all sections)\n";

// SQL queries for both new sections must use $db->quoted() or $db->quote()
$quoteCount = substr_count($viewContent, '$db->quoted(') + substr_count($viewContent, '$db->quote(');
assert(
    $quoteCount >= 2,
    "View must use \$db->quoted()/\$db->quote() at least 2 times for new section queries, found: {$quoteCount}"
);
echo "  [PASS] SQL escaping used {$quoteCount} times\n";


// ============================================================
// Section 22: Echo HTML Output Count (All 3 Sections)
// ============================================================
echo "\nSection 22: Echo HTML Output Count\n";

$echoCount = preg_match_all('/\becho\s/', $viewContent);
assert(
    $echoCount >= 30,
    "View must have at least 30 echo statements for all 3 sections (header + existing + developing + prospecting), found: {$echoCount}"
);
echo "  [PASS] View has {$echoCount} echo statements for all sections\n";


// ============================================================
// Section 23: All Three Sections Present in Correct Order
// ============================================================
echo "\nSection 23: Section Ordering\n";

$existingPos = strpos($viewContent, 'Existing Pipeline');
$developingPos = strpos($viewContent, 'Developing Pipeline');
// Find prospecting heading (not just any mention of 'prospecting')
$prospectingHeadingPos = $prospectingPos;

assert(
    $existingPos !== false && $developingPos !== false && $prospectingHeadingPos !== false,
    "All three section headings must exist: Existing Pipeline, Developing Pipeline, Prospecting"
);
assert(
    $existingPos < $developingPos && $developingPos < $prospectingHeadingPos,
    "Sections must be ordered: Existing Pipeline < Developing Pipeline < Prospecting"
);
echo "  [PASS] All three sections in correct order\n";


// ============================================================
// Section 24: Table Count (At Least 3)
// ============================================================
echo "\nSection 24: Table Count\n";

$totalTableCount = preg_match_all('/<table\b/i', $viewContent);
assert(
    $totalTableCount >= 3,
    "View must contain at least 3 <table> elements (Existing + Developing + Prospecting), found: {$totalTableCount}"
);
echo "  [PASS] At least 3 tables found ({$totalTableCount})\n";


// ============================================================
// Section 25: Developing Pipeline - Does NOT Have Category Column
// ============================================================
echo "\nSection 25: Developing Pipeline - No Category Column\n";

// The Developing Pipeline table should NOT have a 'Category' or 'Current Stage' column
// (unlike Existing Pipeline which has 8 columns including Category and Current Stage)
// Extract content between Developing Pipeline and Prospecting headings
$devSectionEnd = $prospectingPos;
$devSectionContent = substr($viewContent, $developingPipelinePos, $devSectionEnd - $developingPipelinePos);

// Count <th> in the developing pipeline section - should be 6, not 8
$devThCount = preg_match_all('/<th\b/i', $devSectionContent);
assert(
    $devThCount >= 6,
    "Developing Pipeline must have at least 6 <th> column headers, found: {$devThCount}"
);
echo "  [PASS] Developing Pipeline has {$devThCount} column headers\n";

// Category column should NOT be in the developing pipeline section
assert(
    !str_contains($devSectionContent, '>Category<') && !str_contains($devSectionContent, '>Category</'),
    "Developing Pipeline must NOT have a 'Category' column (that's for Existing Pipeline only)"
);
echo "  [PASS] Developing Pipeline does not have Category column\n";

// Current Stage column should NOT be in the developing pipeline section
// (all opps are at 2-Analysis, so no need to display current stage)
assert(
    !str_contains($devSectionContent, '>Current Stage<') && !str_contains($devSectionContent, '>Current Stage</'),
    "Developing Pipeline must NOT have a 'Current Stage' column (all are 2-Analysis)"
);
echo "  [PASS] Developing Pipeline does not have Current Stage column\n";


echo "\n==============================\n";
echo "US-005: All tests passed!\n";
echo "==============================\n";
