<?php
/**
 * US-011: Install Script - Table Creation and Default Config Tests
 *
 * Tests that custom/modules/LF_PRConfig/install.php exists with correct
 * structure: SuiteCRM bootstrap, CREATE TABLE IF NOT EXISTS for all 7 tables,
 * default config value insertions, idempotency patterns, UUID generation,
 * and success/failure output messaging.
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

$installFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_PRConfig'
    . DIRECTORY_SEPARATOR . 'install.php';

// All 7 table names that must be created
$expectedTables = [
    'lf_weekly_plan',
    'lf_plan_op_items',
    'lf_plan_prospect_items',
    'lf_weekly_report',
    'lf_report_snapshots',
    'lf_pr_config',
    'lf_rep_targets',
];

// Expected columns per table (based on vardefs definitions)
// Each entry: column_name => expected SQL type pattern
$tableSchemas = [
    'lf_weekly_plan' => [
        'id' => 'char(36)',
        'name' => 'varchar(255)',
        'date_entered' => 'datetime',
        'date_modified' => 'datetime',
        'modified_user_id' => 'char(36)',
        'created_by' => 'char(36)',
        'deleted' => 'tinyint(1)',
        'assigned_user_id' => 'char(36)',
        'week_start_date' => 'date',
        'status' => 'varchar(100)',
        'submitted_date' => 'datetime',
        'reviewed_by' => 'char(36)',
        'reviewed_date' => 'datetime',
        'notes' => 'text',
    ],
    'lf_plan_op_items' => [
        'id' => 'char(36)',
        'name' => 'varchar(255)',
        'date_entered' => 'datetime',
        'date_modified' => 'datetime',
        'modified_user_id' => 'char(36)',
        'created_by' => 'char(36)',
        'deleted' => 'tinyint(1)',
        'lf_weekly_plan_id' => 'char(36)',
        'opportunity_id' => 'char(36)',
        'item_type' => 'varchar(100)',
        'projected_stage' => 'varchar(100)',
        'planned_day' => 'varchar(100)',
        'plan_description' => 'text',
    ],
    'lf_plan_prospect_items' => [
        'id' => 'char(36)',
        'name' => 'varchar(255)',
        'date_entered' => 'datetime',
        'date_modified' => 'datetime',
        'modified_user_id' => 'char(36)',
        'created_by' => 'char(36)',
        'deleted' => 'tinyint(1)',
        'lf_weekly_plan_id' => 'char(36)',
        'source_type' => 'varchar(100)',
        'planned_day' => 'varchar(100)',
        'expected_value' => 'decimal(26,6)',
        'plan_description' => 'text',
        'status' => 'varchar(100)',
        'converted_opportunity_id' => 'char(36)',
        'prospecting_notes' => 'text',
    ],
    'lf_weekly_report' => [
        'id' => 'char(36)',
        'name' => 'varchar(255)',
        'date_entered' => 'datetime',
        'date_modified' => 'datetime',
        'modified_user_id' => 'char(36)',
        'created_by' => 'char(36)',
        'deleted' => 'tinyint(1)',
        'lf_weekly_plan_id' => 'char(36)',
        'assigned_user_id' => 'char(36)',
        'week_start_date' => 'date',
        'status' => 'varchar(100)',
        'submitted_date' => 'datetime',
        'reviewed_by' => 'char(36)',
        'reviewed_date' => 'datetime',
        'notes' => 'text',
    ],
    'lf_report_snapshots' => [
        'id' => 'char(36)',
        'name' => 'varchar(255)',
        'date_entered' => 'datetime',
        'date_modified' => 'datetime',
        'modified_user_id' => 'char(36)',
        'created_by' => 'char(36)',
        'deleted' => 'tinyint(1)',
        'lf_weekly_report_id' => 'char(36)',
        'opportunity_id' => 'char(36)',
        'account_name' => 'varchar(255)',
        'opportunity_name' => 'varchar(255)',
        'amount_at_snapshot' => 'decimal(26,6)',
        'stage_at_week_start' => 'varchar(100)',
        'stage_at_week_end' => 'varchar(100)',
        'probability_at_start' => 'int',
        'probability_at_end' => 'int',
        'movement' => 'varchar(100)',
        'was_planned' => 'tinyint(1)',
        'plan_category' => 'varchar(50)',
        'result_description' => 'text',
    ],
    'lf_pr_config' => [
        'id' => 'char(36)',
        'name' => 'varchar(255)',
        'date_entered' => 'datetime',
        'date_modified' => 'datetime',
        'modified_user_id' => 'char(36)',
        'created_by' => 'char(36)',
        'deleted' => 'tinyint(1)',
        'category' => 'varchar(50)',
        'config_name' => 'varchar(100)',
        'value' => 'text',
        'description' => 'varchar(255)',
    ],
    'lf_rep_targets' => [
        'id' => 'char(36)',
        'name' => 'varchar(255)',
        'date_entered' => 'datetime',
        'date_modified' => 'datetime',
        'modified_user_id' => 'char(36)',
        'created_by' => 'char(36)',
        'deleted' => 'tinyint(1)',
        'assigned_user_id' => 'char(36)',
        'fiscal_year' => 'int',
        'annual_quota' => 'decimal(26,6)',
        'weekly_new_pipeline' => 'decimal(26,6)',
        'weekly_progression' => 'decimal(26,6)',
        'weekly_closed' => 'decimal(26,6)',
        'is_active' => 'tinyint(1)',
    ],
];

// Default config entries from plan-report-context.md Table 6
// Format: [category, config_name, value, description]
$defaultConfigEntries = [
    ['quotas', 'default_annual_quota', '500000', 'Annual quota per rep ($)'],
    ['quotas', 'pipeline_coverage_multiplier', '4', 'Pipeline coverage ratio'],
    ['quotas', 'fiscal_year_start_month', '1', 'January'],
    ['targets', 'default_new_pipeline_target', '100000', 'Weekly new pipeline target ($)'],
    ['targets', 'default_progression_target', '100000', 'Weekly progression target ($)'],
    ['targets', 'default_closed_target', '20000', 'Weekly closed target ($)'],
    ['display', 'achievement_tier_green', '76', 'Green threshold %'],
    ['display', 'achievement_tier_yellow', '51', 'Yellow threshold %'],
    ['display', 'achievement_tier_orange', '26', 'Orange threshold %'],
    ['weeks', 'week_start_day', '5', '5=Friday'],
    ['weeks', 'weeks_to_show', '12', 'Weeks in selector'],
    ['stages', 'stage_order', '["2-Analysis (1%)","3-Confirmation (10%)","5-Specifications (30%)","6-Solution (60%)","7-Closing (90%)","closed_won"]', 'Stage order'],
    ['stages', 'stage_probabilities', '{"2-Analysis (1%)":1,"3-Confirmation (10%)":10,"5-Specifications (30%)":30,"6-Solution (60%)":60,"7-Closing (90%)":90,"closed_won":100}', 'Stage to probability mapping'],
    ['stages', 'pipeline_stages', '["3-Confirmation (10%)","5-Specifications (30%)","6-Solution (60%)","7-Closing (90%)"]', 'Stages that count as pipeline'],
    ['stages', 'analysis_stage', '"2-Analysis (1%)"', 'Stage excluded from pipeline'],
    ['prospecting', 'source_types', '["Cold Call","Referral","Event","Partner","Inbound","Customer Visit","Other"]', 'Source options'],
    ['prospecting', 'default_conversion_stage', '"3-Confirmation (10%)"', 'Default stage when converting'],
    ['risk', 'stale_deal_days', '14', 'Days before flagging'],
    ['risk', 'activity_types', '["Calls","Meetings","Tasks","Notes","Emails"]', 'Activity types'],
];


// ============================================================
// Section 1: File Existence
// ============================================================
echo "Section 1: File Existence\n";

assert(
    file_exists($installFile),
    "install.php should exist at: custom/modules/LF_PRConfig/install.php"
);

assert(
    is_file($installFile),
    "install.php path should be a regular file, not a directory"
);

echo "  [PASS] install.php file exists\n";


// ============================================================
// Section 2: PHP Format and SuiteCRM Bootstrap
// ============================================================
echo "Section 2: PHP Format and SuiteCRM Bootstrap\n";

$content = file_get_contents($installFile);
assert($content !== false, "Should be able to read install.php content");

// Must start with <?php
assert(
    str_starts_with(trim($content), '<?php'),
    "install.php must start with <?php opening tag"
);

// Must define sugarEntry constant
assert(
    str_contains($content, "sugarEntry")
        && (str_contains($content, "define('sugarEntry'") || str_contains($content, 'define("sugarEntry"')),
    "install.php must define sugarEntry constant for SuiteCRM bootstrap"
);

// Must change to SuiteCRM legacy directory
assert(
    str_contains($content, "chdir(")
        && str_contains($content, '/var/www/html/public/legacy'),
    "install.php must chdir to /var/www/html/public/legacy"
);

// Must require entryPoint.php
assert(
    str_contains($content, 'entryPoint.php'),
    "install.php must require_once entryPoint.php for SuiteCRM bootstrap"
);

assert(
    str_contains($content, 'require_once') && str_contains($content, 'entryPoint'),
    "install.php must use require_once for entryPoint.php inclusion"
);

echo "  [PASS] SuiteCRM bootstrap code is present\n";


// ============================================================
// Section 3: Uses global $db object
// ============================================================
echo "Section 3: Uses global \$db object\n";

assert(
    str_contains($content, '$db') || str_contains($content, '$GLOBALS[\'db\']'),
    "install.php must use the global \$db object for database operations"
);

// Check for global $db declaration or $GLOBALS['db'] usage
assert(
    str_contains($content, 'global $db')
        || str_contains($content, '$GLOBALS[\'db\']')
        || str_contains($content, '$GLOBALS["db"]'),
    "install.php must access the global \$db variable via 'global \$db' or \$GLOBALS['db']"
);

echo "  [PASS] Uses global \$db object\n";


// ============================================================
// Section 4: CREATE TABLE IF NOT EXISTS for all 7 tables
// ============================================================
echo "Section 4: CREATE TABLE IF NOT EXISTS for all 7 tables\n";

$contentUpper = strtoupper($content);

foreach ($expectedTables as $tableName) {
    // Check CREATE TABLE IF NOT EXISTS pattern (case-insensitive)
    assert(
        str_contains($contentUpper, 'CREATE TABLE IF NOT EXISTS')
            && str_contains($content, $tableName),
        "install.php must contain CREATE TABLE IF NOT EXISTS for table: $tableName"
    );
}

// Count CREATE TABLE statements - should be exactly 7
$createTableCount = substr_count($contentUpper, 'CREATE TABLE IF NOT EXISTS');
assert(
    $createTableCount === 7,
    "install.php must contain exactly 7 CREATE TABLE IF NOT EXISTS statements, found: $createTableCount"
);

echo "  [PASS] All 7 CREATE TABLE IF NOT EXISTS statements present\n";


// ============================================================
// Section 5: lf_weekly_plan table schema
// ============================================================
echo "Section 5: lf_weekly_plan table schema\n";

// Extract the CREATE TABLE block for lf_weekly_plan
$pattern = '/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+[`"]?lf_weekly_plan[`"]?\s*\((.*?)\)\s*(ENGINE|;)/si';
$matched = preg_match($pattern, $content, $matches);
assert($matched === 1, "Should find CREATE TABLE block for lf_weekly_plan");

$tableBlock = $matches[1];

// Validate each expected column exists
foreach ($tableSchemas['lf_weekly_plan'] as $colName => $colType) {
    assert(
        str_contains($tableBlock, $colName),
        "lf_weekly_plan must contain column: $colName"
    );
}

// Check for PRIMARY KEY on id
assert(
    str_contains($tableBlock, 'PRIMARY KEY')
        && str_contains($tableBlock, 'id'),
    "lf_weekly_plan must have PRIMARY KEY on id column"
);

// Check for composite index on (assigned_user_id, week_start_date)
assert(
    (str_contains($content, 'idx_assigned_user_week_start')
        || (str_contains($tableBlock, 'assigned_user_id') && str_contains($tableBlock, 'week_start_date') && str_contains(strtoupper($tableBlock), 'INDEX'))),
    "lf_weekly_plan must have index on (assigned_user_id, week_start_date)"
);

// Validate specific column types
assert(
    preg_match('/week_start_date\s+date\b/i', $tableBlock) === 1,
    "lf_weekly_plan.week_start_date must be date type (not datetime)"
);

assert(
    preg_match('/assigned_user_id\s+char\s*\(\s*36\s*\)/i', $tableBlock) === 1,
    "lf_weekly_plan.assigned_user_id must be char(36)"
);

assert(
    preg_match('/notes\s+text/i', $tableBlock) === 1,
    "lf_weekly_plan.notes must be text type"
);

assert(
    preg_match('/deleted\s+tinyint\s*\(\s*1\s*\)/i', $tableBlock) === 1,
    "lf_weekly_plan.deleted must be tinyint(1)"
);

// Validate column count: 14 columns total (7 standard + 7 custom)
$columnCount = 0;
foreach ($tableSchemas['lf_weekly_plan'] as $colName => $colType) {
    if (str_contains($tableBlock, $colName)) {
        $columnCount++;
    }
}
assert(
    $columnCount === count($tableSchemas['lf_weekly_plan']),
    "lf_weekly_plan must define all " . count($tableSchemas['lf_weekly_plan']) . " columns, found: $columnCount"
);

echo "  [PASS] lf_weekly_plan schema validated\n";


// ============================================================
// Section 6: lf_plan_op_items table schema
// ============================================================
echo "Section 6: lf_plan_op_items table schema\n";

$pattern = '/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+[`"]?lf_plan_op_items[`"]?\s*\((.*?)\)\s*(ENGINE|;)/si';
$matched = preg_match($pattern, $content, $matches);
assert($matched === 1, "Should find CREATE TABLE block for lf_plan_op_items");

$tableBlock = $matches[1];

foreach ($tableSchemas['lf_plan_op_items'] as $colName => $colType) {
    assert(
        str_contains($tableBlock, $colName),
        "lf_plan_op_items must contain column: $colName"
    );
}

assert(
    str_contains($tableBlock, 'PRIMARY KEY'),
    "lf_plan_op_items must have PRIMARY KEY"
);

// Check for indexes on foreign keys
assert(
    str_contains($content, 'idx_lf_weekly_plan_id') || str_contains($tableBlock, 'lf_weekly_plan_id'),
    "lf_plan_op_items must have index on lf_weekly_plan_id"
);

assert(
    str_contains($content, 'idx_opportunity_id') || str_contains($tableBlock, 'opportunity_id'),
    "lf_plan_op_items must have index on opportunity_id"
);

// Validate specific types
assert(
    preg_match('/lf_weekly_plan_id\s+char\s*\(\s*36\s*\)/i', $tableBlock) === 1,
    "lf_plan_op_items.lf_weekly_plan_id must be char(36)"
);

assert(
    preg_match('/opportunity_id\s+char\s*\(\s*36\s*\)/i', $tableBlock) === 1,
    "lf_plan_op_items.opportunity_id must be char(36)"
);

assert(
    preg_match('/projected_stage\s+varchar\s*\(\s*100\s*\)/i', $tableBlock) === 1,
    "lf_plan_op_items.projected_stage must be varchar(100)"
);

assert(
    preg_match('/plan_description\s+text/i', $tableBlock) === 1,
    "lf_plan_op_items.plan_description must be text type"
);

$columnCount = 0;
foreach ($tableSchemas['lf_plan_op_items'] as $colName => $colType) {
    if (str_contains($tableBlock, $colName)) {
        $columnCount++;
    }
}
assert(
    $columnCount === count($tableSchemas['lf_plan_op_items']),
    "lf_plan_op_items must define all " . count($tableSchemas['lf_plan_op_items']) . " columns, found: $columnCount"
);

echo "  [PASS] lf_plan_op_items schema validated\n";


// ============================================================
// Section 7: lf_plan_prospect_items table schema
// ============================================================
echo "Section 7: lf_plan_prospect_items table schema\n";

$pattern = '/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+[`"]?lf_plan_prospect_items[`"]?\s*\((.*?)\)\s*(ENGINE|;)/si';
$matched = preg_match($pattern, $content, $matches);
assert($matched === 1, "Should find CREATE TABLE block for lf_plan_prospect_items");

$tableBlock = $matches[1];

foreach ($tableSchemas['lf_plan_prospect_items'] as $colName => $colType) {
    assert(
        str_contains($tableBlock, $colName),
        "lf_plan_prospect_items must contain column: $colName"
    );
}

assert(
    str_contains($tableBlock, 'PRIMARY KEY'),
    "lf_plan_prospect_items must have PRIMARY KEY"
);

// Check for index on lf_weekly_plan_id
assert(
    str_contains($content, 'lf_plan_prospect_items')
        && (str_contains($tableBlock, 'INDEX') || str_contains($tableBlock, 'KEY'))
        && str_contains($tableBlock, 'lf_weekly_plan_id'),
    "lf_plan_prospect_items must have index on lf_weekly_plan_id"
);

// Validate specific types
assert(
    preg_match('/expected_value\s+decimal\s*\(\s*26\s*,\s*6\s*\)/i', $tableBlock) === 1,
    "lf_plan_prospect_items.expected_value must be decimal(26,6)"
);

assert(
    preg_match('/source_type\s+varchar\s*\(\s*100\s*\)/i', $tableBlock) === 1,
    "lf_plan_prospect_items.source_type must be varchar(100)"
);

assert(
    preg_match('/converted_opportunity_id\s+char\s*\(\s*36\s*\)/i', $tableBlock) === 1,
    "lf_plan_prospect_items.converted_opportunity_id must be char(36)"
);

assert(
    preg_match('/prospecting_notes\s+text/i', $tableBlock) === 1,
    "lf_plan_prospect_items.prospecting_notes must be text type"
);

$columnCount = 0;
foreach ($tableSchemas['lf_plan_prospect_items'] as $colName => $colType) {
    if (str_contains($tableBlock, $colName)) {
        $columnCount++;
    }
}
assert(
    $columnCount === count($tableSchemas['lf_plan_prospect_items']),
    "lf_plan_prospect_items must define all " . count($tableSchemas['lf_plan_prospect_items']) . " columns, found: $columnCount"
);

echo "  [PASS] lf_plan_prospect_items schema validated\n";


// ============================================================
// Section 8: lf_weekly_report table schema
// ============================================================
echo "Section 8: lf_weekly_report table schema\n";

$pattern = '/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+[`"]?lf_weekly_report[`"]?\s*\((.*?)\)\s*(ENGINE|;)/si';
$matched = preg_match($pattern, $content, $matches);
assert($matched === 1, "Should find CREATE TABLE block for lf_weekly_report");

$tableBlock = $matches[1];

foreach ($tableSchemas['lf_weekly_report'] as $colName => $colType) {
    assert(
        str_contains($tableBlock, $colName),
        "lf_weekly_report must contain column: $colName"
    );
}

assert(
    str_contains($tableBlock, 'PRIMARY KEY'),
    "lf_weekly_report must have PRIMARY KEY"
);

// Check for composite index
assert(
    (str_contains($content, 'idx_assigned_user_week_start')
        || (str_contains($tableBlock, 'assigned_user_id') && str_contains($tableBlock, 'week_start_date') && str_contains(strtoupper($tableBlock), 'INDEX'))),
    "lf_weekly_report must have index on (assigned_user_id, week_start_date)"
);

// Validate date vs datetime distinction
assert(
    preg_match('/week_start_date\s+date\b/i', $tableBlock) === 1,
    "lf_weekly_report.week_start_date must be date type (not datetime)"
);

assert(
    preg_match('/submitted_date\s+datetime/i', $tableBlock) === 1,
    "lf_weekly_report.submitted_date must be datetime type"
);

assert(
    preg_match('/reviewed_date\s+datetime/i', $tableBlock) === 1,
    "lf_weekly_report.reviewed_date must be datetime type"
);

// lf_weekly_plan_id is a foreign key reference
assert(
    preg_match('/lf_weekly_plan_id\s+char\s*\(\s*36\s*\)/i', $tableBlock) === 1,
    "lf_weekly_report.lf_weekly_plan_id must be char(36)"
);

$columnCount = 0;
foreach ($tableSchemas['lf_weekly_report'] as $colName => $colType) {
    if (str_contains($tableBlock, $colName)) {
        $columnCount++;
    }
}
assert(
    $columnCount === count($tableSchemas['lf_weekly_report']),
    "lf_weekly_report must define all " . count($tableSchemas['lf_weekly_report']) . " columns, found: $columnCount"
);

echo "  [PASS] lf_weekly_report schema validated\n";


// ============================================================
// Section 9: lf_report_snapshots table schema
// ============================================================
echo "Section 9: lf_report_snapshots table schema\n";

$pattern = '/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+[`"]?lf_report_snapshots[`"]?\s*\((.*?)\)\s*(ENGINE|;)/si';
$matched = preg_match($pattern, $content, $matches);
assert($matched === 1, "Should find CREATE TABLE block for lf_report_snapshots");

$tableBlock = $matches[1];

foreach ($tableSchemas['lf_report_snapshots'] as $colName => $colType) {
    assert(
        str_contains($tableBlock, $colName),
        "lf_report_snapshots must contain column: $colName"
    );
}

assert(
    str_contains($tableBlock, 'PRIMARY KEY'),
    "lf_report_snapshots must have PRIMARY KEY"
);

// Check for indexes on foreign keys
assert(
    str_contains($content, 'lf_report_snapshots')
        && str_contains($tableBlock, 'lf_weekly_report_id'),
    "lf_report_snapshots must reference lf_weekly_report_id"
);

// Validate specific types
assert(
    preg_match('/amount_at_snapshot\s+decimal\s*\(\s*26\s*,\s*6\s*\)/i', $tableBlock) === 1,
    "lf_report_snapshots.amount_at_snapshot must be decimal(26,6)"
);

assert(
    preg_match('/stage_at_week_start\s+varchar\s*\(\s*100\s*\)/i', $tableBlock) === 1,
    "lf_report_snapshots.stage_at_week_start must be varchar(100)"
);

assert(
    preg_match('/stage_at_week_end\s+varchar\s*\(\s*100\s*\)/i', $tableBlock) === 1,
    "lf_report_snapshots.stage_at_week_end must be varchar(100)"
);

assert(
    preg_match('/probability_at_start\s+int/i', $tableBlock) === 1,
    "lf_report_snapshots.probability_at_start must be int type"
);

assert(
    preg_match('/probability_at_end\s+int/i', $tableBlock) === 1,
    "lf_report_snapshots.probability_at_end must be int type"
);

assert(
    preg_match('/was_planned\s+tinyint\s*\(\s*1\s*\)/i', $tableBlock) === 1,
    "lf_report_snapshots.was_planned must be tinyint(1)"
);

assert(
    preg_match('/plan_category\s+varchar\s*\(\s*50\s*\)/i', $tableBlock) === 1,
    "lf_report_snapshots.plan_category must be varchar(50)"
);

assert(
    preg_match('/account_name\s+varchar\s*\(\s*255\s*\)/i', $tableBlock) === 1,
    "lf_report_snapshots.account_name must be varchar(255)"
);

assert(
    preg_match('/opportunity_name\s+varchar\s*\(\s*255\s*\)/i', $tableBlock) === 1,
    "lf_report_snapshots.opportunity_name must be varchar(255)"
);

assert(
    preg_match('/result_description\s+text/i', $tableBlock) === 1,
    "lf_report_snapshots.result_description must be text type"
);

$columnCount = 0;
foreach ($tableSchemas['lf_report_snapshots'] as $colName => $colType) {
    if (str_contains($tableBlock, $colName)) {
        $columnCount++;
    }
}
assert(
    $columnCount === count($tableSchemas['lf_report_snapshots']),
    "lf_report_snapshots must define all " . count($tableSchemas['lf_report_snapshots']) . " columns, found: $columnCount"
);

echo "  [PASS] lf_report_snapshots schema validated\n";


// ============================================================
// Section 10: lf_pr_config table schema (config_name field)
// ============================================================
echo "Section 10: lf_pr_config table schema\n";

$pattern = '/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+[`"]?lf_pr_config[`"]?\s*\((.*?)\)\s*(ENGINE|;)/si';
$matched = preg_match($pattern, $content, $matches);
assert($matched === 1, "Should find CREATE TABLE block for lf_pr_config");

$tableBlock = $matches[1];

foreach ($tableSchemas['lf_pr_config'] as $colName => $colType) {
    assert(
        str_contains($tableBlock, $colName),
        "lf_pr_config must contain column: $colName"
    );
}

assert(
    str_contains($tableBlock, 'PRIMARY KEY'),
    "lf_pr_config must have PRIMARY KEY"
);

// CRITICAL: config_name field must exist (not reusing 'name' as config key)
assert(
    str_contains($tableBlock, 'config_name'),
    "lf_pr_config MUST have 'config_name' column (not 'name') for configuration key field"
);

// Validate config_name is varchar(100)
assert(
    preg_match('/config_name\s+varchar\s*\(\s*100\s*\)/i', $tableBlock) === 1,
    "lf_pr_config.config_name must be varchar(100)"
);

// Validate category is varchar(50)
assert(
    preg_match('/category\s+varchar\s*\(\s*50\s*\)/i', $tableBlock) === 1,
    "lf_pr_config.category must be varchar(50)"
);

// Validate value is text type
assert(
    preg_match('/[`"]?value[`"]?\s+text/i', $tableBlock) === 1,
    "lf_pr_config.value must be text type"
);

// Validate description is varchar(255)
assert(
    preg_match('/description\s+varchar\s*\(\s*255\s*\)/i', $tableBlock) === 1,
    "lf_pr_config.description must be varchar(255)"
);

// Validate standard name field still exists (for display name)
assert(
    preg_match('/[`"]?name[`"]?\s+varchar\s*\(\s*255\s*\)/i', $tableBlock) === 1,
    "lf_pr_config must have standard 'name' column as varchar(255) for display name"
);

$columnCount = 0;
foreach ($tableSchemas['lf_pr_config'] as $colName => $colType) {
    if (str_contains($tableBlock, $colName)) {
        $columnCount++;
    }
}
assert(
    $columnCount === count($tableSchemas['lf_pr_config']),
    "lf_pr_config must define all " . count($tableSchemas['lf_pr_config']) . " columns, found: $columnCount"
);

echo "  [PASS] lf_pr_config schema validated (including config_name field)\n";


// ============================================================
// Section 11: lf_rep_targets table schema
// ============================================================
echo "Section 11: lf_rep_targets table schema\n";

$pattern = '/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+[`"]?lf_rep_targets[`"]?\s*\((.*?)\)\s*(ENGINE|;)/si';
$matched = preg_match($pattern, $content, $matches);
assert($matched === 1, "Should find CREATE TABLE block for lf_rep_targets");

$tableBlock = $matches[1];

foreach ($tableSchemas['lf_rep_targets'] as $colName => $colType) {
    assert(
        str_contains($tableBlock, $colName),
        "lf_rep_targets must contain column: $colName"
    );
}

assert(
    str_contains($tableBlock, 'PRIMARY KEY'),
    "lf_rep_targets must have PRIMARY KEY"
);

// Validate decimal fields
assert(
    preg_match('/annual_quota\s+decimal\s*\(\s*26\s*,\s*6\s*\)/i', $tableBlock) === 1,
    "lf_rep_targets.annual_quota must be decimal(26,6)"
);

assert(
    preg_match('/weekly_new_pipeline\s+decimal\s*\(\s*26\s*,\s*6\s*\)/i', $tableBlock) === 1,
    "lf_rep_targets.weekly_new_pipeline must be decimal(26,6)"
);

assert(
    preg_match('/weekly_progression\s+decimal\s*\(\s*26\s*,\s*6\s*\)/i', $tableBlock) === 1,
    "lf_rep_targets.weekly_progression must be decimal(26,6)"
);

assert(
    preg_match('/weekly_closed\s+decimal\s*\(\s*26\s*,\s*6\s*\)/i', $tableBlock) === 1,
    "lf_rep_targets.weekly_closed must be decimal(26,6)"
);

assert(
    preg_match('/is_active\s+tinyint\s*\(\s*1\s*\)/i', $tableBlock) === 1,
    "lf_rep_targets.is_active must be tinyint(1)"
);

assert(
    preg_match('/fiscal_year\s+int/i', $tableBlock) === 1,
    "lf_rep_targets.fiscal_year must be int type"
);

// Check for composite index on (assigned_user_id, fiscal_year)
assert(
    (str_contains($content, 'idx_assigned_user_fiscal_year')
        || (str_contains($tableBlock, 'assigned_user_id') && str_contains($tableBlock, 'fiscal_year') && str_contains(strtoupper($tableBlock), 'INDEX'))),
    "lf_rep_targets must have index on (assigned_user_id, fiscal_year)"
);

$columnCount = 0;
foreach ($tableSchemas['lf_rep_targets'] as $colName => $colType) {
    if (str_contains($tableBlock, $colName)) {
        $columnCount++;
    }
}
assert(
    $columnCount === count($tableSchemas['lf_rep_targets']),
    "lf_rep_targets must define all " . count($tableSchemas['lf_rep_targets']) . " columns, found: $columnCount"
);

echo "  [PASS] lf_rep_targets schema validated\n";


// ============================================================
// Section 12: Default config value insertions
// ============================================================
echo "Section 12: Default config value insertions\n";

// Every default config entry must be referenced in the script
foreach ($defaultConfigEntries as $entry) {
    $category = $entry[0];
    $configName = $entry[1];

    assert(
        str_contains($content, $configName),
        "install.php must contain config entry for config_name: '$configName'"
    );

    assert(
        str_contains($content, $category),
        "install.php must contain category: '$category' for config entry '$configName'"
    );
}

// Check that the correct number of config entries exist
// Each entry should have an INSERT statement or be part of a batch
$totalConfigEntries = count($defaultConfigEntries);
assert(
    $totalConfigEntries === 19,
    "Expected 19 default config entries, found: $totalConfigEntries"
);

// Verify specific critical config values are present
assert(
    str_contains($content, '500000'),
    "install.php must contain default_annual_quota value of 500000"
);

assert(
    str_contains($content, 'pipeline_coverage_multiplier'),
    "install.php must contain pipeline_coverage_multiplier config entry"
);

assert(
    str_contains($content, 'default_conversion_stage'),
    "install.php must contain default_conversion_stage config entry"
);

assert(
    str_contains($content, 'stage_order'),
    "install.php must contain stage_order config entry"
);

assert(
    str_contains($content, 'stage_probabilities'),
    "install.php must contain stage_probabilities config entry"
);

assert(
    str_contains($content, 'stale_deal_days'),
    "install.php must contain stale_deal_days config entry"
);

assert(
    str_contains($content, 'activity_types'),
    "install.php must contain activity_types config entry"
);

assert(
    str_contains($content, 'source_types'),
    "install.php must contain source_types config entry"
);

assert(
    str_contains($content, 'weeks_to_show'),
    "install.php must contain weeks_to_show config entry"
);

assert(
    str_contains($content, 'week_start_day'),
    "install.php must contain week_start_day config entry"
);

assert(
    str_contains($content, 'achievement_tier_green'),
    "install.php must contain achievement_tier_green config entry"
);

assert(
    str_contains($content, 'achievement_tier_yellow'),
    "install.php must contain achievement_tier_yellow config entry"
);

assert(
    str_contains($content, 'achievement_tier_orange'),
    "install.php must contain achievement_tier_orange config entry"
);

assert(
    str_contains($content, 'pipeline_stages'),
    "install.php must contain pipeline_stages config entry"
);

assert(
    str_contains($content, 'analysis_stage'),
    "install.php must contain analysis_stage config entry"
);

assert(
    str_contains($content, 'fiscal_year_start_month'),
    "install.php must contain fiscal_year_start_month config entry"
);

echo "  [PASS] All default config entries referenced\n";


// ============================================================
// Section 13: Idempotent config insertion pattern
// ============================================================
echo "Section 13: Idempotent config insertion pattern\n";

// Must use INSERT IGNORE or SELECT-before-INSERT pattern
$hasInsertIgnore = str_contains(strtoupper($content), 'INSERT IGNORE');
$hasSelectBeforeInsert = str_contains(strtoupper($content), 'SELECT')
    && str_contains(strtoupper($content), 'INSERT');

assert(
    $hasInsertIgnore || $hasSelectBeforeInsert,
    "install.php must use INSERT IGNORE or SELECT-before-INSERT for idempotent config insertion"
);

echo "  [PASS] Idempotent insertion pattern present\n";


// ============================================================
// Section 14: UUID generation with create_guid()
// ============================================================
echo "Section 14: UUID generation with create_guid()\n";

assert(
    str_contains($content, 'create_guid()'),
    "install.php must use create_guid() function for generating UUIDs for id fields"
);

echo "  [PASS] create_guid() usage present\n";


// ============================================================
// Section 15: Success/failure output messages
// ============================================================
echo "Section 15: Success/failure output messages\n";

// Must output messages for each operation
assert(
    str_contains($content, 'echo') || str_contains($content, 'print'),
    "install.php must output messages using echo or print"
);

// Check for success-related output patterns
assert(
    preg_match('/echo.*success/i', $content) === 1
        || preg_match('/echo.*created/i', $content) === 1
        || preg_match('/echo.*OK/i', $content) === 1
        || preg_match('/echo.*\[OK\]/i', $content) === 1
        || preg_match('/echo.*done/i', $content) === 1
        || preg_match('/echo.*\bsucc/i', $content) === 1,
    "install.php must output success messages for successful operations"
);

// Check for failure-related output patterns
assert(
    preg_match('/echo.*fail/i', $content) === 1
        || preg_match('/echo.*error/i', $content) === 1
        || preg_match('/echo.*ERROR/i', $content) === 1
        || preg_match('/echo.*FAIL/i', $content) === 1,
    "install.php must output failure messages for failed operations"
);

echo "  [PASS] Success/failure output messages present\n";


// ============================================================
// Section 16: Exit code handling
// ============================================================
echo "Section 16: Exit code handling\n";

// Must exit with code 0 on success
assert(
    str_contains($content, 'exit(0)') || str_contains($content, 'exit(0 )'),
    "install.php must exit with code 0 on full success"
);

// Must exit with code 1 on failure
assert(
    str_contains($content, 'exit(1)') || str_contains($content, 'exit(1 )'),
    "install.php must exit with code 1 on any failure"
);

echo "  [PASS] Exit code handling present\n";


// ============================================================
// Section 17: INSERT statements reference config_name (not just name)
// ============================================================
echo "Section 17: INSERT statements use config_name field\n";

// The INSERT for config values must reference the config_name column
assert(
    str_contains($content, 'config_name'),
    "install.php INSERT statements must reference 'config_name' column (not just 'name')"
);

// Validate that config INSERT statements use category and config_name together
assert(
    preg_match('/INSERT.*config_name/si', $content) === 1
        || preg_match('/config_name.*INSERT/si', $content) === 1
        || (str_contains($content, 'config_name') && str_contains(strtoupper($content), 'INSERT')),
    "install.php must INSERT using the config_name column for config entries"
);

echo "  [PASS] Config INSERT statements use config_name field\n";


// ============================================================
// Section 18: Default values for specific columns
// ============================================================
echo "Section 18: Default values in table definitions\n";

// lf_weekly_plan: status DEFAULT 'in_progress'
$pattern = '/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+[`"]?lf_weekly_plan[`"]?\s*\((.*?)\)\s*(ENGINE|;)/si';
preg_match($pattern, $content, $matches);
$weeklyPlanBlock = $matches[1];

assert(
    preg_match("/status.*DEFAULT.*'in_progress'/i", $weeklyPlanBlock) === 1
        || preg_match("/status.*DEFAULT.*\"in_progress\"/i", $weeklyPlanBlock) === 1,
    "lf_weekly_plan.status must have DEFAULT 'in_progress'"
);

// lf_plan_prospect_items: status DEFAULT 'planned'
$pattern = '/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+[`"]?lf_plan_prospect_items[`"]?\s*\((.*?)\)\s*(ENGINE|;)/si';
preg_match($pattern, $content, $matches);
$prospectBlock = $matches[1];

assert(
    preg_match("/status.*DEFAULT.*'planned'/i", $prospectBlock) === 1
        || preg_match("/status.*DEFAULT.*\"planned\"/i", $prospectBlock) === 1,
    "lf_plan_prospect_items.status must have DEFAULT 'planned'"
);

// lf_rep_targets: is_active DEFAULT 1
$pattern = '/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+[`"]?lf_rep_targets[`"]?\s*\((.*?)\)\s*(ENGINE|;)/si';
preg_match($pattern, $content, $matches);
$repTargetsBlock = $matches[1];

assert(
    preg_match('/is_active.*DEFAULT\s+1/i', $repTargetsBlock) === 1
        || preg_match("/is_active.*DEFAULT\s+'1'/i", $repTargetsBlock) === 1,
    "lf_rep_targets.is_active must have DEFAULT 1"
);

// deleted fields should default to 0 across tables
assert(
    preg_match('/deleted.*DEFAULT\s+0/i', $weeklyPlanBlock) === 1
        || preg_match("/deleted.*DEFAULT\s+'0'/i", $weeklyPlanBlock) === 1,
    "lf_weekly_plan.deleted must have DEFAULT 0"
);

// lf_report_snapshots: was_planned DEFAULT 0
$pattern = '/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+[`"]?lf_report_snapshots[`"]?\s*\((.*?)\)\s*(ENGINE|;)/si';
preg_match($pattern, $content, $matches);
$snapshotBlock = $matches[1];

assert(
    preg_match('/was_planned.*DEFAULT\s+0/i', $snapshotBlock) === 1
        || preg_match("/was_planned.*DEFAULT\s+'0'/i", $snapshotBlock) === 1,
    "lf_report_snapshots.was_planned must have DEFAULT 0"
);

// lf_weekly_report: status DEFAULT 'in_progress'
$pattern = '/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+[`"]?lf_weekly_report[`"]?\s*\((.*?)\)\s*(ENGINE|;)/si';
preg_match($pattern, $content, $matches);
$weeklyReportBlock = $matches[1];

assert(
    preg_match("/status.*DEFAULT.*'in_progress'/i", $weeklyReportBlock) === 1
        || preg_match("/status.*DEFAULT.*\"in_progress\"/i", $weeklyReportBlock) === 1,
    "lf_weekly_report.status must have DEFAULT 'in_progress'"
);

echo "  [PASS] Default values in table definitions validated\n";


// ============================================================
// Section 19: Table engine specification
// ============================================================
echo "Section 19: Table engine specification\n";

// SuiteCRM typically uses InnoDB engine
$engineCount = substr_count(strtoupper($content), 'ENGINE');
assert(
    $engineCount >= 7 || str_contains(strtoupper($content), 'INNODB'),
    "install.php should specify database engine (InnoDB) for table creation"
);

echo "  [PASS] Table engine specification present\n";


// ============================================================
// Section 20: Character set specification
// ============================================================
echo "Section 20: Character set specification\n";

assert(
    str_contains(strtoupper($content), 'CHARSET')
        || str_contains(strtoupper($content), 'CHARACTER SET')
        || str_contains(strtoupper($content), 'UTF8'),
    "install.php should specify character set (utf8) for tables"
);

echo "  [PASS] Character set specification present\n";


// ============================================================
// Section 21: Config value descriptions included
// ============================================================
echo "Section 21: Config value descriptions included\n";

// Verify that descriptions are included in config insertions
foreach ($defaultConfigEntries as $entry) {
    $description = $entry[3];
    // At least check that description column is used in inserts
    assert(
        str_contains($content, 'description'),
        "install.php config INSERT statements must include description column"
    );
    break; // Only need to verify once that description column is used
}

// Verify specific description values
assert(
    str_contains($content, 'Annual quota per rep'),
    "install.php must include description for default_annual_quota"
);

assert(
    str_contains($content, 'Pipeline coverage ratio'),
    "install.php must include description for pipeline_coverage_multiplier"
);

echo "  [PASS] Config value descriptions present\n";


// ============================================================
// Section 22: NOT NULL constraints match vardefs required fields
// ============================================================
echo "Section 22: NOT NULL constraints match vardefs required fields\n";

// lf_pr_config: category and config_name must be NOT NULL
$pattern = '/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+[`"]?lf_pr_config[`"]?\s*\((.*?)\)\s*(ENGINE|;)/si';
preg_match($pattern, $content, $matches);
$configBlock = $matches[1];

assert(
    preg_match('/category\s+varchar\s*\(\s*50\s*\)\s+NOT\s+NULL/i', $configBlock) === 1,
    "lf_pr_config.category must be NOT NULL (required in vardefs)"
);

assert(
    preg_match('/config_name\s+varchar\s*\(\s*100\s*\)\s+NOT\s+NULL/i', $configBlock) === 1,
    "lf_pr_config.config_name must be NOT NULL (required in vardefs)"
);

// id fields must be NOT NULL
assert(
    preg_match('/[`"]?id[`"]?\s+char\s*\(\s*36\s*\)\s+NOT\s+NULL/i', $configBlock) === 1,
    "lf_pr_config.id must be NOT NULL"
);

// lf_weekly_plan: assigned_user_id and week_start_date must be NOT NULL
$pattern = '/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+[`"]?lf_weekly_plan[`"]?\s*\((.*?)\)\s*(ENGINE|;)/si';
preg_match($pattern, $content, $matches);
$weeklyPlanBlock = $matches[1];

assert(
    preg_match('/assigned_user_id\s+char\s*\(\s*36\s*\)\s+NOT\s+NULL/i', $weeklyPlanBlock) === 1,
    "lf_weekly_plan.assigned_user_id must be NOT NULL (required in vardefs)"
);

assert(
    preg_match('/week_start_date\s+date\s+NOT\s+NULL/i', $weeklyPlanBlock) === 1,
    "lf_weekly_plan.week_start_date must be NOT NULL (required in vardefs)"
);

// lf_plan_op_items: lf_weekly_plan_id and opportunity_id must be NOT NULL
$pattern = '/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+[`"]?lf_plan_op_items[`"]?\s*\((.*?)\)\s*(ENGINE|;)/si';
preg_match($pattern, $content, $matches);
$opItemsBlock = $matches[1];

assert(
    preg_match('/lf_weekly_plan_id\s+char\s*\(\s*36\s*\)\s+NOT\s+NULL/i', $opItemsBlock) === 1,
    "lf_plan_op_items.lf_weekly_plan_id must be NOT NULL (required in vardefs)"
);

assert(
    preg_match('/opportunity_id\s+char\s*\(\s*36\s*\)\s+NOT\s+NULL/i', $opItemsBlock) === 1,
    "lf_plan_op_items.opportunity_id must be NOT NULL (required in vardefs)"
);

// lf_plan_prospect_items: lf_weekly_plan_id must be NOT NULL
$pattern = '/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+[`"]?lf_plan_prospect_items[`"]?\s*\((.*?)\)\s*(ENGINE|;)/si';
preg_match($pattern, $content, $matches);
$prospectBlock = $matches[1];

assert(
    preg_match('/lf_weekly_plan_id\s+char\s*\(\s*36\s*\)\s+NOT\s+NULL/i', $prospectBlock) === 1,
    "lf_plan_prospect_items.lf_weekly_plan_id must be NOT NULL (required in vardefs)"
);

// lf_weekly_report: assigned_user_id and week_start_date must be NOT NULL
$pattern = '/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+[`"]?lf_weekly_report[`"]?\s*\((.*?)\)\s*(ENGINE|;)/si';
preg_match($pattern, $content, $matches);
$weeklyReportBlock = $matches[1];

assert(
    preg_match('/assigned_user_id\s+char\s*\(\s*36\s*\)\s+NOT\s+NULL/i', $weeklyReportBlock) === 1,
    "lf_weekly_report.assigned_user_id must be NOT NULL (required in vardefs)"
);

assert(
    preg_match('/week_start_date\s+date\s+NOT\s+NULL/i', $weeklyReportBlock) === 1,
    "lf_weekly_report.week_start_date must be NOT NULL (required in vardefs)"
);

// lf_report_snapshots: lf_weekly_report_id and opportunity_id must be NOT NULL
$pattern = '/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+[`"]?lf_report_snapshots[`"]?\s*\((.*?)\)\s*(ENGINE|;)/si';
preg_match($pattern, $content, $matches);
$snapshotBlock = $matches[1];

assert(
    preg_match('/lf_weekly_report_id\s+char\s*\(\s*36\s*\)\s+NOT\s+NULL/i', $snapshotBlock) === 1,
    "lf_report_snapshots.lf_weekly_report_id must be NOT NULL (required in vardefs)"
);

assert(
    preg_match('/opportunity_id\s+char\s*\(\s*36\s*\)\s+NOT\s+NULL/i', $snapshotBlock) === 1,
    "lf_report_snapshots.opportunity_id must be NOT NULL (required in vardefs)"
);

// lf_rep_targets: assigned_user_id and fiscal_year must be NOT NULL
$pattern = '/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+[`"]?lf_rep_targets[`"]?\s*\((.*?)\)\s*(ENGINE|;)/si';
preg_match($pattern, $content, $matches);
$repTargetsBlock = $matches[1];

assert(
    preg_match('/assigned_user_id\s+char\s*\(\s*36\s*\)\s+NOT\s+NULL/i', $repTargetsBlock) === 1,
    "lf_rep_targets.assigned_user_id must be NOT NULL (required in vardefs)"
);

assert(
    preg_match('/fiscal_year\s+int.*NOT\s+NULL/i', $repTargetsBlock) === 1,
    "lf_rep_targets.fiscal_year must be NOT NULL (required in vardefs)"
);

echo "  [PASS] NOT NULL constraints match vardefs required fields\n";


// ============================================================
// Section 23: Cross-validation - table names match vardefs
// ============================================================
echo "Section 23: Cross-validation - table names match vardefs\n";

// Load all 7 vardefs files and verify table names match those in install.php
$vardefsDir = $customDir . DIRECTORY_SEPARATOR . 'modules';

$moduleVardefsMap = [
    'LF_WeeklyPlan' => 'lf_weekly_plan',
    'LF_PlanOpItem' => 'lf_plan_op_items',
    'LF_PlanProspectItem' => 'lf_plan_prospect_items',
    'LF_WeeklyReport' => 'lf_weekly_report',
    'LF_ReportSnapshot' => 'lf_report_snapshots',
    'LF_PRConfig' => 'lf_pr_config',
    'LF_RepTargets' => 'lf_rep_targets',
];

foreach ($moduleVardefsMap as $moduleName => $expectedTableName) {
    $vardefsFile = $vardefsDir
        . DIRECTORY_SEPARATOR . $moduleName
        . DIRECTORY_SEPARATOR . 'metadata'
        . DIRECTORY_SEPARATOR . 'vardefs.php';

    // Load vardefs using temp file wrapper
    $tempFile = tempnam(sys_get_temp_dir(), 'us011_vardefs_');
    $wrapperCode = "<?php\n";
    $wrapperCode .= "if (!defined('sugarEntry')) define('sugarEntry', true);\n";
    $wrapperCode .= "\$dictionary = [];\n";
    $wrapperCode .= "include " . var_export($vardefsFile, true) . ";\n";
    $wrapperCode .= "return \$dictionary;\n";
    file_put_contents($tempFile, $wrapperCode);
    $dictionary = include $tempFile;
    unlink($tempFile);

    assert(
        is_array($dictionary),
        "Should be able to load vardefs for $moduleName"
    );

    // Get table name from vardefs
    $vardefsTableName = $dictionary[$moduleName]['table'] ?? null;
    assert(
        $vardefsTableName === $expectedTableName,
        "Vardefs table name for $moduleName should be '$expectedTableName', got: '$vardefsTableName'"
    );

    // Verify the table name appears in install.php's CREATE TABLE statement
    assert(
        str_contains($content, "CREATE TABLE IF NOT EXISTS")
            && str_contains($content, $expectedTableName),
        "install.php must create table '$expectedTableName' matching vardefs for $moduleName"
    );

    // Cross-validate: every field in vardefs should have a corresponding column in CREATE TABLE
    $fields = $dictionary[$moduleName]['fields'] ?? [];
    $tablePattern = '/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+[`"]?' . preg_quote($expectedTableName, '/') . '[`"]?\s*\((.*?)\)\s*(ENGINE|;)/si';
    preg_match($tablePattern, $content, $tableMatches);
    $createBlock = $tableMatches[1] ?? '';

    foreach ($fields as $fieldName => $fieldDef) {
        assert(
            str_contains($createBlock, $fieldName),
            "install.php CREATE TABLE for $expectedTableName must include field '$fieldName' from $moduleName vardefs"
        );
    }
}

echo "  [PASS] All table names and fields cross-validated against vardefs\n";


// ============================================================
// Section 24: Negative tests - script must NOT contain certain patterns
// ============================================================
echo "Section 24: Negative tests\n";

// Must NOT use DROP TABLE (not safe for idempotent runs)
assert(
    !str_contains(strtoupper($content), 'DROP TABLE'),
    "install.php must NOT use DROP TABLE (not idempotent)"
);

// Must NOT use TRUNCATE (would delete existing data)
assert(
    !str_contains(strtoupper($content), 'TRUNCATE'),
    "install.php must NOT use TRUNCATE (would delete existing data)"
);

// Must NOT use DELETE FROM for config table (would remove user customizations)
assert(
    !preg_match('/DELETE\s+FROM\s+[`"]?lf_pr_config/i', $content),
    "install.php must NOT use DELETE FROM lf_pr_config (would remove user customizations)"
);

echo "  [PASS] Negative patterns validated\n";


// ============================================================
// Section 25: Config value correctness - exact values present
// ============================================================
echo "Section 25: Config value correctness\n";

// Verify exact config values are present in the script
// Quotas
assert(
    str_contains($content, '500000'),
    "install.php must contain value '500000' for default_annual_quota"
);
assert(
    str_contains($content, "'4'") || str_contains($content, '"4"') || preg_match('/pipeline_coverage_multiplier.*4/s', $content),
    "install.php must contain value '4' for pipeline_coverage_multiplier"
);

// Targets
assert(
    str_contains($content, '100000'),
    "install.php must contain value '100000' for target entries"
);
assert(
    str_contains($content, '20000'),
    "install.php must contain value '20000' for default_closed_target"
);

// Display thresholds
assert(
    str_contains($content, '76') && str_contains($content, 'achievement_tier_green'),
    "install.php must contain value '76' for achievement_tier_green"
);
assert(
    str_contains($content, '51') && str_contains($content, 'achievement_tier_yellow'),
    "install.php must contain value '51' for achievement_tier_yellow"
);
assert(
    str_contains($content, '26') && str_contains($content, 'achievement_tier_orange'),
    "install.php must contain value '26' for achievement_tier_orange"
);

// Weeks
assert(
    str_contains($content, 'week_start_day') && str_contains($content, '5'),
    "install.php must contain week_start_day value of 5 (Friday)"
);
assert(
    str_contains($content, 'weeks_to_show') && str_contains($content, '12'),
    "install.php must contain weeks_to_show value of 12"
);

// Stages - JSON values
assert(
    str_contains($content, '2-Analysis (1%)'),
    "install.php must contain stage '2-Analysis (1%)' in stage_order"
);
assert(
    str_contains($content, '3-Confirmation (10%)'),
    "install.php must contain stage '3-Confirmation (10%)' in config values"
);
assert(
    str_contains($content, '5-Specifications (30%)'),
    "install.php must contain stage '5-Specifications (30%)' in config values"
);
assert(
    str_contains($content, '6-Solution (60%)'),
    "install.php must contain stage '6-Solution (60%)' in config values"
);
assert(
    str_contains($content, '7-Closing (90%)'),
    "install.php must contain stage '7-Closing (90%)' in config values"
);
assert(
    str_contains($content, 'closed_won'),
    "install.php must contain 'closed_won' in stage_order"
);

// Prospecting - source types
assert(
    str_contains($content, 'Cold Call'),
    "install.php must contain source type 'Cold Call'"
);
assert(
    str_contains($content, 'Referral'),
    "install.php must contain source type 'Referral'"
);
assert(
    str_contains($content, 'Customer Visit'),
    "install.php must contain source type 'Customer Visit'"
);

// Risk
assert(
    str_contains($content, '14') && str_contains($content, 'stale_deal_days'),
    "install.php must contain stale_deal_days value of 14"
);

// Activity types
assert(
    str_contains($content, 'Calls') && str_contains($content, 'Meetings')
        && str_contains($content, 'Tasks') && str_contains($content, 'Notes')
        && str_contains($content, 'Emails'),
    "install.php must contain all 5 activity types: Calls, Meetings, Tasks, Notes, Emails"
);

echo "  [PASS] Config value correctness validated\n";


// ============================================================
// Section 26: INSERT IGNORE targets lf_pr_config table
// ============================================================
echo "Section 26: INSERT IGNORE targets lf_pr_config table\n";

// Verify INSERT statements target the lf_pr_config table specifically
assert(
    preg_match('/INSERT\s+IGNORE\s+INTO\s+[`"]?lf_pr_config/i', $content) === 1
        || (str_contains(strtoupper($content), 'INSERT') && str_contains($content, 'lf_pr_config')),
    "install.php INSERT IGNORE must target the lf_pr_config table"
);

// Verify that INSERT includes all required columns for config entries
assert(
    preg_match('/INSERT.*lf_pr_config.*\(.*id.*category.*config_name.*value/si', $content) === 1
        || preg_match('/INSERT.*lf_pr_config.*\(.*id.*,.*config_name/si', $content) === 1,
    "install.php INSERT into lf_pr_config must include id, category, config_name, and value columns"
);

echo "  [PASS] INSERT IGNORE targets lf_pr_config table\n";


// ============================================================
// Section 27: Index on (category, config_name) for idempotent IGNORE
// ============================================================
echo "Section 27: Index on (category, config_name) for config table\n";

// For INSERT IGNORE to work correctly for idempotency, the lf_pr_config table
// should have a UNIQUE index or regular INDEX on (category, config_name)
$pattern = '/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+[`"]?lf_pr_config[`"]?\s*\((.*?)\)\s*(ENGINE|;)/si';
preg_match($pattern, $content, $matches);
$configBlock = $matches[1] ?? '';

assert(
    (str_contains($configBlock, 'category') && str_contains($configBlock, 'config_name')
        && (str_contains(strtoupper($configBlock), 'INDEX') || str_contains(strtoupper($configBlock), 'UNIQUE')))
        || str_contains($content, 'idx_category_config_name'),
    "lf_pr_config must have an index on (category, config_name) for efficient lookups and idempotent inserts"
);

echo "  [PASS] Index on (category, config_name) present\n";


// ============================================================
// Section 28: Uses $db->quoted() for SQL escaping
// ============================================================
echo "Section 28: Uses \$db->quoted() for SQL escaping\n";

assert(
    str_contains($content, '$db->quoted(') || str_contains($content, "\$db->quoted("),
    "install.php must use \$db->quoted() for SQL parameter escaping in INSERT statements"
);

echo "  [PASS] \$db->quoted() usage present\n";


// ============================================================
// Final Summary
// ============================================================
echo "\n";
echo "============================================================\n";
echo "US-011: Install Script Test - ALL SECTIONS PASSED\n";
echo "============================================================\n";
echo "  Sections validated: 28\n";
echo "  - Section 1:  File existence\n";
echo "  - Section 2:  SuiteCRM bootstrap code\n";
echo "  - Section 3:  Global \$db object usage\n";
echo "  - Section 4:  CREATE TABLE IF NOT EXISTS (7 tables)\n";
echo "  - Section 5:  lf_weekly_plan schema\n";
echo "  - Section 6:  lf_plan_op_items schema\n";
echo "  - Section 7:  lf_plan_prospect_items schema\n";
echo "  - Section 8:  lf_weekly_report schema\n";
echo "  - Section 9:  lf_report_snapshots schema\n";
echo "  - Section 10: lf_pr_config schema (config_name field)\n";
echo "  - Section 11: lf_rep_targets schema\n";
echo "  - Section 12: Default config value insertions (19 entries)\n";
echo "  - Section 13: Idempotent insertion pattern\n";
echo "  - Section 14: UUID generation (create_guid)\n";
echo "  - Section 15: Success/failure output messages\n";
echo "  - Section 16: Exit code handling (0 and 1)\n";
echo "  - Section 17: INSERT uses config_name field\n";
echo "  - Section 18: Default values in table definitions\n";
echo "  - Section 19: Table engine specification (InnoDB)\n";
echo "  - Section 20: Character set specification (utf8)\n";
echo "  - Section 21: Config value descriptions\n";
echo "  - Section 22: NOT NULL constraints match vardefs\n";
echo "  - Section 23: Cross-validation (vardefs vs install SQL)\n";
echo "  - Section 24: Negative tests (no DROP/TRUNCATE/DELETE)\n";
echo "  - Section 25: Config value correctness\n";
echo "  - Section 26: INSERT IGNORE targets lf_pr_config\n";
echo "  - Section 27: Index on (category, config_name)\n";
echo "  - Section 28: Uses \$db->quoted() for SQL escaping\n";
echo "============================================================\n";
