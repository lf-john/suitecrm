<?php 
 //WARNING: The contents of this file are auto-generated


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$dictionary['LF_PRConfig'] = [
    'table' => 'lf_pr_config',
    'fields' => [
        // Standard SuiteCRM fields
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

        // Custom fields
        'category' => [
            'name' => 'category',
            'type' => 'varchar',
            'len' => 50,
            'required' => true,
        ],
        'config_name' => [
            'name' => 'config_name',
            'type' => 'varchar',
            'len' => 100,
            'required' => true,
        ],
        'value' => [
            'name' => 'value',
            'type' => 'text',
        ],
        'description' => [
            'name' => 'description',
            'type' => 'varchar',
            'len' => 255,
        ],
    ],
    'indices' => [
        [
            'name' => 'lf_pr_config_pk',
            'type' => 'primary',
            'fields' => ['id'],
        ],
        [
            'name' => 'idx_category_config_name',
            'type' => 'unique',
            'fields' => ['category', 'config_name'],
        ],
    ],
];

?>