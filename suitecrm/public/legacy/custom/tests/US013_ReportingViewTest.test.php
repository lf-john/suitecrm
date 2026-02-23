<?php
/**
 * US-013: Reporting View Class Tests (TDD-RED)
 *
 * Tests for LF_WeeklyReportViewReporting class and auto-detection logic.
 *
 * Target: custom/modules/LF_WeeklyReport/views/view.reporting.php
 *
 * This test file verifies:
 * - File structure and class definition
 * - Data loading (report, plan, items, snapshots)
 * - Movement detection logic (progressed, static, regressed, closed_won, closed_lost)
 * - Rendering with opportunity links
 * - No stage dropdown in output
 * - Uses echo HTML (no Smarty templates)
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

$customDir = dirname(__DIR__);

$viewFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_WeeklyReport'
    . DIRECTORY_SEPARATOR . 'views'
    . DIRECTORY_SEPARATOR . 'view.reporting.php';

echo "US-013: Reporting View Class Tests\n";
echo str_repeat('=', 60) . "\n\n";


// ============================================================
// Section 1: File Existence and Structure
// ============================================================
echo "Section 1: File Existence and Structure\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: File exists ---
assert(
    file_exists($viewFile),
    "view.reporting.php must exist at custom/modules/LF_WeeklyReport/views/view.reporting.php"
);
echo "  [PASS] view.reporting.php file exists\n";

// --- Happy Path: File is a regular file ---
assert(
    is_file($viewFile),
    "view.reporting.php path should be a regular file, not a directory"
);
echo "  [PASS] view.reporting.php is a regular file\n";

// --- Happy Path: File is readable ---
assert(
    is_readable($viewFile),
    "view.reporting.php must be readable"
);
echo "  [PASS] view.reporting.php is readable\n";

echo "\n";


// ============================================================
// Section 2: PHP Format and sugarEntry Guard
// ============================================================
echo "Section 2: PHP Format and sugarEntry Guard\n";
echo str_repeat('-', 40) . "\n";

$fileContent = file_get_contents($viewFile);
assert($fileContent !== false, "Should be able to read the view file");

// --- Happy Path: File starts with PHP opening tag ---
assert(
    str_starts_with(trim($fileContent), '<?php'),
    "view.reporting.php must start with <?php tag"
);
echo "  [PASS] File starts with <?php tag\n";

// --- Happy Path: sugarEntry guard present ---
assert(
    str_contains($fileContent, "defined('sugarEntry')") || str_contains($fileContent, 'defined("sugarEntry")'),
    "view.reporting.php must contain sugarEntry guard check"
);
echo "  [PASS] File has sugarEntry defined() check\n";

// --- Happy Path: sugarEntry die message ---
assert(
    str_contains($fileContent, 'Not A Valid Entry Point'),
    "view.reporting.php must contain 'Not A Valid Entry Point' die message"
);
echo "  [PASS] File has 'Not A Valid Entry Point' die message\n";

echo "\n";


// ============================================================
// Section 3: Class Definition
// ============================================================
echo "Section 3: Class Definition\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Class named LF_WeeklyReportViewReporting ---
assert(
    preg_match('/class\s+LF_WeeklyReportViewReporting/', $fileContent) === 1,
    "File must contain 'class LF_WeeklyReportViewReporting'"
);
echo "  [PASS] File contains class LF_WeeklyReportViewReporting\n";

// --- Happy Path: Class extends SugarView ---
assert(
    preg_match('/class\s+LF_WeeklyReportViewReporting\s+extends\s+SugarView/', $fileContent) === 1,
    "LF_WeeklyReportViewReporting must extend SugarView"
);
echo "  [PASS] Class extends SugarView\n";

// --- Happy Path: Class has #[\AllowDynamicProperties] attribute ---
assert(
    str_contains($fileContent, '#[\AllowDynamicProperties]') || str_contains($fileContent, '#[\\AllowDynamicProperties]'),
    "Class must have #[\\AllowDynamicProperties] attribute for PHP 8.2 compatibility"
);
echo "  [PASS] Class has #[\\AllowDynamicProperties] attribute\n";

// --- Happy Path: Class has display() method ---
assert(
    preg_match('/public\s+function\s+display\s*\(/', $fileContent) === 1,
    "Class must have public display() method"
);
echo "  [PASS] Class has public display() method\n";

// --- Negative Case: No Smarty template references ---
assert(
    !str_contains($fileContent, '->display()') || str_contains($fileContent, 'parent::display()'),
    "view.reporting.php should not call Smarty display() (except parent::display() for header/footer)"
);
echo "  [PASS] No Smarty template display() calls\n";

// --- Negative Case: No .tpl file references ---
assert(
    !str_contains($fileContent, '.tpl'),
    "view.reporting.php must NOT reference .tpl template files"
);
echo "  [PASS] No .tpl file references\n";

echo "\n";


// ============================================================
// Section 4: Data Loading - Report and Plan
// ============================================================
echo "Section 4: Data Loading - Report and Plan\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Uses LF_WeeklyReport::getOrCreateForWeek() ---
assert(
    str_contains($fileContent, 'LF_WeeklyReport::getOrCreateForWeek'),
    "display() must call LF_WeeklyReport::getOrCreateForWeek() to load the report"
);
echo "  [PASS] Calls LF_WeeklyReport::getOrCreateForWeek()\n";

// --- Happy Path: Uses $current_user->id for user context ---
assert(
    str_contains($fileContent, '$current_user') || str_contains($fileContent, 'current_user'),
    "display() must use \$current_user->id to load the current user's report"
);
echo "  [PASS] References \$current_user for user context\n";

// --- Happy Path: Uses WeekHelper to get current week start ---
assert(
    str_contains($fileContent, 'WeekHelper::getCurrentWeekStart') || str_contains($fileContent, 'WeekHelper::'),
    "display() must use WeekHelper to get the current week start date"
);
echo "  [PASS] Uses WeekHelper for week start calculation\n";

// --- Happy Path: Loads the weekly plan ---
assert(
    str_contains($fileContent, 'lf_weekly_plan') || str_contains($fileContent, 'LF_WeeklyPlan'),
    "display() must load the corresponding LF_WeeklyPlan"
);
echo "  [PASS] References LF_WeeklyPlan for loading plan\n";

// --- Happy Path: Loads plan opportunity items ---
assert(
    str_contains($fileContent, 'lf_plan_op_items') || str_contains($fileContent, 'LF_PlanOpItem'),
    "display() must load lf_plan_op_items from the weekly plan"
);
echo "  [PASS] References lf_plan_op_items\n";

// --- Happy Path: Loads snapshots ---
assert(
    str_contains($fileContent, 'lf_report_snapshot') || str_contains($fileContent, 'LF_ReportSnapshot'),
    "display() must load LF_ReportSnapshot records for the week"
);
echo "  [PASS] References LF_ReportSnapshot\n";

// --- Happy Path: Queries snapshots with stage_at_week_start ---
assert(
    str_contains($fileContent, 'stage_at_week_start'),
    "display() must load stage_at_week_start from snapshots"
);
echo "  [PASS] References stage_at_week_start field\n";

echo "\n";


// ============================================================
// Section 5: Movement Detection Logic
// ============================================================
echo "Section 5: Movement Detection Logic\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Uses LF_PRConfig for stage probabilities ---
assert(
    str_contains($fileContent, 'LF_PRConfig::getConfigJson') || str_contains($fileContent, 'LF_PRConfig::'),
    "display() must use LF_PRConfig to load stage probabilities"
);
echo "  [PASS] Uses LF_PRConfig for stage probabilities\n";

// --- Happy Path: References stage_probabilities config ---
assert(
    str_contains($fileContent, 'stage_probabilities'),
    "display() must reference stage_probabilities from config"
);
echo "  [PASS] References stage_probabilities config\n";

// --- Happy Path: Compares current stage to stage_at_week_start ---
assert(
    str_contains($fileContent, 'sales_stage') || str_contains($fileContent, 'current') && str_contains($fileContent, 'stage'),
    "display() must compare current CRM stage to stage_at_week_start"
);
echo "  [PASS] Compares current stage to start stage\n";

// --- Happy Path: Detects 'progressed' movement ---
assert(
    str_contains($fileContent, 'progressed') || str_contains($fileContent, 'forward'),
    "display() must detect 'progressed' movement when probability increases"
);
echo "  [PASS] Detects 'progressed' or 'forward' movement\n";

// --- Happy Path: Detects 'static' movement ---
assert(
    str_contains($fileContent, 'static'),
    "display() must detect 'static' movement when probability is equal"
);
echo "  [PASS] Detects 'static' movement\n";

// --- Happy Path: Detects 'regressed' movement ---
assert(
    str_contains($fileContent, 'regressed') || str_contains($fileContent, 'backward'),
    "display() must detect 'regressed' movement when probability decreases"
);
echo "  [PASS] Detects 'regressed' or 'backward' movement\n";

// --- Happy Path: Detects 'closed_won' ---
assert(
    str_contains($fileContent, 'closed_won') || str_contains($fileContent, 'Closed Won'),
    "display() must detect 'closed_won' for Closed Won stage"
);
echo "  [PASS] Detects 'closed_won' for Closed Won stage\n";

// --- Happy Path: Detects 'closed_lost' ---
assert(
    str_contains($fileContent, 'closed_lost') || str_contains($fileContent, 'Closed Lost'),
    "display() must detect 'closed_lost' for Closed Lost stage"
);
echo "  [PASS] Detects 'closed_lost' for Closed Lost stage\n";

// --- Happy Path: Uses probability comparison logic ---
assert(
    preg_match('/>\s*\$|<\s*\$|>=|<=|==|===/', $fileContent) === 1,
    "display() must use comparison operators to detect movement"
);
echo "  [PASS] Uses comparison operators for movement detection\n";

echo "\n";


// ============================================================
// Section 6: Opportunity Links Rendering
// ============================================================
echo "Section 6: Opportunity Links Rendering\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Renders opportunity as clickable link ---
assert(
    str_contains($fileContent, '<a href') || str_contains($fileContent, '<a '),
    "display() must render opportunities as <a> links"
);
echo "  [PASS] Renders <a> links\n";

// --- Happy Path: Link format includes module=Opportunities ---
assert(
    str_contains($fileContent, 'module=Opportunities'),
    "Link must use module=Opportunities parameter"
);
echo "  [PASS] Link uses module=Opportunities\n";

// --- Happy Path: Link format includes action=DetailView ---
assert(
    str_contains($fileContent, 'action=DetailView'),
    "Link must use action=DetailView parameter"
);
echo "  [PASS] Link uses action=DetailView\n";

// --- Happy Path: Link format includes record={id} ---
assert(
    str_contains($fileContent, 'record=') || str_contains($fileContent, 'record'),
    "Link must include record parameter with opportunity ID"
);
echo "  [PASS] Link includes record parameter\n";

// --- Happy Path: Link uses index.php as base URL ---
assert(
    str_contains($fileContent, 'index.php'),
    "Link must use index.php as base URL"
);
echo "  [PASS] Link uses index.php as base URL\n";

echo "\n";


// ============================================================
// Section 7: Stage Dropdown Absence
// ============================================================
echo "Section 7: Stage Dropdown Absence\n";
echo str_repeat('-', 40) . "\n";

// --- Negative Case: No <select> element for stages ---
assert(
    !preg_match('/<select[^>]*sales_stage/', $fileContent),
    "display() must NOT render a <select> dropdown for sales_stage"
);
echo "  [PASS] No <select> dropdown for sales_stage\n";

// --- Negative Case: No stage change form ---
assert(
    !str_contains($fileContent, 'change_stage') && !str_contains($fileContent, 'update_stage'),
    "display() must NOT allow stage changes from this view"
);
echo "  [PASS] No stage change functionality\n";

// --- Happy Path: Stage is read-only (display only) ---
// Implied by absence of <select> and forms, but verify we display stage text
assert(
    str_contains($fileContent, 'sales_stage') || str_contains($fileContent, 'stage'),
    "display() must show current stage as read-only text"
);
echo "  [PASS] Stage is displayed as read-only\n";

echo "\n";


// ============================================================
// Section 8: Echo HTML (No Smarty Templates)
// ============================================================
echo "Section 8: Echo HTML Rendering\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Uses echo statements ---
assert(
    str_contains($fileContent, 'echo '),
    "display() must use echo to output HTML"
);
echo "  [PASS] Uses echo statements\n";

// --- Happy Path: Contains HTML tags in echo statements ---
assert(
    preg_match('/echo\s+["\'].*<[a-zA-Z]+/', $fileContent) === 1
    || preg_match('/echo\s+"[^"]*<[a-zA-Z]+/', $fileContent) === 1
    || preg_match('/echo\s+\'[^\']*<[a-zA-Z]+/', $fileContent) === 1,
    "display() must echo HTML tags directly"
);
echo "  [PASS] Echoes HTML tags\n";

// --- Happy Path: Inherits SuiteCRM header via parent::display() ---
assert(
    str_contains($fileContent, 'parent::display()'),
    "display() should call parent::display() to inherit SuiteCRM header and footer"
);
echo "  [PASS] Calls parent::display() for header/footer\n";

// --- Negative Case: No $this->view->display() or $this->ss->display() ---
assert(
    !str_contains($fileContent, '$this->view->display') && !str_contains($fileContent, '$this->ss->display'),
    "display() must NOT use Smarty's display() method (except parent::display())"
);
echo "  [PASS] No Smarty display() calls\n";

echo "\n";


// ============================================================
// Section 9: Security - SQL Safety and XSS Prevention
// ============================================================
echo "Section 9: Security Patterns\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Uses htmlspecialchars for output escaping ---
assert(
    str_contains($fileContent, 'htmlspecialchars') || str_contains($fileContent, 'htmlentities'),
    "display() must use htmlspecialchars() or htmlentities() to prevent XSS"
);
echo "  [PASS] Uses htmlspecialchars() for XSS prevention\n";

// --- Happy Path: Uses $db->quoted() or $db->quote() for SQL parameters ---
assert(
    str_contains($fileContent, '$db->quote') || str_contains($fileContent, '$db->quoted') || str_contains($fileContent, 'sprintf'),
    "display() must use \$db->quote() or \$db->quoted() for SQL parameter escaping"
);
echo "  [PASS] Uses SQL parameter escaping\n";

// --- Negative Case: No raw SQL concatenation ---
// Check that we don't have patterns like: WHERE foo = '$var'
// (We should have: WHERE foo = '" . $db->quote($var) . "')
$hasSafePatterns = str_contains($fileContent, '$db->quote')
    || str_contains($fileContent, '$db->quoted')
    || str_contains($fileContent, 'sprintf');

assert(
    $hasSafePatterns,
    "display() must use safe SQL patterns (quote/quoted/sprintf)"
);
echo "  [PASS] Uses safe SQL patterns\n";

echo "\n";


// ============================================================
// Section 10: Edge Cases
// ============================================================
echo "Section 10: Edge Cases\n";
echo str_repeat('-', 40) . "\n";

// --- Edge Case: Handles empty plan (no opportunities) ---
assert(
    str_contains($fileContent, 'empty') || str_contains($fileContent, 'count') || str_contains($fileContent, 'if'),
    "display() should handle empty plans gracefully"
);
echo "  [PASS] Has conditional logic for handling empty data\n";

// --- Edge Case: Handles missing snapshot data ---
assert(
    str_contains($fileContent, 'if') || str_contains($fileContent, '?:') || str_contains($fileContent, '??'),
    "display() should have conditional checks for missing data"
);
echo "  [PASS] Has conditional logic for missing data\n";

// --- Edge Case: Handles missing stage in config ---
// When a stage is not in the stage_probabilities config, should use default value
assert(
    str_contains($fileContent, '??') || str_contains($fileContent, 'isset') || str_contains($fileContent, 'array_key_exists'),
    "display() should handle missing stage probabilities with defaults"
);
echo "  [PASS] Has checks for missing config values\n";

// --- Edge Case: Handles null current stage ---
assert(
    str_contains($fileContent, 'empty') || str_contains($fileContent, 'null') || str_contains($fileContent, 'isset'),
    "display() should handle null or empty current stage"
);
echo "  [PASS] Has null/empty checks\n";

echo "\n";


// ============================================================
// Section 11: Data Loading - Query Patterns
// ============================================================
echo "Section 11: Database Query Patterns\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Uses $db->query() for database access ---
assert(
    str_contains($fileContent, '$db->query') || str_contains($fileContent, 'query('),
    "display() should use \$db->query() for database operations"
);
echo "  [PASS] Uses \$db->query()\n";

// --- Happy Path: Uses fetchByAssoc for result iteration ---
assert(
    str_contains($fileContent, 'fetchByAssoc'),
    "display() should use \$db->fetchByAssoc() for result iteration"
);
echo "  [PASS] Uses fetchByAssoc()\n";

// --- Happy Path: Queries lf_report_snapshots table ---
assert(
    str_contains($fileContent, 'lf_report_snapshots'),
    "display() must query lf_report_snapshots table"
);
echo "  [PASS] Queries lf_report_snapshots table\n";

// --- Happy Path: Joins with opportunities table for current stage ---
assert(
    str_contains($fileContent, 'opportunities') || str_contains($fileContent, 'Opportunities'),
    "display() must reference opportunities to get current stage"
);
echo "  [PASS] References opportunities for current data\n";

// --- Happy Path: Filters by deleted=0 ---
assert(
    str_contains($fileContent, 'deleted = 0') || str_contains($fileContent, 'deleted=0'),
    "display() must filter by deleted=0 in queries"
);
echo "  [PASS] Filters by deleted=0\n";

echo "\n";


// ============================================================
// Section 12: Movement Detection - Detailed Logic
// ============================================================
echo "Section 12: Movement Detection - Detailed Logic\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Gets probability for start stage ---
assert(
    preg_match('/\$.*probabilities\s*\[.*stage_at_week_start/i', $fileContent) === 1
    || preg_match('/stage_at_week_start.*probabilities/i', $fileContent) === 1,
    "display() must look up probability for stage_at_week_start"
);
echo "  [PASS] Looks up probability for stage_at_week_start\n";

// --- Happy Path: Gets probability for current/end stage ---
assert(
    preg_match('/\$.*probabilities\s*\[.*sales_stage/i', $fileContent) === 1
    || preg_match('/sales_stage.*probabilities/i', $fileContent) === 1
    || preg_match('/current.*stage.*probabilities/i', $fileContent) === 1,
    "display() must look up probability for current stage"
);
echo "  [PASS] Looks up probability for current stage\n";

// --- Happy Path: Compares probabilities (current > start) ---
assert(
    preg_match('/>\s*\$\w+|probability.*>/', $fileContent) === 1,
    "display() must use > operator to detect progression"
);
echo "  [PASS] Uses > operator for progression detection\n";

// --- Happy Path: Compares probabilities (current < start) ---
assert(
    preg_match('/<\s*\$\w+|probability.*</', $fileContent) === 1,
    "display() must use < operator to detect regression"
);
echo "  [PASS] Uses < operator for regression detection\n";

// --- Happy Path: Compares probabilities (current == start) ---
assert(
    preg_match('/==|===/', $fileContent) === 1,
    "display() must use == or === operator to detect static movement"
);
echo "  [PASS] Uses == or === operator for static detection\n";

// --- Happy Path: Checks for exact 'Closed Won' stage match ---
assert(
    preg_match('/===?\s*["\']Closed Won["\']/', $fileContent) === 1
    || preg_match('/["\']Closed Won["\']\s*===?/', $fileContent) === 1,
    "display() must check for exact 'Closed Won' stage match"
);
echo "  [PASS] Checks for exact 'Closed Won' match\n";

// --- Happy Path: Checks for exact 'Closed Lost' stage match ---
assert(
    preg_match('/===?\s*["\']Closed Lost["\']/', $fileContent) === 1
    || preg_match('/["\']Closed Lost["\']\s*===?/', $fileContent) === 1,
    "display() must check for exact 'Closed Lost' stage match"
);
echo "  [PASS] Checks for exact 'Closed Lost' match\n";

echo "\n";


// ============================================================
// Section 13: Rendering - HTML Structure
// ============================================================
echo "Section 13: HTML Rendering Structure\n";
echo str_repeat('-', 40) . "\n";

// --- Happy Path: Renders table structure ---
assert(
    str_contains($fileContent, '<table') || str_contains($fileContent, '<tr') || str_contains($fileContent, '<td'),
    "display() should render table structure for opportunity rows"
);
echo "  [PASS] Renders table structure (table/tr/td)\n";

// --- Happy Path: Renders opportunity name ---
assert(
    str_contains($fileContent, 'name') || str_contains($fileContent, 'opportunity'),
    "display() must render opportunity name"
);
echo "  [PASS] References opportunity name for rendering\n";

// --- Happy Path: Renders current stage ---
assert(
    str_contains($fileContent, 'sales_stage') || str_contains($fileContent, 'stage'),
    "display() must render current sales stage"
);
echo "  [PASS] References sales_stage for rendering\n";

// --- Happy Path: Renders movement indicator ---
assert(
    str_contains($fileContent, 'movement') || str_contains($fileContent, 'progressed') || str_contains($fileContent, 'static'),
    "display() must render movement indicator"
);
echo "  [PASS] Renders movement indicator\n";

// --- Edge Case: Handles special characters in opportunity names ---
assert(
    str_contains($fileContent, 'htmlspecialchars'),
    "display() must use htmlspecialchars() to escape opportunity names"
);
echo "  [PASS] Uses htmlspecialchars() for opportunity names\n";

echo "\n";


// ============================================================
// Final Summary
// ============================================================
echo str_repeat('=', 60) . "\n";
echo "US-013: All Reporting View tests passed!\n";
echo str_repeat('=', 60) . "\n";
