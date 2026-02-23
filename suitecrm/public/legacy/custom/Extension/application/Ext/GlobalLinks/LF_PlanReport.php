<?php
/**
 * US-014: GlobalLinks Extension - Weekly Planning & Reporting Navigation
 *
 * Adds 'Weekly Plan' and 'Weekly Plan Mgmt' links to the SuiteCRM
 * global navigation dropdown.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

// Register Weekly Plan navigation link (for sales reps)
$global_links['LF_WeeklyPlan'] = array(
    'linkinfo' => array(
        'Weekly Plan' => 'index.php?module=LF_WeeklyPlan&action=planning',
    ),
);

// Register Weekly Plan Mgmt navigation link (for admins)
// This will be shown in admin menu - requires is_admin check in the view
$global_links['LF_WeeklyPlanMgmt'] = array(
    'linkinfo' => array(
        'Weekly Plan Mgmt' => 'index.php?module=LF_PRConfig&action=config',
    ),
);

// Register modules in app_list_strings moduleList
$GLOBALS['app_list_strings']['moduleList']['LF_WeeklyPlan'] = 'Weekly Plan';
$GLOBALS['app_list_strings']['moduleList']['LF_WeeklyPlanMgmt'] = 'Weekly Plan Mgmt';
