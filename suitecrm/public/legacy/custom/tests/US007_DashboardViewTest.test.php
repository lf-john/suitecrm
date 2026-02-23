<?php
/**
 * US-007: Dashboard View - Base view with data gathering
 *
 * Tests that custom/modules/LF_WeeklyPlan/views/view.dashboard.php exists and:
 *
 *   1. Extends SugarView with class LF_WeeklyPlanViewDashboard
 *   2. Renders title bar: 'Weekly Planning Dashboard'
 *   3. Renders Team View / Rep View toggle buttons (Team View active by default)
 *   4. Renders Rep dropdown (hidden in Team View) from LF_RepTargets::getActiveReps()
 *   5. Renders week selector: Back, Next, Current Week buttons + dropdown from WeekHelper::getWeekList(12)
 *   6. Gathers ALL dashboard data server-side:
 *      - Config values
 *      - Active reps with targets
 *      - Current week info
 *      - Pipeline by stage (OpportunityQuery::getPipelineByStage())
 *      - Pipeline by rep (OpportunityQuery::getPipelineByRep())
 *      - Stale deals (OpportunityQuery::getStaleDeals())
 *      - Plan items for selected week
 *      - Rep targets
 *   7. Injects all data as single JSON via: window.LF_DASHBOARD_DATA = json_encode(...)
 *   8. Includes external CSS: custom/themes/lf_dashboard.css
 *   9. Includes external JS: custom/modules/LF_WeeklyPlan/js/dashboard.js
 *  10. Uses brand colors: #125EAD (blue), #4BB74E (green)
 *  11. Inherits SuiteCRM header, navigation, and footer
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
    . DIRECTORY_SEPARATOR . 'LF_WeeklyPlan'
    . DIRECTORY_SEPARATOR . 'views'
    . DIRECTORY_SEPARATOR . 'view.dashboard.php';


// ============================================================
// Section 1: View File Exists
// ============================================================
echo "Section 1: View File Exists\n";

test_assert(
    file_exists($viewFile),
    "Dashboard view file must exist at custom/modules/LF_WeeklyPlan/views/view.dashboard.php"
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
    str_contains($viewContent, 'class LF_WeeklyPlanViewDashboard'),
    "Class must be named LF_WeeklyPlanViewDashboard"
);

test_assert(
    preg_match('/class\s+LF_WeeklyPlanViewDashboard\s+extends\s+SugarView/', $viewContent) === 1,
    "Class must extend SugarView (class LF_WeeklyPlanViewDashboard extends SugarView)"
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
    !str_contains($viewContent, 'class LF_WeeklyPlanViewPlanning'),
    "Class must NOT be named LF_WeeklyPlanViewPlanning (that is the planning tool view)"
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

// show_header and show_footer should be set in __construct via $this->options
test_assert(
    str_contains($viewContent, '$this->options'),
    "Constructor must set options via \$this->options array"
);


// ============================================================
// Section 6: Title Bar - 'Weekly Planning Dashboard'
// ============================================================
echo "\nSection 6: Title Bar\n";

test_assert(
    str_contains($viewContent, 'Weekly Planning Dashboard'),
    "View must render title bar with text 'Weekly Planning Dashboard'"
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

// Both toggle buttons must be clickable button elements
test_assert(
    (str_contains($viewContent, '<button') || str_contains($viewContent, 'type="button"')) &&
    str_contains($viewContent, 'Team View') && str_contains($viewContent, 'Rep View'),
    "Toggle buttons must be clickable button elements"
);

// Toggle buttons should have identifiable IDs or data attributes for JS interaction
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

// Rep dropdown must exist
test_assert(
    str_contains($viewContent, '<select') &&
    (str_contains($viewContent, 'rep-select') || str_contains($viewContent, 'rep_select') ||
     str_contains($viewContent, 'rep-dropdown') || str_contains($viewContent, 'rep_dropdown')),
    "View must render a Rep dropdown (<select>) with identifiable ID/class"
);

// Rep dropdown must be populated from LF_RepTargets::getActiveReps()
test_assert(
    str_contains($viewContent, 'getActiveReps'),
    "Rep dropdown must be populated from LF_RepTargets::getActiveReps()"
);

test_assert(
    str_contains($viewContent, 'LF_RepTargets'),
    "View must reference LF_RepTargets module for active reps"
);

// Rep dropdown must be hidden in Team View mode (default)
test_assert(
    (str_contains($viewContent, 'display:none') || str_contains($viewContent, 'display: none') ||
     str_contains($viewContent, "display:'none'") || str_contains($viewContent, 'style="display:none"') ||
     str_contains($viewContent, "style=\"display: none\"") || str_contains($viewContent, 'hidden') ||
     str_contains($viewContent, 'lf-hidden')),
    "Rep dropdown must be hidden by default (Team View mode)"
);

// Rep dropdown should iterate over reps to create <option> elements
test_assert(
    str_contains($viewContent, '<option') && str_contains($viewContent, 'getActiveReps'),
    "Rep dropdown must render <option> elements from active reps data"
);


// ============================================================
// Section 9: Week Selector - Navigation Buttons
// ============================================================
echo "\nSection 9: Week Selector - Navigation Buttons\n";

// Back button
test_assert(
    preg_match('/Back|Previous|Prev|&laquo;|&#9664;|week-back|week-prev/i', $viewContent) === 1,
    "Week selector must have a Back/Previous button"
);

// Next button
test_assert(
    preg_match('/Next|Forward|&raquo;|&#9654;|week-next|week-forward/i', $viewContent) === 1,
    "Week selector must have a Next button"
);

// Current Week button
test_assert(
    str_contains($viewContent, 'Current Week') || str_contains($viewContent, 'current-week') ||
    str_contains($viewContent, 'Today') || str_contains($viewContent, 'week-current'),
    "Week selector must have a 'Current Week' button"
);

// All three navigation buttons should be button elements
$buttonCount = preg_match_all('/<button[^>]*>/', $viewContent);
test_assert(
    $buttonCount >= 3,
    "Week selector must have at least 3 button elements (Back, Next, Current Week), found: {$buttonCount}"
);


// ============================================================
// Section 10: Week Selector - Week Dropdown
// ============================================================
echo "\nSection 10: Week Selector - Week Dropdown\n";

// Must call WeekHelper::getWeekList(12) - specifically with count 12 for +/-12 weeks
test_assert(
    str_contains($viewContent, 'getWeekList'),
    "Week selector must call WeekHelper::getWeekList() to populate dropdown"
);

test_assert(
    str_contains($viewContent, 'WeekHelper'),
    "View must reference WeekHelper class for week operations"
);

// Must use count of 12 in getWeekList call (for +/-12 weeks = 25 weeks total)
test_assert(
    preg_match('/getWeekList\s*\(\s*12\s*[\),]/', $viewContent) === 1,
    "getWeekList must be called with count parameter of exactly 12"
);

// Week dropdown must be a <select> element
test_assert(
    str_contains($viewContent, '<select') &&
    (str_contains($viewContent, 'week-select') || str_contains($viewContent, 'week_select') ||
     str_contains($viewContent, 'week-dropdown') || str_contains($viewContent, 'weekStart')),
    "Week dropdown must be a <select> element with identifiable ID/class"
);

// Current week must be highlighted (selected) in dropdown
test_assert(
    str_contains($viewContent, 'isCurrent') || str_contains($viewContent, 'is_current'),
    "Week dropdown must check isCurrent flag to highlight/select current week"
);

test_assert(
    str_contains($viewContent, 'selected'),
    "Week dropdown must mark the current week as 'selected' in the dropdown"
);

// Week list items should use label for display text
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

// Config should include stale days for getStaleDeals parameter
test_assert(
    str_contains($viewContent, 'stale') || str_contains($viewContent, 'STALE') ||
    str_contains($viewContent, 'staleDays') || str_contains($viewContent, 'stale_days'),
    "View must read stale days configuration for getStaleDeals() parameter"
);


// ============================================================
// Section 12: Data Gathering - Active Reps with Targets
// ============================================================
echo "\nSection 12: Data Gathering - Active Reps with Targets\n";

test_assert(
    str_contains($viewContent, 'getActiveReps'),
    "View must call LF_RepTargets::getActiveReps() for active rep data"
);

// Rep targets data must be gathered
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
// Section 14: Data Gathering - Pipeline Data
// ============================================================
echo "\nSection 14: Data Gathering - Pipeline Data\n";

// Pipeline by stage
test_assert(
    str_contains($viewContent, 'getPipelineByStage'),
    "View must call OpportunityQuery::getPipelineByStage() for pipeline by stage data"
);

// Pipeline by rep
test_assert(
    str_contains($viewContent, 'getPipelineByRep'),
    "View must call OpportunityQuery::getPipelineByRep() for pipeline by rep data"
);

test_assert(
    str_contains($viewContent, 'OpportunityQuery'),
    "View must reference OpportunityQuery class for pipeline data"
);

// Pipeline calls must use OpportunityQuery:: static call pattern
test_assert(
    str_contains($viewContent, 'OpportunityQuery::getPipelineByStage') &&
    str_contains($viewContent, 'OpportunityQuery::getPipelineByRep'),
    "Pipeline data must be gathered via OpportunityQuery:: static method calls"
);


// ============================================================
// Section 15: Data Gathering - Stale Deals
// ============================================================
echo "\nSection 15: Data Gathering - Stale Deals\n";

test_assert(
    str_contains($viewContent, 'getStaleDeals'),
    "View must call OpportunityQuery::getStaleDeals() for stale deal data"
);

test_assert(
    str_contains($viewContent, 'OpportunityQuery::getStaleDeals'),
    "Stale deals must be gathered via OpportunityQuery::getStaleDeals() static call"
);


// ============================================================
// Section 16: Data Gathering - Plan Items for Selected Week
// ============================================================
echo "\nSection 16: Data Gathering - Plan Items\n";

test_assert(
    str_contains($viewContent, 'lf_weekly_plan') || str_contains($viewContent, 'LF_WeeklyPlan') ||
    str_contains($viewContent, 'plan_items') || str_contains($viewContent, 'planItems'),
    "View must gather plan items for the selected week"
);

// Plan items should reference plan_op_items or plan_prospect_items tables
test_assert(
    str_contains($viewContent, 'lf_plan_op_items') || str_contains($viewContent, 'lf_plan_prospect_items') ||
    str_contains($viewContent, 'planItems') || str_contains($viewContent, 'plan_items'),
    "View must query plan items (op_items or prospect_items) for the selected week"
);


// ============================================================
// Section 17: Data Gathering - Closed YTD
// ============================================================
echo "\nSection 17: Data Gathering - Closed YTD\n";

test_assert(
    str_contains($viewContent, 'getClosedYTD') || str_contains($viewContent, 'closedYtd') ||
    str_contains($viewContent, 'closed_ytd'),
    "View must gather Closed YTD data via OpportunityQuery::getClosedYTD() or include it in data"
);


// ============================================================
// Section 18: JSON Data Injection - window.LF_DASHBOARD_DATA
// ============================================================
echo "\nSection 18: JSON Data Injection\n";

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

// Must inject via <script> tag
test_assert(
    str_contains($viewContent, '<script>') && str_contains($viewContent, 'LF_DASHBOARD_DATA') &&
    str_contains($viewContent, 'json_encode'),
    "Data must be injected via <script> tag with json_encode"
);

// The story says "a single JSON object" - verify assignment pattern
test_assert(
    preg_match('/window\.LF_DASHBOARD_DATA\s*=\s*/', $viewContent) === 1,
    "View must assign to window.LF_DASHBOARD_DATA (assignment pattern)"
);

