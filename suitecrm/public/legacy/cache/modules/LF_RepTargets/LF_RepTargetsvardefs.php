<?php 
 $GLOBALS["dictionary"]["LF_RepTargets"]=array (
  'table' => 'lf_rep_targets',
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
    'assigned_user_id' => 
    array (
      'name' => 'assigned_user_id',
      'type' => 'id',
      'required' => true,
    ),
    'fiscal_year' => 
    array (
      'name' => 'fiscal_year',
      'type' => 'int',
      'required' => true,
    ),
    'annual_quota' => 
    array (
      'name' => 'annual_quota',
      'type' => 'decimal',
      'dbType' => 'decimal',
      'len' => '26,6',
    ),
    'weekly_new_pipeline' => 
    array (
      'name' => 'weekly_new_pipeline',
      'type' => 'decimal',
      'dbType' => 'decimal',
      'len' => '26,6',
    ),
    'weekly_progression' => 
    array (
      'name' => 'weekly_progression',
      'type' => 'decimal',
      'dbType' => 'decimal',
      'len' => '26,6',
    ),
    'weekly_closed' => 
    array (
      'name' => 'weekly_closed',
      'type' => 'decimal',
      'dbType' => 'decimal',
      'len' => '26,6',
    ),
    'is_active' => 
    array (
      'name' => 'is_active',
      'type' => 'bool',
      'default' => 1,
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'lf_rep_targets_pk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'idx_rep_targets_user_year',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'assigned_user_id',
        1 => 'fiscal_year',
      ),
    ),
  ),
  'custom_fields' => false,
);