<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$dictionary['LF_ReportSnapshot'] = [
    'table' => 'lf_report_snapshots',
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
        'lf_weekly_report_id' => [
            'name' => 'lf_weekly_report_id',
            'type' => 'id',
            'required' => true,
        ],
        'opportunity_id' => [
            'name' => 'opportunity_id',
            'type' => 'id',
            'required' => true,
        ],
        'account_name' => [
            'name' => 'account_name',
            'type' => 'varchar',
            'len' => 255,
        ],
        'opportunity_name' => [
            'name' => 'opportunity_name',
            'type' => 'varchar',
            'len' => 255,
        ],
        'amount_at_snapshot' => [
            'name' => 'amount_at_snapshot',
            'type' => 'decimal',
            'dbType' => 'decimal',
            'len' => '26,6',
        ],
        'stage_at_week_start' => [
            'name' => 'stage_at_week_start',
            'type' => 'varchar',
            'len' => 100,
        ],
        'stage_at_week_end' => [
            'name' => 'stage_at_week_end',
            'type' => 'varchar',
            'len' => 100,
        ],
        'probability_at_start' => [
            'name' => 'probability_at_start',
            'type' => 'int',
        ],
        'probability_at_end' => [
            'name' => 'probability_at_end',
            'type' => 'int',
        ],
        'movement' => [
            'name' => 'movement',
            'type' => 'enum',
            'options' => 'lf_movement_dom',
        ],
        'was_planned' => [
            'name' => 'was_planned',
            'type' => 'bool',
            'default' => '0',
        ],
        'plan_category' => [
            'name' => 'plan_category',
            'type' => 'varchar',
            'len' => 50,
        ],
        'result_description' => [
            'name' => 'result_description',
            'type' => 'text',
        ],
    ],
    'indices' => [
        [
            'name' => 'lf_report_snapshots_pk',
            'type' => 'primary',
            'fields' => ['id'],
        ],
        [
            'name' => 'idx_lf_weekly_report_id',
            'type' => 'index',
            'fields' => ['lf_weekly_report_id'],
        ],
        [
            'name' => 'idx_opportunity_id',
            'type' => 'index',
            'fields' => ['opportunity_id'],
        ],
    ],
];
