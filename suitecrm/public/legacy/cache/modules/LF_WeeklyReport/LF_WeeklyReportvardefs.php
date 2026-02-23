<?php 
 $GLOBALS["dictionary"]["LF_WeeklyReport"]=array (
  'table' => 'lf_weekly_report',
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
    'lf_weekly_plan_id' => 
    array (
      'name' => 'lf_weekly_plan_id',
      'type' => 'id',
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
      'name' => 'lf_weekly_report_pk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'idx_user_week',
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