// Verify the specific pattern: echo '<script>window.LF_DASHBOARD_DATA = ' . json_encode($...) . ';</script>';
test_assert(
    str_contains($viewContent, "json_encode(") && str_contains($viewContent, 'LF_DASHBOARD_DATA'),
    "View must encode data with json_encode and assign to LF_DASHBOARD_DATA"
);

// The JSON object should contain all required data keys
// Check that the data array being encoded includes pipeline, reps, config, etc.
test_assert(
    (str_contains($viewContent, "'pipelineByStage'") || str_contains($viewContent, '"pipelineByStage"') ||
     str_contains($viewContent, "'pipeline_by_stage'") || str_contains($viewContent, '"pipeline_by_stage"')),
    "JSON data must include pipelineByStage key"
);

test_assert(
    (str_contains($viewContent, "'pipelineByRep'") || str_contains($viewContent, '"pipelineByRep"') ||
     str_contains($viewContent, "'pipeline_by_rep'") || str_contains($viewContent, '"pipeline_by_rep"')),
    "JSON data must include pipelineByRep key"
);

test_assert(
    (str_contains($viewContent, "'staleDeals'") || str_contains($viewContent, '"staleDeals"') ||
     str_contains($viewContent, "'stale_deals'") || str_contains($viewContent, '"stale_deals"')),
    "JSON data must include staleDeals key"
);

