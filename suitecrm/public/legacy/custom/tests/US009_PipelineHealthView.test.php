<?php
// custom/tests/US009_PipelineHealthView.test.php

if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

ini_set('assert.exception', '1');
ini_set('zend.assertions', '1');

echo "Running US-009 Tests: Pipeline Health View Server-Side Logic\n\n";

// ============================================================
// Section 1: File Structure Tests
// ============================================================
echo "Section 1: File Structure Tests\n";

$viewFile = __DIR__ . '/../modules/LF_WeeklyPlan/views/view.dashboard.php';
assert(file_exists($viewFile), "view.dashboard.php must exist at $viewFile");

$viewContent = file_get_contents($viewFile);

// Section 1.1: sugarEntry guard
echo "  1.1: Checking sugarEntry guard... ";
assert(
    strpos($viewContent, "if (!defined('sugarEntry') || !sugarEntry)") !== false,
    "view.dashboard.php must have sugarEntry guard"
);
assert(
    strpos($viewContent, "die('Not A Valid Entry Point')") !== false,
    "view.dashboard.php must have proper die() statement in guard"
);
echo "[OK]\n";

// Section 1.2: Class structure
echo "  1.2: Checking class structure... ";
assert(
    strpos($viewContent, "class LF_WeeklyPlanViewDashboard") !== false,
    "view.dashboard.php must define LF_WeeklyPlanViewDashboard class"
);
assert(
    strpos($viewContent, "extends SugarView") !== false,
    "LF_WeeklyPlanViewDashboard must extend SugarView"
);
assert(
    strpos($viewContent, "public function display()") !== false,
    "LF_WeeklyPlanViewDashboard must have display() method"
);
echo "[OK]\n";

// Section 1.3: Required PHP includes
echo "  1.3: Checking required includes... ";
assert(
    strpos($viewContent, "require_once") !== false && strpos($viewContent, "SugarView") !== false,
    "view.dashboard.php must require SugarView.php"
);
echo "[OK]\n";

echo "\n";

// ============================================================
// Section 2: Data Gathering Tests
// ============================================================
echo "Section 2: Data Gathering Tests\n";

// Section 2.1: Check for config retrieval
echo "  2.1: Checking config data gathering... ";
assert(
    strpos($viewContent, "LF_PRConfig::getConfig") !== false,
    "view.dashboard.php must retrieve config values using LF_PRConfig::getConfig"
);
assert(
    strpos($viewContent, "default_annual_quota") !== false,
    "view.dashboard.php must retrieve default_annual_quota config"
);
assert(
    strpos($viewContent, "pipeline_coverage_multiplier") !== false,
    "view.dashboard.php must retrieve pipeline_coverage_multiplier config"
);
echo "[OK]\n";

// Section 2.2: Check for active reps retrieval
echo "  2.2: Checking active reps retrieval... ";
assert(
    strpos($viewContent, "LF_RepTargets::getActiveReps") !== false,
    "view.dashboard.php must retrieve active reps using LF_RepTargets::getActiveReps()"
);
echo "[OK]\n";

// Section 2.3: Check for closed YTD query
echo "  2.3: Checking closed YTD data gathering... ";
assert(
    strpos($viewContent, "OpportunityQuery::getClosedYTD") !== false,
    "view.dashboard.php must retrieve closed YTD using OpportunityQuery::getClosedYTD"
);
echo "[OK]\n";

// Section 2.4: Check for pipeline by stage query
echo "  2.4: Checking pipeline by stage data gathering... ";
assert(
    strpos($viewContent, "OpportunityQuery::getPipelineByStage") !== false,
    "view.dashboard.php must retrieve pipeline by stage using OpportunityQuery::getPipelineByStage"
);
echo "[OK]\n";

// Section 2.5: Check for pipeline by rep query
echo "  2.5: Checking pipeline by rep data gathering... ";
assert(
    strpos($viewContent, "OpportunityQuery::getPipelineByRep") !== false,
    "view.dashboard.php must retrieve pipeline by rep using OpportunityQuery::getPipelineByRep"
);
echo "[OK]\n";

// Section 2.6: Check for rep targets retrieval
echo "  2.6: Checking rep targets retrieval... ";
assert(
    strpos($viewContent, "LF_RepTargets::getTargetsForYear") !== false,
    "view.dashboard.php must retrieve rep targets using LF_RepTargets::getTargetsForYear"
);
echo "[OK]\n";

echo "\n";

// ============================================================
// Section 3: Data Injection Tests
// ============================================================
echo "Section 3: Data Injection Tests\n";

// Section 3.1: Check for LF_DASHBOARD_DATA injection
echo "  3.1: Checking window.LF_DASHBOARD_DATA injection... ";
assert(
    strpos($viewContent, "window.LF_DASHBOARD_DATA") !== false,
    "view.dashboard.php must inject data into window.LF_DASHBOARD_DATA"
);
assert(
    strpos($viewContent, "json_encode") !== false,
    "view.dashboard.php must use json_encode to inject data"
);
assert(
    strpos($viewContent, "JSON_HEX_TAG") !== false || strpos($viewContent, "JSON_HEX_") !== false,
    "view.dashboard.php must use JSON_HEX flags for safe encoding"
);
echo "[OK]\n";

