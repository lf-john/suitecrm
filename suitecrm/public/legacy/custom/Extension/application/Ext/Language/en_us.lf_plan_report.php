<?php
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
