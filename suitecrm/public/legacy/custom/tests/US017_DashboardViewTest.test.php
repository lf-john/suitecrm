<?php
/**
 * US-017: Dashboard View - Base view with Commitment Review
 *
 * Tests that custom/modules/LF_WeeklyReport/views/view.dashboard.php exists and:
 *
 *   1. Extends SugarView with class LF_WeeklyReportViewDashboard
 *   2. Renders title bar: 'Weekly Reporting Dashboard'
 *   3. Renders Team View / Rep View toggle buttons (Team View active by default)
 *   4. Renders Rep dropdown (hidden in Team View) from LF_RepTargets::getActiveReps()
 *   5. Renders week selector: Back, Next, Current Week buttons + dropdown from WeekHelper::getWeekList(12)
 *   6. Gathers ALL dashboard data server-side:
 *      - Config values (stage probabilities, achievement thresholds, colors)
 *      - Active reps with targets
 *      - Current week info
 *      - Week list
 *      - Commitment data (overall achievement rate, per-rep data, aggregates)
 *   7. Injects all data as single JSON via: window.LF_DASHBOARD_DATA = json_encode(...)
 *   8. Includes external CSS: custom/themes/lf_dashboard.css
 *   9. Includes external JS: custom/modules/LF_WeeklyReport/js/dashboard.js
 *  10. Uses brand colors: #125EAD (blue), #4BB74E (green)
 *  11. Inherits SuiteCRM header, navigation, and footer
 *  12. Renders Commitment Review column as the first column
 *  13. Uses echo HTML (NO Smarty templates, NO .tpl files)
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
    . DIRECTORY_SEPARATOR . 'LF_WeeklyReport'
    . DIRECTORY_SEPARATOR . 'views'
    . DIRECTORY_SEPARATOR . 'view.dashboard.php';


// ============================================================
// Section 1: View File Exists
// ============================================================
echo "Section 1: View File Exists\n";

test_assert(
    file_exists($viewFile),
    "Dashboard view file must exist at custom/modules/LF_WeeklyReport/views/view.dashboard.php"
);

test_assert(
    file_exists($viewFile) && is_file($viewFile),
    "Dashboard view file must be a regular file (not a directory)"
);

// If file does not exist, remaining tests cannot proceed - exit early
if (!file_exists($viewFile)) {
    echo "\n" . str_repeat('=', 60) . "\n";
    echo "SUMMARY (early exit - file not found)\n";
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

$viewContent = file_get_contents($viewFile);


// ============================================================
// Section 2: PHP Format and sugarEntry Guard
// ============================================================
echo "\nSection 2: PHP Format and sugarEntry Guard\n";

test_assert(
    str_starts_with(trim($viewContent), '<?php'),
    "View file must start with <?php"
);

test_assert(
    str_contains($viewContent, "sugarEntry"),
    "View must contain sugarEntry guard"
);

test_assert(
    str_contains($viewContent, "die('Not A Valid Entry Point')") || str_contains($viewContent, 'die("Not A Valid Entry Point")'),
    "View must die with 'Not A Valid Entry Point' message"
);


// ============================================================
// Section 3: Class Definition and SugarView Extension
// ============================================================
echo "\nSection 3: Class Definition\n";

test_assert(
    str_contains($viewContent, 'class LF_WeeklyReportViewDashboard'),
    "Class must be named LF_WeeklyReportViewDashboard"
);

test_assert(
    preg_match('/class\s+LF_WeeklyReportViewDashboard\s+extends\s+SugarView/', $viewContent) === 1,
    "Class must extend SugarView (class LF_WeeklyReportViewDashboard extends SugarView)"
);

test_assert(
    str_contains($viewContent, '#[\\AllowDynamicProperties]') || str_contains($viewContent, '#[\AllowDynamicProperties]'),
    "Class must have #[\\AllowDynamicProperties] attribute for PHP 8.x compatibility"
);

test_assert(
    str_contains($viewContent, "require_once") && str_contains($viewContent, "SugarView.php"),
    "View must require_once SugarView.php"
);

// Negative: must NOT be named something else
test_assert(
    !str_contains($viewContent, 'class LF_WeeklyReportViewReporting'),
    "Class must NOT be named LF_WeeklyReportViewReporting (that is the reporting tool view)"
);


// ============================================================
// Section 4: SuiteCRM Header/Footer Inheritance
// ============================================================
echo "\nSection 4: SuiteCRM Header/Footer Inheritance\n";

test_assert(
    str_contains($viewContent, "show_header") && str_contains($viewContent, "true"),
    "View must set show_header = true to inherit SuiteCRM header/navigation"
);

test_assert(
    str_contains($viewContent, "show_footer") && str_contains($viewContent, "true"),
    "View must set show_footer = true to inherit SuiteCRM footer"
);

test_assert(
    str_contains($viewContent, 'function display()'),
    "View must implement display() method"
);


// ============================================================
// Section 5: Constructor Pattern
// ============================================================
echo "\nSection 5: Constructor Pattern\n";

test_assert(
    str_contains($viewContent, 'function __construct'),
    "View must define a __construct() method"
);

test_assert(
    str_contains($viewContent, 'parent::__construct()'),
    "Constructor must call parent::__construct()"
);

test_assert(
    str_contains($viewContent, '$this->options'),
    "Constructor must set options via \$this->options array"
);


// ============================================================
// Section 6: Title Bar - 'Weekly Reporting Dashboard'
// ============================================================
echo "\nSection 6: Title Bar\n";

test_assert(
    str_contains($viewContent, 'Weekly Reporting Dashboard'),
    "View must render title bar with text 'Weekly Reporting Dashboard'"
);


// ============================================================
// Section 7: Team View / Rep View Toggle Buttons
// ============================================================
echo "\nSection 7: Team View / Rep View Toggle\n";

test_assert(
    str_contains($viewContent, 'Team View'),
    "View must render a 'Team View' toggle button"
);

test_assert(
    str_contains($viewContent, 'Rep View'),
    "View must render a 'Rep View' toggle button"
);

// Team View must be active by default
test_assert(
    preg_match('/team.?view/i', $viewContent) === 1 &&
    (str_contains($viewContent, 'active') || str_contains($viewContent, 'selected') || str_contains($viewContent, 'btn-primary')),
    "Team View must be active by default (active class or selected state)"
);

test_assert(
    (str_contains($viewContent, '<button') || str_contains($viewContent, 'type="button"')) &&
    str_contains($viewContent, 'Team View') && str_contains($viewContent, 'Rep View'),
    "Toggle buttons must be clickable button elements"
);

test_assert(
    (str_contains($viewContent, 'team-view') || str_contains($viewContent, 'teamView') ||
     str_contains($viewContent, 'team_view') || str_contains($viewContent, 'btn-team')) &&
    (str_contains($viewContent, 'rep-view') || str_contains($viewContent, 'repView') ||
     str_contains($viewContent, 'rep_view') || str_contains($viewContent, 'btn-rep')),
    "Toggle buttons must have identifiable IDs/classes for JS interaction (team-view, rep-view)"
);


// ============================================================
// Section 8: Rep Dropdown
// ============================================================
echo "\nSection 8: Rep Dropdown\n";

test_assert(
    str_contains($viewContent, '<select') &&
    (str_contains($viewContent, 'rep-select') || str_contains($viewContent, 'rep_select') ||
     str_contains($viewContent, 'rep-dropdown') || str_contains($viewContent, 'rep_dropdown')),
    "View must render a Rep dropdown (<select>) with identifiable ID/class"
);

test_assert(
    str_contains($viewContent, 'getActiveReps'),
    "Rep dropdown must be populated from LF_RepTargets::getActiveReps()"
);

test_assert(
    str_contains($viewContent, 'LF_RepTargets'),
    "View must reference LF_RepTargets module for active reps"
);

test_assert(
    (str_contains($viewContent, 'display:none') || str_contains($viewContent, 'display: none') ||
     str_contains($viewContent, "display:'none'") || str_contains($viewContent, 'style="display:none"') ||
     str_contains($viewContent, "style=\"display: none\"") || str_contains($viewContent, 'hidden') ||
     str_contains($viewContent, 'lf-hidden')),
    "Rep dropdown must be hidden by default (Team View mode)"
);

test_assert(
    str_contains($viewContent, '<option') && str_contains($viewContent, 'getActiveReps'),
    "Rep dropdown must render <option> elements from active reps data"
);


// ============================================================
// Section 9: Week Selector - Navigation Buttons
// ============================================================
echo "\nSection 9: Week Selector - Navigation Buttons\n";

test_assert(
    preg_match('/Back|Previous|Prev|&laquo;|&#9664;|week-back|week-prev/i', $viewContent) === 1,
    "Week selector must have a Back/Previous button"
);

test_assert(
    preg_match('/Next|Forward|&raquo;|&#9654;|week-next|week-forward/i', $viewContent) === 1,
    "Week selector must have a Next button"
);

test_assert(
    str_contains($viewContent, 'Current Week') || str_contains($viewContent, 'current-week') ||
    str_contains($viewContent, 'Today') || str_contains($viewContent, 'week-current'),
    "Week selector must have a 'Current Week' button"
);

$buttonCount = preg_match_all('/<button[^>]*>/', $viewContent);
test_assert(
    $buttonCount >= 3,
    "Week selector must have at least 3 button elements (Back, Next, Current Week), found: {$buttonCount}"
);


// ============================================================
// Section 10: Week Selector - Week Dropdown
// ============================================================
echo "\nSection 10: Week Selector - Week Dropdown\n";

test_assert(
    str_contains($viewContent, 'getWeekList'),
    "Week selector must call WeekHelper::getWeekList() to populate dropdown"
);

test_assert(
    str_contains($viewContent, 'WeekHelper'),
    "View must reference WeekHelper class for week operations"
);

test_assert(
    preg_match('/getWeekList\s*\(\s*12\s*[\),]/', $viewContent) === 1,
    "getWeekList must be called with count parameter of exactly 12"
);

test_assert(
    str_contains($viewContent, '<select') &&
    (str_contains($viewContent, 'week-select') || str_contains($viewContent, 'week_select') ||
     str_contains($viewContent, 'week-dropdown') || str_contains($viewContent, 'weekStart')),
    "Week dropdown must be a <select> element with identifiable ID/class"
);

test_assert(
    str_contains($viewContent, 'isCurrent') || str_contains($viewContent, 'is_current'),
    "Week dropdown must check isCurrent flag to highlight/select current week"
);

test_assert(
    str_contains($viewContent, 'selected'),
    "Week dropdown must mark the current week as 'selected' in the dropdown"
);

test_assert(
    str_contains($viewContent, "['label']") || str_contains($viewContent, '["label"]') ||
    str_contains($viewContent, "->label") || str_contains($viewContent, "['weekStart']") ||
    str_contains($viewContent, '["weekStart"]'),
    "Week dropdown options must use label or weekStart from getWeekList() results"
);


// ============================================================
// Section 11: Data Gathering - Config Values
// ============================================================
echo "\nSection 11: Data Gathering - Config Values\n";

test_assert(
    str_contains($viewContent, 'LF_PRConfig'),
    "View must reference LF_PRConfig for configuration values"
);

test_assert(
    str_contains($viewContent, 'getConfig') || str_contains($viewContent, 'getConfigJson') || str_contains($viewContent, 'getAll'),
    "View must call LF_PRConfig methods to retrieve config values"
);

test_assert(
    str_contains($viewContent, 'achievement') || str_contains($viewContent, 'tier') || str_contains($viewContent, 'threshold'),
    "View must read achievement threshold configuration for color coding"
);

test_assert(
    str_contains($viewContent, 'stage_probabilities') || str_contains($viewContent, 'stageProbabilities'),
    "View must read stage probabilities configuration"
);


// ============================================================
// Section 12: Data Gathering - Active Reps with Targets
// ============================================================
echo "\nSection 12: Data Gathering - Active Reps with Targets\n";

test_assert(
    str_contains($viewContent, 'getActiveReps'),
    "View must call LF_RepTargets::getActiveReps() for active rep data"
);

test_assert(
    str_contains($viewContent, 'getTargetsForYear') || str_contains($viewContent, 'rep_targets') ||
    str_contains($viewContent, 'annual_quota') || str_contains($viewContent, 'repTargets'),
    "View must gather rep target data (annual quotas)"
);


// ============================================================
// Section 13: Data Gathering - Current Week Info
// ============================================================
echo "\nSection 13: Data Gathering - Current Week Info\n";

test_assert(
    str_contains($viewContent, 'getCurrentWeekStart') || str_contains($viewContent, 'getWeekStart'),
    "View must gather current week start info via WeekHelper"
);

test_assert(
    str_contains($viewContent, 'formatWeekRange') || str_contains($viewContent, 'getWeekEnd') ||
    str_contains($viewContent, 'weekStart'),
    "View must gather week range/end info for display"
);


// ============================================================
// Section 14: Data Gathering - Commitment Data
// ============================================================
echo "\nSection 14: Data Gathering - Commitment Data\n";

test_assert(
    str_contains($viewContent, 'commitmentData') || str_contains($viewContent, 'commitment_data') || str_contains($viewContent, 'gatherCommitmentData'),
    "View must gather commitment review data server-side"
);

test_assert(
    str_contains($viewContent, 'overall_achievement_rate') || str_contains($viewContent, 'overallAchievementRate') || str_contains($viewContent, 'aggregate'),
    "View must calculate overall achievement rate as aggregate across all reps"
);

test_assert(
    str_contains($viewContent, 'rep_data') || str_contains($viewContent, 'repData') || str_contains($viewContent, 'per.?rep'),
    "View must gather per-rep data for commitment review"
);

test_assert(
    str_contains($viewContent, 'achieved') || str_contains($viewContent, 'missed') || str_contains($viewContent, 'unplanned'),
    "View must categorize plan items as achieved, missed, or unplanned successes"
);

test_assert(
    str_contains($viewContent, 'new_pipeline') || str_contains($viewContent, 'newPipeline') || str_contains($viewContent, 'progression'),
    "View must calculate new pipeline and progression actual vs planned values"
);


// ============================================================
// Section 15: JSON Data Injection - window.LF_DASHBOARD_DATA
// ============================================================
echo "\nSection 15: JSON Data Injection\n";

test_assert(
    str_contains($viewContent, 'LF_DASHBOARD_DATA'),
    "View must inject data as window.LF_DASHBOARD_DATA"
);

test_assert(
    str_contains($viewContent, 'window.LF_DASHBOARD_DATA'),
    "View must use window.LF_DASHBOARD_DATA (not just LF_DASHBOARD_DATA)"
);

test_assert(
    str_contains($viewContent, 'json_encode'),
    "View must use json_encode() to serialize data to JSON"
);

test_assert(
    str_contains($viewContent, '<script>') && str_contains($viewContent, 'LF_DASHBOARD_DATA') &&
    str_contains($viewContent, 'json_encode'),
    "Data must be injected via <script> tag with json_encode"
);

test_assert(
    preg_match('/window\.LF_DASHBOARD_DATA\s*=\s*/', $viewContent) === 1,
    "View must assign to window.LF_DASHBOARD_DATA (assignment pattern)"
);

