<?php
/**
 * Test Runner - Discovers and runs all *.test.php files recursively
 *
 * This script discovers all test files matching *.test.php pattern in the
 * custom/tests/ directory and executes them with assert() exceptions enabled.
 *
 * Usage: php run-all-tests.php
 * Exit codes: 0 (all tests pass or no tests), 1 (any test fails)
 */

// CLI-only guard - prevent web execution
if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

// Enable assert() with exceptions
ini_set('assert.exception', '1');
ini_set('zend.assertions', '1');

// Test discovery configuration
$testDir = __DIR__;
$testPattern = '.test.php';
$tests = [];
$passed = [];
$failed = [];

// Discover all *.test.php files recursively
echo "Discovering test files in: {$testDir}\n";
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($testDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->isFile() && str_ends_with($file->getFilename(), $testPattern)) {
        $tests[] = $file->getPathname();
    }
}

$testCount = count($tests);

if ($testCount === 0) {
    echo "No test files found.\n";
    echo "Summary: 0 tests, 0 passed, 0 failed\n";
    exit(0);
}

echo "Found {$testCount} test file(s).\n\n";

// Execute each test file
foreach ($tests as $testFile) {
    $relativePath = str_replace($testDir . DIRECTORY_SEPARATOR, '', $testFile);
    echo "Running: {$relativePath}... ";

    // Capture output to prevent test output from interfering
    ob_start();

    try {
        // Include the test file
        include $testFile;

        // Test passed
        $output = ob_get_clean();

        // Show output if non-empty (trimmed)
        $trimmedOutput = trim($output);
        if ($trimmedOutput !== '') {
            echo "\n" . $output;
        }

        echo "OK\n";
        $passed[] = $testFile;
    } catch (AssertionError $e) {
        // Test failed - assertion error
        $output = ob_get_clean();
        echo "FAILED\n";

        $fileInfo = $e->getFile() !== null ? ":{$e->getFile()}:{$e->getLine()}" : '';
        $error = "Assertion failed in {$relativePath}{$fileInfo}\n" . $e->getMessage();

        // Show any output before the error
        $trimmedOutput = trim($output);
        if ($trimmedOutput !== '') {
            echo "Output:\n" . $output . "\n";
        }

        echo "Error: {$error}\n";
        $failed[] = [
            'file' => $relativePath,
            'error' => $error
        ];
    } catch (Throwable $e) {
        // Test failed - general error
        $output = ob_get_clean();
        echo "FAILED\n";

        $error = "Error in {$relativePath}: " . $e->getMessage() . "\n" . "File: {$e->getFile()}:{$e->getLine()}";

        // Show any output before the error
        $trimmedOutput = trim($output);
        if ($trimmedOutput !== '') {
            echo "Output:\n" . $output . "\n";
        }

        echo "Error: {$error}\n";
        $failed[] = [
            'file' => $relativePath,
            'error' => $error
        ];
    }
}

// Output summary
echo "\n" . str_repeat('=', 60) . "\n";
echo "SUMMARY\n";
echo str_repeat('=', 60) . "\n";
echo "Total: {$testCount}\n";
echo "Passed: " . count($passed) . "\n";
echo "Failed: " . count($failed) . "\n";

if (count($failed) > 0) {
    echo "\n" . str_repeat('-', 60) . "\n";
    echo "Failed tests:\n";
    echo str_repeat('-', 60) . "\n";

    foreach ($failed as $failure) {
        echo "- {$failure['file']}\n";
        echo "  {$failure['error']}\n\n";
    }
}

echo str_repeat('=', 60) . "\n";

// Exit with appropriate code
exit(count($failed) > 0 ? 1 : 0);
