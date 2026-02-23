<?php
/**
 * Fix ACL Actions for LF_* modules
 *
 * Run this script from the SuiteCRM legacy directory:
 *   cd /var/www/html/public/legacy
 *   php custom/scripts/fix_lf_module_acl.php
 *
 * Or from outside the container:
 *   docker exec suitecrm892_app php /var/www/html/public/legacy/custom/scripts/fix_lf_module_acl.php
 */

if (!defined('sugarEntry')) {
    define('sugarEntry', true);
}

// Change to legacy directory if needed
$legacyDir = realpath(__DIR__ . '/../../');
if (file_exists($legacyDir . '/include/entryPoint.php')) {
    chdir($legacyDir);
}

require_once('include/entryPoint.php');

echo "Fixing ACL Actions for LF_* modules...\n\n";

$db = DBManagerFactory::getInstance();

// ACL value constants
$ACL_ALLOW_ENABLED = 89;  // Required for module access
$ACL_ALLOW_ALL = 90;      // Full access to action

// Fix 'access' action to ACL_ALLOW_ENABLED (89)
$result = $db->query(sprintf(
    "UPDATE acl_actions SET aclaccess = %d WHERE category LIKE 'LF_%%' AND name = 'access' AND deleted = 0",
    $ACL_ALLOW_ENABLED
));
echo "Updated 'access' actions to $ACL_ALLOW_ENABLED (ACL_ALLOW_ENABLED)\n";

// Fix other actions to ACL_ALLOW_ALL (90)
$result = $db->query(sprintf(
    "UPDATE acl_actions SET aclaccess = %d WHERE category LIKE 'LF_%%' AND name IN ('view', 'list', 'edit', 'delete', 'export', 'import') AND deleted = 0",
    $ACL_ALLOW_ALL
));
echo "Updated view/list/edit/delete/export/import actions to $ACL_ALLOW_ALL (ACL_ALLOW_ALL)\n";

// Fix acltype to 'module'
$result = $db->query(
    "UPDATE acl_actions SET acltype = 'module' WHERE category LIKE 'LF_%' AND deleted = 0"
);
echo "Updated acltype to 'module'\n";

// Verify and display results
echo "\nVerifying changes:\n";
echo str_repeat('-', 60) . "\n";
echo sprintf("%-25s | %-10s | %-10s | %s\n", "Category", "Name", "AclType", "AclAccess");
echo str_repeat('-', 60) . "\n";

$result = $db->query(
    "SELECT category, name, acltype, aclaccess FROM acl_actions WHERE category LIKE 'LF_%' AND deleted = 0 ORDER BY category, name"
);

while ($row = $db->fetchByAssoc($result)) {
    echo sprintf(
        "%-25s | %-10s | %-10s | %s\n",
        $row['category'],
        $row['name'],
        $row['acltype'],
        $row['aclaccess']
    );
}

echo str_repeat('-', 60) . "\n";
echo "\nDone! Please clear caches and restart the application.\n";
echo "Run: rm -rf cache/modules/* && rm -rf cache/jsLanguage/*\n";
