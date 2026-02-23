<?php 
 $GLOBALS["dictionary"]["LF_PRConfig"]=array (
  'table' => 'lf_pr_config',
  'fields' => 
  array (
    'id' => 
    array (
      'name' => 'id',
      'type' => 'id',
      'required' => true,
    ),
    'name' => 
    array (
      'name' => 'name',
      'type' => 'name',
      'required' => true,
    ),
    'date_entered' => 
    array (
      'name' => 'date_entered',
      'type' => 'datetime',
      'required' => true,
    ),
    'date_modified' => 
    array (
      'name' => 'date_modified',
      'type' => 'datetime',
      'required' => true,
    ),
    'modified_user_id' => 
    array (
      'name' => 'modified_user_id',
      'type' => 'id',
    ),
    'created_by' => 
    array (
      'name' => 'created_by',
      'type' => 'id',
    ),
    'deleted' => 
    array (
      'name' => 'deleted',
      'type' => 'bool',
      'default' => '0',
      'required' => true,
    ),
    'category' => 
    array (
      'name' => 'category',
      'type' => 'varchar',
      'len' => 50,
      'required' => true,
    ),
    'config_name' => 
    array (
      'name' => 'config_name',
      'type' => 'varchar',
      'len' => 100,
      'required' => true,
    ),
    'value' => 
    array (
      'name' => 'value',
      'type' => 'text',
    ),
    'description' => 
    array (
      'name' => 'description',
      'type' => 'varchar',
      'len' => 255,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'lf_pr_config_pk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'idx_category_config_name',
      'type' => 'unique',
      'fields' => 
      array (
        0 => 'category',
        1 => 'config_name',
      ),
    ),
  ),
  'custom_fields' => false,
);