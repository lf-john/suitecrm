<?php
/**
 * US-005: LF_RepTargets Bean and Vardefs Tests
 *
 * Tests that the LF_RepTargets Bean class, vardefs, and language file
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
    . DIRECTORY_SEPARATOR . 'LF_RepTargets'
    . DIRECTORY_SEPARATOR . 'LF_RepTargets.php';

$vardefsFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_RepTargets'
    . DIRECTORY_SEPARATOR . 'metadata'
    . DIRECTORY_SEPARATOR . 'vardefs.php';

$languageFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_RepTargets'
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
    'assigned_user_id' => [
        'type' => 'id',
        'required' => true,
    ],
    'fiscal_year' => [
        'type' => 'int',
        'required' => true,
    ],
    'annual_quota' => [
        'type' => 'decimal',
        'dbType' => 'decimal',
        'len' => '26,6',
    ],
    'weekly_new_pipeline' => [
        'type' => 'decimal',
        'dbType' => 'decimal',
        'len' => '26,6',
    ],
    'weekly_progression' => [
        'type' => 'decimal',
        'dbType' => 'decimal',
        'len' => '26,6',
    ],
    'weekly_closed' => [
        'type' => 'decimal',
        'dbType' => 'decimal',
        'len' => '26,6',
    ],
    'is_active' => [
        'type' => 'bool',
        'default' => 1,
    ],
];

// Decimal fields that must have dbType='decimal' and len='26,6'
$decimalFields = [
    'annual_quota',
    'weekly_new_pipeline',
    'weekly_progression',
    'weekly_closed',
];

// Expected language labels
$expectedLabels = [
    'LBL_MODULE_NAME',
    'LBL_ASSIGNED_USER_ID',
    'LBL_FISCAL_YEAR',
    'LBL_ANNUAL_QUOTA',
    'LBL_WEEKLY_NEW_PIPELINE',
    'LBL_WEEKLY_PROGRESSION',
    'LBL_WEEKLY_CLOSED',
    'LBL_IS_ACTIVE',
];


// ============================================================
// Section 1: Bean File Existence
// ============================================================
echo "Section 1: Bean File Existence\n";

// --- Happy Path: Bean file exists ---
assert(
    file_exists($beanFile),
    "Bean file should exist at: custom/modules/LF_RepTargets/LF_RepTargets.php"
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

// --- Happy Path: File contains class LF_RepTargets extending SugarBean ---
assert(
    preg_match('/class\s+LF_RepTargets\s+extends\s+SugarBean/', $beanContent) === 1,
    "Bean file must contain 'class LF_RepTargets extends SugarBean'"
);
echo "  [PASS] Bean class extends SugarBean\n";

// --- Happy Path: File has #[\AllowDynamicProperties] attribute for PHP 8.2 ---
assert(
    str_contains($beanContent, '#[\AllowDynamicProperties]') || str_contains($beanContent, '#[AllowDynamicProperties]'),
    "Bean file must have #[\\AllowDynamicProperties] attribute for PHP 8.2 compatibility"
);
echo "  [PASS] Bean class has AllowDynamicProperties attribute\n";

// --- Happy Path: $table_name = 'lf_rep_targets' ---
assert(
    preg_match('/\$table_name\s*=\s*[\'"]lf_rep_targets[\'"]/', $beanContent) === 1,
    "Bean must have \$table_name = 'lf_rep_targets'"
);
echo "  [PASS] Bean has \$table_name = 'lf_rep_targets'\n";

// --- Happy Path: $object_name = 'LF_RepTargets' ---
assert(
    preg_match('/\$object_name\s*=\s*[\'"]LF_RepTargets[\'"]/', $beanContent) === 1,
    "Bean must have \$object_name = 'LF_RepTargets'"
);
echo "  [PASS] Bean has \$object_name = 'LF_RepTargets'\n";

// --- Happy Path: $module_name = 'LF_RepTargets' ---
assert(
    preg_match('/\$module_name\s*=\s*[\'"]LF_RepTargets[\'"]/', $beanContent) === 1,
    "Bean must have \$module_name = 'LF_RepTargets'"
);
echo "  [PASS] Bean has \$module_name = 'LF_RepTargets'\n";

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
// Section 4: Bean getActiveReps() Static Method
// ============================================================
echo "\nSection 4: Bean getActiveReps() Static Method\n";

// --- Happy Path: File contains static getActiveReps method ---
assert(
    preg_match('/public\s+static\s+function\s+getActiveReps\s*\(/', $beanContent) === 1,
    "Bean must have 'public static function getActiveReps(' method"
);
echo "  [PASS] Bean has static getActiveReps() method\n";

// --- Happy Path: getActiveReps has no required parameters ---
assert(
    preg_match('/function\s+getActiveReps\s*\(\s*\)/', $beanContent) === 1,
    "getActiveReps() must have no required parameters"
);
echo "  [PASS] getActiveReps() has no required parameters\n";

// --- Happy Path: getActiveReps queries with is_active=1 and deleted=0 ---
assert(
    str_contains($beanContent, 'is_active') && str_contains($beanContent, 'deleted'),
    "getActiveReps() must filter by is_active and deleted fields"
);
echo "  [PASS] getActiveReps() references is_active and deleted fields\n";

// --- Happy Path: getActiveReps joins with users table ---
assert(
    preg_match('/JOIN\s+users/i', $beanContent) === 1,
    "getActiveReps() must JOIN with users table"
);
echo "  [PASS] getActiveReps() joins with users table\n";

// --- Happy Path: getActiveReps retrieves user first_name and last_name ---
assert(
    str_contains($beanContent, 'first_name') && str_contains($beanContent, 'last_name'),
    "getActiveReps() must retrieve user first_name and last_name"
);
echo "  [PASS] getActiveReps() retrieves user first_name and last_name\n";

// --- Happy Path: getActiveReps references the lf_rep_targets table ---
assert(
    str_contains($beanContent, 'lf_rep_targets'),
    "getActiveReps() should reference the lf_rep_targets table"
);
echo "  [PASS] getActiveReps() references lf_rep_targets table\n";


// ============================================================
// Section 5: Bean getTargetsForYear() Static Method
// ============================================================
echo "\nSection 5: Bean getTargetsForYear() Static Method\n";

// --- Happy Path: File contains static getTargetsForYear method ---
assert(
    preg_match('/public\s+static\s+function\s+getTargetsForYear\s*\(/', $beanContent) === 1,
    "Bean must have 'public static function getTargetsForYear(' method"
);
echo "  [PASS] Bean has static getTargetsForYear() method\n";

// --- Happy Path: getTargetsForYear accepts $year parameter ---
assert(
    preg_match('/function\s+getTargetsForYear\s*\(\s*\$year\s*\)/', $beanContent) === 1,
    "getTargetsForYear() must accept \$year parameter"
);
echo "  [PASS] getTargetsForYear() has \$year parameter\n";

// --- Happy Path: getTargetsForYear queries by fiscal_year and deleted=0 ---
assert(
    str_contains($beanContent, 'fiscal_year'),
    "getTargetsForYear() must query by fiscal_year field"
);
echo "  [PASS] getTargetsForYear() references fiscal_year field\n";

// --- Happy Path: getTargetsForYear filters active targets ---
// The story says "all active target records for the specified fiscal_year where deleted=0"
assert(
    preg_match('/is_active\s*=\s*1/i', $beanContent) === 1
    || preg_match('/is_active\s*=\s*[\'"]1[\'"]/i', $beanContent) === 1,
    "getTargetsForYear() must filter by is_active=1"
);
echo "  [PASS] getTargetsForYear() filters by is_active=1\n";


// ============================================================
// Section 6: Vardefs File Existence
// ============================================================
echo "\nSection 6: Vardefs File Existence\n";

// --- Happy Path: Vardefs file exists ---
assert(
    file_exists($vardefsFile),
    "Vardefs file should exist at: custom/modules/LF_RepTargets/metadata/vardefs.php"
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
$tempFile = tempnam(sys_get_temp_dir(), 'us005_vardefs_');
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

// --- Happy Path: Dictionary key matches $object_name: $dictionary['LF_RepTargets'] ---
assert(
    array_key_exists('LF_RepTargets', $dictionary),
    "\$dictionary must have key 'LF_RepTargets' matching \$object_name"
);
echo "  [PASS] \$dictionary has key 'LF_RepTargets'\n";

// --- Edge Case: Dictionary has exactly 1 entry (no extra dictionaries) ---
assert(
    count($dictionary) === 1,
    "\$dictionary should have exactly 1 entry, got: " . count($dictionary)
);
echo "  [PASS] \$dictionary has exactly 1 entry\n";

$vardefEntry = $dictionary['LF_RepTargets'];

// --- Happy Path: Entry has 'table' key matching table_name ---
assert(
    array_key_exists('table', $vardefEntry),
    "Vardefs entry must have 'table' key"
);
assert(
    $vardefEntry['table'] === 'lf_rep_targets',
    "Vardefs 'table' should be 'lf_rep_targets', got: " . ($vardefEntry['table'] ?? 'NULL')
);
echo "  [PASS] Vardefs table is 'lf_rep_targets'\n";

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

// --- Happy Path: 'assigned_user_id' field exists with correct type ---
assert(
    array_key_exists('assigned_user_id', $fields),
    "Vardefs must include custom field 'assigned_user_id'"
);
assert(
    $fields['assigned_user_id']['type'] === 'id',
    "Field 'assigned_user_id' should have type 'id', got: " . ($fields['assigned_user_id']['type'] ?? 'NULL')
);
echo "  [PASS] 'assigned_user_id' field: id\n";

// --- Happy Path: 'assigned_user_id' field is required ---
assert(
    isset($fields['assigned_user_id']['required']) && $fields['assigned_user_id']['required'] === true,
    "Field 'assigned_user_id' should be required"
);
echo "  [PASS] 'assigned_user_id' field is required\n";

// --- Happy Path: 'fiscal_year' field exists with correct type ---
assert(
    array_key_exists('fiscal_year', $fields),
    "Vardefs must include custom field 'fiscal_year'"
);
assert(
    $fields['fiscal_year']['type'] === 'int',
    "Field 'fiscal_year' should have type 'int', got: " . ($fields['fiscal_year']['type'] ?? 'NULL')
);
echo "  [PASS] 'fiscal_year' field: int\n";

// --- Happy Path: 'fiscal_year' field is required ---
assert(
    isset($fields['fiscal_year']['required']) && $fields['fiscal_year']['required'] === true,
    "Field 'fiscal_year' should be required"
);
echo "  [PASS] 'fiscal_year' field is required\n";

// --- Happy Path: 'annual_quota' field exists with correct type, dbType, and len ---
assert(
    array_key_exists('annual_quota', $fields),
    "Vardefs must include custom field 'annual_quota'"
);
assert(
    $fields['annual_quota']['type'] === 'decimal',
    "Field 'annual_quota' should have type 'decimal', got: " . ($fields['annual_quota']['type'] ?? 'NULL')
);
assert(
    isset($fields['annual_quota']['dbType']) && $fields['annual_quota']['dbType'] === 'decimal',
    "Field 'annual_quota' should have dbType 'decimal', got: " . ($fields['annual_quota']['dbType'] ?? 'NULL')
);
assert(
    isset($fields['annual_quota']['len']) && $fields['annual_quota']['len'] === '26,6',
    "Field 'annual_quota' should have len '26,6', got: " . ($fields['annual_quota']['len'] ?? 'NULL')
);
echo "  [PASS] 'annual_quota' field: decimal(26,6) with dbType='decimal'\n";

// --- Happy Path: 'weekly_new_pipeline' field exists with correct type, dbType, and len ---
assert(
    array_key_exists('weekly_new_pipeline', $fields),
    "Vardefs must include custom field 'weekly_new_pipeline'"
);
assert(
    $fields['weekly_new_pipeline']['type'] === 'decimal',
    "Field 'weekly_new_pipeline' should have type 'decimal', got: " . ($fields['weekly_new_pipeline']['type'] ?? 'NULL')
);
assert(
    isset($fields['weekly_new_pipeline']['dbType']) && $fields['weekly_new_pipeline']['dbType'] === 'decimal',
    "Field 'weekly_new_pipeline' should have dbType 'decimal', got: " . ($fields['weekly_new_pipeline']['dbType'] ?? 'NULL')
);
assert(
    isset($fields['weekly_new_pipeline']['len']) && $fields['weekly_new_pipeline']['len'] === '26,6',
    "Field 'weekly_new_pipeline' should have len '26,6', got: " . ($fields['weekly_new_pipeline']['len'] ?? 'NULL')
);
echo "  [PASS] 'weekly_new_pipeline' field: decimal(26,6) with dbType='decimal'\n";

// --- Happy Path: 'weekly_progression' field exists with correct type, dbType, and len ---
assert(
    array_key_exists('weekly_progression', $fields),
    "Vardefs must include custom field 'weekly_progression'"
);
assert(
    $fields['weekly_progression']['type'] === 'decimal',
    "Field 'weekly_progression' should have type 'decimal', got: " . ($fields['weekly_progression']['type'] ?? 'NULL')
);
assert(
    isset($fields['weekly_progression']['dbType']) && $fields['weekly_progression']['dbType'] === 'decimal',
    "Field 'weekly_progression' should have dbType 'decimal', got: " . ($fields['weekly_progression']['dbType'] ?? 'NULL')
);
assert(
    isset($fields['weekly_progression']['len']) && $fields['weekly_progression']['len'] === '26,6',
    "Field 'weekly_progression' should have len '26,6', got: " . ($fields['weekly_progression']['len'] ?? 'NULL')
);
echo "  [PASS] 'weekly_progression' field: decimal(26,6) with dbType='decimal'\n";

// --- Happy Path: 'weekly_closed' field exists with correct type, dbType, and len ---
assert(
    array_key_exists('weekly_closed', $fields),
    "Vardefs must include custom field 'weekly_closed'"
);
assert(
    $fields['weekly_closed']['type'] === 'decimal',
    "Field 'weekly_closed' should have type 'decimal', got: " . ($fields['weekly_closed']['type'] ?? 'NULL')
);
assert(
    isset($fields['weekly_closed']['dbType']) && $fields['weekly_closed']['dbType'] === 'decimal',
    "Field 'weekly_closed' should have dbType 'decimal', got: " . ($fields['weekly_closed']['dbType'] ?? 'NULL')
);
assert(
    isset($fields['weekly_closed']['len']) && $fields['weekly_closed']['len'] === '26,6',
    "Field 'weekly_closed' should have len '26,6', got: " . ($fields['weekly_closed']['len'] ?? 'NULL')
);
echo "  [PASS] 'weekly_closed' field: decimal(26,6) with dbType='decimal'\n";

// --- Happy Path: 'is_active' field exists with correct type and default ---
assert(
    array_key_exists('is_active', $fields),
    "Vardefs must include custom field 'is_active'"
);
assert(
    $fields['is_active']['type'] === 'bool',
    "Field 'is_active' should have type 'bool', got: " . ($fields['is_active']['type'] ?? 'NULL')
);
assert(
    isset($fields['is_active']['default']),
    "Field 'is_active' must have a 'default' value"
);
// Default can be int 1 or string '1' - both are valid in SuiteCRM vardefs
assert(
    $fields['is_active']['default'] == 1,
    "Field 'is_active' should have default value 1, got: " . var_export($fields['is_active']['default'] ?? 'NULL', true)
);
echo "  [PASS] 'is_active' field: bool with default 1\n";


// ============================================================
// Section 11: Decimal Fields Consistency
// ============================================================
echo "\nSection 11: Decimal Fields Consistency\n";

// --- Edge Case: All 4 decimal fields use consistent dbType and len ---
foreach ($decimalFields as $decFieldName) {
    assert(
        array_key_exists($decFieldName, $fields),
        "Decimal field '{$decFieldName}' must exist in vardefs"
    );
    assert(
        $fields[$decFieldName]['type'] === 'decimal',
        "Decimal field '{$decFieldName}' must have type 'decimal', got: " . ($fields[$decFieldName]['type'] ?? 'NULL')
    );
    assert(
        isset($fields[$decFieldName]['dbType']) && $fields[$decFieldName]['dbType'] === 'decimal',
        "Decimal field '{$decFieldName}' must have dbType 'decimal', got: " . ($fields[$decFieldName]['dbType'] ?? 'NULL')
    );
    assert(
        isset($fields[$decFieldName]['len']) && $fields[$decFieldName]['len'] === '26,6',
        "Decimal field '{$decFieldName}' must have len '26,6', got: " . ($fields[$decFieldName]['len'] ?? 'NULL')
    );
}
echo "  [PASS] All 4 decimal fields have consistent dbType='decimal' and len='26,6'\n";


// ============================================================
// Section 12: Composite Index
// ============================================================
echo "\nSection 12: Composite Index\n";

// --- Happy Path: Vardefs entry has 'indices' key ---
assert(
    array_key_exists('indices', $vardefEntry),
    "Vardefs entry must have 'indices' key for composite index"
);
assert(
    is_array($vardefEntry['indices']),
    "Vardefs 'indices' should be an array"
);
echo "  [PASS] Vardefs has 'indices' array\n";

$indices = $vardefEntry['indices'];

// --- Happy Path: There is a composite index on assigned_user_id + fiscal_year ---
$foundCompositeIndex = false;
foreach ($indices as $indexDef) {
    if (isset($indexDef['fields']) && is_array($indexDef['fields'])) {
        $indexFields = $indexDef['fields'];
        if (in_array('assigned_user_id', $indexFields) && in_array('fiscal_year', $indexFields)) {
            $foundCompositeIndex = true;
            break;
        }
    }
}
assert(
    $foundCompositeIndex,
    "Vardefs must have a composite index including both 'assigned_user_id' and 'fiscal_year'"
);
echo "  [PASS] Composite index on assigned_user_id + fiscal_year exists\n";

// --- Edge Case: Composite index has exactly 2 fields ---
$compositeIndexFieldCount = 0;
foreach ($indices as $indexDef) {
    if (isset($indexDef['fields']) && is_array($indexDef['fields'])) {
        $indexFields = $indexDef['fields'];
        if (in_array('assigned_user_id', $indexFields) && in_array('fiscal_year', $indexFields)) {
            $compositeIndexFieldCount = count($indexFields);
            break;
        }
    }
}
assert(
    $compositeIndexFieldCount === 2,
    "Composite index should have exactly 2 fields, got: " . $compositeIndexFieldCount
);
echo "  [PASS] Composite index has exactly 2 fields\n";

// --- Edge Case: Composite index has a name property ---
$compositeIndexHasName = false;
foreach ($indices as $indexDef) {
    if (isset($indexDef['fields']) && is_array($indexDef['fields'])) {
        $indexFields = $indexDef['fields'];
        if (in_array('assigned_user_id', $indexFields) && in_array('fiscal_year', $indexFields)) {
            $compositeIndexHasName = isset($indexDef['name']) && !empty($indexDef['name']);
            break;
        }
    }
}
assert(
    $compositeIndexHasName,
    "Composite index must have a 'name' property"
);
echo "  [PASS] Composite index has a name\n";

// --- Edge Case: Composite index has a type property ---
$compositeIndexHasType = false;
foreach ($indices as $indexDef) {
    if (isset($indexDef['fields']) && is_array($indexDef['fields'])) {
        $indexFields = $indexDef['fields'];
        if (in_array('assigned_user_id', $indexFields) && in_array('fiscal_year', $indexFields)) {
            $compositeIndexHasType = isset($indexDef['type']) && !empty($indexDef['type']);
            break;
        }
    }
}
assert(
    $compositeIndexHasType,
    "Composite index must have a 'type' property"
);
echo "  [PASS] Composite index has a type\n";


// ============================================================
// Section 13: Field Count Validation
// ============================================================
echo "\nSection 13: Field Count Validation\n";

// Total expected: 7 standard + 7 custom = 14 fields
$expectedFieldCount = count($standardFields) + count($customFields);

// --- Edge Case: Exactly 14 fields (no extra, no missing) ---
assert(
    count($fields) === $expectedFieldCount,
    "Vardefs should have exactly {$expectedFieldCount} fields (7 standard + 7 custom), got: " . count($fields)
);
echo "  [PASS] Vardefs has exactly {$expectedFieldCount} fields\n";

// --- Edge Case: All custom field names are present ---
foreach (array_keys($customFields) as $customFieldName) {
    assert(
        array_key_exists($customFieldName, $fields),
        "Custom field '{$customFieldName}' must exist in vardefs fields"
    );
}
echo "  [PASS] All 7 custom fields are present\n";


// ============================================================
// Section 14: Language File Existence
// ============================================================
echo "\nSection 14: Language File Existence\n";

// --- Happy Path: Language file exists ---
assert(
    file_exists($languageFile),
    "Language file should exist at: custom/modules/LF_RepTargets/language/en_us.lang.php"
);
echo "  [PASS] Language file exists\n";

// --- Happy Path: Language file is a regular file ---
assert(
    is_file($languageFile),
    "Language file path should be a regular file, not a directory"
);
echo "  [PASS] Language file is a regular file\n";


// ============================================================
// Section 15: Language File PHP Format and sugarEntry Guard
// ============================================================
echo "\nSection 15: Language File PHP Format\n";

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
// Section 16: Language File Labels ($mod_strings)
// ============================================================
echo "\nSection 16: Language File Labels\n";

// Load language data using temp file wrapper
$tempFile = tempnam(sys_get_temp_dir(), 'us005_lang_');
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

// --- Edge Case: At least 8 labels defined (no less than expected) ---
assert(
    count($modStrings) >= count($expectedLabels),
    "\$mod_strings should have at least " . count($expectedLabels) . " entries, got: " . count($modStrings)
);
echo "  [PASS] \$mod_strings has at least " . count($expectedLabels) . " entries\n";


// ============================================================
// Section 17: Cross-Validation
// ============================================================
echo "\nSection 17: Cross-Validation\n";

// --- Edge Case: Vardefs uses $dictionary variable, not other variable names ---
assert(
    str_contains($vardefsContent, '$dictionary'),
    "Vardefs file must use \$dictionary variable"
);
echo "  [PASS] Vardefs file uses \$dictionary variable\n";

// --- Edge Case: Vardefs dictionary key matches Bean $object_name ---
assert(
    preg_match('/\$object_name\s*=\s*[\'"]LF_RepTargets[\'"]/', $beanContent) === 1,
    "Bean \$object_name must match vardefs dictionary key 'LF_RepTargets'"
);
assert(
    array_key_exists('LF_RepTargets', $dictionary),
    "Vardefs \$dictionary key must match Bean \$object_name 'LF_RepTargets'"
);
echo "  [PASS] Bean \$object_name matches vardefs dictionary key\n";

// --- Edge Case: Vardefs table name matches Bean $table_name ---
assert(
    $vardefEntry['table'] === 'lf_rep_targets',
    "Vardefs table must match Bean \$table_name 'lf_rep_targets'"
);
assert(
    preg_match('/\$table_name\s*=\s*[\'"]lf_rep_targets[\'"]/', $beanContent) === 1,
    "Bean \$table_name must match vardefs table 'lf_rep_targets'"
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

// --- Edge Case: Bean references assigned_user_id for user join ---
assert(
    str_contains($beanContent, 'assigned_user_id'),
    "Bean class must reference 'assigned_user_id' field for user join"
);
echo "  [PASS] Bean class references 'assigned_user_id' field\n";


echo "\n==============================\n";
echo "US-005: All tests passed!\n";
echo "==============================\n";
