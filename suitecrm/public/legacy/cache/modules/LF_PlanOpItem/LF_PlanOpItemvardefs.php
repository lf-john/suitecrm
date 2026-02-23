<?php 
 $GLOBALS["dictionary"]["LF_PlanOpItem"]=array (
  'table' => 'lf_plan_op_items',
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
    'opportunity_id' => 
    array (
      'name' => 'opportunity_id',
      'type' => 'id',
      'required' => true,
    ),
    'item_type' => 
    array (
      'name' => 'item_type',
      'type' => 'enum',
      'options' => 'lf_plan_item_type_dom',
    ),
    'projected_stage' => 
    array (
      'name' => 'projected_stage',
      'type' => 'varchar',
      'len' => 100,
    ),
    'planned_day' => 
    array (
      'name' => 'planned_day',
      'type' => 'enum',
      'options' => 'lf_planned_day_dom',
    ),
    'plan_description' => 
    array (
      'name' => 'plan_description',
      'type' => 'text',
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'lf_plan_op_items_pk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'idx_plan_id',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'lf_weekly_plan_id',
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