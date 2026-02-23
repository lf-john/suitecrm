<?php
/**
 * US-002: Rep Management View Tests
 *
 * Tests that the LF_RepTargets manage view file and its companion Menu.php
 * exist with correct structure, class definition, table rendering, Add Rep form,
 * inline editing, default value display, active toggle, and security patterns.
 *
 * Implementation requirements:
 *   - File: custom/modules/LF_RepTargets/views/view.manage.php
 *   - Class: LF_RepTargetsViewManage extends SugarView
 *   - File: custom/modules/LF_RepTargets/Menu.php
 *
 * The view queries lf_rep_targets joined with users table for rep names.
 * Renders an HTML table with columns: Rep Name, Fiscal Year, Annual Quota,
 * Weekly New Pipeline Target, Weekly Progression Target, Weekly Closed Target,
 * Active Status.
 *
 * Add Rep form: dropdown of SuiteCRM users NOT already in lf_rep_targets.
 * Inline editing: annual_quota, weekly_new_pipeline, weekly_progression, weekly_closed.
 * Null/empty target fields show 'Default ($X)' where $X is from lf_pr_config.
 * Toggle button for is_active flag.
 * POST handling for add/edit/toggle operations using $db->query() with $db->quote().
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

// Base path: from custom/tests/ up one level to custom/
$customDir = dirname(__DIR__);

$viewFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_RepTargets'
    . DIRECTORY_SEPARATOR . 'views'
    . DIRECTORY_SEPARATOR . 'view.manage.php';

$menuFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_RepTargets'
    . DIRECTORY_SEPARATOR . 'Menu.php';

// No .tpl files should exist for this view
$tplFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_RepTargets'
    . DIRECTORY_SEPARATOR . 'views'
    . DIRECTORY_SEPARATOR . 'manage.tpl';

$tplFileAlt = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_RepTargets'
    . DIRECTORY_SEPARATOR . 'tpl'
    . DIRECTORY_SEPARATOR . 'manage.tpl';

// Table columns that must appear in the view
$requiredColumns = [
    'Rep Name',
    'Fiscal Year',
    'Annual Quota',
    'Weekly New Pipeline',
    'Weekly Progression',
    'Weekly Closed',
    'Active',
];

// Target fields that support inline editing and default display
$editableTargetFields = [
    'annual_quota',
    'weekly_new_pipeline',
    'weekly_progression',
    'weekly_closed',
];

// Config keys for default values (from lf_pr_config 'quotas' and 'targets' categories)
$configDefaultKeys = [
    'default_annual_quota',
    'default_new_pipeline_target',
    'default_progression_target',
    'default_closed_target',
];


// ============================================================
// Section 1: View File Existence
// ============================================================
echo "Section 1: View File Existence\n";

assert(
    file_exists($viewFile),
    "View file should exist at: custom/modules/LF_RepTargets/views/view.manage.php"
);
echo "  [PASS] View file exists\n";

assert(
    is_file($viewFile),
    "View file path should be a regular file, not a directory"
);
echo "  [PASS] View file is a regular file\n";


// ============================================================
// Section 2: View File PHP Format
// ============================================================
echo "\nSection 2: View File PHP Format\n";

$viewContent = file_get_contents($viewFile);
assert($viewContent !== false, "Should be able to read the view file");

// File starts with <?php
assert(
    str_starts_with(trim($viewContent), '<?php'),
    "View file must start with <?php"
);
echo "  [PASS] View file starts with <?php\n";

// sugarEntry guard
assert(
    str_contains($viewContent, "defined('sugarEntry')"),
    "View file must contain sugarEntry guard: defined('sugarEntry')"
);
assert(
    str_contains($viewContent, 'Not A Valid Entry Point'),
    "View file must contain 'Not A Valid Entry Point' die message"
);
echo "  [PASS] View file has sugarEntry guard\n";


// ============================================================
// Section 3: View Class Structure
// ============================================================
echo "\nSection 3: View Class Structure\n";

// Class name and inheritance
assert(
    preg_match('/class\s+LF_RepTargetsViewManage\s+extends\s+SugarView/', $viewContent) === 1,
    "View file must contain 'class LF_RepTargetsViewManage extends SugarView'"
);
echo "  [PASS] Class LF_RepTargetsViewManage extends SugarView\n";

// AllowDynamicProperties attribute for PHP 8.x
assert(
    preg_match('/#\[\\\\AllowDynamicProperties\]/', $viewContent) === 1,
    "View file must have #[\\AllowDynamicProperties] attribute for PHP 8.x compatibility"
);
echo "  [PASS] View class has #[\\AllowDynamicProperties] attribute\n";

// Requires SugarView
assert(
    str_contains($viewContent, 'SugarView.php'),
    "View file must require_once SugarView.php"
);
echo "  [PASS] View file requires SugarView.php\n";

// show_header set to true
assert(
    str_contains($viewContent, 'show_header') && str_contains($viewContent, 'true'),
    "View must set show_header to true for SuiteCRM header"
);
echo "  [PASS] show_header is set to true\n";

// show_footer set to true
assert(
    str_contains($viewContent, 'show_footer') && str_contains($viewContent, 'true'),
    "View must set show_footer to true for SuiteCRM footer"
);
echo "  [PASS] show_footer is set to true\n";

// display() method
assert(
    preg_match('/function\s+display\s*\(/', $viewContent) === 1,
    "View must have a display() method"
);
echo "  [PASS] View has display() method\n";

// Constructor
assert(
    str_contains($viewContent, '__construct'),
    "View must have a constructor"
);
echo "  [PASS] View has constructor\n";

// No .tpl template files (must use echo HTML)
assert(
    !file_exists($tplFile),
    "Must NOT have a .tpl template file at views/manage.tpl - echo HTML instead"
);
assert(
    !file_exists($tplFileAlt),
    "Must NOT have a .tpl template file at tpl/manage.tpl - echo HTML instead"
);
echo "  [PASS] No .tpl template files exist\n";

// Uses echo for HTML output (significant amount for table + form)
$echoCount = preg_match_all('/\becho\s/', $viewContent);
assert(
    $echoCount >= 10,
    "View must use echo statements for HTML output (at least 10 expected for table + form), found: {$echoCount}"
);
echo "  [PASS] View uses echo for HTML output ({$echoCount} echo statements)\n";

// Does NOT use Smarty templates
assert(
    !str_contains($viewContent, 'SugarSmarty'),
    "View must NOT use SugarSmarty template engine"
);
assert(
    !str_contains($viewContent, '$this->ss->'),
    "View must NOT use \$this->ss-> Smarty pattern"
);
echo "  [PASS] View does not use Smarty templates\n";


// ============================================================
// Section 4: Database Access and Query Patterns
// ============================================================
echo "\nSection 4: Database Access and Query Patterns\n";

// Uses global $db for database access
assert(
    str_contains($viewContent, '$db'),
    "View must use \$db for database access"
);
echo "  [PASS] View uses \$db\n";

// Queries lf_rep_targets table
assert(
    str_contains($viewContent, 'lf_rep_targets'),
    "View must query the lf_rep_targets table"
);
echo "  [PASS] View references lf_rep_targets table\n";

// JOINs with users table for rep names
assert(
    preg_match('/JOIN\s+users/i', $viewContent) === 1,
    "View must JOIN with users table to get rep names"
);
echo "  [PASS] View JOINs with users table\n";

// References first_name and last_name from users
assert(
    str_contains($viewContent, 'first_name') && str_contains($viewContent, 'last_name'),
    "View must retrieve user first_name and last_name"
);
echo "  [PASS] View retrieves user first_name and last_name\n";

// Filters deleted=0
assert(
    str_contains($viewContent, 'deleted'),
    "View queries must filter by deleted field"
);
echo "  [PASS] View filters by deleted field\n";

// Uses $db->query() for database operations
assert(
    str_contains($viewContent, '$db->query('),
    "View must use \$db->query() for database operations"
);
echo "  [PASS] View uses \$db->query()\n";

// Uses $db->fetchByAssoc() to iterate results
assert(
    str_contains($viewContent, 'fetchByAssoc'),
    "View must use \$db->fetchByAssoc() to iterate query results"
);
echo "  [PASS] View uses \$db->fetchByAssoc()\n";


// ============================================================
// Section 5: Users Exclusion Query for Add Rep Dropdown
// ============================================================
echo "\nSection 5: Users Exclusion Query\n";

// Queries users table for the Add Rep dropdown
assert(
    preg_match('/SELECT.*FROM\s+users/is', $viewContent) === 1,
    "View must query users table for Add Rep dropdown"
);
echo "  [PASS] View queries users table\n";

// Excludes users already in lf_rep_targets (NOT IN or LEFT JOIN ... IS NULL)
assert(
    preg_match('/NOT\s+IN|NOT\s+EXISTS|LEFT\s+JOIN.*IS\s+NULL/is', $viewContent) === 1,
    "View must exclude users already in lf_rep_targets from the Add Rep dropdown"
);
echo "  [PASS] View excludes existing reps from user dropdown\n";

// Generates <select> dropdown for user selection
assert(
    str_contains($viewContent, '<select') && str_contains($viewContent, '</select>'),
    "View must have a <select> dropdown for user selection"
);
echo "  [PASS] View has <select> dropdown for user selection\n";

// Generates <option> elements for users
assert(
    str_contains($viewContent, '<option'),
    "View must generate <option> elements for each available user"
);
echo "  [PASS] View generates <option> elements\n";


// ============================================================
// Section 6: Rep Table Columns
// ============================================================
echo "\nSection 6: Rep Table Columns\n";

// Contains an HTML table
assert(
    str_contains($viewContent, '<table') && str_contains($viewContent, '</table>'),
    "View must contain an HTML <table> for rep listing"
);
echo "  [PASS] View contains HTML table\n";

// Contains table header row with <th> elements
assert(
    str_contains($viewContent, '<th'),
    "View must contain <th> header elements for table columns"
);
echo "  [PASS] View contains <th> header elements\n";

// Check each required column header is present
foreach ($requiredColumns as $col) {
    assert(
        str_contains($viewContent, $col),
        "View table must include column header: '{$col}'"
    );
    echo "  [PASS] Column '{$col}' found\n";
}

// Table has at least 7 column headers
$thCount = preg_match_all('/<th\b/i', $viewContent);
assert(
    $thCount >= 7,
    "View table must have at least 7 <th> column headers, found: {$thCount}"
);
echo "  [PASS] Table has at least 7 column headers ({$thCount} found)\n";


// ============================================================
// Section 7: Add Rep Form
// ============================================================
echo "\nSection 7: Add Rep Form\n";

// Contains a <form> element
assert(
    str_contains($viewContent, '<form') && str_contains($viewContent, '</form>'),
    "View must contain a <form> element for Add Rep"
);
echo "  [PASS] View contains <form> element\n";

// Form uses POST method
assert(
    preg_match('/<form[^>]*method\s*=\s*["\']post["\']/i', $viewContent) === 1,
    "Form must use POST method"
);
echo "  [PASS] Form uses POST method\n";

// Add Rep form text/button is present
assert(
    preg_match('/Add\s+Rep/i', $viewContent) === 1,
    "View must have 'Add Rep' text or button label"
);
echo "  [PASS] 'Add Rep' text found\n";

// Form includes fiscal year field (for new rep)
assert(
    str_contains($viewContent, 'fiscal_year'),
    "Add Rep form must include fiscal_year field"
);
echo "  [PASS] Add Rep form includes fiscal_year field\n";

// Form includes submit button or input
assert(
    str_contains($viewContent, 'type="submit"') || str_contains($viewContent, "type='submit'"),
    "Add Rep form must have a submit button"
);
echo "  [PASS] Form has submit button\n";


// ============================================================
// Section 8: Inline Editing of Target Fields
// ============================================================
echo "\nSection 8: Inline Editing\n";

// All 4 editable target fields are referenced in the view
foreach ($editableTargetFields as $field) {
    assert(
        str_contains($viewContent, $field),
        "View must reference editable target field: '{$field}'"
    );
    echo "  [PASS] Editable field '{$field}' referenced\n";
}

// Contains input fields for editing (type="number" or type="text" for target values)
assert(
    str_contains($viewContent, 'type="number"') || str_contains($viewContent, "type='number'")
    || str_contains($viewContent, 'type="text"') || str_contains($viewContent, "type='text'"),
    "View must contain input fields for inline editing of target values"
);
echo "  [PASS] Input fields present for inline editing\n";

// Contains at least 4 number/text input fields (one per editable target)
$inputCount = preg_match_all('/type=["\'](?:number|text)["\']/', $viewContent);
assert(
    $inputCount >= 4,
    "View must have at least 4 input fields for editable targets, found: {$inputCount}"
);
echo "  [PASS] At least 4 input fields for editable targets ({$inputCount} found)\n";


// ============================================================
// Section 9: Default Value Display Pattern
// ============================================================
echo "\nSection 9: Default Value Display\n";

// View references LF_PRConfig for loading default values
assert(
    str_contains($viewContent, 'LF_PRConfig'),
    "View must reference LF_PRConfig class for loading default config values"
);
echo "  [PASS] View references LF_PRConfig class\n";

// Uses LF_PRConfig::getConfig() to retrieve defaults
assert(
    str_contains($viewContent, 'LF_PRConfig::getConfig(') || str_contains($viewContent, 'LF_PRConfig.php'),
    "View must use LF_PRConfig::getConfig() or include LF_PRConfig.php for default values"
);
echo "  [PASS] View loads config defaults\n";

// References config default keys for target values
foreach ($configDefaultKeys as $configKey) {
    assert(
        str_contains($viewContent, $configKey),
        "View must reference config key '{$configKey}' for default value display"
    );
    echo "  [PASS] Config key '{$configKey}' referenced\n";
}

// Displays 'Default' text for null/empty values
assert(
    str_contains($viewContent, 'Default'),
    "View must display 'Default' text when target values are null or empty"
);
echo "  [PASS] 'Default' text pattern found\n";

// Shows default value in parentheses format: Default ($X) or Default (value)
assert(
    preg_match('/Default\s*\(/', $viewContent) === 1
    || preg_match('/Default\s*\(\$/', $viewContent) === 1
    || (str_contains($viewContent, 'Default') && str_contains($viewContent, '($')),
    "View must show default values in 'Default (\$X)' format where \$X is the config value"
);
echo "  [PASS] Default value display format found\n";

// Checks for null or empty values before displaying defaults
assert(
    preg_match('/null|empty|===\s*\'\'|===\s*""/i', $viewContent) === 1,
    "View must check for null or empty values before displaying defaults"
);
echo "  [PASS] Null/empty check pattern found\n";


// ============================================================
// Section 10: Active Toggle
// ============================================================
echo "\nSection 10: Active Toggle\n";

// References is_active field
assert(
    str_contains($viewContent, 'is_active'),
    "View must reference the is_active field for toggle functionality"
);
echo "  [PASS] View references is_active field\n";

// Has a toggle mechanism (button, link, or checkbox)
assert(
    preg_match('/toggle|is_active.*button|is_active.*submit|type=["\']checkbox["\']/i', $viewContent) === 1
    || (str_contains($viewContent, 'is_active') && (str_contains($viewContent, 'button') || str_contains($viewContent, '<a '))),
    "View must have a toggle mechanism for is_active (button, link, or checkbox)"
);
echo "  [PASS] Toggle mechanism found for is_active\n";

// Active/Inactive status display text
assert(
    preg_match('/Active|Inactive|Yes|No/i', $viewContent) === 1,
    "View must display active/inactive status text"
);
echo "  [PASS] Active/Inactive status text found\n";


// ============================================================
// Section 11: POST Handling for Add/Edit/Toggle
// ============================================================
echo "\nSection 11: POST Handling\n";

// Checks for POST request method
assert(
    str_contains($viewContent, 'REQUEST_METHOD') || str_contains($viewContent, "'POST'") || str_contains($viewContent, '"POST"'),
    "View must detect POST requests"
);
echo "  [PASS] View detects POST requests\n";

// References $_POST for form data
assert(
    str_contains($viewContent, '$_POST') || str_contains($viewContent, '$_REQUEST'),
    "View must reference \$_POST or \$_REQUEST to access form data"
);
echo "  [PASS] View references \$_POST/\$_REQUEST\n";

// Handles 'add' operation
assert(
    preg_match('/add|insert|INSERT\s+INTO/i', $viewContent) === 1,
    "View must handle add/insert operation for new reps"
);
echo "  [PASS] Add/insert operation handled\n";

// Handles 'edit' or 'update' operation
assert(
    preg_match('/edit|update|UPDATE\s+lf_rep_targets/i', $viewContent) === 1,
    "View must handle edit/update operation for rep targets"
);
echo "  [PASS] Edit/update operation handled\n";

// Handles 'toggle' operation
assert(
    preg_match('/toggle|is_active.*UPDATE|UPDATE.*is_active/is', $viewContent) === 1,
    "View must handle toggle operation for is_active"
);
echo "  [PASS] Toggle operation handled\n";

// Uses $db->query() for save operations
assert(
    str_contains($viewContent, '$db->query('),
    "View must use \$db->query() for save operations"
);
echo "  [PASS] View uses \$db->query() for save\n";

// Uses $db->quote() for SQL safety
assert(
    str_contains($viewContent, '$db->quote('),
    "View must use \$db->quote() for SQL injection prevention"
);
echo "  [PASS] View uses \$db->quote() for SQL safety\n";

// Uses create_guid() for new record IDs
assert(
    str_contains($viewContent, 'create_guid()'),
    "View must use create_guid() when inserting new rep records"
);
echo "  [PASS] View uses create_guid() for new records\n";


// ============================================================
// Section 12: Security Patterns
// ============================================================
echo "\nSection 12: Security Patterns\n";

// Uses htmlspecialchars for output escaping
$htmlEscapeCount = substr_count($viewContent, 'htmlspecialchars(');
assert(
    $htmlEscapeCount >= 3,
    "View must use htmlspecialchars() at least 3 times for safe output, found: {$htmlEscapeCount}"
);
echo "  [PASS] htmlspecialchars() used {$htmlEscapeCount} times\n";

// Uses $db->quote() for SQL parameter escaping
$quoteCount = substr_count($viewContent, '$db->quote(');
assert(
    $quoteCount >= 2,
    "View must use \$db->quote() at least 2 times for SQL safety, found: {$quoteCount}"
);
echo "  [PASS] \$db->quote() used {$quoteCount} times\n";

// No raw $_POST values directly in SQL query strings
assert(
    !preg_match('/\$db->query\([^)]*\$_POST/', $viewContent),
    "View must NOT use raw \$_POST values directly in \$db->query() calls"
);
echo "  [PASS] No raw \$_POST values in SQL queries\n";

// Integer values are cast (int) not quoted
assert(
    str_contains($viewContent, '(int)'),
    "View must cast integer values with (int) for SQL safety"
);
echo "  [PASS] Integer casting used\n";


// ============================================================
// Section 13: Menu.php File Existence
// ============================================================
echo "\nSection 13: Menu.php File Existence\n";

assert(
    file_exists($menuFile),
    "Menu file should exist at: custom/modules/LF_RepTargets/Menu.php"
);
echo "  [PASS] Menu.php file exists\n";

assert(
    is_file($menuFile),
    "Menu file path should be a regular file, not a directory"
);
echo "  [PASS] Menu.php is a regular file\n";


// ============================================================
// Section 14: Menu.php PHP Format and Structure
// ============================================================
echo "\nSection 14: Menu.php Structure\n";

$menuContent = file_get_contents($menuFile);
assert($menuContent !== false, "Should be able to read the Menu.php file");

// File starts with <?php
assert(
    str_starts_with(trim($menuContent), '<?php'),
    "Menu.php must start with <?php"
);
echo "  [PASS] Menu.php starts with <?php\n";

// sugarEntry guard
assert(
    str_contains($menuContent, "defined('sugarEntry')"),
    "Menu.php must contain sugarEntry guard: defined('sugarEntry')"
);
assert(
    str_contains($menuContent, 'Not A Valid Entry Point'),
    "Menu.php must contain 'Not A Valid Entry Point' die message"
);
echo "  [PASS] Menu.php has sugarEntry guard\n";

// Uses $module_menu variable
assert(
    str_contains($menuContent, '$module_menu'),
    "Menu.php must define \$module_menu variable"
);
echo "  [PASS] Menu.php defines \$module_menu\n";

// Contains link to manage action
assert(
    str_contains($menuContent, 'action=manage'),
    "Menu.php must contain link with action=manage"
);
echo "  [PASS] Menu.php has action=manage link\n";

// Contains correct module reference
assert(
    str_contains($menuContent, 'module=LF_RepTargets'),
    "Menu.php must contain module=LF_RepTargets"
);
echo "  [PASS] Menu.php references module=LF_RepTargets\n";

// Full URL pattern check
assert(
    str_contains($menuContent, 'index.php?module=LF_RepTargets&action=manage'),
    "Menu.php must contain full URL: index.php?module=LF_RepTargets&action=manage"
);
echo "  [PASS] Menu.php has correct full manage URL\n";


// ============================================================
// Section 15: Menu.php Data Loading (temp wrapper)
// ============================================================
echo "\nSection 15: Menu.php Data Validation\n";

$tempFile = tempnam(sys_get_temp_dir(), 'us002_menu_');
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

// Menu has at least 1 entry
assert(
    count($moduleMenu) >= 1,
    "\$module_menu must have at least 1 entry, got: " . count($moduleMenu)
);
echo "  [PASS] \$module_menu has at least 1 entry\n";

// First entry or an entry has the manage URL
$foundManageLink = false;
foreach ($moduleMenu as $entry) {
    if (is_array($entry) && isset($entry[0]) && str_contains($entry[0], 'action=manage')) {
        $foundManageLink = true;
        break;
    }
}
assert(
    $foundManageLink,
    "\$module_menu must contain an entry with action=manage URL"
);
echo "  [PASS] \$module_menu has manage link entry\n";

// Each menu entry is an array with at least 2 elements (URL, Label)
foreach ($moduleMenu as $idx => $entry) {
    assert(
        is_array($entry) && count($entry) >= 2,
        "\$module_menu entry {$idx} must be an array with at least 2 elements [URL, Label]"
    );
}
echo "  [PASS] All menu entries have correct structure\n";


// ============================================================
// Section 16: Cross-Validation
// ============================================================
echo "\nSection 16: Cross-Validation\n";

// View file naming follows convention: view.manage.php
assert(
    str_ends_with($viewFile, 'view.manage.php'),
    "View file must follow naming convention: view.manage.php"
);
echo "  [PASS] View file naming follows convention\n";

// View class naming follows SuiteCRM convention: LF_RepTargetsViewManage
assert(
    str_contains($viewContent, 'LF_RepTargetsViewManage'),
    "Class name must follow SuiteCRM convention: LF_RepTargetsViewManage"
);
echo "  [PASS] Class name follows SuiteCRM convention\n";

// View requires LF_PRConfig bean for default values
assert(
    str_contains($viewContent, 'LF_PRConfig.php') || str_contains($viewContent, 'LF_PRConfig'),
    "View must require or reference LF_PRConfig for default values"
);
echo "  [PASS] View references LF_PRConfig dependency\n";

// LF_RepTargets bean file exists (dependency)
$beanFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_RepTargets'
    . DIRECTORY_SEPARATOR . 'LF_RepTargets.php';
assert(
    file_exists($beanFile),
    "LF_RepTargets bean file must exist (view dependency)"
);
echo "  [PASS] LF_RepTargets bean file exists (view dependency)\n";

// LF_PRConfig bean file exists (dependency)
$configBeanFile = $customDir
    . DIRECTORY_SEPARATOR . 'modules'
    . DIRECTORY_SEPARATOR . 'LF_PRConfig'
    . DIRECTORY_SEPARATOR . 'LF_PRConfig.php';
assert(
    file_exists($configBeanFile),
    "LF_PRConfig bean file must exist (view dependency for default values)"
);
echo "  [PASS] LF_PRConfig bean file exists (view dependency)\n";

// View file path is within the correct module directory
assert(
    str_contains($viewFile, 'LF_RepTargets' . DIRECTORY_SEPARATOR . 'views'),
    "View file must be in LF_RepTargets/views/ directory"
);
echo "  [PASS] View file is in correct directory\n";

// Menu file path is within the correct module directory
assert(
    str_contains($menuFile, 'LF_RepTargets' . DIRECTORY_SEPARATOR . 'Menu.php'),
    "Menu file must be at LF_RepTargets/Menu.php"
);
echo "  [PASS] Menu file is in correct directory\n";


echo "\n==============================\n";
echo "US-002: All tests passed!\n";
echo "==============================\n";