test_assert(
    str_contains($viewContent, "'commitmentData'") || str_contains($viewContent, '"commitmentData"') ||
    str_contains($viewContent, "'commitment_data'") || str_contains($viewContent, '"commitment_data"'),
    "JSON data must include commitmentData key"
);

test_assert(
    (str_contains($viewContent, "'config'") || str_contains($viewContent, '"config"')),
    "JSON data must include config key"
);

test_assert(
    (str_contains($viewContent, "'reps'") || str_contains($viewContent, '"reps"')),
    "JSON data must include reps key"
);

test_assert(
    (str_contains($viewContent, "'repTargets'") || str_contains($viewContent, '"repTargets"') ||
     str_contains($viewContent, "'rep_targets'") || str_contains($viewContent, '"rep_targets"')),
    "JSON data must include repTargets key"
);

test_assert(
    (str_contains($viewContent, "'weekInfo'") || str_contains($viewContent, '"weekInfo"') ||
     str_contains($viewContent, "'week_info'") || str_contains($viewContent, '"week_info"')),
    "JSON data must include weekInfo key"
);

test_assert(
    (str_contains($viewContent, "'weekList'") || str_contains($viewContent, '"weekList"') ||
     str_contains($viewContent, "'week_list'") || str_contains($viewContent, '"week_list"')),
    "JSON data must include weekList key"
);


