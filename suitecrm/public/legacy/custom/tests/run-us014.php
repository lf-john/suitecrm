<?php
// CLI-only guard
if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

// Enable assert() with exceptions
ini_set('assert.exception', '1');
ini_set('zend.assertions', '1');

$files = [
    __DIR__ . '/US014_ReportingViewTest.test.php',
    __DIR__ . '/US014_GlobalLinksTest.test.php'
];

foreach ($files as $file) {
    echo "Running " . basename($file) . "...
";
    try {
        include $file;
        echo "
OK
";
    } catch (Throwable $e) {
        echo "
FAILED: " . $e->getMessage() . "
";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "
";
    }
    echo str_repeat('-', 20) . "
";
}