test_assert(
    (str_contains($viewContent, "'config'") || str_contains($viewContent, '"config"') ||
     str_contains($viewContent, "'configValues'") || str_contains($viewContent, '"configValues"') ||
     str_contains($viewContent, "'stageProbabilities'") || str_contains($viewContent, '"stageProbabilities"') ||
     str_contains($viewContent, "'stage_probabilities'") || str_contains($viewContent, '"stage_probabilities"')),
    "JSON data must include config/stage configuration keys"
);

test_assert(
    (str_contains($viewContent, "'reps'") || str_contains($viewContent, '"reps"') ||
     str_contains($viewContent, "'activeReps'") || str_contains($viewContent, '"activeReps"') ||
     str_contains($viewContent, "'active_reps'") || str_contains($viewContent, '"active_reps"')),
    "JSON data must include reps/activeReps key"
);

test_assert(
    (str_contains($viewContent, "'repTargets'") || str_contains($viewContent, '"repTargets"') ||
     str_contains($viewContent, "'rep_targets'") || str_contains($viewContent, '"rep_targets"') ||
     str_contains($viewContent, "'targets'") || str_contains($viewContent, '"targets"')),
    "JSON data must include repTargets/targets key"
);

test_assert(
    (str_contains($viewContent, "'weekInfo'") || str_contains($viewContent, '"weekInfo"') ||
     str_contains($viewContent, "'week_info'") || str_contains($viewContent, '"week_info"') ||
     str_contains($viewContent, "'currentWeek'") || str_contains($viewContent, '"currentWeek"') ||
     str_contains($viewContent, "'current_week'") || str_contains($viewContent, '"current_week"')),
    "JSON data must include weekInfo/currentWeek key"
);

