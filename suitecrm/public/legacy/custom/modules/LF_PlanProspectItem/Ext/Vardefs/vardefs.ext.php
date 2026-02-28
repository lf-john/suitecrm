<?php 
 //WARNING: The contents of this file are auto-generated


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$dictionary['LF_PlanProspectItem'] = [
    'table' => 'lf_plan_prospect_items',
    'fields' => [
        'id' => [
            'name' => 'id',
            'type' => 'id',
            'required' => true,
        ],
        'name' => [
            'name' => 'name',
            'type' => 'name',
            'required' => true,
        ],
        'date_entered' => [
            'name' => 'date_entered',
            'type' => 'datetime',
            'required' => true,
        ],
        'date_modified' => [
            'name' => 'date_modified',
            'type' => 'datetime',
            'required' => true,
        ],
        'modified_user_id' => [
            'name' => 'modified_user_id',
            'type' => 'id',
        ],
        'created_by' => [
            'name' => 'created_by',
            'type' => 'id',
        ],
        'deleted' => [
            'name' => 'deleted',
            'type' => 'bool',
            'default' => '0',
            'required' => true,
        ],
        'lf_weekly_plan_id' => [
            'name' => 'lf_weekly_plan_id',
            'type' => 'id',
            'required' => true,
        ],
        'source_type' => [
            'name' => 'source_type',
            'type' => 'varchar',
            'len' => 100,
        ],
        'planned_day' => [
            'name' => 'planned_day',
            'type' => 'enum',
            'options' => 'lf_planned_day_dom',
        ],
        'expected_value' => [
            'name' => 'expected_value',
            'type' => 'decimal',
            'dbType' => 'decimal',
            'len' => '26,6',
        ],
        'plan_description' => [
            'name' => 'plan_description',
            'type' => 'text',
        ],
        'status' => [
            'name' => 'status',
            'type' => 'enum',
            'options' => 'lf_prospect_status_dom',
            'default' => 'planned',
        ],
        'converted_opportunity_id' => [
            'name' => 'converted_opportunity_id',
            'type' => 'id',
        ],
        'prospecting_notes' => [
            'name' => 'prospecting_notes',
            'type' => 'text',
        ],
    ],
    'indices' => [
        [
            'name' => 'lf_plan_prospect_items_pk',
            'type' => 'primary',
            'fields' => ['id'],
        ],
        [
            'name' => 'idx_lf_weekly_plan_id',
            'type' => 'index',
            'fields' => ['lf_weekly_plan_id'],
        ],
    ],
];

?>