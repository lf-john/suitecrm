<?php
// Custom test runner for US015
if (php_sapi_name() !== 'cli') { die('CLI only'); }
ini_set('assert.exception', '1');
ini_set('zend.assertions', '1');

$testDir = __DIR__;
$tests = [
    $testDir . '/US015_ReportingView_Prospecting.test.php',
    $testDir . '/US015_AjaxEndpoint.test.php'
];

$passed = [];
$failed = [];

foreach ($tests as $testFile) {
    $relativePath = basename($testFile);
    echo "Running: {$relativePath}... ";
    ob_start();
    try {
        if (!file_exists($testFile)) { throw new Exception("File not found"); }
        include $testFile;
        $output = ob_get_clean();
        echo "OK
";
        if (trim($output)) echo $output . "
";
        $passed[] = $testFile;
    } catch (Throwable $e) {
        $output = ob_get_clean();
        echo "FAILED
";
        echo "Error: " . $e->getMessage() . "
";
        if (trim($output)) echo "Output:
" . $output . "
";
        $failed[] = ['file' => $relativePath, 'error' => $e->getMessage()];
    }
}

echo "
SUMMARY: Passed: " . count($passed) . ", Failed: " . count($failed) . "
";
exit(count($failed) > 0 ? 1 : 0);
