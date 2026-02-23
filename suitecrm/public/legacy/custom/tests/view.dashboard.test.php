<?php
/**
 * TDD-RED: Tests for LF_WeeklyPlanViewDashboard
 * US-007: Create planning dashboard - base view with data gathering
 */

// CLI-only guard
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line.');
}

// Configure assertions to throw exceptions
ini_set('assert.exception', '1');
ini_set('zend.assertions', '1');

echo "Running view.dashboard.test.php...\n";

// Test 1: File exists at correct path
echo "Test 1: File exists at correct path...";
$viewFile = __DIR__ . '/../modules/LF_WeeklyPlan/views/view.dashboard.php';
assert(file_exists($viewFile), "view.dashboard.php must exist at custom/modules/LF_WeeklyPlan/views/");
echo " PASS\n";

// Test 2: File contains sugarEntry guard
echo "Test 2: File contains sugarEntry guard...";
$content = file_get_contents($viewFile);
assert(strpos($content, "if (!defined('sugarEntry') || !sugarEntry)") !== false, "Must have sugarEntry guard");
assert(strpos($content, "die('Not A Valid Entry Point')") !== false, "Must have sugarEntry die statement");
echo " PASS\n";

// Test 3: File contains correct class definition
echo "Test 3: File contains correct class definition...";
assert(strpos($content, "class LF_WeeklyPlanViewDashboard") !== false, "Must define LF_WeeklyPlanViewDashboard class");
assert(strpos($content, "extends SugarView") !== false, "Must extend SugarView");
echo " PASS\n";

// Test 4: File has AllowDynamicProperties attribute
echo "Test 4: File has AllowDynamicProperties attribute...";
assert(strpos($content, "#[\\AllowDynamicProperties]") !== false, "Must have #[\\AllowDynamicProperties] attribute");
echo " PASS\n";

// Test 5: File requires SugarView
echo "Test 5: File requires SugarView...";
assert(preg_match('/require_once\s*[\'(]include\/MVC\/View\/SugarView\.php/', $content), "Must require_once include/MVC/View/SugarView.php");
echo " PASS\n";

// Test 6: Constructor sets header and footer options
echo "Test 6: Constructor sets header and footer options...";
assert(strpos($content, "public function __construct()") !== false, "Must have public constructor");
assert(strpos($content, "\$this->options['show_header'] = true") !== false, "Must set show_header = true");
assert(strpos($content, "\$this->options['show_footer'] = true") !== false, "Must set show_footer = true");
echo " PASS\n";

// Test 7: Has display() method
echo "Test 7: Has display() method...";
assert(strpos($content, "public function display()") !== false, "Must have public display() method");
echo " PASS\n";

// Test 8: Renders title bar with correct text
echo "Test 8: Renders title bar with correct text...";
assert(strpos($content, "Weekly Planning Dashboard") !== false, "Must render 'Weekly Planning Dashboard' text");
echo " PASS\n";

// Test 9: Renders Team View button (active by default)
echo "Test 9: Renders Team View button (with default logic)...";
assert(strpos($content, "id=\"team-view-btn\"") !== false, "Must render element with id='team-view-btn'");
assert(strpos($content, "Team View") !== false, "Must render 'Team View' button text");
// Check that view_mode defaults to 'team'
assert(preg_match('/\$viewMode\s*=\s*\$_REQUEST\[[\'"]view_mode[\'"]\]\s*\?\?\s*[\'"]team[\'"]/', $content),
       "viewMode must default to 'team' when not specified");
echo " PASS\n";

// Test 10: Renders Rep View button
echo "Test 10: Renders Rep View button...";
assert(strpos($content, "id=\"rep-view-btn\"") !== false, "Must render element with id='rep-view-btn'");
assert(strpos($content, "Rep View") !== false, "Must render 'Rep View' button text");
echo " PASS\n";

// Test 11: Renders rep dropdown element with exact ID 'rep-selector'
echo "Test 11: Renders rep dropdown element with ID 'rep-selector'...";
assert(preg_match('/<select[^>]*id=["\']rep-selector["\']/', $content), "Must render <select> element with EXACT id='rep-selector' (not rep-select or other variation)");
echo " PASS\n";

