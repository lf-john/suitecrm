<?php 
 $GLOBALS["dictionary"]["LF_PlanProspectItem"]=array (
  'table' => 'lf_plan_prospect_items',
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
      'required' => true,
    ),
    'source_type' => 
    array (
      'name' => 'source_type',
      'type' => 'varchar',
      'len' => 100,
    ),
    'planned_day' => 
    array (
      'name' => 'planned_day',
      'type' => 'enum',
      'options' => 'lf_planned_day_dom',
    ),
    'expected_value' => 
    array (
      'name' => 'expected_value',
      'type' => 'decimal',
      'dbType' => 'decimal',
      'len' => '26,6',
    ),
    'plan_description' => 
    array (
      'name' => 'plan_description',
      'type' => 'text',
    ),
    'status' => 
    array (
      'name' => 'status',
      'type' => 'enum',
      'options' => 'lf_prospect_status_dom',
      'default' => 'planned',
    ),
    'converted_opportunity_id' => 
    array (
      'name' => 'converted_opportunity_id',
      'type' => 'id',
    ),
    'prospecting_notes' => 
    array (
      'name' => 'prospecting_notes',
      'type' => 'text',
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'lf_plan_prospect_items_pk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'idx_lf_weekly_plan_id',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'lf_weekly_plan_id',
      ),
    ),
  ),
  'custom_fields' => false,
);