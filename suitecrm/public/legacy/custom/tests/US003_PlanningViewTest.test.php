<?php
/**
 * US-003: Planning Tool View Tests
 *
 * Tests that the LF_WeeklyPlan planning view file exists with correct structure,
 * class definition, data gathering logic, and HTML rendering patterns.
 *
 * Implementation requirements:
 *   - File: custom/modules/LF_WeeklyPlan/views/view.planning.php
 *   - Class: LF_WeeklyPlanViewPlanning extends SugarView
 *
 * The view must:
 *   1. Call LF_WeeklyPlan::getOrCreateForWeek() for the current user and week
 *   2. Load pipeline-stage opportunities (3-Confirmation through 7-Closing)
 *      using OpportunityQuery (exclude 2-Analysis, Closed Won, Closed Lost)
 *   3. Render header with rep name, week range (via WeekHelper::formatWeekRange()),
 *      and plan status badge
 *   4. Render 'Existing Pipeline' table with 8 columns:
 *      Account, Opportunity (linked), Amount, Current Stage,
 *      Projected Stage (dropdown), Category (dropdown), Day (dropdown), Plan (text input)
 *   5. Pre-fill existing plan items from lf_plan_op_items by matching opportunity_id
 *   6. Include external CSS via <link> tag to custom/themes/lf_dashboard.css
 *   7. Pass stage probability data as JSON object in a <script> tag
 *   8. Use echo HTML output (NO .tpl files, NO Smarty)
 *   9. Inherit SuiteCRM header and footer
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

// No .tpl files should exist for this view
$tplFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_WeeklyPlan'
    . DIRECTORY_SEPARATOR . 'views'
    . DIRECTORY_SEPARATOR . 'planning.tpl';

$tplFileAlt = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_WeeklyPlan'
    . DIRECTORY_SEPARATOR . 'tpl'
    . DIRECTORY_SEPARATOR . 'planning.tpl';

// 8 required table columns for the Existing Pipeline table
$requiredColumns = [
    'Account',
    'Opportunity',
    'Amount',
    'Current Stage',
    'Projected Stage',
    'Category',
    'Day',
    'Plan',
];

// Category dropdown options (4 categories)
$categoryOptions = [
    'Closing',
    'At Risk',
    'Progression',
    'Skip',
];

// Day dropdown options (5 days)
$dayOptions = [
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
];

// Pipeline stages included in the planning view (3-Confirmation through 7-Closing)
// These are the stages that should appear; 2-Analysis, Closed Won, Closed Lost are excluded
$pipelineStages = [
    '3-Confirmation',
    '4-Perception Analysis',
    '5-Proposal',
    '6-Negotiation',
    '7-Closing',
];


// ============================================================
// Section 1: View File Existence
// ============================================================
echo "Section 1: View File Existence\n";

assert(
    file_exists($viewFile),
    "View file should exist at: custom/modules/LF_WeeklyPlan/views/view.planning.php"
);
echo "  [PASS] View file exists\n";

assert(
    is_file($viewFile),
    "View file path should be a regular file, not a directory"
);
echo "  [PASS] View file is a regular file\n";


// ============================================================
// Section 2: View File PHP Format
// ============================================================
echo "\nSection 2: View File PHP Format\n";

$viewContent = file_get_contents($viewFile);
assert($viewContent !== false, "Should be able to read the view file");

// File starts with <?php
assert(
    str_starts_with(trim($viewContent), '<?php'),
    "View file must start with <?php"
);
echo "  [PASS] View file starts with <?php\n";

// sugarEntry guard
assert(
    str_contains($viewContent, "defined('sugarEntry')"),
    "View file must contain sugarEntry guard: defined('sugarEntry')"
);
assert(
    str_contains($viewContent, 'Not A Valid Entry Point'),
    "View file must contain 'Not A Valid Entry Point' die message"
);
echo "  [PASS] View file has sugarEntry guard\n";


// ============================================================
// Section 3: View Class Structure
// ============================================================
echo "\nSection 3: View Class Structure\n";

// Class name and inheritance
assert(
    preg_match('/class\s+LF_WeeklyPlanViewPlanning\s+extends\s+SugarView/', $viewContent) === 1,
    "View file must contain 'class LF_WeeklyPlanViewPlanning extends SugarView'"
);
echo "  [PASS] Class LF_WeeklyPlanViewPlanning extends SugarView\n";

// AllowDynamicProperties attribute for PHP 8.x
assert(
    preg_match('/#\[\\\\AllowDynamicProperties\]/', $viewContent) === 1,
    "View file must have #[\\AllowDynamicProperties] attribute for PHP 8.x compatibility"
);
echo "  [PASS] View class has #[\\AllowDynamicProperties] attribute\n";

// Requires SugarView
assert(
    str_contains($viewContent, 'SugarView.php'),
    "View file must require_once SugarView.php"
);
echo "  [PASS] View file requires SugarView.php\n";

// show_header set to true (inherits SuiteCRM header)
assert(
    str_contains($viewContent, 'show_header') && str_contains($viewContent, 'true'),
    "View must set show_header to true for SuiteCRM header"
);
echo "  [PASS] show_header is set to true\n";

// show_footer set to true (inherits SuiteCRM footer)
assert(
    str_contains($viewContent, 'show_footer') && str_contains($viewContent, 'true'),
    "View must set show_footer to true for SuiteCRM footer"
);
echo "  [PASS] show_footer is set to true\n";

// display() method
assert(
    preg_match('/function\s+display\s*\(/', $viewContent) === 1,
    "View must have a display() method"
);
echo "  [PASS] View has display() method\n";

// Constructor
assert(
    str_contains($viewContent, '__construct'),
    "View must have a constructor"
);
echo "  [PASS] View has constructor\n";

// No .tpl template files (must use echo HTML)
assert(
    !file_exists($tplFile),
    "Must NOT have a .tpl template file at views/planning.tpl - echo HTML instead"
);
assert(
    !file_exists($tplFileAlt),
    "Must NOT have a .tpl template file at tpl/planning.tpl - echo HTML instead"
);
echo "  [PASS] No .tpl template files exist\n";

// Uses echo for HTML output (significant amount for header + table + forms)
$echoCount = preg_match_all('/\becho\s/', $viewContent);
assert(
    $echoCount >= 15,
    "View must use echo statements for HTML output (at least 15 expected for header + table + dropdowns), found: {$echoCount}"
);
echo "  [PASS] View uses echo for HTML output ({$echoCount} echo statements)\n";

// Does NOT use Smarty templates
assert(
    !str_contains($viewContent, 'SugarSmarty'),
    "View must NOT use SugarSmarty template engine"
);
assert(
    !str_contains($viewContent, '$this->ss->'),
    "View must NOT use \$this->ss-> Smarty pattern"
);
echo "  [PASS] View does not use Smarty templates\n";


// ============================================================
// Section 4: Data Gathering - getOrCreateForWeek Call
// ============================================================
echo "\nSection 4: Data Gathering - getOrCreateForWeek\n";

// Calls LF_WeeklyPlan::getOrCreateForWeek()
assert(
    str_contains($viewContent, 'getOrCreateForWeek'),
    "View must call LF_WeeklyPlan::getOrCreateForWeek() to auto-create or load plan"
);
echo "  [PASS] View calls getOrCreateForWeek()\n";

// Passes $current_user->id as first parameter
assert(
    str_contains($viewContent, '$current_user'),
    "View must reference \$current_user for the getOrCreateForWeek() call"
);
echo "  [PASS] View references \$current_user\n";

// References the LF_WeeklyPlan bean class
assert(
    str_contains($viewContent, 'LF_WeeklyPlan'),
    "View must reference the LF_WeeklyPlan bean class"
);
echo "  [PASS] View references LF_WeeklyPlan class\n";

// Includes or requires the WeekHelper
assert(
    str_contains($viewContent, 'WeekHelper'),
    "View must reference WeekHelper for week date calculations"
);
echo "  [PASS] View references WeekHelper\n";

// Uses WeekHelper to get the current week start
assert(
    str_contains($viewContent, 'getCurrentWeekStart') || str_contains($viewContent, 'getWeekStart'),
    "View must call WeekHelper::getCurrentWeekStart() or getWeekStart() to determine week"
);
echo "  [PASS] View uses WeekHelper for week start calculation\n";


// ============================================================
// Section 5: Data Gathering - Pipeline Opportunities Query
// ============================================================
echo "\nSection 5: Data Gathering - Pipeline Opportunities\n";

// References OpportunityQuery class for loading opportunities
assert(
    str_contains($viewContent, 'OpportunityQuery'),
    "View must reference OpportunityQuery class for loading pipeline opportunities"
);
echo "  [PASS] View references OpportunityQuery class\n";

// Queries opportunities for the current user (pipeline stages 3-7)
// The view should exclude 2-Analysis, Closed Won, Closed Lost
assert(
    str_contains($viewContent, 'opportunities') || str_contains($viewContent, 'OpportunityQuery'),
    "View must query opportunities data"
);
echo "  [PASS] View queries opportunities data\n";

// Must filter by assigned user
assert(
    str_contains($viewContent, 'assigned_user_id') || str_contains($viewContent, '$current_user->id'),
    "View must filter opportunities by assigned_user_id or current_user->id"
);
echo "  [PASS] View filters opportunities by current user\n";

// The stage filtering logic should exclude specific stages
// Check that the view references the exclusion of 2-Analysis or the inclusion of stages 3-7
assert(
    str_contains($viewContent, 'Analysis')
    || str_contains($viewContent, 'Closed Won')
    || str_contains($viewContent, 'Closed Lost')
    || str_contains($viewContent, 'NOT IN')
    || str_contains($viewContent, '3-Confirmation'),
    "View must handle stage filtering (exclude 2-Analysis, Closed Won, Closed Lost)"
);
echo "  [PASS] View handles pipeline stage filtering\n";


// ============================================================
// Section 6: Header Section - Rep Name, Week Range, Status Badge
// ============================================================
echo "\nSection 6: Header Section\n";

// Header shows rep name
assert(
    str_contains($viewContent, 'first_name') || str_contains($viewContent, 'last_name')
    || str_contains($viewContent, 'full_name') || str_contains($viewContent, '$current_user->name'),
    "View header must display the rep name (first_name/last_name or user name)"
);
echo "  [PASS] View header displays rep name\n";

// Header shows week range via WeekHelper::formatWeekRange()
assert(
    str_contains($viewContent, 'formatWeekRange'),
    "View header must call WeekHelper::formatWeekRange() for week range display"
);
echo "  [PASS] View header uses formatWeekRange()\n";

// Header shows plan status badge
assert(
    str_contains($viewContent, 'status'),
    "View header must display the plan status"
);
echo "  [PASS] View header references plan status\n";

// Status badge should have some visual indicator (CSS class or badge element)
assert(
    preg_match('/badge|status.*class|class.*status/i', $viewContent) === 1
    || str_contains($viewContent, 'lf-status')
    || str_contains($viewContent, 'status-badge')
    || (str_contains($viewContent, 'status') && str_contains($viewContent, 'class=')),
    "View must render plan status as a visual badge with a CSS class"
);
echo "  [PASS] View renders status as a visual badge\n";


// ============================================================
// Section 7: Existing Pipeline Table - 8 Columns
// ============================================================
echo "\nSection 7: Existing Pipeline Table\n";

// Contains 'Existing Pipeline' section heading
assert(
    preg_match('/Existing\s+Pipeline/i', $viewContent) === 1,
    "View must contain 'Existing Pipeline' section heading"
);
echo "  [PASS] 'Existing Pipeline' section heading found\n";

// Contains an HTML table
assert(
    str_contains($viewContent, '<table') && str_contains($viewContent, '</table>'),
    "View must contain an HTML <table> for the Existing Pipeline"
);
echo "  [PASS] View contains HTML table\n";

// Contains table header row with <th> elements
assert(
    str_contains($viewContent, '<th'),
    "View must contain <th> header elements for table columns"
);
echo "  [PASS] View contains <th> header elements\n";

// Check each required column header is present
foreach ($requiredColumns as $col) {
    assert(
        str_contains($viewContent, $col),
        "View table must include column header: '{$col}'"
    );
    echo "  [PASS] Column '{$col}' found\n";
}

// Table has at least 8 column headers
$thCount = preg_match_all('/<th\b/i', $viewContent);
assert(
    $thCount >= 8,
    "View table must have at least 8 <th> column headers, found: {$thCount}"
);
echo "  [PASS] Table has at least 8 column headers ({$thCount} found)\n";


// ============================================================
// Section 8: Table Column Details - Dropdowns and Inputs
// ============================================================
echo "\nSection 8: Table Column Details\n";

// Opportunity column must link to CRM record detail view
assert(
    str_contains($viewContent, 'index.php?module=Opportunities&action=DetailView&record='),
    "Opportunity column must contain link to CRM detail view (index.php?module=Opportunities&action=DetailView&record=)"
);
echo "  [PASS] Opportunity column links to CRM detail view\n";

// Opportunity link uses <a> tag
assert(
    str_contains($viewContent, '<a ') && str_contains($viewContent, 'Opportunities'),
    "Opportunity column must use <a> tag for the detail view link"
);
echo "  [PASS] Opportunity column uses <a> tag link\n";

// Amount column should format currency
assert(
    str_contains($viewContent, 'number_format') || str_contains($viewContent, 'currency_format')
    || str_contains($viewContent, 'format_number') || str_contains($viewContent, '$'),
    "Amount column must format the amount as currency"
);
echo "  [PASS] Amount column formats currency\n";

// Projected Stage dropdown (<select>)
assert(
    str_contains($viewContent, 'projected_stage'),
    "View must have a projected_stage dropdown field"
);
echo "  [PASS] projected_stage field referenced\n";

// Projected Stage dropdown contains only stages above current stage
// Look for comparison logic between current stage and available stages
assert(
    str_contains($viewContent, '<select') && str_contains($viewContent, 'projected_stage'),
    "View must render projected_stage as a <select> dropdown"
);
echo "  [PASS] projected_stage rendered as dropdown\n";

// Category dropdown with all 4 options
assert(
    str_contains($viewContent, '<select') && (
        str_contains($viewContent, 'category') || str_contains($viewContent, 'item_type')
    ),
    "View must render category/item_type as a <select> dropdown"
);
echo "  [PASS] Category rendered as dropdown\n";

// Check each category option is present
foreach ($categoryOptions as $cat) {
    assert(
        str_contains($viewContent, $cat),
        "Category dropdown must include option: '{$cat}'"
    );
    echo "  [PASS] Category option '{$cat}' found\n";
}

// Day dropdown with Monday through Friday
assert(
    str_contains($viewContent, 'planned_day') || str_contains($viewContent, 'day'),
    "View must have a day dropdown field"
);
echo "  [PASS] Day dropdown field referenced\n";

foreach ($dayOptions as $day) {
    assert(
        str_contains($viewContent, $day),
        "Day dropdown must include option: '{$day}'"
    );
    echo "  [PASS] Day option '{$day}' found\n";
}

// Plan column has text input
assert(
    str_contains($viewContent, 'plan_description') || str_contains($viewContent, 'plan_text'),
    "View must have a plan description/text input field"
);
echo "  [PASS] Plan text input field referenced\n";

assert(
    str_contains($viewContent, 'type="text"') || str_contains($viewContent, "type='text'")
    || str_contains($viewContent, '<textarea') || str_contains($viewContent, '<input'),
    "View must contain text input or textarea elements for plan description"
);
echo "  [PASS] Text input/textarea elements present\n";

// Count <select> elements - need at least 3 per row: Projected Stage, Category, Day
// Even with one row, we need at least 3 select elements
$selectCount = substr_count($viewContent, '<select');
assert(
    $selectCount >= 3,
    "View must have at least 3 <select> dropdown elements (Projected Stage, Category, Day), found: {$selectCount}"
);
echo "  [PASS] At least 3 <select> dropdown elements present ({$selectCount} found)\n";


// ============================================================
// Section 9: Projected Stage Dropdown - Stage Comparison Logic
// ============================================================
echo "\nSection 9: Projected Stage Dropdown Logic\n";

// The projected stage dropdown must contain only stages above the current stage
// This requires some comparison/filtering logic in the PHP code
assert(
    str_contains($viewContent, 'stage') && (
        str_contains($viewContent, 'probability') ||
        str_contains($viewContent, 'above') ||
        str_contains($viewContent, 'higher') ||
        str_contains($viewContent, 'stage_probabilities') ||
        str_contains($viewContent, 'stageIndex') ||
        str_contains($viewContent, 'stage_index') ||
        str_contains($viewContent, 'stageOrder') ||
        str_contains($viewContent, 'stage_order') ||
        preg_match('/foreach.*stage/i', $viewContent) === 1
    ),
    "View must implement stage comparison logic for projected stage dropdown (filtering stages above current)"
);
echo "  [PASS] Stage comparison logic found for projected stage dropdown\n";

// At least one pipeline stage name should be referenced in the dropdown generation
$stageFound = false;
foreach ($pipelineStages as $stage) {
    if (str_contains($viewContent, $stage)) {
        $stageFound = true;
        break;
    }
}
assert(
    $stageFound || str_contains($viewContent, 'stage_probabilities') || str_contains($viewContent, 'sales_stage'),
    "View must reference pipeline stage names or stage_probabilities for dropdown generation"
);
echo "  [PASS] Pipeline stage references found\n";


// ============================================================
// Section 10: Pre-fill Existing Plan Items
// ============================================================
echo "\nSection 10: Pre-fill Existing Plan Items\n";

// View queries lf_plan_op_items table to load existing plan items
assert(
    str_contains($viewContent, 'lf_plan_op_items') || str_contains($viewContent, 'LF_PlanOpItem'),
    "View must query lf_plan_op_items or reference LF_PlanOpItem to load existing plan items"
);
echo "  [PASS] View references lf_plan_op_items / LF_PlanOpItem\n";

// Pre-fill matches by opportunity_id
assert(
    str_contains($viewContent, 'opportunity_id'),
    "View must use opportunity_id for matching existing plan items to opportunities"
);
echo "  [PASS] View uses opportunity_id for plan item matching\n";

// The pre-fill logic should load plan items for the current week's plan
assert(
    (str_contains($viewContent, 'lf_weekly_plan_id') || str_contains($viewContent, 'plan_id'))
    && str_contains($viewContent, 'opportunity_id'),
    "View must load plan items by weekly_plan_id and match by opportunity_id"
);
echo "  [PASS] View loads plan items by weekly plan ID and matches by opportunity_id\n";

// Uses fetchByAssoc or similar pattern for plan item iteration
assert(
    str_contains($viewContent, 'fetchByAssoc') || str_contains($viewContent, 'fetch'),
    "View must use \$db->fetchByAssoc() to iterate plan item results"
);
echo "  [PASS] View uses fetchByAssoc() for plan item iteration\n";


// ============================================================
// Section 11: External CSS and JS References
// ============================================================
echo "\nSection 11: External CSS and JS References\n";

// Includes external CSS via link tag to custom/themes/lf_dashboard.css
assert(
    str_contains($viewContent, 'lf_dashboard.css'),
    "View must reference lf_dashboard.css"
);
echo "  [PASS] View references lf_dashboard.css\n";

assert(
    preg_match('/<link[^>]*href\s*=\s*["\'][^"\']*lf_dashboard\.css["\']/', $viewContent) === 1,
    "View must have a properly formed <link> tag for lf_dashboard.css"
);
echo "  [PASS] <link> tag for lf_dashboard.css is properly formed\n";

assert(
    str_contains($viewContent, 'custom/themes/lf_dashboard.css'),
    "CSS link must reference full path: custom/themes/lf_dashboard.css"
);
echo "  [PASS] CSS link has correct full path\n";

// Passes stage probabilities as JSON in a script tag
assert(
    str_contains($viewContent, 'json_encode') || str_contains($viewContent, 'JSON'),
    "View must use json_encode() to pass stage probability data"
);
echo "  [PASS] View uses json_encode() for stage data\n";

assert(
    str_contains($viewContent, '<script'),
    "View must contain a <script> tag for stage probability data"
);
echo "  [PASS] View has <script> tag\n";

assert(
    str_contains($viewContent, 'stage_probabilities') || str_contains($viewContent, 'stageProbabilities'),
    "View must pass stage_probabilities or stageProbabilities as a JavaScript variable"
);
echo "  [PASS] View passes stage probability data as JavaScript variable\n";

// References planning.js
assert(
    str_contains($viewContent, 'planning.js'),
    "View must reference planning.js"
);
echo "  [PASS] View references planning.js\n";

assert(
    preg_match('/<script[^>]*src\s*=\s*["\'][^"\']*planning\.js["\']/', $viewContent) === 1,
    "View must have a properly formed <script src='...planning.js'> tag"
);
echo "  [PASS] <script> tag for planning.js is properly formed\n";


// ============================================================
// Section 12: Database Access Patterns
// ============================================================
echo "\nSection 12: Database Access Patterns\n";

// Uses global $db for database access
assert(
    str_contains($viewContent, '$db'),
    "View must use \$db for database access"
);
echo "  [PASS] View uses \$db\n";

// Uses $db->query() for database operations
assert(
    str_contains($viewContent, '$db->query('),
    "View must use \$db->query() for database operations"
);
echo "  [PASS] View uses \$db->query()\n";

// Uses $db->fetchByAssoc() to iterate results
assert(
    str_contains($viewContent, 'fetchByAssoc'),
    "View must use \$db->fetchByAssoc() to iterate query results"
);
echo "  [PASS] View uses \$db->fetchByAssoc()\n";

// Filters by deleted = 0
assert(
    str_contains($viewContent, 'deleted'),
    "View queries must filter by deleted field"
);
echo "  [PASS] View filters by deleted field\n";


// ============================================================
// Section 13: Security Patterns
// ============================================================
echo "\nSection 13: Security Patterns\n";

// Uses htmlspecialchars for output escaping
$htmlEscapeCount = substr_count($viewContent, 'htmlspecialchars(');
assert(
    $htmlEscapeCount >= 3,
    "View must use htmlspecialchars() at least 3 times for safe output, found: {$htmlEscapeCount}"
);
echo "  [PASS] htmlspecialchars() used {$htmlEscapeCount} times\n";

// Uses $db->quote() or $db->quoted() for SQL parameter escaping
assert(
    str_contains($viewContent, '$db->quote(') || str_contains($viewContent, '$db->quoted('),
    "View must use \$db->quote() or \$db->quoted() for SQL injection prevention"
);
echo "  [PASS] View uses \$db->quote()/\$db->quoted() for SQL safety\n";

// No raw variable interpolation in SQL queries
assert(
    !preg_match('/\$db->query\([^)]*\$_(?:POST|GET|REQUEST)/', $viewContent),
    "View must NOT use raw \$_POST/\$_GET/\$_REQUEST values directly in \$db->query() calls"
);
echo "  [PASS] No raw superglobal values in SQL queries\n";


// ============================================================
// Section 14: Dependency Files Exist
// ============================================================
echo "\nSection 14: Dependency Files Exist\n";

// LF_WeeklyPlan bean file exists
$weeklyPlanBean = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_WeeklyPlan'
    . DIRECTORY_SEPARATOR . 'LF_WeeklyPlan.php';
assert(
    file_exists($weeklyPlanBean),
    "LF_WeeklyPlan bean file must exist (view dependency for getOrCreateForWeek)"
);
echo "  [PASS] LF_WeeklyPlan bean file exists\n";

// WeekHelper utility file exists
$weekHelperFile = $customDir
    . DIRECTORY_SEPARATOR . 'include'
    . DIRECTORY_SEPARATOR . 'LF_PlanningReporting'
    . DIRECTORY_SEPARATOR . 'WeekHelper.php';
assert(
    file_exists($weekHelperFile),
    "WeekHelper.php must exist (view dependency for week calculations)"
);
echo "  [PASS] WeekHelper.php exists\n";

// OpportunityQuery utility file exists
$opportunityQueryFile = $customDir
    . DIRECTORY_SEPARATOR . 'include'
    . DIRECTORY_SEPARATOR . 'LF_PlanningReporting'
    . DIRECTORY_SEPARATOR . 'OpportunityQuery.php';
assert(
    file_exists($opportunityQueryFile),
    "OpportunityQuery.php must exist (view dependency for pipeline data)"
);
echo "  [PASS] OpportunityQuery.php exists\n";

// LF_PlanOpItem bean file exists
$planOpItemBean = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_PlanOpItem'
    . DIRECTORY_SEPARATOR . 'LF_PlanOpItem.php';
assert(
    file_exists($planOpItemBean),
    "LF_PlanOpItem bean file must exist (view dependency for pre-filling plan items)"
);
echo "  [PASS] LF_PlanOpItem bean file exists\n";


// ============================================================
// Section 15: View Requires/Includes Dependencies
// ============================================================
echo "\nSection 15: View Requires/Includes Dependencies\n";

// View file must include or require the WeekHelper class
assert(
    str_contains($viewContent, 'WeekHelper.php'),
    "View must require_once or include WeekHelper.php"
);
echo "  [PASS] View includes WeekHelper.php\n";

// View file must include or require the OpportunityQuery class
assert(
    str_contains($viewContent, 'OpportunityQuery.php'),
    "View must require_once or include OpportunityQuery.php"
);
echo "  [PASS] View includes OpportunityQuery.php\n";

// View file must include or require the LF_WeeklyPlan bean
assert(
    str_contains($viewContent, 'LF_WeeklyPlan.php'),
    "View must require_once or include LF_WeeklyPlan.php"
);
echo "  [PASS] View includes LF_WeeklyPlan.php\n";


// ============================================================
// Section 16: Cross-Validation
// ============================================================
echo "\nSection 16: Cross-Validation\n";

// View file naming follows convention: view.planning.php
assert(
    str_ends_with($viewFile, 'view.planning.php'),
    "View file must follow naming convention: view.planning.php"
);
echo "  [PASS] View file naming follows convention\n";

// View class naming follows SuiteCRM convention: LF_WeeklyPlanViewPlanning
assert(
    str_contains($viewContent, 'LF_WeeklyPlanViewPlanning'),
    "Class name must follow SuiteCRM convention: LF_WeeklyPlanViewPlanning"
);
echo "  [PASS] Class name follows SuiteCRM convention\n";

// View file path is within the correct module directory
assert(
    str_contains($viewFile, 'LF_WeeklyPlan' . DIRECTORY_SEPARATOR . 'views'),
    "View file must be in LF_WeeklyPlan/views/ directory"
);
echo "  [PASS] View file is in correct directory\n";

// View references the LF_PRConfig class for stage probabilities
assert(
    str_contains($viewContent, 'LF_PRConfig') || str_contains($viewContent, 'stage_probabilities'),
    "View must reference LF_PRConfig or stage_probabilities for JSON data"
);
echo "  [PASS] View references LF_PRConfig or stage_probabilities\n";

// The view must use both getOrCreateForWeek and WeekHelper together
assert(
    str_contains($viewContent, 'getOrCreateForWeek') && str_contains($viewContent, 'WeekHelper'),
    "View must use both getOrCreateForWeek() and WeekHelper together for data gathering"
);
echo "  [PASS] View integrates getOrCreateForWeek() with WeekHelper\n";

// The view renders both a header section and a table section
assert(
    str_contains($viewContent, '<table') && (
        str_contains($viewContent, '<h2') || str_contains($viewContent, '<h3')
        || str_contains($viewContent, 'header') || str_contains($viewContent, 'moduleTitle')
    ),
    "View must render both a header section and a table section"
);
echo "  [PASS] View has both header and table sections\n";

// The account_name field is referenced (for the Account column)
assert(
    str_contains($viewContent, 'account_name'),
    "View must reference account_name field for the Account column"
);
echo "  [PASS] View references account_name for Account column\n";

// The amount field is referenced (for the Amount column)
assert(
    str_contains($viewContent, 'amount'),
    "View must reference amount field for the Amount column"
);
echo "  [PASS] View references amount field\n";

// The sales_stage field is referenced (for the Current Stage column)
assert(
    str_contains($viewContent, 'sales_stage'),
    "View must reference sales_stage field for the Current Stage column"
);
echo "  [PASS] View references sales_stage field\n";


echo "\n==============================\n";
echo "US-003: All tests passed!\n";
echo "==============================\n";