// ============================================================
// Section 16: External CSS Include
// ============================================================
echo "\nSection 16: External CSS Include\n";

test_assert(
    str_contains($viewContent, '<link') && str_contains($viewContent, 'lf_dashboard.css'),
    "View must include external CSS via <link> tag to custom/themes/lf_dashboard.css"
);

test_assert(
    str_contains($viewContent, 'custom/themes/lf_dashboard.css'),
    "CSS path must be custom/themes/lf_dashboard.css"
);

test_assert(
    str_contains($viewContent, 'rel="stylesheet"') || str_contains($viewContent, "rel='stylesheet'"),
    "CSS link tag must use rel=\"stylesheet\" attribute"
);


// ============================================================
// Section 17: External JS Include
// ============================================================
echo "\nSection 17: External JS Include\n";

test_assert(
    str_contains($viewContent, '<script') && str_contains($viewContent, 'dashboard.js'),
    "View must include external JS via <script> tag to dashboard.js"
);

test_assert(
    str_contains($viewContent, 'custom/modules/LF_WeeklyReport/js/dashboard.js'),
    "JS path must be custom/modules/LF_WeeklyReport/js/dashboard.js"
);

test_assert(
    str_contains($viewContent, 'src="custom/modules/LF_WeeklyReport/js/dashboard.js"') ||
    str_contains($viewContent, "src='custom/modules/LF_WeeklyReport/js/dashboard.js'"),
    "JS must be included via <script src=\"...\"> attribute"
);


