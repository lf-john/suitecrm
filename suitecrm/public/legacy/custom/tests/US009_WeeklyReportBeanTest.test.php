<?php
/**
 * US-009: LF_WeeklyReport Bean, Vardefs, Language, and Menu Tests
 *
 * Tests that the LF_WeeklyReport Bean class, vardefs, language file, and Menu.php
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
    . DIRECTORY_SEPARATOR . 'LF_WeeklyReport'
    . DIRECTORY_SEPARATOR . 'LF_WeeklyReport.php';

$vardefsFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_WeeklyReport'
    . DIRECTORY_SEPARATOR . 'metadata'
    . DIRECTORY_SEPARATOR . 'vardefs.php';

$languageFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_WeeklyReport'
    . DIRECTORY_SEPARATOR . 'language'
    . DIRECTORY_SEPARATOR . 'en_us.lang.php';

$menuFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_WeeklyReport'
    . DIRECTORY_SEPARATOR . 'Menu.php';

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
    ],
    'assigned_user_id' => [
        'type' => 'id',
        'required' => true,
    ],
    'week_start_date' => [
        'type' => 'date',
        'required' => true,
    ],
    'status' => [
        'type' => 'enum',
        'options' => 'lf_plan_status_dom',
        'default' => 'in_progress',
    ],
    'submitted_date' => [
        'type' => 'datetime',
    ],
    'reviewed_by' => [
        'type' => 'id',
    ],
    'reviewed_date' => [
        'type' => 'datetime',
    ],
    'notes' => [
        'type' => 'text',
    ],
];

// Expected language labels
$expectedLabels = [
    'LBL_MODULE_NAME',
    'LBL_LF_WEEKLY_PLAN_ID',
    'LBL_ASSIGNED_USER_ID',
    'LBL_WEEK_START_DATE',
    'LBL_STATUS',
    'LBL_SUBMITTED_DATE',
    'LBL_REVIEWED_BY',
    'LBL_REVIEWED_DATE',
    'LBL_NOTES',
];


// ============================================================
// Section 1: Bean File Existence
// ============================================================
echo "Section 1: Bean File Existence\n";

// --- Happy Path: Bean file exists ---
assert(
    file_exists($beanFile),
    "Bean file should exist at: custom/modules/LF_WeeklyReport/LF_WeeklyReport.php"
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

// --- Happy Path: File contains class LF_WeeklyReport extending SugarBean ---
assert(
    preg_match('/class\s+LF_WeeklyReport\s+extends\s+SugarBean/', $beanContent) === 1,
    "Bean file must contain 'class LF_WeeklyReport extends SugarBean'"
);
echo "  [PASS] Bean class extends SugarBean\n";

// --- Happy Path: File has #[\AllowDynamicProperties] attribute for PHP 8.2 ---
assert(
    str_contains($beanContent, '#[\AllowDynamicProperties]') || str_contains($beanContent, '#[AllowDynamicProperties]'),
    "Bean file must have #[\\AllowDynamicProperties] attribute for PHP 8.2 compatibility"
);
echo "  [PASS] Bean class has AllowDynamicProperties attribute\n";

// --- Happy Path: $table_name = 'lf_weekly_report' ---
assert(
    preg_match('/\$table_name\s*=\s*[\'"]lf_weekly_report[\'"]/', $beanContent) === 1,
    "Bean must have \$table_name = 'lf_weekly_report'"
);
echo "  [PASS] Bean has \$table_name = 'lf_weekly_report'\n";

// --- Happy Path: $object_name = 'LF_WeeklyReport' ---
assert(
    preg_match('/\$object_name\s*=\s*[\'"]LF_WeeklyReport[\'"]/', $beanContent) === 1,
    "Bean must have \$object_name = 'LF_WeeklyReport'"
);
echo "  [PASS] Bean has \$object_name = 'LF_WeeklyReport'\n";

// --- Happy Path: $module_name = 'LF_WeeklyReport' ---
assert(
    preg_match('/\$module_name\s*=\s*[\'"]LF_WeeklyReport[\'"]/', $beanContent) === 1,
    "Bean must have \$module_name = 'LF_WeeklyReport'"
);
echo "  [PASS] Bean has \$module_name = 'LF_WeeklyReport'\n";

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
// Section 4: Bean getOrCreateForWeek() Static Method
// ============================================================
echo "\nSection 4: Bean getOrCreateForWeek() Static Method\n";

// --- Happy Path: File contains static getOrCreateForWeek method ---
assert(
    preg_match('/public\s+static\s+function\s+getOrCreateForWeek\s*\(/', $beanContent) === 1,
    "Bean must have 'public static function getOrCreateForWeek(' method"
);
echo "  [PASS] Bean has static getOrCreateForWeek() method\n";

// --- Happy Path: getOrCreateForWeek accepts $userId and $weekStartDate parameters ---
assert(
    preg_match('/function\s+getOrCreateForWeek\s*\(\s*\$userId\s*,\s*\$weekStartDate\s*\)/', $beanContent) === 1,
    "getOrCreateForWeek() must accept \$userId and \$weekStartDate parameters"
);
echo "  [PASS] getOrCreateForWeek() has correct parameters\n";

// --- Happy Path: getOrCreateForWeek queries by assigned_user_id, week_start_date, and deleted=0 ---
assert(
    str_contains($beanContent, 'assigned_user_id')
    && str_contains($beanContent, 'week_start_date')
    && str_contains($beanContent, 'deleted'),
    "getOrCreateForWeek() must query by assigned_user_id, week_start_date, and deleted fields"
);
echo "  [PASS] getOrCreateForWeek() references assigned_user_id, week_start_date, and deleted fields\n";

// --- Happy Path: getOrCreateForWeek references the lf_weekly_report table ---
assert(
    str_contains($beanContent, 'lf_weekly_report'),
    "getOrCreateForWeek() should reference the lf_weekly_report table"
);
echo "  [PASS] getOrCreateForWeek() references lf_weekly_report table\n";

// --- Happy Path: getOrCreateForWeek uses $db->quoted() for SQL safety ---
assert(
    str_contains($beanContent, 'quoted('),
    "getOrCreateForWeek() must use \$db->quoted() for SQL parameter escaping"
);
echo "  [PASS] getOrCreateForWeek() uses \$db->quoted() for SQL safety\n";

// --- Happy Path: getOrCreateForWeek sets status 'in_progress' for new records ---
assert(
    str_contains($beanContent, 'in_progress'),
    "getOrCreateForWeek() must set status 'in_progress' for new records"
);
echo "  [PASS] getOrCreateForWeek() references 'in_progress' status\n";

// --- Edge Case: getOrCreateForWeek uses DBManagerFactory for DB access ---
assert(
    str_contains($beanContent, 'DBManagerFactory'),
    "getOrCreateForWeek() should use DBManagerFactory::getInstance() for DB access"
);
echo "  [PASS] getOrCreateForWeek() uses DBManagerFactory\n";

// --- Edge Case: getOrCreateForWeek uses sprintf for SQL query construction ---
assert(
    str_contains($beanContent, 'sprintf'),
    "getOrCreateForWeek() should use sprintf() for SQL query construction"
);
echo "  [PASS] getOrCreateForWeek() uses sprintf()\n";


// ============================================================
// Section 5: Vardefs File Existence
// ============================================================
echo "\nSection 5: Vardefs File Existence\n";

// --- Happy Path: Vardefs file exists ---
assert(
    file_exists($vardefsFile),
    "Vardefs file should exist at: custom/modules/LF_WeeklyReport/metadata/vardefs.php"
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
$tempFile = tempnam(sys_get_temp_dir(), 'us009_vardefs_');
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

// --- Happy Path: Dictionary key matches $object_name: $dictionary['LF_WeeklyReport'] ---
assert(
    array_key_exists('LF_WeeklyReport', $dictionary),
    "\$dictionary must have key 'LF_WeeklyReport' matching \$object_name"
);
echo "  [PASS] \$dictionary has key 'LF_WeeklyReport'\n";

// --- Edge Case: Dictionary has exactly 1 entry (no extra dictionaries) ---
assert(
    count($dictionary) === 1,
    "\$dictionary should have exactly 1 entry, got: " . count($dictionary)
);
echo "  [PASS] \$dictionary has exactly 1 entry\n";

$vardefEntry = $dictionary['LF_WeeklyReport'];

// --- Happy Path: Entry has 'table' key matching table_name ---
assert(
    array_key_exists('table', $vardefEntry),
    "Vardefs entry must have 'table' key"
);
assert(
    $vardefEntry['table'] === 'lf_weekly_report',
    "Vardefs 'table' should be 'lf_weekly_report', got: " . ($vardefEntry['table'] ?? 'NULL')
);
echo "  [PASS] Vardefs table is 'lf_weekly_report'\n";

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
// Section 9: Custom Fields - lf_weekly_plan_id
// ============================================================
echo "\nSection 9: Custom Fields - lf_weekly_plan_id\n";

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

// --- Edge Case: 'lf_weekly_plan_id' is NOT required (links report to plan, but optional) ---
assert(
    !isset($fields['lf_weekly_plan_id']['required']) || $fields['lf_weekly_plan_id']['required'] !== true,
    "Field 'lf_weekly_plan_id' should NOT be required (optional link to plan)"
);
echo "  [PASS] 'lf_weekly_plan_id' field is not required\n";

// --- Edge Case: 'lf_weekly_plan_id' field has 'name' property matching its key ---
if (isset($fields['lf_weekly_plan_id']['name'])) {
    assert(
        $fields['lf_weekly_plan_id']['name'] === 'lf_weekly_plan_id',
        "Field 'lf_weekly_plan_id' 'name' property should match its key"
    );
}
echo "  [PASS] 'lf_weekly_plan_id' field name property matches key\n";


// ============================================================
// Section 10: Custom Fields - assigned_user_id
// ============================================================
echo "\nSection 10: Custom Fields - assigned_user_id\n";

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


// ============================================================
// Section 11: Custom Fields - week_start_date
// ============================================================
echo "\nSection 11: Custom Fields - week_start_date\n";

// --- Happy Path: 'week_start_date' field exists with correct type ---
assert(
    array_key_exists('week_start_date', $fields),
    "Vardefs must include custom field 'week_start_date'"
);
assert(
    $fields['week_start_date']['type'] === 'date',
    "Field 'week_start_date' should have type 'date', got: " . ($fields['week_start_date']['type'] ?? 'NULL')
);
echo "  [PASS] 'week_start_date' field: date\n";

// --- Happy Path: 'week_start_date' field is required ---
assert(
    isset($fields['week_start_date']['required']) && $fields['week_start_date']['required'] === true,
    "Field 'week_start_date' should be required"
);
echo "  [PASS] 'week_start_date' field is required\n";


// ============================================================
// Section 12: Custom Fields - status (enum)
// ============================================================
echo "\nSection 12: Custom Fields - status (enum)\n";

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

// --- Happy Path: 'status' field has options 'lf_plan_status_dom' ---
assert(
    isset($fields['status']['options']) && $fields['status']['options'] === 'lf_plan_status_dom',
    "Field 'status' should have options 'lf_plan_status_dom', got: " . ($fields['status']['options'] ?? 'NULL')
);
echo "  [PASS] 'status' field options: lf_plan_status_dom\n";

// --- Happy Path: 'status' field has default 'in_progress' ---
assert(
    isset($fields['status']['default']) && $fields['status']['default'] === 'in_progress',
    "Field 'status' should have default 'in_progress', got: " . ($fields['status']['default'] ?? 'NULL')
);
echo "  [PASS] 'status' field default: in_progress\n";


// ============================================================
// Section 13: Custom Fields - submitted_date, reviewed_by, reviewed_date, notes
// ============================================================
echo "\nSection 13: Custom Fields - remaining fields\n";

// --- Happy Path: 'submitted_date' field exists with correct type ---
assert(
    array_key_exists('submitted_date', $fields),
    "Vardefs must include custom field 'submitted_date'"
);
assert(
    $fields['submitted_date']['type'] === 'datetime',
    "Field 'submitted_date' should have type 'datetime', got: " . ($fields['submitted_date']['type'] ?? 'NULL')
);
echo "  [PASS] 'submitted_date' field: datetime\n";

// --- Happy Path: 'reviewed_by' field exists with correct type ---
assert(
    array_key_exists('reviewed_by', $fields),
    "Vardefs must include custom field 'reviewed_by'"
);
assert(
    $fields['reviewed_by']['type'] === 'id',
    "Field 'reviewed_by' should have type 'id', got: " . ($fields['reviewed_by']['type'] ?? 'NULL')
);
echo "  [PASS] 'reviewed_by' field: id\n";

// --- Happy Path: 'reviewed_date' field exists with correct type ---
assert(
    array_key_exists('reviewed_date', $fields),
    "Vardefs must include custom field 'reviewed_date'"
);
assert(
    $fields['reviewed_date']['type'] === 'datetime',
    "Field 'reviewed_date' should have type 'datetime', got: " . ($fields['reviewed_date']['type'] ?? 'NULL')
);
echo "  [PASS] 'reviewed_date' field: datetime\n";

// --- Happy Path: 'notes' field exists with correct type ---
assert(
    array_key_exists('notes', $fields),
    "Vardefs must include custom field 'notes'"
);
assert(
    $fields['notes']['type'] === 'text',
    "Field 'notes' should have type 'text', got: " . ($fields['notes']['type'] ?? 'NULL')
);
echo "  [PASS] 'notes' field: text\n";

// --- Edge Case: submitted_date, reviewed_by, reviewed_date, notes are NOT required ---
assert(
    !isset($fields['submitted_date']['required']) || $fields['submitted_date']['required'] !== true,
    "Field 'submitted_date' should NOT be required"
);
assert(
    !isset($fields['reviewed_by']['required']) || $fields['reviewed_by']['required'] !== true,
    "Field 'reviewed_by' should NOT be required"
);
assert(
    !isset($fields['reviewed_date']['required']) || $fields['reviewed_date']['required'] !== true,
    "Field 'reviewed_date' should NOT be required"
);
assert(
    !isset($fields['notes']['required']) || $fields['notes']['required'] !== true,
    "Field 'notes' should NOT be required"
);
echo "  [PASS] submitted_date, reviewed_by, reviewed_date, notes are not required\n";


// ============================================================
// Section 14: Composite Index
// ============================================================
echo "\nSection 14: Composite Index\n";

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

// --- Happy Path: There is a composite index on assigned_user_id + week_start_date ---
$foundCompositeIndex = false;
foreach ($indices as $indexDef) {
    if (isset($indexDef['fields']) && is_array($indexDef['fields'])) {
        $indexFields = $indexDef['fields'];
        if (in_array('assigned_user_id', $indexFields) && in_array('week_start_date', $indexFields)) {
            $foundCompositeIndex = true;
            break;
        }
    }
}
assert(
    $foundCompositeIndex,
    "Vardefs must have a composite index including both 'assigned_user_id' and 'week_start_date'"
);
echo "  [PASS] Composite index on assigned_user_id + week_start_date exists\n";

// --- Edge Case: Composite index has exactly 2 fields ---
$compositeIndexFieldCount = 0;
foreach ($indices as $indexDef) {
    if (isset($indexDef['fields']) && is_array($indexDef['fields'])) {
        $indexFields = $indexDef['fields'];
        if (in_array('assigned_user_id', $indexFields) && in_array('week_start_date', $indexFields)) {
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
        if (in_array('assigned_user_id', $indexFields) && in_array('week_start_date', $indexFields)) {
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
        if (in_array('assigned_user_id', $indexFields) && in_array('week_start_date', $indexFields)) {
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

// --- Edge Case: Composite index field order: assigned_user_id first ---
$compositeIndexFieldOrder = [];
foreach ($indices as $indexDef) {
    if (isset($indexDef['fields']) && is_array($indexDef['fields'])) {
        $indexFields = $indexDef['fields'];
        if (in_array('assigned_user_id', $indexFields) && in_array('week_start_date', $indexFields)) {
            $compositeIndexFieldOrder = $indexFields;
            break;
        }
    }
}
assert(
    $compositeIndexFieldOrder[0] === 'assigned_user_id',
    "Composite index should list 'assigned_user_id' first (most selective), got: " . ($compositeIndexFieldOrder[0] ?? 'NULL')
);
echo "  [PASS] Composite index lists assigned_user_id first\n";


// ============================================================
// Section 15: Field Count Validation
// ============================================================
echo "\nSection 15: Field Count Validation\n";

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
// Section 16: Language File Existence
// ============================================================
echo "\nSection 16: Language File Existence\n";

// --- Happy Path: Language file exists ---
assert(
    file_exists($languageFile),
    "Language file should exist at: custom/modules/LF_WeeklyReport/language/en_us.lang.php"
);
echo "  [PASS] Language file exists\n";

// --- Happy Path: Language file is a regular file ---
assert(
    is_file($languageFile),
    "Language file path should be a regular file, not a directory"
);
echo "  [PASS] Language file is a regular file\n";


// ============================================================
// Section 17: Language File PHP Format and sugarEntry Guard
// ============================================================
echo "\nSection 17: Language File PHP Format\n";

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
// Section 18: Language File Labels ($mod_strings)
// ============================================================
echo "\nSection 18: Language File Labels\n";

// Load language data using temp file wrapper
$tempFile = tempnam(sys_get_temp_dir(), 'us009_lang_');
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
// Section 19: Menu.php File Existence
// ============================================================
echo "\nSection 19: Menu.php File Existence\n";

// --- Happy Path: Menu.php file exists ---
assert(
    file_exists($menuFile),
    "Menu file should exist at: custom/modules/LF_WeeklyReport/Menu.php"
);
echo "  [PASS] Menu.php file exists\n";

// --- Happy Path: Menu.php is a regular file ---
assert(
    is_file($menuFile),
    "Menu file path should be a regular file, not a directory"
);
echo "  [PASS] Menu.php is a regular file\n";


// ============================================================
// Section 20: Menu.php PHP Format and sugarEntry Guard
// ============================================================
echo "\nSection 20: Menu.php PHP Format\n";

$menuContent = file_get_contents($menuFile);
assert($menuContent !== false, "Should be able to read the Menu.php file");

// --- Happy Path: File starts with <?php ---
assert(
    str_starts_with(trim($menuContent), '<?php'),
    "Menu.php file must start with <?php"
);
echo "  [PASS] Menu.php file starts with <?php\n";

// --- Happy Path: File contains sugarEntry guard ---
assert(
    str_contains($menuContent, "defined('sugarEntry')"),
    "Menu.php file must contain sugarEntry guard: defined('sugarEntry')"
);
assert(
    str_contains($menuContent, 'Not A Valid Entry Point'),
    "Menu.php file must contain 'Not A Valid Entry Point' die message"
);
echo "  [PASS] Menu.php file has sugarEntry guard\n";


// ============================================================
// Section 21: Menu.php Structure and Content
// ============================================================
echo "\nSection 21: Menu.php Structure\n";

// --- Happy Path: File defines $module_menu array ---
assert(
    str_contains($menuContent, '$module_menu'),
    "Menu.php must define \$module_menu array"
);
echo "  [PASS] Menu.php defines \$module_menu\n";

// Load menu data using temp file wrapper
$tempFile = tempnam(sys_get_temp_dir(), 'us009_menu_');
$wrapperCode = "<?php\n";
$wrapperCode .= "define('sugarEntry', true);\n";
$wrapperCode .= "\$module_menu = [];\n";
$wrapperCode .= "include " . var_export($menuFile, true) . ";\n";
$wrapperCode .= "return \$module_menu;\n";
file_put_contents($tempFile, $wrapperCode);

$moduleMenu = include $tempFile;
unlink($tempFile);

assert(is_array($moduleMenu), "\$module_menu should be an array after including Menu.php");
echo "  [PASS] \$module_menu is an array\n";

// --- Happy Path: $module_menu has at least 2 entries (reporting tool + dashboard) ---
assert(
    count($moduleMenu) >= 2,
    "\$module_menu should have at least 2 entries (reporting tool + dashboard), got: " . count($moduleMenu)
);
echo "  [PASS] \$module_menu has at least 2 entries\n";

// --- Happy Path: Menu contains a link to reporting tool (action=reporting) ---
$menuContentLower = strtolower($menuContent);
assert(
    str_contains($menuContentLower, 'action=reporting'),
    "Menu.php must contain a link with action=reporting for the reporting tool"
);
echo "  [PASS] Menu.php has reporting tool link (action=reporting)\n";

// --- Happy Path: Menu contains a link to dashboard (action=dashboard) ---
assert(
    str_contains($menuContentLower, 'action=dashboard'),
    "Menu.php must contain a link with action=dashboard for the reporting dashboard"
);
echo "  [PASS] Menu.php has dashboard link (action=dashboard)\n";

// --- Happy Path: Menu references the LF_WeeklyReport module ---
assert(
    str_contains($menuContent, 'LF_WeeklyReport'),
    "Menu.php must reference the LF_WeeklyReport module"
);
echo "  [PASS] Menu.php references LF_WeeklyReport module\n";

// --- Edge Case: Each menu entry is an array ---
foreach ($moduleMenu as $index => $menuEntry) {
    assert(
        is_array($menuEntry),
        "\$module_menu entry at index {$index} should be an array"
    );
}
echo "  [PASS] All menu entries are arrays\n";

// --- Edge Case: Each menu entry has exactly 3 elements ---
foreach ($moduleMenu as $index => $menuEntry) {
    assert(
        count($menuEntry) === 3,
        "\$module_menu entry at index {$index} should have exactly 3 elements (label, URL, icon), got: " . count($menuEntry)
    );
}
echo "  [PASS] All menu entries have exactly 3 elements\n";


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
    preg_match('/\$object_name\s*=\s*[\'"]LF_WeeklyReport[\'"]/', $beanContent) === 1,
    "Bean \$object_name must match vardefs dictionary key 'LF_WeeklyReport'"
);
assert(
    array_key_exists('LF_WeeklyReport', $dictionary),
    "Vardefs \$dictionary key must match Bean \$object_name 'LF_WeeklyReport'"
);
echo "  [PASS] Bean \$object_name matches vardefs dictionary key\n";

// --- Edge Case: Vardefs table name matches Bean $table_name ---
assert(
    $vardefEntry['table'] === 'lf_weekly_report',
    "Vardefs table must match Bean \$table_name 'lf_weekly_report'"
);
assert(
    preg_match('/\$table_name\s*=\s*[\'"]lf_weekly_report[\'"]/', $beanContent) === 1,
    "Bean \$table_name must match vardefs table 'lf_weekly_report'"
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

// --- Edge Case: Bean references assigned_user_id for user queries ---
assert(
    str_contains($beanContent, 'assigned_user_id'),
    "Bean class must reference 'assigned_user_id' field for user queries"
);
echo "  [PASS] Bean class references 'assigned_user_id' field\n";

// --- Edge Case: Bean references week_start_date for date queries ---
assert(
    str_contains($beanContent, 'week_start_date'),
    "Bean class must reference 'week_start_date' field for date queries"
);
echo "  [PASS] Bean class references 'week_start_date' field\n";

// --- Edge Case: Bean references lf_weekly_plan_id is NOT in Bean class (it's a vardef field only) ---
// This validates the field is in vardefs but we don't require the Bean to reference it in code
assert(
    array_key_exists('lf_weekly_plan_id', $fields),
    "lf_weekly_plan_id must exist in vardefs even if Bean doesn't directly reference it"
);
echo "  [PASS] lf_weekly_plan_id exists in vardefs\n";


echo "\n==============================\n";
echo "US-009: All tests passed!\n";
echo "==============================\n";