test_assert(
    (str_contains($viewContent, "'planItems'") || str_contains($viewContent, '"planItems"') ||
     str_contains($viewContent, "'plan_items'") || str_contains($viewContent, '"plan_items"')),
    "JSON data must include planItems key"
);


// ============================================================
// Section 19: External CSS Include
// ============================================================
echo "\nSection 19: External CSS Include\n";

test_assert(
    str_contains($viewContent, '<link') && str_contains($viewContent, 'lf_dashboard.css'),
    "View must include external CSS via <link> tag to custom/themes/lf_dashboard.css"
);

test_assert(
    str_contains($viewContent, 'custom/themes/lf_dashboard.css'),
    "CSS path must be custom/themes/lf_dashboard.css"
);

// CSS link should use rel="stylesheet"
test_assert(
    str_contains($viewContent, 'rel="stylesheet"') || str_contains($viewContent, "rel='stylesheet'"),
    "CSS link tag must use rel=\"stylesheet\" attribute"
);


// ============================================================
// Section 20: External JS Include
// ============================================================
echo "\nSection 20: External JS Include\n";

test_assert(
    str_contains($viewContent, '<script') && str_contains($viewContent, 'dashboard.js'),
    "View must include external JS via <script> tag to dashboard.js"
);

test_assert(
    str_contains($viewContent, 'custom/modules/LF_WeeklyPlan/js/dashboard.js'),
    "JS path must be custom/modules/LF_WeeklyPlan/js/dashboard.js"
);

// JS should be included via src attribute on <script> tag
test_assert(
    str_contains($viewContent, 'src="custom/modules/LF_WeeklyPlan/js/dashboard.js"') ||
    str_contains($viewContent, "src='custom/modules/LF_WeeklyPlan/js/dashboard.js'"),
    "JS must be included via <script src=\"...\"> attribute"
);


// ============================================================
// Section 21: Brand Colors
// ============================================================
echo "\nSection 21: Brand Colors\n";

test_assert(
    str_contains($viewContent, '#125EAD') || str_contains($viewContent, '#125ead'),
    "View must reference Logical Front brand blue color #125EAD"
);

test_assert(
    str_contains($viewContent, '#4BB74E') || str_contains($viewContent, '#4bb74e'),
    "View must reference Logical Front brand green color #4BB74E"
);


// ============================================================
// Section 22: Required PHP Includes
// ============================================================
echo "\nSection 22: Required PHP Includes\n";

test_assert(
    str_contains($viewContent, 'require_once') && str_contains($viewContent, 'WeekHelper.php'),
    "View must require_once WeekHelper.php"
);