// ============================================================
// Section 18: Brand Colors
// ============================================================
echo "\nSection 18: Brand Colors\n";

test_assert(
    str_contains($viewContent, '#125EAD') || str_contains($viewContent, '#125ead'),
    "View must reference Logical Front brand blue color #125EAD"
);

test_assert(
    str_contains($viewContent, '#4BB74E') || str_contains($viewContent, '#4bb74e'),
    "View must reference Logical Front brand green color #4BB74E"
);

test_assert(
    str_contains($viewContent, '#2F7D32') || str_contains($viewContent, '#2f7d32'),
    "View must reference achievement green color #2F7D32"
);

test_assert(
    str_contains($viewContent, '#E6C300') || str_contains($viewContent, '#e6c300'),
    "View must reference achievement yellow color #E6C300"
);

test_assert(
    str_contains($viewContent, '#ff8c00') || str_contains($viewContent, '#FF8C00'),
    "View must reference achievement orange color #ff8c00"
);

test_assert(
    str_contains($viewContent, '#d13438') || str_contains($viewContent, '#D13438'),
    "View must reference achievement red color #d13438"
);


// ============================================================
// Section 19: Required PHP Includes
// ============================================================
echo "\nSection 19: Required PHP Includes\n";

test_assert(
    str_contains($viewContent, 'require_once') && str_contains($viewContent, 'WeekHelper.php'),
    "View must require_once WeekHelper.php"
);

