<?php
/**
 * Example Test File
 *
 * This is a sample test file to verify the test infrastructure works correctly.
 * Tests use PHP's native assert() function with exceptions enabled.
 */

// CLI-only guard - prevent web execution
if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

// Enable assert() with exceptions
ini_set('assert.exception', '1');
ini_set('zend.assertions', '1');

echo "Running example assertions... ";

// Happy path tests
assert(true === true, 'true should equal true');
assert(1 + 1 === 2, '1 + 1 should equal 2');
assert('hello' . ' world' === 'hello world', 'String concatenation should work');

// Edge case tests
assert(str_starts_with('hello world', 'hello'), 'String should start with prefix');
assert(str_ends_with('test.php', '.php'), 'String should end with suffix');
assert(trim('  spaced  ') === 'spaced', 'Trim should remove whitespace');

echo "all assertions passed!\n";
