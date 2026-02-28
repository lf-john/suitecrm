<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$dictionary['LF_WeeklyReport'] = [
    'table' => 'lf_weekly_report',
    'fields' => [
        'id' => [
            'name' => 'id',
            'type' => 'id',
            'required' => true,
        ],
        'name' => [
            'name' => 'name',
            'type' => 'varchar', 'len' => '255',
            'dbType' => 'varchar',
            'len' => '255',
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
        ],
        'assigned_user_id' => [
            'name' => 'assigned_user_id',
            'type' => 'id',
            'required' => true,
        ],
        'week_start_date' => [
            'name' => 'week_start_date',
            'type' => 'date',
            'required' => true,
        ],
        'status' => [
            'name' => 'status',
            'type' => 'enum',
            'options' => 'lf_plan_status_dom',
            'default' => 'in_progress',
        ],
        'submitted_date' => [
            'name' => 'submitted_date',
            'type' => 'datetime',
        ],
        'reviewed_by' => [
            'name' => 'reviewed_by',
            'type' => 'id',
        ],
        'reviewed_date' => [
            'name' => 'reviewed_date',
            'type' => 'datetime',
        ],
        'notes' => [
            'name' => 'notes',
            'type' => 'text',
        ],
    ],
    'indices' => [
        [
            'name' => 'lf_weekly_report_pk',
            'type' => 'primary',
            'fields' => ['id'],
        ],
        [
            'name' => 'idx_user_week',
            'type' => 'unique',
            'fields' => ['assigned_user_id', 'week_start_date'],
        ],
    ],
];
