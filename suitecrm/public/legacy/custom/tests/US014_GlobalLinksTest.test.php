<?php
/**
 * US-014: Create Navigation Links in Dashboards Dropdown Tests
 *
 * Tests that the GlobalLinks Extension file exists at
 * custom/Extension/application/Ext/GlobalLinks/LF_PlanReport.php
 * and defines $global_links entries for 'Weekly Planning' and
 * 'Weekly Reporting' navigation, plus registers both modules
 * in $GLOBALS['app_list_strings']['moduleList'].
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

$globalLinksFile = $customDir
    . DIRECTORY_SEPARATOR . 'Extension'
    . DIRECTORY_SEPARATOR . 'application'
    . DIRECTORY_SEPARATOR . 'Ext'
    . DIRECTORY_SEPARATOR . 'GlobalLinks'
    . DIRECTORY_SEPARATOR . 'LF_PlanReport.php';

// Expected navigation links
$expectedLinks = [
    'LF_WeeklyPlan' => [
        'label' => 'Weekly Planning',
        'url'   => 'index.php?module=LF_WeeklyPlan&action=dashboard',
    ],
    'LF_WeeklyReport' => [
        'label' => 'Weekly Reporting',
        'url'   => 'index.php?module=LF_WeeklyReport&action=dashboard',
    ],
];

// Expected moduleList registrations
$expectedModuleList = [
    'LF_WeeklyPlan'    => 'Weekly Planning',
    'LF_WeeklyReport'  => 'Weekly Reporting',
];


// ============================================================
// Section 1: File Existence
// ============================================================
echo "Section 1: File Existence\n";

// --- Happy Path: GlobalLinks file exists ---
assert(
    file_exists($globalLinksFile),
    "GlobalLinks file should exist at: custom/Extension/application/Ext/GlobalLinks/LF_PlanReport.php"
);
echo "  [PASS] GlobalLinks file exists\n";

// --- Happy Path: GlobalLinks file is a regular file ---
assert(
    is_file($globalLinksFile),
    "GlobalLinks file path should be a regular file, not a directory"
);
echo "  [PASS] GlobalLinks file is a regular file\n";

// --- Happy Path: GlobalLinks file is readable ---
assert(
    is_readable($globalLinksFile),
    "GlobalLinks file should be readable"
);
echo "  [PASS] GlobalLinks file is readable\n";


// ============================================================
// Section 2: PHP File Format (sugarEntry guard)
// ============================================================
echo "\nSection 2: PHP File Format\n";

$fileContent = file_get_contents($globalLinksFile);
assert($fileContent !== false, "Should be able to read the GlobalLinks file");

// --- Happy Path: File starts with <?php ---
assert(
    str_starts_with(trim($fileContent), '<?php'),
    "GlobalLinks file must start with <?php"
);
echo "  [PASS] GlobalLinks file starts with <?php\n";

// --- Happy Path: File contains sugarEntry guard ---
assert(
    str_contains($fileContent, "defined('sugarEntry')"),
    "GlobalLinks file must contain sugarEntry guard: defined('sugarEntry')"
);
assert(
    str_contains($fileContent, 'sugarEntry'),
    "GlobalLinks file must reference sugarEntry"
);
assert(
    str_contains($fileContent, 'Not A Valid Entry Point'),
    "GlobalLinks file must contain 'Not A Valid Entry Point' die message"
);
echo "  [PASS] GlobalLinks file has sugarEntry guard\n";


// ============================================================
// Section 3: Load $global_links Data via Temp Wrapper
// ============================================================
echo "\nSection 3: Loading \$global_links Data\n";

// Use temp file wrapper to safely include guarded file
$tempFile = tempnam(sys_get_temp_dir(), 'us014_');
$wrapperCode = "<?php\n";
$wrapperCode .= "define('sugarEntry', true);\n";
$wrapperCode .= "\$global_links = [];\n";
$wrapperCode .= "\$GLOBALS['app_list_strings'] = ['moduleList' => []];\n";
$wrapperCode .= "include " . var_export($globalLinksFile, true) . ";\n";
$wrapperCode .= "return [\n";
$wrapperCode .= "    'global_links' => \$global_links,\n";
$wrapperCode .= "    'moduleList' => \$GLOBALS['app_list_strings']['moduleList'],\n";
$wrapperCode .= "];\n";
file_put_contents($tempFile, $wrapperCode);

$loadedData = include $tempFile;
unlink($tempFile);

assert(is_array($loadedData), "Loaded data should be an array");
$globalLinks = $loadedData['global_links'];
$moduleListData = $loadedData['moduleList'];

assert(is_array($globalLinks), "\$global_links should be an array after including the GlobalLinks file");
echo "  [PASS] \$global_links is an array\n";

assert(is_array($moduleListData), "\$GLOBALS['app_list_strings']['moduleList'] should be an array");
echo "  [PASS] \$GLOBALS['app_list_strings']['moduleList'] is an array\n";


// ============================================================
// Section 4: $global_links Structure
// ============================================================
echo "\nSection 4: \$global_links Structure\n";

// --- Happy Path: $global_links has exactly 2 entries ---
assert(
    count($globalLinks) === 2,
    "\$global_links should have exactly 2 entries, got: " . count($globalLinks)
);
echo "  [PASS] \$global_links has exactly 2 entries\n";

// --- Happy Path: Each entry has 'linkinfo' sub-array ---
foreach ($globalLinks as $linkKey => $linkData) {
    assert(
        is_array($linkData),
        "\$global_links['{$linkKey}'] should be an array"
    );
    assert(
        array_key_exists('linkinfo', $linkData),
        "\$global_links['{$linkKey}'] should contain 'linkinfo' key"
    );
    assert(
        is_array($linkData['linkinfo']),
        "\$global_links['{$linkKey}']['linkinfo'] should be an array"
    );
}
echo "  [PASS] All \$global_links entries have 'linkinfo' sub-array\n";


// ============================================================
// Section 5: Weekly Planning Link
// ============================================================
echo "\nSection 5: Weekly Planning Link\n";

// Find the Weekly Planning link entry by scanning linkinfo for the label
$weeklyPlanLinkFound = false;
$weeklyPlanLinkKey = null;
$weeklyPlanLinkUrl = null;

foreach ($globalLinks as $linkKey => $linkData) {
    $linkinfo = $linkData['linkinfo'];
    foreach ($linkinfo as $label => $url) {
        if ($label === 'target') continue; // skip the target key
        if ($label === 'Weekly Planning') {
            $weeklyPlanLinkFound = true;
            $weeklyPlanLinkKey = $linkKey;
            $weeklyPlanLinkUrl = $url;
            break 2;
        }
    }
}

// --- Happy Path: Weekly Planning link exists ---
assert(
    $weeklyPlanLinkFound === true,
    "\$global_links should contain a 'Weekly Planning' labeled link"
);
echo "  [PASS] Weekly Planning link exists in \$global_links\n";

// --- Happy Path: Weekly Planning link points to correct URL ---
assert(
    $weeklyPlanLinkUrl === 'index.php?module=LF_WeeklyPlan&action=dashboard',
    "Weekly Planning link should point to 'index.php?module=LF_WeeklyPlan&action=dashboard', got: " . ($weeklyPlanLinkUrl ?? 'NULL')
);
echo "  [PASS] Weekly Planning link URL is correct\n";

// --- Edge Case: URL starts with index.php ---
assert(
    str_starts_with($weeklyPlanLinkUrl, 'index.php'),
    "Weekly Planning URL should start with 'index.php', got: " . $weeklyPlanLinkUrl
);
echo "  [PASS] Weekly Planning URL starts with index.php\n";

// --- Edge Case: URL contains module parameter ---
assert(
    str_contains($weeklyPlanLinkUrl, 'module=LF_WeeklyPlan'),
    "Weekly Planning URL should contain 'module=LF_WeeklyPlan'"
);
echo "  [PASS] Weekly Planning URL contains module parameter\n";

// --- Edge Case: URL contains action parameter ---
assert(
    str_contains($weeklyPlanLinkUrl, 'action=dashboard'),
    "Weekly Planning URL should contain 'action=dashboard'"
);
echo "  [PASS] Weekly Planning URL contains action parameter\n";


// ============================================================
// Section 6: Weekly Reporting Link
// ============================================================
echo "\nSection 6: Weekly Reporting Link\n";

// Find the Weekly Reporting link entry by scanning linkinfo for the label
$weeklyReportLinkFound = false;
$weeklyReportLinkKey = null;
$weeklyReportLinkUrl = null;

foreach ($globalLinks as $linkKey => $linkData) {
    $linkinfo = $linkData['linkinfo'];
    foreach ($linkinfo as $label => $url) {
        if ($label === 'target') continue; // skip the target key
        if ($label === 'Weekly Reporting') {
            $weeklyReportLinkFound = true;
            $weeklyReportLinkKey = $linkKey;
            $weeklyReportLinkUrl = $url;
            break 2;
        }
    }
}

// --- Happy Path: Weekly Reporting link exists ---
assert(
    $weeklyReportLinkFound === true,
    "\$global_links should contain a 'Weekly Reporting' labeled link"
);
echo "  [PASS] Weekly Reporting link exists in \$global_links\n";

// --- Happy Path: Weekly Reporting link points to correct URL ---
assert(
    $weeklyReportLinkUrl === 'index.php?module=LF_WeeklyReport&action=dashboard',
    "Weekly Reporting link should point to 'index.php?module=LF_WeeklyReport&action=dashboard', got: " . ($weeklyReportLinkUrl ?? 'NULL')
);
echo "  [PASS] Weekly Reporting link URL is correct\n";

// --- Edge Case: URL starts with index.php ---
assert(
    str_starts_with($weeklyReportLinkUrl, 'index.php'),
    "Weekly Reporting URL should start with 'index.php', got: " . $weeklyReportLinkUrl
);
echo "  [PASS] Weekly Reporting URL starts with index.php\n";

// --- Edge Case: URL contains module parameter ---
assert(
    str_contains($weeklyReportLinkUrl, 'module=LF_WeeklyReport'),
    "Weekly Reporting URL should contain 'module=LF_WeeklyReport'"
);
echo "  [PASS] Weekly Reporting URL contains module parameter\n";

// --- Edge Case: URL contains action parameter ---
assert(
    str_contains($weeklyReportLinkUrl, 'action=dashboard'),
    "Weekly Reporting URL should contain 'action=dashboard'"
);
echo "  [PASS] Weekly Reporting URL contains action parameter\n";


// ============================================================
// Section 7: URL Format Validation
// ============================================================
echo "\nSection 7: URL Format Validation\n";

// --- Happy Path: Both URLs match SuiteCRM URL format ---
$urlPattern = '/^index\.php\?module=[A-Za-z_]+&action=[a-z]+$/';

assert(
    preg_match($urlPattern, $weeklyPlanLinkUrl) === 1,
    "Weekly Planning URL should match SuiteCRM format: index.php?module=X&action=Y, got: " . $weeklyPlanLinkUrl
);
echo "  [PASS] Weekly Planning URL matches SuiteCRM URL format\n";

assert(
    preg_match($urlPattern, $weeklyReportLinkUrl) === 1,
    "Weekly Reporting URL should match SuiteCRM format: index.php?module=X&action=Y, got: " . $weeklyReportLinkUrl
);
echo "  [PASS] Weekly Reporting URL matches SuiteCRM URL format\n";

// --- Edge Case: URLs use & separator not &amp; ---
assert(
    str_contains($weeklyPlanLinkUrl, '&') && !str_contains($weeklyPlanLinkUrl, '&amp;'),
    "Weekly Planning URL should use '&' separator, not '&amp;'"
);
assert(
    str_contains($weeklyReportLinkUrl, '&') && !str_contains($weeklyReportLinkUrl, '&amp;'),
    "Weekly Reporting URL should use '&' separator, not '&amp;'"
);
echo "  [PASS] URLs use correct & separator\n";

// --- Edge Case: URLs do not contain # fragment ---
assert(
    !str_contains($weeklyPlanLinkUrl, '#'),
    "Weekly Planning URL should not contain '#' fragment"
);
assert(
    !str_contains($weeklyReportLinkUrl, '#'),
    "Weekly Reporting URL should not contain '#' fragment"
);
echo "  [PASS] URLs do not contain fragment identifiers\n";


// ============================================================
// Section 8: moduleList Registration
// ============================================================
echo "\nSection 8: moduleList Registration\n";

// --- Happy Path: LF_WeeklyPlan registered in moduleList ---
assert(
    array_key_exists('LF_WeeklyPlan', $moduleListData),
    "\$GLOBALS['app_list_strings']['moduleList'] should contain key 'LF_WeeklyPlan'"
);
echo "  [PASS] LF_WeeklyPlan registered in moduleList\n";

// --- Happy Path: LF_WeeklyPlan label is 'Weekly Planning' ---
assert(
    $moduleListData['LF_WeeklyPlan'] === 'Weekly Planning',
    "\$GLOBALS['app_list_strings']['moduleList']['LF_WeeklyPlan'] should be 'Weekly Planning', got: " . ($moduleListData['LF_WeeklyPlan'] ?? 'NULL')
);
echo "  [PASS] LF_WeeklyPlan moduleList label is 'Weekly Planning'\n";

// --- Happy Path: LF_WeeklyReport registered in moduleList ---
assert(
    array_key_exists('LF_WeeklyReport', $moduleListData),
    "\$GLOBALS['app_list_strings']['moduleList'] should contain key 'LF_WeeklyReport'"
);
echo "  [PASS] LF_WeeklyReport registered in moduleList\n";

// --- Happy Path: LF_WeeklyReport label is 'Weekly Reporting' ---
assert(
    $moduleListData['LF_WeeklyReport'] === 'Weekly Reporting',
    "\$GLOBALS['app_list_strings']['moduleList']['LF_WeeklyReport'] should be 'Weekly Reporting', got: " . ($moduleListData['LF_WeeklyReport'] ?? 'NULL')
);
echo "  [PASS] LF_WeeklyReport moduleList label is 'Weekly Reporting'\n";

// --- Edge Case: Exactly 2 moduleList entries ---
assert(
    count($moduleListData) === 2,
    "\$GLOBALS['app_list_strings']['moduleList'] should have exactly 2 entries, got: " . count($moduleListData)
);
echo "  [PASS] moduleList has exactly 2 entries\n";

// --- Edge Case: moduleList labels are non-empty strings ---
foreach ($moduleListData as $moduleKey => $moduleLabel) {
    assert(
        is_string($moduleLabel) && strlen(trim($moduleLabel)) > 0,
        "moduleList['{$moduleKey}'] label should be a non-empty string"
    );
}
echo "  [PASS] moduleList labels are non-empty strings\n";

// --- Edge Case: moduleList labels are unique ---
$moduleLabels = array_values($moduleListData);
$uniqueModuleLabels = array_unique($moduleLabels);
assert(
    count($uniqueModuleLabels) === count($moduleLabels),
    "moduleList labels should be unique (no duplicates)"
);
echo "  [PASS] moduleList labels are unique\n";


// ============================================================
// Section 9: File Content Validation
// ============================================================
echo "\nSection 9: File Content Validation\n";

// --- Happy Path: File references $global_links variable ---
assert(
    str_contains($fileContent, '$global_links'),
    "GlobalLinks file must use \$global_links variable"
);
echo "  [PASS] File references \$global_links variable\n";

// --- Happy Path: File references $GLOBALS['app_list_strings']['moduleList'] ---
assert(
    str_contains($fileContent, "app_list_strings") && str_contains($fileContent, "moduleList"),
    "GlobalLinks file must reference app_list_strings moduleList"
);
echo "  [PASS] File references app_list_strings moduleList\n";

// --- Happy Path: File contains linkinfo key ---
assert(
    str_contains($fileContent, 'linkinfo'),
    "GlobalLinks file must contain 'linkinfo' key for link definitions"
);
echo "  [PASS] File contains 'linkinfo' key\n";

// --- Happy Path: File contains both module references ---
assert(
    str_contains($fileContent, 'LF_WeeklyPlan'),
    "GlobalLinks file must reference 'LF_WeeklyPlan' module"
);
assert(
    str_contains($fileContent, 'LF_WeeklyReport'),
    "GlobalLinks file must reference 'LF_WeeklyReport' module"
);
echo "  [PASS] File references both LF_WeeklyPlan and LF_WeeklyReport\n";

// --- Happy Path: File contains both labels ---
assert(
    str_contains($fileContent, 'Weekly Planning'),
    "GlobalLinks file must contain 'Weekly Planning' label"
);
assert(
    str_contains($fileContent, 'Weekly Reporting'),
    "GlobalLinks file must contain 'Weekly Reporting' label"
);
echo "  [PASS] File contains both display labels\n";

// --- Happy Path: File contains dashboard action ---
assert(
    str_contains($fileContent, 'action=dashboard'),
    "GlobalLinks file must contain 'action=dashboard' in URLs"
);
echo "  [PASS] File contains 'action=dashboard' in URLs\n";

// --- Edge Case: File has exactly 2 $global_links assignments ---
$globalLinksCount = substr_count($fileContent, '$global_links[');
assert(
    $globalLinksCount === 2,
    "GlobalLinks file should have exactly 2 \$global_links assignments, got: " . $globalLinksCount
);
echo "  [PASS] File has exactly 2 \$global_links assignments\n";


// ============================================================
// Section 10: Negative Cases
// ============================================================
echo "\nSection 10: Negative Cases\n";

// --- Negative Case: File does NOT use $beanList (not a registration file) ---
assert(
    !str_contains($fileContent, '$beanList'),
    "GlobalLinks file should NOT contain \$beanList - this is not a module registration file"
);
echo "  [PASS] File does not contain \$beanList\n";

// --- Negative Case: File does NOT use $beanFiles ---
assert(
    !str_contains($fileContent, '$beanFiles'),
    "GlobalLinks file should NOT contain \$beanFiles - this is not a module registration file"
);
echo "  [PASS] File does not contain \$beanFiles\n";

// --- Negative Case: File does NOT use $moduleList directly (uses $GLOBALS path) ---
// The file should use $GLOBALS['app_list_strings']['moduleList'], not bare $moduleList
assert(
    str_contains($fileContent, '$GLOBALS'),
    "GlobalLinks file should use \$GLOBALS['app_list_strings']['moduleList'] path"
);
echo "  [PASS] File uses \$GLOBALS path for moduleList\n";

// --- Negative Case: File does NOT contain eval() ---
assert(
    !str_contains($fileContent, 'eval('),
    "GlobalLinks file should NOT contain eval() calls"
);
echo "  [PASS] File does not contain eval()\n";

// --- Negative Case: File does NOT contain include/require for other files ---
assert(
    !preg_match('/\b(include|require|include_once|require_once)\s/', $fileContent),
    "GlobalLinks file should NOT include/require other files"
);
echo "  [PASS] File does not include/require other files\n";


// ============================================================
// Section 11: Link Key Validation
// ============================================================
echo "\nSection 11: Link Key Validation\n";

// --- Edge Case: Link keys are non-empty strings ---
foreach (array_keys($globalLinks) as $linkKey) {
    assert(
        is_string($linkKey) && strlen(trim($linkKey)) > 0,
        "\$global_links key should be a non-empty string, got: " . var_export($linkKey, true)
    );
}
echo "  [PASS] All \$global_links keys are non-empty strings\n";

// --- Edge Case: Link keys are unique (guaranteed by PHP array, but validate structure) ---
$linkKeys = array_keys($globalLinks);
assert(
    count($linkKeys) === count(array_unique($linkKeys)),
    "\$global_links keys should be unique"
);
echo "  [PASS] All \$global_links keys are unique\n";

// --- Edge Case: Link labels within linkinfo are unique ---
$allLabels = [];
foreach ($globalLinks as $linkKey => $linkData) {
    foreach ($linkData['linkinfo'] as $label => $url) {
        if ($label === 'target') continue;
        $allLabels[] = $label;
    }
}
assert(
    count($allLabels) === count(array_unique($allLabels)),
    "Link labels across all \$global_links entries should be unique"
);
echo "  [PASS] Link labels are unique across all entries\n";

// --- Edge Case: Both links point to different modules ---
assert(
    $weeklyPlanLinkUrl !== $weeklyReportLinkUrl,
    "Weekly Planning and Weekly Reporting should point to different URLs"
);
echo "  [PASS] Both links point to different URLs\n";


// ============================================================
// Section 12: Cross-Validation
// ============================================================
echo "\nSection 12: Cross-Validation\n";

// --- Cross-Validation: moduleList keys match the modules referenced in URLs ---
foreach ($moduleListData as $moduleKey => $label) {
    $moduleInUrl = false;
    foreach ($globalLinks as $linkKey => $linkData) {
        foreach ($linkData['linkinfo'] as $linkLabel => $url) {
            if ($linkLabel === 'target') continue;
            if (str_contains($url, "module={$moduleKey}")) {
                $moduleInUrl = true;
                break 2;
            }
        }
    }
    assert(
        $moduleInUrl === true,
        "moduleList key '{$moduleKey}' should have a corresponding link in \$global_links"
    );
}
echo "  [PASS] All moduleList keys have corresponding \$global_links entries\n";

// --- Cross-Validation: moduleList labels match the link labels ---
// Weekly Planning should appear in both moduleList and global_links
assert(
    in_array('Weekly Planning', $moduleLabels) && in_array('Weekly Planning', $allLabels),
    "'Weekly Planning' should appear in both moduleList and \$global_links"
);
assert(
    in_array('Weekly Reporting', $moduleLabels) && in_array('Weekly Reporting', $allLabels),
    "'Weekly Reporting' should appear in both moduleList and \$global_links"
);
echo "  [PASS] Labels are consistent between moduleList and \$global_links\n";

// --- Cross-Validation: Number of links matches number of moduleList entries ---
assert(
    count($globalLinks) === count($moduleListData),
    "Number of \$global_links entries (" . count($globalLinks) . ") should match moduleList entries (" . count($moduleListData) . ")"
);
echo "  [PASS] Link count matches moduleList entry count\n";

// --- Cross-Validation: File uses correct SuiteCRM GlobalLinks directory ---
$globalLinksDir = dirname($globalLinksFile);
assert(
    str_ends_with($globalLinksDir, 'GlobalLinks'),
    "File should be in a 'GlobalLinks' directory"
);
echo "  [PASS] File is in correct GlobalLinks directory\n";


echo "\n==============================\n";
echo "US-014: All tests passed!\n";
echo "==============================\n";
