<?php
/**
 * Tab Configuration Extension - Weekly Planning & Reporting
 *
 * Adds Weekly Planning module to the Sales tab group for SuiteCRM navigation.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

// Add LF_WeeklyPlan to the Sales tab group
if (isset($GLOBALS['tabStructure']['LBL_TABGROUP_SALES']['modules'])) {
    if (!in_array('LF_WeeklyPlan', $GLOBALS['tabStructure']['LBL_TABGROUP_SALES']['modules'])) {
        $GLOBALS['tabStructure']['LBL_TABGROUP_SALES']['modules'][] = 'LF_WeeklyPlan';
    }
}
