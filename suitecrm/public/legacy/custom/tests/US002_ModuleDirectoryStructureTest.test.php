<?php
/**
 * US-002: Module Directory Structure and Registration Tests
 *
 * Tests that all 7 module directories exist under custom/modules/
 * with metadata/ and language/ subdirectories, and that the Extension
 * Include file properly registers all modules in $beanList, $beanFiles,
 * and $moduleList.
 *
 * These tests MUST FAIL until the implementation is created.
 */

// CLI-only guard - prevent web execution
if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

// Enable assert() with exceptions
ini_set('assert.exception', '1');
ini_set('zend.assertions', '1');

// ============================================================
// Configuration
// ============================================================

// Base path: resolve to the custom/ directory root
// From custom/tests/ we go up one level to reach custom/
$customDir = dirname(__DIR__);

// All 7 modules
$modules = [
    'LF_WeeklyPlan',
    'LF_PlanOpItem',
    'LF_PlanProspectItem',
    'LF_WeeklyReport',
    'LF_ReportSnapshot',
    'LF_PRConfig',
    'LF_RepTargets',
];

$extensionIncludeFile = $customDir
    . DIRECTORY_SEPARATOR . 'Extension'
    . DIRECTORY_SEPARATOR . 'application'
    . DIRECTORY_SEPARATOR . 'Ext'
    . DIRECTORY_SEPARATOR . 'Include'
    . DIRECTORY_SEPARATOR . 'LF_PlanReport.php';

// ============================================================
// Section 1: Module Directory Structure Tests
// ============================================================
echo "Section 1: Module Directory Structure\n";

// --- Happy Path: Each module directory exists ---
foreach ($modules as $module) {
    $moduleDir = $customDir . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $module;
    assert(
        is_dir($moduleDir),
        "Module directory should exist: custom/modules/{$module}"
    );
}
echo "  [PASS] All 7 module directories exist\n";

// --- Happy Path: Each module has metadata/ subdirectory ---
foreach ($modules as $module) {
    $metadataDir = $customDir . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'metadata';
    assert(
        is_dir($metadataDir),
        "Metadata subdirectory should exist: custom/modules/{$module}/metadata/"
    );
}
echo "  [PASS] All 7 modules have metadata/ subdirectory\n";

// --- Happy Path: Each module has language/ subdirectory ---
foreach ($modules as $module) {
    $languageDir = $customDir . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'language';
    assert(
        is_dir($languageDir),
        "Language subdirectory should exist: custom/modules/{$module}/language/"
    );
}
echo "  [PASS] All 7 modules have language/ subdirectory\n";

// --- Edge Case: Exactly 7 module directories (no extras, no missing) ---
$expectedModuleDirs = array_map(function ($m) use ($customDir) {
    return $customDir . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $m;
}, $modules);

foreach ($expectedModuleDirs as $dir) {
    assert(is_dir($dir), "Expected module directory should exist: {$dir}");
}
echo "  [PASS] All expected module directories verified\n";


// ============================================================
// Section 2: Extension Include File Existence
// ============================================================
echo "\nSection 2: Extension Include File\n";

// --- Happy Path: Extension Include file exists ---
assert(
    file_exists($extensionIncludeFile),
    "Extension Include file should exist at: custom/Extension/application/Ext/Include/LF_PlanReport.php"
);
echo "  [PASS] Extension Include file exists\n";

// --- Happy Path: Extension Include file is a regular file ---
assert(
    is_file($extensionIncludeFile),
    "Extension Include path should be a regular file, not a directory"
);
echo "  [PASS] Extension Include path is a regular file\n";


// ============================================================
// Section 3: PHP File Format (sugarEntry guard)
// ============================================================
echo "\nSection 3: PHP File Format\n";

$includeContent = file_get_contents($extensionIncludeFile);
assert($includeContent !== false, "Should be able to read the Extension Include file");