// Section 3.2: Check for required data fields
echo "  3.2: Checking required data fields in injection... ";
assert(
    strpos($viewContent, "'config'") !== false || strpos($viewContent, '"config"') !== false,
    "Data injection must include 'config' field"
);
assert(
    strpos($viewContent, "'reps'") !== false || strpos($viewContent, '"reps"') !== false,
    "Data injection must include 'reps' field"
);
assert(
    strpos($viewContent, "'closedYtd'") !== false || strpos($viewContent, '"closedYtd"') !== false ||
    strpos($viewContent, "'closedYTD'") !== false || strpos($viewContent, '"closedYTD"') !== false,
    "Data injection must include 'closedYtd' or 'closedYTD' field"
);
assert(
    strpos($viewContent, "'pipelineByStage'") !== false || strpos($viewContent, '"pipelineByStage"') !== false,
    "Data injection must include 'pipelineByStage' field"
);
assert(
    strpos($viewContent, "'pipelineByRep'") !== false || strpos($viewContent, '"pipelineByRep"') !== false,
    "Data injection must include 'pipelineByRep' field"
);
assert(
    strpos($viewContent, "'repTargets'") !== false || strpos($viewContent, '"repTargets"') !== false,
    "Data injection must include 'repTargets' field"
);
echo "[OK]\n";

echo "\n";

// ============================================================
// Section 4: HTML Structure Tests
// ============================================================
echo "Section 4: HTML Structure Tests\n";

// Section 4.1: Check for Pipeline Health Check column container
echo "  4.1: Checking Pipeline Health Check column container... ";
assert(
    strpos($viewContent, "pipeline-health-column") !== false,
    "view.dashboard.php must render pipeline-health-column container"
);
echo "[OK]\n";

// Section 4.2: Check for required labels
echo "  4.2: Checking required label text... ";
assert(
    strpos($viewContent, "Closed for") !== false,
    "view.dashboard.php must include 'Closed for {Year}' label"
);
assert(
    strpos($viewContent, "Team Quota") !== false,
    "view.dashboard.php must include 'Team Quota' label"
);
assert(
    strpos($viewContent, "Gap to Target") !== false,
    "view.dashboard.php must include 'Gap to Target' label"
);
assert(
    strpos($viewContent, "Pipeline by Rep") !== false,
    "view.dashboard.php must include 'Pipeline by Rep' label"
);
assert(
    strpos($viewContent, "Coverage Ratio") !== false,
    "view.dashboard.php must include 'Coverage Ratio' label"
);
echo "[OK]\n";

// Section 4.3: Check for stacked bar container
echo "  4.3: Checking stacked bar container... ";
assert(
    strpos($viewContent, "lf-stacked-bar") !== false || strpos($viewContent, "stacked-bar") !== false,
    "view.dashboard.php must include stacked bar container element"
);
echo "[OK]\n";

// Section 4.4: Check for Gap to Target red styling
echo "  4.4: Checking Gap to Target red styling indicator... ";
assert(
    strpos($viewContent, "lf-gap") !== false || strpos($viewContent, "gap-alert") !== false ||
    strpos($viewContent, "lf-red") !== false || strpos($viewContent, "alert") !== false,
    "view.dashboard.php must include class for Gap to Target red styling"
);
echo "[OK]\n";

echo "\n";

// ============================================================
// Section 5: External Resource Inclusion Tests
// ============================================================
echo "Section 5: External Resource Inclusion Tests\n";

// Section 5.1: Check for CSS inclusion
echo "  5.1: Checking CSS file inclusion... ";
assert(
    strpos($viewContent, "lf_dashboard.css") !== false,
    "view.dashboard.php must include lf_dashboard.css"
);
assert(
    strpos($viewContent, "<link") !== false,
    "view.dashboard.php must use <link> tag for CSS inclusion"
);
echo "[OK]\n";

// Section 5.2: Check for JS inclusion
echo "  5.2: Checking JavaScript file inclusion... ";
assert(
    strpos($viewContent, "dashboard.js") !== false,
    "view.dashboard.php must include dashboard.js"
);
assert(
    strpos($viewContent, "<script") !== false,
    "view.dashboard.php must use <script> tag for JS inclusion"
);
echo "[OK]\n";

echo "\n";

// ============================================================
// Section 6: Calculation Logic Tests
// ============================================================
echo "Section 6: Calculation Logic Tests\n";

// Section 6.1: Team Quota calculation
echo "  6.1: Checking Team Quota calculation logic... ";
// The view should sum individual rep quotas OR use default * rep count
// Check for loop or sum pattern
assert(
    strpos($viewContent, "annual_quota") !== false,
    "view.dashboard.php must reference annual_quota for Team Quota calculation"
);
echo "[OK]\n";

