<?php 
 $GLOBALS["dictionary"]["LF_WeeklyPlan"]=array (
  'table' => 'lf_weekly_plan',
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
    'week_start_date' => 
    array (
      'name' => 'week_start_date',
      'type' => 'date',
      'required' => true,
    ),
    'status' => 
    array (
      'name' => 'status',
      'type' => 'enum',
      'options' => 'lf_plan_status_dom',
      'default' => 'in_progress',
    ),
    'submitted_date' => 
    array (
      'name' => 'submitted_date',
      'type' => 'datetime',
    ),
    'reviewed_by' => 
    array (
      'name' => 'reviewed_by',
      'type' => 'id',
    ),
    'reviewed_date' => 
    array (
      'name' => 'reviewed_date',
      'type' => 'datetime',
    ),
    'notes' => 
    array (
      'name' => 'notes',
      'type' => 'text',
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'lf_weekly_plan_pk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'idx_lf_weekly_plan_user_week',
      'type' => 'unique',
      'fields' => 
      array (
        0 => 'assigned_user_id',
        1 => 'week_start_date',
      ),
    ),
  ),
  'custom_fields' => false,
);