test_assert(
    str_contains($viewContent, 'require_once') && str_contains($viewContent, 'OpportunityQuery.php'),
    "View must require_once OpportunityQuery.php"
);

test_assert(
    str_contains($viewContent, 'require_once') && str_contains($viewContent, 'LF_PRConfig'),
    "View must require_once LF_PRConfig module"
);

test_assert(
    str_contains($viewContent, 'require_once') && str_contains($viewContent, 'LF_RepTargets'),
    "View must require_once LF_RepTargets module"
);

// Should also include LF_WeeklyPlan bean for plan item queries
test_assert(
    str_contains($viewContent, 'require_once') && str_contains($viewContent, 'LF_WeeklyPlan'),
    "View must require_once LF_WeeklyPlan bean for plan item queries"
);


// ============================================================
// Section 23: Security - Output Escaping
// ============================================================
echo "\nSection 23: Security - Output Escaping\n";

$htmlEscapeCount = substr_count($viewContent, 'htmlspecialchars(');
test_assert(
    $htmlEscapeCount >= 5,
    "View must use htmlspecialchars() at least 5 times for output escaping, found: {$htmlEscapeCount}"
);

// JSON_HEX_TAG should be used for safe JSON injection in script tags
test_assert(
    str_contains($viewContent, 'JSON_HEX_TAG') || str_contains($viewContent, 'json_encode('),
    "View should use json_encode() for safe data injection (JSON_HEX_TAG recommended)"
);


// ============================================================
// Section 24: Echo-based HTML Output (No Smarty)
// ============================================================
echo "\nSection 24: Echo-based HTML Output\n";

$echoCount = preg_match_all('/\becho\s/', $viewContent);
test_assert(
    $echoCount >= 20,
    "View must have at least 20 echo statements for HTML output (NO Smarty), found: {$echoCount}"
);

// Must NOT use Smarty templates
test_assert(
    !str_contains($viewContent, '.tpl') && !str_contains($viewContent, 'smarty') && !str_contains($viewContent, 'Smarty'),
    "View must NOT reference Smarty templates (.tpl files)"
);

// Must NOT use $this->ss (SugarSmarty)
test_assert(
    !str_contains($viewContent, '$this->ss'),
    "View must NOT use \$this->ss (SugarSmarty engine)"
);


// ============================================================
// Section 25: Global Variables Usage
// ============================================================
echo "\nSection 25: Global Variables\n";

test_assert(
    str_contains($viewContent, 'global') && str_contains($viewContent, '$db'),
    "View must use global \$db for database access"
);

test_assert(
    str_contains($viewContent, 'global') && str_contains($viewContent, '$current_user'),
    "View must use global \$current_user for current user context"
);


// ============================================================
// Section 26: Week Parameter Handling
// ============================================================
echo "\nSection 26: Week Parameter Handling\n";

// The view should accept a week parameter from the URL/request for week navigation
test_assert(
    str_contains($viewContent, '$_REQUEST') || str_contains($viewContent, '$_GET') ||
    str_contains($viewContent, 'sugar_clean_string') || str_contains($viewContent, 'week_start'),
    "View must read a week parameter from request for week navigation"
);


// ============================================================
// Section 27: Container Structure
// ============================================================
echo "\nSection 27: Container Structure\n";

// Dashboard should have a container div for JS targeting
test_assert(
    str_contains($viewContent, 'lf-dashboard') || str_contains($viewContent, 'dashboard-container') ||
    str_contains($viewContent, 'lf_dashboard'),
    "View must have a dashboard container element with identifiable class/ID"
);


// ============================================================
// Section 28: Negative Cases - Wrong Patterns
// ============================================================
echo "\nSection 28: Negative Cases\n";

// Must NOT have multiple separate window.LF_ variable injections (should be single JSON object)
$lfWindowVarCount = preg_match_all('/window\.LF_(?!DASHBOARD_DATA)/', $viewContent);
test_assert(
    $lfWindowVarCount === 0,
    "View must NOT inject multiple separate window.LF_ variables - use single LF_DASHBOARD_DATA object, found {$lfWindowVarCount} extra"
);

// Must NOT use document.write
test_assert(
    !str_contains($viewContent, 'document.write'),
    "View must NOT use document.write() for output"
);

// Must NOT have inline JavaScript logic (business logic should be in external dashboard.js)
// The only inline <script> should be the data injection
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
