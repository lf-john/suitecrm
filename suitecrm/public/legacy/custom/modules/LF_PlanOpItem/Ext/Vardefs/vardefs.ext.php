<?php 
 //WARNING: The contents of this file are auto-generated


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$dictionary['LF_PlanOpItem'] = [
    'table' => 'lf_plan_op_items',
    'fields' => [
        'id' => [
            'name' => 'id',
            'type' => 'id',
            'required' => true,
        ],
        'name' => [
            'name' => 'name',
            'type' => 'varchar', 'len' => '255',
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
        'opportunity_id' => [
            'name' => 'opportunity_id',
            'type' => 'id',
            'required' => true,
        ],
        'item_type' => [
            'name' => 'item_type',
            'type' => 'enum',
            'options' => 'lf_plan_item_type_dom',
        ],
        'projected_stage' => [
            'name' => 'projected_stage',
            'type' => 'varchar',
            'len' => 100,
        ],
        'planned_day' => [
            'name' => 'planned_day',
            'type' => 'enum',
            'options' => 'lf_planned_day_dom',
        ],
        'plan_description' => [
            'name' => 'plan_description',
            'type' => 'text',
        ],
    ],
    'indices' => [
        [
            'name' => 'lf_plan_op_items_pk',
            'type' => 'primary',
            'fields' => ['id'],
        ],
        [
            'name' => 'idx_plan_id',
            'type' => 'index',
            'fields' => ['lf_weekly_plan_id'],
        ],
        [
            'name' => 'idx_opportunity_id',
            'type' => 'index',
            'fields' => ['opportunity_id'],
        ],
    ],
];

?>