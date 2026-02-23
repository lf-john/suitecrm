<?php
/**
 * TDD-RED: Test Summary Runner (continues after failures)
 * US-007: Create planning dashboard - base view with data gathering
 */

// CLI-only guard
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line.');
}

echo "Running view.dashboard test summary (non-failing mode)...\n\n";

$viewFile = __DIR__ . '/../modules/LF_WeeklyPlan/views/view.dashboard.php';
$content = file_get_contents($viewFile);

$failures = [];
$passes = [];

// Test 11: Rep selector ID
if (preg_match('/<select[^>]*id=["\']rep-selector["\']/', $content)) {
    $passes[] = "Test 11: Rep dropdown has correct ID 'rep-selector'";
} else {
    $failures[] = "Test 11: FAIL - Rep dropdown must have id='rep-selector' (found id='rep-select' instead)";
}

// Test 16: Week selector ID
if (preg_match('/<select[^>]*id=["\']week-selector["\']/', $content)) {
    $passes[] = "Test 16: Week dropdown has correct ID 'week-selector'";
} else {
    $failures[] = "Test 16: FAIL - Week dropdown must have id='week-selector' (found id='week-select' instead)";
}

// Test: Back button uses &lt; not &laquo;
if (strpos($content, '&lt;') !== false && preg_match('/week-back-btn[^>]*>(&lt;|<)/', $content)) {
    $passes[] = "Test: Back button uses correct symbol '&lt;' or '<'";
} else if (strpos($content, '&laquo;') !== false) {
    $failures[] = "Test: FAIL - Back button should use '&lt;' but uses '&laquo;' instead";
}

// Test: Next button uses &gt; not &raquo;
if (strpos($content, '&gt;') !== false && preg_match('/week-next-btn[^>]*>(&gt;|>)/', $content)) {
    $passes[] = "Test: Next button uses correct symbol '&gt;' or '>'";
} else if (strpos($content, '&raquo;') !== false) {
    $failures[] = "Test: FAIL - Next button should use '&gt;' but uses '&raquo;' instead";
}

echo "===========================================\n";
echo "PASSING TESTS:\n";
echo "===========================================\n";
foreach ($passes as $pass) {
    echo "✓ $pass\n";
}

echo "\n===========================================\n";
echo "FAILING TESTS:\n";
echo "===========================================\n";
foreach ($failures as $failure) {
    echo "✗ $failure\n";
}

echo "\n===========================================\n";
echo "SUMMARY: " . count($passes) . " passing, " . count($failures) . " failing\n";
echo "===========================================\n";

exit(count($failures) > 0 ? 1 : 0);
