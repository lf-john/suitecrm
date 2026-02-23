<?php
// Custom test runner for US020
if (php_sapi_name() !== 'cli') { die('CLI only'); }
ini_set('assert.exception', '1');
ini_set('zend.assertions', '1');

$testDir = __DIR__;
$testFile = $testDir . '/US020_DashboardCssTest.test.php';

echo "Running US020 Test Runner...
";

if (!file_exists($testFile)) {
    echo "Error: Test file not found: $testFile
";
    exit(1);
}

try {
    include $testFile;
    echo "Test finished without unhandled exception/exit.
";
} catch (Throwable $e) {
    echo "Test failed with exception: " . $e->getMessage() . "
";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "
";
    exit(1);
}

// If we got here, check if verify pass/fail based on expectation?
// The test itself calls exit(1) on failure.
// If the test calls exit(0), script ends with 0.
// If the test calls exit(1), script ends with 1.
