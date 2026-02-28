<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$searchFields['LF_WeeklyPlan'] = array(
    'name' => array(
        'query_type' => 'default',
    ),
    'week_start_date' => array(
        'query_type' => 'default',
    ),
    'status' => array(
        'query_type' => 'default',
        'options' => 'lf_plan_status_dom',
    ),
    'assigned_user_id' => array(
        'query_type' => 'default',
    ),
);
