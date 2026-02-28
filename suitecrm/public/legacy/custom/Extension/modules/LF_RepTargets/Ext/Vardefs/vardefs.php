<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$dictionary['LF_RepTargets'] = [
    'table' => 'lf_rep_targets',
    'fields' => [
        // Standard SuiteCRM fields
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

        // Custom fields
        'assigned_user_id' => [
            'name' => 'assigned_user_id',
            'type' => 'id',
            'required' => true,
        ],
        'fiscal_year' => [
            'name' => 'fiscal_year',
            'type' => 'int',
            'required' => true,
        ],
        'annual_quota' => [
            'name' => 'annual_quota',
            'type' => 'decimal',
            'dbType' => 'decimal',
            'len' => '26,6',
        ],
        'weekly_new_pipeline' => [
            'name' => 'weekly_new_pipeline',
            'type' => 'decimal',
            'dbType' => 'decimal',
            'len' => '26,6',
        ],
        'weekly_progression' => [
            'name' => 'weekly_progression',
            'type' => 'decimal',
            'dbType' => 'decimal',
            'len' => '26,6',
        ],
        'weekly_closed' => [
            'name' => 'weekly_closed',
            'type' => 'decimal',
            'dbType' => 'decimal',
            'len' => '26,6',
        ],
        'is_active' => [
            'name' => 'is_active',
            'type' => 'bool',
            'default' => 1,
        ],
    ],
    'indices' => [
        [
            'name' => 'lf_rep_targets_pk',
            'type' => 'primary',
            'fields' => ['id'],
        ],
        [
            'name' => 'idx_rep_targets_user_year',
            'type' => 'index',
            'fields' => ['assigned_user_id', 'fiscal_year'],
        ],
    ],
];