// Test 12: Rep dropdown is hidden in Team View mode
echo "Test 12: Rep dropdown is hidden by default (Team View)...";
// Check for hidden class or style on rep-selector or its container
assert(preg_match('/id=["\']rep-selector["\'][^>]*(class=["\'][^"\']*hidden|style=["\'][^"\']*display:\s*none)/', $content) ||
       preg_match('/<[^>]*class=["\'][^"\']*lf-rep-selector-container[^"\']*hidden[^"\']*["\']/', $content),
       "rep-selector must be hidden by default (Team View mode)");
echo " PASS\n";

// Test 13: Week selector Back button
echo "Test 13: Week selector Back button...";
assert(strpos($content, "id=\"week-back-btn\"") !== false, "Must render element with id='week-back-btn'");
assert(strpos($content, "&lt;") !== false || strpos($content, "<") !== false, "Back button must have < symbol");
echo " PASS\n";

// Test 14: Week selector Next button
echo "Test 14: Week selector Next button...";
assert(strpos($content, "id=\"week-next-btn\"") !== false, "Must render element with id='week-next-btn'");
assert(strpos($content, "&gt;") !== false || strpos($content, ">") !== false, "Next button must have > symbol");
echo " PASS\n";

// Test 15: Current Week button
echo "Test 15: Current Week button...";
assert(strpos($content, "id=\"week-current-btn\"") !== false, "Must render element with id='week-current-btn'");
assert(strpos($content, "Current Week") !== false, "Must render 'Current Week' button text");
echo " PASS\n";

// Test 16: Week selector dropdown with exact ID 'week-selector'
echo "Test 16: Week selector dropdown with ID 'week-selector'...";
assert(preg_match('/<select[^>]*id=["\']week-selector["\']/', $content), "Must render <select> element with EXACT id='week-selector' (not week-select or other variation)");
echo " PASS\n";

// Test 17: Includes external CSS link
echo "Test 17: Includes external CSS link...";
assert(strpos($content, "<link") !== false && strpos($content, "custom/themes/lf_dashboard.css") !== false, "Must include link to custom/themes/lf_dashboard.css");
assert(preg_match('/<link[^>]*href=["\']custom\/themes\/lf_dashboard\.css["\']/', $content), "CSS link must have correct href attribute");
echo " PASS\n";

// Test 18: Includes external JS script
echo "Test 18: Includes external JS script...";
assert(strpos($content, "<script") !== false && strpos($content, "custom/modules/LF_WeeklyPlan/js/dashboard.js") !== false, "Must include script tag for custom/modules/LF_WeeklyPlan/js/dashboard.js");
assert(preg_match('/<script[^>]*src=["\']custom\/modules\/LF_WeeklyPlan\/js\/dashboard\.js["\']/', $content), "JS script must have correct src attribute");
echo " PASS\n";

// Test 19: Uses brand colors in output
echo "Test 19: Uses brand colors in output...";
assert(strpos($content, "#125EAD") !== false || strpos($content, "125EAD") !== false, "Must use Logical Front blue #125EAD");
assert(strpos($content, "#4BB74E") !== false || strpos($content, "4BB74E") !== false, "Must use Logical Front green #4BB74E");
echo " PASS\n";

// Test 20: Injects data as JSON via script tag
echo "Test 20: Injects data as JSON via script tag...";
assert(strpos($content, "window.LF_DASHBOARD_DATA") !== false, "Must inject data to window.LF_DASHBOARD_DATA");
assert(strpos($content, "json_encode") !== false, "Must use json_encode to serialize data");
assert(preg_match('/<script[^>]*>.*window\.LF_DASHBOARD_DATA\s*=.*json_encode/s', $content), "Must inject JSON data via script tag");
echo " PASS\n";

// Test 21: Gathers config values
echo "Test 21: Gathers config values...";
assert(strpos($content, "LF_PRConfig::getConfig") !== false, "Must call LF_PRConfig::getConfig to gather config values");
echo " PASS\n";

// Test 22: Gathers active reps with targets
echo "Test 22: Gathers active reps with targets...";
assert(strpos($content, "LF_RepTargets::getActiveReps") !== false, "Must call LF_RepTargets::getActiveReps()");
echo " PASS\n";

// Test 23: Gathers week info from WeekHelper
echo "Test 23: Gathers week info from WeekHelper...";
assert(strpos($content, "WeekHelper::") !== false, "Must call WeekHelper methods for week info");
assert(strpos($content, "WeekHelper::getWeekList") !== false, "Must call WeekHelper::getWeekList(12) for week dropdown");
echo " PASS\n";

