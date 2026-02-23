<?php
/**
 * US-008: LF_PlanProspectItem Bean, Vardefs, and Language Tests
 *
 * Tests that the LF_PlanProspectItem Bean class, vardefs, and language file
 * exist with correct structure, properties, methods, and field definitions.
 *
 * This module stores prospecting plan items that have no CRM opportunity yet.
 * When converted, the convertToOpportunity() method creates an Account and
 * Opportunity via BeanFactory, reads default stage from LF_PRConfig, and
 * sets converted_opportunity_id and status='converted'.
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
    . DIRECTORY_SEPARATOR . 'LF_PlanProspectItem'
    . DIRECTORY_SEPARATOR . 'LF_PlanProspectItem.php';

$vardefsFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_PlanProspectItem'
    . DIRECTORY_SEPARATOR . 'metadata'
    . DIRECTORY_SEPARATOR . 'vardefs.php';

$languageFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_PlanProspectItem'
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
    'lf_weekly_plan_id' => [
        'type' => 'id',
        'required' => true,
    ],
    'source_type' => [
        'type' => 'varchar',
        'len' => 100,
    ],
    'planned_day' => [
        'type' => 'enum',
        'options' => 'lf_planned_day_dom',
    ],
    'expected_value' => [
        'type' => 'decimal',
        'dbType' => 'decimal',
        'len' => '26,6',
    ],
    'plan_description' => [
        'type' => 'text',
    ],
    'status' => [
        'type' => 'enum',
        'options' => 'lf_prospect_status_dom',
        'default' => 'planned',
    ],
    'converted_opportunity_id' => [
        'type' => 'id',
    ],
    'prospecting_notes' => [
        'type' => 'text',
    ],
];

// Expected language labels
$expectedLabels = [
    'LBL_MODULE_NAME',
    'LBL_LF_WEEKLY_PLAN_ID',
    'LBL_SOURCE_TYPE',
    'LBL_PLANNED_DAY',
    'LBL_EXPECTED_VALUE',
    'LBL_PLAN_DESCRIPTION',
    'LBL_STATUS',
    'LBL_CONVERTED_OPPORTUNITY_ID',
    'LBL_PROSPECTING_NOTES',
];


// ============================================================
// Section 1: Bean File Existence
// ============================================================
echo "Section 1: Bean File Existence\n";

// --- Happy Path: Bean file exists ---
assert(
    file_exists($beanFile),
    "Bean file should exist at: custom/modules/LF_PlanProspectItem/LF_PlanProspectItem.php"
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

// --- Happy Path: File contains class LF_PlanProspectItem extending SugarBean ---
assert(
    preg_match('/class\s+LF_PlanProspectItem\s+extends\s+SugarBean/', $beanContent) === 1,
    "Bean file must contain 'class LF_PlanProspectItem extends SugarBean'"
);
echo "  [PASS] Bean class extends SugarBean\n";

// --- Happy Path: File has #[\AllowDynamicProperties] attribute for PHP 8.2 ---
assert(
    str_contains($beanContent, '#[\AllowDynamicProperties]') || str_contains($beanContent, '#[AllowDynamicProperties]'),
    "Bean file must have #[\\AllowDynamicProperties] attribute for PHP 8.2 compatibility"
);
echo "  [PASS] Bean class has AllowDynamicProperties attribute\n";

// --- Happy Path: $table_name = 'lf_plan_prospect_items' ---
assert(
    preg_match('/\$table_name\s*=\s*[\'"]lf_plan_prospect_items[\'"]/', $beanContent) === 1,
    "Bean must have \$table_name = 'lf_plan_prospect_items'"
);
echo "  [PASS] Bean has \$table_name = 'lf_plan_prospect_items'\n";

// --- Happy Path: $object_name = 'LF_PlanProspectItem' ---
assert(
    preg_match('/\$object_name\s*=\s*[\'"]LF_PlanProspectItem[\'"]/', $beanContent) === 1,
    "Bean must have \$object_name = 'LF_PlanProspectItem'"
);
echo "  [PASS] Bean has \$object_name = 'LF_PlanProspectItem'\n";

// --- Happy Path: $module_name = 'LF_PlanProspectItem' ---
assert(
    preg_match('/\$module_name\s*=\s*[\'"]LF_PlanProspectItem[\'"]/', $beanContent) === 1,
    "Bean must have \$module_name = 'LF_PlanProspectItem'"
);
echo "  [PASS] Bean has \$module_name = 'LF_PlanProspectItem'\n";

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
// Section 4: Bean convertToOpportunity() Method
// ============================================================
echo "\nSection 4: Bean convertToOpportunity() Method\n";

// --- Happy Path: File contains convertToOpportunity method ---
assert(
    preg_match('/public\s+function\s+convertToOpportunity\s*\(/', $beanContent) === 1,
    "Bean must have 'public function convertToOpportunity(' method"
);
echo "  [PASS] Bean has convertToOpportunity() method\n";

// --- Happy Path: convertToOpportunity has 3 parameters ($accountName, $oppName, $amount) ---
assert(
    preg_match('/function\s+convertToOpportunity\s*\(\s*\$\w+\s*,\s*\$\w+\s*,\s*\$\w+\s*\)/', $beanContent) === 1,
    "convertToOpportunity() must have exactly 3 parameters (\$accountName, \$oppName, \$amount)"
);
echo "  [PASS] convertToOpportunity() has 3 parameters\n";

// --- Happy Path: convertToOpportunity uses BeanFactory::newBean for Accounts ---
assert(
    str_contains($beanContent, "BeanFactory::newBean('Accounts')") || str_contains($beanContent, 'BeanFactory::newBean("Accounts")'),
    "convertToOpportunity() must use BeanFactory::newBean('Accounts') to find or create Account"
);
echo "  [PASS] convertToOpportunity() uses BeanFactory::newBean('Accounts')\n";

// --- Happy Path: convertToOpportunity uses BeanFactory::newBean for Opportunities ---
assert(
    str_contains($beanContent, "BeanFactory::newBean('Opportunities')") || str_contains($beanContent, 'BeanFactory::newBean("Opportunities")'),
    "convertToOpportunity() must use BeanFactory::newBean('Opportunities') to create Opportunity"
);
echo "  [PASS] convertToOpportunity() uses BeanFactory::newBean('Opportunities')\n";

// --- Happy Path: convertToOpportunity reads default_conversion_stage from LF_PRConfig ---
assert(
    str_contains($beanContent, 'LF_PRConfig::getConfig'),
    "convertToOpportunity() must read default_conversion_stage from LF_PRConfig::getConfig"
);
echo "  [PASS] convertToOpportunity() references LF_PRConfig::getConfig\n";

// --- Happy Path: convertToOpportunity references 'prospecting' category for config ---
assert(
    str_contains($beanContent, "'prospecting'") || str_contains($beanContent, '"prospecting"'),
    "convertToOpportunity() must reference 'prospecting' category for LF_PRConfig::getConfig"
);
echo "  [PASS] convertToOpportunity() references 'prospecting' category\n";

// --- Happy Path: convertToOpportunity references 'default_conversion_stage' config key ---
assert(
    str_contains($beanContent, "'default_conversion_stage'") || str_contains($beanContent, '"default_conversion_stage"'),
    "convertToOpportunity() must reference 'default_conversion_stage' config key"
);
echo "  [PASS] convertToOpportunity() references 'default_conversion_stage' config key\n";

// --- Happy Path: convertToOpportunity sets converted_opportunity_id ---
assert(
    str_contains($beanContent, 'converted_opportunity_id'),
    "convertToOpportunity() must set \$this->converted_opportunity_id"
);
echo "  [PASS] convertToOpportunity() references converted_opportunity_id\n";

// --- Happy Path: convertToOpportunity sets status to 'converted' ---
assert(
    str_contains($beanContent, "'converted'") || str_contains($beanContent, '"converted"'),
    "convertToOpportunity() must set status to 'converted'"
);
echo "  [PASS] convertToOpportunity() references 'converted' status\n";

// --- Happy Path: convertToOpportunity calls save() ---
assert(
    preg_match('/\$this\s*->\s*save\s*\(/', $beanContent) === 1,
    "convertToOpportunity() must call \$this->save() to persist changes"
);
echo "  [PASS] convertToOpportunity() calls \$this->save()\n";

// --- Negative Case: convertToOpportunity is NOT static (accesses $this) ---
assert(
    preg_match('/public\s+static\s+function\s+convertToOpportunity/', $beanContent) !== 1,
    "convertToOpportunity() must NOT be static - it needs access to \$this properties"
);
echo "  [PASS] convertToOpportunity() is not static (instance method)\n";

// --- Edge Case: convertToOpportunity references $this->status ---
assert(
    preg_match('/\$this\s*->\s*status/', $beanContent) === 1,
    "convertToOpportunity() must set \$this->status"
);
echo "  [PASS] convertToOpportunity() sets \$this->status\n";

// --- Edge Case: convertToOpportunity references $this->converted_opportunity_id ---
assert(
    preg_match('/\$this\s*->\s*converted_opportunity_id/', $beanContent) === 1,
    "convertToOpportunity() must set \$this->converted_opportunity_id"
);
echo "  [PASS] convertToOpportunity() sets \$this->converted_opportunity_id\n";


// ============================================================
// Section 5: Vardefs File Existence
// ============================================================
echo "\nSection 5: Vardefs File Existence\n";

// --- Happy Path: Vardefs file exists ---
assert(
    file_exists($vardefsFile),
    "Vardefs file should exist at: custom/modules/LF_PlanProspectItem/metadata/vardefs.php"
);
echo "  [PASS] Vardefs file exists\n";

// --- Happy Path: Vardefs file is a regular file ---
assert(
    is_file($vardefsFile),
    "Vardefs file path should be a regular file, not a directory"
);
echo "  [PASS] Vardefs file is a regular file\n";


// ============================================================
// Section 6: Vardefs PHP Format and sugarEntry Guard
// ============================================================
echo "\nSection 6: Vardefs PHP Format\n";

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
// Section 7: Vardefs Dictionary Structure
// ============================================================
echo "\nSection 7: Vardefs Dictionary Structure\n";

// Load vardefs data using temp file wrapper
$tempFile = tempnam(sys_get_temp_dir(), 'us008_vardefs_');
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

// --- Happy Path: Dictionary key matches $object_name: $dictionary['LF_PlanProspectItem'] ---
assert(
    array_key_exists('LF_PlanProspectItem', $dictionary),
    "\$dictionary must have key 'LF_PlanProspectItem' matching \$object_name"
);
echo "  [PASS] \$dictionary has key 'LF_PlanProspectItem'\n";

// --- Edge Case: Dictionary has exactly 1 entry (no extra dictionaries) ---
assert(
    count($dictionary) === 1,
    "\$dictionary should have exactly 1 entry, got: " . count($dictionary)
);
echo "  [PASS] \$dictionary has exactly 1 entry\n";

$vardefEntry = $dictionary['LF_PlanProspectItem'];

// --- Happy Path: Entry has 'table' key matching table_name ---
assert(
    array_key_exists('table', $vardefEntry),
    "Vardefs entry must have 'table' key"
);
assert(
    $vardefEntry['table'] === 'lf_plan_prospect_items',
    "Vardefs 'table' should be 'lf_plan_prospect_items', got: " . ($vardefEntry['table'] ?? 'NULL')
);
echo "  [PASS] Vardefs table is 'lf_plan_prospect_items'\n";

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
// Section 8: Standard SuiteCRM Fields (7 fields)
// ============================================================
echo "\nSection 8: Standard SuiteCRM Fields\n";

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
// Section 9: Custom Field - lf_weekly_plan_id (id, required)
// ============================================================
echo "\nSection 9: Custom Field - lf_weekly_plan_id\n";

// --- Happy Path: 'lf_weekly_plan_id' field exists with correct type ---
assert(
    array_key_exists('lf_weekly_plan_id', $fields),
    "Vardefs must include custom field 'lf_weekly_plan_id'"
);
assert(
    $fields['lf_weekly_plan_id']['type'] === 'id',
    "Field 'lf_weekly_plan_id' should have type 'id', got: " . ($fields['lf_weekly_plan_id']['type'] ?? 'NULL')
);
echo "  [PASS] 'lf_weekly_plan_id' field: id\n";

// --- Happy Path: 'lf_weekly_plan_id' field is required ---
assert(
    isset($fields['lf_weekly_plan_id']['required']) && $fields['lf_weekly_plan_id']['required'] === true,
    "Field 'lf_weekly_plan_id' should be required"
);
echo "  [PASS] 'lf_weekly_plan_id' field is required\n";

// --- Edge Case: 'lf_weekly_plan_id' has 'name' property matching its key ---
assert(
    isset($fields['lf_weekly_plan_id']['name']) && $fields['lf_weekly_plan_id']['name'] === 'lf_weekly_plan_id',
    "Field 'lf_weekly_plan_id' 'name' property should match its key"
);
echo "  [PASS] 'lf_weekly_plan_id' has correct 'name' property\n";


// ============================================================
// Section 10: Custom Field - source_type (varchar 100)
// ============================================================
echo "\nSection 10: Custom Field - source_type\n";

// --- Happy Path: 'source_type' field exists with correct type ---
assert(
    array_key_exists('source_type', $fields),
    "Vardefs must include custom field 'source_type'"
);
assert(
    $fields['source_type']['type'] === 'varchar',
    "Field 'source_type' should have type 'varchar', got: " . ($fields['source_type']['type'] ?? 'NULL')
);
echo "  [PASS] 'source_type' field: varchar\n";

// --- Happy Path: 'source_type' field has len 100 ---
assert(
    isset($fields['source_type']['len']) && ($fields['source_type']['len'] == 100),
    "Field 'source_type' should have len 100, got: " . ($fields['source_type']['len'] ?? 'NULL')
);
echo "  [PASS] 'source_type' field len: 100\n";

// --- Edge Case: 'source_type' is NOT required ---
assert(
    !isset($fields['source_type']['required']) || $fields['source_type']['required'] !== true,
    "Field 'source_type' should NOT be required"
);
echo "  [PASS] 'source_type' is not required\n";

// --- Edge Case: 'source_type' has 'name' property matching its key ---
assert(
    isset($fields['source_type']['name']) && $fields['source_type']['name'] === 'source_type',
    "Field 'source_type' 'name' property should match its key"
);
echo "  [PASS] 'source_type' has correct 'name' property\n";


// ============================================================
// Section 11: Custom Field - planned_day (enum)
// ============================================================
echo "\nSection 11: Custom Field - planned_day\n";

// --- Happy Path: 'planned_day' field exists with correct type ---
assert(
    array_key_exists('planned_day', $fields),
    "Vardefs must include custom field 'planned_day'"
);
assert(
    $fields['planned_day']['type'] === 'enum',
    "Field 'planned_day' should have type 'enum', got: " . ($fields['planned_day']['type'] ?? 'NULL')
);
echo "  [PASS] 'planned_day' field: enum\n";

// --- Happy Path: 'planned_day' field has options 'lf_planned_day_dom' ---
assert(
    isset($fields['planned_day']['options']) && $fields['planned_day']['options'] === 'lf_planned_day_dom',
    "Field 'planned_day' should have options 'lf_planned_day_dom', got: " . ($fields['planned_day']['options'] ?? 'NULL')
);
echo "  [PASS] 'planned_day' field options: lf_planned_day_dom\n";

// --- Edge Case: 'planned_day' has 'name' property matching its key ---
assert(
    isset($fields['planned_day']['name']) && $fields['planned_day']['name'] === 'planned_day',
    "Field 'planned_day' 'name' property should match its key"
);
echo "  [PASS] 'planned_day' has correct 'name' property\n";


// ============================================================
// Section 12: Custom Field - expected_value (decimal)
// ============================================================
echo "\nSection 12: Custom Field - expected_value\n";

// --- Happy Path: 'expected_value' field exists with correct type ---
assert(
    array_key_exists('expected_value', $fields),
    "Vardefs must include custom field 'expected_value'"
);
assert(
    $fields['expected_value']['type'] === 'decimal',
    "Field 'expected_value' should have type 'decimal', got: " . ($fields['expected_value']['type'] ?? 'NULL')
);
echo "  [PASS] 'expected_value' field: decimal\n";

// --- Happy Path: 'expected_value' has dbType='decimal' ---
assert(
    isset($fields['expected_value']['dbType']) && $fields['expected_value']['dbType'] === 'decimal',
    "Field 'expected_value' should have dbType 'decimal', got: " . ($fields['expected_value']['dbType'] ?? 'NULL')
);
echo "  [PASS] 'expected_value' field dbType: decimal\n";

// --- Happy Path: 'expected_value' has len='26,6' ---
assert(
    isset($fields['expected_value']['len']) && $fields['expected_value']['len'] === '26,6',
    "Field 'expected_value' should have len '26,6', got: " . ($fields['expected_value']['len'] ?? 'NULL')
);
echo "  [PASS] 'expected_value' field len: 26,6\n";

// --- Edge Case: 'expected_value' is NOT required ---
assert(
    !isset($fields['expected_value']['required']) || $fields['expected_value']['required'] !== true,
    "Field 'expected_value' should NOT be required"
);
echo "  [PASS] 'expected_value' is not required\n";

// --- Edge Case: 'expected_value' has 'name' property matching its key ---
assert(
    isset($fields['expected_value']['name']) && $fields['expected_value']['name'] === 'expected_value',
    "Field 'expected_value' 'name' property should match its key"
);
echo "  [PASS] 'expected_value' has correct 'name' property\n";


// ============================================================
// Section 13: Custom Field - plan_description (text)
// ============================================================
echo "\nSection 13: Custom Field - plan_description\n";

// --- Happy Path: 'plan_description' field exists with correct type ---
assert(
    array_key_exists('plan_description', $fields),
    "Vardefs must include custom field 'plan_description'"
);
assert(
    $fields['plan_description']['type'] === 'text',
    "Field 'plan_description' should have type 'text', got: " . ($fields['plan_description']['type'] ?? 'NULL')
);
echo "  [PASS] 'plan_description' field: text\n";

// --- Edge Case: 'plan_description' is NOT required ---
assert(
    !isset($fields['plan_description']['required']) || $fields['plan_description']['required'] !== true,
    "Field 'plan_description' should NOT be required"
);
echo "  [PASS] 'plan_description' is not required\n";

// --- Edge Case: 'plan_description' has 'name' property matching its key ---
assert(
    isset($fields['plan_description']['name']) && $fields['plan_description']['name'] === 'plan_description',
    "Field 'plan_description' 'name' property should match its key"
);
echo "  [PASS] 'plan_description' has correct 'name' property\n";


// ============================================================
// Section 14: Custom Field - status (enum with default)
// ============================================================
echo "\nSection 14: Custom Field - status\n";

// --- Happy Path: 'status' field exists with correct type ---
assert(
    array_key_exists('status', $fields),
    "Vardefs must include custom field 'status'"
);
assert(
    $fields['status']['type'] === 'enum',
    "Field 'status' should have type 'enum', got: " . ($fields['status']['type'] ?? 'NULL')
);
echo "  [PASS] 'status' field: enum\n";

// --- Happy Path: 'status' field has options 'lf_prospect_status_dom' ---
assert(
    isset($fields['status']['options']) && $fields['status']['options'] === 'lf_prospect_status_dom',
    "Field 'status' should have options 'lf_prospect_status_dom', got: " . ($fields['status']['options'] ?? 'NULL')
);
echo "  [PASS] 'status' field options: lf_prospect_status_dom\n";

// --- Happy Path: 'status' field has default 'planned' ---
assert(
    isset($fields['status']['default']) && $fields['status']['default'] === 'planned',
    "Field 'status' should have default 'planned', got: " . ($fields['status']['default'] ?? 'NULL')
);
echo "  [PASS] 'status' field default: planned\n";

// --- Edge Case: 'status' has 'name' property matching its key ---
assert(
    isset($fields['status']['name']) && $fields['status']['name'] === 'status',
    "Field 'status' 'name' property should match its key"
);
echo "  [PASS] 'status' has correct 'name' property\n";


// ============================================================
// Section 15: Custom Field - converted_opportunity_id (id)
// ============================================================
echo "\nSection 15: Custom Field - converted_opportunity_id\n";

// --- Happy Path: 'converted_opportunity_id' field exists with correct type ---
assert(
    array_key_exists('converted_opportunity_id', $fields),
    "Vardefs must include custom field 'converted_opportunity_id'"
);
assert(
    $fields['converted_opportunity_id']['type'] === 'id',
    "Field 'converted_opportunity_id' should have type 'id', got: " . ($fields['converted_opportunity_id']['type'] ?? 'NULL')
);
echo "  [PASS] 'converted_opportunity_id' field: id\n";

// --- Edge Case: 'converted_opportunity_id' is NOT required (only set after conversion) ---
assert(
    !isset($fields['converted_opportunity_id']['required']) || $fields['converted_opportunity_id']['required'] !== true,
    "Field 'converted_opportunity_id' should NOT be required (only populated on conversion)"
);
echo "  [PASS] 'converted_opportunity_id' is not required\n";

// --- Edge Case: 'converted_opportunity_id' has 'name' property matching its key ---
assert(
    isset($fields['converted_opportunity_id']['name']) && $fields['converted_opportunity_id']['name'] === 'converted_opportunity_id',
    "Field 'converted_opportunity_id' 'name' property should match its key"
);
echo "  [PASS] 'converted_opportunity_id' has correct 'name' property\n";


// ============================================================
// Section 16: Custom Field - prospecting_notes (text)
// ============================================================
echo "\nSection 16: Custom Field - prospecting_notes\n";

// --- Happy Path: 'prospecting_notes' field exists with correct type ---
assert(
    array_key_exists('prospecting_notes', $fields),
    "Vardefs must include custom field 'prospecting_notes'"
);
assert(
    $fields['prospecting_notes']['type'] === 'text',
    "Field 'prospecting_notes' should have type 'text', got: " . ($fields['prospecting_notes']['type'] ?? 'NULL')
);
echo "  [PASS] 'prospecting_notes' field: text\n";

// --- Edge Case: 'prospecting_notes' is NOT required ---
assert(
    !isset($fields['prospecting_notes']['required']) || $fields['prospecting_notes']['required'] !== true,
    "Field 'prospecting_notes' should NOT be required"
);
echo "  [PASS] 'prospecting_notes' is not required\n";

// --- Edge Case: 'prospecting_notes' has 'name' property matching its key ---
assert(
    isset($fields['prospecting_notes']['name']) && $fields['prospecting_notes']['name'] === 'prospecting_notes',
    "Field 'prospecting_notes' 'name' property should match its key"
);
echo "  [PASS] 'prospecting_notes' has correct 'name' property\n";


// ============================================================
// Section 17: Indices
// ============================================================
echo "\nSection 17: Indices\n";

// --- Happy Path: Vardefs entry has 'indices' key ---
assert(
    array_key_exists('indices', $vardefEntry),
    "Vardefs entry must have 'indices' key for index definitions"
);
assert(
    is_array($vardefEntry['indices']),
    "Vardefs 'indices' should be an array"
);
echo "  [PASS] Vardefs has 'indices' array\n";

$indices = $vardefEntry['indices'];

// --- Happy Path: Index on lf_weekly_plan_id for efficient plan lookups ---
$foundPlanIndex = false;
foreach ($indices as $indexDef) {
    if (isset($indexDef['fields']) && is_array($indexDef['fields'])) {
        if (in_array('lf_weekly_plan_id', $indexDef['fields'])) {
            $foundPlanIndex = true;
            break;
        }
    }
}
assert(
    $foundPlanIndex,
    "Vardefs must have an index on 'lf_weekly_plan_id' for efficient plan lookups"
);
echo "  [PASS] Index on lf_weekly_plan_id exists\n";

// --- Edge Case: lf_weekly_plan_id index has a 'name' property ---
$planIndexHasName = false;
foreach ($indices as $indexDef) {
    if (isset($indexDef['fields']) && is_array($indexDef['fields'])) {
        if (in_array('lf_weekly_plan_id', $indexDef['fields'])) {
            $planIndexHasName = isset($indexDef['name']) && !empty($indexDef['name']);
            break;
        }
    }
}
assert(
    $planIndexHasName,
    "Index on lf_weekly_plan_id must have a 'name' property"
);
echo "  [PASS] lf_weekly_plan_id index has a name\n";

// --- Edge Case: lf_weekly_plan_id index has type 'index' ---
$planIndexType = null;
foreach ($indices as $indexDef) {
    if (isset($indexDef['fields']) && is_array($indexDef['fields'])) {
        if (in_array('lf_weekly_plan_id', $indexDef['fields'])) {
            $planIndexType = $indexDef['type'] ?? null;
            break;
        }
    }
}
assert(
    $planIndexType === 'index',
    "Index on lf_weekly_plan_id must have type 'index', got: " . ($planIndexType ?? 'NULL')
);
echo "  [PASS] lf_weekly_plan_id index has type 'index'\n";


// ============================================================
// Section 18: Field Count Validation
// ============================================================
echo "\nSection 18: Field Count Validation\n";

// Total expected: 7 standard + 8 custom = 15 fields
$expectedFieldCount = count($standardFields) + count($customFields);

// --- Edge Case: Exactly 15 fields (no extra, no missing) ---
assert(
    count($fields) === $expectedFieldCount,
    "Vardefs should have exactly {$expectedFieldCount} fields (7 standard + 8 custom), got: " . count($fields)
);
echo "  [PASS] Vardefs has exactly {$expectedFieldCount} fields\n";

// --- Edge Case: All custom field names are present ---
foreach (array_keys($customFields) as $customFieldName) {
    assert(
        array_key_exists($customFieldName, $fields),
        "Custom field '{$customFieldName}' must exist in vardefs fields"
    );
}
echo "  [PASS] All 8 custom fields are present\n";


// ============================================================
// Section 19: Language File Existence
// ============================================================
echo "\nSection 19: Language File Existence\n";

// --- Happy Path: Language file exists ---
assert(
    file_exists($languageFile),
    "Language file should exist at: custom/modules/LF_PlanProspectItem/language/en_us.lang.php"
);
echo "  [PASS] Language file exists\n";

// --- Happy Path: Language file is a regular file ---
assert(
    is_file($languageFile),
    "Language file path should be a regular file, not a directory"
);
echo "  [PASS] Language file is a regular file\n";


// ============================================================
// Section 20: Language File PHP Format and sugarEntry Guard
// ============================================================
echo "\nSection 20: Language File PHP Format\n";

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
// Section 21: Language File Labels ($mod_strings)
// ============================================================
echo "\nSection 21: Language File Labels\n";

// Load language data using temp file wrapper
$tempFile = tempnam(sys_get_temp_dir(), 'us008_lang_');
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

// --- Happy Path: File uses $mod_strings variable (not $app_list_strings or $app_strings) ---
assert(
    str_contains($langContent, '$mod_strings'),
    "Language file must use \$mod_strings variable"
);
echo "  [PASS] Language file uses \$mod_strings variable\n";

// --- Negative Case: File does NOT use $app_list_strings (that's for dropdowns) ---
assert(
    !str_contains($langContent, '$app_list_strings'),
    "Language file must NOT use \$app_list_strings (reserved for application-level dropdowns)"
);
echo "  [PASS] Language file does not use \$app_list_strings\n";

// --- Happy Path: All expected labels exist ---
foreach ($expectedLabels as $labelKey) {
    assert(
        array_key_exists($labelKey, $modStrings),
        "\$mod_strings must contain label key '{$labelKey}'"
    );
}
echo "  [PASS] All expected label keys exist in \$mod_strings\n";

// --- Happy Path: LBL_MODULE_NAME is defined as a non-empty string ---
assert(
    array_key_exists('LBL_MODULE_NAME', $modStrings),
    "\$mod_strings must contain 'LBL_MODULE_NAME'"
);
assert(
    is_string($modStrings['LBL_MODULE_NAME']) && strlen(trim($modStrings['LBL_MODULE_NAME'])) > 0,
    "LBL_MODULE_NAME must be a non-empty string"
);
echo "  [PASS] LBL_MODULE_NAME is a non-empty string\n";

// --- Edge Case: All label values are non-empty strings ---
foreach ($modStrings as $key => $value) {
    assert(
        is_string($value) && strlen(trim($value)) > 0,
        "\$mod_strings['{$key}'] should be a non-empty string"
    );
}
echo "  [PASS] All label values are non-empty strings\n";

// --- Edge Case: At least 9 labels defined (no less than expected) ---
assert(
    count($modStrings) >= count($expectedLabels),
    "\$mod_strings should have at least " . count($expectedLabels) . " entries, got: " . count($modStrings)
);
echo "  [PASS] \$mod_strings has at least " . count($expectedLabels) . " entries\n";


// ============================================================
// Section 22: Cross-Validation
// ============================================================
echo "\nSection 22: Cross-Validation\n";

// --- Edge Case: Vardefs uses $dictionary variable, not other variable names ---
assert(
    str_contains($vardefsContent, '$dictionary'),
    "Vardefs file must use \$dictionary variable"
);
echo "  [PASS] Vardefs file uses \$dictionary variable\n";

// --- Edge Case: Vardefs dictionary key matches Bean $object_name ---
assert(
    preg_match('/\$object_name\s*=\s*[\'"]LF_PlanProspectItem[\'"]/', $beanContent) === 1,
    "Bean \$object_name must match vardefs dictionary key 'LF_PlanProspectItem'"
);
assert(
    array_key_exists('LF_PlanProspectItem', $dictionary),
    "Vardefs \$dictionary key must match Bean \$object_name 'LF_PlanProspectItem'"
);
echo "  [PASS] Bean \$object_name matches vardefs dictionary key\n";

// --- Edge Case: Vardefs table name matches Bean $table_name ---
assert(
    $vardefEntry['table'] === 'lf_plan_prospect_items',
    "Vardefs table must match Bean \$table_name 'lf_plan_prospect_items'"
);
assert(
    preg_match('/\$table_name\s*=\s*[\'"]lf_plan_prospect_items[\'"]/', $beanContent) === 1,
    "Bean \$table_name must match vardefs table 'lf_plan_prospect_items'"
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

// --- Edge Case: Bean references BeanFactory for newBean calls ---
assert(
    str_contains($beanContent, 'BeanFactory'),
    "Bean class must reference BeanFactory for Account and Opportunity creation"
);
echo "  [PASS] Bean class references BeanFactory\n";

// --- Edge Case: Bean references LF_PRConfig for configuration access ---
assert(
    str_contains($beanContent, 'LF_PRConfig'),
    "Bean class must reference LF_PRConfig for default_conversion_stage"
);
echo "  [PASS] Bean class references LF_PRConfig\n";

// --- Edge Case: Vardefs 'deleted' field has default '0' ---
assert(
    isset($fields['deleted']['default']) && ($fields['deleted']['default'] == '0'),
    "Standard field 'deleted' should have default '0', got: " . ($fields['deleted']['default'] ?? 'NULL')
);
echo "  [PASS] 'deleted' field has default '0'\n";

// --- Edge Case: Bean file references 'Accounts' module ---
assert(
    str_contains($beanContent, "'Accounts'") || str_contains($beanContent, '"Accounts"'),
    "Bean class must reference 'Accounts' module for BeanFactory::newBean"
);
echo "  [PASS] Bean class references 'Accounts' module\n";

// --- Edge Case: Bean file references 'Opportunities' module ---
assert(
    str_contains($beanContent, "'Opportunities'") || str_contains($beanContent, '"Opportunities"'),
    "Bean class must reference 'Opportunities' module for BeanFactory::newBean"
);
echo "  [PASS] Bean class references 'Opportunities' module\n";


echo "\n==============================\n";
echo "US-008: All tests passed!\n";
echo "==============================\n";