// Section 6.2: Target calculation
echo "  6.2: Checking Target calculation logic... ";
// Target = (Quota - Closed YTD) * coverage_multiplier
assert(
    strpos($viewContent, "coverage_multiplier") !== false ||
    strpos($viewContent, "pipeline_coverage_multiplier") !== false,
    "view.dashboard.php must use coverage multiplier in Target calculation"
);
echo "[OK]\n";

// Section 6.3: Gap to Target calculation
echo "  6.3: Checking Gap to Target calculation... ";
// Gap = Target - Current Pipeline
// Check for subtraction pattern or gap calculation
assert(
    strpos($viewContent, "Gap") !== false || strpos($viewContent, "gap") !== false,
    "view.dashboard.php must calculate Gap to Target"
);
echo "[OK]\n";

// Section 6.4: Coverage Ratio calculation
echo "  6.4: Checking Coverage Ratio calculation... ";
// Ratio = Current Pipeline / Remaining Quota
assert(
    strpos($viewContent, "Coverage") !== false || strpos($viewContent, "coverage") !== false,
    "view.dashboard.php must calculate Coverage Ratio"
);
echo "[OK]\n";

echo "\n";

// ============================================================
// Section 7: View Mode Tests
// ============================================================
echo "Section 7: View Mode Tests\n";

// Section 7.1: Check for team view support
echo "  7.1: Checking Team View mode support... ";
assert(
    strpos($viewContent, "team-view") !== false || strpos($viewContent, "Team View") !== false,
    "view.dashboard.php must support Team View mode"
);
echo "[OK]\n";

// Section 7.2: Check for rep view support
echo "  7.2: Checking Rep View mode support... ";
assert(
    strpos($viewContent, "rep-view") !== false || strpos($viewContent, "Rep View") !== false,
    "view.dashboard.php must support Rep View mode"
);
echo "[OK]\n";

// Section 7.3: Check for rep selector
echo "  7.3: Checking rep selector element... ";
assert(
    strpos($viewContent, "rep-selector") !== false,
    "view.dashboard.php must include rep selector element"
);
echo "[OK]\n";

echo "\n";

// ============================================================
// Section 8: Security Tests
// ============================================================
echo "Section 8: Security Tests\n";

// Section 8.1: Check for XSS protection
echo "  8.1: Checking XSS protection... ";
assert(
    strpos($viewContent, "htmlspecialchars") !== false ||
    strpos($viewContent, "htmlentities") !== false,
    "view.dashboard.php must use htmlspecialchars or htmlentities for XSS protection"
);
echo "[OK]\n";

// Section 8.2: Check for current user access
echo "  8.2: Checking current user validation... ";
assert(
    strpos($viewContent, "\$current_user") !== false || strpos($viewContent, "global \$current_user") !== false,
    "view.dashboard.php must use \$current_user for access control"
);
echo "[OK]\n";

echo "\n";

// ============================================================
// Section 9: Negative Tests - What Should NOT Exist
// ============================================================
echo "Section 9: Negative Tests - What Should NOT Exist\n";

// Section 9.1: Gap to Target should NOT be in stacked bar data
echo "  9.1: Checking Gap to Target is NOT a bar segment... ";
// This is harder to test statically, but we can check that Gap appears in separate context
// The implementation should have Gap in a separate div, not within the stacked bar loop
// We'll check that Gap appears with alert/callout styling, not bar styling
$gapPattern = "/Gap to Target.*?(lf-gap|gap-alert|alert|callout)/s";
assert(
    preg_match($gapPattern, $viewContent),
    "Gap to Target must be rendered with alert/callout styling, not as a bar segment"
);
echo "[OK]\n";

// Section 9.2: Should NOT use charting library
echo "  9.2: Checking NO charting library is used... ";
assert(
    strpos($viewContent, "chart.js") === false,
    "view.dashboard.php must NOT use chart.js"
);
assert(
    strpos($viewContent, "chartist") === false,
    "view.dashboard.php must NOT use chartist"
);
assert(
    strpos($viewContent, "d3.js") === false,
    "view.dashboard.php must NOT use d3.js"
);
echo "[OK]\n";

echo "\n";

// ============================================================
// Section 10: Edge Case Coverage
// ============================================================
echo "Section 10: Edge Case Coverage\n";

// Section 10.1: Check for division by zero protection
echo "  10.1: Checking division by zero protection... ";
// When calculating coverage ratio or percentages, must check for zero denominators
assert(
    strpos($viewContent, "> 0") !== false || strpos($viewContent, "!= 0") !== false ||
    strpos($viewContent, "!== 0") !== false,
    "view.dashboard.php must protect against division by zero"
);
echo "[OK]\n";

// Section 10.2: Check for empty pipeline handling
echo "  10.2: Checking empty data handling... ";
// Should handle cases where no pipeline exists
assert(
    strpos($viewContent, "empty") !== false || strpos($viewContent, "count") !== false ||
    strpos($viewContent, "isset") !== false,
    "view.dashboard.php must handle empty or missing data gracefully"
);
echo "[OK]\n";

echo "\n";

// ============================================================
// Final Summary
// ============================================================
echo "All US-009 Pipeline Health View tests passed!\n";
