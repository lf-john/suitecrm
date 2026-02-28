<?php
// WARNING: The contents of this file are auto-generated


/**
 * Custom Sales Stages for Logical Front
 * Follows Customer Centered Selling (CCS) methodology
 * 
 * Stage 2 = Analysis (1%) - Not yet a real opportunity, placeholder
 * Stage 3 = Confirmation (10%) - Should progress to Stage 5 in same meeting
 * Stage 5 = Specifications (30%)
 * Stage 6 = Solution (60%)
 * Stage 7 = Closing (90%)
 * 
 * Stages 1 and 4 are intentionally skipped per CCS methodology.
 */

$app_list_strings["sales_stage_dom"] = array(
    "2-Analysis (1%)" => "2-Analysis (1%)",
    "3-Confirmation (10%)" => "3-Confirmation (10%)",
    "5-Specifications (30%)" => "5-Specifications (30%)",
    "6-Solution (60%)" => "6-Solution (60%)",
    "7-Closing (90%)" => "7-Closing (90%)",
    "closed_won" => "Closed Won",
    "closed_lost" => "Closed Lost",
);

$app_list_strings["sales_probability_dom"] = array(
    "2-Analysis (1%)" => "1",
    "3-Confirmation (10%)" => "10",
    "5-Specifications (30%)" => "30",
    "6-Solution (60%)" => "60",
    "7-Closing (90%)" => "90",
    "closed_won" => "100",
    "closed_lost" => "0",
);

// Set default stage for new opportunities
$app_list_strings["sales_stage_default_key"] = "2-Analysis (1%)";


if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

// Module display names - required for Admin > Configure Module Menu Filters
$app_list_strings['moduleList']['LF_WeeklyPlan'] = 'Weekly Plan';
$app_list_strings['moduleList']['LF_WeeklyReport'] = 'Weekly Reporting';
$app_list_strings['moduleList']['LF_PlanOpItem'] = 'Plan Items';
$app_list_strings['moduleList']['LF_PlanProspectItem'] = 'Prospect Items';
$app_list_strings['moduleList']['LF_ReportSnapshot'] = 'Report Snapshots';
$app_list_strings['moduleList']['LF_PRConfig'] = 'Plan Config';
$app_list_strings['moduleList']['LF_RepTargets'] = 'Rep Targets';

// Admin module for Weekly Plan Management
$app_list_strings['moduleList']['LF_WeeklyPlanMgmt'] = 'Weekly Plan Mgmt';

$app_list_strings['lf_plan_status_dom'] = [
    'in_progress' => 'In Progress',
    'submitted' => 'Updates Complete',
    'reviewed' => 'Reviewed',
];

$app_list_strings['lf_plan_item_type_dom'] = [
    'closing' => 'Closing',
    'at_risk' => 'At Risk',
    'progression' => 'Progression',
];

$app_list_strings['lf_planned_day_dom'] = [
    'monday' => 'Monday',
    'tuesday' => 'Tuesday',
    'wednesday' => 'Wednesday',
    'thursday' => 'Thursday',
    'friday' => 'Friday',
];

$app_list_strings['lf_prospect_status_dom'] = [
    'planned' => 'Planned',
    'converted' => 'Converted',
    'no_opportunity' => 'No Opportunity',
];

$app_list_strings['lf_movement_dom'] = [
    'forward' => 'Forward',
    'backward' => 'Backward',
    'static' => 'Static',
    'closed_won' => 'Closed Won',
    'closed_lost' => 'Closed Lost',
    'new' => 'New',
];

$app_list_strings['lf_source_type_dom'] = [
    'cold_call' => 'Cold Call',
    'referral' => 'Referral',
    'event' => 'Event',
    'partner' => 'Partner',
    'inbound' => 'Inbound',
    'customer_visit' => 'Customer Visit',
    'other' => 'Other',
];