// --- Happy Path: File starts with <?php ---
assert(
    str_starts_with(trim($includeContent), '<?php'),
    "Extension Include file must start with <?php"
);
echo "  [PASS] Extension Include file starts with <?php\n";

// --- Happy Path: File contains sugarEntry guard ---
assert(
    str_contains($includeContent, "defined('sugarEntry')"),
    "Extension Include file must contain sugarEntry guard: defined('sugarEntry')"
);
assert(
    str_contains($includeContent, 'sugarEntry'),
    "Extension Include file must reference sugarEntry"
);
assert(
    str_contains($includeContent, 'Not A Valid Entry Point'),
    "Extension Include file must contain 'Not A Valid Entry Point' die message"
);
echo "  [PASS] Extension Include file has sugarEntry guard\n";


// ============================================================
// Section 4: $beanList Registration
// ============================================================
echo "\nSection 4: \$beanList Registration\n";

// To test the registration arrays, we parse the file content.
// We cannot simply include the file because it uses sugarEntry guard.
// Instead, we extract and evaluate the PHP variable assignments.

// Strategy: strip the sugarEntry guard, define sugarEntry, then evaluate.
// We define sugarEntry so the guard passes, then include via a temp wrapper.
$tempFile = tempnam(sys_get_temp_dir(), 'us002_');
$wrapperCode = "<?php\n";
$wrapperCode .= "define('sugarEntry', true);\n";
$wrapperCode .= "\$beanList = [];\n";
$wrapperCode .= "\$beanFiles = [];\n";
$wrapperCode .= "\$moduleList = [];\n";
$wrapperCode .= "include " . var_export($extensionIncludeFile, true) . ";\n";
$wrapperCode .= "return ['beanList' => \$beanList, 'beanFiles' => \$beanFiles, 'moduleList' => \$moduleList];\n";
file_put_contents($tempFile, $wrapperCode);

$registrationData = include $tempFile;
unlink($tempFile);

assert(is_array($registrationData), "Registration data should be an array");
$beanList = $registrationData['beanList'];
$beanFiles = $registrationData['beanFiles'];
$moduleList = $registrationData['moduleList'];

// --- Happy Path: $beanList has all 7 modules ---
foreach ($modules as $module) {
    assert(
        array_key_exists($module, $beanList),
        "\$beanList should contain key '{$module}'"
    );
}
echo "  [PASS] \$beanList contains all 7 module keys\n";

// --- Happy Path: $beanList maps module name to Bean class name ---
foreach ($modules as $module) {
    assert(
        $beanList[$module] === $module,
        "\$beanList['{$module}'] should equal '{$module}', got: " . ($beanList[$module] ?? 'NULL')
    );
}
echo "  [PASS] \$beanList maps module names to correct Bean class names\n";

// --- Edge Case: $beanList has exactly 7 entries (no extra entries) ---
assert(
    count($beanList) === 7,
    "\$beanList should have exactly 7 entries, got: " . count($beanList)
);
echo "  [PASS] \$beanList has exactly 7 entries\n";


// ============================================================
// Section 5: $beanFiles Registration
// ============================================================
echo "\nSection 5: \$beanFiles Registration\n";

// --- Happy Path: $beanFiles has all 7 modules ---
foreach ($modules as $module) {
    assert(
        array_key_exists($module, $beanFiles),
        "\$beanFiles should contain key '{$module}'"
    );
}
echo "  [PASS] \$beanFiles contains all 7 Bean class name keys\n";

// --- Happy Path: $beanFiles maps to correct file paths ---
foreach ($modules as $module) {
    $expectedPath = "custom/modules/{$module}/{$module}.php";
    assert(
        $beanFiles[$module] === $expectedPath,
        "\$beanFiles['{$module}'] should equal '{$expectedPath}', got: " . ($beanFiles[$module] ?? 'NULL')
    );
}
echo "  [PASS] \$beanFiles maps to correct file paths\n";