test_assert(
    str_contains($viewContent, 'require_once') && str_contains($viewContent, 'LF_PRConfig'),
    "View must require_once LF_PRConfig module"
);

test_assert(
    str_contains($viewContent, 'require_once') && str_contains($viewContent, 'LF_RepTargets'),
    "View must require_once LF_RepTargets module"
);

test_assert(
    str_contains($viewContent, 'require_once') && str_contains($viewContent, 'LF_WeeklyReport'),
    "View must require_once LF_WeeklyReport bean"
);

test_assert(
    str_contains($viewContent, 'require_once') && str_contains($viewContent, 'LF_ReportSnapshot'),
    "View must require_once LF_ReportSnapshot bean"
);

test_assert(
    str_contains($viewContent, 'require_once') && str_contains($viewContent, 'LF_WeeklyPlan'),
    "View must require_once LF_WeeklyPlan bean"
);


// ============================================================
// Section 20: Security - Output Escaping
// ============================================================
echo "\nSection 20: Security - Output Escaping\n";

$htmlEscapeCount = substr_count($viewContent, 'htmlspecialchars(');
test_assert(
    $htmlEscapeCount >= 5,
    "View must use htmlspecialchars() at least 5 times for output escaping, found: {$htmlEscapeCount}"
);

test_assert(
    str_contains($viewContent, 'JSON_HEX_TAG') || str_contains($viewContent, 'json_encode('),
    "View should use json_encode() for safe data injection (JSON_HEX_TAG recommended)"
);


