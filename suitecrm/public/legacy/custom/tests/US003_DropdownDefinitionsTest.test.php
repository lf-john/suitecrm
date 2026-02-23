<?php
/**
 * US-003: Application-Level Dropdown Definitions Tests
 *
 * Tests that the Extension Language file exists at
 * custom/Extension/application/Ext/Language/en_us.lf_plan_report.php
 * and defines all 6 dropdown arrays in $app_list_strings with correct
 * keys, labels, and entry counts.
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

$languageFile = $customDir
    . DIRECTORY_SEPARATOR . 'Extension'
    . DIRECTORY_SEPARATOR . 'application'
    . DIRECTORY_SEPARATOR . 'Ext'
    . DIRECTORY_SEPARATOR . 'Language'
    . DIRECTORY_SEPARATOR . 'en_us.lf_plan_report.php';

// Expected dropdown definitions
$expectedDropdowns = [
    'lf_plan_status_dom' => [
        'in_progress' => 'In Progress',
        'submitted'   => 'Updates Complete',
        'reviewed'    => 'Reviewed',
    ],
    'lf_plan_item_type_dom' => [
        'closing'     => 'Closing',
        'at_risk'     => 'At Risk',
        'progression' => 'Progression',
    ],
    'lf_planned_day_dom' => [
        'monday'    => 'Monday',
        'tuesday'   => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday'  => 'Thursday',
        'friday'    => 'Friday',
    ],
    'lf_prospect_status_dom' => [
        'planned'        => 'Planned',
        'converted'      => 'Converted',
        'no_opportunity'  => 'No Opportunity',
    ],
    'lf_movement_dom' => [
        'forward'     => 'Forward',
        'backward'    => 'Backward',
        'static'      => 'Static',
        'closed_won'  => 'Closed Won',
        'closed_lost' => 'Closed Lost',
        'new'         => 'New',
    ],
    'lf_source_type_dom' => [
        'cold_call'      => 'Cold Call',
        'referral'       => 'Referral',
        'event'          => 'Event',
        'partner'        => 'Partner',
        'inbound'        => 'Inbound',
        'customer_visit' => 'Customer Visit',
        'other'          => 'Other',
    ],
];

$expectedCounts = [
    'lf_plan_status_dom'     => 3,
    'lf_plan_item_type_dom'  => 3,
    'lf_planned_day_dom'     => 5,
    'lf_prospect_status_dom' => 3,
    'lf_movement_dom'        => 6,
    'lf_source_type_dom'     => 7,
];


// ============================================================
// Section 1: File Existence
// ============================================================
echo "Section 1: File Existence\n";

// --- Happy Path: Language file exists ---
assert(
    file_exists($languageFile),
    "Language file should exist at: custom/Extension/application/Ext/Language/en_us.lf_plan_report.php"
);
echo "  [PASS] Language file exists\n";

// --- Happy Path: Language file is a regular file ---
assert(
    is_file($languageFile),
    "Language file path should be a regular file, not a directory"
);
echo "  [PASS] Language file is a regular file\n";


// ============================================================
// Section 2: PHP File Format (sugarEntry guard)
// ============================================================
echo "\nSection 2: PHP File Format\n";

$fileContent = file_get_contents($languageFile);
assert($fileContent !== false, "Should be able to read the Language file");

// --- Happy Path: File starts with <?php ---
assert(
    str_starts_with(trim($fileContent), '<?php'),
    "Language file must start with <?php"
);
echo "  [PASS] Language file starts with <?php\n";

// --- Happy Path: File contains sugarEntry guard ---
assert(
    str_contains($fileContent, "defined('sugarEntry')"),
    "Language file must contain sugarEntry guard: defined('sugarEntry')"
);
assert(
    str_contains($fileContent, 'sugarEntry'),
    "Language file must reference sugarEntry"
);
assert(
    str_contains($fileContent, 'Not A Valid Entry Point'),
    "Language file must contain 'Not A Valid Entry Point' die message"
);
echo "  [PASS] Language file has sugarEntry guard\n";


// ============================================================
// Section 3: Load Dropdown Data via Temp Wrapper
// ============================================================
echo "\nSection 3: Loading Dropdown Data\n";

// Use temp file wrapper to safely include guarded file
$tempFile = tempnam(sys_get_temp_dir(), 'us003_');
$wrapperCode = "<?php\n";
$wrapperCode .= "define('sugarEntry', true);\n";
$wrapperCode .= "\$app_list_strings = [];\n";
$wrapperCode .= "include " . var_export($languageFile, true) . ";\n";
$wrapperCode .= "return \$app_list_strings;\n";
file_put_contents($tempFile, $wrapperCode);

$appListStrings = include $tempFile;
unlink($tempFile);

assert(is_array($appListStrings), "\$app_list_strings should be an array after including the Language file");
echo "  [PASS] \$app_list_strings is an array\n";

// --- Happy Path: All 6 dropdown arrays are defined ---
$dropdownNames = array_keys($expectedDropdowns);
foreach ($dropdownNames as $dropdownName) {
    assert(
        array_key_exists($dropdownName, $appListStrings),
        "\$app_list_strings should contain key '{$dropdownName}'"
    );
}
echo "  [PASS] All 6 dropdown arrays are defined in \$app_list_strings\n";

// --- Edge Case: Exactly 6 dropdown arrays (no extras) ---
assert(
    count($appListStrings) === 6,
    "\$app_list_strings should have exactly 6 dropdown entries, got: " . count($appListStrings)
);
echo "  [PASS] \$app_list_strings has exactly 6 entries\n";

// --- Edge Case: All dropdown values are arrays ---
foreach ($dropdownNames as $dropdownName) {
    assert(
        is_array($appListStrings[$dropdownName]),
        "\$app_list_strings['{$dropdownName}'] should be an array"
    );
}
echo "  [PASS] All dropdown values are arrays\n";


// ============================================================
// Section 4: lf_plan_status_dom (3 entries)
// ============================================================
echo "\nSection 4: lf_plan_status_dom\n";

$dropdown = $appListStrings['lf_plan_status_dom'];

// --- Happy Path: Has exactly 3 entries ---
assert(
    count($dropdown) === 3,
    "lf_plan_status_dom should have exactly 3 entries, got: " . count($dropdown)
);
echo "  [PASS] lf_plan_status_dom has exactly 3 entries\n";

// --- Happy Path: Contains all expected keys ---
foreach ($expectedDropdowns['lf_plan_status_dom'] as $key => $label) {
    assert(
        array_key_exists($key, $dropdown),
        "lf_plan_status_dom should contain key '{$key}'"
    );
}
echo "  [PASS] lf_plan_status_dom contains all expected keys\n";

// --- Happy Path: Labels match expected human-readable values ---
foreach ($expectedDropdowns['lf_plan_status_dom'] as $key => $label) {
    assert(
        $dropdown[$key] === $label,
        "lf_plan_status_dom['{$key}'] should be '{$label}', got: " . ($dropdown[$key] ?? 'NULL')
    );
}
echo "  [PASS] lf_plan_status_dom labels match expected values\n";

// --- Edge Case: Labels are non-empty strings ---
foreach ($dropdown as $key => $label) {
    assert(
        is_string($label) && strlen(trim($label)) > 0,
        "lf_plan_status_dom['{$key}'] label should be a non-empty string"
    );
}
echo "  [PASS] lf_plan_status_dom labels are non-empty strings\n";

// --- Edge Case: Keys are snake_case strings ---
foreach (array_keys($dropdown) as $key) {
    assert(
        is_string($key) && preg_match('/^[a-z][a-z0-9_]*$/', $key),
        "lf_plan_status_dom key '{$key}' should be a snake_case string"
    );
}
echo "  [PASS] lf_plan_status_dom keys are snake_case strings\n";


// ============================================================
// Section 5: lf_plan_item_type_dom (3 entries)
// ============================================================
echo "\nSection 5: lf_plan_item_type_dom\n";

$dropdown = $appListStrings['lf_plan_item_type_dom'];

// --- Happy Path: Has exactly 3 entries ---
assert(
    count($dropdown) === 3,
    "lf_plan_item_type_dom should have exactly 3 entries, got: " . count($dropdown)
);
echo "  [PASS] lf_plan_item_type_dom has exactly 3 entries\n";

// --- Happy Path: Contains all expected keys and labels ---
foreach ($expectedDropdowns['lf_plan_item_type_dom'] as $key => $label) {
    assert(
        array_key_exists($key, $dropdown),
        "lf_plan_item_type_dom should contain key '{$key}'"
    );
    assert(
        $dropdown[$key] === $label,
        "lf_plan_item_type_dom['{$key}'] should be '{$label}', got: " . ($dropdown[$key] ?? 'NULL')
    );
}
echo "  [PASS] lf_plan_item_type_dom contains all expected keys and labels\n";


// ============================================================
// Section 6: lf_planned_day_dom (5 entries)
// ============================================================
echo "\nSection 6: lf_planned_day_dom\n";

$dropdown = $appListStrings['lf_planned_day_dom'];

// --- Happy Path: Has exactly 5 entries ---
assert(
    count($dropdown) === 5,
    "lf_planned_day_dom should have exactly 5 entries, got: " . count($dropdown)
);
echo "  [PASS] lf_planned_day_dom has exactly 5 entries\n";

// --- Happy Path: Contains all expected keys and labels ---
foreach ($expectedDropdowns['lf_planned_day_dom'] as $key => $label) {
    assert(
        array_key_exists($key, $dropdown),
        "lf_planned_day_dom should contain key '{$key}'"
    );
    assert(
        $dropdown[$key] === $label,
        "lf_planned_day_dom['{$key}'] should be '{$label}', got: " . ($dropdown[$key] ?? 'NULL')
    );
}
echo "  [PASS] lf_planned_day_dom contains all expected keys and labels\n";

// --- Edge Case: Days are in correct order (monday through friday) ---
$dayKeys = array_keys($dropdown);
$expectedOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
assert(
    $dayKeys === $expectedOrder,
    "lf_planned_day_dom keys should be in weekday order (monday-friday), got: " . implode(', ', $dayKeys)
);
echo "  [PASS] lf_planned_day_dom keys are in correct weekday order\n";


// ============================================================
// Section 7: lf_prospect_status_dom (3 entries)
// ============================================================
echo "\nSection 7: lf_prospect_status_dom\n";

$dropdown = $appListStrings['lf_prospect_status_dom'];

// --- Happy Path: Has exactly 3 entries ---
assert(
    count($dropdown) === 3,
    "lf_prospect_status_dom should have exactly 3 entries, got: " . count($dropdown)
);
echo "  [PASS] lf_prospect_status_dom has exactly 3 entries\n";

// --- Happy Path: Contains all expected keys and labels ---
foreach ($expectedDropdowns['lf_prospect_status_dom'] as $key => $label) {
    assert(
        array_key_exists($key, $dropdown),
        "lf_prospect_status_dom should contain key '{$key}'"
    );
    assert(
        $dropdown[$key] === $label,
        "lf_prospect_status_dom['{$key}'] should be '{$label}', got: " . ($dropdown[$key] ?? 'NULL')
    );
}
echo "  [PASS] lf_prospect_status_dom contains all expected keys and labels\n";


// ============================================================
// Section 8: lf_movement_dom (6 entries)
// ============================================================
echo "\nSection 8: lf_movement_dom\n";

$dropdown = $appListStrings['lf_movement_dom'];

// --- Happy Path: Has exactly 6 entries ---
assert(
    count($dropdown) === 6,
    "lf_movement_dom should have exactly 6 entries, got: " . count($dropdown)
);
echo "  [PASS] lf_movement_dom has exactly 6 entries\n";

// --- Happy Path: Contains all expected keys and labels ---
foreach ($expectedDropdowns['lf_movement_dom'] as $key => $label) {
    assert(
        array_key_exists($key, $dropdown),
        "lf_movement_dom should contain key '{$key}'"
    );
    assert(
        $dropdown[$key] === $label,
        "lf_movement_dom['{$key}'] should be '{$label}', got: " . ($dropdown[$key] ?? 'NULL')
    );
}
echo "  [PASS] lf_movement_dom contains all expected keys and labels\n";


// ============================================================
// Section 9: lf_source_type_dom (7 entries)
// ============================================================
echo "\nSection 9: lf_source_type_dom\n";

$dropdown = $appListStrings['lf_source_type_dom'];

// --- Happy Path: Has exactly 7 entries ---
assert(
    count($dropdown) === 7,
    "lf_source_type_dom should have exactly 7 entries, got: " . count($dropdown)
);
echo "  [PASS] lf_source_type_dom has exactly 7 entries\n";

// --- Happy Path: Contains all expected keys and labels ---
foreach ($expectedDropdowns['lf_source_type_dom'] as $key => $label) {
    assert(
        array_key_exists($key, $dropdown),
        "lf_source_type_dom should contain key '{$key}'"
    );
    assert(
        $dropdown[$key] === $label,
        "lf_source_type_dom['{$key}'] should be '{$label}', got: " . ($dropdown[$key] ?? 'NULL')
    );
}
echo "  [PASS] lf_source_type_dom contains all expected keys and labels\n";


// ============================================================
// Section 10: Cross-Validation
// ============================================================
echo "\nSection 10: Cross-Validation\n";

// --- Edge Case: Every dropdown has the expected entry count ---
foreach ($expectedCounts as $dropdownName => $expectedCount) {
    assert(
        count($appListStrings[$dropdownName]) === $expectedCount,
        "{$dropdownName} should have exactly {$expectedCount} entries, got: " . count($appListStrings[$dropdownName])
    );
}
echo "  [PASS] All dropdown arrays have correct entry counts\n";

// --- Edge Case: All labels across all dropdowns are non-empty strings ---
foreach ($appListStrings as $dropdownName => $dropdown) {
    foreach ($dropdown as $key => $label) {
        assert(
            is_string($label) && strlen(trim($label)) > 0,
            "{$dropdownName}['{$key}'] should be a non-empty string label"
        );
    }
}
echo "  [PASS] All labels across all dropdowns are non-empty strings\n";

// --- Edge Case: All keys across all dropdowns are snake_case strings ---
foreach ($appListStrings as $dropdownName => $dropdown) {
    foreach (array_keys($dropdown) as $key) {
        assert(
            is_string($key) && preg_match('/^[a-z][a-z0-9_]*$/', $key),
            "{$dropdownName} key '{$key}' should be a snake_case string"
        );
    }
}
echo "  [PASS] All keys across all dropdowns are snake_case strings\n";

// --- Edge Case: No duplicate labels within the same dropdown ---
foreach ($appListStrings as $dropdownName => $dropdown) {
    $labels = array_values($dropdown);
    $uniqueLabels = array_unique($labels);
    assert(
        count($uniqueLabels) === count($labels),
        "{$dropdownName} should have unique labels within the dropdown"
    );
}
echo "  [PASS] No duplicate labels within any dropdown\n";

// --- Edge Case: Dropdown names all start with 'lf_' prefix ---
foreach (array_keys($appListStrings) as $dropdownName) {
    assert(
        str_starts_with($dropdownName, 'lf_'),
        "Dropdown name '{$dropdownName}' should start with 'lf_' prefix"
    );
}
echo "  [PASS] All dropdown names start with 'lf_' prefix\n";

// --- Edge Case: Dropdown names all end with '_dom' suffix ---
foreach (array_keys($appListStrings) as $dropdownName) {
    assert(
        str_ends_with($dropdownName, '_dom'),
        "Dropdown name '{$dropdownName}' should end with '_dom' suffix"
    );
}
echo "  [PASS] All dropdown names end with '_dom' suffix\n";

// --- Negative Case: File uses $app_list_strings, not $app_strings or other vars ---
assert(
    str_contains($fileContent, '$app_list_strings'),
    "Language file must use \$app_list_strings variable"
);
echo "  [PASS] File uses \$app_list_strings variable\n";


echo "\n==============================\n";
echo "US-003: All tests passed!\n";
echo "==============================\n";
