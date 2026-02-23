<?php
/**
 * US-007: LF_PlanOpItem Bean, Vardefs, and Language Tests
 *
 * Tests that the LF_PlanOpItem Bean class, vardefs, and language file
 * exist with correct structure, properties, methods, and field definitions.
 *
 * This module stores ONLY new planning info. Account name, opportunity name,
 * amount, current stage, and probability are read live from the linked
 * opportunity record via getOpportunityData(), NOT duplicated in this table.
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
    . DIRECTORY_SEPARATOR . 'LF_PlanOpItem'
    . DIRECTORY_SEPARATOR . 'LF_PlanOpItem.php';

$vardefsFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_PlanOpItem'
    . DIRECTORY_SEPARATOR . 'metadata'
    . DIRECTORY_SEPARATOR . 'vardefs.php';

$languageFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_PlanOpItem'
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
    'opportunity_id' => [
        'type' => 'id',
        'required' => true,
    ],
    'item_type' => [
        'type' => 'enum',
        'options' => 'lf_plan_item_type_dom',
    ],
    'projected_stage' => [
        'type' => 'varchar',
        'len' => 100,
    ],
    'planned_day' => [
        'type' => 'enum',
        'options' => 'lf_planned_day_dom',
    ],
    'plan_description' => [
        'type' => 'text',
    ],
];

// Fields that must NOT exist in vardefs (live from opportunity record)
$forbiddenFields = [
    'account_name',
    'amount',
    'current_stage',
    'probability',
    'sales_stage',
];

// Expected language labels
$expectedLabels = [
    'LBL_MODULE_NAME',
    'LBL_LF_WEEKLY_PLAN_ID',
    'LBL_OPPORTUNITY_ID',
    'LBL_ITEM_TYPE',
    'LBL_PROJECTED_STAGE',
    'LBL_PLANNED_DAY',
    'LBL_PLAN_DESCRIPTION',
];


// ============================================================
// Section 1: Bean File Existence
// ============================================================
echo "Section 1: Bean File Existence\n";

// --- Happy Path: Bean file exists ---
assert(
    file_exists($beanFile),
    "Bean file should exist at: custom/modules/LF_PlanOpItem/LF_PlanOpItem.php"
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

// --- Happy Path: File contains class LF_PlanOpItem extending SugarBean ---
assert(
    preg_match('/class\s+LF_PlanOpItem\s+extends\s+SugarBean/', $beanContent) === 1,
    "Bean file must contain 'class LF_PlanOpItem extends SugarBean'"
);
echo "  [PASS] Bean class extends SugarBean\n";

// --- Happy Path: File has #[\AllowDynamicProperties] attribute for PHP 8.2 ---
assert(
    str_contains($beanContent, '#[\AllowDynamicProperties]') || str_contains($beanContent, '#[AllowDynamicProperties]'),
    "Bean file must have #[\\AllowDynamicProperties] attribute for PHP 8.2 compatibility"
);
echo "  [PASS] Bean class has AllowDynamicProperties attribute\n";

// --- Happy Path: $table_name = 'lf_plan_op_items' ---
assert(
    preg_match('/\$table_name\s*=\s*[\'"]lf_plan_op_items[\'"]/', $beanContent) === 1,
    "Bean must have \$table_name = 'lf_plan_op_items'"
);
echo "  [PASS] Bean has \$table_name = 'lf_plan_op_items'\n";

// --- Happy Path: $object_name = 'LF_PlanOpItem' ---
assert(
    preg_match('/\$object_name\s*=\s*[\'"]LF_PlanOpItem[\'"]/', $beanContent) === 1,
    "Bean must have \$object_name = 'LF_PlanOpItem'"
);
echo "  [PASS] Bean has \$object_name = 'LF_PlanOpItem'\n";

// --- Happy Path: $module_name = 'LF_PlanOpItem' ---
assert(
    preg_match('/\$module_name\s*=\s*[\'"]LF_PlanOpItem[\'"]/', $beanContent) === 1,
    "Bean must have \$module_name = 'LF_PlanOpItem'"
);
echo "  [PASS] Bean has \$module_name = 'LF_PlanOpItem'\n";

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
// Section 4: Bean getOpportunityData() Method
// ============================================================
echo "\nSection 4: Bean getOpportunityData() Method\n";

// --- Happy Path: File contains getOpportunityData method ---
assert(
    preg_match('/public\s+function\s+getOpportunityData\s*\(/', $beanContent) === 1,
    "Bean must have 'public function getOpportunityData(' method"
);
echo "  [PASS] Bean has getOpportunityData() method\n";

// --- Happy Path: getOpportunityData has no required parameters (uses $this->opportunity_id) ---
assert(
    preg_match('/function\s+getOpportunityData\s*\(\s*\)/', $beanContent) === 1,
    "getOpportunityData() must have no required parameters (uses \$this->opportunity_id)"
);
echo "  [PASS] getOpportunityData() has no required parameters\n";

// --- Happy Path: getOpportunityData uses BeanFactory::getBean ---
assert(
    str_contains($beanContent, 'BeanFactory::getBean'),
    "getOpportunityData() must use BeanFactory::getBean to retrieve opportunity"
);
echo "  [PASS] getOpportunityData() uses BeanFactory::getBean\n";

// --- Happy Path: getOpportunityData references 'Opportunities' module ---
assert(
    str_contains($beanContent, "'Opportunities'") || str_contains($beanContent, '"Opportunities"'),
    "getOpportunityData() must reference 'Opportunities' module via BeanFactory::getBean"
);
echo "  [PASS] getOpportunityData() references 'Opportunities' module\n";

// --- Happy Path: getOpportunityData references opportunity_id property ---
assert(
    str_contains($beanContent, 'opportunity_id'),
    "getOpportunityData() must reference opportunity_id property (\$this->opportunity_id)"
);
echo "  [PASS] getOpportunityData() references opportunity_id\n";

// --- Negative Case: getOpportunityData is NOT static (it needs $this->opportunity_id) ---
assert(
    preg_match('/public\s+static\s+function\s+getOpportunityData/', $beanContent) !== 1,
    "getOpportunityData() must NOT be static - it needs access to \$this->opportunity_id"
);
echo "  [PASS] getOpportunityData() is not static (instance method)\n";

// --- Happy Path: getOpportunityData returns data with expected keys ---
// The method should return an associative array with: name, account_name, amount, sales_stage, probability
assert(
    str_contains($beanContent, 'name'),
    "getOpportunityData() must reference 'name' field from opportunity"
);
assert(
    str_contains($beanContent, 'account_name'),
    "getOpportunityData() must reference 'account_name' field from opportunity"
);
assert(
    str_contains($beanContent, 'amount'),
    "getOpportunityData() must reference 'amount' field from opportunity"
);
assert(
    str_contains($beanContent, 'sales_stage'),
    "getOpportunityData() must reference 'sales_stage' field from opportunity"
);
assert(
    str_contains($beanContent, 'probability'),
    "getOpportunityData() must reference 'probability' field from opportunity"
);
echo "  [PASS] getOpportunityData() references all 5 live opportunity fields\n";

// --- Edge Case: Method returns an array ---
assert(
    str_contains($beanContent, 'return') && str_contains($beanContent, '['),
    "getOpportunityData() must return an array"
);
echo "  [PASS] getOpportunityData() returns an array\n";


// ============================================================
// Section 5: Vardefs File Existence
// ============================================================
echo "\nSection 5: Vardefs File Existence\n";

// --- Happy Path: Vardefs file exists ---
assert(
    file_exists($vardefsFile),
    "Vardefs file should exist at: custom/modules/LF_PlanOpItem/metadata/vardefs.php"
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
$tempFile = tempnam(sys_get_temp_dir(), 'us007_vardefs_');
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

// --- Happy Path: Dictionary key matches $object_name: $dictionary['LF_PlanOpItem'] ---
assert(
    array_key_exists('LF_PlanOpItem', $dictionary),
    "\$dictionary must have key 'LF_PlanOpItem' matching \$object_name"
);
echo "  [PASS] \$dictionary has key 'LF_PlanOpItem'\n";

// --- Edge Case: Dictionary has exactly 1 entry (no extra dictionaries) ---
assert(
    count($dictionary) === 1,
    "\$dictionary should have exactly 1 entry, got: " . count($dictionary)
);
echo "  [PASS] \$dictionary has exactly 1 entry\n";

$vardefEntry = $dictionary['LF_PlanOpItem'];

// --- Happy Path: Entry has 'table' key matching table_name ---
assert(
    array_key_exists('table', $vardefEntry),
    "Vardefs entry must have 'table' key"
);
assert(
    $vardefEntry['table'] === 'lf_plan_op_items',
    "Vardefs 'table' should be 'lf_plan_op_items', got: " . ($vardefEntry['table'] ?? 'NULL')
);
echo "  [PASS] Vardefs table is 'lf_plan_op_items'\n";

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
// Section 9: Custom Field - lf_weekly_plan_id
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
// Section 10: Custom Field - opportunity_id
// ============================================================
echo "\nSection 10: Custom Field - opportunity_id\n";

// --- Happy Path: 'opportunity_id' field exists with correct type ---
assert(
    array_key_exists('opportunity_id', $fields),
    "Vardefs must include custom field 'opportunity_id'"
);
assert(
    $fields['opportunity_id']['type'] === 'id',
    "Field 'opportunity_id' should have type 'id', got: " . ($fields['opportunity_id']['type'] ?? 'NULL')
);
echo "  [PASS] 'opportunity_id' field: id\n";

// --- Happy Path: 'opportunity_id' field is required ---
assert(
    isset($fields['opportunity_id']['required']) && $fields['opportunity_id']['required'] === true,
    "Field 'opportunity_id' should be required"
);
echo "  [PASS] 'opportunity_id' field is required\n";

// --- Edge Case: 'opportunity_id' has 'name' property matching its key ---
assert(
    isset($fields['opportunity_id']['name']) && $fields['opportunity_id']['name'] === 'opportunity_id',
    "Field 'opportunity_id' 'name' property should match its key"
);
echo "  [PASS] 'opportunity_id' has correct 'name' property\n";


// ============================================================
// Section 11: Custom Field - item_type (enum)
// ============================================================
echo "\nSection 11: Custom Field - item_type (enum)\n";

// --- Happy Path: 'item_type' field exists with correct type ---
assert(
    array_key_exists('item_type', $fields),
    "Vardefs must include custom field 'item_type'"
);
assert(
    $fields['item_type']['type'] === 'enum',
    "Field 'item_type' should have type 'enum', got: " . ($fields['item_type']['type'] ?? 'NULL')
);
echo "  [PASS] 'item_type' field: enum\n";

// --- Happy Path: 'item_type' field has options 'lf_plan_item_type_dom' ---
assert(
    isset($fields['item_type']['options']) && $fields['item_type']['options'] === 'lf_plan_item_type_dom',
    "Field 'item_type' should have options 'lf_plan_item_type_dom', got: " . ($fields['item_type']['options'] ?? 'NULL')
);
echo "  [PASS] 'item_type' field options: lf_plan_item_type_dom\n";

// --- Edge Case: 'item_type' has 'name' property matching its key ---
assert(
    isset($fields['item_type']['name']) && $fields['item_type']['name'] === 'item_type',
    "Field 'item_type' 'name' property should match its key"
);
echo "  [PASS] 'item_type' has correct 'name' property\n";


// ============================================================
// Section 12: Custom Field - projected_stage (varchar)
// ============================================================
echo "\nSection 12: Custom Field - projected_stage\n";

// --- Happy Path: 'projected_stage' field exists with correct type ---
assert(
    array_key_exists('projected_stage', $fields),
    "Vardefs must include custom field 'projected_stage'"
);
assert(
    $fields['projected_stage']['type'] === 'varchar',
    "Field 'projected_stage' should have type 'varchar', got: " . ($fields['projected_stage']['type'] ?? 'NULL')
);
echo "  [PASS] 'projected_stage' field: varchar\n";

// --- Happy Path: 'projected_stage' field has len 100 ---
assert(
    isset($fields['projected_stage']['len']) && ($fields['projected_stage']['len'] == 100),
    "Field 'projected_stage' should have len 100, got: " . ($fields['projected_stage']['len'] ?? 'NULL')
);
echo "  [PASS] 'projected_stage' field len: 100\n";

// --- Edge Case: 'projected_stage' is NOT required ---
assert(
    !isset($fields['projected_stage']['required']) || $fields['projected_stage']['required'] !== true,
    "Field 'projected_stage' should NOT be required"
);
echo "  [PASS] 'projected_stage' is not required\n";

// --- Edge Case: 'projected_stage' has 'name' property matching its key ---
assert(
    isset($fields['projected_stage']['name']) && $fields['projected_stage']['name'] === 'projected_stage',
    "Field 'projected_stage' 'name' property should match its key"
);
echo "  [PASS] 'projected_stage' has correct 'name' property\n";


// ============================================================
// Section 13: Custom Field - planned_day (enum)
// ============================================================
echo "\nSection 13: Custom Field - planned_day (enum)\n";

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
// Section 14: Custom Field - plan_description (text)
// ============================================================
echo "\nSection 14: Custom Field - plan_description\n";

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
// Section 15: Negative Case - No Duplicate Opportunity Fields
// ============================================================
echo "\nSection 15: No Duplicate Opportunity Fields in Vardefs\n";

// --- Negative Case: account_name, amount, current_stage, probability, sales_stage NOT in vardefs ---
foreach ($forbiddenFields as $forbiddenField) {
    assert(
        !array_key_exists($forbiddenField, $fields),
        "Vardefs must NOT include field '{$forbiddenField}' - this data is read live from the opportunity record"
    );
}
echo "  [PASS] No opportunity fields duplicated in vardefs (account_name, amount, current_stage, probability, sales_stage)\n";

// --- Negative Case: Vardefs file content should not define these forbidden field names as array keys ---
foreach ($forbiddenFields as $forbiddenField) {
    assert(
        preg_match("/'" . preg_quote($forbiddenField, '/') . "'\s*=>\s*\[/", $vardefsContent) !== 1,
        "Vardefs file content must NOT define '{$forbiddenField}' as a field array key"
    );
}
echo "  [PASS] Vardefs file content does not define forbidden field keys\n";


// ============================================================
// Section 16: Indices
// ============================================================
echo "\nSection 16: Indices\n";

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

// --- Happy Path: Index on opportunity_id for reverse lookups ---
$foundOpportunityIndex = false;
foreach ($indices as $indexDef) {
    if (isset($indexDef['fields']) && is_array($indexDef['fields'])) {
        if (in_array('opportunity_id', $indexDef['fields'])) {
            $foundOpportunityIndex = true;
            break;
        }
    }
}
assert(
    $foundOpportunityIndex,
    "Vardefs must have an index on 'opportunity_id' for reverse lookups"
);
echo "  [PASS] Index on opportunity_id exists\n";

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

// --- Edge Case: opportunity_id index has a 'name' property ---
$oppIndexHasName = false;
foreach ($indices as $indexDef) {
    if (isset($indexDef['fields']) && is_array($indexDef['fields'])) {
        if (in_array('opportunity_id', $indexDef['fields'])) {
            $oppIndexHasName = isset($indexDef['name']) && !empty($indexDef['name']);
            break;
        }
    }
}
assert(
    $oppIndexHasName,
    "Index on opportunity_id must have a 'name' property"
);
echo "  [PASS] opportunity_id index has a name\n";

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

// --- Edge Case: opportunity_id index has type 'index' ---
$oppIndexType = null;
foreach ($indices as $indexDef) {
    if (isset($indexDef['fields']) && is_array($indexDef['fields'])) {
        if (in_array('opportunity_id', $indexDef['fields'])) {
            $oppIndexType = $indexDef['type'] ?? null;
            break;
        }
    }
}
assert(
    $oppIndexType === 'index',
    "Index on opportunity_id must have type 'index', got: " . ($oppIndexType ?? 'NULL')
);
echo "  [PASS] opportunity_id index has type 'index'\n";


// ============================================================
// Section 17: Field Count Validation
// ============================================================
echo "\nSection 17: Field Count Validation\n";

// Total expected: 7 standard + 6 custom = 13 fields
$expectedFieldCount = count($standardFields) + count($customFields);

// --- Edge Case: Exactly 13 fields (no extra, no missing) ---
assert(
    count($fields) === $expectedFieldCount,
    "Vardefs should have exactly {$expectedFieldCount} fields (7 standard + 6 custom), got: " . count($fields)
);
echo "  [PASS] Vardefs has exactly {$expectedFieldCount} fields\n";

// --- Edge Case: All custom field names are present ---
foreach (array_keys($customFields) as $customFieldName) {
    assert(
        array_key_exists($customFieldName, $fields),
        "Custom field '{$customFieldName}' must exist in vardefs fields"
    );
}
echo "  [PASS] All 6 custom fields are present\n";


// ============================================================
// Section 18: Language File Existence
// ============================================================
echo "\nSection 18: Language File Existence\n";

// --- Happy Path: Language file exists ---
assert(
    file_exists($languageFile),
    "Language file should exist at: custom/modules/LF_PlanOpItem/language/en_us.lang.php"
);
echo "  [PASS] Language file exists\n";

// --- Happy Path: Language file is a regular file ---
assert(
    is_file($languageFile),
    "Language file path should be a regular file, not a directory"
);
echo "  [PASS] Language file is a regular file\n";


// ============================================================
// Section 19: Language File PHP Format and sugarEntry Guard
// ============================================================
echo "\nSection 19: Language File PHP Format\n";

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
// Section 20: Language File Labels ($mod_strings)
// ============================================================
echo "\nSection 20: Language File Labels\n";

// Load language data using temp file wrapper
$tempFile = tempnam(sys_get_temp_dir(), 'us007_lang_');
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

// --- Edge Case: At least 7 labels defined (no less than expected) ---
assert(
    count($modStrings) >= count($expectedLabels),
    "\$mod_strings should have at least " . count($expectedLabels) . " entries, got: " . count($modStrings)
);
echo "  [PASS] \$mod_strings has at least " . count($expectedLabels) . " entries\n";


// ============================================================
// Section 21: Cross-Validation
// ============================================================
echo "\nSection 21: Cross-Validation\n";

// --- Edge Case: Vardefs uses $dictionary variable, not other variable names ---
assert(
    str_contains($vardefsContent, '$dictionary'),
    "Vardefs file must use \$dictionary variable"
);
echo "  [PASS] Vardefs file uses \$dictionary variable\n";

// --- Edge Case: Vardefs dictionary key matches Bean $object_name ---
assert(
    preg_match('/\$object_name\s*=\s*[\'"]LF_PlanOpItem[\'"]/', $beanContent) === 1,
    "Bean \$object_name must match vardefs dictionary key 'LF_PlanOpItem'"
);
assert(
    array_key_exists('LF_PlanOpItem', $dictionary),
    "Vardefs \$dictionary key must match Bean \$object_name 'LF_PlanOpItem'"
);
echo "  [PASS] Bean \$object_name matches vardefs dictionary key\n";

// --- Edge Case: Vardefs table name matches Bean $table_name ---
assert(
    $vardefEntry['table'] === 'lf_plan_op_items',
    "Vardefs table must match Bean \$table_name 'lf_plan_op_items'"
);
assert(
    preg_match('/\$table_name\s*=\s*[\'"]lf_plan_op_items[\'"]/', $beanContent) === 1,
    "Bean \$table_name must match vardefs table 'lf_plan_op_items'"
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

// --- Edge Case: Bean references opportunity_id for opportunity lookups ---
assert(
    str_contains($beanContent, 'opportunity_id'),
    "Bean class must reference 'opportunity_id' field for opportunity lookups"
);
echo "  [PASS] Bean class references 'opportunity_id' field\n";

// --- Edge Case: Bean references BeanFactory for opportunity data retrieval ---
assert(
    str_contains($beanContent, 'BeanFactory'),
    "Bean class must reference BeanFactory for opportunity data retrieval"
);
echo "  [PASS] Bean class references BeanFactory\n";

// --- Edge Case: Vardefs 'deleted' field has default '0' ---
assert(
    isset($fields['deleted']['default']) && ($fields['deleted']['default'] == '0'),
    "Standard field 'deleted' should have default '0', got: " . ($fields['deleted']['default'] ?? 'NULL')
);
echo "  [PASS] 'deleted' field has default '0'\n";


echo "\n==============================\n";
echo "US-007: All tests passed!\n";
echo "==============================\n";
