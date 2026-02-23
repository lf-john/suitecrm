<?php
/**
 * US-004: LF_PRConfig Bean and Vardefs Tests
 *
 * Tests that the LF_PRConfig Bean class, vardefs, and language file
 * exist with correct structure, properties, methods, and field definitions.
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

$beanFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_PRConfig'
    . DIRECTORY_SEPARATOR . 'LF_PRConfig.php';

$vardefsFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_PRConfig'
    . DIRECTORY_SEPARATOR . 'metadata'
    . DIRECTORY_SEPARATOR . 'vardefs.php';

$languageFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_PRConfig'
    . DIRECTORY_SEPARATOR . 'language'
    . DIRECTORY_SEPARATOR . 'en_us.lang.php';

// Expected standard SuiteCRM fields (7 total)
$standardFields = [
    'id',
    'name',
    'date_entered',
    'date_modified',
    'modified_user_id',
    'created_by',
    'deleted',
];

// Expected custom fields with their definitions
$customFields = [
    'category' => [
        'type' => 'varchar',
        'len' => 50,
        'required' => true,
    ],
    'config_name' => [
        'type' => 'varchar',
        'len' => 100,
        'required' => true,
    ],
    'value' => [
        'type' => 'text',
    ],
    'description' => [
        'type' => 'varchar',
        'len' => 255,
    ],
];

// Expected language labels
$expectedLabels = [
    'LBL_MODULE_NAME',
    'LBL_CATEGORY',
    'LBL_CONFIG_NAME',
    'LBL_VALUE',
    'LBL_DESCRIPTION',
];


// ============================================================
// Section 1: Bean File Existence
// ============================================================
echo "Section 1: Bean File Existence\n";

// --- Happy Path: Bean file exists ---
assert(
    file_exists($beanFile),
    "Bean file should exist at: custom/modules/LF_PRConfig/LF_PRConfig.php"
);
echo "  [PASS] Bean file exists\n";

// --- Happy Path: Bean file is a regular file ---
assert(
    is_file($beanFile),
    "Bean file path should be a regular file, not a directory"
);
echo "  [PASS] Bean file is a regular file\n";


// ============================================================
// Section 2: Bean File PHP Format (sugarEntry guard)
// ============================================================
echo "\nSection 2: Bean File PHP Format\n";

$beanContent = file_get_contents($beanFile);
assert($beanContent !== false, "Should be able to read the Bean file");

// --- Happy Path: File starts with <?php ---
assert(
    str_starts_with(trim($beanContent), '<?php'),
    "Bean file must start with <?php"
);
echo "  [PASS] Bean file starts with <?php\n";

// --- Happy Path: File contains sugarEntry guard ---
assert(
    str_contains($beanContent, "defined('sugarEntry')"),
    "Bean file must contain sugarEntry guard: defined('sugarEntry')"
);
assert(
    str_contains($beanContent, 'Not A Valid Entry Point'),
    "Bean file must contain 'Not A Valid Entry Point' die message"
);
echo "  [PASS] Bean file has sugarEntry guard\n";


// ============================================================
// Section 3: Bean Class Structure
// ============================================================
echo "\nSection 3: Bean Class Structure\n";

// --- Happy Path: File contains class LF_PRConfig extending SugarBean ---
assert(
    preg_match('/class\s+LF_PRConfig\s+extends\s+SugarBean/', $beanContent) === 1,
    "Bean file must contain 'class LF_PRConfig extends SugarBean'"
);
echo "  [PASS] Bean class extends SugarBean\n";

// --- Happy Path: File has #[\AllowDynamicProperties] attribute for PHP 8.2 ---
assert(
    str_contains($beanContent, '#[\AllowDynamicProperties]') || str_contains($beanContent, '#[AllowDynamicProperties]'),
    "Bean file must have #[\\AllowDynamicProperties] attribute for PHP 8.2 compatibility"
);
echo "  [PASS] Bean class has AllowDynamicProperties attribute\n";

// --- Happy Path: $table_name = 'lf_pr_config' ---
assert(
    preg_match('/\$table_name\s*=\s*[\'"]lf_pr_config[\'"]/', $beanContent) === 1,
    "Bean must have \$table_name = 'lf_pr_config'"
);
echo "  [PASS] Bean has \$table_name = 'lf_pr_config'\n";

// --- Happy Path: $object_name = 'LF_PRConfig' ---
assert(
    preg_match('/\$object_name\s*=\s*[\'"]LF_PRConfig[\'"]/', $beanContent) === 1,
    "Bean must have \$object_name = 'LF_PRConfig'"
);
echo "  [PASS] Bean has \$object_name = 'LF_PRConfig'\n";

// --- Happy Path: $module_name = 'LF_PRConfig' ---
assert(
    preg_match('/\$module_name\s*=\s*[\'"]LF_PRConfig[\'"]/', $beanContent) === 1,
    "Bean must have \$module_name = 'LF_PRConfig'"
);
echo "  [PASS] Bean has \$module_name = 'LF_PRConfig'\n";

// --- Edge Case: Properties are public (SugarBean convention) ---
assert(
    preg_match('/public\s+\$table_name/', $beanContent) === 1,
    "Bean \$table_name should be declared as public"
);
assert(
    preg_match('/public\s+\$object_name/', $beanContent) === 1,
    "Bean \$object_name should be declared as public"
);
assert(
    preg_match('/public\s+\$module_name/', $beanContent) === 1,
    "Bean \$module_name should be declared as public"
);
echo "  [PASS] Bean properties are declared as public\n";


// ============================================================
// Section 4: Bean getConfig() Static Method
// ============================================================
echo "\nSection 4: Bean getConfig() Static Method\n";

// --- Happy Path: File contains static getConfig method ---
assert(
    preg_match('/public\s+static\s+function\s+getConfig\s*\(/', $beanContent) === 1,
    "Bean must have 'public static function getConfig(' method"
);
echo "  [PASS] Bean has static getConfig() method\n";

// --- Happy Path: getConfig method accepts $category and $configName parameters ---
assert(
    preg_match('/function\s+getConfig\s*\(\s*\$category\s*,\s*\$configName\s*\)/', $beanContent) === 1,
    "getConfig() must accept \$category and \$configName parameters"
);
echo "  [PASS] getConfig() has correct parameters\n";

// --- Happy Path: getConfig method queries by category, config_name, and deleted=0 ---
assert(
    str_contains($beanContent, 'category') && str_contains($beanContent, 'config_name') && str_contains($beanContent, 'deleted'),
    "getConfig() must query by category, config_name, and deleted fields"
);
echo "  [PASS] getConfig() references category, config_name, and deleted fields\n";

// --- Happy Path: getConfig references the lf_pr_config table ---
assert(
    str_contains($beanContent, 'lf_pr_config'),
    "getConfig() should reference the lf_pr_config table"
);
echo "  [PASS] getConfig() references lf_pr_config table\n";


// ============================================================
// Section 5: Bean getAll() Static Method
// ============================================================
echo "\nSection 5: Bean getAll() Static Method\n";

// --- Happy Path: File contains static getAll method ---
assert(
    preg_match('/public\s+static\s+function\s+getAll\s*\(/', $beanContent) === 1,
    "Bean must have 'public static function getAll(' method"
);
echo "  [PASS] Bean has static getAll() method\n";

// --- Happy Path: getAll method has no required parameters ---
assert(
    preg_match('/function\s+getAll\s*\(\s*\)/', $beanContent) === 1,
    "getAll() must have no required parameters"
);
echo "  [PASS] getAll() has no required parameters\n";

// --- Happy Path: getAll references deleted=0 filter ---
// The method should filter out deleted rows
assert(
    str_contains($beanContent, 'deleted'),
    "getAll() should filter by deleted=0"
);
echo "  [PASS] getAll() references deleted field for filtering\n";

// --- Happy Path: getAll builds nested array by category and config_name ---
assert(
    str_contains($beanContent, 'category') && str_contains($beanContent, 'config_name') && str_contains($beanContent, 'value'),
    "getAll() should reference category, config_name, and value fields to build nested array"
);
echo "  [PASS] getAll() references category, config_name, and value fields\n";


// ============================================================
// Section 6: Vardefs File Existence
// ============================================================
echo "\nSection 6: Vardefs File Existence\n";

// --- Happy Path: Vardefs file exists ---
assert(
    file_exists($vardefsFile),
    "Vardefs file should exist at: custom/modules/LF_PRConfig/metadata/vardefs.php"
);
echo "  [PASS] Vardefs file exists\n";

// --- Happy Path: Vardefs file is a regular file ---
assert(
    is_file($vardefsFile),
    "Vardefs file path should be a regular file, not a directory"
);
echo "  [PASS] Vardefs file is a regular file\n";


// ============================================================
// Section 7: Vardefs PHP Format and sugarEntry Guard
// ============================================================
echo "\nSection 7: Vardefs PHP Format\n";

$vardefsContent = file_get_contents($vardefsFile);
assert($vardefsContent !== false, "Should be able to read the vardefs file");

// --- Happy Path: File starts with <?php ---
assert(
    str_starts_with(trim($vardefsContent), '<?php'),
    "Vardefs file must start with <?php"
);
echo "  [PASS] Vardefs file starts with <?php\n";

// --- Happy Path: File contains sugarEntry guard ---
assert(
    str_contains($vardefsContent, "defined('sugarEntry')"),
    "Vardefs file must contain sugarEntry guard: defined('sugarEntry')"
);
assert(
    str_contains($vardefsContent, 'Not A Valid Entry Point'),
    "Vardefs file must contain 'Not A Valid Entry Point' die message"
);
echo "  [PASS] Vardefs file has sugarEntry guard\n";


// ============================================================
// Section 8: Vardefs Dictionary Structure
// ============================================================
echo "\nSection 8: Vardefs Dictionary Structure\n";

// Load vardefs data using temp file wrapper
$tempFile = tempnam(sys_get_temp_dir(), 'us004_vardefs_');
$wrapperCode = "<?php\n";
$wrapperCode .= "define('sugarEntry', true);\n";
$wrapperCode .= "\$dictionary = [];\n";
$wrapperCode .= "include " . var_export($vardefsFile, true) . ";\n";
$wrapperCode .= "return \$dictionary;\n";
file_put_contents($tempFile, $wrapperCode);

$dictionary = include $tempFile;
unlink($tempFile);

assert(is_array($dictionary), "\$dictionary should be an array after including the vardefs file");
echo "  [PASS] \$dictionary is an array\n";

// --- Happy Path: Dictionary key matches $object_name: $dictionary['LF_PRConfig'] ---
assert(
    array_key_exists('LF_PRConfig', $dictionary),
    "\$dictionary must have key 'LF_PRConfig' matching \$object_name"
);
echo "  [PASS] \$dictionary has key 'LF_PRConfig'\n";

// --- Edge Case: Dictionary has exactly 1 entry (no extra dictionaries) ---
assert(
    count($dictionary) === 1,
    "\$dictionary should have exactly 1 entry, got: " . count($dictionary)
);
echo "  [PASS] \$dictionary has exactly 1 entry\n";

$vardefEntry = $dictionary['LF_PRConfig'];

// --- Happy Path: Entry has 'table' key matching table_name ---
assert(
    array_key_exists('table', $vardefEntry),
    "Vardefs entry must have 'table' key"
);
assert(
    $vardefEntry['table'] === 'lf_pr_config',
    "Vardefs 'table' should be 'lf_pr_config', got: " . ($vardefEntry['table'] ?? 'NULL')
);
echo "  [PASS] Vardefs table is 'lf_pr_config'\n";

// --- Happy Path: Entry has 'fields' key ---
assert(
    array_key_exists('fields', $vardefEntry),
    "Vardefs entry must have 'fields' key"
);
assert(
    is_array($vardefEntry['fields']),
    "Vardefs 'fields' should be an array"
);
echo "  [PASS] Vardefs has 'fields' array\n";

$fields = $vardefEntry['fields'];


// ============================================================
// Section 9: Standard SuiteCRM Fields (7 fields)
// ============================================================
echo "\nSection 9: Standard SuiteCRM Fields\n";

// --- Happy Path: All 7 standard fields exist ---
foreach ($standardFields as $fieldName) {
    assert(
        array_key_exists($fieldName, $fields),
        "Vardefs must include standard field '{$fieldName}'"
    );
}
echo "  [PASS] All 7 standard SuiteCRM fields exist\n";

// --- Happy Path: 'id' field is type 'id' ---
assert(
    $fields['id']['type'] === 'id',
    "Standard field 'id' should have type 'id', got: " . ($fields['id']['type'] ?? 'NULL')
);
echo "  [PASS] 'id' field has type 'id'\n";

// --- Happy Path: 'name' field is type 'name' or 'varchar' ---
assert(
    $fields['name']['type'] === 'name' || $fields['name']['type'] === 'varchar',
    "Standard field 'name' should have type 'name' or 'varchar', got: " . ($fields['name']['type'] ?? 'NULL')
);
echo "  [PASS] 'name' field has valid type\n";

// --- Happy Path: 'deleted' field is type 'bool' ---
assert(
    $fields['deleted']['type'] === 'bool',
    "Standard field 'deleted' should have type 'bool', got: " . ($fields['deleted']['type'] ?? 'NULL')
);
echo "  [PASS] 'deleted' field has type 'bool'\n";

// --- Happy Path: 'date_entered' field is type 'datetime' ---
assert(
    $fields['date_entered']['type'] === 'datetime',
    "Standard field 'date_entered' should have type 'datetime', got: " . ($fields['date_entered']['type'] ?? 'NULL')
);
echo "  [PASS] 'date_entered' field has type 'datetime'\n";

// --- Happy Path: 'date_modified' field is type 'datetime' ---
assert(
    $fields['date_modified']['type'] === 'datetime',
    "Standard field 'date_modified' should have type 'datetime', got: " . ($fields['date_modified']['type'] ?? 'NULL')
);
echo "  [PASS] 'date_modified' field has type 'datetime'\n";

// --- Happy Path: 'modified_user_id' field is type 'id' ---
assert(
    $fields['modified_user_id']['type'] === 'id',
    "Standard field 'modified_user_id' should have type 'id', got: " . ($fields['modified_user_id']['type'] ?? 'NULL')
);
echo "  [PASS] 'modified_user_id' field has type 'id'\n";

// --- Happy Path: 'created_by' field is type 'id' ---
assert(
    $fields['created_by']['type'] === 'id',
    "Standard field 'created_by' should have type 'id', got: " . ($fields['created_by']['type'] ?? 'NULL')
);
echo "  [PASS] 'created_by' field has type 'id'\n";


// ============================================================
// Section 10: Custom Fields
// ============================================================
echo "\nSection 10: Custom Fields\n";

// --- Happy Path: 'category' field exists with correct type and length ---
assert(
    array_key_exists('category', $fields),
    "Vardefs must include custom field 'category'"
);
assert(
    $fields['category']['type'] === 'varchar',
    "Field 'category' should have type 'varchar', got: " . ($fields['category']['type'] ?? 'NULL')
);
assert(
    isset($fields['category']['len']) && (int)$fields['category']['len'] === 50,
    "Field 'category' should have len 50, got: " . ($fields['category']['len'] ?? 'NULL')
);
echo "  [PASS] 'category' field: varchar(50)\n";

// --- Happy Path: 'category' field is required ---
assert(
    isset($fields['category']['required']) && $fields['category']['required'] === true,
    "Field 'category' should be required"
);
echo "  [PASS] 'category' field is required\n";

// --- Happy Path: 'config_name' field exists with correct type and length ---
assert(
    array_key_exists('config_name', $fields),
    "Vardefs must include custom field 'config_name'"
);
assert(
    $fields['config_name']['type'] === 'varchar',
    "Field 'config_name' should have type 'varchar', got: " . ($fields['config_name']['type'] ?? 'NULL')
);
assert(
    isset($fields['config_name']['len']) && (int)$fields['config_name']['len'] === 100,
    "Field 'config_name' should have len 100, got: " . ($fields['config_name']['len'] ?? 'NULL')
);
echo "  [PASS] 'config_name' field: varchar(100)\n";

// --- Happy Path: 'config_name' field is required ---
assert(
    isset($fields['config_name']['required']) && $fields['config_name']['required'] === true,
    "Field 'config_name' should be required"
);
echo "  [PASS] 'config_name' field is required\n";

// --- Happy Path: 'value' field exists with correct type ---
assert(
    array_key_exists('value', $fields),
    "Vardefs must include custom field 'value'"
);
assert(
    $fields['value']['type'] === 'text',
    "Field 'value' should have type 'text', got: " . ($fields['value']['type'] ?? 'NULL')
);
echo "  [PASS] 'value' field: text\n";

// --- Happy Path: 'description' field exists with correct type and length ---
assert(
    array_key_exists('description', $fields),
    "Vardefs must include custom field 'description'"
);
assert(
    $fields['description']['type'] === 'varchar',
    "Field 'description' should have type 'varchar', got: " . ($fields['description']['type'] ?? 'NULL')
);
assert(
    isset($fields['description']['len']) && (int)$fields['description']['len'] === 255,
    "Field 'description' should have len 255, got: " . ($fields['description']['len'] ?? 'NULL')
);
echo "  [PASS] 'description' field: varchar(255)\n";


// ============================================================
// Section 11: Field Count Validation
// ============================================================
echo "\nSection 11: Field Count Validation\n";

// Total expected: 7 standard + 4 custom = 11 fields
$expectedFieldCount = count($standardFields) + count($customFields);

// --- Edge Case: Exactly 11 fields (no extra, no missing) ---
assert(
    count($fields) === $expectedFieldCount,
    "Vardefs should have exactly {$expectedFieldCount} fields (7 standard + 4 custom), got: " . count($fields)
);
echo "  [PASS] Vardefs has exactly {$expectedFieldCount} fields\n";

// --- Edge Case: All custom field names are present ---
foreach (array_keys($customFields) as $customFieldName) {
    assert(
        array_key_exists($customFieldName, $fields),
        "Custom field '{$customFieldName}' must exist in vardefs fields"
    );
}
echo "  [PASS] All 4 custom fields are present\n";


// ============================================================
// Section 12: Vardefs Field Name Validation (config_name NOT name)
// ============================================================
echo "\nSection 12: Config Key Field Naming\n";

// --- Happy Path: config_name field exists (the config key field) ---
assert(
    array_key_exists('config_name', $fields),
    "Config key field must be 'config_name' in vardefs"
);
echo "  [PASS] Config key field is 'config_name'\n";

// --- Negative Case: 'name' field is the standard SuiteCRM name, NOT the config key ---
// The 'name' field should exist as a standard field, but should NOT be varchar(100)
// because that would indicate it's being used as the config key field
assert(
    array_key_exists('name', $fields),
    "Standard 'name' field must still exist"
);
echo "  [PASS] Standard 'name' field exists alongside 'config_name'\n";

// --- Edge Case: config_name and name are separate, distinct fields ---
assert(
    $fields['config_name']['type'] === 'varchar' && (int)$fields['config_name']['len'] === 100,
    "config_name must be varchar(100) - the actual config key field"
);
echo "  [PASS] 'config_name' is the config key field (varchar 100)\n";


// ============================================================
// Section 13: Language File Existence
// ============================================================
echo "\nSection 13: Language File Existence\n";

// --- Happy Path: Language file exists ---
assert(
    file_exists($languageFile),
    "Language file should exist at: custom/modules/LF_PRConfig/language/en_us.lang.php"
);
echo "  [PASS] Language file exists\n";

// --- Happy Path: Language file is a regular file ---
assert(
    is_file($languageFile),
    "Language file path should be a regular file, not a directory"
);
echo "  [PASS] Language file is a regular file\n";


// ============================================================
// Section 14: Language File PHP Format and sugarEntry Guard
// ============================================================
echo "\nSection 14: Language File PHP Format\n";

$langContent = file_get_contents($languageFile);
assert($langContent !== false, "Should be able to read the language file");

// --- Happy Path: File starts with <?php ---
assert(
    str_starts_with(trim($langContent), '<?php'),
    "Language file must start with <?php"
);
echo "  [PASS] Language file starts with <?php\n";

// --- Happy Path: File contains sugarEntry guard ---
assert(
    str_contains($langContent, "defined('sugarEntry')"),
    "Language file must contain sugarEntry guard: defined('sugarEntry')"
);
assert(
    str_contains($langContent, 'Not A Valid Entry Point'),
    "Language file must contain 'Not A Valid Entry Point' die message"
);
echo "  [PASS] Language file has sugarEntry guard\n";


// ============================================================
// Section 15: Language File Labels ($mod_strings)
// ============================================================
echo "\nSection 15: Language File Labels\n";

// Load language data using temp file wrapper
$tempFile = tempnam(sys_get_temp_dir(), 'us004_lang_');
$wrapperCode = "<?php\n";
$wrapperCode .= "define('sugarEntry', true);\n";
$wrapperCode .= "\$mod_strings = [];\n";
$wrapperCode .= "include " . var_export($languageFile, true) . ";\n";
$wrapperCode .= "return \$mod_strings;\n";
file_put_contents($tempFile, $wrapperCode);

$modStrings = include $tempFile;
unlink($tempFile);

assert(is_array($modStrings), "\$mod_strings should be an array after including the language file");
echo "  [PASS] \$mod_strings is an array\n";

// --- Happy Path: File uses $mod_strings variable (not $mod_list_strings or $app_strings) ---
assert(
    str_contains($langContent, '$mod_strings'),
    "Language file must use \$mod_strings variable"
);
echo "  [PASS] Language file uses \$mod_strings variable\n";

// --- Happy Path: All expected labels exist ---
foreach ($expectedLabels as $labelKey) {
    assert(
        array_key_exists($labelKey, $modStrings),
        "\$mod_strings must contain label key '{$labelKey}'"
    );
}
echo "  [PASS] All expected label keys exist in \$mod_strings\n";

// --- Happy Path: LBL_MODULE_NAME is defined ---
assert(
    array_key_exists('LBL_MODULE_NAME', $modStrings),
    "\$mod_strings must contain 'LBL_MODULE_NAME'"
);
assert(
    is_string($modStrings['LBL_MODULE_NAME']) && strlen(trim($modStrings['LBL_MODULE_NAME'])) > 0,
    "LBL_MODULE_NAME must be a non-empty string"
);
echo "  [PASS] LBL_MODULE_NAME is a non-empty string\n";

// --- Happy Path: LBL_CATEGORY is defined ---
assert(
    array_key_exists('LBL_CATEGORY', $modStrings),
    "\$mod_strings must contain 'LBL_CATEGORY'"
);
assert(
    is_string($modStrings['LBL_CATEGORY']) && strlen(trim($modStrings['LBL_CATEGORY'])) > 0,
    "LBL_CATEGORY must be a non-empty string"
);
echo "  [PASS] LBL_CATEGORY is a non-empty string\n";

// --- Happy Path: LBL_CONFIG_NAME is defined ---
assert(
    array_key_exists('LBL_CONFIG_NAME', $modStrings),
    "\$mod_strings must contain 'LBL_CONFIG_NAME'"
);
assert(
    is_string($modStrings['LBL_CONFIG_NAME']) && strlen(trim($modStrings['LBL_CONFIG_NAME'])) > 0,
    "LBL_CONFIG_NAME must be a non-empty string"
);
echo "  [PASS] LBL_CONFIG_NAME is a non-empty string\n";

// --- Happy Path: LBL_VALUE is defined ---
assert(
    array_key_exists('LBL_VALUE', $modStrings),
    "\$mod_strings must contain 'LBL_VALUE'"
);
assert(
    is_string($modStrings['LBL_VALUE']) && strlen(trim($modStrings['LBL_VALUE'])) > 0,
    "LBL_VALUE must be a non-empty string"
);
echo "  [PASS] LBL_VALUE is a non-empty string\n";

// --- Happy Path: LBL_DESCRIPTION is defined ---
assert(
    array_key_exists('LBL_DESCRIPTION', $modStrings),
    "\$mod_strings must contain 'LBL_DESCRIPTION'"
);
assert(
    is_string($modStrings['LBL_DESCRIPTION']) && strlen(trim($modStrings['LBL_DESCRIPTION'])) > 0,
    "LBL_DESCRIPTION must be a non-empty string"
);
echo "  [PASS] LBL_DESCRIPTION is a non-empty string\n";

// --- Edge Case: All label values are non-empty strings ---
foreach ($modStrings as $key => $value) {
    assert(
        is_string($value) && strlen(trim($value)) > 0,
        "\$mod_strings['{$key}'] should be a non-empty string"
    );
}
echo "  [PASS] All label values are non-empty strings\n";

// --- Edge Case: At least 5 labels defined (no less than expected) ---
assert(
    count($modStrings) >= count($expectedLabels),
    "\$mod_strings should have at least " . count($expectedLabels) . " entries, got: " . count($modStrings)
);
echo "  [PASS] \$mod_strings has at least " . count($expectedLabels) . " entries\n";


// ============================================================
// Section 16: Cross-Validation
// ============================================================
echo "\nSection 16: Cross-Validation\n";

// --- Edge Case: Vardefs uses $dictionary variable, not other variable names ---
assert(
    str_contains($vardefsContent, '$dictionary'),
    "Vardefs file must use \$dictionary variable"
);
echo "  [PASS] Vardefs file uses \$dictionary variable\n";

// --- Edge Case: Vardefs dictionary key matches Bean $object_name ---
// We already verified $dictionary['LF_PRConfig'] exists, but let's also
// verify the Bean file declares $object_name = 'LF_PRConfig'
assert(
    preg_match('/\$object_name\s*=\s*[\'"]LF_PRConfig[\'"]/', $beanContent) === 1,
    "Bean \$object_name must match vardefs dictionary key 'LF_PRConfig'"
);
assert(
    array_key_exists('LF_PRConfig', $dictionary),
    "Vardefs \$dictionary key must match Bean \$object_name 'LF_PRConfig'"
);
echo "  [PASS] Bean \$object_name matches vardefs dictionary key\n";

// --- Edge Case: Vardefs table name matches Bean $table_name ---
assert(
    $vardefEntry['table'] === 'lf_pr_config',
    "Vardefs table must match Bean \$table_name 'lf_pr_config'"
);
assert(
    preg_match('/\$table_name\s*=\s*[\'"]lf_pr_config[\'"]/', $beanContent) === 1,
    "Bean \$table_name must match vardefs table 'lf_pr_config'"
);
echo "  [PASS] Bean \$table_name matches vardefs table\n";

// --- Edge Case: Every custom field in vardefs has a 'name' key that matches the field key ---
foreach (array_keys($customFields) as $customFieldName) {
    if (isset($fields[$customFieldName]['name'])) {
        assert(
            $fields[$customFieldName]['name'] === $customFieldName,
            "Vardefs field '{$customFieldName}' 'name' property should match its key"
        );
    }
}
echo "  [PASS] Custom field 'name' properties match their keys\n";

// --- Edge Case: All vardefs fields have a 'type' property ---
foreach ($fields as $fieldName => $fieldDef) {
    assert(
        isset($fieldDef['type']),
        "Vardefs field '{$fieldName}' must have a 'type' property"
    );
}
echo "  [PASS] All vardefs fields have 'type' property\n";

// --- Negative Case: Bean file does NOT use 'name' as the config key field ---
// The getConfig method should query by 'config_name', not 'name'
// (We check that both the getConfig and getAll methods reference config_name)
assert(
    str_contains($beanContent, 'config_name'),
    "Bean class must reference 'config_name' field (NOT just 'name')"
);
echo "  [PASS] Bean class references 'config_name' field\n";


echo "\n==============================\n";
echo "US-004: All tests passed!\n";
echo "==============================\n";