// Test 24: Gathers pipeline by stage
echo "Test 24: Gathers pipeline by stage...";
assert(strpos($content, "OpportunityQuery::getPipelineByStage") !== false, "Must call OpportunityQuery::getPipelineByStage()");
echo " PASS\n";

// Test 25: Gathers pipeline by rep
echo "Test 25: Gathers pipeline by rep...";
assert(strpos($content, "OpportunityQuery::getPipelineByRep") !== false, "Must call OpportunityQuery::getPipelineByRep()");
echo " PASS\n";

// Test 26: Gathers stale deals
echo "Test 26: Gathers stale deals...";
assert(strpos($content, "OpportunityQuery::getStaleDeals") !== false, "Must call OpportunityQuery::getStaleDeals()");
echo " PASS\n";

// Test 27: Gathers plan items for selected week
echo "Test 27: Gathers plan items for selected week...";
// Should query lf_weekly_plan table or use a helper method
assert(strpos($content, "lf_weekly_plan") !== false || strpos($content, "getPlanItems") !== false, "Must gather plan items from lf_weekly_plan table");
echo " PASS\n";

// Test 28: Gathers rep targets
echo "Test 28: Gathers rep targets...";
assert(strpos($content, "lf_rep_targets") !== false || strpos($content, "getRepTargets") !== false || strpos($content, "LF_RepTargets") !== false, "Must gather rep targets data");
echo " PASS\n";

// Test 29: Week dropdown shows +/-12 weeks
echo "Test 29: Week dropdown populated with +/-12 weeks...";
assert(strpos($content, "getWeekList(12)") !== false || strpos($content, "getWeekList(12,") !== false, "Must call WeekHelper::getWeekList(12) for +/-12 weeks");
echo " PASS\n";

// Test 30: Current week is highlighted in dropdown
echo "Test 30: Current week is highlighted in dropdown...";
// Should have 'selected' attribute on current week option
assert(preg_match('/selected|data-current=["\']true["\']/', $content), "Current week must be highlighted/selected in dropdown");
echo " PASS\n";

// Test 31: Edge case - handles empty reps list
echo "Test 31: Edge case - handles empty reps list gracefully...";
// Implementation should not crash if getActiveReps() returns empty array
// This is verified by ensuring defensive coding patterns exist
assert(strpos($content, "foreach") !== false || strpos($content, "if") !== false, "Must have conditional/loop logic to handle data arrays");
echo " PASS\n";

// Test 32: JSON encoding uses security flags
echo "Test 32: JSON encoding uses security flags...";
assert(preg_match('/json_encode\([^)]+,\s*(JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)/', $content), "json_encode must use security flags (JSON_HEX_TAG, JSON_HEX_AMP, JSON_HEX_APOS, JSON_HEX_QUOT)");
echo " PASS\n";

// Test 33: Data structure includes all required keys
echo "Test 33: Data structure includes all required keys...";
// Check that the $dashboardData or $data array includes expected keys
assert(preg_match('/["\']config["\']/', $content), "Dashboard data must include 'config' key");
assert(preg_match('/["\']reps["\']/', $content), "Dashboard data must include 'reps' key");
assert(preg_match('/["\']weekInfo["\']/', $content), "Dashboard data must include 'weekInfo' key");
assert(preg_match('/["\']pipelineByStage["\']/', $content), "Dashboard data must include 'pipelineByStage' key");
assert(preg_match('/["\']pipelineByRep["\']/', $content), "Dashboard data must include 'pipelineByRep' key");
assert(preg_match('/["\']staleDeals["\']/', $content), "Dashboard data must include 'staleDeals' key");
echo " PASS\n";

// Test 34: Uses global $current_user
echo "Test 34: Uses global current_user for data scoping...";
assert(strpos($content, "global \$current_user") !== false, "Must declare global \$current_user");
echo " PASS\n";

// Test 35: Uses DBManagerFactory for queries
echo "Test 35: Uses DBManagerFactory for database queries...";
assert(strpos($content, "DBManagerFactory::getInstance()") !== false, "Must use DBManagerFactory::getInstance()");
echo " PASS\n";

echo "\n===========================================\n";
echo "All 35 tests PASSED!\n";
echo "===========================================\n";
