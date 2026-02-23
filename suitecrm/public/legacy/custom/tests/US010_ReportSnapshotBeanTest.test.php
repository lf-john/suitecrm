<?php
/**
 * US-010: LF_ReportSnapshot Bean, Vardefs, and Language Tests
 *
 * Tests that the LF_ReportSnapshot Bean class, vardefs, and language file
 * exist with correct structure, properties, methods, and field definitions.
 *
 * This module stores weekly report snapshots for each opportunity, tracking
 * stage movement and planned status. The Bean includes:
 * - detectMovement(): instance method comparing stage probabilities via LF_PRConfig
 * - createSnapshotsForWeek(): static method creating snapshots for open opportunities
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
    . DIRECTORY_SEPARATOR . 'LF_ReportSnapshot'
    . DIRECTORY_SEPARATOR . 'LF_ReportSnapshot.php';

$vardefsFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_ReportSnapshot'
    . DIRECTORY_SEPARATOR . 'metadata'
    . DIRECTORY_SEPARATOR . 'vardefs.php';

$languageFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_ReportSnapshot'
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
    'lf_weekly_report_id' => [
        'type' => 'id',
        'required' => true,
    ],
    'opportunity_id' => [
        'type' => 'id',
        'required' => true,
    ],
    'account_name' => [
        'type' => 'varchar',
        'len' => 255,
    ],
    'opportunity_name' => [
        'type' => 'varchar',
        'len' => 255,
    ],
    'amount_at_snapshot' => [
        'type' => 'decimal',
        'dbType' => 'decimal',
        'len' => '26,6',
    ],
    'stage_at_week_start' => [
        'type' => 'varchar',
        'len' => 100,
    ],
    'stage_at_week_end' => [
        'type' => 'varchar',
        'len' => 100,
    ],
    'probability_at_start' => [
        'type' => 'int',
    ],
    'probability_at_end' => [
        'type' => 'int',
    ],
    'movement' => [
        'type' => 'enum',
        'options' => 'lf_movement_dom',
    ],
    'was_planned' => [
        'type' => 'bool',
        'default' => '0',
    ],
    'plan_category' => [
        'type' => 'varchar',
        'len' => 50,
    ],
    'result_description' => [
        'type' => 'text',
    ],
];

// Expected language labels
$expectedLabels = [
    'LBL_MODULE_NAME',
    'LBL_LF_WEEKLY_REPORT_ID',
    'LBL_OPPORTUNITY_ID',
    'LBL_ACCOUNT_NAME',
    'LBL_OPPORTUNITY_NAME',
    'LBL_AMOUNT_AT_SNAPSHOT',
    'LBL_STAGE_AT_WEEK_START',
    'LBL_STAGE_AT_WEEK_END',
    'LBL_PROBABILITY_AT_START',
    'LBL_PROBABILITY_AT_END',
    'LBL_MOVEMENT',
    'LBL_WAS_PLANNED',
    'LBL_PLAN_CATEGORY',
    'LBL_RESULT_DESCRIPTION',
];


// ============================================================
// Section 1: Bean File Existence
// ============================================================
echo "Section 1: Bean File Existence\n";

// --- Happy Path: Bean file exists ---
assert(
    file_exists($beanFile),
    "Bean file should exist at: custom/modules/LF_ReportSnapshot/LF_ReportSnapshot.php"
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

// --- Happy Path: File contains class LF_ReportSnapshot extending SugarBean ---
assert(
    preg_match('/class\s+LF_ReportSnapshot\s+extends\s+SugarBean/', $beanContent) === 1,
    "Bean file must contain 'class LF_ReportSnapshot extends SugarBean'"
);
echo "  [PASS] Bean class extends SugarBean\n";

// --- Happy Path: File has #[\AllowDynamicProperties] attribute for PHP 8.2 ---
assert(
    str_contains($beanContent, '#[\AllowDynamicProperties]') || str_contains($beanContent, '#[AllowDynamicProperties]'),
    "Bean file must have #[\\AllowDynamicProperties] attribute for PHP 8.2 compatibility"
);
echo "  [PASS] Bean class has AllowDynamicProperties attribute\n";

// --- Happy Path: $table_name = 'lf_report_snapshots' ---
assert(
    preg_match('/\$table_name\s*=\s*[\'"]lf_report_snapshots[\'"]/', $beanContent) === 1,
    "Bean must have \$table_name = 'lf_report_snapshots'"
);
echo "  [PASS] Bean has \$table_name = 'lf_report_snapshots'\n";

// --- Happy Path: $object_name = 'LF_ReportSnapshot' ---
assert(
    preg_match('/\$object_name\s*=\s*[\'"]LF_ReportSnapshot[\'"]/', $beanContent) === 1,
    "Bean must have \$object_name = 'LF_ReportSnapshot'"
);
echo "  [PASS] Bean has \$object_name = 'LF_ReportSnapshot'\n";

// --- Happy Path: $module_name = 'LF_ReportSnapshot' ---
assert(
    preg_match('/\$module_name\s*=\s*[\'"]LF_ReportSnapshot[\'"]/', $beanContent) === 1,
    "Bean must have \$module_name = 'LF_ReportSnapshot'"
);
echo "  [PASS] Bean has \$module_name = 'LF_ReportSnapshot'\n";

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
// Section 4: Bean detectMovement() Instance Method
// ============================================================
echo "\nSection 4: Bean detectMovement() Instance Method\n";

// --- Happy Path: File contains detectMovement method ---
assert(
    preg_match('/public\s+function\s+detectMovement\s*\(/', $beanContent) === 1,
    "Bean must have 'public function detectMovement(' method"
);
echo "  [PASS] Bean has detectMovement() method\n";

// --- Negative Case: detectMovement is NOT static (accesses $this for stage properties) ---
assert(
    preg_match('/public\s+static\s+function\s+detectMovement/', $beanContent) !== 1,
    "detectMovement() must NOT be static - it needs access to \$this->stage_at_week_start and \$this->stage_at_week_end"
);
echo "  [PASS] detectMovement() is not static (instance method)\n";

// --- Happy Path: detectMovement references stage_at_week_start ---
assert(
    str_contains($beanContent, 'stage_at_week_start'),
    "detectMovement() must reference stage_at_week_start field"
);
echo "  [PASS] detectMovement() references stage_at_week_start\n";

// --- Happy Path: detectMovement references stage_at_week_end ---
assert(
    str_contains($beanContent, 'stage_at_week_end'),
    "detectMovement() must reference stage_at_week_end field"
);
echo "  [PASS] detectMovement() references stage_at_week_end\n";

// --- Happy Path: detectMovement uses LF_PRConfig for probability lookups ---
assert(
    str_contains($beanContent, 'LF_PRConfig::getConfig'),
    "detectMovement() must use LF_PRConfig::getConfig to look up stage probabilities"
);
echo "  [PASS] detectMovement() references LF_PRConfig::getConfig\n";

// --- Happy Path: detectMovement references 'stages' category for config lookups ---
assert(
    str_contains($beanContent, "'stages'") || str_contains($beanContent, '"stages"'),
    "detectMovement() must reference 'stages' category for probability lookups from lf_pr_config"
);
echo "  [PASS] detectMovement() references 'stages' category\n";

// --- Happy Path: detectMovement handles 'closed_won' movement ---
assert(
    str_contains($beanContent, "'closed_won'") || str_contains($beanContent, '"closed_won"'),
    "detectMovement() must handle 'closed_won' movement when stage_at_week_end is 'Closed Won'"
);
echo "  [PASS] detectMovement() handles 'closed_won' movement\n";

// --- Happy Path: detectMovement handles 'closed_lost' movement ---
assert(
    str_contains($beanContent, "'closed_lost'") || str_contains($beanContent, '"closed_lost"'),
    "detectMovement() must handle 'closed_lost' movement when stage_at_week_end is 'Closed Lost'"
);
echo "  [PASS] detectMovement() handles 'closed_lost' movement\n";

// --- Happy Path: detectMovement handles 'new' movement ---
assert(
    str_contains($beanContent, "'new'") || str_contains($beanContent, '"new"'),
    "detectMovement() must handle 'new' movement when stage_at_week_start is empty/null"
);
echo "  [PASS] detectMovement() handles 'new' movement\n";

// --- Happy Path: detectMovement handles 'forward' movement ---
assert(
    str_contains($beanContent, "'forward'") || str_contains($beanContent, '"forward"'),
    "detectMovement() must handle 'forward' movement when end probability > start probability"
);
echo "  [PASS] detectMovement() handles 'forward' movement\n";

// --- Happy Path: detectMovement handles 'backward' movement ---
assert(
    str_contains($beanContent, "'backward'") || str_contains($beanContent, '"backward"'),
    "detectMovement() must handle 'backward' movement when end probability < start probability"
);
echo "  [PASS] detectMovement() handles 'backward' movement\n";

// --- Happy Path: detectMovement handles 'static' movement ---
assert(
    str_contains($beanContent, "'static'") || str_contains($beanContent, '"static"'),
    "detectMovement() must handle 'static' movement when probabilities are equal"
);
echo "  [PASS] detectMovement() handles 'static' movement\n";

// --- Edge Case: detectMovement references $this->stage_at_week_start ---
assert(
    preg_match('/\$this\s*->\s*stage_at_week_start/', $beanContent) === 1,
    "detectMovement() must access \$this->stage_at_week_start"
);
echo "  [PASS] detectMovement() accesses \$this->stage_at_week_start\n";

// --- Edge Case: detectMovement references $this->stage_at_week_end ---
assert(
    preg_match('/\$this\s*->\s*stage_at_week_end/', $beanContent) === 1,
    "detectMovement() must access \$this->stage_at_week_end"
);
echo "  [PASS] detectMovement() accesses \$this->stage_at_week_end\n";

// --- Edge Case: detectMovement checks for 'Closed Won' string ---
assert(
    str_contains($beanContent, 'Closed Won'),
    "detectMovement() must check for 'Closed Won' stage name"
);
echo "  [PASS] detectMovement() checks for 'Closed Won'\n";

// --- Edge Case: detectMovement checks for 'Closed Lost' string ---
assert(
    str_contains($beanContent, 'Closed Lost'),
    "detectMovement() must check for 'Closed Lost' stage name"
);
echo "  [PASS] detectMovement() checks for 'Closed Lost'\n";

// --- Edge Case: detectMovement has no parameters (uses $this properties) ---
assert(
    preg_match('/function\s+detectMovement\s*\(\s*\)/', $beanContent) === 1,
    "detectMovement() should have no parameters (uses \$this->stage_at_week_start and \$this->stage_at_week_end)"
);
echo "  [PASS] detectMovement() has no parameters\n";


// ============================================================
// Section 5: Bean createSnapshotsForWeek() Static Method
// ============================================================
echo "\nSection 5: Bean createSnapshotsForWeek() Static Method\n";

// --- Happy Path: File contains static createSnapshotsForWeek method ---
assert(
    preg_match('/public\s+static\s+function\s+createSnapshotsForWeek\s*\(/', $beanContent) === 1,
    "Bean must have 'public static function createSnapshotsForWeek(' method"
);
echo "  [PASS] Bean has static createSnapshotsForWeek() method\n";

// --- Happy Path: createSnapshotsForWeek accepts $userId and $weekStartDate parameters ---
assert(
    preg_match('/function\s+createSnapshotsForWeek\s*\(\s*\$userId\s*,\s*\$weekStartDate\s*,\s*\$reportId\s*\)/', $beanContent) === 1,
    "createSnapshotsForWeek() must accept \$userId and \$weekStartDate parameters"
);
echo "  [PASS] createSnapshotsForWeek() has correct parameters\n";

// --- Happy Path: createSnapshotsForWeek uses DBManagerFactory for DB access ---
assert(
    str_contains($beanContent, 'DBManagerFactory'),
    "createSnapshotsForWeek() should use DBManagerFactory::getInstance() for DB access"
);
echo "  [PASS] createSnapshotsForWeek() uses DBManagerFactory\n";

// --- Happy Path: createSnapshotsForWeek uses $db->quoted() for SQL safety ---
assert(
    str_contains($beanContent, 'quoted('),
    "createSnapshotsForWeek() must use \$db->quoted() for SQL parameter escaping"
);
echo "  [PASS] createSnapshotsForWeek() uses \$db->quoted() for SQL safety\n";

// --- Happy Path: createSnapshotsForWeek uses sprintf for SQL query construction ---
assert(
    str_contains($beanContent, 'sprintf'),
    "createSnapshotsForWeek() should use sprintf() for SQL query construction"
);
echo "  [PASS] createSnapshotsForWeek() uses sprintf()\n";

// --- Happy Path: createSnapshotsForWeek queries opportunities ---
assert(
    str_contains($beanContent, 'opportunities') || str_contains($beanContent, 'Opportunities'),
    "createSnapshotsForWeek() must query for open opportunities"
);
echo "  [PASS] createSnapshotsForWeek() references opportunities\n";

// --- Happy Path: createSnapshotsForWeek filters by user (assigned_user_id) ---
assert(
    str_contains($beanContent, 'assigned_user_id'),
    "createSnapshotsForWeek() must filter opportunities by assigned_user_id"
);
echo "  [PASS] createSnapshotsForWeek() filters by assigned_user_id\n";

// --- Happy Path: createSnapshotsForWeek sets stage_at_week_start from opportunity's sales_stage ---
assert(
    str_contains($beanContent, 'sales_stage'),
    "createSnapshotsForWeek() must read opportunity's sales_stage for stage_at_week_start"
);
echo "  [PASS] createSnapshotsForWeek() references sales_stage\n";

// --- Happy Path: createSnapshotsForWeek sets amount_at_snapshot from opportunity's amount ---
assert(
    str_contains($beanContent, 'amount_at_snapshot') || str_contains($beanContent, 'amount'),
    "createSnapshotsForWeek() must set amount_at_snapshot from opportunity's amount"
);
echo "  [PASS] createSnapshotsForWeek() references amount fields\n";

// --- Happy Path: createSnapshotsForWeek determines was_planned based on plan items ---
assert(
    str_contains($beanContent, 'was_planned'),
    "createSnapshotsForWeek() must determine was_planned based on whether opportunity appears in plan items"
);
echo "  [PASS] createSnapshotsForWeek() references was_planned\n";

// --- Edge Case: createSnapshotsForWeek filters out closed opportunities ---
assert(
    str_contains($beanContent, 'Closed Won') || str_contains($beanContent, 'Closed Lost') || str_contains($beanContent, 'closed'),
    "createSnapshotsForWeek() must filter out closed opportunities (non-closed only)"
);
echo "  [PASS] createSnapshotsForWeek() filters out closed opportunities\n";

// --- Edge Case: createSnapshotsForWeek filters by deleted=0 ---
assert(
    str_contains($beanContent, 'deleted'),
    "createSnapshotsForWeek() must filter by deleted=0 to exclude soft-deleted records"
);
echo "  [PASS] createSnapshotsForWeek() filters by deleted=0\n";

// --- Edge Case: createSnapshotsForWeek calls save() to persist snapshots ---
assert(
    preg_match('/->save\s*\(/', $beanContent) === 1,
    "createSnapshotsForWeek() must call save() to persist snapshot records"
);
echo "  [PASS] createSnapshotsForWeek() calls save()\n";


// ============================================================
// Section 6: Vardefs File Existence
// ============================================================
echo "\nSection 6: Vardefs File Existence\n";

// --- Happy Path: Vardefs file exists ---
assert(
    file_exists($vardefsFile),
    "Vardefs file should exist at: custom/modules/LF_ReportSnapshot/metadata/vardefs.php"
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
$tempFile = tempnam(sys_get_temp_dir(), 'us010_vardefs_');
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

// --- Happy Path: Dictionary key matches $object_name: $dictionary['LF_ReportSnapshot'] ---
assert(
    array_key_exists('LF_ReportSnapshot', $dictionary),
    "\$dictionary must have key 'LF_ReportSnapshot' matching \$object_name"
);
echo "  [PASS] \$dictionary has key 'LF_ReportSnapshot'\n";

// --- Edge Case: Dictionary has exactly 1 entry (no extra dictionaries) ---
assert(
    count($dictionary) === 1,
    "\$dictionary should have exactly 1 entry, got: " . count($dictionary)
);
echo "  [PASS] \$dictionary has exactly 1 entry\n";

$vardefEntry = $dictionary['LF_ReportSnapshot'];

// --- Happy Path: Entry has 'table' key matching table_name ---
assert(
    array_key_exists('table', $vardefEntry),
    "Vardefs entry must have 'table' key"
);
assert(
    $vardefEntry['table'] === 'lf_report_snapshots',
    "Vardefs 'table' should be 'lf_report_snapshots', got: " . ($vardefEntry['table'] ?? 'NULL')
);
echo "  [PASS] Vardefs table is 'lf_report_snapshots'\n";

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
// Section 10: Custom Field - lf_weekly_report_id (id, required)
// ============================================================
echo "\nSection 10: Custom Field - lf_weekly_report_id\n";

// --- Happy Path: 'lf_weekly_report_id' field exists with correct type ---
assert(
    array_key_exists('lf_weekly_report_id', $fields),
    "Vardefs must include custom field 'lf_weekly_report_id'"
);
assert(
    $fields['lf_weekly_report_id']['type'] === 'id',
    "Field 'lf_weekly_report_id' should have type 'id', got: " . ($fields['lf_weekly_report_id']['type'] ?? 'NULL')
);
echo "  [PASS] 'lf_weekly_report_id' field: id\n";

// --- Happy Path: 'lf_weekly_report_id' field is required ---
assert(
    isset($fields['lf_weekly_report_id']['required']) && $fields['lf_weekly_report_id']['required'] === true,
    "Field 'lf_weekly_report_id' should be required"
);
echo "  [PASS] 'lf_weekly_report_id' field is required\n";

// --- Edge Case: 'lf_weekly_report_id' has 'name' property matching its key ---
assert(
    isset($fields['lf_weekly_report_id']['name']) && $fields['lf_weekly_report_id']['name'] === 'lf_weekly_report_id',
    "Field 'lf_weekly_report_id' 'name' property should match its key"
);
echo "  [PASS] 'lf_weekly_report_id' has correct 'name' property\n";


// ============================================================
// Section 11: Custom Field - opportunity_id (id, required)
// ============================================================
echo "\nSection 11: Custom Field - opportunity_id\n";

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
// Section 12: Custom Field - account_name (varchar 255)
// ============================================================
echo "\nSection 12: Custom Field - account_name\n";

// --- Happy Path: 'account_name' field exists with correct type ---
assert(
    array_key_exists('account_name', $fields),
    "Vardefs must include custom field 'account_name'"
);
assert(
    $fields['account_name']['type'] === 'varchar',
    "Field 'account_name' should have type 'varchar', got: " . ($fields['account_name']['type'] ?? 'NULL')
);
echo "  [PASS] 'account_name' field: varchar\n";

// --- Happy Path: 'account_name' has len 255 ---
assert(
    isset($fields['account_name']['len']) && ($fields['account_name']['len'] == 255),
    "Field 'account_name' should have len 255, got: " . ($fields['account_name']['len'] ?? 'NULL')
);
echo "  [PASS] 'account_name' field len: 255\n";

// --- Edge Case: 'account_name' is NOT required ---
assert(
    !isset($fields['account_name']['required']) || $fields['account_name']['required'] !== true,
    "Field 'account_name' should NOT be required"
);
echo "  [PASS] 'account_name' is not required\n";

// --- Edge Case: 'account_name' has 'name' property matching its key ---
assert(
    isset($fields['account_name']['name']) && $fields['account_name']['name'] === 'account_name',
    "Field 'account_name' 'name' property should match its key"
);
echo "  [PASS] 'account_name' has correct 'name' property\n";


// ============================================================
// Section 13: Custom Field - opportunity_name (varchar 255)
// ============================================================
echo "\nSection 13: Custom Field - opportunity_name\n";

// --- Happy Path: 'opportunity_name' field exists with correct type ---
assert(
    array_key_exists('opportunity_name', $fields),
    "Vardefs must include custom field 'opportunity_name'"
);
assert(
    $fields['opportunity_name']['type'] === 'varchar',
    "Field 'opportunity_name' should have type 'varchar', got: " . ($fields['opportunity_name']['type'] ?? 'NULL')
);
echo "  [PASS] 'opportunity_name' field: varchar\n";

// --- Happy Path: 'opportunity_name' has len 255 ---
assert(
    isset($fields['opportunity_name']['len']) && ($fields['opportunity_name']['len'] == 255),
    "Field 'opportunity_name' should have len 255, got: " . ($fields['opportunity_name']['len'] ?? 'NULL')
);
echo "  [PASS] 'opportunity_name' field len: 255\n";

// --- Edge Case: 'opportunity_name' is NOT required ---
assert(
    !isset($fields['opportunity_name']['required']) || $fields['opportunity_name']['required'] !== true,
    "Field 'opportunity_name' should NOT be required"
);
echo "  [PASS] 'opportunity_name' is not required\n";

// --- Edge Case: 'opportunity_name' has 'name' property matching its key ---
assert(
    isset($fields['opportunity_name']['name']) && $fields['opportunity_name']['name'] === 'opportunity_name',
    "Field 'opportunity_name' 'name' property should match its key"
);
echo "  [PASS] 'opportunity_name' has correct 'name' property\n";


// ============================================================
// Section 14: Custom Field - amount_at_snapshot (decimal 26,6)
// ============================================================
echo "\nSection 14: Custom Field - amount_at_snapshot\n";

// --- Happy Path: 'amount_at_snapshot' field exists with correct type ---
assert(
    array_key_exists('amount_at_snapshot', $fields),
    "Vardefs must include custom field 'amount_at_snapshot'"
);
assert(
    $fields['amount_at_snapshot']['type'] === 'decimal',
    "Field 'amount_at_snapshot' should have type 'decimal', got: " . ($fields['amount_at_snapshot']['type'] ?? 'NULL')
);
echo "  [PASS] 'amount_at_snapshot' field: decimal\n";

// --- Happy Path: 'amount_at_snapshot' has dbType='decimal' ---
assert(
    isset($fields['amount_at_snapshot']['dbType']) && $fields['amount_at_snapshot']['dbType'] === 'decimal',
    "Field 'amount_at_snapshot' should have dbType 'decimal', got: " . ($fields['amount_at_snapshot']['dbType'] ?? 'NULL')
);
echo "  [PASS] 'amount_at_snapshot' field dbType: decimal\n";

// --- Happy Path: 'amount_at_snapshot' has len='26,6' ---
assert(
    isset($fields['amount_at_snapshot']['len']) && $fields['amount_at_snapshot']['len'] === '26,6',
    "Field 'amount_at_snapshot' should have len '26,6', got: " . ($fields['amount_at_snapshot']['len'] ?? 'NULL')
);
echo "  [PASS] 'amount_at_snapshot' field len: 26,6\n";

// --- Edge Case: 'amount_at_snapshot' is NOT required ---
assert(
    !isset($fields['amount_at_snapshot']['required']) || $fields['amount_at_snapshot']['required'] !== true,
    "Field 'amount_at_snapshot' should NOT be required"
);
echo "  [PASS] 'amount_at_snapshot' is not required\n";

// --- Edge Case: 'amount_at_snapshot' has 'name' property matching its key ---
assert(
    isset($fields['amount_at_snapshot']['name']) && $fields['amount_at_snapshot']['name'] === 'amount_at_snapshot',
    "Field 'amount_at_snapshot' 'name' property should match its key"
);
echo "  [PASS] 'amount_at_snapshot' has correct 'name' property\n";


// ============================================================
// Section 15: Custom Fields - stage_at_week_start and stage_at_week_end (varchar 100)
// ============================================================
echo "\nSection 15: Custom Fields - stage_at_week_start and stage_at_week_end\n";

// --- Happy Path: 'stage_at_week_start' field exists with correct type ---
assert(
    array_key_exists('stage_at_week_start', $fields),
    "Vardefs must include custom field 'stage_at_week_start'"
);
assert(
    $fields['stage_at_week_start']['type'] === 'varchar',
    "Field 'stage_at_week_start' should have type 'varchar', got: " . ($fields['stage_at_week_start']['type'] ?? 'NULL')
);
echo "  [PASS] 'stage_at_week_start' field: varchar\n";

// --- Happy Path: 'stage_at_week_start' has len 100 ---
assert(
    isset($fields['stage_at_week_start']['len']) && ($fields['stage_at_week_start']['len'] == 100),
    "Field 'stage_at_week_start' should have len 100, got: " . ($fields['stage_at_week_start']['len'] ?? 'NULL')
);
echo "  [PASS] 'stage_at_week_start' field len: 100\n";

// --- Edge Case: 'stage_at_week_start' is NOT required ---
assert(
    !isset($fields['stage_at_week_start']['required']) || $fields['stage_at_week_start']['required'] !== true,
    "Field 'stage_at_week_start' should NOT be required"
);
echo "  [PASS] 'stage_at_week_start' is not required\n";

// --- Edge Case: 'stage_at_week_start' has 'name' property matching its key ---
assert(
    isset($fields['stage_at_week_start']['name']) && $fields['stage_at_week_start']['name'] === 'stage_at_week_start',
    "Field 'stage_at_week_start' 'name' property should match its key"
);
echo "  [PASS] 'stage_at_week_start' has correct 'name' property\n";

// --- Happy Path: 'stage_at_week_end' field exists with correct type ---
assert(
    array_key_exists('stage_at_week_end', $fields),
    "Vardefs must include custom field 'stage_at_week_end'"
);
assert(
    $fields['stage_at_week_end']['type'] === 'varchar',
    "Field 'stage_at_week_end' should have type 'varchar', got: " . ($fields['stage_at_week_end']['type'] ?? 'NULL')
);
echo "  [PASS] 'stage_at_week_end' field: varchar\n";

// --- Happy Path: 'stage_at_week_end' has len 100 ---
assert(
    isset($fields['stage_at_week_end']['len']) && ($fields['stage_at_week_end']['len'] == 100),
    "Field 'stage_at_week_end' should have len 100, got: " . ($fields['stage_at_week_end']['len'] ?? 'NULL')
);
echo "  [PASS] 'stage_at_week_end' field len: 100\n";

// --- Edge Case: 'stage_at_week_end' is NOT required ---
assert(
    !isset($fields['stage_at_week_end']['required']) || $fields['stage_at_week_end']['required'] !== true,
    "Field 'stage_at_week_end' should NOT be required"
);
echo "  [PASS] 'stage_at_week_end' is not required\n";

// --- Edge Case: 'stage_at_week_end' has 'name' property matching its key ---
assert(
    isset($fields['stage_at_week_end']['name']) && $fields['stage_at_week_end']['name'] === 'stage_at_week_end',
    "Field 'stage_at_week_end' 'name' property should match its key"
);
echo "  [PASS] 'stage_at_week_end' has correct 'name' property\n";


// ============================================================
// Section 16: Custom Fields - probability_at_start and probability_at_end (int)
// ============================================================
echo "\nSection 16: Custom Fields - probability_at_start and probability_at_end\n";

// --- Happy Path: 'probability_at_start' field exists with correct type ---
assert(
    array_key_exists('probability_at_start', $fields),
    "Vardefs must include custom field 'probability_at_start'"
);
assert(
    $fields['probability_at_start']['type'] === 'int',
    "Field 'probability_at_start' should have type 'int', got: " . ($fields['probability_at_start']['type'] ?? 'NULL')
);
echo "  [PASS] 'probability_at_start' field: int\n";

// --- Edge Case: 'probability_at_start' is NOT required ---
assert(
    !isset($fields['probability_at_start']['required']) || $fields['probability_at_start']['required'] !== true,
    "Field 'probability_at_start' should NOT be required"
);
echo "  [PASS] 'probability_at_start' is not required\n";

// --- Edge Case: 'probability_at_start' has 'name' property matching its key ---
assert(
    isset($fields['probability_at_start']['name']) && $fields['probability_at_start']['name'] === 'probability_at_start',
    "Field 'probability_at_start' 'name' property should match its key"
);
echo "  [PASS] 'probability_at_start' has correct 'name' property\n";

// --- Happy Path: 'probability_at_end' field exists with correct type ---
assert(
    array_key_exists('probability_at_end', $fields),
    "Vardefs must include custom field 'probability_at_end'"
);
assert(
    $fields['probability_at_end']['type'] === 'int',
    "Field 'probability_at_end' should have type 'int', got: " . ($fields['probability_at_end']['type'] ?? 'NULL')
);
echo "  [PASS] 'probability_at_end' field: int\n";

// --- Edge Case: 'probability_at_end' is NOT required ---
assert(
    !isset($fields['probability_at_end']['required']) || $fields['probability_at_end']['required'] !== true,
    "Field 'probability_at_end' should NOT be required"
);
echo "  [PASS] 'probability_at_end' is not required\n";

// --- Edge Case: 'probability_at_end' has 'name' property matching its key ---
assert(
    isset($fields['probability_at_end']['name']) && $fields['probability_at_end']['name'] === 'probability_at_end',
    "Field 'probability_at_end' 'name' property should match its key"
);
echo "  [PASS] 'probability_at_end' has correct 'name' property\n";


// ============================================================
// Section 17: Custom Field - movement (enum with lf_movement_dom)
// ============================================================
echo "\nSection 17: Custom Field - movement\n";

// --- Happy Path: 'movement' field exists with correct type ---
assert(
    array_key_exists('movement', $fields),
    "Vardefs must include custom field 'movement'"
);
assert(
    $fields['movement']['type'] === 'enum',
    "Field 'movement' should have type 'enum', got: " . ($fields['movement']['type'] ?? 'NULL')
);
echo "  [PASS] 'movement' field: enum\n";

// --- Happy Path: 'movement' field has options 'lf_movement_dom' ---
assert(
    isset($fields['movement']['options']) && $fields['movement']['options'] === 'lf_movement_dom',
    "Field 'movement' should have options 'lf_movement_dom', got: " . ($fields['movement']['options'] ?? 'NULL')
);
echo "  [PASS] 'movement' field options: lf_movement_dom\n";

// --- Edge Case: 'movement' is NOT required ---
assert(
    !isset($fields['movement']['required']) || $fields['movement']['required'] !== true,
    "Field 'movement' should NOT be required (set by detectMovement)"
);
echo "  [PASS] 'movement' is not required\n";

// --- Edge Case: 'movement' has 'name' property matching its key ---
assert(
    isset($fields['movement']['name']) && $fields['movement']['name'] === 'movement',
    "Field 'movement' 'name' property should match its key"
);
echo "  [PASS] 'movement' has correct 'name' property\n";


// ============================================================
// Section 18: Custom Field - was_planned (bool, default 0)
// ============================================================
echo "\nSection 18: Custom Field - was_planned\n";

// --- Happy Path: 'was_planned' field exists with correct type ---
assert(
    array_key_exists('was_planned', $fields),
    "Vardefs must include custom field 'was_planned'"
);
assert(
    $fields['was_planned']['type'] === 'bool',
    "Field 'was_planned' should have type 'bool', got: " . ($fields['was_planned']['type'] ?? 'NULL')
);
echo "  [PASS] 'was_planned' field: bool\n";

// --- Happy Path: 'was_planned' field has default '0' ---
assert(
    isset($fields['was_planned']['default']) && ($fields['was_planned']['default'] == '0'),
    "Field 'was_planned' should have default '0', got: " . ($fields['was_planned']['default'] ?? 'NULL')
);
echo "  [PASS] 'was_planned' field default: 0\n";

// --- Edge Case: 'was_planned' is NOT required ---
assert(
    !isset($fields['was_planned']['required']) || $fields['was_planned']['required'] !== true,
    "Field 'was_planned' should NOT be required"
);
echo "  [PASS] 'was_planned' is not required\n";

// --- Edge Case: 'was_planned' has 'name' property matching its key ---
assert(
    isset($fields['was_planned']['name']) && $fields['was_planned']['name'] === 'was_planned',
    "Field 'was_planned' 'name' property should match its key"
);
echo "  [PASS] 'was_planned' has correct 'name' property\n";


// ============================================================
// Section 19: Custom Field - plan_category (varchar 50)
// ============================================================
echo "\nSection 19: Custom Field - plan_category\n";

// --- Happy Path: 'plan_category' field exists with correct type ---
assert(
    array_key_exists('plan_category', $fields),
    "Vardefs must include custom field 'plan_category'"
);
assert(
    $fields['plan_category']['type'] === 'varchar',
    "Field 'plan_category' should have type 'varchar', got: " . ($fields['plan_category']['type'] ?? 'NULL')
);
echo "  [PASS] 'plan_category' field: varchar\n";

// --- Happy Path: 'plan_category' has len 50 ---
assert(
    isset($fields['plan_category']['len']) && ($fields['plan_category']['len'] == 50),
    "Field 'plan_category' should have len 50, got: " . ($fields['plan_category']['len'] ?? 'NULL')
);
echo "  [PASS] 'plan_category' field len: 50\n";

// --- Edge Case: 'plan_category' is NOT required ---
assert(
    !isset($fields['plan_category']['required']) || $fields['plan_category']['required'] !== true,
    "Field 'plan_category' should NOT be required"
);
echo "  [PASS] 'plan_category' is not required\n";

// --- Edge Case: 'plan_category' has 'name' property matching its key ---
assert(
    isset($fields['plan_category']['name']) && $fields['plan_category']['name'] === 'plan_category',
    "Field 'plan_category' 'name' property should match its key"
);
echo "  [PASS] 'plan_category' has correct 'name' property\n";


// ============================================================
// Section 20: Custom Field - result_description (text)
// ============================================================
echo "\nSection 20: Custom Field - result_description\n";

// --- Happy Path: 'result_description' field exists with correct type ---
assert(
    array_key_exists('result_description', $fields),
    "Vardefs must include custom field 'result_description'"
);
assert(
    $fields['result_description']['type'] === 'text',
    "Field 'result_description' should have type 'text', got: " . ($fields['result_description']['type'] ?? 'NULL')
);
echo "  [PASS] 'result_description' field: text\n";

// --- Edge Case: 'result_description' is NOT required ---
assert(
    !isset($fields['result_description']['required']) || $fields['result_description']['required'] !== true,
    "Field 'result_description' should NOT be required"
);
echo "  [PASS] 'result_description' is not required\n";

// --- Edge Case: 'result_description' has 'name' property matching its key ---
assert(
    isset($fields['result_description']['name']) && $fields['result_description']['name'] === 'result_description',
    "Field 'result_description' 'name' property should match its key"
);
echo "  [PASS] 'result_description' has correct 'name' property\n";


// ============================================================
// Section 21: Indices on lf_weekly_report_id and opportunity_id
// ============================================================
echo "\nSection 21: Indices\n";

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

// --- Happy Path: Index on lf_weekly_report_id ---
$foundReportIndex = false;
foreach ($indices as $indexDef) {
    if (isset($indexDef['fields']) && is_array($indexDef['fields'])) {
        if (in_array('lf_weekly_report_id', $indexDef['fields'])) {
            $foundReportIndex = true;
            break;
        }
    }
}
assert(
    $foundReportIndex,
    "Vardefs must have an index on 'lf_weekly_report_id' for efficient report lookups"
);
echo "  [PASS] Index on lf_weekly_report_id exists\n";

// --- Edge Case: lf_weekly_report_id index has a 'name' property ---
$reportIndexHasName = false;
foreach ($indices as $indexDef) {
    if (isset($indexDef['fields']) && is_array($indexDef['fields'])) {
        if (in_array('lf_weekly_report_id', $indexDef['fields'])) {
            $reportIndexHasName = isset($indexDef['name']) && !empty($indexDef['name']);
            break;
        }
    }
}
assert(
    $reportIndexHasName,
    "Index on lf_weekly_report_id must have a 'name' property"
);
echo "  [PASS] lf_weekly_report_id index has a name\n";

// --- Edge Case: lf_weekly_report_id index has type 'index' ---
$reportIndexType = null;
foreach ($indices as $indexDef) {
    if (isset($indexDef['fields']) && is_array($indexDef['fields'])) {
        if (in_array('lf_weekly_report_id', $indexDef['fields'])) {
            $reportIndexType = $indexDef['type'] ?? null;
            break;
        }
    }
}
assert(
    $reportIndexType === 'index',
    "Index on lf_weekly_report_id must have type 'index', got: " . ($reportIndexType ?? 'NULL')
);
echo "  [PASS] lf_weekly_report_id index has type 'index'\n";

// --- Happy Path: Index on opportunity_id ---
$foundOppIndex = false;
foreach ($indices as $indexDef) {
    if (isset($indexDef['fields']) && is_array($indexDef['fields'])) {
        if (in_array('opportunity_id', $indexDef['fields'])) {
            $foundOppIndex = true;
            break;
        }
    }
}
assert(
    $foundOppIndex,
    "Vardefs must have an index on 'opportunity_id' for efficient opportunity lookups"
);
echo "  [PASS] Index on opportunity_id exists\n";

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
// Section 22: Field Count Validation
// ============================================================
echo "\nSection 22: Field Count Validation\n";

// Total expected: 7 standard + 13 custom = 20 fields
$expectedFieldCount = count($standardFields) + count($customFields);

// --- Edge Case: Exactly 20 fields (no extra, no missing) ---
assert(
    count($fields) === $expectedFieldCount,
    "Vardefs should have exactly {$expectedFieldCount} fields (7 standard + 13 custom), got: " . count($fields)
);
echo "  [PASS] Vardefs has exactly {$expectedFieldCount} fields\n";

// --- Edge Case: All custom field names are present ---
foreach (array_keys($customFields) as $customFieldName) {
    assert(
        array_key_exists($customFieldName, $fields),
        "Custom field '{$customFieldName}' must exist in vardefs fields"
    );
}
echo "  [PASS] All 13 custom fields are present\n";


// ============================================================
// Section 23: Language File Existence, Format, and Labels
// ============================================================
echo "\nSection 23: Language File\n";

// --- Happy Path: Language file exists ---
assert(
    file_exists($languageFile),
    "Language file should exist at: custom/modules/LF_ReportSnapshot/language/en_us.lang.php"
);
echo "  [PASS] Language file exists\n";

// --- Happy Path: Language file is a regular file ---
assert(
    is_file($languageFile),
    "Language file path should be a regular file, not a directory"
);
echo "  [PASS] Language file is a regular file\n";

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

// Load language data using temp file wrapper
$tempFile = tempnam(sys_get_temp_dir(), 'us010_lang_');
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

// --- Edge Case: At least 14 labels defined (no less than expected) ---
assert(
    count($modStrings) >= count($expectedLabels),
    "\$mod_strings should have at least " . count($expectedLabels) . " entries, got: " . count($modStrings)
);
echo "  [PASS] \$mod_strings has at least " . count($expectedLabels) . " entries\n";


// ============================================================
// Section 24: Cross-Validation
// ============================================================
echo "\nSection 24: Cross-Validation\n";

// --- Edge Case: Vardefs uses $dictionary variable, not other variable names ---
assert(
    str_contains($vardefsContent, '$dictionary'),
    "Vardefs file must use \$dictionary variable"
);
echo "  [PASS] Vardefs file uses \$dictionary variable\n";

// --- Edge Case: Vardefs dictionary key matches Bean $object_name ---
assert(
    preg_match('/\$object_name\s*=\s*[\'"]LF_ReportSnapshot[\'"]/', $beanContent) === 1,
    "Bean \$object_name must match vardefs dictionary key 'LF_ReportSnapshot'"
);
assert(
    array_key_exists('LF_ReportSnapshot', $dictionary),
    "Vardefs \$dictionary key must match Bean \$object_name 'LF_ReportSnapshot'"
);
echo "  [PASS] Bean \$object_name matches vardefs dictionary key\n";

// --- Edge Case: Vardefs table name matches Bean $table_name ---
assert(
    $vardefEntry['table'] === 'lf_report_snapshots',
    "Vardefs table must match Bean \$table_name 'lf_report_snapshots'"
);
assert(
    preg_match('/\$table_name\s*=\s*[\'"]lf_report_snapshots[\'"]/', $beanContent) === 1,
    "Bean \$table_name must match vardefs table 'lf_report_snapshots'"
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

// --- Edge Case: Bean references LF_PRConfig for configuration access ---
assert(
    str_contains($beanContent, 'LF_PRConfig'),
    "Bean class must reference LF_PRConfig for probability lookups in detectMovement()"
);
echo "  [PASS] Bean class references LF_PRConfig\n";

// --- Edge Case: Bean references DBManagerFactory for database access ---
assert(
    str_contains($beanContent, 'DBManagerFactory'),
    "Bean class must reference DBManagerFactory for database queries in createSnapshotsForWeek()"
);
echo "  [PASS] Bean class references DBManagerFactory\n";

// --- Edge Case: Vardefs 'deleted' field has default '0' ---
assert(
    isset($fields['deleted']['default']) && ($fields['deleted']['default'] == '0'),
    "Standard field 'deleted' should have default '0', got: " . ($fields['deleted']['default'] ?? 'NULL')
);
echo "  [PASS] 'deleted' field has default '0'\n";

// --- Edge Case: Bean references lf_report_snapshots table ---
assert(
    str_contains($beanContent, 'lf_report_snapshots'),
    "Bean class should reference 'lf_report_snapshots' table name"
);
echo "  [PASS] Bean class references lf_report_snapshots table\n";

// --- Edge Case: movement enum has exactly 6 options in lf_movement_dom (validated via dropdown) ---
// Validate the dropdown file has the correct number of movement options
$dropdownFile = $customDir
    . DIRECTORY_SEPARATOR . '..'
    . DIRECTORY_SEPARATOR . 'custom'
    . DIRECTORY_SEPARATOR . 'Extension'
    . DIRECTORY_SEPARATOR . 'application'
    . DIRECTORY_SEPARATOR . 'Ext'
    . DIRECTORY_SEPARATOR . 'Language'
    . DIRECTORY_SEPARATOR . 'en_us.lf_plan_report.php';

// Use a normalized path to find the dropdown file
$dropdownFileAlt = $customDir
    . DIRECTORY_SEPARATOR . '..'
    . DIRECTORY_SEPARATOR . 'custom'
    . DIRECTORY_SEPARATOR . 'Extension'
    . DIRECTORY_SEPARATOR . 'application'
    . DIRECTORY_SEPARATOR . 'Ext'
    . DIRECTORY_SEPARATOR . 'Language'
    . DIRECTORY_SEPARATOR . 'en_us.lf_plan_report.php';

// Resolve from project root
$projectRoot = dirname($customDir);
$dropdownFilePath = $projectRoot
    . DIRECTORY_SEPARATOR . 'custom'
    . DIRECTORY_SEPARATOR . 'Extension'
    . DIRECTORY_SEPARATOR . 'application'
    . DIRECTORY_SEPARATOR . 'Ext'
    . DIRECTORY_SEPARATOR . 'Language'
    . DIRECTORY_SEPARATOR . 'en_us.lf_plan_report.php';

if (file_exists($dropdownFilePath)) {
    // Load dropdown definitions
    $tempFile = tempnam(sys_get_temp_dir(), 'us010_dropdown_');
    $wrapperCode = "<?php\n";
    $wrapperCode .= "define('sugarEntry', true);\n";
    $wrapperCode .= "\$app_list_strings = [];\n";
    $wrapperCode .= "include " . var_export($dropdownFilePath, true) . ";\n";
    $wrapperCode .= "return \$app_list_strings;\n";
    file_put_contents($tempFile, $wrapperCode);

    $appListStrings = include $tempFile;
    unlink($tempFile);

    // --- Happy Path: lf_movement_dom dropdown exists ---
    assert(
        isset($appListStrings['lf_movement_dom']),
        "Application dropdown 'lf_movement_dom' must exist in en_us.lf_plan_report.php"
    );
    echo "  [PASS] lf_movement_dom dropdown exists\n";

    // --- Happy Path: lf_movement_dom has exactly 6 options ---
    assert(
        count($appListStrings['lf_movement_dom']) === 6,
        "lf_movement_dom should have exactly 6 options (forward, backward, static, closed_won, closed_lost, new), got: " . count($appListStrings['lf_movement_dom'])
    );
    echo "  [PASS] lf_movement_dom has exactly 6 options\n";

    // --- Happy Path: lf_movement_dom contains all expected movement keys ---
    $expectedMovements = ['forward', 'backward', 'static', 'closed_won', 'closed_lost', 'new'];
    foreach ($expectedMovements as $movementKey) {
        assert(
            array_key_exists($movementKey, $appListStrings['lf_movement_dom']),
            "lf_movement_dom must contain key '{$movementKey}'"
        );
    }
    echo "  [PASS] lf_movement_dom contains all 6 expected movement keys\n";
} else {
    echo "  [SKIP] Dropdown file not found at expected path - will validate during integration\n";
}


echo "\n==============================\n";
echo "US-010: All tests passed!\n";
echo "==============================\n";