// ============================================================
// Section 21: Echo-based HTML Output (No Smarty)
// ============================================================
echo "\nSection 21: Echo-based HTML Output\n";

$echoCount = preg_match_all('/\becho\s/', $viewContent);
test_assert(
    $echoCount >= 15,
    "View must have at least 15 echo statements for HTML output (NO Smarty), found: {$echoCount}"
);

test_assert(
    !str_contains($viewContent, '.tpl') && !str_contains($viewContent, 'smarty') && !str_contains($viewContent, 'Smarty'),
    "View must NOT reference Smarty templates (.tpl files)"
);

test_assert(
    !str_contains($viewContent, '$this->ss'),
    "View must NOT use \$this->ss (SugarSmarty engine)"
);


// ============================================================
// Section 22: Global Variables Usage
// ============================================================
echo "\nSection 22: Global Variables\n";

test_assert(
    str_contains($viewContent, 'global') && str_contains($viewContent, '$db'),
    "View must use global \$db for database access"
);

test_assert(
    str_contains($viewContent, 'global') && str_contains($viewContent, '$current_user'),
    "View must use global \$current_user for current user context"
);


// ============================================================
// Section 23: Week Parameter Handling
// ============================================================
echo "\nSection 23: Week Parameter Handling\n";

test_assert(
    str_contains($viewContent, '$_REQUEST') || str_contains($viewContent, '$_GET') ||
    str_contains($viewContent, 'sugar_clean_string') || str_contains($viewContent, 'week_start'),
    "View must read a week parameter from request for week navigation"
);


// ============================================================
// Section 24: Container Structure
// ============================================================
echo "\nSection 24: Container Structure\n";

test_assert(
    str_contains($viewContent, 'lf-dashboard') || str_contains($viewContent, 'dashboard-container') ||
    str_contains($viewContent, 'lf_dashboard'),
    "View must have a dashboard container element with identifiable class/ID"
);


// ============================================================
// Section 25: Commitment Review Column
// ============================================================
echo "\nSection 25: Commitment Review Column\n";

test_assert(
    str_contains($viewContent, 'commitment-review') || str_contains($viewContent, 'commitment_review') ||
    str_contains($viewContent, 'Commitment Review'),
    "View must render a 'Commitment Review' column as the first column"
);

test_assert(
    preg_match('/<div[^>]*id=["\'].*commitment.*["\'][^>]*>/i', $viewContent) === 1,
    "View must have a container div for Commitment Review content"
);


// ============================================================
// Section 26: Negative Cases - Wrong Patterns
// ============================================================
echo "\nSection 26: Negative Cases\n";

$lfWindowVarCount = preg_match_all('/window\.LF_(?!DASHBOARD_DATA)/', $viewContent);
test_assert(
    $lfWindowVarCount === 0,
    "View must NOT inject multiple separate window.LF_ variables - use single LF_DASHBOARD_DATA object, found {$lfWindowVarCount} extra"
);

test_assert(
    !str_contains($viewContent, 'document.write'),
    "View must NOT use document.write() for output"
);

$scriptTagCount = preg_match_all('/<script[^>]*>/', $viewContent);
test_assert(
    $scriptTagCount <= 3,
    "View should have at most 3 script tags (data injection + external JS include + optional CSRF), found: {$scriptTagCount}"
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
