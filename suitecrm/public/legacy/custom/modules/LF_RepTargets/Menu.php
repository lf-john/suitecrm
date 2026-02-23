<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings, $app_strings;

$module_menu = array(
    array(
        'index.php?module=LF_RepTargets&action=manage',
        $mod_strings['LBL_MANAGE_REP_TARGETS'] ?? 'Manage Rep Targets',
        'LF_RepTargets',
        'LF_RepTargets'
    ),
);