<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

global $mod_strings;

$module_menu = [
    ['index.php?module=LF_WeeklyPlan&action=planning', $mod_strings['LNK_PLANNING'], 'View', 'LF_WeeklyPlan'],
    ['index.php?module=LF_WeeklyPlan&action=plan', $mod_strings['LNK_DASHBOARD'], 'View', 'LF_WeeklyPlan'],
    ['index.php?module=LF_WeeklyPlan&action=report', $mod_strings['LNK_REPORTING'], 'View', 'LF_WeeklyPlan'],
];