// --- Edge Case: $beanFiles has exactly 7 entries ---
assert(
    count($beanFiles) === 7,
    "\$beanFiles should have exactly 7 entries, got: " . count($beanFiles)
);
echo "  [PASS] \$beanFiles has exactly 7 entries\n";

// --- Edge Case: All $beanFiles paths follow consistent pattern ---
foreach ($beanFiles as $className => $filePath) {
    assert(
        str_starts_with($filePath, 'custom/modules/'),
        "\$beanFiles path for '{$className}' should start with 'custom/modules/', got: '{$filePath}'"
    );
    assert(
        str_ends_with($filePath, '.php'),
        "\$beanFiles path for '{$className}' should end with '.php', got: '{$filePath}'"
    );
}
echo "  [PASS] All \$beanFiles paths follow consistent naming pattern\n";


// ============================================================
// Section 6: $moduleList Registration
// ============================================================
echo "\nSection 6: \$moduleList Registration\n";

// --- Happy Path: $moduleList has all 7 modules ---
foreach ($modules as $module) {
    assert(
        array_key_exists($module, $moduleList),
        "\$moduleList should contain key '{$module}'"
    );
}
echo "  [PASS] \$moduleList contains all 7 module keys\n";

// --- Happy Path: $moduleList values are non-empty display labels ---
foreach ($modules as $module) {
    assert(
        is_string($moduleList[$module]) && strlen($moduleList[$module]) > 0,
        "\$moduleList['{$module}'] should be a non-empty string display label"
    );
}
echo "  [PASS] \$moduleList values are non-empty display labels\n";

// --- Edge Case: $moduleList has exactly 7 entries ---
assert(
    count($moduleList) === 7,
    "\$moduleList should have exactly 7 entries, got: " . count($moduleList)
);
echo "  [PASS] \$moduleList has exactly 7 entries\n";

// --- Edge Case: $moduleList values are unique ---
$labelValues = array_values($moduleList);
$uniqueLabels = array_unique($labelValues);
assert(
    count($uniqueLabels) === count($labelValues),
    "\$moduleList display labels should be unique (no duplicate labels)"
);
echo "  [PASS] \$moduleList display labels are unique\n";


// ============================================================
// Section 7: Cross-Validation
// ============================================================
echo "\nSection 7: Cross-Validation\n";

// --- Edge Case: $beanList and $beanFiles have matching keys via class name ---
// $beanList maps module -> className, $beanFiles maps className -> path
// So every value in $beanList should be a key in $beanFiles
foreach ($beanList as $moduleName => $className) {
    assert(
        array_key_exists($className, $beanFiles),
        "Bean class '{$className}' from \$beanList should exist as key in \$beanFiles"
    );
}
echo "  [PASS] All \$beanList class names exist in \$beanFiles\n";

// --- Edge Case: Every module in $beanList is also in $moduleList ---
foreach ($beanList as $moduleName => $className) {
    assert(
        array_key_exists($moduleName, $moduleList),
        "Module '{$moduleName}' from \$beanList should exist in \$moduleList"
    );
}
echo "  [PASS] All modules registered in all three arrays\n";

// --- Edge Case: $beanFiles paths reference modules that have directories ---
foreach ($beanFiles as $className => $filePath) {
    // Extract module name from path: custom/modules/{ModuleName}/{ModuleName}.php
    $parts = explode('/', $filePath);
    assert(
        count($parts) === 4,
        "File path '{$filePath}' should have 4 parts (custom/modules/Module/Module.php)"
    );
    $moduleFromPath = $parts[2];
    $moduleDir = $customDir . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleFromPath;
    assert(
        is_dir($moduleDir),
        "Module directory for '{$moduleFromPath}' referenced in \$beanFiles should exist"
    );
}
echo "  [PASS] All \$beanFiles paths reference existing module directories\n";


echo "\n==============================\n";
echo "US-002: All tests passed!\n";
echo "==============================\n";
