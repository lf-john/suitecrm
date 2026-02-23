<?php 
 $GLOBALS["dictionary"]["LF_ReportSnapshot"]=array (
  'table' => 'lf_report_snapshots',
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
    'lf_weekly_report_id' => 
    array (
      'name' => 'lf_weekly_report_id',
      'type' => 'id',
      'required' => true,
    ),
    'opportunity_id' => 
    array (
      'name' => 'opportunity_id',
      'type' => 'id',
      'required' => true,
    ),
    'account_name' => 
    array (
      'name' => 'account_name',
      'type' => 'varchar',
      'len' => 255,
    ),
    'opportunity_name' => 
    array (
      'name' => 'opportunity_name',
      'type' => 'varchar',
      'len' => 255,
    ),
    'amount_at_snapshot' => 
    array (
      'name' => 'amount_at_snapshot',
      'type' => 'decimal',
      'dbType' => 'decimal',
      'len' => '26,6',
    ),
    'stage_at_week_start' => 
    array (
      'name' => 'stage_at_week_start',
      'type' => 'varchar',
      'len' => 100,
    ),
    'stage_at_week_end' => 
    array (
      'name' => 'stage_at_week_end',
      'type' => 'varchar',
      'len' => 100,
    ),
    'probability_at_start' => 
    array (
      'name' => 'probability_at_start',
      'type' => 'int',
    ),
    'probability_at_end' => 
    array (
      'name' => 'probability_at_end',
      'type' => 'int',
    ),
    'movement' => 
    array (
      'name' => 'movement',
      'type' => 'enum',
      'options' => 'lf_movement_dom',
    ),
    'was_planned' => 
    array (
      'name' => 'was_planned',
      'type' => 'bool',
      'default' => '0',
    ),
    'plan_category' => 
    array (
      'name' => 'plan_category',
      'type' => 'varchar',
      'len' => 50,
    ),
    'result_description' => 
    array (
      'name' => 'result_description',
      'type' => 'text',
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'lf_report_snapshots_pk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'idx_lf_weekly_report_id',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'lf_weekly_report_id',
      ),
    ),
    2 => 
    array (
      'name' => 'idx_opportunity_id',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'opportunity_id',
      ),
    ),
  ),
  'custom_fields' => false,
);