<?php
/**
 * Custom Tab Configuration - Weekly Planning & Reporting
 *
 * This file adds Weekly Planning module to the SuiteCRM tab structure.
 * After deployment, run Admin > Repair > Quick Repair and Rebuild.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

// Check if tabStructure exists and add our module to Sales group
if (isset($GLOBALS['tabStructure']['LBL_TABGROUP_SALES']['modules'])) {
    if (!in_array('LF_WeeklyPlan', $GLOBALS['tabStructure']['LBL_TABGROUP_SALES']['modules'])) {
        $GLOBALS['tabStructure']['LBL_TABGROUP_SALES']['modules'][] = 'LF_WeeklyPlan';
    }
}
